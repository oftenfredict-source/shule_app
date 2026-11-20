<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HomePackageController;
use App\Http\Controllers\ParentsContoller;
use App\Http\Controllers\SubjectNoteController;
use App\Models\ParentModel;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\MobileApiTokenService;
use App\Services\ParentFingerprintPortalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ParentMobileApiController extends Controller
{
    public function __construct(private MobileApiTokenService $tokens)
    {
    }

    public function me(Request $request)
    {
        $user = $request->attributes->get('mobile_user');
        $token = $request->attributes->get('mobile_token');
        $type = strtolower((string) $user->user_type);

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ],
            'token_expires_at' => optional($token->expires_at)->toIso8601String(),
        ];

        if ($type === 'parent') {
            $parent = $request->attributes->get('mobile_parent');
            $data['parent'] = $this->parentPayload($parent);
            $data['school'] = $this->schoolPayload($parent->school ?? School::find($parent->schoolID));
            $data['children'] = $this->children($request)->map(fn (Student $student) => $this->studentPayload($student))->values();
        } elseif ($type === 'teacher') {
            $teacher = $request->attributes->get('mobile_teacher') ?: Teacher::find($token->teacherID);
            $data['teacher'] = $teacher ? [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'full_name' => trim($teacher->first_name . ' ' . $teacher->last_name),
                'employee_number' => $teacher->employee_number,
                'schoolID' => $teacher->schoolID,
            ] : null;
            $data['school'] = $teacher ? $this->schoolPayload(School::find($teacher->schoolID)) : null;
            $data['schoolID'] = $teacher ? (int) $teacher->schoolID : $token->schoolID;
            if ($teacher) {
                // Same menu/permission flags the website sidebar uses (roles, HOD, class teacher, badges, ...).
                $data['capabilities'] = app(TeacherMobileApiController::class)->capabilitiesPayload($teacher);
            }
        } elseif ($type === 'boardmember') {
            $member = $request->attributes->get('mobile_board_member');
            $data['board_member'] = app(BoardMobileApiController::class)->profilePayload($member);
            $data['school'] = $this->schoolPayload(School::find($member->schoolID));
            $data['schoolID'] = (int) $member->schoolID;
        } elseif ($type === 'watchman') {
            $watchman = $request->attributes->get('mobile_watchman');
            $data['watchman'] = app(WatchmanMobileApiController::class)->watchmanPayload($watchman);
            $data['school'] = $watchman
                ? $this->schoolPayload(School::find($watchman->schoolID))
                : null;
            $data['schoolID'] = $watchman ? (int) $watchman->schoolID : $token->schoolID;
        } else {
            $data['schoolID'] = $token->schoolID;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function logout(Request $request)
    {
        $this->tokens->revokeFromRequest($request);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->attributes->get('mobile_user');
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = $validated['password'];
        $user->save();

        $token = $request->attributes->get('mobile_token');
        $this->tokens->revokeOtherTokens((int) $user->id, (int) $token->id);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function dashboard(Request $request)
    {
        $raw = app(ApiController::class)->parentDashboard($request);
        $payload = $raw->getData(true);
        if (!($payload['success'] ?? false)) {
            return $raw;
        }

        $data = $payload['data'] ?? [];
        $data['result_cards'] = $this->shapeResultCards($data['recent_results'] ?? []);
        $data['statistics']['recent_results_count'] = count($data['result_cards']);
        $data['statistics']['recent_results_note'] = 'Tap a result_cards[] item and open /results with its open query.';

        // Full alerts: bell notifications + dashboard live alerts (exam published, attendance, upcoming).
        $bell = app(ParentsContoller::class)->headerNotifications();
        $bellPayload = $bell->getData(true);
        $bellItems = collect($bellPayload['notifications'] ?? [])->map(function ($n) {
            return [
                'id' => $n['id'] ?? null,
                'source' => $n['source'] ?? ($n['type'] ?? 'alert'),
                'type' => $n['type'] ?? 'alert',
                'title' => $n['title'] ?? null,
                'message' => $n['message'] ?? null,
                'icon' => $n['icon'] ?? 'bx-bell',
                'is_viewed' => (bool) ($n['is_viewed'] ?? false),
                'created_at' => $n['created_at'] ?? ($n['date'] ?? null),
                'screen' => $n['screen'] ?? null,
                'open' => $this->alertOpenParams($n),
            ];
        });

        $liveAlerts = collect($data['notifications'] ?? [])->map(function ($n, $i) {
            return [
                'id' => 'live_' . $i,
                'source' => $n['type'] ?? 'alert',
                'type' => $n['type'] ?? 'alert',
                'title' => $n['title'] ?? null,
                'message' => $n['message'] ?? null,
                'icon' => $n['icon'] ?? 'bi-bell',
                'is_viewed' => false,
                'created_at' => $n['date'] ?? null,
                'screen' => match ($n['type'] ?? '') {
                    'result' => 'results',
                    'attendance' => 'attendance',
                    'exam' => 'exams',
                    default => null,
                },
                'open' => null,
            ];
        });

        $data['alerts'] = $bellItems->concat($liveAlerts)->values();
        $data['alerts_unread'] = (int) ($bellPayload['unread'] ?? 0);
        $data['services'] = $this->serviceCards();
        $data['ui'] = [
            'services_layout' => 'cards',
            'result_cards_note' => 'Do not open bare GET /results. Use result_cards[].open query.',
            'alerts_note' => 'Show all alerts[]. Tap uses screen + open when present.',
        ];

        return response()->json([
            'success' => true,
            'message' => $payload['message'] ?? 'Parent dashboard data retrieved successfully',
            'data' => $data,
        ]);
    }

    /** GET /services — teacher-style service cards (website parent menu). */
    public function services(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Services',
                'layout' => 'cards',
                'sections' => [
                    [
                        'key' => 'academics',
                        'title' => 'Academics and activities',
                        'cards' => collect($this->serviceCards())->where('section', 'academics')->values(),
                    ],
                    [
                        'key' => 'finance',
                        'title' => 'Finance',
                        'cards' => collect($this->serviceCards())->where('section', 'finance')->values(),
                    ],
                    [
                        'key' => 'account',
                        'title' => 'Account',
                        'cards' => collect($this->serviceCards())->where('section', 'account')->values(),
                    ],
                ],
                'cards' => $this->serviceCards(),
            ],
        ]);
    }

    public function childrenList(Request $request)
    {
        $parent = $request->attributes->get('mobile_parent');

        return response()->json([
            'success' => true,
            'data' => [
                'parent' => $this->parentPayload($parent),
                'children' => $this->children($request)->map(fn (Student $student) => $this->studentPayload($student))->values(),
            ],
        ]);
    }

    public function academicYears(Request $request)
    {
        $schoolID = (int) $request->attributes->get('mobile_school_id');
        $studentID = (int) ($request->input('student') ?: $request->input('studentID') ?: 0);
        if ($studentID > 0) {
            $this->ownedChild($request, $studentID);
        }

        $context = app(ParentsContoller::class)->mobileAcademicYearContext(
            $schoolID,
            $studentID > 0 ? $studentID : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Academic years retrieved successfully',
            'data' => $context,
        ]);
    }

    public function publishedExams(Request $request)
    {
        return app(ParentsContoller::class)->getPublishedExams($request);
    }

    public function results(Request $request)
    {
        return app(ParentsContoller::class)->apiGetParentResults($request);
    }

    public function attendance(Request $request)
    {
        return app(ParentsContoller::class)->apiGetParentAttendance($request);
    }

    public function attendanceOverview(Request $request)
    {
        return $this->monthOverview($request, 'system');
    }

    public function attendanceCalendar(Request $request, int $studentID)
    {
        return $this->monthCalendar($request, $studentID, 'system');
    }

    public function fingerprintOverview(Request $request)
    {
        return $this->monthOverview($request, 'fingerprint');
    }

    public function fingerprintCalendar(Request $request, int $studentID)
    {
        return $this->monthCalendar($request, $studentID, 'fingerprint');
    }

    public function payments(Request $request)
    {
        $raw = app(ParentsContoller::class)->apiGetParentPayments($request);
        $payload = $raw->getData(true);
        if (!($payload['success'] ?? false)) {
            return $raw;
        }

        $data = $payload['data'] ?? $payload;
        $children = collect($data['students'] ?? $data['children'] ?? [])->map(function ($s) {
            if (is_array($s)) {
                return [
                    'studentID' => (int) ($s['studentID'] ?? 0),
                    'label' => $s['full_name'] ?? trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                ];
            }

            return [
                'studentID' => (int) ($s->studentID ?? 0),
                'label' => trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')),
            ];
        })->values();

        $years = collect($data['available_years'] ?? $data['years'] ?? [])->map(function ($y) {
            return is_array($y)
                ? ['value' => $y['year'] ?? $y['calendar_year'] ?? $y['value'] ?? $y, 'label' => (string) ($y['label'] ?? $y['year'] ?? $y)]
                : ['value' => $y, 'label' => (string) $y];
        })->values();

        $data['filter_ui'] = [
            'icon' => 'filter',
            'label' => 'Filter',
            'presentation' => 'bottom_sheet',
            'note' => 'Show a filter icon on Make Payment. On tap, open a bottom sheet (bottom navigation sheet) with these fields, then reload GET /payments with query params.',
            'fields' => [
                [
                    'key' => 'student',
                    'label' => 'Child',
                    'type' => 'select',
                    'query_param' => 'student',
                    'options' => $children->prepend(['studentID' => null, 'label' => 'All children'])->values(),
                ],
                [
                    'key' => 'year',
                    'label' => 'Year',
                    'type' => 'select',
                    'query_param' => 'year',
                    'options' => $years,
                ],
                [
                    'key' => 'fee_type',
                    'label' => 'Fee type',
                    'type' => 'select',
                    'query_param' => 'fee_type',
                    'options' => [
                        ['value' => '', 'label' => 'All'],
                        ['value' => 'tuition', 'label' => 'Tuition'],
                        ['value' => 'other', 'label' => 'Other'],
                    ],
                ],
                [
                    'key' => 'search',
                    'label' => 'Search',
                    'type' => 'text',
                    'query_param' => 'search',
                    'placeholder' => 'Name or admission number',
                ],
            ],
            'apply_path' => '/payments',
            'apply_method' => 'GET',
        ];

        return response()->json([
            'success' => true,
            'message' => $payload['message'] ?? 'Payments retrieved successfully',
            'data' => $data,
        ]);
    }

    public function feesSummary(Request $request)
    {
        return app(ParentsContoller::class)->apiGetParentFeesSummary($request);
    }

    public function requestControlNumber(Request $request, int $studentID)
    {
        return app(ParentsContoller::class)->request_control_number($request, $studentID);
    }

    public function subjects(Request $request, int $studentID)
    {
        $this->ownedChild($request, $studentID, true);

        return app(ParentsContoller::class)->getStudentSubjects($studentID);
    }

    public function electSubject(Request $request)
    {
        return app(ParentsContoller::class)->electSubject($request);
    }

    public function deselectSubject(Request $request)
    {
        return app(ParentsContoller::class)->deselectSubject($request);
    }

    public function learningChildren(Request $request)
    {
        $request->merge([
            'kind' => $this->learningKind($request),
        ]);

        return app(SubjectNoteController::class)->parentIndex($request);
    }

    public function learningSubjects(Request $request, int $studentID)
    {
        $request->merge([
            'kind' => $this->learningKind($request),
        ]);

        return app(SubjectNoteController::class)->parentChildSubjects($request, $studentID);
    }

    public function learningNotes(Request $request, int $studentID, int $classSubjectID)
    {
        $request->merge([
            'kind' => $this->learningKind($request),
        ]);

        return app(SubjectNoteController::class)->parentSubjectNotes($request, $studentID, $classSubjectID);
    }

    public function noteFile(Request $request, int $noteID)
    {
        $notes = app(SubjectNoteController::class);

        return $request->boolean('inline')
            ? $notes->viewFile($noteID)
            : $notes->downloadFile($noteID);
    }

    public function homePackages(Request $request)
    {
        return app(HomePackageController::class)->parentIndex();
    }

    public function holidayPackages(Request $request, int $holidayID)
    {
        return app(HomePackageController::class)->parentHolidayPackages($request, $holidayID);
    }

    public function homePackageFile(Request $request, int $homePackageID)
    {
        $packages = app(HomePackageController::class);

        return $request->boolean('inline')
            ? $packages->viewFile($homePackageID)
            : $packages->downloadFile($homePackageID);
    }

    public function notifications(Request $request)
    {
        return app(ParentsContoller::class)->headerNotifications();
    }

    public function markNotificationsRead(Request $request)
    {
        return app(ParentsContoller::class)->markHeaderNotificationsRead($request);
    }

    public function permissions(Request $request)
    {
        $parentID = (int) $request->attributes->get('mobile_parent_id');
        $schoolID = (int) $request->attributes->get('mobile_school_id');
        $students = Student::where('schoolID', $schoolID)
            ->where('parentID', $parentID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        $names = $students->mapWithKeys(function (Student $student) {
            return [$student->studentID => trim(($student->first_name ?? '').' '.($student->last_name ?? ''))];
        });

        $rows = collect();
        if ($students->isNotEmpty() && Schema::hasTable('student_permissions')) {
            $query = DB::table('student_permissions')
                ->where('schoolID', $schoolID)
                ->where('requester_type', 'parent')
                ->where('requester_id', $parentID)
                ->whereIn('student_id', $students->pluck('studentID'));

            $filterType = $request->input('filter_type', 'all');
            if ($filterType === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('start_date', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('start_date', '<=', $request->input('date_to'));
                }
            } elseif ($filterType === 'month' && $request->filled('filter_month')) {
                $query->whereMonth('start_date', $request->input('filter_month'))
                    ->whereYear('start_date', $request->input('filter_month_year', date('Y')));
            } elseif ($filterType === 'year' && $request->filled('filter_year')) {
                $query->whereYear('start_date', $request->input('filter_year'));
            }

            if ($request->filled('status') && in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)) {
                $query->where('status', $request->input('status'));
            }

            $rows = $query->orderByDesc('created_at')->get()->map(function ($row) use ($names) {
                return [
                    'id' => (int) $row->id,
                    'student_id' => (int) $row->student_id,
                    'student_name' => $names[$row->student_id] ?? '',
                    'start_date' => $row->start_date,
                    'end_date' => $row->end_date,
                    'days_count' => isset($row->days_count) ? (int) $row->days_count : null,
                    'reason_type' => $row->reason_type,
                    'reason_description' => $row->reason_description,
                    'status' => $row->status,
                    'attachment_url' => !empty($row->attachment) ? url('uploads/permissions/'.$row->attachment) : null,
                    'is_read_by_parent' => (bool) ($row->is_read_by_parent ?? true),
                    'created_at' => $row->created_at,
                ];
            });
        }

        $unread = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        if (Schema::hasColumn('student_permissions', 'is_read_by_parent') && $students->isNotEmpty()) {
            foreach (array_keys($unread) as $status) {
                $unread[$status] = DB::table('student_permissions')
                    ->where('schoolID', $schoolID)
                    ->where('requester_type', 'parent')
                    ->where('requester_id', $parentID)
                    ->whereIn('student_id', $students->pluck('studentID'))
                    ->where('status', $status)
                    ->where('is_read_by_parent', false)
                    ->count();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'students' => $students->map(fn (Student $student) => $this->studentPayload($student))->values(),
                'pending' => $rows->where('status', 'pending')->values(),
                'approved' => $rows->where('status', 'approved')->values(),
                'rejected' => $rows->where('status', 'rejected')->values(),
                'unread' => $unread,
            ],
        ]);
    }

    public function storePermission(Request $request)
    {
        return app(ParentsContoller::class)->storeParentPermission($request);
    }

    public function markPermissionsRead(Request $request)
    {
        return app(ParentsContoller::class)->markParentPermissionTabRead($request);
    }

    private function monthOverview(Request $request, string $mode)
    {
        [$year, $month] = $this->monthYear($request);
        $search = strtolower(trim((string) $request->input('q', '')));
        $schoolID = (int) $request->attributes->get('mobile_school_id');
        $portal = app(ParentFingerprintPortalService::class);
        $parents = app(ParentsContoller::class);

        $children = [];
        foreach ($this->children($request) as $student) {
            if ($search !== '') {
                $haystack = strtolower(trim(
                    ($student->first_name ?? '').' '.
                    ($student->middle_name ?? '').' '.
                    ($student->last_name ?? '').' '.
                    ($student->admission_number ?? '')
                ));
                if (!str_contains($haystack, $search)) {
                    continue;
                }
            }

            $enabled = $mode !== 'fingerprint' || $portal->isStudentEnabled($student);
            $stats = null;
            if ($enabled) {
                $calendar = $mode === 'fingerprint'
                    ? $parents->mobileFingerprintCalendar($student->studentID, $schoolID, $year, $month)
                    : $parents->mobileAttendanceCalendar($student->studentID, $schoolID, $year, $month);
                $stats = $calendar['stats'] ?? null;
            }

            $card = $this->studentPayload($student);
            $card['fingerprint_enabled'] = $mode === 'fingerprint' ? $enabled : null;
            $card['stats'] = $stats;
            $children[] = $card;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mode' => $mode,
                'month' => $month,
                'year' => $year,
                'children' => $children,
            ],
        ]);
    }

    private function monthCalendar(Request $request, int $studentID, string $mode)
    {
        $student = $this->ownedChild($request, $studentID);
        [$year, $month] = $this->monthYear($request);
        $schoolID = (int) $request->attributes->get('mobile_school_id');

        if ($mode === 'fingerprint' && !app(ParentFingerprintPortalService::class)->isStudentEnabled($student)) {
            return response()->json([
                'success' => false,
                'message' => 'Fingerprint attendance is not enabled for this student\'s class.',
            ], 403);
        }

        $parents = app(ParentsContoller::class);
        $calendar = $mode === 'fingerprint'
            ? $parents->mobileFingerprintCalendar($student->studentID, $schoolID, $year, $month)
            : $parents->mobileAttendanceCalendar($student->studentID, $schoolID, $year, $month);

        $days = [];
        foreach ($calendar['rows'] ?? [] as $row) {
            foreach ($row as $cell) {
                $days[] = $cell;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mode' => $mode,
                'month' => $month,
                'year' => $year,
                'month_label' => Carbon::create($year, $month, 1)->locale(app()->getLocale())->isoFormat('MMMM Y'),
                'student' => $this->studentPayload($student),
                'stats' => $calendar['stats'] ?? [],
                'days' => $days,
                'legend' => $calendar['legend'] ?? [],
            ],
        ]);
    }

    private function children(Request $request)
    {
        return Student::with(['subclass.class'])
            ->where('parentID', (int) $request->attributes->get('mobile_parent_id'))
            ->where('schoolID', (int) $request->attributes->get('mobile_school_id'))
            ->where('status', '!=', 'Transferred')
            ->orderBy('first_name')
            ->get();
    }

    private function ownedChild(Request $request, int $studentID, bool $activeOnly = false): Student
    {
        $query = Student::with(['subclass.class'])
            ->where('studentID', $studentID)
            ->where('parentID', (int) $request->attributes->get('mobile_parent_id'))
            ->where('schoolID', (int) $request->attributes->get('mobile_school_id'))
            ->where('status', '!=', 'Transferred');

        if ($activeOnly) {
            $query->where('status', 'Active');
        }

        $student = $query->first();
        if (!$student) {
            abort(response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404));
        }

        return $student;
    }

    private function monthYear(Request $request): array
    {
        $month = max(1, min(12, (int) $request->input('month', (int) date('m'))));
        $year = (int) $request->input('year', (int) date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        return [$year, $month];
    }

    private function learningKind(Request $request): string
    {
        return strtolower((string) $request->input('kind', 'note')) === 'past_paper'
            ? 'past_paper'
            : 'note';
    }

    private function parentPayload(ParentModel $parent): array
    {
        return [
            'parentID' => $parent->parentID,
            'schoolID' => $parent->schoolID,
            'first_name' => $parent->first_name,
            'middle_name' => $parent->middle_name,
            'last_name' => $parent->last_name,
            'full_name' => trim(($parent->first_name ?? '').' '.($parent->middle_name ? $parent->middle_name.' ' : '').($parent->last_name ?? '')),
            'phone' => $parent->phone,
            'email' => $parent->email,
            'gender' => $parent->gender,
            'occupation' => $parent->occupation,
            'address' => $parent->address,
            'photo' => $parent->photo ? url('userImages/'.$parent->photo) : null,
        ];
    }

    private function schoolPayload(?School $school): ?array
    {
        if (!$school) {
            return null;
        }

        return [
            'schoolID' => $school->schoolID,
            'school_name' => $school->school_name,
            'school_type' => $school->school_type,
            'registration_number' => $school->registration_number ?? null,
            'logo' => School::publicAssetUrl($school->school_logo ?? null),
        ];
    }

    private function studentPayload(Student $student): array
    {
        $className = $student->subclass && $student->subclass->class
            ? $student->subclass->class->class_name
            : null;
        $subclassName = $student->subclass?->subclass_name;
        $yearService = app(\App\Services\SelectedAcademicYearService::class);
        $isALevel = $yearService->isALevelClassName($className);

        return [
            'studentID' => $student->studentID,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'full_name' => trim(($student->first_name ?? '').' '.($student->middle_name ? $student->middle_name.' ' : '').($student->last_name ?? '')),
            'admission_number' => $student->admission_number,
            'gender' => $student->gender,
            'status' => $student->status,
            'photo' => $student->photo ? url('userImages/'.$student->photo) : null,
            'class' => $className,
            'subclass' => $subclassName,
            'class_label' => trim(($className ?? '').' '.($subclassName ?? '')) ?: null,
            'is_a_level' => $isALevel,
            'level_label' => $isALevel ? 'A Level' : 'O Level',
        ];
    }

    /** Teacher-style service cards matching the parent website menu. */
    private function serviceCards(): array
    {
        return [
            ['key' => 'dashboard', 'section' => 'academics', 'title' => 'Dashboard', 'subtitle' => 'Home summary', 'icon' => 'home', 'path' => '/dashboard'],
            ['key' => 'results', 'section' => 'academics', 'title' => 'Exam results', 'subtitle' => 'Published exam marks', 'icon' => 'trophy', 'path' => '/results'],
            ['key' => 'reports', 'section' => 'academics', 'title' => 'Student report', 'subtitle' => 'Term report', 'icon' => 'book', 'path' => '/results?type=report'],
            ['key' => 'attendance', 'section' => 'academics', 'title' => 'System attendance', 'subtitle' => 'Class attendance', 'icon' => 'calendar', 'path' => '/attendance/overview'],
            ['key' => 'fingerprint', 'section' => 'academics', 'title' => 'Fingerprints', 'subtitle' => 'Fingerprint attendance', 'icon' => 'fingerprint', 'path' => '/fingerprint-attendance'],
            ['key' => 'subjects', 'section' => 'academics', 'title' => 'Subjects', 'subtitle' => 'Elect optional subjects', 'icon' => 'book-open', 'path' => '/children'],
            ['key' => 'home_packages', 'section' => 'academics', 'title' => 'Home package', 'subtitle' => 'Holiday packages', 'icon' => 'package', 'path' => '/home-packages'],
            ['key' => 'notes', 'section' => 'academics', 'title' => 'Notes', 'subtitle' => 'Subject notes', 'icon' => 'note', 'path' => '/learning?kind=note'],
            ['key' => 'past_papers', 'section' => 'academics', 'title' => 'Past papers', 'subtitle' => 'Exam papers', 'icon' => 'file', 'path' => '/learning?kind=past_paper'],
            ['key' => 'payments', 'section' => 'finance', 'title' => 'Make payment', 'subtitle' => 'Control numbers & payments', 'icon' => 'credit-card', 'path' => '/payments'],
            ['key' => 'fees', 'section' => 'finance', 'title' => 'Fees summary', 'subtitle' => 'Balances per child', 'icon' => 'wallet', 'path' => '/fees-summary'],
            ['key' => 'permissions', 'section' => 'account', 'title' => 'Permissions', 'subtitle' => 'Leave / permission requests', 'icon' => 'shield', 'path' => '/permissions'],
            ['key' => 'alerts', 'section' => 'account', 'title' => 'Alerts', 'subtitle' => 'All notifications', 'icon' => 'bell', 'path' => '/notifications'],
        ];
    }

    /**
     * Group subject-level recent_results into tappable exam cards with deep-link query.
     */
    private function shapeResultCards(array $recentResults): array
    {
        return collect($recentResults)
            ->groupBy(function ($row) {
                $examID = $row['examination']['examID'] ?? 0;
                $studentID = $row['student']['studentID'] ?? 0;

                return $examID . ':' . $studentID;
            })
            ->map(function ($rows) {
                $first = $rows->first();
                $examID = (int) ($first['examination']['examID'] ?? 0);
                $studentID = (int) ($first['student']['studentID'] ?? 0);
                $examName = $first['examination']['exam_name'] ?? 'Exam';
                $studentName = trim(($first['student']['first_name'] ?? '') . ' ' . ($first['student']['last_name'] ?? ''));
                $exam = $examID > 0 ? \App\Models\Examination::find($examID) : null;
                $term = $exam->term ?? null;
                $year = $exam->year ?? null;
                $academicYearID = $exam->academic_yearID ?? null;

                // Normalize website term tokens
                $termKey = null;
                if (is_string($term) && $term !== '') {
                    $t = strtolower($term);
                    $termKey = match (true) {
                        str_contains($t, '1') || str_contains($t, 'first') => 'first_term',
                        str_contains($t, '2') || str_contains($t, 'second') => 'second_term',
                        str_contains($t, '3') || str_contains($t, 'third') => 'third_term',
                        default => $t,
                    };
                }

                $open = array_filter([
                    'student' => $studentID ?: null,
                    'exam' => $examID ?: null,
                    'type' => 'exam',
                    'term' => $termKey,
                    'year' => $year,
                    'academic_yearID' => $academicYearID,
                ], fn ($v) => $v !== null && $v !== '');

                return [
                    'title' => $examName,
                    'subtitle' => $studentName,
                    'studentID' => $studentID,
                    'student_name' => $studentName,
                    'examID' => $examID,
                    'exam_name' => $examName,
                    'term' => $termKey,
                    'year' => $year,
                    'academic_yearID' => $academicYearID,
                    'subject_count' => $rows->count(),
                    'date' => $first['date'] ?? null,
                    'screen' => 'results',
                    'open' => $open,
                    'open_path' => '/results?' . http_build_query($open),
                ];
            })
            ->values()
            ->all();
    }

    private function alertOpenParams(array $n): ?array
    {
        $screen = $n['screen'] ?? null;
        if ($screen === 'results' || ($n['type'] ?? '') === 'exam') {
            return ['screen' => 'results', 'type' => 'exam'];
        }
        if ($screen === 'reports' || ($n['type'] ?? '') === 'report') {
            return ['screen' => 'results', 'type' => 'report'];
        }
        if (in_array($screen, ['home_packages', 'notes', 'past_papers'], true)) {
            return ['screen' => $screen];
        }

        return $screen ? ['screen' => $screen] : null;
    }
}
