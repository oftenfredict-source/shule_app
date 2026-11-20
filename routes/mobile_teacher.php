<?php

/*
|--------------------------------------------------------------------------
| Teacher mobile API  (/api/v1/teacher/*)
|--------------------------------------------------------------------------
| Loaded from routes/api.php inside:
|   Route::middleware('mobile.auth:teacher')->prefix('teacher')
|
| Every row binds an API path to the web controller method that powers the same
| teacher-portal feature (see TeacherMobileApiController::bridge). {placeholders}
| use the same names as the web controller method arguments.
*/

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\TeacherMobileApiController;
use App\Http\Controllers\BlackBookController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CharacterAssessmentController;
use App\Http\Controllers\ExamAttendanceController;
use App\Http\Controllers\GoalManagementController;
use App\Http\Controllers\ManageAttendanceController;
use App\Http\Controllers\ManageClassessController;
use App\Http\Controllers\ManageExaminationController;
use App\Http\Controllers\ResultManagementController;
use App\Http\Controllers\SgpmPerformanceController;
use App\Http\Controllers\SgpmTaskController;
use App\Http\Controllers\StorePortalController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StudentPortalMaterialController;
use App\Http\Controllers\TeacherDutyController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\TimeTableController;
use Illuminate\Support\Facades\Route;

$web = function (string $verb, string $uri, string $class, string $action, ?array $only = null) {
    $route = Route::match([$verb], $uri, [TeacherMobileApiController::class, 'bridge'])
        ->defaults('web', [$class, $action]);

    if ($only) {
        $route->defaults('only', $only);
    }

    return $route;
};

$get = fn (string $uri, string $class, string $action, ?array $only = null) => $web('GET', $uri, $class, $action, $only);
$post = fn (string $uri, string $class, string $action, ?array $only = null) => $web('POST', $uri, $class, $action, $only);
$delete = fn (string $uri, string $class, string $action, ?array $only = null) => $web('DELETE', $uri, $class, $action, $only);

// ── Account / dashboard ─────────────────────────────────────────────────────
Route::get('/capabilities', [TeacherMobileApiController::class, 'capabilities']);
Route::get('/dashboard', [TeacherMobileApiController::class, 'dashboard']);
Route::get('/profile', [\App\Http\Controllers\Api\TeacherProfileMobileApiController::class, 'show']);
Route::post('/profile/update', [\App\Http\Controllers\Api\TeacherProfileMobileApiController::class, 'update']);
$post('/profile/image', TeachersController::class, 'updateMyImage');
$get('/profile/pdf', TeachersController::class, 'downloadMyProfilePdf');
$post('/signature', ManageClassessController::class, 'uploadTeacherSignature');
$post('/signature/delete', ManageClassessController::class, 'deleteTeacherSignature');
Route::post('/password/change', [\App\Http\Controllers\Api\TeacherProfileMobileApiController::class, 'changePassword']);

// ── My attendance ───────────────────────────────────────────────────────────
Route::get('/attendance', [\App\Http\Controllers\Api\TeacherMyAttendanceMobileApiController::class, 'show']);
$get('/attendance/pdf', ManageAttendanceController::class, 'exportTeacherMyAttendancePdf');

// ── Student leave / permission requests (student requesters only) ───────────
$get('/student-leave/search', TeachersController::class, 'searchStudentsForPermission');
$get('/student-leave/requests', TeachersController::class, 'getTeacherPermissionRequests');
$get('/student-leave/{id}/progress', TeachersController::class, 'getStudentPermissionProgress')->whereNumber('id');
$get('/student-leave/{id}', TeachersController::class, 'getStudentPermissionDetails')->whereNumber('id');
$post('/student-leave', TeachersController::class, 'storeStudentPermission');

