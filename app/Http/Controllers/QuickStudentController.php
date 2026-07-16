<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ParentModel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subclass;
use App\Models\User;
use App\Services\SelectedAcademicYearService;
use App\Services\SmsService;
use App\Support\ParentPhone;
use App\Support\ParentUserAccount;
use App\Support\SafeErrorMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Quick student registration (no enrollment) + enroll / unenroll helpers.
 * Does not replace the full registration flow.
 */
class QuickStudentController extends Controller
{
    private function hasPermission(string $permissionName): bool
    {
        $userType = Session::get('user_type');

        if ($userType === 'Admin') {
            return true;
        }

        if ($userType === 'Teacher') {
            $teacherID = Session::get('teacherID');
            if (!$teacherID) {
                return false;
            }
            $roleIds = DB::table('teachers')
                ->join('role_user', 'teachers.id', '=', 'role_user.teacher_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.teacher_id', $teacherID)
                ->pluck('roles.id')
                ->all();
            if (empty($roleIds)) {
                return false;
            }
            return DB::table('permissions')
                ->whereIn('role_id', $roleIds)
                ->where('name', $permissionName)
                ->exists();
        }

        if ($userType === 'Staff') {
            $staffID = Session::get('staffID');
            if (!$staffID) {
                return false;
            }
            $professionId = DB::table('other_staff')->where('id', $staffID)->value('profession_id');
            if (!$professionId) {
                return false;
            }
            return DB::table('staff_permissions')
                ->where('profession_id', $professionId)
                ->where('name', $permissionName)
                ->exists();
        }

        return false;
    }

    private function canCreateStudents(): bool
    {
        return $this->hasPermission('student_create')
            || $this->hasPermission('register_students');
    }

    private function canUpdateStudents(): bool
    {
        return $this->hasPermission('student_update')
            || $this->hasPermission('edit_student')
            || $this->canCreateStudents();
    }

    /**
     * Class teacher may enroll into their own subclass even without global student_create.
     */
    private function teacherOwnsSubclass(int $subclassID): bool
    {
        $teacherID = Session::get('teacherID');
        if (!$teacherID) {
            return false;
        }
        return Subclass::where('subclassID', $subclassID)
            ->where('teacherID', $teacherID)
            ->exists();
    }

    private function resolveUserImagesPath(): string
    {
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : public_path();
        $uploadPath = $scriptDir . '/userImages';

        if (!is_dir($uploadPath) && !@mkdir($uploadPath, 0777, true)) {
            $docRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/userImages' : null;
            if ($docRootPath && (is_dir($docRootPath) || @mkdir($docRootPath, 0777, true))) {
                $uploadPath = $docRootPath;
            } else {
                $uploadPath = public_path('userImages');
            }
        }

        if (!file_exists($uploadPath)) {
            @mkdir($uploadPath, 0755, true);
        }

        return $uploadPath;
    }

