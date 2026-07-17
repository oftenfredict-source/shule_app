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
     * Students available for enrollment (not enrolled first; enrolled elsewhere for reassignment).
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
        $unenrolledOnly = $request->boolean('unenrolled_only', true);
        $limit = min(max((int) $request->input('limit', 3000), 50), 5000);

        $yearService = app(SelectedAcademicYearService::class);
        $yearIDs = array_map('intval', $yearService->selectedYearIds((int) $schoolID));

        $buildBaseQuery = function () use ($schoolID, $search) {
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

            return $query;
        };

        $unenrolledQuery = $buildBaseQuery();
        $this->applyNotEnrolledStudentScope($unenrolledQuery, (int) $schoolID, $yearIDs);
        $unenrolledStudents = $unenrolledQuery
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit($limit)
            ->get();

        $enrolledStudents = collect();
        if (!$unenrolledOnly) {
            $remaining = max(0, $limit - $unenrolledStudents->count());
            if ($remaining > 0) {
                $enrolledQuery = $buildBaseQuery();
                $enrolledQuery->whereExists(function ($q) use ($schoolID, $yearIDs) {
                    $q->select(DB::raw(1))
                        ->from('student_enrollments')
                        ->whereColumn('student_enrollments.studentID', 'students.studentID')
                        ->where('student_enrollments.schoolID', $schoolID)
                        ->where('student_enrollments.status', 'Active');
                    if (!empty($yearIDs)) {
                        $q->whereIn('student_enrollments.academic_yearID', $yearIDs);
                    }
                });
                if ($unenrolledStudents->isNotEmpty()) {
                    $enrolledQuery->whereNotIn('studentID', $unenrolledStudents->pluck('studentID')->all());
                }
                $enrolledStudents = $enrolledQuery
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->limit($remaining)
                    ->get();
            }
        }

        $students = $unenrolledStudents->concat($enrolledStudents);

        $activeEnrollments = StudentEnrollment::with(['classModel', 'subclass'])
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereIn('studentID', $students->pluck('studentID')->all())
            ->when(!empty($yearIDs), function ($q) use ($yearIDs) {
                $q->whereIn('academic_yearID', $yearIDs);
            })
            ->orderByDesc('enrollmentID')
            ->get()
            ->unique('studentID')
            ->keyBy('studentID');

        $rows = [];
        $notEnrolledCount = 0;
        foreach ($students as $student) {
            $enrollment = $activeEnrollments->get($student->studentID);
            $isEnrolled = (bool) $enrollment;

            if (!$isEnrolled) {
                $notEnrolledCount++;
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
            'not_enrolled_count' => $notEnrolledCount,
        ]);
    }

    /**
     * Students with no Active enrollment in the selected academic year(s).
     */
    private function applyNotEnrolledStudentScope($query, int $schoolID, array $yearIDs): void
    {
        if (!StudentEnrollment::where('schoolID', $schoolID)->exists()) {
            $query->whereNull('subclassID');
            return;
        }

        $query->whereNotExists(function ($q) use ($schoolID, $yearIDs) {
            $q->select(DB::raw(1))
                ->from('student_enrollments')
                ->whereColumn('student_enrollments.studentID', 'students.studentID')
                ->where('student_enrollments.schoolID', $schoolID)
                ->where('student_enrollments.status', 'Active');

            if (!empty($yearIDs)) {
                $q->whereIn('student_enrollments.academic_yearID', $yearIDs);
            }
        });
    }

    /**
     * Current logged-in actor for enrollment audit (who enrolled / closed).
     *
     * @return array{id: ?int, name: string, type: string, label: string}
     */
    private function resolveCurrentActor(): array
    {
        $userType = (string) (Session::get('user_type') ?? 'User');
        $userId = Session::get('userID') ?: Session::get('teacherID') ?: Session::get('staffID');
        $name = trim((string) (
            Session::get('teacher_name')
            ?: Session::get('staff_name')
            ?: Session::get('user_name')
            ?: 'Unknown'
        ));

        return [
            'id' => $userId ? (int) $userId : null,
            'name' => $name,
            'type' => $userType,
            'label' => $userType . ': ' . $name,
        ];
    }

    /**
     * Close an Active enrollment (preserve history — never delete).
     */
    private function closeActiveEnrollment(
        StudentEnrollment $enrollment,
        string $newStatus,
        ?string $promotionStatus = null,
        ?string $note = null,
        ?array $actor = null
    ): void {
        $actor = $actor ?? $this->resolveCurrentActor();
        $enrollment->status = $newStatus;
        $enrollment->left_date = now()->toDateString();
        if ($promotionStatus !== null) {
            $enrollment->promotion_status = $promotionStatus;
        }
        $enrollment->closed_by = $actor['id'];
        $enrollment->closed_by_name = $actor['label'];
        if ($note !== null && $note !== '') {
            $withBy = rtrim($note, '.') . ' by ' . $actor['label'];
            $enrollment->notes = trim(
                ($enrollment->notes ? $enrollment->notes . ' | ' : '') . $withBy
            );
        }
        $enrollment->save();
    }

    /**
     * Sync students.subclassID from latest Active enrollment (or null).
     */
    private function syncStudentSubclassFromActive(int $schoolID, int $studentID): void
    {
        $latest = StudentEnrollment::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->where('status', 'Active')
            ->orderByDesc('enrollmentID')
            ->first();

        Student::where('studentID', $studentID)
            ->where('schoolID', $schoolID)
            ->update(['subclassID' => $latest ? $latest->subclassID : null]);
    }

    /**
     * Bulk enroll selected students into a subclass (current academic year).
     * If already Active elsewhere, old Active enrollment is marked Completed (history kept).
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
        $actor = $this->resolveCurrentActor();

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
                    $this->closeActiveEnrollment(
                        $old,
                        'Completed',
                        'Promoted',
                        'Reassigned via Quick Enroll to ' . $classLabel . ' on ' . now()->format('Y-m-d'),
                        $actor
                    );
                }

                $reuse = StudentEnrollment::where('schoolID', $schoolID)
                    ->where('studentID', $sid)
                    ->where('academic_yearID', $yearID)
                    ->where('subclassID', $subclassID)
                    ->where('status', '!=', 'Active')
                    ->orderByDesc('enrollmentID')
                    ->first();

                $enrollNote = 'Enrolled via Quick Enroll on ' . now()->format('Y-m-d') . ' by ' . $actor['label'];

                if ($reuse) {
                    $reuse->status = 'Active';
                    $reuse->promotion_status = 'Pending';
                    $reuse->classID = (int) $subclass->classID;
                    $reuse->joined_date = now()->toDateString();
                    $reuse->left_date = null;
                    $reuse->enrolled_by = $actor['id'];
                    $reuse->enrolled_by_name = $actor['label'];
                    $reuse->closed_by = null;
                    $reuse->closed_by_name = null;
                    $reuse->notes = trim(($reuse->notes ? $reuse->notes . ' | ' : '') . $enrollNote);
                    $reuse->save();
                } else {
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
                        'notes' => $enrollNote,
                        'enrolled_by' => $actor['id'],
                        'enrolled_by_name' => $actor['label'],
                    ]);
                }

                $student->subclassID = $subclassID;
                $student->save();
                $enrolled++;
            }

            DB::commit();

            $message = $enrolled . ' student(s) enrolled into ' . trim($classLabel) . ' by ' . $actor['label'] . '.';
            if (count($movedFromOther) > 0) {
                $message .= ' Note: ' . count($movedFromOther) . ' had an Active enrollment elsewhere — previous enrollment status was updated (history kept).';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'enrolled_count' => $enrolled,
                'reassigned' => $movedFromOther,
                'class_label' => trim($classLabel),
                'performed_by' => $actor['label'],
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
                'enrolled_by' => $enr->enrolled_by_name ?: null,
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
     * Remove Active enrollment (status update only — history preserved).
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
            $actor = $this->resolveCurrentActor();
            $removed = 0;
            foreach ($enrollments as $enr) {
                if ($enr->status !== 'Active') {
                    continue;
                }
                $studentID = (int) $enr->studentID;
                $this->closeActiveEnrollment(
                    $enr,
                    'Cancelled',
                    null,
                    'Removed from enrollment on ' . now()->format('Y-m-d'),
                    $actor
                );
                $removed++;
                $this->syncStudentSubclassFromActive((int) $schoolID, $studentID);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $removed . ' student(s) removed from Active enrollment by ' . $actor['label'] . '. Enrollment history was kept — status updated to Cancelled.',
                'removed_count' => $removed,
                'performed_by' => $actor['label'],
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

    /**
     * Stream XLSX download without writing to storage (avoids permission issues on live servers).
     */
    private function downloadSpreadsheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, string $fileName)
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Minimal Excel template for Quick Registration (no class / no enrollment).
     */
    public function downloadQuickTemplate(Request $request)
    {
        if (!$this->canCreateStudents()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $withDemo = $request->boolean('with_demo');
        $spreadsheet = $this->buildQuickRegistrationSpreadsheet($withDemo);
        $filename = $withDemo
            ? 'Quick_Student_Registration_Demo_2000_' . date('Y-m-d') . '.xlsx'
            : 'Quick_Student_Registration_Template.xlsx';

        return $this->downloadSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Build the Quick Registration workbook (empty or with demo rows).
     */
    private function buildQuickRegistrationSpreadsheet(bool $withDemo): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Quick Registration');

        $columns = [
            'Student First Name*',
            'Student Middle Name',
            'Student Last Name*',
            'Student Gender (Male/Female)*',
            'Parent Phone (255XXXXXXXXX)*',
            'Parent First Name*',
            'Parent Middle Name',
            'Parent Last Name*',
            'Parent Gender (Male/Female)*',
        ];

        $sheetData = [$columns];

        if ($withDemo) {
            foreach ($this->generateQuickDemoStudentRows(self::QUICK_DEMO_ROW_COUNT) as $row) {
                $sheetData[] = $row;
            }
        } else {
            $sheetData[] = ['Juma', 'Ali', 'Hassan', 'Male', '255712345678', 'Amina', '', 'Hassan', 'Female'];
        }

        $sheet->fromArray($sheetData, null, 'A1');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        for ($i = 1; $i <= count($columns); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        if (!$withDemo) {
            $sheet->getStyle('A2:I2')->getFont()->setItalic(true)->getColor()->setRGB('888888');
        }

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Instructions');
        $sheet2->setCellValue('A1', 'Quick Registration Import');
        $sheet2->getStyle('A1')->getFont()->setBold(true);
        $sheet2->setCellValue('A3', '1. Fill one student per row (delete the grey example row if empty template).');
        $sheet2->setCellValue('A4', '2. Required: Student first/last name, gender, parent phone. Parent names + gender only if phone is NEW in this school.');
        $sheet2->setCellValue('A5', '3. Parent phone must be 255 + 9 digits (e.g. 255712345678). 0712… is also accepted.');
        $sheet2->setCellValue('A6', '4. If parent phone already exists in this school, that parent is reused (new parent names ignored for that phone).');
        $sheet2->setCellValue('A7', '5. Students are registered WITHOUT class enrollment — enroll later via Enroll Student.');
        $sheet2->setCellValue('A8', '6. Admission numbers are auto-generated.');
        if ($withDemo) {
            $sheet2->setCellValue('A9', '7. This file contains ' . self::QUICK_DEMO_ROW_COUNT . ' demo students (Tanzanian names) for testing — upload or use Import with demo data.');
        }
        $sheet2->getColumnDimension('A')->setWidth(100);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private const QUICK_DEMO_ROW_COUNT = 2000;

    /**
     * Export students with no Active enrollment (quick-registered / free to enroll).
     */
    public function exportUnenrolledStudents()
    {
        if (!$this->canCreateStudents() && !$this->canUpdateStudents()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->back()->with('error', 'School ID not found');
        }

        $enrolledIds = StudentEnrollment::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('studentID')
            ->unique()
            ->all();

        $students = Student::with('parent')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->when(!empty($enrolledIds), function ($q) use ($enrolledIds) {
                $q->whereNotIn('studentID', $enrolledIds);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Unenrolled Students');

        $headers = [
            'Admission Number',
            'Student First Name',
            'Student Middle Name',
            'Student Last Name',
            'Student Gender',
            'Parent Phone',
            'Parent First Name',
            'Parent Middle Name',
            'Parent Last Name',
            'Parent Gender',
            'Registration Status',
        ];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $row = 2;
        foreach ($students as $s) {
            $p = $s->parent;
            $sheet->setCellValue('A' . $row, $s->admission_number);
            $sheet->setCellValue('B' . $row, $s->first_name);
            $sheet->setCellValue('C' . $row, $s->middle_name);
            $sheet->setCellValue('D' . $row, $s->last_name);
            $sheet->setCellValue('E' . $row, $s->gender);
            $sheet->setCellValue('F' . $row, $p->phone ?? '');
            $sheet->setCellValue('G' . $row, $p->first_name ?? '');
            $sheet->setCellValue('H' . $row, $p->middle_name ?? '');
            $sheet->setCellValue('I' . $row, $p->last_name ?? '');
            $sheet->setCellValue('J' . $row, $p->gender ?? '');
            $sheet->setCellValue('K' . $row, $s->registration_status ?? 'Draft');
            $row++;
        }

        return $this->downloadSpreadsheet($spreadsheet, 'Unenrolled_Students_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Import quick-registration Excel: create students + parents, NO enrollment.
     */
    public function importQuickStudents(Request $request)
    {
        if (!$this->canCreateStudents()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School ID not found'], 400);
        }

        try {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
            @ini_set('memory_limit', '1024M');
        } catch (\Throwable $e) {
            // ignore
        }

        $withDemo = $request->boolean('with_demo');
        $file = $request->file('excel_file');

        if ($withDemo) {
            $rows = $file
                ? $this->readQuickImportRowsFromSpreadsheet($file)
                : $this->generateQuickDemoStudentRows(self::QUICK_DEMO_ROW_COUNT);

            $parsed = $this->parseQuickImportRows($rows, !$file);
            if (empty($parsed['items'])) {
                return response()->json([
                    'success' => false,
                    'message' => count($parsed['errors']) > 0
                        ? 'No students imported. ' . implode(' | ', array_slice($parsed['errors'], 0, 5))
                        : 'No valid demo rows found.',
                    'imported' => 0,
                    'errors' => $parsed['errors'],
                ]);
            }

            return $this->importQuickDemoBulk((int) $schoolID, $parsed['items']);
        }

        if (!$file) {
            return response()->json(['success' => false, 'message' => 'Please upload an Excel file or enable Import with demo data.']);
        }

        try {
            $rows = $this->readQuickImportRowsFromSpreadsheet($file);

            return $this->processQuickImportRows((int) $schoolID, $rows, false, false);
        } catch (\Throwable $e) {
            Log::error('Quick import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => SafeErrorMessage::from($e, 'Error importing file. Please check the template and try again.'),
            ]);
        }
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readQuickImportRowsFromSpreadsheet($file): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $worksheet = $this->resolveQuickImportWorksheet($spreadsheet);
        $rows = $worksheet->toArray();

        if (!empty($rows) && stripos((string) ($rows[0][0] ?? ''), 'student') !== false) {
            array_shift($rows);
        } else {
            array_shift($rows);
        }

        return $rows;
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{items: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    private function parseQuickImportRows(array $rows, bool $skipExampleRow): array
    {
        $items = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $row = array_pad($row, 9, null);

            $sFirst = trim((string) ($row[0] ?? ''));
            $sMiddle = trim((string) ($row[1] ?? ''));
            $sLast = trim((string) ($row[2] ?? ''));
            $sGender = $this->normalizeQuickGender($row[3] ?? '');
            $pPhoneRaw = trim((string) ($row[4] ?? ''));
            $pFirst = trim((string) ($row[5] ?? ''));
            $pMiddle = trim((string) ($row[6] ?? ''));
            $pLast = trim((string) ($row[7] ?? ''));
            $pGender = $this->normalizeQuickGender($row[8] ?? '');

            if ($sFirst === '' && $sLast === '' && $pPhoneRaw === '') {
                continue;
            }

            if ($skipExampleRow && strcasecmp($sFirst, 'Juma') === 0 && strcasecmp($sLast, 'Hassan') === 0 && str_contains($pPhoneRaw, '712345678')) {
                continue;
            }

            if ($sFirst === '' || $sLast === '') {
                $errors[] = "Row {$rowNum}: Student first name and last name are required.";
                continue;
            }
            if (!$sGender) {
                $errors[] = "Row {$rowNum}: Student gender must be Male or Female.";
                continue;
            }

            $phone = ParentPhone::normalize($pPhoneRaw);
            if (!$phone) {
                $errors[] = "Row {$rowNum}: Valid parent phone is required (255 + 9 digits).";
                continue;
            }

            $items[] = [
                'row_num' => $rowNum,
                'first_name' => $sFirst,
                'middle_name' => $sMiddle !== '' ? $sMiddle : null,
                'last_name' => $sLast,
                'gender' => $sGender,
                'phone' => $phone,
                'parent_first_name' => $pFirst,
                'parent_middle_name' => $pMiddle !== '' ? $pMiddle : null,
                'parent_last_name' => $pLast,
                'parent_gender' => $pGender ?? '',
            ];
        }

        return ['items' => $items, 'errors' => $errors];
    }

    /**
     * Fast bulk path for demo imports (2,000 rows) — batch inserts, no SMS, no per-row locks.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    private function importQuickDemoBulk(int $schoolID, array $items): \Illuminate\Http\JsonResponse
    {
        try {
            DB::connection()->disableQueryLog();

            $school = School::find($schoolID);
            if (!$school) {
                return response()->json(['success' => false, 'message' => 'School not found'], 400);
            }

            $now = now();
            $today = $now->toDateString();
            $studentPassword = Hash::make('demo');
            $year = date('Y');
            $schoolNumber = $school->registration_number ?? ('SCH' . $schoolID);
            $admPrefix = $schoolNumber . '/';
            $admSuffix = '/' . $year;

            $parentByPhone = ParentModel::where('schoolID', $schoolID)
                ->whereNotNull('phone')
                ->get(['parentID', 'phone'])
                ->mapWithKeys(function ($parent) {
                    $normalized = ParentPhone::normalize($parent->phone);
                    return $normalized ? [$normalized => (int) $parent->parentID] : [];
                })
                ->all();

            $lastAdmission = Student::where('schoolID', $schoolID)
                ->where('admission_number', 'like', $admPrefix . '%' . $admSuffix)
                ->orderByDesc('admission_number')
                ->value('admission_number');

            $admSeq = 1;
            if ($lastAdmission && preg_match('/\/(\d+)\/' . preg_quote($year, '/') . '$/', $lastAdmission, $matches)) {
                $admSeq = (int) $matches[1] + 1;
            }

            $nextStudentId = max((int) (Student::max('studentID') ?? 0), 1000) + 1;

            $successCount = 0;
            $errors = [];
            $chunkSize = 250;

            foreach (array_chunk($items, $chunkSize) as $chunk) {
                DB::beginTransaction();
                try {
                    $phonesToCreate = [];
                    foreach ($chunk as $item) {
                        if (!isset($parentByPhone[$item['phone']])) {
                            $phonesToCreate[$item['phone']] = $item;
                        }
                    }

                    if (!empty($phonesToCreate)) {
                        $parentRows = [];
                        foreach ($phonesToCreate as $phone => $item) {
                            $pGender = $item['parent_gender'] ?: 'Male';
                            $pFirst = $item['parent_first_name'] ?: 'Demo';
                            $pLast = $item['parent_last_name'] ?: 'Parent';

                            $parentRows[] = [
                                'schoolID' => $schoolID,
                                'first_name' => $pFirst,
                                'middle_name' => $item['parent_middle_name'],
                                'last_name' => $pLast,
                                'gender' => in_array($pGender, ['Male', 'Female'], true) ? $pGender : 'Male',
                                'phone' => $phone,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }

                        foreach (array_chunk($parentRows, 100) as $parentBatch) {
                            DB::table('parents')->insert($parentBatch);
                        }

                        $createdParents = ParentModel::where('schoolID', $schoolID)
                            ->whereIn('phone', array_keys($phonesToCreate))
                            ->get(['parentID', 'phone']);

                        foreach ($createdParents as $parent) {
                            $normalized = ParentPhone::normalize($parent->phone);
                            if ($normalized) {
                                $parentByPhone[$normalized] = (int) $parent->parentID;
                            }
                        }
                    }

                    $studentRows = [];
                    $userRows = [];

                    foreach ($chunk as $item) {
                        $parentId = $parentByPhone[$item['phone']] ?? null;
                        if (!$parentId) {
                            $errors[] = 'Row ' . $item['row_num'] . ': Could not resolve parent.';
                            continue;
                        }

                        $studentId = $nextStudentId++;
                        $fingerprintId = (string) $studentId;
                        $admissionNumber = $admPrefix . str_pad((string) $admSeq++, 4, '0', STR_PAD_LEFT) . $admSuffix;

                        $studentRows[] = [
                            'studentID' => $studentId,
                            'schoolID' => $schoolID,
                            'subclassID' => null,
                            'parentID' => $parentId,
                            'first_name' => $item['first_name'],
                            'middle_name' => $item['middle_name'],
                            'last_name' => $item['last_name'],
                            'gender' => $item['gender'],
                            'admission_number' => $admissionNumber,
                            'fingerprint_id' => $fingerprintId,
                            'sent_to_device' => false,
                            'device_sent_at' => null,
                            'fingerprint_capture_count' => 0,
                            'admission_date' => $today,
                            'photo' => null,
                            'status' => 'Active',
                            'nationality' => 'Tanzanian',
                            'registration_status' => 'Draft',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $userRows[] = [
                            'name' => $admissionNumber,
                            'email' => $admissionNumber . '@student.local',
                            'password' => $studentPassword,
                            'user_type' => 'student',
                            'fingerprint_id' => $fingerprintId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if (!empty($studentRows)) {
                        foreach (array_chunk($studentRows, 100) as $batch) {
                            DB::table('students')->insert($batch);
                        }
                        foreach (array_chunk($userRows, 100) as $batch) {
                            DB::table('users')->insert($batch);
                        }
                        $successCount += count($studentRows);
                    }

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Quick demo bulk chunk failed: ' . $e->getMessage());
                    $errors[] = 'Import stopped at ' . $successCount . ' students: ' . $e->getMessage();
                    break;
                }
            }

            if ($successCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => count($errors) > 0
                        ? 'No students imported. ' . implode(' | ', array_slice($errors, 0, 5))
                        : 'No students imported.',
                    'imported' => 0,
                    'errors' => $errors,
                ]);
            }

            $msg = "Successfully imported {$successCount} demo student(s) (not enrolled). No parent SMS sent.";
            if (count($errors) > 0) {
                $msg .= ' Some rows failed: ' . implode(' | ', array_slice($errors, 0, 3));
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'imported' => $successCount,
                'errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            Log::error('Quick demo bulk import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => SafeErrorMessage::from($e, 'Demo import failed. Please try again.'),
            ]);
        }
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function processQuickImportRows(int $schoolID, array $rows, bool $skipExampleRow, bool $skipParentSms): \Illuminate\Http\JsonResponse
    {
        $parsed = $this->parseQuickImportRows($rows, $skipExampleRow);

        try {
            DB::beginTransaction();
            $successCount = 0;
            $errors = $parsed['errors'];
            $newParentsSms = 0;

            foreach ($parsed['items'] as $item) {
                $rowNum = $item['row_num'];

                try {
                    $result = $this->createQuickStudentRecord((int) $schoolID, [
                        'first_name' => $item['first_name'],
                        'middle_name' => $item['middle_name'],
                        'last_name' => $item['last_name'],
                        'gender' => $item['gender'],
                        'parent_phone' => $item['phone'],
                        'parent_first_name' => $item['parent_first_name'],
                        'parent_middle_name' => $item['parent_middle_name'],
                        'parent_last_name' => $item['parent_last_name'],
                        'parent_gender' => $item['parent_gender'],
                    ], $skipParentSms);

                    if (!$result['success']) {
                        $errors[] = "Row {$rowNum}: " . $result['message'];
                        continue;
                    }

                    $successCount++;
                    if (!empty($result['is_new_parent']) && !empty($result['parent_sms_sent'])) {
                        $newParentsSms++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                }
            }

            DB::commit();

            if ($successCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => count($errors) > 0
                        ? 'No students imported. ' . implode(' | ', array_slice($errors, 0, 5))
                        : 'No students imported. Fill the "Quick Registration" sheet (not Instructions) and remove the grey example row.',
                    'imported' => 0,
                    'errors' => $errors,
                ]);
            }

            $msg = "Successfully imported {$successCount} student(s) (not enrolled).";
            if ($newParentsSms > 0) {
                $msg .= " Parent SMS sent for {$newParentsSms} new parent(s).";
            }
            if (count($errors) > 0) {
                $msg .= ' Some rows failed: ' . implode(' | ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '...' : '');
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'imported' => $successCount,
                'errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Quick import error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => SafeErrorMessage::from($e, 'Error importing students. Please try again.'),
            ]);
        }
    }

    /**
     * Shared create for one quick-registered student (no enrollment).
     * Caller manages outer transaction for bulk import.
     *
     * @param  array<string,mixed>  $data
     * @return array{success:bool,message:string,admission_number?:string,is_new_parent?:bool,parent_sms_sent?:bool}
     */
    private function createQuickStudentRecord(int $schoolID, array $data, bool $skipParentSms = false): array
    {
        $normalizedPhone = ParentPhone::normalize($data['parent_phone'] ?? null);
        if ($normalizedPhone && str_starts_with($normalizedPhone, '2557999')) {
            $skipParentSms = true;
        }

        $parent = ParentPhone::findInSchool($schoolID, $data['parent_phone'] ?? null);
        $isNewParent = false;

        if (!$parent) {
            $inOther = ParentPhone::findAnywhere($data['parent_phone'] ?? null, $schoolID);
            if ($inOther) {
                return [
                    'success' => false,
                    'message' => 'Parent phone already exists in another school.',
                ];
            }

            $pFirst = trim((string) ($data['parent_first_name'] ?? ''));
            $pLast = trim((string) ($data['parent_last_name'] ?? ''));
            $pGender = $this->normalizeQuickGender($data['parent_gender'] ?? '');
            if ($pFirst === '' || $pLast === '' || !$pGender) {
                return [
                    'success' => false,
                    'message' => 'New parent requires first name, last name and gender (Male/Female).',
                ];
            }

            $parent = ParentModel::create([
                'schoolID' => $schoolID,
                'first_name' => $pFirst,
                'middle_name' => $data['parent_middle_name'] ?? null,
                'last_name' => $pLast,
                'gender' => $pGender,
                'phone' => ParentPhone::normalize($data['parent_phone']),
            ]);
            $isNewParent = true;
        }

        try {
            ParentUserAccount::ensure($parent);
        } catch (\Throwable $e) {
            Log::warning('Quick import parent login skipped: ' . $e->getMessage());
        }

        do {
            $admissionNumber = $this->generateAdmissionNumber($schoolID);
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

        Student::create([
            'studentID' => (int) $fingerprintId,
            'schoolID' => $schoolID,
            'subclassID' => null,
            'parentID' => (int) $parent->parentID,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'admission_number' => $admissionNumber,
            'fingerprint_id' => $fingerprintId,
            'sent_to_device' => false,
            'device_sent_at' => null,
            'fingerprint_capture_count' => 0,
            'admission_date' => now()->toDateString(),
            'photo' => null,
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
            'password' => Hash::make($data['first_name']),
            'user_type' => 'student',
            'fingerprint_id' => $fingerprintId,
        ]);

        $parentSmsSent = false;
        if (!$skipParentSms && $isNewParent && $parent->phone) {
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
            } catch (\Throwable $e) {
                Log::warning('Quick import parent SMS skipped: ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => 'OK',
            'admission_number' => $admissionNumber,
            'is_new_parent' => $isNewParent,
            'parent_sms_sent' => $parentSmsSent,
        ];
    }

    /**
     * Prefer the Quick Registration sheet when the workbook has multiple tabs.
     */
    private function resolveQuickImportWorksheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (strcasecmp(trim((string) $sheet->getTitle()), 'Quick Registration') === 0) {
                return $sheet;
            }
        }

        return $spreadsheet->getActiveSheet();
    }

    /**
     * Accept Male/Female in any common casing or shorthand.
     */
    private function normalizeQuickGender($value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $lower = strtolower($raw);
        if (in_array($lower, ['male', 'm', 'me', 'mvulana', 'kiume'], true)) {
            return 'Male';
        }
        if (in_array($lower, ['female', 'f', 'ke', 'msichana', 'kike'], true)) {
            return 'Female';
        }
        if ($raw === 'Male' || $raw === 'Female') {
            return $raw;
        }

        return null;
    }

    /**
     * Generate demo quick-registration rows (Tanzanian / African names).
     *
     * @return array<int, array{0:string,1:string,2:string,3:string,4:string,5:string,6:string,7:string,8:string}>
     */
    private function generateQuickDemoStudentRows(int $count): array
    {
        $maleFirst = [
            'Juma', 'Hassan', 'Rajabu', 'Musa', 'Omari', 'Hamisi', 'Yusuf', 'Ibrahim', 'Mohamed', 'Abdallah',
            'Salim', 'Rashid', 'Dickson', 'Johnson', 'Frank', 'Joseph', 'Godfrey', 'Patrick', 'Michael', 'Emmanuel',
            'David', 'Peter', 'John', 'James', 'Charles', 'George', 'Robert', 'William', 'Thomas', 'Daniel',
            'Selemani', 'Bakari', 'Kassim', 'Ramadhani', 'Baraka', 'Elias', 'Fredrick', 'Goodluck', 'Ismail', 'Jafari',
        ];
        $femaleFirst = [
            'Asha', 'Fatuma', 'Neema', 'Glory', 'Amina', 'Rehema', 'Zainabu', 'Mariam', 'Halima', 'Khadija',
            'Grace', 'Mary', 'Anna', 'Elizabeth', 'Sarah', 'Rose', 'Agnes', 'Joyce', 'Janet', 'Christina',
            'Catherine', 'Helen', 'Lucy', 'Irene', 'Pendo', 'Tumaini', 'Faraja', 'Upendo', 'Mwanahamisi', 'Mariam',
            'Stella', 'Violet', 'Winfrida', 'Rehema', 'Neema', 'Salma', 'Hadija', 'Mwanajuma', 'Latifa', 'Nasra',
        ];
        $middle = [
            'Ali', 'Said', 'Mohamed', 'Abdallah', 'Omary', 'Joseph', 'Peter', 'Paul', 'John', 'Hassan',
            'Mwinyi', 'Kassim', 'Bakari', 'Rajabu', 'Selemani', 'Yusuph', 'Juma', 'Musa', 'Hamisi', 'Salim',
        ];
        $last = [
            'Hassan', 'Mwinyi', 'Kimaro', 'Lyimo', 'Mussa', 'Juma', 'Rashid', 'Salim', 'Mkumbo', 'Moshi',
            'Kileo', 'Shirima', 'Malecela', 'Nnko', 'Temu', 'Massawe', 'Mwangosi', 'Urassa', 'Fernando', 'Msuya',
            'Temba', 'Mwakalinga', 'Mfinanga', 'Mrema', 'Mwakapenda', 'Ngowi', 'Macha', 'Majaliwa', 'Mlowe', 'Masanja',
            'Mbuya', 'Mchome', 'Mdemu', 'Mhando', 'Mkiva', 'Mollel', 'Mosha', 'Msoffe', 'Mtui', 'Mwambene',
        ];
        $parentMaleFirst = [
            'Juma', 'Hassan', 'Rajabu', 'Musa', 'Omari', 'Hamisi', 'Salim', 'Rashid', 'Mohamed', 'Abdallah',
            'Yusuf', 'Ibrahim', 'Peter', 'John', 'James', 'Joseph', 'Frank', 'Dickson', 'Emmanuel', 'Godfrey',
        ];
        $parentFemaleFirst = [
            'Asha', 'Fatuma', 'Amina', 'Rehema', 'Zainabu', 'Mariam', 'Halima', 'Khadija', 'Neema', 'Mary',
            'Grace', 'Anna', 'Elizabeth', 'Sarah', 'Rose', 'Agnes', 'Joyce', 'Janet', 'Mwanahamisi', 'Salma',
        ];

        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $isMale = ($i % 2 === 1);
            $sGender = $isMale ? 'Male' : 'Female';
            $sFirstPool = $isMale ? $maleFirst : $femaleFirst;
            $sFirst = $sFirstPool[($i - 1) % count($sFirstPool)];
            $sMiddle = $middle[($i + 3) % count($middle)];
            $sLast = $last[($i * 7 + 11) % count($last)];

            // Unique demo phones: 2557999XXXXX (demo range — no SMS)
            $phone = '2557999' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);

            $parentIsMale = (($i + 5) % 3 !== 0);
            $pGender = $parentIsMale ? 'Male' : 'Female';
            $pFirstPool = $parentIsMale ? $parentMaleFirst : $parentFemaleFirst;
            $pFirst = $pFirstPool[($i + 2) % count($pFirstPool)];
            $pMiddle = $middle[($i + 9) % count($middle)];
            $pLast = $last[($i * 13 + 5) % count($last)];

            $rows[] = [
                $sFirst,
                $sMiddle,
                $sLast,
                $sGender,
                $phone,
                $pFirst,
                $pMiddle,
                $pLast,
                $pGender,
            ];
        }

        return $rows;
    }
}