// ── Teaching: sessions & tasks ──────────────────────────────────────────────
Route::get('/sessions', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'sessions']);
$get('/sessions/pdf', TeachersController::class, 'exportMySessionsPdf');
$get('/sessions/students', TeachersController::class, 'getSessionStudentsAPI');
// Session attendance update uses the same session-auth method as the website (includes can_edit).
$get('/sessions/attendance', TeachersController::class, 'getSessionAttendanceForUpdate');
$post('/sessions/attendance', TeachersController::class, 'collectSessionAttendanceAPI');
$get('/sessions/attendance-stats', TeachersController::class, 'getSessionAttendanceStats');
$get('/sessions/collected-attendance', TeachersController::class, 'getCollectedAttendance');
$get('/sessions/by-subject', TeachersController::class, 'getSessionsBySubject');
$get('/sessions/all-for-year', TeachersController::class, 'getAllSessionsForYear');
$get('/sessions/terms-for-year', TeachersController::class, 'getTermsForYear');
$get('/sessions/exams-for-year-term', TeachersController::class, 'getExamsForYearTerm');
$post('/sessions/tasks', TeachersController::class, 'assignSessionTaskAPI');
$post('/sessions/tasks/{taskID}', TeachersController::class, 'updateSessionTask')->whereNumber('taskID');
Route::get('/tasks', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'tasks']);
$get('/resolve-class-subject', TeachersController::class, 'teacherResolveClassSubject');

// ── Teaching: subjects, students, results ───────────────────────────────────
$get('/subjects', TeachersController::class, 'getTeacherSubjectsAPI');
$get('/subjects/{classSubjectID}/students', TeachersController::class, 'getSubjectStudentsAPI')->whereNumber('classSubjectID');
$get('/subjects/{classSubjectID}/examinations', TeachersController::class, 'getExaminationsForSubjectAPI')->whereNumber('classSubjectID');
$get('/subjects/{classSubjectID}/results', TeachersController::class, 'getSubjectResultsAPI')->whereNumber('classSubjectID');
$get('/subjects/{classSubjectID}/results/{examID}', TeachersController::class, 'getSubjectResultsAPI')->whereNumber(['classSubjectID', 'examID']);
$get('/subjects/{classSubjectID}/results/{examID}/template', TeachersController::class, 'downloadExcelTemplateAPI')->whereNumber(['classSubjectID', 'examID']);
$get('/subjects/{classSubjectID}/results/{examID}/pdf', TeachersController::class, 'exportSubjectResultsPdf')->whereNumber(['classSubjectID', 'examID']);
$get('/subjects/{classSubjectID}/exam-attendance', TeachersController::class, 'getExamAttendanceDataAPI')->whereNumber('classSubjectID');
$get('/subjects/{classSubjectID}/session-attendance', TeachersController::class, 'getSessionAttendanceDataAPI')->whereNumber('classSubjectID');
$get('/subjects/{classSubjectID}/papers', TeachersController::class, 'getClassSubjectPapers')->whereNumber('classSubjectID');
$post('/results/save', TeachersController::class, 'saveSubjectResultsAPI');
$post('/results/upload-excel', TeachersController::class, 'uploadExcelResultsAPI');
$post('/results/parse-excel', TeachersController::class, 'parseExcelResultsForForm');
$get('/results/all-streams/{classID}/{subjectID}', TeachersController::class, 'getClassSubjectStudentsAll')->whereNumber(['classID', 'subjectID']);
$get('/results/exams-for-term', ResultManagementController::class, 'getExamsForTerm');
$get('/results/tests-for-term', ResultManagementController::class, 'getTestsForTermList');
$get('/results/school-exams-for-term', ResultManagementController::class, 'getSchoolExamsForTermList');

