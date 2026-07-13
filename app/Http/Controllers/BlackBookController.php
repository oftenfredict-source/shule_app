<?php

namespace App\Http\Controllers;

use App\Models\BlackBookActor;
use App\Models\BlackBookProblemCategory;
use App\Models\BlackBookPunishmentCategory;
use App\Models\BlackBookReport;
use App\Models\ClassModel;
use App\Models\OtherStaff;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\BlackBookAccessService;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BlackBookController extends Controller
{
    private function schoolId(): ?int
    {
        $id = Session::get('schoolID');
        return $id ? (int) $id : null;
    }

    private function jsonError(string $message, int $code = 400)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    protected function getBlackBookAccess(?string $requiredPrivilege = null): ?array
    {
        return app(BlackBookAccessService::class)->resolve($requiredPrivilege);
    }

    private function formatPersonName(?string $first, ?string $middle, ?string $last): string
    {
        return trim(implode(' ', array_filter([$first, $middle, $last])));
    }

    protected function formatBlackBookActorUi(BlackBookActor $actor): array
    {
        $privilegeLabels = BlackBookActor::privilegeLabels();
        $privileges = array_merge(
            BlackBookActor::defaultPrivileges(),
            is_array($actor->privileges) ? $actor->privileges : []
        );
        $enabled = collect($privileges)->filter()->keys()->map(
            fn ($k) => $privilegeLabels[$k] ?? ucfirst(str_replace('_', ' ', $k))
        )->values()->all();

        return [
            'actorID' => (int) $actor->actorID,
            'actor_type' => $actor->actor_type,
            'actor_type_label' => ucfirst($actor->actor_type),
            'actor_ref_id' => (int) $actor->actor_ref_id,
            'actor_name' => $actor->actor_name,
            'privileges' => $privileges,
            'privileges_summary' => implode(', ', $enabled) ?: 'No privileges',
            'granted_by' => $actor->granted_by,
            'created_at' => $actor->created_at ? $actor->created_at->format('d M Y, H:i') : '—',
        ];
    }

    private function normalizePunishmentTypeFields(string $type): array
    {
        $punishmentType = $type === 'continuous' ? 'continuous' : 'one_time';

        return [
            'punishment_type' => $punishmentType,
            'requires_monitoring' => $punishmentType === 'continuous',
        ];
    }

    private function punishmentTypeLabel(?string $type): string
    {
        return $type === 'continuous' ? 'Continuous' : 'One Time';
    }

    private function currentActor(): array
    {
        $type = (string) Session::get('user_type', 'Admin');
        $name = Session::get('teacher_name')
            ?: Session::get('staff_name')
            ?: Session::get('admin_name')
            ?: Session::get('user_name')
            ?: 'User';

        $id = Session::get('teacherID') ?: Session::get('staffID') ?: Session::get('userID');

        return [
            'type' => strtolower($type),
            'id' => $id ? (int) $id : null,
            'name' => trim((string) $name),
        ];
    }

    private function formatStudentName(?Student $student): string
    {
        if (!$student) {
            return 'Unknown';
        }
        return trim(implode(' ', array_filter([
            $student->first_name,
            $student->middle_name,
            $student->last_name,
        ])));
    }

    private function formatStudentClass(?Student $student): string
    {
        if (!$student) {
            return '—';
        }

        $enrollment = $student->relationLoaded('currentEnrollment')
            ? $student->currentEnrollment
            : $student->currentEnrollment()->with(['subclass.class', 'classModel'])->first();

        if ($enrollment) {
            $class = $enrollment->classModel->class_name
                ?? $enrollment->subclass->class->class_name
                ?? '';
            $sub = $enrollment->subclass->subclass_name ?? '';
            $label = trim($class . ($sub ? ' ' . $sub : ''));
            if ($label !== '') {
                return $label;
            }
        }

        if (!$student->subclass) {
            return '—';
        }
        $class = $student->subclass->class->class_name ?? '';
        $sub = $student->subclass->subclass_name ?? '';
        return trim($class . ($sub ? ' ' . $sub : '')) ?: '—';
    }

    private function formatReport(BlackBookReport $report): array
    {
        $report->loadMissing(['student.subclass.class', 'student.currentEnrollment.subclass.class', 'student.currentEnrollment.classModel', 'problemCategory', 'punishmentCategory', 'student.parent', 'enrollment.subclass.class', 'enrollment.classModel']);

        $student = $report->student;
        $parent = $student?->parent;

        return [
            'reportID' => (int) $report->reportID,
            'studentID' => (int) $report->studentID,
            'enrollmentID' => (int) ($report->enrollmentID ?? optional($student?->currentEnrollment)->enrollmentID),
            'student_name' => $this->formatStudentName($student),
            'admission_number' => $student->admission_number ?? '—',
            'class_name' => $report->enrollment
                ? trim(($report->enrollment->classModel->class_name ?? $report->enrollment->subclass->class->class_name ?? '') . ' ' . ($report->enrollment->subclass->subclass_name ?? ''))
                : $this->formatStudentClass($student),
            'problem_categoryID' => (int) $report->problem_categoryID,
            'problem_category' => $report->problemCategory->name ?? '—',
            'problem_weight' => (int) ($report->problemCategory->weight ?? 0),
            'description' => $report->description,
            'reported_by_name' => $report->reported_by_name,
            'reported_by_type' => $report->reported_by_type,
            'status' => $report->status,
            'viewed_at' => $report->viewed_at?->format('Y-m-d H:i'),
            'viewed_by_name' => $report->viewed_by_name,
            'resolve_method' => $report->resolve_method,
            'punishment_category' => $report->punishmentCategory->name ?? null,
            'punishment_description' => $report->punishment_description,
            'punishment_type' => $report->punishment_type,
            'punishment_type_label' => $this->punishmentTypeLabel($report->punishment_type),
            'requires_monitoring' => (bool) $report->requires_monitoring,
            'monitoring_deadline' => $report->monitoring_deadline?->format('Y-m-d'),
            'monitoring_ended_at' => $report->monitoring_ended_at?->format('Y-m-d H:i'),
            'status_label' => $this->reportStatusLabel($report->status),
            'parent_sms_message' => $report->parent_sms_message,
            'parent_sms_sent' => (bool) $report->parent_sms_sent,
            'resolved_at' => $report->resolved_at?->format('Y-m-d H:i'),
            'resolved_by_name' => $report->resolved_by_name,
            'created_at' => $report->created_at?->format('Y-m-d H:i'),
            'parent_name' => $parent ? trim(implode(' ', array_filter([
                $parent->first_name,
                $parent->middle_name,
                $parent->last_name,
            ]))) : null,
            'parent_phone' => $parent->phone ?? null,
        ];
    }

    private function reportStatusLabel(?string $status): string
    {
        return match ($status) {
            'monitored' => 'Resolved (Monitored)',
            'resolved' => 'Resolved',
            'viewed' => 'Viewed',
            default => 'Pending',
        };
    }

    private function notifyAdminSms(BlackBookReport $report): void
    {
        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return;
        }

        $school = School::find($schoolID);
        if (!$school || empty($school->phone)) {
            return;
        }

        $report->loadMissing(['student.subclass.class', 'problemCategory']);
        $studentName = $this->formatStudentName($report->student);
        $className = $this->formatStudentClass($report->student);
        $category = $report->problemCategory->name ?? 'misconduct';

        $message = "Black Book Alert: {$studentName} ({$className}) reported for {$category}. Reported by {$report->reported_by_name}.";

        try {
            $sms = new SmsService();
            $sms->sendSms($school->phone, $sms->formatMessage($message, $school->school_name));
        } catch (\Throwable $e) {
            \Log::warning('Black Book admin SMS failed: ' . $e->getMessage());
        }
    }

    private function notifyParentOnReport(BlackBookReport $report): bool
    {
        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return false;
        }

        $report->loadMissing(['student.parent', 'student.subclass.class', 'problemCategory']);
        $parentPhone = $report->student?->parent?->phone;
        if (!$parentPhone) {
            return false;
        }

        $school = School::find($schoolID);
        $studentName = $this->formatStudentName($report->student);
        $className = $this->formatStudentClass($report->student);
        $category = $report->problemCategory->name ?? 'misconduct';
        $parent = $report->student->parent;
        $parentName = trim(implode(' ', array_filter([
            $parent->first_name ?? '',
            $parent->last_name ?? '',
        ]))) ?: 'Parent';

        $message = "Habari {$parentName}, mtoto wako {$studentName} ({$className}) ameripotiwa kwa tatizo la {$category}. Tafadhali wasiliana na shule kwa maelezo zaidi.";

        try {
            $sms = new SmsService();
            $result = $sms->sendSms(
                $parentPhone,
                $sms->formatMessage($message, $school->school_name ?? null)
            );

            return !empty($result['success']);
        } catch (\Throwable $e) {
            \Log::warning('Black Book parent report SMS failed: ' . $e->getMessage());

            return false;
        }
    }

    public function manageBlackBook(Request $request)
    {
        $access = $this->getBlackBookAccess();
        if (!$access || !app(BlackBookAccessService::class)->hasAnyManagePrivilege($access)) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = (int) $access['schoolID'];
        $accessService = app(BlackBookAccessService::class);

        $section = $request->get('section', $accessService->firstAllowedSection($access));
        $valid = ['dashboard', 'categories', 'punishments', 'pending', 'monitoring', 'resolved', 'reports'];
        if (!in_array($section, $valid, true)) {
            $section = $accessService->firstAllowedSection($access);
        }

        $sectionPrivilege = $accessService->sectionPrivilege($section);
        if ($sectionPrivilege && !$accessService->can($access, $sectionPrivilege)) {
            $section = $accessService->firstAllowedSection($access);
        }

        $stats = $accessService->can($access, 'view_dashboard')
            ? $this->buildDashboardStats($schoolID)
            : ['pending' => 0, 'viewed' => 0, 'resolved' => 0, 'monitored' => 0, 'total' => 0, 'top_students' => []];

        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('class_name')
            ->get(['classID', 'class_name']);

        $blackBookActors = collect();
        $actorPickerTeachers = collect();
        $actorPickerStaff = collect();

        if (!empty($access['can_manage_actors'])) {
            $blackBookActors = BlackBookActor::where('schoolID', $schoolID)
                ->where('status', 'active')
                ->orderBy('actor_name')
                ->get()
                ->map(fn ($a) => $this->formatBlackBookActorUi($a))
                ->values();

            $existingActorKeys = BlackBookActor::where('schoolID', $schoolID)
                ->where('status', 'active')
                ->get()
                ->mapWithKeys(fn ($a) => [$a->actor_type . ':' . $a->actor_ref_id => true])
                ->all();

            $actorPickerTeachers = Teacher::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->get()
                ->filter(fn ($t) => !isset($existingActorKeys['teacher:' . $t->id]))
                ->map(fn ($t) => [
                    'type' => 'teacher',
                    'id' => $t->id,
                    'name' => $this->formatPersonName($t->first_name, $t->middle_name, $t->last_name),
                    'employee_number' => $t->employee_number,
                ])
                ->values();

            $actorPickerStaff = OtherStaff::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('first_name')
                ->get()
                ->filter(fn ($s) => !isset($existingActorKeys['staff:' . $s->id]))
                ->map(fn ($s) => [
                    'type' => 'staff',
                    'id' => $s->id,
                    'name' => $this->formatPersonName($s->first_name, $s->middle_name, $s->last_name),
                    'employee_number' => $s->employee_number,
                ])
                ->values();
        }

        $bbAccess = $access;

        $problemCategories = collect();
        $punishmentCategories = collect();
        $initialPendingReports = [];

        if ($accessService->can($access, 'manage_problem_categories')) {
            $problemCategories = BlackBookProblemCategory::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderByDesc('weight')
                ->orderBy('name')
                ->get(['categoryID', 'name', 'weight', 'status']);
        }

        if ($accessService->can($access, 'manage_punishment_categories')
            || $accessService->can($access, 'manage_pending')) {
            $punishmentCategories = BlackBookPunishmentCategory::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->orderBy('name')
                ->get(['punishmentCategoryID', 'name', 'punishment_type', 'requires_monitoring', 'status']);
        }

        if ($accessService->can($access, 'manage_pending') && $section === 'pending') {
            $initialPendingReports = $this->fetchFormattedReports($schoolID, ['pending', 'viewed'], 200);
        }

        return view('Admin.manage_black_book', compact(
            'section',
            'stats',
            'classes',
            'bbAccess',
            'blackBookActors',
            'actorPickerTeachers',
            'actorPickerStaff',
            'problemCategories',
            'punishmentCategories',
            'initialPendingReports'
        ));
    }

    public function reportStudentPage()
    {
        if (!app(BlackBookAccessService::class)->canReportStudent()) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $categories = BlackBookProblemCategory::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderByDesc('weight')
            ->orderBy('name')
            ->get();

        $actor = $this->currentActor();
        $myReports = BlackBookReport::where('schoolID', $schoolID)
            ->where('reported_by_name', $actor['name'])
            ->with(['student.subclass.class', 'problemCategory', 'punishmentCategory'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($r) => $this->formatReport($r));

        return view('Admin.report_student_black_book', compact('categories', 'myReports'));
    }

    private function buildDashboardStats(int $schoolID): array
    {
        $counts = BlackBookReport::where('schoolID', $schoolID)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pending = (int) ($counts['pending'] ?? 0);
        $viewed = (int) ($counts['viewed'] ?? 0);
        $resolved = (int) ($counts['resolved'] ?? 0);
        $monitored = (int) ($counts['monitored'] ?? 0);
        $total = $pending + $viewed + $resolved + $monitored;

        $topStudents = BlackBookReport::query()
            ->from('black_book_reports as r')
            ->join('students as s', 's.studentID', '=', 'r.studentID')
            ->leftJoin('subclasses as sc', 'sc.subclassID', '=', 's.subclassID')
            ->leftJoin('classes as c', 'c.classID', '=', 'sc.classID')
            ->where('r.schoolID', $schoolID)
            ->selectRaw('r.studentID, COUNT(*) as report_count, s.first_name, s.middle_name, s.last_name, s.admission_number, c.class_name, sc.subclass_name')
            ->groupBy('r.studentID', 's.first_name', 's.middle_name', 's.last_name', 's.admission_number', 'c.class_name', 'sc.subclass_name')
            ->orderByDesc('report_count')
            ->limit(5)
            ->get();

        $topList = $topStudents->map(function ($row) {
            $name = trim(implode(' ', array_filter([
                $row->first_name,
                $row->middle_name,
                $row->last_name,
            ]))) ?: ($row->admission_number ?: 'Unknown');

            $class = trim(($row->class_name ?? '') . ' ' . ($row->subclass_name ?? ''));

            return [
                'studentID' => (int) $row->studentID,
                'student_name' => $name,
                'class_name' => $class ?: '—',
                'report_count' => (int) $row->report_count,
            ];
        })->values()->all();

        return [
            'pending' => $pending,
            'viewed' => $viewed,
            'resolved' => $resolved,
            'monitored' => $monitored,
            'total' => $total,
            'top_students' => $topList,
        ];
    }

    private function parseReportStatuses(?string $status): ?array
    {
        if (!$status) {
            return null;
        }

        $allowed = ['pending', 'viewed', 'resolved', 'monitored'];
        if (str_contains($status, ',')) {
            $statuses = array_values(array_intersect(explode(',', $status), $allowed));

            return $statuses ?: null;
        }

        return in_array($status, $allowed, true) ? [$status] : null;
    }

    private function fetchFormattedReports(int $schoolID, ?array $statuses = null, int $limit = 200): array
    {
        $query = BlackBookReport::where('schoolID', $schoolID)
            ->with(['student.subclass.class', 'problemCategory', 'punishmentCategory', 'student.parent']);

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => $this->formatReport($r))
            ->values()
            ->all();
    }

    public function dashboardStats()
    {
        $access = $this->getBlackBookAccess('view_dashboard');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        return response()->json([
            'success' => true,
            'stats' => $this->buildDashboardStats((int) $access['schoolID']),
        ]);
    }

    // --- Problem categories ---

    public function listProblemCategories()
    {
        $access = $this->getBlackBookAccess('manage_problem_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $items = BlackBookProblemCategory::where('schoolID', (int) $access['schoolID'])
            ->where('status', 'Active')
            ->orderByDesc('weight')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'categories' => $items]);
    }

    public function storeProblemCategory(Request $request)
    {
        $access = $this->getBlackBookAccess('manage_problem_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        if ($request->has('categories') && is_array($request->categories)) {
            $validator = Validator::make($request->all(), [
                'categories' => 'required|array|min:1',
                'categories.*.name' => 'required|string|max:120',
                'categories.*.weight' => 'required|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->jsonError($validator->errors()->first(), 422);
            }

            $saved = [];
            foreach ($request->categories as $item) {
                $saved[] = BlackBookProblemCategory::create([
                    'schoolID' => $schoolID,
                    'name' => trim($item['name']),
                    'weight' => (int) $item['weight'],
                    'status' => 'Active',
                ]);
            }

            $count = count($saved);

            return response()->json([
                'success' => true,
                'message' => $count . ' problem categor' . ($count === 1 ? 'y' : 'ies') . ' saved.',
                'categories' => $saved,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'weight' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $cat = BlackBookProblemCategory::create([
            'schoolID' => $schoolID,
            'name' => trim($request->name),
            'weight' => (int) $request->weight,
            'status' => 'Active',
        ]);

        return response()->json(['success' => true, 'message' => 'Problem category saved.', 'category' => $cat]);
    }

    public function updateProblemCategory(Request $request, $categoryID)
    {
        $access = $this->getBlackBookAccess('manage_problem_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $cat = BlackBookProblemCategory::where('schoolID', $schoolID)
            ->where('categoryID', $categoryID)
            ->first();

        if (!$cat) {
            return $this->jsonError('Category not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'weight' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $cat->update([
            'name' => trim($request->name),
            'weight' => (int) $request->weight,
        ]);

        return response()->json(['success' => true, 'message' => 'Category updated.', 'category' => $cat]);
    }

    public function deleteProblemCategory($categoryID)
    {
        $access = $this->getBlackBookAccess('manage_problem_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $cat = BlackBookProblemCategory::where('schoolID', $schoolID)
            ->where('categoryID', $categoryID)
            ->first();

        if (!$cat) {
            return $this->jsonError('Category not found.', 404);
        }

        $cat->update(['status' => 'Inactive']);

        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }

    // --- Punishment categories ---

    public function listPunishmentCategories()
    {
        $access = $this->getBlackBookAccess('manage_punishment_categories');
        if (!$access) {
            $access = $this->getBlackBookAccess('manage_pending');
        }
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $items = BlackBookPunishmentCategory::where('schoolID', (int) $access['schoolID'])
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'categories' => $items]);
    }

    public function storePunishmentCategory(Request $request)
    {
        $access = $this->getBlackBookAccess('manage_punishment_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        if ($request->has('categories') && is_array($request->categories)) {
            $validator = Validator::make($request->all(), [
                'categories' => 'required|array|min:1',
                'categories.*.name' => 'required|string|max:120',
                'categories.*.punishment_type' => 'required|in:one_time,continuous',
            ]);

            if ($validator->fails()) {
                return $this->jsonError($validator->errors()->first(), 422);
            }

            $saved = [];
            foreach ($request->categories as $item) {
                $typeFields = $this->normalizePunishmentTypeFields($item['punishment_type'] ?? 'one_time');
                $saved[] = BlackBookPunishmentCategory::create([
                    'schoolID' => $schoolID,
                    'name' => trim($item['name']),
                    'punishment_type' => $typeFields['punishment_type'],
                    'requires_monitoring' => $typeFields['requires_monitoring'],
                    'status' => 'Active',
                ]);
            }

            $count = count($saved);

            return response()->json([
                'success' => true,
                'message' => $count . ' punishment categor' . ($count === 1 ? 'y' : 'ies') . ' saved.',
                'categories' => $saved,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'punishment_type' => 'required|in:one_time,continuous',
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $typeFields = $this->normalizePunishmentTypeFields($request->punishment_type);

        $cat = BlackBookPunishmentCategory::create([
            'schoolID' => $schoolID,
            'name' => trim($request->name),
            'punishment_type' => $typeFields['punishment_type'],
            'requires_monitoring' => $typeFields['requires_monitoring'],
            'status' => 'Active',
        ]);

        return response()->json(['success' => true, 'message' => 'Punishment category saved.', 'category' => $cat]);
    }

    public function updatePunishmentCategory(Request $request, $punishmentCategoryID)
    {
        $access = $this->getBlackBookAccess('manage_punishment_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $cat = BlackBookPunishmentCategory::where('schoolID', $schoolID)
            ->where('punishmentCategoryID', $punishmentCategoryID)
            ->first();

        if (!$cat) {
            return $this->jsonError('Category not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'punishment_type' => 'required|in:one_time,continuous',
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $typeFields = $this->normalizePunishmentTypeFields($request->punishment_type);

        $cat->update([
            'name' => trim($request->name),
            'punishment_type' => $typeFields['punishment_type'],
            'requires_monitoring' => $typeFields['requires_monitoring'],
        ]);

        return response()->json(['success' => true, 'message' => 'Punishment category updated.', 'category' => $cat]);
    }

    public function deletePunishmentCategory($punishmentCategoryID)
    {
        $access = $this->getBlackBookAccess('manage_punishment_categories');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $cat = BlackBookPunishmentCategory::where('schoolID', $schoolID)
            ->where('punishmentCategoryID', $punishmentCategoryID)
            ->first();

        if (!$cat) {
            return $this->jsonError('Category not found.', 404);
        }

        $cat->update(['status' => 'Inactive']);

        return response()->json(['success' => true, 'message' => 'Punishment category deleted.']);
    }

    // --- Students search ---

    public function searchStudents(Request $request)
    {
        $canReport = app(BlackBookAccessService::class)->canReportStudent();
        $canManage = $this->getBlackBookAccess() !== null;
        if (!$canReport && !$canManage) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized', 403);
        }
        $search = trim((string) $request->input('search', ''));
        if (strlen($search) < 2) {
            return response()->json(['success' => true, 'students' => []]);
        }

        $students = Student::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name)"), 'like', "%{$search}%");
            })
            ->with(['subclass.class', 'currentEnrollment.subclass.class', 'currentEnrollment.classModel'])
            ->limit(20)
            ->get()
            ->map(function ($student) {
                $className = $this->formatStudentClass($student);
                return [
                    'studentID' => $student->studentID,
                    'enrollmentID' => optional($student->currentEnrollment)->enrollmentID,
                    'full_name' => $this->formatStudentName($student),
                    'admission_number' => $student->admission_number,
                    'class_name' => $className,
                    'display' => $this->formatStudentName($student) . ' — ' . $className . ' — Adm: ' . ($student->admission_number ?? 'N/A'),
                ];
            });

        return response()->json(['success' => true, 'students' => $students]);
    }

    // --- Reports ---

    public function storeReport(Request $request)
    {
        if (!app(BlackBookAccessService::class)->canReportStudent()) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized', 403);
        }
        $validator = Validator::make($request->all(), [
            'studentID' => 'required|integer',
            'problem_categoryID' => 'required|integer',
            'description' => 'required|string|min:5|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $student = Student::where('schoolID', $schoolID)
            ->where('studentID', $request->studentID)
            ->where('status', 'Active')
            ->with('parent')
            ->first();

        if (!$student) {
            return $this->jsonError('Student not found.', 404);
        }

        $category = BlackBookProblemCategory::where('schoolID', $schoolID)
            ->where('categoryID', $request->problem_categoryID)
            ->where('status', 'Active')
            ->first();

        if (!$category) {
            return $this->jsonError('Problem category not found.', 404);
        }

        $actor = $this->currentActor();

        $report = BlackBookReport::create([
            'schoolID' => $schoolID,
            'studentID' => $student->studentID,
            'enrollmentID' => app(\App\Services\StudentEnrollmentService::class)
                ->bestEnrollmentID($student->studentID, (int) $schoolID),
            'problem_categoryID' => $category->categoryID,
            'description' => trim($request->description),
            'reported_by_type' => $actor['type'],
            'reported_by_id' => $actor['id'],
            'reported_by_name' => $actor['name'],
            'status' => 'pending',
        ]);

        $this->notifyAdminSms($report);

        $sendParentSms = $request->boolean('send_parent_sms');
        $parentSmsSent = false;
        if ($sendParentSms) {
            $parentSmsSent = $this->notifyParentOnReport($report);
        }

        $message = 'Student reported successfully. Admin has been notified.';
        if ($sendParentSms) {
            $message .= $parentSmsSent
                ? ' Parent has been notified by SMS.'
                : ' Parent SMS was not sent (no parent phone or sending failed).';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'parent_sms_sent' => $parentSmsSent,
            'report' => $this->formatReport($report->fresh(['student.subclass.class', 'problemCategory', 'student.parent'])),
        ]);
    }

    public function listReports(Request $request)
    {
        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized', 403);
        }

        $status = $request->input('status');
        $statuses = $this->parseReportStatuses(is_string($status) ? $status : null);
        $mine = $request->boolean('mine');
        $studentID = $request->input('studentID');

        if ($mine) {
            if (!app(BlackBookAccessService::class)->canReportStudent()) {
                return $this->jsonError('Unauthorized', 403);
            }
        } elseif ($statuses && count($statuses) === 1 && $statuses[0] === 'resolved') {
            if (!$this->getBlackBookAccess('view_resolved')) {
                return $this->jsonError('Unauthorized', 403);
            }
        } elseif ($statuses && count($statuses) === 1 && $statuses[0] === 'monitored') {
            if (!$this->getBlackBookAccess('manage_pending')) {
                return $this->jsonError('Unauthorized', 403);
            }
        } else {
            if (!$this->getBlackBookAccess('manage_pending')) {
                return $this->jsonError('Unauthorized', 403);
            }
        }

        $query = BlackBookReport::where('schoolID', $schoolID)
            ->with(['student.subclass.class', 'problemCategory', 'punishmentCategory', 'student.parent']);

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        if ($mine) {
            $actor = $this->currentActor();
            $query->where('reported_by_name', $actor['name']);
        }

        if ($studentID) {
            $query->where('studentID', (int) $studentID);
        }

        $limit = ($statuses && in_array('resolved', $statuses, true)) ? 500 : 200;
        $reports = $query->orderByDesc('created_at')->limit($limit)->get()
            ->map(fn ($r) => $this->formatReport($r));

        return response()->json(['success' => true, 'reports' => $reports]);
    }

    public function markViewed($reportID)
    {
        $access = $this->getBlackBookAccess('manage_pending');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $report = BlackBookReport::where('schoolID', $schoolID)->where('reportID', $reportID)->first();
        if (!$report) {
            return $this->jsonError('Report not found.', 404);
        }

        if ($report->status === 'pending') {
            $actor = $this->currentActor();
            $report->update([
                'status' => 'viewed',
                'viewed_at' => now(),
                'viewed_by_name' => $actor['name'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report marked as viewed.',
            'report' => $this->formatReport($report->fresh(['student.subclass.class', 'problemCategory', 'punishmentCategory', 'student.parent'])),
        ]);
    }

    public function resolveReport(Request $request, $reportID)
    {
        $access = $this->getBlackBookAccess('manage_pending');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $validator = Validator::make($request->all(), [
            'resolve_method' => 'required|in:parent_call,punishment',
            'punishment_categoryID' => 'required_if:resolve_method,punishment|nullable|integer',
            'punishment_description' => 'required_if:resolve_method,punishment|nullable|string|max:2000',
            'parent_sms_message' => 'nullable|string|max:500',
            'send_parent_sms' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->jsonError($validator->errors()->first(), 422);
        }

        $report = BlackBookReport::where('schoolID', $schoolID)
            ->where('reportID', $reportID)
            ->with(['student.parent', 'student.subclass.class', 'problemCategory'])
            ->first();

        if (!$report) {
            return $this->jsonError('Report not found.', 404);
        }

        if (in_array($report->status, ['resolved', 'monitored'], true)) {
            return $this->jsonError('Report already resolved.', 422);
        }

        $actor = $this->currentActor();
        $method = $request->resolve_method;
        $parentSmsSent = false;
        $finalStatus = 'resolved';

        if ($method === 'punishment') {
            $punCat = BlackBookPunishmentCategory::where('schoolID', $schoolID)
                ->where('punishmentCategoryID', $request->punishment_categoryID)
                ->where('status', 'Active')
                ->first();

            if (!$punCat) {
                return $this->jsonError('Punishment category not found.', 404);
            }

            $typeFields = $this->normalizePunishmentTypeFields($punCat->punishment_type);

            $report->punishment_categoryID = $punCat->punishmentCategoryID;
            $report->punishment_description = trim((string) $request->punishment_description);
            $report->punishment_type = $typeFields['punishment_type'];
            $report->requires_monitoring = $typeFields['requires_monitoring'];

            if ($typeFields['punishment_type'] === 'continuous') {
                $request->validate(['monitoring_deadline' => 'required|date|after_or_equal:today']);
                $report->monitoring_deadline = $request->monitoring_deadline;
                $finalStatus = 'monitored';
            } else {
                $report->monitoring_deadline = null;
                $report->monitoring_ended_at = null;
                $finalStatus = 'resolved';
            }
        } else {
            $report->punishment_type = null;
            $report->requires_monitoring = false;
            $report->monitoring_deadline = null;
            $report->monitoring_ended_at = null;
        }

        $smsMessage = trim((string) $request->parent_sms_message);
        if ($request->boolean('send_parent_sms', false)) {
            if ($smsMessage === '') {
                return $this->jsonError('Enter SMS message or turn OFF parent notification.', 422);
            }

            $report->parent_sms_message = $smsMessage;
            $parentPhone = $report->student?->parent?->phone;
            if ($parentPhone) {
                try {
                    $sms = new SmsService();
                    $school = School::find($schoolID);
                    $result = $sms->sendSms(
                        $parentPhone,
                        $sms->formatMessage($smsMessage, $school->school_name ?? null)
                    );
                    $parentSmsSent = !empty($result['success']);
                } catch (\Throwable $e) {
                    \Log::warning('Black Book parent SMS failed: ' . $e->getMessage());
                }
            }
            $report->parent_sms_sent = $parentSmsSent;
        } else {
            $report->parent_sms_message = $smsMessage !== '' ? $smsMessage : null;
            $report->parent_sms_sent = false;
        }

        $report->resolve_method = $method;
        $report->status = $finalStatus;
        $report->resolved_at = now();
        $report->resolved_by_name = $actor['name'];

        if (!$report->viewed_at) {
            $report->viewed_at = now();
            $report->viewed_by_name = $actor['name'];
        }

        $report->save();

        $message = $finalStatus === 'monitored'
            ? ($parentSmsSent
                ? 'Punishment assigned and parent notified via SMS. Student is under monitoring.'
                : 'Punishment assigned. Student is now under monitoring until punishment ends.')
            : ($parentSmsSent
                ? 'Report resolved successfully. Parent notified via SMS.'
                : ($request->boolean('send_parent_sms', false)
                    ? 'Report resolved. Parent SMS was not sent (no phone or failed).'
                    : 'Report resolved successfully.'));

        return response()->json([
            'success' => true,
            'message' => $message,
            'report' => $this->formatReport($report->fresh(['student.subclass.class', 'problemCategory', 'punishmentCategory', 'student.parent'])),
        ]);
    }

    public function endMonitoring(Request $request, $reportID)
    {
        $access = $this->getBlackBookAccess('manage_pending');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $report = BlackBookReport::where('schoolID', $schoolID)
            ->where('reportID', $reportID)
            ->with(['student.parent', 'student.subclass.class', 'problemCategory', 'punishmentCategory'])
            ->first();

        if (!$report) {
            return $this->jsonError('Report not found.', 404);
        }

        if ($report->status !== 'monitored') {
            return $this->jsonError('This punishment is not under monitoring.', 422);
        }

        $parentSmsSent = false;
        $update = [
            'status' => 'resolved',
            'monitoring_ended_at' => now(),
        ];

        $smsMessage = trim((string) $request->parent_sms_message);
        if ($request->boolean('send_parent_sms', false)) {
            if ($smsMessage === '') {
                return $this->jsonError('Enter SMS message or turn OFF parent notification.', 422);
            }

            $update['parent_sms_message'] = $smsMessage;
            $parentPhone = $report->student?->parent?->phone;
            if ($parentPhone) {
                try {
                    $sms = new SmsService();
                    $school = School::find($schoolID);
                    $result = $sms->sendSms(
                        $parentPhone,
                        $sms->formatMessage($smsMessage, $school->school_name ?? null)
                    );
                    $parentSmsSent = !empty($result['success']);
                } catch (\Throwable $e) {
                    \Log::warning('Black Book end punishment SMS failed: ' . $e->getMessage());
                }
            }
            $update['parent_sms_sent'] = $parentSmsSent;
        }

        $report->update($update);

        $message = $request->boolean('send_parent_sms', false)
            ? ($parentSmsSent
                ? 'Punishment ended. Parent notified via SMS. Case is now fully resolved.'
                : 'Punishment ended. Parent SMS was not sent (no phone or failed).')
            : 'Punishment ended. Case is now fully resolved.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'report' => $this->formatReport($report->fresh(['student.subclass.class', 'problemCategory', 'punishmentCategory', 'student.parent'])),
        ]);
    }

    public function studentReportSummary(Request $request)
    {
        $access = $this->getBlackBookAccess('view_dashboard');
        if (!$access) {
            $access = $this->getBlackBookAccess('view_reports');
        }
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $studentID = (int) $request->input('studentID');
        if (!$studentID) {
            return $this->jsonError('Student ID required.', 422);
        }

        $student = Student::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->with(['subclass.class'])
            ->first();

        if (!$student) {
            return $this->jsonError('Student not found.', 404);
        }

        $count = BlackBookReport::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->count();

        $reports = BlackBookReport::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->with(['problemCategory', 'punishmentCategory'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => $this->formatReport($r));

        return response()->json([
            'success' => true,
            'student' => [
                'studentID' => $student->studentID,
                'student_name' => $this->formatStudentName($student),
                'class_name' => $this->formatStudentClass($student),
                'admission_number' => $student->admission_number,
                'report_count' => $count,
            ],
            'reports' => $reports,
        ]);
    }

    public function reportAnalytics(Request $request)
    {
        $access = $this->getBlackBookAccess('view_reports');
        if (!$access) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $type = $request->input('type', 'all');
        $studentID = $request->input('studentID');
        $classID = $request->input('classID');

        if ($type === 'specific') {
            if (!$studentID) {
                return response()->json([
                    'success' => true,
                    'rows' => [],
                    'message' => 'Select a student to view reports.',
                ]);
            }

            $student = Student::where('schoolID', $schoolID)
                ->where('studentID', $studentID)
                ->with(['subclass.class'])
                ->first();

            if (!$student) {
                return $this->jsonError('Student not found.', 404);
            }

            $count = BlackBookReport::where('schoolID', $schoolID)
                ->where('studentID', $studentID)
                ->count();

            if ($count === 0) {
                return response()->json([
                    'success' => true,
                    'rows' => [],
                    'message' => 'This student has not been reported yet.',
                ]);
            }

            $reports = BlackBookReport::where('schoolID', $schoolID)
                ->where('studentID', $studentID)
                ->with(['problemCategory', 'punishmentCategory'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($r) => $this->formatReport($r));

            return response()->json([
                'success' => true,
                'rows' => [[
                    'studentID' => $student->studentID,
                    'student_name' => $this->formatStudentName($student),
                    'class_name' => $this->formatStudentClass($student),
                    'admission_number' => $student->admission_number,
                    'report_count' => $count,
                    'reports' => $reports,
                ]],
            ]);
        }

        $query = BlackBookReport::where('black_book_reports.schoolID', $schoolID)
            ->join('students', 'students.studentID', '=', 'black_book_reports.studentID')
            ->leftJoin('subclasses', 'subclasses.subclassID', '=', 'students.subclassID')
            ->leftJoin('classes', 'classes.classID', '=', 'subclasses.classID')
            ->select(
                'students.studentID',
                DB::raw("CONCAT(students.first_name, ' ', COALESCE(students.middle_name, ''), ' ', students.last_name) as student_name"),
                'students.admission_number',
                DB::raw("CONCAT(COALESCE(classes.class_name, ''), ' ', COALESCE(subclasses.subclass_name, '')) as class_name"),
                DB::raw('COUNT(black_book_reports.reportID) as report_count')
            )
            ->groupBy('students.studentID', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_number', 'classes.class_name', 'subclasses.subclass_name');

        if ($type === 'class' && $classID) {
            $query->where('classes.classID', (int) $classID);
        }

        $rows = $query->orderByDesc('report_count')->get();

        $result = [];
        foreach ($rows as $row) {
            $reports = BlackBookReport::where('schoolID', $schoolID)
                ->where('studentID', $row->studentID)
                ->with(['problemCategory', 'punishmentCategory'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($r) => $this->formatReport($r));

            $result[] = [
                'studentID' => (int) $row->studentID,
                'student_name' => trim(preg_replace('/\s+/', ' ', $row->student_name)),
                'admission_number' => $row->admission_number,
                'class_name' => trim($row->class_name) ?: '—',
                'report_count' => (int) $row->report_count,
                'reports' => $reports,
            ];
        }

        return response()->json(['success' => true, 'rows' => $result]);
    }

    public function exportPdf(Request $request)
    {
        $access = $this->getBlackBookAccess('view_reports');
        if (!$access) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = (int) $access['schoolID'];
        $type = $request->input('type', 'all');
        $studentID = $request->input('studentID');
        $classID = $request->input('classID');

        $analyticsRequest = new Request([
            'type' => $type,
            'studentID' => $studentID,
            'classID' => $classID,
        ]);

        $data = json_decode($this->reportAnalytics($analyticsRequest)->getContent(), true);
        $rows = $data['rows'] ?? [];
        $school = School::find($schoolID);

        $pdf = PDF::loadView('Admin.pdf.black_book_report', [
            'school' => $school,
            'rows' => $rows,
            'type' => $type,
            'generated_at' => now()->format('Y-m-d H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('black_book_report_' . date('Y-m-d_His') . '.pdf');
    }

    public function listBlackBookActors()
    {
        $access = $this->getBlackBookAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized', 403);
        }

        $actors = BlackBookActor::where('schoolID', (int) $access['schoolID'])
            ->where('status', 'active')
            ->orderBy('actor_name')
            ->get()
            ->map(fn ($a) => $this->formatBlackBookActorUi($a))
            ->values();

        return response()->json(['success' => true, 'actors' => $actors]);
    }

    public function storeBlackBookActor(Request $request)
    {
        $access = $this->getBlackBookAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $request->validate([
            'actor_type' => 'required|in:teacher,staff',
            'actor_ref_id' => 'required|integer',
            'privileges' => 'required|array',
        ]);

        $accessService = app(BlackBookAccessService::class);
        $name = $accessService->resolvePersonName($request->actor_type, (int) $request->actor_ref_id, $schoolID);
        if (!$name) {
            return $this->jsonError('Selected person not found or inactive.', 404);
        }

        $exists = BlackBookActor::where('schoolID', $schoolID)
            ->where('actor_type', $request->actor_type)
            ->where('actor_ref_id', (int) $request->actor_ref_id)
            ->where('status', 'active')
            ->exists();
        if ($exists) {
            return $this->jsonError('This person already has Black Book access.', 422);
        }

        $privileges = array_merge(
            BlackBookActor::defaultPrivileges(),
            collect($request->privileges)->mapWithKeys(fn ($v, $k) => [$k => (bool) $v])->all()
        );

        if (!collect($privileges)->contains(true)) {
            return $this->jsonError('Select at least one privilege.', 422);
        }

        $actor = BlackBookActor::create([
            'schoolID' => $schoolID,
            'actor_type' => $request->actor_type,
            'actor_ref_id' => (int) $request->actor_ref_id,
            'actor_name' => $name,
            'privileges' => $privileges,
            'status' => 'active',
            'granted_by' => $this->currentActor()['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Black Book actor saved.',
            'actor' => $this->formatBlackBookActorUi($actor),
        ]);
    }

    public function updateBlackBookActor(Request $request, $actorID)
    {
        $access = $this->getBlackBookAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized', 403);
        }

        $schoolID = (int) $access['schoolID'];
        $actor = BlackBookActor::where('schoolID', $schoolID)
            ->where('actorID', $actorID)
            ->where('status', 'active')
            ->first();

        if (!$actor) {
            return $this->jsonError('Actor not found.', 404);
        }

        $request->validate(['privileges' => 'required|array']);

        $privileges = array_merge(
            BlackBookActor::defaultPrivileges(),
            collect($request->privileges)->mapWithKeys(fn ($v, $k) => [$k => (bool) $v])->all()
        );

        if (!collect($privileges)->contains(true)) {
            return $this->jsonError('Select at least one privilege.', 422);
        }

        $actor->update(['privileges' => $privileges]);

        return response()->json([
            'success' => true,
            'message' => 'Actor privileges updated.',
            'actor' => $this->formatBlackBookActorUi($actor->fresh()),
        ]);
    }

    public function deleteBlackBookActor($actorID)
    {
        $access = $this->getBlackBookAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized', 403);
        }

        $actor = BlackBookActor::where('schoolID', (int) $access['schoolID'])
            ->where('actorID', $actorID)
            ->where('status', 'active')
            ->first();

        if (!$actor) {
            return $this->jsonError('Actor not found.', 404);
        }

        $actor->update(['status' => 'inactive']);

        return response()->json(['success' => true, 'message' => 'Actor access removed.']);
    }
}
