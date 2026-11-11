<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\ParentModel;
use App\Models\PendingStudentRegistration;
use App\Models\School;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PendingStudentRegistrationController extends Controller
{
    private function schoolId()
    {
        return Session::get('schoolID');
    }

    private function authorizeStaff(?Request $request = null)
    {
        $user = Session::get('user_type');
        if (!$user || !in_array($user, ['Admin', 'Staff'], true)) {
            if ($request && ($request->ajax() || $request->expectsJson() || $request->wantsJson())) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        return null;
    }

    private function normalizePayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'pendingID', 'middle_name', 'gender', 'date_of_birth', 'previous_level', 'previous_school',
            'intended_classID', 'intended_subclassID', 'intended_academic_yearID',
            'parent_first_name', 'parent_middle_name', 'parent_last_name', 'parent_gender',
            'parent_phone', 'parent_email', 'parent_relationship', 'phone', 'notes', 'subclassID',
            'pay_amount', 'payment_reference', 'payment_method',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    public function index()
    {
        if ($redirect = $this->authorizeStaff()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        $pending = PendingStudentRegistration::with(['intendedClass', 'intendedSubclass', 'intendedAcademicYear'])
            ->where('schoolID', $schoolID)
            ->orderByRaw("CASE status WHEN 'Pending' THEN 1 WHEN 'Paid' THEN 2 WHEN 'Confirmed' THEN 3 ELSE 4 END")
            ->orderByDesc('pendingID')
            ->get();

        $classes = ClassModel::where('schoolID', $schoolID)
            ->with(['subclasses' => function ($q) {
                $q->orderBy('subclass_name');
            }])
            ->orderBy('class_name')
            ->get();

        $years = AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->get();

        return view('Admin.pending_student_registrations', [
            'pending' => $pending,
            'classes' => $classes,
            'years' => $years,
            'pendingCount' => $pending->where('status', 'Pending')->count(),
            'paidCount' => $pending->where('status', 'Paid')->count(),
            'confirmedCount' => $pending->where('status', 'Confirmed')->count(),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizePayload($request), [
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
                'date_of_birth' => 'nullable|date',
                'previous_level' => 'nullable|string|max:50',
                'previous_school' => 'nullable|string|max:200',
                'intended_classID' => 'nullable|integer',
                'intended_subclassID' => 'nullable|integer',
                'intended_academic_yearID' => 'nullable|integer',
                'parent_first_name' => 'nullable|string|max:100',
                'parent_middle_name' => 'nullable|string|max:100',
                'parent_last_name' => 'nullable|string|max:100',
                'parent_gender' => 'nullable|in:Male,Female,Other',
                'parent_phone' => 'nullable|string|max:40',
                'parent_email' => 'nullable|email|max:150',
                'parent_relationship' => 'nullable|string|max:50',
                'phone' => 'nullable|string|max:40',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            if (!$this->ownsClassSubclassYear($schoolID, $validated)) {
                return response()->json(['success' => false, 'message' => 'Invalid class or academic year.'], 422);
            }

            $row = PendingStudentRegistration::create(array_merge($validated, [
                'schoolID' => $schoolID,
                'status' => 'Pending',
                'created_by' => Session::get('userID') ?? Session::get('staffID'),
                'created_by_name' => Session::get('user_name') ?? Session::get('fullname') ?? Session::get('user_type'),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Pending registration saved. This does not affect enrolled student counts.',
                'pending' => ['pendingID' => $row->pendingID, 'full_name' => $row->full_name],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not save: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizePayload($request), [
                'pendingID' => 'required|integer',
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
                'date_of_birth' => 'nullable|date',
                'previous_level' => 'nullable|string|max:50',
                'previous_school' => 'nullable|string|max:200',
                'intended_classID' => 'nullable|integer',
                'intended_subclassID' => 'nullable|integer',
                'intended_academic_yearID' => 'nullable|integer',
                'parent_first_name' => 'nullable|string|max:100',
                'parent_middle_name' => 'nullable|string|max:100',
                'parent_last_name' => 'nullable|string|max:100',
                'parent_gender' => 'nullable|in:Male,Female,Other',
                'parent_phone' => 'nullable|string|max:40',
                'parent_email' => 'nullable|email|max:150',
                'parent_relationship' => 'nullable|string|max:50',
                'phone' => 'nullable|string|max:40',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            $row = PendingStudentRegistration::where('schoolID', $schoolID)
                ->where('pendingID', $validated['pendingID'])
                ->firstOrFail();

            if (!in_array($row->status, ['Pending', 'Paid'], true)) {
                return response()->json(['success' => false, 'message' => 'Only pending/paid records can be edited.'], 422);
            }

            if (!$this->ownsClassSubclassYear($schoolID, $validated)) {
                return response()->json(['success' => false, 'message' => 'Invalid class or academic year.'], 422);
            }

            // Keep payment/status intact — payment is recorded on its own step.
            $row->update(collect($validated)->except('pendingID')->all());

            return response()->json([
                'success' => true,
                'message' => 'Pending registration updated.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizePayload($request), [
            'pendingID' => 'required|integer',
        ])->validate();

        $row = PendingStudentRegistration::where('schoolID', $schoolID)
            ->where('pendingID', $validated['pendingID'])
            ->firstOrFail();

        if ($row->status === 'Confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Confirmed records cannot be deleted. The student is already enrolled.',
            ], 422);
        }

        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pending registration removed.',
        ]);
    }

    public function downloadTemplate()
    {
        if ($redirect = $this->authorizeStaff()) {
            return $redirect;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pending Students');

        $headers = [
            'A1' => 'first_name',
            'B1' => 'middle_name',
            'C1' => 'last_name',
            'D1' => 'gender',
            'E1' => 'date_of_birth',
            'F1' => 'previous_level',
            'G1' => 'previous_school',
            'H1' => 'intended_class_name',
            'I1' => 'intended_subclass_name',
            'J1' => 'parent_first_name',
            'K1' => 'parent_last_name',
            'L1' => 'parent_gender',
            'M1' => 'parent_phone',
            'N1' => 'parent_email',
            'O1' => 'parent_relationship',
            'P1' => 'phone',
            'Q1' => 'notes',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Q1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('940000');
        $sheet->getStyle('A1:Q1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:Q1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Example row
        $sheet->setCellValue('A2', 'John');
        $sheet->setCellValue('B2', 'Juma');
        $sheet->setCellValue('C2', 'Mdoe');
        $sheet->setCellValue('D2', 'Male');
        $sheet->setCellValue('E2', '2012-05-15');
        $sheet->setCellValue('F2', 'Std 7');
        $sheet->setCellValue('G2', 'Example Primary');
        $sheet->setCellValue('H2', 'Form 1');
        $sheet->setCellValue('I2', 'A');
        $sheet->setCellValue('J2', 'Mary');
        $sheet->setCellValue('K2', 'Mdoe');
        $sheet->setCellValue('L2', 'Female');
        $sheet->setCellValue('M2', '2557XXXXXXXX');
        $sheet->setCellValue('N2', '');
        $sheet->setCellValue('O2', 'Mother');
        $sheet->setCellValue('P2', '');
        $sheet->setCellValue('Q2', 'Ready for next intake');

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->setCellValue('A1', 'Pending Student Registration - Import Template');
        $instructions->setCellValue('A3', '1. Fill student rows on the "Pending Students" sheet.');
        $instructions->setCellValue('A4', '2. Required columns: first_name, last_name.');
        $instructions->setCellValue('A5', '3. gender: Male, Female, or Other.');
        $instructions->setCellValue('A6', '4. date_of_birth format: YYYY-MM-DD (e.g. 2012-05-15).');
        $instructions->setCellValue('A7', '5. previous_level examples: Std 7, Form 4, Other.');
        $instructions->setCellValue('A8', '6. intended_class_name / intended_subclass_name must match existing school classes (optional).');
        $instructions->setCellValue('A9', '7. Imported students stay PENDING - they do NOT count as enrolled students until Confirm.');
        $instructions->setCellValue('A10', '8. Delete the example row before importing.');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $instructions->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'pending_student_registration_template.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 'pending_xlsx_');
        (new Xlsx($spreadsheet))->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheetByName('Pending Students') ?: $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) < 2) {
                return response()->json(['success' => false, 'message' => 'The file has no data rows.'], 422);
            }

            $headerRow = array_shift($rows);
            $map = [];
            foreach ($headerRow as $col => $label) {
                $key = strtolower(trim((string) $label));
                $key = str_replace([' ', '-'], '_', $key);
                if ($key !== '') {
                    $map[$key] = $col;
                }
            }

            if (!isset($map['first_name']) || !isset($map['last_name'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template must include first_name and last_name columns.',
                ], 422);
            }

            $classes = ClassModel::where('schoolID', $schoolID)->with('subclasses')->get();
            $created = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $get = function (string $field) use ($map, $row) {
                    if (!isset($map[$field])) {
                        return null;
                    }
                    $val = $row[$map[$field]] ?? null;
                    if (is_string($val)) {
                        $val = trim($val);
                    }
                    return ($val === '' || $val === null) ? null : $val;
                };

                $first = $get('first_name');
                $last = $get('last_name');
                if (!$first || !$last) {
                    $skipped++;
                    continue;
                }

                // Skip example-looking empty duplicates of template sample if identical note
                $className = $get('intended_class_name');
                $subclassName = $get('intended_subclass_name');
                $classID = null;
                $subclassID = null;
                if ($className) {
                    $class = $classes->first(function ($c) use ($className) {
                        return strcasecmp(trim($c->class_name), trim($className)) === 0;
                    });
                    if ($class) {
                        $classID = $class->classID;
                        if ($subclassName) {
                            $sub = $class->subclasses->first(function ($s) use ($subclassName) {
                                return strcasecmp(trim((string) $s->subclass_name), trim($subclassName)) === 0;
                            });
                            if ($sub) {
                                $subclassID = $sub->subclassID;
                            } else {
                                $errors[] = "Row {$line}: subclass \"{$subclassName}\" not found under {$className}.";
                            }
                        } else {
                            $firstSub = $class->subclasses->first();
                            $subclassID = $firstSub?->subclassID;
                        }
                    } else {
                        $errors[] = "Row {$line}: class \"{$className}\" not found.";
                    }
                }

                $gender = $get('gender');
                if ($gender && !in_array($gender, ['Male', 'Female', 'Other'], true)) {
                    $gender = null;
                }

                $dob = $get('date_of_birth');
                if ($dob && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dob)) {
                    // Excel serial or other format - try parse
                    try {
                        $dob = \Carbon\Carbon::parse($dob)->toDateString();
                    } catch (\Throwable $e) {
                        $dob = null;
                        $errors[] = "Row {$line}: invalid date_of_birth.";
                    }
                }

                PendingStudentRegistration::create([
                    'schoolID' => $schoolID,
                    'first_name' => $first,
                    'middle_name' => $get('middle_name'),
                    'last_name' => $last,
                    'gender' => $gender,
                    'date_of_birth' => $dob,
                    'previous_level' => $get('previous_level'),
                    'previous_school' => $get('previous_school'),
                    'intended_classID' => $classID,
                    'intended_subclassID' => $subclassID,
                    'parent_first_name' => $get('parent_first_name'),
                    'parent_last_name' => $get('parent_last_name'),
                    'parent_gender' => in_array($get('parent_gender'), ['Male', 'Female', 'Other'], true) ? $get('parent_gender') : null,
                    'parent_phone' => $get('parent_phone'),
                    'parent_email' => $get('parent_email'),
                    'parent_relationship' => $get('parent_relationship'),
                    'phone' => $get('phone'),
                    'notes' => $get('notes'),
                    'status' => 'Pending',
                    'created_by' => Session::get('userID') ?? Session::get('staffID'),
                    'created_by_name' => Session::get('user_name') ?? Session::get('fullname') ?? Session::get('user_type'),
                ]);
                $created++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Imported {$created} pending student(s). Skipped {$skipped} empty row(s). They are NOT enrolled yet.",
                'created' => $created,
                'skipped' => $skipped,
                'warnings' => $errors,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function recordPayment(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizePayload($request), [
                'pendingID' => 'required|integer',
                'pay_amount' => 'required|numeric|min:0.01',
                'payment_reference' => 'nullable|string|max:120',
                'payment_method' => 'nullable|string|max:40',
            ])->validate();

            $row = PendingStudentRegistration::where('schoolID', $schoolID)
                ->where('pendingID', $validated['pendingID'])
                ->firstOrFail();

            if (!in_array($row->status, ['Pending', 'Paid'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment can only be recorded before enrollment.',
                ], 422);
            }

            $payment = $this->applyPaymentFields([
                'pay_amount' => $validated['pay_amount'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
            ]);

            $row->update(array_merge($payment, [
                'status' => 'Paid',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded. Student is marked Paid but not enrolled yet.',
                'status' => 'Paid',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not record payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function confirmBatch(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizePayload($request), [
            'pendingIDs' => 'required|array|min:1',
            'pendingIDs.*' => 'integer',
            'subclassID' => 'required|integer',
        ])->validate();

        $ok = 0;
        $fail = [];
        foreach ($validated['pendingIDs'] as $id) {
            try {
                $result = $this->confirmPendingRecord(
                    (int) $schoolID,
                    (int) $id,
                    (int) $validated['subclassID']
                );
                if ($result['success']) {
                    $ok++;
                } else {
                    $fail[] = ['pendingID' => $id, 'message' => $result['message']];
                }
            } catch (\Throwable $e) {
                $fail[] = ['pendingID' => $id, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => $ok > 0,
            'message' => "Enrolled {$ok} student(s)." . (count($fail) ? ' ' . count($fail) . ' failed.' : ''),
            'confirmed' => $ok,
            'failed' => $fail,
        ]);
    }

    public function confirm(Request $request)
    {
        if ($redirect = $this->authorizeStaff($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizePayload($request), [
                'pendingID' => 'required|integer',
                'subclassID' => 'required|integer',
            ])->validate();

            $result = $this->confirmPendingRecord(
                (int) $schoolID,
                (int) $validated['pendingID'],
                (int) $validated['subclassID']
            );

            $status = $result['success'] ? 200 : 422;

            return response()->json($result, $status);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function confirmPendingRecord(int $schoolID, int $pendingID, int $subclassID): array
    {
        $pending = PendingStudentRegistration::where('schoolID', $schoolID)
            ->where('pendingID', $pendingID)
            ->first();

        if (!$pending) {
            return ['success' => false, 'message' => 'Pending record not found.'];
        }

        if (!in_array($pending->status, ['Pending', 'Paid'], true)) {
            return ['success' => false, 'message' => 'This record is already enrolled or cannot be enrolled.'];
        }

        $subclass = Subclass::with('class')
            ->where('subclassID', $subclassID)
            ->whereHas('class', fn ($q) => $q->where('schoolID', $schoolID))
            ->first();

        if (!$subclass) {
            return ['success' => false, 'message' => 'Invalid subclass selected.'];
        }

        DB::beginTransaction();
        try {
            $parentID = null;
            if ($pending->parent_phone || ($pending->parent_first_name && $pending->parent_last_name)) {
                $parent = null;
                if ($pending->parent_phone) {
                    $parent = ParentModel::where('schoolID', $schoolID)
                        ->where('phone', $pending->parent_phone)
                        ->first();
                }
                if (!$parent && $pending->parent_first_name) {
                    $parent = ParentModel::create([
                        'schoolID' => $schoolID,
                        'first_name' => $pending->parent_first_name,
                        'middle_name' => $pending->parent_middle_name,
                        'last_name' => $pending->parent_last_name ?: $pending->last_name,
                        'gender' => $pending->parent_gender ?: 'Female',
                        'phone' => $pending->parent_phone,
                        'email' => $pending->parent_email,
                    ]);
                }
                $parentID = $parent?->parentID;
            }

            $admissionNumber = $this->generateAdmissionNumber($schoolID);
            $fingerprintId = $this->generateFingerprintId($schoolID);

            $studentData = [
                'studentID' => (int) $fingerprintId,
                'schoolID' => $schoolID,
                'subclassID' => null,
                'parentID' => $parentID,
                'first_name' => $pending->first_name,
                'middle_name' => $pending->middle_name,
                'last_name' => $pending->last_name,
                'gender' => $pending->gender ?: 'Male',
                'admission_number' => $admissionNumber,
                'fingerprint_id' => $fingerprintId,
                'sent_to_device' => false,
                'admission_date' => now()->toDateString(),
                'status' => 'Active',
                'nationality' => 'Tanzanian',
                'registration_status' => 'Completed',
            ];

            if (\Schema::hasColumn('students', 'date_of_birth') && $pending->date_of_birth) {
                $studentData['date_of_birth'] = $pending->date_of_birth;
            }
            if (\Schema::hasColumn('students', 'phone') && $pending->phone) {
                $studentData['phone'] = $pending->phone;
            }

            $student = Student::create($studentData);

            $userEmail = $admissionNumber . '@student.local';
            $emailCounter = 1;
            while (User::where('email', $userEmail)->exists()) {
                $userEmail = $admissionNumber . '_' . $emailCounter . '@student.local';
                $emailCounter++;
            }

            User::create([
                'name' => $admissionNumber,
                'email' => $userEmail,
                'password' => Hash::make($pending->first_name),
                'user_type' => 'student',
                'fingerprint_id' => $fingerprintId,
            ]);

            app(StudentEnrollmentService::class)->enrollOnRegistration(
                $student,
                $schoolID,
                $subclass,
                'Confirmed from pending registration #' . $pending->pendingID
            );

            $confirmPayload = [
                'status' => 'Confirmed',
                'confirmed_studentID' => $student->studentID,
                'confirmed_at' => now(),
                'intended_classID' => $subclass->classID,
                'intended_subclassID' => $subclass->subclassID,
            ];

            $payNote = $this->formatPaymentNote($pending);
            if ($payNote !== '') {
                $existingNotes = trim((string) ($pending->notes ?? ''));
                $confirmPayload['notes'] = $existingNotes !== ''
                    ? ($existingNotes . "\n" . $payNote)
                    : $payNote;
            }

            $pending->update($confirmPayload);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Student enrolled successfully.',
                'admission_number' => $admissionNumber,
                'studentID' => $student->studentID,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function applyPaymentFields(array $data): array
    {
        $amount = array_key_exists('pay_amount', $data) && $data['pay_amount'] !== null && $data['pay_amount'] !== ''
            ? round((float) $data['pay_amount'], 2)
            : null;

        return [
            'pay_amount' => $amount,
            'payment_reference' => isset($data['payment_reference']) && trim((string) $data['payment_reference']) !== ''
                ? trim((string) $data['payment_reference'])
                : null,
            'payment_method' => isset($data['payment_method']) && trim((string) $data['payment_method']) !== ''
                ? trim((string) $data['payment_method'])
                : null,
            'payment_recorded_at' => now(),
        ];
    }

    private function formatPaymentNote(PendingStudentRegistration $pending): string
    {
        if ($pending->pay_amount === null && !$pending->payment_reference && !$pending->payment_method) {
            return '';
        }

        $parts = ['Pre-admission payment recorded'];
        if ($pending->pay_amount !== null) {
            $parts[] = 'amount: ' . number_format((float) $pending->pay_amount, 2);
        }
        if ($pending->payment_method) {
            $parts[] = 'method: ' . $pending->payment_method;
        }
        if ($pending->payment_reference) {
            $parts[] = 'ref: ' . $pending->payment_reference;
        }

        return implode(' | ', $parts);
    }

    private function ownsClassSubclassYear(int $schoolID, array $validated): bool
    {
        if (!empty($validated['intended_classID'])) {
            if (!ClassModel::where('schoolID', $schoolID)->where('classID', $validated['intended_classID'])->exists()) {
                return false;
            }
        }
        if (!empty($validated['intended_subclassID'])) {
            $ok = Subclass::where('subclassID', $validated['intended_subclassID'])
                ->whereHas('class', fn ($q) => $q->where('schoolID', $schoolID))
                ->exists();
            if (!$ok) {
                return false;
            }
        }
        if (!empty($validated['intended_academic_yearID'])) {
            if (!AcademicYear::where('schoolID', $schoolID)->where('academic_yearID', $validated['intended_academic_yearID'])->exists()) {
                return false;
            }
        }

        return true;
    }

    private function generateAdmissionNumber(int $schoolID): string
    {
        $school = School::find($schoolID);
        $currentYear = date('Y');
        $schoolNumber = $school->registration_number ?? ('SCH' . $schoolID);
        $prefix = $schoolNumber . '/';
        $suffix = '/' . $currentYear;

        $lastAdmissionNumber = Student::where('schoolID', $schoolID)
            ->where('admission_number', 'like', $prefix . '%' . $suffix)
            ->lockForUpdate()
            ->orderBy('admission_number', 'desc')
            ->value('admission_number');

        $sequence = 1;
        if ($lastAdmissionNumber && preg_match('/\/(\d+)\/' . $currentYear . '$/', $lastAdmissionNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $schoolNumber . '/' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT) . '/' . $currentYear;
    }

    private function generateFingerprintId(int $schoolID): string
    {
        $attempts = 0;
        do {
            $attempts++;
            $fingerprintId = (string) random_int(100000, 999999999);
            $exists = Student::where('fingerprint_id', $fingerprintId)->exists()
                || Student::where('studentID', $fingerprintId)->exists();
            if (!$exists) {
                return $fingerprintId;
            }
        } while ($attempts < 100);

        throw new \RuntimeException('Unable to generate unique student ID.');
    }
}