// ── Teaching: scheme of work ────────────────────────────────────────────────
Route::get('/scheme-of-work', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'schemeOfWork']);
$post('/scheme-of-work', TeachersController::class, 'storeNewScheme');
$post('/scheme-of-work/check-existing', TeachersController::class, 'checkExistingScheme');
$get('/scheme-of-work/create/{classSubjectID}', TeachersController::class, 'createNewScheme')->whereNumber('classSubjectID');
$get('/scheme-of-work/existing/{classSubjectID}', TeachersController::class, 'useExistingSchemes')->whereNumber('classSubjectID');
$post('/scheme-of-work/{schemeOfWorkID}/use', TeachersController::class, 'useThisScheme')->whereNumber('schemeOfWorkID');
Route::get('/scheme-of-work/{schemeOfWorkID}', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'viewScheme'])->whereNumber('schemeOfWorkID');
Route::get('/scheme-of-work/{schemeOfWorkID}/manage', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'manageScheme'])->whereNumber('schemeOfWorkID');
$post('/scheme-of-work/{schemeOfWorkID}/update', TeachersController::class, 'updateSchemeOfWork')->whereNumber('schemeOfWorkID');
$post('/scheme-of-work/{schemeOfWorkID}/remark', TeachersController::class, 'updateSchemeOfWorkRemark')->whereNumber('schemeOfWorkID');
$delete('/scheme-of-work/{schemeOfWorkID}', TeachersController::class, 'deleteSchemeOfWork')->whereNumber('schemeOfWorkID');
$get('/scheme-of-work/{schemeOfWorkID}/pdf', TeachersController::class, 'exportSchemeOfWorkPDF')->whereNumber('schemeOfWorkID');
$get('/scheme-of-work/{schemeOfWorkID}/excel', TeachersController::class, 'exportSchemeOfWorkExcel')->whereNumber('schemeOfWorkID');

// ── Teaching: lesson plans ──────────────────────────────────────────────────
Route::get('/lesson-plans', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'lessonPlans']);
$get('/lesson-plans/check-exists', TeachersController::class, 'checkLessonPlanExists');
Route::get('/lesson-plans/get', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'lessonPlanGet']);
Route::get('/lesson-plans/by-filter', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'lessonPlansByFilter']);
Route::get('/lesson-plans/by-id', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'lessonPlanById']);
Route::get('/lesson-plans/previous', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'lessonPlanPrevious']);
$post('/lesson-plans', TeachersController::class, 'storeLessonPlan');
$post('/lesson-plans/send-to-admin', TeachersController::class, 'sendLessonPlanToAdmin');
$post('/lesson-plans/bulk-pdf', TeachersController::class, 'downloadBulkLessonPlansPDF');
$post('/lesson-plans/{lessonPlanID}/update', TeachersController::class, 'updateLessonPlan')->whereNumber('lessonPlanID');
$get('/lesson-plans/{lessonPlanID}/pdf', TeachersController::class, 'downloadLessonPlanPDF')->whereNumber('lessonPlanID');

// ── Teaching: logbook ───────────────────────────────────────────────────────
Route::get('/logbook', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'logbook']);
Route::get('/logbook/{classSubjectID}', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'logbookEntries'])->whereNumber('classSubjectID');
$get('/logbook/{classSubjectID}/pdf', TeachersController::class, 'downloadLogbookPdf')->whereNumber('classSubjectID');
$post('/logbook/entry', TeachersController::class, 'saveLogbookEntry');
$post('/logbook/sign', TeachersController::class, 'signLogbook');
Route::get('/department-logbook', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'departmentLogbook']);
Route::get('/teacher/department-logbook', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'departmentLogbook']); // website path alias
Route::get('/department-logbook/data', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'departmentLogbookEntries']);
Route::get('/teacher/get-department-logbook-data', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'departmentLogbookEntries']); // website path alias
$get('/department-logbook/subclasses/{classID}', ManageClassessController::class, 'get_class_subclasses')->whereNumber('classID');

// ── Teaching: calendar ──────────────────────────────────────────────────────
Route::get('/calendar', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'calendar']);
$get('/calendar/data', CalendarController::class, 'getCalendarData');

// ── Class ───────────────────────────────────────────────────────────────────
$get('/my-class', ManageClassessController::class, 'AdmitedClasses');
Route::get('/timetable', [\App\Http\Controllers\Api\TeacherTeachingMobileApiController::class, 'timetable']);

// ── Exams ───────────────────────────────────────────────────────────────────
Route::get('/exams', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'index']);

// Add Results
Route::get('/add-results', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'addResults']);
$get('/add-results/class-subject', ResultManagementController::class, 'getClassSubjectId');

// Supervise
Route::get('/exams/supervise', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'supervise']);
Route::get('/exams/supervise/timetable', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'superviseTimetable']);
Route::get('/exams/supervise/take-attendance', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'superviseTakeAttendance']);
Route::get('/exams/supervise/students', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'superviseStudents']);
$get('/exams/supervise/hall-students/{examHallID}', ManageExaminationController::class, 'getHallStudents')->whereNumber('examHallID');
$post('/exams/{examID}/attendance', ManageExaminationController::class, 'updateExamAttendance')->whereNumber('examID');

