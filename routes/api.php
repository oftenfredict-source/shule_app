<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\BoardMobileApiController;
use App\Http\Controllers\Api\ParentMobileApiController;
use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\AccomodationController;
use App\Http\Controllers\SchoolAgentDeviceController;
use App\Http\Controllers\SchoolAgentAttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Base URL: http://192.168.100.104/api
|
*/

// Health check endpoint - No authentication required
Route::get('/health', [ApiController::class, 'healthCheck'])->name('api.health');

// Authentication endpoints - No authentication required
Route::post('/login', [ApiController::class, 'login'])->name('api.login');
Route::post('/logout', [ApiController::class, 'logout'])->name('api.logout');

/*
| Parent mobile app (Bearer token).
| Hand-off for the app developer: docs/PARENT_MOBILE_API.md
*/
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [ApiController::class, 'login'])->name('api.v1.auth.login');

    Route::middleware('mobile.auth')->group(function () {
        Route::get('/auth/me', [ParentMobileApiController::class, 'me'])->name('api.v1.auth.me');
        Route::post('/auth/logout', [ParentMobileApiController::class, 'logout'])->name('api.v1.auth.logout');
        Route::post('/auth/password', [ParentMobileApiController::class, 'changePassword'])->name('api.v1.auth.password');
    });

    Route::middleware(['mobile.auth:parent', \App\Http\Middleware\RequireAcademicYear::class])->prefix('parent')->group(function () {
        Route::get('/profile', [ParentMobileApiController::class, 'me'])->name('api.v1.parent.profile');
        Route::get('/dashboard', [ParentMobileApiController::class, 'dashboard'])->name('api.v1.parent.dashboard');
        Route::get('/services', [ParentMobileApiController::class, 'services'])->name('api.v1.parent.services');
        Route::get('/children', [ParentMobileApiController::class, 'childrenList'])->name('api.v1.parent.children');

        Route::get('/academic-years', [ParentMobileApiController::class, 'academicYears'])->name('api.v1.parent.academic_years');
        Route::get('/exams', [ParentMobileApiController::class, 'publishedExams'])->name('api.v1.parent.exams');
        Route::get('/results', [ParentMobileApiController::class, 'results'])->name('api.v1.parent.results');

        Route::get('/attendance', [ParentMobileApiController::class, 'attendance'])->name('api.v1.parent.attendance');
        Route::get('/attendance/overview', [ParentMobileApiController::class, 'attendanceOverview'])->name('api.v1.parent.attendance.overview');
        Route::get('/attendance/students/{studentID}', [ParentMobileApiController::class, 'attendanceCalendar'])->whereNumber('studentID')->name('api.v1.parent.attendance.calendar');

        Route::get('/fingerprint-attendance', [ParentMobileApiController::class, 'fingerprintOverview'])->name('api.v1.parent.fingerprint.overview');
        Route::get('/fingerprint-attendance/students/{studentID}', [ParentMobileApiController::class, 'fingerprintCalendar'])->whereNumber('studentID')->name('api.v1.parent.fingerprint.calendar');

        Route::get('/payments', [ParentMobileApiController::class, 'payments'])->name('api.v1.parent.payments');
        Route::get('/fees-summary', [ParentMobileApiController::class, 'feesSummary'])->name('api.v1.parent.fees');
        Route::post('/students/{studentID}/control-number', [ParentMobileApiController::class, 'requestControlNumber'])->whereNumber('studentID')->name('api.v1.parent.control_number');

        Route::get('/students/{studentID}/subjects', [ParentMobileApiController::class, 'subjects'])->whereNumber('studentID')->name('api.v1.parent.subjects');
        Route::post('/subjects/elect', [ParentMobileApiController::class, 'electSubject'])->name('api.v1.parent.subjects.elect');
        Route::post('/subjects/deselect', [ParentMobileApiController::class, 'deselectSubject'])->name('api.v1.parent.subjects.deselect');

        Route::get('/learning', [ParentMobileApiController::class, 'learningChildren'])->name('api.v1.parent.learning');
        Route::get('/learning/students/{studentID}/subjects', [ParentMobileApiController::class, 'learningSubjects'])->whereNumber('studentID')->name('api.v1.parent.learning.subjects');
        Route::get('/learning/students/{studentID}/subjects/{classSubjectID}', [ParentMobileApiController::class, 'learningNotes'])->whereNumber('studentID')->whereNumber('classSubjectID')->name('api.v1.parent.learning.notes');
        Route::get('/files/notes/{noteID}', [ParentMobileApiController::class, 'noteFile'])->whereNumber('noteID')->name('api.v1.parent.files.notes');

        Route::get('/home-packages', [ParentMobileApiController::class, 'homePackages'])->name('api.v1.parent.home_packages');
        Route::get('/home-packages/holidays/{holidayID}', [ParentMobileApiController::class, 'holidayPackages'])->whereNumber('holidayID')->name('api.v1.parent.home_packages.holiday');
        Route::get('/files/home-packages/{homePackageID}', [ParentMobileApiController::class, 'homePackageFile'])->whereNumber('homePackageID')->name('api.v1.parent.files.home_packages');

        Route::get('/notifications', [ParentMobileApiController::class, 'notifications'])->name('api.v1.parent.notifications');
        Route::post('/notifications/read', [ParentMobileApiController::class, 'markNotificationsRead'])->name('api.v1.parent.notifications.read');

        Route::get('/permissions', [ParentMobileApiController::class, 'permissions'])->name('api.v1.parent.permissions');
        Route::post('/permissions', [ParentMobileApiController::class, 'storePermission'])->name('api.v1.parent.permissions.store');
        Route::post('/permissions/read', [ParentMobileApiController::class, 'markPermissionsRead'])->name('api.v1.parent.permissions.read');
    });

    /*
    | Board member mobile app (Bearer token). Mirrors /board-portal on the website.
    | Hand-off for the app developer: docs/BOARD_MOBILE_API.md
    */
    Route::middleware(['mobile.auth:teacher', \App\Http\Middleware\RequireAcademicYear::class])->prefix('teacher')->group(base_path('routes/mobile_teacher.php'));

    /*
    | Watchman mobile app (Bearer token). Mirrors /watchman/visitors.
    | Hand-off: docs/WATCHMAN_MOBILE_API.md
    */
    Route::middleware('mobile.auth:watchman')->prefix('watchman')->group(base_path('routes/mobile_watchman.php'));

    Route::middleware('mobile.auth:board')->prefix('board')->group(function () {
        Route::get('/profile', [BoardMobileApiController::class, 'profile'])->name('api.v1.board.profile');
        Route::get('/dashboard', [BoardMobileApiController::class, 'dashboard'])->name('api.v1.board.dashboard');

        Route::get('/messages', [BoardMobileApiController::class, 'messages'])->name('api.v1.board.messages');
        Route::get('/messages/{messageID}', [BoardMobileApiController::class, 'messageShow'])->whereNumber('messageID')->name('api.v1.board.messages.show');
        Route::post('/messages/compose', [BoardMobileApiController::class, 'composeMessage'])->name('api.v1.board.messages.compose');
        Route::post('/messages/reply', [BoardMobileApiController::class, 'replyMessage'])->name('api.v1.board.messages.reply');
        Route::post('/messages/mark-read', [BoardMobileApiController::class, 'markMessageRead'])->name('api.v1.board.messages.mark_read');

        Route::get('/members', [BoardMobileApiController::class, 'members'])->name('api.v1.board.members');
        Route::post('/members/message', [BoardMobileApiController::class, 'sendMemberMessage'])->name('api.v1.board.members.message');

        Route::get('/committees', [BoardMobileApiController::class, 'committees'])->name('api.v1.board.committees');
        Route::post('/committees', [BoardMobileApiController::class, 'storeCommittee'])->name('api.v1.board.committees.store');

        Route::get('/meetings', [BoardMobileApiController::class, 'meetings'])->name('api.v1.board.meetings');
        Route::get('/meetings/{meetingID}', [BoardMobileApiController::class, 'meetingShow'])->whereNumber('meetingID')->name('api.v1.board.meetings.show');
        Route::post('/meetings', [BoardMobileApiController::class, 'storeMeeting'])->name('api.v1.board.meetings.store');
        Route::post('/meetings/comment', [BoardMobileApiController::class, 'storeMeetingComment'])->name('api.v1.board.meetings.comment');

        Route::get('/minutes', [BoardMobileApiController::class, 'minutes'])->name('api.v1.board.minutes');
        Route::post('/minutes', [BoardMobileApiController::class, 'storeMinute'])->name('api.v1.board.minutes.store');

        Route::get('/resolutions', [BoardMobileApiController::class, 'resolutions'])->name('api.v1.board.resolutions');
        Route::post('/resolutions', [BoardMobileApiController::class, 'storeResolution'])->name('api.v1.board.resolutions.store');

        Route::get('/action-items', [BoardMobileApiController::class, 'actionItems'])->name('api.v1.board.action_items');
        Route::post('/action-items', [BoardMobileApiController::class, 'storeActionItem'])->name('api.v1.board.action_items.store');
        Route::post('/action-items/status', [BoardMobileApiController::class, 'updateActionItemStatus'])->name('api.v1.board.action_items.status');

        Route::get('/documents', [BoardMobileApiController::class, 'documents'])->name('api.v1.board.documents');
        Route::post('/documents', [BoardMobileApiController::class, 'storeDocument'])->name('api.v1.board.documents.store');
        Route::get('/documents/{documentID}/download', [BoardMobileApiController::class, 'documentFile'])->whereNumber('documentID')->name('api.v1.board.documents.download');
    });
});

