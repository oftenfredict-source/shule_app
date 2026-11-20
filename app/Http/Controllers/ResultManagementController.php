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
use App\Models\Payment;
use App\Services\SmsService;
use App\Services\SelectedAcademicYearService;
use App\Services\StudentEnrollmentService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Support\CenturyGothicPdfFont;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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
        if ($this->normalizeResultsSubclassFilter($subclassFilter) === null) {
            $fromSubclassIdParam = $this->normalizeResultsSubclassFilter(
                $subclassIDParam,
            );
            if ($fromSubclassIdParam !== null) {
                $subclassFilter = (string) $fromSubclassIdParam;
            }
        }
        $classIDParam = $request->input("classID", ""); // From coordinator view
        $isCoordinatorView = $request->input("coordinator") === "true";
        $examFilter = $request->input("examID", ""); // Specific exam filter
        $weekFilter = $request->input("week", ""); // Week filter for weekly/monthly tests
        $subjectFilter = $request->input("subjectID", ""); // Specific subject filter
        $rankBy = strtolower(trim((string) $request->input("rank_by", "average")));
        if (!in_array($rankBy, ["average", "division"], true)) {
            $rankBy = "average";
        }
        // Primary schools always rank by average
        if ($schoolType !== "Secondary") {
            $rankBy = "average";
        }

        // Academic year always from header selection (like manageExamination) - no page year picker
        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        // Expand sibling AYs (e.g. 2025/2026 id + plain 2026 id) so enrollments & exams still match
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        $yearFilter = $yearCtx["calendar_year"];
        $calendarYears = !empty($yearCtx["calendar_years"])
            ? $yearCtx["calendar_years"]
            : ($yearFilter ? [(int) $yearFilter] : []);
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
        if ($isCoordinatorView && !empty($teacherID)) {
            $resolvedCoordinatorClassID = $this->resolveResultsClassId(
                $classIDParam,
            );
            if ($resolvedCoordinatorClassID) {
                $mainClass = ClassModel::find($resolvedCoordinatorClassID);
                if (
                    $mainClass &&
                    ((int) $mainClass->teacherID === (int) $teacherID ||
                        $hasBroadResultPermission) &&
                    (int) $mainClass->schoolID === (int) $schoolID
                ) {
                    $classFilter = (string) $resolvedCoordinatorClassID;
                    $classIDParam = (string) $resolvedCoordinatorClassID;
                    $isCoordinatorResultsView = true;
                } elseif ($userType === "Teacher" && !$hasBroadResultPermission) {
                    return redirect()
                        ->route("AdmitedClasses", ["coordinator" => "true"])
                        ->with("error", "Unauthorized access to this class")
                        ->with("error_type", "unauthorized_access");
                }
            } elseif ($userType === "Teacher" && !$hasBroadResultPermission) {
                return redirect()
                    ->route("AdmitedClasses", ["coordinator" => "true"])
                    ->with("error", "Please select a class to view results")
                    ->with("error_type", "no_class_selected");
            }
        }

        // If subclassID is provided (from classManagement), verify teacher access and set defaults
        $isTeacherView = false;
        if (!empty($subclassIDParam) && !$isCoordinatorResultsView) {
            $subclass = Subclass::with("class")->find($subclassIDParam);
            $isAssignedClassTeacher =
                $subclass &&
                !empty($teacherID) &&
                (int) $subclass->teacherID === (int) $teacherID;

            // Class teacher arriving from Class Management: always lock to THAT stream,
            // even if they also have school-wide result permissions.
            if ($isAssignedClassTeacher) {
                $subclassFilter = (string) $subclass->subclassID;
                $classFilter = $subclass->classID;
                $isTeacherView = true;
            } elseif (
                $subclass &&
                ($hasBroadResultPermission || $userType === "Admin")
            ) {
                $subclassFilter = (string) $subclass->subclassID;
                $classFilter = $subclass->classID;
            } elseif ($userType === "Teacher") {
                return redirect()
                    ->route("AdmitedClasses")
                    ->with("error", "Unauthorized access to this class")
                    ->with("error_type", "unauthorized_access");
            }
        }

        // SECURITY: Class-teacher view (or teachers without school-wide access)
        // must stay on their assigned subclass.
        // Coordinators are assigned to a MAIN class (all streams), not a subclass.
        if (
            $userType === "Teacher" &&
            !empty($teacherID) &&
            !$isCoordinatorResultsView &&
            ($isTeacherView || !$hasBroadResultPermission)
        ) {
            // If subclassID param is provided, use it
            if (!empty($subclassIDParam)) {
                $subclass = Subclass::with("class")->find($subclassIDParam);
                if (
                    $subclass &&
                    (int) $subclass->teacherID === (int) $teacherID
                ) {
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
                if ((int) $subclass->teacherID !== (int) $teacherID) {
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
        $classesQuery = ClassModel::where("schoolID", $schoolID)->where(
            "status",
            "Active",
        );
        if (($isTeacherView || $isCoordinatorResultsView) && $classFilter) {
            $classesQuery->where("classID", $classFilter);
        }
        $classes = $classesQuery->orderBy("class_name")->get();

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
            !empty($teacherID) &&
            !$isCoordinatorResultsView &&
            ($isTeacherView ||
                ($userType === "Teacher" && !$hasBroadResultPermission))
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
        // A-Level (Form Five/Six): subclass only (e.g. "A") - Main Class already selected
        // O-Level: keep "FORM ONE A" style
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
                    $displayName = $this->formatSubclassFilterDisplayName(
                        $subclass->class->class_name ?? "",
                        $subclass->subclass_name ?? "",
                        $isCoordinatorResultsView,
                    );
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
                // Multiple subclasses
                foreach ($classSubclasses as $subclass) {
                    $displayName = $this->formatSubclassFilterDisplayName(
                        $subclass->class->class_name ?? "",
                        $subclass->subclass_name ?? "",
                        $isCoordinatorResultsView,
                    );

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
            try {
                $exam = Examination::find($examFilter);
                if (!$exam) {
                    return response()->json(["error" => "Exam not found"], 404);
                }

                $classIDFilter =
                    $request->input("classID", "") ?: $classFilter;
                $subclassIDFilter =
                    $request->input("subclassID", "") ?: $subclassFilter;
                $classIDFilter = $this->normalizeResultsSubclassFilter(
                    $classIDFilter,
                );
                $subclassIDFilter = $this->normalizeResultsSubclassFilter(
                    $subclassIDFilter,
                );

                $studentsQuery = DB::table("students")
                    ->where("schoolID", $schoolID)
                    ->where("status", "Active")
                    ->select(
                        "studentID",
                        "subclassID",
                        "first_name",
                        "last_name",
                        "admission_number",
                    );

                $enrollmentSvc = app(StudentEnrollmentService::class);
                if ($subclassIDFilter) {
                    $enrolledIds = $enrollmentSvc->studentIDsInSubclass(
                        (int) $subclassIDFilter,
                        (int) $schoolID,
                        "Active",
                    );
                    if (!empty($enrolledIds)) {
                        $studentsQuery->whereIn("studentID", $enrolledIds);
                    } else {
                        $studentsQuery->where(function ($q) use (
                            $subclassIDFilter,
                        ) {
                            $q->where("subclassID", $subclassIDFilter)->orWhere(
                                "old_subclassID",
                                $subclassIDFilter,
                            );
                        });
                    }
                } elseif ($classIDFilter) {
                    $enrolledIds = $enrollmentSvc->studentIDsInClass(
                        (int) $classIDFilter,
                        (int) $schoolID,
                        "Active",
                    );
                    if (!empty($enrolledIds)) {
                        $studentsQuery->whereIn("studentID", $enrolledIds);
                    }
                }

                $studentsBySubclass = $studentsQuery
                    ->get()
                    ->groupBy("subclassID");

                $completedMarksBySubject = DB::table("results")
                    ->where("examID", $examFilter)
                    ->whereNotNull("marks")
                    ->select("studentID", "class_subjectID")
                    ->get()
                    ->groupBy("class_subjectID")
                    ->map(function ($rows) {
                        return $rows->pluck("studentID")->toArray();
                    });

                $classSubjects = ClassSubject::where("status", "Active")
                    ->whereHas("subclass.class", function ($q) use ($schoolID) {
                        $q->where("schoolID", $schoolID);
                    })
                    ->when($classIDFilter, function ($query) use (
                        $classIDFilter,
                    ) {
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

                $optionalCidIds = $classSubjects
                    ->filter(function ($cs) {
                        return strtolower($cs->student_status ?? "") ===
                            "optional";
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

                    $expectedStudents = collect(
                        $studentsBySubclass->get($cs->subclassID) ?:
                        $studentsBySubclass->get((string) $cs->subclassID) ?:
                        [],
                    );
                    if ($expectedStudents->isEmpty() && $subclassIDFilter) {
                        $expectedStudents = $studentsBySubclass
                            ->flatten(1)
                            ->values();
                    }
                    if ($expectedStudents->isEmpty()) {
                        continue;
                    }

                    if (strtolower($cs->student_status ?? "") === "optional") {
                        $electedIds = $electorsBySubject->get(
                            $cs->class_subjectID,
                            [],
                        );
                        $expectedStudents = $expectedStudents->filter(
                            function ($st) use ($electedIds) {
                                return in_array(
                                    $st->studentID,
                                    $electedIds,
                                    false,
                                );
                            },
                        );
                    }

                    if ($expectedStudents->isEmpty()) {
                        continue;
                    }

                    $completedIds =
                        $completedMarksBySubject->get($cs->class_subjectID) ?:
                        [];

                    $missing = $expectedStudents->filter(function ($st) use (
                        $completedIds,
                    ) {
                        return !in_array($st->studentID, $completedIds, false);
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
                            "students" => $missing
                                ->values()
                                ->take(150)
                                ->toArray(),
                        ];
                    }
                }

                return response()->json([
                    "exam_name" => $exam->exam_name,
                    "incomplete_count" => count($incompleteData),
                    "data" => array_values($incompleteData),
                ]);
            } catch (\Throwable $e) {
                \Log::warning(
                    "Incomplete results analysis failed: " . $e->getMessage(),
                );

                return response()->json(
                    [
                        "error" => "Analysis failed",
                        "exam_name" => "Current Exam",
                        "incomplete_count" => 0,
                        "data" => [],
                    ],
                    500,
                );
            }
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
                    "subclass.combie.subjects",
                    "subclass.teacher",
                    "oldSubclass.class",
                    "oldSubclass.combie.subjects",
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

            // Get class name and classID early (needed for A/O level definition lookup)
            $className = "";
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? "";
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className = $student->oldSubclass->class->class_name ?? "";
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            // Get results for this student
            $reportDefinition = null;
            $reportUsePercent = false;
            $reportExamWeights = [];
            if ($typeFilterForDetails === "report") {
                // For term report: get only exams defined in TermReportDefinition if available
                $reportDefinition = $this->resolveTermReportDefinition(
                    $schoolID,
                    $yearFilter,
                    $termFilter,
                    $className,
                );
                $reportUsePercent = (bool) ($reportDefinition?->use_percent_division ?? false);
                $reportExamWeights = is_array($reportDefinition?->exam_weights ?? null)
                    ? $reportDefinition->exam_weights
                    : [];

                $examinationsQuery = Examination::where("schoolID", $schoolID)
                    ->where("year", $yearFilter)
                    ->where("term", $termFilter);

                if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
                    $examinationsQuery->whereIn(
                        "examID",
                        $reportDefinition->exam_ids,
                    );
                }

                $examinations = $examinationsQuery->get();
                if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
                    $examinations = $this->orderModelsByIdList(
                        $examinations,
                        (array) $reportDefinition->exam_ids,
                    );
                } else {
                    $examinations = $examinations
                        ->sortBy("start_date")
                        ->values();
                }
                $examIDs = $examinations->pluck("examID")->toArray();

                $results = Result::where("studentID", $studentIDFilter)
                    ->whereIn("examID", $examIDs)
                    ->with(["classSubject.subject.components", "examination"])
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

                $results = $resultsQuery->with(["classSubject.subject.components"])->get();
            }

            $electedClassSubjectIds = $this->getElectedClassSubjectIdsForStudent(
                $studentIDFilter,
            );
            $results = $this->filterResultsForStudentElection(
                $results,
                $studentIDFilter,
                $electedClassSubjectIds,
            );

            // Process results differently for exam vs term report
            $examsData = []; // Initialize outside if block for use in response
            $caDefinition = null;
            if ($typeFilterForDetails === "exam" && $examFilter) {
                $caDefinition = $this->resolveCaDefinition(
                    $schoolID,
                    $examFilter,
                    $className,
                );
            }

            if ($typeFilterForDetails === "report") {
                // For term report: group by subject and collect exam results
                $subjectData = [];

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

                $examinations = $examinationsQuery->get();
                if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
                    $examinations = $this->orderModelsByIdList(
                        $examinations,
                        (array) $reportDefinition->exam_ids,
                    );
                } else {
                    $examinations = $examinations
                        ->sortBy("start_date")
                        ->values();
                }

                foreach ($examinations as $exam) {
                    $examsData[$exam->examID] = [
                        "examID" => $exam->examID,
                        "exam_name" => $this->formatExamLabelWithWeight(
                            $exam->exam_name,
                            $exam->examID,
                            $reportExamWeights,
                            $reportUsePercent,
                        ),
                        "start_date" => $exam->start_date,
                        "weight_percent" => $reportUsePercent
                            ? ($reportExamWeights[$exam->examID] ??
                                $reportExamWeights[(string) $exam->examID] ??
                                null)
                            : null,
                    ];
                }

                // CA defs kept for compatibility with helpers; averages use column marks only
                $selectedExamIDs = $examinations->pluck("examID")->toArray();
                $caDefinitionsByExam = CaDefinition::where(
                    "schoolID",
                    $schoolID,
                )
                    ->whereIn("examID", $selectedExamIDs)
                    ->get()
                    ->keyBy("examID");

                // Group by subjectID; duo papers → one % per exam (not Paper 1 / Paper 2)
                foreach ($results as $result) {
                    $subject = $result->classSubject->subject ?? null;
                    if (!$subject) {
                        continue;
                    }
                    $subjectID = (int) $subject->subjectID;
                    $subjectName = $subject->subject_name ?? "N/A";
                    $examID = $result->examID;

                    if (!isset($subjectData[$subjectID])) {
                        if (
                            ($subject->is_duo ?? false) &&
                            !$subject->relationLoaded("components")
                        ) {
                            $subject->load("components");
                        }
                        $subjectData[$subjectID] = [
                            "subject" => $subject,
                            "subject_name" => $subjectName,
                            "class_subjectID" =>
                                (int) ($result->class_subjectID ?? 0),
                            "student_status" =>
                                $result->classSubject->student_status ?? null,
                            "is_optional" => $this->isOptionalStudentStatus(
                                $result->classSubject->student_status ?? null,
                            ),
                            "exams" => [],
                            "paper_rows" => [],
                        ];
                    }

                    if ($result->marks !== null && $result->marks !== "") {
                        $isDuo =
                            (bool) ($subject->is_duo ?? false) &&
                            ($subject->components?->count() ?? 0) > 0;
                        if ($isDuo) {
                            if (
                                !isset(
                                    $subjectData[$subjectID]["paper_rows"][
                                        $examID
                                    ],
                                )
                            ) {
                                $subjectData[$subjectID]["paper_rows"][
                                    $examID
                                ] = [];
                            }
                            $subjectData[$subjectID]["paper_rows"][$examID][] =
                                $result;
                        } else {
                            $marks = (float) $result->marks;
                            $gradePoints = $this->calculateGradePoints(
                                $marks,
                                $schoolType,
                                $className,
                                $classID,
                            );
                            $examName =
                                $examsData[$examID]["exam_name"] ??
                                ($result->examination->exam_name ?? "N/A");
                            $subjectData[$subjectID]["exams"][$examID] = [
                                "marks" => $marks,
                                "grade" =>
                                    $result->grade ?? $gradePoints["grade"],
                                "exam_name" => $examName,
                            ];
                        }
                    }
                }

                foreach ($subjectData as $subjectID => &$data) {
                    $subject = $data["subject"];
                    $isDuo =
                        (bool) ($subject->is_duo ?? false) &&
                        ($subject->components?->count() ?? 0) > 0;
                    if (!$isDuo) {
                        continue;
                    }
                    foreach ($data["paper_rows"] as $examID => $rows) {
                        $raw = 0.0;
                        $max = 0;
                        $any = false;
                        $papers = [];
                        foreach ($subject->components as $comp) {
                            $compMax = max(1, (int) $comp->max_marks);
                            $max += $compMax;
                            $row = collect($rows)->first(
                                fn($r) => (int) ($r->componentID ?? 0) ===
                                    (int) $comp->componentID,
                            );
                            $paperMarks =
                                $row &&
                                $row->marks !== null &&
                                $row->marks !== ""
                                    ? (float) $row->marks
                                    : null;
                            if ($paperMarks !== null) {
                                $raw += $paperMarks;
                                $any = true;
                            }
                            $papers[] = [
                                "componentID" => (int) $comp->componentID,
                                "component_name" =>
                                    $comp->component_name ?? "Paper",
                                "marks" => $paperMarks,
                                "max_marks" => $compMax,
                            ];
                        }
                        if ($any && $max > 0) {
                            $marks = round(($raw / $max) * 100, 2);
                            $gradePoints = $this->calculateGradePoints(
                                $marks,
                                $schoolType,
                                $className,
                                $classID,
                            );
                            $data["exams"][$examID] = [
                                "marks" => $marks,
                                "grade" => $gradePoints["grade"],
                                "exam_name" =>
                                    $examsData[$examID]["exam_name"] ?? "N/A",
                                "papers" => $papers,
                            ];
                        }
                    }
                }
                unset($data);

                // Subject average = mean of marks across term-report exam columns
                $subjects = [];
                $totalMarks = 0;
                $subjectCount = 0;

                foreach ($subjectData as $subjectID => $data) {
                    $subjectName = $data["subject_name"];
                    $slotCollect = $this->collectTermReportSlotMarksForSubject(
                        $data,
                        $examinations,
                        $caDefinitionsByExam,
                        $reportUsePercent,
                        $reportExamWeights,
                    );
                    $subjectIncomplete = $slotCollect["is_incomplete"];

                    if ($subjectIncomplete) {
                        $subjects[] = [
                            "subject_name" => $subjectName,
                            "subjectID" => $subjectID,
                            "class_subjectID" =>
                                (int) ($data["class_subjectID"] ?? 0),
                            "student_status" => $data["student_status"] ?? null,
                            "is_optional" => !empty($data["is_optional"]),
                            "exams" => $data["exams"],
                            "average" => null,
                            "grade" => "Incomplete",
                            "is_term_report" => true,
                            "is_incomplete" => true,
                            "is_duo" => (bool) ($data["subject"]->is_duo ?? false),
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
                        "subjectID" => $subjectID,
                        "class_subjectID" =>
                            (int) ($data["class_subjectID"] ?? 0),
                        "student_status" => $data["student_status"] ?? null,
                        "is_optional" => !empty($data["is_optional"]),
                        "exams" => $data["exams"],
                        "average" => $average,
                        "grade" => $overallGrade,
                        "is_term_report" => true,
                        "is_incomplete" => false,
                        "is_duo" => (bool) ($data["subject"]->is_duo ?? false),
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

                    $collapsedExam = $this->collapseResultsToSubjectMarks(
                        $examResults,
                    );
                    $defaultFailPoints = $this->isALevelClassName($className)
                        ? 7
                        : 5;

                    foreach ($collapsedExam as $entry) {
                        if ($entry["marks"] !== null && $entry["marks"] !== "") {
                            $marks = (float) $entry["marks"];
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
                                    "points" =>
                                        $gradePoints["points"] ??
                                        $defaultFailPoints,
                                    "marks" => $marks,
                                    "grade" => $gradePoints["grade"] ?? null,
                                    "subjectID" => $entry["subjectID"],
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
                                    $this->getCombinationSubjectIdsForStudent(
                                        $student,
                                    ),
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
                $allInvolvedExams = array_values(
                    array_unique(
                        array_merge(
                            [(int) $examFilter],
                            array_map(
                                "intval",
                                (array) ($caDefinition->test_ids ?? []),
                            ),
                        ),
                    ),
                );
                $useCaPercent = (bool) ($caDefinition->use_percent_division ?? false);
                $caWeights = is_array($caDefinition->exam_weights ?? null)
                    ? $caDefinition->exam_weights
                    : [];
                $involvedExams = $this->orderModelsByIdList(
                    Examination::whereIn("examID", $allInvolvedExams)->get(),
                    $allInvolvedExams,
                )->keyBy("examID");

                $caResults = Result::where("studentID", $studentIDFilter)
                    ->whereIn("examID", $allInvolvedExams)
                    ->with(["classSubject.subject.components", "examination"])
                    ->get();
                $caResults = $this->filterResultsForStudentElection(
                    $caResults,
                    $studentIDFilter,
                    $electedClassSubjectIds,
                );

                // Collapse duo papers per exam first, then average (one total per subject)
                $rawBySubject = [];
                foreach ($caResults as $result) {
                    $subject = $result->classSubject->subject ?? null;
                    if (!$subject) {
                        continue;
                    }
                    $subjectID = (int) $subject->subjectID;
                    if (!isset($rawBySubject[$subjectID])) {
                        if (
                            ($subject->is_duo ?? false) &&
                            !$subject->relationLoaded("components")
                        ) {
                            $subject->load("components");
                        }
                        $rawBySubject[$subjectID] = [
                            "subject" => $subject,
                            "subject_name" => $subject->subject_name ?? "N/A",
                            "by_exam" => [],
                        ];
                    }
                    if ($result->marks === null || $result->marks === "") {
                        continue;
                    }
                    $examID = $result->examID;
                    if (!isset($rawBySubject[$subjectID]["by_exam"][$examID])) {
                        $rawBySubject[$subjectID]["by_exam"][$examID] = collect();
                    }
                    $rawBySubject[$subjectID]["by_exam"][$examID]->push($result);
                }

                $subjectData = [];
                foreach ($rawBySubject as $subjectID => $bundle) {
                    $subject = $bundle["subject"];
                    $subjectName = $bundle["subject_name"];
                    $subjectData[$subjectID] = [
                        "subject_name" => $subjectName,
                        "exams" => [],
                        "marks_by_exam" => [],
                        "marks_sum" => 0,
                        "marks_count" => 0,
                        "is_duo" => (bool) ($subject->is_duo ?? false),
                    ];

                    foreach ($allInvolvedExams as $examID) {
                        if (!isset($bundle["by_exam"][$examID])) {
                            continue;
                        }
                        $examRows = $bundle["by_exam"][$examID];
                        $collapsed = $this->collapseResultsToSubjectMarks(
                            $examRows,
                        );
                        $entry = $collapsed[0] ?? null;
                        if (
                            !$entry ||
                            $entry["marks"] === null ||
                            $entry["marks"] === ""
                        ) {
                            continue;
                        }
                        $marks = (float) $entry["marks"];
                        $gradePoints = $this->calculateGradePoints(
                            $marks,
                            $schoolType,
                            $className,
                            $classID,
                        );
                        $examModel = $involvedExams[$examID] ?? null;
                        $examName = $examModel
                            ? $examModel->exam_name
                            : "Unknown";
                        if ($examModel && $examModel->exam_category === "test") {
                            $examName .= " (CA)";
                        }
                        $examName = $this->formatExamLabelWithWeight(
                            $examName,
                            $examID,
                            $caWeights,
                            $useCaPercent,
                        );
                        $subjectData[$subjectID]["exams"][] = [
                            "marks" => $marks,
                            "grade" => $gradePoints["grade"],
                            "exam_name" => $examName,
                            "papers" => $entry["papers"] ?? [],
                        ];
                        $subjectData[$subjectID]["marks_by_exam"][$examID] = $marks;
                        $subjectData[$subjectID]["marks_sum"] += $marks;
                        $subjectData[$subjectID]["marks_count"]++;
                    }
                }

                $subjects = [];
                $totalMarks = 0;
                $subjectCount = 0;
                $subjectPoints = [];

                foreach ($subjectData as $subjectID => $data) {
                    if ($data["marks_count"] <= 0) {
                        continue;
                    }
                    if ($useCaPercent && !empty($caWeights)) {
                        $average = round(
                            $this->computePercentWeightedTotal(
                                $data["marks_by_exam"],
                                $caWeights,
                            ),
                            2,
                        );
                    } else {
                        $divisor =
                            count($allInvolvedExams) > 0
                                ? count($allInvolvedExams)
                                : $data["marks_count"];
                        $average = round($data["marks_sum"] / max(1, $divisor), 2);
                    }
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
                        $overallGrade = $gradeResult
                            ? $gradeResult["grade"]
                            : null;
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
                        "subject_name" => $data["subject_name"],
                        "subjectID" => $subjectID,
                        "exams" => $data["exams"],
                        "marks" => $average,
                        "grade" => $overallGrade,
                        "is_ca_averaged" => true,
                        "is_duo" => $data["is_duo"] ?? false,
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
                            "points" =>
                                $avgGradePoints["points"] ??
                                ($this->isALevelClassName($className) ? 7 : 5),
                            "marks" => $average,
                            "subjectID" => $subjectID,
                        ];
                    }
                }
                $averageMarks =
                    $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            } else {
                // For exam: collapse duo papers → % per subject
                $totalMarks = 0;
                $subjectCount = 0;
                $subjects = [];
                $subjectPoints = [];

                $collapsed = $this->collapseResultsToSubjectMarks($results);
                $defaultFailPoints = $this->isALevelClassName($className) ? 7 : 5;

                foreach ($collapsed as $entry) {
                    $marks = $entry["marks"];
                    if ($marks !== null && $marks !== "") {
                        $totalMarks += (float) $marks;
                        $subjectCount++;
                    }
                    $gradePoints = $this->calculateGradePoints(
                        $marks,
                        $schoolType,
                        $className,
                        $classID,
                    );
                    $subjects[] = [
                        "subject_name" => $entry["subject_name"],
                        "subjectID" => $entry["subjectID"],
                        "class_subjectID" =>
                            (int) ($entry["class_subjectID"] ?? 0),
                        "student_status" => $entry["student_status"] ?? null,
                        "is_optional" => !empty($entry["is_optional"]),
                        "marks" => $marks,
                        "grade" => $gradePoints["grade"],
                        "is_duo" => $entry["is_duo"],
                        "papers" => $entry["papers"] ?? [],
                    ];

                    if (
                        $schoolType === "Secondary" &&
                        $marks !== null &&
                        $marks !== ""
                    ) {
                        $subjectPoints[] = [
                            "points" =>
                                $gradePoints["points"] ?? $defaultFailPoints,
                            "marks" => (float) $marks,
                            "grade" => $gradePoints["grade"] ?? null,
                            "subjectID" => $entry["subjectID"],
                        ];
                    }
                }

                $averageMarks =
                    $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
            }

            // NECTA division: term report uses column averages (same as bulk list / PDF)
            $allSubjectGrades = [];
            if ($typeFilterForDetails === "report") {
                $subjectPoints = $this->buildTermReportSubjectPointEntries(
                    $results,
                    $examinations,
                    $caDefinitionsByExam,
                    $schoolType,
                    $className,
                    $studentIDFilter,
                    $electedClassSubjectIds,
                    $reportUsePercent,
                    $reportExamWeights,
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
            $combinationSubjectIds = $this->getCombinationSubjectIdsForStudent(
                $student,
            );
            if ($schoolType === "Secondary" && !empty($subjectPoints)) {
                $divisionPick = $this->resolveSecondaryBestSubjectsForDivision(
                    $subjectPoints,
                    $className,
                    $schoolType,
                    $combinationSubjectIds,
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

            // Nafasi: mchepuo = subclass/stream; kidato = whole main class
            // A-Level processing: primary position = combination/subclass; Kidato still shown (same report structure as O-Level)
            $totalStudentsCount = 0;
            $studentPosition = 0;
            $subclassPosition = null;
            $subclassTotal = 0;
            $classPosition = null;
            $classTotal = 0;

            // Get student's class / stream IDs
            $studentClassID = null;
            $studentSubclassID =
                $student->subclassID ?? ($student->old_subclassID ?? null);
            if ($student->subclass && $student->subclass->class) {
                $studentClassID = $student->subclass->class->classID;
                $studentSubclassID =
                    $studentSubclassID ?: $student->subclass->subclassID;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $studentClassID = $student->oldSubclass->class->classID;
                $studentSubclassID =
                    $studentSubclassID ?: $student->oldSubclass->subclassID;
            }

            if ($studentClassID || $studentSubclassID) {
                $enrollmentSvc = app(StudentEnrollmentService::class);

                // Same pool structure for A & O: load main class, then derive mchepuo + kidato
                $classStudentsQuery = Student::where("schoolID", $schoolID)
                    ->where("status", "Active")
                    ->with(["subclass.class", "oldSubclass.class"]);

                if ($studentClassID) {
                    $enrolledInClass = $enrollmentSvc->studentIDsInClass(
                        (int) $studentClassID,
                        (int) $schoolID,
                        "Active",
                    );
                    if (!empty($enrolledInClass)) {
                        $classStudentsQuery->whereIn(
                            "studentID",
                            $enrolledInClass,
                        );
                    } else {
                        $classStudentsQuery->where(function ($query) use (
                            $studentClassID,
                        ) {
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
                        });
                    }
                } else {
                    $classStudentsQuery->where(function ($query) use (
                        $studentSubclassID,
                    ) {
                        $query
                            ->where("subclassID", $studentSubclassID)
                            ->orWhere("old_subclassID", $studentSubclassID);
                    });
                }

                $classStudents = $classStudentsQuery->get();

                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if (
                    $typeFilterForDetails !== "report" &&
                    $examFilter &&
                    !is_array($examFilter)
                ) {
                    $caDef = $this->resolveCaDefinition(
                        $schoolID,
                        $examFilter,
                        null,
                    );
                    if ($caDef && !empty($caDef->test_ids)) {
                        $caExamIDs = array_merge(
                            [$examFilter],
                            $caDef->test_ids,
                        );
                    }
                }

                $positionYearFilter = !empty($calendarYears)
                    ? $calendarYears
                    : $yearFilter;

                if ($typeFilterForDetails === "report") {
                    $classResultsData = $this->getTermReport(
                        $classStudents,
                        $termFilter,
                        $positionYearFilter,
                        $schoolType,
                        null,
                        $schoolID,
                        $academicYearIDs,
                    );
                } else {
                    $classResultsData = $this->getExamResults(
                        $classStudents,
                        $termFilter,
                        $positionYearFilter,
                        $schoolType,
                        $caExamIDs,
                        $weekFilter ?: null,
                        null,
                        $schoolID,
                        $academicYearIDs,
                    );
                }

                $classStudentsWithResults = [];
                foreach ($classStudents as $classStudent) {
                    if (!isset($classResultsData[$classStudent->studentID])) {
                        continue;
                    }
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
                            "division" =>
                                $studentResultData["division"] ?? null,
                            "grade" => $studentResultData["grade"] ?? null,
                            "subjects" => $studentResultData["subjects"] ?? [],
                            "is_all_incomplete" => !empty(
                                $studentResultData["is_all_incomplete"]
                            ),
                        ];
                    } else {
                        foreach ($studentResultData as $er) {
                            if (
                                isset($er["exam"]) &&
                                (int) ($er["exam"]->examID ?? 0) ===
                                    (int) $examFilter
                            ) {
                                $classStudentsWithResults[] = [
                                    "student" => $classStudent,
                                    "total_marks" => $er["total_marks"] ?? 0,
                                    "average_marks" =>
                                        $er["average_marks"] ?? 0,
                                    "total_points" =>
                                        $er["total_points"] ?? 9999,
                                    "best_seven_total_marks" =>
                                        $er["best_seven_total_marks"] ?? 0,
                                    "division" => $er["division"] ?? null,
                                    "grade" => $er["grade"] ?? null,
                                    "subjects" => $er["subjects"] ?? [],
                                    "is_all_incomplete" => !empty(
                                        $er["is_all_incomplete"]
                                    ),
                                ];
                                break;
                            }
                        }
                    }
                }

                $sortRankList = function (array &$list) use (
                    $typeFilterForDetails,
                    $schoolType,
                    $rankBy,
                ) {
                    $useDivision =
                        $rankBy === "division" && $schoolType === "Secondary";
                    $reportDivisionStyle =
                        $useDivision && $typeFilterForDetails === "report";

                    usort($list, function ($a, $b) use (
                        $rankBy,
                        $schoolType,
                        $reportDivisionStyle,
                    ) {
                        return $this->compareStudentsForPosition(
                            $a,
                            $b,
                            $rankBy === "division" ? "division" : "average",
                            $schoolType,
                            $reportDivisionStyle,
                        );
                    });
                };

                $findRank = function (array $list, $sid) {
                    foreach ($list as $index => $row) {
                        if (
                            (int) ($row["student"]->studentID ?? 0) ===
                            (int) $sid
                        ) {
                            return $index + 1;
                        }
                    }
                    return null;
                };

                // Kidato = whole main class
                $kidatoList = $classStudentsWithResults;
                $sortRankList($kidatoList);
                $classTotal = count($kidatoList);
                $classPosition = $findRank($kidatoList, $studentIDFilter);

                $filteredSubclassId = $this->normalizeResultsSubclassFilter(
                    $subclassFilter,
                );
                $streamIdForMchepuo = $filteredSubclassId
                    ?: ($studentSubclassID ? (int) $studentSubclassID : null);

                // Mchepuo = same subclass/stream (A-Level = combination e.g. PCM)
                $mchepuoList = $classStudentsWithResults;
                if ($streamIdForMchepuo) {
                    $streamStudentIds = $enrollmentSvc->studentIDsInSubclass(
                        (int) $streamIdForMchepuo,
                        (int) $schoolID,
                        "Active",
                    );
                    if (!empty($streamStudentIds)) {
                        $streamIdSet = array_fill_keys($streamStudentIds, true);
                        $mchepuoList = array_values(
                            array_filter(
                                $classStudentsWithResults,
                                function ($row) use ($streamIdSet) {
                                    $id = (int) ($row["student"]->studentID ?? 0);
                                    return isset($streamIdSet[$id]);
                                },
                            ),
                        );
                    } else {
                        $mchepuoList = array_values(
                            array_filter(
                                $classStudentsWithResults,
                                function ($row) use ($streamIdForMchepuo) {
                                    return $this->studentResultStreamId(
                                        $row["student"],
                                    ) === (int) $streamIdForMchepuo;
                                },
                            ),
                        );
                    }
                }
                $sortRankList($mchepuoList);
                $subclassTotal = count($mchepuoList);
                $subclassPosition = $findRank($mchepuoList, $studentIDFilter);

                // Board filter drives the headline Position:
                // All subclasses → whole main class (all streams);
                // a specific stream → that stream only.
                if ($filteredSubclassId) {
                    $studentPosition = $subclassPosition ?? ($classPosition ?? 0);
                    $totalStudentsCount =
                        $subclassTotal > 0 ? $subclassTotal : $classTotal;
                } else {
                    $studentPosition = $classPosition ?? ($subclassPosition ?? 0);
                    $totalStudentsCount =
                        $classTotal > 0 ? $classTotal : $subclassTotal;
                }
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

            $studentSubclassIDForElection =
                $student->subclassID ??
                ($student->old_subclassID ??
                    ($student->subclass->subclassID ??
                        ($student->oldSubclass->subclassID ?? null)));

            $subjects = $this->filterSubjectPayloadForStudentElection(
                $subjects,
                (int) $studentIDFilter,
                $studentSubclassIDForElection
                    ? (int) $studentSubclassIDForElection
                    : null,
                $electedClassSubjectIds,
            );
            $subjectCount = count($subjects);
            // Recalculate totals after dropping unelected election subjects
            $totalMarks = 0;
            $scoredCount = 0;
            foreach ($subjects as $s) {
                if (!empty($s["is_incomplete"])) {
                    continue;
                }
                if (isset($s["average"]) && $s["average"] !== null && $s["average"] !== "") {
                    $totalMarks += (float) $s["average"];
                    $scoredCount++;
                } elseif (isset($s["marks"]) && $s["marks"] !== null && $s["marks"] !== "" && $s["marks"] !== "incomplete") {
                    $totalMarks += (float) $s["marks"];
                    $scoredCount++;
                }
            }
            $averageMarks = $scoredCount > 0 ? $totalMarks / $scoredCount : 0;
            $subjectCount = $scoredCount > 0 ? $scoredCount : count($subjects);

            $responseData = [
                "subjects" => $subjects,
                "schoolType" => $schoolType,
                "isALevel" => $this->isALevelClassName($className),
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
                "totalPoints" => $totalPoints,
                "grade" =>
                    $typeFilterForDetails === "report" &&
                    $schoolType === "Secondary"
                        ? ($gradeDivision["division"] ?? $calculatedOverallGrade)
                        : $calculatedOverallGrade,
                "gradeLetter" => $calculatedOverallGrade,
                "division" => $gradeDivision["division"] ?? null,
                "totalStudentsCount" => $totalStudentsCount,
                "position" => $studentPosition,
                // Nafasi (mchepuo) = subclass; Nafasi (kidato) = main class
                "subclassPosition" => $subclassPosition,
                "subclassTotal" => $subclassTotal,
                "classPosition" => $classPosition,
                "classTotal" => $classTotal,
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
                !empty($calendarYears) ? $calendarYears : $yearFilter,
            );
            if ($termFilter) {
                // Keep all_terms exams visible under any term; exact match for first/second
                $examQuery->where(function ($q) use ($termFilter) {
                    $q->where("term", $termFilter)
                        ->orWhere("term", "all_terms")
                        ->orWhere("term", "all")
                        ->orWhere("term", "both")
                        ->orWhereNull("term")
                        ->orWhere("term", "");
                });
            }
            $availableExams = $examQuery->get();

            // Also merge exams that already have saved marks (same year/term scope)
            $resultExamIds = Result::query()
                ->whereHas("examination", function ($q) use ($schoolID) {
                    $q->where("schoolID", $schoolID);
                })
                ->distinct()
                ->pluck("examID")
                ->filter()
                ->values()
                ->all();

            if (!empty($resultExamIds)) {
                $extra = Examination::where("schoolID", $schoolID)->whereIn(
                    "examID",
                    $resultExamIds,
                );
                $this->applyExaminationAcademicYearFilter(
                    $extra,
                    $academicYearIDs,
                    !empty($calendarYears) ? $calendarYears : $yearFilter,
                );
                if ($termFilter) {
                    $extra->where(function ($q) use ($termFilter) {
                        $q->where("term", $termFilter)
                            ->orWhere("term", "all_terms")
                            ->orWhere("term", "all")
                            ->orWhere("term", "both")
                            ->orWhereNull("term")
                            ->orWhere("term", "");
                    });
                }
                $availableExams = $availableExams
                    ->concat($extra->get())
                    ->unique("examID")
                    ->sortBy("start_date")
                    ->values();
            }
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
            !empty($teacherID) &&
            !$isCoordinatorResultsView &&
            ($isTeacherView ||
                ($userType === "Teacher" && !$hasBroadResultPermission))
        ) {
            if ($isTeacherView) {
                if (empty($subclassFilter)) {
                    return redirect()
                        ->route("AdmitedClasses")
                        ->with("error", "Please select a class to view results")
                        ->with("error_type", "no_subclass_selected");
                }

                $subclass = Subclass::find($subclassFilter);
                if (
                    !$subclass ||
                    (int) $subclass->teacherID !== (int) $teacherID
                ) {
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
                if (
                    !$subclass ||
                    (int) $subclass->teacherID !== (int) $teacherID
                ) {
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
                "subclass.combie.subjects",
                "subclass.teacher",
                "parent",
                "oldSubclass.class",
                "oldSubclass.combie.subjects",
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

        // Exam filter does not require Term in the UI ("All Terms").
        // Resolve term from the selected exam when missing so SMS/board data loads.
        if (
            $typeFilter === "exam" &&
            $examFilter &&
            !$termFilter
        ) {
            $examForTerm = Examination::where("schoolID", $schoolID)
                ->where("examID", $examFilter)
                ->first();
            if (
                $examForTerm &&
                !empty($examForTerm->term) &&
                $examForTerm->term !== "all_terms"
            ) {
                $termFilter = $examForTerm->term;
            }
        }

        // Exam: need year + (examID or term). Report: need year + term.
        $canLoadExamResults =
            $typeFilter === "exam" &&
            $yearFilter &&
            ($examFilter || $termFilter);
        $canLoadTermReport =
            $typeFilter === "report" && $yearFilter && $termFilter;

        if ($canLoadExamResults || $canLoadTermReport) {
            if ($typeFilter === "exam") {
                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if ($examFilter && !is_array($examFilter)) {
                    $filterClassName = null;
                    if ($classFilter) {
                        $fc = ClassModel::find($classFilter);
                        $filterClassName = $fc->class_name ?? null;
                    }
                    $caDef = $this->resolveCaDefinition(
                        $schoolID,
                        $examFilter,
                        $filterClassName,
                    );
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
                        !empty($calendarYears) ? $calendarYears : $yearFilter,
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

        $isALevelView = false;
        if ($schoolType === "Secondary") {
            if ($classFilter) {
                $filteredClass =
                    ClassModel::find($classFilter) ?:
                    $classes->first(function ($class) use ($classFilter) {
                        return (string) $class->classID ===
                            (string) $classFilter;
                    });
                $isALevelView = $this->isALevelClassName(
                    $filteredClass->class_name ?? null,
                );
            }
            if (!$isALevelView) {
                foreach ($students as $student) {
                    $cn =
                        $student->subclass->class->class_name ??
                        ($student->oldSubclass->class->class_name ?? null);
                    if ($this->isALevelClassName($cn)) {
                        $isALevelView = true;
                        break;
                    }
                }
            }
        }

        return view("Admin.result_management", [
            "school" => $school,
            "schoolType" => $schoolType,
            "isALevelView" => $isALevelView,
            "schoolHasALevel" =>
                $schoolType === "Secondary" &&
                app(SelectedAcademicYearService::class)->schoolHasALevelClasses(
                    (int) $schoolID,
                ),
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
                "rank_by" => $rankBy,
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

    private function isOptionalStudentStatus(?string $studentStatus): bool
    {
        return strtolower(trim((string) ($studentStatus ?? ""))) === "optional";
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

        // Inactive / removed from class → never show
        if (
            strcasecmp((string) ($classSubject->status ?? "Active"), "Active") !==
            0
        ) {
            return false;
        }

        // Default: null / empty / Required → required for all
        if (!$this->isOptionalStudentStatus($classSubject->student_status ?? null)) {
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
        $classSubject = $result->classSubject ?? null;
        if (!$classSubject && !empty($result->class_subjectID)) {
            $classSubject = ClassSubject::find($result->class_subjectID);
            if ($classSubject) {
                $result->setRelation("classSubject", $classSubject);
            }
        }

        return $this->shouldIncludeClassSubjectForStudent(
            $studentID,
            $classSubject,
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
     * Final safety net for individual report / modal / SMS subject rows.
     * - Keeps only Active class subjects on the student's subclass (when known)
     * - Required / null student_status → include for everyone
     * - Optional → include only if student elected
     *
     * @param  list<array<string, mixed>>  $subjects
     * @param  list<int>|null  $electedClassSubjectIds
     * @return list<array<string, mixed>>
     */
    private function filterSubjectPayloadForStudentElection(
        array $subjects,
        int $studentID,
        ?int $subclassID = null,
        ?array $electedClassSubjectIds = null,
    ): array {
        if ($subjects === []) {
            return [];
        }

        $electedClassSubjectIds ??= $this->getElectedClassSubjectIdsForStudent(
            $studentID,
        );

        // Allowed subjects on this student's class/stream (darasa lake)
        $allowedSubjectIds = null;
        $allowedClassSubjectIds = null;
        if ($subclassID) {
            $classSubjects = ClassSubject::where("subclassID", $subclassID)
                ->where("status", "Active")
                ->get(["class_subjectID", "subjectID", "student_status"]);

            $allowedSubjectIds = [];
            $allowedClassSubjectIds = [];
            foreach ($classSubjects as $cs) {
                if (
                    !$this->shouldIncludeClassSubjectForStudent(
                        $studentID,
                        $cs,
                        $electedClassSubjectIds,
                    )
                ) {
                    continue;
                }
                $allowedSubjectIds[] = (int) $cs->subjectID;
                $allowedClassSubjectIds[] = (int) $cs->class_subjectID;
            }
            $allowedSubjectIds = array_values(array_unique($allowedSubjectIds));
            $allowedClassSubjectIds = array_values(
                array_unique($allowedClassSubjectIds),
            );
        }

        // Fallback maps when subclass not known (optional-by-subjectID only)
        $optionalSubjectIds = [];
        $electedSubjectIds = [];
        if ($allowedSubjectIds === null) {
            $optionalSubjectIds = ClassSubject::query()
                ->where("status", "Active")
                ->whereIn(
                    "class_subjectID",
                    array_filter(
                        array_map(
                            fn($s) => (int) ($s["class_subjectID"] ?? 0),
                            $subjects,
                        ),
                    ),
                )
                ->get(["class_subjectID", "subjectID", "student_status"])
                ->filter(
                    fn($cs) => $this->isOptionalStudentStatus(
                        $cs->student_status ?? null,
                    ),
                )
                ->pluck("subjectID")
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($optionalSubjectIds !== [] && $electedClassSubjectIds !== []) {
                $electedSubjectIds = ClassSubject::whereIn(
                    "class_subjectID",
                    $electedClassSubjectIds,
                )
                    ->pluck("subjectID")
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        $filtered = [];
        foreach ($subjects as $subj) {
            if (!is_array($subj)) {
                continue;
            }

            $subjectID = (int) ($subj["subjectID"] ?? 0);
            $classSubjectID = (int) ($subj["class_subjectID"] ?? 0);

            // Strict: must be offered + elected/required on student's subclass
            if ($allowedSubjectIds !== null) {
                $ok = false;
                if (
                    $classSubjectID > 0 &&
                    in_array($classSubjectID, $allowedClassSubjectIds, true)
                ) {
                    $ok = true;
                } elseif (
                    $subjectID > 0 &&
                    in_array($subjectID, $allowedSubjectIds, true)
                ) {
                    $ok = true;
                }
                if (!$ok) {
                    continue;
                }
                $filtered[] = $subj;
                continue;
            }

            // Fallback without subclass: drop unelected Optionals only
            $isOptional = !empty($subj["is_optional"]);
            if (!$isOptional && $subjectID > 0 && $optionalSubjectIds !== []) {
                $isOptional = in_array($subjectID, $optionalSubjectIds, true);
            }
            if (
                !$isOptional &&
                isset($subj["student_status"])
            ) {
                $isOptional = $this->isOptionalStudentStatus(
                    (string) $subj["student_status"],
                );
            }
            if (
                !$isOptional &&
                $classSubjectID > 0
            ) {
                $cs = ClassSubject::find($classSubjectID);
                if ($cs) {
                    $isOptional = $this->isOptionalStudentStatus(
                        $cs->student_status ?? null,
                    );
                }
            }

            if ($isOptional) {
                $elected = false;
                if ($classSubjectID > 0) {
                    $elected = in_array(
                        $classSubjectID,
                        $electedClassSubjectIds,
                        true,
                    );
                } elseif ($subjectID > 0) {
                    $elected = in_array($subjectID, $electedSubjectIds, true);
                }
                if (!$elected) {
                    continue;
                }
            }

            $filtered[] = $subj;
        }

        return array_values($filtered);
    }

    /**
     * Active enrollments for a class_subject in the header-selected academic year.
     * No students.subclassID fallback.
     */
    private function getStudentsForSubjectAnalysis(ClassSubject $classSubject): Collection
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return collect();
        }

        $yearIds = app(SelectedAcademicYearService::class)->selectedYearIds(
            (int) $schoolID,
        );
        if (empty($yearIds)) {
            return collect();
        }

        $enrollment = app(StudentEnrollmentService::class);
        $ids = [];
        if ($classSubject->subclassID) {
            $ids = $enrollment->studentIDsInSubclass(
                (int) $classSubject->subclassID,
                (int) $schoolID,
                "Active",
            );
        } elseif ($classSubject->classID) {
            $ids = $enrollment->studentIDsInClass(
                (int) $classSubject->classID,
                (int) $schoolID,
                "Active",
            );
        }

        if (empty($ids)) {
            return collect();
        }

        $students = Student::where("schoolID", $schoolID)
            ->whereIn("studentID", $ids)
            ->get();

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

        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        $academicYearLabel = $yearCtx["label"];
        $hasSelectedAcademicYear = !empty($academicYearIDs);

        // Year comes from header academic year (calendar year used for exam AJAX compatibility)
        $year = $hasSelectedAcademicYear
            ? (string) ($yearCtx["calendar_year"] ?? "")
            : "";
        $term = $request->get("term", "");
        $examID = $request->get("examID", "");
        $classID = $request->get("classID", "");
        $subclassID = $request->get("subclassID", "");
        $subjectID = $request->get("subjectID", "");
        $aggregateBySubject = (bool) $classID && !$subclassID;
        $allSubclasses = $aggregateBySubject;

        $availableYears = !empty($yearCtx["calendar_years"])
            ? $yearCtx["calendar_years"]
            : [];

        $classes = ClassModel::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->orderBy("class_name")
            ->get();

        $subjects = SchoolSubject::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->orderBy("subject_name")
            ->get();

        $examsQuery = Examination::where("schoolID", $schoolID);
        $this->applyExaminationAcademicYearFilter(
            $examsQuery,
            $academicYearIDs,
            $year ?: null,
        );
        if ($term) {
            $examsQuery->where("term", $term);
        }
        $exams = $examsQuery->orderBy("start_date", "desc")->get();

        $analysisData = [];
        $selectedExam = null;
        if ($examID && $hasSelectedAcademicYear) {
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
                "academicYearLabel",
                "academicYearIDs",
                "hasSelectedAcademicYear",
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

        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        if (empty($academicYearIDs)) {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "Select an academic year in the header before exporting topic analysis.",
                );
        }

        $year = $request->get("year", "") ?: (string) ($yearCtx["calendar_year"] ?? "");
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
            "title" => "Topic Analysis - " . $selectedExam->exam_name,
            "academicYearLabel" => $yearCtx["label"] ?? null,
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

        // Same audience as manageResults: Admin, or anyone with results permission,
        // or Teacher/Staff already allowed to view the results board.
        $allowed =
            $schoolID &&
            ($userType === "Admin" ||
                $userType === "Teacher" ||
                $userType === "Staff" ||
                $this->hasPermission("view_results"));

        if (!$allowed) {
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
        $isALevel = (bool) $request->boolean("is_a_level");
        $gradeHeaders = $isPrimary
            ? ["A", "B", "C", "D", "E", "F"]
            : ($isALevel
                ? ["A", "B", "C", "D", "E", "S", "F"]
                : ["A", "B", "C", "D", "F"]);

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
            ->map(function ($row) use ($rankMap, $isPrimary, $isALevel) {
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
                            $remarkText = $isALevel ? "Average" : "Satisfactory";
                            $remarkClass = "remark-avg";
                            $competency = "Grade D (" . $remarkText . ")";
                        } elseif ($isALevel && $grade === "E") {
                            $remarkText = "Satisfactory";
                            $remarkClass = "remark-avg";
                            $competency = "Grade E (Satisfactory)";
                        } elseif ($isALevel && $grade === "S") {
                            $remarkText = "Subsidiary";
                            $remarkClass = "remark-avg";
                            $competency = "Grade S (Subsidiary)";
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
            "isALevel" => $isALevel,
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

    /**
     * Accept only plain positive integers for SMS NAFASI.
     * Client may send arrays when jQuery .data('position') collides with UI position coords.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function resolveResultSmsPosition(
        mixed $rawPosition,
        mixed $rawTotal,
        Student $student,
        string $type,
        mixed $term,
        mixed $yearForQuery,
        string $schoolType,
        int $schoolID,
        ?int $examID,
        mixed $week,
        array $academicYearIDs,
        string $rankBy = "average",
    ): array {
        $position = $this->sanitizePositiveInt($rawPosition);
        $total = $this->sanitizePositiveInt($rawTotal);

        if ($position !== null && $total !== null && $position <= $total) {
            return [$position, $total];
        }

        try {
            $computed = $this->computeStudentRankForSms(
                $student,
                $type,
                $term,
                $yearForQuery,
                $schoolType,
                $schoolID,
                $examID,
                $week,
                $academicYearIDs,
                $rankBy,
            );
            if ($computed[0] !== null && $computed[1] !== null) {
                return $computed;
            }
        } catch (\Throwable $e) {
            \Log::warning("resolveResultSmsPosition recompute failed: " . $e->getMessage());
        }

        // Prefer a valid total even if rank is unknown
        if ($position !== null && $total !== null) {
            return [$position, $total];
        }

        return [null, null];
    }

    private function sanitizePositiveInt(mixed $value): ?int
    {
        if (is_array($value) || is_object($value) || $value === null || $value === "") {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            // Reject "N/A", "5 out of 29", coordinates noise, etc.
            if ($value === "" || !preg_match('/^\d+$/', $value)) {
                return null;
            }
        }
        $int = filter_var($value, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1],
        ]);

        return $int === false ? null : (int) $int;
    }

    /**
     * Rank student within subclass (mchepuo), fallback to main class - same idea as individual report.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function computeStudentRankForSms(
        Student $student,
        string $type,
        mixed $term,
        mixed $yearForQuery,
        string $schoolType,
        int $schoolID,
        ?int $examID,
        mixed $week,
        array $academicYearIDs,
        string $rankBy = "average",
    ): array {
        $studentClassID = null;
        $studentSubclassID =
            $student->subclassID ?? ($student->old_subclassID ?? null);
        if ($student->subclass && $student->subclass->class) {
            $studentClassID = $student->subclass->class->classID;
            $studentSubclassID =
                $studentSubclassID ?: $student->subclass->subclassID;
        } elseif ($student->oldSubclass && $student->oldSubclass->class) {
            $studentClassID = $student->oldSubclass->class->classID;
            $studentSubclassID =
                $studentSubclassID ?: $student->oldSubclass->subclassID;
        }

        if (!$studentClassID && !$studentSubclassID) {
            return [null, null];
        }

        $enrollmentSvc = app(\App\Services\StudentEnrollmentService::class);
        $classStudentsQuery = Student::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->with(["subclass.class", "oldSubclass.class"]);

        if ($studentClassID) {
            $enrolledInClass = $enrollmentSvc->studentIDsInClass(
                (int) $studentClassID,
                (int) $schoolID,
                "Active",
            );
            if (!empty($enrolledInClass)) {
                $classStudentsQuery->whereIn("studentID", $enrolledInClass);
            } else {
                $classStudentsQuery->where(function ($query) use ($studentClassID) {
                    $query
                        ->whereHas("subclass", function ($q) use ($studentClassID) {
                            $q->where("classID", $studentClassID);
                        })
                        ->orWhereHas("oldSubclass", function ($q) use ($studentClassID) {
                            $q->where("classID", $studentClassID);
                        });
                });
            }
        } else {
            $classStudentsQuery->where(function ($query) use ($studentSubclassID) {
                $query
                    ->where("subclassID", $studentSubclassID)
                    ->orWhere("old_subclassID", $studentSubclassID);
            });
        }

        $classStudents = $classStudentsQuery->get();
        if ($classStudents->isEmpty()) {
            return [null, null];
        }

        $caExamIDs = $examID ? [$examID] : null;
        if ($type !== "report" && $examID) {
            $caDef = $this->resolveCaDefinition($schoolID, $examID, null);
            if ($caDef && !empty($caDef->test_ids)) {
                $caExamIDs = array_values(
                    array_unique(array_merge([$examID], (array) $caDef->test_ids)),
                );
            }
        }

        if ($type === "report") {
            $classResultsData = $this->getTermReport(
                $classStudents,
                $term,
                $yearForQuery,
                $schoolType,
                null,
                $schoolID,
                $academicYearIDs,
            );
        } else {
            $classResultsData = $this->getExamResults(
                $classStudents,
                $term,
                $yearForQuery,
                $schoolType,
                $caExamIDs,
                $week ?: null,
                null,
                $schoolID,
                $academicYearIDs,
            );
        }

        $rows = [];
        foreach ($classStudents as $classStudent) {
            if (!isset($classResultsData[$classStudent->studentID])) {
                continue;
            }
            $studentResultData = $classResultsData[$classStudent->studentID];
            if ($type === "report") {
                $rows[] = [
                    "student" => $classStudent,
                    "average_marks" => $studentResultData["average_marks"] ?? 0,
                    "total_marks" => $studentResultData["total_marks"] ?? 0,
                    "total_points" => $studentResultData["total_points"] ?? 9999,
                    "division" => $studentResultData["division"] ?? null,
                    "is_all_incomplete" => !empty($studentResultData["is_all_incomplete"]),
                    "subjects" => $studentResultData["subjects"] ?? [],
                ];
            } else {
                foreach ($studentResultData as $er) {
                    if (!is_array($er)) {
                        continue;
                    }
                    if (
                        $examID &&
                        isset($er["exam"]) &&
                        (int) ($er["exam"]->examID ?? 0) !== (int) $examID
                    ) {
                        continue;
                    }
                    $rows[] = [
                        "student" => $classStudent,
                        "total_marks" => $er["total_marks"] ?? 0,
                        "average_marks" => $er["average_marks"] ?? 0,
                        "total_points" => $er["total_points"] ?? 9999,
                        "best_seven_total_marks" => $er["best_seven_total_marks"] ?? 0,
                        "division" => $er["division"] ?? null,
                        "is_all_incomplete" => !empty($er["is_all_incomplete"]),
                        "subjects" => $er["subjects"] ?? [],
                    ];
                    break;
                }
            }
        }

        if (empty($rows)) {
            return [null, null];
        }

        $reportDivisionStyle = $type === "report";
        $sortList = function (array &$list) use (
            $rankBy,
            $schoolType,
            $reportDivisionStyle,
        ) {
            usort($list, function ($a, $b) use (
                $rankBy,
                $schoolType,
                $reportDivisionStyle,
            ) {
                return $this->compareStudentsForPosition(
                    $a,
                    $b,
                    $rankBy,
                    $schoolType,
                    $reportDivisionStyle,
                );
            });
        };

        // Prefer subclass/stream pool (matches board when a stream is filtered)
        $pool = $rows;
        if ($studentSubclassID) {
            $pool = array_values(
                array_filter($rows, function ($row) use ($studentSubclassID) {
                    $st = $row["student"];
                    $sid =
                        $st->subclassID ??
                        ($st->old_subclassID ??
                            ($st->subclass->subclassID ??
                                ($st->oldSubclass->subclassID ?? null)));
                    return (int) $sid === (int) $studentSubclassID;
                }),
            );
            if (empty($pool)) {
                $pool = $rows;
            }
        }

        $sortList($pool);
        $total = count($pool);
        $sid = (int) $student->studentID;
        foreach ($pool as $index => $row) {
            if ((int) ($row["student"]->studentID ?? 0) === $sid) {
                return [$index + 1, $total];
            }
        }

        return [null, $total > 0 ? $total : null];
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
        $summaryTotal = null;
        $formattedResults = [];
        $results = collect();
        $classID =
            $student->subclass->classID ??
            ($student->oldSubclass->classID ?? null);
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : "Secondary";

        $resolveSmsGrade = function ($marks) use (
            $schoolType,
            $className,
            $classID,
        ) {
            if ($marks === null || $marks === "" || $marks === "incomplete") {
                return "-";
            }
            if ($schoolType === "Secondary") {
                return $this->getSecondaryHardcodedGrade($marks, $className)[
                    "grade"
                ] ?? "-";
            }
            $gradeRes = $this->getGradeFromDefinition(
                $marks,
                $classID,
                $schoolType,
                $className,
            );
            return $gradeRes["grade"] ?? "-";
        };

        $appendSubjectsToSms = function (array $subjects) use (
            &$formattedResults,
            &$totalSum,
            $resolveSmsGrade,
        ) {
            foreach ($subjects as $subj) {
                if (!is_array($subj)) {
                    continue;
                }
                $name = trim((string) ($subj["subject_name"] ?? ""));
                if ($name === "" || strtoupper($name) === "EXAMS") {
                    continue;
                }
                $rawMarks = $subj["marks"] ?? null;
                if (
                    $rawMarks === null ||
                    $rawMarks === "" ||
                    $rawMarks === "incomplete"
                ) {
                    continue;
                }
                $sMarks = (float) $rawMarks;
                $totalSum += $sMarks;
                $sGrade =
                    !empty($subj["grade"]) && $subj["grade"] !== "-"
                        ? $subj["grade"]
                        : $resolveSmsGrade($sMarks);
                $formattedResults[] =
                    "{$name}-(" . round($sMarks) . ")-({$sGrade})";
            }
        };

        // Same rules as individual PDF: only class subjects the student takes
        $smsSubclassID =
            $student->subclass->subclassID ??
            ($student->oldSubclass->subclassID ?? null);
        $sanitizeSmsSubjects = function (array $subjects) use (
            $studentID,
            $smsSubclassID,
        ): array {
            return $this->filterSubjectPayloadForStudentElection(
                $subjects,
                (int) $studentID,
                $smsSubclassID ? (int) $smsSubclassID : null,
            );
        };

        // Prefer exact subject lines from filtered manageResults board
        $subjectsPayload = $request->input("subjects");
        if (is_string($subjectsPayload)) {
            $decoded = json_decode($subjectsPayload, true);
            $subjectsPayload = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($subjectsPayload)) {
            $subjectsPayload = [];
        }
        if (!empty($subjectsPayload)) {
            $subjectsPayload = $sanitizeSmsSubjects($subjectsPayload);
        }

        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"] ?? [],
        );
        $calendarYears = !empty($yearCtx["calendar_years"])
            ? $yearCtx["calendar_years"]
            : ($year ? [(int) $year] : []);
        $yearForQuery = !empty($calendarYears) ? $calendarYears : $year;

        if ($subject && ($marks !== null && $marks !== "")) {
            // Single subject mode (weekly subject table)
            $totalSum = (float) $marks;
            $summaryTotal = $totalSum;
            $smsGrade = $grade ?: $resolveSmsGrade($marks);
            $formattedResults[] =
                "{$subject}-(" . round((float) $marks) . ")-({$smsGrade})";
            $resultsString = $formattedResults[0];
        } elseif (!empty($subjectsPayload)) {
            $appendSubjectsToSms($subjectsPayload);
            // Totals from filtered subjects only (never trust client total for wrong subjects)
            if ($type === "report" && count($formattedResults) > 0) {
                $summaryTotal = $totalSum / count($formattedResults);
            } else {
                $summaryTotal = $totalSum;
            }
            $resultsString = implode("\n", $formattedResults);
        } elseif ($type === "report") {
            // Fallback: same aggregation as term-report board (AY expand + definition + CA)
            $reportData = $this->getTermReport(
                collect([$student]),
                $term,
                $yearForQuery,
                $schoolType,
                null,
                $schoolID,
                $academicYearIDs,
            );
            $studentReport = $reportData[$studentID] ?? null;
            if (
                !$studentReport ||
                empty($studentReport["subjects"])
            ) {
                \Illuminate\Support\Facades\Log::warning(
                    "sendResultSms: No results found for student {$studentID} in term {$term} / year {$year}",
                );
                return response()->json([
                    "success" => false,
                    "error" => "No results found for this term",
                ]);
            }
            $reportSubjects = $sanitizeSmsSubjects(
                is_array($studentReport["subjects"])
                    ? $studentReport["subjects"]
                    : [],
            );
            $appendSubjectsToSms($reportSubjects);
            $summaryTotal =
                count($formattedResults) > 0
                    ? $totalSum / count($formattedResults)
                    : (float) ($studentReport["average_marks"] ?? $totalSum);
            $requestDivision = $request->input("division");
            $reportDivision = $studentReport["division"] ?? null;
            if (
                ($requestDivision === null || $requestDivision === "") &&
                $reportDivision !== null &&
                $reportDivision !== ""
            ) {
                $request->merge(["division" => $reportDivision]);
            }
            $resultsString = implode("\n", $formattedResults);
        } else {
            // Fallback: same exam/CA aggregation as exam board
            $examIdsForSms = $examID ? [$examID] : null;
            if ($examID) {
                $caDefinition = CaDefinition::where("schoolID", $schoolID)
                    ->where("examID", $examID)
                    ->first();
                if ($caDefinition && !empty($caDefinition->test_ids)) {
                    $examIdsForSms = array_values(
                        array_unique(
                            array_merge(
                                [$examID],
                                (array) $caDefinition->test_ids,
                            ),
                        ),
                    );
                }
            }

            $examResultsData = $this->getExamResults(
                collect([$student]),
                $term,
                $yearForQuery,
                $schoolType,
                $examIdsForSms,
                $week && $week !== "all" ? $week : null,
                null,
                $schoolID,
                $academicYearIDs,
            );

            $studentExamRow = $examResultsData[$studentID] ?? null;
            // getExamResults may return keyed by exam or a flat subjects payload
            $subjectsFromExam = [];
            $examTotalMarks = null;
            $examDivision = null;
            if (is_array($studentExamRow)) {
                if (isset($studentExamRow["subjects"])) {
                    $subjectsFromExam = $studentExamRow["subjects"];
                    $examTotalMarks = $studentExamRow["total_marks"] ?? null;
                    $examDivision = $studentExamRow["division"] ?? null;
                } else {
                    foreach ($studentExamRow as $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        if (
                            $examID &&
                            isset($row["exam"]) &&
                            (int) ($row["exam"]->examID ?? 0) !== (int) $examID
                        ) {
                            continue;
                        }
                        if (!empty($row["subjects"])) {
                            $subjectsFromExam = $row["subjects"];
                            $examTotalMarks = $row["total_marks"] ?? null;
                            $examDivision = $row["division"] ?? null;
                            break;
                        }
                    }
                }
            }

            if (empty($subjectsFromExam)) {
                \Log::warning(
                    "sendResultSms: No results found for student {$studentID}. ExamID: {$examID}, Week: {$week}, UserType: {$userType}",
                );
                return response()->json([
                    "success" => false,
                    "error" => "No results found for this student",
                ]);
            }

            $subjectsFromExam = $sanitizeSmsSubjects(
                is_array($subjectsFromExam) ? $subjectsFromExam : [],
            );
            $appendSubjectsToSms($subjectsFromExam);
            $summaryTotal =
                count($formattedResults) > 0
                    ? $totalSum
                    : ($examTotalMarks !== null
                        ? (float) $examTotalMarks
                        : $totalSum);
            $requestDivision = $request->input("division");
            if (
                ($requestDivision === null || $requestDivision === "") &&
                $examDivision !== null &&
                $examDivision !== ""
            ) {
                $request->merge(["division" => $examDivision]);
            }
            $resultsString = implode("\n", $formattedResults);
        }

        if (empty($formattedResults) || empty($resultsString)) {
            return response()->json([
                "success" => false,
                "error" => "No clear subject results to send for this student",
            ]);
        }

        if ($summaryTotal === null) {
            $summaryTotal = $totalSum;
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
        if (is_array($division)) {
            $division = "";
        }
        $division = trim((string) $division);
        // Strip non-ASCII so GSM/UCS gateways do not replace chars with "?"
        $division = preg_replace('/[^\x20-\x7E]/', '', $division) ?? "";

        $rankBy = strtolower(trim((string) $request->input("rank_by", "average")));
        if (!in_array($rankBy, ["average", "division"], true)) {
            $rankBy = "average";
        }

        [$position, $totalStudentsCount] = $this->resolveResultSmsPosition(
            $request->input("position"),
            $request->input("totalStudentsCount"),
            $student,
            (string) $type,
            $term,
            $yearForQuery,
            $schoolType,
            (int) $schoolID,
            $actualExamID ? (int) $actualExamID : ($examID ? (int) $examID : null),
            $week && $week !== "all" ? $week : null,
            is_array($academicYearIDs) ? $academicYearIDs : [],
            $rankBy,
        );

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

        // Construct Premium SMS Format
        // Avoid "|" (GSM-7 extended) - some handsets/gateways render it as "?"
        $msg = "";
        if (!($school && $school->senderID)) {
            $msg .= "{$schoolName}\n";
        }
        $msg .= "MZAZI WA {$studentName},\n";
        $msg .= "YAFUATAYO NI MATOKEO YA {$studentName} KATIKA {$examNameContext}.\n";

        $summaryLabel = $type === "report" ? "WASTANI" : "JUMLA";
        $msg .= "{$summaryLabel}: " . round((float) $summaryTotal) . "\n";
        if ($schoolType === "Secondary" && $division !== "") {
            $msg .= "DIV: {$division}\n";
        }
        if ($position !== null && $totalStudentsCount !== null) {
            $msg .= "NAFASI: {$position}/{$totalStudentsCount}\n";
        }

        $msg .= "------------------------\n";
        $msg .= "MASOMO YAFUATAYO:\n";
        $msg .= $resultsString;
        $msg .= "\n------------------------\n";
        $msg .= "Ingia kuona matokeo kamili:\n";
        $msg .= "https://shulexpert.com/login";

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

            // Get examinations for this term and academic year (header).
            // When a specific examID is requested, skip year/term filters - identity
            // is enough (avoids Position 0/0 when exam.year differs from header calendar year).
            $examinationsQuery = Examination::where("schoolID", $schoolID);

            if ($examID) {
                if (is_array($examID)) {
                    $examinationsQuery->whereIn("examID", $examID);
                } else {
                    $examinationsQuery->where("examID", $examID);
                }
            } else {
                $this->applyExaminationAcademicYearFilter(
                    $examinationsQuery,
                    is_array($academicYearIDs) ? $academicYearIDs : [],
                    $year,
                );

                if ($term) {
                    $examinationsQuery->where(function ($q) use ($term) {
                        $q->where("term", $term)->orWhere(
                            "term",
                            "all_terms",
                        );
                    });
                }
            }

            $examinations = $examinationsQuery->orderBy("start_date")->get();

            // Class name for this student (A/O CA definition)
            $studentClassName =
                $student->subclass->class->class_name ??
                ($student->oldSubclass->class->class_name ?? null);

            // Check if selected exam has CA definition
            $caDefinition = null;
            if ($examID) {
                $checkID = is_array($examID) ? $examID[0] : $examID;
                $caDefinition = $this->resolveCaDefinition(
                    $schoolID,
                    $checkID,
                    $studentClassName,
                );
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
                    ->with(["classSubject.subject.components", "examination"])
                    ->get();
                $results = $this->filterResultsForStudentElection(
                    $results,
                    $student->studentID,
                    $electedClassSubjectIds,
                );

                if (!$results->isEmpty()) {
                    $useCaPercent = (bool) ($caDefinition->use_percent_division ?? false);
                    $caWeights = is_array($caDefinition->exam_weights ?? null)
                        ? $caDefinition->exam_weights
                        : [];
                    $involvedCount = count($examID);
                    $aggregatedResults = $results
                        ->groupBy("class_subjectID")
                        ->map(function ($group) use (
                            $involvedCount,
                            $useCaPercent,
                            $caWeights,
                            $examID,
                        ) {
                            if ($useCaPercent && !empty($caWeights)) {
                                $marksByExam = [];
                                foreach ($group as $r) {
                                    $marksByExam[$r->examID] =
                                        $r->marks !== null && $r->marks !== ""
                                            ? (float) $r->marks
                                            : null;
                                }
                                // Ensure all involved exam keys exist
                                foreach ($examID as $invId) {
                                    if (!array_key_exists($invId, $marksByExam)) {
                                        $marksByExam[$invId] = null;
                                    }
                                }
                                $avg = $this->computePercentWeightedTotal(
                                    $marksByExam,
                                    $caWeights,
                                );
                            } else {
                                $marksSum = $group->sum(function ($r) {
                                    return $r->marks !== null && $r->marks !== ""
                                        ? (float) $r->marks
                                        : 0;
                                });
                                $avg =
                                    $involvedCount > 0
                                        ? $marksSum / $involvedCount
                                        : 0;
                            }

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
                        ->with(["classSubject.subject.components", "examination"])
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
                    $useCaPercent = (bool) ($caDefinition->use_percent_division ?? false);
                    $caWeights = is_array($caDefinition->exam_weights ?? null)
                        ? $caDefinition->exam_weights
                        : [];
                    $results = $results
                        ->groupBy("class_subjectID")
                        ->map(function ($group) use (
                            $involvedCount,
                            $useCaPercent,
                            $caWeights,
                            $allInvolvedExams,
                        ) {
                            if ($useCaPercent && !empty($caWeights)) {
                                $marksByExam = [];
                                foreach ($group as $r) {
                                    $marksByExam[$r->examID] =
                                        $r->marks !== null && $r->marks !== ""
                                            ? (float) $r->marks
                                            : null;
                                }
                                foreach ($allInvolvedExams as $invId) {
                                    if (!array_key_exists($invId, $marksByExam)) {
                                        $marksByExam[$invId] = null;
                                    }
                                }
                                $avg = $this->computePercentWeightedTotal(
                                    $marksByExam,
                                    $caWeights,
                                );
                            } else {
                                $marksSum = $group->sum(function ($r) {
                                    return $r->marks !== null && $r->marks !== ""
                                        ? (float) $r->marks
                                        : 0;
                                });
                                $avg =
                                    $involvedCount > 0
                                        ? $marksSum / $involvedCount
                                        : 0;
                            }

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
                        ->with(["classSubject.subject.components", "examination"])
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

        $filteredResults = collect();
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
            $filteredResults->push($result);
        }

        // Duo papers → one % per subject; non-duo keep marks as-is
        $collapsed = $this->collapseResultsToSubjectMarks($filteredResults);
        $defaultFailPoints = $this->isALevelClassName($className) ? 7 : 5;

        foreach ($collapsed as $entry) {
            $marks = $entry["marks"];
            if ($marks !== null && $marks !== "") {
                $totalMarks += (float) $marks;
                $subjectCount++;
            } else {
                $subjectCount++;
            }

            $gradePoints = $this->calculateGradePoints(
                $marks !== null ? round((float) $marks, 2) : null,
                $schoolType,
                $className,
                $classID,
            );

            $subjectsData[] = [
                "subject_name" => $entry["subject_name"],
                "subjectID" => $entry["subjectID"],
                "class_subjectID" => (int) ($entry["class_subjectID"] ?? 0),
                "student_status" => $entry["student_status"] ?? null,
                "is_optional" => !empty($entry["is_optional"]),
                "marks" => $marks !== null ? $marks : "incomplete",
                "grade" => $gradePoints["grade"],
                "points" => $gradePoints["points"],
                "remark" => $gradePoints["remark"] ?? null,
                "is_duo" => $entry["is_duo"],
                "papers" => $entry["papers"] ?? [],
                "question_marks" => [],
            ];
        }

        $subjectsData = $this->filterSubjectPayloadForStudentElection(
            $subjectsData,
            (int) $student->studentID,
            (int) ($student->subclassID ?? ($student->old_subclassID ?? 0)) ?:
            null,
            $electedClassSubjectIds,
        );

        // Sat ≥1 subject → remaining incompletes become 0; sat none → all-incomplete (rank last)
        [$subjectsData, $isAllIncomplete] = $this->applySatZeroRuleToSubjects(
            $subjectsData,
            $schoolType,
            $className,
            $classID,
        );

        // Recount after election filter + sat/zero rule
        $totalMarks = 0;
        $subjectCount = 0;
        foreach ($subjectsData as $s) {
            if ($this->subjectEntryIsSat($s)) {
                $totalMarks += (float) $s["marks"];
                $subjectCount++;
            } elseif (($s["marks"] ?? null) === "incomplete") {
                $subjectCount++;
            }
        }

        $averageMarks = $subjectCount > 0 ? $totalMarks / $subjectCount : 0;
        $totalPoints = 0;
        $bestSevenTotalMarks = 0;
        $marksPointsArray = [];
        foreach ($subjectsData as $s) {
            if ($this->subjectEntryIsSat($s) && ($s["points"] ?? null) !== null) {
                $marksPointsArray[] = [
                    "marks" => (float) $s["marks"],
                    "points" => $s["points"],
                    "grade" => $s["grade"] ?? null,
                    "subjectID" => $s["subjectID"] ?? null,
                ];
            }
        }

        $combinationSubjectIds = $this->getCombinationSubjectIdsForStudent(
            $student,
        );
        $bestSevenGrades = [];
        $allSubjectGrades = $this->extractSubjectGradesFromPointEntries(
            $marksPointsArray,
        );
        if ($schoolType === "Secondary" && !empty($marksPointsArray)) {
            $divisionPick = $this->resolveSecondaryBestSubjectsForDivision(
                $marksPointsArray,
                $className,
                $schoolType,
                $combinationSubjectIds,
            );
            $totalPoints = $divisionPick["total_points"];
            $bestSevenGrades = $divisionPick["grades"];
        } elseif (
            $schoolType === "Secondary" &&
            $this->isALevelClassName($className) &&
            !empty($combinationSubjectIds)
        ) {
            // All combination subjects missing → F×N
            $totalPoints = count($combinationSubjectIds) * $defaultFailPoints;
            $bestSevenGrades = array_fill(0, count($combinationSubjectIds), "F");
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
            "is_all_incomplete" => $isAllIncomplete,
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

        // Resolve report definition by class level (A/O/all). Hint from first student.
        $hintClassName = null;
        foreach ($students as $hintStudent) {
            $hintClassName =
                $hintStudent->subclass->class->class_name ??
                ($hintStudent->oldSubclass->class->class_name ?? null);
            if ($hintClassName) {
                break;
            }
        }
        $reportDefinition = $this->resolveTermReportDefinition(
            $schoolID,
            $year,
            $term,
            $hintClassName,
        );
        $reportUsePercent = (bool) ($reportDefinition?->use_percent_division ?? false);
        $reportExamWeights = is_array($reportDefinition?->exam_weights ?? null)
            ? $reportDefinition->exam_weights
            : [];

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

        $examinations = $examinationsQuery->get();
        if ($reportDefinition && !empty($reportDefinition->exam_ids)) {
            $examinations = $this->orderModelsByIdList(
                $examinations,
                (array) $reportDefinition->exam_ids,
            );
        } else {
            $examinations = $examinations->sortBy("start_date")->values();
        }

        // No longer filtering by ended status
        $endedExaminations = $examinations;

        if ($endedExaminations->isEmpty()) {
            return $resultsData;
        }

        /**
         * Term report columns = all selected exams in the definition.
         * Equal average OR percent-weighted total when use_percent_division is on.
         */
        $reportExaminations = $endedExaminations->values();
        $reportExamIDs = $reportExaminations->pluck("examID")->toArray();
        $caDefinitionsByExam = CaDefinition::where("schoolID", $schoolID)
            ->whereIn("examID", $reportExamIDs)
            ->get()
            ->keyBy("examID");

        // Fetch results for all report exam columns
        $examIDs = $reportExamIDs;

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

            foreach ($reportExaminations as $exam) {
                $results = $studentResultsByExam->get(
                    $exam->examID,
                    collect(),
                );
                $results = $this->filterResultsForStudentElection(
                    $results,
                    $student->studentID,
                    $electedClassSubjectIds,
                );

                if ($results->isEmpty()) {
                    continue;
                }

                $examTotalMarks = 0;
                $examSubjectCount = 0;

                // Collapse duo papers to one % per subject before exam average
                $collapsedSubjects = $this->collapseResultsToSubjectMarks(
                    $results,
                );
                foreach ($collapsedSubjects as $subjectEntry) {
                    if (
                        $subjectEntry["marks"] !== null &&
                        $subjectEntry["marks"] !== ""
                    ) {
                        $examTotalMarks += (float) $subjectEntry["marks"];
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
                        "exam_label" => $this->formatExamLabelWithWeight(
                            $exam->exam_name,
                            $exam->examID,
                            $reportExamWeights,
                            $reportUsePercent,
                        ),
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
                // Overall average = mean of subject term averages (column mean per subject)
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
                    $reportExaminations,
                    $caDefinitionsByExam,
                    $schoolType,
                    $className,
                    $student->studentID,
                    $electedClassSubjectIds,
                    $reportUsePercent,
                    $reportExamWeights,
                );

                $subjectSummaries = $this->buildTermReportSubjectSummaries(
                    $allStudentResults,
                    $reportExaminations,
                    $caDefinitionsByExam,
                    $schoolType,
                    $className,
                    $student->studentID,
                    $electedClassSubjectIds,
                    $classID,
                    $reportUsePercent,
                    $reportExamWeights,
                );

                $scoredSubjectAverages = [];
                [$subjectSummaries, $isAllIncomplete] = $this->applySatZeroRuleToSubjects(
                    $subjectSummaries,
                    $schoolType,
                    $className,
                    $classID,
                );
                // Rebuild point entries after sat/zero rule (incomplete → 0 must count for division)
                if (!$isAllIncomplete) {
                    $subjectPointEntries = [];
                    foreach ($subjectSummaries as $summary) {
                        if (!$this->subjectEntryIsSat($summary)) {
                            continue;
                        }
                        $avg = (float) $summary["marks"];
                        $gp = $this->calculateGradePoints(
                            $avg,
                            $schoolType,
                            $className,
                            $classID,
                        );
                        if (($gp["points"] ?? null) === null) {
                            continue;
                        }
                        $subjectPointEntries[] = [
                            "marks" => $avg,
                            "points" => $gp["points"],
                            "grade" => $gp["grade"] ?? ($summary["grade"] ?? null),
                            "subjectID" => $summary["subjectID"] ?? null,
                        ];
                    }
                } else {
                    $subjectPointEntries = [];
                }

                foreach ($subjectSummaries as $summary) {
                    if ($this->subjectEntryIsSat($summary)) {
                        $scoredSubjectAverages[] = (float) $summary["marks"];
                    }
                }
                $overallAverage =
                    count($scoredSubjectAverages) > 0
                        ? array_sum($scoredSubjectAverages) /
                            count($scoredSubjectAverages)
                        : 0;

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
                        $this->getCombinationSubjectIdsForStudent($student),
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
                    "is_all_incomplete" => $isAllIncomplete,
                ];
            }
        }

        return $resultsData;
    }

    /**
     * Whether class is Form Five / Form Six (A-Level).
     */
    private function isALevelClassName(?string $className): bool
    {
        return app(SelectedAcademicYearService::class)->isALevelClassName(
            $className,
        );
    }

    /**
     * Numeric classID from results URL (plain ID or encrypted ClassMangement token).
     */
    private function resolveResultsClassId($classIDParam): ?int
    {
        if ($classIDParam === null || $classIDParam === "") {
            return null;
        }
        if (is_numeric($classIDParam)) {
            return (int) $classIDParam;
        }
        try {
            $decrypted = Crypt::decrypt($classIDParam);
            if (is_numeric($decrypted)) {
                return (int) $decrypted;
            }
        } catch (\Exception $e) {
            // Not an encrypted class ID
        }

        return null;
    }

    /**
     * Subclass dropdown / title label.
     * A-Level: subclass only (e.g. "A"). O-Level: "FORM ONE A" (or "FORM ONE - A" for coordinator).
     */
    private function formatSubclassFilterDisplayName(
        ?string $className,
        ?string $subclassName,
        bool $coordinatorStyle = false,
    ): string {
        $className = trim((string) $className);
        $subclassName = trim((string) $subclassName);

        if ($this->isALevelClassName($className)) {
            return $subclassName !== "" ? $subclassName : $className;
        }

        if ($subclassName === "") {
            return $className;
        }

        return $coordinatorStyle
            ? $className . " - " . $subclassName
            : $className . " " . $subclassName;
    }

    /**
     * Board STREAM/COMBIE column:
     * - A-Level → combination / subclass name only (unchanged)
     * - O-Level → stream_code, else "CLASS + subclass" (original)
     */
    private function resolveBoardStreamLabel($student, ?string $fallbackClassName = null): string
    {
        $subclass = $student->subclass ?? ($student->oldSubclass ?? null);
        if (!$subclass) {
            return "";
        }

        // Always prefer the student's real main-class name (not board title which may be "A")
        $className =
            $subclass->class->class_name ??
            ($student->oldSubclass->class->class_name ??
                ($fallbackClassName ?? ""));

        if ($this->isALevelClassName($className)) {
            $name = trim((string) ($subclass->subclass_name ?? ""));
            return $name !== ""
                ? $name
                : trim((string) ($subclass->stream_code ?? ""));
        }

        $code = trim((string) ($subclass->stream_code ?? ""));
        if ($code !== "") {
            return $code;
        }

        $sub = trim((string) ($subclass->subclass_name ?? ""));
        return trim($className . ($sub !== "" ? " " . $sub : ""));
    }

    /**
     * Subject IDs assigned to the student's subclass combination (max 3).
     *
     * @return list<int>
     */
    private function getCombinationSubjectIdsForStudent($student): array
    {
        if (!$student) {
            return [];
        }

        $subclass = $student->subclass ?? ($student->oldSubclass ?? null);
        if (!$subclass) {
            return [];
        }

        if (!$subclass->relationLoaded("combie")) {
            $subclass->load("combie.subjects");
        } elseif (
            $subclass->combie &&
            !$subclass->combie->relationLoaded("subjects")
        ) {
            $subclass->combie->load("subjects");
        }

        $combie = $subclass->combie ?? null;
        if (!$combie || !$combie->subjects) {
            return [];
        }

        return $combie->subjects
            ->pluck("subjectID")
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Collapse raw result rows to one marks value per subject.
     * Duo subjects: (sum paper marks ÷ sum paper max) × 100.
     *
     * @return list<array{subjectID:int,subject_name:string,class_subjectID:mixed,marks:float|null,is_duo:bool}>
     */
    private function collapseResultsToSubjectMarks($results): array
    {
        $groups = [];

        foreach ($results as $result) {
            $cs = $result->classSubject ?? null;
            $subject = $cs->subject ?? null;
            if (!$subject) {
                continue;
            }

            $sid = (int) $subject->subjectID;
            if (!isset($groups[$sid])) {
                if (
                    ($subject->is_duo ?? false) &&
                    !$subject->relationLoaded("components")
                ) {
                    $subject->load("components");
                }
                $groups[$sid] = [
                    "subject" => $subject,
                    "class_subjectID" => $result->class_subjectID,
                    "student_status" => $cs->student_status ?? null,
                    "rows" => collect(),
                ];
            }
            $groups[$sid]["rows"]->push($result);
        }

        $out = [];
        foreach ($groups as $sid => $group) {
            $subject = $group["subject"];
            $components = $subject->components ?? collect();
            $isDuo =
                (bool) ($subject->is_duo ?? false) && $components->count() > 0;

            if ($isDuo) {
                $raw = 0.0;
                $max = 0;
                $any = false;
                $papers = [];
                foreach ($components as $comp) {
                    $compMax = max(1, (int) $comp->max_marks);
                    $max += $compMax;
                    $row = $group["rows"]->first(function ($r) use ($comp) {
                        return (int) ($r->componentID ?? 0) ===
                            (int) $comp->componentID &&
                            $r->marks !== null &&
                            $r->marks !== "";
                    });
                    $paperMarks = $row ? (float) $row->marks : null;
                    if ($row) {
                        $raw += $paperMarks;
                        $any = true;
                    }
                    $papers[] = [
                        "componentID" => (int) $comp->componentID,
                        "component_name" =>
                            $comp->component_name ?? "Paper",
                        "marks" => $paperMarks,
                        "max_marks" => $compMax,
                    ];
                }
                $marks =
                    $any && $max > 0
                        ? round(($raw / $max) * 100, 2)
                        : null;
            } else {
                $papers = [];
                $marked = $group["rows"]->filter(
                    fn($r) => $r->marks !== null && $r->marks !== "",
                );
                if ($marked->count() > 1) {
                    $marks = round((float) $marked->avg("marks"), 2);
                } elseif ($marked->count() === 1) {
                    $marks = (float) $marked->first()->marks;
                } else {
                    $marks = null;
                }
            }

            $out[] = [
                "subjectID" => $sid,
                "subject_name" => $subject->subject_name ?? "N/A",
                "class_subjectID" => $group["class_subjectID"],
                "student_status" => $group["student_status"] ?? null,
                "is_optional" => $this->isOptionalStudentStatus(
                    $group["student_status"] ?? null,
                ),
                "marks" => $marks,
                "is_duo" => $isDuo,
                "papers" => $papers,
            ];
        }

        return $out;
    }

    /**
     * Secondary schools: fixed letter grades (do not use grade_definitions).
     * O-Level: A 75-100 (1), B 65-74 (2), C 45-64 (3), D 30-44 (4), F 0-29 (5)
     * A-Level (Form 5/6): A 80-100 (1), B 70-79 (2), C 60-69 (3), D 50-59 (4),
     *   E 40-49 (5), S 35-39 (6), F 0-34 (7)
     */
    private function getSecondaryHardcodedGrade(
        $marks,
        ?string $className = null,
    ): array {
        if ($marks === null || $marks === "") {
            return ["grade" => null, "points" => null, "remark" => null];
        }

        $marksNum = round((float) $marks, 2);

        if ($this->isALevelClassName($className)) {
            if ($marksNum >= 80) {
                return ["grade" => "A", "points" => 1, "remark" => "Excellent"];
            }
            if ($marksNum >= 70) {
                return ["grade" => "B", "points" => 2, "remark" => "Very Good"];
            }
            if ($marksNum >= 60) {
                return ["grade" => "C", "points" => 3, "remark" => "Good"];
            }
            if ($marksNum >= 50) {
                return ["grade" => "D", "points" => 4, "remark" => "Average"];
            }
            if ($marksNum >= 40) {
                return ["grade" => "E", "points" => 5, "remark" => "Satisfactory"];
            }
            if ($marksNum >= 35) {
                return ["grade" => "S", "points" => 6, "remark" => "Subsidiary"];
            }

            return ["grade" => "F", "points" => 7, "remark" => "Fail"];
        }

        if ($marksNum >= 75) {
            return ["grade" => "A", "points" => 1, "remark" => "Excellent"];
        }
        if ($marksNum >= 65) {
            return ["grade" => "B", "points" => 2, "remark" => "Very Good"];
        }
        if ($marksNum >= 45) {
            return ["grade" => "C", "points" => 3, "remark" => "Good"];
        }
        if ($marksNum >= 30) {
            return ["grade" => "D", "points" => 4, "remark" => "Pass"];
        }

        return ["grade" => "F", "points" => 5, "remark" => "Fail"];
    }

    /**
     * Normalize level_scope to all | a_level | o_level.
     * Legacy empty/null → all (applies to every class).
     */
    private function normalizeLevelScope(?string $scope): string
    {
        $scope = strtolower(trim((string) $scope));
        if (in_array($scope, ["a_level", "o_level", "all"], true)) {
            return $scope;
        }

        return "all";
    }

    /**
     * Preferred definition scope for a class name.
     */
    private function levelScopeForClassName(?string $className): string
    {
        if ($this->isALevelClassName($className)) {
            return "a_level";
        }

        return "o_level";
    }

    /**
     * Pick term-report definition for school/term/year + class.
     * Prefer exact level (a_level/o_level), else fall back to "all" / legacy.
     */
    private function resolveTermReportDefinition(
        $schoolID,
        $year,
        string $term,
        ?string $className = null,
    ): ?TermReportDefinition {
        $query = TermReportDefinition::where("schoolID", $schoolID)->where(
            "term",
            $term,
        );
        if (is_array($year) && count($year) > 0) {
            $query->whereIn("year", array_map("intval", $year));
        } elseif ($year) {
            $query->where("year", is_array($year) ? ($year[0] ?? null) : $year);
        }

        $defs = $query->get();
        if ($defs->isEmpty()) {
            return null;
        }

        $preferred = $className
            ? $this->levelScopeForClassName($className)
            : "all";

        $match = $defs->first(
            fn($d) => $this->normalizeLevelScope($d->level_scope ?? null) ===
                $preferred,
        );
        if ($match) {
            return $match;
        }

        return $defs->first(
            fn($d) => $this->normalizeLevelScope($d->level_scope ?? null) ===
                "all",
        ) ?:
            $defs->first();
    }

    /**
     * Pick CA definition for a main exam + class level.
     */
    private function resolveCaDefinition(
        $schoolID,
        $examID,
        ?string $className = null,
    ): ?CaDefinition {
        $defs = CaDefinition::where("schoolID", $schoolID)
            ->where("examID", $examID)
            ->get();
        if ($defs->isEmpty()) {
            return null;
        }

        $preferred = $className
            ? $this->levelScopeForClassName($className)
            : "all";

        $match = $defs->first(
            fn($d) => $this->normalizeLevelScope($d->level_scope ?? null) ===
                $preferred,
        );
        if ($match) {
            return $match;
        }

        return $defs->first(
            fn($d) => $this->normalizeLevelScope($d->level_scope ?? null) ===
                "all",
        ) ?:
            $defs->first();
    }

    /**
     * Weighted total: sum(marks * percent/100). Missing marks count as 0.
     *
     * @param  array<int|string, float|null>  $marksByExamId
     * @param  array<int|string, float|int>  $weights  examID => percent (0-100)
     */
    private function computePercentWeightedTotal(
        array $marksByExamId,
        array $weights,
    ): float {
        $total = 0.0;
        foreach ($weights as $examID => $percent) {
            $pct = (float) $percent;
            if ($pct <= 0) {
                continue;
            }
            $marks = $marksByExamId[$examID] ?? ($marksByExamId[(string) $examID] ?? null);
            $marksNum =
                $marks !== null && $marks !== "" ? (float) $marks : 0.0;
            $total += $marksNum * ($pct / 100.0);
        }

        return $total;
    }

    /**
     * Display label: "Midterm (50%)" when percent mode is on.
     */
    private function formatExamLabelWithWeight(
        string $examName,
        $examID,
        ?array $weights,
        bool $usePercent,
    ): string {
        if (!$usePercent || empty($weights)) {
            return $examName;
        }
        $pct =
            $weights[$examID] ??
            ($weights[(string) $examID] ??
                ($weights[(int) $examID] ?? null));
        if ($pct === null || $pct === "") {
            return $examName;
        }

        return $examName . " (" . rtrim(rtrim(number_format((float) $pct, 1), "0"), ".") . "%)";
    }

    /**
     * Validate percent weights: must sum to 100 (±0.01) when percent mode on.
     *
     * @param  array<int|string, mixed>  $weights
     */
    private function validatePercentWeights(
        bool $usePercent,
        array $examIds,
        array $weights,
    ): ?string {
        if (!$usePercent) {
            return null;
        }
        if (empty($examIds)) {
            return "Select at least one exam for percent division.";
        }
        $sum = 0.0;
        foreach ($examIds as $id) {
            $key = (string) $id;
            if (!isset($weights[$key]) && !isset($weights[(int) $id])) {
                return "Please assign a percent for every selected exam.";
            }
            $pct = (float) ($weights[$key] ?? $weights[(int) $id]);
            if ($pct < 0 || $pct > 100) {
                return "Each percent must be between 0 and 100.";
            }
            $sum += $pct;
        }
        if (abs($sum - 100.0) > 0.05) {
            $remaining = round(100.0 - $sum, 2);
            if ($remaining > 0) {
                return "Remaining {$remaining}% - please assign the rest to an exam.";
            }

            return "Percent total is {$sum}%. It must equal 100%.";
        }

        return null;
    }

    /**
     * Marks for one term-report exam column - use the column's own marks only.
     * Do not fold CA tests into the parent here: CA tests that are in the report
     * are their own columns, and CA tests outside the report must not wipe a
     * scored parent column (e.g. Terminal 85 ignored because Monthly Test missing).
     */
    private function resolveTermReportExamSlotMarks(
        array $data,
        $exam,
        $caDefinitionsByExam = null,
    ): ?float {
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
     * Term report subject average:
     * - Equal mode: jumla ÷ idadi ya mitihani (missing = 0)
     * - Percent mode: sum(marks × percent/100)  e.g. 100×25% + 100×25% + 100×50% = 100
     * - Incomplete only when ALL slots have no marks
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
        $reportExaminations,
        $caDefinitionsByExam = null,
        bool $usePercentDivision = false,
        ?array $examWeights = null,
    ): array {
        $examSlotCount = $reportExaminations->count();
        $slotMarksList = [];
        $sum = 0.0;
        $markedSlotCount = 0;
        $marksByExamId = [];

        foreach ($reportExaminations as $exam) {
            $slotMarks = $this->resolveTermReportExamSlotMarks(
                $data,
                $exam,
                $caDefinitionsByExam,
            );
            $marksByExamId[$exam->examID] = $slotMarks;
            if ($slotMarks !== null) {
                $slotMarksList[] = $slotMarks;
                $sum += $slotMarks;
                $markedSlotCount++;
            }
        }

        $isIncomplete = $markedSlotCount === 0 || $examSlotCount === 0;

        $average = null;
        if (!$isIncomplete) {
            if ($usePercentDivision && !empty($examWeights)) {
                $average = $this->computePercentWeightedTotal(
                    $marksByExamId,
                    $examWeights,
                );
            } else {
                $average = $sum / $examSlotCount;
            }
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
     * One grade-point entry per subject from term averages (NECTA: O-Level best 7 / A-Level combie 3).
     *
     * @return list<array{points: int, marks: float, grade: string, subjectID?: int}>
     */
    private function buildTermReportSubjectPointEntries(
        $results,
        $effectiveExaminations,
        $caDefinitionsByExam,
        string $schoolType,
        string $className,
        int $studentID,
        array $electedClassSubjectIds = [],
        bool $usePercentDivision = false,
        ?array $examWeights = null,
    ): array {
        if (
            strtolower(trim($schoolType)) !== "secondary" ||
            $results->isEmpty()
        ) {
            return [];
        }

        // Group by subjectID so duo papers can be aggregated per exam
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

            $subject = $result->classSubject->subject ?? null;
            if (!$subject) {
                continue;
            }
            $subjectID = (int) $subject->subjectID;
            $subjectName = $subject->subject_name ?? "N/A";

            if (!isset($subjectData[$subjectID])) {
                if (
                    ($subject->is_duo ?? false) &&
                    !$subject->relationLoaded("components")
                ) {
                    $subject->load("components");
                }
                $subjectData[$subjectID] = [
                    "subject" => $subject,
                    "subject_name" => $subjectName,
                    "exams" => [],
                    "paper_rows" => [],
                ];
            }

            if ($result->marks !== null && $result->marks !== "") {
                $examID = $result->examID;
                $isDuo =
                    (bool) ($subject->is_duo ?? false) &&
                    ($subject->components?->count() ?? 0) > 0;

                if ($isDuo) {
                    if (!isset($subjectData[$subjectID]["paper_rows"][$examID])) {
                        $subjectData[$subjectID]["paper_rows"][$examID] = [];
                    }
                    $subjectData[$subjectID]["paper_rows"][$examID][] = $result;
                } else {
                    $subjectData[$subjectID]["exams"][$examID] = [
                        "marks" => (float) $result->marks,
                    ];
                }
            }
        }

        // Convert duo paper rows → % per exam
        foreach ($subjectData as $subjectID => &$data) {
            $subject = $data["subject"];
            $isDuo =
                (bool) ($subject->is_duo ?? false) &&
                ($subject->components?->count() ?? 0) > 0;
            if (!$isDuo) {
                continue;
            }
            foreach ($data["paper_rows"] as $examID => $rows) {
                $raw = 0.0;
                $max = 0;
                $any = false;
                foreach ($subject->components as $comp) {
                    $max += max(1, (int) $comp->max_marks);
                    $row = collect($rows)->first(
                        fn($r) => (int) ($r->componentID ?? 0) ===
                            (int) $comp->componentID,
                    );
                    if (
                        $row &&
                        $row->marks !== null &&
                        $row->marks !== ""
                    ) {
                        $raw += (float) $row->marks;
                        $any = true;
                    }
                }
                if ($any && $max > 0) {
                    $data["exams"][$examID] = [
                        "marks" => round(($raw / $max) * 100, 2),
                    ];
                }
            }
        }
        unset($data);

        $entries = [];
        foreach ($subjectData as $subjectID => $data) {
            $slotCollect = $this->collectTermReportSlotMarksForSubject(
                $data,
                $effectiveExaminations,
                $caDefinitionsByExam,
                $usePercentDivision,
                $examWeights,
            );

            if ($slotCollect["is_incomplete"]) {
                continue;
            }

            $average = $slotCollect["average"];
            $gradeInfo = $this->getSecondaryHardcodedGrade($average, $className);
            if ($gradeInfo["grade"] === null) {
                continue;
            }

            $entries[] = [
                "points" => (int) ($gradeInfo["points"] ?? ($this->isALevelClassName($className) ? 7 : 5)),
                "marks" => $average,
                "grade" => $gradeInfo["grade"],
                "subjectID" => (int) $subjectID,
                "subject_name" => $data["subject_name"],
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
        bool $usePercentDivision = false,
        ?array $examWeights = null,
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

            $subject = $result->classSubject->subject ?? null;
            if (!$subject) {
                continue;
            }
            $subjectID = (int) $subject->subjectID;
            $subjectName = $subject->subject_name ?? "N/A";

            if (!isset($subjectData[$subjectID])) {
                if (
                    ($subject->is_duo ?? false) &&
                    !$subject->relationLoaded("components")
                ) {
                    $subject->load("components");
                }
                $subjectData[$subjectID] = [
                    "subject" => $subject,
                    "subject_name" => $subjectName,
                    "exams" => [],
                    "paper_rows" => [],
                ];
            }

            if ($result->marks !== null && $result->marks !== "") {
                $examID = $result->examID;
                $isDuo =
                    (bool) ($subject->is_duo ?? false) &&
                    ($subject->components?->count() ?? 0) > 0;
                if ($isDuo) {
                    if (!isset($subjectData[$subjectID]["paper_rows"][$examID])) {
                        $subjectData[$subjectID]["paper_rows"][$examID] = [];
                    }
                    $subjectData[$subjectID]["paper_rows"][$examID][] = $result;
                } else {
                    $subjectData[$subjectID]["exams"][$examID] = [
                        "marks" => (float) $result->marks,
                    ];
                }
            }
        }

        foreach ($subjectData as $subjectID => &$data) {
            $subject = $data["subject"];
            $isDuo =
                (bool) ($subject->is_duo ?? false) &&
                ($subject->components?->count() ?? 0) > 0;
            if (!$isDuo) {
                continue;
            }
            foreach ($data["paper_rows"] as $examID => $rows) {
                $raw = 0.0;
                $max = 0;
                $any = false;
                foreach ($subject->components as $comp) {
                    $max += max(1, (int) $comp->max_marks);
                    $row = collect($rows)->first(
                        fn($r) => (int) ($r->componentID ?? 0) ===
                            (int) $comp->componentID,
                    );
                    if ($row && $row->marks !== null && $row->marks !== "") {
                        $raw += (float) $row->marks;
                        $any = true;
                    }
                }
                if ($any && $max > 0) {
                    $data["exams"][$examID] = [
                        "marks" => round(($raw / $max) * 100, 2),
                    ];
                }
            }
        }
        unset($data);

        $summaries = [];
        foreach ($subjectData as $subjectID => $data) {
            $subjectName = $data["subject_name"];
            $slotCollect = $this->collectTermReportSlotMarksForSubject(
                $data,
                $effectiveExaminations,
                $caDefinitionsByExam,
                $usePercentDivision,
                $examWeights,
            );

            if ($slotCollect["is_incomplete"]) {
                $summaries[] = [
                    "subject_name" => $subjectName,
                    "subjectID" => $subjectID,
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
                "subjectID" => $subjectID,
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
     * NECTA secondary: O-Level best 7 subjects; A-Level = 3 combination subjects only.
     *
     * @param  list<array{points: int|float, marks: float, grade?: string|null, subjectID?: int|null}>  $subjectPoints
     * @param  list<int>|null  $combinationSubjectIds  A-Level combie subject IDs (max 3)
     * @return array{total_points: int, grades: list<string>, counted_subjects: int}
     */
    private function resolveSecondaryBestSubjectsForDivision(
        array $subjectPoints,
        string $className,
        string $schoolType,
        ?array $combinationSubjectIds = null,
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

        if ($this->isALevelClassName($className)) {
            $combinationSubjectIds = array_values(
                array_filter(array_map("intval", $combinationSubjectIds ?? [])),
            );

            // Preferred: exactly the 3 (or fewer) combination subjects assigned to the stream
            if (!empty($combinationSubjectIds)) {
                $bySubjectId = [];
                foreach ($subjectPoints as $entry) {
                    $sid = (int) ($entry["subjectID"] ?? 0);
                    if ($sid > 0) {
                        $bySubjectId[$sid] = $entry;
                    }
                }

                $totalPoints = 0;
                $grades = [];
                $counted = 0;
                foreach ($combinationSubjectIds as $sid) {
                    if (
                        isset($bySubjectId[$sid]) &&
                        isset($bySubjectId[$sid]["points"]) &&
                        $bySubjectId[$sid]["points"] !== null
                    ) {
                        $totalPoints += (int) $bySubjectId[$sid]["points"];
                        $g = strtoupper(
                            trim((string) ($bySubjectId[$sid]["grade"] ?? "")),
                        );
                        if ($g !== "") {
                            $grades[] = $g;
                        }
                        $counted++;
                    } else {
                        // Missing combination subject counts as F (7 points)
                        $totalPoints += 7;
                        $grades[] = "F";
                    }
                }

                return [
                    "total_points" => $totalPoints,
                    "grades" => $grades,
                    "counted_subjects" => $counted,
                ];
            }

            // Fallback when no combie is assigned: best 3 grades (lowest points)
            usort($subjectPoints, function ($a, $b) {
                if ($a["points"] != $b["points"]) {
                    return $a["points"] <=> $b["points"];
                }
                return $b["marks"] <=> $a["marks"];
            });

            $bestSubjects = array_slice(
                $subjectPoints,
                0,
                min(3, count($subjectPoints)),
            );
            $totalPoints = 0;
            foreach ($bestSubjects as $subject) {
                $totalPoints += (int) $subject["points"];
            }
            if (count($bestSubjects) < 3) {
                $totalPoints += (3 - count($bestSubjects)) * 7;
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
     * NECTA O-Level Division IV: 26-33 points and at least 2×D or 1×C in the best seven.
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
                // A-Level Form 5/6 - A=1 … F=7
                if ($marksNum >= 80) {
                    return ["grade" => "A", "points" => 1];
                } elseif ($marksNum >= 70) {
                    return ["grade" => "B", "points" => 2];
                } elseif ($marksNum >= 60) {
                    return ["grade" => "C", "points" => 3];
                } elseif ($marksNum >= 50) {
                    return ["grade" => "D", "points" => 4];
                } elseif ($marksNum >= 40) {
                    return ["grade" => "E", "points" => 5];
                } elseif ($marksNum >= 35) {
                    return ["grade" => "S", "points" => 6];
                } else {
                    return ["grade" => "F", "points" => 7];
                }
            }
        }

        // Primary or default - no points calculation
        return ["grade" => null, "points" => null];
    }

    /**
     * Sum subject points for Secondary (O-Level best 7, A-Level combination subjects).
     */
    private function computeTotalPointsFromSubjectEntries(
        array $subjectPoints,
        string $className,
        string $schoolType = "Secondary",
        ?array $combinationSubjectIds = null,
    ): int {
        return $this->resolveSecondaryBestSubjectsForDivision(
            $subjectPoints,
            $className,
            $schoolType,
            $combinationSubjectIds,
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
            $this->isALevelClassName($className)
        ) {
            // A-Level Form 5/6: points from combination subjects only
            // A=1, B=2, C=3, D=4, E=5, S=6, F=7
            // Division I: 3-9, II: 10-12, III: 13-17, IV: 18-19, 0 from 20

            if ($totalPoints >= 3 && $totalPoints <= 9) {
                return ["grade" => null, "division" => "I." . $totalPoints];
            }
            if ($totalPoints >= 10 && $totalPoints <= 12) {
                return ["grade" => null, "division" => "II." . $totalPoints];
            }
            if ($totalPoints >= 13 && $totalPoints <= 17) {
                return ["grade" => null, "division" => "III." . $totalPoints];
            }
            if ($totalPoints >= 18 && $totalPoints <= 19) {
                return ["grade" => null, "division" => "IV." . $totalPoints];
            }

            return ["grade" => null, "division" => "0." . $totalPoints];
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
     * Specific stream filter from manageResults (empty / 0 / all = All Subclasses).
     */
    private function normalizeResultsSubclassFilter($value): ?int
    {
        if (
            $value === null ||
            $value === "" ||
            $value === "0" ||
            strtolower((string) $value) === "all"
        ) {
            return null;
        }
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return $id === false ? null : (int) $id;
    }

    private function studentResultStreamId($student): ?int
    {
        $sid = $student->subclassID ?? ($student->old_subclassID ?? null);
        if (!$sid && $student->subclass) {
            $sid = $student->subclass->subclassID ?? null;
        }
        if (!$sid && $student->oldSubclass) {
            $sid = $student->oldSubclass->subclassID ?? null;
        }

        return $sid ? (int) $sid : null;
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
     * Students for PDF/Excel export: Active enrollments for the header academic year.
     */
    private function studentsForResultDownload(Request $request, $schoolID)
    {
        $enrollment = app(StudentEnrollmentService::class);
        $option = $request->get("option", "all");
        $studentID = $request->get("studentID", null);
        $classID = $request->get("classID") ?: $request->get("class");
        $subclassID = $request->get("subclassID") ?: $request->get("subclass");
        $statusFilter = $this->normalizeResultsEnrollmentStatusFilter(
            $request->get("status", "Active"),
        );

        $teacherID = Session::get("teacherID");
        $userType = Session::get("user_type");
        if (
            $option !== "single" &&
            empty($subclassID) &&
            empty($classID) &&
            !empty($teacherID) &&
            $userType !== "Admin"
        ) {
            $assigned = Subclass::where("teacherID", $teacherID)
                ->where("status", "Active")
                ->get();
            if ($assigned->isNotEmpty()) {
                $ids = [];
                foreach ($assigned as $sub) {
                    $ids = array_merge(
                        $ids,
                        $enrollment->studentIDsInSubclass(
                            (int) $sub->subclassID,
                            (int) $schoolID,
                            "Active",
                        ),
                    );
                }
                $ids = array_values(array_unique(array_map("intval", $ids)));
                if (empty($ids)) {
                    return collect();
                }

                $withAssigned = [
                    "subclass.class",
                    "subclass.combie.subjects",
                    "subclass.teacher",
                    "parent",
                    "oldSubclass.class",
                    "oldSubclass.combie.subjects",
                    "oldSubclass.teacher",
                ];

                return Student::where("schoolID", $schoolID)
                    ->whereIn("studentID", $ids)
                    ->with($withAssigned)
                    ->orderBy("first_name")
                    ->get();
            }
        }

        $with = [
            "subclass.class",
            "subclass.combie.subjects",
            "subclass.teacher",
            "parent",
            "oldSubclass.class",
            "oldSubclass.combie.subjects",
            "oldSubclass.teacher",
        ];

        if ($option === "single" && $studentID) {
            return Student::where("studentID", $studentID)
                ->where("schoolID", $schoolID)
                ->with($with)
                ->get();
        }

        if ($option === "class" && $classID) {
            $ids = $enrollment->studentIDsInClass(
                (int) $classID,
                (int) $schoolID,
                "Active",
            );
            if (empty($ids)) {
                return collect();
            }

            return Student::where("schoolID", $schoolID)
                ->whereIn("studentID", $ids)
                ->with($with)
                ->orderBy("first_name")
                ->get();
        }

        if ($option === "subclass" && $subclassID) {
            $ids = $enrollment->studentIDsInSubclass(
                (int) $subclassID,
                (int) $schoolID,
                "Active",
            );
            if (empty($ids)) {
                return collect();
            }

            return Student::where("schoolID", $schoolID)
                ->whereIn("studentID", $ids)
                ->with($with)
                ->orderBy("first_name")
                ->get();
        }

        $classFilterID = $classID;
        $subclassFilterID = $subclassID;
        $genderFilter = $request->get("gender", "");

        $studentQuery = Student::where("schoolID", $schoolID);

        if ($statusFilter === "Active") {
            if ($subclassFilterID) {
                $ids = $enrollment->studentIDsInSubclass(
                    (int) $subclassFilterID,
                    (int) $schoolID,
                    "Active",
                );
            } elseif ($classFilterID) {
                $ids = $enrollment->studentIDsInClass(
                    (int) $classFilterID,
                    (int) $schoolID,
                    "Active",
                );
            } else {
                $ids = $enrollment
                    ->activeEnrollments((int) $schoolID)
                    ->pluck("studentID")
                    ->unique()
                    ->values()
                    ->all();
            }

            if (empty($ids)) {
                return collect();
            }

            $studentQuery->whereIn("studentID", $ids);
        } elseif ($statusFilter === "Transferred" || $statusFilter === "history") {
            $studentQuery->whereNotNull("old_subclassID");

            if ($subclassFilterID) {
                $studentQuery->where("subclassID", $subclassFilterID);
            } elseif ($classFilterID) {
                $studentQuery->where(function ($q) use ($classFilterID) {
                    $q->whereHas("subclass", function ($sq) use ($classFilterID) {
                        $sq->where("classID", $classFilterID);
                    })->orWhereHas("oldSubclass", function ($sq) use (
                        $classFilterID,
                    ) {
                        $sq->where("classID", $classFilterID);
                    });
                });
            }
        } else {
            if ($subclassFilterID) {
                $studentQuery->where("subclassID", $subclassFilterID);
            } elseif ($classFilterID) {
                $studentQuery->where(function ($q) use ($classFilterID) {
                    $q->whereHas("subclass", function ($sq) use ($classFilterID) {
                        $sq->where("classID", $classFilterID);
                    })->orWhereHas("oldSubclass", function ($sq) use (
                        $classFilterID,
                    ) {
                        $sq->where("classID", $classFilterID);
                    });
                });
            }
        }

        if ($genderFilter) {
            $studentQuery->where("gender", $genderFilter);
        }

        return $studentQuery
            ->with($with)
            ->orderBy("first_name")
            ->get();
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
        $typeFilter = $request->get("type", "exam");
        $examFilter = $request->get("examID", "");
        $statusFilter = $this->normalizeResultsEnrollmentStatusFilter(
            $request->get("status", "Active"),
        );

        // Same academic-year source as manageResults page (header selection)
        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        $yearFilter = $yearCtx["calendar_year"]
            ?: $request->get("year", date("Y"));
        $calendarYears = !empty($yearCtx["calendar_years"])
            ? $yearCtx["calendar_years"]
            : ($yearFilter ? [(int) $yearFilter] : []);

        // Get school details
        $school = \App\Models\School::find($schoolID);
        $schoolType = $school ? $school->school_type : "Secondary";

        // Get students based on option (Active enrollments for selected year)
        $students = $this->studentsForResultDownload($request, $schoolID);

        // Get results data
        $resultsData = [];
        if (
            $typeFilter === "exam" &&
            $examFilter &&
            !$termFilter
        ) {
            $examForTerm = Examination::where("schoolID", $schoolID)
                ->where("examID", $examFilter)
                ->first();
            if (
                $examForTerm &&
                !empty($examForTerm->term) &&
                $examForTerm->term !== "all_terms"
            ) {
                $termFilter = $examForTerm->term;
            }
        }

        $canLoadExamResults =
            $typeFilter === "exam" &&
            ($yearFilter || !empty($academicYearIDs)) &&
            ($examFilter || $termFilter);
        $canLoadTermReport =
            $typeFilter !== "exam" &&
            $termFilter &&
            ($yearFilter || !empty($academicYearIDs));

        if ($canLoadExamResults || $canLoadTermReport) {
            if ($typeFilter === "exam") {
                // Handle CA Definitions for exam filtering
                $caExamIDs = $examFilter;
                if ($examFilter && !is_array($examFilter)) {
                    $caDef = $this->resolveCaDefinition(
                        $schoolID,
                        $examFilter,
                        null,
                    );
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
                    null,
                    null,
                    $schoolID,
                    $academicYearIDs,
                );
            } else {
                $resultsData = $this->getTermReport(
                    $students,
                    $termFilter,
                    $yearFilter,
                    $schoolType,
                    null,
                    $schoolID,
                    $academicYearIDs,
                );
            }
        }

        // Filter students to only those with results
        $students = $students->filter(function ($student) use ($resultsData) {
            return isset($resultsData[$student->studentID]);
        });

        // Never show unelected Optional (election) subjects on individual / list PDF payloads
        foreach ($students as $student) {
            $sid = (int) $student->studentID;
            if (!isset($resultsData[$sid])) {
                continue;
            }
            $subclassID =
                $student->subclassID ??
                ($student->old_subclassID ??
                    ($student->subclass->subclassID ??
                        ($student->oldSubclass->subclassID ?? null)));
            $electedIds = $this->getElectedClassSubjectIdsForStudent($sid);

            if ($typeFilter === "exam" && is_array($resultsData[$sid])) {
                foreach ($resultsData[$sid] as $idx => $examRow) {
                    if (
                        isset($examRow["subjects"]) &&
                        is_array($examRow["subjects"])
                    ) {
                        $resultsData[$sid][$idx]["subjects"] =
                            $this->filterSubjectPayloadForStudentElection(
                                $examRow["subjects"],
                                $sid,
                                $subclassID ? (int) $subclassID : null,
                                $electedIds,
                            );
                        $resultsData[$sid][$idx]["subject_count"] = count(
                            $resultsData[$sid][$idx]["subjects"],
                        );
                    }
                }
            } elseif (
                isset($resultsData[$sid]["subjects"]) &&
                is_array($resultsData[$sid]["subjects"])
            ) {
                $resultsData[$sid]["subjects"] =
                    $this->filterSubjectPayloadForStudentElection(
                        $resultsData[$sid]["subjects"],
                        $sid,
                        $subclassID ? (int) $subclassID : null,
                        $electedIds,
                    );
            }
        }

        if ($students->isEmpty()) {
            return response(
                "No results found for the selected filters.",
                422,
                ["Content-Type" => "text/plain; charset=UTF-8"],
            );
        }
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
                if (
                    is_array($detailedSingleData) &&
                    isset($detailedSingleData["subjects"]) &&
                    is_array($detailedSingleData["subjects"])
                ) {
                    $detailStudent = $students->firstWhere(
                        "studentID",
                        (int) $studentID,
                    );
                    $detailSubclassID =
                        $detailStudent->subclassID ??
                        ($detailStudent->old_subclassID ??
                            ($detailStudent->subclass->subclassID ??
                                ($detailStudent->oldSubclass->subclassID ??
                                    null)));
                    $detailedSingleData["subjects"] =
                        $this->filterSubjectPayloadForStudentElection(
                            $detailedSingleData["subjects"],
                            (int) $studentID,
                            $detailSubclassID ? (int) $detailSubclassID : null,
                        );
                    $detailedSingleData["subjectCount"] = count(
                        $detailedSingleData["subjects"],
                    );
                }
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
                    $bulkPayload = json_decode($res->content(), true);
                    if (
                        is_array($bulkPayload) &&
                        isset($bulkPayload["subjects"]) &&
                        is_array($bulkPayload["subjects"])
                    ) {
                        $bulkSubclassID =
                            $student->subclassID ??
                            ($student->old_subclassID ??
                                ($student->subclass->subclassID ??
                                    ($student->oldSubclass->subclassID ??
                                        null)));
                        $bulkPayload["subjects"] =
                            $this->filterSubjectPayloadForStudentElection(
                                $bulkPayload["subjects"],
                                (int) $student->studentID,
                                $bulkSubclassID ? (int) $bulkSubclassID : null,
                            );
                        $bulkPayload["subjectCount"] = count(
                            $bulkPayload["subjects"],
                        );
                    }
                    $detailedBulkData[$student->studentID] = $bulkPayload;
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

        // Align filter keys with manageResults form (export sends classID/subclassID)
        $filterClassID = $request->get("classID", "") ?: $request->get("class", "");
        $filterSubclassID =
            $request->get("subclassID", "") ?: $request->get("subclass", "");

        // Title / overview scope: always class + subclass when filtered (e.g. FORM FIVE / A).
        // Per-student "Class" cells may still show subclass-only for A-Level.
        $filterScopeLabel = "";
        if ($filterSubclassID) {
            $scopeSub = Subclass::with("class")->find($filterSubclassID);
            if ($scopeSub) {
                $scopeMain =
                    $scopeSub->class->class_name ??
                    (ClassModel::find($filterClassID)?->class_name ?? "");
                $scopeStream = trim((string) ($scopeSub->subclass_name ?? ""));
                $filterScopeLabel =
                    $scopeStream !== ""
                        ? trim($scopeMain . " / " . $scopeStream)
                        : $scopeMain;
            }
        } elseif ($filterClassID) {
            $filterScopeLabel =
                ClassModel::find($filterClassID)?->class_name ?? "";
        }

        if ($filterScopeLabel !== "") {
            $title .= " - " . $filterScopeLabel;
        }

        // Exam index numbers (same sequence as Excel board / attendance)
        $examNumbers = [];
        $classIdsForExamNos = $students
            ->map(function ($s) {
                return $s->subclass->class->classID
                    ?? ($s->oldSubclass->class->classID ?? null);
            })
            ->filter()
            ->unique()
            ->values();
        foreach ($classIdsForExamNos as $cid) {
            $examNumbers += $this->buildExamNumberMapForClass(
                (int) $cid,
                $school,
            );
        }

        // Board payloads for class / all / subclass Export All (Excel-like landscape PDF)
        $boardClasses = [];
        $useBoardPdf = !in_array($option, ["single", "bulk_single"], true);
        $nameMode = strtolower(trim((string) $request->get("name_mode", "both")));
        if (!in_array($nameMode, ["both", "name", "index"], true)) {
            $nameMode = "both";
        }
        $rankBy = strtolower(trim((string) $request->get("rank_by", "average")));
        if (!in_array($rankBy, ["average", "division"], true)) {
            $rankBy = "average";
        }
        if ($useBoardPdf && $students->isNotEmpty()) {
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
                $cid = $cid ?: 0;
                $studentsByClass[$cid][] = $student;
            }
            foreach ($studentsByClass as $cid => $classStudents) {
                $mainClassName =
                    $cid > 0
                        ? ClassModel::find($cid)?->class_name ?? "Class {$cid}"
                        : "Unknown Class";
                $className = $mainClassName;
                if ($option === "subclass" && $subclassID) {
                    $exportSubclass = Subclass::with("class")->find($subclassID);
                    if ($exportSubclass) {
                        $mainName =
                            $exportSubclass->class->class_name ?? $mainClassName;
                        $subName = trim(
                            (string) ($exportSubclass->subclass_name ?? ""),
                        );
                        if ($this->isALevelClassName($mainName)) {
                            $className =
                                $subName !== ""
                                    ? strtoupper($mainName) .
                                        " - " .
                                        strtoupper($subName)
                                    : strtoupper($mainName);
                        } else {
                            $className = $this->formatSubclassFilterDisplayName(
                                $mainName,
                                $subName,
                            );
                        }
                    }
                }
                $classExamNos =
                    $cid > 0
                        ? $this->buildExamNumberMapForClass((int) $cid, $school)
                        : $examNumbers;
                $payload = $this->buildBoardPdfClassData(
                    $className,
                    $titleLine,
                    $classStudents,
                    $resultsData,
                    $classExamNos,
                    $schoolType,
                    $typeFilter,
                    $examFilter,
                    $cid > 0 ? $cid : null,
                    $option === "class" || $option === "all",
                    $nameMode,
                    $rankBy,
                );
                if ($payload) {
                    $boardClasses[] = $payload;
                }
            }
        }

        // Fee summary for ADA block on individual PDF (same year as header / view_payments)
        $studentFeeSummaries = $this->buildStudentFeeSummariesForReport(
            $students->pluck("studentID")->map(fn($id) => (int) $id)->all(),
            (int) $schoolID,
            is_array($academicYearIDs) ? $academicYearIDs : [],
        );

        $schoolCalendar = $this->resolveSchoolCalendarForIndividualReport(
            (int) $schoolID,
            (string) $typeFilter,
            $examFilter,
            $termFilter,
            $yearFilter,
            $students,
        );

        // Prepare data for PDF
        $data = [
            "school" => $school,
            "schoolType" => $schoolType,
            "students" => $students,
            "resultsData" => $resultsData,
            "detailedSingleData" => $detailedSingleData,
            "detailedBulkData" => $detailedBulkData,
            "examNumbers" => $examNumbers,
            "filterScopeLabel" => $filterScopeLabel,
            "studentFeeSummaries" => $studentFeeSummaries,
            "schoolCalendar" => $schoolCalendar,
            "boardClasses" => $boardClasses,
            "useBoardPdf" => $useBoardPdf,
            "filters" => [
                "term" => $termFilter,
                "year" => $yearFilter,
                "type" => $typeFilter,
                "status" => $statusFilter,
                "examID" => $examFilter,
                "class" => $filterClassID,
                "subclass" => $filterSubclassID,
                "grade" => $request->get("grade", ""),
                "gender" => $request->get("gender", ""),
            ],
            "option" => $option,
            "title" => $title,
            "showWatermark" => $request->boolean("watermark", true),
            "headerRepeat" => $useBoardPdf && $request->boolean("header_repeat", false),
            "pdfScale" => $this->normalizeIndividualPdfScale($request->input("pdf_scale", 1)),
            "characterAssessments" => $this->getLatestCharacterAssessments(
                $students,
                $termFilter,
                $yearFilter,
                $schoolID,
            ),
        ];

        try {
            // Generate PDF
            $pdf = PDF::loadView("Admin.pdf.results", $data);
            CenturyGothicPdfFont::ensureRegistered($pdf->getDomPDF());
            // Board: landscape; individual student report: portrait (one page)
            $landscape = $useBoardPdf;
            $orientation = $landscape ? "landscape" : "portrait";
            $pdf->setPaper("a4", $orientation);
            // Reinforce DomPDF paper (some versions ignore first setPaper)
            try {
                $pdf->getDomPDF()->setPaper("A4", $orientation);
            } catch (\Throwable $e) {
                // ignore
            }
            // Individual reports: keep content denser so one portrait page fits
            if (in_array($option, ["single", "bulk_single"], true)) {
                try {
                    $pdf->setOptions([
                        "dpi" => 96,
                        "defaultFont" => "Helvetica",
                        "isRemoteEnabled" => true,
                    ]);
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // Generate filename
            $filename = "Results_";
            if ($option === "single" && $students->count() > 0) {
                $student = $students->first();
                $filename .=
                    $student->first_name . "_" . $student->last_name . "_";
            } elseif ($option === "class" && $classID) {
                $class = ClassModel::find($classID);
                $filename .=
                    ($class
                        ? str_replace(" ", "_", $class->class_name)
                        : "Class") . "_";
            } elseif ($option === "subclass" && $subclassID) {
                $subclass = Subclass::find($subclassID);
                $filename .=
                    ($subclass
                        ? str_replace(" ", "_", $subclass->subclass_name)
                        : "Subclass") . "_";
            } else {
                $filename .= "All_Students_";
            }
            $filename .=
                ($typeFilter === "exam" ? "Exam" : "Term_Report") . "_";
            $filename .=
                ($termFilter ? str_replace("_", "", $termFilter) . "_" : "") .
                $yearFilter .
                ".pdf";
            $filename = preg_replace(
                '/[\\\\\\/\\?\\*\\[\\]:<>\\|"]+/',
                "_",
                $filename,
            );

            // Page numbers on every page (board + individual) - does not change layout scale
            try {
                $dompdf = $pdf->getDomPDF();
                $dompdf->render();
                $canvas = $dompdf->getCanvas();
                $fontMetrics = $dompdf->getFontMetrics();
                $font = $fontMetrics->getFont("Helvetica", "normal");
                $size = 8;
                $color = [0.35, 0.35, 0.35];
                $w = $canvas->get_width();
                $h = $canvas->get_height();
                // Bottom-center; leave room above edge
                $y = $h - 14;
                $canvas->page_text(
                    $w / 2 - 36,
                    $y,
                    "Page {PAGE_NUM} of {PAGE_COUNT}",
                    $font,
                    $size,
                    $color,
                );

                return response($dompdf->output(), 200, [
                    "Content-Type" => "application/pdf",
                    "Content-Disposition" =>
                        ($request->boolean("preview") ? "inline" : "attachment")
                        . '; filename="' . $filename . '"',
                ]);
            } catch (\Throwable $pageErr) {
                \Log::warning("PDF page numbers skipped: " . $pageErr->getMessage());
                if ($request->boolean("preview")) {
                    return $pdf->stream($filename);
                }
                return $pdf->download($filename);
            }
        } catch (\Throwable $e) {
            \Log::error("downloadPdf failed: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);

            return response(
                "Failed to generate PDF: " . $e->getMessage(),
                500,
                ["Content-Type" => "text/plain; charset=UTF-8"],
            );
        }
    }

    /**
     * Individual report PDF scale. 1.0 = current default (unchanged).
     * Allowed range: 1.00 - 1.40.
     */
    private function normalizeIndividualPdfScale($raw): float
    {
        $scale = is_numeric($raw) ? (float) $raw : 1.0;
        if ($scale < 1.0) {
            $scale = 1.0;
        }
        if ($scale > 1.4) {
            $scale = 1.4;
        }
        // Snap near 1.0 so floating noise never triggers scale CSS
        if (abs($scale - 1.0) < 0.001) {
            return 1.0;
        }

        return round($scale, 2);
    }

    /**
     * Exam numbers per class - same sequence as attendance template / add-results.
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
        // Do not use !$division / empty(): PHP treats string "0" as empty.
        if ($division === null || $division === "") {
            return ["roman" => "", "points" => $fallbackPoints ?? ""];
        }

        if (preg_match("/^-?\.(\d+)$/", $division, $m)) {
            return ["roman" => "0", "points" => (int) $m[1]];
        }

        if (preg_match("/^([0IVX]+)\.(\d+)/i", $division, $m)) {
            return ["roman" => strtoupper($m[1]), "points" => (int) $m[2]];
        }

        if (preg_match("/^([0IVX]+)$/i", $division, $m)) {
            return ["roman" => strtoupper($m[1]), "points" => $fallbackPoints ?? ""];
        }

        return ["roman" => (string) $division, "points" => $fallbackPoints ?? ""];
    }

    private function hasDivisionRoman($roman): bool
    {
        return $roman !== null && $roman !== "";
    }

    private function displayDivisionRoman($roman): string
    {
        if (!$this->hasDivisionRoman($roman)) {
            return "-";
        }

        return (string) $roman;
    }

    /**
     * Division label is roman.points (0.34). A leading 0 must stay 0, never "-".
     */
    private function normalizeSecondaryDivisionLabel($division, $points = null): string
    {
        $division = trim((string) ($division ?? ""));
        if (preg_match("/^-?\.(\d+)$/", $division, $m)) {
            return "0." . $m[1];
        }
        if (preg_match("/^([0IVX]+)\.(\d+)$/i", $division, $m)) {
            return strtoupper($m[1]) . "." . $m[2];
        }
        if (
            ($division === "" || $division === "-" || strcasecmp($division, "0") === 0)
            && $points !== null
            && $points !== ""
            && (int) $points > 0
        ) {
            return "0." . (int) $points;
        }
        if ($division === "" || $division === "-") {
            return "-";
        }

        return $division;
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
                "is_all_incomplete" => !empty($payload["is_all_incomplete"])
                    || $this->isAllIncompleteFromSubjects(
                        is_array($payload["subjects"] ?? null)
                            ? $payload["subjects"]
                            : [],
                    ),
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
            "is_all_incomplete" => !empty($chosen["is_all_incomplete"])
                || $this->isAllIncompleteFromSubjects(
                    is_array($chosen["subjects"] ?? null)
                        ? $chosen["subjects"]
                        : [],
                ),
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
        ?string $classNameHint = null,
    ): float {
        $classGpaSum = 0.0;
        $classGpaCount = 0;

        foreach ($prepared as $item) {
            $student = $item["student"];
            $row = $item["row"];
            $className = $classNameHint ?? "";
            $classID = null;
            if ($student->subclass && $student->subclass->class) {
                $className = $student->subclass->class->class_name ?? $className;
                $classID = $student->subclass->class->classID ?? null;
            } elseif ($student->oldSubclass && $student->oldSubclass->class) {
                $className =
                    $student->oldSubclass->class->class_name ?? $className;
                $classID = $student->oldSubclass->class->classID ?? null;
            }

            $rowIsALevel =
                $schoolType === "Secondary" &&
                $this->isALevelClassName($className);
            $rowPtMap =
                $schoolType === "Primary"
                    ? ["A" => 5, "B" => 4, "C" => 3, "D" => 2, "E" => 1, "F" => 0]
                    : ($rowIsALevel
                        ? [
                            "A" => 1,
                            "B" => 2,
                            "C" => 3,
                            "D" => 4,
                            "E" => 5,
                            "S" => 6,
                            "F" => 7,
                        ]
                        : ["A" => 1, "B" => 2, "C" => 3, "D" => 4, "F" => 5]);

            $stuPoints = 0;
            $stuSubCount = 0;
            foreach ($row["subjects"] as $subj) {
                $grade = $this->resolveSubjectGradeLetter(
                    $subj,
                    $schoolType,
                    $className,
                    $classID,
                );
                if ($grade && isset($rowPtMap[$grade])) {
                    $stuPoints += $rowPtMap[$grade];
                    $stuSubCount++;
                }
            }

            if ($stuSubCount > 0) {
                $classGpaSum += $stuPoints / $stuSubCount;
                $classGpaCount++;
            }
        }

        return $classGpaCount > 0
            ? round($classGpaSum / $classGpaCount, 4)
            : 0.0;
    }

    /**
     * True when subject has a numeric mark (including 0). Incomplete / missing = false.
     */
    private function subjectEntryIsSat(array $subject): bool
    {
        $marks = $subject["marks"] ?? null;
        if ($marks === null || $marks === "") {
            return false;
        }
        if (strtolower(trim((string) $marks)) === "incomplete") {
            return false;
        }

        return is_numeric($marks);
    }

    /**
     * Student did not sit any exam paper → all incomplete (rank last).
     *
     * @param  list<array<string,mixed>>  $subjects
     */
    private function isAllIncompleteFromSubjects(array $subjects): bool
    {
        if ($subjects === []) {
            return true;
        }
        foreach ($subjects as $subject) {
            if (is_array($subject) && $this->subjectEntryIsSat($subject)) {
                return false;
            }
        }

        return true;
    }

    /**
     * If student sat ≥1 subject, convert remaining incompletes to 0 (not Incomplete).
     * If sat none, leave incompletes and flag is_all_incomplete.
     *
     * @param  list<array<string,mixed>>  $subjects
     * @return array{0: list<array<string,mixed>>, 1: bool}
     */
    private function applySatZeroRuleToSubjects(
        array $subjects,
        string $schoolType,
        string $className,
        $classID = null,
    ): array {
        if ($subjects === []) {
            return [[], true];
        }

        $hasSat = false;
        foreach ($subjects as $subject) {
            if ($this->subjectEntryIsSat($subject)) {
                $hasSat = true;
                break;
            }
        }

        if (!$hasSat) {
            return [$subjects, true];
        }

        foreach ($subjects as &$subject) {
            if ($this->subjectEntryIsSat($subject)) {
                continue;
            }
            $gradePoints = $this->calculateGradePoints(
                0,
                $schoolType,
                $className,
                $classID,
            );
            $subject["marks"] = 0;
            $subject["grade"] = $gradePoints["grade"] ?? "F";
            $subject["points"] = $gradePoints["points"] ?? null;
            if (array_key_exists("remark", $subject) || isset($gradePoints["remark"])) {
                $subject["remark"] = $gradePoints["remark"] ?? ($subject["remark"] ?? null);
            }
            $subject["is_incomplete"] = false;
        }
        unset($subject);

        return [$subjects, false];
    }

    /**
     * Rank compare: all-incomplete always last; then average or division rules.
     *
     * @param  array<string,mixed>  $a
     * @param  array<string,mixed>  $b
     */
    private function compareStudentsForPosition(
        array $a,
        array $b,
        string $rankBy,
        string $schoolType,
        bool $reportDivisionStyle = false,
    ): int {
        $aInc = !empty($a["is_all_incomplete"]);
        $bInc = !empty($b["is_all_incomplete"]);
        if (!$aInc && isset($a["subjects"]) && is_array($a["subjects"])) {
            $aInc = $this->isAllIncompleteFromSubjects($a["subjects"]);
        }
        if (!$bInc && isset($b["subjects"]) && is_array($b["subjects"])) {
            $bInc = $this->isAllIncompleteFromSubjects($b["subjects"]);
        }
        if ($aInc !== $bInc) {
            return ($aInc ? 1 : 0) <=> ($bInc ? 1 : 0);
        }

        $useDivision = $rankBy === "division" && $schoolType === "Secondary";
        if ($useDivision && $reportDivisionStyle) {
            $divOrder = ["I" => 1, "II" => 2, "III" => 3, "IV" => 4, "0" => 5];
            preg_match('/^([0IVX]+)\./', (string) ($a["division"] ?? ""), $mA);
            preg_match('/^([0IVX]+)\./', (string) ($b["division"] ?? ""), $mB);
            $rankA = $divOrder[$mA[1] ?? ""] ?? 999;
            $rankB = $divOrder[$mB[1] ?? ""] ?? 999;
            if ($rankA != $rankB) {
                return $rankA <=> $rankB;
            }
            $ptA = $a["total_points"] ?? 9999;
            $ptB = $b["total_points"] ?? 9999;
            if ($ptA != $ptB) {
                return $ptA <=> $ptB;
            }

            return ($b["average_marks"] ?? 0) <=> ($a["average_marks"] ?? 0);
        }

        if ($useDivision) {
            if (($a["total_points"] ?? 9999) != ($b["total_points"] ?? 9999)) {
                return ($a["total_points"] ?? 9999) <=> ($b["total_points"] ?? 9999);
            }
            if (
                ($a["best_seven_total_marks"] ?? 0) !=
                ($b["best_seven_total_marks"] ?? 0)
            ) {
                return ($b["best_seven_total_marks"] ?? 0) <=>
                    ($a["best_seven_total_marks"] ?? 0);
            }

            return ($b["average_marks"] ?? ($b["total_marks"] ?? 0)) <=>
                ($a["average_marks"] ?? ($a["total_marks"] ?? 0));
        }

        $aa = (float) ($a["average_marks"] ?? 0);
        $ba = (float) ($b["average_marks"] ?? 0);
        if ($aa != $ba) {
            return $ba <=> $aa;
        }
        $ptA = $a["total_points"] ?? 9999;
        $ptB = $b["total_points"] ?? 9999;
        if ($ptA != $ptB) {
            return $ptA <=> $ptB;
        }

        return ($b["total_marks"] ?? 0) <=> ($a["total_marks"] ?? 0);
    }

    /**
     * Sort board export rows: average (default, highest first) or division (Secondary only).
     * All-incomplete students always rank last (below zeros).
     *
     * @param  list<array<string,mixed>>  $prepared
     */
    private function sortBoardPreparedRows(
        array &$prepared,
        string $schoolType,
        string $rankBy,
    ): void {
        $rankBy = strtolower(trim($rankBy));
        if ($rankBy !== "division") {
            $rankBy = "average";
        }
        if ($rankBy === "division" && $schoolType !== "Secondary") {
            $rankBy = "average";
        }

        foreach ($prepared as &$item) {
            $row = $item["row"] ?? [];
            if (!isset($row["is_all_incomplete"])) {
                $row["is_all_incomplete"] = $this->isAllIncompleteFromSubjects(
                    is_array($row["subjects"] ?? null) ? $row["subjects"] : [],
                );
                $item["row"] = $row;
            }
        }
        unset($item);

        usort($prepared, function ($a, $b) use ($rankBy, $schoolType) {
            $left = array_merge($a["row"] ?? [], [
                "is_all_incomplete" => !empty(($a["row"] ?? [])["is_all_incomplete"]),
            ]);
            $right = array_merge($b["row"] ?? [], [
                "is_all_incomplete" => !empty(($b["row"] ?? [])["is_all_incomplete"]),
            ]);

            return $this->compareStudentsForPosition(
                $left,
                $right,
                $rankBy,
                $schoolType,
                false,
            );
        });
    }

    /**
     * Board AVERAGE: always 2 decimal places (no integer approximation).
     */
    private function formatBoardAverageMarks(float $average): string
    {
        return number_format($average, 2, ".", "");
    }

    /**
     * Order Eloquent models by an explicit ID list (definition check order).
     * Unknown IDs are skipped; leftovers (if any) append at the end.
     *
     * @param  \Illuminate\Support\Collection|iterable  $models
     * @param  array<int|string>  $orderedIds
     * @return \Illuminate\Support\Collection
     */
    private function orderModelsByIdList($models, array $orderedIds, string $key = "examID")
    {
        $byId = collect($models)->keyBy(function ($m) use ($key) {
            return (int) (is_array($m) ? ($m[$key] ?? 0) : $m->{$key});
        });
        $ordered = collect();
        foreach ($orderedIds as $id) {
            $id = (int) $id;
            if ($id > 0 && $byId->has($id)) {
                $ordered->push($byId->get($id));
                $byId->forget($id);
            }
        }
        foreach ($byId as $left) {
            $ordered->push($left);
        }

        return $ordered->values();
    }

    /**
     * Board TOTAL marks - compact, no forced rounding up to whole when fractional.
     */
    private function formatBoardTotalMarks(float $total): string
    {
        if (abs($total - round($total)) < 0.00001) {
            return (string) (int) round($total);
        }

        return number_format($total, 2, ".", "");
    }

    /**
     * Remark CSS class from letter grade (for average / competency coloring).
     */
    private function boardRemarkClassFromGrade(?string $grade): string
    {
        $g = strtoupper(trim((string) $grade));
        return match ($g) {
            "A" => "remark-excellent",
            "B" => "remark-pass",
            "C" => "remark-good",
            "D", "E", "S" => "remark-avg",
            "F" => "remark-fail",
            default => "remark-inc",
        };
    }

    /**
     * Excel ARGB fill/font for board remark classes (matches PDF legend colors).
     *
     * @return array{fill:string,font:string}
     */
    private function boardRemarkArgb(string $remarkClass): array
    {
        return match ($remarkClass) {
            "remark-excellent" => [
                "fill" => "FF28A745",
                "font" => "FFFFFFFF",
            ],
            "remark-pass" => ["fill" => "FFD4EDDA", "font" => "FF155724"],
            "remark-good" => ["fill" => "FFFFF3CD", "font" => "FF856404"],
            "remark-avg" => ["fill" => "FFFFE8D6", "font" => "FFA04000"],
            "remark-fail" => ["fill" => "FFF8D7DA", "font" => "FF721C24"],
            default => ["fill" => "FFD1ECF1", "font" => "FF0C5460"],
        };
    }

    /**
     * Map average marks to remark class using school grade bands.
     */
    private function boardRemarkClassFromAverage(
        $averageMarks,
        string $schoolType,
        ?string $className = null,
        $classID = null,
    ): string {
        if ($averageMarks === null || $averageMarks === "") {
            return "remark-inc";
        }
        if ($schoolType === "Secondary") {
            $info = $this->getSecondaryHardcodedGrade($averageMarks, $className);

            return $this->boardRemarkClassFromGrade($info["grade"] ?? null);
        }
        $info = $this->getGradeFromDefinition(
            $averageMarks,
            $classID,
            $schoolType,
            $className,
        );

        return $this->boardRemarkClassFromGrade($info["grade"] ?? null);
    }

    /**
     * Unique subjects assigned to a main class (all subclasses).
     *
     * @return list<array{subjectID:int,subject_name:string}>
     */
    private function getMainClassBoardSubjects(int $classID): array
    {
        if ($classID <= 0) {
            return [];
        }

        $rows = ClassSubject::where("status", "Active")
            ->where(function ($q) use ($classID) {
                $q->where("classID", $classID)->orWhereHas(
                    "subclass",
                    function ($sq) use ($classID) {
                        $sq->where("classID", $classID);
                    },
                );
            })
            ->with("subject")
            ->get();

        $byId = [];
        foreach ($rows as $cs) {
            $subject = $cs->subject;
            if (!$subject) {
                continue;
            }
            $sid = (int) $cs->subjectID;
            if ($sid <= 0 || isset($byId[$sid])) {
                continue;
            }
            $byId[$sid] = [
                "subjectID" => $sid,
                "subject_name" => (string) ($subject->subject_name ?? "N/A"),
            ];
        }

        $list = array_values($byId);
        usort(
            $list,
            fn($a, $b) => strcasecmp($a["subject_name"], $b["subject_name"]),
        );

        return $list;
    }

    /**
     * Subject columns for Excel board. When multiple subclasses are included
     * (e.g. Form Five all streams), use all main-class subjects.
     *
     * @param  list<array{student:mixed,row:array}>  $prepared
     * @return list<array{subjectID:int,subject_name:string}>
     */
    private function resolveBoardSubjectColumns(
        ?int $classID,
        array $prepared,
        bool $expandToMainClassSubjects,
    ): array {
        $byId = [];
        $byName = [];

        if ($expandToMainClassSubjects && $classID && $classID > 0) {
            foreach ($this->getMainClassBoardSubjects($classID) as $subj) {
                $byId[$subj["subjectID"]] = $subj;
            }
        }

        foreach ($prepared as $item) {
            foreach ($item["row"]["subjects"] ?? [] as $subj) {
                $sid = (int) ($subj["subjectID"] ?? 0);
                $name = trim((string) ($subj["subject_name"] ?? ""));
                if ($name === "") {
                    continue;
                }
                if ($sid > 0) {
                    if (!isset($byId[$sid])) {
                        $byId[$sid] = [
                            "subjectID" => $sid,
                            "subject_name" => $name,
                        ];
                    }
                } else {
                    $key = strtolower($name);
                    if (!isset($byName[$key])) {
                        $byName[$key] = [
                            "subjectID" => 0,
                            "subject_name" => $name,
                        ];
                    }
                }
            }
        }

        $list = array_merge(array_values($byId), array_values($byName));
        usort(
            $list,
            fn($a, $b) => strcasecmp($a["subject_name"], $b["subject_name"]),
        );

        return $list;
    }

    /**
     * @param  list<int>  $subclassIds
     * @return array<int, array{core: list<int>, optional: array<int, list<int>>}>
     */
    private function buildOfferedSubjectMapBySubclass(array $subclassIds): array
    {
        $subclassIds = array_values(
            array_unique(array_filter(array_map("intval", $subclassIds))),
        );
        if ($subclassIds === []) {
            return [];
        }

        $rows = ClassSubject::where("status", "Active")
            ->whereIn("subclassID", $subclassIds)
            ->get([
                "class_subjectID",
                "subclassID",
                "subjectID",
                "student_status",
            ]);

        $optionalCsIds = $rows
            ->filter(
                fn($r) => strtolower((string) ($r->student_status ?? "")) ===
                    "optional",
            )
            ->pluck("class_subjectID")
            ->map(fn($id) => (int) $id)
            ->all();

        $electorsByCs = collect();
        if ($optionalCsIds !== []) {
            $electorsByCs = DB::table("subject_electors")
                ->whereIn("classSubjectID", $optionalCsIds)
                ->get()
                ->groupBy("classSubjectID")
                ->map(
                    fn($group) => $group
                        ->pluck("studentID")
                        ->map(fn($id) => (int) $id)
                        ->all(),
                );
        }

        $map = [];
        foreach ($subclassIds as $subclassID) {
            $map[$subclassID] = [
                "core" => [],
                "optional" => [],
            ];
        }

        foreach ($rows as $cs) {
            $subclassID = (int) $cs->subclassID;
            $subjectID = (int) $cs->subjectID;
            if ($subclassID <= 0 || $subjectID <= 0) {
                continue;
            }
            if (!isset($map[$subclassID])) {
                $map[$subclassID] = ["core" => [], "optional" => []];
            }

            $isOptional =
                strtolower((string) ($cs->student_status ?? "")) === "optional";
            if ($isOptional) {
                $map[$subclassID]["optional"][$subjectID] = $electorsByCs->get(
                    (int) $cs->class_subjectID,
                    [],
                );
            } else {
                $map[$subclassID]["core"][] = $subjectID;
            }
        }

        foreach ($map as $subclassID => $info) {
            $map[$subclassID]["core"] = array_values(
                array_unique($info["core"]),
            );
        }

        return $map;
    }

    private function studentTakesBoardSubject(
        $student,
        int $subjectID,
        array $offeredBySubclass,
    ): bool {
        if ($subjectID <= 0) {
            return false;
        }

        $subclass = $student->subclass ?? ($student->oldSubclass ?? null);
        $subclassID = $subclass ? (int) $subclass->subclassID : 0;
        if ($subclassID <= 0 || !isset($offeredBySubclass[$subclassID])) {
            return false;
        }

        $info = $offeredBySubclass[$subclassID];
        if (in_array($subjectID, $info["core"] ?? [], true)) {
            return true;
        }

        $optionalStudents = $info["optional"][$subjectID] ?? null;
        if ($optionalStudents === null) {
            return false;
        }

        return in_array((int) $student->studentID, $optionalStudents, true);
    }

    /**
     * A-Level: student is assigned this subject via subclass offering or combination.
     */
    private function aLevelStudentAssignedSubject(
        $student,
        int $subjectID,
        array $offeredBySubclass,
    ): bool {
        if ($this->studentTakesBoardSubject($student, $subjectID, $offeredBySubclass)) {
            return true;
        }

        $sub = $student->subclass ?? ($student->oldSubclass ?? null);
        $combie = $sub->combie ?? null;
        if (!$combie) {
            return false;
        }

        $subjects = $combie->relationLoaded("subjects")
            ? $combie->subjects
            : $combie->subjects()->get();
        foreach ($subjects as $s) {
            if ((int) ($s->subjectID ?? 0) === $subjectID) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build one class board payload for PDF Export All (matches Excel board columns).
     */
    private function buildBoardPdfClassData(
        string $className,
        string $titleLine,
        $classStudents,
        array $resultsData,
        array $examNumbers,
        string $schoolType,
        string $typeFilter,
        $examFilter,
        $classID = null,
        bool $forceExpandMainClassSubjects = false,
        string $nameMode = "both",
        string $rankBy = "average",
    ): ?array {
        $prepared = [];
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
        if ($prepared === []) {
            return null;
        }

        $subclassIds = [];
        foreach ($prepared as $item) {
            $st = $item["student"];
            $sub = $st->subclass ?? ($st->oldSubclass ?? null);
            if ($sub && $sub->subclassID) {
                $subclassIds[] = (int) $sub->subclassID;
            }
        }
        $uniqueSubclassIds = array_values(array_unique($subclassIds));
        $isALevelClass = $this->isALevelClassName($className);
        $expandToMainClassSubjects =
            $schoolType === "Secondary" &&
            $classID &&
            (int) $classID > 0 &&
            ($forceExpandMainClassSubjects ||
                count($uniqueSubclassIds) > 1 ||
                $isALevelClass);

        $boardSubjects = $this->resolveBoardSubjectColumns(
            $classID ? (int) $classID : null,
            $prepared,
            $expandToMainClassSubjects,
        );
        $offeredBySubclass =
            $expandToMainClassSubjects || $isALevelClass
                ? $this->buildOfferedSubjectMapBySubclass($uniqueSubclassIds)
                : [];

        $this->sortBoardPreparedRows($prepared, $schoolType, $rankBy);
        foreach ($prepared as $i => &$item) {
            $item["position"] = $i + 1;
        }
        unset($item);

        // O-Level (Form 1-4) / Primary: no STREAM column. A-Level only: COMBINATION.
        $streamHeader = $isALevelClass ? "COMBINATION" : null;
        $showName = $nameMode !== "index";
        $showIndex = $nameMode !== "name";
        if ($schoolType === "Secondary") {
            $leadingHeaders = ["S/N"];
            if ($showName) {
                $leadingHeaders[] = "STUDENT NAME";
            }
            if ($showIndex) {
                $leadingHeaders[] = "INDEX NO";
            }
            $leadingHeaders[] = "SEX";
            if ($isALevelClass) {
                $leadingHeaders[] = $streamHeader;
            }
            $leadingHeaders[] = "DIVISION";
            $leadingHeaders[] = "POINT";
        } else {
            $leadingHeaders = ["S/N"];
            if ($showName) {
                $leadingHeaders[] = "STUDENT NAME";
            }
            if ($showIndex) {
                $leadingHeaders[] = "INDEX NO";
            }
            $leadingHeaders[] = "SEX";
        }
        $trailingHeaders =
            $schoolType === "Secondary"
                ? ["TOTAL", "AVERAGE", "POSITION"]
                : ["TOTAL", "AVERAGE", "GRADE", "POSITION"];

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
        foreach ($prepared as $item) {
            $sex = $this->genderShort($item["student"]->gender);
            if (!isset($stats[$sex])) {
                $sex = "M";
            }
            $stats[$sex]["reg"]++;
            $hasIncomplete = false;
            foreach ($item["row"]["subjects"] as $subj) {
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
                $roman = (string) $item["division_roman"];
                if ($this->hasDivisionRoman($roman) && isset($stats[$sex][$roman])) {
                    $stats[$sex][$roman]++;
                }
            }
        }
        $classGpa = $this->calculateClassGpaForExport(
            $prepared,
            $schoolType,
            $className,
        );
        $perfRows = [];
        foreach (["F", "M"] as $sexKey) {
            $s = $stats[$sexKey];
            $perfRows[] = [
                "sex" => $sexKey,
                "reg" => $s["reg"],
                "abs" => $s["abs"],
                "inc" => $s["inc"],
                "I" => $s["I"],
                "II" => $s["II"],
                "III" => $s["III"],
                "IV" => $s["IV"],
                "0" => $s["0"],
                "i_iii" => $s["I"] + $s["II"] + $s["III"],
            ];
        }
        $perfRows[] = [
            "sex" => "T",
            "reg" => $stats["F"]["reg"] + $stats["M"]["reg"],
            "abs" => 0,
            "inc" => $stats["F"]["inc"] + $stats["M"]["inc"],
            "I" => $stats["F"]["I"] + $stats["M"]["I"],
            "II" => $stats["F"]["II"] + $stats["M"]["II"],
            "III" => $stats["F"]["III"] + $stats["M"]["III"],
            "IV" => $stats["F"]["IV"] + $stats["M"]["IV"],
            "0" => $stats["F"]["0"] + $stats["M"]["0"],
            "i_iii" =>
                $stats["F"]["I"] +
                $stats["M"]["I"] +
                $stats["F"]["II"] +
                $stats["M"]["II"] +
                $stats["F"]["III"] +
                $stats["M"]["III"],
            "gpa" => number_format((float) $classGpa, 4),
        ];

        $rows = [];
        foreach ($prepared as $item) {
            $student = $item["student"];
            $row = $item["row"];
            $studentName = mb_strtoupper(
                trim(
                    $student->first_name .
                        " " .
                        ($student->middle_name
                            ? $student->middle_name . " "
                            : "") .
                        $student->last_name,
                ),
                "UTF-8",
            );
            $subjectMapById = [];
            $subjectMapByName = [];
            foreach ($row["subjects"] as $subj) {
                $sid = (int) ($subj["subjectID"] ?? 0);
                $name = (string) ($subj["subject_name"] ?? "");
                if ($sid > 0) {
                    $subjectMapById[$sid] = $subj;
                }
                if ($name !== "") {
                    $subjectMapByName[$name] = $subj;
                }
            }
            $subjectCells = [];
            foreach ($boardSubjects as $boardSubj) {
                $sid = (int) ($boardSubj["subjectID"] ?? 0);
                $name = (string) ($boardSubj["subject_name"] ?? "");
                $subj =
                    ($sid > 0 ? $subjectMapById[$sid] ?? null : null) ??
                    ($subjectMapByName[$name] ?? null);
                $marksRaw = $subj["marks"] ?? null;
                $hasPositiveMark =
                    $subj &&
                    $marksRaw !== null &&
                    $marksRaw !== "" &&
                    $marksRaw !== "incomplete" &&
                    (float) $marksRaw > 0;
                $notTaken =
                    $isALevelClass &&
                    $sid > 0 &&
                    !$hasPositiveMark &&
                    !$this->aLevelStudentAssignedSubject(
                        $student,
                        $sid,
                        $offeredBySubclass,
                    );
                if (
                    $notTaken ||
                    ($expandToMainClassSubjects &&
                        $subj === null &&
                        !$this->studentTakesBoardSubject(
                            $student,
                            $sid,
                            $offeredBySubclass,
                        ))
                ) {
                    $subjectCells[] = [
                        "marks" => "",
                        "grade" => "",
                        "empty" => true,
                    ];
                    continue;
                }
                if (!$subj) {
                    $subjectCells[] = [
                        "marks" => "-",
                        "grade" => "-",
                        "empty" => false,
                    ];
                    continue;
                }
                $marks = $subj["marks"] ?? null;
                if (
                    $marks === null ||
                    $marks === "" ||
                    $marks === "incomplete"
                ) {
                    $subjectCells[] = [
                        "marks" => "-",
                        "grade" => "-",
                        "empty" => false,
                    ];
                } else {
                    $grade = trim((string) ($subj["grade"] ?? ""));
                    $subjectCells[] = [
                        "marks" => (string) round((float) $marks),
                        "grade" => $grade !== "" ? $grade : "-",
                        "empty" => false,
                    ];
                }
            }

            $avgRaw = (float) ($row["average_marks"] ?? 0);
            $totalRaw = (float) ($row["total_marks"] ?? 0);
            $rowOut = [
                "sn" => $item["position"],
                "name" => $studentName,
                "index" => $examNumbers[$student->studentID] ?? "N/A",
                "sex" => $this->genderShort($student->gender),
                "point" =>
                    $item["division_points"] !== ""
                        ? $item["division_points"]
                        : "-",
                "division" => $this->displayDivisionRoman($item["division_roman"]),
                "subjects" => $subjectCells,
                "total" => $this->formatBoardTotalMarks($totalRaw),
                "average" => $this->formatBoardAverageMarks($avgRaw),
                "average_class" => $this->boardRemarkClassFromAverage(
                    $avgRaw,
                    $schoolType,
                    $className,
                    $classID,
                ),
                "grade" => $row["grade"] ?? "-",
                "position" => $item["position"],
            ];
            if ($isALevelClass) {
                $rowOut["stream"] = $this->resolveBoardStreamLabel(
                    $student,
                    $className,
                );
            }
            $rows[] = $rowOut;
        }

        $subjectStats = $this->buildSubjectStatsForBoardExport(
            $prepared,
            $boardSubjects,
            $schoolType,
            $isALevelClass,
            $className,
            $classID,
            $offeredBySubclass,
            $expandToMainClassSubjects,
        );

        $classPerfLabel =
            strtoupper(trim((string) $className)) . " - CLASS PERFORMANCE";
        $titleCombined =
            rtrim(trim((string) $titleLine), ".") . ", " . $classPerfLabel;

        return [
            "className" => $className,
            "titleLine" => rtrim(trim((string) $titleLine), "."),
            "titleCombined" => $titleCombined,
            "schoolType" => $schoolType,
            "isALevel" => $isALevelClass,
            "showStream" => $isALevelClass,
            "showName" => $showName,
            "showIndex" => $showIndex,
            "nameMode" => $nameMode,
            "rankBy" => $rankBy,
            "streamHeader" => $streamHeader,
            "leadingHeaders" => $leadingHeaders,
            "trailingHeaders" => $trailingHeaders,
            "subjects" => $boardSubjects,
            "perfRows" => $perfRows,
            "rows" => $rows,
            "subjectStats" => $subjectStats,
        ];
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
        $classID = null,
        bool $forceExpandMainClassSubjects = false,
        $school = null,
        string $rankBy = "average",
    ): void {
        $prepared = [];

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

        $subclassIds = [];
        foreach ($prepared as $item) {
            $st = $item["student"];
            $sub = $st->subclass ?? ($st->oldSubclass ?? null);
            if ($sub && $sub->subclassID) {
                $subclassIds[] = (int) $sub->subclassID;
            }
        }
        $uniqueSubclassIds = array_values(array_unique($subclassIds));
        $isALevelClass = $this->isALevelClassName($className);
        $expandToMainClassSubjects =
            $schoolType === "Secondary" &&
            $classID &&
            (int) $classID > 0 &&
            ($forceExpandMainClassSubjects ||
                count($uniqueSubclassIds) > 1 ||
                $isALevelClass);

        $boardSubjects = $this->resolveBoardSubjectColumns(
            $classID ? (int) $classID : null,
            $prepared,
            $expandToMainClassSubjects,
        );
        $subjectNames = array_map(
            fn($s) => $s["subject_name"],
            $boardSubjects,
        );

        $offeredBySubclass =
            $expandToMainClassSubjects || $isALevelClass
                ? $this->buildOfferedSubjectMapBySubclass($uniqueSubclassIds)
                : [];

        $this->sortBoardPreparedRows($prepared, $schoolType, $rankBy);

        foreach ($prepared as $i => &$item) {
            $item["position"] = $i + 1;
        }
        unset($item);

        $subjectColSpan = 2; // Marks + Grade under each subject
        // O-Level (Form 1-4) / Primary: no STREAM. A-Level only: COMBIE.
        $streamHeader = $isALevelClass ? "COMBIE" : null;

        // Leading: S/N, Name, Index No, Sex [, COMBIE for A-Level] [, Division, Point for Secondary]
        if ($schoolType === "Secondary") {
            $leadingHeaders = ["S/N", "STUDENT NAME", "INDEX NO", "SEX"];
            if ($isALevelClass) {
                $leadingHeaders[] = $streamHeader;
            }
            $leadingHeaders[] = "DIVISION";
            $leadingHeaders[] = "POINT";
        } else {
            $leadingHeaders = ["S/N", "STUDENT NAME", "INDEX NO", "SEX"];
        }
        // Trailing after subjects (full names, vertical)
        $trailingHeaders =
            $schoolType === "Secondary"
                ? ["TOTAL", "AVERAGE", "POSITION"]
                : ["TOTAL", "AVERAGE", "GRADE", "POSITION"];
        $fixedCols = count($leadingHeaders);
        $summaryCols = count($trailingHeaders);
        $subjectBlockCols = count($subjectNames) * $subjectColSpan;
        $totalCols = $fixedCols + $subjectBlockCols + $summaryCols;
        $lastCol = Coordinate::stringFromColumnIndex(max(1, $totalCols));
        // Titles merge across the full board (start → end of subjects + trailing)
        $titleEndCol = max(1, $totalCols);
        $titleEndLetter = Coordinate::stringFromColumnIndex($titleEndCol);
        $nameColLetter = Coordinate::stringFromColumnIndex(2);

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
        // Centre overview island (SEX → GPA) under the full board - like the title
        if ($totalCols > $perfColCount) {
            $perfStartCol = (int) floor(($totalCols - $perfColCount) / 2) + 1;
        } else {
            $perfStartCol = 1;
        }
        $perfEndCol = $perfStartCol + $perfColCount - 1;
        $perfStartLetter = Coordinate::stringFromColumnIndex($perfStartCol);
        $perfEndLetter = Coordinate::stringFromColumnIndex($perfEndCol);
        // School name / contact / title: always centre across FULL student board width
        $headerEndLetter = $titleEndLetter;
        $perfDataStartCol = $perfStartCol + 1; // REG. column (skip F/M/T label col)
        $perfDataStartLetter = Coordinate::stringFromColumnIndex(
            min($perfDataStartCol, $perfEndCol),
        );
        $gpaCol = Coordinate::stringFromColumnIndex($perfEndCol);

        // School header - org logo LEFT, school logo RIGHT, title text CENTER between them
        // Organization name (if any) sits above school name
        $underOrg = (bool) ($school->is_under_organization ?? false);
        $orgName = $underOrg
            ? strtoupper(trim((string) ($school->organization_name ?? "")))
            : "";
        $hasOrgName = $orgName !== "";
        $schoolName = strtoupper((string) ($school->school_name ?? "SCHOOL"));
        $schoolAddress = trim((string) ($school->address ?? ""));
        $schoolPhone = trim((string) ($school->phone ?? ""));
        $schoolEmail = trim((string) ($school->email ?? ""));
        $contactLine = trim(
            ($schoolPhone !== "" ? "Phone: {$schoolPhone}" : "") .
                ($schoolPhone !== "" && $schoolEmail !== "" ? "  |  " : "") .
                ($schoolEmail !== "" ? "Email: {$schoolEmail}" : ""),
        );

        $logoRowOffset = $this->embedSchoolLogosOnSheet(
            $sheet,
            $school,
            $headerEndLetter,
        );

        $cursor = 1 + $logoRowOffset;
        $orgNameRow = null;
        if ($hasOrgName) {
            $orgNameRow = $cursor;
            $cursor++;
        }
        $nameRow = $cursor;
        $addrRow = $cursor + 1;
        $contactRow = $cursor + 2;

        // When logos sit left/right (side-by-side), give header rows enough height
        if ($logoRowOffset === 0 && $school) {
            $hasOrgLogo =
                $underOrg &&
                $this->resolveLogoAbsolutePath($school->organization_logo ?? null);
            $hasSchoolLogo = $this->resolveLogoAbsolutePath(
                $school->school_logo ?? null,
            );
            if ($hasOrgLogo && $hasSchoolLogo) {
                if ($orgNameRow) {
                    $sheet->getRowDimension($orgNameRow)->setRowHeight(18);
                }
                $sheet->getRowDimension($nameRow)->setRowHeight(26);
                $sheet->getRowDimension($addrRow)->setRowHeight(16);
                $sheet->getRowDimension($contactRow)->setRowHeight(18);
            }
        }

        if ($orgNameRow) {
            $sheet->setCellValue("A{$orgNameRow}", $orgName);
            $sheet->mergeCells("A{$orgNameRow}:{$headerEndLetter}{$orgNameRow}");
            $sheet
                ->getStyle("A{$orgNameRow}:{$headerEndLetter}{$orgNameRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setTextRotation(0)
                ->setWrapText(false);
            $sheet
                ->getStyle("A{$orgNameRow}")
                ->getFont()
                ->setBold(true)
                ->setSize(11)
                ->getColor()
                ->setARGB("FF555555");
            if ($sheet->getRowDimension($orgNameRow)->getRowHeight() < 18) {
                $sheet->getRowDimension($orgNameRow)->setRowHeight(18);
            }
        }

        $sheet->setCellValue("A{$nameRow}", $schoolName);
        $sheet->mergeCells("A{$nameRow}:{$headerEndLetter}{$nameRow}");
        $sheet
            ->getStyle("A{$nameRow}:{$headerEndLetter}{$nameRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0)
            ->setWrapText(false);
        $sheet
            ->getStyle("A{$nameRow}")
            ->getFont()
            ->setBold(true)
            ->setSize(14)
            ->getColor()
            ->setARGB("FF940000");
        if ($sheet->getRowDimension($nameRow)->getRowHeight() < 22) {
            $sheet->getRowDimension($nameRow)->setRowHeight(22);
        }

        $sheet->setCellValue("A{$addrRow}", $schoolAddress !== "" ? $schoolAddress : " ");
        $sheet->mergeCells("A{$addrRow}:{$headerEndLetter}{$addrRow}");
        $sheet
            ->getStyle("A{$addrRow}:{$headerEndLetter}{$addrRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0)
            ->setWrapText(false);
        $sheet->getStyle("A{$addrRow}")->getFont()->setSize(10)->getColor()->setARGB("FF555555");
        if ($sheet->getRowDimension($addrRow)->getRowHeight() < 16) {
            $sheet->getRowDimension($addrRow)->setRowHeight(16);
        }

        $sheet->setCellValue("A{$contactRow}", $contactLine !== "" ? $contactLine : " ");
        $sheet->mergeCells("A{$contactRow}:{$headerEndLetter}{$contactRow}");
        $sheet
            ->getStyle("A{$contactRow}:{$headerEndLetter}{$contactRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0)
            ->setWrapText(false);
        $sheet->getStyle("A{$contactRow}")->getFont()->setSize(10)->getColor()->setARGB("FF555555");
        if ($sheet->getRowDimension($contactRow)->getRowHeight() < 16) {
            $sheet->getRowDimension($contactRow)->setRowHeight(16);
        }

        // Title band: one horizontal line - centred across full board width
        // e.g. "FIRST TERM REPORT RESULTS 2026, FORM THREE - CLASS PERFORMANCE"
        $classPerfLabel =
            strtoupper(trim((string) $className)) . " - CLASS PERFORMANCE";
        $titleCombined =
            rtrim(trim((string) $titleLine), ".") . ", " . $classPerfLabel;

        $titleBlockStart = $contactRow + 1;
        $titleBlockEnd = $titleBlockStart;
        $titleBlockRange = "A{$titleBlockStart}:{$headerEndLetter}{$titleBlockEnd}";

        // Clear / unmerge leftover cells in the title band so merge is clean
        foreach (array_keys($sheet->getMergeCells()) as $mergedRange) {
            try {
                [$rangeStart, $rangeEnd] = Coordinate::rangeBoundaries($mergedRange);
                $mergeMinRow = (int) $rangeStart[1];
                $mergeMaxRow = (int) $rangeEnd[1];
                if (
                    $mergeMaxRow >= $titleBlockStart &&
                    $mergeMinRow <= $titleBlockEnd
                ) {
                    $sheet->unmergeCells($mergedRange);
                }
            } catch (\Throwable $e) {
                // ignore invalid merge ranges
            }
        }
        for ($c = 1; $c <= $titleEndCol; $c++) {
            $sheet
                ->getCell(Coordinate::stringFromColumnIndex($c) . $titleBlockStart)
                ->setValue(null);
        }

        $sheet->setCellValue("A{$titleBlockStart}", $titleCombined);
        $sheet->mergeCells($titleBlockRange);
        $sheet
            ->getStyle($titleBlockRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0)
            ->setWrapText(false);
        $sheet
            ->getStyle("A{$titleBlockStart}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0)
            ->setWrapText(false);
        $sheet
            ->getStyle($titleBlockRange)
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->getColor()
            ->setARGB("FF000000");
        $sheet->getRowDimension($titleBlockStart)->setRowHeight(20);

        // Outer border around the title (like the sample image)
        $sheet
            ->getStyle($titleBlockRange)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()
            ->setARGB("FF333333");
        $sheet
            ->getStyle($titleBlockRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFFFFFFF");

        // Compact: performance table starts on next row (no blank spacer)
        $perfRowStart = $titleBlockEnd + 1;
        $sheet->fromArray([$perfHeader], null, "{$perfStartLetter}{$perfRowStart}");
        $perfDataEndLetter = Coordinate::stringFromColumnIndex(
            max($perfStartCol, $perfEndCol - 1),
        );
        $sheet
            ->getStyle("{$perfStartLetter}{$perfRowStart}:{$perfEndLetter}{$perfRowStart}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setTextRotation(0);
        $sheet
            ->getStyle("{$perfStartLetter}{$perfRowStart}:{$perfEndLetter}{$perfRowStart}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFF0F0F0");
        $sheet
            ->getStyle("{$perfStartLetter}{$perfRowStart}:{$perfEndLetter}{$perfRowStart}")
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setARGB("FF940000");

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

        $classGpa = $this->calculateClassGpaForExport(
            $prepared,
            $schoolType,
            $className,
        );

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
                $roman = (string) $item["division_roman"];
                if ($this->hasDivisionRoman($roman) && isset($stats[$sex][$roman])) {
                    $stats[$sex][$roman]++;
                }
            }
        }

        // Write F / M / T data rows (GPA column left empty - filled after merge)
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

        $perfLastRow = $perfRowStart + 3;
        $fRow = $perfRowStart + 1;
        $mRow = $perfRowStart + 2;
        $tRow = $perfRowStart + 3;
        $gpaFirstRow = $fRow;
        $gpaLastRow = $tRow;
        $gpaMergeRange = "{$gpaCol}{$gpaFirstRow}:{$gpaCol}{$gpaLastRow}";
        $dataColsRange = "{$perfStartLetter}{$fRow}:{$perfDataEndLetter}{$tRow}";
        $headerGpaCell = "{$gpaCol}{$perfRowStart}";

        // Borders on SEX→I-III only (NOT GPA) so merge stays clean
        $sheet
            ->getStyle("{$perfStartLetter}{$perfRowStart}:{$perfDataEndLetter}{$perfLastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Soft-group F/M (no line between them) for data cols
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

        // Center-align F / M / T data columns
        $sheet
            ->getStyle($dataColsRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // GPA - one merge across ALL data rows (F+M+T), value centred in the block
        foreach ([$fRow, $mRow, $tRow] as $gpaClearRow) {
            $sheet->setCellValue("{$gpaCol}{$gpaClearRow}", null);
        }
        try {
            $sheet->unmergeCells($gpaMergeRange);
        } catch (\Throwable $e) {
            // not previously merged
        }

        $sheet->mergeCells($gpaMergeRange);
        $sheet->setCellValue("{$gpaCol}{$gpaFirstRow}", $classGpa);
        $sheet
            ->getStyle($gpaMergeRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0)
            ->setWrapText(false);
        $sheet
            ->getStyle("{$gpaCol}{$gpaFirstRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setTextRotation(0);
        $sheet
            ->getStyle("{$gpaCol}{$gpaFirstRow}")
            ->getNumberFormat()
            ->setFormatCode("0.0000");
        $sheet->getStyle("{$gpaCol}{$gpaFirstRow}")->getFont()->setBold(true);

        // GPA header + merged body: outline only (no internal F/M/T lines)
        $sheet
            ->getStyle($headerGpaCell)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet
            ->getStyle($gpaMergeRange)
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(Border::BORDER_THIN);
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
        $sheet
            ->getStyle("{$gpaCol}{$tRow}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle(Border::BORDER_NONE);

        // Centre overview island under the data board width (merge empty side cells;
        // do NOT attach overview borders to the results table below).
        if ($perfStartCol > 1) {
            $leftEndLetter = Coordinate::stringFromColumnIndex(
                $perfStartCol - 1,
            );
            for ($r = $perfRowStart; $r <= $perfLastRow; $r++) {
                try {
                    $sheet->mergeCells("A{$r}:{$leftEndLetter}{$r}");
                } catch (\Throwable $e) {
                    // already merged
                }
                $sheet
                    ->getStyle("A{$r}:{$leftEndLetter}{$r}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_NONE);
            }
        }
        if ($perfEndCol < $totalCols) {
            $rightStartLetter = Coordinate::stringFromColumnIndex(
                $perfEndCol + 1,
            );
            for ($r = $perfRowStart; $r <= $perfLastRow; $r++) {
                try {
                    $sheet->mergeCells(
                        "{$rightStartLetter}{$r}:{$titleEndLetter}{$r}",
                    );
                } catch (\Throwable $e) {
                    // already merged
                }
                $sheet
                    ->getStyle("{$rightStartLetter}{$r}:{$titleEndLetter}{$r}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_NONE);
            }
        }

        // Blank row between overview and student results (keep tables separate)
        $subjectHeaderRow = $perfLastRow + 1;
        $subHeaderRow = $subjectHeaderRow + 1;

        // Leading columns (row 1 labels; merge down to sub-header row)
        foreach ($leadingHeaders as $i => $label) {
            $col = $i + 1;
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$letter}{$subjectHeaderRow}", $label);
            $sheet->mergeCells(
                "{$letter}{$subjectHeaderRow}:{$letter}{$subHeaderRow}",
            );
        }

        // Subject names - each merged across Marks + Grade
        $colIndex = $fixedCols + 1;
        $maxVerticalLen = strlen("INDEX NO");
        foreach ($subjectNames as $name) {
            $startLetter = Coordinate::stringFromColumnIndex($colIndex);
            $endLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue("{$startLetter}{$subjectHeaderRow}", $name);
            $sheet->mergeCells(
                "{$startLetter}{$subjectHeaderRow}:{$endLetter}{$subjectHeaderRow}",
            );
            $sheet->setCellValue("{$startLetter}{$subHeaderRow}", "Marks");
            $sheet->setCellValue("{$endLetter}{$subHeaderRow}", "Grade");
            $maxVerticalLen = max($maxVerticalLen, strlen((string) $name));
            $colIndex += $subjectColSpan;
        }

        // Trailing columns - merge vertically across both header rows
        foreach ($trailingHeaders as $label) {
            $letter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$letter}{$subjectHeaderRow}", $label);
            $sheet->mergeCells(
                "{$letter}{$subjectHeaderRow}:{$letter}{$subHeaderRow}",
            );
            $maxVerticalLen = max($maxVerticalLen, strlen((string) $label));
            $colIndex++;
        }

        $headerRange = "A{$subjectHeaderRow}:{$lastCol}{$subHeaderRow}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet
            ->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFF5F5F5");
        $sheet
            ->getStyle($headerRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // S/N, Student Name, Index No - horizontal; SEX and after rotate vertical
        for ($c = 1; $c <= 3; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $sheet
                ->getStyle("{$letter}{$subjectHeaderRow}")
                ->getAlignment()
                ->setTextRotation(0)
                ->setWrapText(true);
        }

        for ($c = 4; $c <= $fixedCols; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $sheet
                ->getStyle("{$letter}{$subjectHeaderRow}")
                ->getAlignment()
                ->setTextRotation(90);
        }

        $colIndex = $fixedCols + 1;
        foreach ($subjectNames as $name) {
            $startLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet
                ->getStyle("{$startLetter}{$subjectHeaderRow}")
                ->getAlignment()
                ->setTextRotation(90)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $colIndex += $subjectColSpan;
        }

        for ($c = $fixedCols + count($subjectNames) * $subjectColSpan + 1; $c <= $totalCols; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $sheet
                ->getStyle("{$letter}{$subjectHeaderRow}")
                ->getAlignment()
                ->setTextRotation(90);
        }

        // Sub-header Marks/Grade - horizontal, smaller
        $marksGradeStart = Coordinate::stringFromColumnIndex($fixedCols + 1);
        $marksGradeEnd = Coordinate::stringFromColumnIndex(
            $fixedCols + count($subjectNames) * $subjectColSpan,
        );
        if (count($subjectNames) > 0) {
            $sheet
                ->getStyle("{$marksGradeStart}{$subHeaderRow}:{$marksGradeEnd}{$subHeaderRow}")
                ->getFont()
                ->setBold(true)
                ->setSize(8);
            $sheet
                ->getStyle("{$marksGradeStart}{$subHeaderRow}:{$marksGradeEnd}{$subHeaderRow}")
                ->getAlignment()
                ->setTextRotation(0)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet
            ->getRowDimension($subjectHeaderRow)
            ->setRowHeight(min(220, max(80, (int) round($maxVerticalLen * 6.5))));
        $sheet->getRowDimension($subHeaderRow)->setRowHeight(18);

        $dataRow = $subHeaderRow + 1;
        $serialNo = 0;
        $notTakenCellRanges = [];
        foreach ($prepared as $item) {
            $serialNo++;
            $student = $item["student"];
            $row = $item["row"];
            $studentName = mb_strtoupper(
                trim(
                    $student->first_name .
                        " " .
                        ($student->middle_name
                            ? $student->middle_name . " "
                            : "") .
                        $student->last_name,
                ),
                "UTF-8",
            );

            // S/N, Name, Index No, Sex [, COMBIE for A-Level] [, Division, Point]
            $line = [
                $serialNo,
                $studentName,
                $examNumbers[$student->studentID] ?? "N/A",
                $this->genderShort($student->gender),
            ];
            if ($isALevelClass) {
                $line[] = $this->resolveBoardStreamLabel($student, $className);
            }
            if ($schoolType === "Secondary") {
                $line[] = $this->displayDivisionRoman($item["division_roman"]);
                $line[] = $item["division_points"] !== ""
                    ? $item["division_points"]
                    : "-";
            }

            $subjectMapById = [];
            $subjectMapByName = [];
            foreach ($row["subjects"] as $subj) {
                $sid = (int) ($subj["subjectID"] ?? 0);
                $name = (string) ($subj["subject_name"] ?? "");
                if ($sid > 0) {
                    $subjectMapById[$sid] = $subj;
                }
                if ($name !== "") {
                    $subjectMapByName[$name] = $subj;
                }
            }

            $colIndex = $fixedCols + 1;
            foreach ($boardSubjects as $boardSubj) {
                $sid = (int) ($boardSubj["subjectID"] ?? 0);
                $name = (string) ($boardSubj["subject_name"] ?? "");
                $subj =
                    ($sid > 0 ? $subjectMapById[$sid] ?? null : null) ??
                    ($subjectMapByName[$name] ?? null);

                $marksRaw = $subj["marks"] ?? null;
                $hasPositiveMark =
                    $subj &&
                    $marksRaw !== null &&
                    $marksRaw !== "" &&
                    $marksRaw !== "incomplete" &&
                    (float) $marksRaw > 0;
                $notTaken =
                    $isALevelClass &&
                    $sid > 0 &&
                    !$hasPositiveMark &&
                    !$this->aLevelStudentAssignedSubject(
                        $student,
                        $sid,
                        $offeredBySubclass,
                    );
                if (
                    $notTaken ||
                    ($expandToMainClassSubjects &&
                        $subj === null &&
                        !$this->studentTakesBoardSubject(
                            $student,
                            $sid,
                            $offeredBySubclass,
                        ))
                ) {
                    $line[] = "";
                    $line[] = "";
                    $marksLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $gradeLetter = Coordinate::stringFromColumnIndex(
                        $colIndex + 1,
                    );
                    $notTakenCellRanges[] = "{$marksLetter}{$dataRow}:{$gradeLetter}{$dataRow}";
                    $colIndex += $subjectColSpan;
                    continue;
                }

                if (!$subj) {
                    $line[] = "-";
                    $line[] = "-";
                    $colIndex += $subjectColSpan;
                    continue;
                }
                $marks = $subj["marks"] ?? null;
                if (
                    $marks === null ||
                    $marks === "" ||
                    $marks === "incomplete"
                ) {
                    $line[] = "-";
                    $line[] = "-";
                } else {
                    $grade = trim((string) ($subj["grade"] ?? ""));
                    $line[] = round((float) $marks);
                    $line[] = $grade !== "" ? $grade : "-";
                }
                $colIndex += $subjectColSpan;
            }

            // Trailing: TOTAL, AVERAGE, POSITION (Secondary) | TOTAL, AVERAGE, GRADE, POSITION (Primary)
            $avgRaw = (float) ($row["average_marks"] ?? 0);
            $totalRaw = (float) ($row["total_marks"] ?? 0);
            $totalLetter = Coordinate::stringFromColumnIndex($colIndex);
            $line[] = $this->formatBoardTotalMarks($totalRaw);
            $colIndex++;
            $avgLetter = Coordinate::stringFromColumnIndex($colIndex);
            $line[] = $this->formatBoardAverageMarks($avgRaw);
            $colIndex++;
            if ($schoolType === "Secondary") {
                $line[] = $item["position"];
            } else {
                $line[] = $row["grade"] ?? "-";
                $line[] = $item["position"];
            }

            $sheet->fromArray([$line], null, "A{$dataRow}");
            if ($schoolType === "Secondary") {
                $divColIndex = $isALevelClass ? 6 : 5;
                $divLetter = Coordinate::stringFromColumnIndex($divColIndex);
                $sheet
                    ->getCell("{$divLetter}{$dataRow}")
                    ->setValueExplicit(
                        $this->displayDivisionRoman($item["division_roman"]),
                        DataType::TYPE_STRING,
                    );
            }

            $avgRemarkClass = $this->boardRemarkClassFromAverage(
                $avgRaw,
                $schoolType,
                $className,
                $classID,
            );
            $avgArgb = $this->boardRemarkArgb($avgRemarkClass);
            $sheet
                ->getStyle("{$avgLetter}{$dataRow}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($avgArgb["fill"]);
            $sheet
                ->getStyle("{$avgLetter}{$dataRow}")
                ->getFont()
                ->setBold(true)
                ->getColor()
                ->setARGB($avgArgb["font"]);

            $dataRow++;
        }

        foreach ($notTakenCellRanges as $range) {
            $sheet
                ->getStyle($range)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB("FFE8E8E8");
        }

        $lastDataRow = $dataRow - 1;
        if ($lastDataRow >= $subjectHeaderRow) {
            $sheet
                ->getStyle("A{$subjectHeaderRow}:{$lastCol}{$lastDataRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet
                ->getStyle("A" . ($subHeaderRow + 1) . ":{$lastCol}{$lastDataRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet
                ->getStyle("{$nameColLetter}" . ($subHeaderRow + 1) . ":{$nameColLetter}{$lastDataRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // COMBIE is column 5 (after SEX) for A-Level only; TOTAL / AVERAGE / POSITION at end
        $strmCol = $isALevelClass ? 5 : 0;
        $totalCol = $fixedCols + $subjectBlockCols + 1;
        $avgCol = $totalCol + 1;
        $postCol =
            $schoolType === "Secondary"
                ? $avgCol + 1
                : $avgCol + 2; // Primary: TOTAL, AVERAGE, GRADE, POSITION

        for ($c = 1; $c <= $totalCols; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            if ($c === 1) {
                $sheet->getColumnDimension($colLetter)->setWidth(5);
            } elseif ($c === 2) {
                $sheet->getColumnDimension($colLetter)->setWidth(28);
            } elseif ($c === 3) {
                $sheet->getColumnDimension($colLetter)->setWidth(18);
            } elseif ($strmCol > 0 && $c === $strmCol) {
                // Combie - wider so labels fit
                $sheet->getColumnDimension($colLetter)->setWidth(14);
            } elseif ($c === $totalCol) {
                $sheet->getColumnDimension($colLetter)->setWidth(11);
            } elseif ($c === $avgCol || $c === $postCol) {
                $sheet->getColumnDimension($colLetter)->setWidth(8);
            } elseif ($c <= $fixedCols) {
                $sheet->getColumnDimension($colLetter)->setWidth(6);
            } else {
                // Marks / Grade subject columns
                $sheet->getColumnDimension($colLetter)->setWidth(5.5);
            }
        }

        // Below results: Remark Color Definition + Subject Performance Statistics
        $isALevel = $isALevelClass;
        $subjectStats = $this->buildSubjectStatsForBoardExport(
            $prepared,
            $boardSubjects,
            $schoolType,
            $isALevel,
            $className,
            $classID,
            $offeredBySubclass,
            $expandToMainClassSubjects,
        );
        $this->appendSubjectPerformanceStatsToSheet(
            $sheet,
            $lastDataRow + 2,
            $subjectStats,
            $schoolType,
            $isALevel,
            max(10, $totalCols),
        );
    }

    /**
     * Build per-subject grade counts (male/female/total) for Excel export,
     * matching Subject Performance Statistics on the results board.
     */
    private function buildSubjectStatsForBoardExport(
        array $prepared,
        array $boardSubjects,
        string $schoolType,
        bool $isALevel,
        string $className,
        $classID,
        array $offeredBySubclass = [],
        bool $expandToMainClassSubjects = false,
    ): array {
        $gradesToInit =
            $schoolType === "Primary"
                ? ["A", "B", "C", "D", "E", "F", "Incomplete"]
                : ($isALevel
                    ? ["A", "B", "C", "D", "E", "S", "F", "Incomplete"]
                    : ["A", "B", "C", "D", "F", "Incomplete"]);

        $subjectStats = [];
        foreach ($boardSubjects as $boardSubj) {
            $subjectName = $boardSubj["subject_name"] ?? "N/A";
            if (!isset($subjectStats[$subjectName])) {
                $subjectStats[$subjectName] = [];
                foreach ($gradesToInit as $g) {
                    $subjectStats[$subjectName][$g] = [
                        "male" => 0,
                        "female" => 0,
                        "total" => 0,
                    ];
                }
            }
        }

        foreach ($prepared as $item) {
            $student = $item["student"];
            $gender = $student->gender ?? "";
            $subjects = $item["row"]["subjects"] ?? [];
            $byId = collect($subjects)->keyBy("subjectID")->all();
            $byName = collect($subjects)->keyBy("subject_name")->all();

            foreach ($boardSubjects as $boardSubj) {
                $subjectName = $boardSubj["subject_name"] ?? "N/A";
                $subjectID = $boardSubj["subjectID"] ?? null;

                $resPeek =
                    $subjectID && isset($byId[$subjectID])
                        ? $byId[$subjectID]
                        : ($byName[$subjectName] ?? null);
                $marksPeek = $resPeek["marks"] ?? null;
                $hasPositiveMark =
                    $resPeek &&
                    $marksPeek !== null &&
                    $marksPeek !== "" &&
                    $marksPeek !== "incomplete" &&
                    (float) $marksPeek > 0;
                $sidInt = (int) ($subjectID ?? 0);
                $notTaken =
                    $isALevel &&
                    $sidInt > 0 &&
                    !$hasPositiveMark &&
                    !$this->aLevelStudentAssignedSubject(
                        $student,
                        $sidInt,
                        $offeredBySubclass,
                    );
                if (
                    $notTaken ||
                    ($expandToMainClassSubjects &&
                        $resPeek === null &&
                        !$this->studentTakesBoardSubject(
                            $student,
                            $sidInt,
                            $offeredBySubclass,
                        ))
                ) {
                    continue;
                }

                $res =
                    $subjectID && isset($byId[$subjectID])
                        ? $byId[$subjectID]
                        : ($byName[$subjectName] ?? null);

                $marksVal = $res["marks"] ?? ($res["average"] ?? null);
                $subjectGrade = $res["grade"] ?? "Incomplete";

                if ($marksVal === null || $marksVal === "") {
                    $subjectGrade = "Incomplete";
                } elseif (
                    strtoupper((string) $subjectGrade) === "INCOMPLETE" ||
                    !in_array(
                        $subjectGrade,
                        ["A", "B", "C", "D", "E", "S", "F"],
                        true,
                    )
                ) {
                    // Re-resolve letter from marks when grade missing/invalid
                    $letter = $this->resolveSubjectGradeLetter(
                        [
                            "marks" => $marksVal,
                            "grade" => $res["grade"] ?? null,
                        ],
                        $schoolType,
                        $className,
                        $classID,
                    );
                    $subjectGrade = $letter ?: "Incomplete";
                }

                if (!isset($subjectStats[$subjectName][$subjectGrade])) {
                    $subjectStats[$subjectName][$subjectGrade] = [
                        "male" => 0,
                        "female" => 0,
                        "total" => 0,
                    ];
                }

                if ($gender === "Male") {
                    $subjectStats[$subjectName][$subjectGrade]["male"]++;
                } elseif ($gender === "Female") {
                    $subjectStats[$subjectName][$subjectGrade]["female"]++;
                }
                $subjectStats[$subjectName][$subjectGrade]["total"]++;
            }
        }

        ksort($subjectStats);

        return $subjectStats;
    }

    /**
     * Append Remark Color Definition + Subject Performance Statistics below board results.
     * Layout matches subject_performance_statistics_pdf (no school header - already on sheet).
     */
    private function appendSubjectPerformanceStatsToSheet(
        Worksheet $sheet,
        int $startRow,
        array $subjectStats,
        string $schoolType,
        bool $isALevel,
        int $boardWidthCols,
    ): void {
        if (empty($subjectStats)) {
            return;
        }

        $row = max(1, $startRow);
        $isPrimary = strtolower($schoolType) === "primary";
        $gradesToLoop = $isPrimary
            ? ["A", "B", "C", "D", "E", "F"]
            : ($isALevel
                ? ["A", "B", "C", "D", "E", "S", "F"]
                : ["A", "B", "C", "D", "F"]);

        // Columns: SUBJECTS + (F,M,T)×grades + GPA + RANKS + COMPETENCY LEVEL
        $totalCols = 1 + count($gradesToLoop) * 3 + 3;
        $endLetter = Coordinate::stringFromColumnIndex($totalCols);

        $remarkColors = [
            "excellent" => ["fill" => "FF28A745", "font" => "FFFFFFFF"],
            "pass" => ["fill" => "FFD4EDDA", "font" => "FF155724"],
            "good" => ["fill" => "FFFFF3CD", "font" => "FF856404"],
            "avg" => ["fill" => "FFFFE8D6", "font" => "FFA04000"],
            "fail" => ["fill" => "FFF8D7DA", "font" => "FF721C24"],
            "inc" => ["fill" => "FFD1ECF1", "font" => "FF0C5460"],
        ];

        // --- Remark Color Definition ---
        $sheet->setCellValue("A{$row}", "Remark Color Definition");
        $sheet->mergeCells("A{$row}:{$endLetter}{$row}");
        $sheet
            ->getStyle("A{$row}")
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->getColor()
            ->setARGB("FF6C757D");
        $sheet
            ->getStyle("A{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        if (!$isPrimary) {
            $legend = [
                ["A - Excellent", "excellent"],
                ["B - Very Good", "pass"],
                ["C - Good", "good"],
                [
                    $isALevel ? "D - Average" : "D - Satisfactory",
                    "avg",
                ],
            ];
            if ($isALevel) {
                $legend[] = ["E - Satisfactory", "avg"];
                $legend[] = ["S - Subsidiary", "avg"];
            }
            $legend[] = ["F - Fail", "fail"];
            $legend[] = ["Incomplete", "inc"];
        } else {
            $legend = [
                ["Pass", "pass"],
                ["Failed", "fail"],
                ["Incomplete", "inc"],
            ];
        }

        $legendCount = count($legend);
        $colsPerLegend = max(1, (int) floor($totalCols / $legendCount));
        $legendCol = 1;
        foreach ($legend as $i => $item) {
            [$label, $key] = $item;
            $startCol = $legendCol;
            $span = $colsPerLegend;
            if ($i === $legendCount - 1) {
                $span = max(1, $totalCols - $startCol + 1);
            }
            $endCol = min($totalCols, $startCol + $span - 1);
            $startL = Coordinate::stringFromColumnIndex($startCol);
            $endL = Coordinate::stringFromColumnIndex($endCol);
            $sheet->setCellValue("{$startL}{$row}", $label);
            if ($startCol !== $endCol) {
                $sheet->mergeCells("{$startL}{$row}:{$endL}{$row}");
            }
            $sheet
                ->getStyle("{$startL}{$row}:{$endL}{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($remarkColors[$key]["fill"]);
            $sheet
                ->getStyle("{$startL}{$row}:{$endL}{$row}")
                ->getFont()
                ->setBold(true)
                ->setSize(11)
                ->getColor()
                ->setARGB($remarkColors[$key]["font"]);
            $sheet
                ->getStyle("{$startL}{$row}:{$endL}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet
                ->getStyle("{$startL}{$row}:{$endL}{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setARGB("FFEEEEEE");
            $legendCol = $endCol + 1;
        }
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row += 2;

        // --- Subject Performance Statistics title ---
        $sheet->setCellValue("A{$row}", "Subject Performance Statistics");
        $sheet->mergeCells("A{$row}:{$endLetter}{$row}");
        $sheet
            ->getStyle("A{$row}")
            ->getFont()
            ->setBold(true)
            ->setSize(14)
            ->getColor()
            ->setARGB("FF940000");
        $row++;

        $headerRow1 = $row;
        $headerRow2 = $row + 1;

        // SUBJECTS (rowspan 2)
        $sheet->setCellValue("A{$headerRow1}", "SUBJECTS");
        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");

        // Grade groups: colspan 3 each (F/M/T)
        $col = 2;
        foreach ($gradesToLoop as $g) {
            $startL = Coordinate::stringFromColumnIndex($col);
            $endL = Coordinate::stringFromColumnIndex($col + 2);
            $sheet->setCellValue("{$startL}{$headerRow1}", $g);
            $sheet->mergeCells(
                "{$startL}{$headerRow1}:{$endL}{$headerRow1}",
            );
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($col) . $headerRow2,
                "F",
            );
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($col + 1) . $headerRow2,
                "M",
            );
            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex($col + 2) . $headerRow2,
                "T",
            );
            $col += 3;
        }

        $trailing = ["GPA", "RANKS", "COMPETENCY LEVEL"];
        foreach ($trailing as $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$letter}{$headerRow1}", $label);
            $sheet->mergeCells("{$letter}{$headerRow1}:{$letter}{$headerRow2}");
            $col++;
        }

        $headerRange = "A{$headerRow1}:{$endLetter}{$headerRow2}";
        $sheet
            ->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB("FFF8F9FA");
        $sheet
            ->getStyle($headerRange)
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->getColor()
            ->setARGB("FF940000");
        $sheet
            ->getStyle($headerRange)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet
            ->getStyle($headerRange)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()
            ->setARGB("FFEEEEEE");
        $sheet->getRowDimension($headerRow1)->setRowHeight(22);
        $sheet->getRowDimension($headerRow2)->setRowHeight(20);

        $ptMap = $isPrimary
            ? ["A" => 5, "B" => 4, "C" => 3, "D" => 2, "E" => 1, "F" => 0]
            : ($isALevel
                ? [
                    "A" => 1,
                    "B" => 2,
                    "C" => 3,
                    "D" => 4,
                    "E" => 5,
                    "S" => 6,
                    "F" => 7,
                ]
                : ["A" => 1, "B" => 2, "C" => 3, "D" => 4, "F" => 5]);

        // Build row payloads (GPA / grade / remark) then ranks
        $builtRows = [];
        foreach ($subjectStats as $subjectName => $grades) {
            $sTotal = 0;
            $sPoints = 0;
            $breakdown = [];
            foreach ($gradesToLoop as $g) {
                $male = (int) ($grades[$g]["male"] ?? 0);
                $female = (int) ($grades[$g]["female"] ?? 0);
                $total = (int) ($grades[$g]["total"] ?? 0);
                $breakdown[$g] = [
                    "female" => $female,
                    "male" => $male,
                    "total" => $total,
                ];
                $sTotal += $total;
                $sPoints += $total * ($ptMap[$g] ?? 0);
            }

            $sGPA = $sTotal > 0 ? round($sPoints / $sTotal, 4) : 0.0;

            if ($isPrimary) {
                if ($sTotal === 0) {
                    $subjectGrade = "Incomplete";
                } elseif ($sGPA >= 4.5) {
                    $subjectGrade = "A";
                } elseif ($sGPA >= 3.5) {
                    $subjectGrade = "B";
                } elseif ($sGPA >= 2.5) {
                    $subjectGrade = "C";
                } elseif ($sGPA >= 1.5) {
                    $subjectGrade = "D";
                } elseif ($sGPA >= 0.5) {
                    $subjectGrade = "E";
                } else {
                    $subjectGrade = "F";
                }
            } else {
                if ($sTotal === 0) {
                    $subjectGrade = "Incomplete";
                } elseif ($sGPA <= 1.5) {
                    $subjectGrade = "A";
                } elseif ($sGPA <= 2.5) {
                    $subjectGrade = "B";
                } elseif ($sGPA <= 3.5) {
                    $subjectGrade = "C";
                } elseif ($sGPA <= 4.5) {
                    $subjectGrade = "D";
                } elseif ($isALevel && $sGPA <= 5.5) {
                    $subjectGrade = "E";
                } elseif ($isALevel && $sGPA <= 6.5) {
                    $subjectGrade = "S";
                } else {
                    $subjectGrade = "F";
                }
            }

            $remarkKey = "inc";
            $competency = "Incomplete";
            if ($sTotal > 0) {
                if (!$isPrimary) {
                    if ($subjectGrade === "A") {
                        $remarkKey = "excellent";
                        $competency = "Grade A (Excellent)";
                    } elseif ($subjectGrade === "B") {
                        $remarkKey = "pass";
                        $competency = "Grade B (Very Good)";
                    } elseif ($subjectGrade === "C") {
                        $remarkKey = "good";
                        $competency = "Grade C (Good)";
                    } elseif ($subjectGrade === "D") {
                        $remarkText = $isALevel ? "Average" : "Satisfactory";
                        $remarkKey = "avg";
                        $competency = "Grade D ({$remarkText})";
                    } elseif ($isALevel && $subjectGrade === "E") {
                        $remarkKey = "avg";
                        $competency = "Grade E (Satisfactory)";
                    } elseif ($isALevel && $subjectGrade === "S") {
                        $remarkKey = "avg";
                        $competency = "Grade S (Subsidiary)";
                    } elseif ($subjectGrade === "F") {
                        $remarkKey = "fail";
                        $competency = "Grade F (Fail)";
                    }
                } else {
                    if (in_array($subjectGrade, ["A", "B", "C", "D"], true)) {
                        $remarkKey = "pass";
                        $competency = "Grade {$subjectGrade} (Pass)";
                    } elseif (in_array($subjectGrade, ["E", "F"], true)) {
                        $remarkKey = "fail";
                        $competency = "Grade {$subjectGrade} (Fail)";
                    }
                }
            }

            $builtRows[] = [
                "subject" => $subjectName,
                "breakdown" => $breakdown,
                "gpa" => $sGPA,
                "remark_key" => $remarkKey,
                "competency" => $competency,
                "total" => $sTotal,
            ];
        }

        // Ranks: Primary higher GPA better; Secondary lower GPA better
        $ranked = $builtRows;
        usort($ranked, function ($a, $b) use ($isPrimary) {
            if ($isPrimary) {
                return $b["gpa"] <=> $a["gpa"];
            }
            return $a["gpa"] <=> $b["gpa"];
        });
        $rankMap = [];
        $rankTotal = max(1, count($ranked));
        foreach ($ranked as $i => $item) {
            $rankMap[$item["subject"]] = ($i + 1) . "/" . $rankTotal;
        }

        $dataStart = $headerRow2 + 1;
        $row = $dataStart;
        foreach ($builtRows as $item) {
            $line = [strtoupper((string) $item["subject"])];
            foreach ($gradesToLoop as $g) {
                $line[] = (int) ($item["breakdown"][$g]["female"] ?? 0);
                $line[] = (int) ($item["breakdown"][$g]["male"] ?? 0);
                $line[] = (int) ($item["breakdown"][$g]["total"] ?? 0);
            }
            $line[] = number_format((float) $item["gpa"], 4);
            $line[] = $rankMap[$item["subject"]] ?? "-";
            $line[] = $item["competency"];

            $sheet->fromArray([$line], null, "A{$row}");

            $sheet
                ->getStyle("A{$row}:{$endLetter}{$row}")
                ->getFont()
                ->setSize(11);
            $sheet
                ->getStyle("A{$row}:{$endLetter}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet
                ->getStyle("A{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);

            // Bold T columns under each grade
            $tCol = 4; // first T is col 4 (A=1 subject, B=F, C=M, D=T)
            foreach ($gradesToLoop as $_) {
                $tLetter = Coordinate::stringFromColumnIndex($tCol);
                $sheet
                    ->getStyle("{$tLetter}{$row}")
                    ->getFont()
                    ->setBold(true);
                $tCol += 3;
            }

            // Colored COMPETENCY LEVEL cell (last column) by remark/ufaulu
            $competencyLetter = $endLetter;
            $rk = $item["remark_key"];
            $sheet
                ->getStyle("{$competencyLetter}{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($remarkColors[$rk]["fill"]);
            $sheet
                ->getStyle("{$competencyLetter}{$row}")
                ->getFont()
                ->setBold(true)
                ->setSize(11)
                ->getColor()
                ->setARGB($remarkColors[$rk]["font"]);

            $sheet
                ->getStyle("A{$row}:{$endLetter}{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setARGB("FFEEEEEE");

            $row++;
        }

        $lastStatsRow = $row - 1;
        if ($lastStatsRow >= $headerRow1) {
            $sheet
                ->getStyle("A{$headerRow1}:{$endLetter}{$lastStatsRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setARGB("FFEEEEEE");
        }

        // Only widen trailing columns - do not shrink board result columns above
        $trailStart = 2 + count($gradesToLoop) * 3;
        $trailMinWidths = [12, 10, 32]; // GPA, RANKS, COMPETENCY LEVEL
        foreach ($trailMinWidths as $i => $minW) {
            $letter = Coordinate::stringFromColumnIndex($trailStart + $i);
            $current = (float) $sheet->getColumnDimension($letter)->getWidth();
            if ($current < $minW) {
                $sheet->getColumnDimension($letter)->setWidth($minW);
            }
        }
        $subjW = (float) $sheet->getColumnDimension("A")->getWidth();
        if ($subjW < 26) {
            $sheet->getColumnDimension("A")->setWidth(26);
        }
    }

    /**
     * Resolve a school/org logo path on disk for Excel Drawing.
     */
    private function resolveLogoAbsolutePath(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }
        $relativePath = ltrim(str_replace("\\", "/", $relativePath), "/");
        $candidates = [
            public_path($relativePath),
            base_path("public/" . $relativePath),
            (isset($_SERVER["DOCUMENT_ROOT"])
                ? rtrim($_SERVER["DOCUMENT_ROOT"], "/\\") . "/" . $relativePath
                : null),
            (isset($_SERVER["SCRIPT_FILENAME"])
                ? dirname($_SERVER["SCRIPT_FILENAME"]) .
                    "/" .
                    $relativePath
                : null),
        ];
        foreach ($candidates as $path) {
            if ($path && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Embed logos on the board header.
     * Under organization: org + school logos close beside the centred title (not sheet edges).
     * Not under organization / school only: school logo centred above title → returns rows used.
     */
    private function embedSchoolLogosOnSheet(
        Worksheet $sheet,
        $school,
        string $headerEndLetter,
    ): int {
        if (!$school) {
            return 0;
        }

        $underOrg = (bool) ($school->is_under_organization ?? false);
        $orgPath =
            $underOrg
                ? $this->resolveLogoAbsolutePath(
                    $school->organization_logo ?? null,
                )
                : null;
        $schoolPath = $this->resolveLogoAbsolutePath(
            $school->school_logo ?? null,
        );

        $lastColIndex = max(
            1,
            Coordinate::columnIndexFromString($headerEndLetter),
        );
        // Anchor logos next to the centred header text (not far left/right of wide boards)
        $midCol = max(2, (int) round($lastColIndex / 2));
        $sideGap = 4; // columns from center to each logo
        $leftCol = max(1, $midCol - $sideGap);
        $rightCol = min($lastColIndex, $midCol + $sideGap - 1);

        // Side-by-side near title: org LEFT of center, school RIGHT of center
        if ($orgPath && $schoolPath) {
            try {
                $orgDrawing = new Drawing();
                $orgDrawing->setName("OrgLogo");
                $orgDrawing->setPath($orgPath);
                $orgDrawing->setHeight(52);
                $orgDrawing->setCoordinates(
                    Coordinate::stringFromColumnIndex($leftCol) . "1",
                );
                $orgDrawing->setOffsetX(2);
                $orgDrawing->setOffsetY(4);
                $orgDrawing->setWorksheet($sheet);

                $schoolDrawing = new Drawing();
                $schoolDrawing->setName("SchoolLogo");
                $schoolDrawing->setPath($schoolPath);
                $schoolDrawing->setHeight(55);
                $schoolDrawing->setCoordinates(
                    Coordinate::stringFromColumnIndex($rightCol) . "1",
                );
                $schoolDrawing->setOffsetX(2);
                $schoolDrawing->setOffsetY(4);
                $schoolDrawing->setWorksheet($sheet);

                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(22);
            } catch (\Throwable $e) {
                \Log::warning("Excel side logos embed failed: " . $e->getMessage());
            }

            return 0;
        }

        // Org only: just left of center
        if ($orgPath) {
            try {
                $drawing = new Drawing();
                $drawing->setName("OrgLogo");
                $drawing->setPath($orgPath);
                $drawing->setHeight(52);
                $drawing->setCoordinates(
                    Coordinate::stringFromColumnIndex($leftCol) . "1",
                );
                $drawing->setOffsetX(2);
                $drawing->setOffsetY(4);
                $drawing->setWorksheet($sheet);
                $sheet->getRowDimension(1)->setRowHeight(42);
            } catch (\Throwable $e) {
                \Log::warning("Excel org logo embed failed: " . $e->getMessage());
            }

            return 0;
        }

        // School logo only (not under org): centred above title
        if ($schoolPath) {
            try {
                $drawing = new Drawing();
                $drawing->setName("SchoolLogo");
                $drawing->setPath($schoolPath);
                $drawing->setHeight(55);
                $drawing->setCoordinates(
                    Coordinate::stringFromColumnIndex(
                        max(1, $midCol - 1),
                    ) . "1",
                );
                $drawing->setWorksheet($sheet);
                $sheet->getRowDimension(1)->setRowHeight(42);
            } catch (\Throwable $e) {
                \Log::warning("Excel school logo embed failed: " . $e->getMessage());
            }

            return 1;
        }

        return 0;
    }

    /**
     * Download results as Excel
     */
    public function downloadExcel(Request $request)
    {
        try {
            set_time_limit(0);
            ini_set("memory_limit", "2048M");

            $schoolID = Session::get("schoolID");

            // Match PDF export: any logged-in user with a valid school session
            if (!$schoolID) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(
                        ["success" => false, "message" => "Access denied"],
                        401,
                    );
                }

                return redirect()->route("login")->with("error", "Access denied");
            }

            $option = $request->get("option", "all");
            $studentID = $request->get("studentID", null);
            $classID = $request->get("classID", null);
            $subclassID = $request->get("subclassID", null);

            // Get filter parameters
            $termFilter = $request->get("term", "");
            $typeFilter = $request->get("type", "exam");
            $examFilter = $request->get("examID", "");

            // Same academic-year source as manageResults page (header selection)
            $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
            $academicYearIDs = $this->expandRelatedAcademicYearIds(
                (int) $schoolID,
                $yearCtx["academic_year_ids"],
            );
            $yearFilter = $yearCtx["calendar_year"]
                ?: $request->get("year", date("Y"));
            $calendarYears = !empty($yearCtx["calendar_years"])
                ? $yearCtx["calendar_years"]
                : ($yearFilter ? [(int) $yearFilter] : []);

            // Get school details
            $school = \App\Models\School::find($schoolID);
            $schoolType = $school ? $school->school_type : "Secondary";

            $rankBy = strtolower(trim((string) $request->get("rank_by", "average")));
            if (!in_array($rankBy, ["average", "division"], true)) {
                $rankBy = "average";
            }

            // Get students based on option (Active enrollments for selected year - same as PDF)
            $students = $this->studentsForResultDownload($request, $schoolID);

            // Get results data
            $resultsData = [];
            if ($termFilter && ($yearFilter || !empty($academicYearIDs))) {
                if ($typeFilter === "exam") {
                    // Handle CA Definitions for exam filtering
                    $caExamIDs = $examFilter;
                    if ($examFilter && !is_array($examFilter)) {
                        $caDef = $this->resolveCaDefinition(
                            $schoolID,
                            $examFilter,
                            null,
                        );
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
                        null,
                        null,
                        $schoolID,
                        $academicYearIDs,
                    );
                } else {
                    $resultsData = $this->getTermReport(
                        $students,
                        $termFilter,
                        $yearFilter,
                        $schoolType,
                        null,
                        $schoolID,
                        $academicYearIDs,
                    );
                }
            }

            // Filter students to only those with results
            $students = $students->filter(function ($student) use ($resultsData) {
                return isset($resultsData[$student->studentID]);
            });

            if ($students->isEmpty()) {
                return response(
                    "No results found for the selected filters.",
                    422,
                    ["Content-Type" => "text/plain; charset=UTF-8"],
                );
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
                $mainClassName =
                    $cid > 0
                        ? ClassModel::find($cid)?->class_name ?? "Class {$cid}"
                        : "Unknown Class";
                // Header label: A-Level + subclass filter → "FORM FIVE - PCM"
                // (data STRM column stays subclass-only via resolveBoardStreamLabel)
                $className = $mainClassName;
                if ($option === "subclass" && $subclassID) {
                    $exportSubclass = Subclass::with("class")->find($subclassID);
                    if ($exportSubclass) {
                        $mainName =
                            $exportSubclass->class->class_name ?? $mainClassName;
                        $subName = trim(
                            (string) ($exportSubclass->subclass_name ?? ""),
                        );
                        if ($this->isALevelClassName($mainName)) {
                            $className =
                                $subName !== ""
                                    ? strtoupper($mainName) .
                                        " - " .
                                        strtoupper($subName)
                                    : strtoupper($mainName);
                        } else {
                            $className = $this->formatSubclassFilterDisplayName(
                                $mainName,
                                $subName,
                            );
                        }
                    }
                }
                $sheetTitle = preg_replace(
                    '/[\\\\\\/\\?\\*\\[\\]:]/',
                    "",
                    $className,
                );
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
                    $cid > 0 ? (int) $cid : null,
                    // Form Five (etc.) all subclasses / whole main class export
                    ($option === "class" && empty($subclassID)) ||
                        (empty($subclassID) && !empty($classID)),
                    $school,
                    $rankBy,
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
            $filename = preg_replace('/[\\\\\\/\\?\\*\\[\\]:<>\\|"]+/', "_", $filename);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    while (ob_get_level() > 0) {
                        ob_end_clean();
                    }
                    $writer = new Xlsx($spreadsheet);
                    $writer->save("php://output");
                },
                $filename,
                [
                    "Content-Type" =>
                        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    "Cache-Control" => "max-age=0, no-cache, must-revalidate",
                    "Pragma" => "public",
                ],
            );
        } catch (\Throwable $e) {
            \Log::error("downloadExcel failed: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);

            return response(
                "Failed to generate Excel: " . $e->getMessage(),
                500,
                ["Content-Type" => "text/plain; charset=UTF-8"],
            );
        }
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
            "use_percent_division" => "nullable|boolean",
            "exam_weights" => "nullable|array",
            "level_scope" => "nullable|string|in:all,a_level,o_level",
            "school_closing_date" => "nullable|date",
            "school_opening_date" => "nullable|date|after_or_equal:school_closing_date",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->errors()->first(),
            ]);
        }

        $usePercent = filter_var(
            $request->input("use_percent_division", false),
            FILTER_VALIDATE_BOOLEAN,
        );
        $weights = $request->input("exam_weights", []) ?: [];
        // Normalize weight keys to strings
        $normalizedWeights = [];
        foreach ($weights as $k => $v) {
            $normalizedWeights[(string) $k] = (float) $v;
        }
        $levelScope = $this->normalizeLevelScope(
            $request->input("level_scope", "all"),
        );

        $closingDate = $request->input("school_closing_date") ?: null;
        $openingDate = $request->input("school_opening_date") ?: null;
        if ($closingDate === "") {
            $closingDate = null;
        }
        if ($openingDate === "") {
            $openingDate = null;
        }

        $weightError = $this->validatePercentWeights(
            $usePercent,
            $request->exam_ids,
            $normalizedWeights,
        );
        if ($weightError) {
            return response()->json([
                "success" => false,
                "message" => $weightError,
            ]);
        }

        try {
            $payload = [
                "exam_ids" => $request->exam_ids,
                "use_percent_division" => $usePercent,
                "exam_weights" => $usePercent ? $normalizedWeights : null,
                "level_scope" => $levelScope,
                "school_closing_date" => $closingDate,
                "school_opening_date" => $openingDate,
                "created_by" =>
                    Session::get("teacherID") ?? (auth()->id() ?? 1),
            ];

            $editId = (int) $request->input("id", 0);

            // Block create/overwrite when another definition already exists for this key
            $existingQuery = TermReportDefinition::where("schoolID", $schoolID)
                ->where("year", $request->year)
                ->where("term", $request->term)
                ->where("level_scope", $levelScope);
            if ($editId > 0) {
                $existingQuery->where("id", "!=", $editId);
            }
            $existing = $existingQuery->first();
            if ($existing) {
                $termLabel =
                    $request->term === "first_term"
                        ? "First Term"
                        : ($request->term === "second_term"
                            ? "Second Term"
                            : $request->term);
                return response()->json([
                    "success" => false,
                    "message" =>
                        "Report already defined for {$termLabel} {$request->year}. Open View/Filter Reports and use Edit instead of saving a new one.",
                    "existing_id" => $existing->id,
                ]);
            }

            if ($editId > 0) {
                $def = TermReportDefinition::where("id", $editId)
                    ->where("schoolID", $schoolID)
                    ->first();
                if (!$def) {
                    return response()->json([
                        "success" => false,
                        "message" => "Definition not found.",
                    ]);
                }
                $def->update(
                    array_merge($payload, [
                        "year" => $request->year,
                        "term" => $request->term,
                    ]),
                );
            } else {
                TermReportDefinition::create(
                    array_merge($payload, [
                        "schoolID" => $schoolID,
                        "year" => $request->year,
                        "term" => $request->term,
                    ]),
                );
            }

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
     * Realtime check: report already defined for this school / year / term / level.
     */
    public function checkReportDefinitionExists(Request $request)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return response()->json([
                "success" => false,
                "message" => "Session expired",
            ]);
        }

        $year = (int) $request->input("year");
        $term = (string) $request->input("term");
        $levelScope = $this->normalizeLevelScope(
            $request->input("level_scope", "all"),
        );
        $excludeId = (int) $request->input("exclude_id", 0);

        if (!$year || $term === "") {
            return response()->json([
                "success" => true,
                "exists" => false,
            ]);
        }

        $query = TermReportDefinition::where("schoolID", $schoolID)
            ->where("year", $year)
            ->where("term", $term)
            ->where("level_scope", $levelScope);

        if ($excludeId > 0) {
            $query->where("id", "!=", $excludeId);
        }

        $def = $query->first();

        if (!$def) {
            return response()->json([
                "success" => true,
                "exists" => false,
            ]);
        }

        $examCount = is_array($def->exam_ids) ? count($def->exam_ids) : 0;
        $termLabel =
            $term === "first_term"
                ? "First Term"
                : ($term === "second_term"
                    ? "Second Term"
                    : $term);
        $levelLabel =
            $levelScope === "a_level"
                ? "A Level"
                : ($levelScope === "o_level"
                    ? "O Level"
                    : "All Classes");

        return response()->json([
            "success" => true,
            "exists" => true,
            "definition" => [
                "id" => $def->id,
                "year" => $def->year,
                "term" => $def->term,
                "level_scope" => $def->level_scope,
                "exam_count" => $examCount,
                "use_percent_division" => (bool) $def->use_percent_division,
            ],
            "message" =>
                "Report already defined for {$termLabel} {$year} ({$levelLabel})" .
                ($examCount > 0 ? " - {$examCount} exam(s)." : "."),
        ]);
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
            $weights = is_array($def->exam_weights) ? $def->exam_weights : [];
            $usePct = (bool) $def->use_percent_division;
            if (!empty($def->exam_ids)) {
                $exams = $this->orderModelsByIdList(
                    Examination::whereIn("examID", $def->exam_ids)->get(),
                    (array) $def->exam_ids,
                );
                $def->exam_names = $exams
                    ->map(function ($ex) use ($weights, $usePct) {
                        return $this->formatExamLabelWithWeight(
                            $ex->exam_name,
                            $ex->examID,
                            $weights,
                            $usePct,
                        );
                    })
                    ->toArray();
            } else {
                $def->exam_names = [];
            }
            $def->level_scope = $this->normalizeLevelScope(
                $def->level_scope ?? null,
            );
            $def->school_closing_date = $def->school_closing_date
                ? $def->school_closing_date->format("Y-m-d")
                : null;
            $def->school_opening_date = $def->school_opening_date
                ? $def->school_opening_date->format("Y-m-d")
                : null;
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

        $yearService = app(SelectedAcademicYearService::class);
        $yearCtx = $this->resolveResultsAcademicYearContext((int) $schoolID);
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        $calendarYears = $yearCtx["calendar_years"] ?? [];
        if ($year) {
            $calendarYears = array_values(
                array_unique(
                    array_merge(
                        array_map("intval", (array) $calendarYears),
                        [(int) $year],
                    ),
                ),
            );
        }
        // Also expand from the modal calendar year so slash AYs (2025/2026) match year=2026
        if (!empty($calendarYears)) {
            $siblingIds = AcademicYear::where("schoolID", $schoolID)
                ->get()
                ->filter(function ($ay) use ($yearService, $calendarYears) {
                    $overlap = array_intersect(
                        $yearService->extractCalendarYearsFromAcademicYear($ay),
                        $calendarYears,
                    );
                    return !empty($overlap);
                })
                ->pluck("academic_yearID")
                ->map(fn ($id) => (int) $id)
                ->all();
            $academicYearIDs = array_values(
                array_unique(array_merge($academicYearIDs, $siblingIds)),
            );
        }

        $examQuery = Examination::where("schoolID", $schoolID)->where(
            "term",
            $term,
        );
        $this->applyExaminationAcademicYearFilter(
            $examQuery,
            $academicYearIDs,
            $calendarYears,
        );
        $exams = $examQuery
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
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        $calendarYears = $yearCtx["calendar_years"] ?? [];
        if ($year) {
            $calendarYears = array_values(
                array_unique(
                    array_merge(
                        array_map("intval", (array) $calendarYears),
                        [(int) $year],
                    ),
                ),
            );
        }

        $examQuery = Examination::where("schoolID", $schoolID)
            ->where("term", $term)
            ->where("exam_category", "school_exams")
            ->orderBy("start_date", "asc");
        $this->applyExaminationAcademicYearFilter(
            $examQuery,
            $academicYearIDs,
            $calendarYears,
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
        $academicYearIDs = $this->expandRelatedAcademicYearIds(
            (int) $schoolID,
            $yearCtx["academic_year_ids"],
        );
        $calendarYears = $yearCtx["calendar_years"] ?? [];
        if ($year) {
            $calendarYears = array_values(
                array_unique(
                    array_merge(
                        array_map("intval", (array) $calendarYears),
                        [(int) $year],
                    ),
                ),
            );
        }

        $examQuery = Examination::where("schoolID", $schoolID)
            ->where("term", $term)
            ->where("exam_category", "test")
            ->orderBy("start_date", "asc");
        $this->applyExaminationAcademicYearFilter(
            $examQuery,
            $academicYearIDs,
            $calendarYears,
        );

        $exams = $examQuery->get(["examID", "exam_name", "end_date"]);

        return response()->json(["success" => true, "data" => $exams]);
    }

    public function checkExamCaExists(Request $request)
    {
        $examID = $request->examID;
        $schoolID = Session::get("schoolID");
        $levelScope = $this->normalizeLevelScope(
            $request->input("level_scope", "all"),
        );

        $exists = CaDefinition::where("examID", $examID)
            ->where("schoolID", $schoolID)
            ->where("level_scope", $levelScope)
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
            "use_percent_division" => "nullable|boolean",
            "exam_weights" => "nullable|array",
            "level_scope" => "nullable|string|in:all,a_level,o_level",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "All fields are required.",
            ]);
        }

        $usePercent = filter_var(
            $request->input("use_percent_division", false),
            FILTER_VALIDATE_BOOLEAN,
        );
        $weights = $request->input("exam_weights", []) ?: [];
        $normalizedWeights = [];
        foreach ($weights as $k => $v) {
            $normalizedWeights[(string) $k] = (float) $v;
        }
        $levelScope = $this->normalizeLevelScope(
            $request->input("level_scope", "all"),
        );

        // Percent applies to main exam + selected tests
        $allIds = array_values(
            array_unique(
                array_merge([(int) $request->examID], $request->test_ids),
            ),
        );
        $weightError = $this->validatePercentWeights(
            $usePercent,
            $allIds,
            $normalizedWeights,
        );
        if ($weightError) {
            return response()->json([
                "success" => false,
                "message" => $weightError,
            ]);
        }

        CaDefinition::updateOrCreate(
            [
                "schoolID" => $schoolID,
                "examID" => $request->examID,
                "level_scope" => $levelScope,
            ],
            [
                "year" => $request->year,
                "term" => $request->term,
                "test_ids" => $request->test_ids,
                "use_percent_division" => $usePercent,
                "exam_weights" => $usePercent ? $normalizedWeights : null,
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
                $weights = is_array($def->exam_weights) ? $def->exam_weights : [];
                $usePct = (bool) $def->use_percent_division;
                $tests = $this->orderModelsByIdList(
                    Examination::whereIn("examID", $def->test_ids)->get(),
                    (array) $def->test_ids,
                );
                $def->test_names = $tests
                    ->map(function ($ex) use ($weights, $usePct) {
                        return $this->formatExamLabelWithWeight(
                            $ex->exam_name,
                            $ex->examID,
                            $weights,
                            $usePct,
                        );
                    })
                    ->toArray();
                if ($def->mainExam && $usePct) {
                    $def->main_exam_label = $this->formatExamLabelWithWeight(
                        $def->mainExam->exam_name,
                        $def->examID,
                        $weights,
                        true,
                    );
                } else {
                    $def->main_exam_label = $def->mainExam->exam_name ?? "N/A";
                }
                $def->level_scope = $this->normalizeLevelScope(
                    $def->level_scope ?? null,
                );
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
    /**
     * Closing / opening dates for individual PDF Taarifa block.
     * Exam filter → school exam track_school_calendar.
     * Term report → term_report_definitions dates (if set).
     *
     * @return array{closing_date:?string,opening_date:?string,source:?string}
     */
    private function resolveSchoolCalendarForIndividualReport(
        int $schoolID,
        string $typeFilter,
        $examFilter,
        $termFilter,
        $yearFilter,
        $students,
    ): array {
        $empty = [
            "closing_date" => null,
            "opening_date" => null,
            "source" => null,
        ];

        if ($typeFilter === "exam" && $examFilter) {
            $exam = Examination::where("schoolID", $schoolID)
                ->where("examID", (int) $examFilter)
                ->first();
            if (
                $exam &&
                $exam->exam_category === "school_exams" &&
                !empty($exam->track_school_calendar)
            ) {
                return [
                    "closing_date" => $exam->school_closing_date
                        ? $exam->school_closing_date->format("Y-m-d")
                        : null,
                    "opening_date" => $exam->school_opening_date
                        ? $exam->school_opening_date->format("Y-m-d")
                        : null,
                    "source" => "exam",
                ];
            }
            return $empty;
        }

        if ($typeFilter === "report" && $termFilter && $yearFilter) {
            $levelScope = "all";
            $first = $students instanceof Collection ? $students->first() : null;
            if ($first) {
                $cn =
                    $first->subclass->class->class_name ??
                    ($first->oldSubclass->class->class_name ?? "");
                if ($this->isALevelClassName($cn)) {
                    $levelScope = "a_level";
                } else {
                    $levelScope = "o_level";
                }
            }

            $defQuery = TermReportDefinition::where("schoolID", $schoolID)
                ->where("year", (int) $yearFilter)
                ->where("term", $termFilter);

            $def = (clone $defQuery)
                ->where("level_scope", $levelScope)
                ->first();
            if (!$def) {
                $def = (clone $defQuery)->where("level_scope", "all")->first();
            }
            if (!$def) {
                $def = $defQuery->first();
            }

            if (
                $def &&
                ($def->school_closing_date || $def->school_opening_date)
            ) {
                return [
                    "closing_date" => $def->school_closing_date
                        ? $def->school_closing_date->format("Y-m-d")
                        : null,
                    "opening_date" => $def->school_opening_date
                        ? $def->school_opening_date->format("Y-m-d")
                        : null,
                    "source" => "term_report",
                ];
            }
        }

        return $empty;
    }

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

        $baseQuery = Result::where("examID", $examID);
        if ($subclassID) {
            $baseQuery->where("subclassID", $subclassID);
        } elseif ($classID) {
            $subclassIDs = Subclass::where("classID", $classID)->pluck(
                "subclassID",
            );
            $baseQuery->whereIn("subclassID", $subclassIDs);
        }

        (clone $baseQuery)->update(["parent_status" => $status]);

        if ($status === "enabled") {
            // Create notifications for parents (fresh query after publish)
            $studentIDs = (clone $baseQuery)
                ->distinct()
                ->pluck("studentID")
                ->filter()
                ->unique()
                ->values();
            $exam = Examination::find($examID);

            foreach ($studentIDs as $studentID) {
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
                ->with("components")
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
                ->with("components")
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

        // This lookup can create a class-subject row, so refuse incomplete requests instead of inserting nulls.
        if (!$classID || !$subjectID) {
            return response()->json(['success' => false, 'message' => 'classID and subjectID are required.'], 422);
        }

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
        // and deduplicate - so a subject taught in multiple subclasses appears once.
        if (!empty($subclassID) && $subclassID !== "all") {
            $query->where("subclassID", $subclassID);
        }
        // else: no subclassID filter - fetches every class_subject row for this class

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
     * Fee snapshot for individual PDF ADA block (aligned with view_payments).
     * Ada = current-year expected fees (amount_required − debt).
     * Madeni = outstanding balance on current-year bill(s).
     *
     * @param  list<int>  $studentIds
     * @param  list<int>  $academicYearIDs
     * @return array<int, array{ada: float|null, madeni: float|null, has_bill: bool}>
     */
    private function buildStudentFeeSummariesForReport(
        array $studentIds,
        int $schoolID,
        array $academicYearIDs,
    ): array {
        $studentIds = array_values(
            array_unique(array_filter(array_map("intval", $studentIds))),
        );
        $yearIds = array_values(
            array_unique(array_filter(array_map("intval", $academicYearIDs))),
        );
        $out = [];
        foreach ($studentIds as $sid) {
            $out[$sid] = [
                "ada" => null,
                "madeni" => null,
                "has_bill" => false,
            ];
        }
        if (empty($studentIds) || empty($yearIds) || $schoolID <= 0) {
            return $out;
        }

        $payments = Payment::where("schoolID", $schoolID)
            ->whereIn("studentID", $studentIds)
            ->whereIn("academic_yearID", $yearIds)
            ->get(["studentID", "amount_required", "debt", "balance"]);

        $byStudent = $payments->groupBy("studentID");
        foreach ($byStudent as $sid => $rows) {
            $ada = 0.0;
            $madeni = 0.0;
            foreach ($rows as $p) {
                $required = (float) ($p->amount_required ?? 0);
                $debt = (float) ($p->debt ?? 0);
                $ada += max(0, $required - $debt);
                $madeni += max(0, (float) ($p->balance ?? 0));
            }
            $out[(int) $sid] = [
                "ada" => $ada,
                "madeni" => $madeni,
                "has_bill" => true,
            ];
        }

        return $out;
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
                foreach ($this->extractCalendarYearsFromAcademicYear($y) as $cal) {
                    $calendarYears[] = $cal;
                }
            }
        }

        $calendarYears = array_values(array_unique(array_filter($calendarYears)));
        $primaryCal = $calendarYears[0] ?? (int) date("Y");

        return [
            "academic_year_ids" => $yearIds,
            "academic_years" => $years,
            "label" => !empty($labels)
                ? implode(" / ", $labels)
                : "Year " . $primaryCal,
            "calendar_year" => $primaryCal,
            "calendar_years" => $calendarYears,
        ];
    }

    /**
     * Pull every 20xx token from academic year.year + year_name (e.g. 2025/2026 → [2025, 2026]).
     */
    private function extractCalendarYearsFromAcademicYear($year): array
    {
        $blob = trim(
            (string) ($year->year ?? "") .
                " " .
                (string) ($year->year_name ?? ""),
        );
        if ($blob === "") {
            return [];
        }

        preg_match_all("/(20\d{2})/", $blob, $matches);
        $found = array_map("intval", $matches[1] ?? []);
        if (empty($found) && is_numeric($year->year ?? null)) {
            $found[] = (int) $year->year;
        }

        return array_values(array_unique(array_filter($found, fn ($y) => $y >= 2000)));
    }

    /**
     * Header may select AY id=15 (2025/2026) while enrollments still use sibling id=5 (2026).
     * Expand to all school AYs that share any calendar year token with the selection.
     */
    private function expandRelatedAcademicYearIds(
        int $schoolID,
        array $yearIds,
    ): array {
        $yearIds = array_values(array_filter(array_map("intval", $yearIds)));
        if (empty($yearIds)) {
            return [];
        }

        $selected = AcademicYear::where("schoolID", $schoolID)
            ->whereIn("academic_yearID", $yearIds)
            ->get();
        if ($selected->isEmpty()) {
            return $yearIds;
        }

        $calendarYears = [];
        foreach ($selected as $y) {
            foreach ($this->extractCalendarYearsFromAcademicYear($y) as $cal) {
                $calendarYears[] = $cal;
            }
        }
        $calendarYears = array_values(array_unique(array_filter($calendarYears)));
        if (empty($calendarYears)) {
            return $yearIds;
        }

        $related = [];
        foreach (
            AcademicYear::where("schoolID", $schoolID)->get()
            as $candidate
        ) {
            $cals = $this->extractCalendarYearsFromAcademicYear($candidate);
            if (!empty(array_intersect($cals, $calendarYears))) {
                $related[] = (int) $candidate->academic_yearID;
            }
        }

        $merged = array_values(
            array_unique(array_merge($yearIds, $related)),
        );

        return !empty($merged) ? $merged : $yearIds;
    }

    /**
     * Filter examinations by academic_yearID (preferred) with calendar-year fallback.
     * @param int|array|null $calendarYear Single year or list of calendar years
     */
    private function applyExaminationAcademicYearFilter(
        $query,
        array $yearIds,
        $calendarYear = null,
    ) {
        $yearIds = array_values(array_filter(array_map("intval", $yearIds)));
        $calendarYears = [];
        if (is_array($calendarYear)) {
            $calendarYears = array_values(
                array_filter(array_map("intval", $calendarYear)),
            );
        } elseif ($calendarYear) {
            $calendarYears = [(int) $calendarYear];
        }

        if (!empty($yearIds)) {
            $query->where(function ($q) use ($yearIds, $calendarYears) {
                $q->whereIn("academic_yearID", $yearIds);
                foreach ($yearIds as $yearId) {
                    $q->orWhereJsonContains("academic_year_ids", $yearId);
                    $q->orWhereJsonContains(
                        "academic_year_ids",
                        (string) $yearId,
                    );
                }
                if (!empty($calendarYears)) {
                    $q->orWhere(function ($q2) use ($calendarYears) {
                        $q2->whereNull("academic_yearID")->whereIn(
                            "year",
                            $calendarYears,
                        );
                    });
                    // Bridge rows that have AY set but calendar year still matches selection
                    $q->orWhereIn("year", $calendarYears);
                }
            });
        } elseif (!empty($calendarYears)) {
            $query->whereIn("year", $calendarYears);
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