// Exam Papers
Route::get('/exam-papers', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'examPapers']);
$get('/exam-papers/mine', ManageExaminationController::class, 'getMyExamPapers');
$post('/exam-papers', ManageExaminationController::class, 'storeExamPaper');
$get('/exam-papers/by-exam/{examID}', ManageExaminationController::class, 'getExamPapers')->whereNumber('examID');
$get('/exam-papers/available-periods', ManageExaminationController::class, 'getAvailablePeriods');
$get('/exam-papers/scheduled-subjects', ManageExaminationController::class, 'getScheduledSubjects');
$get('/exam-papers/allowed-classes/{examID}', ManageExaminationController::class, 'getExamAllowedClasses')->whereNumber('examID');
$get('/exam-papers/{examPaperID}/questions', ManageExaminationController::class, 'getExamPaperQuestions')->whereNumber('examPaperID');
$post('/exam-papers/{examPaperID}/questions', ManageExaminationController::class, 'updateExamPaperQuestions')->whereNumber('examPaperID');
$post('/exam-papers/{examPaperID}/update', ManageExaminationController::class, 'updateExamPaper')->whereNumber('examPaperID');
$post('/exam-papers/{examPaperID}/resubmit', ManageExaminationController::class, 'resubmitExamPaper')->whereNumber('examPaperID');
$get('/exam-papers/{examPaperID}/download', ManageExaminationController::class, 'downloadExamPaper')->whereNumber('examPaperID');
$delete('/exam-papers/{examPaperID}', ManageExaminationController::class, 'deleteExamPaper')->whereNumber('examPaperID');
$get('/exam-papers/status/{examID}/{classSubjectID}', TeachersController::class, 'checkExamPaperStatus')->whereNumber(['examID', 'classSubjectID']);
$get('/exam-papers/questions/{classSubjectID}/{examID}', TeachersController::class, 'getExamPaperQuestionData')->whereNumber(['classSubjectID', 'examID']);
$get('/exam-papers/summary/{examID}', ManageExaminationController::class, 'getTeacherExamPaperSummary')->whereNumber('examID');
$get('/exam-papers/{examID}/approval-chain', ManageExaminationController::class, 'viewApprovalChain')->whereNumber('examID');

// Paper Approvals
Route::get('/exam-paper-approvals', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'examPaperApprovals']);
$get('/exam-paper-approvals/filters/exams', ManageExaminationController::class, 'getExaminationsForFilter');
$get('/exam-paper-approvals/filters/subclasses', ManageExaminationController::class, 'getSubclasses');
$get('/exam-paper-approvals/filters/weeks/{examID}', ManageExaminationController::class, 'getAvailableWeeksForExam')->whereNumber('examID');
$post('/exam-paper-approvals/filters/subjects', ManageExaminationController::class, 'getClassSubjects');
$get('/exam-paper-approvals/{examPaperID}/review', ManageExaminationController::class, 'getAdminExamPaperReview')->whereNumber('examPaperID');
$post('/exam-paper-approvals/{examPaperID}', ManageExaminationController::class, 'approveRejectExamPaper')->whereNumber('examPaperID');

// Result approvals (web: /approve_result/{examID} — Academic / class teacher / coordinator)
Route::get('/result-approvals', [\App\Http\Controllers\Api\TeacherResultApprovalMobileApiController::class, 'index']);
Route::get('/result-approvals/{examID}', [\App\Http\Controllers\Api\TeacherResultApprovalMobileApiController::class, 'show'])->whereNumber('examID');
$get('/result-approvals/{examID}/chain', TeachersController::class, 'viewApprovalChain')->whereNumber('examID');
$get('/result-approvals/{examID}/filtered', TeachersController::class, 'getFilteredResultsForApproval')->whereNumber('examID');
$get('/result-approvals/{examID}/class-teacher', TeachersController::class, 'getClassTeacherApprovals')->whereNumber('examID');
$get('/result-approvals/{examID}/coordinator', TeachersController::class, 'getCoordinatorApprovals')->whereNumber('examID');
$post('/result-approvals/{examID}/submit', TeachersController::class, 'submitResultApproval')->whereNumber('examID');
$post('/result-approvals/dismiss-rejection', TeachersController::class, 'dismissExamRejectionNotification');