// Flutter/mobile - open web blades (parent portal, etc.) inside WebView with session cookie
Route::post('/mobile/web-session', [\App\Http\Controllers\Auth::class, 'createMobileWebSession'])
    ->middleware('web')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->name('api.mobile.web_session');

// School fingerprint sync agent (Admin only, token-based)
Route::post('/school-agent/login', [\App\Http\Controllers\Auth::class, 'schoolAgentLoginApi'])->name('api.school_agent.login');
Route::get('/school-agent/verify', [\App\Http\Controllers\Auth::class, 'schoolAgentVerifyToken'])->name('api.school_agent.verify');

Route::middleware('school.agent')->prefix('school-agent')->group(function () {
    Route::get('/devices', [SchoolAgentDeviceController::class, 'index'])->name('api.school_agent.devices.index');
    Route::post('/devices', [SchoolAgentDeviceController::class, 'store'])->name('api.school_agent.devices.store');
    Route::get('/devices/{id}', [SchoolAgentDeviceController::class, 'show'])->name('api.school_agent.devices.show');
    Route::put('/devices/{id}', [SchoolAgentDeviceController::class, 'update'])->name('api.school_agent.devices.update');
    Route::delete('/devices/{id}', [SchoolAgentDeviceController::class, 'destroy'])->name('api.school_agent.devices.destroy');

    // Local attendance-agent (school LAN PC) ingests teacher/staff live punches here.
    Route::post('/attendance/teacher-staff-live', [SchoolAgentAttendanceController::class, 'ingestTeacherStaffLive'])
        ->name('api.school_agent.attendance.teacher_staff_live.ingest');

    Route::post('/attendance/teacher-staff-sync', [SchoolAgentAttendanceController::class, 'syncTeacherStaffBulk'])
        ->name('api.school_agent.attendance.teacher_staff_sync');

    // Local attendance-agent (school LAN PC) ingests student live punches here.
    Route::post('/attendance/student-live', [SchoolAgentAttendanceController::class, 'ingestStudentLive'])
        ->name('api.school_agent.attendance.student_live.ingest');

    Route::post('/attendance/student-sync', [SchoolAgentAttendanceController::class, 'syncStudentBulk'])
        ->name('api.school_agent.attendance.student_sync');
});

