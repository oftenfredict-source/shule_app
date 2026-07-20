<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Student;
use App\Models\SchoolSubject;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Services\OnlineApplicationApplyTokenService;
use App\Services\OnlineApplicationCapacityService;
use App\Services\OnlineApplicationSchoolService;
use App\Services\OnlineAdmissionFormService;
use App\Services\OnlineAdmissionService;
use App\Services\OnlineAdmissionSpaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class OnlineApplicationController extends Controller
{
    public function __construct(
        private OnlineApplicationApplyTokenService $applyTokens,
        private OnlineApplicationCapacityService $capacity,
        private OnlineApplicationSchoolService $schoolService,
        private OnlineAdmissionFormService $formService,
        private OnlineAdmissionService $admissionService,
        private OnlineAdmissionSpaceService $spaceService
    ) {
    }

    /**
     * Display the online application page (first 10 schools only).
     */
    public function index(Request $request)
    {
        if (auth('online_application')->check()) {
            return redirect()->route('online_application.portal.apply', array_filter([
                'search' => $request->get('search') ?: null,
            ]));
        }

        try {
            $search = $request->get('search', '');
            $result = $this->schoolService->paginatedSchools($search ?: null, 1, 10);

            return view('online_application', [
                'schools' => $result['schools'],
                'search' => $search,
                'hasMore' => $result['hasMore'],
                'nextPage' => $result['nextPage'],
            ]);
        } catch (\Exception $e) {
            \Log::error('OnlineApplicationController index error: ' . $e->getMessage());

            return view('online_application', [
                'schools' => collect(),
                'search' => $request->get('search', ''),
                'hasMore' => false,
                'nextPage' => 2,
            ])->with('error', 'An error occurred while loading schools. Please try again.');
        }
    }

    /**
     * Load more schools (infinite scroll).
     */
    public function loadSchools(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $page = max(1, (int) $request->get('page', 1));
            $result = $this->schoolService->paginatedSchools($search ?: null, $page, 10);

            $html = view('partials.online_application_school_cards', [
                'schools' => $result['schools'],
                'requireAuth' => !auth('online_application')->check(),
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'hasMore' => $result['hasMore'],
                'nextPage' => $result['nextPage'],
            ]);
        } catch (\Exception $e) {
            \Log::error('OnlineApplicationController loadSchools error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to load more schools. Please try again.',
            ], 500);
        }
    }

    /**
     * Get detailed information about a school
     */
    public function getSchoolDetails($schoolID)
    {
        $school = $this->schoolService->liveSchoolsQuery()->where('schoolID', $schoolID)->first();

        if (!$school) {
            return response()->json(['error' => 'School not found'], 404);
        }

        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get()
            ->map(function ($class) {
                $subclasses = Subclass::where('classID', $class->classID)
                    ->where('status', 'Active')
                    ->get();

                $subclassesWithStudents = $subclasses->map(function ($subclass) {
                    $studentCount = Student::where('subclassID', $subclass->subclassID)
                        ->where('status', 'Active')
                        ->count();

                    return [
                        'subclassID' => $subclass->subclassID,
                        'subclassName' => $subclass->subclass_name ?: 'Main',
                        'studentCount' => $studentCount,
                    ];
                });

                return [
                    'classID' => $class->classID,
                    'className' => $class->class_name,
                    'subclassesCount' => $subclasses->count(),
                    'subclasses' => $subclassesWithStudents,
                ];
            });

        $schoolSubjects = SchoolSubject::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('subject_name')
            ->get(['subjectID', 'subject_name', 'subject_code']);

        $classSubjects = ClassSubject::whereHas('class', function ($query) use ($schoolID) {
            $query->where('schoolID', $schoolID)->where('status', 'Active');
        })
            ->where('status', 'Active')
            ->with(['subject', 'class', 'teacher'])
            ->get()
            ->groupBy('classID')
            ->map(function ($subjects) {
                return $subjects->map(function ($classSubject) {
                    return [
                        'subjectName' => $classSubject->subject ? $classSubject->subject->subject_name : 'N/A',
                        'subjectCode' => $classSubject->subject ? $classSubject->subject->subject_code : '',
                        'teacherName' => $classSubject->teacher
                            ? ($classSubject->teacher->first_name . ' ' . $classSubject->teacher->last_name)
                            : 'Not Assigned',
                    ];
                });
            });

        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get()
            ->map(function ($teacher) use ($schoolID) {
                $roles = DB::table('role_user')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('role_user.teacher_id', $teacher->id)
                    ->where('roles.schoolID', $schoolID)
                    ->pluck('roles.role_name')
                    ->toArray();

                $isCoordinator = ClassModel::where('teacherID', $teacher->id)
                    ->where('schoolID', $schoolID)
                    ->exists();

                if ($isCoordinator && !in_array('Coordinator', $roles)) {
                    $roles[] = 'Coordinator';
                }

                $isClassTeacher = Subclass::where('teacherID', $teacher->id)
                    ->whereHas('class', function ($query) use ($schoolID) {
                        $query->where('schoolID', $schoolID);
                    })
                    ->exists();

                if ($isClassTeacher && !in_array('Class Teacher', $roles)) {
                    $roles[] = 'Class Teacher';
                }

                return [
                    'teacherID' => $teacher->id,
                    'firstName' => $teacher->first_name,
                    'middleName' => $teacher->middle_name,
                    'lastName' => $teacher->last_name,
                    'fullName' => trim($teacher->first_name . ' ' . ($teacher->middle_name ?? '') . ' ' . $teacher->last_name),
                    'position' => $teacher->position,
                    'roles' => $roles,
                ];
            });

        return response()->json([
            'success' => true,
            'school' => [
                'schoolID' => $school->schoolID,
                'schoolName' => $school->school_name,
                'schoolType' => $school->school_type,
                'ownership' => $school->ownership,
                'region' => $school->region,
                'district' => $school->district,
                'ward' => $school->ward,
                'village' => $school->village,
                'address' => $school->address,
                'email' => $school->email,
                'phone' => $school->phone,
                'establishedYear' => $school->established_year,
                'schoolLogo' => $school->school_logo,
            ],
            'classes' => $classes,
            'schoolSubjects' => $schoolSubjects,
            'classSubjects' => $classSubjects,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Public school landing (encrypted token) — school info, classes, fees, apply CTAs.
     */
    public function showSchoolLanding(string $token)
    {
        try {
            $schoolID = $this->applyTokens->decodeSchool($token);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('online_application')
                ->with('error', $e->getMessage());
        }

        $school = $this->schoolService->liveSchoolsQuery()->where('schoolID', $schoolID)->first();

        if (!$school) {
            return redirect()->route('online_application')
                ->with('error', 'Selected school is not available for online application.');
        }

        $card = $this->schoolService->buildSchoolCard($school);
        $feesByClass = $this->schoolService->feesByClassForSchool($schoolID);
        $schoolUrl = $this->applyTokens->schoolUrl($schoolID);
        $isLoggedIn = auth('online_application')->check();

        return view('online_application.school', compact(
            'school',
            'card',
            'feesByClass',
            'schoolUrl',
            'token',
            'isLoggedIn'
        ));
    }

    /**
     * Public fees structure PDF for a school landing link (same layout as manage_fees).
     */
    public function exportFeesStructurePdf(Request $request)
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            return response()->json(['error' => 'Invalid school link.'], 422);
        }

        try {
            $schoolID = $this->applyTokens->decodeSchool($token);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $school = $this->schoolService->liveSchoolsQuery()->where('schoolID', $schoolID)->first();
        if (!$school) {
            return response()->json(['error' => 'School not available.'], 404);
        }

        $rawClassIDs = $request->query('classIDs', $request->input('classIDs', []));
        if (!is_array($rawClassIDs)) {
            $rawClassIDs = $rawClassIDs !== null && $rawClassIDs !== ''
                ? preg_split('/[,\s]+/', (string) $rawClassIDs)
                : [];
        }
        $legacyClassID = $request->query('classID');
        if (($legacyClassID !== null && $legacyClassID !== '') && empty($rawClassIDs)) {
            $rawClassIDs = [$legacyClassID];
        }

        $classIDs = array_values(array_unique(array_filter(array_map(function ($id) {
            return is_numeric($id) ? (int) $id : null;
        }, $rawClassIDs))));

        $activeYear = $this->spaceService->currentAcademicYearForSchool($schoolID);
        $yearLabel = $activeYear->year_name ?? $activeYear->year ?? date('Y');
        $year = $activeYear->year ?? date('Y');

        $query = ClassModel::where('schoolID', $schoolID)
            ->with(['fees' => function ($q) {
                $q->where('status', 'Active')
                    ->with(['bankAccount.bank', 'breakdowns', 'installments', 'otherFeeDetails', 'deadlines'])
                    ->orderBy('display_order');
            }])
            ->orderBy('class_name');

        if (!empty($classIDs)) {
            $query->whereIn('classID', $classIDs);
        }

        $classes = $query->get();

        // Only include classes that have at least one active fee (public landing).
        if (empty($classIDs)) {
            $classes = $classes->filter(fn ($c) => $c->fees && $c->fees->count() > 0)->values();
        }

        if ($classes->isEmpty()) {
            return response()->json(['error' => 'No fee structure found for the selected class.'], 404);
        }

        $totalFeeClasses = ClassModel::where('schoolID', $schoolID)
            ->whereHas('fees', fn ($q) => $q->where('status', 'Active'))
            ->count();

        if (empty($classIDs) || $classes->count() === 0 || $classes->count() >= $totalFeeClasses) {
            $scopeLabel = 'ALL CLASSES';
        } elseif ($classes->count() === 1) {
            $scopeLabel = strtoupper((string) ($classes->first()->class_name ?? 'Selected Class'));
        } else {
            $names = $classes->pluck('class_name')->filter()->map(function ($n) {
                return strtoupper((string) $n);
            })->values()->all();
            $scopeLabel = count($names) <= 4
                ? implode(' + ', $names)
                : (count($names) . ' CLASSES');
        }

        $reportTitle = 'OFFICIAL FEES STRUCTURE — ' . $scopeLabel . ' | ' . $yearLabel;

        $data = [
            'school' => $school,
            'classes' => $classes,
            'year' => $year,
            'yearLabel' => $yearLabel,
            'scopeLabel' => $scopeLabel,
            'reportTitle' => $reportTitle,
            'title' => $reportTitle,
        ];

        $fileScope = preg_replace('/\s+/', '_', (string) $scopeLabel);
        $fileScope = preg_replace('/[^\w\-]+/', '', $fileScope) ?: 'Fees_Structure';
        $filename = 'Fees_Structure_' . $fileScope . '_' . date('Ymd') . '.pdf';

        try {
            try {
                $pdf = PDF::loadView('Admin.pdf.fees_structure_pdf', $data);
                $pdf->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => base_path(),
                ]);
                $pdf->setPaper('A4', 'portrait');
                return $pdf->download($filename);
            } catch (\Throwable $e) {
                $dompdf = new \Dompdf\Dompdf();
                $html = view('Admin.pdf.fees_structure_pdf', $data)->render();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return response()->streamDownload(function () use ($dompdf) {
                    echo $dompdf->output();
                }, $filename, ['Content-Type' => 'application/pdf']);
            }
        } catch (\Throwable $e) {
            \Log::error('Online application fees PDF error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF.'], 500);
        }
    }

    /**
     * Show the application form (encrypted token only — auth required).
     */
    public function showApplicationForm(Request $request)
    {
        if (!auth('online_application')->check()) {
            return redirect()
                ->route('online_application.auth.login', [
                    'redirect' => $request->getRequestUri(),
                ])
                ->with('info', 'In order to apply, please login first. After login you will continue to the application form.');
        }

        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('online_application')
                ->with('error', 'Invalid application link. Please select a school and class again.');
        }

        try {
            $ids = $this->applyTokens->decode($token);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('online_application')
                ->with('error', $e->getMessage());
        }

        $schoolID = $ids['schoolID'];
        $classID = $ids['classID'];

        $school = $this->schoolService->liveSchoolsQuery()->where('schoolID', $schoolID)->first();

        if (!$school) {
            return redirect()->route('online_application')
                ->with('error', 'Selected school is not available for online application.');
        }

        $class = ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$class) {
            return redirect()->route('online_application')
                ->with('error', 'Selected class not found or is inactive.');
        }

        $stats = $this->capacity->calculateForClass($class);
        $availableSpaces = $stats['availableSpaces'];
        $academicYearID = $ids['academicYearID'] ?? $stats['academicYearID'];
        $academicYearName = $stats['academicYearName'] ?? null;

        if (!$academicYearID || $availableSpaces <= 0) {
            return redirect()->route('online_application')
                ->with('error', 'No available admission spaces in the selected class for the current academic year.');
        }

        $this->formService->ensureDefaults($schoolID, $classID);
        $formSections = $this->formService->activeSectionsWithFields($schoolID, $classID);

        return view('apply', compact(
            'school',
            'class',
            'availableSpaces',
            'token',
            'formSections',
            'academicYearID',
            'academicYearName'
        ));
    }

    /**
     * Store the application (auth required).
     */
    public function storeApplication(Request $request)
    {
        $wantsJson = $request->expectsJson() || $request->ajax();

        if (!auth('online_application')->check()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to submit your application.',
                    'redirect' => route('online_application.auth.login'),
                ], 401);
            }

            return redirect()
                ->route('online_application.auth.login')
                ->with('info', 'Please login to submit your application.');
        }

        try {
            $ids = $this->applyTokens->decode($request->input('apply_token', ''));
        } catch (\InvalidArgumentException $e) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->route('online_application')->with('error', $e->getMessage());
        }

        $schoolID = $ids['schoolID'];
        $classID = $ids['classID'];

        $school = $this->schoolService->liveSchoolsQuery()->where('schoolID', $schoolID)->first();
        if (!$school) {
            $message = 'Selected school is not available for online application.';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('online_application')->with('error', $message);
        }

        $class = ClassModel::where('classID', $classID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$class) {
            $message = 'Selected class not found or is inactive.';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message)->withInput();
        }

        $stats = $this->capacity->calculateForClass($class);
        $academicYearID = $ids['academicYearID'] ?? $stats['academicYearID'];

        if (!$academicYearID || $stats['availableSpaces'] <= 0) {
            $message = 'No available admission spaces in the selected class.';
            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message)->withInput();
        }

        $validation = $this->admissionService->buildValidationRules($schoolID, $classID);
        $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

        if ($validator->fails()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        try {
            $admission = $this->admissionService->submit(
                $request,
                $schoolID,
                $classID,
                (int) $academicYearID,
                (int) (auth('online_application')->user()?->getKey() ?? 0) ?: null
            );

            if (!$admission->online_application_userID) {
                \Log::warning('Online application saved without user ID for application ' . $admission->application_number);
            }

            $payload = [
                'applicationNumber' => $admission->application_number,
                'studentName' => trim($admission->student_first_name . ' ' . ($admission->student_middle_name ? $admission->student_middle_name . ' ' : '') . $admission->student_last_name),
                'schoolName' => $school->school_name,
                'className' => $class->class_name,
                'isPendingReview' => true,
            ];

            $request->session()->put('oa_application_success', $payload);

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Application submitted successfully!',
                    'redirect' => route('online_application.application_success'),
                ]);
            }

            return redirect()->route('online_application.application_success');
        } catch (\Throwable $e) {
            \Log::error('OnlineApplicationController store error: ' . $e->getMessage());
            $message = $e->getMessage() ?: 'An error occurred while submitting your application. Please try again.';

            if ($wantsJson) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message)->withInput();
        }
    }

    /**
     * Success page after AJAX/normal submit (reads flash payload).
     */
    public function applicationSuccess(Request $request)
    {
        $payload = $request->session()->pull('oa_application_success');
        if (!$payload || !is_array($payload)) {
            return redirect()->route('online_application.portal.applications');
        }

        return view('application_success', $payload);
    }
}