// Exam attendance (assigned invigilators)
Route::get('/exam-attendance', [\App\Http\Controllers\Api\TeacherExamMobileApiController::class, 'examAttendance']);
$get('/exam-attendance/data', TeachersController::class, 'getExamAttendanceData');
$get('/exam-attendance/{id}', ExamAttendanceController::class, 'getAssignmentDetailsForTeacher')->whereNumber('id');
$post('/exam-attendance/save', ExamAttendanceController::class, 'saveAttendance');
$get('/exam-attendance/template', ExamAttendanceController::class, 'downloadTemplate');

// ── HR: my permissions & approvals ──────────────────────────────────────────
$get('/permissions', TeachersController::class, 'manageTeacherPermissions');
$post('/permissions', TeachersController::class, 'storeTeacherPermission');
$get('/permission-approvals', AdminController::class, 'getPendingApprovals');
$post('/permission-approvals/approve', AdminController::class, 'approvePermission');
$post('/permission-approvals/reject', AdminController::class, 'rejectPermission');
$get('/permissions/progress', AdminController::class, 'getPermissionProgress');
$get('/permissions/{permissionID}/pdf', AdminController::class, 'exportPermissionPdf')->whereNumber('permissionID');
$get('/permissions/{permissionID}/attachment', AdminController::class, 'viewPermissionAttachment')->whereNumber('permissionID');

// ── Goals & performance ─────────────────────────────────────────────────────
Route::get('/goals', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'index']);
Route::get('/goals/team', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'team']);
Route::get('/goals/team/{id}', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'teamTask'])->whereNumber('id');
Route::get('/goals/direct', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'direct']);
Route::get('/goals/assigned', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'assigned']);
Route::get('/goals/my-tasks', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'myTasks']);
// Legacy HOD paths → same reshaped hubs
Route::get('/goals/hod/assigned', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'team']);
Route::get('/goals/hod/tasks/{id}', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'teamTask'])->whereNumber('id');
$get('/goals/hod/member-tasks/{parent_task_id}', GoalManagementController::class, 'fetchMemberTasks')->whereNumber('parent_task_id');
$get('/goals/hod/department-members', GoalManagementController::class, 'fetchDeptMembers');
$post('/goals/hod/assign-member', GoalManagementController::class, 'assignMemberStore');
$post('/goals/review/approve-subtask', GoalManagementController::class, 'approveSubtask');
$post('/goals/review/reset-marks/{id}', GoalManagementController::class, 'resetSubtaskMarks')->whereNumber('id');
$get('/goals/subtasks/{task_id}', GoalManagementController::class, 'fetchSubtasks')->whereNumber('task_id');
$get('/goals/subtask/{id}', GoalManagementController::class, 'fetchSubtaskDetails')->whereNumber('id');
$post('/goals/subtask', GoalManagementController::class, 'subtaskStore');
$post('/goals/subtask/{id}/update', GoalManagementController::class, 'updateSubtask')->whereNumber('id');
$post('/goals/subtask/{id}/submit', GoalManagementController::class, 'submitSubtask')->whereNumber('id');
$delete('/goals/subtask/{id}', GoalManagementController::class, 'deleteSubtask')->whereNumber('id');
$post('/goals/step', GoalManagementController::class, 'saveSubtaskStep');
$post('/goals/step/{id}/update', GoalManagementController::class, 'updateSubtaskStep')->whereNumber('id');
$delete('/goals/step/{id}', GoalManagementController::class, 'deleteSubtaskStep')->whereNumber('id');
$get('/goals/task/{id}', GoalManagementController::class, 'fetchTaskDetails')->whereNumber('id');
$get('/goals/notifications', GoalManagementController::class, 'getNotifications');
$post('/goals/notifications/read-all', GoalManagementController::class, 'markAllAsRead');
$post('/goals/notifications/{id}/read', GoalManagementController::class, 'markAsRead')->whereNumber('id');
Route::get('/sgpm/tasks', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'sgpmTasks']);
$post('/sgpm/tasks', SgpmTaskController::class, 'store');
$post('/sgpm/tasks/{id}/submit', SgpmTaskController::class, 'submitProgress')->whereNumber('id');
$post('/sgpm/tasks/{id}/evaluate', SgpmTaskController::class, 'evaluate')->whereNumber('id');
$post('/sgpm/subtasks', SgpmTaskController::class, 'storeSubtask');
$post('/sgpm/subtasks/{id}/submit', SgpmTaskController::class, 'submitSubtask')->whereNumber('id');
$post('/sgpm/subtasks/{id}/approve', SgpmTaskController::class, 'approveSubtask')->whereNumber('id');
$post('/sgpm/subtasks/{id}/reject', SgpmTaskController::class, 'rejectSubtask')->whereNumber('id');
Route::get('/performance/staff', [\App\Http\Controllers\Api\TeacherGoalsMobileApiController::class, 'scorecard']);
$get('/performance/hod', SgpmPerformanceController::class, 'hodDashboard');

