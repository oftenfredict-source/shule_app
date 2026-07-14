<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Combie;
use App\Models\Attendance;
use App\Models\School;
use App\Models\Fee;
use App\Models\FeeInstallment;
use App\Models\FeeCondition;
use App\Models\StudentFeeCondition;
use App\Models\FeeAgreement;
use App\Models\OtherFeeDetail;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\ParentModel;
use App\Models\StudentFeePayment;
use App\Models\StudentEnrollment;
use App\Services\SmsService;
use App\Services\StudentEnrollmentService;
use App\Services\SelectedAcademicYearService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class FeesController extends Controller
{
    protected $smsService;

    public function __construct()
    {
        $this->smsService = new SmsService();
    }

    /**
     * Get current academic year ID for a school
     * Returns the active academic year ID, or the current year's academic year ID if found
     */
    private function getCurrentAcademicYearID($schoolID, $requestedYear = null)
    {
        $yearToUse = $requestedYear ?: date('Y');

        // 1. Try to get the specific year record
        $academicYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('year', $yearToUse)
            ->first();

        if ($academicYear) {
            return $academicYear->academic_yearID;
        }

        // 2. If not found, try to get ANY active year
        $activeYear = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('year', 'desc')
            ->first();

        if ($activeYear) {
            return $activeYear->academic_yearID;
        }

        // 3. Fallback: Get most recent year regardless of status
        $mostRecent = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->orderBy('year', 'desc')
            ->first();

        if ($mostRecent) {
            return $mostRecent->academic_yearID;
        }

        // 4. Final resort: Create the year record automatically so the user is never blocked
        try {
            return DB::table('academic_years')->insertGetId([
                'schoolID' => $schoolID,
                'year' => $yearToUse,
                'year_name' => (string)$yearToUse,
                'start_date' => $yearToUse . '-01-01',
                'end_date' => $yearToUse . '-12-31',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-create academic year: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Prefer header-selected academic year for the class (O/A aware), else getCurrentAcademicYearID.
     */
    private function resolveFeesAcademicYearID(int $schoolID, $classID = null): ?int
    {
        $yearService = app(SelectedAcademicYearService::class);

        if ($classID) {
            $class = ClassModel::find($classID);
            $yearId = $yearService->yearIdForClassName($class->class_name ?? null, $schoolID);
            if ($yearId) {
                return (int) $yearId;
            }
        }

        $selected = $yearService->selectedYearIds($schoolID);
        if (!empty($selected)) {
            return (int) $selected[0];
        }

        return $this->getCurrentAcademicYearID($schoolID);
    }

    /**
     * @return array{0: ?\Illuminate\Http\JsonResponse, 1: ?int, 2: array<int>}
     */
    private function activeEnrollmentStudentsForClass(int $schoolID, int $classID): array
    {
        $yearIds = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID);
        if (empty($yearIds)) {
            return [
                response()->json([
                    'success' => false,
                    'message' => 'Select an academic year in the header before managing fee agreements or discounts.',
                    'error_code' => 'no_academic_year',
                    'students' => [],
                    'agreements' => [],
                ], 422),
                null,
                [],
            ];
        }

        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, $classID);
        $enrollments = app(StudentEnrollmentService::class)
            ->enrollmentsInClass($classID, $schoolID, 'Active');

        $studentIds = $enrollments->pluck('studentID')->map(fn ($id) => (int) $id)->unique()->values()->all();

        if (empty($studentIds)) {
            return [
                response()->json([
                    'success' => true,
                    'message' => 'Selected academic year has no Active enrollments for this class.',
                    'error_code' => 'no_active_enrollments',
                    'students' => [],
                    'agreements' => [],
                    'academic_year_id' => $academicYearID,
                    'active_enrollment_count' => 0,
                ]),
                $academicYearID,
                [],
            ];
        }

        return [null, $academicYearID, $studentIds];
    }

    /**
     * Get previous years outstanding balance (debt) for a student.
     * Prefers live payments for prior academic years + payments_history for closed years.
     */
    private function getPreviousYearBalance($studentID, $schoolID, $currentAcademicYearID = null)
    {
        $schoolFeeBalance = 0;
        $otherContributionBalance = 0;
        $seenYearIds = [];

        // 1) Live payments from other academic years / enrollments
        $liveQuery = Payment::where('studentID', $studentID)
            ->where('schoolID', $schoolID);
        if ($currentAcademicYearID) {
            $liveQuery->where(function ($q) use ($currentAcademicYearID) {
                $q->whereNull('academic_yearID')
                    ->orWhere('academic_yearID', '!=', (int) $currentAcademicYearID);
            });
        }
        foreach ($liveQuery->get() as $payment) {
            $balance = (float) $payment->balance;
            if ($balance > 0) {
                $schoolFeeBalance += $balance;
            }
            if ($payment->academic_yearID) {
                $seenYearIds[] = (int) $payment->academic_yearID;
            }
        }

        // 2) Closed-year history not already covered by live payments
        if (Schema::hasTable('payments_history')) {
            $historyQuery = DB::table('payments_history')
                ->where('studentID', $studentID);
            if (Schema::hasColumn('payments_history', 'schoolID')) {
                $historyQuery->where('schoolID', $schoolID);
            }
            if ($currentAcademicYearID) {
                $historyQuery->where('academic_yearID', '!=', (int) $currentAcademicYearID);
            }
            if (!empty($seenYearIds)) {
                $historyQuery->whereNotIn('academic_yearID', array_unique($seenYearIds));
            }
            foreach ($historyQuery->get() as $payment) {
                $balance = (float) $payment->balance;
                if ($balance > 0) {
                    $schoolFeeBalance += $balance;
                }
            }
        }

        // Fallback: most recent closed year only (legacy) if nothing found yet
        if ($schoolFeeBalance <= 0 && empty($seenYearIds) && Schema::hasTable('payments_history')) {
            $closedAcademicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('status', 'Closed')
                ->orderBy('year', 'desc')
                ->first();

            if ($closedAcademicYear) {
                $previousYearPayments = DB::table('payments_history')
                    ->where('studentID', $studentID)
                    ->where('academic_yearID', $closedAcademicYear->academic_yearID)
                    ->get();

                foreach ($previousYearPayments as $payment) {
                    $balance = (float) $payment->balance;
                    if ($balance > 0) {
                        $schoolFeeBalance += $balance;
                    }
                }
            }
        }

        return [
            'school_fee_balance' => max(0, $schoolFeeBalance),
            'other_contribution_balance' => max(0, $otherContributionBalance),
        ];
    }
    public function manage_fees()
    {
      $userType = Session::get('user_type');
      $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get all classes for the school
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        // Get all fees with class information and relationships
        $fees = Fee::where('schoolID', $schoolID)
            ->with(['class', 'otherFeeDetails'])
            ->orderBy('classID')
            ->orderBy('display_order')
            ->orderBy('fee_name')
            ->get();

        // Group fees by class for widgets
        $feesByClass = $fees->groupBy('classID');

        return view('Admin.manageFees', compact('classes', 'fees', 'feesByClass'));
    }

    public function store_fee(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'classID' => 'required|exists:classes,classID',
            'duration' => 'required|in:Year,Month,Term,Semester,One-time',
            'fees' => 'required|array|min:1',
            'fees.*.name' => 'required|string|max:200',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.description' => 'nullable|string',
            'fees.*.must_start_pay' => 'nullable|boolean',
            'fees.*.deadline_amount' => 'nullable|numeric|min:0',
            'fees.*.deadline_date' => 'nullable|date',
            'allow_installments' => 'nullable|boolean',
            'default_installment_type' => 'nullable|in:Semester,Month,Two Months,Term,Quarter,One-time',
            'number_of_installments' => 'nullable|integer|min:1|max:12',
            'allow_partial_payment' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $createdFees = [];
            foreach ($request->fees as $index => $feeData) {
                $fee = Fee::create([
                    'schoolID' => $schoolID,
                    'classID' => $request->classID,
                    'fee_name' => $feeData['name'] ?? 'Fee',
                    'amount' => $feeData['amount'] ?? 0,
                    'must_start_pay' => isset($feeData['must_start_pay']) && $feeData['must_start_pay'] == 1,
                    'payment_deadline_amount' => $feeData['deadline_amount'] ?? null,
                    'payment_deadline_date' => $feeData['deadline_date'] ?? null,
                    'display_order' => $index,
                    'duration' => $request->duration,
                    'description' => $feeData['description'] ?? null,
                    'sms_assignment_template' => $request->input('sms_assignment_template'),
                    'sms_reminder_template' => $request->input('sms_reminder_template'),
                    'status' => 'Active',
                    'allow_installments' => $request->has('allow_installments'),
                    'default_installment_type' => $request->input('default_installment_type'),
                    'number_of_installments' => $request->input('number_of_installments'),
                    'allow_partial_payment' => $request->has('allow_partial_payment'),
                ]);

                // Create installments if allowed
                if ($request->has('allow_installments') && $request->input('number_of_installments')) {
                    $numInstallments = $request->input('number_of_installments');
                    $installmentType = $request->input('default_installment_type', 'Installment');
                    $amountPerInstallment = $fee->amount / $numInstallments;

                    for ($i = 1; $i <= $numInstallments; $i++) {
                        FeeInstallment::create([
                            'feeID' => $fee->feeID,
                            'installment_name' => $installmentType . ' ' . $i,
                            'installment_type' => $installmentType,
                            'installment_number' => $i,
                            'amount' => $amountPerInstallment,
                            'status' => 'Active',
                        ]);
                    }
                }

                // Handle Multiple Fee Conditions if provided
                if (isset($feeData['conditions']) && is_array($feeData['conditions'])) {
                    foreach ($feeData['conditions'] as $condData) {
                        FeeCondition::create([
                            'schoolID' => $schoolID,
                            'feeID' => $fee->feeID,
                            'condition_name' => $condData['name'] ?? 'Discount',
                            'discount_amount' => $condData['amount'] ?? 0,
                            'discount_type' => $condData['type'] ?? 'Fixed',
                            'description' => $condData['description'] ?? null, // Added description
                            'status' => 'Active',
                        ]);
                    }
                }

                $createdFees[] = $fee;
            }

            // Trigger Billing for all students in this class
            $this->generateBillsForClass($request->classID, $schoolID);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdFees) . ' Fees saved and assigned to students successfully.',
                'fees' => $createdFees
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee Assignment Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to generate/update billing for all students in a class
     */
    private function generateBillsForClass($classID, $schoolID)
    {
        $academicYearID = $this->resolveFeesAcademicYearID((int) $schoolID, (int) $classID);
        if (!$academicYearID) return;

        [, $yearId, $studentIds] = $this->activeEnrollmentStudentsForClass((int) $schoolID, (int) $classID);
        if (empty($studentIds)) {
            return;
        }

        $students = Student::where('schoolID', $schoolID)
            ->whereIn('studentID', $studentIds)
            ->get();

        foreach ($students as $student) {
            $this->generateStudentBill($student, $yearId ?: $academicYearID, $schoolID);
        }
    }

    /**
     * Helper to generate/update a specific student's bill (Single Control Number)
     */
    private function generateStudentBill($student, $academicYearID, $schoolID)
    {
        $enrollmentService = app(StudentEnrollmentService::class);
        $enrollmentID = $enrollmentService->bestEnrollmentIDForYear(
            $student->studentID,
            (int) $schoolID,
            (int) $academicYearID
        );

        $enrollment = $enrollmentID
            ? StudentEnrollment::with('classModel')->find($enrollmentID)
            : null;

        $classID = $enrollment->classID
            ?? ($student->subclass->classID ?? null);

        if (!$classID) {
            return null;
        }

        // 1. Get all active fees for enrollment/main class of that academic year
        $fees = Fee::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('status', 'Active')
            ->orderBy('display_order')
            ->get();

        // 2. Calculate totals with potential discounts
        $totalAmount = 0;
        $mustPayAmount = 0;

        foreach ($fees as $fee) {
            $effectiveAmount = $fee->amount;
            $feeNameNote = '';

            // Check for student-specific Agreement (Highest Priority)
            $agreement = FeeAgreement::where('studentID', $student->studentID)
                ->where('feeID', $fee->feeID)
                ->where('academic_yearID', $academicYearID)
                ->where('status', 'Active')
                ->first();

            if ($agreement) {
                $effectiveAmount = $agreement->agreed_amount;
                $feeNameNote = ' (Agreement)';
            } else {
                // Check for student-specific condition/discount for this fee
                $studentCondition = StudentFeeCondition::where('studentID', $student->studentID)
                    ->where('feeID', $fee->feeID)
                    ->where('academic_yearID', $academicYearID)
                    ->where('status', 'Active')
                    ->first();

                if ($studentCondition) {
                    $condition = FeeCondition::find($studentCondition->conditionID);
                    if ($condition && $condition->status === 'Active') {
                        $discount = $condition->discount_amount;
                        if ($condition->discount_type === 'Percentage') {
                            $effectiveAmount -= ($effectiveAmount * $discount / 100);
                        } else {
                            $effectiveAmount -= $discount;
                        }
                        $feeNameNote = ' (Discount)';
                    }
                }
            }

            $effectiveAmount = max(0, $effectiveAmount);
            $totalAmount += $effectiveAmount;
            if ($fee->must_start_pay) {
                $mustPayAmount += $effectiveAmount;
            }

            // Temporarily store effective amount on the model for step 4
            $fee->calculated_amount = $effectiveAmount;
            $fee->calculated_name = $fee->fee_name . $feeNameNote;
        }

        $previousYearBalance = $this->getPreviousYearBalance(
            $student->studentID,
            $schoolID,
            (int) $academicYearID
        );
        $debt = $previousYearBalance['school_fee_balance'] + $previousYearBalance['other_contribution_balance'];

        $totalRequiredAmount = $totalAmount + $debt;

        // Calculate split portions for sponsorship
        $sponsorPercentage = (float)($student->sponsorship_percentage ?? 0);
        $sponsorAmountReq = 0;
        $parentAmountReq = $totalRequiredAmount;

        if ($student->sponsor_id && $sponsorPercentage > 0) {
            if ($sponsorPercentage >= 100) {
                $sponsorAmountReq = $totalRequiredAmount;
                $parentAmountReq = 0;
            } else {
                $sponsorAmountReq = ($totalRequiredAmount * $sponsorPercentage) / 100;
                $parentAmountReq = $totalRequiredAmount - $sponsorAmountReq;
            }
        }

        // 3. Get existing control number or generate new one
        $payment = Payment::where('studentID', $student->studentID)
            ->where('academic_yearID', $academicYearID)
            ->first();

        if (!$payment) {
            // New Control Number Logic (Strictly numeric, starts with 3345)
            $controlNumber = $this->generateControlNumber($schoolID, $student->studentID);

            $payment = Payment::create([
                'schoolID' => $schoolID,
                'academic_yearID' => $academicYearID,
                'studentID' => $student->studentID,
                'enrollmentID' => $enrollmentID,
                'control_number' => $controlNumber,
                'amount_required' => $totalRequiredAmount,
                'amount_paid' => 0,
                'balance' => $totalRequiredAmount,
                'debt' => $debt,
                'required_fees_amount' => $mustPayAmount,
                'required_fees_paid' => 0,
                'can_start_school' => $mustPayAmount <= 0,
                'payment_status' => 'Pending',
                'sponsor_amount_required' => $sponsorAmountReq,
                'sponsor_amount_paid' => 0,
                'sponsor_balance' => $sponsorAmountReq,
                'parent_amount_required' => $parentAmountReq,
                'parent_amount_paid' => 0,
                'parent_balance' => $parentAmountReq,
            ]);
        } else {
            // Update amounts (keep existing paid amount)
            $paymentUpdate = [
                'amount_required' => $totalRequiredAmount,
                'balance' => max(0, $totalRequiredAmount - $payment->amount_paid),
                'debt' => $debt,
                'required_fees_amount' => $mustPayAmount,
                'can_start_school' => ($payment->required_fees_paid >= $mustPayAmount),
                'sponsor_amount_required' => $sponsorAmountReq,
                'sponsor_balance' => max(0, $sponsorAmountReq - $payment->sponsor_amount_paid),
                'parent_amount_required' => $parentAmountReq,
                'parent_balance' => max(0, $parentAmountReq - $payment->parent_amount_paid),
            ];
            if (empty($payment->enrollmentID) && $enrollmentID) {
                $paymentUpdate['enrollmentID'] = $enrollmentID;
            }
            $payment->update($paymentUpdate);
            $payment->refresh();
        }

        // 4. Update individual fee payment tracking entries
        foreach ($fees as $fee) {
            $sfp = StudentFeePayment::where('studentID', $student->studentID)
                ->where('feeID', $fee->feeID)
                ->where('paymentID', $payment->paymentID)
                ->first();

            if (!$sfp) {
                StudentFeePayment::create([
                    'schoolID' => $schoolID,
                    'studentID' => $student->studentID,
                    'enrollmentID' => $payment->enrollmentID ?: $enrollmentID,
                    'paymentID' => $payment->paymentID,
                    'feeID' => $fee->feeID,
                    'fee_name' => $fee->calculated_name ?? $fee->fee_name,
                    'fee_total_amount' => $fee->calculated_amount,
                    'amount_paid' => 0,
                    'balance' => $fee->calculated_amount,
                    'is_required' => $fee->must_start_pay,
                    'display_order' => $fee->display_order,
                ]);
            } else {
                $sfpUpdate = [
                    'paymentID' => $payment->paymentID,
                    'fee_name' => $fee->calculated_name ?? $fee->fee_name,
                    'fee_total_amount' => $fee->calculated_amount,
                    'balance' => $fee->calculated_amount - $sfp->amount_paid,
                    'is_required' => $fee->must_start_pay,
                    'display_order' => $fee->display_order,
                ];
                if (empty($sfp->enrollmentID)) {
                    $sfpUpdate['enrollmentID'] = $payment->enrollmentID ?: $enrollmentID;
                }
                $sfp->update($sfpUpdate);
            }
        }

        return $payment;
    }


    public function update_fee(Request $request, $feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

    $validator = Validator::make($request->all(), [
        'classID' => 'required|exists:classes,classID',
        'fee_name' => 'required|string|max:200',
        'amount' => 'required|numeric|min:0',
        'must_start_pay' => 'nullable|boolean',
        'payment_deadline_amount' => 'nullable|numeric|min:0',
        'payment_deadline_date' => 'nullable|date',
        'duration' => 'required|in:Year,Month,Term,Semester,One-time',
        'description' => 'nullable|string',
        'allow_installments' => 'nullable|boolean',
        'default_installment_type' => 'nullable|in:Semester,Month,Two Months,Term,Quarter,One-time',
        'number_of_installments' => 'nullable|integer|min:1|max:12',
        'allow_partial_payment' => 'nullable|boolean',
        'has_condition' => 'nullable|boolean',
        'condition_name' => 'nullable|string|required_if:has_condition,true',
        'discount_amount' => 'nullable|numeric|min:0|required_if:has_condition,true',
        'discount_type' => 'nullable|in:Fixed,Percentage',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $fee->update([
            'classID' => $request->classID,
            'fee_name' => $request->fee_name,
            'amount' => $request->amount,
            'must_start_pay' => $request->has('must_start_pay'),
            'payment_deadline_amount' => $request->payment_deadline_amount,
            'payment_deadline_date' => $request->payment_deadline_date,
            'duration' => $request->duration,
            'description' => $request->description,
            'sms_assignment_template' => $request->sms_assignment_template,
            'sms_reminder_template' => $request->sms_reminder_template,
            'allow_installments' => $request->has('allow_installments'),
            'default_installment_type' => $request->input('default_installment_type'),
            'number_of_installments' => $request->input('number_of_installments'),
            'allow_partial_payment' => $request->has('allow_partial_payment'),
        ]);

        // Handle Fee Condition updates (Multiple)
        if ($request->has('conditions') && is_array($request->conditions)) {
            // First, deactivate all existing conditions for this fee
            FeeCondition::where('feeID', $fee->feeID)->update(['status' => 'Inactive']);
            
            foreach ($request->conditions as $condData) {
                if (!isset($condData['name']) || empty($condData['name'])) continue;

                if (isset($condData['id']) && !empty($condData['id'])) {
                    // Update existing
                    FeeCondition::where('conditionID', $condData['id'])
                        ->where('feeID', $fee->feeID)
                        ->update([
                            'condition_name' => $condData['name'],
                            'discount_amount' => $condData['amount'] ?? 0,
                            'discount_type' => $condData['type'] ?? 'Fixed',
                            'description' => $condData['description'] ?? null, // Added description
                            'status' => 'Active',
                        ]);
                } else {
                    // Create new
                    FeeCondition::create([
                        'schoolID' => $schoolID,
                        'feeID' => $fee->feeID,
                        'condition_name' => $condData['name'],
                        'discount_amount' => $condData['amount'] ?? 0,
                        'discount_type' => $condData['type'] ?? 'Fixed',
                        'description' => $condData['description'] ?? null, // Added description
                        'status' => 'Active',
                    ]);
                }
            }
        } else {
            // No conditions provided - deactivate all
            FeeCondition::where('feeID', $fee->feeID)->update(['status' => 'Inactive']);
        }

        // Delete existing installments and create new ones if allowed
        FeeInstallment::where('feeID', $fee->feeID)->delete();

        if ($request->has('allow_installments') && $request->input('number_of_installments') && $request->input('default_installment_type')) {
            $installmentType = $request->input('default_installment_type');
            $numberOfInstallments = $request->input('number_of_installments');
            $amountPerInstallment = $request->amount / $numberOfInstallments;

            for ($i = 1; $i <= $numberOfInstallments; $i++) {
                FeeInstallment::create([
                    'feeID' => $fee->feeID,
                    'installment_name' => $installmentType . ' ' . $i,
                    'installment_type' => $installmentType,
                    'installment_number' => $i,
                    'amount' => $amountPerInstallment,
                    'status' => 'Active',
                ]);
            }
        }

            // Handle student assignments for removed conditions
            $deactivatedCondIDs = FeeCondition::where('schoolID', $schoolID)
                ->where('feeID', $feeID)
                ->where('status', 'Deactivated')
                ->pluck('conditionID');
            
            if ($deactivatedCondIDs->isNotEmpty()) {
                StudentFeeCondition::whereIn('conditionID', $deactivatedCondIDs)
                    ->update(['status' => 'Inactive']);
            }

            // Update bills for this class to reflect any changes in conditions/discounts
            $this->generateBillsForClass($fee->classID, $schoolID);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee updated successfully. Bills recalculated for the class.',
                'fee' => $fee->load(['class', 'installments', 'feeConditions'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update fee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_fee($feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $classID = $fee->classID;
            
            // 1. Deactivate related Agreements
            FeeAgreement::where('feeID', $feeID)->update(['status' => 'Inactive']);

            // 2. Deactivate related Student Conditions
            $conditionIDs = FeeCondition::where('feeID', $feeID)->pluck('conditionID');
            if ($conditionIDs->isNotEmpty()) {
                StudentFeeCondition::whereIn('conditionID', $conditionIDs)
                    ->update(['status' => 'Inactive']);
            }

            // 3. Delete the fee itself (soft delete or regular delete depending on model setup, 
            // the existing code uses delete())
            $fee->delete();

            // 4. Update bills for the entire class
            $this->generateBillsForClass($classID, $schoolID);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee deleted successfully. All student bills for ' . $fee->class->class_name . ' have been updated.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete fee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggle_fee_status($feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        try {
            $fee->status = $fee->status === 'Active' ? 'Inactive' : 'Active';
            $fee->save();

            return response()->json([
                'success' => true,
                'message' => 'Fee status updated successfully',
                'fee' => $fee
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update fee status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_fee($feeID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $fee = Fee::where('feeID', $feeID)
            ->where('schoolID', $schoolID)
            ->with(['class', 'installments', 'otherFeeDetails', 'feeConditions'])
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        // Format other fee details for JSON response
        $fee->other_fee_details = $fee->otherFeeDetails->map(function($detail) {
            return [
                'detailID' => $detail->detailID,
                'fee_detail_name' => $detail->fee_detail_name,
                'amount' => (float) $detail->amount,
                'description' => $detail->description,
            ];
        });

        return response()->json([
            'success' => true,
            'fee' => $fee
        ], 200);
    }

    /**
     * View payments and control numbers
     */
    public function view_payments()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        // Get school info
        $school = School::find($schoolID);

        // Get academic years from academic_years table (Active and Closed for dropdown)
        $academicYears = DB::table('academic_years')
            ->where('schoolID', $schoolID)
            ->orderBy('year', 'desc')
            ->get();

        // Format academic years for dropdown (show both Active and Closed)
        $availableYears = [];
        foreach ($academicYears as $academicYear) {
            $availableYears[] = [
                'year' => $academicYear->year,
                'year_name' => $academicYear->year_name,
                'status' => $academicYear->status,
                'academic_yearID' => $academicYear->academic_yearID,
            ];
        }

        // Get current year and next year
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        // Default academic year from header selection
        $yearService = app(SelectedAcademicYearService::class);
        $selectedYearIds = $yearService->selectedYearIds((int) $schoolID);
        $defaultAcademicYearID = $selectedYearIds[0] ?? null;
        $defaultYear = $currentYear;
        if ($defaultAcademicYearID) {
            $headerYear = collect($availableYears)->firstWhere('academic_yearID', $defaultAcademicYearID);
            if ($headerYear) {
                $defaultYear = $headerYear['year'];
            }
        } elseif (!empty($availableYears)) {
            $defaultAcademicYearID = $availableYears[0]['academic_yearID'] ?? null;
            $defaultYear = $availableYears[0]['year'] ?? $currentYear;
        }

        // Get all classes and subclasses for filters
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get();

        $subclasses = Subclass::whereIn('classID', $classes->pluck('classID'))
            ->where('status', 'Active')
            ->orderBy('subclass_name')
            ->get();

        return view('Admin.viewPayments', compact(
            'school',
            'availableYears',
            'defaultYear',
            'defaultAcademicYearID',
            'currentYear',
            'nextYear',
            'classes',
            'subclasses'
        ));
    }

    /**
     * Resolve academic year for payments filters.
     *
     * @return array{academicYearID: ?int, year: string|int, isClosedYear: bool}
     */
    private function resolvePaymentsAcademicYear(int $schoolID, Request $request): array
    {
        $academicYearID = $request->input('academic_year_id');
        $year = $request->input('year', '');

        $academicYear = null;
        if (!empty($academicYearID)) {
            $academicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->first();
        } elseif ($year !== '' && $year !== null) {
            $academicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('year', $year)
                ->orderByDesc('is_current')
                ->orderByDesc('academic_yearID')
                ->first();
        }

        if (!$academicYear) {
            $selectedIds = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID);
            if (!empty($selectedIds)) {
                $academicYear = DB::table('academic_years')
                    ->where('schoolID', $schoolID)
                    ->where('academic_yearID', $selectedIds[0])
                    ->first();
            }
        }

        if (!$academicYear) {
            $academicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderByDesc('year')
                ->first();
        }

        return [
            'academicYearID' => $academicYear ? (int) $academicYear->academic_yearID : null,
            'year' => $academicYear->year ?? ($year ?: date('Y')),
            'isClosedYear' => $academicYear && ($academicYear->status ?? '') === 'Closed',
        ];
    }

    /**
     * Student IDs enrolled in a given academic year (+ optional class/subclass/status).
     */
    private function paymentCohortStudentIDs(
        int $schoolID,
        ?int $academicYearID,
        string $enrollmentStatus = 'Active',
        $classID = null,
        $subclassID = null
    ): array {
        if (!$academicYearID) {
            return [];
        }

        $query = StudentEnrollment::query()
            ->where('schoolID', $schoolID)
            ->where('academic_yearID', $academicYearID);

        if ($subclassID) {
            $query->where('subclassID', (int) $subclassID);
        } elseif ($classID) {
            $query->where('classID', (int) $classID);
        }

        $normalized = $enrollmentStatus === 'Graduated' ? 'Completed' : $enrollmentStatus;
        if ($normalized !== '' && strtolower($normalized) !== 'all') {
            $allowed = ['Active', 'Completed', 'Transferred', 'Dropped', 'Cancelled'];
            if (in_array($normalized, $allowed, true)) {
                $query->where('status', $normalized);
            }
        }

        return $query
            ->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
            ->orderByDesc('enrollmentID')
            ->get()
            ->unique('studentID')
            ->pluck('studentID')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Enrollment meta for payment list display keyed by studentID.
     */
    private function paymentCohortEnrollmentMeta(
        int $schoolID,
        ?int $academicYearID,
        array $studentIDs
    ): array {
        if (!$academicYearID || empty($studentIDs)) {
            return [];
        }

        $rows = StudentEnrollment::with(['classModel', 'subclass'])
            ->where('schoolID', $schoolID)
            ->where('academic_yearID', $academicYearID)
            ->whereIn('studentID', $studentIDs)
            ->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
            ->orderByDesc('enrollmentID')
            ->get()
            ->unique('studentID');

        $map = [];
        foreach ($rows as $enr) {
            $map[(int) $enr->studentID] = [
                'enrollmentID' => (int) $enr->enrollmentID,
                'enrollment_status' => $enr->status,
                'class_name' => $enr->classModel->class_name ?? null,
                'subclass_name' => $enr->subclass->subclass_name ?? null,
            ];
        }

        return $map;
    }

    /**
     * Get payments data via AJAX with filtering
     */
    public function get_payments_ajax(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID)
        {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            // Get filter parameters
            $classID = $request->input('class_id', '');
            $subclassID = $request->input('subclass_id', '');
            $enrollmentStatus = $request->input(
                'enrollment_status',
                $request->input('student_status', 'Active')
            );
            if ($enrollmentStatus === null || $enrollmentStatus === '') {
                $enrollmentStatus = 'all';
            }
            $paymentStatus = $request->input('payment_status', '');
            $searchStudentName = $request->input('search_student_name', '');
            $sponsorshipFilter = $request->input('sponsorship_filter', ''); // 'sponsored' or 'self'

            $resolved = $this->resolvePaymentsAcademicYear((int) $schoolID, $request);
            $academicYearID = $resolved['academicYearID'];
            $year = $resolved['year'];
            $isClosedYear = $resolved['isClosedYear'];

            $studentIDs = $this->paymentCohortStudentIDs(
                (int) $schoolID,
                $academicYearID,
                (string) $enrollmentStatus,
                $classID !== '' ? $classID : null,
                $subclassID !== '' ? $subclassID : null
            );

            if (empty($studentIDs)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'statistics' => [
                        'pending_payments' => 0,
                        'incomplete_payments' => 0,
                        'paid_payments' => 0,
                        'overpaid_payments' => 0,
                        'total_required' => 0,
                        'total_paid' => 0,
                        'total_balance' => 0,
                    ],
                    'is_closed_year' => $isClosedYear,
                    'error_code' => 'no_enrollments',
                    'message' => 'No enrollments found for the selected academic year / enrollment status.',
                ]);
            }

            $enrollmentMeta = $this->paymentCohortEnrollmentMeta(
                (int) $schoolID,
                $academicYearID,
                $studentIDs
            );

            $query = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $studentIDs)
                ->with(['parent', 'sponsor', 'subclass.class', 'payments' => function($q) use ($academicYearID, $year) {
                    if ($academicYearID) {
                        $q->where('academic_yearID', $academicYearID);
                    } else {
                        $q->whereYear('created_at', $year);
                    }
                }, 'payments.fee_payments', 'payments.academicYear', 'payments.paymentRecords']);

            // Filter by sponsorship
            if ($sponsorshipFilter === 'sponsored') {
                $query->whereNotNull('sponsor_id')
                      ->where('sponsorship_percentage', '>', 0);
            } elseif ($sponsorshipFilter === 'self') {
                $query->where(function($q) {
                    $q->whereNull('sponsor_id')
                      ->orWhere('sponsorship_percentage', '<=', 0);
                });
            }

            // Filter by student name search
            if (!empty($searchStudentName)) {
                $query->where(function($q) use ($searchStudentName) {
                    $q->where('first_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('last_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('middle_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%'])
                      ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%']);
                });
            }

            $students = $query->orderBy('first_name')->get();

            // Group payments by student and aggregate totals
            $filteredData = [];
            $index = 1; // Initialize index
            foreach ($students as $student) {
                // Get the unified payment record for this student and year
                $payment = $student->payments->first(); // Should be only one due to generateStudentBill logic

                if (!$payment && !empty($paymentStatus)) {
                    if ($paymentStatus !== "Pending") continue;
                }

                if ($payment && !empty($paymentStatus)) {
                    $status = $payment->payment_status;
                    if ($paymentStatus === 'Incomplete' || $paymentStatus === 'Partial' || $paymentStatus === 'Incomplete Payment') {
                        if ($status !== 'Incomplete Payment' && $status !== 'Partial') continue;
                    } elseif ($status !== $paymentStatus) {
                        continue;
                    }
                }

                // Calculate verification status
                $hasUnverified = $payment ? $payment->paymentRecords()->where('is_verified', false)->exists() : false;
                $hasVerified = $payment ? $payment->paymentRecords()->where('is_verified', true)->exists() : false;

                $verificationStatus = 'Unverified';
                if ($payment) {
                    if ($payment->amount_paid == 0) {
                        $verificationStatus = 'No Payment';
                    } elseif (!$hasUnverified && $hasVerified) {
                        $verificationStatus = 'Verified';
                    } elseif ($hasUnverified && $hasVerified) {
                        $verificationStatus = 'Partially Verified';
                    } else {
                        $verificationStatus = 'Pending Verification';
                    }
                }

                $enrMeta = $enrollmentMeta[(int) $student->studentID] ?? null;

                $studentData = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'status' => $student->status,
                    'enrollment_status' => $enrMeta['enrollment_status'] ?? null,
                    'enrollmentID' => $enrMeta['enrollmentID'] ?? null,
                    'photo' => $student->photo ? asset('userImages/' . $student->photo) : null,
                    'parent' => $student->parent ? [
                        'first_name' => $student->parent->first_name,
                        'last_name' => $student->parent->last_name,
                        'phone' => $student->parent->phone,
                    ] : null,
                    'subclass' => [
                        'subclass_name' => $enrMeta['subclass_name']
                            ?? ($student->subclass->subclass_name ?? 'N/A'),
                        'class_name' => $enrMeta['class_name']
                            ?? ($student->subclass->class->class_name ?? 'N/A'),
                    ],
                    'sponsor' => $student->sponsor ? [
                        'sponsor_name' => $student->sponsor->sponsor_name,
                        'contact_person' => $student->sponsor->contact_person,
                        'phone' => $student->sponsor->phone,
                        'email' => $student->sponsor->email,
                        'percentage' => $student->sponsorship_percentage,
                    ] : null,
                ];

                $totalRequired = $payment ? $payment->amount_required : 0;
                $totalPaid = $payment ? $payment->amount_paid : 0;
                $totalBalance = $payment ? $payment->balance : 0;
                $requiredPaid = $payment ? $payment->required_fees_paid : 0;
                $requiredAmount = $payment ? $payment->required_fees_amount : 0;
                $canStartSchool = $payment ? $payment->can_start_school : false;

                // Prior enrollment years still owing (excluding filtered year)
                $priorDebtInfo = $this->getPreviousYearBalance(
                    $student->studentID,
                    $schoolID,
                    $academicYearID
                );
                $priorYearsOwed = (float) ($priorDebtInfo['school_fee_balance'] ?? 0)
                    + (float) ($priorDebtInfo['other_contribution_balance'] ?? 0);

                // No calculation for sponsorAmount is required as sponsors do not adjust 'totalRequiredAmount'
                $originalTotal = $totalRequired;
                $sponsorAmount = 0;

                $filteredData[] = [
                    'index' => $index++,
                    'student' => $studentData,
                    'payment' => $payment,
                    'totals' => [
                        'total_required' => $totalRequired,
                        'total_paid' => $totalPaid,
                        'total_balance' => $totalBalance,
                        'required_paid' => $requiredPaid,
                        'required_amount' => $requiredAmount,
                        'can_start_school' => $canStartSchool,
                        'original_total' => $originalTotal,
                        'sponsor_amount' => $sponsorAmount,
                        'prior_years_owed' => $priorYearsOwed,
                        'overall_status' => $payment ? $payment->payment_status : 'Pending',
                    ],
                    'payment_status' => $payment ? $payment->payment_status : 'Pending',
                    'verification_status' => $verificationStatus,
                    'academic_year' => $year,
                    'prior_years_owed' => $priorYearsOwed,
                ];
            }
            $totalRequired = 0;
            $totalPaid = 0;
            $totalBalance = 0;
            $pendingPayments = 0;
            $incompletePayments = 0;
            $paidPayments = 0;
            $overpaidPayments = 0;

            foreach ($filteredData as $item) {
                if (isset($item['totals'])) {
                    $totals = $item['totals'];
                    $totalRequired += $totals['total_required'] ?? 0;
                    $totalPaid += $totals['total_paid'] ?? 0;
                    $totalBalance += $totals['total_balance'] ?? 0;

                    $status = $item['payment_status'] ?? 'Pending';
                    if ($status === 'Pending' || $status === 'No Billing') {
                        $pendingPayments++;
                    } elseif ($status === 'Incomplete Payment' || $status === 'Partial') {
                        $incompletePayments++;
                    } elseif ($status === 'Paid') {
                        $paidPayments++;
                    } elseif ($status === 'Overpaid') {
                        $overpaidPayments++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'academic_year' => $year,
                'academic_year_id' => $academicYearID,
                'is_closed_year' => $isClosedYear,
                'statistics' => [
                    'total_students' => count($filteredData),
                    'total_payments' => count($filteredData),
                    'pending_payments' => $pendingPayments,
                    'incomplete_payments' => $incompletePayments,
                    'paid_payments' => $paidPayments,
                    'overpaid_payments' => $overpaidPayments,
                    'total_required' => $totalRequired,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * All enrollment / academic-year payment rows for one student (View More modal).
     */
    public function get_student_enrollment_payments($studentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $student = Student::where('studentID', $studentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        try {
            $enrollments = StudentEnrollment::with(['academicYear', 'classModel', 'subclass'])
                ->where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->orderByDesc('academic_yearID')
                ->orderByDesc('enrollmentID')
                ->get();

            $livePayments = Payment::with('academicYear')
                ->where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->get();

            $paymentsByEnrollment = $livePayments->filter(fn ($p) => !empty($p->enrollmentID))
                ->keyBy(fn ($p) => (int) $p->enrollmentID);
            $paymentsByYear = $livePayments->groupBy(fn ($p) => (int) ($p->academic_yearID ?? 0));

            $historyByYear = collect();
            if (Schema::hasTable('payments_history')) {
                $hq = DB::table('payments_history')->where('studentID', $studentID);
                if (Schema::hasColumn('payments_history', 'schoolID')) {
                    $hq->where('schoolID', $schoolID);
                }
                $historyByYear = $hq->get()->groupBy(fn ($p) => (int) ($p->academic_yearID ?? 0));
            }

            $years = [];
            $coveredYearIds = [];
            $totalRequired = 0;
            $totalPaid = 0;
            $totalOwed = 0;

            foreach ($enrollments as $enr) {
                $yearId = (int) $enr->academic_yearID;
                $coveredYearIds[] = $yearId;

                $payment = $paymentsByEnrollment->get((int) $enr->enrollmentID);
                if (!$payment) {
                    $payment = optional($paymentsByYear->get($yearId))->first();
                }

                $source = 'none';
                $amountRequired = 0;
                $amountPaid = 0;
                $balance = 0;
                $debt = 0;
                $paymentStatus = 'No Bill';
                $controlNumber = null;
                $paymentID = null;

                if ($payment) {
                    $source = 'live';
                    $amountRequired = (float) $payment->amount_required;
                    $amountPaid = (float) $payment->amount_paid;
                    $balance = (float) $payment->balance;
                    $debt = (float) ($payment->debt ?? 0);
                    $paymentStatus = $payment->payment_status ?: 'Pending';
                    $controlNumber = $payment->control_number;
                    $paymentID = $payment->paymentID;
                } elseif ($historyByYear->has($yearId)) {
                    $hist = $historyByYear->get($yearId)->first();
                    $source = 'history';
                    $amountRequired = (float) ($hist->amount_required ?? 0);
                    $amountPaid = (float) ($hist->amount_paid ?? 0);
                    $balance = (float) ($hist->balance ?? 0);
                    $debt = (float) ($hist->debt ?? 0);
                    $paymentStatus = $hist->payment_status ?? 'Archived';
                    $controlNumber = $hist->control_number ?? null;
                }

                $ay = $enr->academicYear;
                $yearLabel = $ay
                    ? ($ay->year_name ?? $ay->year)
                    : ('Year #' . $yearId);

                $years[] = [
                    'enrollmentID' => (int) $enr->enrollmentID,
                    'academic_yearID' => $yearId,
                    'year' => $ay->year ?? null,
                    'year_name' => $yearLabel,
                    'year_status' => $ay->status ?? null,
                    'class_name' => $enr->classModel->class_name ?? 'N/A',
                    'subclass_name' => $enr->subclass->subclass_name ?? 'N/A',
                    'enrollment_status' => $enr->status,
                    'promotion_status' => $enr->promotion_status,
                    'amount_required' => $amountRequired,
                    'amount_paid' => $amountPaid,
                    'balance' => $balance,
                    'debt' => $debt,
                    'payment_status' => $paymentStatus,
                    'control_number' => $controlNumber,
                    'paymentID' => $paymentID,
                    'source' => $source,
                    'owes' => $balance > 0,
                ];

                $totalRequired += $amountRequired;
                $totalPaid += $amountPaid;
                $totalOwed += max(0, $balance);
            }

            // Payments without matching enrollment (legacy)
            foreach ($livePayments as $payment) {
                $yearId = (int) ($payment->academic_yearID ?? 0);
                if ($yearId && in_array($yearId, $coveredYearIds, true)) {
                    continue;
                }
                $ay = $payment->academicYear
                    ?: ($yearId ? DB::table('academic_years')->where('academic_yearID', $yearId)->first() : null);
                $yearLabel = $ay
                    ? ($ay->year_name ?? $ay->year)
                    : 'Unknown year';

                $years[] = [
                    'enrollmentID' => $payment->enrollmentID ? (int) $payment->enrollmentID : null,
                    'academic_yearID' => $yearId ?: null,
                    'year' => is_object($ay) ? ($ay->year ?? null) : null,
                    'year_name' => $yearLabel,
                    'year_status' => is_object($ay) ? ($ay->status ?? null) : null,
                    'class_name' => $student->subclass->class->class_name ?? 'N/A',
                    'subclass_name' => $student->subclass->subclass_name ?? 'N/A',
                    'enrollment_status' => null,
                    'promotion_status' => null,
                    'amount_required' => (float) $payment->amount_required,
                    'amount_paid' => (float) $payment->amount_paid,
                    'balance' => (float) $payment->balance,
                    'debt' => (float) ($payment->debt ?? 0),
                    'payment_status' => $payment->payment_status ?: 'Pending',
                    'control_number' => $payment->control_number,
                    'paymentID' => $payment->paymentID,
                    'source' => 'live',
                    'owes' => (float) $payment->balance > 0,
                ];

                $totalRequired += (float) $payment->amount_required;
                $totalPaid += (float) $payment->amount_paid;
                $totalOwed += max(0, (float) $payment->balance);
            }

            return response()->json([
                'success' => true,
                'student' => [
                    'studentID' => $student->studentID,
                    'name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'admission_number' => $student->admission_number,
                ],
                'years' => $years,
                'summary' => [
                    'total_required' => $totalRequired,
                    'total_paid' => $totalPaid,
                    'total_owed' => $totalOwed,
                    'years_count' => count($years),
                    'years_owing' => collect($years)->where('owes', true)->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading student enrollment payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading enrollment payment history.',
            ], 500);
        }
    }

    /**
     * Generate control numbers for all active students (separate for Tuition and Other Fees)
     */
    public function generate_control_numbers(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            DB::beginTransaction();

            $requestedYear = $request->input('year');
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            if (!$academicYearID) {
                return response()->json(['success' => false, 'message' => 'No academic year found. Please set an academic year first.'], 404);
            }

            // Get the year value for logging
            $academicYear = DB::table('academic_years')->where('academic_yearID', $academicYearID)->first();
            $yearValue = $academicYear ? $academicYear->year : 'N/A';

            // Get all active students
            $students = Student::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->get();

            if ($students->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active students found.'], 404);
            }

            $generated = 0;
            $skipped = 0;
            foreach ($students as $student) {
                // Check if student already has a control number for this academic year
                $existingPayment = Payment::where('studentID', $student->studentID)
                    ->where('academic_yearID', $academicYearID)
                    ->whereNotNull('control_number')
                    ->where('control_number', '!=', '')
                    ->first();

                if (!$existingPayment) {
                    // generateStudentBill updates if exists, creates if not
                    $this->generateStudentBill($student, $academicYearID, $schoolID);
                    $generated++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $msg = "Control numbers generated for {$generated} new students for year {$yearValue}!";
            if ($skipped > 0) {
                $msg .= " ({$skipped} students skipped as they already have them)";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'generated' => $generated,
                'skipped' => $skipped
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating control numbers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate unique control number
     * Format: 3345XXXXX (9 digits total, starts with 3345, only digits)
     */
    private function generateControlNumber($schoolID, $studentID)
    {
        do {
            // Generate 5 random digits (0-9)
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Format: 3345 + 5 random digits = 9 digits total
            $controlNumber = '3345' . $randomDigits;

            // Check if control number already exists in active payments
            $existsActive = DB::table('payments')->where('control_number', $controlNumber)->exists();

            // Check if control number exists in history
            $existsHistory = DB::table('payments_history')->where('control_number', $controlNumber)->exists();

        } while ($existsActive || $existsHistory);

        return $controlNumber;
    }

    /**
     * Send control numbers to all parents via SMS
     */
    public function send_control_numbers_sms(Request $request)
    {
        set_time_limit(3600); // 60 minutes
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $school = School::find($schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 404);
            }

            // Get parameters
            $requestedYear = $request->input('year');
            $recipient = $request->input('recipient', 'parent'); // 'parent' | 'sponsor' | 'both'
            $targetStatus = $request->input('target_payment_status', ''); // '', 'no_payment', 'incomplete', 'overpaid', 'pending'
            $sponsorshipFilter = $request->input('sponsorship_filter', ''); // 'sponsored' | 'self' | ''

            // Resolve academic year
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            // Base query for payments
            $paymentsQuery = Payment::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->with(['student.parent', 'student.sponsor', 'fee_payments']);

            // Default previous behavior: only send new and not fully paid, when no explicit target provided
            if (empty($targetStatus)) {
                $paymentsQuery->where('sms_sent', 'No')
                              ->where('payment_status', '!=', 'Paid');
            }

            // Filter by sponsorship if requested
            if ($sponsorshipFilter === 'sponsored') {
                $paymentsQuery->whereHas('student', function($q) {
                    $q->whereNotNull('sponsor_id')->where('sponsorship_percentage', '>', 0);
                });
            } elseif ($sponsorshipFilter === 'self') {
                $paymentsQuery->whereHas('student', function($q) {
                    $q->whereNull('sponsor_id')->orWhere('sponsorship_percentage', '<=', 0);
                });
            }

            $payments = $paymentsQuery->get();

            if ($payments->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No new control numbers to send.'], 404);
            }

            $sent    = 0;
            $failed  = 0;
            $results = [];

            foreach ($payments as $payment) {
                $student = $payment->student;
                if (!$student) { $failed++; continue; }

                // Apply target payment status filter if provided
                if (!empty($targetStatus)) {
                    $statusOk = true;
                    $amtRequired = (float) ($payment->amount_required ?? 0);
                    $amtPaid = (float) ($payment->amount_paid ?? 0);
                    $status = $payment->payment_status ?? 'Pending';

                    switch ($targetStatus) {
                        case 'no_payment':
                            $statusOk = ($amtPaid <= 0.0);
                            break;
                        case 'incomplete':
                            $statusOk = ($amtPaid > 0.0 && $amtPaid < $amtRequired) || in_array($status, ['Incomplete Payment', 'Partial']);
                            break;
                        case 'overpaid':
                            $statusOk = ($amtPaid > $amtRequired) || $status === 'Overpaid';
                            break;
                        case 'pending':
                            $statusOk = $status === 'Pending' || $status === 'No Billing';
                            break;
                        case 'paid':
                            $statusOk = $status === 'Paid';
                            break;
                        default:
                            $statusOk = true;
                    }
                    if (!$statusOk) { continue; }
                }

                $parent = $student->parent ?? null;
                $sponsor = $student->sponsor ?? null;

                $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                $controlNumber = $payment->control_number;
                $requiredAmount = number_format($payment->required_fees_amount ?? 0, 0);

                // Determine recipients based on requested recipient mode
                $sendToSponsor = false;
                $sendToParent = false;
                if ($recipient === 'auto') {
                    $sendToSponsor = ($sponsor && !empty($sponsor->phone) && (float)($student->sponsorship_percentage ?? 0) > 0);
                    $sendToParent = !$sendToSponsor && ($parent && !empty($parent->phone));
                } elseif ($recipient === 'sponsor') {
                    $sendToSponsor = true;
                } elseif ($recipient === 'parent') {
                    $sendToParent = true;
                } elseif ($recipient === 'both') {
                    $sendToSponsor = true;
                    $sendToParent = true;
                }

                // Compute original total and sponsorship shares
                $baseFeesSum = $payment->fee_payments ? $payment->fee_payments->sum('fee_total_amount') : 0;
                $originalTotal = ($baseFeesSum + ($payment->debt ?? 0)) ?: ($payment->amount_required ?? 0);
                $p = (float) ($student->sponsorship_percentage ?? 0);
                $sponsorShareRaw = $p > 0 ? ($originalTotal * $p / 100.0) : 0;
                $sponsorShare = (int) round($sponsorShareRaw, 0);
                $parentShare = (int) max(0, round($originalTotal - $sponsorShare, 0));

                $totalAmountForMsg = number_format($originalTotal, 0);
                $parentShareMsg = number_format($parentShare, 0);
                $sponsorShareMsg = number_format($sponsorShare, 0);

                $sentAtLeastOne = false;
                $rowResult = [
                    'student_name' => 'Parent of ' . $studentName,
                    'phone'        => null,
                    'success'      => false,
                ];

                // Send to sponsor if requested and available
                if ($sendToSponsor && $sponsor && $sponsor->phone && $p > 0) {
                    $sponsorPct = (int)$p;
                    $messageSponsor = "";
                    if (!($school && $school->senderID)) {
                        $messageSponsor .= "{$school->school_name}: ";
                    }
                    $messageSponsor .= "Mdhamini wa {$studentName}, Control Number: {$controlNumber}. Lipa TZS {$sponsorShareMsg} ({$sponsorPct}% ya TZS {$totalAmountForMsg}). Lipia benki/mitandao.";
                    $res = $this->smsService->sendSms($sponsor->phone, $messageSponsor);
                    $rowResult['phone']   = $sponsor->phone;
                    $rowResult['success'] = $res['success'];
                    if ($res['success']) { $sent++; $sentAtLeastOne = true; } else { $failed++; }
                }

                // Send to parent if requested and available
                if ($sendToParent && $parent && $parent->phone) {
                    $messageParent = "";
                    if (!($school && $school->senderID)) {
                        $messageParent .= "{$school->school_name}: ";
                    }
                    if ($p > 0) {
                        $messageParent .= "Mzazi wa {$studentName}, CN: {$controlNumber}. Jumla TZS {$totalAmountForMsg}, ulipe TZS {$parentShareMsg} (mdhamini TZS {$sponsorShareMsg}). Kuanza: TZS {$requiredAmount}.";
                    } else {
                        $totalAmount = number_format($payment->amount_required ?? 0, 0);
                        $messageParent .= "Mzazi wa {$studentName}, amepangiwa CN: {$controlNumber}. Kiasi: TZS {$totalAmount}, kuanza: TZS {$requiredAmount}. Lipa kuepuka usumbufu.";
                    }
                    $res = $this->smsService->sendSms($parent->phone, $messageParent);
                    $rowResult['phone']   = $parent->phone;
                    $rowResult['success'] = $res['success'];
                    if ($res['success']) { $sent++; $sentAtLeastOne = true; } else { $failed++; }
                }

                $results[] = $rowResult;

                if ($sentAtLeastOne) {
                    $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                } else {
                    // If neither parent nor sponsor had a phone, count as failed once
                    if (!(($parent && $parent->phone) || ($sponsor && $sponsor->phone))) {
                        $failed++;
                        $rowResult['success'] = false;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "SMS zimetumwa! Imefanikiwa: {$sent}, Imefeli: {$failed}",
                'sent'    => $sent,
                'failed'  => $failed,
                'results' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send debt reminders to all parents with outstanding balances
     */
    public function send_debt_reminders_sms(Request $request)
    {
        set_time_limit(3600); // 60 minutes
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $school = DB::table('schools')->where('schoolID', $schoolID)->first();
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 404);
            }

            // Get requested year or active year
            $requestedYear = $request->input('year');
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);

            // Get all payments with balance > 0
            $payments = \App\Models\Payment::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->where('balance', '>', 0)
                ->with(['student.parent'])
                ->get();

            if ($payments->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Hakuna mwanafunzi mwenye deni la kutumiwa ujumbe.'], 404);
            }

            $sent    = 0;
            $failed  = 0;
            $results = [];

            foreach ($payments as $payment) {
                $student = $payment->student;
                $parent = $student ? $student->parent : null;

                if (!$parent || !$parent->phone) {
                    $failed++;
                    continue;
                }

                $studentName   = ($student->first_name ?? '') . ' ' . ($student->last_name ?? '');
                $controlNumber = $payment->control_number;
                $balance       = number_format($payment->balance, 0);

                // Short Kiswahili Message for Debt Reminder (≤163 chars)
                $message = "";
                if (!($school && $school->senderID)) {
                    $message .= "{$school->school_name}: ";
                }
                $message .= "Mzazi wa {$studentName} unakumbushwa kulipa deni, sh {$balance}, control number {$controlNumber}. Lipa kuepuka usumbufu.";

                // Send SMS using the service
                $smsResult = $this->smsService->sendSms($parent->phone, $message);

                $results[] = [
                    'student_name' => 'Parent of ' . $studentName,
                    'phone'        => $parent->phone,
                    'success'      => $smsResult['success'],
                ];

                if ($smsResult['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "SMS za kukumbusha deni zimetumwa! Imefanikiwa: {$sent}, Imefeli: {$failed}",
                'sent'    => $sent,
                'failed'  => $failed,
                'results' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending debt reminders: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get list of recipients (parents/sponsors) for the fee SMS modal
     * type: 'control_number' or 'debt_reminder'
     */
    public function get_fee_sms_recipients(Request $request)
    {
        $schoolID  = Session::get('schoolID');
        $userType  = Session::get('user_type');
        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $type              = $request->input('type', 'control_number'); // 'control_number' | 'debt_reminder'
        $requestedYear     = $request->input('year');
        $recipientType     = $request->input('recipient_type', 'parent'); // 'parent' | 'sponsor'

        try {
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);
            $recipients = [];

            if ($recipientType === 'sponsor') {
                // Better approach: Join tables and group by sponsor
                $recipients = Payment::join('students', 'payments.studentID', '=', 'students.studentID')
                    ->join('sponsors', 'students.sponsor_id', '=', 'sponsors.sponsorID')
                    ->where('payments.schoolID', $schoolID)
                    ->where('payments.sponsor_balance', '>', 0)
                    ->select(
                        'sponsors.sponsorID',
                        'sponsors.sponsor_name as recipientLabel',
                        'sponsors.phone',
                        DB::raw('COUNT(DISTINCT payments.studentID) as studentCount'),
                        DB::raw('SUM(payments.sponsor_balance) as balance')
                    )
                    ->groupBy('sponsors.sponsorID', 'sponsors.sponsor_name', 'sponsors.phone')
                    ->get()
                    ->map(function($item) {
                        $item->type = 'sponsor';
                        return $item;
                    })
                    ->toArray();
            } else {
                // Default: Parent
                $query = Payment::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->with(['student.parent', 'student.sponsor']);

                if ($type === 'debt_reminder') {
                    // For debt reminders, only show those where parent actually owes money
                    // EXCLUDE: 100% sponsored students, and parents with 0 balance
                    $query->whereHas('student', fn($q) => $q->where('sponsorship_percentage', '<', 100))
                          ->where('parent_balance', '>', 0);
                }

                $payments = $query->get();

                foreach ($payments as $payment) {
                    $student = $payment->student;
                    if (!$student) continue;

                    $parent = $student->parent ?? null;
                    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                    
                    $recipients[] = [
                        'paymentID'      => $payment->paymentID,
                        'recipientLabel' => 'Parent of ' . $studentName,
                        'studentName'    => $studentName,
                        'phone'          => $parent->phone ?? '',
                        'balance'        => $payment->parent_balance,
                        'type'           => 'parent'
                    ];
                }
            }

            return response()->json(['success' => true, 'recipients' => $recipients]);

        } catch (\Exception $e) {
            Log::error('get_fee_sms_recipients error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a single fee SMS (control number OR debt reminder) for one payment.
     * Called per-row by the JS async loop (same pattern as send_result_sms).
     */
    public function send_single_fee_sms(Request $request)
    {
        set_time_limit(120);
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');
        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $paymentID     = $request->input('paymentID');
        $sponsorID     = $request->input('sponsorID');
        $type          = $request->input('type', 'control_number'); // 'control_number' | 'debt_reminder'
        $recipientType = $request->input('recipient_type', 'parent');
        $year          = $request->input('year');

        try {
            $school = \App\Models\School::find($schoolID);
            $schoolName = $school->school_name ?? 'School';
            $academicYearID = $this->getCurrentAcademicYearID($schoolID, $year);

            if ($recipientType === 'sponsor') {
                if (!$sponsorID) {
                    return response()->json(['success' => false, 'message' => 'Missing sponsor ID']);
                }

                $sponsor = \App\Models\Sponsor::where('sponsorID', $sponsorID)->where('schoolID', $schoolID)->first();
                if (!$sponsor || !$sponsor->phone) {
                    return response()->json(['success' => false, 'message' => 'Sponsor not found or no phone'], 404);
                }

                $debtInfo = Payment::join('students', 'payments.studentID', '=', 'students.studentID')
                    ->where('payments.schoolID', $schoolID)
                    ->where('students.sponsor_id', $sponsorID)
                    ->where('payments.sponsor_balance', '>', 0)
                    ->selectRaw('COUNT(DISTINCT payments.studentID) as count, SUM(payments.sponsor_balance) as total')
                    ->first();

                $count = $debtInfo->count ?? 0;
                $totalDebt = $debtInfo->total ?? 0;
                if ($count <= 0) {
                    return response()->json(['success' => false, 'message' => 'Huyu mdhamini hana deni kwa sasa.']);
                }

                $formattedAmount = number_format($totalDebt);
                $message = "";
                if (!($school && $school->senderID)) {
                    $message .= "{$schoolName}: ";
                }
                $message .= "Unakumbushwa kulipa deni la wanafunzi {$count} kiasi cha Sh {$formattedAmount}. Tafadhali wahi kukamilisha malipo. Ahsante.";
                $phone = $sponsor->phone;

                $smsResult = $this->smsService->sendSms($phone, $message);
                if ($smsResult['success']) {
                    return response()->json(['success' => true]);
                }
                return response()->json(['success' => false, 'message' => $smsResult['message'] ?? 'SMS failed']);

            } else {
                // Parent case
                $payment = Payment::where('paymentID', $paymentID)
                    ->where('schoolID', $schoolID)
                    ->with(['student.parent', 'student.sponsor'])
                    ->first();

                if (!$payment || !$payment->student || !$payment->student->parent || !$payment->student->parent->phone) {
                    return response()->json(['success' => false, 'message' => 'Recipient not found'], 404);
                }

                $student = $payment->student;
                $phone = $student->parent->phone;
                $p = (float)($student->sponsorship_percentage ?? 0);
                $cn = $payment->control_number;

                if ($type === 'debt_reminder') {
                    $balanceStr = number_format($payment->parent_balance);
                    $message = "";
                    if (!($school && $school->senderID)) {
                        $message .= "{$schoolName}: ";
                    }
                    $message .= "Unakumbushwa kulipa deni la Ada kiasi cha Sh {$balanceStr}. Tafadhali wahi kulipa mapema. Ahsante.";
                } else {
                    // control_number SMS (keep existing logic but respect shorter templates if possible)
                    $totalAmount = number_format($payment->amount_required, 0);
                    $message = "";
                    if (!($school && $school->senderID)) {
                        $message .= "{$schoolName}: ";
                    }
                    if ($p > 0) {
                        $parentShare = number_format(max(0, $payment->amount_required - ($payment->amount_required * $p / 100)), 0);
                        $message .= "Mzazi wa {$student->first_name}, CN: {$cn}. Jumla Sh {$totalAmount}, sehemu yako Sh {$parentShare}. Ahsante.";
                    } else {
                        $message .= "Mzazi wa {$student->first_name}, Control Number: {$cn}. Kiasi: Sh {$totalAmount}. Ahsante.";
                    }
                }

                if (strlen($message) > 160) {
                    $message = substr($message, 0, 157) . '...';
                }

                $smsResult = $this->smsService->sendSms($phone, $message);
                if ($smsResult['success']) {
                    $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                    return response()->json(['success' => true]);
                }
                return response()->json(['success' => false, 'message' => $smsResult['message'] ?? 'SMS failed']);
            }

        } catch (\Exception $e) {
            Log::error('send_single_fee_sms error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Resend control number to specific parent
     */
    public function resend_control_number($paymentID)
    {
        set_time_limit(3600); // 60 minutes
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->with(['student.parent', 'school'])
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $student = $payment->student;
            $parent = $student ? $student->parent : null;
            $school = $payment->school;

            if (!$student || !$student->parent || !$student->parent->phone) {
                return response()->json(['success' => false, 'message' => 'Parent or phone number not found'], 404);
            }

            $studentName = ($student->first_name ?? '') . ' ' . ($student->last_name ?? '');
            $controlNumber = $payment->control_number;
            $totalAmount = number_format($payment->amount_required, 0);
            $requiredAmount = number_format($payment->required_fees_amount, 0);

            // Shortered Message for Control Number
            $message = "";
            if (!($school && $school->senderID)) {
                $message .= "{$school->school_name}: ";
            }
            $message .= "Mzazi wa {$studentName}, mwanafunzi amepangiwa Control Number: {$controlNumber}. Kiasi kuanza: sh {$requiredAmount}, Jumla: sh {$totalAmount}. Lipa kuepuka usumbufu.";

            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Control Number resent successfully.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to send SMS: ' . $smsResult['message']], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error resending control number: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send single debt reminder
     */
    public function send_single_debt_reminder(Request $request)
    {
        set_time_limit(3600);
        $paymentID = $request->input('paymentID');
        $schoolID = Session::get('schoolID');

        if (!$paymentID || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Missing data'], 400);
        }

        try {
            $school = School::find($schoolID);
            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->with(['student.parent'])
                ->first();

            if (!$payment || !$payment->student || !$payment->student->parent || !$payment->student->parent->phone) {
                return response()->json(['success' => false, 'message' => 'Student or parent phone not found'], 404);
            }

            $student = $payment->student;
            $parent = $student->parent;
            $studentName = ($student->first_name ?? '') . ' ' . ($student->last_name ?? '');
            $balance = number_format($payment->balance, 0);
            $controlNumber = $payment->control_number;

            // Shortered Message for Debt Reminder
            $message = "";
            if (!($school && $school->senderID)) {
                $message .= "{$school->school_name}: ";
            }
            $message .= "Mzazi wa {$studentName} unakumbushwa kulipa deni, sh {$balance}, control number {$controlNumber}. Lipa kuepuka usumbufu.";

            $smsResult = $this->smsService->sendSms($parent->phone, $message);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Debt reminder sent successfully.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to send SMS: ' . $smsResult['message']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error sending single debt reminder: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update payment status (when payment is received from bank)
     */
    public function update_payment_status(Request $request, $paymentID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount_paid' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::where('paymentID', $paymentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Create a payment record
            $paymentRecord = PaymentRecord::create([
                'paymentID' => $payment->paymentID,
                'paid_amount' => $request->amount_paid,
                'payment_date' => now(),
                'payment_source' => 'Bank/System',
                'reference_number' => $request->payment_reference,
                'notes' => $request->notes,
                'is_verified' => true,
                'verified_at' => now(),
            ]);

            // Allocate payment across fees
            $this->allocatePayment($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment received and allocated successfully.',
                'payment' => $payment->fresh(['student'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export Payment Invoice PDF
     */
    public function exportPaymentInvoicePDF($studentID)
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID || !in_array($userType, ['Admin', 'Teacher'])) {
            return redirect()->back()->with('error', 'Access denied');
        }

        try {
            // Get student with relationships
            $student = Student::with(['parent', 'subclass.class', 'payments.fee.installments', 'payments.fee.otherFeeDetails'])
                ->where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Get school information
            $school = School::find($schoolID);
            if (!$school) {
                return response()->json(['error' => 'School not found'], 404);
            }

            // Get active academic year
            $activeAcademicYear = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('year', 'desc')
                ->first();

            $currentYear = $activeAcademicYear ? $activeAcademicYear->year : date('Y');
            $academicYearID = $activeAcademicYear ? $activeAcademicYear->academic_yearID : null;

            // Get payments for active academic year - filter out empty/null payments
            $payments = $student->payments()
                ->where(function($query) use ($academicYearID, $currentYear) {
                    if ($academicYearID) {
                        $query->where('academic_yearID', $academicYearID);
                    } else {
                        $query->whereYear('created_at', $currentYear);
                    }
                })
                ->whereNotNull('control_number')
                ->where('control_number', '!=', '')
                ->with(['fee.installments', 'fee.otherFeeDetails'])
                ->get();

            // Get previous year debt for this student (only for active students)
            $previousYearDebt = ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
            if ($student->status === 'Active') {
                $previousYearDebt = $this->getPreviousYearBalance($student->studentID, $schoolID);
            }

            // Calculate totals
            $payment = $payments->first(); // Get the first (unified) payment record
            $totalRequired = $payment ? $payment->amount_required : 0;
            $totalPaid = $payment ? $payment->amount_paid : 0;
            $totalBalance = $payment ? $payment->balance : 0;
            $requiredPaid = $payment ? $payment->required_fees_paid : 0;
            $requiredAmount = $payment ? $payment->required_fees_amount : 0;

            // Get fee breakdown
            $feeBreakdown = $payment ? $payment->fee_payments()->orderBy('display_order')->get() : collect();

            // School logo path
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;

            // Prepare data for PDF
            $data = [
                'student' => $student,
                'school' => $school,
                'payment' => $payment,
                'feeBreakdown' => $feeBreakdown,
                'totalRequired' => $totalRequired,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance,
                'requiredAmount' => $requiredAmount,
                'requiredPaid' => $requiredPaid,
                'previousYearDebt' => $previousYearDebt,
                'schoolLogo' => $schoolLogo,
                'year' => $currentYear,
            ];

            // Generate PDF - try multiple methods for compatibility
            try {
                // Method 1: Try using the facade
                $pdf = PDF::loadView('Admin.pdf.payment_invoice', $data);
                $pdf->setPaper('A4', 'portrait');

                $filename = 'Payment_Invoice_' . str_replace(' ', '_', $student->admission_number) . '_' . $currentYear . '.pdf';

                return $pdf->download($filename);
            } catch (\Exception $facadeError) {
                // Method 2: Fallback to direct Dompdf class
                try {
                    $dompdf = new \Dompdf\Dompdf();
                    $html = view('Admin.pdf.payment_invoice', $data)->render();

                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $filename = 'Payment_Invoice_' . str_replace(' ', '_', $student->admission_number) . '_' . $currentYear . '.pdf';

                    return response()->streamDownload(function() use ($dompdf) {
                        echo $dompdf->output();
                    }, $filename, [
                        'Content-Type' => 'application/pdf',
                    ]);
                } catch (\Exception $directError) {
                    throw new \Exception('PDF generation failed: ' . $directError->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('Error generating payment invoice PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Record payment (Cash or Bank)
     */
    public function record_payment(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $rules = [
                'paymentID' => 'required|exists:payments,paymentID',
                'fee_id' => 'nullable', // Use 'any' or a specific ID
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'payment_by' => 'required|in:Parent,Sponsor',
                'notes' => 'nullable|string',
            ];

            if ($request->payment_source === 'Bank') {
                $rules['reference_number'] = 'required|string';
                $rules['bank_name'] = 'required|string|max:200';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $request->paymentID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment record not found.'], 404);
            }

            DB::beginTransaction();

            $paymentRecord = PaymentRecord::create([
                'paymentID' => $payment->paymentID,
                'feeID' => ($request->fee_id && $request->fee_id !== 'any') ? $request->fee_id : null,
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'payment_by' => $request->payment_by ?? 'Parent',
                'reference_number' => $request->reference_number,
                'bank_name' => $request->bank_name,
                'notes' => $request->notes,
                'is_verified' => true, // Assuming auto-verified on manual entry
            ]);

            // Allocate payment across fees
            $this->allocatePayment($payment);

            DB::commit();

            // --- SYNC TO INCOME TABLE ---
            // Only sync if payment is verified (for now, assume all are verified on record)
            // Avoid duplicate: Only add for this payment record (not for all history)
            try {
                $student = $payment->student;
                $payerName = $student ? ($student->first_name . ' ' . $student->last_name) : 'Student';
                \App\Models\Income::create([
                    'schoolID' => $payment->schoolID,
                    'receipt_number' => 'AUTO-FEE-' . $payment->paymentID . '-' . now()->format('YmdHis'),
                    'date' => $request->payment_date,
                    'income_category' => 'Tuition & Fees',
                    'description' => 'Student fee payment (auto-sync)',
                    'amount' => $request->paid_amount,
                    'payment_method' => $request->payment_source,
                    'payer_name' => $payerName,
                    'entered_by' => auth()->id() ?? 1,
                ]);
            } catch (\Exception $ex) {
                \Log::error('Failed to sync payment to Income: ' . $ex->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded and allocated.',
                'payment_record' => $paymentRecord
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Allocate all paid amounts for a payment across student fees based on priority.
     */
    private function allocatePayment($payment)
    {
        // 1. Fetch all verified records for this payment
        $allRecords = PaymentRecord::where('paymentID', $payment->paymentID)
            ->where('is_verified', true)
            ->get();

        $totalPaid = $allRecords->sum('paid_amount');
        $sponsorPaid = $allRecords->where('payment_by', 'Sponsor')->sum('paid_amount');
        $parentPaid = $allRecords->where('payment_by', 'Parent')->sum('paid_amount');

        // 2. Fetch all student fee lines with priority info
        $fee_payments = StudentFeePayment::where('paymentID', $payment->paymentID)
            ->join('fees', 'student_fee_payments.feeID', '=', 'fees.feeID')
            ->select('student_fee_payments.*', 'fees.must_start_pay', 'fees.display_order', 'fees.fee_name')
            ->orderBy('fees.must_start_pay', 'desc')
            ->orderBy('fees.display_order', 'asc')
            ->get();

        // 3. Reset internal balances for fresh allocation
        foreach ($fee_payments as $fp) {
            $fp->amount_paid = 0;
            $fp->balance = $fp->fee_total_amount;
        }

        // 4. Phase 1: Targeted Payments (Specific Fee selected)
        $targetedRecords = $allRecords->whereNotNull('feeID');
        foreach ($targetedRecords as $record) {
            $target = $fee_payments->where('feeID', $record->feeID)->first();
            if ($target) {
                $target->amount_paid += $record->paid_amount;
                $target->balance = max(0, $target->fee_total_amount - $target->amount_paid);
            }
        }

        // 5. Phase 2: General Payments (Pay to Any Fee)
        $generalAmount = $allRecords->whereNull('feeID')->sum('paid_amount');
        $remainingGeneral = $generalAmount;

        foreach ($fee_payments as $fp) {
            if ($remainingGeneral <= 0) break;

            $currentFeeBalance = $fp->fee_total_amount - $fp->amount_paid;
            if ($currentFeeBalance <= 0) continue;

            $allocation = min($remainingGeneral, $currentFeeBalance);
            $fp->amount_paid += $allocation;
            $fp->balance = max(0, $fp->fee_total_amount - $fp->amount_paid);
            $remainingGeneral -= $allocation;
        }

        // 6. Persist changes to student_fee_payments
        foreach ($fee_payments as $fp) {
            $fp->save();
        }

        // 7. Recalculate summary metrics
        $requiredPaid = $fee_payments->where('must_start_pay', 1)->sum('amount_paid');
        $totalBalance = max(0, $payment->amount_required - $totalPaid);
        
        $paymentStatus = 'Pending';
        if ($totalBalance <= 0) {
            $paymentStatus = $totalPaid > $payment->amount_required ? 'Overpaid' : 'Paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'Incomplete Payment';
        }

        $canStartSchool = ($requiredPaid >= $payment->required_fees_amount);

        $payment->update([
            'amount_paid' => $totalPaid,
            'balance' => $totalBalance,
            'required_fees_paid' => $requiredPaid,
            'can_start_school' => $canStartSchool,
            'payment_status' => $paymentStatus,
            'sponsor_amount_paid' => $sponsorPaid,
            'sponsor_balance' => max(0, $payment->sponsor_amount_required - $sponsorPaid),
            'parent_amount_paid' => $parentPaid,
            'parent_balance' => max(0, $payment->parent_amount_required - $parentPaid),
        ]);
    }

    /**
     * Get payment records for a specific payment
     */
    public function get_payment_records(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $paymentID = $request->input('paymentID');

            if (!$paymentID) {
                return response()->json(['success' => false, 'message' => 'Payment ID is required'], 400);
            }

            // Verify payment belongs to this school
            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $paymentID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Get payment records
            $records = PaymentRecord::where('paymentID', $paymentID)
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $recordsData = $records->map(function($record) {
                return [
                    'recordID' => $record->recordID,
                    'paid_amount' => $record->paid_amount,
                    'reference_number' => $record->reference_number,
                    'payment_date' => $record->payment_date ? $record->payment_date->format('Y-m-d') : null,
                    'payment_source' => $record->payment_source,
                    'payment_by' => $record->payment_by,
                    'bank_name' => $record->bank_name,
                    'notes' => $record->notes,
                    'is_verified' => $record->is_verified,
                ];
            });

            return response()->json([
                'success' => true,
                'records' => $recordsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading payment records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment record
     */
    public function update_payment_record(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $rules = [
                'recordID' => 'required|exists:payment_records,recordID',
                'paymentID' => 'required|exists:payments,paymentID',
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_source' => 'required|in:Cash,Bank',
                'notes' => 'nullable|string',
            ];

            if ($request->payment_source === 'Bank') {
                $rules['reference_number'] = 'required|string';
                $rules['bank_name'] = 'required|string|max:200';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
            }

            $payment = Payment::where('schoolID', $schoolID)->where('paymentID', $request->paymentID)->first();
            $paymentRecord = PaymentRecord::where('recordID', $request->recordID)->where('paymentID', $request->paymentID)->first();

            if (!$payment || !$paymentRecord) {
                return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
            }

            DB::beginTransaction();

            $paymentRecord->update([
                'paid_amount' => $request->paid_amount,
                'payment_date' => $request->payment_date,
                'payment_source' => $request->payment_source,
                'reference_number' => $request->reference_number,
                'bank_name' => $request->bank_name,
                'notes' => $request->notes,
            ]);

            $this->allocatePayment($payment);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Record updated.', 'payment_record' => $paymentRecord]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating record: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete payment record
     */
    public function delete_payment_record(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $recordID = $request->input('recordID');
            $paymentID = $request->input('paymentID');

            if (!$recordID || !$paymentID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record ID and Payment ID are required'
                ], 400);
            }

            // Verify payment belongs to this school
            $payment = Payment::where('schoolID', $schoolID)
                ->where('paymentID', $paymentID)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            // Check if payment record belongs to this payment
            $paymentRecord = PaymentRecord::where('recordID', $recordID)
                ->where('paymentID', $paymentID)
                ->first();

            if (!$paymentRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found.'
                ], 404);
            }

            DB::beginTransaction();

            $paymentRecord->delete();

            $this->allocatePayment($payment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment record deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export filtered payments as PDF
     */
    /**
     * Export Fees Structure PDF
     */
    public function exportFeesStructurePdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        try {
            $classID = $request->query('classID');
            $year = date('Y');

            $school = School::find($schoolID);
            
            $query = ClassModel::where('schoolID', $schoolID)
                ->with(['fees' => function($q) {
                    $q->where('status', 'Active')->orderBy('display_order');
                }, 'fees.installments', 'fees.otherFeeDetails']);

            if ($classID) {
                $query->where('classID', $classID);
            }

            $classes = $query->get();

            $data = [
                'school' => $school,
                'classes' => $classes,
                'year' => $year,
                'title' => 'OFFICIAL FEES STRUCTURE - ' . $year
            ];

            // Method 1: Try using the facade
            try {
                $pdf = PDF::loadView('Admin.pdf.fees_structure_pdf', $data);
                $pdf->setPaper('A4', 'portrait');
                $filename = 'Fees_Structure_' . $year . '.pdf';
                return $pdf->download($filename);
            } catch (\Exception $e) {
                // Fallback to direct Dompdf if facade fails
                $dompdf = new \Dompdf\Dompdf();
                $html = view('Admin.pdf.fees_structure_pdf', $data)->render();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return response()->streamDownload(function() use ($dompdf) {
                    echo $dompdf->output();
                }, 'Fees_Structure_' . $year . '.pdf', ['Content-Type' => 'application/pdf']);
            }

        } catch (\Exception $e) {
            Log::error('Error exporting fees structure PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

    public function exportFilteredPaymentsPDF(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $userType = Session::get('user_type');

        if (!$schoolID || !in_array($userType, ['Admin', 'Teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            // Get filter parameters (same as get_payments_ajax)
            $classID = $request->input('class_id', '');
            $subclassID = $request->input('subclass_id', '');
            $enrollmentStatus = $request->input(
                'enrollment_status',
                $request->input('student_status', 'Active')
            );
            if ($enrollmentStatus === null || $enrollmentStatus === '') {
                $enrollmentStatus = 'all';
            }
            $paymentStatus = $request->input('payment_status', '');
            $searchStudentName = $request->input('search_student_name', '');

            $resolved = $this->resolvePaymentsAcademicYear((int) $schoolID, $request);
            $academicYearID = $resolved['academicYearID'];
            $year = $resolved['year'];

            $studentIDs = $this->paymentCohortStudentIDs(
                (int) $schoolID,
                $academicYearID,
                (string) $enrollmentStatus,
                $classID !== '' ? $classID : null,
                $subclassID !== '' ? $subclassID : null
            );

            if (empty($studentIDs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No enrollments found for the selected academic year / enrollment status.',
                    'error_code' => 'no_enrollments',
                ], 422);
            }

            $enrollmentMeta = $this->paymentCohortEnrollmentMeta(
                (int) $schoolID,
                $academicYearID,
                $studentIDs
            );

            $query = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $studentIDs)
                ->with(['parent', 'subclass.class', 'payments' => function($q) use ($academicYearID, $year) {
                    if ($academicYearID) {
                        $q->where('academic_yearID', $academicYearID);
                    } else {
                        $q->whereYear('created_at', $year);
                    }
                }, 'payments.fee_payments', 'payments.academicYear']);

            // Filter by student name search
            if (!empty($searchStudentName)) {
                $query->where(function($q) use ($searchStudentName) {
                    $q->where('first_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('last_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhere('middle_name', 'like', '%' . $searchStudentName . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%'])
                      ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $searchStudentName . '%']);
                });
            }

            $students = $query->orderBy('first_name')->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found matching the filters'
                ], 404);
            }

            // Get school info
            $school = School::find($schoolID);
            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'School not found'
                ], 404);
            }

            // Generate PDF for all filtered students
            $pdfData = [];

            foreach ($students as $student) {
                $payment = $student->payments->first(); // Unified payment

                if (!$payment && !empty($paymentStatus)) {
                    if ($paymentStatus !== 'Pending') continue;
                }
                if ($payment && !empty($paymentStatus)) {
                    $status = $payment->payment_status;
                    if ($paymentStatus === 'Incomplete' || $paymentStatus === 'Partial' || $paymentStatus === 'Incomplete Payment') {
                        if ($status !== 'Incomplete Payment' && $status !== 'Partial') continue;
                    } elseif ($status !== $paymentStatus) {
                        continue;
                    }
                }

                // Calculate totals
                $totalRequired = $payment ? $payment->amount_required : 0;
                $totalPaid = $payment ? $payment->amount_paid : 0;
                $totalBalance = $payment ? $payment->balance : 0;
                $requiredPaid = $payment ? $payment->required_fees_paid : 0;
                $requiredAmount = $payment ? $payment->required_fees_amount : 0;
                $canStartSchool = $payment ? $payment->can_start_school : false;

                // Get fee breakdown
                $feeBreakdown = $payment ? $payment->fee_payments()->orderBy('display_order')->get() : collect();

                $enrMeta = $enrollmentMeta[(int) $student->studentID] ?? null;
                $mainClass = $enrMeta['class_name'] ?? ($student->subclass->class->class_name ?? '');
                $subclassName = $enrMeta['subclass_name'] ?? ($student->subclass->subclass_name ?? '');
                $studentClass = trim(($mainClass ? $mainClass . ' ' : '') . $subclassName) ?: 'N/A';
                $enrollmentStatusLabel = $enrMeta['enrollment_status'] ?? $student->status;

                $pdfData[] = [
                    'student' => [
                        'studentID' => $student->studentID,
                        'first_name' => $student->first_name,
                        'middle_name' => $student->middle_name,
                        'last_name' => $student->last_name,
                        'admission_number' => $student->admission_number,
                        'class' => $studentClass,
                        'status' => $enrollmentStatusLabel,
                        'enrollment_status' => $enrollmentStatusLabel,
                        'photo' => $student->photo ? asset('userImages/' . $student->photo) : null,
                        'sponsorship_percentage' => $student->sponsorship_percentage,
                        'sponsor_name' => $student->sponsor ? $student->sponsor->sponsor_name : null,
                    ],
                    'payment' => $payment,
                    'feeBreakdown' => $feeBreakdown,
                    'totalRequired' => $totalRequired,
                    'totalPaid' => $totalPaid,
                    'totalBalance' => $totalBalance,
                    'requiredAmount' => $requiredAmount,
                    'requiredPaid' => $requiredPaid,
                    'canStartSchool' => $canStartSchool,
                ];
            }

            if (empty($pdfData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payment data found matching the filters'
                ], 404);
            }

            // Generate combined PDF using existing template
            $schoolLogo = $school->school_logo ? public_path($school->school_logo) : null;
            $currentYear = $year;

            $data = [
                'school' => $school,
                'students' => $pdfData,
                'schoolLogo' => $schoolLogo,
                'year' => $currentYear,
                'filters' => [
                    'class' => $classID ? (ClassModel::find($classID)->class_name ?? '') : '',
                    'subclass' => $subclassID ? (Subclass::find($subclassID)->subclass_name ?? '') : '',
                    'status' => $enrollmentStatus,
                    'payment_status' => $paymentStatus,
                ]
            ];

            // Generate PDF - create a simple list view for filtered payments
            try {
                // Use a simple view for filtered payments list
                $pdf = PDF::loadView('Admin.pdf.filtered_payments_list', $data);
                $pdf->setPaper('A4', 'portrait');

                $filename = 'Filtered_Payments_' . $year . '_' . date('YmdHis') . '.pdf';

                return $pdf->download($filename);
            } catch (\Exception $e) {
                // Fallback: generate individual PDFs
                Log::error('Error generating filtered payments PDF: ' . $e->getMessage());

                // For now, return first student's PDF as fallback
                if (!empty($pdfData)) {
                    $firstStudent = $pdfData[0];
                    return $this->exportPaymentInvoicePDF($firstStudent['student']['studentID']);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Error generating PDF: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error exporting filtered payments PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error exporting PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Verify a payment record
     */
    public function verify_payment(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $recordID = $request->input('recordID');
            $record = PaymentRecord::whereHas('payment', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->where('recordID', $recordID)->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }

            $record->update([
                'is_verified' => true,
                'verified_by' => Session::get('userID'),
                'verified_at' => now(),
            ]);

            // Recalculate payment totals after verification
            $this->allocatePayment($record->payment);

            return response()->json(['success' => true, 'message' => 'Payment verified!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unverify a payment record
     */
    public function unverify_payment(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $recordID = $request->input('recordID');
            $record = PaymentRecord::whereHas('payment', function($q) use ($schoolID) {
                $q->where('schoolID', $schoolID);
            })->where('recordID', $recordID)->first();

            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Record not found'], 404);
            }

            $record->update([
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            // Recalculate payment totals after unverification
            $this->allocatePayment($record->payment);

            return response()->json(['success' => true, 'message' => 'Payment verification deleted!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get conditions for a class
     */
    public function get_class_conditions($classID)
    {
        $schoolID = (int) Session::get('schoolID');
        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, (int) $classID);
        [, , $enrolledIds] = $this->activeEnrollmentStudentsForClass($schoolID, (int) $classID);

        $conditions = FeeCondition::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereHas('fee', function($q) use ($classID) {
                $q->where('classID', $classID);
            })
            ->with(['fee', 'studentConditions' => function($q) use ($academicYearID, $enrolledIds) {
                $q->select('conditionID', 'studentID', 'academic_yearID', 'status')
                    ->where('status', 'Active');
                if ($academicYearID) {
                    $q->where('academic_yearID', $academicYearID);
                }
                if (!empty($enrolledIds)) {
                    $q->whereIn('studentID', $enrolledIds);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }])
            ->get();
            
        $conditions->each(function($cond) {
            $cond->assigned_student_ids = $cond->studentConditions->pluck('studentID');
            unset($cond->studentConditions);
        });
            
        return response()->json([
            'success' => true,
            'conditions' => $conditions,
            'academic_year_id' => $academicYearID,
        ]);
    }

    /**
     * Get students for condition assignment / fee agreements (Active enrollments)
     */
    public function get_class_students($classID)
    {
        $schoolID = (int) Session::get('schoolID');
        [$gate, $academicYearID, $studentIds] = $this->activeEnrollmentStudentsForClass($schoolID, (int) $classID);
        if ($gate && empty($studentIds)) {
            return $gate;
        }

        $enrollmentMeta = [];
        foreach (app(StudentEnrollmentService::class)->enrollmentsInClass((int) $classID, $schoolID, 'Active') as $enr) {
            $enrollmentMeta[(int) $enr->studentID] = [
                'enrollmentID' => (int) $enr->enrollmentID,
                'enrollment_status' => $enr->status,
                'subclass_name' => $enr->subclass->subclass_name ?? null,
            ];
        }

        $students = Student::where('schoolID', $schoolID)
            ->whereIn('studentID', $studentIds)
            ->select('studentID', 'first_name', 'middle_name', 'last_name', 'admission_number', 'sponsor_id', 'sponsorship_percentage')
            ->orderBy('first_name')
            ->get()
            ->map(function ($student) use ($enrollmentMeta) {
                $meta = $enrollmentMeta[(int) $student->studentID] ?? null;
                $student->enrollmentID = $meta['enrollmentID'] ?? null;
                $student->enrollment_status = $meta['enrollment_status'] ?? 'Active';
                $student->subclass_name = $meta['subclass_name'] ?? null;
                $student->can_assign = ($meta['enrollment_status'] ?? '') === 'Active';
                return $student;
            });
            
        return response()->json([
            'success' => true,
            'students' => $students,
            'academic_year_id' => $academicYearID,
            'active_enrollment_count' => count($studentIds),
        ]);
    }

    public function get_class_fees($classID)
    {
        $schoolID = Session::get('schoolID');
        $fees = Fee::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('status', 'Active')
            ->orderBy('display_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'fees' => $fees
        ]);
    }

    public function get_existing_agreements($classID)
    {
        $schoolID = (int) Session::get('schoolID');
        [$gate, $academicYearID, $studentIds] = $this->activeEnrollmentStudentsForClass($schoolID, (int) $classID);
        if ($gate && empty($studentIds)) {
            return $gate;
        }

        if (!$academicYearID) {
            $academicYearID = $this->resolveFeesAcademicYearID($schoolID, (int) $classID);
        }

        $agreements = FeeAgreement::where('schoolID', $schoolID)
            ->where('academic_yearID', $academicYearID)
            ->whereIn('studentID', $studentIds)
            ->where('status', 'Active')
            ->get();

        return response()->json([
            'success' => true,
            'agreements' => $agreements,
            'academic_year_id' => $academicYearID,
        ]);
    }

    /**
     * Store custom fee agreements
     */
    public function store_fee_agreements(Request $request)
    {
        $schoolID = (int) ($request->schoolID ?: Session::get('schoolID'));
        $agreements = $request->agreements;

        if (empty($agreements)) {
            return response()->json(['success' => false, 'message' => 'No agreements provided'], 400);
        }

        $classID = $request->input('classID');
        if (!$classID && !empty($agreements[0]['studentID'])) {
            $enrollment = app(StudentEnrollmentService::class)
                ->currentEnrollment((int) $agreements[0]['studentID'], $schoolID);
            $classID = $enrollment->classID ?? null;
        }

        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, $classID ? (int) $classID : null);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header before saving fee agreements.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        $enrollmentService = app(StudentEnrollmentService::class);

        DB::beginTransaction();
        try {
            $studentIDsAffected = [];

            foreach ($agreements as $ag) {
                $studentID = (int) $ag['studentID'];
                $enrollment = $enrollmentService->currentEnrollment($studentID, $schoolID);
                if (!$enrollment || $enrollment->status !== 'Active') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Active enrollments for the selected academic year can receive fee agreements.',
                        'error_code' => 'enrollment_not_active',
                    ], 422);
                }

                FeeAgreement::updateOrCreate(
                    [
                        'schoolID' => $schoolID,
                        'studentID' => $studentID,
                        'feeID' => $ag['feeID'],
                        'academic_yearID' => $academicYearID,
                    ],
                    [
                        'agreed_amount' => $ag['amount'],
                        'status' => 'Active'
                    ]
                );

                $studentIDsAffected[] = $studentID;
            }

            DB::commit();

            // Regenerate bills for affected students
            $uniqueStudentIDs = array_unique($studentIDsAffected);
            foreach ($uniqueStudentIDs as $studentID) {
                $student = Student::find($studentID);
                if ($student) {
                    $this->generateStudentBill($student, $academicYearID, $schoolID);
                }
            }

            return response()->json([
                'success' => true, 
                'message' => count($agreements) . ' Agreements saved and bills updated.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Failed to save agreements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign students to a Sponsor
     */
    public function assign_sponsor_students(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        
        $sponsorID = $request->input('sponsorID');
        $classID = $request->input('classID');
        $assignments = $request->input('assignments', []); // array of {studentID, percentage}

        if (!$sponsorID || !$classID) {
            return response()->json(['success' => false, 'message' => 'Missing sponsor or class context'], 400);
        }

        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, (int) $classID);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header before assigning sponsors.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        [, , $enrolledIds] = $this->activeEnrollmentStudentsForClass($schoolID, (int) $classID);
        if (empty($enrolledIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected academic year has no Active enrollments for this class.',
                'error_code' => 'no_active_enrollments',
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // 1. Unassign students in this class who were previously assigned to this sponsor but are no longer in the list (unchecked)
            $keepStudentIDs = array_column($assignments, 'studentID');
            
            $studentsToUnassign = Student::where('schoolID', $schoolID)
                ->where('sponsor_id', $sponsorID)
                ->whereIn('studentID', $enrolledIds)
                ->whereNotIn('studentID', $keepStudentIDs)
                ->get();

            foreach ($studentsToUnassign as $student) {
                // Remove sponsor info
                $student->update([
                    'sponsor_id' => null,
                    'sponsorship_percentage' => 0
                ]);
                $this->generateStudentBill($student, $academicYearID, $schoolID);
            }

            // 2. Assign/update the provided students
            $enrollmentService = app(StudentEnrollmentService::class);
            foreach ($assignments as $ag) {
                $studentID = (int) $ag['studentID'];
                if (!in_array($studentID, $enrolledIds, true)) {
                    continue;
                }
                $enrollment = $enrollmentService->currentEnrollment($studentID, $schoolID);
                if (!$enrollment || $enrollment->status !== 'Active') {
                    continue;
                }
                $student = Student::find($studentID);
                if ($student) {
                    $student->update([
                        'sponsor_id' => $sponsorID,
                        'sponsorship_percentage' => $ag['percentage'] ?? 0
                    ]);
                    $this->generateStudentBill($student, $academicYearID, $schoolID);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Sponsorship assignments updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assign Sponsor error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sponsorship assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign students to fee conditions
     */
    public function assign_students_to_conditions(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $classID = $request->input('classID');
        $studentIDs = $request->input('studentIDs', []);
        $conditionIDs = $request->input('conditionIDs', []);

        if (empty($studentIDs) || empty($conditionIDs)) {
            return response()->json(['success' => false, 'message' => 'Select at least one student and one condition'], 422);
        }

        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, $classID ? (int) $classID : null);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header before assigning fee discounts.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        $enrollmentService = app(StudentEnrollmentService::class);

        try {
            DB::beginTransaction();
            
            $affectedStudentIDs = [];

            foreach ($conditionIDs as $conditionID) {
                $condition = FeeCondition::find($conditionID);
                if (!$condition) continue;

                foreach ($studentIDs as $studentID) {
                    $enrollment = $enrollmentService->currentEnrollment((int) $studentID, $schoolID);
                    if (!$enrollment || $enrollment->status !== 'Active') {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Active enrollments for the selected academic year can receive fee discounts.',
                            'error_code' => 'enrollment_not_active',
                        ], 422);
                    }

                    // Check if student already has this EXACT condition active for this fee + year
                    $alreadyAssigned = StudentFeeCondition::where([
                        'studentID' => $studentID,
                        'conditionID' => $conditionID,
                        'feeID' => $condition->feeID,
                        'academic_yearID' => $academicYearID,
                        'status' => 'Active'
                    ])->exists();
                    
                    if (!$alreadyAssigned) {
                        StudentFeeCondition::updateOrCreate(
                            [
                                'schoolID' => $schoolID,
                                'studentID' => $studentID,
                                'feeID' => $condition->feeID,
                                'academic_yearID' => $academicYearID,
                            ],
                            [
                                'conditionID' => $conditionID,
                                'status' => 'Active'
                            ]
                        );
                        $affectedStudentIDs[] = $studentID;
                    }
                }
            }

            // Only regenerate bills for students whose condition was actually added or changed
            $uniqueAffected = array_unique($affectedStudentIDs);
            if (!empty($uniqueAffected)) {
                $studentsToUpdate = Student::whereIn('studentID', $uniqueAffected)->get();
                foreach ($studentsToUpdate as $student) {
                    $this->generateStudentBill($student, $academicYearID, $schoolID);
                }
            }

            DB::commit();
            
            $msg = count($uniqueAffected) > 0 
                ? 'Assignments saved and bills updated for ' . count($uniqueAffected) . ' students.' 
                : 'Selected students were already assigned. No changes needed.';

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
