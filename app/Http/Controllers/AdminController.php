<?php

namespace App\Http\Controllers;

use App\Models\SessionTask;
use App\Models\Teacher;
use App\Models\ClassSessionTimetable;
use App\Models\School;
use App\Models\ClassSubject;
use App\Models\SchemeOfWork;
use App\Models\SchemeOfWorkItem;
use App\Models\LessonPlan;
use App\Models\ClassModel;
use App\Models\SchoolSubject;
use App\Models\PermissionRequest;
use App\Models\ParentModel;
use App\Models\OtherStaff;
use App\Models\Student;
use App\Models\SchoolVisitor;
use App\Models\SchoolVisitorSmsLog;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function changePasswordForm()
    {
        $userType = Session::get("user_type");
        if ($userType !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.change_password");
    }

    public function changePassword(Request $request)
    {
        $userType = Session::get("user_type");
        if ($userType !== "Admin") {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    ["success" => false, "message" => "Unauthorized access"],
                    403,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validator = Validator::make(
            $request->all(),
            [
                "current_password" => "required|string",
                "new_password" => [
                    "required",
                    "string",
                    "min:8",
                    "confirmed",
                    "regex:/[a-z]/",
                    "regex:/[A-Z]/",
                    "regex:/[0-9]/",
                    "regex:/[^a-zA-Z0-9]/",
                ],
            ],
            [
                "new_password.regex" =>
                    "Password must include uppercase, lowercase, number, and special character.",
            ],
        );

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Validation failed",
                        "errors" => $validator->errors()->all(),
                    ],
                    422,
                );
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::find(Session::get("userID"));
        if (!$user || $user->user_type !== "Admin") {
            Session::flush();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Session expired. Please login again.",
                    ],
                    401,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Session expired. Please login again.");
        }

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Current password is incorrect.",
                    ],
                    422,
                );
            }
            return redirect()
                ->back()
                ->with("error", "Current password is incorrect.");
        }

        if (Hash::check($request->new_password, $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "New password must be different from the current password.",
                    ],
                    422,
                );
            }
            return redirect()
                ->back()
                ->with(
                    "error",
                    "New password must be different from the current password.",
                );
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        Session::forget("force_password_change");

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Password updated successfully.",
            ]);
        }
        return redirect()
            ->route("AdminDashboard")
            ->with("success", "Password updated successfully.");
    }

    public function AdminDashboard()
    {
        $user = Session::get("user_type");

        if (!$user) {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $schoolID = Session::get("schoolID");

        // Dashboard Statistics
        $dashboardStats = [];
        if ($schoolID) {
            // Count all active subjects in school
            $subjectsCount = DB::table("school_subjects")
                ->where("schoolID", $schoolID)
                ->where("status", "Active")
                ->count();

            // Count all active classes (subclasses) in school
            $classesCount = DB::table("subclasses")
                ->join("classes", "subclasses.classID", "=", "classes.classID")
                ->where("classes.schoolID", $schoolID)
                ->where("subclasses.status", "Active")
                ->count();

            // Count all active students in school
            $studentsCount = DB::table("students")
                ->join(
                    "subclasses",
                    "students.subclassID",
                    "=",
                    "subclasses.subclassID",
                )
                ->join("classes", "subclasses.classID", "=", "classes.classID")
                ->where("classes.schoolID", $schoolID)
                ->where("students.status", "Active")
                ->count();

            // Count all parents in school
            $parentsCount = DB::table("parents")
                ->where("schoolID", $schoolID)
                ->count();

            // Count all teachers in school
            $teachersCount = DB::table("teachers")
                ->where("schoolID", $schoolID)
                ->where("status", "Active")
                ->count();

            // Count all examinations in school
            $examinationsCount = DB::table("examinations")
                ->where("schoolID", $schoolID)
                ->count();

            // Count all fees records
            $feesCount = DB::table("fees")
                ->where("schoolID", $schoolID)
                ->count();

            // Get active session timetable definition
            $definition = DB::table("session_timetable_definitions")
                ->where("schoolID", $schoolID)
                ->first();

            // Count sessions per week (Monday-Friday) - all sessions in school
            $sessionsPerWeek = 0;
            if ($definition) {
                $sessionsPerWeek = DB::table("class_session_timetables")
                    ->where("definitionID", $definition->definitionID)
                    ->whereIn("day", [
                        "Monday",
                        "Tuesday",
                        "Wednesday",
                        "Thursday",
                        "Friday",
                    ])
                    ->count();
            }

            // Count sessions per year (excluding holidays and events)
            $sessionsPerYear = 0;
            if ($definition) {
                $currentYear = Carbon::now()->year;
                $yearStart = Carbon::create($currentYear, 1, 1);
                $yearEnd = Carbon::create($currentYear, 12, 31);

                // Get all holidays for the year
                $yearHolidays = DB::table("holidays")
                    ->where("schoolID", $schoolID)
                    ->where(function ($query) use ($yearStart, $yearEnd) {
                        $query
                            ->whereBetween("start_date", [$yearStart, $yearEnd])
                            ->orWhereBetween("end_date", [$yearStart, $yearEnd])
                            ->orWhere(function ($q) use ($yearStart, $yearEnd) {
                                $q->where(
                                    "start_date",
                                    "<=",
                                    $yearStart,
                                )->where("end_date", ">=", $yearEnd);
                            });
                    })
                    ->get();

                // Get non-working events
                $yearEvents = DB::table("events")
                    ->where("schoolID", $schoolID)
                    ->whereYear("event_date", $currentYear)
                    ->where("is_non_working_day", true)
                    ->get();

                // Calculate total working days in year
                $totalWorkingDays = 0;
                $current = $yearStart->copy();
                while ($current <= $yearEnd) {
                    // Check if it's a weekday (Monday-Friday)
                    if (
                        in_array($current->dayOfWeek, [
                            Carbon::MONDAY,
                            Carbon::TUESDAY,
                            Carbon::WEDNESDAY,
                            Carbon::THURSDAY,
                            Carbon::FRIDAY,
                        ])
                    ) {
                        $dateStr = $current->format("Y-m-d");
                        $isHoliday = false;

                        // Check holidays
                        foreach ($yearHolidays as $holiday) {
                            $holidayStart = Carbon::parse($holiday->start_date);
                            $holidayEnd = Carbon::parse($holiday->end_date);
                            if ($current->between($holidayStart, $holidayEnd)) {
                                $isHoliday = true;
                                break;
                            }
                        }

                        // Check events
                        if (!$isHoliday) {
                            foreach ($yearEvents as $event) {
                                if (
                                    $current->format("Y-m-d") ===
                                    Carbon::parse($event->event_date)->format(
                                        "Y-m-d",
                                    )
                                ) {
                                    $isHoliday = true;
                                    break;
                                }
                            }
                        }

                        if (!$isHoliday) {
                            $totalWorkingDays++;
                        }
                    }
                    $current->addDay();
                }

                // Sessions per year = sessions per week * (total working days / 5)
                if ($sessionsPerWeek > 0) {
                    $sessionsPerYear =
                        (int) ($sessionsPerWeek * ($totalWorkingDays / 5));
                }
            }

            // Count approved sessions (sessions with approved tasks) - all teachers
            $approvedSessionsCount = 0;
            if ($definition) {
                $approvedSessionsCount = DB::table("session_tasks")
                    ->join(
                        "class_session_timetables",
                        "session_tasks.session_timetableID",
                        "=",
                        "class_session_timetables.session_timetableID",
                    )
                    ->where("session_tasks.status", "approved")
                    ->where(
                        "class_session_timetables.definitionID",
                        $definition->definitionID,
                    )
                    ->distinct("session_tasks.session_timetableID")
                    ->count("session_tasks.session_timetableID");
            }

            $dashboardStats = [
                "subjects_count" => $subjectsCount,
                "classes_count" => $classesCount,
                "students_count" => $studentsCount,
                "parents_count" => $parentsCount,
                "teachers_count" => $teachersCount,
                "examinations_count" => $examinationsCount,
                "fees_count" => $feesCount,
                "sessions_per_week" => $sessionsPerWeek,
                "sessions_per_year" => $sessionsPerYear,
                "approved_sessions_count" => $approvedSessionsCount,
            ];
        }

        // Return admin dashboard view
        // Note: school_details is already shared via AppServiceProvider
        return view("Admin.dashboard", compact("dashboardStats"));
    }

    /**
     * Task Management page for Admin
     */
    public function taskManagement()
    {
        $user = Session::get("user_type");
        $taskPermissions = [
            "task_create",
            "task_update",
            "task_delete",
            "task_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($taskPermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Session expired");
        }

        // Get all teachers for filter
        $teachers = Teacher::where("schoolID", $schoolID)
            ->orderBy("first_name")
            ->orderBy("last_name")
            ->get();

        return view("Admin.task_management", compact("teachers"));
    }

    /**
     * Get teacher tasks with filters
     */
    public function getTeacherTasks(Request $request)
    {
        try {
            $user = Session::get("user_type");
            $taskPermissions = [
                "task_create",
                "task_update",
                "task_delete",
                "task_read_only",
            ];
            if (
                !$user ||
                ($user !== "Admin" &&
                    !$this->staffHasAnyPermission($taskPermissions))
            ) {
                return response()->json(
                    ["success" => false, "error" => "Unauthorized access"],
                    403,
                );
            }

            $schoolID = Session::get("schoolID");
            if (!$schoolID) {
                return response()->json(
                    ["success" => false, "error" => "Session expired"],
                    401,
                );
            }

            $teacherID = $request->input("teacherID");
            $date = $request->input("date");
            $status = $request->input("status");

            $query = SessionTask::with([
                "teacher",
                "sessionTimetable.subject",
                "sessionTimetable.classSubject.subject",
                "sessionTimetable.subclass.class",
            ])->where("schoolID", $schoolID);

            if ($teacherID) {
                $query->where("teacherID", $teacherID);
            }

            if ($date) {
                $query->where("task_date", $date);
            }

            if ($status) {
                $query->where("status", $status);
            }

            $tasks = $query
                ->orderBy("task_date", "desc")
                ->orderBy("created_at", "desc")
                ->get()
                ->map(function ($task) {
                    $startTime = $task->sessionTimetable->start_time ?? null;
                    $endTime = $task->sessionTimetable->end_time ?? null;
                    $day = $task->sessionTimetable->day ?? "N/A";

                    // Get subject name - try from subject relationship first, then from classSubject
                    $subjectName = "N/A";
                    if ($task->sessionTimetable) {
                        if (
                            $task->sessionTimetable->subject &&
                            $task->sessionTimetable->subject->subject_name
                        ) {
                            $subjectName =
                                $task->sessionTimetable->subject->subject_name;
                        } elseif (
                            $task->sessionTimetable->classSubject &&
                            $task->sessionTimetable->classSubject->subject &&
                            $task->sessionTimetable->classSubject->subject
                                ->subject_name
                        ) {
                            $subjectName =
                                $task->sessionTimetable->classSubject->subject
                                    ->subject_name;
                        }
                    }

                    return [
                        "session_taskID" => $task->session_taskID,
                        "teacher_name" => $task->teacher
                            ? $task->teacher->first_name .
                                " " .
                                $task->teacher->last_name
                            : "N/A",
                        "subject_name" => $subjectName,
                        "class_name" => $task->sessionTimetable->subclass
                            ? ($task->sessionTimetable->subclass->class
                                    ->class_name ??
                                    "") .
                                " - " .
                                ($task->sessionTimetable->subclass
                                    ->subclass_name ??
                                    "")
                            : "N/A",
                        "task_date" => $task->task_date->format("Y-m-d"),
                        "task_date_display" => $task->task_date->format(
                            "F d, Y",
                        ),
                        "day" => $day,
                        "start_time" => $startTime
                            ? \Carbon\Carbon::parse($startTime)->format("h:i A")
                            : "N/A",
                        "end_time" => $endTime
                            ? \Carbon\Carbon::parse($endTime)->format("h:i A")
                            : "N/A",
                        "time_display" =>
                            $startTime && $endTime
                                ? \Carbon\Carbon::parse($startTime)->format(
                                        "h:i A",
                                    ) .
                                    " - " .
                                    \Carbon\Carbon::parse($endTime)->format(
                                        "h:i A",
                                    )
                                : "N/A",
                        "topic" =>
                            $task->topic && trim($task->topic)
                                ? $task->topic
                                : "N/A",
                        "subtopic" =>
                            $task->subtopic && trim($task->subtopic)
                                ? $task->subtopic
                                : "N/A",
                        "task_description" => $task->task_description,
                        "status" => $task->status,
                        "admin_comment" => $task->admin_comment,
                        "approved_at" => $task->approved_at
                            ? $task->approved_at->format("Y-m-d H:i")
                            : null,
                    ];
                });

            return response()->json([
                "success" => true,
                "tasks" => $tasks,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ["success" => false, "error" => $e->getMessage()],
                500,
            );
        }
    }

    /**
     * Approve a task
     */
    public function approveTask($taskID)
    {
        try {
            $user = Session::get("user_type");
            if (!$user || $user !== "Admin") {
                return response()->json(
                    ["success" => false, "error" => "Unauthorized access"],
                    403,
                );
            }

            $validator = Validator::make(request()->all(), [
                "admin_comment" => "nullable|string|max:500",
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ["success" => false, "errors" => $validator->errors()],
                    422,
                );
            }

            $task = SessionTask::with([
                "teacher",
                "sessionTimetable.subject",
                "sessionTimetable.classSubject.subject",
                "sessionTimetable.subclass.class",
            ])->findOrFail($taskID);

            // Verify task belongs to admin's school
            $schoolID = Session::get("schoolID");
            if ($task->schoolID != $schoolID) {
                return response()->json(
                    ["success" => false, "error" => "Unauthorized access"],
                    403,
                );
            }

            $task->update([
                "status" => "approved",
                "admin_comment" => request()->input("admin_comment"),
                "approved_by" => Session::get("user_id"), // Assuming user_id is stored in session
                "approved_at" => now(),
            ]);

            // Send SMS to teacher
            try {
                $teacher = $task->teacher;
                $school = \App\Models\School::find($schoolID);
                $schoolName = $school ? $school->school_name : "ShuleXpert";

                // Get subject and class info
                $subjectName = "N/A";
                $className = "N/A";
                if ($task->sessionTimetable) {
                    if ($task->sessionTimetable->subject) {
                        $subjectName =
                            $task->sessionTimetable->subject->subject_name;
                    } elseif (
                        $task->sessionTimetable->classSubject &&
                        $task->sessionTimetable->classSubject->subject
                    ) {
                        $subjectName =
                            $task->sessionTimetable->classSubject->subject
                                ->subject_name;
                    }

                    if ($task->sessionTimetable->subclass) {
                        $class = $task->sessionTimetable->subclass->class;
                        $subclassName = trim(
                            $task->sessionTimetable->subclass->subclass_name,
                        );
                        $className = $class->class_name;
                        if ($subclassName !== "") {
                            $className .= " - " . $subclassName;
                        }
                    }
                }

                $taskDate = $task->task_date->format("d/m/Y");
                $comment = request()->input("admin_comment");
                $topic = $task->topic ?? "";
                $subtopic = $task->subtopic ?? "";

                // Build SMS message
                $message = "{$schoolName}. Task yako imeidhinishwa. Somo: {$subjectName}, Darasa: {$className}, Tarehe: {$taskDate}";
                if ($topic) {
                    $message .= ". Topic: {$topic}";
                }
                if ($subtopic) {
                    $message .= ", Subtopic: {$subtopic}";
                }
                if ($comment && trim($comment)) {
                    $message .= ". Maoni: " . trim($comment);
                }
                $message .= ". Asante";

                if ($teacher && $teacher->phone_number) {
                    $smsService = new \App\Services\SmsService();
                    $smsResult = $smsService->sendSms(
                        $teacher->phone_number,
                        $message,
                    );

                    if (!$smsResult["success"]) {
                        \Illuminate\Support\Facades\Log::warning(
                            "Failed to send approval SMS to teacher {$teacher->id}: " .
                                ($smsResult["message"] ?? "Unknown error"),
                        );
                    }
                }
            } catch (\Exception $smsException) {
                \Illuminate\Support\Facades\Log::error(
                    "Error sending approval SMS to teacher: " .
                        $smsException->getMessage(),
                );
            }

            return response()->json([
                "success" => true,
                "message" => "Task approved successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ["success" => false, "error" => $e->getMessage()],
                500,
            );
        }
    }

    /**
     * Reject a task
     */
    public function rejectTask($taskID)
    {
        try {
            $user = Session::get("user_type");
            if (!$user || $user !== "Admin") {
                return response()->json(
                    ["success" => false, "error" => "Unauthorized access"],
                    403,
                );
            }

            $validator = Validator::make(request()->all(), [
                "admin_comment" => "required|string|max:500",
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ["success" => false, "errors" => $validator->errors()],
                    422,
                );
            }

            $task = SessionTask::with([
                "teacher",
                "sessionTimetable.subject",
                "sessionTimetable.classSubject.subject",
                "sessionTimetable.subclass.class",
            ])->findOrFail($taskID);

            // Verify task belongs to admin's school
            $schoolID = Session::get("schoolID");
            if ($task->schoolID != $schoolID) {
                return response()->json(
                    ["success" => false, "error" => "Unauthorized access"],
                    403,
                );
            }

            $task->update([
                "status" => "rejected",
                "admin_comment" => request()->input("admin_comment"),
                "approved_by" => Session::get("user_id"),
                "approved_at" => now(),
            ]);

            // Send SMS to teacher with rejection reason
            try {
                $teacher = $task->teacher;
                $school = \App\Models\School::find($schoolID);
                $schoolName = $school ? $school->school_name : "ShuleXpert";

                // Get subject and class info
                $subjectName = "N/A";
                $className = "N/A";
                if ($task->sessionTimetable) {
                    if ($task->sessionTimetable->subject) {
                        $subjectName =
                            $task->sessionTimetable->subject->subject_name;
                    } elseif (
                        $task->sessionTimetable->classSubject &&
                        $task->sessionTimetable->classSubject->subject
                    ) {
                        $subjectName =
                            $task->sessionTimetable->classSubject->subject
                                ->subject_name;
                    }

                    if ($task->sessionTimetable->subclass) {
                        $class = $task->sessionTimetable->subclass->class;
                        $subclassName = trim(
                            $task->sessionTimetable->subclass->subclass_name,
                        );
                        $className = $class->class_name;
                        if ($subclassName !== "") {
                            $className .= " - " . $subclassName;
                        }
                    }
                }

                $taskDate = $task->task_date->format("d/m/Y");
                $reason = request()->input("admin_comment"); // This is the rejection reason
                $topic = $task->topic ?? "";
                $subtopic = $task->subtopic ?? "";

                // Build SMS message
                $message = "{$schoolName}. Task yako imekataliwa. Somo: {$subjectName}, Darasa: {$className}, Tarehe: {$taskDate}";
                if ($topic) {
                    $message .= ". Topic: {$topic}";
                }
                if ($subtopic) {
                    $message .= ", Subtopic: {$subtopic}";
                }
                if ($reason && trim($reason)) {
                    $message .= ". Sababu: " . trim($reason);
                }
                $message .= ". Asante";

                if ($teacher && $teacher->phone_number) {
                    $smsService = new \App\Services\SmsService();
                    $smsResult = $smsService->sendSms(
                        $teacher->phone_number,
                        $message,
                    );

                    if (!$smsResult["success"]) {
                        \Illuminate\Support\Facades\Log::warning(
                            "Failed to send rejection SMS to teacher {$teacher->id}: " .
                                ($smsResult["message"] ?? "Unknown error"),
                        );
                    }
                }
            } catch (\Exception $smsException) {
                \Illuminate\Support\Facades\Log::error(
                    "Error sending rejection SMS to teacher: " .
                        $smsException->getMessage(),
                );
            }

            return response()->json([
                "success" => true,
                "message" => "Task rejected successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ["success" => false, "error" => $e->getMessage()],
                500,
            );
        }
    }

    public function adminSchemeOfWork()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user) {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        if (!$schoolID) {
            return redirect()
                ->route("AdminDashboard")
                ->with("error", "School ID not found");
        }

        // Get all class subjects with scheme of work for this school
        $currentYear = date("Y");

        $classSubjectsWithSchemes = ClassSubject::with([
            "subject",
            "class",
            "subclass.class",
            "teacher",
            "schemeOfWork" => function ($query) use ($currentYear) {
                $query->where("year", $currentYear)->orderBy("year", "desc");
            },
            "schemeOfWork.items",
            "schemeOfWork.createdBy",
        ])
            ->whereHas("class", function ($query) use ($schoolID) {
                $query->where("schoolID", $schoolID)->where("status", "Active");
            })
            ->where("status", "Active")
            ->get()
            ->map(function ($classSubject) use ($currentYear) {
                // Get current year scheme
                $currentScheme = $classSubject->schemeOfWork
                    ->where("year", $currentYear)
                    ->first();

                // Calculate progress (percentage of items marked as done)
                $progress = 0;
                $totalItems = 0;
                $doneItems = 0;

                if ($currentScheme) {
                    $totalItems = $currentScheme->items->count();
                    $doneItems = $currentScheme->items
                        ->where("remarks", "done")
                        ->count();
                    if ($totalItems > 0) {
                        $progress = round(($doneItems / $totalItems) * 100, 2);
                    }
                }

                return [
                    "class_subjectID" => $classSubject->class_subjectID,
                    "subject_name" =>
                        $classSubject->subject->subject_name ?? "N/A",
                    "class_name" =>
                        $classSubject->subclass &&
                        $classSubject->subclass->class
                            ? $classSubject->subclass->class->class_name .
                                " " .
                                $classSubject->subclass->subclass_name
                            : ($classSubject->class
                                ? $classSubject->class->class_name
                                : "N/A"),
                    "teacher_name" => $classSubject->teacher
                        ? $classSubject->teacher->first_name .
                            " " .
                            $classSubject->teacher->last_name
                        : "Not Assigned",
                    "teacherID" => $classSubject->teacherID,
                    "scheme" => $currentScheme,
                    "progress" => $progress,
                    "totalItems" => $totalItems,
                    "doneItems" => $doneItems,
                    "year" => $currentYear,
                ];
            })
            ->sortBy("subject_name")
            ->values();

        return view(
            "Admin.scheme_of_work",
            compact("classSubjectsWithSchemes"),
        );
    }

    public function adminViewSchemeOfWork($schemeOfWorkID)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || !in_array($user, ["Admin", "Staff", "Teacher"])) {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        if (!$schoolID) {
            return redirect()
                ->route("AdminDashboard")
                ->with("error", "School ID not found");
        }

        // Get scheme of work with relationships
        $scheme = SchemeOfWork::with([
            "classSubject.subject",
            "classSubject.class",
            "classSubject.subclass.class",
            "items" => function ($query) {
                $query->orderBy("month")->orderBy("row_order");
            },
            "learningObjectives" => function ($query) {
                $query->orderBy("order");
            },
            "createdBy",
        ])
            ->where("scheme_of_workID", $schemeOfWorkID)
            ->first();

        if (!$scheme) {
            return redirect()
                ->route("admin.schemeOfWork")
                ->with("error", "Scheme of work not found");
        }

        // Verify scheme belongs to admin's school
        $classSubject = $scheme->classSubject;
        $schemeSchoolID = null;

        if ($classSubject->subclass && $classSubject->subclass->class) {
            $schemeSchoolID = $classSubject->subclass->class->schoolID;
        } elseif ($classSubject->class) {
            $schemeSchoolID = $classSubject->class->schoolID;
        }

        if ($schemeSchoolID != $schoolID) {
            return redirect()
                ->route("admin.schemeOfWork")
                ->with(
                    "error",
                    "You do not have access to this scheme of work",
                );
        }

        // Get school info
        $school = School::where("schoolID", $schoolID)->first();

        return view("Teacher.view_scheme_of_work", compact("scheme", "school"));
    }

    /**
     * Admin view lesson plans
     */
    public function adminLessonPlans()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || !in_array($user, ["Admin", "Staff", "Teacher"])) {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $school = School::where("schoolID", $schoolID)->first();
        $schoolType =
            $school && $school->school_type
                ? strtolower($school->school_type)
                : "primary";
        $isPrimary =
            strpos($schoolType, "primary") !== false ||
            strpos($schoolType, "pre") !== false;
        $schoolTypeDisplay = $isPrimary
            ? "PRE AND PRIMARY SCHOOL"
            : "SECONDARY SCHOOL";

        $classes = ClassModel::where("schoolID", $schoolID)
            ->where("status", "Active")
            ->orderBy("class_name")
            ->get(["classID", "class_name"]);

        $schoolStampUrl =
            $school && $school->school_stamp
                ? asset($school->school_stamp)
                : null;
        $schoolSignatureUrl =
            $school && $school->school_signature
                ? asset($school->school_signature)
                : null;

        return view(
            "Admin.lesson_plans",
            compact(
                "classes",
                "school",
                "schoolTypeDisplay",
                "schoolStampUrl",
                "schoolSignatureUrl",
            ),
        );
    }

    /**
     * Manage Other Staff - Redirect to ManageOtherStaffController
     */
    public function manageOtherStaff()
    {
        // Delegate to ManageOtherStaffController
        $controller = new ManageOtherStaffController();
        return $controller->manageOtherStaff();
    }

    /**
     * Manage Revenue
     */
    public function manageRevenue()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        $revenuePermissions = [
            "revenue_create",
            "revenue_update",
            "revenue_delete",
            "revenue_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($revenuePermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        $revenueSources = \App\Models\RevenueSource::where(
            "schoolID",
            $schoolID,
        )
            ->where("status", "Active")
            ->orderBy("source_name")
            ->get();

        return view(
            "Admin.manage_revenue",
            compact("schoolID", "revenueSources"),
        );
    }

    /**
     * Store revenue source
     */
    public function storeRevenueSource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "source_name" => "required|string|max:255",
            "source_type" => "required|in:fixed,per_item,variable",
            "default_amount" => "nullable|numeric|min:0",
            "description" => "nullable|string",
        ]);

        \App\Models\RevenueSource::create([
            "schoolID" => $schoolID,
            "source_name" => $validated["source_name"],
            "source_type" => $validated["source_type"],
            "default_amount" => $validated["default_amount"] ?? null,
            "description" => $validated["description"] ?? null,
            "status" => "Active",
        ]);

        return redirect()
            ->route("manage_revenue")
            ->with("success", "Source of income added successfully.");
    }

    /**
     * Update revenue source
     */
    public function updateRevenueSource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "revenue_sourceID" =>
                "required|exists:revenue_sources,revenue_sourceID",
            "source_name" => "required|string|max:255",
            "source_type" => "required|in:fixed,per_item,variable",
            "default_amount" => "nullable|numeric|min:0",
            "description" => "nullable|string",
            "status" => "required|in:Active,Inactive",
        ]);

        $source = \App\Models\RevenueSource::where(
            "revenue_sourceID",
            $validated["revenue_sourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $source->update([
            "source_name" => $validated["source_name"],
            "source_type" => $validated["source_type"],
            "default_amount" => $validated["default_amount"] ?? null,
            "description" => $validated["description"] ?? null,
            "status" => $validated["status"],
        ]);

        return redirect()
            ->route("manage_revenue")
            ->with("success", "Source of income updated successfully.");
    }

    public function deleteRevenueSource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "revenue_sourceID" =>
                "required|exists:revenue_sources,revenue_sourceID",
        ]);

        $source = \App\Models\RevenueSource::where(
            "revenue_sourceID",
            $validated["revenue_sourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $source->update(["status" => "Inactive"]);

        return redirect()
            ->route("manage_revenue")
            ->with("success", "Source of income deleted successfully.");
    }

    /**
     * Store revenue record
     */
    public function storeRevenueRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "record_date" => "required|date",
            "revenue_sourceID" =>
                "required|exists:revenue_sources,revenue_sourceID",
            "quantity" => "nullable|integer|min:0",
            "amount" => "nullable|numeric|min:0",
            "note" => "nullable|string",
        ]);

        $source = \App\Models\RevenueSource::where(
            "revenue_sourceID",
            $validated["revenue_sourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $quantity = $validated["quantity"] ?? null;
        $amountInput = $validated["amount"] ?? null;
        $unitAmount = null;
        $totalAmount = 0;

        if ($source->source_type === "per_item") {
            $unitAmount = $source->default_amount ?? 0;
            $totalAmount = $unitAmount * ((int) ($quantity ?? 0));
        } elseif ($source->source_type === "fixed") {
            $unitAmount = $source->default_amount ?? 0;
            $totalAmount = $unitAmount;
        } else {
            $unitAmount = $amountInput ?? 0;
            $totalAmount = $unitAmount;
        }

        if ($totalAmount <= 0) {
            return redirect()
                ->route("manage_revenue")
                ->with("error", "Amount must be greater than 0.");
        }

        \App\Models\RevenueRecord::create([
            "schoolID" => $schoolID,
            "revenue_sourceID" => $source->revenue_sourceID,
            "record_date" => $validated["record_date"],
            "unit_amount" => $unitAmount,
            "quantity" => $quantity,
            "total_amount" => $totalAmount,
            "note" => $validated["note"] ?? null,
        ]);

        return redirect()
            ->route("manage_revenue")
            ->with("success", "Revenue recorded successfully.");
    }

    /**
     * Get revenue report data for other sources
     */
    public function revenueReportData(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $year = $request->input("year", date("Y"));

        $totals = \App\Models\RevenueRecord::where("schoolID", $schoolID)
            ->whereYear("record_date", $year)
            ->select(
                "revenue_sourceID",
                \DB::raw("SUM(total_amount) as total_amount"),
            )
            ->groupBy("revenue_sourceID")
            ->get()
            ->pluck("total_amount", "revenue_sourceID");

        return response()->json([
            "success" => true,
            "data" => $totals,
        ]);
    }

    /**
     * Manage Expenses
     */
    public function manageExpenses()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        $expensePermissions = [
            "expenses_create",
            "expenses_update",
            "expenses_delete",
            "expenses_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($expensePermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        $year = request()->input("year", date("Y"));

        $totalRevenue = $this->getTotalRevenueForYear($schoolID, $year);
        $budget = \App\Models\ExpenseBudget::where("schoolID", $schoolID)
            ->where("year", $year)
            ->first();

        $expenses = \App\Models\ExpenseRecord::where("schoolID", $schoolID)
            ->whereYear("expense_date", $year)
            ->orderBy("expense_date", "desc")
            ->limit(50)
            ->get();

        return view(
            "Admin.manage_expenses",
            compact("schoolID", "year", "totalRevenue", "budget", "expenses"),
        );
    }

    private function getTotalRevenueForYear($schoolID, $year)
    {
        $feesRevenue = \DB::table("payment_records")
            ->join(
                "payments",
                "payment_records.paymentID",
                "=",
                "payments.paymentID",
            )
            ->join(
                "academic_years",
                "payments.academic_yearID",
                "=",
                "academic_years.academic_yearID",
            )
            ->where("payments.schoolID", $schoolID)
            ->where("academic_years.year", $year)
            ->sum("payment_records.paid_amount");

        $otherRevenue = \App\Models\RevenueRecord::where("schoolID", $schoolID)
            ->whereYear("record_date", $year)
            ->sum("total_amount");

        return (float) $feesRevenue + (float) $otherRevenue;
    }

    public function storeExpenseBudget(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "year" => "required|integer|min:2000|max:2100",
            "total_amount" => "required|numeric|min:0.01",
        ]);

        $totalRevenue = $this->getTotalRevenueForYear(
            $schoolID,
            $validated["year"],
        );
        if ($validated["total_amount"] > $totalRevenue) {
            return redirect()
                ->route("manage_expenses", ["year" => $validated["year"]])
                ->with(
                    "error",
                    "Budget cannot exceed total revenue for the selected year.",
                );
        }

        $existing = \App\Models\ExpenseBudget::where("schoolID", $schoolID)
            ->where("year", $validated["year"])
            ->first();

        if ($existing) {
            return redirect()
                ->route("manage_expenses", ["year" => $validated["year"]])
                ->with(
                    "error",
                    "Budget for this year already exists. Use update instead.",
                );
        }

        \App\Models\ExpenseBudget::create([
            "schoolID" => $schoolID,
            "year" => $validated["year"],
            "total_amount" => $validated["total_amount"],
            "remaining_amount" => $validated["total_amount"],
            "status" => "Active",
        ]);

        return redirect()
            ->route("manage_expenses", ["year" => $validated["year"]])
            ->with("success", "Budget created successfully.");
    }

    public function updateExpenseBudget(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "expense_budgetID" =>
                "required|exists:expense_budgets,expense_budgetID",
            "total_amount" => "required|numeric|min:0.01",
        ]);

        $budget = \App\Models\ExpenseBudget::where(
            "expense_budgetID",
            $validated["expense_budgetID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $totalRevenue = $this->getTotalRevenueForYear($schoolID, $budget->year);
        if ($validated["total_amount"] > $totalRevenue) {
            return redirect()
                ->route("manage_expenses", ["year" => $budget->year])
                ->with(
                    "error",
                    "Budget cannot exceed total revenue for the selected year.",
                );
        }

        $spent = \App\Models\ExpenseRecord::where("schoolID", $schoolID)
            ->where("expense_budgetID", $budget->expense_budgetID)
            ->sum("amount");

        if ($validated["total_amount"] < $spent) {
            return redirect()
                ->route("manage_expenses", ["year" => $budget->year])
                ->with(
                    "error",
                    "Budget cannot be less than total expenses already recorded.",
                );
        }

        $budget->update([
            "total_amount" => $validated["total_amount"],
            "remaining_amount" => $validated["total_amount"] - $spent,
        ]);

        return redirect()
            ->route("manage_expenses", ["year" => $budget->year])
            ->with("success", "Budget updated successfully.");
    }

    public function storeExpenseRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "expense_budgetID" =>
                "required|exists:expense_budgets,expense_budgetID",
            "expense_date" => "required|date",
            "amount" => "required|numeric|min:0.01",
            "description" => "required|string",
        ]);

        $budget = \App\Models\ExpenseBudget::where(
            "expense_budgetID",
            $validated["expense_budgetID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        if ($validated["amount"] > $budget->remaining_amount) {
            return redirect()
                ->route("manage_expenses", ["year" => $budget->year])
                ->with("error", "Expense exceeds remaining budget.");
        }

        \DB::transaction(function () use ($validated, $schoolID, $budget) {
            \App\Models\ExpenseRecord::create([
                "schoolID" => $schoolID,
                "expense_budgetID" => $budget->expense_budgetID,
                "expense_date" => $validated["expense_date"],
                "amount" => $validated["amount"],
                "description" => $validated["description"],
            ]);

            $budget->update([
                "remaining_amount" =>
                    $budget->remaining_amount - $validated["amount"],
            ]);
        });

        return redirect()
            ->route("manage_expenses", ["year" => $budget->year])
            ->with("success", "Expense recorded successfully.");
    }

    public function updateExpenseRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "expense_recordID" =>
                "required|exists:expense_records,expense_recordID",
            "expense_date" => "required|date",
            "amount" => "required|numeric|min:0.01",
            "description" => "required|string",
        ]);

        $record = \App\Models\ExpenseRecord::where(
            "expense_recordID",
            $validated["expense_recordID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $budget = \App\Models\ExpenseBudget::where(
            "expense_budgetID",
            $record->expense_budgetID,
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $available = $budget->remaining_amount + $record->amount;
        if ($validated["amount"] > $available) {
            return redirect()
                ->route("manage_expenses", ["year" => $budget->year])
                ->with("error", "Updated amount exceeds remaining budget.");
        }

        \DB::transaction(function () use ($record, $budget, $validated) {
            $budget->update([
                "remaining_amount" =>
                    $budget->remaining_amount +
                    $record->amount -
                    $validated["amount"],
            ]);

            $record->update([
                "expense_date" => $validated["expense_date"],
                "amount" => $validated["amount"],
                "description" => $validated["description"],
            ]);
        });

        return redirect()
            ->route("manage_expenses", ["year" => $budget->year])
            ->with("success", "Expense updated successfully.");
    }

    public function deleteExpenseRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "expense_recordID" =>
                "required|exists:expense_records,expense_recordID",
        ]);

        $record = \App\Models\ExpenseRecord::where(
            "expense_recordID",
            $validated["expense_recordID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $budget = \App\Models\ExpenseBudget::where(
            "expense_budgetID",
            $record->expense_budgetID,
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        \DB::transaction(function () use ($record, $budget) {
            $budget->update([
                "remaining_amount" =>
                    $budget->remaining_amount + $record->amount,
            ]);
            $record->delete();
        });

        return redirect()
            ->route("manage_expenses", ["year" => $budget->year])
            ->with("success", "Expense deleted successfully.");
    }

    /**
     * School Resources Management Methods
     */
    public function manageIncomingResources()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        $resourcePermissions = [
            "resources_create",
            "resources_update",
            "resources_delete",
            "resources_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($resourcePermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        $resources = \App\Models\SchoolResource::where("schoolID", $schoolID)
            ->orderBy("resource_name")
            ->get();

        $incomingRecords = \App\Models\IncomingResourceRecord::where(
            "schoolID",
            $schoolID,
        )
            ->orderBy("received_date", "desc")
            ->limit(50)
            ->get();

        return view(
            "Admin.manage_incoming_resources",
            compact("schoolID", "resources", "incomingRecords"),
        );
    }

    public function storeSchoolResources(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "resources" => "required|array|min:1",
            "resources.*.resource_name" => "required|string|max:255",
            "resources.*.resource_type" => "required|string|max:100",
            "resources.*.requires_quantity" => "required|in:0,1",
            "resources.*.requires_price" => "required|in:0,1",
            "resources.*.quantity" => "nullable|integer|min:0",
            "resources.*.unit_price" => "nullable|numeric|min:0",
        ]);

        foreach ($validated["resources"] as $resource) {
            $requiresQuantity = (bool) $resource["requires_quantity"];
            $requiresPrice = (bool) $resource["requires_price"];
            $quantity = $requiresQuantity
                ? (int) ($resource["quantity"] ?? 0)
                : null;
            $unitPrice = $requiresPrice
                ? (float) ($resource["unit_price"] ?? 0)
                : 0;

            \App\Models\SchoolResource::create([
                "schoolID" => $schoolID,
                "resource_name" => $resource["resource_name"],
                "resource_type" => $resource["resource_type"],
                "requires_quantity" => $requiresQuantity,
                "requires_price" => $requiresPrice,
                "quantity" => $quantity,
                "unit_price" => $unitPrice,
            ]);
        }

        return redirect()
            ->route("manage_incoming_resources")
            ->with("success", "Resources saved successfully.");
    }

    public function updateSchoolResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "resourceID" => "required|exists:school_resources,resourceID",
            "resource_name" => "required|string|max:255",
            "resource_type" => "required|string|max:100",
            "requires_quantity" => "required|in:0,1",
            "requires_price" => "required|in:0,1",
            "quantity" => "nullable|integer|min:0",
            "unit_price" => "nullable|numeric|min:0",
        ]);

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $validated["resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $validated["requires_quantity"];
        $requiresPrice = (bool) $validated["requires_price"];
        $quantity = $requiresQuantity
            ? (int) ($validated["quantity"] ?? 0)
            : null;
        $unitPrice = $requiresPrice
            ? (float) ($validated["unit_price"] ?? 0)
            : 0;

        $resource->update([
            "resource_name" => $validated["resource_name"],
            "resource_type" => $validated["resource_type"],
            "requires_quantity" => $requiresQuantity,
            "requires_price" => $requiresPrice,
            "quantity" => $quantity,
            "unit_price" => $unitPrice,
        ]);

        return redirect()
            ->route("manage_incoming_resources")
            ->with("success", "Resource updated successfully.");
    }

    public function deleteSchoolResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "resourceID" => "required|exists:school_resources,resourceID",
        ]);

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $validated["resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $hasIncoming = \App\Models\IncomingResourceRecord::where(
            "schoolID",
            $schoolID,
        )
            ->where("resourceID", $resource->resourceID)
            ->exists();
        $hasOutgoing = \App\Models\OutgoingResourceRecord::where(
            "schoolID",
            $schoolID,
        )
            ->where("resourceID", $resource->resourceID)
            ->exists();
        $hasDamaged = \App\Models\DamagedLostRecord::where(
            "schoolID",
            $schoolID,
        )
            ->where("resourceID", $resource->resourceID)
            ->exists();

        if ($hasIncoming || $hasOutgoing || $hasDamaged) {
            return redirect()
                ->route("manage_incoming_resources")
                ->with(
                    "error",
                    "Cannot delete resource with existing records.",
                );
        }

        $resource->delete();

        return redirect()
            ->route("manage_incoming_resources")
            ->with("success", "Resource deleted successfully.");
    }

    public function storeIncomingResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "resourceID" => "required|exists:school_resources,resourceID",
            "received_date" => "required|date",
            "quantity" => "nullable|integer|min:0",
            "unit_price" => "nullable|numeric|min:0",
            "note" => "nullable|string",
        ]);

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $validated["resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $requiresPrice = (bool) $resource->requires_price;
        $quantity = $requiresQuantity
            ? (int) ($validated["quantity"] ?? 0)
            : null;
        $unitPrice = $requiresPrice
            ? (float) ($validated["unit_price"] ?? $resource->unit_price)
            : 0;
        $totalPrice = $requiresPrice
            ? ($requiresQuantity
                ? $unitPrice * $quantity
                : $unitPrice)
            : 0;

        \DB::transaction(function () use (
            $schoolID,
            $resource,
            $validated,
            $quantity,
            $unitPrice,
            $totalPrice,
            $requiresQuantity,
        ) {
            \App\Models\IncomingResourceRecord::create([
                "schoolID" => $schoolID,
                "resourceID" => $resource->resourceID,
                "received_date" => $validated["received_date"],
                "quantity" => $quantity,
                "unit_price" => $unitPrice,
                "total_price" => $totalPrice,
                "note" => $validated["note"] ?? null,
            ]);

            if ($requiresQuantity) {
                $resource->update([
                    "quantity" => ($resource->quantity ?? 0) + $quantity,
                ]);
            }
        });

        return redirect()
            ->route("manage_incoming_resources")
            ->with("success", "Incoming resource recorded successfully.");
    }

    public function manageOutgoingResources()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        $resourcePermissions = [
            "resources_create",
            "resources_update",
            "resources_delete",
            "resources_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($resourcePermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        $resources = \App\Models\SchoolResource::where("schoolID", $schoolID)
            ->orderBy("resource_name")
            ->get();

        $outgoingRecords = \App\Models\OutgoingResourceRecord::where(
            "schoolID",
            $schoolID,
        )
            ->orderBy("outgoing_date", "desc")
            ->limit(50)
            ->get();

        $outgoingSummary = \App\Models\OutgoingResourceRecord::where(
            "schoolID",
            $schoolID,
        )
            ->select(
                "resourceID",
                \DB::raw("SUM(quantity) as total_quantity"),
                \DB::raw("SUM(total_price) as total_price"),
            )
            ->groupBy("resourceID")
            ->get()
            ->keyBy("resourceID");

        return view(
            "Admin.manage_outgoing_resources",
            compact(
                "schoolID",
                "resources",
                "outgoingRecords",
                "outgoingSummary",
            ),
        );
    }

    public function storeOutgoingResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "resourceID" => "required|exists:school_resources,resourceID",
            "outgoing_date" => "required|date",
            "outgoing_type" => "required|in:permanent,temporary",
            "quantity" => "nullable|integer|min:0",
            "unit_price" => "nullable|numeric|min:0",
            "description" => "required|string",
        ]);

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $validated["resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $requiresPrice = (bool) $resource->requires_price;
        $quantity = $requiresQuantity
            ? (int) ($validated["quantity"] ?? 0)
            : null;
        $unitPrice = $requiresPrice
            ? (float) ($validated["unit_price"] ?? $resource->unit_price)
            : 0;
        $totalPrice = $requiresPrice
            ? ($requiresQuantity
                ? $unitPrice * $quantity
                : $unitPrice)
            : 0;

        if ($requiresQuantity && $quantity > ($resource->quantity ?? 0)) {
            return redirect()
                ->route("manage_outgoing_resources")
                ->with("error", "Outgoing quantity exceeds available stock.");
        }

        \DB::transaction(function () use (
            $schoolID,
            $resource,
            $validated,
            $quantity,
            $unitPrice,
            $totalPrice,
            $requiresQuantity,
        ) {
            \App\Models\OutgoingResourceRecord::create([
                "schoolID" => $schoolID,
                "resourceID" => $resource->resourceID,
                "outgoing_date" => $validated["outgoing_date"],
                "outgoing_type" => $validated["outgoing_type"],
                "quantity" => $quantity,
                "unit_price" => $unitPrice,
                "total_price" => $totalPrice,
                "description" => $validated["description"],
            ]);

            if ($requiresQuantity) {
                $resource->update([
                    "quantity" => max(
                        0,
                        ($resource->quantity ?? 0) - $quantity,
                    ),
                ]);
            }
        });

        return redirect()
            ->route("manage_outgoing_resources")
            ->with("success", "Outgoing resource recorded successfully.");
    }

    public function updateOutgoingResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "outgoing_resourceID" =>
                "required|exists:outgoing_resource_records,outgoing_resourceID",
            "outgoing_date" => "required|date",
            "outgoing_type" => "required|in:permanent,temporary",
            "quantity" => "nullable|integer|min:0",
            "unit_price" => "nullable|numeric|min:0",
            "description" => "required|string",
        ]);

        $record = \App\Models\OutgoingResourceRecord::where(
            "outgoing_resourceID",
            $validated["outgoing_resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        if ($record->is_returned) {
            return redirect()
                ->route("manage_outgoing_resources")
                ->with("error", "Returned records cannot be edited.");
        }

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $record->resourceID,
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $requiresPrice = (bool) $resource->requires_price;
        $newQty = $requiresQuantity
            ? (int) ($validated["quantity"] ?? 0)
            : null;
        $unitPrice = $requiresPrice
            ? (float) ($validated["unit_price"] ?? $resource->unit_price)
            : 0;
        $totalPrice = $requiresPrice
            ? ($requiresQuantity
                ? $unitPrice * $newQty
                : $unitPrice)
            : 0;

        if ($requiresQuantity) {
            $available = ($resource->quantity ?? 0) + ($record->quantity ?? 0);
            if ($newQty > $available) {
                return redirect()
                    ->route("manage_outgoing_resources")
                    ->with(
                        "error",
                        "Outgoing quantity exceeds available stock.",
                    );
            }
        }

        \DB::transaction(function () use (
            $record,
            $resource,
            $validated,
            $requiresQuantity,
            $newQty,
            $unitPrice,
            $totalPrice,
        ) {
            if ($requiresQuantity) {
                $restored =
                    ($resource->quantity ?? 0) + ($record->quantity ?? 0);
                $resource->update([
                    "quantity" => max(0, $restored - $newQty),
                ]);
            }

            $record->update([
                "outgoing_date" => $validated["outgoing_date"],
                "outgoing_type" => $validated["outgoing_type"],
                "quantity" => $newQty,
                "unit_price" => $unitPrice,
                "total_price" => $totalPrice,
                "description" => $validated["description"],
            ]);
        });

        return redirect()
            ->route("manage_outgoing_resources")
            ->with("success", "Outgoing resource updated successfully.");
    }

    public function deleteOutgoingResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "outgoing_resourceID" =>
                "required|exists:outgoing_resource_records,outgoing_resourceID",
        ]);

        $record = \App\Models\OutgoingResourceRecord::where(
            "outgoing_resourceID",
            $validated["outgoing_resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $record->resourceID,
        )
            ->where("schoolID", $schoolID)
            ->first();

        \DB::transaction(function () use ($record, $resource) {
            if (
                $resource &&
                $resource->requires_quantity &&
                !$record->is_returned
            ) {
                $resource->update([
                    "quantity" =>
                        ($resource->quantity ?? 0) + ($record->quantity ?? 0),
                ]);
            }
            $record->delete();
        });

        return redirect()
            ->route("manage_outgoing_resources")
            ->with("success", "Outgoing resource deleted successfully.");
    }

    public function returnOutgoingResource(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "outgoing_resourceID" =>
                "required|exists:outgoing_resource_records,outgoing_resourceID",
            "returned_at" => "required|date",
            "return_quantity" => "nullable|integer|min:0",
            "return_description" => "required|string",
        ]);

        $record = \App\Models\OutgoingResourceRecord::where(
            "outgoing_resourceID",
            $validated["outgoing_resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        if ($record->outgoing_type !== "temporary") {
            return redirect()
                ->route("manage_outgoing_resources")
                ->with(
                    "error",
                    "Only temporary outgoing resources can be returned.",
                );
        }

        if ($record->is_returned) {
            return redirect()
                ->route("manage_outgoing_resources")
                ->with("error", "This outgoing record was already returned.");
        }

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $record->resourceID,
        )
            ->where("schoolID", $schoolID)
            ->first();

        $requiresQuantity = $resource && $resource->requires_quantity;
        $recordQty = (int) ($record->quantity ?? 0);
        $alreadyReturned = (int) ($record->returned_quantity ?? 0);
        $remaining = max(0, $recordQty - $alreadyReturned);
        $returnQty = $requiresQuantity
            ? (int) ($validated["return_quantity"] ?? 0)
            : 0;

        if ($requiresQuantity) {
            if ($returnQty <= 0) {
                return redirect()
                    ->route("manage_outgoing_resources")
                    ->with(
                        "error",
                        "Return quantity must be greater than zero.",
                    );
            }
            if ($returnQty > $remaining) {
                return redirect()
                    ->route("manage_outgoing_resources")
                    ->with(
                        "error",
                        "Return quantity exceeds remaining quantity.",
                    );
            }
        }

        \DB::transaction(function () use (
            $record,
            $resource,
            $validated,
            $requiresQuantity,
            $returnQty,
            $remaining,
        ) {
            if ($resource && $resource->requires_quantity) {
                $resource->update([
                    "quantity" => ($resource->quantity ?? 0) + $returnQty,
                ]);
            }

            $newReturned = $requiresQuantity
                ? ($record->returned_quantity ?? 0) + $returnQty
                : 0;
            $record->update([
                "returned_at" => $validated["returned_at"],
                "returned_quantity" => $newReturned,
                "is_returned" => $requiresQuantity
                    ? $newReturned >= ($record->quantity ?? 0)
                    : true,
                "return_description" => $validated["return_description"],
            ]);
        });

        return redirect()
            ->route("manage_outgoing_resources")
            ->with("success", "Resource returned successfully.");
    }

    public function manageBuildingsInfrastructure()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view(
            "Admin.manage_buildings_infrastructure",
            compact("schoolID"),
        );
    }

    public function manageDesks()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.manage_desks", compact("schoolID"));
    }

    public function manageChairs()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.manage_chairs", compact("schoolID"));
    }

    public function manageChalk()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.manage_chalk", compact("schoolID"));
    }

    public function manageBooks()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.manage_books", compact("schoolID"));
    }

    public function manageTeachingAids()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.manage_teaching_aids", compact("schoolID"));
    }

    public function inventoryList()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        return view("Admin.inventory_list", compact("schoolID"));
    }

    public function manageDamagedLostItems()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $resources = \App\Models\SchoolResource::where("schoolID", $schoolID)
            ->orderBy("resource_name")
            ->get();

        $records = \App\Models\DamagedLostRecord::where("schoolID", $schoolID)
            ->orderBy("record_date", "desc")
            ->limit(50)
            ->get();

        $summary = \App\Models\DamagedLostRecord::where("schoolID", $schoolID)
            ->select(
                "resourceID",
                \DB::raw(
                    "SUM(CASE WHEN record_type = 'damaged' THEN quantity ELSE 0 END) as total_damaged",
                ),
                \DB::raw(
                    "SUM(CASE WHEN record_type = 'lost' THEN quantity ELSE 0 END) as total_lost",
                ),
                \DB::raw(
                    "SUM(CASE WHEN record_type = 'used_up' THEN quantity ELSE 0 END) as total_used_up",
                ),
            )
            ->groupBy("resourceID")
            ->get()
            ->keyBy("resourceID");

        return view(
            "Admin.manage_damaged_lost_items",
            compact("schoolID", "resources", "records", "summary"),
        );
    }

    public function manageTeacherFeedbackAdmin(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $tab = $request->query("tab");
        $section = $request->query("section", "view");
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab =
                $routeName === "admin.incidents" ? "incidents" : "suggestions";
        }

        $dateFrom = $request->query("date_from");
        $dateTo = $request->query("date_to");
        $teacherName = $request->query("teacher_name");

        $feedbackQuery = \App\Models\TeacherFeedback::where(
            "schoolID",
            $schoolID,
        );

        if ($dateFrom) {
            $feedbackQuery->whereDate("created_at", ">=", $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate("created_at", "<=", $dateTo);
        }

        $teacherMap = \App\Models\Teacher::where("schoolID", $schoolID)
            ->get()
            ->keyBy("id");

        if ($teacherName) {
            $teacherIds = $teacherMap
                ->filter(function ($teacher) use ($teacherName) {
                    $fullName = trim(
                        ($teacher->first_name ?? "") .
                            " " .
                            ($teacher->last_name ?? ""),
                    );
                    return stripos($fullName, $teacherName) !== false;
                })
                ->keys()
                ->toArray();

            if (count($teacherIds) === 0) {
                $feedback = collect();
            } else {
                $feedbackQuery->whereIn("teacherID", $teacherIds);
                $feedback = $feedbackQuery
                    ->orderBy("created_at", "desc")
                    ->get();
            }
        } else {
            $feedback = $feedbackQuery->orderBy("created_at", "desc")->get();
        }

        $feedback = $feedback->map(function ($item) use ($teacherMap) {
            $teacher = $teacherMap->get($item->teacherID);
            $name = $teacher
                ? trim(
                    ($teacher->first_name ?? "") .
                        " " .
                        ($teacher->last_name ?? ""),
                )
                : "Teacher";
            $item->teacher_name = $name;
            $item->person_name = $name;
            return $item;
        });

        $suggestions = $feedback->where("type", "suggestion")->values();
        $incidents = $feedback->where("type", "incident")->values();

        $suggestionStats = [
            "total" => $suggestions->count(),
            "pending" => $suggestions->where("status", "pending")->count(),
            "approved" => $suggestions->where("status", "approved")->count(),
            "rejected" => $suggestions->where("status", "rejected")->count(),
        ];

        $incidentStats = [
            "total" => $incidents->count(),
            "pending" => $incidents->where("status", "pending")->count(),
            "approved" => $incidents->where("status", "approved")->count(),
            "rejected" => $incidents->where("status", "rejected")->count(),
        ];

        $readType = $tab === "incidents" ? "incident" : "suggestion";
        \App\Models\TeacherFeedback::where("schoolID", $schoolID)
            ->where("type", $readType)
            ->update(["is_read_by_admin" => true]);

        return view("Admin.manage_teacher_feedback", [
            "activeTab" => $tab,
            "activeSection" => $section,
            "suggestions" => $suggestions,
            "incidents" => $incidents,
            "suggestionStats" => $suggestionStats,
            "incidentStats" => $incidentStats,
            "dateFrom" => $dateFrom,
            "dateTo" => $dateTo,
            "teacherName" => $teacherName,
            "adminFeedbackContext" => "teacher",
        ]);
    }

    public function manageStaffFeedbackAdmin(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $tab = $request->query("tab");
        $section = $request->query("section", "view");
        if (!$tab) {
            $routeName = optional($request->route())->getName();
            $tab =
                $routeName === "admin.staff.incidents"
                    ? "incidents"
                    : "suggestions";
        }

        $dateFrom = $request->query("date_from");
        $dateTo = $request->query("date_to");
        $staffName = $request->query("staff_name");

        $feedbackQuery = \App\Models\StaffFeedback::where(
            "schoolID",
            $schoolID,
        );

        if ($dateFrom) {
            $feedbackQuery->whereDate("created_at", ">=", $dateFrom);
        }
        if ($dateTo) {
            $feedbackQuery->whereDate("created_at", "<=", $dateTo);
        }

        $staffMap = \App\Models\OtherStaff::where("schoolID", $schoolID)
            ->get()
            ->keyBy("id");

        if ($staffName) {
            $staffIds = $staffMap
                ->filter(function ($staff) use ($staffName) {
                    $fullName = trim(
                        ($staff->first_name ?? "") .
                            " " .
                            ($staff->last_name ?? ""),
                    );
                    return stripos($fullName, $staffName) !== false;
                })
                ->keys()
                ->toArray();

            if (count($staffIds) === 0) {
                $feedback = collect();
            } else {
                $feedbackQuery->whereIn("staffID", $staffIds);
                $feedback = $feedbackQuery
                    ->orderBy("created_at", "desc")
                    ->get();
            }
        } else {
            $feedback = $feedbackQuery->orderBy("created_at", "desc")->get();
        }

        $feedback = $feedback->map(function ($item) use ($staffMap) {
            $staff = $staffMap->get($item->staffID);
            $name = $staff
                ? trim(
                    ($staff->first_name ?? "") .
                        " " .
                        ($staff->last_name ?? ""),
                )
                : "Staff";
            $item->person_name = $name;
            return $item;
        });

        $suggestions = $feedback->where("type", "suggestion")->values();
        $incidents = $feedback->where("type", "incident")->values();

        $suggestionStats = [
            "total" => $suggestions->count(),
            "pending" => $suggestions->where("status", "pending")->count(),
            "approved" => $suggestions->where("status", "approved")->count(),
            "rejected" => $suggestions->where("status", "rejected")->count(),
        ];

        $incidentStats = [
            "total" => $incidents->count(),
            "pending" => $incidents->where("status", "pending")->count(),
            "approved" => $incidents->where("status", "approved")->count(),
            "rejected" => $incidents->where("status", "rejected")->count(),
        ];

        $readType = $tab === "incidents" ? "incident" : "suggestion";
        \App\Models\StaffFeedback::where("schoolID", $schoolID)
            ->where("type", $readType)
            ->update(["is_read_by_admin" => true]);

        return view("Admin.manage_teacher_feedback", [
            "activeTab" => $tab,
            "activeSection" => $section,
            "suggestions" => $suggestions,
            "incidents" => $incidents,
            "suggestionStats" => $suggestionStats,
            "incidentStats" => $incidentStats,
            "dateFrom" => $dateFrom,
            "dateTo" => $dateTo,
            "staffName" => $staffName,
            "adminFeedbackContext" => "staff",
        ]);
    }

    public function approveStaffFeedback(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "feedbackID" => "required|integer",
            "admin_response" => "nullable|string",
            "response_due_date" => "nullable|date",
        ]);

        $feedback = \App\Models\StaffFeedback::where(
            "feedbackID",
            $validated["feedbackID"],
        )
            ->where("schoolID", $schoolID)
            ->first();

        if (!$feedback) {
            return response()->json(
                ["success" => false, "message" => "Feedback not found"],
                404,
            );
        }

        $feedback->status = "approved";
        $feedback->admin_response = $validated["admin_response"] ?? null;
        $feedback->response_due_date = $validated["response_due_date"] ?? null;
        $feedback->responded_at = now();
        $feedback->is_read_by_staff = false;
        $feedback->save();

        return response()->json([
            "success" => true,
            "message" => "Feedback approved successfully.",
        ]);
    }

    public function rejectStaffFeedback(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "feedbackID" => "required|integer",
            "admin_response" => "nullable|string",
        ]);

        $feedback = \App\Models\StaffFeedback::where(
            "feedbackID",
            $validated["feedbackID"],
        )
            ->where("schoolID", $schoolID)
            ->first();

        if (!$feedback) {
            return response()->json(
                ["success" => false, "message" => "Feedback not found"],
                404,
            );
        }

        $feedback->status = "rejected";
        $feedback->admin_response = $validated["admin_response"] ?? null;
        $feedback->responded_at = now();
        $feedback->is_read_by_staff = false;
        $feedback->save();

        return response()->json([
            "success" => true,
            "message" => "Feedback rejected successfully.",
        ]);
    }

    public function performanceDashboard()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $teachers = \App\Models\Teacher::where("schoolID", $schoolID)
            ->orderBy("first_name")
            ->get();
        $subjects = \App\Models\SchoolSubject::where("schoolID", $schoolID)
            ->orderBy("subject_name")
            ->get();
        $exams = \App\Models\Examination::where("schoolID", $schoolID)
            ->orderBy("created_at", "desc")
            ->limit(50)
            ->get();
        $classes = \App\Models\ClassModel::where("schoolID", $schoolID)
            ->orderBy("class_name")
            ->get();

        return view(
            "Admin.performance",
            compact("teachers", "subjects", "exams", "classes"),
        );
    }

    private function calculatePassFailCounts($results)
    {
        $pass = 0;
        $fail = 0;
        foreach ($results as $result) {
            $remark = strtolower(trim($result->remark ?? ""));
            if ($remark === "fail" || $remark === "failed") {
                $fail++;
                continue;
            }
            if ($result->marks !== null) {
                if ($result->marks >= 30) {
                    $pass++;
                } else {
                    $fail++;
                }
                continue;
            }
            $grade = strtoupper(trim($result->grade ?? ""));
            if (in_array($grade, ["A", "B", "C", "D"])) {
                $pass++;
            } else {
                $fail++;
            }
        }

        $total = $pass + $fail;
        $passRate = $total > 0 ? round(($pass / $total) * 100, 1) : 0;
        $failRate = $total > 0 ? round(($fail / $total) * 100, 1) : 0;

        $comment = "Needs improvement.";
        if ($passRate >= 75) {
            $comment = "Excellent performance. Keep going!";
        } elseif ($passRate >= 50) {
            $comment = "Good effort. Keep improving.";
        } elseif ($passRate >= 30) {
            $comment = "Fair performance. Work harder.";
        }

        return [
            "total" => $total,
            "pass_count" => $pass,
            "fail_count" => $fail,
            "pass_rate" => $passRate,
            "fail_rate" => $failRate,
            "comment" => $comment,
        ];
    }

    public function teacherTermPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "teacher_id" => "required|integer",
            "subject_id" => "required",
            "term" => "required|string",
            "year" => "required|integer",
        ]);

        $subjectId = $validated["subject_id"];
        $resultsQuery = \App\Models\Result::query()
            ->join(
                "class_subjects",
                "results.class_subjectID",
                "=",
                "class_subjects.class_subjectID",
            )
            ->join("examinations", "results.examID", "=", "examinations.examID")
            ->where("examinations.schoolID", $schoolID)
            ->where("examinations.term", $validated["term"])
            ->where("examinations.year", $validated["year"])
            ->where("class_subjects.teacherID", $validated["teacher_id"]);
        if ($subjectId !== "all") {
            $resultsQuery->where("class_subjects.subjectID", $subjectId);
        }
        $results = $resultsQuery
            ->select("results.marks", "results.remark", "results.grade")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $this->calculatePassFailCounts($results),
        ]);
    }

    public function teacherExamPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "teacher_id" => "required|integer",
            "subject_id" => "required",
            "exam_id" => "required|integer",
        ]);

        $subjectId = $validated["subject_id"];
        $resultsQuery = \App\Models\Result::query()
            ->join(
                "class_subjects",
                "results.class_subjectID",
                "=",
                "class_subjects.class_subjectID",
            )
            ->join("examinations", "results.examID", "=", "examinations.examID")
            ->where("examinations.schoolID", $schoolID)
            ->where("examinations.examID", $validated["exam_id"])
            ->where("class_subjects.teacherID", $validated["teacher_id"]);
        if ($subjectId !== "all") {
            $resultsQuery->where("class_subjects.subjectID", $subjectId);
        }
        $results = $resultsQuery
            ->select("results.marks", "results.remark", "results.grade")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $this->calculatePassFailCounts($results),
        ]);
    }

    public function teacherYearPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "teacher_id" => "required|integer",
            "subject_id" => "required",
            "year" => "required|integer",
        ]);

        $subjectId = $validated["subject_id"];
        $resultsQuery = \App\Models\Result::query()
            ->join(
                "class_subjects",
                "results.class_subjectID",
                "=",
                "class_subjects.class_subjectID",
            )
            ->join("examinations", "results.examID", "=", "examinations.examID")
            ->where("examinations.schoolID", $schoolID)
            ->where("examinations.year", $validated["year"])
            ->where("class_subjects.teacherID", $validated["teacher_id"]);
        if ($subjectId !== "all") {
            $resultsQuery->where("class_subjects.subjectID", $subjectId);
        }
        $results = $resultsQuery
            ->select("results.marks", "results.remark", "results.grade")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $this->calculatePassFailCounts($results),
        ]);
    }

    public function teacherSubjectsForPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "teacher_id" => "required|integer",
        ]);

        $subjects = \App\Models\ClassSubject::with("subject")
            ->where("teacherID", $validated["teacher_id"])
            ->where("status", "Active")
            ->get()
            ->map(function ($cs) {
                return [
                    "id" => $cs->subjectID,
                    "name" => $cs->subject->subject_name ?? "N/A",
                ];
            })
            ->unique("id")
            ->values();

        return response()->json([
            "success" => true,
            "data" => $subjects,
        ]);
    }

    public function classSubjectsForPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "class_id" => "required|integer",
        ]);

        $subjects = \App\Models\ClassSubject::with("subject")
            ->where("classID", $validated["class_id"])
            ->where("status", "Active")
            ->get()
            ->map(function ($cs) {
                return [
                    "id" => $cs->subjectID,
                    "name" => $cs->subject->subject_name ?? "N/A",
                ];
            })
            ->unique("id")
            ->values();

        return response()->json([
            "success" => true,
            "data" => $subjects,
        ]);
    }

    public function classSubclassesForPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "class_id" => "required|integer",
        ]);

        $subclasses = \App\Models\Subclass::where(
            "classID",
            $validated["class_id"],
        )
            ->orderBy("subclass_name")
            ->get()
            ->map(function ($subclass) {
                return [
                    "id" => $subclass->subclassID,
                    "name" =>
                        $subclass->display_name ?? $subclass->subclass_name,
                ];
            })
            ->values();

        return response()->json([
            "success" => true,
            "data" => $subclasses,
        ]);
    }

    public function classStudentsForPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "class_id" => "required|integer",
            "subclass_id" => "nullable|integer",
        ]);

        $studentsQuery = \App\Models\Student::query()
            ->join(
                "subclasses",
                "students.subclassID",
                "=",
                "subclasses.subclassID",
            )
            ->join("classes", "subclasses.classID", "=", "classes.classID")
            ->where("classes.schoolID", $schoolID)
            ->where("classes.classID", $validated["class_id"])
            ->where("students.status", "Active");

        if (!empty($validated["subclass_id"])) {
            $studentsQuery->where(
                "students.subclassID",
                $validated["subclass_id"],
            );
        }

        $students = $studentsQuery
            ->select(
                "students.studentID",
                "students.first_name",
                "students.middle_name",
                "students.last_name",
                "students.admission_number",
            )
            ->orderBy("students.first_name")
            ->get()
            ->map(function ($student) {
                $name = trim(
                    ($student->first_name ?? "") .
                        " " .
                        ($student->middle_name ?? "") .
                        " " .
                        ($student->last_name ?? ""),
                );
                $label = $name;
                if (!empty($student->admission_number)) {
                    $label .= " (" . $student->admission_number . ")";
                }
                return [
                    "id" => $student->studentID,
                    "name" => $label,
                ];
            });

        return response()->json([
            "success" => true,
            "data" => $students,
        ]);
    }

    public function studentTermPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "class_id" => "required|integer",
            "subclass_id" => "nullable|integer",
            "subject_id" => "required",
            "student_id" => "nullable|integer",
            "term" => "required|string",
            "year" => "required|integer",
        ]);

        $subjectId = $validated["subject_id"];
        $examIds = \App\Models\Examination::where("schoolID", $schoolID)
            ->where("term", $validated["term"])
            ->where("year", $validated["year"])
            ->where("approval_status", "Approved")
            ->pluck("examID");

        $resultsQuery = \App\Models\Result::query()
            ->join("students", "results.studentID", "=", "students.studentID")
            ->join(
                "subclasses",
                "students.subclassID",
                "=",
                "subclasses.subclassID",
            )
            ->join("classes", "subclasses.classID", "=", "classes.classID")
            ->leftJoin(
                "class_subjects",
                "results.class_subjectID",
                "=",
                "class_subjects.class_subjectID",
            )
            ->where("classes.schoolID", $schoolID)
            ->whereIn("results.examID", $examIds)
            ->where("results.status", "allowed")
            ->whereNotNull("results.marks")
            ->where("classes.classID", $validated["class_id"]);

        if (!empty($validated["subclass_id"])) {
            $resultsQuery->where(
                "subclasses.subclassID",
                $validated["subclass_id"],
            );
        }
        if (!empty($validated["student_id"])) {
            $resultsQuery->where("results.studentID", $validated["student_id"]);
        }
        if ($subjectId !== "all") {
            $resultsQuery->where("class_subjects.subjectID", $subjectId);
        }

        $results = $resultsQuery
            ->select("results.marks", "results.remark", "results.grade")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $this->calculatePassFailCounts($results),
        ]);
    }

    public function studentExamPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "class_id" => "required|integer",
            "subclass_id" => "nullable|integer",
            "subject_id" => "required",
            "student_id" => "nullable|integer",
            "exam_id" => "required|integer",
        ]);

        $subjectId = $validated["subject_id"];
        $examApproved = \App\Models\Examination::where("schoolID", $schoolID)
            ->where("examID", $validated["exam_id"])
            ->where("approval_status", "Approved")
            ->exists();
        if (!$examApproved) {
            return response()->json([
                "success" => true,
                "data" => $this->calculatePassFailCounts(collect()),
            ]);
        }

        $resultsQuery = \App\Models\Result::query()
            ->join("students", "results.studentID", "=", "students.studentID")
            ->join(
                "subclasses",
                "students.subclassID",
                "=",
                "subclasses.subclassID",
            )
            ->join("classes", "subclasses.classID", "=", "classes.classID")
            ->leftJoin(
                "class_subjects",
                "results.class_subjectID",
                "=",
                "class_subjects.class_subjectID",
            )
            ->where("classes.schoolID", $schoolID)
            ->where("results.examID", $validated["exam_id"])
            ->where("results.status", "allowed")
            ->whereNotNull("results.marks")
            ->where("classes.classID", $validated["class_id"]);

        if (!empty($validated["subclass_id"])) {
            $resultsQuery->where(
                "subclasses.subclassID",
                $validated["subclass_id"],
            );
        }
        if (!empty($validated["student_id"])) {
            $resultsQuery->where("results.studentID", $validated["student_id"]);
        }
        if ($subjectId !== "all") {
            $resultsQuery->where("class_subjects.subjectID", $subjectId);
        }

        $results = $resultsQuery
            ->select("results.marks", "results.remark", "results.grade")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $this->calculatePassFailCounts($results),
        ]);
    }

    public function studentYearPerformance(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "class_id" => "required|integer",
            "subclass_id" => "nullable|integer",
            "subject_id" => "required",
            "student_id" => "nullable|integer",
            "year" => "required|integer",
        ]);

        $subjectId = $validated["subject_id"];
        $examIds = \App\Models\Examination::where("schoolID", $schoolID)
            ->where("year", $validated["year"])
            ->where("approval_status", "Approved")
            ->pluck("examID");

        $resultsQuery = \App\Models\Result::query()
            ->join("students", "results.studentID", "=", "students.studentID")
            ->join(
                "subclasses",
                "students.subclassID",
                "=",
                "subclasses.subclassID",
            )
            ->join("classes", "subclasses.classID", "=", "classes.classID")
            ->leftJoin(
                "class_subjects",
                "results.class_subjectID",
                "=",
                "class_subjects.class_subjectID",
            )
            ->where("classes.schoolID", $schoolID)
            ->whereIn("results.examID", $examIds)
            ->where("results.status", "allowed")
            ->whereNotNull("results.marks")
            ->where("classes.classID", $validated["class_id"]);

        if (!empty($validated["subclass_id"])) {
            $resultsQuery->where(
                "subclasses.subclassID",
                $validated["subclass_id"],
            );
        }
        if (!empty($validated["student_id"])) {
            $resultsQuery->where("results.studentID", $validated["student_id"]);
        }
        if ($subjectId !== "all") {
            $resultsQuery->where("class_subjects.subjectID", $subjectId);
        }

        $results = $resultsQuery
            ->select("results.marks", "results.remark", "results.grade")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $this->calculatePassFailCounts($results),
        ]);
    }

    public function approveTeacherFeedback(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(
                    ["success" => false, "message" => "Unauthorized access"],
                    403,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "feedbackID" => "required|exists:teacher_feedbacks,feedbackID",
            "admin_response" => "required|string",
            "response_due_date" => "required|date",
        ]);

        $feedback = \App\Models\TeacherFeedback::where(
            "feedbackID",
            $validated["feedbackID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $feedback->update([
            "status" => "approved",
            "admin_response" => $validated["admin_response"],
            "response_due_date" => $validated["response_due_date"],
            "responded_at" => now(),
            "is_read_by_teacher" => false,
        ]);

        $teacher = \App\Models\Teacher::where(
            "id",
            $feedback->teacherID,
        )->first();
        if ($teacher && $teacher->phone_number) {
            $typeLabel =
                $feedback->type === "incident" ? "Incident" : "Suggestion";
            $message = "{$typeLabel} approved. Planned date: {$validated["response_due_date"]}. {$validated["admin_response"]}";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($teacher->phone_number, $message);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Feedback approved successfully.",
            ]);
        }
        return redirect()
            ->back()
            ->with("success", "Feedback approved successfully.");
    }

    public function rejectTeacherFeedback(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(
                    ["success" => false, "message" => "Unauthorized access"],
                    403,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "feedbackID" => "required|exists:teacher_feedbacks,feedbackID",
            "admin_response" => "required|string",
        ]);

        $feedback = \App\Models\TeacherFeedback::where(
            "feedbackID",
            $validated["feedbackID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $feedback->update([
            "status" => "rejected",
            "admin_response" => $validated["admin_response"],
            "responded_at" => now(),
            "is_read_by_teacher" => false,
        ]);

        $teacher = \App\Models\Teacher::where(
            "id",
            $feedback->teacherID,
        )->first();
        if ($teacher && $teacher->phone_number) {
            $typeLabel =
                $feedback->type === "incident" ? "Incident" : "Suggestion";
            $message = "{$typeLabel} rejected. Reason: {$validated["admin_response"]}";
            $smsService = new \App\Services\SmsService();
            $smsService->sendSms($teacher->phone_number, $message);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => "Feedback rejected successfully.",
            ]);
        }
        return redirect()
            ->back()
            ->with("success", "Feedback rejected successfully.");
    }

    public function managePermissionsAdmin(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $tab = $request->get("tab", "teacher");
        $dateFrom = $request->get("date_from");
        $dateTo = $request->get("date_to");
        $filterType = $request->get("filter_type", "all");
        $filterMonth = $request->get("filter_month");
        $filterMonthYear = $request->get("filter_month_year", date("Y"));
        $filterYear = $request->input("filter_year", date("Y"));
        $search = trim($request->get("search", ""));

        $query = PermissionRequest::with([
            "teacher",
            "student",
            "parent",
            "staff",
        ])
            ->where("schoolID", $schoolID)
            ->where(
                "requester_type",
                $tab === "student"
                    ? "student"
                    : ($tab === "staff"
                        ? "staff"
                        : "teacher"),
            );

        if ($filterType === "range") {
            if ($dateFrom) {
                $query->whereDate("start_date", ">=", $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate("start_date", "<=", $dateTo);
            }
        } elseif ($filterType === "month") {
            if ($filterMonth) {
                $query
                    ->whereMonth("start_date", $filterMonth)
                    ->whereYear("start_date", $filterMonthYear);
            }
        } elseif ($filterType === "year") {
            if ($filterYear) {
                $query->whereYear("start_date", $filterYear);
            }
        }

        if ($search !== "") {
            if ($tab === "student") {
                $query->whereHas("student", function ($q) use ($search) {
                    $q->where("first_name", "like", "%{$search}%")
                        ->orWhere("last_name", "like", "%{$search}%")
                        ->orWhere("admission_number", "like", "%{$search}%");
                });
            } elseif ($tab === "staff") {
                $query->whereHas("staff", function ($q) use ($search) {
                    $q->where("first_name", "like", "%{$search}%")
                        ->orWhere("last_name", "like", "%{$search}%")
                        ->orWhere("employee_number", "like", "%{$search}%");
                });
            } else {
                $query->whereHas("teacher", function ($q) use ($search) {
                    $q->where("first_name", "like", "%{$search}%")
                        ->orWhere("last_name", "like", "%{$search}%")
                        ->orWhere("employee_number", "like", "%{$search}%");
                });
            }
        }

        // Check if Admin is assigned in the approval chain
        $chain = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where(
                "permission_type",
                $tab === "student"
                    ? "student"
                    : ($tab === "staff"
                        ? "staff"
                        : "teacher"),
            )
            ->where("is_admin", true)
            ->orderBy("approval_order")
            ->first();

        $adminInChain = $chain ? true : false;
        $adminApprovalOrder = $chain ? $chain->approval_order : null;

        $permissions = $query->orderBy("created_at", "desc")->get();

        $pendingCount = PermissionRequest::where("schoolID", $schoolID)
            ->where("status", "pending")
            ->count();
        $approvedCount = PermissionRequest::where("schoolID", $schoolID)
            ->where("status", "approved")
            ->count();
        $rejectedCount = PermissionRequest::where("schoolID", $schoolID)
            ->where("status", "rejected")
            ->count();

        $tabCounts = [
            "teacher" => PermissionRequest::where("schoolID", $schoolID)
                ->where("requester_type", "teacher")
                ->where("status", "pending")
                ->count(),
            "student" => PermissionRequest::where("schoolID", $schoolID)
                ->where("requester_type", "student")
                ->where("status", "pending")
                ->count(),
            "staff" => PermissionRequest::where("schoolID", $schoolID)
                ->where("requester_type", "staff")
                ->where("status", "pending")
                ->count(),
        ];

        PermissionRequest::where("schoolID", $schoolID)->update([
            "is_read_by_admin" => true,
        ]);

        $teacherResultPermission = \App\Models\TeacherResultPermission::where(
            "schoolID",
            $schoolID,
        )->first();
        $resultButtonStatus = $teacherResultPermission
            ? $teacherResultPermission->result_button_status
            : true;

        if ($request->ajax() || $request->expectsJson()) {
            $data = $permissions->map(function ($permission) use ($tab) {
                $requesterName = "N/A";
                if ($tab === "student" && $permission->student) {
                    $requesterName = trim(
                        ($permission->student->first_name ?? "") .
                            " " .
                            ($permission->student->last_name ?? ""),
                    );
                }
                if ($tab === "staff" && $permission->staff) {
                    $requesterName = trim(
                        ($permission->staff->first_name ?? "") .
                            " " .
                            ($permission->staff->last_name ?? ""),
                    );
                }
                if ($tab === "teacher" && $permission->teacher) {
                    $requesterName = trim(
                        ($permission->teacher->first_name ?? "") .
                            " " .
                            ($permission->teacher->last_name ?? ""),
                    );
                }
                return [
                    "permissionID" => $permission->permissionID,
                    "requesterName" => $requesterName,
                    "timeMode" => $permission->time_mode,
                    "daysCount" => $permission->days_count,
                    "startDate" => $permission->start_date
                        ? $permission->start_date->format("Y-m-d")
                        : null,
                    "endDate" => $permission->end_date
                        ? $permission->end_date->format("Y-m-d")
                        : null,
                    "startTime" => $permission->start_time,
                    "endTime" => $permission->end_time,
                    "reasonType" => $permission->reason_type,
                    "status" => $permission->status,
                    "attachment" => $permission->attachment_path
                        ? route(
                            "admin.permissions.attachment",
                            $permission->permissionID,
                        )
                        : null,
                ];
            });

            return response()->json([
                "success" => true,
                "data" => $data,
                "pendingCount" => $pendingCount,
                "approvedCount" => $approvedCount,
                "rejectedCount" => $rejectedCount,
                "roles" => DB::table("roles")
                    ->where("schoolID", $schoolID)
                    ->get(["id", "name"]),
                "positions" => DB::table("staff_professions")
                    ->where("schoolID", $schoolID)
                    ->get(["id", "name"]),
            ]);
        }

        return view("Admin.manage_permissions", [
            "activeTab" => $tab,
            "permissions" => $permissions,
            "dateFrom" => $dateFrom,
            "dateTo" => $dateTo,
            "filterType" => $filterType,
            "filterMonth" => $filterMonth,
            "filterMonthYear" => $filterMonthYear,
            "filterYear" => $filterYear,
            "search" => $search,
            "pendingCount" => $pendingCount,
            "approvedCount" => $approvedCount,
            "rejectedCount" => $rejectedCount,
            "tabCounts" => $tabCounts,
            "resultButtonStatus" => $resultButtonStatus,
            "adminInChain" => $adminInChain,
            "adminApprovalOrder" => $adminApprovalOrder,
            "roles" => DB::table("roles")
                ->where("schoolID", $schoolID)
                ->get(["id", "name"]),
            "positions" => DB::table("staff_professions")
                ->where("schoolID", $schoolID)
                ->get(["id", "name"]),
        ]);
    }

    public function viewPermissionAttachment($permissionID)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin" || !$schoolID) {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $permission = PermissionRequest::where("schoolID", $schoolID)
            ->where("permissionID", $permissionID)
            ->firstOrFail();

        if (!$permission->attachment_path) {
            abort(404);
        }

        $path = $permission->attachment_path;
        if (!Storage::disk("public")->exists($path)) {
            abort(404);
        }

        return Storage::disk("public")->response($path);
    }

    public function approvePermission(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        $userID = Session::get("userID") ?: Session::get("id");

        if (!$user || !in_array($user, ["Admin", "Teacher", "Staff"])) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(
                    ["success" => false, "message" => "Unauthorized access"],
                    403,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        try {
            $validated = $request->validate([
                "permissionID" => "required",
                "admin_response" => "required|string",
                "admin_attachment" =>
                    "nullable|file|mimes:pdf,jpg,jpeg,png|max:2048",
                "is_student_permission" => "nullable|in:1,0,true,false",
            ]);

            $isStudentPermission = in_array(
                $request->input("is_student_permission"),
                [1, "1", true, "true"],
                true,
            );

            if ($isStudentPermission) {
                return $this->approveStudentPermissionRequest(
                    $request,
                    $validated,
                    $schoolID,
                    $user,
                    $userID,
                );
            }

            return $this->approveTeacherStaffPermissionRequest(
                $request,
                $validated,
                $schoolID,
                $user,
                $userID,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("approvePermission failed: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);

            $message = \App\Support\SafeErrorMessage::from(
                $e,
                "Failed to approve permission. Please try again.",
            );

            if (
                str_contains(strtolower($e->getMessage()), "permission denied")
                || str_contains(strtolower($e->getMessage()), "failed to open stream")
            ) {
                $message =
                    "Server cannot save the attachment (storage permission). Approve without a file, or fix storage/app/public permissions.";
            }

            return response()->json(
                ["success" => false, "message" => $message],
                500,
            );
        }
    }

    private function approveStudentPermissionRequest(
        Request $request,
        array $validated,
        $schoolID,
        $user,
        $userID,
    ) {
        $permission = DB::table("student_permissions")
            ->where("schoolID", $schoolID)
            ->where("id", $validated["permissionID"])
            ->first();

        if (!$permission) {
            return response()->json(
                ["success" => false, "message" => "Permission not found"],
                404,
            );
        }

        $chain = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", "student")
            ->orderBy("approval_order")
            ->get();

        $currentOrder = (int) ($permission->current_approval_order ?: 1);

        if ($user === "Admin" && $chain->isNotEmpty()) {
            $adminStep = $chain->where("is_admin", true)->first();
            if ($adminStep && (int) $adminStep->approval_order !== $currentOrder) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "This permission is not at your approval step in the chain.",
                    ],
                    403,
                );
            }
        }

        $isFinalApproval = true;
        $nextOrder = $currentOrder + 1;

        if ($chain->isNotEmpty()) {
            $maxOrder = (int) $chain->max("approval_order");
            if ($currentOrder < $maxOrder) {
                $isFinalApproval = false;
            }
        }

        $attachmentPath = $permission->attachment;
        if ($request->hasFile("admin_attachment")) {
            try {
                $attachmentPath = $request
                    ->file("admin_attachment")
                    ->store("permission_admin_attachments", "public");
            } catch (\Throwable $e) {
                Log::warning("Permission attachment store failed: " . $e->getMessage());
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Could not save attachment (storage permission). Try again without a file.",
                    ],
                    500,
                );
            }
        }

        $currentApprover = $this->getApprovalChainStepName(
            $schoolID,
            "student",
            $currentOrder,
        );
        $nextApprover = $this->getApprovalChainStepName(
            $schoolID,
            "student",
            $nextOrder,
        );

        $this->insertPermissionApprovalLog([
            "permissionID" => $permission->id,
            "permission_type" => "student",
            "approver_id" => $userID,
            "approver_role" => $user,
            "approval_order" => $currentOrder,
            "status" => "approved",
            "admin_response" => $validated["admin_response"],
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        if ($isFinalApproval) {
            DB::table("student_permissions")
                ->where("id", $permission->id)
                ->update([
                    "status" => "approved",
                    "reason_description" => $validated["admin_response"],
                    "updated_at" => now(),
                    "is_chain_completed" => true,
                    "attachment" => $attachmentPath,
                ]);

            $this->safeNotify(function () use (
                $permission,
                $currentApprover,
                $validated,
            ) {
                $this->notifyStudentPermissionRequester(
                    $permission,
                    "approved",
                    $currentApprover,
                    $validated["admin_response"],
                );
                $this->notifyStudentPermissionParent(
                    $permission,
                    "approved",
                    $currentApprover,
                    $validated["admin_response"],
                );
            });
        } else {
            DB::table("student_permissions")
                ->where("id", $permission->id)
                ->update([
                    "current_approval_order" => $nextOrder,
                    "updated_at" => now(),
                ]);

            $permission->current_approval_order = $nextOrder;

            $this->safeNotify(function () use (
                $permission,
                $currentApprover,
                $validated,
                $nextApprover,
            ) {
                $this->notifyStudentPermissionRequester(
                    $permission,
                    "intermediary_approved",
                    $currentApprover,
                    $validated["admin_response"],
                    $nextApprover,
                );
                $this->notifyStudentPermissionParent(
                    $permission,
                    "intermediary_approved",
                    $currentApprover,
                    $validated["admin_response"],
                    $nextApprover,
                );
                $this->notifyNextApprover($permission, true);
            });
        }

        return response()->json([
            "success" => true,
            "message" => $isFinalApproval
                ? "Student permission approved successfully."
                : "Student permission moved to next approval stage.",
        ]);
    }

    private function approveTeacherStaffPermissionRequest(
        Request $request,
        array $validated,
        $schoolID,
        $user,
        $userID,
    ) {
        $permission = PermissionRequest::where("schoolID", $schoolID)
            ->where("permissionID", $validated["permissionID"])
            ->first();

        if (!$permission) {
            return response()->json(
                ["success" => false, "message" => "Permission not found"],
                404,
            );
        }

        $chain = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", $permission->requester_type)
            ->orderBy("approval_order")
            ->get();

        $currentOrder = (int) ($permission->current_approval_order ?: 1);

        if ($user === "Admin" && $chain->isNotEmpty()) {
            $adminStep = $chain->where("is_admin", true)->first();
            if ($adminStep && (int) $adminStep->approval_order !== $currentOrder) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "This permission is not at your approval step in the chain.",
                    ],
                    403,
                );
            }
        }

        $isFinalApproval = true;
        $nextOrder = $currentOrder + 1;

        if ($chain->isNotEmpty()) {
            $maxOrder = (int) $chain->max("approval_order");
            if ($currentOrder < $maxOrder) {
                $isFinalApproval = false;
            }
        }

        $attachmentPath = $permission->admin_attachment_path;
        if ($request->hasFile("admin_attachment")) {
            try {
                $attachmentPath = $request
                    ->file("admin_attachment")
                    ->store("permission_admin_attachments", "public");
            } catch (\Throwable $e) {
                Log::warning("Permission attachment store failed: " . $e->getMessage());
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Could not save attachment (storage permission). Try again without a file.",
                    ],
                    500,
                );
            }
        }

        $currentApprover =
            DB::table("permission_approval_chains")
                ->where("schoolID", $schoolID)
                ->where("permission_type", $permission->requester_type)
                ->where("approval_order", $currentOrder)
                ->value("role_name") ?? "System Admin";

        if ($isFinalApproval) {
            DB::table("permission_requests")
                ->where("permissionID", $permission->permissionID)
                ->update([
                    "status" => "approved",
                    "admin_response" => $validated["admin_response"],
                    "reviewed_at" => now(),
                    "is_read_by_requester" => false,
                    "is_chain_completed" => true,
                    "admin_attachment_path" => $attachmentPath,
                ]);

            $this->safeNotify(function () use (
                $permission,
                $currentApprover,
                $validated,
            ) {
                $this->notifyRequester(
                    $permission,
                    "approved",
                    $currentApprover,
                    $validated["admin_response"],
                );
            });
        } else {
            DB::table("permission_requests")
                ->where("permissionID", $permission->permissionID)
                ->update([
                    "current_approval_order" => $nextOrder,
                    "admin_response" => $validated["admin_response"],
                ]);

            $permission = PermissionRequest::find($permission->permissionID);

            $nextApprover =
                DB::table("permission_approval_chains")
                    ->where("schoolID", $schoolID)
                    ->where("permission_type", $permission->requester_type)
                    ->where("approval_order", $nextOrder)
                    ->value("role_name") ?? "System Admin";

            $this->safeNotify(function () use (
                $permission,
                $currentApprover,
                $validated,
                $nextApprover,
            ) {
                $this->notifyRequester(
                    $permission,
                    "intermediary_approved",
                    $currentApprover,
                    $validated["admin_response"],
                    $nextApprover,
                );
                $this->notifyNextApprover($permission);
            });
        }

        $this->insertPermissionApprovalLog([
            "permissionID" => $permission->permissionID,
            "permission_type" => $permission->requester_type ?? "teacher",
            "approver_id" => $userID,
            "approver_role" => $user,
            "approval_order" => $currentOrder,
            "status" => "approved",
            "admin_response" => $validated["admin_response"],
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                "success" => true,
                "message" => $isFinalApproval
                    ? "Permission approved successfully."
                    : "Permission moved to next approval stage.",
            ]);
        }

        return redirect()
            ->back()
            ->with("success", "Permission approved successfully.");
    }

    private function insertPermissionApprovalLog(array $data): void
    {
        if (
            !Schema::hasColumn("permission_approval_logs", "permission_type")
            && array_key_exists("permission_type", $data)
        ) {
            unset($data["permission_type"]);
        }

        DB::table("permission_approval_logs")->insert($data);
    }

    private function safeNotify(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning("Permission notification failed: " . $e->getMessage());
        }
    }

    private function notifyRequester(
        $permission,
        $status,
        $approverRole = "System Admin",
        $comment = "",
        $nextApprover = "",
    ) {
        $smsService = new SmsService();
        $name = "N/A";
        $phone = null;

        if ($permission->requester_type === "teacher") {
            $user = Teacher::find($permission->teacherID);
            if ($user) {
                $name = trim($user->first_name . " " . $user->last_name);
                $phone = $user->phone_number;
            }
        } elseif ($permission->requester_type === "staff") {
            $user = OtherStaff::find($permission->staffID);
            if ($user) {
                $name = trim($user->first_name . " " . $user->last_name);
                $phone = $user->phone_number;
            }
        } else {
            $user = ParentModel::where(
                "parentID",
                $permission->parentID,
            )->first();
            if ($user) {
                $name = $user->name;
                $phone = $user->phone;
            }
        }

        if (!$phone) {
            return;
        }

        $message = "";
        if ($status === "approved") {
            $message = "Ndugu {$name}, ruksa yako imekubaliwa kikamilifu. Sasa unaweza kwenda.";
        } elseif ($status === "rejected") {
            $message = "Ndugu {$name}, ruksa yako imekataliwa na {$approverRole}. Sababu: {$comment}";
        } elseif ($status === "intermediary_approved") {
            $message = "Ndugu {$name}, ruksa yako imekubaliwa na {$approverRole}. Maoni: {$comment}. Sasa inasubiri kwa {$nextApprover}.";
        } else {
            $message =
                "Ndugu {$name}, maombi yako ya ruksa yamepata mabadiliko. Status: " .
                ucfirst($status);
        }

        $smsService->sendSms($phone, $message);
    }

    private function getApprovalChainStepName(
        $schoolID,
        string $permissionType,
        $approvalOrder,
    ): string {
        if (!$schoolID || !$approvalOrder) {
            return "System Admin";
        }

        $step = DB::table("permission_approval_chains")
            ->leftJoin(
                "roles",
                "permission_approval_chains.role_id",
                "=",
                "roles.id",
            )
            ->leftJoin(
                "staff_professions",
                "permission_approval_chains.profession_id",
                "=",
                "staff_professions.id",
            )
            ->where("permission_approval_chains.schoolID", $schoolID)
            ->where(
                "permission_approval_chains.permission_type",
                $permissionType,
            )
            ->where("permission_approval_chains.approval_order", $approvalOrder)
            ->select(
                "permission_approval_chains.role_name",
                "permission_approval_chains.is_admin",
                "roles.name as db_role_name",
                "staff_professions.name as profession_name",
            )
            ->first();

        if (!$step) {
            return "System Admin";
        }

        return $step->role_name ?:
            $step->db_role_name ?:
            $step->profession_name ?:
            ($step->is_admin
                ? "Admin (School Administrator)"
                : "Approval Stage");
    }

    private function notifyStudentPermissionRequester(
        $permission,
        string $status,
        string $approverRole = "System Admin",
        string $comment = "",
        string $nextApprover = "",
    ): void {
        $phone = null;
        $name = "Requester";

        if (($permission->requester_type ?? null) === "teacher") {
            $teacher = Teacher::find($permission->requester_id);
            if ($teacher) {
                $name = trim(
                    ($teacher->first_name ?? "") .
                        " " .
                        ($teacher->last_name ?? ""),
                );
                $phone = $teacher->phone_number;
            }
        } elseif (($permission->requester_type ?? null) === "staff") {
            $staff = OtherStaff::find($permission->requester_id);
            if ($staff) {
                $name = trim(
                    ($staff->first_name ?? "") .
                        " " .
                        ($staff->last_name ?? ""),
                );
                $phone = $staff->phone_number;
            }
        }

        if (!$phone) {
            return;
        }

        $student = Student::with("parent")->find($permission->student_id);
        $studentName = $student
            ? trim(
                ($student->first_name ?? "") .
                    " " .
                    ($student->middle_name ?? "") .
                    " " .
                    ($student->last_name ?? ""),
            )
            : "mwanafunzi";

        $message = "";
        if ($status === "approved") {
            $message = "Ndugu {$name}, ruhusa ya mwanafunzi {$studentName} imekubaliwa kikamilifu na {$approverRole}. Maoni: {$comment}";
        } elseif ($status === "rejected") {
            $message = "Ndugu {$name}, ruhusa ya mwanafunzi {$studentName} imekataliwa na {$approverRole}. Sababu: {$comment}";
        } elseif ($status === "intermediary_approved") {
            $message = "Ndugu {$name}, ruhusa ya mwanafunzi {$studentName} imekubaliwa na {$approverRole}. Inasubiri hatua ya {$nextApprover}. Maoni: {$comment}";
        } else {
            $message = "Ndugu {$name}, maombi ya ruhusa ya mwanafunzi {$studentName} yamepata mabadiliko.";
        }

        (new SmsService())->sendSms($phone, $message);
    }

    private function notifyStudentPermissionParent(
        $permission,
        string $status,
        string $approverRole = "System Admin",
        string $comment = "",
        string $nextApprover = "",
    ): void {
        $student = Student::with("parent")->find($permission->student_id);
        $parentPhone = $student?->parent?->phone;
        if (!$student || !$parentPhone) {
            return;
        }

        $studentName = trim(
            ($student->first_name ?? "") .
                " " .
                ($student->middle_name ?? "") .
                " " .
                ($student->last_name ?? ""),
        );

        $message = "";
        if ($status === "approved") {
            $message = "Ruhusa ya mwanafunzi {$studentName} imekubaliwa kikamilifu na {$approverRole}. Maoni: {$comment}";
        } elseif ($status === "rejected") {
            $message = "Ruhusa ya mwanafunzi {$studentName} imekataliwa na {$approverRole}. Sababu: {$comment}";
        } elseif ($status === "intermediary_approved") {
            $message = "Ruhusa ya mwanafunzi {$studentName} imekubaliwa na {$approverRole}. Inasubiri hatua ya {$nextApprover}. Maoni: {$comment}";
        } else {
            $message = "Ruhusa ya mwanafunzi {$studentName} imepata mabadiliko kwenye mfumo.";
        }

        (new SmsService())->sendSms($parentPhone, $message);
    }

    public function notifyNextApprover(
        $permission,
        $isStudentPermission = false,
    ) {
        \Log::info("notifyNextApprover called", [
            "isStudentPermission" => $isStudentPermission,
            "permission_id" =>
                $permission->id ?? ($permission->permissionID ?? "unknown"),
            "permission_type" => $permission->requester_type ?? "student",
        ]);

        if (!$permission) {
            \Log::error("notifyNextApprover: Permission is null", [
                "isStudentPermission" => $isStudentPermission,
            ]);
            return;
        }

        $schoolID = null;
        $order = null;
        $permissionType = null;

        if ($isStudentPermission) {
            $schoolID = $permission->schoolID ?? null;
            $order = $permission->current_approval_order ?? null;
            $permissionType = "student";
        } else {
            $schoolID = $permission->schoolID ?? null;
            $order = $permission->current_approval_order ?? null;
            $permissionType = $permission->requester_type ?? null;
        }

        \Log::info("notifyNextApprover values", [
            "schoolID" => $schoolID,
            "order" => $order,
            "permissionType" => $permissionType,
        ]);

        if (!$schoolID) {
            \Log::error("notifyNextApprover: schoolID is null", [
                "isStudentPermission" => $isStudentPermission,
                "permission" => $permission,
                "schoolID" => $schoolID,
            ]);
            return;
        }

        // Get requester name
        $requesterName = "N/A";
        if ($isStudentPermission) {
            $st = \App\Models\Student::find($permission->student_id);
            $requesterName = $st
                ? trim($st->first_name . " " . $st->last_name)
                : "Student";
        } elseif ($permission->requester_type === "teacher") {
            $t = \App\Models\Teacher::find($permission->teacherID);
            $requesterName = $t
                ? trim($t->first_name . " " . $t->last_name)
                : "Teacher";
        } elseif ($permission->requester_type === "staff") {
            $s = \App\Models\OtherStaff::find($permission->staffID);
            $requesterName = $s
                ? trim($s->first_name . " " . $s->last_name)
                : "Staff";
        } elseif ($permission->requester_type === "student") {
            $st = \App\Models\Student::find($permission->studentID);
            $requesterName = $st
                ? trim($st->first_name . " " . $st->last_name)
                : "Student";
        }

        // Find the approver at this order
        $approver = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", $permissionType)
            ->where("approval_order", $order)
            ->first();

        \Log::info("notifyNextApprover approver found", [
            "approver" => $approver,
            "role_id" => $approver->role_id ?? null,
            "profession_id" => $approver->profession_id ?? null,
        ]);

        if (!$approver) {
            \Log::warning("notifyNextApprover: No approver found for order", [
                "order" => $order,
                "permissionType" => $permissionType,
            ]);
            // No more steps in chain, notify Admin
            $school = DB::table("schools")
                ->where("schoolID", $schoolID)
                ->first();
            if ($school && $school->phone) {
                (new SmsService())->sendSms(
                    $school->phone,
                    "Final approval required for a permission request from {$requesterName}. Please login to ShuleXpert to review.",
                );
                \Log::info("notifyNextApprover: SMS sent to school admin", [
                    "phone" => $school->phone,
                ]);
            } else {
                \Log::warning(
                    "notifyNextApprover: School admin phone not found",
                );
            }
            return;
        }

        $phones = [];
        if ($approver->role_id) {
            $teacherPhones = [];
            $legacyTeacherPhones = [];
            $hasTeacherId = Schema::hasColumn("role_user", "teacher_id");

            if ($hasTeacherId) {
                $teacherPhones = DB::table("role_user")
                    ->join("teachers", "role_user.teacher_id", "=", "teachers.id")
                    ->where("role_user.role_id", $approver->role_id)
                    ->where("teachers.schoolID", $schoolID)
                    ->whereNotNull("teachers.phone_number")
                    ->pluck("teachers.phone_number")
                    ->toArray();
            }

            $legacyQuery = DB::table("role_user")
                ->join("users", "role_user.user_id", "=", "users.id")
                ->join("teachers", function ($join) {
                    $join
                        ->on("users.email", "=", "teachers.email")
                        ->orOn("users.name", "=", "teachers.employee_number");
                })
                ->where("role_user.role_id", $approver->role_id)
                ->where("teachers.schoolID", $schoolID)
                ->whereNotNull("teachers.phone_number");

            if ($hasTeacherId) {
                $legacyQuery->whereNull("role_user.teacher_id");
            }

            $legacyTeacherPhones = $legacyQuery
                ->pluck("teachers.phone_number")
                ->toArray();

            $staffQuery = DB::table("role_user")
                ->join("users", "role_user.user_id", "=", "users.id")
                ->join("other_staff", function ($join) {
                    $join
                        ->on("users.email", "=", "other_staff.email")
                        ->orOn(
                            "users.name",
                            "=",
                            "other_staff.employee_number",
                        );
                })
                ->where("role_user.role_id", $approver->role_id)
                ->where("other_staff.schoolID", $schoolID)
                ->whereNotNull("other_staff.phone_number");

            if ($hasTeacherId) {
                $staffQuery->whereNull("role_user.teacher_id");
            }

            $staffPhones = $staffQuery
                ->pluck("other_staff.phone_number")
                ->toArray();

            $phones = array_unique(
                array_merge($teacherPhones, $legacyTeacherPhones, $staffPhones),
            );
            \Log::info("notifyNextApprover: Phones from role_id", [
                "teacherPhones" => $teacherPhones,
                "legacyTeacherPhones" => $legacyTeacherPhones,
                "staffPhones" => $staffPhones,
                "merged" => $phones,
            ]);
        } elseif ($approver->profession_id) {
            // Find staff with this position
            $phones = DB::table("other_staff")
                ->where("profession_id", $approver->profession_id)
                ->where("schoolID", $schoolID)
                ->whereNotNull("phone_number")
                ->pluck("phone_number")
                ->toArray();
            \Log::info("notifyNextApprover: Phones from profession_id", [
                "phones" => $phones,
            ]);
        }

        if (!empty($phones)) {
            $smsService = new SmsService();
            $msg = "New permission request pending your approval from {$requesterName}. Please login to ShuleXpert to review.";
            foreach ($phones as $phone) {
                \Log::info("notifyNextApprover: Sending SMS", [
                    "phone" => $phone,
                ]);
                $smsService->sendSms($phone, $msg);
            }
            \Log::info("notifyNextApprover: SMS sent successfully", [
                "phones_count" => count($phones),
            ]);
        } else {
            \Log::warning("notifyNextApprover: No phones found to notify");
        }
    }

    public function rejectPermission(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        $userID = Session::get("userID") ?: Session::get("id");

        if (!$user || !in_array($user, ["Admin", "Teacher", "Staff"])) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(
                    ["success" => false, "message" => "Unauthorized access"],
                    403,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        try {
            $validated = $request->validate([
                "permissionID" => "required",
                "admin_response" => "required|string",
                "is_student_permission" => "nullable|in:1,0,true,false",
            ]);

            $isStudentPermission = in_array(
                $request->input("is_student_permission"),
                [1, "1", true, "true"],
                true,
            );

            if ($isStudentPermission) {
                $permission = DB::table("student_permissions")
                    ->where("schoolID", $schoolID)
                    ->where("id", $validated["permissionID"])
                    ->first();

                if (!$permission) {
                    return response()->json(
                        ["success" => false, "message" => "Permission not found"],
                        404,
                    );
                }

                DB::table("student_permissions")
                    ->where("id", $permission->id)
                    ->update([
                        "status" => "rejected",
                        "reason_description" => $validated["admin_response"],
                        "updated_at" => now(),
                    ]);

                $this->insertPermissionApprovalLog([
                    "permissionID" => $permission->id,
                    "permission_type" => "student",
                    "approver_id" => $userID,
                    "approver_role" => $user,
                    "approval_order" =>
                        (int) ($permission->current_approval_order ?: 1),
                    "status" => "rejected",
                    "admin_response" => $validated["admin_response"],
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);

                $this->safeNotify(function () use ($permission, $validated) {
                    $student = \App\Models\Student::find($permission->student_id);
                    if ($student && $student->parent) {
                        (new SmsService())->sendSms(
                            $student->parent->phone,
                            "Ruksa ya mwanafunzi {$student->first_name} {$student->last_name} imekataliwa. Sababu: {$validated["admin_response"]}",
                        );
                    }
                });

                return response()->json([
                    "success" => true,
                    "message" => "Student permission rejected successfully.",
                ]);
            }

            $permission = PermissionRequest::where("schoolID", $schoolID)
                ->where("permissionID", $validated["permissionID"])
                ->first();

            if (!$permission) {
                return response()->json(
                    ["success" => false, "message" => "Permission not found"],
                    404,
                );
            }

            $permission->update([
                "status" => "rejected",
                "admin_response" => $validated["admin_response"],
                "reviewed_at" => now(),
                "is_read_by_requester" => false,
            ]);

            $this->insertPermissionApprovalLog([
                "permissionID" => $permission->permissionID,
                "permission_type" => $permission->requester_type ?? "teacher",
                "approver_id" => $userID,
                "approver_role" => $user,
                "approval_order" =>
                    (int) ($permission->current_approval_order ?: 1),
                "status" => "rejected",
                "admin_response" => $validated["admin_response"],
                "created_at" => now(),
                "updated_at" => now(),
            ]);

            $currentApprover =
                DB::table("permission_approval_chains")
                    ->where("schoolID", $schoolID)
                    ->where("permission_type", $permission->requester_type)
                    ->where(
                        "approval_order",
                        (int) ($permission->current_approval_order ?: 1),
                    )
                    ->value("role_name") ?? "System Admin";

            $this->safeNotify(function () use (
                $permission,
                $currentApprover,
                $validated,
            ) {
                $this->notifyRequester(
                    $permission,
                    "rejected",
                    $currentApprover,
                    $validated["admin_response"],
                );
            });

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    "success" => true,
                    "message" => "Permission rejected successfully.",
                ]);
            }

            return redirect()
                ->back()
                ->with("success", "Permission rejected successfully.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error("rejectPermission failed: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    "success" => false,
                    "message" => \App\Support\SafeErrorMessage::from(
                        $e,
                        "Failed to reject permission. Please try again.",
                    ),
                ],
                500,
            );
        }
    }

    public function viewPermissionApprovalChainSettings(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $roles = DB::table("roles")
            ->where("schoolID", $schoolID)
            ->get(["id", "name"]);
        $positions = DB::table("staff_professions")
            ->where("schoolID", $schoolID)
            ->get(["id", "name"]);

        return view(
            "partials.permission_approval_chain_settings",
            compact("roles", "positions"),
        );
    }

    public function savePermissionApprovalChain(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "permission_type" => "required|in:student,teacher,staff",
            "approvers" => "required|array|min:1",
            "approvers.*.role_id" => "nullable|integer",
            "approvers.*.profession_id" => "nullable|integer",
            "approvers.*.is_admin" => "nullable|boolean",
            "approvers.*.role_name" => "required|string",
        ]);

        DB::beginTransaction();
        try {
            DB::table("permission_approval_chains")
                ->where("schoolID", $schoolID)
                ->where("permission_type", $validated["permission_type"])
                ->delete();

            foreach ($validated["approvers"] as $index => $approver) {
                DB::table("permission_approval_chains")->insert([
                    "schoolID" => $schoolID,
                    "permission_type" => $validated["permission_type"],
                    "approval_order" => $index + 1,
                    "role_id" => $approver["role_id"] ?? null,
                    "profession_id" => $approver["profession_id"] ?? null,
                    "is_admin" => $approver["is_admin"] ?? false,
                    "role_name" => $approver["role_name"],
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);
            }

            DB::commit();
            return response()->json([
                "success" => true,
                "message" => "Approval chain saved successfully.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Error saving approval chain: " . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getPermissionApprovalChain(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $type = $request->get("type");

        $chains = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", $type)
            ->orderBy("approval_order")
            ->get();

        return response()->json(["success" => true, "data" => $chains]);
    }

    public function deletePermissionApprovalChain(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $type = $request->get("type");

        DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", $type)
            ->delete();

        return response()->json([
            "success" => true,
            "message" => "Approval chain deleted successfully.",
        ]);
    }

    public function getRolesAndPositions()
    {
        $schoolID = Session::get("schoolID");

        $roles = DB::table("roles")
            ->where("schoolID", $schoolID)
            ->get(["id", "name"]);
        $positions = DB::table("staff_professions")
            ->where("schoolID", $schoolID)
            ->get(["id", "name"]);

        return response()->json([
            "success" => true,
            "roles" => $roles,
            "positions" => $positions,
        ]);
    }

    public function getPermissionProgress(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $permissionID = $request->query("permissionID");
        $isStudentPermissionParam = $request->query(
            "is_student_permission",
            false,
        );

        $permissionType = null;
        $isStudentPermission = false;

        // Use explicit parameter if provided, otherwise try to detect
        if (
            $isStudentPermissionParam ||
            $isStudentPermissionParam === "1" ||
            $isStudentPermissionParam === 1
        ) {
            $isStudentPermission = true;
            $permission = DB::table("student_permissions as sp")
                ->where("sp.schoolID", $schoolID)
                ->where("sp.id", $permissionID)
                ->leftJoin(
                    "students",
                    "sp.student_id",
                    "=",
                    "students.studentID",
                )
                ->leftJoin(
                    "subclasses",
                    "students.subclassID",
                    "=",
                    "subclasses.subclassID",
                )
                ->leftJoin(
                    "classes",
                    "subclasses.classID",
                    "=",
                    "classes.classID",
                )
                ->leftJoin("teachers", "sp.requester_id", "=", "teachers.id")
                ->leftJoin(
                    "other_staff",
                    "sp.requester_id",
                    "=",
                    "other_staff.id",
                )
                ->select(
                    "sp.*",
                    DB::raw("sp.id as permissionID"),
                    DB::raw("sp.id as id"),
                    "students.first_name as student_first_name",
                    "students.last_name as student_last_name",
                    "students.middle_name as student_middle_name",
                    "classes.class_name",
                    "subclasses.subclass_name",
                    "teachers.first_name as teacher_first_name",
                    "teachers.last_name as teacher_last_name",
                    "other_staff.first_name as staff_first_name",
                    "other_staff.last_name as staff_last_name",
                )
                ->first();
        } else {
            // Try teacher/staff permission first
            $permission = PermissionRequest::where("schoolID", $schoolID)
                ->where("permissionID", $permissionID)
                ->first();

            if ($permission) {
                $permissionType = $permission->requester_type;
                $isStudentPermission = false;
            } else {
                // Try student permission as fallback
                $permission = DB::table("student_permissions as sp")
                    ->where("sp.schoolID", $schoolID)
                    ->where("sp.id", $permissionID)
                    ->leftJoin(
                        "students",
                        "sp.student_id",
                        "=",
                        "students.studentID",
                    )
                    ->leftJoin(
                        "subclasses",
                        "students.subclassID",
                        "=",
                        "subclasses.subclassID",
                    )
                    ->leftJoin(
                        "classes",
                        "subclasses.classID",
                        "=",
                        "classes.classID",
                    )
                    ->leftJoin(
                        "teachers",
                        "sp.requester_id",
                        "=",
                        "teachers.id",
                    )
                    ->leftJoin(
                        "other_staff",
                        "sp.requester_id",
                        "=",
                        "other_staff.id",
                    )
                    ->select(
                        "sp.*",
                        DB::raw("sp.id as permissionID"),
                        DB::raw("sp.id as id"),
                        "students.first_name as student_first_name",
                        "students.last_name as student_last_name",
                        "students.middle_name as student_middle_name",
                        "classes.class_name",
                        "subclasses.subclass_name",
                        "teachers.first_name as teacher_first_name",
                        "teachers.last_name as teacher_last_name",
                        "other_staff.first_name as staff_first_name",
                        "other_staff.last_name as staff_last_name",
                    )
                    ->first();

                if ($permission) {
                    $isStudentPermission = true;
                }
            }
        }

        if (!$permission) {
            return response()->json(
                ["success" => false, "message" => "Permission not found"],
                404,
            );
        }

        if (!$permissionType) {
            $permissionType = $isStudentPermission
                ? "student"
                : $permission->requester_type ?? "teacher";
        }

        $chain = DB::table("permission_approval_chains")
            ->leftJoin(
                "roles",
                "permission_approval_chains.role_id",
                "=",
                "roles.id",
            )
            ->leftJoin(
                "staff_professions",
                "permission_approval_chains.profession_id",
                "=",
                "staff_professions.id",
            )
            ->where("permission_approval_chains.schoolID", $schoolID)
            ->where(
                "permission_approval_chains.permission_type",
                $permissionType,
            )
            ->select(
                "permission_approval_chains.*",
                DB::raw(
                    "COALESCE(permission_approval_chains.role_name, roles.name) as role_name",
                ),
                "staff_professions.name as profession_name",
            )
            ->orderBy("permission_approval_chains.approval_order")
            ->get();

        $logs = DB::table("permission_approval_logs")
            ->where("permissionID", $permissionID)
            ->where("permission_type", $permissionType)
            ->orderBy("approval_order")
            ->get();

        $roles = DB::table("roles")
            ->where("schoolID", $schoolID)
            ->get(["id", "name"]);
        $positions = DB::table("staff_professions")
            ->where("schoolID", $schoolID)
            ->get(["id", "name"]);

        return response()->json([
            "success" => true,
            "permission" => $permission,
            "is_student_permission" => $isStudentPermission,
            "chain" => $chain,
            "logs" => $logs,
            "roles" => $roles,
            "positions" => $positions,
        ]);
    }

    public function exportPermissionPdf($permissionID)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Session expired");
        }

        $permission = PermissionRequest::where("schoolID", $schoolID)
            ->where("permissionID", $permissionID)
            ->with(["teacher", "staff", "student.subclass.class", "parent"])
            ->firstOrFail();

        $chain = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", $permission->requester_type)
            ->orderBy("approval_order")
            ->get();

        $logs = DB::table("permission_approval_logs")
            ->where("permissionID", $permissionID)
            ->where("permission_type", $permission->requester_type)
            ->orderBy("approval_order")
            ->get()
            ->keyBy("approval_order");

        $school = School::find($schoolID);

        $requesterName = "N/A";
        $requesterInfo = "";
        if ($permission->requester_type === "teacher" && $permission->teacher) {
            $requesterName =
                $permission->teacher->first_name .
                " " .
                $permission->teacher->last_name;
            $requesterInfo = "Teacher";
        } elseif (
            $permission->requester_type === "staff" &&
            $permission->staff
        ) {
            $requesterName =
                $permission->staff->first_name .
                " " .
                $permission->staff->last_name;
            $requesterInfo = "Staff";
        } elseif (
            $permission->requester_type === "student" &&
            $permission->student
        ) {
            $requesterName = trim(
                $permission->student->first_name .
                    " " .
                    $permission->student->middle_name .
                    " " .
                    $permission->student->last_name,
            );
            $className = $permission->student->subclass
                ? $permission->student->subclass->class->class_name .
                    " " .
                    $permission->student->subclass->subclass_name
                : "N/A";
            $requesterInfo = "Student - " . $className;
        }

        $data = [
            "school" => $school,
            "permission" => $permission,
            "chain" => $chain,
            "logs" => $logs,
            "requesterName" => $requesterName,
            "requesterInfo" => $requesterInfo,
            "title" => "Permission Request - " . $requesterName,
        ];

        $pdf = PDF::loadView("Admin.pdf.permission_request_pdf", $data);
        $pdf->setOptions([
            "isRemoteEnabled" => true,
            "isHtml5ParserEnabled" => true,
            "chroot" => base_path(),
        ]);
        $pdf->setPaper("A4", "portrait");

        $filename =
            "Permission_" .
            str_replace(" ", "_", $requesterName) .
            "_" .
            date("YmdHis") .
            ".pdf";
        return $pdf->download($filename);
    }

    public function exportStudentPermissionPdf($id)
    {
        $schoolID = Session::get("schoolID");
        if (!$schoolID) {
            return redirect()->route("login")->with("error", "Session expired");
        }

        $permission = DB::table("student_permissions as sp")
            ->where("sp.schoolID", $schoolID)
            ->where("sp.id", $id)
            ->leftJoin("students", "sp.student_id", "=", "students.studentID")
            ->leftJoin(
                "subclasses",
                "students.subclassID",
                "=",
                "subclasses.subclassID",
            )
            ->leftJoin("classes", "subclasses.classID", "=", "classes.classID")
            ->leftJoin("teachers", "sp.requester_id", "=", "teachers.id")
            ->leftJoin("other_staff", "sp.requester_id", "=", "other_staff.id")
            ->select(
                "sp.*",
                "students.first_name as student_first_name",
                "students.last_name as student_last_name",
                "students.middle_name as student_middle_name",
                "students.photo as student_photo",
                "students.gender as student_gender",
                "classes.class_name",
                "subclasses.subclass_name",
                "teachers.first_name as teacher_first_name",
                "teachers.last_name as teacher_last_name",
                "other_staff.first_name as staff_first_name",
                "other_staff.last_name as staff_last_name",
            )
            ->first();

        if (!$permission) {
            return redirect()->back()->with("error", "Permission not found");
        }

        $chain = DB::table("permission_approval_chains")
            ->where("schoolID", $schoolID)
            ->where("permission_type", "student")
            ->orderBy("approval_order")
            ->get();

        $school = School::find($schoolID);

        $requesterName = "N/A";
        $requesterInfo = "";
        if ($permission->requester_type === "teacher") {
            $requesterName =
                $permission->teacher_first_name .
                " " .
                $permission->teacher_last_name;
            $requesterInfo = "Teacher";
        } elseif ($permission->requester_type === "staff") {
            $requesterName =
                $permission->staff_first_name .
                " " .
                $permission->staff_last_name;
            $requesterInfo = "Staff";
        }

        $studentName = trim(
            $permission->student_first_name .
                " " .
                $permission->student_middle_name .
                " " .
                $permission->student_last_name,
        );
        $className = $permission->class_name . " " . $permission->subclass_name;

        $data = [
            "school" => $school,
            "permission" => $permission,
            "chain" => $chain,
            "requesterName" => $requesterName,
            "requesterInfo" => $requesterInfo,
            "studentName" => $studentName,
            "className" => $className,
            "title" => "Student Permission Request - " . $studentName,
        ];

        $pdf = PDF::loadView("Admin.pdf.student_permission_pdf", $data);
        $pdf->setOptions([
            "isRemoteEnabled" => true,
            "isHtml5ParserEnabled" => true,
            "chroot" => base_path(),
        ]);
        $pdf->setPaper("A4", "portrait");

        $filename =
            "Student_Permission_" .
            str_replace(" ", "_", $studentName) .
            "_" .
            date("YmdHis") .
            ".pdf";
        return $pdf->download($filename);
    }
    public function manageSchoolVisitors()
    {
        $user = Session::get("user_type");
        if (!$user || !in_array($user, ["Admin", "Staff", "Teacher"])) {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $schoolID = Session::get("schoolID");
        $schoolName =
            School::where("schoolID", $schoolID)->value("school_name") ??
            "School";
        Session::put("visitors_last_seen", now()->toDateTimeString());

        return view("Admin.manage_school_visitors", [
            "schoolName" => $schoolName,
        ]);
    }

    public function getPendingApprovals(Request $request)
    {
        $userType = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        $userEmail = Session::get("user_email");

        $teacherID = Session::get("teacherID");
        $userID = Session::get("userID");
        $staffID = Session::get("staffID");

        $dateFrom = $request->input("date_from");
        $dateTo = $request->input("date_to");
        $filterType = $request->input("filter_type", "all");
        $filterMonth = $request->input("filter_month");
        $filterMonthYear = $request->input("filter_month_year", date("Y"));
        $filterYear = $request->input("filter_year", date("Y"));

        if (!$userType) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(
                    ["success" => false, "message" => "Unauthorized"],
                    401,
                );
            }
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        // Robust detection: if teacherID or staffID is missing from session (common for Admin accounts),
        // find them by email to capture all roles/professions.
        if (!$teacherID && $userEmail) {
            $teacherID = DB::table("teachers")
                ->where("email", $userEmail)
                ->where("schoolID", $schoolID)
                ->value("id");
        }
        if (!$staffID && $userEmail) {
            $staffID = DB::table("other_staff")
                ->where("email", $userEmail)
                ->where("schoolID", $schoolID)
                ->value("id");
        }

        $query = PermissionRequest::where("schoolID", $schoolID)->where(
            "status",
            "pending",
        );

        if ($filterType === "range") {
            if ($dateFrom) {
                $query->whereDate("start_date", ">=", $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate("start_date", "<=", $dateTo);
            }
        } elseif ($filterType === "month") {
            if ($filterMonth) {
                $query
                    ->whereMonth("start_date", $filterMonth)
                    ->whereYear("start_date", $filterMonthYear);
            }
        } elseif ($filterType === "year") {
            if ($filterYear) {
                $query->whereYear("start_date", $filterYear);
            }
        } // Get roles for this user (handles teacher, staff, and admin links)
        $myRoleIDs = DB::table("role_user")
            ->where(function ($q) use ($teacherID, $userID) {
                if ($teacherID) {
                    $q->orWhere("teacher_id", $teacherID);
                }
                if ($userID) {
                    $q->orWhere("user_id", $userID);
                }
            })
            ->pluck("role_id")
            ->toArray();

        // Get profession if staff
        $myProfessionID = Session::get("profession_id");
        if (!$myProfessionID && $staffID) {
            $myProfessionID = DB::table("other_staff")
                ->where("id", $staffID)
                ->value("profession_id");
        }

        $query = PermissionRequest::with(["teacher", "student", "staff"])
            ->where("schoolID", $schoolID)
            ->where("status", "pending");

        $query->where(function ($q) use (
            $userType,
            $myRoleIDs,
            $myProfessionID,
            $schoolID,
        ) {
            // 1. If user is Admin, check if they are in the chain and show permissions at their step
            if ($userType === "Admin") {
                $adminChain = null;
                $maxChainOrder = 0;
                if (Schema::hasTable("permission_approval_chains")) {
                    $adminChain = DB::table("permission_approval_chains")
                        ->where("schoolID", $schoolID)
                        ->where("is_admin", true)
                        ->first();

                    $maxChainOrder =
                        DB::table("permission_approval_chains")
                            ->where("schoolID", $schoolID)
                            ->max("approval_order") ?? 0;
                }

                if ($adminChain) {
                    // Admin is in the chain - only show permissions at their step
                    $q->where(
                        "current_approval_order",
                        $adminChain->approval_order,
                    );
                } else {
                    // Admin is not in the chain - only show permissions where chain is complete
                    $q->where(function ($sub) use ($maxChainOrder) {
                        $sub->where("current_approval_order", 0)->orWhere(
                            "current_approval_order",
                            ">",
                            DB::raw($maxChainOrder),
                        );
                    });
                }
            }

            // 2. If user has a role that is part of ANY chain for this school, they see the requests for that type
            if (!empty($myRoleIDs)) {
                $q->orWhereIn("requester_type", function ($sub) use (
                    $myRoleIDs,
                ) {
                    $sub->select("permission_type")
                        ->from("permission_approval_chains")
                        ->whereIn("role_id", $myRoleIDs)
                        ->whereColumn(
                            "permission_approval_chains.schoolID",
                            "permission_requests.schoolID",
                        );
                });
            }

            // 3. If user has a profession that is part of ANY chain
            if ($myProfessionID) {
                $q->orWhereIn("requester_type", function ($sub) use (
                    $myProfessionID,
                ) {
                    $sub->select("permission_type")
                        ->from("permission_approval_chains")
                        ->where("profession_id", $myProfessionID)
                        ->whereColumn(
                            "permission_approval_chains.schoolID",
                            "permission_requests.schoolID",
                        );
                });
            }
        });

        $permissions = $query
            ->with(["teacher", "staff", "student.subclass.class", "parent"])
            ->orderBy("created_at", "desc")
            ->get();

        // Also query student_permissions table
        $studentPermissionsQuery = DB::table("student_permissions as sp")
            ->where("sp.schoolID", $schoolID)
            ->where("sp.status", "pending")
            ->leftJoin("students", "sp.student_id", "=", "students.studentID")
            ->leftJoin(
                "subclasses",
                "students.subclassID",
                "=",
                "subclasses.subclassID",
            )
            ->leftJoin("classes", "subclasses.classID", "=", "classes.classID")
            ->select(
                "sp.id as permissionID",
                "sp.schoolID",
                "sp.student_id",
                "sp.requester_id",
                "sp.requester_type",
                "sp.time_mode",
                "sp.days_count",
                "sp.start_date",
                "sp.end_date",
                "sp.start_time",
                "sp.end_time",
                "sp.reason_type",
                "sp.reason_description",
                "sp.status",
                "sp.current_approval_order",
                "sp.is_chain_completed",
                "sp.attachment",
                "sp.created_at",
                "students.first_name as student_first_name",
                "students.last_name as student_last_name",
                "students.middle_name as student_middle_name",
                "classes.class_name",
                "subclasses.subclass_name",
                DB::raw("'student' as permission_type"),
            );

        \Log::info("Student permissions base query", [
            "schoolID" => $schoolID,
            "userType" => $userType,
            "myRoleIDs" => $myRoleIDs,
            "myProfessionID" => $myProfessionID,
        ]);

        // Apply same chain logic for student permissions
        $studentPermissionsQuery->where(function ($q) use (
            $userType,
            $myRoleIDs,
            $myProfessionID,
            $schoolID,
        ) {
            if ($userType === "Admin") {
                $adminChain = null;
                if (Schema::hasTable("permission_approval_chains")) {
                    $adminChain = DB::table("permission_approval_chains")
                        ->where("schoolID", $schoolID)
                        ->where("permission_type", "student")
                        ->where("is_admin", true)
                        ->first();

                    \Log::info("Admin chain check for student permissions", [
                        "adminChain" => $adminChain,
                    ]);
                }

                if ($adminChain) {
                    $q->where(
                        "sp.current_approval_order",
                        $adminChain->approval_order,
                    );
                } else {
                    // If no admin in student chain, show all pending student permissions
                    $q->where("sp.current_approval_order", ">", 0);
                }
            } else {
                // For non-admin users, show all pending student permissions
                $q->where("sp.current_approval_order", ">", 0);
            }

            if (!empty($myRoleIDs)) {
                $q->orWhereIn("sp.requester_type", function ($sub) use (
                    $myRoleIDs,
                    $schoolID,
                ) {
                    $sub->select("permission_type")
                        ->from("permission_approval_chains")
                        ->whereIn("role_id", $myRoleIDs)
                        ->where("permission_type", "student")
                        ->where("schoolID", $schoolID);
                });
            }

            if ($myProfessionID) {
                $q->orWhereIn("sp.requester_type", function ($sub) use (
                    $myProfessionID,
                    $schoolID,
                ) {
                    $sub->select("permission_type")
                        ->from("permission_approval_chains")
                        ->where("profession_id", $myProfessionID)
                        ->where("permission_type", "student")
                        ->where("schoolID", $schoolID);
                });
            }
        });

        $studentPermissions = $studentPermissionsQuery
            ->orderBy("sp.created_at", "desc")
            ->get();

        \Log::info("Student permissions query result", [
            "count" => $studentPermissions->count(),
            "schoolID" => $schoolID,
        ]);

        // Combine both collections
        $allPermissions = collect();
        foreach ($permissions as $perm) {
            $perm->is_student_permission = false;
            $allPermissions->push($perm);
        }
        foreach ($studentPermissions as $perm) {
            $perm->is_student_permission = true;
            $perm->student_name = trim(
                ($perm->student_first_name ?? "") .
                    " " .
                    ($perm->student_middle_name ?? "") .
                    " " .
                    ($perm->student_last_name ?? ""),
            );
            $perm->class_name =
                ($perm->class_name ?? "") . " " . ($perm->subclass_name ?? "");
            $allPermissions->push($perm);
        }

        // Sort by created_at
        $allPermissions = $allPermissions->sortByDesc("created_at")->values();

        if ($request->ajax() || $request->expectsJson()) {
            $html = view("partials.permission_approval_list", [
                "permissions" => $allPermissions,
                "userType" => $userType,
                "myRoleIDs" => $myRoleIDs,
                "myProfessionID" => $myProfessionID,
            ])->render();
            return response()->json([
                "success" => true,
                "html" => $html,
                "data" => $allPermissions,
            ]);
        }

        return view("Admin.permission_approval_tasks", [
            "permissions" => $allPermissions,
            "userType" => $userType,
            "myRoleIDs" => $myRoleIDs,
            "myProfessionID" => $myProfessionID,
            "dateFrom" => $dateFrom,
            "dateTo" => $dateTo,
            "filterType" => $filterType,
            "filterMonth" => $filterMonth,
            "filterMonthYear" => $filterMonthYear,
            "filterYear" => $filterYear,
        ]);
    }

    public function storeSchoolVisitors(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        if (
            !$user ||
            !in_array($user, ["Admin", "Staff", "Teacher"]) ||
            !$schoolID
        ) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $names = $request->input("name", []);
        $contacts = $request->input("contact", []);
        $occupations = $request->input("occupation", []);
        $reasons = $request->input("reason", []);
        $signatures = $request->input("signature", []);

        $today = Carbon::today()->toDateString();
        $rows = [];

        foreach ($names as $index => $name) {
            $trimmed = trim((string) $name);
            if ($trimmed === "") {
                continue;
            }
            $rows[] = [
                "schoolID" => $schoolID,
                "visit_date" => $today,
                "name" => $trimmed,
                "contact" => isset($contacts[$index])
                    ? trim((string) $contacts[$index])
                    : null,
                "occupation" => isset($occupations[$index])
                    ? trim((string) $occupations[$index])
                    : null,
                "reason" => isset($reasons[$index])
                    ? trim((string) $reasons[$index])
                    : null,
                "signature" => isset($signatures[$index])
                    ? $signatures[$index]
                    : null,
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

        if (empty($rows)) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Please enter at least one visitor.",
                ],
                422,
            );
        }

        DB::table("school_visitors")->insert($rows);

        return response()->json([
            "success" => true,
            "message" => "Visitors recorded successfully.",
        ]);
    }

    public function todaySchoolVisitors()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        if (
            !$user ||
            !in_array($user, ["Admin", "Staff", "Teacher"]) ||
            !$schoolID
        ) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $today = Carbon::today()->toDateString();
        $visitors = SchoolVisitor::where("schoolID", $schoolID)
            ->whereDate("visit_date", $today)
            ->orderBy("created_at")
            ->get();

        return response()->json([
            "success" => true,
            "data" => $visitors->map(function ($visitor) {
                return [
                    "visitorID" => $visitor->visitorID,
                    "visitDate" => $visitor->visit_date
                        ? $visitor->visit_date->format("Y-m-d")
                        : null,
                    "name" => $visitor->name,
                    "contact" => $visitor->contact,
                    "occupation" => $visitor->occupation,
                    "reason" => $visitor->reason,
                    "signature" => $visitor->signature,
                ];
            }),
        ]);
    }

    public function listSchoolVisitors(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        if (
            !$user ||
            !in_array($user, ["Admin", "Staff", "Teacher"]) ||
            !$schoolID
        ) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $dateFrom = $request->get("date_from");
        $dateTo = $request->get("date_to");

        $query = SchoolVisitor::where("schoolID", $schoolID);
        if ($dateFrom && $dateTo) {
            $query->whereBetween("visit_date", [$dateFrom, $dateTo]);
        }

        $visitors = $query
            ->orderBy("visit_date", "desc")
            ->orderBy("created_at", "desc")
            ->get();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $usedCount = SchoolVisitorSmsLog::where("schoolID", $schoolID)
            ->whereBetween("created_at", [$monthStart, $monthEnd])
            ->sum("recipient_count");

        return response()->json([
            "success" => true,
            "data" => $visitors->map(function ($visitor) {
                return [
                    "visitorID" => $visitor->visitorID,
                    "visitDate" => $visitor->visit_date
                        ? $visitor->visit_date->format("Y-m-d")
                        : null,
                    "name" => $visitor->name,
                    "contact" => $visitor->contact,
                    "occupation" => $visitor->occupation,
                    "reason" => $visitor->reason,
                    "signature" => $visitor->signature,
                ];
            }),
            "used" => $usedCount,
        ]);
    }

    public function updateSchoolVisitor(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        if (
            !$user ||
            !in_array($user, ["Admin", "Staff", "Teacher"]) ||
            !$schoolID
        ) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "visitorID" => "required|integer",
            "name" => "required|string",
            "contact" => "nullable|string",
            "occupation" => "nullable|string",
            "reason" => "nullable|string",
        ]);

        $visitor = SchoolVisitor::where("schoolID", $schoolID)
            ->where("visitorID", $validated["visitorID"])
            ->firstOrFail();

        $visitor->update([
            "name" => $validated["name"],
            "contact" => $validated["contact"],
            "occupation" => $validated["occupation"],
            "reason" => $validated["reason"],
        ]);

        return response()->json([
            "success" => true,
            "message" => "Visitor updated successfully.",
        ]);
    }

    public function deleteSchoolVisitor(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        if (
            !$user ||
            !in_array($user, ["Admin", "Staff", "Teacher"]) ||
            !$schoolID
        ) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "visitorID" => "required|integer",
        ]);

        $visitor = SchoolVisitor::where("schoolID", $schoolID)
            ->where("visitorID", $validated["visitorID"])
            ->firstOrFail();
        $visitor->delete();

        return response()->json([
            "success" => true,
            "message" => "Visitor deleted successfully.",
        ]);
    }

    public function sendVisitorSms(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");
        if (
            !$user ||
            !in_array($user, ["Admin", "Staff", "Teacher"]) ||
            !$schoolID
        ) {
            return response()->json(
                ["success" => false, "message" => "Unauthorized access"],
                403,
            );
        }

        $validated = $request->validate([
            "message" => "required|string",
            "visitor_ids" => "required|array|min:1",
            "visitor_ids.*" => "integer",
        ]);

        $schoolName =
            School::where("schoolID", $schoolID)->value("school_name") ??
            "School";
        $prefix = trim($schoolName) !== "" ? trim($schoolName) . ": " : "";
        $message = $validated["message"];
        if (strpos($message, $prefix) !== 0) {
            $message = $prefix . $message;
        }
        if (mb_strlen($message) > 163) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "SMS message must be 163 characters or less.",
                ],
                422,
            );
        }
        if (!preg_match('/^[\x00-\x7F]+$/', $message)) {
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "SMS must not contain emojis or non-ASCII characters.",
                ],
                422,
            );
        }

        $visitorIds = $validated["visitor_ids"];
        $visitors = SchoolVisitor::where("schoolID", $schoolID)
            ->whereIn("visitorID", $visitorIds)
            ->get();

        if ($visitors->isEmpty()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "No valid visitors selected.",
                ],
                422,
            );
        }

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $usedCount = SchoolVisitorSmsLog::where("schoolID", $schoolID)
            ->whereBetween("created_at", [$monthStart, $monthEnd])
            ->sum("recipient_count");

        $recipientCount = $visitors->count();
        if ($usedCount + $recipientCount > 200) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Monthly SMS limit (200) exceeded.",
                ],
                422,
            );
        }

        set_time_limit(60);
        $smsService = new SmsService();
        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $firstError = null;

        foreach ($visitors as $visitor) {
            $phone = $visitor->contact;
            if (!$phone || !preg_match("/\d{7,}/", $phone)) {
                $skipped++;
                continue;
            }
            $result = $smsService->sendSms($phone, $message);
            if ($result["success"]) {
                $sent++;
            } else {
                $failed++;
                if ($firstError === null) {
                    $firstError = $result["message"] ?? "Failed to send SMS";
                }
            }
        }

        SchoolVisitorSmsLog::create([
            "schoolID" => $schoolID,
            "message" => $message,
            "recipient_count" => $sent,
            "recipient_ids" => $visitors->pluck("visitorID")->values()->all(),
        ]);

        if ($sent === 0) {
            return response()->json(
                [
                    "success" => false,
                    "message" => $firstError
                        ? "SMS failed. {$firstError}"
                        : "SMS failed.",
                    "sent" => $sent,
                    "failed" => $failed,
                    "skipped" => $skipped,
                    "used" => $usedCount,
                ],
                422,
            );
        }

        return response()->json([
            "success" => true,
            "message" => "SMS sent: {$sent}. Failed: {$failed}. Skipped: {$skipped}.",
            "sent" => $sent,
            "failed" => $failed,
            "skipped" => $skipped,
            "used" => $usedCount + $sent,
        ]);
    }

    public function storeDamagedLostRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "resourceID" => "required|exists:school_resources,resourceID",
            "record_date" => "required|date",
            "record_type" => "required|in:damaged,lost,used_up",
            "quantity" => "nullable|integer|min:0",
            "description" => "required|string",
        ]);

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $validated["resourceID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $quantity = $requiresQuantity
            ? (int) ($validated["quantity"] ?? 0)
            : null;

        if ($requiresQuantity && $quantity > ($resource->quantity ?? 0)) {
            return redirect()
                ->route("manage_damaged_lost_items")
                ->with("error", "Recorded quantity exceeds available stock.");
        }

        \DB::transaction(function () use (
            $schoolID,
            $resource,
            $validated,
            $quantity,
            $requiresQuantity,
        ) {
            \App\Models\DamagedLostRecord::create([
                "schoolID" => $schoolID,
                "resourceID" => $resource->resourceID,
                "record_date" => $validated["record_date"],
                "record_type" => $validated["record_type"],
                "quantity" => $quantity,
                "description" => $validated["description"],
            ]);

            if ($requiresQuantity) {
                $resource->update([
                    "quantity" => max(
                        0,
                        ($resource->quantity ?? 0) - $quantity,
                    ),
                ]);
            }
        });

        return redirect()
            ->route("manage_damaged_lost_items")
            ->with("success", "Record saved successfully.");
    }

    public function updateDamagedLostRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "damaged_lostID" =>
                "required|exists:damaged_lost_records,damaged_lostID",
            "record_date" => "required|date",
            "record_type" => "required|in:damaged,lost,used_up",
            "quantity" => "nullable|integer|min:0",
            "description" => "required|string",
        ]);

        $record = \App\Models\DamagedLostRecord::where(
            "damaged_lostID",
            $validated["damaged_lostID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $record->resourceID,
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $requiresQuantity = (bool) $resource->requires_quantity;
        $newQty = $requiresQuantity
            ? (int) ($validated["quantity"] ?? 0)
            : null;

        if ($requiresQuantity) {
            $available = ($resource->quantity ?? 0) + ($record->quantity ?? 0);
            if ($newQty > $available) {
                return redirect()
                    ->route("manage_damaged_lost_items")
                    ->with(
                        "error",
                        "Recorded quantity exceeds available stock.",
                    );
            }
        }

        \DB::transaction(function () use (
            $record,
            $resource,
            $validated,
            $requiresQuantity,
            $newQty,
        ) {
            if ($requiresQuantity) {
                $restored =
                    ($resource->quantity ?? 0) + ($record->quantity ?? 0);
                $resource->update([
                    "quantity" => max(0, $restored - $newQty),
                ]);
            }

            $record->update([
                "record_date" => $validated["record_date"],
                "record_type" => $validated["record_type"],
                "quantity" => $newQty,
                "description" => $validated["description"],
            ]);
        });

        return redirect()
            ->route("manage_damaged_lost_items")
            ->with("success", "Record updated successfully.");
    }

    public function deleteDamagedLostRecord(Request $request)
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        if (!$user || $user !== "Admin") {
            return redirect()
                ->route("login")
                ->with("error", "Unauthorized access");
        }

        $validated = $request->validate([
            "damaged_lostID" =>
                "required|exists:damaged_lost_records,damaged_lostID",
        ]);

        $record = \App\Models\DamagedLostRecord::where(
            "damaged_lostID",
            $validated["damaged_lostID"],
        )
            ->where("schoolID", $schoolID)
            ->firstOrFail();

        $resource = \App\Models\SchoolResource::where(
            "resourceID",
            $record->resourceID,
        )
            ->where("schoolID", $schoolID)
            ->first();

        \DB::transaction(function () use ($record, $resource) {
            if ($resource && $resource->requires_quantity) {
                $resource->update([
                    "quantity" =>
                        ($resource->quantity ?? 0) + ($record->quantity ?? 0),
                ]);
            }
            $record->delete();
        });

        return redirect()
            ->route("manage_damaged_lost_items")
            ->with("success", "Record deleted successfully.");
    }

    public function resourceReport()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        $resourcePermissions = [
            "resources_create",
            "resources_update",
            "resources_delete",
            "resources_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($resourcePermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        return view("Admin.resource_report", compact("schoolID"));
    }

    public function usageReport()
    {
        $user = Session::get("user_type");
        $schoolID = Session::get("schoolID");

        $resourcePermissions = [
            "resources_create",
            "resources_update",
            "resources_delete",
            "resources_read_only",
        ];
        if (
            !$user ||
            ($user !== "Admin" &&
                !$this->staffHasAnyPermission($resourcePermissions))
        ) {
            return redirect()->back()->with("error", "Unauthorized access");
        }

        return view("Admin.usage_report", compact("schoolID"));
    }

    /**
     * Lesson plans created by teachers for a class / subclass / subject.
     */
    public function getLessonPlansForAdmin(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $subjectID = $request->input("subjectID");
            $classID = $request->input("classID");
            $subclassID = $request->input("subclassID");

            if (!$schoolID) {
                return response()->json([
                    "success" => false,
                    "error" => "Session expired",
                ]);
            }

            if (!$subjectID || !$classID) {
                return response()->json([
                    "success" => false,
                    "error" => "Please select class and subject",
                ]);
            }

            $subject = DB::table("school_subjects")
                ->where("subjectID", $subjectID)
                ->where("schoolID", $schoolID)
                ->first();

            if (!$subject) {
                return response()->json([
                    "success" => false,
                    "error" => "Subject not found",
                ]);
            }

            $class = ClassModel::where("classID", $classID)
                ->where("schoolID", $schoolID)
                ->first();

            if (!$class) {
                return response()->json([
                    "success" => false,
                    "error" => "Class not found",
                ]);
            }

            $lessonPlans = $this->queryAdminLessonPlans(
                (int) $schoolID,
                (int) $classID,
                $subclassID,
                (int) $subjectID,
            );

            $formattedPlans = $lessonPlans->map(function ($plan) {
                return $this->formatAdminLessonPlanRow($plan);
            });

            $scopeLabel = $class->class_name;
            if ($subclassID && $subclassID !== "all" && $subclassID !== "") {
                $sub = DB::table("subclasses")
                    ->where("subclassID", $subclassID)
                    ->where("classID", $classID)
                    ->value("subclass_name");
                if ($sub) {
                    $scopeLabel .= " — " . $sub;
                }
            } else {
                $scopeLabel .= " — All subclasses";
            }

            return response()->json([
                "success" => true,
                "lesson_plans" => $formattedPlans,
                "subject_name" => $subject->subject_name,
                "class_name" => $scopeLabel,
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error getting lesson plans for admin: " . $e->getMessage(),
            );
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get single lesson plan for admin
     */
    public function getLessonPlanForAdmin(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $lessonPlanID = $request->input("lesson_planID");

            if (!$schoolID) {
                return response()->json([
                    "success" => false,
                    "error" => "Session expired",
                ]);
            }

            $lessonPlan = LessonPlan::where("lesson_planID", $lessonPlanID)
                ->where("schoolID", $schoolID)
                ->with([
                    "teacher",
                    "sessionTimetable.subclass",
                    "sessionTimetable.classSubject.subject",
                ])
                ->first();

            if (!$lessonPlan) {
                return response()->json([
                    "success" => false,
                    "error" => "Lesson plan not found",
                ]);
            }

            // Get teacher name
            $teacherName = "N/A";
            if ($lessonPlan->teacher) {
                $teacherName = trim(
                    ($lessonPlan->teacher->first_name ?? "") .
                        " " .
                        ($lessonPlan->teacher->last_name ?? ""),
                );
            }

            $data = $lessonPlan->toArray();
            $data["teacher_name"] = $teacherName;
            $data["is_approved"] = !empty($lessonPlan->supervisor_signature);
            $data["subclass_name"] =
                $lessonPlan->sessionTimetable->subclass->subclass_name ?? "";

            return response()->json([
                "success" => true,
                "data" => $data,
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error getting lesson plan for admin: " . $e->getMessage(),
            );
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve lesson plan: apply headmaster signature and school stamp.
     */
    public function signLessonPlan(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $lessonPlanID = $request->input("lesson_planID");

            if (!$schoolID) {
                return response()->json([
                    "success" => false,
                    "error" => "Session expired",
                ]);
            }

            $lessonPlan = LessonPlan::where("lesson_planID", $lessonPlanID)
                ->where("schoolID", $schoolID)
                ->first();

            if (!$lessonPlan) {
                return response()->json([
                    "success" => false,
                    "error" => "Lesson plan not found",
                ]);
            }

            $school = School::where("schoolID", $schoolID)->first();
            $headSign = $this->schoolFileToDataUrl(
                $school?->school_signature,
            );

            if (!$headSign) {
                return response()->json([
                    "success" => false,
                    "error" =>
                        "Headmaster signature is not set. Upload it in School settings first.",
                ]);
            }

            $lessonPlan->update([
                "supervisor_signature" => $headSign,
                "status" => "completed",
                "sent_to_admin" => true,
                "sent_at" => $lessonPlan->sent_at ?: now(),
            ]);

            return response()->json([
                "success" => true,
                "message" =>
                    "Lesson plan approved. Headmaster signature and stamp applied.",
            ]);
        } catch (\Exception $e) {
            Log::error("Error signing lesson plan: " . $e->getMessage());
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove supervisor signature from lesson plan
     */
    public function removeLessonPlanSignature(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $lessonPlanID = $request->input("lesson_planID");

            if (!$schoolID) {
                return response()->json([
                    "success" => false,
                    "error" => "Session expired",
                ]);
            }

            $lessonPlan = LessonPlan::where("lesson_planID", $lessonPlanID)
                ->where("schoolID", $schoolID)
                ->first();

            if (!$lessonPlan) {
                return response()->json([
                    "success" => false,
                    "error" => "Lesson plan not found",
                ]);
            }

            if (!$lessonPlan->supervisor_signature) {
                return response()->json([
                    "success" => false,
                    "error" => "No signature found to remove",
                ]);
            }

            $lessonPlan->update([
                "supervisor_signature" => null,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Signature removed successfully",
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error removing lesson plan signature: " . $e->getMessage(),
            );
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function getLessonPlanFilterOptions(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $classID = $request->input("classID");
            $subclassID = $request->input("subclassID");

            if (!$schoolID || !$classID) {
                return response()->json([
                    "success" => false,
                    "error" => "Class is required",
                ]);
            }

            $class = ClassModel::where("classID", $classID)
                ->where("schoolID", $schoolID)
                ->first();
            if (!$class) {
                return response()->json([
                    "success" => false,
                    "error" => "Class not found",
                ]);
            }

            $subclasses = DB::table("subclasses")
                ->where("classID", $classID)
                ->orderBy("subclass_name")
                ->get(["subclassID", "subclass_name"]);

            $subjectsQuery = DB::table("class_subjects")
                ->join(
                    "school_subjects",
                    "class_subjects.subjectID",
                    "=",
                    "school_subjects.subjectID",
                )
                ->where("class_subjects.classID", $classID)
                ->where("class_subjects.status", "Active")
                ->where("school_subjects.status", "Active")
                ->where("school_subjects.schoolID", $schoolID);

            if ($subclassID && $subclassID !== "all") {
                $subjectsQuery->where(
                    "class_subjects.subclassID",
                    $subclassID,
                );
            }

            $subjects = $subjectsQuery
                ->select(
                    "school_subjects.subjectID",
                    "school_subjects.subject_name",
                )
                ->distinct()
                ->orderBy("school_subjects.subject_name")
                ->get();

            return response()->json([
                "success" => true,
                "subclasses" => $subclasses,
                "subjects" => $subjects,
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error getting lesson plan filter options: " .
                    $e->getMessage(),
            );
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function approveAllLessonPlans(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $subjectID = $request->input("subjectID");
            $classID = $request->input("classID");
            $subclassID = $request->input("subclassID");

            if (!$schoolID) {
                return response()->json([
                    "success" => false,
                    "error" => "Session expired",
                ]);
            }

            if (!$subjectID || !$classID) {
                return response()->json([
                    "success" => false,
                    "error" => "Please select class and subject",
                ]);
            }

            $school = School::where("schoolID", $schoolID)->first();
            $headSign = $this->schoolFileToDataUrl(
                $school?->school_signature,
            );
            if (!$headSign) {
                return response()->json([
                    "success" => false,
                    "error" =>
                        "Headmaster signature is not set. Upload it in School settings first.",
                ]);
            }

            $plans = $this->queryAdminLessonPlans(
                (int) $schoolID,
                (int) $classID,
                $subclassID,
                (int) $subjectID,
            )->filter(function ($plan) {
                return empty($plan->supervisor_signature);
            });

            $count = 0;
            foreach ($plans as $plan) {
                $plan->update([
                    "supervisor_signature" => $headSign,
                    "status" => "completed",
                    "sent_to_admin" => true,
                    "sent_at" => $plan->sent_at ?: now(),
                ]);
                $count++;
            }

            return response()->json([
                "success" => true,
                "message" =>
                    $count === 0
                        ? "All lesson plans in this filter are already approved."
                        : "Approved {$count} lesson plan(s). Headmaster signature and stamp applied.",
                "approved_count" => $count,
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error approving all lesson plans: " . $e->getMessage(),
            );
            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function downloadAllLessonPlans(Request $request)
    {
        try {
            $schoolID = Session::get("schoolID");
            $subjectID = $request->input("subjectID");
            $classID = $request->input("classID");
            $subclassID = $request->input("subclassID");

            if (!$schoolID) {
                return redirect()
                    ->back()
                    ->with("error", "Session expired");
            }

            if (!$subjectID || !$classID) {
                return redirect()
                    ->back()
                    ->with("error", "Please select class and subject");
            }

            $lessonPlans = $this->queryAdminLessonPlans(
                (int) $schoolID,
                (int) $classID,
                $subclassID,
                (int) $subjectID,
            );

            if ($lessonPlans->isEmpty()) {
                return redirect()
                    ->back()
                    ->with("error", "No lesson plans found for this filter");
            }

            $school = School::find($schoolID);
            $schoolType =
                $school && $school->school_type
                    ? strtolower($school->school_type)
                    : "primary";
            $isPrimary =
                strpos($schoolType, "primary") !== false ||
                strpos($schoolType, "pre") !== false;
            $schoolTypeDisplay = $isPrimary
                ? "PRE AND PRIMARY SCHOOL"
                : "SECONDARY SCHOOL";

            $html = view("Teacher.pdf.bulk_lesson_plans", [
                "lessonPlans" => $lessonPlans,
                "school" => $school,
                "schoolType" => $schoolTypeDisplay,
            ])->render();

            $options = new \Dompdf\Options();
            $options->set("defaultFont", "Helvetica");
            $options->set("isRemoteEnabled", true);
            $options->set("isHtml5ParserEnabled", true);
            $options->setChroot(public_path());

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper("A4", "portrait");
            $dompdf->render();

            $filename =
                "Lesson_Plans_" .
                Carbon::now()->format("Y-m-d_His") .
                ".pdf";

            return response($dompdf->output(), 200, [
                "Content-Type" => "application/pdf",
                "Content-Disposition" =>
                    'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Error downloading admin lesson plans: " . $e->getMessage(),
            );
            return redirect()
                ->back()
                ->with("error", "Failed to generate PDF");
        }
    }

    private function queryAdminLessonPlans(
        int $schoolID,
        int $classID,
        $subclassID,
        int $subjectID,
    ) {
        return LessonPlan::with([
            "teacher",
            "sessionTimetable.subclass",
            "sessionTimetable.classSubject.subject",
        ])
            ->where("schoolID", $schoolID)
            ->whereHas("sessionTimetable", function ($q) use (
                $classID,
                $subclassID,
                $subjectID,
            ) {
                $q->whereHas("subclass", function ($s) use ($classID) {
                    $s->where("classID", $classID);
                });
                if (
                    $subclassID &&
                    $subclassID !== "all" &&
                    $subclassID !== ""
                ) {
                    $q->where("subclassID", $subclassID);
                }
                $q->where(function ($inner) use ($subjectID) {
                    $inner
                        ->where("subjectID", $subjectID)
                        ->orWhereHas("classSubject", function ($cs) use (
                            $subjectID,
                        ) {
                            $cs->where("subjectID", $subjectID);
                        });
                });
            })
            ->orderBy("lesson_date", "asc")
            ->orderBy("lesson_time_start", "asc")
            ->get();
    }

    private function formatAdminLessonPlanRow(LessonPlan $plan): array
    {
        $teacherName = "N/A";
        if ($plan->teacher) {
            $teacherName = trim(
                ($plan->teacher->first_name ?? "") .
                    " " .
                    ($plan->teacher->last_name ?? ""),
            );
        }

        $subclassName =
            $plan->sessionTimetable->subclass->subclass_name ?? "";

        return [
            "lesson_planID" => $plan->lesson_planID,
            "lesson_date" =>
                $plan->lesson_date instanceof \DateTimeInterface
                    ? $plan->lesson_date->format("Y-m-d")
                    : $plan->lesson_date,
            "subject" => $plan->subject,
            "class_name" => $plan->class_name,
            "subclass_name" => $subclassName,
            "teacher_name" => $teacherName ?: "N/A",
            "lesson_time_start" => $plan->lesson_time_start,
            "lesson_time_end" => $plan->lesson_time_end,
            "sent_at" => $plan->sent_at,
            "is_approved" => !empty($plan->supervisor_signature),
            "supervisor_signature" => $plan->supervisor_signature,
        ];
    }

    private function schoolFileToDataUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $relative = ltrim($path, "/");
        $parent = dirname(base_path());
        $possibilities = [
            public_path($relative),
            $parent . "/public_html/" . $relative,
        ];
        foreach ($possibilities as $fullPath) {
            if (@file_exists($fullPath) && is_file($fullPath)) {
                try {
                    $ext = strtolower(
                        pathinfo($fullPath, PATHINFO_EXTENSION) ?: "png",
                    );
                    if ($ext === "jpg") {
                        $ext = "jpeg";
                    }
                    return "data:image/" .
                        $ext .
                        ";base64," .
                        base64_encode(file_get_contents($fullPath));
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return null;
    }

    public function getVisitorNotificationCount()
    {
        $isAdmin = Session::get("user_type") === "Admin";
        $schoolID = Session::get("schoolID");
        if (!$isAdmin || !$schoolID) {
            return response()->json(
                ["success" => false, "error" => "Unauthorized"],
                403,
            );
        }

        $lastSeen = Session::get("visitors_last_seen");
        $lastSeenAt = $lastSeen
            ? Carbon::parse($lastSeen)
            : Carbon::createFromTimestamp(0);

        $visitorCount = DB::table("school_visitors")
            ->where("schoolID", $schoolID)
            ->where("created_at", ">", $lastSeenAt)
            ->count();

        $oaLastSeen = Session::get("online_admissions_last_seen");
        $oaLastSeenAt = $oaLastSeen
            ? Carbon::parse($oaLastSeen)
            : Carbon::createFromTimestamp(0);

        $onlineAdmissionCount = 0;
        try {
            $onlineAdmissionCount = DB::table("online_admissions")
                ->where("schoolID", $schoolID)
                ->where("status", "Pending")
                ->where("created_at", ">", $oaLastSeenAt)
                ->count();
        } catch (\Throwable $e) {
            $onlineAdmissionCount = 0;
        }

        return response()->json([
            "success" => true,
            "count" => $visitorCount + $onlineAdmissionCount,
            "visitors" => $visitorCount,
            "online_admissions" => $onlineAdmissionCount,
        ]);
    }

    public function markVisitorNotificationsRead()
    {
        $isAdmin = Session::get("user_type") === "Admin";
        if (!$isAdmin) {
            return response()->json(
                ["success" => false, "error" => "Unauthorized"],
                403,
            );
        }

        Session::put("visitors_last_seen", now()->toDateTimeString());
        Session::put("online_admissions_last_seen", now()->toDateTimeString());
        return response()->json(["success" => true]);
    }

    public function getRecentVisitors()
    {
        $isAdmin = Session::get("user_type") === "Admin";
        $schoolID = Session::get("schoolID");
        if (!$isAdmin || !$schoolID) {
            return response()->json(
                ["success" => false, "error" => "Unauthorized"],
                403,
            );
        }

        $lastSeenAt = Session::get("visitors_last_seen");
        $oaLastSeenAt = Session::get("online_admissions_last_seen");

        $visitors = DB::table("school_visitors")
            ->where("schoolID", $schoolID)
            ->orderBy("created_at", "desc")
            ->limit(5)
            ->get()
            ->map(function ($visitor) use ($lastSeenAt) {
                $created = Carbon::parse($visitor->created_at);
                $visitor->time_label = $created->isToday()
                    ? $created->diffForHumans()
                    : $created->format("d M Y");
                $visitor->is_new = $lastSeenAt
                    ? $created->gt(Carbon::parse($lastSeenAt))
                    : true;
                $visitor->type = "visitor";
                return $visitor;
            });

        $onlineAdmissions = collect();
        try {
            $oaCutoff = $oaLastSeenAt
                ? Carbon::parse($oaLastSeenAt)
                : Carbon::createFromTimestamp(0);

            $onlineAdmissions = DB::table("online_admissions")
                ->where("schoolID", $schoolID)
                ->where("status", "Pending")
                ->where("created_at", ">", $oaCutoff)
                ->orderBy("created_at", "desc")
                ->limit(5)
                ->get()
                ->map(function ($app) {
                    $created = Carbon::parse($app->created_at);
                    return [
                        "type" => "online_admission",
                        "name" => trim(($app->student_first_name ?? "") . " " . ($app->student_last_name ?? "")),
                        "application_number" => $app->application_number ?? "",
                        "time_label" => $created->isToday()
                            ? $created->diffForHumans()
                            : $created->format("d M Y"),
                        "url" => route("school.online_admission.index"),
                    ];
                });
        } catch (\Throwable $e) {
            $onlineAdmissions = collect();
        }

        return response()->json([
            "success" => true,
            "visitors" => $visitors,
            "online_admissions" => $onlineAdmissions,
        ]);
    }

    // Student Permission Requesters Management
    public function getStudentPermissionRequestersUsers()
    {
        try {
            $schoolID = Session::get("schoolID");

            if (!$schoolID) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "School ID not found in session",
                    ],
                    401,
                );
            }

            $teachers = DB::table("teachers")
                ->where("schoolID", $schoolID)
                ->get(["id as teacherID", "first_name", "last_name"]);

            $staff = DB::table("other_staff")
                ->where("schoolID", $schoolID)
                ->get(["id as staffID", "first_name", "last_name"]);

            return response()->json([
                "success" => true,
                "teachers" => $teachers,
                "staff" => $staff,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                ["success" => false, "message" => "Error: " . $e->getMessage()],
                500,
            );
        }
    }

    public function indexStudentPermissionRequesters()
    {
        $schoolID = Session::get("schoolID");

        $requesters = DB::table("student_permission_requesters as spr")
            ->leftJoin("teachers", function ($join) {
                $join
                    ->on("spr.user_id", "=", "teachers.id")
                    ->where("spr.user_type", "=", "teacher");
            })
            ->leftJoin("other_staff", function ($join) {
                $join
                    ->on("spr.user_id", "=", "other_staff.id")
                    ->where("spr.user_type", "=", "staff");
            })
            ->where("spr.schoolID", $schoolID)
            ->where("spr.is_active", true)
            ->select(
                "spr.id",
                "spr.user_id",
                "spr.user_type",
                DB::raw(
                    "COALESCE(teachers.first_name, other_staff.first_name) as first_name",
                ),
                DB::raw(
                    "COALESCE(teachers.last_name, other_staff.last_name) as last_name",
                ),
            )
            ->get()
            ->map(function ($item) {
                $item->name = $item->first_name . " " . $item->last_name;
                $item->type = ucfirst($item->user_type);
                return $item;
            });

        \Log::info("Student Permission Requesters loaded:", [
            "requesters" => $requesters,
            "schoolID" => $schoolID,
        ]);

        return response()->json([
            "success" => true,
            "requesters" => $requesters,
        ]);
    }

    public function storeStudentPermissionRequesters(Request $request)
    {
        $schoolID = Session::get("schoolID");
        $users = $request->input("users", []);

        if (empty($users)) {
            return response()->json([
                "success" => false,
                "message" => "No users selected",
            ]);
        }

        foreach ($users as $user) {
            // Check if already assigned
            $exists = DB::table("student_permission_requesters")
                ->where("schoolID", $schoolID)
                ->where("user_id", $user["user_id"])
                ->where("user_type", $user["type"])
                ->exists();

            if (!$exists) {
                DB::table("student_permission_requesters")->insert([
                    "schoolID" => $schoolID,
                    "user_id" => $user["user_id"],
                    "user_type" => $user["type"],
                    "is_active" => true,
                    "created_at" => now(),
                    "updated_at" => now(),
                ]);
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Requesters assigned successfully",
        ]);
    }

    public function destroyStudentPermissionRequester($id)
    {
        $schoolID = Session::get("schoolID");

        DB::table("student_permission_requesters")
            ->where("id", $id)
            ->where("schoolID", $schoolID)
            ->delete();

        return response()->json([
            "success" => true,
            "message" => "Requester removed successfully",
        ]);
    }
}
