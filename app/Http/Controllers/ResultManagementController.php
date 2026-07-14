<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Examination;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\Role;
use App\Models\Student;
use App\Models\Subclass;
use App\Models\SchoolSubject;
use App\Models\ClassSubject;
use App\Models\GradeDefinition;
use App\Models\ExamPaper;
use App\Models\ExamPaperQuestion;
use App\Models\ExamPaperQuestionMark;
use App\Models\ExamPaperOptionalRange;
use App\Models\Teacher;
use App\Models\TermReportDefinition;
use App\Models\CaDefinition;
use App\Models\AcademicYear;
use App\Models\CharacterAssessmentTimetable;
use App\Models\StudentCharacterAssessment;
use App\Models\PublishedReport;
use App\Models\ParentResultNotification;
use App\Models\ParentModel;
use App\Models\TeacherResultAssignment;
use App\Models\School;
use App\Models\SubjectElector;
use App\Models\StudentEnrollment;
use App\Services\SmsService;
use App\Services\SelectedAcademicYearService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Support\CenturyGothicPdfFont;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultManagementController extends Controller
{
    /**
     * Helper to check permissions with hierarchy support
     */
    private function hasPermission($permission)
    {
        $user_type = Session::get("user_type");
        if ($user_type === "Admin") {
            return true;
        }

        $teacherPermissions = Session::get("teacherPermissions") ?? collect();

        // Define permission aliases for hierarchy
        $aliases = [
            "view_results" => [
                "view_results",
                "result_read_only",
                "result_create",
                "result_update",
                "result_delete",
                "manage_results",
                "manageResults",
            ],
            "view_class_subjects" => [
                "view_class_subjects",
                "subject_read_only",
                "subject_create",
                "subject_update",
                "subject_delete",
                "manage_subjects",
            ],
            "view_exams" => [
                "view_exams",
                "examination_read_only",
                "examination_create",
                "examination_update",
                "examination_delete",
                "manage_exam",
            ],
            "view_classes" => [
                "view_classes",
                "classes_read_only",
                "classes_create",
                "classes_update",
                "classes_delete",
                "manage_classes",
                "create_class",
                "edit_class",
            ],
        ];

        // If the teacher has broad modify permissions, they also have view permissions
        if (isset($aliases[$permission])) {
            if (
                $teacherPermissions
                    ->intersect($aliases[$permission])
                    ->isNotEmpty()
            ) {
                return true;
            }
        }

        return $teacherPermissions->contains($permission);
    }

    /**
     * Parent phone from parents.phone (manage_parents), else student emergency contact.
     */
    private function resolveParentPhoneForStudent(Student $student): ?string
    {
        $phone = null;
        if ($student->parent && !empty(trim((string) $student->parent->phone))) {
            $phone = $student->parent->phone;
        } elseif ($student->parentID) {
            $parent = $student->relationLoaded("parent")
                ? $student->parent
                : ParentModel::find($student->parentID);
            if ($parent && !empty(trim((string) $parent->phone))) {
                $phone = $parent->phone;
            }
        }
        if (!$phone && !empty($student->emergency_contact_phone)) {
            $phone = $student->emergency_contact_phone;
        }
        $phone = trim((string) $phone);
        if (
            $phone === "" ||
            in_array(strtolower($phone), ["null", "undefined", "n/a"], true)
        ) {
            return null;
        }

        return $phone;
    }

    /**
     * Display result management page
     */
    public function index(Request $request)
    {
        $userType = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        $teacherID = Session::get("teacherID");

        // Allow both Admin and Teacher access
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Access denied");
        }

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : "Secondary";

        // Check for encoded payload and merge to request to prevent simple URL bypassing
        $payload = $request->get("payload");
        if ($payload) {
            try {
                // Decode from base64 (acts as URL obfuscation for security)
                $decodedStr = base64_decode($payload);
                if ($decodedStr !== false) {
                    parse_str($decodedStr, $decodedArray);
                    if (is_array($decodedArray)) {
                        $request->merge($decodedArray);
                    }
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        // Get filter parameters
        $termFilter = $request->input("term", "");
        $typeFilter = $request->input("type", "exam"); // 'exam' or 'report'
        $statusFilter = $this->normalizeResultsEnrollmentStatusFilter(
            $request->input("status", "Active"),
        ); // enrollment statuses
        $classFilter = $request->input("class", "");
        $subclassFilter = $request->input("subclass", "");
        $subclassIDParam = $request->input("subclassID", ""); // From classManagement link
        $classIDParam = $request->input("classID", ""); // From coordinator view
        $isCoordinatorView = $request->input("coordinator") === "true";
        $examFilter = $request->input("examID", ""); // Specific exam filter
        $weekFilter = $request->input("week", ""); // Week filter for weekly/monthly tests
        $subjectFilter = $request->input("subjectID", ""); // Specific subject filter

        // Academic year always from header selection (like manageExamination) — no page year picker
        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $yearCtx["academic_year_ids"];
        $yearFilter = $yearCtx["calendar_year"];
        $academicYearLabel = $yearCtx["label"];

        // BROAD PERMISSION CHECK: If teacher has broad result view permissions, don't restrict to "assigned class"
        $hasBroadResultPermission = $this->hasPermission("view_results");

        // Allow any user with 'result' menu permission to view all classes like Admin
        if (!$hasBroadResultPermission && $userType !== "Admin") {
            $hasResultPermission = false;

            if ($userType === "Teacher") {
                $teacherID = Session::get("teacherID");
                if ($teacherID) {
                    $roles = DB::table("role_user")
                        ->where("teacher_id", $teacherID)
                        ->pluck("role_id");
                    $perms = DB::table("permissions")
                        ->whereIn("role_id", $roles)
                        ->get();
                    if (
                        $perms
                            ->where("permission_category", "result")
                            ->count() > 0
                    ) {
                        $hasResultPermission = true;
                    } elseif (
                        $perms->contains(function ($p) {
                            return strpos(strtolower($p->name), "result") !==
                                false;
                        })
                    ) {
                        $hasResultPermission = true;
                    }
                }
            } elseif ($userType === "Staff") {
                $staffID = Session::get("staffID");
                if ($staffID) {
                    $profId = DB::table("other_staff")
                        ->where("id", $staffID)
                        ->value("profession_id");
                    if ($profId) {
                        $perms = DB::table("staff_permissions")
                            ->where("profession_id", $profId)
                            ->get();
                        if (
                            $perms
                                ->where("permission_category", "result")
                                ->count() > 0
                        ) {
                            $hasResultPermission = true;
                        } elseif (
                            $perms->contains(function ($p) {
                                return strpos(
                                    strtolower($p->name),
                                    "result",
                                ) !== false;
                            })
                        ) {
                            $hasResultPermission = true;
                        }
                    }
                }
            }

            if ($hasResultPermission) {
                $hasBroadResultPermission = true;
            }
        }

        // Check if coordinator view is requested
        $isCoordinatorResultsView = false;
        if ($isCoordinatorView && !empty($classIDParam) && !empty($teacherID)) {
            // Verify teacher is coordinator of this main class
            $mainClass = \App\Models\ClassModel::find($classIDParam);
            if (
                $mainClass &&
                ($mainClass->teacherID == $teacherID ||
                    $hasBroadResultPermission) &&
                $mainClass->schoolID == $schoolID
            ) {
                // Set default class filter for coordinator view
                $classFilter = $classIDParam;
                $isCoordinatorResultsView = true;
                // Coordinator can only choose subclass, main class is pre-selected and locked
            } else {
                // Coordinator doesn't have access to this class
                if ($userType === "Teacher" && !$hasBroadResultPermission) {
                    return redirect()
                        ->route("AdmitedClasses", ["coordinator" => "true"])
                        ->with("error", "Unauthorized access to this class")
                        ->with("error_type", "unauthorized_access");
                }
            }
        }

        // If subclassID is provided (from classManagement), verify teacher access and set defaults
        $isTeacherView = false;
        if (
            !empty($subclassIDParam) &&
            !empty($teacherID) &&
            !$isCoordinatorResultsView
        ) {
            // Verify teacher has access to this subclass
            $subclass = Subclass::with("class")->find($subclassIDParam);

            // Allow if teacher is assigned OR has broad permissions
            if (
                $subclass &&
                ($subclass->teacherID == $teacherID ||
                    $hasBroadResultPermission)
            ) {
                // Set default filters for teacher view
                $subclassFilter = $subclassIDParam;
                $classFilter = $subclass->classID;
                if ($subclass->teacherID == $teacherID) {
                    $isTeacherView = true;
                }
            } else {
                // Teacher doesn't have access to this subclass
                if ($userType === "Teacher" && !$hasBroadResultPermission) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with("error", "Unauthorized access to this class")
                        ->with("error_type", "unauthorized_access");
                }
            }
        }

        // SECURITY: For regular teachers without broad permissions, always verify they have access to the selected subclass
        if (
            $userType === "Teacher" &&
            !empty($teacherID) &&
            !$hasBroadResultPermission
        ) {
            // If subclassID param is provided, use it
            if (!empty($subclassIDParam)) {
                $subclass = Subclass::with("class")->find($subclassIDParam);
                if ($subclass && $subclass->teacherID == $teacherID) {
                    $subclassFilter = $subclassIDParam;
                    $classFilter = $subclass->classID;
                    $isTeacherView = true;
                }
            }

            // Priority 2: If subclassFilter is set (from form submission), verify access
            if (!empty($subclassFilter) && !$isTeacherView) {
                $subclass = Subclass::with("class")->find($subclassFilter);
                if (!$subclass) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with("error", "Invalid class ID")
                        ->with("error_type", "invalid_class");
                }

                // Verify teacher is the class teacher of this subclass
                if ($subclass->teacherID != $teacherID) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with(
                            "error",
                            "You do not have access to view results for this class",
                        )
                        ->with("error_type", "unauthorized_access");
                }

                // Force teacher view mode and lock filters
                $isTeacherView = true;
                $classFilter = $subclass->classID; // Ensure class filter matches subclass
            }

            // Priority 3: If no subclass filter is set, default to their assigned subclass to prevent dropdown from rendering
            if (empty($subclassFilter) && !$isTeacherView) {
                $assignedSubclass = \App\Models\Subclass::where(
                    "teacherID",
                    $teacherID,
                )->first();
                if ($assignedSubclass) {
                    $subclassFilter = $assignedSubclass->subclassID;
                    $classFilter = $assignedSubclass->classID;
                    $isTeacherView = true;
                } else {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with(
                            "error",
                            "You must be assigned to a class to view this page",
                        )
                        ->with("error_type", "no_assigned_class");
                }
            }

            // If isTeacherView is true, subclassFilter MUST be set
            if ($isTeacherView && empty($subclassFilter)) {
                return redirect()
                    ->route("AdmitedClasses")
                    ->with("error", "Please select a class to view results")
                    ->with("error_type", "no_subclass_selected");
            }
        }

        // Available academic years (for display); calendar years kept for legacy PDF helpers
        $availableYears = !empty($yearCtx["calendar_years"])
            ? $yearCtx["calendar_years"]
            : Examination::where("schoolID", $schoolID)
                ->distinct()
                ->orderBy("year", "desc")
                ->pluck("year")
                ->toArray();

        // Get classes for filter
        $classes = ClassModel::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->orderBy("class_name")
            ->get();

        // Get subclasses for filter (only for selected main class) with display names
        $subclasses = collect();
        $allSubclasses = collect();

        // If coordinator view, get all subclasses for the main class
        if ($isCoordinatorResultsView && $classFilter) {
            $classSubclasses = Subclass::with("class")
                ->where("classID", $classFilter)
                ->where("status", "Active")
                ->orderBy("subclass_name")
                ->get();

            // Group by classID for consistency with other views
            $allSubclasses = $classSubclasses->groupBy("classID");
        } elseif (
            $userType === "Teacher" &&
            !empty($teacherID) &&
            !$isCoordinatorResultsView &&
            !$hasBroadResultPermission
        ) {
            // SECURITY: For regular teachers (class teacher view), only show their assigned subclasses
            if ($classFilter) {
                // Get only teacher's subclasses in the selected class
                $allSubclasses = Subclass::with("class")
                    ->whereHas("class", function ($query) use (
                        $schoolID,
                        $classFilter,
                    ) {
                        $query
                            ->where("schoolID", $schoolID)
                            ->where("classID", $classFilter);
                    })
                    ->where("teacherID", $teacherID)
                    ->where("status", "Active")
                    ->get()
                    ->groupBy("classID");
            } else {
                // Get all teacher's assigned subclasses
                $allSubclasses = Subclass::with("class")
                    ->whereHas("class", function ($query) use ($schoolID) {
                        $query->where("schoolID", $schoolID);
                    })
                    ->where("teacherID", $teacherID)
                    ->where("status", "Active")
                    ->get()
                    ->groupBy("classID");
            }
        } elseif ($classFilter) {
            // Admin: Get subclasses for selected class
            $allSubclasses = Subclass::with("class")
                ->whereHas("class", function ($query) use (
                    $schoolID,
                    $classFilter,
                ) {
                    $query
                        ->where("schoolID", $schoolID)
                        ->where("classID", $classFilter);
                })
                ->where("status", "Active")
                ->get()
                ->groupBy("classID");
        } else {
            // Admin: If no class selected, get all subclasses
            $allSubclasses = Subclass::with("class")
                ->whereHas("class", function ($query) use ($schoolID) {
                    $query->where("schoolID", $schoolID);
                })
                ->where("status", "Active")
                ->get()
                ->groupBy("classID");
        }

        // Format subclasses with display names
        foreach ($allSubclasses as $classID => $classSubclasses) {
            // If class has only one subclass and it's default (empty name), include it but show only class name
            if ($classSubclasses->count() === 1) {
                $subclass = $classSubclasses->first();
                if (trim($subclass->subclass_name) === "") {
                    // Default subclass - show only class name
                    $subclasses->push(
                        (object) [
                            "subclassID" => $subclass->subclassID,
                            "subclass_name" => $subclass->subclass_name,
                            "display_name" => $subclass->class->class_name,
                            "class_name" => $subclass->class->class_name,
                        ],
                    );
                } else {
                    // Single subclass with name
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = $isCoordinatorResultsView
                        ? (empty($subclassName)
                            ? $subclass->class->class_name
                            : $subclass->class->class_name .
                                " - " .
                                $subclassName)
                        : $subclass->class->class_name .
                            " " .
                            $subclass->subclass_name;
                    $subclasses->push(
                        (object) [
                            "subclassID" => $subclass->subclassID,
                            "subclass_name" => $subclass->subclass_name,
                            "display_name" => $displayName,
                            "class_name" => $subclass->class->class_name,
                        ],
                    );
                }
            } else {
                // Multiple subclasses - show all with class_name + subclass_name
                foreach ($classSubclasses as $subclass) {
                    $subclassName = trim($subclass->subclass_name);
                    $displayName = empty($subclassName)
                        ? $subclass->class->class_name
                        : ($isCoordinatorResultsView
                            ? $subclass->class->class_name .
                                " - " .
                                $subclassName
                            : $subclass->class->class_name .
                                " " .
                                $subclassName);

                    $subclasses->push(
                        (object) [
                            "subclassID" => $subclass->subclassID,
                            "subclass_name" => $subclass->subclass_name,
                            "display_name" => $displayName,
                            "class_name" => $subclass->class->class_name,
                        ],
                    );
                }
            }
        }

        // Sort by class_name then subclass_name
        $subclasses = $subclasses
            ->sortBy(function ($item) {
                return $item->class_name . " " . $item->subclass_name;
            })
            ->values();

        // Check if this is a request for subject details (AJAX)
        $getSubjectDetails = $request->input("getSubjectDetails", false);
        $getIncompleteResults = $request->input("getIncompleteResults", false);
        $studentIDFilter = $request->input("studentID", "");
        $typeFilterForDetails = $request->input("type", "exam");

        // NEW: Fetch Incomplete Results logic (STABLE ULTRA-FAST BATCH SCAN)
        if ($getIncompleteResults && $examFilter) {
            $exam = Examination::find($examFilter);
            if (!$exam) {
                return response()->json(["error" => "Exam not found"], 404);
            }

            // 1. Get all active students grouped by subclass (minimal columns)
            $studentsBySubclass = DB::table("students")
                ->where("schoolID", $schoolID)
                ->where("status", "Active")
                ->select(
                    "studentID",
                    "subclassID",
                    "first_name",
                    "last_name",
                    "admission_number",
                )
                ->get()
                ->groupBy("subclassID");

            // 2. Get ONLY IDs of existing marks for this exam
            $completedMarksBySubject = DB::table("results")
                ->where("examID", $examFilter)
                ->whereNotNull("marks")
                ->select("studentID", "class_subjectID")
                ->get()
                ->groupBy("class_subjectID")
                ->map(function ($rows) {
                    return $rows->pluck("studentID")->toArray();
                });

            // 3. Get active class subjects (Scoped correctly without non-existent schoolID column)
            $classIDFilter = $request->input("classID", "");
            $subclassIDFilter = $request->input("subclassID", "");

            $classSubjects = ClassSubject::where("status", "Active")
                ->whereHas("subclass.class", function ($q) use ($schoolID) {
                    $q->where("schoolID", $schoolID);
                })
                ->when($classIDFilter, function ($query) use ($classIDFilter) {
                    return $query->whereHas("subclass", function ($q) use (
                        $classIDFilter,
                    ) {
                        $q->where("classID", $classIDFilter);
                    });
                })
                ->when($subclassIDFilter, function ($query) use (
                    $subclassIDFilter,
                ) {
                    return $query->where("subclassID", $subclassIDFilter);
                })
                ->with(["subject", "teacher", "subclass.class"])
                ->get();

            $incompleteData = [];

            // Get all elected student mappings for Optional Subjects
            $optionalCidIds = $classSubjects
                ->filter(function ($cs) {
                    return strtolower($cs->student_status ?? "") === "optional";
                })
                ->pluck("class_subjectID")
                ->toArray();

            $electorsBySubject = collect();
            if (!empty($optionalCidIds)) {
                $electorsBySubject = DB::table("subject_electors")
                    ->whereIn("classSubjectID", $optionalCidIds)
                    ->get()
                    ->groupBy("classSubjectID")
                    ->map(function ($rows) {
                        return $rows->pluck("studentID")->toArray();
                    });
            }

            foreach ($classSubjects as $cs) {
                if (!$cs->subclassID) {
                    continue;
                }

                $expectedStudents = clone $studentsBySubclass->get(
                    $cs->subclassID,
                ) ?:
                collect();
                if ($expectedStudents->isEmpty()) {
                    continue;
                }

                // If subject is optional, only process elected students
                if (strtolower($cs->student_status ?? "") === "optional") {
                    $electedIds = $electorsBySubject->get(
                        $cs->class_subjectID,
                        [],
                    );
                    $expectedStudents = $expectedStudents->filter(function (
                        $st,
                    ) use ($electedIds) {
                        return in_array($st->studentID, $electedIds);
                    });
                }

                if ($expectedStudents->isEmpty()) {
                    continue;
                }

                $completedIds =
                    $completedMarksBySubject->get($cs->class_subjectID) ?: [];

                // Identify missing students (not in results OR have no marks)
                $missing = $expectedStudents->filter(function ($st) use (
                    $completedIds,
                ) {
                    return !in_array($st->studentID, $completedIds);
                });

                if ($missing->isNotEmpty()) {
                    $teacherId = $cs->teacherID ?: 0;
                    if (!isset($incompleteData[$teacherId])) {
                        $incompleteData[$teacherId] = [
                            "teacher_name" => $cs->teacher
                                ? $cs->teacher->first_name .
                                    " " .
                                    $cs->teacher->last_name
                                : "Unassigned",
                            "teacher_photo" => $cs->teacher
                                ? $cs->teacher->image
                                : null,
                            "teacher_phone" => $cs->teacher
                                ? $cs->teacher->phone_number
                                : null,
                            "teacher_gender" => $cs->teacher
                                ? $cs->teacher->gender
                                : "Male",
                            "subjects" => [],
                        ];
                    }

                    $incompleteData[$teacherId]["subjects"][] = [
                        "subject_name" => $cs->subject
                            ? $cs->subject->subject_name
                            : "Unknown",
                        "class_name" =>
                            $cs->subclass && $cs->subclass->class
                                ? $cs->subclass->class->class_name .
                                    " " .
                                    $cs->subclass->subclass_name
                                : "Unknown",
                        "missing_count" => $missing->count(),
                        "students" => $missing->values()->take(150)->toArray(),
                    ];
                }
            }

            return response()->json([
                "exam_name" => $exam->exam_name,
                "incomplete_count" => count($incompleteData),
                "data" => array_values($incompleteData),
            ]);
        }

        // Support both exam and term report subject details
        if (
            $getSubjectDetails &&
            $studentIDFilter &&
            (($typeFilterForDetails === "exam" && $examFilter) ||
                ($typeFilterForDetails === "report" &&
                    $termFilter &&
                    $yearFilter))
        ) {
            // Return JSON response with subject details
            $student = Student::where("studentID", $studentIDFilter)
                ->where("schoolID", $schoolID)
                ->with([
                    "subclass.class",
                    "subclass.teacher",
                    "oldSubclass.class",
                    "oldSubclass.teacher",
                ])
                ->first();

            if (!$student) {
                return response()->json(
                    [
                        "error" => "Student not found",
                    ],
                    404,
                );
            }

            // Removed view permissions validation as requested

            // Get results for this student
            if ($typeFilterForDetails === "report") {
                // For term report: get only exams defined in TermReportDefinition if available
                $reportDefinition = TermReportDefinition::where(
                    "schoolID",
                    $schoolID,
                )
                    ->where("year", $yearFilter)
                    ->where("term", $termFilter)
                    ->first();

                $examinationsQuery = Examination::where("schoolID", $schoolID)
                    ->where("year", $yearFilter)
                    ->where("term", $termFilter)
                    ->orderBy("start_date");

                if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
                    $examinationsQuery->whereIn(
                        "examID",
                        $reportDefinition->exam_ids,
                    );
                }

                $examinations = $examinationsQuery->get();
                $examIDs = $examinations->pluck("examID")->toArray();

                $results = Result::where("studentID", $studentIDFilter)
                    ->whereIn("examID", $examIDs)
                    ->with(["classSubject.subject", "examination"])
                    ->get();
            } else {
                // For exam: get results for specific exam
                $resultsQuery = Result::where(
                    "studentID",
                    $studentIDFilter,
                )->where("examID", $examFilter);

                if ($weekFilter && $weekFilter !== "all") {
                    $resultsQuery->where("test_week", $weekFilter);
                }

                $results = $resultsQuery->with(["classSubject.subject"])->get();
            }

            $electedClassSubjectIds = $this->getElectedClassSubjectIdsForStudent(
                $studentIDFilter,
            );
            $results = $this->filterResultsForStudentElection(
                $results,
                $studentIDFilter,
                $electedClassSubjectIds,
            );

            // Get class name and classID
            $className = "";
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? "";
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className = $student->oldSubclass->class->class_name ?? "";
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            // Process results differently for exam vs term report
            $examsData = []; // Initialize outside if block for use in response
            $caDefinition = null;
            if ($typeFilterForDetails === "exam" && $examFilter) {
                $caDefinition = CaDefinition::where("examID", $examFilter)
                    ->where("schoolID", $schoolID)
                    ->first();
            }

            if ($typeFilterForDetails === "report") {
                // For term report: group by subject and collect exam results
                $subjectData = [];

                // Check for report definition to filter exams
                $reportDefinition = TermReportDefinition::where(
                    "schoolID",
                    $schoolID,
                )
                    ->where("year", $yearFilter)
                    ->where("term", $termFilter)
                    ->first();

                // Get only exams for the term that are in the definition (if exists)
                $examinationsQuery = Examination::where("schoolID", $schoolID)
                    ->where("year", $yearFilter)
                    ->where("term", $termFilter);

                if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
                    $examinationsQuery->whereIn(
                        "examID",
                        $reportDefinition->exam_ids,
                    );
                }

                // For regular teachers without broad permission, only show approved exams
                if ($userType !== "Admin" && !$hasBroadResultPermission) {
                    $examinationsQuery->where("approval_status", "Approved");
                }

                $examinations = $examinationsQuery
                    ->orderBy("start_date")
                    ->get();

                foreach ($examinations as $exam) {
                    $examsData[$exam->examID] = [
                        "examID" => $exam->examID,
                        "exam_name" => $exam->exam_name,
                        "start_date" => $exam->start_date,
                    ];
                }

                // Prevent double-counting: exclude CA test exams from average divisor
                // when their parent exam is also in the term report definition
                $selectedExamIDs = $examinations->pluck("examID")->toArray();
                $caDefinitionsForReport = CaDefinition::where(
                    "schoolID",
                    $schoolID,
                )
                    ->whereIn("examID", $selectedExamIDs)
                    ->get();
                $caDefinitionsByExam = $caDefinitionsForReport->keyBy(
                    "examID",
                );

                $excludedExamIDs = [];
                foreach ($caDefinitionsForReport as $caDefinition) {
                    $testIDs = is_array($caDefinition->test_ids)
                        ? $caDefinition->test_ids
                        : [];
                    foreach ($testIDs as $testID) {
                        if (in_array($testID, $selectedExamIDs)) {
                            $excludedExamIDs[] = $testID;
                        }
                    }
                }

                $effectiveExaminations = $examinations
                    ->reject(function ($exam) use ($excludedExamIDs) {
                        return in_array($exam->examID, $excludedExamIDs);
                    })
                    ->values();

                if ($effectiveExaminations->isEmpty()) {
                    $effectiveExaminations = $examinations;
                }

                // Group raw results by subject and exam (all exams shown as columns)
                foreach ($results as $result) {
                    $subjectName =
                        $result->classSubject->subject->subject_name ?? "N/A";
                    $examID = $result->examID;

                    if (!isset($subjectData[$subjectName])) {
                        $subjectData[$subjectName] = [
                            "subject_name" => $subjectName,
                            "exams" => [],
                        ];
                    }

                    if ($result->marks !== null && $result->marks !== "") {
                        $marks = (float) $result->marks;
                        $gradePoints = $this->calculateGradePoints(
                            $marks,
                            $schoolType,
                            $className,
                            $classID,
                        );
                        $grade = $result->grade ?? $gradePoints["grade"];

                        $examName = "N/A";
                        if (
                            isset($examsData[$examID]) &&
                            isset($examsData[$examID]["exam_name"])
                        ) {
                            $examName = $examsData[$examID]["exam_name"];
                        } elseif (
                            $result->examination &&
                            $result->examination->exam_name
                        ) {
                            $examName = $result->examination->exam_name;
                        }

                        $subjectData[$subjectName]["exams"][$examID] = [
                            "marks" => $marks,
                            "grade" => $grade,
                            "exam_name" => $examName,
                        ];
                    }
                }

                // Calculate CA-aware average and overall grade for each subject
                $subjects = [];
                $totalMarks = 0;
                $subjectCount = 0;
                $effectiveExamCount = max(1, $effectiveExaminations->count());

                foreach ($subjectData as $subjectName => $data) {
                    $slotCollect = $this->collectTermReportSlotMarksForSubject(
                        $data,
                        $effectiveExaminations,
                        $caDefinitionsByExam,
                    );
                    $slotMarksList = $slotCollect["slot_marks"];
                    $subjectIncomplete = $slotCollect["is_incomplete"];

                    if ($subjectIncomplete) {
                        $subjects[] = [
                            "subject_name" => $subjectName,
                            "exams" => $data["exams"],
                            "average" => null,
                            "grade" => "Incomplete",
                            "is_term_report" => true,
                            "is_incomplete" => true,
                        ];
                        continue;
                    }

                    $average = $slotCollect["average"];

                    if ($schoolType === "Secondary") {
                        $overallGrade = $this->getSecondaryHardcodedGrade(
                            $average,
                            $className,
                        )["grade"];
                    } elseif ($classID) {
                        $overallGrade = $this->getGradeFromDefinition(
                            $average,
                            $classID,
                            $schoolType,
                            $className,
                        )["grade"];
                        if (!$overallGrade) {
                            if ($average >= 75) {
                                $overallGrade = "A";
                            } elseif ($average >= 65) {
                                $overallGrade = "B";
                            } elseif ($average >= 45) {
                                $overallGrade = "C";
                            } elseif ($average >= 30) {
                                $overallGrade = "D";
                            } else {
                                $overallGrade = "F";
                            }
                        }
                    } else {
                        if ($average >= 75) {
                            $overallGrade = "A";
                        } elseif ($average >= 65) {
                            $overallGrade = "B";
                        } elseif ($average >= 45) {
                            $overallGrade = "C";
                        } elseif ($average >= 30) {
                            $overallGrade = "D";
                        } else {
                            $overallGrade = "F";
                        }
                    }

                    $subjects[] = [
                        "subject_name" => $subjectName,
                        "exams" => $data["exams"],
                        "average" => $average,
                        "grade" => $overallGrade,
                        "is_term_report" => true,
                        "is_incomplete" => false,
                    ];

                    $totalMarks += $average;
                    $subjectCount++;
                }

                $averageMarks =
                    $subjectCount > 0 ? $totalMarks / $subjectCount : 0;

                // Per-examination summary: every exam in report definition
                $examinationSummaries = [];
                $resultsByExamID = $results->groupBy("examID");

                foreach ($examinations as $exam) {
                    $examResults = $resultsByExamID->get(
                        $exam->examID,
                        collect(),
                    );
                    $examTotalMarks = 0;
                    $examSubjectCount = 0;
                    $examSubjectPoints = [];

                    foreach ($examResults as $result) {
                        if ($result->marks !== null && $result->marks !== "") {
                            $marks = (float) $result->marks;
                            $examTotalMarks += $marks;
                            $examSubjectCount++;

                            if ($schoolType === "Secondary") {
                                $gradePoints = $this->calculateGradePoints(
                                    $marks,
                                    $schoolType,
                                    $className,
                                    $classID,
                                );
                                $examSubjectPoints[] = [
                                    "points" => $gradePoints["points"] ?? 5,
                                    "marks" => $marks,
                                    "grade" => $gradePoints["grade"] ?? null,
                                ];
                            }
                        }
                    }

                    $examAverage = null;
                    $examGrade = null;
                    $examDivision = null;

                    if ($examSubjectCount > 0) {
                        $examAverage = $examTotalMarks / $examSubjectCount;

                        if ($schoolType === "Secondary") {
                            $examGrade = $this->getSecondaryHardcodedGrade(
                                $examAverage,
                                $className,
                            )["grade"];
                        } elseif ($classID && $examAverage > 0) {
                            $gradeResult = $this->getGradeFromDefinition(
                                $examAverage,
                                $classID,
                                $schoolType,
                                $className,
                            );
                            $examGrade = $gradeResult["grade"];
                        }
                        if (!$examGrade) {
                            if ($examAverage >= 75) {
                                $examGrade = "A";
                            } elseif ($examAverage >= 65) {
                                $examGrade = "B";
                            } elseif ($examAverage >= 45) {
                                $examGrade = "C";
                            } elseif ($examAverage >= 30) {
                                $examGrade = "D";
                            } else {
                                $examGrade = "F";
                            }
                        }

                        if (
                            $schoolType === "Secondary" &&
                            !empty($examSubjectPoints)
                        ) {
                            $examDivisionPick =
                                $this->resolveSecondaryBestSubjectsForDivision(
                                    $examSubjectPoints,
                                    $className,
                                    $schoolType,
                                );
                            $examGradeDivision = $this->calculateGradeDivision(
                                $examTotalMarks,
                                $examAverage,
                                $examSubjectCount,
                                $schoolType,
                                $className,
                                $examDivisionPick["total_points"],
                                $classID,
                                $examDivisionPick["grades"],
                            );
                            $examDivision =
                                $examGradeDivision["division"] ?? null;
                        }
                    }

                    $examinationSummaries[] = [
                        "examID" => $exam->examID,
                        "exam_name" => $exam->exam_name,
                        "average" =>
                            $examAverage !== null
                                ? round($examAverage, 1)
                                : null,
                        "grade" => $examGrade,
                        "division" => $examDivision,
                    ];
                }
            } elseif ($caDefinition) {
                // For school exam with CA: Group by subject and calculate average from Tests + Exam
                $subjectData = [];
                $allInvolvedExams = array_merge(
                    [$examFilter],
                    $caDefinition->test_ids,
                );
                $involvedExams = Examination::whereIn(
                    "examID",
                    $allInvolvedExams,
                )
                    ->orderBy("end_date")
                    ->get()
                    ->keyBy("examID");

                $caResults = Result::where("studentID", $studentIDFilter)
                    ->whereIn("examID", $allInvolvedExams)
                    ->with(["classSubject.subject", "examination"])
                    ->get();
                $caResults = $this->filterResultsForStudentElection(
                    $caResults,
                    $studentIDFilter,
                    $electedClassSubjectIds,
                );

                foreach ($caResults as $result) {
                    $subjectName =
                        $result->classSubject->subject->subject_name ?? "N/A";
                    $examID = $result->examID;
                    $examModel = $involvedExams[$examID] ?? null;
                    $examName = $examModel ? $examModel->exam_name : "Unknown";
                    if ($examModel && $examModel->exam_category === "test") {
                        $examName .= " (CA)";
                    }

                    if (!isset($subjectData[$subjectName])) {
                        $subjectData[$subjectName] = [
                            "subject_name" => $subjectName,
                            "exams" => [],
                            "marks_sum" => 0,
                            "marks_count" => 0,
                        ];
                    }

                    if ($result->marks !== null && $result->marks !== "") {
                        $marks = (float) $result->marks;
                        $gradePoints = $this->calculateGradePoints(
                            $marks,
                            $schoolType,
                            $className,
                            $classID,
                        );

                        $subjectData[$subjectName]["exams"][] = [
                            "marks" => $marks,
                            "grade" => $result->grade ?? $gradePoints["grade"],
                            "exam_name" => $examName,
                        ];
                        $subjectData[$subjectName]["marks_sum"] += $marks;
                        $subjectData[$subjectName]["marks_count"]++;
                    }
                }

                $subjects = [];
                $totalMarks = 0;
                $subjectCount = 0;
                $subjectPoints = [];

                foreach ($subjectData as $subjectName => $data) {
                    $divisor =
                        count($allInvolvedExams) > 0
                            ? count($allInvolvedExams)
                            : ($data["marks_count"] > 0
                                ? $data["marks_count"]
                                : 1);
                    $average = round($data["marks_sum"] / $divisor, 2);
                    if ($schoolType === "Secondary") {
                        $overallGrade = $this->getSecondaryHardcodedGrade(
                            $average,
                            $className,
                        )["grade"];
                    } else {
                        $gradeResult = $classID
                            ? $this->getGradeFromDefinition(
                                $average,
                                $classID,
                                $schoolType,
                                $className,
                            )
                            : null;
                        $overallGrade = $gradeResult ? $gradeResult["grade"] : null;
                        if (!$overallGrade) {
                            if ($average >= 75) {
                                $overallGrade = "A";
                            } elseif ($average >= 65) {
                                $overallGrade = "B";
                            } elseif ($average >= 45) {
                                $overallGrade = "C";
                            } elseif ($average >= 30) {
                                $overallGrade = "D";
                            } else {
                                $overallGrade = "F";
                            }
                        }
                    }

                    $subjects[] = [
                        "subject_name" => $subjectName,
                        "exams" => $data["exams"], // Shows breakdown in View More
                        "marks" => $average,
                        "grade" => $overallGrade,
                        "is_ca_averaged" => true,
                    ];

                    $totalMarks += $average;
                    $subjectCount++;

                    if ($schoolType === "Secondary" && $average > 0) {
                        $avgGradePoints = $this->calculateGradePoints(
                            $average,
                            $schoolType,
                            $className,
                            $classID,
                        );
                        $subjectPoints[] = [
                            "points" => $avgGradePoints["points"] ?? 5,
                            "marks" => $average,
                        ];
                    }
                }
                $averageMarks =
                    $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            } else {
                // For exam: original logic
                $totalMarks = 0;
                $subjectCount = 0;
                $subjects = [];
                $subjectPoints = [];

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== "") {
                        $totalMarks += (float) $result->marks;
                        $subjectCount++;
                    }
                    $gradePoints = $this->calculateGradePoints(
                        $result->marks,
                        $schoolType,
                        $className,
                        $classID,
                    );
                    $subjects[] = [
                        "subject_name" =>
                            $result->classSubject->subject->subject_name ??
                            "N/A",
                        "marks" => $result->marks,
                        "grade" => $result->grade ?? $gradePoints["grade"],
                    ];

                    if (
                        $schoolType === "Secondary" &&
                        $result->marks !== null &&
                        $result->marks !== ""
                    ) {
                        $subjectPoints[] = [
                            "points" => $gradePoints["points"] ?? 5,
                            "marks" => (float) $result->marks,
                        ];
                    }
                }

                $averageMarks =
                    $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            }

            // NECTA division: term report uses CA-aware term averages (same as bulk list / PDF)
            $allSubjectGrades = [];
            if ($typeFilterForDetails === "report") {
                $subjectPoints = $this->buildTermReportSubjectPointEntries(
                    $results,
                    $effectiveExaminations,
                    $caDefinitionsByExam,
                    $schoolType,
                    $className,
                    $studentIDFilter,
                    $electedClassSubjectIds,
                );
                $allSubjectGrades = $this->extractSubjectGradesFromPointEntries(
                    $subjectPoints,
                );
            } else {
                $allSubjectGrades = $this->extractSubjectGradesFromPointEntries(
                    $subjectPoints,
                );
            }

            $bestSevenGrades = [];
            $totalPoints = 0;
            if ($schoolType === "Secondary" && !empty($subjectPoints)) {
                $divisionPick = $this->resolveSecondaryBestSubjectsForDivision(
                    $subjectPoints,
                    $className,
                    $schoolType,
                );
                $totalPoints = $divisionPick["total_points"];
                $bestSevenGrades = $divisionPick["grades"];
            }

            // Calculate grade/division (SKIPPED if specific week is selected for weekly/monthly tests)
            $gradeDivision = ["grade" => null, "division" => null];
            if (!$weekFilter || $weekFilter === "all") {
                $gradeDivision = $this->calculateGradeDivision(
                    $totalMarks,
                    $averageMarks,
                    $subjectCount,
                    $schoolType,
                    $className,
                    $totalPoints,
                    $classID,
                    $bestSevenGrades,
                    $allSubjectGrades,
                );
            }

            // Always calculate a literal grade (A,B,C,D,F) for the overall average to avoid nulls
            if ($schoolType === "Secondary") {
                $calculatedOverallGrade = $this->getSecondaryHardcodedGrade(
                    $averageMarks,
                    $className,
                )["grade"];
            } else {
                $overallGradeResult = $classID
                    ? $this->getGradeFromDefinition(
                        $averageMarks,
                        $classID,
                        $schoolType,
                        $className,
                    )
                    : null;
                $calculatedOverallGrade = $overallGradeResult
                    ? $overallGradeResult["grade"]
                    : null;

                if (!$calculatedOverallGrade) {
                    if ($averageMarks >= 75) {
                        $calculatedOverallGrade = "A";
                    } elseif ($averageMarks >= 65) {
                        $calculatedOverallGrade = "B";
                    } elseif ($averageMarks >= 45) {
                        $calculatedOverallGrade = "C";
                    } elseif ($averageMarks >= 30) {
                        $calculatedOverallGrade = "D";
                    } else {
                        $calculatedOverallGrade = "F";
                    }
                }
            }

            // Get total students count and position for this student in their class
            // Position should be calculated per class (not overall)
            $totalStudentsCount = 0;
            $studentPosition = 0;

            // Get student's class ID
            $studentClassID = null;
            if ($student->subclass && $student->subclass->class) {
                $studentClassID = $student->subclass->class->classID;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $studentClassID = $student->oldSubclass->class->classID;
            }

            if ($studentClassID) {
                // Get all students in the same class
                $classStudents = Student::where("schoolID", $schoolID)
                    ->where("status", "Active")
                    ->where(function ($query) use ($studentClassID) {
                        $query
                            ->whereHas("subclass", function ($q) use (
                                $studentClassID,
                            ) {
                                $q->where("classID", $studentClassID);
                            })
                            ->orWhereHas("oldSubclass", function ($q) use (
                                $studentClassID,
                            ) {
                                $q->where("classID", $studentClassID);
                            });
                    })
                    ->with(["subclass.class", "oldSubclass.class"])
                    ->get();

                // Get results for all students in this class
                $classStudentsWithResults = [];

                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if (
                    $typeFilterForDetails !== "report" &&
                    $examFilter &&
                    !is_array($examFilter)
                ) {
                    $caDef = CaDefinition::where("examID", $examFilter)
                        ->where("schoolID", $schoolID)
                        ->first();
                    if ($caDef && !empty($caDef->test_ids)) {
                        $caExamIDs = array_merge(
                            [$examFilter],
                            $caDef->test_ids,
                        );
                    }
                }

                if ($typeFilterForDetails === "report") {
                    $classResultsData = $this->getTermReport(
                        $classStudents,
                        $termFilter,
                        $yearFilter,
                        $schoolType,
                    );
                } else {
                    $classResultsData = $this->getExamResults(
                        $classStudents,
                        $termFilter,
                        $yearFilter,
                        $schoolType,
                        $caExamIDs,
                    );
                }

                foreach ($classStudents as $classStudent) {
                    if (isset($classResultsData[$classStudent->studentID])) {
                        $studentResultData =
                            $classResultsData[$classStudent->studentID];
                        if ($typeFilterForDetails === "report") {
                            $classStudentsWithResults[] = [
                                "student" => $classStudent,
                                "average_marks" =>
                                    $studentResultData["average_marks"] ?? 0,
                                "total_marks" =>
                                    $studentResultData["total_marks"] ?? 0,
                                "total_points" =>
                                    $studentResultData["total_points"] ?? 9999,
                            ];
                        } else {
                            // Find specific exam in results
                            foreach ($studentResultData as $er) {
                                if ($er["exam"]->examID == $examFilter) {
                                    $classStudentsWithResults[] = [
                                        "student" => $classStudent,
                                        "total_marks" =>
                                            $er["total_marks"] ?? 0,
                                        "total_points" =>
                                            $er["total_points"] ?? 9999,
                                        "best_seven_total_marks" =>
                                            $er["best_seven_total_marks"] ?? 0,
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                }

                $totalStudentsCount = count($classStudentsWithResults);

                // Sort students by performance (same logic as in frontend)
                if ($typeFilterForDetails === "report") {
                    // For term report: sort by average (descending for primary, by grade then average for secondary)
                    if ($schoolType === "Secondary") {
                        usort($classStudentsWithResults, function ($a, $b) {
                            // Sort by grade order (A is best), then by average
                            $gradeOrder = [
                                "A" => 1,
                                "B" => 2,
                                "C" => 3,
                                "D" => 4,
                                "E" => 5,
                                "F" => 6,
                            ];
                            // Calculate grade for average using grade_definitions
                            $avgA = $a["average_marks"] ?? 0;
                            $avgB = $b["average_marks"] ?? 0;

                            // Get classID for students
                            $classIDA = null;
                            $classIDB = null;
                            if (isset($a["student"])) {
                                if (
                                    $a["student"]->subclass &&
                                    $a["student"]->subclass->class
                                ) {
                                    $classIDA =
                                        $a["student"]->subclass->class
                                            ->classID ?? null;
                                } elseif (
                                    $a["student"]->oldSubclass &&
                                    $a["student"]->oldSubclass->class
                                ) {
                                    $classIDA =
                                        $a["student"]->oldSubclass->class
                                            ->classID ?? null;
                                }
                            }
                            if (isset($b["student"])) {
                                if (
                                    $b["student"]->subclass &&
                                    $b["student"]->subclass->class
                                ) {
                                    $classIDB =
                                        $b["student"]->subclass->class
                                            ->classID ?? null;
                                } elseif (
                                    $b["student"]->oldSubclass &&
                                    $b["student"]->oldSubclass->class
                                ) {
                                    $classIDB =
                                        $b["student"]->oldSubclass->class
                                            ->classID ?? null;
                                }
                            }

                            $gradeResultA = $classIDA
                                ? $this->getGradeFromDefinition(
                                    $avgA,
                                    $classIDA,
                                )
                                : null;
                            $gradeResultB = $classIDB
                                ? $this->getGradeFromDefinition(
                                    $avgB,
                                    $classIDB,
                                )
                                : null;
                            $gradeA =
                                $gradeResultA["grade"] ??
                                ($avgA >= 75
                                    ? "A"
                                    : ($avgA >= 65
                                        ? "B"
                                        : ($avgA >= 45
                                            ? "C"
                                            : ($avgA >= 30
                                                ? "D"
                                                : "F"))));
                            $gradeB =
                                $gradeResultB["grade"] ??
                                ($avgB >= 75
                                    ? "A"
                                    : ($avgB >= 65
                                        ? "B"
                                        : ($avgB >= 45
                                            ? "C"
                                            : ($avgB >= 30
                                                ? "D"
                                                : "F"))));
                            $orderA = $gradeOrder[$gradeA] ?? 999;
                            $orderB = $gradeOrder[$gradeB] ?? 999;

                            if ($orderA != $orderB) {
                                return $orderA <=> $orderB; // Lower number = better grade
                            }
                            return $b["average_marks"] <=> $a["average_marks"]; // Higher average = better
                        });
                    } else {
                        // Primary: Sort by average descending
                        usort($classStudentsWithResults, function ($a, $b) {
                            return $b["average_marks"] <=> $a["average_marks"];
                        });
                    }
                } else {
                    // For exam: original sorting logic
                    if ($schoolType === "Secondary") {
                        usort($classStudentsWithResults, function ($a, $b) {
                            // First sort by total points (ascending - lower is better)
                            if ($a["total_points"] != $b["total_points"]) {
                                return $a["total_points"] <=>
                                    $b["total_points"];
                            }
                            // If points are equal, sort by total marks descending (higher marks = better)
                            return $b["total_marks"] <=> $a["total_marks"];
                        });
                    } else {
                        // Primary: Sort by total marks descending
                        usort($classStudentsWithResults, function ($a, $b) {
                            return $b["total_marks"] <=> $a["total_marks"];
                        });
                    }
                }

                // Find position of current student
                foreach (
                    $classStudentsWithResults
                    as $index => $classStudentData
                ) {
                    if (
                        $classStudentData["student"]->studentID ==
                        $studentIDFilter
                    ) {
                        $studentPosition = $index + 1;
                        break;
                    }
                }
                $totalStudentsCount = count($classStudentsWithResults);
            }

            // Get student photo path or placeholder
            $studentPhoto = null;
            if ($student->photo) {
                $studentPhoto = asset("userImages/" . $student->photo);
            } else {
                // Use placeholder based on gender
                $placeholderPath =
                    $student->gender === "Female"
                        ? asset("placeholder/female.png")
                        : asset("placeholder/male.png");
                $studentPhoto = $placeholderPath;
            }

            $responseData = [
                "subjects" => $subjects,
                "schoolType" => $schoolType,
                "student" => [
                    "studentID" => $student->studentID,
                    "first_name" => $student->first_name,
                    "middle_name" => $student->middle_name,
                    "last_name" => $student->last_name,
                    "admission_number" => $student->admission_number,
                    "gender" => $student->gender,
                    "photo" => $studentPhoto,
                ],
                "teacher" => [
                    "name" =>
                        $student->subclass && $student->subclass->teacher
                            ? $student->subclass->teacher->first_name .
                                " " .
                                $student->subclass->teacher->last_name
                            : "N/A",
                    "signature" =>
                        $student->subclass &&
                        $student->subclass->teacher &&
                        $student->subclass->teacher->signature
                            ? asset($student->subclass->teacher->signature)
                            : null,
                ],
                "totalMarks" => $totalMarks,
                "averageMarks" => $averageMarks,
                "subjectCount" => $subjectCount,
                "grade" =>
                    $typeFilterForDetails === "report" &&
                    $schoolType === "Secondary"
                        ? ($gradeDivision["division"] ?? $calculatedOverallGrade)
                        : $calculatedOverallGrade,
                "division" => $gradeDivision["division"] ?? null,
                "totalStudentsCount" => $totalStudentsCount,
                "position" => $studentPosition,
            ];

            // For term report, include exams data
            if ($typeFilterForDetails === "report") {
                // Use the exams we already fetched earlier
                $responseData["exams"] = array_values($examsData);
                $responseData["examinationSummaries"] =
                    $examinationSummaries ?? [];
                $responseData["term"] = $termFilter;
                $responseData["year"] = $yearFilter;
            }

            // Include latest character assessment in timetable range
            // so it can be shown in individual student result modal/PDF.
            $referenceDate = null;
            if ($typeFilterForDetails === "exam" && $examFilter) {
                $selectedExam = Examination::find($examFilter);
                if ($selectedExam) {
                    $referenceDate =
                        $selectedExam->end_date ?: $selectedExam->start_date;
                    // Fill missing term/year from selected exam when needed
                    if (!$termFilter && !empty($selectedExam->term)) {
                        $termFilter = $selectedExam->term;
                    }
                    if (!$yearFilter && !empty($selectedExam->year)) {
                        $yearFilter = $selectedExam->year;
                    }
                }
            }

            $responseData["characterAssessment"] = $this->getLatestCharacterAssessmentForStudent(
                $studentIDFilter,
                $termFilter,
                $yearFilter,
                $schoolID,
                $referenceDate,
            );

            return response()->json($responseData);
        }

        // Get available exams for the selected term + header academic year
        $availableExams = collect();
        if (!empty($academicYearIDs) || $yearFilter) {
            $examQuery = Examination::where("schoolID", $schoolID)->orderBy(
                "start_date",
            );
            $this->applyExaminationAcademicYearFilter(
                $examQuery,
                $academicYearIDs,
                $yearFilter,
            );
            if ($termFilter) {
                $examQuery->where("term", $termFilter);
            }
            $availableExams = $examQuery->get();
        }

        // Get Available Weeks if it's a test
        $availableWeeks = [];
        $examStatusMessage = null;
        if ($examFilter) {
            $selExam = Examination::find($examFilter);
            if ($selExam && $selExam->exam_category === "test") {
                $weeksInfo = ExamPaper::where("examID", $examFilter)
                    ->whereNotNull("test_week")
                    ->select(
                        "test_week",
                        "test_week_range",
                        \DB::raw("MIN(test_date) as start_date"),
                        \DB::raw("MAX(test_date) as end_date"),
                    );

                if ($subclassFilter) {
                    $weeksInfo->whereHas("classSubject", function ($q) use (
                        $subclassFilter,
                    ) {
                        $q->where("subclassID", $subclassFilter);
                    });
                } elseif ($classFilter) {
                    $weeksInfo->whereHas("classSubject", function ($q) use (
                        $classFilter,
                    ) {
                        $q->where("classID", $classFilter);
                    });
                }

                $weeksData = $weeksInfo
                    ->groupBy("test_week", "test_week_range")
                    ->orderByRaw(
                        "CAST(REPLACE(test_week, 'Week ', '') AS UNSIGNED) ASC",
                    )
                    ->get();

                $availableWeeks = [];
                $today = date("Y-m-d");
                foreach ($weeksData as $winfo) {
                    $isCurrent =
                        $today >= $winfo->start_date &&
                        $today <= $winfo->end_date;

                    $rangeDisplay = $winfo->test_week_range;
                    if (
                        empty($rangeDisplay) &&
                        $winfo->start_date &&
                        $winfo->end_date
                    ) {
                        $rangeDisplay =
                            date("d M", strtotime($winfo->start_date)) .
                            " - " .
                            date("d M", strtotime($winfo->end_date));
                    }

                    $label = $winfo->test_week;
                    if (!empty($rangeDisplay)) {
                        $label .= " (" . $rangeDisplay . ")";
                    }

                    if ($isCurrent) {
                        $label = "CURRENT: " . $label;
                    } elseif ($winfo->end_date < $today) {
                        $label = "OLD: " . $label;
                    }

                    $availableWeeks[] = [
                        "week" => $winfo->test_week,
                        "display" => $label,
                    ];
                }

                // Determine Exam Status Message
                // Check if there are any papers with test_date >= today
                $ongoing = ExamPaper::where("examID", $examFilter)
                    ->where("test_date", ">=", date("Y-m-d"))
                    ->exists();

                if ($ongoing) {
                    $examStatusMessage = "This exam is ongoing";

                    try {
                        if (
                            $selExam->end_date &&
                            !in_array($selExam->end_date, [
                                "every_week",
                                "every_month",
                            ])
                        ) {
                            $today = \Carbon\Carbon::now()->startOfDay();
                            $endDate = \Carbon\Carbon::parse(
                                $selExam->end_date,
                            )->startOfDay();

                            if ($endDate->gt($today)) {
                                $weeks = $today->diffInWeeks($endDate);
                                if ($weeks > 0) {
                                    $examStatusMessage .=
                                        " (" .
                                        $weeks .
                                        " " .
                                        ($weeks == 1 ? "week" : "weeks") .
                                        " remaining)";
                                } else {
                                    $days = $today->diffInDays($endDate);
                                    $examStatusMessage .=
                                        " (" .
                                        $days .
                                        " " .
                                        ($days == 1 ? "day" : "days") .
                                        " remaining)";
                                }
                            } elseif ($endDate->eq($today)) {
                                $examStatusMessage .= " (Ends today)";
                            }
                        }
                    } catch (\Exception $e) {
                        // Keep simple message if date parsing fails
                    }
                } elseif (!empty($availableWeeks)) {
                    $examStatusMessage =
                        "This exam has ended. You can view the results for all weeks.";
                }
            }
        }

        // Check result approvals if exam filter is selected
        if ($examFilter && $typeFilter === "exam") {
            $exam = Examination::where("examID", $examFilter)
                ->where("schoolID", $schoolID)
                ->first();

            if ($exam) {
            }
        }

        // Build query for students via enrollment (academic year from header + enrollment status)
        $studentQuery = Student::where("schoolID", $schoolID);
        $hasEnrollments = DB::table("student_enrollments")
            ->where("schoolID", $schoolID)
            ->exists();

        // SECURITY: For regular teachers without broad permissions, ensure they can only access their assigned subclass
        if (
            $userType === "Teacher" &&
            !empty($teacherID) &&
            !$hasBroadResultPermission
        ) {
            if ($isTeacherView) {
                if (empty($subclassFilter)) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with("error", "Please select a class to view results")
                        ->with("error_type", "no_subclass_selected");
                }

                $subclass = Subclass::find($subclassFilter);
                if (!$subclass || $subclass->teacherID != $teacherID) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with(
                            "error",
                            "You do not have access to view results for this class",
                        )
                        ->with("error_type", "unauthorized_access");
                }
            } elseif ($subclassFilter) {
                $subclass = Subclass::find($subclassFilter);
                if (!$subclass || $subclass->teacherID != $teacherID) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with(
                            "error",
                            "You do not have access to view results for this class",
                        )
                        ->with("error_type", "unauthorized_access");
                }
            } else {
                $studentQuery->whereRaw("1 = 0");
            }
        }

        if ($hasEnrollments) {
            $this->applyResultsEnrollmentScopeToStudentsQuery(
                $studentQuery,
                (int) $schoolID,
                $academicYearIDs,
                $classFilter ?: null,
                $subclassFilter ?: null,
                $statusFilter,
            );
        } else {
            // Legacy schools without enrollments table data
            if ($statusFilter === "Active") {
                $studentQuery->where("status", "Active");
            } elseif ($statusFilter === "Transferred") {
                $studentQuery->whereNotNull("old_subclassID");
            } elseif ($statusFilter === "Completed") {
                $studentQuery->where("status", "Graduated");
            } elseif (
                $statusFilter === "Dropped" ||
                $statusFilter === "Cancelled"
            ) {
                $studentQuery->where("status", "Inactive");
            }

            if ($subclassFilter) {
                $studentQuery->where("subclassID", $subclassFilter);
            } elseif ($classFilter) {
                $studentQuery->where(function ($query) use ($classFilter) {
                    $query
                        ->whereHas("subclass", function ($q) use ($classFilter) {
                            $q->where("classID", $classFilter);
                        })
                        ->orWhereHas("oldSubclass", function ($q) use (
                            $classFilter,
                        ) {
                            $q->where("classID", $classFilter);
                        });
                });
            }
        }

        $students = $studentQuery
            ->with([
                "subclass.class",
                "subclass.combie",
                "subclass.teacher",
                "parent",
                "oldSubclass.class",
                "oldSubclass.teacher",
            ])
            ->orderBy("first_name")
            ->get();

        $this->attachEnrollmentMetaToStudents(
            $students,
            (int) $schoolID,
            $academicYearIDs,
            $statusFilter,
        );

        // Get results data
        $resultsData = [];
        $debugInfo = [];

        // Only process if we have term and year (required for results)
        if ($termFilter && $yearFilter) {
            if ($typeFilter === "exam") {
                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if ($examFilter && !is_array($examFilter)) {
                    $caDef = CaDefinition::where("examID", $examFilter)
                        ->where("schoolID", $schoolID)
                        ->first();
                    if ($caDef && !empty($caDef->test_ids)) {
                        $caExamIDs = array_merge(
                            [$examFilter],
                            $caDef->test_ids,
                        );
                    }
                }

                // Get exam results
                $resultsData = $this->getExamResults(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                    $caExamIDs,
                    $weekFilter,
                    $subjectFilter,
                    $schoolID,
                    $academicYearIDs,
                );
            } elseif ($typeFilter === "report") {
                // Get term report
                $resultsData = $this->getTermReport(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                    $subjectFilter,
                    $schoolID,
                    $academicYearIDs,
                );
            }
            // Debug: Check why results might be empty
            if (empty($resultsData) && $students->count() > 0) {
                // Check if exam exists and is approved
                if ($examFilter) {
                    $exam = Examination::where("examID", $examFilter)
                        ->where("schoolID", $schoolID)
                        ->first();
                    if (!$exam) {
                        $debugInfo[] = "Exam with ID {$examFilter} not found.";
                    } elseif ($exam->approval_status !== "Approved") {
                        $debugInfo[] = "Exam '{$exam->exam_name}' is not approved yet.";
                    } else {
                        // Check if exam has ended
                        $isWeeklyTest =
                            $exam->exam_name === "Weekly Test" ||
                            $exam->start_date === "every_week" ||
                            $exam->end_date === "every_week";
                        $isMonthlyTest =
                            $exam->exam_name === "Monthly Test" ||
                            $exam->start_date === "every_month" ||
                            $exam->end_date === "every_month";
                        if (!$isWeeklyTest && !$isMonthlyTest) {
                            try {
                                $today = now()->startOfDay();
                                $endDate = \Carbon\Carbon::parse(
                                    $exam->end_date,
                                )->startOfDay();
                                if ($endDate >= $today) {
                                    $debugInfo[] = "Exam '{$exam->exam_name}' has not ended yet (ends on {$exam->end_date}).";
                                }
                            } catch (\Exception $e) {
                                // Date parsing failed
                            }
                        }
                    }
                }

                // Check if students have results but with wrong status or no marks
                $studentIDs = $students->pluck("studentID")->toArray();
                $totalResults = \App\Models\Result::whereIn(
                    "studentID",
                    $studentIDs,
                );
                if ($examFilter) {
                    $totalResults->where("examID", $examFilter);
                } else {
                    $examsQuery = Examination::where("schoolID", $schoolID)->where(
                        "term",
                        $termFilter,
                    );
                    $this->applyExaminationAcademicYearFilter(
                        $examsQuery,
                        $academicYearIDs,
                        $yearFilter,
                    );
                    $examIDs = $examsQuery->pluck("examID")->toArray();
                    $totalResults->whereIn("examID", $examIDs);
                }

                $totalCount = $totalResults->count();
                $allowedCount = (clone $totalResults)
                    ->where("status", "allowed")
                    ->count();
                $withMarksCount = (clone $totalResults)
                    ->whereNotNull("marks")
                    ->count();
                $allowedWithMarksCount = (clone $totalResults)
                    ->where("status", "allowed")
                    ->whereNotNull("marks")
                    ->count();

                if ($totalCount > 0) {
                    $notAllowedCount = $totalCount - $allowedCount;
                    if ($allowedCount < $totalCount) {
                        if ($notAllowedCount > 0) {
                            $debugInfo[] = "Found {$totalCount} results total. {$notAllowedCount} results have status 'not_allowed' and need to be allowed first. Only results with status 'allowed' can be viewed.";
                        } else {
                            $debugInfo[] = "Found {$totalCount} results, but only {$allowedCount} have status 'allowed'. Results need to be allowed first.";
                        }
                    }
                    if ($withMarksCount < $totalCount) {
                        $debugInfo[] = "Found {$totalCount} results, but only {$withMarksCount} have marks entered.";
                    }
                    if ($allowedWithMarksCount < $totalCount) {
                        $debugInfo[] = "Found {$totalCount} results, but only {$allowedWithMarksCount} have both status 'allowed' and marks entered. To view results, you need to change the status from 'not_allowed' to 'allowed'.";
                    }
                } else {
                    $debugInfo[] =
                        "No results found in database for selected students and exam.";
                }
            }
        }

        return view("Admin.result_management", [
            "school" => $school,
            "schoolType" => $schoolType,
            "students" => $students,
            "resultsData" => $resultsData,
            "classes" => $classes,
            "subclasses" => $subclasses,
            "availableYears" => $availableYears,
            "availableWeeks" => $availableWeeks,
            "examStatusMessage" => $examStatusMessage,
            "academicYearLabel" => $academicYearLabel,
            "academicYearIDs" => $academicYearIDs,
            "filters" => [
                "term" => $termFilter,
                "year" => $yearFilter,
                "academic_yearID" => !empty($academicYearIDs)
                    ? implode(",", $academicYearIDs)
                    : "",
                "type" => $typeFilter,
                "status" => $statusFilter,
                "class" => $classFilter,
                "subclass" => $subclassFilter,
                "examID" => $examFilter,
                "week" => $weekFilter,
                "subjectID" => $subjectFilter,
            ],
            "availableExams" => $availableExams,
            "schoolSubjects" => SchoolSubject::where("schoolID", $schoolID)
                ->where("status", "Active")
                ->orderBy("subject_name")
                ->get(),
            "isTeacherView" => $isTeacherView, // Pass teacher view flag
            "isCoordinatorResultsView" => $isCoordinatorResultsView ?? false,
            "debugInfo" => $debugInfo ?? [],
            "user_type" => $userType, // Pass user type for navigation
            "error" => null, // No error if we reach here
        ]);
    }

    /**
     * Send SMS reminder to a single teacher
     */
    public function sendTeacherReminder(Request $request)
    {
        try {
            $phoneNumber = $request->input("phone");
            $message = $request->input("message");
            $teacherName = $request->input("name");

            if (!$phoneNumber || !$message) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Phone number and message are required.",
                    ],
                    400,
                );
            }

            $smsService = new SmsService();
            $result = $smsService->sendSms($phoneNumber, $message);

            if ($result["success"]) {
                return response()->json([
                    "success" => true,
                    "message" => "Reminder sent to {$teacherName}",
                ]);
            } else {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Gateway error: " . $result["message"],
                    ],
                    500,
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "System error: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Broadcast SMS reminder to all teachers with incomplete results for an exam
     */
    public function sendBroadcastReminder(Request $request)
    {
        try {
            $examID = $request->input("examID");
            $customMessageTemplate = $request->input("message"); // e.g. "Habari {name}, ..." or generic
            $schoolID = Session::get("schoolID");

            if (!$schoolID || !$examID || !$customMessageTemplate) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Missing required broadcast parameters.",
                    ],
                    400,
                );
            }

            // Reuse the scanning logic to identify teachers with incomplete results
            // 1. Get all students grouped by subclass
            $studentsBySubclass = DB::table("students")
                ->where("schoolID", $schoolID)
                ->where("status", "Active")
                ->pluck("subclassID", "studentID")
                ->groupBy(function ($item) {
                    return $item;
                });

            // 2. Get students by subclass properly
            $subclassStudentsCount = DB::table("students")
                ->where("schoolID", $schoolID)
                ->where("status", "Active")
                ->select("subclassID", DB::raw("count(*) as total"))
                ->groupBy("subclassID")
                ->pluck("total", "subclassID");

            // 3. Get completed marks count per subject
            $completedCounts = DB::table("results")
                ->where("examID", $examID)
                ->whereNotNull("marks")
                ->select("class_subjectID", DB::raw("count(*) as count"))
                ->groupBy("class_subjectID")
                ->pluck("count", "class_subjectID");

            // 4. Find all active class subjects for this school
            $classSubjects = ClassSubject::where("status", "Active")
                ->whereHas("subclass.class", function ($q) use ($schoolID) {
                    $q->where("schoolID", $schoolID);
                })
                ->with(["subject", "teacher"])
                ->get();

            $teachersToNotify = [];
            foreach ($classSubjects as $cs) {
                if (!$cs->teacher || !$cs->teacher->phone_number) {
                    continue;
                }

                $expected = $subclassStudentsCount->get($cs->subclassID) ?: 0;
                $actual = $completedCounts->get($cs->class_subjectID) ?: 0;

                if ($actual < $expected) {
                    $teachersToNotify[$cs->teacherID] = [
                        "phone" => $cs->teacher->phone_number,
                        "name" =>
                            $cs->teacher->first_name .
                            " " .
                            $cs->teacher->last_name,
                    ];
                }
            }

            if (empty($teachersToNotify)) {
                return response()->json([
                    "success" => true,
                    "message" => "No teachers with pending results found.",
                ]);
            }

            $smsService = new SmsService();
            $successCount = 0;
            $failCount = 0;

            foreach ($teachersToNotify as $teacher) {
                // Personalize if template contains placeholder
                $msg = str_replace(
                    "{name}",
                    $teacher["name"],
                    $customMessageTemplate,
                );
                $res = $smsService->sendSms($teacher["phone"], $msg);

                if ($res["success"]) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            return response()->json([
                "success" => true,
                "message" => "Broadcast complete. Sent: {$successCount}, Failed: {$failCount}",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "Broadcast failed: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * class_subjectIDs this student elected (optional subjects only).
     *
     * @return list<int>
     */
    private function getElectedClassSubjectIdsForStudent(int $studentID): array
    {
        return SubjectElector::where("studentID", $studentID)
            ->pluck("classSubjectID")
            ->map(fn($id) => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>|null  $electedClassSubjectIds
     */
    private function shouldIncludeClassSubjectForStudent(
        int $studentID,
        ?ClassSubject $classSubject,
        ?array $electedClassSubjectIds = null,
    ): bool {
        if (!$classSubject) {
            return false;
        }

        if (strtolower(trim((string) ($classSubject->student_status ?? ""))) !== "optional") {
            return true;
        }

        $electedClassSubjectIds ??= $this->getElectedClassSubjectIdsForStudent(
            $studentID,
        );

        return in_array(
            (int) $classSubject->class_subjectID,
            $electedClassSubjectIds,
            true,
        );
    }

    /**
     * @param  list<int>|null  $electedClassSubjectIds
     */
    private function shouldIncludeResultForStudent(
        int $studentID,
        $result,
        ?array $electedClassSubjectIds = null,
    ): bool {
        return $this->shouldIncludeClassSubjectForStudent(
            $studentID,
            $result->classSubject ?? null,
            $electedClassSubjectIds,
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Result>  $results
     * @param  list<int>|null  $electedClassSubjectIds
     * @return \Illuminate\Support\Collection<int, \App\Models\Result>
     */
    private function filterResultsForStudentElection(
        $results,
        int $studentID,
        ?array $electedClassSubjectIds = null,
    ) {
        $electedClassSubjectIds ??= $this->getElectedClassSubjectIdsForStudent(
            $studentID,
        );

        return $results
            ->filter(function ($result) use (
                $studentID,
                $electedClassSubjectIds,
            ) {
                return $this->shouldIncludeResultForStudent(
                    $studentID,
                    $result,
                    $electedClassSubjectIds,
                );
            })
            ->values();
    }

    /**
     * Active students enrolled for a class_subject (required = whole stream; optional = electors only).
     */
    private function getStudentsForSubjectAnalysis(ClassSubject $classSubject): Collection
    {
        $schoolID = Session::get("schoolID");
        $studentsQuery = Student::where("status", "Active");

        if ($classSubject->subclassID) {
            $studentsQuery->inSubclass($classSubject->subclassID, $schoolID);
        } elseif ($classSubject->classID) {
            $studentsQuery->inClass($classSubject->classID, $schoolID);
        }

        $students = $studentsQuery->get();

        if (strtolower($classSubject->student_status ?? "") === "optional") {
            $electedIds = SubjectElector::where(
                "classSubjectID",
                $classSubject->class_subjectID,
            )
                ->pluck("studentID")
                ->toArray();
            $students = $students->whereIn("studentID", $electedIds);
        }

        return $students->values();
    }

    /**
     * Build analysis rows for one subject stream or aggregated across subclasses (same main class).
     */
    private function buildSubjectAnalysisEntry(
        Collection $classSubjectGroup,
        int $examID,
        bool $aggregated,
    ): array {
        $primary = $classSubjectGroup->first();
        $classSubjectIDs = $classSubjectGroup
            ->pluck("class_subjectID")
            ->filter()
            ->values()
            ->all();

        $subjectName = $primary->subject->subject_name ?? "N/A";
        $className = $primary->class->class_name ?? "N/A";

        if ($aggregated) {
            $streamNames = $classSubjectGroup
                ->map(fn($cs) => trim($cs->subclass->subclass_name ?? ""))
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $classDisplay = $className;
            if ($streamNames->isNotEmpty()) {
                $classDisplay .= " (Streams: " . $streamNames->implode(", ") . ")";
            }
        } else {
            $subclassName = $primary->subclass->subclass_name ?? "";
            $classDisplay = trim($className . " " . $subclassName);
        }

        $students = collect();
        foreach ($classSubjectGroup as $classSubject) {
            $students = $students->merge(
                $this->getStudentsForSubjectAnalysis($classSubject),
            );
        }
        $students = $students->unique("studentID")->values();

        $results = Result::with(["student"])
            ->where("examID", $examID)
            ->whereIn("class_subjectID", $classSubjectIDs)
            ->get();

        $resultsByStudent = $results
            ->groupBy("studentID")
            ->map(function ($rows) {
                return $rows->sortByDesc("marks")->first();
            });

        $resultRows = $students->map(function ($student) use ($resultsByStudent) {
            $result = $resultsByStudent->get($student->studentID);
            return [
                "student" => $student,
                "marks" => $result ? $result->marks : null,
                "grade" => $result ? $result->grade : null,
                "remark" => $result ? $result->remark : null,
                "result_id" => $result ? $result->resultID : null,
            ];
        });

        $answeredRows = $resultRows->filter(fn($row) => $row["marks"] !== null);
        $passCount = $answeredRows
            ->filter(function ($row) {
                $grade = strtoupper($row["grade"] ?? "");
                return in_array($grade, ["A", "B", "C", "D"]);
            })
            ->count();
        $failCount = $answeredRows
            ->filter(function ($row) {
                return strtoupper($row["grade"] ?? "") === "F";
            })
            ->count();
        $overallRemark =
            $answeredRows->count() > 0
                ? ($passCount >= $failCount ? "Pass" : "Fail")
                : "Incomplete";
        $overallClass =
            $overallRemark === "Pass"
                ? "success"
                : ($overallRemark === "Fail"
                    ? "danger"
                    : "secondary");

        $questions = collect();
        $optionalRanges = collect();
        $questionStats = [];
        $studentQuestionMarks = [];
        $bestQuestion = null;
        $worstQuestion = null;

        $marks = ExamPaperQuestionMark::where("examID", $examID)
            ->whereIn("class_subjectID", $classSubjectIDs)
            ->get();

        $questionBuckets = [];

        foreach ($classSubjectGroup as $classSubject) {
            $examPaper = ExamPaper::where("examID", $examID)
                ->where("class_subjectID", $classSubject->class_subjectID)
                ->where("status", "approved")
                ->orderBy("created_at", "desc")
                ->first();

            if (!$examPaper) {
                continue;
            }

            if ($questions->isEmpty()) {
                $questions = ExamPaperQuestion::where(
                    "exam_paperID",
                    $examPaper->exam_paperID,
                )
                    ->orderBy("question_number")
                    ->get();
                $optionalRanges = ExamPaperOptionalRange::where(
                    "exam_paperID",
                    $examPaper->exam_paperID,
                )
                    ->orderBy("range_number")
                    ->get();
            }

            $paperQuestions = ExamPaperQuestion::where(
                "exam_paperID",
                $examPaper->exam_paperID,
            )->get();

            foreach ($paperQuestions as $question) {
                $qNum = (int) $question->question_number;
                if (!isset($questionBuckets[$qNum])) {
                    $questionBuckets[$qNum] = [
                        "question" => $question,
                        "marks" => collect(),
                    ];
                }

                $qMarks = $marks
                    ->where(
                        "exam_paper_questionID",
                        $question->exam_paper_questionID,
                    )
                    ->pluck("marks")
                    ->filter(fn($value) => $value !== null);

                $questionBuckets[$qNum]["marks"] = $questionBuckets[$qNum][
                    "marks"
                ]->merge($qMarks);
            }
        }

        ksort($questionBuckets);

        foreach ($marks as $mark) {
            $studentQuestionMarks[$mark->studentID][
                $mark->exam_paper_questionID
            ] = $mark->marks;
        }

        foreach ($questionBuckets as $bucket) {
            $question = $bucket["question"];
            $questionMarks = $bucket["marks"];
            $selectedCount = $questionMarks->count();
            $avg = $selectedCount > 0 ? $questionMarks->avg() : null;
            $percent =
                $selectedCount > 0 && $question->marks > 0
                    ? round(($avg / $question->marks) * 100, 1)
                    : null;

            $questionStats[] = [
                "question" => $question,
                "average" => $avg !== null ? round($avg, 2) : null,
                "percent" => $percent,
                "selected_count" => $selectedCount,
            ];
        }

        if ($questions->isEmpty() && !empty($questionBuckets)) {
            $questions = collect($questionBuckets)
                ->sortKeys()
                ->pluck("question")
                ->values();
        }

        $scoredQuestions = collect($questionStats)->filter(
            fn($stat) => $stat["percent"] !== null,
        );
        if ($scoredQuestions->isNotEmpty()) {
            $bestQuestion = $scoredQuestions->sortByDesc("percent")->first();
            $worstQuestion = $scoredQuestions->sortBy("percent")->first();
        }

        $optionalSelections = [];
        foreach ($questionStats as $stat) {
            $question = $stat["question"] ?? null;
            if ($question && !empty($question->is_optional)) {
                $optionalSelections[] = [
                    "question" => $question,
                    "count" => $stat["selected_count"] ?? 0,
                ];
            }
        }
        if (!empty($optionalSelections)) {
            usort(
                $optionalSelections,
                fn($a, $b) => $b["count"] <=> $a["count"],
            );
        }

        $totalAnswered = $answeredRows->count();
        $passRate =
            $totalAnswered > 0
                ? round(($passCount / $totalAnswered) * 100, 1)
                : 0;
        $failRate =
            $totalAnswered > 0
                ? round(($failCount / $totalAnswered) * 100, 1)
                : 0;

        return [
            "class_subjectID" => $primary->class_subjectID,
            "subject_name" => $subjectName,
            "class_display" => $classDisplay,
            "aggregated" => $aggregated,
            "teacher" => $primary->teacher,
            "results" => $results,
            "result_rows" => $resultRows,
            "questions" => $questions,
            "optional_ranges" => $optionalRanges,
            "question_stats" => $questionStats,
            "optional_selections" => $optionalSelections,
            "best_question" => $bestQuestion,
            "worst_question" => $worstQuestion,
            "student_question_marks" => $studentQuestionMarks,
            "overall_stats" => [
                "answered" => $totalAnswered,
                "pass" => $passCount,
                "fail" => $failCount,
                "pass_rate" => $passRate,
                "fail_rate" => $failRate,
                "remark" => $overallRemark,
                "remark_class" => $overallClass,
            ],
        ];
    }

    private function buildSubjectAnalysisData(
        Collection $classSubjects,
        int $examID,
        bool $aggregateBySubject,
    ): array {
        $analysisData = [];

        if ($aggregateBySubject) {
            foreach ($classSubjects->groupBy("subjectID") as $group) {
                $analysisData[] = $this->buildSubjectAnalysisEntry(
                    $group,
                    $examID,
                    true,
                );
            }
        } else {
            foreach ($classSubjects as $classSubject) {
                $analysisData[] = $this->buildSubjectAnalysisEntry(
                    collect([$classSubject]),
                    $examID,
                    false,
                );
            }
        }

        return $analysisData;
    }

    public function subjectAnalysis(Request $request)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Access denied");
        }

        $year = $request->get("year", "");
        $term = $request->get("term", "");
        $examID = $request->get("examID", "");
        $classID = $request->get("classID", "");
        $subclassID = $request->get("subclassID", "");
        $subjectID = $request->get("subjectID", "");
        $aggregateBySubject = (bool) $classID && !$subclassID;
        $allSubclasses = $aggregateBySubject;

        $availableYears = Examination::where("schoolID", $schoolID)
            ->distinct()
            ->orderBy("year", "desc")
            ->pluck("year")
            ->toArray();

        $classes = ClassModel::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->orderBy("class_name")
            ->get();

        $subjects = SchoolSubject::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->orderBy("subject_name")
            ->get();

        $examsQuery = Examination::where("schoolID", $schoolID);
        if ($year) {
            $examsQuery->where("year", $year);
        }
        if ($term) {
            $examsQuery->where("term", $term);
        }
        $exams = $examsQuery->orderBy("start_date", "desc")->get();

        $analysisData = [];
        $selectedExam = null;
        if ($examID) {
            $selectedExam = Examination::where("examID", $examID)
                ->where("schoolID", $schoolID)
                ->first();

            $classSubjectsQuery = ClassSubject::with([
                "subject",
                "class",
                "subclass",
                "teacher",
            ])->where("status", "Active");

            if ($classID) {
                $classSubjectsQuery->where("classID", $classID);
            }

            if ($subclassID && !$aggregateBySubject) {
                $classSubjectsQuery->where("subclassID", $subclassID);
            }

            if ($subjectID) {
                $classSubjectsQuery->where("subjectID", $subjectID);
            }

            $classSubjects = $classSubjectsQuery->get();

            $analysisData = $this->buildSubjectAnalysisData(
                $classSubjects,
                (int) $examID,
                $aggregateBySubject,
            );
        }

        $groupedAnalysis = collect($analysisData)->groupBy("class_display");

        $user_type = Session::get("user_type");
        return view(
            "Admin.subject_analysis",
            compact(
                "availableYears",
                "classes",
                "subjects",
                "exams",
                "analysisData",
                "groupedAnalysis",
                "year",
                "term",
                "examID",
                "selectedExam",
                "classID",
                "subclassID",
                "subjectID",
                "allSubclasses",
                "user_type",
            ),
        );
    }

    public function exportSubjectAnalysisPdf(Request $request)
    {
        set_time_limit(0);
        ini_set("memory_limit", "2048M");

        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return response()->json([
                "success" => false,
                "message" => "Session expired",
            ]);
        }

        $year = $request->get("year", "");
        $term = $request->get("term", "");
        $examID = $request->get("examID", "");
        $classID = $request->get("classID", "");
        $subclassID = $request->get("subclassID", "");
        $subjectID = $request->get("subjectID", "");
        $aggregateBySubject = (bool) $classID && !$subclassID;

        $school = \App\Models\School::find($schoolID);
        $selectedExam = Examination::where("examID", $examID)
            ->where("schoolID", $schoolID)
            ->first();

        if (!$selectedExam) {
            return redirect()->back()->with("error", "Exam not found");
        }

        // Logic copied from subjectAnalysis for data gathering
        $analysisData = [];
        $classSubjectsQuery = ClassSubject::with([
            "subject",
            "class",
            "subclass",
            "teacher",
        ])->where("status", "Active");

        if ($classID) {
            $classSubjectsQuery->where("classID", $classID);
        }

        if ($subclassID && !$aggregateBySubject) {
            $classSubjectsQuery->where("subclassID", $subclassID);
        }

        if ($subjectID) {
            $classSubjectsQuery->where("subjectID", $subjectID);
        }

        $classSubjects = $classSubjectsQuery->get();

        $analysisData = $this->buildSubjectAnalysisData(
            $classSubjects,
            (int) $examID,
            $aggregateBySubject,
        );

        $groupedAnalysis = collect($analysisData)->groupBy("class_display");

        $data = [
            "school" => $school,
            "selectedExam" => $selectedExam,
            "groupedAnalysis" => $groupedAnalysis,
            "title" => "Subject Analysis - " . $selectedExam->exam_name,
        ];

        $pdf = PDF::loadView("Admin.pdf.subject_analysis_pdf", $data);
        $pdf->setOptions([
            "isRemoteEnabled" => true,
            "isHtml5ParserEnabled" => true,
            "chroot" => base_path(),
        ]);
        $pdf->setPaper("A4", "portrait");

        $filename =
            "Subject_Analysis_" .
            str_replace(" ", "_", $selectedExam->exam_name) .
            "_" .
            date("YmdHis") .
            ".pdf";
        return $pdf->download($filename);
    }

    public function exportSubjectPerformanceStatisticsPdf(Request $request)
    {
        $userType = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (
            ($userType !== "Admin" && !$this->hasPermission("view_results")) ||
            !$schoolID
        ) {
            return response()->json(["error" => "Access denied"], 403);
        }

        $validator = Validator::make($request->all(), [
            "rows" => "required|array|min:1",
            "rows.*.subject" => "required|string",
            "rows.*.grade_breakdown" => "required|array",
            "rows.*.gpa" => "nullable",
            "rows.*.grade" => "nullable|string",
            "term" => "nullable|string",
            "year" => "nullable|string",
            "class_label" => "nullable|string",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => "Invalid subject statistics payload",
                "details" => $validator->errors(),
            ], 422);
        }

        $school = School::find($schoolID);
        $schoolType = strtolower($school->school_type ?? "secondary");
        $isPrimary = $schoolType === "primary";
        $gradeHeaders = $isPrimary
            ? ["A", "B", "C", "D", "E", "F"]
            : ["A", "B", "C", "D", "F"];

        $rows = collect($request->input("rows", []))
            ->map(function ($row) use ($gradeHeaders) {
                $gradeBreakdown = [];
                foreach ($gradeHeaders as $grade) {
                    $cell = $row["grade_breakdown"][$grade] ?? [];
                    $gradeBreakdown[$grade] = [
                        "female" => (int) ($cell["female"] ?? 0),
                        "male" => (int) ($cell["male"] ?? 0),
                        "total" => (int) ($cell["total"] ?? 0),
                    ];
                }
                return [
                    "subject" => (string) ($row["subject"] ?? ""),
                    "grade_breakdown" => $gradeBreakdown,
                    "gpa" => (float) ($row["gpa"] ?? 0),
                    "gpa_text" => number_format((float) ($row["gpa"] ?? 0), 2),
                    "grade" => strtoupper(trim((string) ($row["grade"] ?? "Incomplete"))),
                ];
            })
            ->sortBy("subject")
            ->values();

        $ranked = $isPrimary
            ? $rows->sortByDesc("gpa")->values()
            : $rows->sortBy("gpa")->values();
        $rankMap = [];
        foreach ($ranked as $index => $item) {
            $rankMap[$item["subject"]] = ($index + 1) . "/" . max(1, $ranked->count());
        }

        $rows = $rows
            ->map(function ($row) use ($rankMap, $isPrimary) {
                $overall = collect($row["grade_breakdown"])->sum("total");
                $grade = $row["grade"];
                $remarkText = "Incomplete";
                $remarkClass = "remark-inc";
                $competency = "Incomplete";

                if ($overall > 0) {
                    if (!$isPrimary) {
                        if ($grade === "A") {
                            $remarkText = "Excellent";
                            $remarkClass = "remark-excellent";
                            $competency = "Grade A (Excellent)";
                        } elseif ($grade === "B") {
                            $remarkText = "Very Good";
                            $remarkClass = "remark-pass";
                            $competency = "Grade B (Very Good)";
                        } elseif ($grade === "C") {
                            $remarkText = "Good";
                            $remarkClass = "remark-good";
                            $competency = "Grade C (Good)";
                        } elseif ($grade === "D") {
                            $remarkText = "Satisfactory";
                            $remarkClass = "remark-avg";
                            $competency = "Grade D (Satisfactory)";
                        } elseif ($grade === "F") {
                            $remarkText = "Fail";
                            $remarkClass = "remark-fail";
                            $competency = "Grade F (Fail)";
                        }
                    } else {
                        if (in_array($grade, ["A", "B", "C", "D"])) {
                            $remarkText = "Pass";
                            $remarkClass = "remark-pass";
                            $competency = "Grade {$grade} (Pass)";
                        } elseif (in_array($grade, ["E", "F"])) {
                            $remarkText = "Fail";
                            $remarkClass = "remark-fail";
                            $competency = "Grade {$grade} (Fail)";
                        }
                    }
                }

                $row["rank"] = $rankMap[$row["subject"]] ?? "-";
                $row["remark_text"] = $remarkText;
                $row["remark_class"] = $remarkClass;
                $row["competency_level"] = $competency;
                return $row;
            })
            ->values();

        $pdf = PDF::loadView("Admin.pdf.subject_performance_statistics_pdf", [
            "school" => $school,
            "schoolType" => $isPrimary ? "Primary" : "Secondary",
            "gradeHeaders" => $gradeHeaders,
            "rows" => $rows,
            "termLabel" => $request->input("term", "-"),
            "yearLabel" => $request->input("year", "-"),
            "classLabel" => $request->input("class_label", "All Classes"),
        ]);
        $pdf->setOptions([
            "isRemoteEnabled" => true,
            "isHtml5ParserEnabled" => true,
            "chroot" => base_path(),
        ]);
        $pdf->setPaper("A4", "landscape");

        $safeClass = preg_replace("/[^a-zA-Z0-9]+/", "_", (string) $request->input("class_label", "Class"));
        $filename = "Subject_Performance_Summary_" . trim($safeClass, "_") . "_" . date("YmdHis") . ".pdf";

        return $pdf->download($filename);
    }

    public function getClassSubjectsForAnalysis(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $classID = $request->input("classID");
            $subclassID = $request->input("subclassID");

            if (!$schoolID || !$classID) {
                return response()->json([
                    "success" => false,
                    "error" => "Class ID and school ID required",
                ]);
            }

            $query = DB::table("class_subjects")
                ->join(
                    "school_subjects",
                    "class_subjects.subjectID",
                    "=",
                    "school_subjects.subjectID",
                )
                ->join(
                    "classes",
                    "class_subjects.classID",
                    "=",
                    "classes.classID",
                )
                ->where("classes.schoolID", $schoolID)
                ->where("class_subjects.status", "Active")
                ->where("school_subjects.status", "Active")
                ->where("class_subjects.classID", $classID);

            if ($subclassID) {
                $query->where(function ($subQuery) use ($subclassID) {
                    $subQuery
                        ->where("class_subjects.subclassID", $subclassID)
                        ->orWhereNull("class_subjects.subclassID");
                });
            }

            $subjects = $query
                ->select(
                    "school_subjects.subjectID",
                    "school_subjects.subject_name",
                )
                ->distinct()
                ->orderBy("school_subjects.subject_name")
                ->get();

            return response()->json([
                "success" => true,
                "subjects" => $subjects,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function sendSubjectAnalysisComment(Request $request)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return response()->json(["error" => "Access denied"], 403);
        }

        $validator = Validator::make($request->all(), [
            "teacherID" => "required|integer",
            "message" => "required|string|max:500",
            "class_subjectID" => "required|integer",
            "examID" => "nullable|integer",
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 422);
        }

        $teacher = Teacher::where("id", $request->teacherID)
            ->where("schoolID", $schoolID)
            ->first();

        if (!$teacher || empty($teacher->phone_number)) {
            return response()->json(
                ["error" => "Teacher phone number not available"],
                422,
            );
        }

        $classSubject = ClassSubject::with(["subject", "class", "subclass"])
            ->where("class_subjectID", $request->class_subjectID)
            ->first();

        if (!$classSubject) {
            return response()->json(
                ["error" => "Class subject not found"],
                404,
            );
        }

        $subjectName = $classSubject->subject->subject_name ?? "Subject";
        $className = $classSubject->class->class_name ?? "";
        $subclassName = $classSubject->subclass->subclass_name ?? "";
        $classDisplay = trim($className . " " . $subclassName);

        $examName = "Exam";
        if ($request->examID) {
            $exam = Examination::where("examID", $request->examID)
                ->where("schoolID", $schoolID)
                ->first();
            if ($exam) {
                $examName = $exam->exam_name;
            }
        }

        $smsMessage = "Subject analysis comment: {$subjectName} ({$classDisplay}) - {$examName}. {$request->message}";
        try {
            $smsService = new SmsService();
            $smsService->sendSms($teacher->phone_number, $smsMessage);
        } catch (\Exception $e) {
            return response()->json(["error" => "Failed to send message"], 500);
        }

        return response()->json(["success" => true]);
    }

    public function sendResultSms(Request $request)
    {
        \Illuminate\Support\Facades\Log::info(
            "sendResultSms method reached",
            $request->all(),
        );
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            \Illuminate\Support\Facades\Log::error(
                "sendResultSms: Access denied - No schoolID in session",
            );
            return response()->json(
                ["success" => false, "error" => "Access denied"],
                403,
            );
        }

        $studentID = $request->input("studentID");
        $subject = $request->input("subject");
        $marks = $request->input("marks");
        $grade = $request->input("grade");
        $week = $request->input("week");
        $examID = $request->input("examID");

        $student = Student::with([
            "parent",
            "subclass.class",
            "oldSubclass.class",
        ])->find($studentID);
        if (!$student) {
            \Illuminate\Support\Facades\Log::warning(
                "sendResultSms: Student not found for ID {$studentID}",
            );
            return response()->json([
                "success" => false,
                "error" => "Student not found",
            ]);
        }

        $parentPhone = $this->resolveParentPhoneForStudent($student);

        if (empty($parentPhone)) {
            \Illuminate\Support\Facades\Log::warning(
                "sendResultSms: No valid phone for student {$studentID}",
            );
            return response()->json([
                "success" => false,
                "error" => "No valid contact phone found for student or parent",
            ]);
        }

        $studentName = trim(
            $student->first_name .
                " " .
                $student->middle_name .
                " " .
                $student->last_name,
        );
        $studentFirstName = $student->first_name;

        $className = "";
        if ($student->subclass && $student->subclass->class) {
            $className = $student->subclass->class->class_name;
        } elseif ($student->oldSubclass && $student->oldSubclass->class) {
            $className = $student->oldSubclass->class->class_name;
        }

        $type = $request->input("type", "exam");
        $term = $request->input("term");
        $year = $request->input("year", date("Y"));
        $userType = Session::get("user_type");

        $totalSum = 0;
        $formattedResults = [];
        $classID =
            $student->subclass->classID ??
            ($student->oldSubclass->classID ?? null);

        if ($subject) {
            // Single subject mode
            $totalSum = (float) $marks;
            $formattedResults[] =
                "{$subject}-(" . round($marks) . ")-({$grade})";
            $resultsString = $formattedResults[0];
        } elseif ($type === "report") {
            // Term report: Average across exams in TermReportDefinition if available
            $reportDefinition = TermReportDefinition::where(
                "schoolID",
                $schoolID,
            )
                ->where("year", $year)
                ->where("term", $term)
                ->first();

            $examinationsQuery = Examination::where("schoolID", $schoolID)
                ->where("year", $year)
                ->where("term", $term);

            if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
                $examinationsQuery->whereIn(
                    "examID",
                    $reportDefinition->exam_ids,
                );
            }

            $examinations = $examinationsQuery->pluck("examID");

            $results = Result::where("studentID", $studentID)
                ->whereIn("examID", $examinations)
                ->with("classSubject.subject")
                ->get();

            if ($results->isEmpty()) {
                \Illuminate\Support\Facades\Log::warning(
                    "sendResultSms: No results found for student {$studentID} in term {$term} / year {$year}",
                );
                return response()->json([
                    "success" => false,
                    "error" => "No results found for this term",
                ]);
            }

            $subjectData = [];
            foreach ($results as $res) {
                $sName = $res->classSubject->subject->subject_name ?? "N/A";
                if (!isset($subjectData[$sName])) {
                    $subjectData[$sName] = ["total" => 0, "count" => 0];
                }
                if ($res->marks !== null && $res->marks !== "") {
                    $subjectData[$sName]["total"] += (float) $res->marks;
                    $subjectData[$sName]["count"]++;
                }
            }

            foreach ($subjectData as $sName => $data) {
                $avgMarks =
                    $data["count"] > 0 ? $data["total"] / $data["count"] : 0;
                $totalSum += $avgMarks;
                $gradeRes = $this->getGradeFromDefinition($avgMarks, $classID);
                $sGrade = $gradeRes["grade"] ?? "-";
                $formattedResults[] =
                    "{$sName}-(" . round($avgMarks) . ")-({$sGrade})";
            }
            $resultsString = implode("\n", $formattedResults);
        } else {
            // Multi-subject mode (Exam level)
            // Check if this exam has CA defined
            $caDefinition = \App\Models\CaDefinition::where(
                "schoolID",
                $schoolID,
            )
                ->where("examID", $examID)
                ->first();

            $allExamIds = [$examID];
            if ($caDefinition && !empty($caDefinition->test_ids)) {
                $allExamIds = array_unique(
                    array_merge([$examID], $caDefinition->test_ids),
                );
            }

            $allResultsQuery = Result::where("studentID", $studentID)->whereIn(
                "examID",
                $allExamIds,
            );

            if (
                $week &&
                $week !== "all" &&
                (!$caDefinition || empty($caDefinition->test_ids))
            ) {
                $allResultsQuery->where("test_week", $week);
            }

            $results = $allResultsQuery->with("classSubject.subject")->get();

            if ($results->isEmpty()) {
                \Log::warning(
                    "sendResultSms: No results found for student {$studentID}. ExamID: {$examID}, Week: {$week}, UserType: {$userType}",
                );
                return response()->json([
                    "success" => false,
                    "error" => "No results found for this student",
                ]);
            }

            \Log::info(
                "sendResultSms: Found " .
                    $results->count() .
                    " subjects for student {$studentID}",
            );

            if ($caDefinition && !empty($caDefinition->test_ids)) {
                // Group by subject and average (CA support)
                $subjectData = [];
                foreach ($results as $res) {
                    $sName = $res->classSubject->subject->subject_name ?? "N/A";
                    if (!isset($subjectData[$sName])) {
                        $subjectData[$sName] = ["total" => 0, "count" => 0];
                    }
                    if ($res->marks !== null && $res->marks !== "") {
                        $subjectData[$sName]["total"] += (float) $res->marks;
                        $subjectData[$sName]["count"]++;
                    }
                }

                foreach ($subjectData as $sName => $data) {
                    $avgMarks =
                        $data["count"] > 0
                            ? $data["total"] / $data["count"]
                            : 0;
                    $totalSum += $avgMarks;
                    $gradeRes = $this->getGradeFromDefinition(
                        $avgMarks,
                        $classID,
                    );
                    $sGrade = $gradeRes["grade"] ?? "-";
                    $formattedResults[] =
                        "{$sName}-(" . round($avgMarks) . ")-({$sGrade})";
                }
            } else {
                // Standard mode
                foreach ($results as $res) {
                    $sName = $res->classSubject->subject->subject_name ?? "N/A";
                    $sMarks = $res->marks ?? 0;
                    $sGrade = $res->grade ?? "-";
                    $totalSum += (float) $sMarks;
                    $formattedResults[] =
                        "{$sName}-(" . round((float) $sMarks) . ")-({$sGrade})";
                }
            }
            $resultsString = implode("\n", $formattedResults);
        }

        // Get week range
        $weekRange = "";
        $actualExamID =
            $examID && $examID !== "1" && $examID !== "null" ? $examID : null;

        // If we have results, use the examID from the first result if the provided one was generic
        if (!$actualExamID && isset($results) && !$results->isEmpty()) {
            $actualExamID = $results->first()->examID;
        }

        if ($week && $actualExamID) {
            $paper = ExamPaper::where("examID", $actualExamID)
                ->where("test_week", $week)
                ->select("test_week_range", "test_date")
                ->first();

            if ($paper) {
                $weekRange = $paper->test_week_range;
                if (empty($weekRange) && $paper->test_date) {
                    $weekRange = date("d M Y", strtotime($paper->test_date));
                }
            }
        }

        if (empty($weekRange)) {
            $weekRange = $week;
        }

        $school = \App\Models\School::find($schoolID);
        $schoolName = $school ? $school->school_name : "ShuleXpert";

        $weekLabel = $week;
        if ($type === "report") {
            $weekLabel = "Term " . ucfirst($term) . " " . $year;
        } elseif (str_contains(strtolower($week), "week")) {
            $weekLabel = "wiki ya {$weekRange}";
        } elseif (str_contains(strtolower($week), "month")) {
            $weekLabel = "mwezi wa {$weekRange}";
        } elseif ($weekRange) {
            $weekLabel = $weekRange;
        }

        $division = $request->input("division", "");
        $position = $request->input("position", "");
        $totalStudentsCount = $request->input("totalStudentsCount", "");

        // Determine Exam Context Name
        $examNameContext =
            $examID && $actualExamID
                ? Examination::where("examID", $actualExamID)->value(
                    "exam_name",
                )
                : ($weekLabel ?:
                "MTIHANI");
        if ($type === "report") {
            $termLabel = $term === "first_term" ? "TERM 1" : "TERM 2";
            $examNameContext = "RIPOTI YA {$termLabel} {$year}";
        }

        $schoolType = $school ? $school->school_type : "Secondary";

        // Construct Premium SMS Format
        $msg = "";
        if (!($school && $school->senderID)) {
            $msg .= "{$schoolName}\n";
        }
        $msg .= "MZAZI WA {$studentName},\n";
        $msg .= "YAFUATAYO NI MATOKEO YA {$studentName} KATIKA {$examNameContext}.\n";

        $summary = "JUMLA: " . round($totalSum);
        if ($schoolType === "Secondary" && $division) {
            $summary .= " | DIV: {$division}";
        }
        if ($position && $totalStudentsCount) {
            $summary .= " | NAFASI: {$position}/{$totalStudentsCount}";
        }
        $msg .= $summary . "\n";

        $msg .= "------------------------\n";
        $msg .= "MASOMO YAFUATAYO:\n";
        $msg .= $resultsString;

        $message = $msg;
        \Log::info(
            "Preparing to send Result SMS to {$parentPhone} for student {$studentName}. Message Length: " .
                strlen($message),
        );

        try {
            $smsService = new SmsService();
            $result = $smsService->sendSms($parentPhone, $message);

            if ($result["success"]) {
                \Log::info(
                    "Result SMS sent successfully to {$parentPhone}. Student: {$studentName}.",
                );
                return response()->json(["success" => true]);
            } else {
                \Log::error(
                    "Result SMS failed for {$parentPhone} (Student: {$studentName}). Gateway Error: " .
                        ($result["message"] ?? "Unknown gateway error"),
                );
                \Log::error(
                    "Gateway full response: " . ($result["response"] ?? "N/A"),
                );
                return response()->json([
                    "success" => false,
                    "error" => $result["message"] ?? "Failed to send SMS",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error(
                "Result SMS exception for student {$studentName}: " .
                    $e->getMessage() .
                    "\n" .
                    $e->getTraceAsString(),
            );
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get exam results for students
     */
    private function getExamResults(
        $students,
        $term,
        $year,
        $schoolType,
        $examID = null,
        $weekFilter = null,
        $subjectFilter = null,
        $schoolID = null,
        $academicYearIDs = [],
    ) {
        $resultsData = [];
        if (!$schoolID) {
            $schoolID = Session::get("schoolID");
        }

        foreach ($students as $student) {
            $studentResults = [];
            $electedClassSubjectIds = $this->getElectedClassSubjectIdsForStudent(
                $student->studentID,
            );

            // Get examinations for this term and academic year (header)
            $examinationsQuery = Examination::where("schoolID", $schoolID);
            $this->applyExaminationAcademicYearFilter(
                $examinationsQuery,
                is_array($academicYearIDs) ? $academicYearIDs : [],
                $year,
            );

            if ($term) {
                $examinationsQuery->where("term", $term);
            }

            // Filter by specific exam if selected
            if ($examID) {
                if (is_array($examID)) {
                    $examinationsQuery->whereIn("examID", $examID);
                } else {
                    $examinationsQuery->where("examID", $examID);
                }
            }

            $examinations = $examinationsQuery->orderBy("start_date")->get();

            // Check if selected exam has CA definition
            $caDefinition = null;
            if ($examID) {
                $checkID = is_array($examID) ? $examID[0] : $examID;
                $caDefinition = CaDefinition::where("examID", $checkID)
                    ->where("schoolID", $schoolID)
                    ->first();
            }

            // If CA is defined and we are doing a combined view, aggregate ALL involved exams into one pass
            if ($caDefinition && is_array($examID) && count($examID) > 1) {
                $resultsQuery = Result::where(
                    "studentID",
                    $student->studentID,
                )->whereIn("examID", $examID);

                if ($subjectFilter) {
                    $resultsQuery->whereHas("classSubject", function ($q) use (
                        $subjectFilter,
                    ) {
                        $q->where("subjectID", $subjectFilter);
                    });
                }

                $results = $resultsQuery
                    ->with(["classSubject.subject", "examination"])
                    ->get();
                $results = $this->filterResultsForStudentElection(
                    $results,
                    $student->studentID,
                    $electedClassSubjectIds,
                );

                if (!$results->isEmpty()) {
                    $involvedCount = count($examID);
                    $aggregatedResults = $results
                        ->groupBy("class_subjectID")
                        ->map(function ($group) use ($involvedCount) {
                            $marksSum = $group->sum(function ($r) {
                                return $r->marks !== null && $r->marks !== ""
                                    ? (float) $r->marks
                                    : 0;
                            });
                            $avg =
                                $involvedCount > 0
                                    ? $marksSum / $involvedCount
                                    : 0;

                            $first = $group->first();
                            $first->marks = $avg;
                            $first->grade = null;
                            return $first;
                        });

                    // Tie the aggregated result to the specific requested Main Exam record
                    $mainExam = null;
                    if (is_array($examID) && count($examID) > 0) {
                        $mainExam = $examinations->firstWhere(
                            "examID",
                            $examID[0],
                        );
                    }
                    if (!$mainExam) {
                        $mainExam = $examinations->first();
                    }

                    $this->processResultsCollection(
                        $student,
                        $aggregatedResults,
                        $mainExam,
                        $schoolType,
                        $studentResults,
                        $weekFilter,
                        $subjectFilter,
                    );
                }
                if (!empty($studentResults)) {
                    $resultsData[$student->studentID] = $studentResults;
                }
                continue; // Move to next student
            }

            foreach ($examinations as $exam) {
                // If CA is defined for a single exam selection, aggregate tests
                if ($caDefinition) {
                    $allInvolvedExams = array_merge(
                        [$exam->examID],
                        $caDefinition->test_ids,
                    );
                    $results = Result::where("studentID", $student->studentID)
                        ->whereIn("examID", $allInvolvedExams)
                        ->with(["classSubject.subject", "examination"])
                        ->get();
                    $results = $this->filterResultsForStudentElection(
                        $results,
                        $student->studentID,
                        $electedClassSubjectIds,
                    );

                    if ($results->isEmpty()) {
                        continue;
                    }

                    $involvedCount = count($allInvolvedExams);
                    $results = $results
                        ->groupBy("class_subjectID")
                        ->map(function ($group) use ($involvedCount) {
                            $marksSum = $group->sum(function ($r) {
                                return $r->marks !== null && $r->marks !== ""
                                    ? (float) $r->marks
                                    : 0;
                            });
                            $avg =
                                $involvedCount > 0
                                    ? $marksSum / $involvedCount
                                    : 0;

                            $first = $group->first();
                            $first->marks = $avg;
                            $first->grade = null;
                            return $first;
                        });
                } else {
                    // Standard fetching
                    $resultsQuery = Result::where(
                        "studentID",
                        $student->studentID,
                    )->where("examID", $exam->examID);

                    if (
                        $exam->exam_name === "Weekly Test" &&
                        $weekFilter &&
                        $weekFilter !== "all"
                    ) {
                        $resultsQuery->where("test_week", $weekFilter);
                    }

                    if ($subjectFilter) {
                        $resultsQuery->whereHas("classSubject", function (
                            $q,
                        ) use ($subjectFilter) {
                            $q->where("subjectID", $subjectFilter);
                        });
                    }

                    $results = $resultsQuery
                        ->with(["classSubject.subject", "examination"])
                        ->get();
                    $results = $this->filterResultsForStudentElection(
                        $results,
                        $student->studentID,
                        $electedClassSubjectIds,
                    );
                }

                if (!$results->isEmpty()) {
                    $this->processResultsCollection(
                        $student,
                        $results,
                        $exam,
                        $schoolType,
                        $studentResults,
                        $weekFilter,
                        $subjectFilter,
                    );
                }
            }

            if (!empty($studentResults)) {
                $resultsData[$student->studentID] = $studentResults;
            }
        }

        return $resultsData;
    }

    /**
     * Helper to process a collection of results and add to studentResults
     */
    private function processResultsCollection(
        $student,
        $results,
        $exam,
        $schoolType,
        &$studentResults,
        $weekFilter,
        $subjectFilter,
    ) {
        $isWeeklyTest = $exam->exam_name === "Weekly Test";
        if ($isWeeklyTest && ($weekFilter === "all" || !$weekFilter)) {
            $results = $results
                ->groupBy("class_subjectID")
                ->map(function ($group) {
                    $marks = $group->filter(
                        fn($r) => $r->marks !== null && $r->marks !== "",
                    );
                    $avgMarks =
                        $marks->count() > 0 ? $marks->avg("marks") : null;
                    $first = $group->first();
                    $first->marks = $avgMarks;
                    $first->grade = null;
                    return $first;
                });
        }

        if ($isWeeklyTest && $weekFilter && $weekFilter !== "all") {
            foreach ($results as $result) {
                try {
                    $paper = ExamPaper::where("examID", $exam->examID)
                        ->where("class_subjectID", $result->class_subjectID)
                        ->where("test_week", $weekFilter)
                        ->first();
                    if ($paper) {
                        $allQuestions = \App\Models\ExamPaperQuestion::where(
                            "exam_paperID",
                            $paper->exam_paperID,
                        )
                            ->orderBy("question_number")
                            ->get();
                        $qMarks = ExamPaperQuestionMark::where(
                            "studentID",
                            $student->studentID,
                        )
                            ->whereIn(
                                "exam_paper_questionID",
                                $allQuestions->pluck("exam_paper_questionID"),
                            )
                            ->get()
                            ->keyBy("exam_paper_questionID");
                        $result->question_details = $allQuestions->map(
                            function ($q) use ($qMarks) {
                                $hasMark = $qMarks->has(
                                    $q->exam_paper_questionID,
                                );
                                return (object) [
                                    "question" => $q->question_number,
                                    "marks" => $hasMark
                                        ? $qMarks->get(
                                            $q->exam_paper_questionID,
                                        )->marks
                                        : "Incomplete",
                                    "max_marks" => $q->marks,
                                ];
                            },
                        );
                    }
                } catch (\Exception $e) {
                }
            }
        }

        $totalMarks = 0;
        $subjectCount = 0;
        $subjectsData = [];
        $className =
            $student->subclass->class->class_name ??
            ($student->oldSubclass->class->class_name ?? "");
        $classID =
            $student->subclass->class->classID ??
            ($student->oldSubclass->class->classID ?? null);

        $electedClassSubjectIds = $this->getElectedClassSubjectIdsForStudent(
            $student->studentID,
        );

        foreach ($results as $result) {
            if (
                !$this->shouldIncludeResultForStudent(
                    $student->studentID,
                    $result,
                    $electedClassSubjectIds,
                )
            ) {
                continue;
            }

            if ($result->marks !== null && $result->marks !== "") {
                $totalMarks += (float) $result->marks;
                $subjectCount++;
            } else {
                $subjectCount++;
            }
            $gradePoints = $this->calculateGradePoints(
                round((float) $result->marks, 2),
                $schoolType,
                $className,
                $classID,
            );
            $subjectsData[] = [
                "subject_name" =>
                    $result->classSubject->subject->subject_name ?? "N/A",
                "marks" => $result->marks ?? "incomplete",
                "grade" => $result->grade ?? $gradePoints["grade"],
                "points" => $gradePoints["points"],
                "question_marks" => $result->question_details ?? [],
            ];
        }

        $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
        $totalPoints = 0;
        $bestSevenTotalMarks = 0;
        $marksPointsArray = [];
        foreach ($subjectsData as $s) {
            if ($s["marks"] !== "incomplete" && $s["points"] !== null) {
                $marksPointsArray[] = [
                    "marks" => (float) $s["marks"],
                    "points" => $s["points"],
                    "grade" => $s["grade"] ?? null,
                ];
            }
        }

        $bestSevenGrades = [];
        $allSubjectGrades = $this->extractSubjectGradesFromPointEntries(
            $marksPointsArray,
        );
        if ($schoolType === "Secondary" && !empty($marksPointsArray)) {
            $divisionPick = $this->resolveSecondaryBestSubjectsForDivision(
                $marksPointsArray,
                $className,
                $schoolType,
            );
            $totalPoints = $divisionPick["total_points"];
            $bestSevenGrades = $divisionPick["grades"];
        }

        $gradeDivision =
            !$weekFilter || $weekFilter === "all"
                ? $this->calculateGradeDivision(
                    $totalMarks,
                    $averageMarks,
                    $subjectCount,
                    $schoolType,
                    $className,
                    $totalPoints,
                    $classID,
                    $bestSevenGrades,
                    $allSubjectGrades,
                )
                : ["grade" => null, "division" => null];

        $studentResults[] = [
            "exam" => $exam,
            "total_marks" => $totalMarks,
            "average_marks" => $averageMarks,
            "subject_count" => $subjectCount,
            "subjects" => $subjectsData,
            "grade" => $gradeDivision["grade"] ?? null,
            "division" => $gradeDivision["division"] ?? null,
            "total_points" => $totalPoints,
            "best_seven_total_marks" => $bestSevenTotalMarks,
        ];
    }

    /**
     * Get term report (average of all exams in term)
     */
    private function getTermReport(
        $students,
        $term,
        $year,
        $schoolType,
        $subjectFilter = null,
        $schoolID = null,
        $academicYearIDs = [],
    ) {
        $resultsData = [];
        if (!$schoolID) {
            $schoolID = Session::get("schoolID");
        }

        // Check if there's a custom report definition for this term
        $reportDefinition = TermReportDefinition::where("schoolID", $schoolID)
            ->where("year", $year)
            ->where("term", $term)
            ->first();

        // OPTIMIZATION: Query examinations by academic year (header)
        $examinationsQuery = Examination::where("schoolID", $schoolID)->where(
            "term",
            $term,
        );
        $this->applyExaminationAcademicYearFilter(
            $examinationsQuery,
            is_array($academicYearIDs) ? $academicYearIDs : [],
            $year,
        );

        // If definition exists and has exams, restrict to those exams
        if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
            $examinationsQuery->whereIn("examID", $reportDefinition->exam_ids);
        }

        $examinations = $examinationsQuery->orderBy("start_date")->get();

        // No longer filtering by ended status
        $endedExaminations = $examinations;

        if ($endedExaminations->isEmpty()) {
            return $resultsData;
        }

        /**
         * Prevent double-counting in term report:
         * If a selected exam already has CA definition that includes a selected test exam,
         * drop that test exam from term-report aggregation.
         */
        $selectedExamIDs = $endedExaminations->pluck("examID")->toArray();
        $caDefinitions = CaDefinition::where("schoolID", $schoolID)
            ->whereIn("examID", $selectedExamIDs)
            ->get();

        $excludedExamIDs = [];
        foreach ($caDefinitions as $caDefinition) {
            $testIDs = is_array($caDefinition->test_ids)
                ? $caDefinition->test_ids
                : [];
            foreach ($testIDs as $testID) {
                if (in_array($testID, $selectedExamIDs)) {
                    $excludedExamIDs[] = $testID;
                }
            }
        }

        $effectiveExaminations = $endedExaminations
            ->reject(function ($exam) use ($excludedExamIDs) {
                return in_array($exam->examID, $excludedExamIDs);
            })
            ->values();

        if ($effectiveExaminations->isEmpty()) {
            // Safety fallback
            $effectiveExaminations = $endedExaminations;
        }

        $effectiveExamIDs = $effectiveExaminations->pluck("examID")->toArray();
        $caDefinitionsByExam = CaDefinition::where("schoolID", $schoolID)
            ->whereIn("examID", $effectiveExamIDs)
            ->get()
            ->keyBy("examID");

        // Fetch all exams needed for aggregation (main exams + their CA tests)
        $examIDs = $effectiveExamIDs;
        foreach ($caDefinitionsByExam as $caDefinition) {
            $testIDs = is_array($caDefinition->test_ids)
                ? $caDefinition->test_ids
                : [];
            $examIDs = array_merge($examIDs, $testIDs);
        }
        $examIDs = array_values(array_unique($examIDs));

        // OPTIMIZATION: Get all student IDs
        $studentIDs = $students->pluck("studentID")->toArray();

        // Fetch all results in batch
        $allResultsQuery = Result::whereIn("studentID", $studentIDs)
            ->whereIn("examID", $examIDs)
            ->with(["classSubject.subject"]);

        if ($subjectFilter) {
            $allResultsQuery->whereHas("classSubject", function ($q) use (
                $subjectFilter,
            ) {
                $q->where("subjectID", $subjectFilter);
            });
        }

        $allResults = $allResultsQuery->get()->groupBy("studentID");

        $electorsByStudent = SubjectElector::whereIn("studentID", $studentIDs)
            ->get()
            ->groupBy("studentID")
            ->map(function ($rows) {
                return $rows
                    ->pluck("classSubjectID")
                    ->map(fn($id) => (int) $id)
                    ->all();
            })
            ->all();

        // Store results by studentID and examID for quick lookup
        $resultsByStudentAndExam = [];
        foreach ($allResults as $studentID => $studentResults) {
            $resultsByStudentAndExam[$studentID] = $studentResults->groupBy(
                "examID",
            );
        }

        foreach ($students as $student) {
            $studentResultsByExam =
                $resultsByStudentAndExam[$student->studentID] ?? collect();

            if ($studentResultsByExam->isEmpty()) {
                continue;
            }

            $electedClassSubjectIds =
                $electorsByStudent[$student->studentID] ?? [];

            // Get class name and classID once
            $className = "";
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? "";
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className = $student->oldSubclass->class->class_name ?? "";
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            $classNameLower = strtolower(
                preg_replace("/[\s\-]+/", "_", $className),
            );

            $allExamResults = [];
            $totalMarksAllExams = 0;
            $totalSubjectCount = 0;

            foreach ($effectiveExaminations as $exam) {
                $caDefinition = $caDefinitionsByExam->get($exam->examID);

                if ($caDefinition && !empty($caDefinition->test_ids)) {
                    $involvedExamIDs = array_values(
                        array_unique(
                            array_merge([$exam->examID], $caDefinition->test_ids),
                        ),
                    );
                    $involvedCount = count($involvedExamIDs);

                    $rawResults = collect();
                    foreach ($involvedExamIDs as $involvedExamID) {
                        $rawResults = $rawResults->merge(
                            $studentResultsByExam->get(
                                $involvedExamID,
                                collect(),
                            ),
                        );
                    }
                    $rawResults = $this->filterResultsForStudentElection(
                        $rawResults,
                        $student->studentID,
                        $electedClassSubjectIds,
                    );

                    $results = $rawResults
                        ->groupBy("class_subjectID")
                        ->map(function ($group) use ($involvedCount) {
                            $marksSum = $group->sum(function ($r) {
                                return $r->marks !== null && $r->marks !== ""
                                    ? (float) $r->marks
                                    : 0;
                            });
                            $avg =
                                $involvedCount > 0
                                    ? $marksSum / $involvedCount
                                    : 0;

                            $first = $group->first();
                            $first->marks = $avg;
                            $first->grade = null;
                            return $first;
                        });
                } else {
                    $results = $studentResultsByExam->get(
                        $exam->examID,
                        collect(),
                    );
                    $results = $this->filterResultsForStudentElection(
                        $results,
                        $student->studentID,
                        $electedClassSubjectIds,
                    );
                }

                if ($results->isEmpty()) {
                    continue;
                }

                $examTotalMarks = 0;
                $examSubjectCount = 0;

                foreach ($results as $result) {
                    if (
                        !$this->shouldIncludeResultForStudent(
                            $student->studentID,
                            $result,
                            $electedClassSubjectIds,
                        )
                    ) {
                        continue;
                    }

                    if ($result->marks !== null && $result->marks !== "") {
                        $marks = (float) $result->marks;
                        $examTotalMarks += $marks;
                        $examSubjectCount++;
                    }
                }

                if ($examSubjectCount > 0) {
                    $examAverage = $examTotalMarks / $examSubjectCount;

                    $examGrade = null;
                    if ($schoolType === "Secondary") {
                        $examGrade = $this->getSecondaryHardcodedGrade(
                            $examAverage,
                            $className,
                        )["grade"];
                    } elseif ($classID && $examAverage > 0) {
                        $gradeResult = $this->getGradeFromDefinition(
                            $examAverage,
                            $classID,
                            $schoolType,
                            $className,
                        );
                        $examGrade = $gradeResult["grade"];
                    }
                    if (!$examGrade) {
                        if ($examAverage >= 75) {
                            $examGrade = "A";
                        } elseif ($examAverage >= 65) {
                            $examGrade = "B";
                        } elseif ($examAverage >= 45) {
                            $examGrade = "C";
                        } elseif ($examAverage >= 30) {
                            $examGrade = "D";
                        } else {
                            $examGrade = "F";
                        }
                    }

                    $allExamResults[] = [
                        "exam" => $exam,
                        "total_marks" => $examTotalMarks,
                        "subject_count" => $examSubjectCount,
                        "average" => $examAverage,
                        "grade" => $examGrade,
                    ];
                    $totalMarksAllExams += $examTotalMarks;
                    $totalSubjectCount += $examSubjectCount;
                }
            }

            if (!empty($allExamResults)) {
                // Calculate overall average as average of exam averages (for report view)
                $examAveragesSum = 0;
                foreach ($allExamResults as $examResult) {
                    $examAveragesSum += $examResult["average"];
                }
                $overallAverage =
                    count($effectiveExaminations) > 0
                        ? $examAveragesSum / count($effectiveExaminations)
                        : 0;

                // NECTA division: one grade per subject (term average), then best 7 subjects
                $allStudentResults = collect();
                foreach ($examIDs as $examIdForDivision) {
                    $allStudentResults = $allStudentResults->merge(
                        $studentResultsByExam->get($examIdForDivision, collect()),
                    );
                }
                $allStudentResults = $this->filterResultsForStudentElection(
                    $allStudentResults,
                    $student->studentID,
                    $electedClassSubjectIds,
                );

                $subjectPointEntries = $this->buildTermReportSubjectPointEntries(
                    $allStudentResults,
                    $effectiveExaminations,
                    $caDefinitionsByExam,
                    $schoolType,
                    $className,
                    $student->studentID,
                    $electedClassSubjectIds,
                );

                $subjectSummaries = $this->buildTermReportSubjectSummaries(
                    $allStudentResults,
                    $effectiveExaminations,
                    $caDefinitionsByExam,
                    $schoolType,
                    $className,
                    $student->studentID,
                    $electedClassSubjectIds,
                    $classID,
                );

                $bestSevenGrades = [];
                $totalPoints = 0;
                $allSubjectGrades = [];
                if ($schoolType === "Secondary" && !empty($subjectPointEntries)) {
                    $allSubjectGrades = $this->extractSubjectGradesFromPointEntries(
                        $subjectPointEntries,
                    );
                    $divisionPick = $this->resolveSecondaryBestSubjectsForDivision(
                        $subjectPointEntries,
                        $className,
                        $schoolType,
                    );
                    $totalPoints = $divisionPick["total_points"];
                    $bestSevenGrades = $divisionPick["grades"];
                }

                if ($schoolType === "Secondary") {
                    $overallGrade = $this->getSecondaryHardcodedGrade(
                        $overallAverage,
                        $className,
                    )["grade"];
                } elseif ($classID && $overallAverage > 0) {
                    $gradeResult = $this->getGradeFromDefinition(
                        $overallAverage,
                        $classID,
                        $schoolType,
                        $className,
                    );
                    $overallGrade = $gradeResult["grade"];
                } else {
                    if ($overallAverage >= 75) {
                        $overallGrade = "A";
                    } elseif ($overallAverage >= 65) {
                        $overallGrade = "B";
                    } elseif ($overallAverage >= 45) {
                        $overallGrade = "C";
                    } elseif ($overallAverage >= 30) {
                        $overallGrade = "D";
                    } else {
                        $overallGrade = "F";
                    }
                }

                $gradeDivision = $this->calculateGradeDivision(
                    $totalMarksAllExams,
                    $overallAverage,
                    $totalSubjectCount,
                    $schoolType,
                    $className,
                    $totalPoints,
                    $classID,
                    $bestSevenGrades,
                    $allSubjectGrades,
                );

                // OPTIMIZATION: Skip position calculation here - it's very expensive and will be calculated in the view per class
                // Position will be calculated in the Blade template where it's actually needed

                $displayGrade =
                    $schoolType === "Secondary"
                        ? ($gradeDivision["division"] ?? $overallGrade)
                        : $overallGrade;

                $resultsData[$student->studentID] = [
                    "exams" => $allExamResults,
                    "total_marks" => $totalMarksAllExams,
                    "average_marks" => $overallAverage,
                    "subject_count" => $totalSubjectCount,
                    "exam_count" => count($allExamResults),
                    "grade" => $displayGrade,
                    "division" => $gradeDivision["division"] ?? null,
                    "position" => null, // Will be calculated in view per class
                    "total_points" => $totalPoints,
                    "subjects" => $subjectSummaries,
                ];
            }
        }

        return $resultsData;
    }

    /**
     * Secondary schools: fixed letter grades (do not use grade_definitions).
     * A 75–100, B 65–74, C 45–64, D 30–44, F 0–29
     */
    private function getSecondaryHardcodedGrade(
        $marks,
        ?string $className = null,
    ): array {
        if ($marks === null || $marks === "") {
            return ["grade" => null, "points" => null];
        }

        $marksNum = round((float) $marks, 2);

        if ($marksNum >= 75) {
            return ["grade" => "A", "points" => 1];
        }
        if ($marksNum >= 65) {
            return ["grade" => "B", "points" => 2];
        }
        if ($marksNum >= 45) {
            return ["grade" => "C", "points" => 3];
        }
        if ($marksNum >= 30) {
            return ["grade" => "D", "points" => 4];
        }

        return ["grade" => "F", "points" => 5];
    }

    /**
     * Marks for one term-report exam column (CA bundle = all parts must have marks).
     */
    private function resolveTermReportExamSlotMarks(
        array $data,
        $exam,
        $caDefinitionsByExam,
    ): ?float {
        $caDefinition = $caDefinitionsByExam->get($exam->examID);

        if ($caDefinition && !empty($caDefinition->test_ids)) {
            $involvedExamIDs = array_values(
                array_unique(
                    array_merge([$exam->examID], $caDefinition->test_ids),
                ),
            );
            $involvedCount = count($involvedExamIDs);
            if ($involvedCount === 0) {
                return null;
            }

            $involvedSum = 0;
            foreach ($involvedExamIDs as $involvedExamID) {
                if (
                    !isset($data["exams"][$involvedExamID]["marks"]) ||
                    $data["exams"][$involvedExamID]["marks"] === null ||
                    $data["exams"][$involvedExamID]["marks"] === ""
                ) {
                    return null;
                }
                $involvedSum += (float) $data["exams"][$involvedExamID]["marks"];
            }

            return $involvedSum / $involvedCount;
        }

        if (
            !isset($data["exams"][$exam->examID]["marks"]) ||
            $data["exams"][$exam->examID]["marks"] === null ||
            $data["exams"][$exam->examID]["marks"] === ""
        ) {
            return null;
        }

        return (float) $data["exams"][$exam->examID]["marks"];
    }

    /**
     * Term report subject average (kawaida):
     * - Jumla = alama zilizopo + 0 kwa kila mtihani incomplete (null)
     * - Wastani = jumla ÷ idadi ya mitihani kwenye term report (daima)
     *   (mf. TERMINAL 71, MIDTERM incomplete → (71+0)/2 = 35.5)
     * - Incomplete tu wakati mitihani YOTE hazina marks
     *
     * @return array{
     *   slot_marks: list<float>,
     *   is_incomplete: bool,
     *   average: float|null,
     *   exam_slot_count: int,
     *   marked_slot_count: int
     * }
     */
    private function collectTermReportSlotMarksForSubject(
        array $data,
        $effectiveExaminations,
        $caDefinitionsByExam,
    ): array {
        $examSlotCount = $effectiveExaminations->count();
        $slotMarksList = [];
        $sum = 0.0;
        $markedSlotCount = 0;

        foreach ($effectiveExaminations as $exam) {
            $slotMarks = $this->resolveTermReportExamSlotMarks(
                $data,
                $exam,
                $caDefinitionsByExam,
            );
            if ($slotMarks !== null) {
                $slotMarksList[] = $slotMarks;
                $sum += $slotMarks;
                $markedSlotCount++;
            }
            // Incomplete slot counts as 0 in the sum (still divides by total exam count)
        }

        $isIncomplete = $markedSlotCount === 0 || $examSlotCount === 0;

        $average = null;
        if (!$isIncomplete) {
            $average = $sum / $examSlotCount;
        }

        return [
            "slot_marks" => $slotMarksList,
            "is_incomplete" => $isIncomplete,
            "average" => $average,
            "exam_slot_count" => $examSlotCount,
            "marked_slot_count" => $markedSlotCount,
        ];
    }

    /**
     * One grade-point entry per subject from term averages (NECTA: division = sum of best 7 subjects).
     *
     * @return list<array{points: int, marks: float, grade: string}>
     */
    private function buildTermReportSubjectPointEntries(
        $results,
        $effectiveExaminations,
        $caDefinitionsByExam,
        string $schoolType,
        string $className,
        int $studentID,
        array $electedClassSubjectIds = [],
    ): array {
        if (
            strtolower(trim($schoolType)) !== "secondary" ||
            $results->isEmpty()
        ) {
            return [];
        }

        // Group by subject name (matches individual student report / getSubjectDetails)
        $subjectData = [];
        foreach ($results as $result) {
            if (
                !$this->shouldIncludeResultForStudent(
                    $studentID,
                    $result,
                    $electedClassSubjectIds,
                )
            ) {
                continue;
            }

            $subjectName =
                $result->classSubject->subject->subject_name ?? "N/A";

            if (!isset($subjectData[$subjectName])) {
                $subjectData[$subjectName] = ["exams" => []];
            }

            if ($result->marks !== null && $result->marks !== "") {
                $subjectData[$subjectName]["exams"][$result->examID] = [
                    "marks" => (float) $result->marks,
                ];
            }
        }

        $entries = [];
        foreach ($subjectData as $data) {
            $slotCollect = $this->collectTermReportSlotMarksForSubject(
                $data,
                $effectiveExaminations,
                $caDefinitionsByExam,
            );
            $slotMarksList = $slotCollect["slot_marks"];

            if ($slotCollect["is_incomplete"]) {
                continue;
            }

            $average = $slotCollect["average"];
            $gradeInfo = $this->getSecondaryHardcodedGrade($average, $className);
            if ($gradeInfo["grade"] === null) {
                continue;
            }

            $entries[] = [
                "points" => (int) ($gradeInfo["points"] ?? 5),
                "marks" => $average,
                "grade" => $gradeInfo["grade"],
            ];
        }

        return $entries;
    }

    /**
     * Term report subject rows for PDF / detailed lists (subject_name, marks, grade).
     *
     * @return list<array{subject_name: string, marks: float|null, grade: string}>
     */
    private function buildTermReportSubjectSummaries(
        $results,
        $effectiveExaminations,
        $caDefinitionsByExam,
        string $schoolType,
        string $className,
        int $studentID,
        array $electedClassSubjectIds = [],
        $classID = null,
    ): array {
        if ($results->isEmpty()) {
            return [];
        }

        $subjectData = [];
        foreach ($results as $result) {
            if (
                !$this->shouldIncludeResultForStudent(
                    $studentID,
                    $result,
                    $electedClassSubjectIds,
                )
            ) {
                continue;
            }

            $subjectName =
                $result->classSubject->subject->subject_name ?? "N/A";

            if (!isset($subjectData[$subjectName])) {
                $subjectData[$subjectName] = ["exams" => []];
            }

            if ($result->marks !== null && $result->marks !== "") {
                $subjectData[$subjectName]["exams"][$result->examID] = [
                    "marks" => (float) $result->marks,
                ];
            }
        }

        $summaries = [];
        foreach ($subjectData as $subjectName => $data) {
            $slotCollect = $this->collectTermReportSlotMarksForSubject(
                $data,
                $effectiveExaminations,
                $caDefinitionsByExam,
            );
            $slotMarksList = $slotCollect["slot_marks"];

            if ($slotCollect["is_incomplete"]) {
                $summaries[] = [
                    "subject_name" => $subjectName,
                    "marks" => null,
                    "grade" => "Incomplete",
                ];
                continue;
            }

            $average = $slotCollect["average"];

            if ($schoolType === "Secondary") {
                $grade = $this->getSecondaryHardcodedGrade(
                    $average,
                    $className,
                )["grade"];
            } elseif ($classID) {
                $grade = $this->getGradeFromDefinition(
                    $average,
                    $classID,
                    $schoolType,
                    $className,
                )["grade"];
            } else {
                $grade = null;
            }

            if (!$grade) {
                if ($average >= 75) {
                    $grade = "A";
                } elseif ($average >= 65) {
                    $grade = "B";
                } elseif ($average >= 45) {
                    $grade = "C";
                } elseif ($average >= 30) {
                    $grade = "D";
                } elseif ($schoolType === "Primary" && $average >= 20) {
                    $grade = "E";
                } else {
                    $grade = "F";
                }
            }

            $summaries[] = [
                "subject_name" => $subjectName,
                "marks" => round($average, 1),
                "grade" => $grade,
            ];
        }

        usort($summaries, function ($a, $b) {
            return strcasecmp($a["subject_name"], $b["subject_name"]);
        });

        return $summaries;
    }

    /**
     * @param  list<array{points: int, marks: float, grade?: string|null}>  $subjectPoints
     * @return list<string>
     */
    private function extractSubjectGradesFromPointEntries(array $subjectPoints): array
    {
        $grades = [];
        foreach ($subjectPoints as $entry) {
            $grade = strtoupper(trim((string) ($entry["grade"] ?? "")));
            if ($grade !== "") {
                $grades[] = $grade;
            }
        }

        return $grades;
    }

    /**
     * NECTA secondary: pick best subjects by points (O-Level 7, A-Level 3).
     *
     * @param  list<array{points: int, marks: float, grade?: string|null}>  $subjectPoints
     * @return array{total_points: int, grades: list<string>, counted_subjects: int}
     */
    private function resolveSecondaryBestSubjectsForDivision(
        array $subjectPoints,
        string $className,
        string $schoolType,
    ): array {
        $empty = [
            "total_points" => 0,
            "grades" => [],
            "counted_subjects" => 0,
        ];

        if (
            empty($subjectPoints) ||
            strtolower(trim($schoolType)) !== "secondary"
        ) {
            return $empty;
        }

        $classNameLower = strtolower(
            preg_replace("/[\s\-]+/", "_", $className),
        );

        if (
            in_array($classNameLower, [
                "form_one",
                "form_two",
                "form_three",
                "form_four",
                "form_1",
                "form_2",
                "form_3",
                "form_4",
            ]) ||
            preg_match(
                "/form\s*(one|two|three|four|1|2|3|4)/i",
                $className,
            )
        ) {
            usort($subjectPoints, function ($a, $b) {
                if ($a["points"] != $b["points"]) {
                    return $a["points"] <=> $b["points"];
                }
                return $b["marks"] <=> $a["marks"];
            });

            $bestSubjects = array_slice($subjectPoints, 0, min(7, count($subjectPoints)));
            $totalPoints = 0;
            foreach ($bestSubjects as $subject) {
                $totalPoints += (int) $subject["points"];
            }

            if (count($bestSubjects) < 7) {
                $totalPoints += (7 - count($bestSubjects)) * 5;
            }

            $grades = array_values(
                array_filter(
                    array_map(
                        fn($s) => strtoupper(trim((string) ($s["grade"] ?? ""))),
                        $bestSubjects,
                    ),
                    fn($g) => $g !== "",
                ),
            );

            return [
                "total_points" => $totalPoints,
                "grades" => $grades,
                "counted_subjects" => count($bestSubjects),
            ];
        }

        if (
            in_array($classNameLower, [
                "form_five",
                "form_six",
                "form_5",
                "form_6",
            ]) ||
            preg_match("/form\s*(five|six|5|6)/i", $className)
        ) {
            usort($subjectPoints, function ($a, $b) {
                if ($a["points"] != $b["points"]) {
                    return $b["points"] <=> $a["points"];
                }
                return $b["marks"] <=> $a["marks"];
            });

            $bestSubjects = array_slice($subjectPoints, 0, min(3, count($subjectPoints)));
            $totalPoints = 0;
            foreach ($bestSubjects as $subject) {
                $totalPoints += (int) $subject["points"];
            }

            $grades = array_values(
                array_filter(
                    array_map(
                        fn($s) => strtoupper(trim((string) ($s["grade"] ?? ""))),
                        $bestSubjects,
                    ),
                    fn($g) => $g !== "",
                ),
            );

            return [
                "total_points" => $totalPoints,
                "grades" => $grades,
                "counted_subjects" => count($bestSubjects),
            ];
        }

        $totalPoints = 0;
        $grades = [];
        foreach ($subjectPoints as $subject) {
            $totalPoints += (int) $subject["points"];
            if (!empty($subject["grade"])) {
                $grades[] = strtoupper(trim((string) $subject["grade"]));
            }
        }

        return [
            "total_points" => $totalPoints,
            "grades" => $grades,
            "counted_subjects" => count($subjectPoints),
        ];
    }

    /**
     * NECTA O-Level Division IV: 26–33 points and at least 2×D or 1×C in the best seven.
     */
    private function meetsOLevelDivisionIVGradeCriteria(array $bestSevenGrades): bool
    {
        if (empty($bestSevenGrades)) {
            return true;
        }

        $cCount = 0;
        $dCount = 0;
        foreach ($bestSevenGrades as $grade) {
            $grade = strtoupper(trim((string) $grade));
            if ($grade === "C") {
                $cCount++;
            }
            if ($grade === "D") {
                $dCount++;
            }
        }

        return $cCount >= 1 || $dCount >= 2;
    }

    /**
     * NECTA: at least five subjects with grade D or better to earn a division.
     */
    private function countOLevelPassingSubjects(array $grades): int
    {
        $pass = 0;
        foreach ($grades as $grade) {
            if (in_array(strtoupper(trim((string) $grade)), ["A", "B", "C", "D"], true)) {
                $pass++;
            }
        }

        return $pass;
    }

    /**
     * Get grade from grade_definitions table based on classID and marks
     */
    private function getGradeFromDefinition(
        $marks,
        $classID,
        $schoolType = null,
        $className = null,
    ) {
        if ($marks === null || $marks === "") {
            return ["grade" => null, "points" => null];
        }

        if ($schoolType === null && $classID) {
            $class = ClassModel::find($classID);
            $className = $className ?? ($class->class_name ?? "");
            if ($class && $class->schoolID) {
                $schoolType = School::find($class->schoolID)->school_type ?? null;
            }
        }

        if (strtolower(trim((string) ($schoolType ?? ""))) === "secondary") {
            return $this->getSecondaryHardcodedGrade($marks, $className);
        }

        if (!$classID) {
            return ["grade" => null, "points" => null];
        }

        $marksNum = round((float) $marks, 2);

        // Get grade definition from database (Primary / non-secondary)
        $gradeDefinition = GradeDefinition::where("classID", $classID)
            ->where("first", "<=", $marksNum)
            ->where("last", ">=", $marksNum)
            ->first();

        if (!$gradeDefinition) {
            return ["grade" => null, "points" => null];
        }

        // Calculate points based on grade (for backward compatibility)
        $grade = $gradeDefinition->grade;
        $points = null;

        // Points calculation for O-Level and A-Level (maintain existing logic)
        $className = ClassModel::find($classID)->class_name ?? "";
        $classNameLower = strtolower(
            preg_replace("/[\s\-]+/", "_", $className),
        );

        if (
            in_array($classNameLower, [
                "form_one",
                "form_two",
                "form_three",
                "form_four",
                "form_1",
                "form_2",
                "form_3",
                "form_4",
            ]) ||
            preg_match("/form\s*(one|two|three|four|1|2|3|4)/i", $className)
        ) {
            // O-Level points
            $pointsMap = ["A" => 1, "B" => 2, "C" => 3, "D" => 4, "F" => 5];
            $points = $pointsMap[$grade] ?? 5;
        } elseif (
            in_array($classNameLower, [
                "form_five",
                "form_six",
                "form_5",
                "form_6",
            ]) ||
            preg_match("/form\s*(five|six|5|6)/i", $className)
        ) {
            // A-Level points
            $pointsMap = [
                "A" => 5,
                "B" => 4,
                "C" => 3,
                "D" => 2,
                "E" => 1,
                "S" => 0.5,
                "F" => 0,
            ];
            $points = $pointsMap[$grade] ?? 0;
        }

        return ["grade" => $grade, "points" => $points];
    }

    /**
     * Calculate grade points for a single subject
     * Now uses grade_definitions table based on student's class
     */
    private function calculateGradePoints(
        $marks,
        $schoolType,
        $className,
        $classID = null,
    ) {
        if ($marks === null || $marks === "") {
            return ["grade" => null, "points" => null];
        }

        if (strtolower(trim((string) ($schoolType ?? ""))) === "secondary") {
            return $this->getSecondaryHardcodedGrade($marks, $className);
        }

        // Primary: use grade_definitions when class is known
        if ($classID) {
            return $this->getGradeFromDefinition(
                $marks,
                $classID,
                $schoolType,
                $className,
            );
        }

        // Fallback to old logic if classID not provided (for backward compatibility)
        $marksNum = (float) $marks;
        $classNameLower = strtolower(
            preg_replace("/[\s\-]+/", "_", $className),
        );

        if ($schoolType === "Secondary") {
            if (
                in_array($classNameLower, [
                    "form_one",
                    "form_two",
                    "form_three",
                    "form_four",
                    "form_1",
                    "form_2",
                    "form_3",
                    "form_4",
                ]) ||
                preg_match("/form\s*(one|two|three|four|1|2|3|4)/i", $className)
            ) {
                // O-Level Grading System (Secondary only)
                if ($marksNum >= 75) {
                    return ["grade" => "A", "points" => 1];
                } elseif ($marksNum >= 65) {
                    return ["grade" => "B", "points" => 2];
                } elseif ($marksNum >= 45) {
                    return ["grade" => "C", "points" => 3];
                } elseif ($marksNum >= 30) {
                    return ["grade" => "D", "points" => 4];
                } else {
                    return ["grade" => "F", "points" => 5];
                }
            } elseif (
                in_array($classNameLower, [
                    "form_five",
                    "form_six",
                    "form_5",
                    "form_6",
                ]) ||
                preg_match("/form\s*(five|six|5|6)/i", $className)
            ) {
                // A-Level Grading System (Secondary only)
                if ($marksNum >= 75) {
                    return ["grade" => "A", "points" => 5];
                } elseif ($marksNum >= 70) {
                    return ["grade" => "B", "points" => 4];
                } elseif ($marksNum >= 60) {
                    return ["grade" => "C", "points" => 3];
                } elseif ($marksNum >= 50) {
                    return ["grade" => "D", "points" => 2];
                } elseif ($marksNum >= 40) {
                    return ["grade" => "E", "points" => 1];
                } elseif ($marksNum >= 35) {
                    return ["grade" => "S", "points" => 0.5];
                } else {
                    return ["grade" => "F", "points" => 0];
                }
            }
        }

        // Primary or default - no points calculation
        return ["grade" => null, "points" => null];
    }

    /**
     * Sum best-subject points for Secondary (O-Level best 7, A-Level best 3).
     */
    private function computeTotalPointsFromSubjectEntries(
        array $subjectPoints,
        string $className,
        string $schoolType = "Secondary",
    ): int {
        return $this->resolveSecondaryBestSubjectsForDivision(
            $subjectPoints,
            $className,
            $schoolType,
        )["total_points"];
    }

    /**
     * Calculate grade or division with points
     * Now uses grade_definitions table for average marks grade
     */
    private function calculateGradeDivision(
        $totalMarks,
        $averageMarks,
        $subjectCount,
        $schoolType,
        $className,
        $totalPoints = 0,
        $classID = null,
        array $bestSevenGrades = [],
        array $allSubjectGrades = [],
    ) {
        $classNameLower = strtolower(
            preg_replace("/[\s\-]+/", "_", $className),
        );

        if (
            $schoolType === "Secondary" &&
            (in_array($classNameLower, [
                "form_one",
                "form_two",
                "form_three",
                "form_four",
                "form_1",
                "form_2",
                "form_3",
                "form_4",
            ]) ||
                preg_match(
                    "/form\s*(one|two|three|four|1|2|3|4)/i",
                    $className,
                ))
        ) {
            // NECTA O-Level: sum of points from best 7 subjects (A=1 … F=5)
            if ($totalPoints >= 7 && $totalPoints <= 17) {
                return ["grade" => null, "division" => "I." . $totalPoints];
            }
            if ($totalPoints >= 18 && $totalPoints <= 21) {
                return ["grade" => null, "division" => "II." . $totalPoints];
            }
            if ($totalPoints >= 22 && $totalPoints <= 25) {
                return ["grade" => null, "division" => "III." . $totalPoints];
            }
            if ($totalPoints >= 26 && $totalPoints <= 33) {
                return ["grade" => null, "division" => "IV." . $totalPoints];
            }

            return ["grade" => null, "division" => "0." . $totalPoints];
        } elseif (
            $schoolType === "Secondary" &&
            (in_array($classNameLower, [
                "form_five",
                "form_six",
                "form_5",
                "form_6",
            ]) ||
                preg_match("/form\s*(five|six|5|6)/i", $className))
        ) {
            // A-Level: Calculate division based on total points (Best 3 principal subjects)
            // Points mapping: A=5, B=4, C=3, D=2, E=1, S=0.5, F=0

            // Standard NECTA A-Level Thresholds (A=5 scale):
            // Division I: 13 - 15
            // Division II: 10 - 12
            // Division III: 7 - 9
            // Division IV: 3 - 6
            // Division 0: 0 - 2

            if ($totalPoints >= 13 && $totalPoints <= 15) {
                return ["grade" => null, "division" => "I." . $totalPoints];
            } elseif ($totalPoints >= 10 && $totalPoints <= 12) {
                return ["grade" => null, "division" => "II." . $totalPoints];
            } elseif ($totalPoints >= 7 && $totalPoints <= 9) {
                return ["grade" => null, "division" => "III." . $totalPoints];
            } elseif ($totalPoints >= 3 && $totalPoints <= 6) {
                return ["grade" => null, "division" => "IV." . $totalPoints];
            } else {
                return ["grade" => null, "division" => "0." . $totalPoints];
            }
        } else {
            // Primary or Secondary without division: Calculate grade from grade_definitions
            if ($classID && $averageMarks !== null && $averageMarks !== "") {
                $gradeResult = $this->getGradeFromDefinition(
                    $averageMarks,
                    $classID,
                );
                return ["grade" => $gradeResult["grade"], "division" => null];
            }

            // Fallback to old logic if classID not provided
            if ($averageMarks >= 75) {
                return ["grade" => "A", "division" => null];
            } elseif ($averageMarks >= 65) {
                return ["grade" => "B", "division" => null];
            } elseif ($averageMarks >= 45) {
                return ["grade" => "C", "division" => null];
            } elseif ($averageMarks >= 30) {
                return ["grade" => "D", "division" => null];
            } else {
                return ["grade" => "F", "division" => null];
            }
        }
    }

    /**
     * Calculate student position in main class
     */
    private function calculatePosition(
        $student,
        $term,
        $year,
        $studentAverage,
        $schoolType,
    ) {
        // Get main class (class, not subclass) - handle both current and old subclass
        $mainClassID = null;
        if ($student->subclass) {
            $mainClassID = $student->subclass->classID ?? null;
        } elseif ($student->oldSubclass) {
            $mainClassID = $student->oldSubclass->classID ?? null;
        }

        if (!$mainClassID) {
            return null;
        }

        // Get all students in the same main class
        $classStudents = Student::whereHas("subclass", function ($query) use (
            $mainClassID,
        ) {
            $query->where("classID", $mainClassID);
        })
            ->where("schoolID", Session::get("schoolID"))
            ->where("status", "Active")
            ->get();

        // Calculate averages for all students in the class
        $studentAverages = [];

        $userType = Session::get("user_type");
        $hasBroadResultPermission = $this->hasPermission("view_results");
        $bypassRestrictions =
            $userType === "Admin" || $hasBroadResultPermission;

        foreach ($classStudents as $classStudent) {
            $examinationsQuery = Examination::where(
                "schoolID",
                Session::get("schoolID"),
            )->where("year", $year);

            if (!$bypassRestrictions) {
                $examinationsQuery->where("approval_status", "Approved");
            }

            if ($term) {
                $examinationsQuery->where("term", $term);
            }

            $examinations = $examinationsQuery->get();

            $totalMarks = 0;
            $totalSubjects = 0;

            foreach ($examinations as $exam) {
                // Get results with marks
                $resultsQuery = Result::where(
                    "studentID",
                    $classStudent->studentID,
                )
                    ->where("examID", $exam->examID)
                    ->whereNotNull("marks");

                if (!$bypassRestrictions) {
                    $resultsQuery->where("status", "allowed");
                }

                $results = $resultsQuery->get();

                foreach ($results as $result) {
                    if ($result->marks !== null && $result->marks !== "") {
                        $totalMarks += (float) $result->marks;
                        $totalSubjects++;
                    }
                }
            }

            if ($totalSubjects > 0) {
                $avg = $totalMarks / $totalSubjects;
                $studentAverages[] = [
                    "studentID" => $classStudent->studentID,
                    "average" => $avg,
                ];
            }
        }

        // Sort by average (descending)
        usort($studentAverages, function ($a, $b) {
            return $b["average"] <=> $a["average"];
        });

        // Find position
        $position = null;
        foreach ($studentAverages as $index => $data) {
            if ($data["studentID"] == $student->studentID) {
                $position = $index + 1;
                break;
            }
        }

        return $position;
    }

    /**
     * Download results as PDF
     */
    public function downloadPdf(Request $request)
    {
        // Increase execution time and memory for large PDF generation
        set_time_limit(0); // Unlimited execution time
        ini_set("memory_limit", "2048M"); // 2GB memory for 7,000+ students

        $userType = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        // Allow any logged-in user with a valid school session to download PDF
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Access denied");
        }

        $option = $request->get("option", "all"); // single, all, class, subclass
        $studentID = $request->get("studentID", null);
        $classID = $request->get("classID", null);
        $subclassID = $request->get("subclassID", null);

        // Get filter parameters from request
        $termFilter = $request->get("term", "");
        $yearFilter = $request->get("year", date("Y"));
        $typeFilter = $request->get("type", "exam");
        $statusFilter = $request->get("status", "active");
        $examFilter = $request->get("examID", "");

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : "Secondary";

        // Get students based on option
        $students = collect();
        if ($option === "single" && $studentID) {
            $students = Student::where("studentID", $studentID)
                ->where("schoolID", $schoolID)
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "subclass.teacher",
                    "parent",
                    "oldSubclass.class",
                    "oldSubclass.teacher",
                ])
                ->get();
        } elseif ($option === "class" && $classID) {
            $students = Student::where("schoolID", $schoolID)
                ->where(function ($query) use ($classID) {
                    $query
                        ->whereHas("subclass", function ($q) use ($classID) {
                            $q->where("classID", $classID);
                        })
                        ->orWhereHas("oldSubclass", function ($q) use (
                            $classID,
                        ) {
                            $q->where("classID", $classID);
                        });
                })
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "subclass.teacher",
                    "parent",
                    "oldSubclass.class",
                    "oldSubclass.teacher",
                ])
                ->orderBy("first_name")
                ->get();
        } elseif ($option === "subclass" && $subclassID) {
            $students = Student::where("schoolID", $schoolID)
                ->where("subclassID", $subclassID)
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "subclass.teacher",
                    "parent",
                    "oldSubclass.class",
                    "oldSubclass.teacher",
                ])
                ->orderBy("first_name")
                ->get();
        } else {
            // All students — respect optional class/subclass/gender filters
            $classFilterID = $request->get("classID", "");
            $subclassFilterID = $request->get("subclassID", "");
            $genderFilter = $request->get("gender", "");

            $studentQuery = Student::where("schoolID", $schoolID);

            if ($statusFilter === "active") {
                $studentQuery->where("status", "Active");
            } elseif ($statusFilter === "history") {
                $studentQuery->whereNotNull("old_subclassID");
            }

            // Filter by subclass if provided
            if ($subclassFilterID) {
                $studentQuery->where("subclassID", $subclassFilterID);
                // Filter by class if provided (no subclass)
            } elseif ($classFilterID) {
                $studentQuery->where(function ($q) use ($classFilterID) {
                    $q->whereHas("subclass", function ($sq) use (
                        $classFilterID,
                    ) {
                        $sq->where("classID", $classFilterID);
                    })->orWhereHas("oldSubclass", function ($sq) use (
                        $classFilterID,
                    ) {
                        $sq->where("classID", $classFilterID);
                    });
                });
            }

            // Filter by gender if provided
            if ($genderFilter) {
                $studentQuery->where("gender", $genderFilter);
            }

            $students = $studentQuery
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "subclass.teacher",
                    "parent",
                    "oldSubclass.class",
                    "oldSubclass.teacher",
                ])
                ->orderBy("first_name")
                ->get();
        }

        // Get results data
        $resultsData = [];
        if ($termFilter && $yearFilter) {
            if ($typeFilter === "exam") {
                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if ($examFilter && !is_array($examFilter)) {
                    $caDef = CaDefinition::where("examID", $examFilter)
                        ->where("schoolID", $schoolID)
                        ->first();
                    if ($caDef && !empty($caDef->test_ids)) {
                        $caExamIDs = array_merge(
                            [$examFilter],
                            $caDef->test_ids,
                        );
                    }
                }
                $resultsData = $this->getExamResults(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                    $caExamIDs,
                );
            } else {
                $resultsData = $this->getTermReport(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                );
            }
        }

        // Filter students to only those with results
        $students = $students->filter(function ($student) use ($resultsData) {
            return isset($resultsData[$student->studentID]);
        });

        // Fetch detailed data for single student (CAs and Exam breakdowns)
        $detailedSingleData = null;
        $detailedBulkData = [];
        if ($option === "single" && count($students) > 0) {
            $reqClone = $request->duplicate();
            $reqClone->merge([
                "getSubjectDetails" => true,
                "studentID" => $studentID,
            ]);
            $res = $this->index($reqClone);
            if ($res instanceof \Illuminate\Http\JsonResponse) {
                $detailedSingleData = json_decode($res->content(), true);
            }
        } elseif ($option === "bulk_single" && count($students) > 0) {
            foreach ($students as $student) {
                $reqClone = $request->duplicate();
                $reqClone->merge([
                    "getSubjectDetails" => true,
                    "studentID" => $student->studentID,
                ]);
                $res = $this->index($reqClone);
                if ($res instanceof \Illuminate\Http\JsonResponse) {
                    $detailedBulkData[$student->studentID] = json_decode(
                        $res->content(),
                        true,
                    );
                }
            }
        }

        // Build title based on filters
        $title = "";
        if ($typeFilter === "exam") {
            $title = "Exam Results";
            if ($examFilter) {
                $exam = Examination::find($examFilter);
                if ($exam) {
                    $title = $exam->exam_name;
                }
            }
        } else {
            $title = "Term Report";
        }

        if ($termFilter) {
            $title .= " - " . ucfirst(str_replace("_", " ", $termFilter));
        }
        $title .= " - " . $yearFilter;

        // Prepare data for PDF
        $data = [
            "school" => $school,
            "schoolType" => $schoolType,
            "students" => $students,
            "resultsData" => $resultsData,
            "detailedSingleData" => $detailedSingleData,
            "detailedBulkData" => $detailedBulkData,
            "filters" => [
                "term" => $termFilter,
                "year" => $yearFilter,
                "type" => $typeFilter,
                "status" => $statusFilter,
                "examID" => $examFilter,
                "class" => $request->get("class", ""),
                "subclass" => $request->get("subclass", ""),
                "grade" => $request->get("grade", ""),
                "gender" => $request->get("gender", ""),
            ],
            "option" => $option,
            "title" => $title,
            "characterAssessments" => $this->getLatestCharacterAssessments(
                $students,
                $termFilter,
                $yearFilter,
                $schoolID,
            ),
        ];

        // Generate PDF
        $pdf = PDF::loadView("Admin.pdf.results", $data);
        CenturyGothicPdfFont::ensureRegistered($pdf->getDomPDF());
        $pdf->setPaper("A4", "portrait");

        // Generate filename
        $filename = "Results_";
        if ($option === "single" && $students->count() > 0) {
            $student = $students->first();
            $filename .= $student->first_name . "_" . $student->last_name . "_";
        } elseif ($option === "class" && $classID) {
            $class = ClassModel::find($classID);
            $filename .=
                ($class ? str_replace(" ", "_", $class->class_name) : "Class") .
                "_";
        } elseif ($option === "subclass" && $subclassID) {
            $subclass = Subclass::find($subclassID);
            $filename .=
                ($subclass
                    ? str_replace(" ", "_", $subclass->subclass_name)
                    : "Subclass") . "_";
        } else {
            $filename .= "All_Students_";
        }
        $filename .= ($typeFilter === "exam" ? "Exam" : "Term_Report") . "_";
        $filename .=
            ($termFilter ? str_replace("_", "", $termFilter) . "_" : "") .
            $yearFilter .
            ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Exam numbers per class — same sequence as attendance template / add-results.
     */
    private function buildExamNumberMapForClass(int $classID, ?School $school): array
    {
        $examCenterNo = !empty($school?->exam_center_number)
            ? $school->exam_center_number
            : "SET-CENTER-NO";

        $allStudentsInClass = Student::join(
            "subclasses",
            "students.subclassID",
            "=",
            "subclasses.subclassID",
        )
            ->where("subclasses.classID", $classID)
            ->where("students.status", "Active")
            ->select("students.studentID")
            ->orderByRaw("CASE 
                WHEN students.gender LIKE 'F%' OR students.gender LIKE 'K%' THEN 1 
                ELSE 2 
            END ASC")
            ->orderBy("students.first_name", "asc")
            ->get();

        $map = [];
        foreach ($allStudentsInClass as $index => $student) {
            $sequence = str_pad($index + 1, 4, "0", STR_PAD_LEFT);
            $map[$student->studentID] = "{$examCenterNo}/{$sequence}";
        }

        return $map;
    }

    private function genderShort(?string $gender): string
    {
        $g = strtolower(trim((string) $gender));
        if (
            str_starts_with($g, "f") ||
            str_starts_with($g, "k") ||
            $g === "female"
        ) {
            return "F";
        }

        return "M";
    }

    private function parseDivisionParts(?string $division, ?int $fallbackPoints = null): array
    {
        if (!$division) {
            return ["roman" => "", "points" => $fallbackPoints ?? ""];
        }

        if (preg_match("/^([0IVX]+)\.(\d+)/", $division, $m)) {
            return ["roman" => $m[1], "points" => (int) $m[2]];
        }

        if (preg_match("/^([0IVX]+)$/", $division, $m)) {
            return ["roman" => $m[1], "points" => $fallbackPoints ?? ""];
        }

        return ["roman" => $division, "points" => $fallbackPoints ?? ""];
    }

    /**
     * Normalize exam vs term-report payload into one row structure.
     */
    private function resolveStudentResultRow(
        $student,
        array $resultsData,
        string $typeFilter,
        $examFilter,
    ): ?array {
        if (!isset($resultsData[$student->studentID])) {
            return null;
        }

        $payload = $resultsData[$student->studentID];

        if ($typeFilter === "report") {
            return [
                "subjects" => $payload["subjects"] ?? [],
                "average_marks" => $payload["average_marks"] ?? 0,
                "total_marks" => $payload["total_marks"] ?? 0,
                "grade" => $payload["grade"] ?? null,
                "division" => $payload["division"] ?? null,
                "total_points" => $payload["total_points"] ?? null,
                "best_seven_total_marks" => 0,
            ];
        }

        $entries = is_array($payload) ? $payload : [];
        $chosen = null;
        foreach ($entries as $examResult) {
            if (
                $examFilter &&
                isset($examResult["exam"]->examID) &&
                (string) $examResult["exam"]->examID === (string) $examFilter
            ) {
                $chosen = $examResult;
                break;
            }
        }
        if (!$chosen && !empty($entries)) {
            $chosen = $entries[0];
        }

        if (!$chosen) {
            return null;
        }

        return [
            "subjects" => $chosen["subjects"] ?? [],
            "average_marks" => $chosen["average_marks"] ?? 0,
            "total_marks" => $chosen["total_marks"] ?? 0,
            "grade" => $chosen["grade"] ?? null,
            "division" => $chosen["division"] ?? null,
            "total_points" => $chosen["total_points"] ?? null,
            "best_seven_total_marks" => $chosen["best_seven_total_marks"] ?? 0,
        ];
    }

    private function buildBoardTitleLine(
        string $typeFilter,
        $examFilter,
        string $termFilter,
        $yearFilter,
    ): string {
        if ($typeFilter === "exam" && $examFilter) {
            $exam = Examination::find($examFilter);
            if ($exam) {
                $monthYear = $exam->start_date
                    ? strtoupper($exam->start_date->format("F, Y"))
                    : strtoupper(str_replace("_", " ", $termFilter)) .
                        ", " .
                        $yearFilter;

                return strtoupper($exam->exam_name) .
                    " RESULTS " .
                    $monthYear .
                    ".";
            }
        }

        $termLabel = $termFilter
            ? strtoupper(str_replace("_", " ", $termFilter))
            : "TERM";

        return $termLabel . " REPORT RESULTS " . $yearFilter . ".";
    }

    private function resolveSubjectGradeLetter(
        array $subj,
        string $schoolType,
        string $className,
        $classID = null,
    ): ?string {
        $marks = $subj["marks"] ?? null;
        if ($marks === null || $marks === "" || $marks === "incomplete") {
            return null;
        }

        $grade = strtoupper(trim((string) ($subj["grade"] ?? "")));
        if ($grade === "" || $grade === "INCOMPLETE") {
            $avg = (float) $marks;
            if ($schoolType === "Secondary") {
                $grade =
                    $this->getSecondaryHardcodedGrade($avg, $className)[
                        "grade"
                    ] ?? "";
            } elseif ($classID) {
                $grade =
                    $this->getGradeFromDefinition(
                        $avg,
                        $classID,
                        $schoolType,
                        $className,
                    )["grade"] ?? "";
            } else {
                if ($avg >= 75) {
                    $grade = "A";
                } elseif ($avg >= 65) {
                    $grade = "B";
                } elseif ($avg >= 45) {
                    $grade = "C";
                } elseif ($avg >= 30) {
                    $grade = "D";
                } else {
                    $grade =
                        $schoolType === "Primary" && $avg >= 20 ? "E" : "F";
                }
            }
        }

        $grade = strtoupper(trim($grade));

        return $grade !== "" ? $grade : null;
    }

    private function calculateClassGpaForExport(
        array $prepared,
        string $schoolType,
    ): float {
        $ptMapGPA =
            $schoolType === "Primary"
                ? ["A" => 5, "B" => 4, "C" => 3, "D" => 2, "E" => 1, "F" => 0]
                : ["A" => 1, "B" => 2, "C" => 3, "D" => 4, "F" => 5];

        $classGpaSum = 0.0;
        $classGpaCount = 0;

        foreach ($prepared as $item) {
            $student = $item["student"];
            $row = $item["row"];
            $className = "";
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? "";
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className = $student->oldSubclass->class->class_name ?? "";
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            $stuPoints = 0;
            $stuSubCount = 0;
            foreach ($row["subjects"] as $subj) {
                $grade = $this->resolveSubjectGradeLetter(
                    $subj,
                    $schoolType,
                    $className,
                    $classID,
                );
                if ($grade && isset($ptMapGPA[$grade])) {
                    $stuPoints += $ptMapGPA[$grade];
                    $stuSubCount++;
                }
            }

            if ($stuSubCount > 0) {
                $classGpaSum += $stuPoints / $stuSubCount;
                $classGpaCount++;
            }
        }

        return $classGpaCount > 0
            ? round($classGpaSum / $classGpaCount, 1)
            : 0.0;
    }

    private function populateBoardResultsSheet(
        Worksheet $sheet,
        string $className,
        string $titleLine,
        $classStudents,
        array $resultsData,
        array $examNumbers,
        string $schoolType,
        string $typeFilter,
        $examFilter,
    ): void {
        $prepared = [];
        $subjectNames = [];

        foreach ($classStudents as $student) {
            $row = $this->resolveStudentResultRow(
                $student,
                $resultsData,
                $typeFilter,
                $examFilter,
            );
            if (!$row) {
                continue;
            }

            foreach ($row["subjects"] as $subj) {
                $name = $subj["subject_name"] ?? "";
                if ($name && !in_array($name, $subjectNames, true)) {
                    $subjectNames[] = $name;
                }
            }

            $divisionParts = $this->parseDivisionParts(
                $row["division"],
                $row["total_points"],
            );

            $prepared[] = [
                "student" => $student,
                "row" => $row,
                "division_roman" => $divisionParts["roman"],
                "division_points" => $divisionParts["points"],
            ];
        }

        sort($subjectNames);

        if ($schoolType === "Secondary") {
            usort($prepared, function ($a, $b) {
                $ap = $a["row"]["total_points"] ?? 9999;
                $bp = $b["row"]["total_points"] ?? 9999;
                if ($ap != $bp) {
                    return $ap <=> $bp;
                }
                $ab = $a["row"]["best_seven_total_marks"] ?? 0;
                $bb = $b["row"]["best_seven_total_marks"] ?? 0;
                if ($ab != $bb) {
                    return $bb <=> $ab;
                }

                return ($b["row"]["total_marks"] ?? 0) <=>
                    ($a["row"]["total_marks"] ?? 0);
            });
        } else {
            usort($prepared, function ($a, $b) {
                return ($b["row"]["total_marks"] ?? 0) <=>
                    ($a["row"]["total_marks"] ?? 0);
            });
        }

        foreach ($prepared as $i => &$item) {
            $item["position"] = $i + 1;
        }
        unset($item);

        $fixedCols = 3; // Exam No, Sex, Strm
        $summaryCols = $schoolType === "Secondary" ? 4 : 2;
        $totalCols = $fixedCols + count($subjectNames) + $summaryCols;
        $lastCol = Coordinate::stringFromColumnIndex(max(1, $totalCols));

        $perfHeader = [
            "SEX",
            "REG.",
            "ABS",
            "INC.",
            "I",
            "II",
            "III",
            "IV",
            "0",
            "I-III",
            "GPA",
        ];
        $perfColCount = count($perfHeader);
        // Summary table starts at SEX column (col 2) — not under EXAM NO (board layout)
        $perfStartCol = 2;
        $perfEndCol = $perfStartCol + $perfColCount - 1;
        $perfStartLetter = Coordinate::stringFromColumnIndex($perfStartCol);
        $perfEndLetter = Coordinate::stringFromColumnIndex($perfEndCol);
        $perfDataStartCol = $perfStartCol + 1; // REG. column (skip F/M/T label col)
        $perfDataStartLetter = Coordinate::stringFromColumnIndex($perfDataStartCol);
        $gpaCol = Coordinate::stringFromColumnIndex($perfEndCol);

        // Title rows — centered over CLASS PERFORMANCE summary table (SEX through GPA)
        $sheet->setCellValue("{$perfStartLetter}1", $titleLine);
        $sheet->mergeCells("{$perfStartLetter}1:{$perfEndLetter}1");
        $sheet
            ->getStyle("{$perfStartLetter}1:{$perfEndLetter}1")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet
            ->getStyle("{$perfStartLetter}1:{$perfEndLetter}1")
            ->getFont()
            ->setBold(true)
            ->setSize(12);

        $sheet->setCellValue("{$perfStartLetter}2", strtoupper($className));
        $sheet->mergeCells("{$perfStartLetter}2:{$perfEndLetter}2");
        $sheet
            ->getStyle("{$perfStartLetter}2:{$perfEndLetter}2")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet
            ->getStyle("{$perfStartLetter}2:{$perfEndLetter}2")
            ->getFont()
            ->setBold(true)
            ->setSize(11);

        $sheet->setCellValue("{$perfStartLetter}3", "CLASS PERFORMANCE");
        $sheet->mergeCells("{$perfStartLetter}3:{$perfEndLetter}3");
        $sheet
            ->getStyle("{$perfStartLetter}3:{$perfEndLetter}3")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet
            ->getStyle("{$perfStartLetter}3:{$perfEndLetter}3")
            ->getFont()
            ->setBold(true);

        $perfRowStart = 5;
        $sheet->fromArray([$perfHeader], null, "{$perfStartLetter}{$perfRowStart}");
        $perfDataEndLetter = Coordinate::stringFromColumnIndex($perfEndCol - 1);
        $sheet
            ->getStyle("{$perfStartLetter}{$perfRowStart}:{$perfEndLetter}{$perfRowStart}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet
            ->getStyle("{$perfDataStartLetter}{$perfRowStart}:{$perfDataEndLetter}{$perfRowStart}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFE8F4FC");
        $sheet
            ->getStyle("{$perfStartLetter}{$perfRowStart}:{$perfEndLetter}{$perfRowStart}")
            ->getFont()
            ->setBold(true);

        $stats = [
            "F" => [
                "reg" => 0,
                "abs" => 0,
                "inc" => 0,
                "I" => 0,
                "II" => 0,
                "III" => 0,
                "IV" => 0,
                "0" => 0,
            ],
            "M" => [
                "reg" => 0,
                "abs" => 0,
                "inc" => 0,
                "I" => 0,
                "II" => 0,
                "III" => 0,
                "IV" => 0,
                "0" => 0,
            ],
        ];

        $classGpa = $this->calculateClassGpaForExport($prepared, $schoolType);

        foreach ($prepared as $item) {
            $student = $item["student"];
            $row = $item["row"];
            $sex = $this->genderShort($student->gender);
            if (!isset($stats[$sex])) {
                $sex = "M";
            }
            $stats[$sex]["reg"]++;

            $hasIncomplete = false;
            foreach ($row["subjects"] as $subj) {
                $marks = $subj["marks"] ?? null;
                if (
                    $marks === null ||
                    $marks === "" ||
                    $marks === "incomplete"
                ) {
                    $hasIncomplete = true;
                    break;
                }
            }
            if ($hasIncomplete) {
                $stats[$sex]["inc"]++;
            }

            if ($schoolType === "Secondary") {
                $roman = $item["division_roman"];
                if ($roman && isset($stats[$sex][$roman])) {
                    $stats[$sex][$roman]++;
                }
            }
        }

        $writePerfRow = function ($label, $key) use (
            $sheet,
            $stats,
            $perfRowStart,
            $perfStartLetter,
        ) {
            $s = $stats[$key];
            $iIII = ($s["I"] ?? 0) + ($s["II"] ?? 0) + ($s["III"] ?? 0);
            $sheet->fromArray(
                [
                    [
                        $label,
                        $s["reg"],
                        $s["abs"],
                        $s["inc"],
                        $s["I"],
                        $s["II"],
                        $s["III"],
                        $s["IV"],
                        $s["0"],
                        $iIII,
                    ],
                ],
                null,
                $perfStartLetter .
                    ($perfRowStart + ($label === "F" ? 1 : 2)),
            );
        };
        $writePerfRow("F", "F");
        $writePerfRow("M", "M");

        $total = [
            "reg" => $stats["F"]["reg"] + $stats["M"]["reg"],
            "abs" => 0,
            "inc" => $stats["F"]["inc"] + $stats["M"]["inc"],
            "I" => $stats["F"]["I"] + $stats["M"]["I"],
            "II" => $stats["F"]["II"] + $stats["M"]["II"],
            "III" => $stats["F"]["III"] + $stats["M"]["III"],
            "IV" => $stats["F"]["IV"] + $stats["M"]["IV"],
            "0" => $stats["F"]["0"] + $stats["M"]["0"],
        ];
        $sheet->fromArray(
            [
                [
                    "T",
                    $total["reg"],
                    $total["abs"],
                    $total["inc"],
                    $total["I"],
                    $total["II"],
                    $total["III"],
                    $total["IV"],
                    $total["0"],
                    $total["I"] + $total["II"] + $total["III"],
                ],
            ],
            null,
            $perfStartLetter . ($perfRowStart + 3),
        );

        // GPA — merged vertically across F/M/T, centered in performance block
        $gpaFirstRow = $perfRowStart + 1;
        $gpaLastRow = $perfRowStart + 3;
        $sheet->mergeCells("{$gpaCol}{$gpaFirstRow}:{$gpaCol}{$gpaLastRow}");
        $sheet->setCellValue("{$gpaCol}{$gpaFirstRow}", $classGpa);
        $sheet
            ->getStyle("{$gpaCol}{$gpaFirstRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet
            ->getStyle("{$gpaCol}{$gpaFirstRow}")
            ->getNumberFormat()
            ->setFormatCode("0.0");
        $sheet->getStyle("{$gpaCol}{$gpaFirstRow}")->getFont()->setBold(true);

        // Center-align F / M / T data
        $sheet
            ->getStyle(
                "{$perfStartLetter}" .
                    ($perfRowStart + 1) .
                    ":{$perfEndLetter}" .
                    ($perfRowStart + 3),
            )
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Summary table borders: full outline + vertical dividers; F/M grouped (no line between them)
        $perfLastRow = $perfRowStart + 3;
        $perfRange = "{$perfStartLetter}{$perfRowStart}:{$perfEndLetter}{$perfLastRow}";
        $fRow = $perfRowStart + 1;
        $mRow = $perfRowStart + 2;

        $sheet
            ->getStyle($perfRange)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet
            ->getStyle("{$perfStartLetter}{$fRow}:{$perfDataEndLetter}{$fRow}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_NONE);
        $sheet
            ->getStyle("{$perfStartLetter}{$mRow}:{$perfDataEndLetter}{$mRow}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(Border::BORDER_NONE);

        // GPA merged cell — no internal horizontal lines between F / M / T
        $sheet
            ->getStyle("{$gpaCol}{$fRow}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_NONE);
        $sheet
            ->getStyle("{$gpaCol}{$mRow}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(Border::BORDER_NONE);
        $sheet
            ->getStyle("{$gpaCol}{$mRow}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle(Border::BORDER_NONE);

        $tableHeaderRow = $perfLastRow + 1;
        $headers = ["EXAM NO.", "SEX", "STRM"];
        foreach ($subjectNames as $name) {
            $headers[] = $name;
        }
        $headers[] = "AVG";
        if ($schoolType === "Secondary") {
            $headers[] = "DIV";
            $headers[] = "POINT";
            $headers[] = "POST";
        } else {
            $headers[] = "GRADE";
            $headers[] = "POST";
        }

        $sheet->fromArray([$headers], null, "A{$tableHeaderRow}");

        $dataRow = $tableHeaderRow + 1;
        foreach ($prepared as $item) {
            $student = $item["student"];
            $row = $item["row"];
            $stream =
                $student->subclass->stream_code ??
                ($student->oldSubclass->stream_code ?? "");

            $line = [
                $examNumbers[$student->studentID] ?? "N/A",
                $this->genderShort($student->gender),
                $stream,
            ];

            $subjectMap = [];
            foreach ($row["subjects"] as $subj) {
                $subjectMap[$subj["subject_name"] ?? ""] = $subj;
            }

            foreach ($subjectNames as $name) {
                $subj = $subjectMap[$name] ?? null;
                if (!$subj) {
                    $line[] = "-";
                    continue;
                }
                $marks = $subj["marks"] ?? null;
                if (
                    $marks === null ||
                    $marks === "" ||
                    $marks === "incomplete"
                ) {
                    $line[] = "-";
                } else {
                    $grade = $subj["grade"] ?? "";
                    $line[] =
                        round((float) $marks) . ($grade ? "-" . $grade : "");
                }
            }

            $line[] = number_format((float) ($row["average_marks"] ?? 0), 0);
            if ($schoolType === "Secondary") {
                $line[] = $item["division_roman"] ?: "-";
                $line[] = $item["division_points"] !== ""
                    ? $item["division_points"]
                    : "-";
                $line[] = $item["position"];
            } else {
                $line[] = $row["grade"] ?? "-";
                $line[] = $item["position"];
            }

            $sheet->fromArray([$line], null, "A{$dataRow}");
            $dataRow++;
        }

        $headerStyle = "A{$tableHeaderRow}:{$lastCol}{$tableHeaderRow}";
        $sheet
            ->getStyle($headerStyle)
            ->getFont()
            ->setBold(true);
        $sheet
            ->getStyle($headerStyle)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFF5F5F5");
        $sheet
            ->getStyle($headerStyle)
            ->getFont()
            ->getColor()
            ->setARGB("FF000000");
        $sheet
            ->getStyle($headerStyle)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // EXAM NO. — horizontal; all other headers — vertical (board style)
        $sheet
            ->getStyle("A{$tableHeaderRow}")
            ->getAlignment()
            ->setWrapText(true);

        $maxVerticalLen = 0;
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $headerText = $headers[$c - 1] ?? "";
            $maxVerticalLen = max($maxVerticalLen, strlen($headerText));
            $sheet
                ->getStyle("{$colLetter}{$tableHeaderRow}")
                ->getAlignment()
                ->setTextRotation(90)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        $sheet
            ->getRowDimension($tableHeaderRow)
            ->setRowHeight(min(200, max(70, (int) round($maxVerticalLen * 5.5))));

        $lastDataRow = $dataRow - 1;
        if ($lastDataRow >= $tableHeaderRow) {
            $sheet
                ->getStyle("A{$tableHeaderRow}:{$lastCol}{$lastDataRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet
                ->getStyle("A" . ($tableHeaderRow + 1) . ":{$lastCol}{$lastDataRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        for ($c = 1; $c <= $totalCols; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            if ($c === 1) {
                $sheet->getColumnDimension($colLetter)->setWidth(18);
            } else {
                $sheet->getColumnDimension($colLetter)->setWidth(5);
            }
        }
    }

    /**
     * Download results as Excel
     */
    public function downloadExcel(Request $request)
    {
        $userType = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        // Match PDF export: any logged-in user with a valid school session
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Access denied");
        }

        $option = $request->get("option", "all");
        $studentID = $request->get("studentID", null);
        $classID = $request->get("classID", null);
        $subclassID = $request->get("subclassID", null);

        // Get filter parameters
        $termFilter = $request->get("term", "");
        $yearFilter = $request->get("year", date("Y"));
        $typeFilter = $request->get("type", "exam");
        $statusFilter = $request->get("status", "active");
        $examFilter = $request->get("examID", "");

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : "Secondary";

        // Get students based on option (same logic as PDF)
        $students = collect();
        if ($option === "single" && $studentID) {
            $students = Student::where("studentID", $studentID)
                ->where("schoolID", $schoolID)
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "parent",
                    "oldSubclass.class",
                ])
                ->get();
        } elseif ($option === "class" && $classID) {
            $students = Student::where("schoolID", $schoolID)
                ->where(function ($query) use ($classID) {
                    $query
                        ->whereHas("subclass", function ($q) use ($classID) {
                            $q->where("classID", $classID);
                        })
                        ->orWhereHas("oldSubclass", function ($q) use (
                            $classID,
                        ) {
                            $q->where("classID", $classID);
                        });
                })
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "parent",
                    "oldSubclass.class",
                ])
                ->orderBy("first_name")
                ->get();
        } elseif ($option === "subclass" && $subclassID) {
            $students = Student::where("schoolID", $schoolID)
                ->where("subclassID", $subclassID)
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "parent",
                    "oldSubclass.class",
                ])
                ->orderBy("first_name")
                ->get();
        } else {
            // All students — respect optional class/subclass/gender filters (same as PDF)
            $classFilterID = $request->get("classID", "");
            $subclassFilterID = $request->get("subclassID", "");
            $genderFilter = $request->get("gender", "");

            $studentQuery = Student::where("schoolID", $schoolID);

            if ($statusFilter === "active") {
                $studentQuery->where("status", "Active");
            } elseif ($statusFilter === "history") {
                $studentQuery->whereNotNull("old_subclassID");
            }

            if ($subclassFilterID) {
                $studentQuery->where("subclassID", $subclassFilterID);
            } elseif ($classFilterID) {
                $studentQuery->where(function ($q) use ($classFilterID) {
                    $q->whereHas("subclass", function ($sq) use (
                        $classFilterID,
                    ) {
                        $sq->where("classID", $classFilterID);
                    })->orWhereHas("oldSubclass", function ($sq) use (
                        $classFilterID,
                    ) {
                        $sq->where("classID", $classFilterID);
                    });
                });
            }

            if ($genderFilter) {
                $studentQuery->where("gender", $genderFilter);
            }

            $students = $studentQuery
                ->with([
                    "subclass.class",
                    "subclass.combie",
                    "parent",
                    "oldSubclass.class",
                ])
                ->orderBy("first_name")
                ->get();
        }

        // Get results data
        $resultsData = [];
        if ($termFilter && $yearFilter) {
            if ($typeFilter === "exam") {
                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if ($examFilter && !is_array($examFilter)) {
                    $caDef = CaDefinition::where("examID", $examFilter)
                        ->where("schoolID", $schoolID)
                        ->first();
                    if ($caDef && !empty($caDef->test_ids)) {
                        $caExamIDs = array_merge(
                            [$examFilter],
                            $caDef->test_ids,
                        );
                    }
                }
                $resultsData = $this->getExamResults(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                    $caExamIDs,
                );
            } else {
                $resultsData = $this->getTermReport(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                );
            }
        }

        // Filter students to only those with results
        $students = $students->filter(function ($student) use ($resultsData) {
            return isset($resultsData[$student->studentID]);
        });

        if ($students->isEmpty()) {
            return redirect()
                ->back()
                ->with("error", "No results found for the selected filters.");
        }

        $titleLine = $this->buildBoardTitleLine(
            $typeFilter,
            $examFilter,
            $termFilter,
            $yearFilter,
        );

        $studentsByClass = [];
        foreach ($students as $student) {
            $cid = null;
            if ($student->subclass && $student->subclass->class) {
                $cid = $student->subclass->class->classID;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $cid = $student->oldSubclass->class->classID;
            }
            if (!$cid) {
                $cid = 0;
            }
            $studentsByClass[$cid][] = $student;
        }

        $spreadsheet = new Spreadsheet();
        $sheetIndex = 0;

        foreach ($studentsByClass as $cid => $classStudents) {
            $className =
                $cid > 0
                    ? ClassModel::find($cid)?->class_name ?? "Class {$cid}"
                    : "Unknown Class";
            $sheetTitle = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', "", $className);
            $sheetTitle = substr($sheetTitle, 0, 31) ?: "Results";

            $sheet =
                $sheetIndex === 0
                    ? $spreadsheet->getActiveSheet()
                    : new Worksheet($spreadsheet, $sheetTitle);

            if ($sheetIndex > 0) {
                $spreadsheet->addSheet($sheet);
            } else {
                $sheet->setTitle($sheetTitle);
            }

            $examNumbers =
                $cid > 0
                    ? $this->buildExamNumberMapForClass($cid, $school)
                    : [];

            $this->populateBoardResultsSheet(
                $sheet,
                $className,
                $titleLine,
                collect($classStudents),
                $resultsData,
                $examNumbers,
                $schoolType,
                $typeFilter,
                $examFilter,
            );

            $sheetIndex++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        // Generate filename
        $filename = "Results_";
        if ($option === "single" && $students->count() > 0) {
            $student = $students->first();
            $filename .= $student->first_name . "_" . $student->last_name . "_";
        } elseif ($option === "class" && $classID) {
            $class = ClassModel::find($classID);
            $filename .=
                ($class ? str_replace(" ", "_", $class->class_name) : "Class") .
                "_";
        } elseif ($option === "subclass" && $subclassID) {
            $subclass = Subclass::find($subclassID);
            $filename .=
                ($subclass
                    ? str_replace(" ", "_", $subclass->subclass_name)
                    : "Subclass") . "_";
        } else {
            $filename .= "All_Students_";
        }
        $filename .= ($typeFilter === "exam" ? "Exam" : "Term_Report") . "_";
        $filename .=
            ($termFilter ? str_replace("_", "", $termFilter) . "_" : "") .
            $yearFilter .
            ".xlsx";

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        header(
            "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        );
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header("Cache-Control: max-age=0");
        $writer->save("php://output");
        exit();
    }
    /**
     * Save term report definition
     */
    public function saveReportDefinition(Request $request)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return response()->json([
                "success" => false,
                "message" => "School ID not found in session",
            ]);
        }

        $validator = Validator::make($request->all(), [
            "year" => "required|integer",
            "term" => "required|string",
            "exam_ids" => "required|array",
            "exam_ids.*" => "integer",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->errors()->first(),
            ]);
        }

        try {
            TermReportDefinition::updateOrCreate(
                [
                    "schoolID" => $schoolID,
                    "year" => $request->year,
                    "term" => $request->term,
                ],
                [
                    "exam_ids" => $request->exam_ids,
                    "created_by" =>
                        Session::get("teacherID") ?? (auth()->id() ?? 1),
                ],
            );

            return response()->json([
                "success" => true,
                "message" => "Report definition saved successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all report definitions for the school
     */
    public function getReportDefinitions(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $year = $request->query("year");
        $term = $request->query("term");

        $query = TermReportDefinition::where("schoolID", $schoolID);

        if ($year) {
            $query->where("year", $year);
        }
        if ($term && $term !== "") {
            $query->where("term", $term);
        }

        $definitions = $query
            ->orderBy("year", "desc")
            ->orderBy("term", "asc")
            ->get();

        // Get names for exams in each definition
        foreach ($definitions as $def) {
            if (!empty($def->exam_ids)) {
                $def->exam_names = Examination::whereIn(
                    "examID",
                    $def->exam_ids,
                )
                    ->pluck("exam_name")
                    ->toArray();
            } else {
                $def->exam_names = [];
            }
        }

        return response()->json(["success" => true, "data" => $definitions]);
    }

    /**
     * Delete report definition
     */
    public function deleteReportDefinition($id)
    {
        $schoolID = Session::get("schoolID");
        TermReportDefinition::where("id", $id)
            ->where("schoolID", $schoolID)
            ->delete();

        return response()->json([
            "success" => true,
            "message" => "Definition deleted successfully",
        ]);
    }

    /**
     * Get exams for a specific term and year
     */
    public function getExamsForTerm(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $term = $request->term;
        $year = $request->year;

        $exams = Examination::where("schoolID", $schoolID)
            ->where("year", $year)
            ->where("term", $term)
            ->orderBy("start_date", "asc")
            ->get(["examID", "exam_name"]);

        return response()->json(["success" => true, "data" => $exams]);
    }

    // ==========================================
    // CA Definition Methods
    // ==========================================

    public function getSchoolExamsForTermList(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $year = $request->year;
        $term = $request->term;
        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $yearCtx["academic_year_ids"];
        $calendarYear = $year ?: $yearCtx["calendar_year"];

        $examQuery = Examination::where("schoolID", $schoolID)
            ->where("term", $term)
            ->where("exam_category", "school_exams")
            ->orderBy("start_date", "asc");
        $this->applyExaminationAcademicYearFilter(
            $examQuery,
            $academicYearIDs,
            $calendarYear,
        );

        $exams = $examQuery->get(["examID", "exam_name"]);

        return response()->json(["success" => true, "data" => $exams]);
    }

    public function getTestsForTermList(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $year = $request->year;
        $term = $request->term;
        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $yearCtx["academic_year_ids"];
        $calendarYear = $year ?: $yearCtx["calendar_year"];

        $examQuery = Examination::where("schoolID", $schoolID)
            ->where("term", $term)
            ->where("exam_category", "test")
            ->orderBy("start_date", "asc");
        $this->applyExaminationAcademicYearFilter(
            $examQuery,
            $academicYearIDs,
            $calendarYear,
        );

        $exams = $examQuery->get(["examID", "exam_name", "end_date"]);

        return response()->json(["success" => true, "data" => $exams]);
    }

    public function checkExamCaExists(Request $request)
    {
        $examID = $request->examID;
        $schoolID = Session::get("schoolID");

        $exists = CaDefinition::where("examID", $examID)
            ->where("schoolID", $schoolID)
            ->exists();

        return response()->json(["success" => true, "exists" => $exists]);
    }

    public function saveCaDefinition(Request $request)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return response()->json([
                "success" => false,
                "message" => "Session expired",
            ]);
        }

        $validator = Validator::make($request->all(), [
            "year" => "required|integer",
            "term" => "required|string",
            "examID" => "required|integer",
            "test_ids" => "required|array",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "All fields are required.",
            ]);
        }

        CaDefinition::updateOrCreate(
            [
                "schoolID" => $schoolID,
                "examID" => $request->examID,
            ],
            [
                "year" => $request->year,
                "term" => $request->term,
                "test_ids" => $request->test_ids,
                "created_by" =>
                    Session::get("teacherID") ?? (Session::get("staffID") ?? 0),
            ],
        );

        return response()->json([
            "success" => true,
            "message" => "CA Definition saved successfully.",
        ]);
    }

    public function getCaDefinitions(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $year = $request->year;
        $term = $request->term;

        $query = CaDefinition::where("schoolID", $schoolID)->with("mainExam");

        if ($year) {
            $query->where("year", $year);
        }
        if ($term) {
            $query->where("term", $term);
        }
        if ($request->examID) {
            $query->where("examID", $request->examID);
        }

        $defs = $query
            ->orderBy("created_at", "desc")
            ->get()
            ->map(function ($def) {
                $testNames = Examination::whereIn("examID", $def->test_ids)
                    ->pluck("exam_name")
                    ->toArray();
                $def->test_names = $testNames;
                return $def;
            });

        return response()->json(["success" => true, "data" => $defs]);
    }

    public function deleteCaDefinition($id)
    {
        $schoolID = Session::get("schoolID");
        $def = CaDefinition::where("id", $id)
            ->where("schoolID", $schoolID)
            ->first();

        if ($def) {
            $def->delete();
            return response()->json([
                "success" => true,
                "message" => "CA Definition deleted.",
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => "Definition not found.",
        ]);
    }

    /**
     * Get latest character assessments for a set of students by term/year
     */
    private function getLatestCharacterAssessments(
        $students,
        $term,
        $year,
        $schoolID,
    ) {
        $studentIDs = $students->pluck("studentID")->filter()->values();
        if ($studentIDs->isEmpty()) {
            return collect();
        }

        $candidateTimetables = CharacterAssessmentTimetable::where(
            "schoolID",
            $schoolID,
        )
            ->where("term", $term)
            ->where("year", $year)
            ->orderBy("end_date", "desc")
            ->orderBy("start_date", "desc")
            ->get();

        if ($candidateTimetables->isEmpty()) {
            return collect();
        }

        $latestTimetable = null;
        foreach ($candidateTimetables as $tt) {
            $hasAnyAssessment = StudentCharacterAssessment::where(
                "timetableID",
                $tt->timetableID,
            )
                ->whereIn("studentID", $studentIDs)
                ->exists();
            if ($hasAnyAssessment) {
                $latestTimetable = $tt;
                break;
            }
        }

        if (!$latestTimetable) {
            return collect();
        }

        return StudentCharacterAssessment::where(
            "timetableID",
            $latestTimetable->timetableID,
        )
            ->whereIn("studentID", $studentIDs)
            ->with(["category", "choice"])
            ->get()
            ->groupBy("studentID");
    }

    /**
     * Get latest character assessment for one student in selected timetable range.
     */
    private function getLatestCharacterAssessmentForStudent(
        $studentID,
        $term,
        $year,
        $schoolID,
        $referenceDate = null,
    ) {
        if (!$studentID || !$schoolID || !$term || !$year) {
            return null;
        }

        $resolveTimetable = function ($withDateRange = true) use (
            $studentID,
            $term,
            $year,
            $schoolID,
            $referenceDate,
        ) {
            $query = CharacterAssessmentTimetable::where("schoolID", $schoolID)
                ->where("term", $term)
                ->where("year", $year);

            if ($withDateRange && $referenceDate) {
                $query
                    ->whereDate("start_date", "<=", $referenceDate)
                    ->whereDate("end_date", ">=", $referenceDate);
            }

            return $query
                ->whereExists(function ($sub) use ($studentID) {
                    $sub->select(DB::raw(1))
                        ->from("student_character_assessments as sca")
                        ->whereColumn("sca.timetableID", "character_assessment_timetables.timetableID")
                        ->where("sca.studentID", $studentID);
                })
                ->orderBy("end_date", "desc")
                ->orderBy("start_date", "desc")
                ->first();
        };

        $latestTimetable = $resolveTimetable(true);
        if (!$latestTimetable) {
            $latestTimetable = $resolveTimetable(false);
        }
        if (!$latestTimetable) {
            return null;
        }

        $items = StudentCharacterAssessment::where(
            "timetableID",
            $latestTimetable->timetableID,
        )
            ->where("studentID", $studentID)
            ->with(["category", "choice"])
            ->orderBy("categoryID")
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        return [
            "timetable" => [
                "timetableID" => $latestTimetable->timetableID,
                "year" => $latestTimetable->year,
                "term" => $latestTimetable->term,
                "start_date" => $latestTimetable->start_date,
                "end_date" => $latestTimetable->end_date,
            ],
            "items" => $items
                ->map(function ($row) {
                    return [
                        "category" => $row->category->name ?? "N/A",
                        "choice" => $row->choice->choice_name ?? null,
                        "description" => $row->description,
                    ];
                })
                ->values(),
        ];
    }
    public function toggleExamPublication(Request $request)
    {
        $examID = $request->get("examID");
        $classID = $request->get("classID");
        $subclassID = $request->get("subclassID");
        $status = $request->get("status"); // 'enabled' or 'disabled'
        $schoolID = Session::get("schoolID");

        if (!$schoolID) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized"],
                401,
            );
        }

        $query = Result::where("examID", $examID);

        if ($subclassID) {
            $query->where("subclassID", $subclassID);
        } elseif ($classID) {
            $subclassIDs = Subclass::where("classID", $classID)->pluck(
                "subclassID",
            );
            $query->whereIn("subclassID", $subclassIDs);
        }

        $query->update(["parent_status" => $status]);

        if ($status === "enabled") {
            // Create notifications for parents
            $results = $query->with("student.parent")->get();
            $exam = Examination::find($examID);

            $uniqueStudents = $results->pluck("studentID")->unique();

            foreach ($uniqueStudents as $studentID) {
                $student = Student::find($studentID);
                if ($student && $student->parentID) {
                    ParentResultNotification::updateOrCreate(
                        [
                            "schoolID" => $schoolID,
                            "parentID" => $student->parentID,
                            "studentID" => $studentID,
                            "type" => "exam",
                            "examID" => $examID,
                        ],
                        [
                            "term" => $exam->term ?? "",
                            "year" => $exam->year ?? "",
                            "is_viewed" => false,
                            "updated_at" => now(),
                        ],
                    );
                }
            }
        } else {
            // Remove notifications if disabled - ONLY for students in this subclass
            $subclassID = $request->get("subclassID");
            $classID = $request->get("classID");

            $studentQuery = Student::where("schoolID", $schoolID);
            if ($subclassID) {
                $studentQuery->where("subclassID", $subclassID);
            } elseif ($classID) {
                $subclassIDs = Subclass::where("classID", $classID)->pluck(
                    "subclassID",
                );
                $studentQuery->whereIn("subclassID", $subclassIDs);
            }

            $studentIDs = $studentQuery->pluck("studentID");

            ParentResultNotification::where("examID", $examID)
                ->where("type", "exam")
                ->whereIn("studentID", $studentIDs)
                ->delete();
        }

        return response()->json([
            "success" => true,
            "message" =>
                "Exam results " .
                ($status === "enabled" ? "published" : "unpublished") .
                " for parents.",
        ]);
    }

    public function toggleReportPublication(Request $request)
    {
        $classID = $request->get("classID");
        $term = $request->get("term");
        $year = $request->get("year");
        $status = $request->get("status");
        $schoolID = Session::get("schoolID");

        if (!$schoolID) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized"],
                401,
            );
        }

        $validator = Validator::make($request->all(), [
            "classID" => "required",
            "term" => "required",
            "year" => "required",
            "status" => "required|in:enabled,disabled",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Please select class, term, and year before publishing reports.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            PublishedReport::updateOrCreate(
                [
                    "schoolID" => $schoolID,
                    "classID" => $classID,
                    "term" => $term,
                    "year" => $year,
                ],
                ["status" => $status],
            );

            if ($status === "enabled") {
                $subclassIDs = Subclass::where("classID", $classID)->pluck(
                    "subclassID",
                );
                $students = Student::whereIn("subclassID", $subclassIDs)
                    ->where("status", "Active")
                    ->get();

                foreach ($students as $student) {
                    if ($student->parentID) {
                        ParentResultNotification::updateOrCreate(
                            [
                                "schoolID" => $schoolID,
                                "parentID" => $student->parentID,
                                "studentID" => $student->studentID,
                                "type" => "report",
                                "term" => $term,
                                "year" => $year,
                            ],
                            [
                                "is_viewed" => false,
                                "updated_at" => now(),
                            ],
                        );
                    }
                }
            } else {
                // Remove notifications - ONLY for students in this class
                $subclassIDs = Subclass::where("classID", $classID)->pluck(
                    "subclassID",
                );
                $studentIDs = Student::whereIn("subclassID", $subclassIDs)->pluck(
                    "studentID",
                );

                ParentResultNotification::where("schoolID", $schoolID)
                    ->where("type", "report")
                    ->where("term", $term)
                    ->where("year", $year)
                    ->whereIn("studentID", $studentIDs)
                    ->delete();
            }
        } catch (\Throwable $e) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Unable to update report publication status. Please try again.",
                ],
                500,
            );
        }

        return response()->json([
            "success" => true,
            "message" =>
                "Term reports " .
                ($status === "enabled" ? "published" : "unpublished") .
                " for parents.",
        ]);
    }

    public function getPublishedReports(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $year = $request->get("year");
        $term = $request->get("term");

        $published = PublishedReport::where("schoolID", $schoolID)
            ->where("year", $year)
            ->where("term", $term)
            ->get();

        return response()->json([
            "success" => true,
            "data" => $published,
        ]);
    }

    public function addResultsView(Request $request)
    {
        $user_type = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        $teacherID = Session::get("teacherID");

        if (
            $user_type !== "Admin" &&
            $user_type !== "Staff" &&
            $user_type !== "Teacher"
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        // Get global teacher result entry permission
        $teacherResultPermission = \App\Models\TeacherResultPermission::where(
            "schoolID",
            $schoolID,
        )->first();
        $resultButtonStatus = $teacherResultPermission
            ? $teacherResultPermission->result_button_status
            : true;

        // Initialize $availableTerms for all user types (overridden for Admin/Staff below)
        $availableTerms = collect();

        if ($user_type === "Teacher") {
            if (!$resultButtonStatus) {
                return redirect()
                    ->back()
                    ->with(
                        "error",
                        "Teacher result entry is currently disabled by administrator.",
                    );
            }

            $assignments = TeacherResultAssignment::where("schoolID", $schoolID)
                ->where("teacherID", $teacherID)
                ->where("status", true)
                ->get();

            if ($assignments->isEmpty()) {
                return redirect()
                    ->back()
                    ->with(
                        "error",
                        "You have no active result entry assignments.",
                    );
            }

            $assignedClassIDs = $assignments->pluck("classID")->unique();
            $assignedSubclassIDs = $assignments
                ->pluck("subclassID")
                ->filter()
                ->unique();
            $assignedExamIDs = $assignments->pluck("examID")->unique();
            $assignedSubjectIDs = $assignments
                ->pluck("subject_ids")
                ->flatten()
                ->unique();

            $availableYears = Examination::whereIn("examID", $assignedExamIDs)
                ->distinct()
                ->orderBy("year", "desc")
                ->pluck("year");
            $classes = ClassModel::whereIn("classID", $assignedClassIDs)
                ->where("status", "Active")
                ->get();

            $subclassesQuery = Subclass::with("class")->where(
                "status",
                "Active",
            );
            if ($assignedSubclassIDs->isNotEmpty()) {
                $subclassesQuery->whereIn("subclassID", $assignedSubclassIDs);
            } else {
                $subclassesQuery->whereIn("classID", $assignedClassIDs);
            }
            $subclasses = $subclassesQuery->get();

            $schoolSubjects = SchoolSubject::whereIn(
                "subjectID",
                $assignedSubjectIDs,
            )
                ->where("status", "Active")
                ->get();
            $availableExams = Examination::whereIn("examID", $assignedExamIDs)
                ->orderBy("created_at", "desc")
                ->get();
        } else {
            // Admin/Staff logic
            $availableYears = Examination::where("schoolID", $schoolID)
                ->distinct()
                ->orderBy("year", "desc")
                ->pluck("year");
            $availableTerms = \App\Models\Term::where("schoolID", $schoolID)
                ->orderBy("term_number")
                ->get();

            $classes = ClassModel::where("schoolID", $schoolID)
                ->where("status", "Active")
                ->get();
            $subclasses = Subclass::with("class")
                ->whereHas("class", function ($q) use ($schoolID) {
                    $q->where("schoolID", $schoolID);
                })
                ->where("status", "Active")
                ->get();

            $schoolSubjects = SchoolSubject::where("schoolID", $schoolID)
                ->where("status", "Active")
                ->get();

            $availableExams = Examination::where("schoolID", $schoolID)
                ->whereIn("status", [
                    "ongoing",
                    "awaiting_results",
                    "published",
                ])
                ->orderBy("created_at", "desc")
                ->get();
        }

        return view(
            "Admin.admin_add_result",
            compact(
                "user_type",
                "availableYears",
                "availableTerms",
                "classes",
                "subclasses",
                "schoolSubjects",
                "availableExams",
                "resultButtonStatus",
            ),
        );
    }

    public function getClassSubjectId(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $classID = $request->get("classID");
        $subclassID = $request->get("subclassID"); // '' or 'all' means every subclass
        $subjectID = $request->get("subjectID");

        $baseQuery = ClassSubject::whereHas("class", function ($q) use (
            $schoolID,
        ) {
            $q->where("schoolID", $schoolID);
        })
            ->where("classID", $classID)
            ->where("subjectID", $subjectID)
            ->where("status", "Active");

        // Specific subclass selected → return single class_subjectID (old behaviour)
        if (!empty($subclassID) && $subclassID !== "all") {
            $classSubject = ClassSubject::where("subclassID", $subclassID)
                ->where("subjectID", $subjectID)
                ->first();
            if ($classSubject) {
                if ($classSubject->status !== "Active") {
                    $classSubject->status = "Active";
                    $classSubject->save();
                }
            } else {
                $classSubject = ClassSubject::create([
                    "classID" => $classID,
                    "subclassID" => $subclassID,
                    "subjectID" => $subjectID,
                    "status" => "Active",
                    "student_status" => "Required",
                ]);
            }
            return response()->json([
                "success" => true,
                "all_subclasses" => false,
                "class_subjectID" => $classSubject->class_subjectID,
            ]);
        }

        // "All Subclasses" mode → get every class_subject row for this class+subject
        // First, ensure all active subclasses have a ClassSubject entry
        $activeSubclasses = Subclass::where("classID", $classID)
            ->where("status", "Active")
            ->get();

        foreach ($activeSubclasses as $sub) {
            $cs = ClassSubject::where("subclassID", $sub->subclassID)
                ->where("subjectID", $subjectID)
                ->first();
            if ($cs) {
                if ($cs->status !== "Active") {
                    $cs->status = "Active";
                    $cs->save();
                }
            } else {
                ClassSubject::create([
                    "classID" => $classID,
                    "subclassID" => $sub->subclassID,
                    "subjectID" => $subjectID,
                    "status" => "Active",
                    "student_status" => "Required",
                ]);
            }
        }

        $classSubjects = $baseQuery->whereNotNull("subclassID")->get();

        if ($classSubjects->isEmpty()) {
            // Fallback: maybe there is one without subclassID
            $single = ClassSubject::where("classID", $classID)
                ->where("subjectID", $subjectID)
                ->whereNull("subclassID")
                ->first();
            if ($single) {
                if ($single->status !== "Active") {
                    $single->status = "Active";
                    $single->save();
                }
            } else {
                $single = ClassSubject::create([
                    "classID" => $classID,
                    "subjectID" => $subjectID,
                    "status" => "Active",
                    "student_status" => "Required",
                ]);
            }
            return response()->json([
                "success" => true,
                "all_subclasses" => false,
                "class_subjectID" => $single->class_subjectID,
            ]);
        }

        // Build subclassID → class_subjectID map
        $map = [];
        foreach ($classSubjects as $cs) {
            $map[$cs->subclassID] = $cs->class_subjectID;
        }

        return response()->json([
            "success" => true,
            "all_subclasses" => true,
            "class_subjectID" => $classSubjects->first()->class_subjectID, // fallback
            "class_subject_map" => $map, // { subclassID: class_subjectID }
            "classID" => $classID,
            "subjectID" => $subjectID,
        ]);
    }

    public function getTeachersForAssignment(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $teachers = Teacher::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->select("id", "first_name", "middle_name", "last_name")
            ->get();
        return response()->json(["success" => true, "teachers" => $teachers]);
    }

    public function getSubjectsForAssignment(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $classID = $request->get("classID");
        $subclassID = $request->get("subclassID"); // 'all' or a specific subclassID
        $examID = $request->get("examID");

        $query = ClassSubject::with("subject")
            ->whereHas("class", function ($q) use ($schoolID) {
                $q->where("schoolID", $schoolID);
            })
            ->where("classID", $classID)
            ->where("status", "Active");

        // When a specific subclass is chosen, filter to that subclass only.
        // When 'all' (or empty), load subjects from ALL subclasses of this class
        // and deduplicate — so a subject taught in multiple subclasses appears once.
        if (!empty($subclassID) && $subclassID !== "all") {
            $query->where("subclassID", $subclassID);
        }
        // else: no subclassID filter — fetches every class_subject row for this class

        $subjects = $query
            ->get()
            ->map(function ($cs) {
                return [
                    "subjectID" => $cs->subjectID,
                    "subject_name" => $cs->subject
                        ? $cs->subject->subject_name
                        : "Unknown",
                ];
            })
            ->unique("subjectID")
            ->sortBy("subject_name")
            ->values();

        // Find already assigned subjects for this context.
        // For 'all' subclasses, check assignments where subclassID IS NULL (stored as null for "all").
        $alreadyAssignedIds = [];
        if ($examID) {
            $alreadyAssignedIds = TeacherResultAssignment::where(
                "schoolID",
                $schoolID,
            )
                ->where("classID", $classID)
                ->where("examID", $examID)
                ->where(
                    "subclassID",
                    !empty($subclassID) && $subclassID !== "all"
                        ? $subclassID
                        : null,
                )
                ->where("status", true)
                ->get()
                ->pluck("subject_ids")
                ->flatten()
                ->unique()
                ->toArray();
        }

        return response()->json([
            "success" => true,
            "subjects" => $subjects,
            "alreadyAssignedIds" => $alreadyAssignedIds,
        ]);
    }

    public function assignTeacherToResult(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $createdBy = Session::get("teacherID") ?? 0;

        $validator = Validator::make($request->all(), [
            "id" => "nullable|exists:teacher_result_assignments,id",
            "teacherID" => "required|exists:teachers,id",
            "classID" => "required|exists:classes,classID",
            "examID" => "required|exists:examinations,examID",
            "subject_ids" => "required|array",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->errors()->first(),
            ]);
        }

        if ($request->filled("id")) {
            $assignment = TeacherResultAssignment::findOrFail($request->id);
            $assignment->update([
                "teacherID" => $request->teacherID,
                "classID" => $request->classID,
                "subclassID" =>
                    $request->subclassID === "all"
                        ? null
                        : $request->subclassID,
                "examID" => $request->examID,
                "subject_ids" => $request->subject_ids,
            ]);
        } else {
            $assignment = TeacherResultAssignment::updateOrCreate(
                [
                    "schoolID" => $schoolID,
                    "teacherID" => $request->teacherID,
                    "classID" => $request->classID,
                    "subclassID" =>
                        $request->subclassID === "all"
                            ? null
                            : $request->subclassID,
                    "examID" => $request->examID,
                ],
                [
                    "subject_ids" => $request->subject_ids,
                    "status" => true,
                    "created_by" => $createdBy,
                ],
            );
        }

        // Send SMS to teacher
        try {
            $teacher = Teacher::find($request->teacherID);
            $exam = Examination::find($request->examID);
            $school = School::find($schoolID);

            if ($teacher && $teacher->phone_number && $exam && $school) {
                $class = ClassModel::find($request->classID);
                $subclass =
                    $request->subclassID && $request->subclassID !== "all"
                        ? Subclass::find($request->subclassID)
                        : null;

                $className = $class ? $class->class_name : "N/A";
                if ($subclass) {
                    $className .= " " . $subclass->subclass_name;
                }

                $termName =
                    $exam->term === "first_term"
                        ? "First Term"
                        : ($exam->term === "second_term"
                            ? "Second Term"
                            : "All Terms");
                $subjectNames = SchoolSubject::whereIn(
                    "subjectID",
                    $request->subject_ids,
                )
                    ->pluck("subject_name")
                    ->toArray();
                $subjectList = implode(", ", $subjectNames);

                $message = "{$school->school_name}. Habari teacher {$teacher->first_name}, umechaguliwa kujaza matokeo ya mtihani {$exam->exam_name}, darasa la {$className}, muhula wa {$termName}, katika masomo yafuatayo: {$subjectList}.";

                $smsService = new SmsService();
                $smsService->sendSms($teacher->phone_number, $message);
            }
        } catch (\Exception $e) {
            \Log::error(
                "Failed to send teacher assignment SMS: " . $e->getMessage(),
            );
        }

        return response()->json([
            "success" => true,
            "message" => "Teacher assignment saved successfully.",
        ]);
    }

    public function getAssignmentDetails($id)
    {
        $schoolID = Session::get("schoolID");
        $assignment = TeacherResultAssignment::where("id", $id)
            ->where("schoolID", $schoolID)
            ->first();

        if (!$assignment) {
            return response()->json([
                "success" => false,
                "message" => "Assignment not found.",
            ]);
        }

        return response()->json([
            "success" => true,
            "assignment" => $assignment,
        ]);
    }

    public function getTeacherAssignments(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $query = TeacherResultAssignment::with([
            "teacher",
            "class",
            "subclass",
            "exam",
        ])->where("schoolID", $schoolID);

        if ($request->filled("teacherID")) {
            $query->where("teacherID", $request->teacherID);
        }
        if ($request->filled("examID")) {
            $query->where("examID", $request->examID);
        }
        if ($request->filled("classID")) {
            $query->where("classID", $request->classID);
        }
        if ($request->filled("year")) {
            $query->whereHas(
                "exam",
                fn($q) => $q->where("year", $request->year),
            );
        }
        if ($request->filled("term")) {
            $query->whereHas(
                "exam",
                fn($q) => $q->where("term", $request->term),
            );
        }

        $assignments = $query->orderBy("created_at", "desc")->get();

        // Get subject names for each assignment
        foreach ($assignments as $assignment) {
            $subjectNames = SchoolSubject::whereIn(
                "subjectID",
                $assignment->subject_ids,
            )
                ->pluck("subject_name")
                ->toArray();
            $assignment->subject_names = implode(", ", $subjectNames);
        }

        return response()->json([
            "success" => true,
            "assignments" => $assignments,
        ]);
    }

    public function toggleAssignmentStatus(Request $request)
    {
        $assignment = TeacherResultAssignment::findOrFail($request->id);
        $assignment->status = !$assignment->status;
        $assignment->save();

        return response()->json([
            "success" => true,
            "status" => $assignment->status,
            "message" => "Status updated successfully.",
        ]);
    }

    public function deleteAssignment(Request $request)
    {
        $assignment = TeacherResultAssignment::findOrFail($request->id);
        $assignment->delete();

        return response()->json([
            "success" => true,
            "message" => "Assignment deleted successfully.",
        ]);
    }

    public function toggleTeacherResultPermission(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $permission = \App\Models\TeacherResultPermission::updateOrCreate(
            ["schoolID" => $schoolID],
            ["updated_at" => now()],
        );

        $permission->result_button_status = !$permission->result_button_status;
        $permission->save();

        return response()->json([
            "success" => true,
            "status" => $permission->result_button_status,
            "message" =>
                "Teacher result entry " .
                ($permission->result_button_status ? "enabled" : "disabled") .
                " globally.",
        ]);
    }

    /**
     * Resolve academic year context from header (same source as manageExamination).
     */
    private function resolveResultsAcademicYearContext(int $schoolID): array
    {
        $yearService = app(SelectedAcademicYearService::class);
        $ctx = $yearService->headerContext($schoolID);
        $yearIds = array_values(
            array_filter(array_map("intval", $ctx["selected_year_ids"] ?? [])),
        );

        $years = collect();
        $labels = [];
        $calendarYears = [];

        if (!empty($yearIds)) {
            $years = AcademicYear::where("schoolID", $schoolID)
                ->whereIn("academic_yearID", $yearIds)
                ->orderBy("academic_yearID")
                ->get();
            foreach ($years as $y) {
                $labels[] = $y->year_name ?? $y->year;
                $cal = is_numeric($y->year)
                    ? (int) $y->year
                    : (int) preg_replace(
                        "/\D.*$/",
                        "",
                        (string) ($y->year ?? date("Y")),
                    );
                if ($cal >= 2000) {
                    $calendarYears[] = $cal;
                }
            }
        }

        $primaryCal =
            $calendarYears[0] ??
            (int) date("Y");

        return [
            "academic_year_ids" => $yearIds,
            "academic_years" => $years,
            "label" => !empty($labels)
                ? implode(" / ", $labels)
                : "Year " . $primaryCal,
            "calendar_year" => $primaryCal,
            "calendar_years" => array_values(array_unique($calendarYears)),
        ];
    }

    /**
     * Filter examinations by academic_yearID (preferred) with calendar-year fallback.
     */
    private function applyExaminationAcademicYearFilter(
        $query,
        array $yearIds,
        $calendarYear = null,
    ) {
        $yearIds = array_values(array_filter(array_map("intval", $yearIds)));
        if (!empty($yearIds)) {
            $query->where(function ($q) use ($yearIds, $calendarYear) {
                $q->whereIn("academic_yearID", $yearIds);
                if ($calendarYear) {
                    $q->orWhere(function ($q2) use ($calendarYear) {
                        $q2->whereNull("academic_yearID")->where(
                            "year",
                            $calendarYear,
                        );
                    });
                }
            });
        } elseif ($calendarYear) {
            $query->where("year", $calendarYear);
        }

        return $query;
    }

    private function normalizeResultsEnrollmentStatusFilter($status): string
    {
        $status = is_string($status) ? trim($status) : "";
        $legacy = [
            "active" => "Active",
            "history" => "Transferred",
            "all" => "all",
        ];
        $lower = strtolower($status);
        if (isset($legacy[$lower])) {
            return $legacy[$lower];
        }

        $allowed = [
            "Active",
            "Completed",
            "Transferred",
            "Dropped",
            "Cancelled",
            "all",
        ];
        if (in_array($status, $allowed, true)) {
            return $status;
        }

        return "Active";
    }

    private function applyResultsEnrollmentScopeToStudentsQuery(
        $query,
        int $schoolID,
        array $yearIds,
        $classID,
        $subclassID,
        string $enrollmentStatus,
    ): void {
        $yearIds = array_values(array_filter(array_map("intval", $yearIds)));

        $query->whereExists(function ($q) use (
            $schoolID,
            $yearIds,
            $classID,
            $subclassID,
            $enrollmentStatus,
        ) {
            $q->select(DB::raw(1))
                ->from("student_enrollments")
                ->whereColumn(
                    "student_enrollments.studentID",
                    "students.studentID",
                )
                ->where("student_enrollments.schoolID", $schoolID);

            if ($enrollmentStatus !== "all") {
                $q->where("student_enrollments.status", $enrollmentStatus);
            }

            if (!empty($yearIds)) {
                $q->whereIn(
                    "student_enrollments.academic_yearID",
                    $yearIds,
                );
            }

            if (!empty($subclassID)) {
                $q->where(
                    "student_enrollments.subclassID",
                    (int) $subclassID,
                );
            } elseif (!empty($classID)) {
                $q->where("student_enrollments.classID", (int) $classID);
            }
        });
    }

    private function attachEnrollmentMetaToStudents(
        $students,
        int $schoolID,
        array $yearIds,
        string $enrollmentStatus,
    ): void {
        if (!$students || $students->isEmpty()) {
            return;
        }

        $ids = $students->pluck("studentID")->filter()->all();
        if (empty($ids)) {
            return;
        }

        $q = StudentEnrollment::where("schoolID", $schoolID)->whereIn(
            "studentID",
            $ids,
        );
        $yearIds = array_values(array_filter(array_map("intval", $yearIds)));
        if (!empty($yearIds)) {
            $q->whereIn("academic_yearID", $yearIds);
        }
        if ($enrollmentStatus !== "all") {
            $q->where("status", $enrollmentStatus);
        }

        $map = $q
            ->orderByDesc("enrollmentID")
            ->get()
            ->unique("studentID")
            ->keyBy("studentID");

        foreach ($students as $student) {
            $enr = $map->get($student->studentID);
            $student->result_enrollmentID = $enr->enrollmentID ?? null;
            $student->enrollment_status = $enr->status ?? null;
            $student->enrollment_promotion_status =
                $enr->promotion_status ?? null;
            $student->enrollment_classID = $enr->classID ?? null;
            $student->enrollment_subclassID = $enr->subclassID ?? null;
        }
    }
}