// ── Feedback ────────────────────────────────────────────────────────────────
$get('/suggestions', TeachersController::class, 'manageTeacherFeedback');
$get('/incidents', TeachersController::class, 'manageTeacherFeedback');
$post('/feedback', TeachersController::class, 'storeTeacherFeedback');

// ── Character assessment ────────────────────────────────────────────────────
Route::get('/character-assessment', [\App\Http\Controllers\Api\TeacherCharacterAssessmentMobileApiController::class, 'index']);
Route::get('/character-assessment/students', [\App\Http\Controllers\Api\TeacherCharacterAssessmentMobileApiController::class, 'students']);
Route::get('/character-assessment/history', [\App\Http\Controllers\Api\TeacherCharacterAssessmentMobileApiController::class, 'history']);
$post('/character-assessment', CharacterAssessmentController::class, 'store_student_assessment');

// ── Black book ──────────────────────────────────────────────────────────────
Route::get('/black-book/report-form', [\App\Http\Controllers\Api\TeacherBlackBookMobileApiController::class, 'reportForm']);
Route::get('/black-book/report-student', [\App\Http\Controllers\Api\TeacherBlackBookMobileApiController::class, 'reportForm']);
$get('/black-book/students/search', BlackBookController::class, 'searchStudents');
$get('/black-book/problem-categories', BlackBookController::class, 'listProblemCategories');
$get('/black-book/punishment-categories', BlackBookController::class, 'listPunishmentCategories');
$get('/black-book/actors', BlackBookController::class, 'listBlackBookActors');
$get('/black-book/reports', BlackBookController::class, 'listReports');
$post('/black-book/reports', BlackBookController::class, 'storeReport');
$get('/black-book/student-summary', BlackBookController::class, 'studentReportSummary');
$get('/black-book/dashboard-stats', BlackBookController::class, 'dashboardStats');
$get('/black-book/analytics', BlackBookController::class, 'reportAnalytics');
$post('/black-book/reports/{reportID}/view', BlackBookController::class, 'markViewed')->whereNumber('reportID');
$post('/black-book/reports/{reportID}/resolve', BlackBookController::class, 'resolveReport')->whereNumber('reportID');
$post('/black-book/reports/{reportID}/end-monitoring', BlackBookController::class, 'endMonitoring')->whereNumber('reportID');

// ── Black book management (only when has_black_book_access) ─────────────────
$get('/black-book/manage', BlackBookController::class, 'manageBlackBook');
$get('/black-book/export-pdf', BlackBookController::class, 'exportPdf');
$post('/black-book/actors', BlackBookController::class, 'storeBlackBookActor');
$post('/black-book/actors/{actorID}/update', BlackBookController::class, 'updateBlackBookActor')->whereNumber('actorID');
$post('/black-book/actors/{actorID}/delete', BlackBookController::class, 'deleteBlackBookActor')->whereNumber('actorID');
$post('/black-book/problem-categories', BlackBookController::class, 'storeProblemCategory');
$post('/black-book/problem-categories/{categoryID}/update', BlackBookController::class, 'updateProblemCategory')->whereNumber('categoryID');
$post('/black-book/problem-categories/{categoryID}/delete', BlackBookController::class, 'deleteProblemCategory')->whereNumber('categoryID');
$post('/black-book/punishment-categories', BlackBookController::class, 'storePunishmentCategory');
$post('/black-book/punishment-categories/{punishmentCategoryID}/update', BlackBookController::class, 'updatePunishmentCategory')->whereNumber('punishmentCategoryID');
$post('/black-book/punishment-categories/{punishmentCategoryID}/delete', BlackBookController::class, 'deletePunishmentCategory')->whereNumber('punishmentCategoryID');