    private function storeUploadedImage($file): ?string
    {
        if (!$file) {
            return null;
        }
        $uploadPath = $this->resolveUserImagesPath();
        $imageName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $file->move($uploadPath, $imageName);
        return $imageName;
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

    /**
     * Current (is_current) academic year for class O/A level — not header selection.
     */
    private function resolveCurrentYearIdForClass(?string $className, int $schoolID): ?int
    {
        $yearService = app(SelectedAcademicYearService::class);
        $isA = $yearService->isALevelClassName($className);
        $years = AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get();

        if ($years->isEmpty()) {
            return null;
        }

        $pool = $years->filter(function ($y) use ($yearService, $isA) {
            return $isA ? $yearService->isALevelYear($y) : !$yearService->isALevelYear($y);
        })->values();

        if ($pool->isEmpty()) {
            $pool = $years;
        }

        $pick = $pool->firstWhere('is_current', true) ?: $pool->first();

        return $pick ? (int) $pick->academic_yearID : null;
    }

    private function studentPhotoUrl(?Student $student): ?string
    {
        if (!$student) {
            return null;
        }
        return \App\Support\UserImageUrl::forGender($student->photo, $student->gender);
    }

    /**
     * Quick register: minimal student + parent, NO enrollment.
     */
    public function quickRegister(Request $request)
    {
        if (!$this->canCreateStudents()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to register students.',
            ], 403);
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female',
            'student_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'parent_id' => 'nullable|exists:parents,parentID',
            'parent_phone' => 'nullable|string|max:20',
            'parent_first_name' => 'nullable|string|max:100',
            'parent_middle_name' => 'nullable|string|max:100',
            'parent_last_name' => 'nullable|string|max:100',
            'parent_gender' => 'nullable|in:Male,Female',
            'parent_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0];
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        try {
            DB::beginTransaction();

            $parent = null;
            $isNewParent = false;
            $parentID = $request->filled('parent_id') ? (int) $request->parent_id : null;

            if ($parentID) {
                $parent = ParentModel::where('parentID', $parentID)
                    ->where('schoolID', $schoolID)
                    ->first();
                if (!$parent) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected parent not found in this school.',
                    ], 404);
                }
            } else {
                $phone = ParentPhone::normalize($request->parent_phone);
                if (!$phone) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Parent phone is required. Search an existing parent or enter a new phone.',
                        'errors' => ['parent_phone' => 'Parent phone is required.'],
                    ], 422);
                }

                $parent = ParentPhone::findInSchool((int) $schoolID, $phone);
                if (!$parent) {
                    $inOther = ParentPhone::findAnywhere($phone, (int) $schoolID);
                    if ($inOther) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'This phone number already exists in another school. Please try another number.',
                            'errors' => ['parent_phone' => 'Phone already used in another school.'],
                        ], 422);
                    }

                    if (!$request->parent_first_name || !$request->parent_last_name || !$request->parent_gender) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'New parent requires first name, last name and gender.',
                            'errors' => [
                                'parent_first_name' => !$request->parent_first_name ? 'Required' : null,
                                'parent_last_name' => !$request->parent_last_name ? 'Required' : null,
                                'parent_gender' => !$request->parent_gender ? 'Required' : null,
                            ],
                        ], 422);
                    }

                    $parentPhoto = $this->storeUploadedImage($request->file('parent_photo'));
                    $parent = ParentModel::create([
                        'schoolID' => $schoolID,
                        'first_name' => $request->parent_first_name,
                        'middle_name' => $request->parent_middle_name ?: null,
                        'last_name' => $request->parent_last_name,
                        'gender' => $request->parent_gender,
                        'phone' => $phone,
                        'photo' => $parentPhoto,
                    ]);
                    $isNewParent = true;
                }
            }

            $parentID = (int) $parent->parentID;

            try {
                ParentUserAccount::ensure($parent);
            } catch (\Throwable $e) {
                Log::warning('Quick register parent login skipped: ' . $e->getMessage(), [
                    'parentID' => $parentID,
                ]);
            }

            do {
                $admissionNumber = $this->generateAdmissionNumber((int) $schoolID);
            } while (
                Student::where('admission_number', $admissionNumber)->exists() ||
                User::where('name', $admissionNumber)->exists()
            );

            $fingerprintId = null;
            $attempts = 0;
            do {
                $fingerprintId = (string) rand(1000, 9999);
                $attempts++;
                $exists = User::where('fingerprint_id', $fingerprintId)->exists()
                    || Student::where('fingerprint_id', $fingerprintId)->exists()
                    || Student::where('studentID', $fingerprintId)->exists();
                if (!$exists) {
                    break;
                }
                if ($attempts >= 100) {
                    throw new \RuntimeException('Unable to generate unique student ID.');
                }
            } while (true);

            $studentPhoto = $this->storeUploadedImage($request->file('student_photo'));

            $student = Student::create([
                'studentID' => (int) $fingerprintId,
                'schoolID' => $schoolID,
                'subclassID' => null,
                'parentID' => $parentID,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?: null,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'admission_number' => $admissionNumber,
                'fingerprint_id' => $fingerprintId,
                'sent_to_device' => false,
                'device_sent_at' => null,
                'fingerprint_capture_count' => 0,
                'admission_date' => now()->toDateString(),
                'photo' => $studentPhoto,
                'status' => 'Active',
                'nationality' => 'Tanzanian',
                'registration_status' => 'Draft',
            ]);

            $userEmail = $admissionNumber . '@student.local';
            $emailCounter = 1;
            while (User::where('email', $userEmail)->exists()) {
                $userEmail = $admissionNumber . '_' . $emailCounter . '@student.local';
                $emailCounter++;
            }

            User::create([
                'name' => $admissionNumber,
                'email' => $userEmail,
                'password' => Hash::make($request->first_name),
                'user_type' => 'student',
                'fingerprint_id' => $fingerprintId,
            ]);

            $smsSent = false;
            $parentSmsSent = false;
            // SMS only when a NEW parent is created — never when reusing an existing parent
            if ($isNewParent && $parent && $parent->phone) {
                try {
                    $school = School::find($schoolID);
                    $smsService = new SmsService();
                    $parentSms = $smsService->sendParentCredentials(
                        $parent->phone,
                        $school->school_name ?? 'School',
                        $parent->phone,
                        $parent->first_name
                    );
                    $parentSmsSent = $parentSms['success'] ?? false;
                    $smsSent = $parentSmsSent;
                } catch (\Throwable $e) {
                    Log::warning('Quick register parent SMS skipped: ' . $e->getMessage());
                }
            }

            DB::commit();

            $message = 'Student quick-registered successfully. Enroll them into a class when ready.';
            if ($parentSmsSent) {
                $message .= ' Parent account credentials sent by SMS.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'admission_number' => $admissionNumber,
                'student_id' => $student->studentID,
                'fingerprint_id' => $fingerprintId,
                'is_new_parent' => $isNewParent,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Quick register failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => SafeErrorMessage::from($e, 'Failed to quick-register student.'),
            ], 500);
        }
    }

    /**
     * Students available for enrollment (unenrolled + enrolled elsewhere for reassignment).
     */
    public function listForEnroll(Request $request)
    {
        if (!$this->canUpdateStudents() && Session::get('user_type') !== 'Teacher') {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
        }

        $search = trim((string) $request->input('search', ''));
        $unenrolledOnly = $request->boolean('unenrolled_only', false);

        $query = Student::with(['parent'])
            ->where('schoolID', $schoolID)
            ->where('status', 'Active');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('admission_number', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("CONCAT(COALESCE(first_name,''),' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')) LIKE ?", [$like]);
            });
        }

        $students = $query->orderBy('first_name')->orderBy('last_name')->limit(500)->get();

        $activeEnrollments = StudentEnrollment::with(['classModel', 'subclass'])
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereIn('studentID', $students->pluck('studentID')->all())
            ->orderByDesc('enrollmentID')
            ->get()
            ->unique('studentID')
            ->keyBy('studentID');

        $rows = [];
        foreach ($students as $student) {
            $enrollment = $activeEnrollments->get($student->studentID);
            $isEnrolled = (bool) $enrollment;

            if ($unenrolledOnly && $isEnrolled) {
                continue;
            }

            $classLabel = null;
            if ($enrollment) {
                $className = $enrollment->classModel->class_name ?? '';
                $subName = $enrollment->subclass->subclass_name ?? '';
                $classLabel = trim($className . ($subName !== '' ? ' ' . $subName : '')) ?: 'Enrolled';
            }

            $rows[] = [
                'studentID' => $student->studentID,
                'admission_number' => $student->admission_number,
                'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                'gender' => $student->gender,
                'photo' => $this->studentPhotoUrl($student),
                'is_enrolled' => $isEnrolled,
                'current_class' => $classLabel,
                'current_enrollmentID' => $enrollment->enrollmentID ?? null,
                'current_subclassID' => $enrollment->subclassID ?? null,
                'parent_name' => $student->parent
                    ? trim($student->parent->first_name . ' ' . ($student->parent->last_name ?? ''))
                    : null,
                'parent_phone' => $student->parent->phone ?? null,
            ];
        }

        // Unenrolled first, then enrolled
        usort($rows, function ($a, $b) {
            if ($a['is_enrolled'] === $b['is_enrolled']) {
                return strcasecmp($a['full_name'], $b['full_name']);
            }
            return $a['is_enrolled'] ? 1 : -1;
        });

        return response()->json([
            'success' => true,
            'students' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * Bulk enroll selected students into a subclass (current academic year).
     * If already Active elsewhere, old Active enrollment is deleted (not Move).
     */
    public function enrollStudents(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
        }

        $validator = Validator::make($request->all(), [
            'subclassID' => 'required|exists:subclasses,subclassID',
            'studentIDs' => 'required|array|min:1',
            'studentIDs.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $subclassID = (int) $request->subclassID;
        $subclass = Subclass::with('class')->find($subclassID);
        if (!$subclass || (int) ($subclass->class->schoolID ?? 0) !== (int) $schoolID) {
            return response()->json(['success' => false, 'message' => 'Invalid subclass.'], 403);
        }

        $userType = Session::get('user_type');
        $allowed = $this->canUpdateStudents();
        if (!$allowed && $userType === 'Teacher' && $this->teacherOwnsSubclass($subclassID)) {
            $allowed = true;
        }
        if (!$allowed) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $yearID = $this->resolveCurrentYearIdForClass($subclass->class->class_name ?? null, (int) $schoolID);
        if (!$yearID) {
            return response()->json([
                'success' => false,
                'message' => 'No current academic year found. Set a current academic year first.',
            ], 422);
        }

        $classLabel = trim(($subclass->class->class_name ?? '') . ' ' . ($subclass->subclass_name ?? ''));
        $studentIDs = array_values(array_unique(array_map('intval', $request->studentIDs)));
        $movedFromOther = [];
        $enrolled = 0;

        try {
            DB::beginTransaction();

            $students = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $studentIDs)
                ->get()
                ->keyBy('studentID');

            foreach ($studentIDs as $sid) {
                $student = $students->get($sid);
                if (!$student) {
                    continue;
                }

                $existingActive = StudentEnrollment::where('schoolID', $schoolID)
                    ->where('studentID', $sid)
                    ->where('status', 'Active')
                    ->get();

                $alreadyHere = $existingActive->contains(function ($old) use ($subclassID) {
                    return (int) $old->subclassID === $subclassID;
                });
                if ($alreadyHere) {
                    continue;
                }

                foreach ($existingActive as $old) {
                    $old->loadMissing(['classModel', 'subclass']);
                    $fromClass = trim(($old->classModel->class_name ?? '') . ' ' . ($old->subclass->subclass_name ?? ''));
                    $movedFromOther[] = [
                        'studentID' => $sid,
                        'name' => trim($student->first_name . ' ' . $student->last_name),
                        'from' => $fromClass ?: 'another class',
                        'to' => $classLabel,
                    ];
                    $old->delete();
                }

                StudentEnrollment::create([
                    'studentID' => $sid,
                    'schoolID' => $schoolID,
                    'academic_yearID' => $yearID,
                    'classID' => (int) $subclass->classID,
                    'subclassID' => $subclassID,
                    'roll_number' => null,
                    'status' => 'Active',
                    'promotion_status' => 'Pending',
                    'joined_date' => now()->toDateString(),
                    'left_date' => null,
                    'notes' => 'Enrolled via Quick Enroll',
                ]);

                $student->subclassID = $subclassID;
                $student->save();
                $enrolled++;
            }

            DB::commit();

            $message = $enrolled . ' student(s) enrolled into ' . trim($classLabel) . '.';
            if (count($movedFromOther) > 0) {
                $message .= ' Note: ' . count($movedFromOther) . ' already had an Active enrollment elsewhere — that enrollment was removed (not archived like Move).';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'enrolled_count' => $enrolled,
                'reassigned' => $movedFromOther,
                'class_label' => trim($classLabel),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Enroll students failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => SafeErrorMessage::from($e, 'Failed to enroll students.'),
            ], 500);
        }
    }

    /**
     * Active enrollments for a subclass (Enrolled tab).
     */
    public function listEnrolled(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
        }

        $subclassID = (int) $request->input('subclassID', 0);
        if (!$subclassID) {
            return response()->json(['success' => false, 'message' => 'subclassID is required'], 422);
        }

        $subclass = Subclass::with('class')->find($subclassID);
        if (!$subclass || (int) ($subclass->class->schoolID ?? 0) !== (int) $schoolID) {
            return response()->json(['success' => false, 'message' => 'Invalid subclass.'], 403);
        }

        $userType = Session::get('user_type');
        $allowed = $this->canUpdateStudents() || $this->hasPermission('student_read_only') || $this->hasPermission('view_students');
        if (!$allowed && $userType === 'Teacher' && $this->teacherOwnsSubclass($subclassID)) {
            $allowed = true;
        }
        if (!$allowed) {
            return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
        }

        $search = trim((string) $request->input('search', ''));

        $query = StudentEnrollment::with(['student.parent', 'classModel', 'subclass'])
            ->where('schoolID', $schoolID)
            ->where('subclassID', $subclassID)
            ->where('status', 'Active');

        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->whereHas('student', function ($q) use ($like) {
                $q->where('admission_number', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like);
            });
        }

        $enrollments = $query->orderByDesc('enrollmentID')->get();

        $rows = $enrollments->map(function ($enr) {
            $s = $enr->student;
            if (!$s) {
                return null;
            }
            return [
                'enrollmentID' => $enr->enrollmentID,
                'studentID' => $s->studentID,
                'admission_number' => $s->admission_number,
                'full_name' => trim($s->first_name . ' ' . ($s->middle_name ? $s->middle_name . ' ' : '') . $s->last_name),
                'gender' => $s->gender,
                'photo' => $this->studentPhotoUrl($s),
                'joined_date' => $enr->joined_date ? $enr->joined_date->format('Y-m-d') : null,
            ];
        })->filter()->values();

        $classLabel = trim(($subclass->class->class_name ?? '') . ' ' . ($subclass->subclass_name ?? ''));

        return response()->json([
            'success' => true,
            'class_label' => $classLabel,
            'students' => $rows,
            'total' => $rows->count(),
        ]);
    }

    /**
     * Remove Active enrollment permanently (student becomes free — no history kept).
     */
    public function removeEnrollment(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
        }

        $validator = Validator::make($request->all(), [
            'enrollmentIDs' => 'required|array|min:1',
            'enrollmentIDs.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $ids = array_values(array_unique(array_map('intval', $request->enrollmentIDs)));
        $enrollments = StudentEnrollment::with('subclass')
            ->where('schoolID', $schoolID)
            ->whereIn('enrollmentID', $ids)
            ->get();

        if ($enrollments->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No enrollments found.'], 404);
        }

        $userType = Session::get('user_type');
        foreach ($enrollments as $enr) {
            $allowed = $this->canUpdateStudents();
            if (!$allowed && $userType === 'Teacher' && $this->teacherOwnsSubclass((int) $enr->subclassID)) {
                $allowed = true;
            }
            if (!$allowed) {
                return response()->json(['success' => false, 'message' => 'Permission denied for one or more enrollments.'], 403);
            }
        }

        try {
            DB::beginTransaction();
            $removed = 0;
            foreach ($enrollments as $enr) {
                $studentID = (int) $enr->studentID;
                $subclassID = (int) $enr->subclassID;
                $enr->delete();
                $removed++;

                $latest = StudentEnrollment::where('schoolID', $schoolID)
                    ->where('studentID', $studentID)
                    ->where('status', 'Active')
                    ->orderByDesc('enrollmentID')
                    ->first();

                Student::where('studentID', $studentID)
                    ->where('schoolID', $schoolID)
                    ->update(['subclassID' => $latest ? $latest->subclassID : null]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $removed . ' student(s) removed from enrollment. They are free to enroll again. No class history was kept for this removal.',
                'removed_count' => $removed,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Remove enrollment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => SafeErrorMessage::from($e, 'Failed to remove enrollment.'),
            ], 500);
        }
    }
}