// User profile endpoint
Route::get('/user/profile', [ApiController::class, 'getUserProfile'])->name('api.user.profile');

// Parent Dashboard endpoint
Route::get('/parent/dashboard', [ApiController::class, 'parentDashboard'])->name('api.parent.dashboard');

// Parent Results endpoint
Route::get('/parent/results', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentResults'])->name('api.parent.results');
Route::post('/parent/results', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentResults'])->name('api.parent.results.post');

// Parent Payments endpoint
Route::get('/parent/payments', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentPayments'])->name('api.parent.payments');
Route::post('/parent/payments', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentPayments'])->name('api.parent.payments.post');

// Parent Fees Summary endpoint
Route::get('/parent/fees-summary', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentFeesSummary'])->name('api.parent.fees.summary');
Route::post('/parent/fees-summary', [\App\Http\Controllers\ParentsContoller::class, 'apiGetParentFeesSummary'])->name('api.parent.fees.summary.post');

// Attendance API endpoint - No authentication required (for external biometric device)
Route::post('/attendance/record', [AttendanceApiController::class, 'recordAttendance'])->name('api.attendance.record');

// Sync attendance from external biometric system
Route::get('/attendance/sync-external', [AttendanceApiController::class, 'syncFromExternal'])->name('api.attendance.sync_external');

// List raw external attendance records (no local filtering)
Route::get('/attendance/external-list', [AttendanceApiController::class, 'listExternal'])->name('api.attendance.external_list');

// Get fingerprint attendance from local database (student_fingerprint_attendance table)
Route::get('/attendance/local-fingerprint', [AttendanceApiController::class, 'getLocalFingerprintAttendance'])->name('api.attendance.local_fingerprint');

// List external attendance records filtered to students that exist in this system
Route::get('/attendance/external-students', [AttendanceApiController::class, 'listExternalStudents'])->name('api.attendance.external_students');

// List external attendance records filtered to teachers that exist in this system
Route::get('/attendance/external-teachers', [AttendanceApiController::class, 'listExternalTeachers'])->name('api.attendance.external_teachers');

// Get all attendance records from external API filtered to teachers only
Route::get('/attendance/all-teachers', [AttendanceApiController::class, 'getAllAttendanceTeachers'])->name('api.attendance.all_teachers');

// Get teacher fingerprint attendance from local database (teacher_fingerprint_attendance table)
Route::get('/attendance/teachers-fingerprint-local', [AttendanceApiController::class, 'getTeacherFingerprintAttendanceLocal'])->name('api.attendance.teachers_fingerprint_local');

// Get teacher fingerprint attendance from local database
Route::get('/attendance/teachers-fingerprint', [AttendanceApiController::class, 'getTeacherFingerprintAttendance'])->name('api.attendance.teachers_fingerprint');

// Teacher + staff fingerprint attendance from local database (school-scoped)
Route::get('/attendance/teacher-staff-fingerprint', [AttendanceApiController::class, 'getTeacherStaffFingerprintAttendance'])->name('api.attendance.teacher_staff_fingerprint');

// Teacher Dashboard endpoint
Route::get('/teacher/dashboard', [\App\Http\Controllers\TeachersController::class, 'getTeacherDashboardAPI'])->name('api.teacher.dashboard');

// Teacher Profile endpoints
Route::get('/teacher/profile', [\App\Http\Controllers\TeachersController::class, 'getTeacherProfileAPI'])->name('api.teacher.profile');
Route::post('/teacher/profile/update', [\App\Http\Controllers\TeachersController::class, 'updateTeacherProfileAPI'])->name('api.teacher.profile.update');
Route::post('/teacher/password/change', [\App\Http\Controllers\TeachersController::class, 'changeTeacherPasswordAPI'])->name('api.teacher.password.change');

// Teacher Subjects endpoints
Route::get('/teacher/subjects', [\App\Http\Controllers\TeachersController::class, 'getTeacherSubjectsAPI'])->name('api.teacher.subjects');
Route::get('/teacher/subjects/{classSubjectID}/students', [\App\Http\Controllers\TeachersController::class, 'getSubjectStudentsAPI'])->name('api.teacher.subjects.students');
Route::get('/teacher/subjects/{classSubjectID}/examinations', [\App\Http\Controllers\TeachersController::class, 'getExaminationsForSubjectAPI'])->name('api.teacher.subjects.examinations');
Route::get('/teacher/subjects/{classSubjectID}/results', [\App\Http\Controllers\TeachersController::class, 'getSubjectResultsAPI'])->name('api.teacher.subjects.results');
Route::get('/teacher/subjects/{classSubjectID}/results/{examID}', [\App\Http\Controllers\TeachersController::class, 'getSubjectResultsAPI'])->name('api.teacher.subjects.results.by_exam');
Route::post('/teacher/subjects/results/save', [\App\Http\Controllers\TeachersController::class, 'saveSubjectResultsAPI'])->name('api.teacher.subjects.results.save');
Route::post('/teacher/subjects/results/upload-excel', [\App\Http\Controllers\TeachersController::class, 'uploadExcelResultsAPI'])->name('api.teacher.subjects.results.upload_excel');
Route::get('/teacher/subjects/{classSubjectID}/results/{examID}/download-excel-template', [\App\Http\Controllers\TeachersController::class, 'downloadExcelTemplateAPI'])->name('api.teacher.subjects.results.download_excel_template');

// Teacher Session Attendance endpoints
Route::get('/teacher/subjects/{classSubjectID}/session-attendance', [\App\Http\Controllers\TeachersController::class, 'getSessionAttendanceDataAPI'])->name('api.teacher.subjects.session_attendance');

// Teacher Exam Attendance endpoints
Route::get('/teacher/subjects/{classSubjectID}/exam-attendance', [\App\Http\Controllers\TeachersController::class, 'getExamAttendanceDataAPI'])->name('api.teacher.subjects.exam_attendance');

// Teacher My Sessions endpoints
Route::get('/teacher/my-sessions', [\App\Http\Controllers\TeachersController::class, 'getTeacherMySessionsAPI'])->name('api.teacher.my_sessions');
Route::get('/teacher/my-sessions/students', [\App\Http\Controllers\TeachersController::class, 'getSessionStudentsAPI'])->name('api.teacher.my_sessions.students');
Route::get('/teacher/my-sessions/attendance', [\App\Http\Controllers\TeachersController::class, 'getSessionAttendanceForUpdateAPI'])->name('api.teacher.my_sessions.attendance');
Route::post('/teacher/my-sessions/attendance', [\App\Http\Controllers\TeachersController::class, 'collectSessionAttendanceAPI'])->name('api.teacher.my_sessions.attendance.save');
Route::post('/teacher/my-sessions/tasks', [\App\Http\Controllers\TeachersController::class, 'assignSessionTaskAPI'])->name('api.teacher.my_sessions.tasks.assign');

// Accommodation management (admin web session)
Route::middleware('web')->group(function () {
    Route::get('/accommodation/blocks', [AccomodationController::class, 'apiBlocksIndex']);
    Route::post('/accommodation/blocks', [AccomodationController::class, 'apiBlocksStore']);
    Route::put('/accommodation/blocks', [AccomodationController::class, 'apiBlocksUpdate']);
    Route::delete('/accommodation/blocks/{blockID}', [AccomodationController::class, 'apiBlocksDestroy']);

    Route::get('/accommodation/rooms', [AccomodationController::class, 'apiRoomsIndex']);
    Route::post('/accommodation/rooms', [AccomodationController::class, 'apiRoomsStore']);
    Route::put('/accommodation/rooms', [AccomodationController::class, 'apiRoomsUpdate']);
    Route::delete('/accommodation/rooms/{roomID}', [AccomodationController::class, 'apiRoomsDestroy']);

    Route::get('/accommodation/beds', [AccomodationController::class, 'apiBedsIndex']);
    Route::post('/accommodation/beds', [AccomodationController::class, 'apiBedsStore']);
    Route::put('/accommodation/beds', [AccomodationController::class, 'apiBedsUpdate']);
    Route::delete('/accommodation/beds/{bedID}', [AccomodationController::class, 'apiBedsDestroy']);

    Route::get('/accommodation/bed-assignments', [AccomodationController::class, 'apiBedAssignmentsIndex']);
    Route::get('/accommodation/students', [AccomodationController::class, 'apiSearchStudents']);
    Route::post('/accommodation/beds/assign', [AccomodationController::class, 'apiAssignStudentToBed']);
    Route::post('/accommodation/beds/release', [AccomodationController::class, 'apiReleaseStudentFromBed']);
    Route::post('/accommodation/beds/move', [AccomodationController::class, 'apiMoveStudentToBed']);
    Route::post('/accommodation/blocks/{blockID}/remove-students', [AccomodationController::class, 'apiRemoveStudentsFromBlock']);
});