// ── Inventory / store management (only when has_inventory_access) ───────────
$get('/inventory', StoreController::class, 'manageStore');
$get('/inventory/dashboard', StoreController::class, 'storeDashboard');
$get('/inventory/export-pdf', StoreController::class, 'exportStorePdf');
$get('/inventory/reports/all', StoreController::class, 'allInventoryReport');
$get('/inventory/reports/requisitions', StoreController::class, 'requisitionReport');
$get('/inventory/reports/specific-item', StoreController::class, 'specificItemReport');
$get('/inventory/actors', StoreController::class, 'listInventoryActors');
$post('/inventory/actors', StoreController::class, 'storeInventoryActor');
$post('/inventory/actors/{actorID}/update', StoreController::class, 'updateInventoryActor')->whereNumber('actorID');
$post('/inventory/actors/{actorID}/delete', StoreController::class, 'deleteInventoryActor')->whereNumber('actorID');
$post('/inventory/categories', StoreController::class, 'storeCategory');
$post('/inventory/categories/{categoryID}/update', StoreController::class, 'updateCategory')->whereNumber('categoryID');
$post('/inventory/categories/{categoryID}/delete', StoreController::class, 'deleteCategory')->whereNumber('categoryID');
$post('/inventory/measurement-types', StoreController::class, 'storeMeasurementType');
$post('/inventory/measurement-types/{measurementTypeID}/update', StoreController::class, 'updateMeasurementType')->whereNumber('measurementTypeID');
$post('/inventory/measurement-types/{measurementTypeID}/delete', StoreController::class, 'deleteMeasurementType')->whereNumber('measurementTypeID');
$get('/inventory/measurement-types/{measurementTypeID}/units', StoreController::class, 'getUnitsByType')->whereNumber('measurementTypeID');
$post('/inventory/units', StoreController::class, 'storeUnit');
$post('/inventory/units/{unitID}/update', StoreController::class, 'updateUnit')->whereNumber('unitID');
$post('/inventory/units/{unitID}/delete', StoreController::class, 'deleteUnit')->whereNumber('unitID');
$post('/inventory/items', StoreController::class, 'storeItem');
$post('/inventory/items/{itemID}/update', StoreController::class, 'updateItem')->whereNumber('itemID');
$post('/inventory/items/{itemID}/delete', StoreController::class, 'deleteItem')->whereNumber('itemID');
$post('/inventory/packages/bulk', StoreController::class, 'storePackagesBulk');
$post('/inventory/packages/{packageID}/update', StoreController::class, 'updatePackage')->whereNumber('packageID');
$post('/inventory/packages/{packageID}/delete', StoreController::class, 'deletePackage')->whereNumber('packageID');
$post('/inventory/stock', StoreController::class, 'storeItemStock');
$post('/inventory/stock/{stockEntryID}/delete', StoreController::class, 'deleteItemStock')->whereNumber('stockEntryID');
$post('/inventory/requisitions', StoreController::class, 'storeRequisition');
$post('/inventory/requisitions/{requisitionID}/return', StoreController::class, 'returnRequisition')->whereNumber('requisitionID');
$post('/inventory/item-requests/mark-read', StoreController::class, 'markItemRequestsRead');
$post('/inventory/item-requests/{requestID}/approve', StoreController::class, 'approveItemRequest')->whereNumber('requestID');
$post('/inventory/item-requests/{requestID}/reject', StoreController::class, 'rejectItemRequest')->whereNumber('requestID');
$post('/inventory/incidents', StoreController::class, 'storeIncident');
$post('/inventory/incidents/{incidentID}/update', StoreController::class, 'updateIncident')->whereNumber('incidentID');
$post('/inventory/incidents/{incidentID}/resolve', StoreController::class, 'resolveIncident')->whereNumber('incidentID');
$post('/inventory/incidents/{incidentID}/delete', StoreController::class, 'deleteIncident')->whereNumber('incidentID');

