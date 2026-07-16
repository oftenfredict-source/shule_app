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
use App\Models\FeeDeadline;
use App\Models\FeeCondition;
use App\Models\StudentFeeCondition;
use App\Models\FeeAgreement;
use App\Models\FeeBreakdown;
use App\Models\OtherFeeDetail;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\ParentModel;
use App\Models\StudentFeePayment;
use App\Models\StudentEnrollment;
use App\Models\Bank;
use App\Models\SchoolBankAccount;
use App\Models\AcademicYear;
use App\Services\SmsService;
use App\Services\StudentEnrollmentService;
use App\Services\SelectedAcademicYearService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            ->with(['class', 'otherFeeDetails', 'bankAccount.bank', 'breakdowns', 'feeConditions'])
            ->orderBy('classID')
            ->orderBy('display_order')
            ->orderBy('fee_name')
            ->get();

        // Group fees by class for widgets
        $feesByClass = $fees->groupBy('classID');
        $singleFeesByClass = $fees->filter(fn ($f) => ($f->account_mode ?? 'single') !== 'multi')->groupBy('classID');
        $multiFeesByClass = $fees->filter(fn ($f) => ($f->account_mode ?? 'single') === 'multi')->groupBy('classID');
        $isAdmin = $userType === 'Admin';
        $approvedBankAccounts = SchoolBankAccount::with('bank')
            ->where('schoolID', $schoolID)
            ->approved()
            ->orderBy('account_number')
            ->get();

        return view('Admin.manageFees', compact(
            'classes',
            'fees',
            'feesByClass',
            'singleFeesByClass',
            'multiFeesByClass',
            'isAdmin',
            'approvedBankAccounts'
        ));
    }

    public function store_fee(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $accountMode = $request->input('account_mode', 'single') === 'multi' ? 'multi' : 'single';
        $editFeeID = $request->input('edit_feeID');

        $rules = [
            'classID' => 'required|exists:classes,classID',
            'bank_account_id' => 'nullable|integer',
            'duration' => 'required|in:Year,Month,Term,Semester,One-time',
            'fees' => 'required|array|min:1',
            'fees.*.name' => 'required|string|max:200',
            'fees.*.amount' => 'nullable|numeric|min:0',
            'fees.*.description' => 'nullable|string',
            'fees.*.must_start_pay' => 'nullable|boolean',
            'fees.*.deadline_amount' => 'nullable|numeric|min:0',
            'fees.*.deadline_date' => 'nullable|date',
            'fees.*.bank_account_id' => 'nullable|integer',
            'fees.*.has_breakdown' => 'nullable',
            'fees.*.breakdowns' => 'nullable|array',
            'fees.*.conditions' => 'nullable|array',
            'fees.*.conditions.*.name' => 'required|string|max:200',
            'fees.*.conditions.*.amount' => 'required|numeric|min:0',
            'fees.*.conditions.*.type' => 'required|in:Fixed,Percentage',
            'fees.*.conditions.*.description' => 'nullable|string|max:500',
            'allow_installments' => 'nullable|boolean',
            'default_installment_type' => 'nullable|in:Semester,Month,Two Months,Term,Quarter,One-time',
            'number_of_installments' => 'nullable|integer|min:1|max:12',
            'allow_partial_payment' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $classID = (int) $request->classID;
        $classBelongsToSchool = ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->exists();
        if (!$classBelongsToSchool) {
            return response()->json([
                'success' => false,
                'message' => 'The selected class does not belong to this school.',
            ], 422);
        }

        $oppositeModeExists = Fee::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->when($accountMode === 'multi', function ($query) {
                $query->where(function ($q) {
                    $q->where('account_mode', 'single')->orWhereNull('account_mode');
                });
            }, function ($query) {
                $query->where('account_mode', 'multi');
            })
            ->exists();

        if ($oppositeModeExists) {
            $existingMode = $accountMode === 'multi' ? 'Single Account Fee' : 'Multi Account Fee';
            return response()->json([
                'success' => false,
                'message' => "This class is already defined under {$existingMode}. A class cannot use both fee account modes.",
            ], 422);
        }

        // Single account mode: one approved account (shared by all fees of the class,
        // and the same account may be reused by other classes).
        $singleBankAccountId = null;
        if ($accountMode === 'single' && $request->filled('bank_account_id')) {
            $singleAccount = SchoolBankAccount::where('schoolID', $schoolID)
                ->where('accountID', (int) $request->bank_account_id)
                ->approved()
                ->first();
            if (!$singleAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected account number is not an approved account for this school.',
                ], 422);
            }
            $singleBankAccountId = $singleAccount->accountID;
        }
        if ($accountMode === 'single' && $singleBankAccountId === null) {
            // Inherit the account already used by this class's single fees, if any.
            $singleBankAccountId = Fee::where('schoolID', $schoolID)
                ->where('classID', $classID)
                ->where(function ($q) {
                    $q->where('account_mode', 'single')->orWhereNull('account_mode');
                })
                ->whereNotNull('bank_account_id')
                ->value('bank_account_id');
        }

        try {
            DB::beginTransaction();

            // Edit path for multi-account fee (single fee row)
            if ($editFeeID && $accountMode === 'multi') {
                $fee = Fee::where('schoolID', $schoolID)->where('feeID', $editFeeID)->first();
                if (!$fee) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Fee not found'], 404);
                }
                $feeData = collect($request->fees)->first();
                $payload = $this->normalizeMultiFeePayload($feeData, $schoolID);
                if (isset($payload['error'])) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $payload['error']], 422);
                }
                $fee->update([
                    'classID' => $request->classID,
                    'fee_name' => $payload['fee_name'],
                    'amount' => $payload['amount'],
                    'duration' => $request->duration,
                    'description' => $payload['description'],
                    'account_mode' => 'multi',
                    'bank_account_id' => $payload['bank_account_id'],
                    'has_breakdown' => $payload['has_breakdown'],
                ]);
                FeeBreakdown::where('feeID', $fee->feeID)->delete();
                foreach ($payload['breakdowns'] as $i => $bd) {
                    FeeBreakdown::create([
                        'feeID' => $fee->feeID,
                        'item_name' => $bd['item_name'],
                        'amount' => $bd['amount'],
                        'display_order' => $i,
                    ]);
                }
                $this->syncFeeConditions(
                    $fee,
                    is_array($feeData['conditions'] ?? null) ? $feeData['conditions'] : [],
                    $schoolID
                );
                $this->generateBillsForClass($request->classID, $schoolID);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Multi account fee updated successfully.',
                    'fees' => [$fee->fresh(['breakdowns', 'bankAccount.bank', 'feeConditions'])],
                ]);
            }

            $createdFees = [];
            foreach ($request->fees as $index => $feeData) {
                $isMulti = $accountMode === 'multi' || (($feeData['account_mode'] ?? '') === 'multi');
                $bankAccountId = $isMulti ? null : $singleBankAccountId;
                $hasBreakdown = false;
                $breakdowns = [];
                $amount = (float) ($feeData['amount'] ?? 0);

                if ($isMulti) {
                    $payload = $this->normalizeMultiFeePayload($feeData, $schoolID);
                    if (isset($payload['error'])) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => $payload['error']], 422);
                    }
                    $amount = $payload['amount'];
                    $bankAccountId = $payload['bank_account_id'];
                    $hasBreakdown = $payload['has_breakdown'];
                    $breakdowns = $payload['breakdowns'];
                }

                $fee = Fee::create([
                    'schoolID' => $schoolID,
                    'classID' => $request->classID,
                    'fee_name' => $feeData['name'] ?? 'Fee',
                    'amount' => $amount,
                    'must_start_pay' => isset($feeData['must_start_pay']) && $feeData['must_start_pay'] == 1,
                    'payment_deadline_amount' => $feeData['deadline_amount'] ?? null,
                    'payment_deadline_date' => $feeData['deadline_date'] ?? null,
                    'display_order' => $index,
                    'duration' => $request->duration,
                    'description' => $feeData['description'] ?? null,
                    'sms_assignment_template' => $request->input('sms_assignment_template'),
                    'sms_reminder_template' => $request->input('sms_reminder_template'),
                    'status' => 'Active',
                    'account_mode' => $isMulti ? 'multi' : 'single',
                    'bank_account_id' => $bankAccountId,
                    'has_breakdown' => $hasBreakdown,
                    'allow_installments' => !$isMulti && $request->boolean('allow_installments'),
                    'default_installment_type' => $request->input('default_installment_type'),
                    'number_of_installments' => $request->input('number_of_installments'),
                    'allow_partial_payment' => !$isMulti && $request->boolean('allow_partial_payment'),
                ]);

                if ($isMulti) {
                    foreach ($breakdowns as $i => $bd) {
                        FeeBreakdown::create([
                            'feeID' => $fee->feeID,
                            'item_name' => $bd['item_name'],
                            'amount' => $bd['amount'],
                            'display_order' => $i,
                        ]);
                    }
                }

                // Create installments if allowed (single account only)
                if (!$isMulti && $request->boolean('allow_installments') && $request->input('number_of_installments')) {
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
                            'description' => $condData['description'] ?? null,
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
     * Normalize + validate one multi-account fee row.
     */
    private function normalizeMultiFeePayload(array $feeData, int $schoolID): array
    {
        $bankAccountId = (int) ($feeData['bank_account_id'] ?? 0);
        $account = SchoolBankAccount::where('schoolID', $schoolID)
            ->where('accountID', $bankAccountId)
            ->approved()
            ->first();
        if (!$account) {
            return ['error' => 'Select an approved bank account for each multi-account fee.'];
        }

        $hasBreakdown = !empty($feeData['has_breakdown']) && (string) $feeData['has_breakdown'] !== '0';
        $breakdowns = [];
        $amount = (float) ($feeData['amount'] ?? 0);

        if ($hasBreakdown) {
            $raw = $feeData['breakdowns'] ?? [];
            if (!is_array($raw) || empty($raw)) {
                return ['error' => 'Add at least one breakdown item when Sub-fee Breakdown is ON.'];
            }
            $sum = 0;
            foreach ($raw as $bd) {
                $name = trim((string) ($bd['item_name'] ?? ''));
                $bdAmount = (float) ($bd['amount'] ?? 0);
                if ($name === '' || $bdAmount < 0) {
                    continue;
                }
                $breakdowns[] = ['item_name' => $name, 'amount' => $bdAmount];
                $sum += $bdAmount;
            }
            if (empty($breakdowns)) {
                return ['error' => 'Breakdown items are incomplete.'];
            }
            $amount = $sum;
        }

        if ($amount < 0) {
            return ['error' => 'Fee amount cannot be negative.'];
        }

        return [
            'fee_name' => $feeData['name'] ?? 'Fee',
            'amount' => $amount,
            'description' => $feeData['description'] ?? null,
            'bank_account_id' => $account->accountID,
            'has_breakdown' => $hasBreakdown,
            'breakdowns' => $breakdowns,
        ];
    }

    /**
     * Synchronize condition definitions for one fee.
     */
    private function syncFeeConditions(Fee $fee, array $conditions, int $schoolID): void
    {
        $keptConditionIDs = [];

        foreach ($conditions as $conditionData) {
            $name = trim((string) ($conditionData['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $values = [
                'schoolID' => $schoolID,
                'feeID' => $fee->feeID,
                'condition_name' => $name,
                'discount_amount' => max(0, (float) ($conditionData['amount'] ?? 0)),
                'discount_type' => ($conditionData['type'] ?? 'Fixed') === 'Percentage' ? 'Percentage' : 'Fixed',
                'description' => isset($conditionData['description'])
                    ? trim((string) $conditionData['description'])
                    : null,
                'status' => 'Active',
            ];

            $conditionID = (int) ($conditionData['id'] ?? 0);
            $condition = $conditionID
                ? FeeCondition::where('conditionID', $conditionID)
                    ->where('feeID', $fee->feeID)
                    ->where('schoolID', $schoolID)
                    ->first()
                : null;

            if ($condition) {
                $condition->update($values);
            } else {
                $condition = FeeCondition::create($values);
            }
            $keptConditionIDs[] = $condition->conditionID;
        }

        FeeCondition::where('schoolID', $schoolID)
            ->where('feeID', $fee->feeID)
            ->when(!empty($keptConditionIDs), fn ($query) => $query->whereNotIn('conditionID', $keptConditionIDs))
            ->update(['status' => 'Inactive']);
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
     * Calculate effective fee amount after agreement/discount.
     */
    private function calculateEffectiveFeeAmount($student, $fee, $academicYearID): array
    {
        $effectiveAmount = (float) $fee->amount;
        $feeNameNote = '';

        $agreement = FeeAgreement::where('studentID', $student->studentID)
            ->where('feeID', $fee->feeID)
            ->where('academic_yearID', $academicYearID)
            ->where('status', 'Active')
            ->first();

        if ($agreement) {
            $effectiveAmount = (float) $agreement->agreed_amount;
            $feeNameNote = ' (Agreement)';
        } else {
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

        return [
            'amount' => max(0, $effectiveAmount),
            'name' => $fee->fee_name . $feeNameNote,
        ];
    }

    /**
     * Amount that should already have been paid for a fee based on academic-year deadlines.
     *
     * Fixed and percent rows are both period portions:
     * - fixed: TZS for that period (scaled for discount/agreement)
     * - percent: % of the student's effective fee for that period
     * Required by today = sum of every deadline whose end date has already passed.
     */
    private function deadlineRequiredForFee(int $feeID, int $academicYearID, float $effectiveAmount, float $standardAmount): float
    {
        if ($effectiveAmount <= 0 || $academicYearID <= 0) {
            return 0;
        }

        $deadlines = FeeDeadline::where('feeID', $feeID)
            ->where('academic_yearID', $academicYearID)
            ->where('status', 'Active')
            ->whereDate('end_date', '<=', Carbon::today()->toDateString())
            ->orderBy('end_date')
            ->orderBy('deadlineID')
            ->get();

        if ($deadlines->isEmpty()) {
            return 0;
        }

        $required = 0.0;
        foreach ($deadlines as $deadline) {
            $required += $deadline->requiredForAmount($effectiveAmount, $standardAmount);
        }

        return max(0, min($effectiveAmount, $required));
    }

    /**
     * Upsert payment + student_fee_payments for a fee group (single unified or one multi fee).
     */
    private function upsertPaymentBill(
        $student,
        $academicYearID,
        $schoolID,
        $enrollmentID,
        $fees,
        $debt = 0,
        $feeID = null
    ) {
        $totalAmount = 0;
        $mustPayAmount = 0;
        foreach ($fees as $fee) {
            $calc = $this->calculateEffectiveFeeAmount($student, $fee, $academicYearID);
            $fee->calculated_amount = $calc['amount'];
            $fee->calculated_name = $calc['name'];
            $totalAmount += $calc['amount'];

            $requiredNow = 0;
            if ($fee->must_start_pay) {
                $requiredNow = $calc['amount'];
            }
            $deadlineRequired = $this->deadlineRequiredForFee(
                (int) $fee->feeID,
                (int) $academicYearID,
                (float) $calc['amount'],
                (float) $fee->amount
            );
            $mustPayAmount += max($requiredNow, $deadlineRequired);
        }

        // Prior-year debt applies only to the unified single-account bill
        $applyDebt = $feeID === null ? (float) $debt : 0;
        $totalRequiredAmount = $totalAmount + $applyDebt;

        $sponsorPercentage = (float) ($student->sponsorship_percentage ?? 0);
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

        $paymentQuery = Payment::where('studentID', $student->studentID)
            ->where('academic_yearID', $academicYearID)
            ->where('schoolID', $schoolID);
        if ($feeID === null) {
            $paymentQuery->where(function ($q) {
                $q->whereNull('feeID')->orWhere('feeID', 0);
            });
        } else {
            $paymentQuery->where('feeID', $feeID);
        }
        $payment = $paymentQuery->first();

        if (!$payment) {
            $payment = Payment::create([
                'schoolID' => $schoolID,
                'academic_yearID' => $academicYearID,
                'studentID' => $student->studentID,
                'enrollmentID' => $enrollmentID,
                'feeID' => $feeID,
                'control_number' => $this->generateControlNumber($schoolID, $student->studentID),
                'amount_required' => $totalRequiredAmount,
                'amount_paid' => 0,
                'balance' => $totalRequiredAmount,
                'debt' => $applyDebt,
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
            $paymentUpdate = [
                'feeID' => $feeID,
                'amount_required' => $totalRequiredAmount,
                'balance' => max(0, $totalRequiredAmount - $payment->amount_paid),
                'debt' => $applyDebt,
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

        $activeFeeIDs = [];
        foreach ($fees as $fee) {
            $activeFeeIDs[] = $fee->feeID;
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
                    'balance' => max(0, $fee->calculated_amount - $sfp->amount_paid),
                    'is_required' => $fee->must_start_pay,
                    'display_order' => $fee->display_order,
                ];
                if (empty($sfp->enrollmentID)) {
                    $sfpUpdate['enrollmentID'] = $payment->enrollmentID ?: $enrollmentID;
                }
                $sfp->update($sfpUpdate);
            }
        }

        // Drop stale fee lines on this payment that are no longer in the group
        if (!empty($activeFeeIDs)) {
            StudentFeePayment::where('paymentID', $payment->paymentID)
                ->whereNotIn('feeID', $activeFeeIDs)
                ->delete();
        }

        return $payment;
    }

    /**
     * Helper to generate/update a specific student's bill.
     * Single-account fees share one control number; each multi-account fee gets its own.
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

        $fees = Fee::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('status', 'Active')
            ->orderBy('display_order')
            ->get();

        $singleFees = $fees->filter(fn ($f) => ($f->account_mode ?? 'single') !== 'multi')->values();
        $multiFees = $fees->filter(fn ($f) => ($f->account_mode ?? 'single') === 'multi')->values();

        $previousYearBalance = $this->getPreviousYearBalance(
            $student->studentID,
            $schoolID,
            (int) $academicYearID
        );
        $debt = $previousYearBalance['school_fee_balance'] + $previousYearBalance['other_contribution_balance'];

        $primaryPayment = null;
        if ($singleFees->count() > 0 || $debt > 0) {
            $primaryPayment = $this->upsertPaymentBill(
                $student,
                $academicYearID,
                $schoolID,
                $enrollmentID,
                $singleFees,
                $debt,
                null
            );
        }

        $activeMultiFeeIDs = [];
        foreach ($multiFees as $fee) {
            $activeMultiFeeIDs[] = $fee->feeID;
            $this->upsertPaymentBill(
                $student,
                $academicYearID,
                $schoolID,
                $enrollmentID,
                collect([$fee]),
                0,
                $fee->feeID
            );
        }

        // Remove orphan multi-account payments for fees that no longer exist/are inactive
        $orphanQuery = Payment::where('studentID', $student->studentID)
            ->where('academic_yearID', $academicYearID)
            ->where('schoolID', $schoolID)
            ->whereNotNull('feeID')
            ->where('feeID', '>', 0);
        if (!empty($activeMultiFeeIDs)) {
            $orphanQuery->whereNotIn('feeID', $activeMultiFeeIDs);
        }
        $orphans = $orphanQuery->get();
        foreach ($orphans as $orphan) {
            StudentFeePayment::where('paymentID', $orphan->paymentID)->delete();
            $orphan->delete();
        }

        // Re-allocate verified receipts against the newly calculated fee amounts.
        $studentPayments = Payment::where('studentID', $student->studentID)
            ->where('academic_yearID', $academicYearID)
            ->where('schoolID', $schoolID)
            ->get();
        foreach ($studentPayments as $studentPayment) {
            $this->allocatePayment($studentPayment);
        }

        return $primaryPayment;
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
        'bank_account_id' => 'nullable|integer',
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

    $targetClassID = (int) $request->classID;
    $classBelongsToSchool = ClassModel::where('classID', $targetClassID)
        ->where('schoolID', $schoolID)
        ->exists();
    if (!$classBelongsToSchool) {
        return response()->json([
            'success' => false,
            'message' => 'The selected class does not belong to this school.',
        ], 422);
    }

    $feeMode = ($fee->account_mode ?? 'single') === 'multi' ? 'multi' : 'single';
    $oppositeModeExists = Fee::where('schoolID', $schoolID)
        ->where('classID', $targetClassID)
        ->where('feeID', '!=', $fee->feeID)
        ->when($feeMode === 'multi', function ($query) {
            $query->where(function ($q) {
                $q->where('account_mode', 'single')->orWhereNull('account_mode');
            });
        }, function ($query) {
            $query->where('account_mode', 'multi');
        })
        ->exists();

    if ($oppositeModeExists) {
        $existingMode = $feeMode === 'multi' ? 'Single Account Fee' : 'Multi Account Fee';
        return response()->json([
            'success' => false,
            'message' => "This class is already defined under {$existingMode}. A class cannot use both fee account modes.",
        ], 422);
    }

    $updatedBankAccountId = $fee->bank_account_id;
    if ($feeMode === 'single') {
        if ($request->filled('bank_account_id')) {
            $singleAccount = SchoolBankAccount::where('schoolID', $schoolID)
                ->where('accountID', (int) $request->bank_account_id)
                ->approved()
                ->first();
            if (!$singleAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected account number is not an approved account for this school.',
                ], 422);
            }
            $updatedBankAccountId = $singleAccount->accountID;
        } else {
            $updatedBankAccountId = null;
        }
    }

    try {
        DB::beginTransaction();

        $fee->update([
            'classID' => $request->classID,
            'bank_account_id' => $updatedBankAccountId,
            'fee_name' => $request->fee_name,
            'amount' => $request->amount,
            'duration' => $request->duration,
            'description' => $request->description,
            'sms_assignment_template' => $request->sms_assignment_template,
            'sms_reminder_template' => $request->sms_reminder_template,
        ]);

        $this->syncFeeConditions(
            $fee,
            is_array($request->conditions) ? $request->conditions : [],
            (int) $schoolID
        );

        // Student assignments linked to now-inactive conditions must also deactivate.
        $inactiveCondIDs = FeeCondition::where('schoolID', $schoolID)
            ->where('feeID', $feeID)
            ->where('status', 'Inactive')
            ->pluck('conditionID');
        if ($inactiveCondIDs->isNotEmpty()) {
            StudentFeeCondition::whereIn('conditionID', $inactiveCondIDs)
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
            ->with(['class', 'otherFeeDetails', 'feeConditions', 'breakdowns', 'bankAccount.bank'])
            ->first();

        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'Fee not found'
            ], 404);
        }

        $academicYearID = $this->resolveFeesAcademicYearID((int) $schoolID, (int) $fee->classID);
        $currentInstallments = FeeInstallment::where('feeID', $fee->feeID)
            ->when(
                $academicYearID,
                fn ($query) => $query->where('academic_yearID', $academicYearID),
                fn ($query) => $query->whereNull('academic_yearID')
            )
            ->where('status', 'Active')
            ->orderBy('installment_number')
            ->get();
        $fee->setRelation('installments', $currentInstallments);

        // Format other fee details for JSON response
        $fee->other_fee_details = $fee->otherFeeDetails->map(function($detail) {
            return [
                'detailID' => $detail->detailID,
                'fee_detail_name' => $detail->fee_detail_name,
                'amount' => (float) $detail->amount,
                'description' => $detail->description,
            ];
        });

        $fee->breakdowns_list = $fee->breakdowns->map(function ($bd) {
            return [
                'breakdownID' => $bd->breakdownID,
                'item_name' => $bd->item_name,
                'amount' => (float) $bd->amount,
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
     * Resolve academic year ID(s) for payments from header selection (class-aware for O/A).
     * Request academic_year_id is only used as an explicit override (e.g. record against a prior year bill).
     *
     * @return array{academicYearIDs: array<int>, academicYearID: ?int, year: string|int, yearLabel: string, isClosedYear: bool}
     */
    private function resolvePaymentsAcademicYear(int $schoolID, Request $request, $classID = null): array
    {
        $yearService = app(SelectedAcademicYearService::class);
        $yearIDs = [];

        // Explicit override (e.g. recording against a specific enrollment year bill)
        $requestYearID = $request->input('academic_year_id');
        if (!empty($requestYearID) && $request->boolean('use_request_year', false)) {
            $yearIDs = [(int) $requestYearID];
        } elseif (!empty($classID)) {
            $class = ClassModel::find($classID);
            $yearID = $yearService->yearIdForClassName($class->class_name ?? null, $schoolID);
            if ($yearID) {
                $yearIDs = [(int) $yearID];
            }
        }

        if (empty($yearIDs)) {
            $yearIDs = array_values(array_filter(array_map('intval', $yearService->selectedYearIds($schoolID))));
        }

        if (empty($yearIDs)) {
            $fallback = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderByDesc('year')
                ->orderByDesc('academic_yearID')
                ->first();
            if ($fallback) {
                $yearIDs = [(int) $fallback->academic_yearID];
            }
        }

        $primaryID = $yearIDs[0] ?? null;
        $academicYear = $primaryID
            ? DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->where('academic_yearID', $primaryID)
                ->first()
            : null;

        $labels = [];
        if (!empty($yearIDs)) {
            $labels = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->whereIn('academic_yearID', $yearIDs)
                ->get()
                ->map(fn ($y) => $y->year_name ?: $y->year)
                ->filter()
                ->values()
                ->all();
        }

        $isClosedYear = false;
        if (!empty($yearIDs)) {
            $openCount = DB::table('academic_years')
                ->where('schoolID', $schoolID)
                ->whereIn('academic_yearID', $yearIDs)
                ->where('status', '!=', 'Closed')
                ->count();
            $isClosedYear = $openCount === 0;
        }

        return [
            'academicYearIDs' => $yearIDs,
            'academicYearID' => $primaryID,
            'year' => $academicYear->year ?? date('Y'),
            'yearLabel' => !empty($labels) ? implode(' | ', $labels) : (string) ($academicYear->year ?? date('Y')),
            'isClosedYear' => $isClosedYear,
        ];
    }

    /**
     * Student IDs enrolled in given academic year(s) (+ optional class/subclass/status/promotion).
     */
    private function paymentCohortStudentIDs(
        int $schoolID,
        $academicYearIDs,
        string $enrollmentStatus = 'Active',
        $classID = null,
        $subclassID = null,
        string $promotionStatus = ''
    ): array {
        $yearIDs = is_array($academicYearIDs)
            ? array_values(array_filter(array_map('intval', $academicYearIDs)))
            : (empty($academicYearIDs) ? [] : [(int) $academicYearIDs]);

        if (empty($yearIDs)) {
            return [];
        }

        $query = StudentEnrollment::query()
            ->where('schoolID', $schoolID)
            ->whereIn('academic_yearID', $yearIDs);

        if ($subclassID) {
            $query->where('subclassID', (int) $subclassID);
        } elseif ($classID) {
            $query->where('classID', (int) $classID);
        }

        $normalized = $enrollmentStatus === 'Graduated' ? 'Completed' : $enrollmentStatus;
        if ($normalized === '' || $normalized === null) {
            $normalized = 'Active';
        }
        if (strtolower($normalized) !== 'all') {
            $allowed = ['Active', 'Completed', 'Transferred', 'Dropped', 'Cancelled'];
            if (in_array($normalized, $allowed, true)) {
                $query->where('status', $normalized);
            }
        }

        $promotionStatus = is_string($promotionStatus) ? trim($promotionStatus) : '';
        if ($promotionStatus !== '' && strtolower($promotionStatus) !== 'all') {
            if (in_array($promotionStatus, StudentEnrollment::PROMOTION_STATUSES, true)) {
                $query->where('promotion_status', $promotionStatus);
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
        $academicYearIDs,
        array $studentIDs
    ): array {
        $yearIDs = is_array($academicYearIDs)
            ? array_values(array_filter(array_map('intval', $academicYearIDs)))
            : (empty($academicYearIDs) ? [] : [(int) $academicYearIDs]);

        if (empty($yearIDs) || empty($studentIDs)) {
            return [];
        }

        $rows = StudentEnrollment::with(['classModel', 'subclass', 'academicYear'])
            ->where('schoolID', $schoolID)
            ->whereIn('academic_yearID', $yearIDs)
            ->whereIn('studentID', $studentIDs)
            ->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
            ->orderByDesc('enrollmentID')
            ->get()
            ->unique('studentID');

        $map = [];
        foreach ($rows as $enr) {
            $ay = $enr->academicYear;
            $map[(int) $enr->studentID] = [
                'enrollmentID' => (int) $enr->enrollmentID,
                'academic_yearID' => (int) $enr->academic_yearID,
                'academic_year_label' => $ay
                    ? ($ay->year_name ?: $ay->year)
                    : null,
                'enrollment_status' => $enr->status,
                'promotion_status' => $enr->promotion_status ?: 'Pending',
                'promotion_status_label' => StudentEnrollment::promotionStatusLabel($enr->promotion_status ?: 'Pending'),
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
                $enrollmentStatus = 'Active';
            }
            $promotionStatus = $request->input('promotion_status', '');
            $paymentStatus = $request->input('payment_status', '');
            $searchStudentName = $request->input('search_student_name', '');
            $sponsorshipFilter = $request->input('sponsorship_filter', ''); // 'sponsored' or 'self'
            $feeIDFilterRaw = $request->input('fee_id', '');
            $feeIDFilter = ($feeIDFilterRaw !== '' && $feeIDFilterRaw !== 'all' && $feeIDFilterRaw !== null)
                ? (int) $feeIDFilterRaw
                : null;

            $resolved = $this->resolvePaymentsAcademicYear(
                (int) $schoolID,
                $request,
                $classID !== '' ? $classID : null
            );
            $academicYearIDs = $resolved['academicYearIDs'];
            $academicYearID = $resolved['academicYearID'];
            $year = $resolved['year'];
            $yearLabel = $resolved['yearLabel'];
            $isClosedYear = $resolved['isClosedYear'];

            $studentIDs = $this->paymentCohortStudentIDs(
                (int) $schoolID,
                $academicYearIDs,
                (string) $enrollmentStatus,
                $classID !== '' ? $classID : null,
                $subclassID !== '' ? $subclassID : null,
                (string) $promotionStatus
            );

            if (empty($studentIDs)) {
                $emptyFeeStructure = ['mode' => 'none', 'fees' => []];
                if ($classID !== '') {
                    $classFees = Fee::where('schoolID', $schoolID)
                        ->where('classID', (int) $classID)
                        ->where('status', 'Active')
                        ->with('bankAccount.bank')
                        ->orderBy('display_order')
                        ->get();
                    $hasMulti = $classFees->contains(fn ($f) => ($f->account_mode ?? 'single') === 'multi');
                    $hasSingle = $classFees->contains(fn ($f) => ($f->account_mode ?? 'single') !== 'multi');
                    if ($hasMulti && !$hasSingle) {
                        $emptyFeeStructure['mode'] = 'multi';
                    } elseif ($hasSingle && !$hasMulti) {
                        $emptyFeeStructure['mode'] = 'single';
                    } elseif ($hasMulti && $hasSingle) {
                        $emptyFeeStructure['mode'] = 'mixed';
                    }
                    $emptyFeeStructure['fees'] = $classFees->map(function ($fee) {
                        return [
                            'feeID' => (int) $fee->feeID,
                            'fee_name' => $fee->fee_name,
                            'amount' => (float) $fee->amount,
                            'account_mode' => $fee->account_mode ?? 'single',
                            'account_display' => $fee->bankAccount ? $fee->bankAccount->displayLabel() : null,
                        ];
                    })->values()->all();
                }

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
                    'academic_year' => $yearLabel,
                    'academic_year_id' => $academicYearID,
                    'academic_year_ids' => $academicYearIDs,
                    'fee_structure' => $emptyFeeStructure,
                    'fee_filter' => $feeIDFilter ?: 'all',
                    'error_code' => 'no_enrollments',
                    'message' => 'No enrollments found for the header academic year / selected filters.',
                ]);
            }

            $enrollmentMeta = $this->paymentCohortEnrollmentMeta(
                (int) $schoolID,
                $academicYearIDs,
                $studentIDs
            );

            $query = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $studentIDs)
                ->with(['parent', 'sponsor', 'subclass.class', 'payments' => function ($q) use ($academicYearIDs, $year) {
                    if (!empty($academicYearIDs)) {
                        $q->whereIn('academic_yearID', $academicYearIDs);
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

            // Class fee structure (single vs multi) for the payments fee filter
            $feeStructure = [
                'mode' => 'none',
                'fees' => [],
            ];
            if ($classID !== '') {
                $classFees = Fee::where('schoolID', $schoolID)
                    ->where('classID', (int) $classID)
                    ->where('status', 'Active')
                    ->with('bankAccount.bank')
                    ->orderBy('display_order')
                    ->get();
                $hasMulti = $classFees->contains(fn ($f) => ($f->account_mode ?? 'single') === 'multi');
                $hasSingle = $classFees->contains(fn ($f) => ($f->account_mode ?? 'single') !== 'multi');
                if ($hasMulti && !$hasSingle) {
                    $feeStructure['mode'] = 'multi';
                } elseif ($hasSingle && !$hasMulti) {
                    $feeStructure['mode'] = 'single';
                } elseif ($hasMulti && $hasSingle) {
                    $feeStructure['mode'] = 'mixed';
                }
                $feeStructure['fees'] = $classFees->map(function ($fee) {
                    return [
                        'feeID' => (int) $fee->feeID,
                        'fee_name' => $fee->fee_name,
                        'amount' => (float) $fee->amount,
                        'account_mode' => $fee->account_mode ?? 'single',
                        'account_display' => $fee->bankAccount ? $fee->bankAccount->displayLabel() : null,
                        'payment_deadline_amount' => $fee->payment_deadline_amount !== null
                            ? (float) $fee->payment_deadline_amount
                            : null,
                        'payment_deadline_date' => $fee->payment_deadline_date
                            ? $fee->payment_deadline_date->format('Y-m-d')
                            : null,
                    ];
                })->values()->all();
            }

            // Group payments by student and aggregate totals
            $filteredData = [];
            $index = 1; // Initialize index
            // Prefetch multi-account fee + bank labels for student year payments
            $multiFeeIDs = $students->flatMap(fn ($s) => $s->payments->pluck('feeID'))
                ->filter()
                ->unique()
                ->values()
                ->all();
            if ($feeIDFilter) {
                $multiFeeIDs[] = $feeIDFilter;
                $multiFeeIDs = array_values(array_unique($multiFeeIDs));
            }
            $multiFeesMap = empty($multiFeeIDs)
                ? collect()
                : Fee::with('bankAccount.bank')->whereIn('feeID', $multiFeeIDs)->get()->keyBy('feeID');

            foreach ($students as $student) {
                $enrMeta = $enrollmentMeta[(int) $student->studentID] ?? null;
                $studentYearID = (int) ($enrMeta['academic_yearID'] ?? $academicYearID);

                $yearPayments = $student->payments->filter(function ($p) use ($studentYearID) {
                    return (int) $p->academic_yearID === (int) $studentYearID;
                });
                if ($yearPayments->isEmpty()) {
                    $yearPayments = $student->payments;
                }

                // Focus on one multi-account fee when fee filter is set
                $focusFeeMeta = null;
                if ($feeIDFilter) {
                    $focusedPayment = $yearPayments->first(function ($p) use ($feeIDFilter) {
                        return (int) ($p->feeID ?? 0) === $feeIDFilter;
                    });
                    if (!$focusedPayment) {
                        // No bill yet for this fee — still list the student as pending
                        $feeMeta = $multiFeesMap->get($feeIDFilter);
                        $yearPayments = collect();
                        $payment = null;
                        $multiPayments = collect();
                        $totalRequired = 0.0;
                        $totalPaid = 0.0;
                        $totalBalance = 0.0;
                        $requiredPaid = 0.0;
                        $requiredAmount = 0.0;
                        $canStartSchool = false;
                        $aggregatedStatus = 'Pending';
                        $focusFeeMeta = [
                            'feeID' => $feeIDFilter,
                            'fee_name' => $feeMeta->fee_name ?? 'Fee',
                            'control_number' => null,
                            'account_label' => ($feeMeta && $feeMeta->bankAccount)
                                ? $feeMeta->bankAccount->displayLabel()
                                : null,
                            'amount_required' => 0,
                            'amount_paid' => 0,
                            'balance' => 0,
                            'deadline_required' => 0,
                            'deadline_paid' => 0,
                            'deadline_met' => false,
                            'deadline_overdue' => false,
                            'payment_status' => 'Pending',
                        ];
                    } else {
                        $yearPayments = collect([$focusedPayment]);
                        $payment = $focusedPayment;
                        $multiPayments = collect([$focusedPayment]);
                        $totalRequired = (float) $focusedPayment->amount_required;
                        $totalPaid = (float) $focusedPayment->amount_paid;
                        $totalBalance = (float) $focusedPayment->balance;
                        $requiredPaid = (float) $focusedPayment->required_fees_paid;
                        $requiredAmount = (float) $focusedPayment->required_fees_amount;
                        $canStartSchool = (bool) $focusedPayment->can_start_school;
                        $aggregatedStatus = $focusedPayment->payment_status ?: 'Pending';
                        if ($totalBalance <= 0 && $totalPaid > 0) {
                            $aggregatedStatus = $totalPaid > $totalRequired ? 'Overpaid' : 'Paid';
                        } elseif ($totalPaid > 0 && $totalBalance > 0) {
                            $aggregatedStatus = 'Incomplete Payment';
                        }
                        $feeMeta = $multiFeesMap->get($feeIDFilter);
                        $focusFeeMeta = [
                            'feeID' => $feeIDFilter,
                            'paymentID' => $focusedPayment->paymentID,
                            'fee_name' => $feeMeta->fee_name
                                ?? (optional($focusedPayment->fee_payments->first())->fee_name ?? 'Fee'),
                            'control_number' => $focusedPayment->control_number,
                            'account_label' => ($feeMeta && $feeMeta->bankAccount)
                                ? $feeMeta->bankAccount->displayLabel()
                                : null,
                            'amount_required' => $totalRequired,
                            'amount_paid' => $totalPaid,
                            'balance' => $totalBalance,
                            'deadline_required' => $requiredAmount,
                            'deadline_paid' => $requiredPaid,
                            'deadline_met' => $requiredAmount <= 0
                                ? true
                                : ($requiredPaid + 0.00001 >= $requiredAmount),
                            'deadline_overdue' => $requiredAmount > 0
                                && ($requiredPaid + 0.00001 < $requiredAmount),
                            'payment_status' => $aggregatedStatus,
                        ];
                    }
                } else {
                    // Unified single-account payment (feeID null/0) for primary actions
                    $payment = $yearPayments->first(function ($p) {
                        return empty($p->feeID);
                    }) ?: $yearPayments->first();

                    $multiPayments = $yearPayments->filter(fn ($p) => !empty($p->feeID))->values();

                    // Aggregate student totals across all control numbers for the year
                    $totalRequired = (float) $yearPayments->sum('amount_required');
                    $totalPaid = (float) $yearPayments->sum('amount_paid');
                    $totalBalance = (float) $yearPayments->sum('balance');
                    $requiredPaid = (float) $yearPayments->sum('required_fees_paid');
                    $requiredAmount = (float) $yearPayments->sum('required_fees_amount');
                    $canStartSchool = $yearPayments->isNotEmpty()
                        && $yearPayments->every(fn ($p) => (bool) $p->can_start_school);

                    $aggregatedStatus = 'Pending';
                    if ($yearPayments->isEmpty()) {
                        $aggregatedStatus = 'Pending';
                    } elseif ($totalBalance <= 0 && $totalPaid > 0) {
                        $aggregatedStatus = $totalPaid > $totalRequired ? 'Overpaid' : 'Paid';
                    } elseif ($totalPaid > 0) {
                        $aggregatedStatus = 'Incomplete Payment';
                    }
                }

                if (!$payment && $yearPayments->isEmpty() && !empty($paymentStatus)) {
                    if ($paymentStatus !== "Pending") continue;
                }

                if (!empty($paymentStatus)) {
                    $status = $aggregatedStatus;
                    if ($paymentStatus === 'Incomplete' || $paymentStatus === 'Partial' || $paymentStatus === 'Incomplete Payment') {
                        if ($status !== 'Incomplete Payment' && $status !== 'Partial') continue;
                    } elseif ($status !== $paymentStatus) {
                        continue;
                    }
                }

                // Calculate verification status (across year payment records)
                $paymentIDs = $yearPayments->pluck('paymentID')->filter()->all();
                $hasUnverified = !empty($paymentIDs)
                    ? PaymentRecord::whereIn('paymentID', $paymentIDs)->where('is_verified', false)->exists()
                    : false;
                $hasVerified = !empty($paymentIDs)
                    ? PaymentRecord::whereIn('paymentID', $paymentIDs)->where('is_verified', true)->exists()
                    : false;

                $verificationStatus = 'Unverified';
                if ($yearPayments->isNotEmpty()) {
                    if ($totalPaid == 0) {
                        $verificationStatus = 'No Payment';
                    } elseif (!$hasUnverified && $hasVerified) {
                        $verificationStatus = 'Verified';
                    } elseif ($hasUnverified && $hasVerified) {
                        $verificationStatus = 'Partially Verified';
                    } else {
                        $verificationStatus = 'Pending Verification';
                    }
                }

                $studentData = [
                    'studentID' => $student->studentID,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'status' => $student->status,
                    'enrollment_status' => $enrMeta['enrollment_status'] ?? null,
                    'promotion_status' => $enrMeta['promotion_status'] ?? null,
                    'promotion_status_label' => $enrMeta['promotion_status_label'] ?? null,
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

                // Prior enrollment years still owing (excluding this student's bill year)
                $priorDebtInfo = $this->getPreviousYearBalance(
                    $student->studentID,
                    $schoolID,
                    $studentYearID ?: $academicYearID
                );
                $priorYearsOwed = (float) ($priorDebtInfo['school_fee_balance'] ?? 0)
                    + (float) ($priorDebtInfo['other_contribution_balance'] ?? 0);

                // Prefer live prior-debt calc; fall back to bill's stored debt field
                $billDebt = (float) ($payment?->debt ?? $yearPayments->sum('debt') ?? 0);
                $displayPriorDebt = $feeIDFilter ? 0.0 : max($priorYearsOwed, $billDebt);

                // No calculation for sponsorAmount is required as sponsors do not adjust 'totalRequiredAmount'
                $originalTotal = $totalRequired;
                $sponsorAmount = 0;

                $rowYearLabel = $enrMeta['academic_year_label']
                    ?? ($payment && $payment->academicYear
                        ? ($payment->academicYear->year_name ?: $payment->academicYear->year)
                        : $yearLabel);

                $multiAccountPayload = $multiPayments->map(function ($mp) use ($multiFeesMap) {
                    $fee = $multiFeesMap->get($mp->feeID);
                    $accountLabel = $fee && $fee->bankAccount ? $fee->bankAccount->displayLabel() : null;
                    $deadlineRequired = (float) ($mp->required_fees_amount ?? 0);
                    $deadlinePaid = (float) ($mp->required_fees_paid ?? 0);
                    return [
                        'paymentID' => $mp->paymentID,
                        'feeID' => $mp->feeID,
                        'fee_name' => $fee->fee_name ?? (optional($mp->fee_payments->first())->fee_name ?? 'Multi Fee'),
                        'control_number' => $mp->control_number,
                        'account_label' => $accountLabel,
                        'amount_required' => (float) $mp->amount_required,
                        'amount_paid' => (float) $mp->amount_paid,
                        'balance' => (float) $mp->balance,
                        'payment_status' => $mp->payment_status,
                        'deadline_required' => $deadlineRequired,
                        'deadline_paid' => $deadlinePaid,
                        'deadline_met' => $deadlineRequired <= 0
                            ? true
                            : ($deadlinePaid + 0.00001 >= $deadlineRequired),
                        'deadline_overdue' => $deadlineRequired > 0
                            && ($deadlinePaid + 0.00001 < $deadlineRequired),
                        'can_start_school' => (bool) $mp->can_start_school,
                        'fee_payments' => $mp->fee_payments,
                        'payment_records' => $mp->paymentRecords ?? $mp->payment_records ?? [],
                        'sponsor_balance' => (float) ($mp->sponsor_balance ?? 0),
                        'parent_balance' => (float) ($mp->parent_balance ?? 0),
                        'sponsor_amount_required' => (float) ($mp->sponsor_amount_required ?? 0),
                        'parent_amount_required' => (float) ($mp->parent_amount_required ?? 0),
                    ];
                })->values();

                $filteredData[] = [
                    'index' => $index++,
                    'student' => $studentData,
                    'payment' => $payment,
                    'multi_accounts' => $multiAccountPayload,
                    'focus_fee' => $focusFeeMeta,
                    'all_payments' => $yearPayments->values(),
                    'totals' => [
                        'total_required' => $totalRequired,
                        'total_paid' => $totalPaid,
                        'total_balance' => $totalBalance,
                        'required_paid' => $requiredPaid,
                        'required_amount' => $requiredAmount,
                        'can_start_school' => $canStartSchool,
                        'deadline_met' => $requiredAmount <= 0
                            ? true
                            : ($requiredPaid + 0.00001 >= $requiredAmount),
                        'deadline_overdue' => $requiredAmount > 0
                            && ($requiredPaid + 0.00001 < $requiredAmount),
                        'original_total' => $originalTotal,
                        'sponsor_amount' => $sponsorAmount,
                        'prior_years_owed' => $displayPriorDebt,
                        'overall_status' => $aggregatedStatus,
                    ],
                    'payment_status' => $aggregatedStatus,
                    'verification_status' => $verificationStatus,
                    'academic_year' => $rowYearLabel,
                    'academic_year_id' => $studentYearID ?: $academicYearID,
                    'prior_years_owed' => $displayPriorDebt,
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
                'academic_year' => $yearLabel,
                'academic_year_id' => $academicYearID,
                'academic_year_ids' => $academicYearIDs,
                'is_closed_year' => $isClosedYear,
                'fee_structure' => $feeStructure,
                'fee_filter' => $feeIDFilter ?: 'all',
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

            $livePayments = Payment::with(['academicYear', 'fee_payments'])
                ->where('studentID', $studentID)
                ->where('schoolID', $schoolID)
                ->get();

            $paymentsByYear = $livePayments->groupBy(fn ($p) => (int) ($p->academic_yearID ?? 0));
            $feeIDs = $livePayments->pluck('feeID')->filter()->unique()->values()->all();
            $feesMap = empty($feeIDs)
                ? collect()
                : Fee::with('bankAccount.bank')->whereIn('feeID', $feeIDs)->get()->keyBy('feeID');

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

                $yearPays = ($paymentsByYear->get($yearId) ?? collect())->values();
                // Prefer matching enrollment, then unified single account
                $payment = $yearPays->first(fn ($p) => (int) ($p->enrollmentID ?? 0) === (int) $enr->enrollmentID && empty($p->feeID))
                    ?: $yearPays->first(fn ($p) => empty($p->feeID))
                    ?: $yearPays->first();

                $source = 'none';
                $amountRequired = (float) $yearPays->sum('amount_required');
                $amountPaid = (float) $yearPays->sum('amount_paid');
                $balance = (float) $yearPays->sum('balance');
                $debt = (float) $yearPays->sum('debt');
                $paymentStatus = 'No Bill';
                $controlNumber = null;
                $paymentID = null;
                $multiAccounts = [];

                if ($yearPays->isNotEmpty()) {
                    $source = 'live';
                    $paymentStatus = $balance <= 0 && $amountPaid > 0
                        ? ($amountPaid > $amountRequired ? 'Overpaid' : 'Paid')
                        : ($amountPaid > 0 ? 'Incomplete Payment' : 'Pending');
                    $controlNumber = $payment->control_number ?? null;
                    $paymentID = $payment->paymentID ?? null;
                    $multiAccounts = $yearPays->filter(fn ($p) => !empty($p->feeID))->map(function ($mp) use ($feesMap) {
                        $fee = $feesMap->get($mp->feeID);
                        return [
                            'paymentID' => $mp->paymentID,
                            'feeID' => $mp->feeID,
                            'fee_name' => $fee->fee_name ?? 'Multi Fee',
                            'control_number' => $mp->control_number,
                            'account_label' => $fee && $fee->bankAccount ? $fee->bankAccount->displayLabel() : null,
                            'amount_required' => (float) $mp->amount_required,
                            'amount_paid' => (float) $mp->amount_paid,
                            'balance' => (float) $mp->balance,
                            'payment_status' => $mp->payment_status,
                        ];
                    })->values()->all();
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

                $allFeePayments = $yearPays->flatMap(fn ($p) => $p->fee_payments ?? [])->values();

                $years[] = [
                    'enrollmentID' => (int) $enr->enrollmentID,
                    'academic_yearID' => $yearId,
                    'year' => $ay->year ?? null,
                    'year_name' => $yearLabel,
                    'year_status' => $ay->status ?? null,
                    'class_name' => $enr->classModel->class_name ?? 'N/A',
                    'subclass_name' => $enr->subclass->subclass_name ?? 'N/A',
                    'enrollment_status' => $enr->status,
                    'promotion_status' => $enr->promotion_status ?: 'Pending',
                    'promotion_status_label' => StudentEnrollment::promotionStatusLabel($enr->promotion_status ?: 'Pending'),
                    'amount_required' => $amountRequired,
                    'amount_paid' => $amountPaid,
                    'balance' => $balance,
                    'debt' => $debt,
                    'payment_status' => $paymentStatus,
                    'control_number' => $controlNumber,
                    'paymentID' => $paymentID,
                    'source' => $source,
                    'owes' => $balance > 0,
                    'can_record' => $source === 'live' && $paymentID && (($ay->status ?? '') !== 'Closed'),
                    'fee_payments' => $allFeePayments,
                    'multi_accounts' => $multiAccounts,
                    'payment' => $payment,
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
                    'promotion_status_label' => null,
                    'amount_required' => (float) $payment->amount_required,
                    'amount_paid' => (float) $payment->amount_paid,
                    'balance' => (float) $payment->balance,
                    'debt' => (float) ($payment->debt ?? 0),
                    'payment_status' => $payment->payment_status ?: 'Pending',
                    'control_number' => $payment->control_number,
                    'paymentID' => $payment->paymentID,
                    'source' => 'live',
                    'owes' => (float) $payment->balance > 0,
                    'can_record' => (($ay->status ?? '') !== 'Closed'),
                    'fee_payments' => $payment->fee_payments ?? [],
                    'payment' => $payment,
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
            $requestYearID = $request->input('academic_year_id');
            $filterClassID = $request->input('class_id');
            $academicYearID = null;

            if (!empty($requestYearID)) {
                $ay = DB::table('academic_years')
                    ->where('schoolID', $schoolID)
                    ->where('academic_yearID', $requestYearID)
                    ->first();
                if ($ay) {
                    $academicYearID = (int) $ay->academic_yearID;
                }
            }

            if (!$academicYearID && !empty($filterClassID)) {
                $class = ClassModel::find($filterClassID);
                $yearID = app(SelectedAcademicYearService::class)
                    ->yearIdForClassName($class->class_name ?? null, (int) $schoolID);
                if ($yearID) {
                    $academicYearID = (int) $yearID;
                }
            }

            if (!$academicYearID) {
                $selectedIds = app(SelectedAcademicYearService::class)->selectedYearIds((int) $schoolID);
                if (!empty($selectedIds)) {
                    $academicYearID = (int) $selectedIds[0];
                }
            }

            if (!$academicYearID) {
                $academicYearID = $this->getCurrentAcademicYearID($schoolID, $requestedYear);
            }

            if (!$academicYearID) {
                return response()->json(['success' => false, 'message' => 'No academic year found. Please set an academic year first.'], 404);
            }

            // Get the year value for logging
            $academicYear = DB::table('academic_years')->where('academic_yearID', $academicYearID)->first();
            $yearValue = $academicYear ? $academicYear->year : 'N/A';

            // Get active students (optionally scoped to filtered class enrollments)
            $studentsQuery = Student::where('schoolID', $schoolID)->where('status', 'Active');
            if (!empty($filterClassID)) {
                $classStudentIDs = StudentEnrollment::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->where('classID', (int) $filterClassID)
                    ->where('status', 'Active')
                    ->pluck('studentID')
                    ->unique()
                    ->values()
                    ->all();
                $studentsQuery->whereIn('studentID', $classStudentIDs ?: [0]);
            }
            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => !empty($filterClassID)
                        ? 'No active enrolled students found for the selected class.'
                        : 'No active students found.',
                ], 404);
            }

            $generated = 0;
            $skipped = 0;
            foreach ($students as $student) {
                // Always refresh bills so new multi-account fees get their own control numbers
                $beforeCount = Payment::where('studentID', $student->studentID)
                    ->where('academic_yearID', $academicYearID)
                    ->whereNotNull('control_number')
                    ->where('control_number', '!=', '')
                    ->count();

                $this->generateStudentBill($student, $academicYearID, $schoolID);

                $afterCount = Payment::where('studentID', $student->studentID)
                    ->where('academic_yearID', $academicYearID)
                    ->whereNotNull('control_number')
                    ->where('control_number', '!=', '')
                    ->count();

                if ($afterCount > $beforeCount) {
                    $generated += ($afterCount - $beforeCount);
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $scope = !empty($filterClassID) ? 'selected class' : 'all active students';
            $msg = "Control numbers generated for {$generated} new bill(s) ({$scope}) for year {$yearValue}!";
            if ($skipped > 0) {
                $msg .= " ({$skipped} students already had control numbers)";
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
                    $messageSponsor .= "Mdhamini wa {$studentName}, Control Number: {$controlNumber}. Lipa TZS {$sponsorShareMsg} ({$sponsorPct}% ya TZS {$totalAmountForMsg}) kupitia mtandao wowote wa simu au benki yoyote.";
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
                        $messageParent .= "Mzazi wa {$studentName}, Control Number: {$controlNumber}. Jumla TZS {$totalAmountForMsg}, ulipe TZS {$parentShareMsg} (mdhamini TZS {$sponsorShareMsg}). Kuanza: TZS {$requiredAmount}. Lipa kupitia mtandao wowote wa simu au benki yoyote.";
                    } else {
                        $totalAmount = number_format($payment->amount_required ?? 0, 0);
                        $messageParent .= "Mzazi wa {$studentName}, Control Number: {$controlNumber}. Kiasi: TZS {$totalAmount}, kuanza: TZS {$requiredAmount}. Lipa kupitia mtandao wowote wa simu au benki yoyote.";
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
                $message .= "Mzazi wa {$studentName}, unakumbushwa kulipa deni la Sh {$balance} kupitia Control Number {$controlNumber}. Lipa kupitia mtandao wowote wa simu au benki yoyote.";

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

        $type              = $request->input('type', 'control_number');
        $recipientType     = $request->input('recipient_type', 'parent');
        $audience          = $request->input('audience', 'all');
        $classID           = $request->input('class_id', '');
        $subclassID        = $request->input('subclass_id', '');
        $enrollmentStatus  = $request->input('enrollment_status', $request->input('student_status', 'Active'));
        if ($enrollmentStatus === null || $enrollmentStatus === '') {
            $enrollmentStatus = 'Active';
        }
        $promotionStatus   = $request->input('promotion_status', '');
        $sponsorshipFilter = $request->input('sponsorship_filter', '');
        $feeIDFilterRaw    = $request->input('fee_id', '');
        $feeIDFilter       = ($feeIDFilterRaw !== '' && $feeIDFilterRaw !== 'all' && $feeIDFilterRaw !== null)
            ? (int) $feeIDFilterRaw
            : null;

        try {
            $resolved = $this->resolvePaymentsAcademicYear(
                (int) $schoolID,
                $request,
                $classID !== '' ? $classID : null
            );
            $academicYearID = $resolved['academicYearID'];
            $academicYearIDs = $resolved['academicYearIDs'];
            $recipients = [];

            if ($recipientType === 'sponsor') {
                $studentIDs = $this->paymentCohortStudentIDs(
                    (int) $schoolID,
                    $academicYearIDs,
                    (string) $enrollmentStatus,
                    $classID !== '' ? $classID : null,
                    $subclassID !== '' ? $subclassID : null,
                    (string) $promotionStatus
                );

                $sponsorQuery = Payment::join('students', 'payments.studentID', '=', 'students.studentID')
                    ->join('sponsors', 'students.sponsor_id', '=', 'sponsors.sponsorID')
                    ->where('payments.schoolID', $schoolID)
                    ->where('payments.academic_yearID', $academicYearID)
                    ->where('payments.sponsor_balance', '>', 0);

                if (!empty($studentIDs)) {
                    $sponsorQuery->whereIn('payments.studentID', $studentIDs);
                } else {
                    $sponsorQuery->whereRaw('1 = 0');
                }

                if ($feeIDFilter) {
                    $sponsorQuery->where('payments.feeID', $feeIDFilter);
                }

                $recipients = $sponsorQuery
                    ->select(
                        'sponsors.sponsorID',
                        'sponsors.sponsor_name as recipientLabel',
                        'sponsors.phone',
                        DB::raw('COUNT(DISTINCT payments.studentID) as studentCount'),
                        DB::raw('SUM(payments.sponsor_balance) as balance')
                    )
                    ->groupBy('sponsors.sponsorID', 'sponsors.sponsor_name', 'sponsors.phone')
                    ->get()
                    ->map(function ($item) use ($schoolID, $type, $audience) {
                        $school = School::find($schoolID);
                        $schoolName = $school->school_name ?? 'Shule';
                        $count = (int) ($item->studentCount ?? 0);
                        $totalDebt = (float) ($item->balance ?? 0);
                        $formattedAmount = number_format($totalDebt, 0);
                        $message = '';
                        if (!($school && $school->senderID)) {
                            $message .= "{$schoolName}: ";
                        }
                        if ($type === 'debt_reminder' && $audience === 'deadline_reached') {
                            $message .= "Unakumbushwa kulipa deni la wanafunzi {$count} ambalo deadline limefika, kiasi cha Sh {$formattedAmount}. Tafadhali kamilisha malipo. Ahsante.";
                        } else {
                            $message .= "Unakumbushwa kulipa deni la wanafunzi {$count} kiasi cha Sh {$formattedAmount}. Tafadhali kamilisha malipo. Ahsante.";
                        }
                        return [
                            'sponsorID' => $item->sponsorID,
                            'recipientLabel' => $item->recipientLabel,
                            'phone' => $item->phone,
                            'studentCount' => $count,
                            'balance' => $totalDebt,
                            'sms_message' => $message,
                            'type' => 'sponsor',
                        ];
                    })
                    ->values()
                    ->all();
            } else {
                $studentIDs = $this->paymentCohortStudentIDs(
                    (int) $schoolID,
                    $academicYearIDs,
                    (string) $enrollmentStatus,
                    $classID !== '' ? $classID : null,
                    $subclassID !== '' ? $subclassID : null,
                    (string) $promotionStatus
                );

                if (empty($studentIDs)) {
                    return response()->json(['success' => true, 'recipients' => [], 'count' => 0]);
                }

                $query = Payment::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->whereIn('studentID', $studentIDs)
                    ->with(['student.parent', 'student.sponsor', 'school']);

                if ($feeIDFilter) {
                    $query->where('feeID', $feeIDFilter);
                }

                if ($sponsorshipFilter === 'sponsored') {
                    $query->whereHas('student', function ($q) {
                        $q->whereNotNull('sponsor_id')->where('sponsorship_percentage', '>', 0);
                    });
                } elseif ($sponsorshipFilter === 'self') {
                    $query->whereHas('student', function ($q) {
                        $q->where(function ($inner) {
                            $inner->whereNull('sponsor_id')->orWhere('sponsorship_percentage', '<=', 0);
                        });
                    });
                }

                if ($type === 'control_number') {
                    $query->whereNotNull('control_number')->where('control_number', '!=', '');
                }

                if ($type === 'debt_reminder') {
                    $query->whereHas('student', fn ($q) => $q->where('sponsorship_percentage', '<', 100))
                        ->where('parent_balance', '>', 0);

                    if ($audience === 'deadline_reached') {
                        $query->where('required_fees_amount', '>', 0)
                            ->whereColumn('required_fees_paid', '<', 'required_fees_amount');
                    }
                }

                $payments = $query->orderBy('studentID')->orderBy('feeID')->get();

                foreach ($payments as $payment) {
                    $student = $payment->student;
                    if (!$student) {
                        continue;
                    }

                    $parent = $student->parent ?? null;
                    $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                    $deadlineRequired = (float) ($payment->required_fees_amount ?? 0);
                    $deadlinePaid = (float) ($payment->required_fees_paid ?? 0);
                    $deadlineOverdue = $deadlineRequired > 0
                        && ($deadlinePaid + 0.00001 < $deadlineRequired);

                    $preview = $type === 'control_number'
                        ? $this->buildControlNumberSmsMessage($payment)
                        : $this->buildDebtReminderSmsMessage($payment, $audience);

                    $recipients[] = [
                        'paymentID' => $payment->paymentID,
                        'recipientLabel' => 'Mzazi wa ' . $studentName,
                        'studentName' => $studentName,
                        'phone' => $parent->phone ?? '',
                        'balance' => (float) $payment->parent_balance,
                        'fee_name' => $preview['fee_name'] ?? 'Ada',
                        'control_number' => $preview['control_number'] ?? $payment->control_number,
                        'deadline_overdue' => $deadlineOverdue,
                        'deadline_required' => $deadlineRequired,
                        'deadline_paid' => $deadlinePaid,
                        'sms_message' => $preview['message'] ?? '',
                        'type' => 'parent',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'recipients' => $recipients,
                'count' => count($recipients),
                'audience' => $audience,
                'type' => $type,
            ]);

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
        $type          = $request->input('type', 'control_number');
        $recipientType = $request->input('recipient_type', 'parent');
        $year          = $request->input('year');
        $audience      = $request->input('audience', 'all');
        $customMessage = trim((string) $request->input('message', ''));

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
                $message = $customMessage !== '' ? $customMessage : '';
                if ($message === '') {
                    if (!($school && $school->senderID)) {
                        $message .= "{$schoolName}: ";
                    }
                    if ($type === 'debt_reminder' && $audience === 'deadline_reached') {
                        $message .= "Unakumbushwa kulipa deni la wanafunzi {$count} ambalo deadline limefika, kiasi cha Sh {$formattedAmount}. Tafadhali kamilisha malipo. Ahsante.";
                    } else {
                        $message .= "Unakumbushwa kulipa deni la wanafunzi {$count} kiasi cha Sh {$formattedAmount}. Tafadhali kamilisha malipo. Ahsante.";
                    }
                }
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

                if ($customMessage !== '') {
                    $message = $customMessage;
                } elseif ($type === 'debt_reminder') {
                    $preview = $this->buildDebtReminderSmsMessage($payment, $audience);
                    $message = $preview['message'];
                } else {
                    $preview = $this->buildControlNumberSmsMessage($payment);
                    $message = $preview['message'];
                }

                if (strlen($message) > 480) {
                    $message = substr($message, 0, 477) . '...';
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
    /**
     * Build Kiswahili debt-reminder SMS (deadline reached or general balance).
     */
    private function buildDebtReminderSmsMessage(Payment $payment, string $audience = 'all'): array
    {
        $student = $payment->student;
        $parent = $student ? $student->parent : null;
        $school = $payment->school ?: School::find($payment->schoolID);

        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
        $controlNumber = $payment->control_number;
        $balance = (float) $payment->parent_balance;
        $deadlineRequired = (float) $payment->required_fees_amount;
        $deadlinePaid = (float) $payment->required_fees_paid;

        $feeName = 'Ada';
        $accountLabel = null;
        if (!empty($payment->feeID)) {
            $fee = Fee::with('bankAccount.bank')->find($payment->feeID);
            if ($fee) {
                $feeName = $fee->fee_name;
                $accountLabel = $fee->bankAccount ? $fee->bankAccount->displayLabel() : null;
            }
        }

        $message = '';
        if (!($school && $school->senderID)) {
            $message .= ($school->school_name ?? 'Shule') . ': ';
        }

        if ($audience === 'deadline_reached' && $deadlineRequired > 0) {
            $shortfall = max(0, $deadlineRequired - $deadlinePaid);
            $amountStr = number_format($shortfall, 0);
            $message .= "Mzazi wa {$studentName}, muda wa malipo ya {$feeName} umefika. Unatakiwa kulipa Sh {$amountStr}";
            if (!empty($controlNumber)) {
                $message .= " kupitia Control Number {$controlNumber}";
            }
            $message .= ' kwa kutumia mtandao wowote wa simu au benki yoyote. Tafadhali lipa mapema ili kuepuka usumbufu. Ahsante.';
        } else {
            $amountStr = number_format($balance, 0);
            $message .= "Mzazi wa {$studentName}, unakumbushwa kulipa {$feeName} deni la Sh {$amountStr}";
            if (!empty($controlNumber)) {
                $message .= " kupitia Control Number {$controlNumber}";
            }
            $message .= ' kwa kutumia mtandao wowote wa simu au benki yoyote. Tafadhali kamilisha malipo. Ahsante.';
        }

        return [
            'message' => $message,
            'student_name' => $studentName,
            'parent_phone' => $parent->phone ?? null,
            'parent_name' => $parent
                ? trim(($parent->first_name ?? '') . ' ' . ($parent->last_name ?? ''))
                : null,
            'control_number' => $controlNumber,
            'fee_name' => $feeName,
            'account_label' => $accountLabel,
            'balance' => $balance,
            'deadline_required' => $deadlineRequired,
            'deadline_paid' => $deadlinePaid,
            'school_name' => $school->school_name ?? null,
        ];
    }

    /**
     * Build the control-number SMS body (shared by preview + send).
     */
    private function buildControlNumberSmsMessage(Payment $payment): array
    {
        $student = $payment->student;
        $parent = $student ? $student->parent : null;
        $school = $payment->school ?: School::find($payment->schoolID);

        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
        $controlNumber = $payment->control_number;
        $totalAmount = number_format((float) $payment->amount_required, 0);
        $requiredAmount = number_format((float) $payment->required_fees_amount, 0);

        $feeName = null;
        $accountLabel = null;
        $feeContext = '';
        if (!empty($payment->feeID)) {
            $fee = Fee::with('bankAccount.bank')->find($payment->feeID);
            if ($fee) {
                $feeName = $fee->fee_name;
                $accountLabel = $fee->bankAccount ? $fee->bankAccount->displayLabel() : null;
                $feeContext = " ya {$fee->fee_name}";
            }
        }

        $message = '';
        if (!($school && $school->senderID)) {
            $message .= ($school->school_name ?? 'School') . ': ';
        }
        $message .= "Mzazi wa {$studentName}, Control Number{$feeContext}: {$controlNumber}. Kiasi cha kuanza: Sh {$requiredAmount}, jumla: Sh {$totalAmount}. Lipa kupitia mtandao wowote wa simu au benki yoyote. Ahsante.";

        return [
            'message' => $message,
            'student_name' => $studentName,
            'parent_phone' => $parent->phone ?? null,
            'parent_name' => $parent
                ? trim(($parent->first_name ?? '') . ' ' . ($parent->last_name ?? ''))
                : null,
            'control_number' => $controlNumber,
            'fee_name' => $feeName,
            'account_label' => $accountLabel,
            'amount_required' => (float) $payment->amount_required,
            'deadline_amount' => (float) $payment->required_fees_amount,
            'school_name' => $school->school_name ?? null,
        ];
    }

    public function resend_control_number(Request $request, $paymentID)
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

            if (!$student || !$parent || !$parent->phone) {
                return response()->json(['success' => false, 'message' => 'Parent or phone number not found'], 404);
            }

            $preview = $this->buildControlNumberSmsMessage($payment);
            $customMessage = trim((string) $request->input('message', ''));

            // Preview only — do not send yet
            if ($request->boolean('preview') || $request->input('preview_only')) {
                return response()->json([
                    'success' => true,
                    'preview' => true,
                    'message' => 'SMS preview ready.',
                    'sms_sample' => $preview['message'],
                    'to' => $preview['parent_phone'],
                    'parent_name' => $preview['parent_name'],
                    'student_name' => $preview['student_name'],
                    'control_number' => $preview['control_number'],
                    'fee_name' => $preview['fee_name'],
                    'account_label' => $preview['account_label'],
                ], 200);
            }

            $messageToSend = $customMessage !== '' ? $customMessage : $preview['message'];
            $smsResult = $this->smsService->sendSms($parent->phone, $messageToSend);

            if ($smsResult['success']) {
                $payment->update(['sms_sent' => 'Yes', 'sms_sent_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Control Number resent successfully.'], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . ($smsResult['message'] ?? 'Unknown error'),
            ], 500);
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
            $message .= "Mzazi wa {$studentName}, unakumbushwa kulipa deni la Sh {$balance} kupitia Control Number {$controlNumber}. Lipa kupitia mtandao wowote wa simu au benki yoyote. Ahsante.";

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
                ->with(['fee.installments', 'fee.otherFeeDetails', 'fee.bankAccount'])
                ->get();

            // Get previous year debt for this student (only for active students)
            $previousYearDebt = ['school_fee_balance' => 0, 'other_contribution_balance' => 0];
            if ($student->status === 'Active') {
                $previousYearDebt = $this->getPreviousYearBalance($student->studentID, $schoolID);
            }

            $payment = $payments->first();
            $totalRequired = (float) $payments->sum('amount_required');
            $totalPaid = (float) $payments->sum('amount_paid');
            $totalBalance = (float) $payments->sum('balance');
            $requiredPaid = $payment ? (float) $payment->required_fees_paid : 0;
            $requiredAmount = $payment ? (float) $payment->required_fees_amount : 0;

            $prevDebtTotal = ($previousYearDebt['school_fee_balance'] ?? 0) + ($previousYearDebt['other_contribution_balance'] ?? 0);
            $billBalance = max(0, $totalBalance - $prevDebtTotal);
            $totalOutstanding = $totalBalance;

            $multiAccounts = $payments
                ->filter(fn ($p) => !empty($p->feeID))
                ->map(function ($mp) {
                    $fee = $mp->fee;
                    return [
                        'fee_name' => $fee->fee_name ?? 'Fee',
                        'account_label' => $fee && $fee->bankAccount ? $fee->bankAccount->displayLabel() : '—',
                        'control_number' => $mp->control_number,
                        'amount_required' => (float) $mp->amount_required,
                        'amount_paid' => (float) $mp->amount_paid,
                        'balance' => (float) $mp->balance,
                        'payment_status' => $mp->payment_status ?? 'Pending',
                    ];
                })
                ->values()
                ->all();

            $feeBreakdown = $payments
                ->flatMap(fn ($p) => $p->fee_payments()->orderBy('display_order')->get())
                ->values();

            // Prepare data for PDF
            $data = [
                'student' => $student,
                'school' => $school,
                'payment' => $payment,
                'feeBreakdown' => $feeBreakdown,
                'multiAccounts' => $multiAccounts,
                'totalRequired' => $totalRequired,
                'totalPaid' => $totalPaid,
                'totalBalance' => $totalBalance,
                'billBalance' => $billBalance,
                'totalOutstanding' => $totalOutstanding,
                'requiredAmount' => $requiredAmount,
                'requiredPaid' => $requiredPaid,
                'previousYearDebt' => $previousYearDebt,
                'year' => $currentYear,
            ];

            // Generate PDF - try multiple methods for compatibility
            try {
                // Method 1: Try using the facade
                $pdf = PDF::loadView('Admin.pdf.payment_invoice', $data);
                $pdf->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => base_path(),
                ]);
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

            $payment = Payment::with('academicYear')
                ->where('schoolID', $schoolID)
                ->where('paymentID', $request->paymentID)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment record not found.'], 404);
            }

            $ay = $payment->academicYear;
            $yearLabel = $ay
                ? (string) ($ay->year_name ?: $ay->year)
                : '';

            $notes = trim((string) ($request->notes ?? ''));
            if ($yearLabel !== '') {
                $yearTag = '[Academic Year: ' . $yearLabel . ']';
                if ($notes === '' || stripos($notes, '[Academic Year:') === false) {
                    $notes = trim($yearTag . ($notes !== '' ? ' ' . $notes : ''));
                }
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
                'notes' => $notes !== '' ? $notes : null,
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
                $incomeDesc = 'Student fee payment (auto-sync)';
                if ($yearLabel !== '') {
                    $incomeDesc .= ' — ' . $yearLabel;
                }
                \App\Models\Income::create([
                    'schoolID' => $payment->schoolID,
                    'receipt_number' => 'AUTO-FEE-' . $payment->paymentID . '-' . now()->format('YmdHis'),
                    'date' => $request->payment_date,
                    'income_category' => 'Tuition & Fees',
                    'description' => $incomeDesc,
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
                'payment_record' => $paymentRecord,
                'academic_year' => $yearLabel,
                'academic_year_id' => $payment->academic_yearID,
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
                $enrollmentStatus = 'Active';
            }
            $promotionStatus = $request->input('promotion_status', '');
            $paymentStatus = $request->input('payment_status', '');
            $searchStudentName = $request->input('search_student_name', '');

            $resolved = $this->resolvePaymentsAcademicYear(
                (int) $schoolID,
                $request,
                $classID !== '' ? $classID : null
            );
            $academicYearIDs = $resolved['academicYearIDs'];
            $academicYearID = $resolved['academicYearID'];
            $year = $resolved['year'];

            $studentIDs = $this->paymentCohortStudentIDs(
                (int) $schoolID,
                $academicYearIDs,
                (string) $enrollmentStatus,
                $classID !== '' ? $classID : null,
                $subclassID !== '' ? $subclassID : null,
                (string) $promotionStatus
            );

            if (empty($studentIDs)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No enrollments found for the header academic year / selected filters.',
                    'error_code' => 'no_enrollments',
                ], 422);
            }

            $enrollmentMeta = $this->paymentCohortEnrollmentMeta(
                (int) $schoolID,
                $academicYearIDs,
                $studentIDs
            );

            $query = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $studentIDs)
                ->with(['parent', 'subclass.class', 'payments' => function ($q) use ($academicYearIDs, $year) {
                    if (!empty($academicYearIDs)) {
                        $q->whereIn('academic_yearID', $academicYearIDs);
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
                $enrMeta = $enrollmentMeta[(int) $student->studentID] ?? null;
                $studentYearID = (int) ($enrMeta['academic_yearID'] ?? $academicYearID);
                $payment = $student->payments->first(function ($p) use ($studentYearID) {
                    return (int) $p->academic_yearID === (int) $studentYearID;
                }) ?: $student->payments->first();

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
                        'promotion_status' => $enrMeta['promotion_status'] ?? null,
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
            ->with('bankAccount.bank')
            ->orderBy('display_order')
            ->get()
            ->each(function ($fee) {
                $fee->account_display = $fee->bankAccount
                    ? $fee->bankAccount->displayLabel()
                    : null;
            });
            
        return response()->json([
            'success' => true,
            'fees' => $fees
        ]);
    }

    public function get_fee_installments($feeID)
    {
        $schoolID = (int) Session::get('schoolID');
        $fee = Fee::where('schoolID', $schoolID)
            ->where('feeID', (int) $feeID)
            ->where('status', 'Active')
            ->first();
        if (!$fee) {
            return response()->json(['success' => false, 'message' => 'Fee not found.'], 404);
        }

        if (empty(app(SelectedAcademicYearService::class)->selectedYearIds($schoolID))) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }
        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, (int) $fee->classID);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        $installments = FeeInstallment::where('feeID', $fee->feeID)
            ->where('academic_yearID', $academicYearID)
            ->where('status', 'Active')
            ->orderBy('installment_number')
            ->get();

        return response()->json([
            'success' => true,
            'academic_year_id' => $academicYearID,
            'fee' => [
                'feeID' => $fee->feeID,
                'fee_name' => $fee->fee_name,
                'amount' => (float) $fee->amount,
                'account_mode' => $fee->account_mode ?? 'single',
            ],
            'installments' => $installments,
        ]);
    }

    public function store_fee_installments(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $validator = Validator::make($request->all(), [
            'classID' => 'required|integer',
            'feeID' => 'required|integer',
            'installment_type' => 'required|in:Quarter,Term,Month',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $classID = (int) $request->classID;
        $fee = Fee::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('feeID', (int) $request->feeID)
            ->where('status', 'Active')
            ->first();
        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'The selected fee does not belong to this school and class.',
            ], 422);
        }

        if (empty(app(SelectedAcademicYearService::class)->selectedYearIds($schoolID))) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }
        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, $classID);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        $counts = ['Quarter' => 4, 'Term' => 2, 'Month' => 12];
        $count = $counts[$request->installment_type];
        $totalCents = (int) round(((float) $fee->amount) * 100);
        $baseCents = intdiv($totalCents, $count);
        $remainingCents = $totalCents - ($baseCents * $count);

        DB::beginTransaction();
        try {
            FeeInstallment::where('feeID', $fee->feeID)
                ->where('academic_yearID', $academicYearID)
                ->delete();

            $created = [];
            for ($i = 1; $i <= $count; $i++) {
                $amountCents = $baseCents + ($i === $count ? $remainingCents : 0);
                $created[] = FeeInstallment::create([
                    'feeID' => $fee->feeID,
                    'academic_yearID' => $academicYearID,
                    'installment_name' => $request->installment_type . ' ' . $i,
                    'installment_type' => $request->installment_type,
                    'installment_number' => $i,
                    'amount' => $amountCents / 100,
                    'status' => 'Active',
                ]);
            }

            $fee->update([
                'allow_installments' => true,
                'default_installment_type' => $request->installment_type,
                'number_of_installments' => $count,
                'allow_partial_payment' => true,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} {$request->installment_type} installments saved for the selected academic year.",
                'academic_year_id' => $academicYearID,
                'installments' => $created,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Store fee installments failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save installments.',
            ], 500);
        }
    }

    public function get_class_fee_deadlines($classID)
    {
        $schoolID = (int) Session::get('schoolID');
        $classExists = ClassModel::where('schoolID', $schoolID)
            ->where('classID', (int) $classID)
            ->exists();
        if (!$classExists) {
            return response()->json(['success' => false, 'message' => 'Class not found.'], 404);
        }

        if (empty(app(SelectedAcademicYearService::class)->selectedYearIds($schoolID))) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }
        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, (int) $classID);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        $deadlines = FeeDeadline::where('schoolID', $schoolID)
            ->where('academic_yearID', $academicYearID)
            ->where('status', 'Active')
            ->whereHas('fee', fn ($query) => $query->where('classID', (int) $classID))
            ->orderBy('feeID')
            ->orderBy('start_date')
            ->get();
        $academicYear = AcademicYear::find($academicYearID);

        return response()->json([
            'success' => true,
            'academic_year_id' => $academicYearID,
            'academic_year' => $academicYear ? [
                'year_name' => $academicYear->year_name ?: $academicYear->year,
                'start_date' => optional($academicYear->start_date)->format('Y-m-d'),
                'end_date' => optional($academicYear->end_date)->format('Y-m-d'),
            ] : null,
            'deadlines' => $deadlines,
        ]);
    }

    private function resolveDeadlineMonthRange(AcademicYear $academicYear, int $startMonth, int $endMonth): ?array
    {
        if (!$academicYear->start_date || !$academicYear->end_date) {
            return null;
        }

        $yearStart = $academicYear->start_date->copy()->startOfDay();
        $yearEnd = $academicYear->end_date->copy()->endOfDay();
        $cursor = $yearStart->copy()->startOfMonth();
        $startDate = null;
        $endDate = null;

        while ($cursor->lte($yearEnd)) {
            if ($startDate === null && (int) $cursor->month === $startMonth) {
                $startDate = $cursor->copy()->startOfMonth()->max($yearStart);
            }
            if ((int) $cursor->month === $endMonth) {
                $candidate = $cursor->copy()->endOfMonth()->min($yearEnd);
                if ($startDate !== null && $candidate->gte($startDate)) {
                    $endDate = $candidate;
                    break;
                }
            }
            $cursor->addMonth();
        }

        if (!$startDate || !$endDate || $endDate->lt($startDate)) {
            return null;
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    private function resolveDeadlineDateRange(
        AcademicYear $academicYear,
        int $startMonth,
        int $startDay,
        int $endMonth,
        int $endDay
    ): ?array {
        if (!$academicYear->start_date || !$academicYear->end_date) {
            return null;
        }

        $yearStart = $academicYear->start_date->copy()->startOfDay();
        $yearEnd = $academicYear->end_date->copy()->endOfDay();
        $cursor = $yearStart->copy()->startOfMonth();
        $startDate = null;
        $endDate = null;

        while ($cursor->lte($yearEnd)) {
            if ($startDate === null && (int) $cursor->month === $startMonth) {
                try {
                    $candidate = $cursor->copy()->day($startDay)->startOfDay();
                    if ((int) $candidate->month === $startMonth && $candidate->betweenIncluded($yearStart, $yearEnd)) {
                        $startDate = $candidate;
                    }
                } catch (\Throwable $e) {
                    // Invalid calendar day for the selected month.
                }
            }

            if ($startDate !== null && (int) $cursor->month === $endMonth) {
                try {
                    $candidate = $cursor->copy()->day($endDay)->startOfDay();
                    if (
                        (int) $candidate->month === $endMonth
                        && $candidate->betweenIncluded($yearStart, $yearEnd)
                        && $candidate->gte($startDate)
                    ) {
                        $endDate = $candidate;
                        break;
                    }
                } catch (\Throwable $e) {
                    // Invalid calendar day for the selected month.
                }
            }

            $cursor->addMonth();
        }

        if (!$startDate || !$endDate) {
            return null;
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    public function store_fee_deadlines(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $validator = Validator::make($request->all(), [
            'classID' => 'required|integer',
            'feeID' => 'required|integer',
            'deadlines' => 'present|array',
            'deadlines.*.deadline_type' => 'required|in:date,month',
            'deadlines.*.start_date' => 'nullable|date',
            'deadlines.*.end_date' => 'nullable|date',
            'deadlines.*.start_month' => 'nullable|integer|min:1|max:12',
            'deadlines.*.end_month' => 'nullable|integer|min:1|max:12',
            'deadlines.*.start_day' => 'nullable|integer|min:1|max:31',
            'deadlines.*.end_day' => 'nullable|integer|min:1|max:31',
            'deadlines.*.amount_type' => 'required|in:fixed,percent',
            'deadlines.*.amount' => 'required|numeric|min:0.01',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $classID = (int) $request->classID;
        if (empty(app(SelectedAcademicYearService::class)->selectedYearIds($schoolID))) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }
        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, $classID);
        if (!$academicYearID) {
            return response()->json([
                'success' => false,
                'message' => 'Select an academic year in the header first.',
                'error_code' => 'no_academic_year',
            ], 422);
        }

        $fee = Fee::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('feeID', (int) $request->feeID)
            ->where('status', 'Active')
            ->first();
        if (!$fee) {
            return response()->json([
                'success' => false,
                'message' => 'The selected fee is invalid for this class.',
            ], 422);
        }

        $academicYear = AcademicYear::find($academicYearID);
        if (!$academicYear) {
            return response()->json(['success' => false, 'message' => 'Academic year not found.'], 422);
        }

        $normalized = collect($request->deadlines)->map(function ($row) use ($academicYear) {
            $type = $row['deadline_type'];
            $amountType = $row['amount_type'] ?? FeeDeadline::AMOUNT_FIXED;
            $amount = (float) $row['amount'];

            if ($amountType === FeeDeadline::AMOUNT_PERCENT && $amount > 100) {
                return ['error' => 'Percent deadlines cannot exceed 100%.'];
            }

            if ($type === 'month') {
                $range = $this->resolveDeadlineMonthRange(
                    $academicYear,
                    (int) ($row['start_month'] ?? 0),
                    (int) ($row['end_month'] ?? 0)
                );
                if (!$range) {
                    return ['error' => 'Selected month range is outside or conflicts with the academic year.'];
                }
                $row['start_date'] = $range['start_date'];
                $row['end_date'] = $range['end_date'];
            } else {
                $range = $this->resolveDeadlineDateRange(
                    $academicYear,
                    (int) ($row['start_month'] ?? 0),
                    (int) ($row['start_day'] ?? 0),
                    (int) ($row['end_month'] ?? 0),
                    (int) ($row['end_day'] ?? 0)
                );
                if (!$range) {
                    return ['error' => 'Select valid month and day values within the header academic year.'];
                }
                $row['start_date'] = $range['start_date'];
                $row['end_date'] = $range['end_date'];
            }
            $row['amount_type'] = $amountType;
            return $row;
        });
        $normalizationError = $normalized->first(fn ($row) => isset($row['error']));
        if ($normalizationError) {
            return response()->json(['success' => false, 'message' => $normalizationError['error']], 422);
        }

        $sorted = $normalized->sortBy('start_date')->values();
        $previousEnd = null;
        $fixedTotal = 0.0;
        $percentTotal = 0.0;
        foreach ($sorted as $row) {
            $start = Carbon::parse($row['start_date']);
            $end = Carbon::parse($row['end_date']);
            $amount = (float) $row['amount'];
            $amountType = $row['amount_type'] ?? FeeDeadline::AMOUNT_FIXED;
            if ($end->lt($start)) {
                return response()->json([
                    'success' => false,
                    'message' => "Deadline end date must be on or after its start date for {$fee->fee_name}.",
                ], 422);
            }
            if ($previousEnd && $start->lte($previousEnd)) {
                return response()->json([
                    'success' => false,
                    'message' => "Deadline dates overlap for {$fee->fee_name}. Start the next deadline after {$previousEnd->format('d M Y')}.",
                ], 422);
            }
            if ($amountType === FeeDeadline::AMOUNT_PERCENT) {
                $percentTotal += $amount;
                if ($percentTotal > 100) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Total percent deadlines cannot exceed 100%. Current total: '
                            . number_format($percentTotal, 2) . '%. '
                            . 'Example: 70% then 30% (completes 100%), not 70% then 40%.',
                    ], 422);
                }
            } else {
                $fixedTotal += $amount;
                if ($fixedTotal > (float) $fee->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Total fixed deadline amounts (TZS ' . number_format($fixedTotal, 2)
                            . ") exceed the fee total (TZS " . number_format((float) $fee->amount, 2)
                            . ") for {$fee->fee_name}.",
                    ], 422);
                }
            }
            $previousEnd = $end;
        }

        if ($percentTotal > 0 && abs($percentTotal - 100) > 0.009) {
            return response()->json([
                'success' => false,
                'message' => 'Percent deadlines must add up to exactly 100%. Current total: '
                    . number_format($percentTotal, 2) . '%. '
                    . 'Add or adjust the remaining ' . number_format(100 - $percentTotal, 2) . '%.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            FeeDeadline::where('schoolID', $schoolID)
                ->where('academic_yearID', $academicYearID)
                ->where('feeID', $fee->feeID)
                ->delete();

            $created = [];
            foreach ($normalized as $row) {
                $created[] = FeeDeadline::create([
                    'schoolID' => $schoolID,
                    'academic_yearID' => $academicYearID,
                    'feeID' => $fee->feeID,
                    'deadline_type' => $row['deadline_type'],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'start_month' => $row['deadline_type'] === 'month' ? (int) $row['start_month'] : null,
                    'end_month' => $row['deadline_type'] === 'month' ? (int) $row['end_month'] : null,
                    'amount_type' => $row['amount_type'] ?? FeeDeadline::AMOUNT_FIXED,
                    'amount' => $row['amount'],
                    'status' => 'Active',
                ]);
            }

            // Keep legacy fee display fields in sync with the next upcoming/current deadline.
            $next = collect($created)->sortBy('end_date')->first();
            $legacyAmount = null;
            if ($next) {
                if (($next->amount_type ?? 'fixed') === 'percent') {
                    $legacyAmount = round(((float) $fee->amount) * ((float) $next->amount / 100), 2);
                } else {
                    $legacyAmount = $next->amount;
                }
            }
            $fee->update([
                'payment_deadline_amount' => $legacyAmount,
                'payment_deadline_date' => $next?->end_date,
            ]);

            DB::commit();

            // Refresh year bills so required_fees_amount reflects newly saved deadlines.
            $this->generateBillsForClass($classID, $schoolID);

            return response()->json([
                'success' => true,
                'message' => count($created) . ' deadline(s) saved and student bills refreshed for the selected academic year.',
                'academic_year_id' => $academicYearID,
                'deadlines' => $created,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Store fee deadlines failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save fee deadlines.',
            ], 500);
        }
    }

    public function delete_fee_deadline($deadlineID)
    {
        $schoolID = (int) Session::get('schoolID');
        $deadline = FeeDeadline::where('schoolID', $schoolID)
            ->where('deadlineID', (int) $deadlineID)
            ->first();
        if (!$deadline) {
            return response()->json(['success' => false, 'message' => 'Deadline not found.'], 404);
        }

        $fee = Fee::where('schoolID', $schoolID)->where('feeID', $deadline->feeID)->first();
        $classID = (int) ($fee->classID ?? 0);
        $academicYearID = (int) $deadline->academic_yearID;

        DB::beginTransaction();
        try {
            $deadline->delete();
            if ($fee) {
                $next = FeeDeadline::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->where('feeID', $fee->feeID)
                    ->where('status', 'Active')
                    ->orderBy('end_date')
                    ->first();
                $fee->update([
                    'payment_deadline_amount' => $next?->amount,
                    'payment_deadline_date' => $next?->end_date,
                ]);
            }
            DB::commit();

            if ($classID) {
                $this->generateBillsForClass($classID, $schoolID);
            }
            return response()->json([
                'success' => true,
                'message' => 'Deadline deleted and student bills refreshed.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Delete fee deadline failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete deadline.'], 500);
        }
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
        $schoolID = (int) Session::get('schoolID');
        $agreements = $request->agreements ?? [];

        if (empty($agreements)) {
            return response()->json(['success' => false, 'message' => 'No agreements provided'], 400);
        }

        $classID = (int) $request->input('classID');
        if (!$classID && !empty($agreements[0]['studentID'])) {
            $enrollment = app(StudentEnrollmentService::class)
                ->currentEnrollment((int) $agreements[0]['studentID'], $schoolID);
            $classID = (int) ($enrollment->classID ?? 0);
        }

        $academicYearID = $this->resolveFeesAcademicYearID($schoolID, $classID ?: null);
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
            $savedCount = 0;
            $clearedCount = 0;

            foreach ($agreements as $ag) {
                $studentID = (int) ($ag['studentID'] ?? 0);
                $feeID = (int) ($ag['feeID'] ?? 0);
                $clear = !empty($ag['clear']) || $ag['amount'] === '' || $ag['amount'] === null;
                $enrollment = $enrollmentService->currentEnrollment($studentID, $schoolID);
                if (
                    !$enrollment ||
                    $enrollment->status !== 'Active' ||
                    (int) $enrollment->classID !== $classID
                ) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Active enrollments for the selected academic year can receive fee agreements.',
                        'error_code' => 'enrollment_not_active',
                    ], 422);
                }

                $fee = Fee::where('schoolID', $schoolID)
                    ->where('classID', $classID)
                    ->where('feeID', $feeID)
                    ->where('status', 'Active')
                    ->first();
                if (!$fee) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'One of the selected fees is invalid for this class.',
                    ], 422);
                }

                if ($clear) {
                    FeeAgreement::where([
                        'schoolID' => $schoolID,
                        'studentID' => $studentID,
                        'feeID' => $feeID,
                        'academic_yearID' => $academicYearID,
                    ])->update(['status' => 'Inactive']);
                    $clearedCount++;
                } else {
                    $agreedAmount = (float) $ag['amount'];
                    if ($agreedAmount < 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Agreed amounts cannot be negative.',
                        ], 422);
                    }

                    FeeAgreement::updateOrCreate(
                        [
                            'schoolID' => $schoolID,
                            'studentID' => $studentID,
                            'feeID' => $feeID,
                            'academic_yearID' => $academicYearID,
                        ],
                        [
                            'agreed_amount' => $agreedAmount,
                            'status' => 'Active'
                        ]
                    );
                    $savedCount++;
                }

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
                'message' => "Agreements updated ({$savedCount} saved, {$clearedCount} cleared) and bills refreshed.",
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

            $selectedStudentIDs = array_map('intval', $studentIDs);

            foreach ($conditionIDs as $conditionID) {
                $condition = FeeCondition::where('schoolID', $schoolID)
                    ->where('conditionID', $conditionID)
                    ->where('status', 'Active')
                    ->whereHas('fee', function ($query) use ($classID, $schoolID) {
                        $query->where('schoolID', $schoolID)
                            ->where('classID', $classID)
                            ->where('status', 'Active');
                    })
                    ->first();
                if (!$condition) continue;

                // Unassign students who were previously on this condition but are no longer selected.
                $toUnassign = StudentFeeCondition::where('schoolID', $schoolID)
                    ->where('conditionID', $conditionID)
                    ->where('feeID', $condition->feeID)
                    ->where('academic_yearID', $academicYearID)
                    ->where('status', 'Active')
                    ->when(!empty($selectedStudentIDs), fn ($q) => $q->whereNotIn('studentID', $selectedStudentIDs))
                    ->when(empty($selectedStudentIDs), fn ($q) => $q)
                    ->get();
                foreach ($toUnassign as $assignment) {
                    $assignment->update(['status' => 'Inactive']);
                    $affectedStudentIDs[] = $assignment->studentID;
                }

                foreach ($selectedStudentIDs as $studentID) {
                    $enrollment = $enrollmentService->currentEnrollment((int) $studentID, $schoolID);
                    if (
                        !$enrollment ||
                        $enrollment->status !== 'Active' ||
                        (int) $enrollment->classID !== (int) $classID
                    ) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Active enrollments for the selected academic year can receive fee discounts.',
                            'error_code' => 'enrollment_not_active',
                        ], 422);
                    }

                    $existing = StudentFeeCondition::where([
                        'studentID' => $studentID,
                        'feeID' => $condition->feeID,
                        'academic_yearID' => $academicYearID,
                    ])->first();

                    if (
                        !$existing ||
                        (int) $existing->conditionID !== (int) $conditionID ||
                        $existing->status !== 'Active'
                    ) {
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

    // ─── School bank accounts (Admin only) + OTP ────────────────────────────

    private function ensureSchoolAdmin()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if ($userType !== 'Admin' || !$schoolID) {
            return [null, null, response()->json(['success' => false, 'message' => 'Only school Admin can manage bank accounts.'], 403)];
        }
        return [$userType, (int) $schoolID, null];
    }

    public function list_active_banks()
    {
        [, , $deny] = $this->ensureSchoolAdmin();
        if ($deny) {
            return $deny;
        }

        $banks = Bank::active()->orderBy('bank_name')->get(['bankID', 'bank_name', 'bank_code']);
        return response()->json(['success' => true, 'banks' => $banks]);
    }

    public function list_school_bank_accounts()
    {
        [, $schoolID, $deny] = $this->ensureSchoolAdmin();
        if ($deny) {
            return $deny;
        }

        $accounts = SchoolBankAccount::with('bank')
            ->where('schoolID', $schoolID)
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('accountID')
            ->get()
            ->map(function ($acc) {
                return [
                    'accountID' => $acc->accountID,
                    'bankID' => $acc->bankID,
                    'account_number' => $acc->account_number,
                    'account_name' => $acc->account_name,
                    'status' => $acc->status,
                    'rejection_reason' => $acc->rejection_reason,
                    'otp_verified_at' => optional($acc->otp_verified_at)->format('Y-m-d H:i'),
                    'bank_name' => $acc->bank->bank_name ?? '—',
                    'bank_code' => $acc->bank->bank_code ?? '',
                    'display_label' => $acc->displayLabel(),
                ];
            });

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    public function list_approved_school_bank_accounts()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');
        if (!$userType || !$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $accounts = SchoolBankAccount::with('bank')
            ->where('schoolID', $schoolID)
            ->approved()
            ->orderBy('account_number')
            ->get()
            ->map(fn ($acc) => [
                'accountID' => $acc->accountID,
                'display_label' => $acc->displayLabel(),
                'account_number' => $acc->account_number,
                'bank_code' => $acc->bank->bank_code ?? ($acc->bank->bank_name ?? ''),
            ]);

        return response()->json(['success' => true, 'accounts' => $accounts]);
    }

    public function send_bank_account_otp(Request $request)
    {
        [, $schoolID, $deny] = $this->ensureSchoolAdmin();
        if ($deny) {
            return $deny;
        }

        $validator = Validator::make($request->all(), [
            'bankID' => 'required|exists:banks,bankID',
            'account_number' => 'required|string|max:100',
            'account_name' => 'nullable|string|max:200',
            'accountID' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $bank = Bank::active()->find($request->bankID);
        if (!$bank) {
            return response()->json(['success' => false, 'message' => 'Selected bank is not active.'], 422);
        }

        $school = School::find($schoolID);
        if (!$school || empty($school->phone)) {
            return response()->json(['success' => false, 'message' => 'School phone number is not configured. Update school contact first.'], 422);
        }

        $accountNumber = trim($request->account_number);
        $existsQuery = SchoolBankAccount::where('schoolID', $schoolID)
            ->where('bankID', $request->bankID)
            ->where('account_number', $accountNumber);
        if ($request->accountID) {
            $existsQuery->where('accountID', '!=', (int) $request->accountID);
        }
        if ($existsQuery->exists()) {
            return response()->json(['success' => false, 'message' => 'This account number is already registered for the selected bank.'], 422);
        }

        $otp = (string) random_int(100000, 999999);
        $token = (string) Str::uuid();
        Session::put('bank_account_otp', [
            'token' => $token,
            'code_hash' => Hash::make($otp),
            'expires_at' => time() + (10 * 60),
            'schoolID' => $schoolID,
            'bankID' => (int) $request->bankID,
            'account_number' => $accountNumber,
            'account_name' => $request->account_name ? trim($request->account_name) : null,
            'accountID' => $request->accountID ? (int) $request->accountID : null,
        ]);

        $bankLabel = $bank->bank_code ?: $bank->bank_name;
        $msg = "ShuleXpert OTP: {$otp}. This code authorizes {$bankLabel} account {$accountNumber} to receive school fee payments. SECURITY: Protect school funds - never share this OTP. If you did not request this, contact support immediately. Expires in 10 minutes.";
        $smsResult = $this->smsService->sendSms($school->phone, $msg, $school->senderID);
        if (is_array($smsResult) && isset($smsResult['success']) && !$smsResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP SMS: ' . ($smsResult['message'] ?? 'Unknown error'),
            ], 500);
        }

        $digits = preg_replace('/\D+/', '', (string) $school->phone);
        $masked = strlen($digits) > 5
            ? substr($digits, 0, 6) . '****' . substr($digits, -2)
            : $digits;

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to school phone ' . $masked,
            'otp_token' => $token,
            'masked_phone' => $masked,
        ]);
    }

    public function verify_bank_account_otp(Request $request)
    {
        [, $schoolID, $deny] = $this->ensureSchoolAdmin();
        if ($deny) {
            return $deny;
        }

        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|min:4|max:10',
            'otp_token' => 'required|string|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $pending = Session::get('bank_account_otp');
        if (!$pending || ($pending['schoolID'] ?? null) != $schoolID) {
            return response()->json(['success' => false, 'message' => 'OTP session expired. Please request a new OTP.'], 401);
        }
        if ((string) ($pending['token'] ?? '') !== (string) $request->otp_token) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP session.'], 401);
        }
        if (empty($pending['expires_at']) || time() > (int) $pending['expires_at']) {
            Session::forget('bank_account_otp');
            return response()->json(['success' => false, 'message' => 'OTP expired. Please request a new OTP.'], 401);
        }
        if (!Hash::check(preg_replace('/\s+/', '', (string) $request->otp), (string) $pending['code_hash'])) {
            return response()->json(['success' => false, 'message' => 'Incorrect OTP.'], 401);
        }

        try {
            DB::beginTransaction();

            if (!empty($pending['accountID'])) {
                $account = SchoolBankAccount::where('schoolID', $schoolID)
                    ->where('accountID', $pending['accountID'])
                    ->first();
                if (!$account) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
                }
                $account->update([
                    'bankID' => $pending['bankID'],
                    'account_number' => $pending['account_number'],
                    'account_name' => $pending['account_name'],
                    'status' => SchoolBankAccount::STATUS_PENDING,
                    'rejection_reason' => null,
                    'otp_verified_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);
            } else {
                $account = SchoolBankAccount::create([
                    'schoolID' => $schoolID,
                    'bankID' => $pending['bankID'],
                    'account_number' => $pending['account_number'],
                    'account_name' => $pending['account_name'],
                    'status' => SchoolBankAccount::STATUS_PENDING,
                    'otp_verified_at' => now(),
                ]);
            }

            Session::forget('bank_account_otp');
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account registered successfully and is pending Super Admin approval.',
                'account' => [
                    'accountID' => $account->accountID,
                    'status' => $account->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bank account OTP verify failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save account: ' . $e->getMessage()], 500);
        }
    }

    public function update_school_bank_account(Request $request, $accountID)
    {
        [, $schoolID, $deny] = $this->ensureSchoolAdmin();
        if ($deny) {
            return $deny;
        }

        $account = SchoolBankAccount::where('schoolID', $schoolID)->where('accountID', $accountID)->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        // Editing requires OTP re-verification via send/verify endpoints.
        // This endpoint only deletes pending drafts without OTP if needed — keep update via OTP flow.
        return response()->json([
            'success' => true,
            'message' => 'To edit this account, use Register Account Number tab with OTP verification.',
            'account' => [
                'accountID' => $account->accountID,
                'bankID' => $account->bankID,
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'status' => $account->status,
            ],
        ]);
    }

    public function delete_school_bank_account($accountID)
    {
        [, $schoolID, $deny] = $this->ensureSchoolAdmin();
        if ($deny) {
            return $deny;
        }

        $account = SchoolBankAccount::where('schoolID', $schoolID)->where('accountID', $accountID)->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        if (Fee::where('bank_account_id', $account->accountID)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account linked to fee structures. Unlink fees first.',
            ], 422);
        }

        $account->delete();

        return response()->json(['success' => true, 'message' => 'Bank account deleted successfully.']);
    }
}