// ── Duty book ───────────────────────────────────────────────────────────────
Route::get('/duty-book', [\App\Http\Controllers\Api\TeacherDutyBookMobileApiController::class, 'index']);
$get('/duty-book/report', TeacherDutyController::class, 'getDailyReport');
$post('/duty-book/report', TeacherDutyController::class, 'saveDailyReport');
$get('/duty-book/parent-permissions', TeacherDutyController::class, 'parentPermissions');
$get('/duty-book/pdf', TeacherDutyController::class, 'export_pdf');
$get('/duty-book/report/pdf', TeacherDutyController::class, 'exportDailyReportPdf');

// ── Learning portal (notes, books, quizzes) ─────────────────────────────────
$get('/learning-portal', StudentPortalMaterialController::class, 'index');
$post('/learning-portal/notes', StudentPortalMaterialController::class, 'storeNote');
$post('/learning-portal/notes/{id}/toggle', StudentPortalMaterialController::class, 'toggleNote')->whereNumber('id');
$delete('/learning-portal/notes/{id}', StudentPortalMaterialController::class, 'deleteNote')->whereNumber('id');
$post('/learning-portal/books', StudentPortalMaterialController::class, 'storeBook');
$post('/learning-portal/books/{id}/toggle', StudentPortalMaterialController::class, 'toggleBook')->whereNumber('id');
$delete('/learning-portal/books/{id}', StudentPortalMaterialController::class, 'deleteBook')->whereNumber('id');
$post('/learning-portal/quizzes', StudentPortalMaterialController::class, 'storeQuiz');
$web('PUT', '/learning-portal/quizzes/{id}', StudentPortalMaterialController::class, 'updateQuiz')->whereNumber('id');
$post('/learning-portal/quizzes/{id}/toggle', StudentPortalMaterialController::class, 'toggleQuiz')->whereNumber('id');
$delete('/learning-portal/quizzes/{id}', StudentPortalMaterialController::class, 'deleteQuiz')->whereNumber('id');
$get('/learning-portal/quizzes/{quizId}/attempts/{attemptId}', StudentPortalMaterialController::class, 'reviewAttempt')->whereNumber(['quizId', 'attemptId']);
$post('/learning-portal/quizzes/{quizId}/attempts/{attemptId}/mark', StudentPortalMaterialController::class, 'markAttempt')->whereNumber(['quizId', 'attemptId']);
$get('/learning-portal/results/export', StudentPortalMaterialController::class, 'exportResults');
$get('/learning-portal/results/export/{quizId}', StudentPortalMaterialController::class, 'exportResults')->whereNumber('quizId');

// ── Store requisitions (Main → Requisitions; always available) ──────────────
Route::get('/store-requisitions', [\App\Http\Controllers\Api\TeacherStoreRequisitionsMobileApiController::class, 'index']);
$post('/store-requisitions', StorePortalController::class, 'teacherStoreRequest');
$get('/store-requisitions/pdf', StorePortalController::class, 'teacherExportPdf');

// ── Management hubs (permission_category gated; CRUD siblings in management table) ─
Route::get('/management', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'index']);
Route::get('/manageExamination', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'examinations']);
Route::get('/manageResults', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'results']);
Route::get('/manageSubjects', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'subjects']);
Route::get('/manageClasses', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'classes']);
Route::get('/manage_student', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'students']);
Route::get('/timeTable', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'timetable']);
Route::get('/manage_fees', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'fees']);
Route::get('/manage_library', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'library']);
Route::get('/manage_accomodation', [\App\Http\Controllers\Api\TeacherManagementMobileApiController::class, 'accommodation']);

// ── Management modules (sidebar "Management" menu), generated table ──────────
require __DIR__ . '/mobile_teacher_management.php';
