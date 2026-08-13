<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ManageClassessController;
use App\Http\Controllers\ManageExaminationController;
use App\Http\Controllers\ManageParentsController;
use App\Http\Controllers\ResultManagementController;
use App\Http\Controllers\ManageStudentController;
use App\Http\Controllers\ManageSubjectController;
use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\ManageTeachersController;
use App\Http\Controllers\ManageOtherStaffController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\SMS_InformationController;
use App\Http\Controllers\AccomodationController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\HomePackageController;
use App\Http\Controllers\ManageAttendanceController;
use App\Http\Controllers\AcademicYearsController;
use App\Http\Controllers\SelectedAcademicYearController;
use App\Http\Controllers\ParentsContoller;
use App\Http\Controllers\ZKTecoPushController;
use App\Http\Controllers\ZKTecoController;
use App\Http\Controllers\GradeDefinitionController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\OnlineApplicationController;
use App\Http\Controllers\OnlineApplicationAuthController;
use App\Http\Controllers\OnlineApplicationDashboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StudentRegistrationController;
use App\Http\Controllers\WatchmanController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\AccountantReportController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\IncomeCategoryController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\BlackBookController;
use App\Http\Controllers\StorePortalController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\GoalManagementController;
use App\Http\Controllers\AttendanceSmsController;
use App\Http\Controllers\FingerprintAttendanceSmsController;
use App\Http\Controllers\FingerprintAttendanceSetupController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CharacterAssessmentController;
use App\Http\Controllers\ExamAttendanceController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Goal Management Routes
Route::prefix("goals")->group(function () {
    // Admin
    Route::get("/create", [
        GoalManagementController::class,
        "createGoal",
    ])->name("admin.goals.create");
    Route::post("/store", [GoalManagementController::class, "storeGoal"])->name(
        "admin.goals.store",
    );
    Route::get("/list", [GoalManagementController::class, "goalList"])->name(
        "admin.goals.index",
    );
    Route::get("/reports", [GoalManagementController::class, "goalList"])->name(
        "admin.goals.reports",
    ); // Reuse list for now
    Route::post("/assign-task", [
        GoalManagementController::class,
        "assignTask",
    ])->name("admin.goals.assignTask");
    Route::get("/view/{id}", [
        GoalManagementController::class,
        "showGoal",
    ])->name("admin.goals.show");
    Route::get("/edit/{id}", [
        GoalManagementController::class,
        "editGoal",
    ])->name("admin.goals.edit");
    Route::post("/update/{id}", [
        GoalManagementController::class,
        "updateGoal",
    ])->name("admin.goals.update");
    Route::delete("/delete/{id}", [
        GoalManagementController::class,
        "deleteGoal",
    ])->name("admin.goals.delete");
    Route::get("/tasks/{goal_id}", [
        GoalManagementController::class,
        "fetchGoalTasks",
    ])->name("admin.goals.fetchTasks");
    Route::post("/task/update/{id}", [
        GoalManagementController::class,
        "updateTask",
    ])->name("admin.goals.updateTask");
    Route::delete("/task/delete/{id}", [
        GoalManagementController::class,
        "deleteTask",
    ])->name("admin.goals.deleteTask");
    Route::get("/task/details/{id}", [
        GoalManagementController::class,
        "fetchTaskDetails",
    ])->name("admin.goals.getTask");
    Route::get("/task/full-structure/{id}", [
        GoalManagementController::class,
        "fetchTaskFullStructure",
    ])->name("admin.goals.taskFullStructure");
    Route::get("/fetch-targets/{type}", [
        GoalManagementController::class,
        "fetchTargets",
    ])->name("goals.fetchTargets");

    // HOD
    Route::get("/hod/assigned", [
        GoalManagementController::class,
        "hodAssignedTasks",
    ])->name("hod.goals.assigned");
    Route::get("/hod/view-task/{id}", [
        GoalManagementController::class,
        "showHODTaskDetails",
    ])->name("hod.goals.viewTask");
    Route::get("/hod/fetch-member-tasks/{parent_task_id}", [
        GoalManagementController::class,
        "fetchMemberTasks",
    ]);
    Route::get("/fetch-dept-members", [
        GoalManagementController::class,
        "fetchDeptMembers",
    ])->name("goals.fetchDeptMembers");
    Route::post("/hod/assign-member-store", [
        GoalManagementController::class,
        "assignMemberStore",
    ])->name("hod.goals.assignMemberStore");
    Route::get("/hod/assign-members", [
        GoalManagementController::class,
        "hodAssignedTasks",
    ])->name("hod.goals.assignMebers");
    Route::get("/hod/progress", [
        GoalManagementController::class,
        "hodAssignedTasks",
    ])->name("hod.goals.progress");
    Route::get("/hod/review", [
        GoalManagementController::class,
        "hodAssignedTasks",
    ])->name("hod.goals.review");

    // Member
    Route::get("/my-tasks", [
        GoalManagementController::class,
        "memberTasks",
    ])->name("member.goals.myTasks");
    Route::get("/fetch-subtasks/{member_task_id}", [
        GoalManagementController::class,
        "fetchSubtasks",
    ]);
    Route::get("/fetch-subtask-details/{id}", [
        GoalManagementController::class,
        "fetchSubtaskDetails",
    ]);
    Route::post("/member/subtask-store", [
        GoalManagementController::class,
        "subtaskStore",
    ])->name("member.goals.subtaskStore");
    Route::post("/member/update-subtask/{id}", [
        GoalManagementController::class,
        "updateSubtask",
    ]);
    Route::delete("/member/delete-subtask/{id}", [
        GoalManagementController::class,
        "deleteSubtask",
    ]);

    Route::post("/member/save-step", [
        GoalManagementController::class,
        "saveSubtaskStep",
    ]);
    Route::post("/member/update-step/{id}", [
        GoalManagementController::class,
        "updateSubtaskStep",
    ]);
    Route::delete("/member/delete-step/{id}", [
        GoalManagementController::class,
        "deleteSubtaskStep",
    ]);

    Route::post("/member/submit-subtask/{id}", [
        GoalManagementController::class,
        "submitSubtask",
    ]);

    // Review Actions
    Route::post("/review/approve-subtask", [
        GoalManagementController::class,
        "approveSubtask",
    ])->name("goals.review.approve");
    Route::post("/review/reset-marks/{id}", [
        GoalManagementController::class,
        "resetSubtaskMarks",
    ]);

    // Notifications
    Route::get("/notifications", [
        GoalManagementController::class,
        "getNotifications",
    ])->name("goals.notifications");
    Route::post("/notifications/read/{id}", [
        GoalManagementController::class,
        "markAsRead",
    ])->name("goals.notifications.read");
    Route::post("/notifications/read-all", [
        GoalManagementController::class,
        "markAllAsRead",
    ])->name("goals.notifications.read_all");
});

Route::get("/", [SiteController::class, "home"])->name("site.home");
Route::get("/about", [SiteController::class, "about"])->name("site.about");
Route::get("/team", [SiteController::class, "team"])->name("site.team");
Route::get("/team/{slug}", [SiteController::class, "teamShow"])
    ->where("slug", "[a-z0-9\-]+")
    ->name("site.team.member");
Route::get("/services", [SiteController::class, "services"])->name("site.services");
Route::get("/modules", [SiteController::class, "modulesIndex"])->name("site.modules");
Route::get("/modules/{slug}", [SiteController::class, "moduleShow"])
    ->where("slug", "[a-z0-9\-]+")
    ->name("site.module");
Route::get("/pricing", [SiteController::class, "pricing"])->name("pricing");
Route::get("/faq", [SiteController::class, "faq"])->name("site.faq");
Route::get("/download", [SiteController::class, "download"])->name("site.download");
Route::get("/contact", [SiteController::class, "contact"])->name("site.contact");
Route::post("/contact/send-otp", [SiteController::class, "contactSendOtp"])
    ->middleware("throttle:5,1")
    ->name("site.contact.send_otp");
Route::post("/contact/verify-otp", [SiteController::class, "contactVerifyOtp"])
    ->middleware("throttle:10,1")
    ->name("site.contact.verify_otp");
Route::post("/contact", [SiteController::class, "contactSubmit"])
    ->middleware("throttle:3,10")
    ->name("site.contact.submit");

Route::get("/register-school", [SiteController::class, "registerSchool"])->name("site.register_school");
Route::post("/register-school/check-field", [SiteController::class, "registerSchoolCheckField"])
    ->middleware("throttle:30,1")
    ->name("site.register_school.check_field");
Route::post("/register-school/send-otp", [SiteController::class, "registerSchoolSendOtp"])
    ->middleware("throttle:5,1")
    ->name("site.register_school.send_otp");
Route::post("/register-school/verify-otp", [SiteController::class, "registerSchoolVerifyOtp"])
    ->middleware("throttle:10,1")
    ->name("site.register_school.verify_otp");
Route::post("/register-school", [SiteController::class, "registerSchoolSubmit"])
    ->middleware("throttle:3,10")
    ->name("site.register_school.submit");

// Browser auto-requests /favicon.ico — serve round ShuleXpert logo
Route::get("/favicon.ico", function () {
    $round = public_path("images/shuleXpert-favicon.png");
    $jpg = public_path("images/shuleXpert.jpg");
    $png = public_path("images/logo.png");
    if (is_file($round)) {
        return response()->file($round, [
            "Content-Type" => "image/png",
            "Cache-Control" => "public, max-age=604800",
        ]);
    }
    if (is_file($jpg)) {
        return response()->file($jpg, [
            "Content-Type" => "image/jpeg",
            "Cache-Control" => "public, max-age=604800",
        ]);
    }
    if (is_file($png)) {
        return response()->file($png, [
            "Content-Type" => "image/png",
            "Cache-Control" => "public, max-age=604800",
        ]);
    }
    abort(404);
});

Route::get("/shulexpert-map.xml", [SitemapController::class, "index"])->name("site.map");

// Online Application Routes
Route::get("online-application", [
    OnlineApplicationController::class,
    "index",
])->name("online_application");
Route::get("online-application/schools", [
    OnlineApplicationController::class,
    "loadSchools",
])->name("online_application.schools");
Route::get("online-application/school/{schoolID}", [
    OnlineApplicationController::class,
    "getSchoolDetails",
])->name("online_application.school_details");
Route::get("online-application/s/{token}", [
    OnlineApplicationController::class,
    "showSchoolLanding",
])->where('token', '.*')->name("online_application.school");
Route::get("online-application/fees-structure-pdf", [
    OnlineApplicationController::class,
    "exportFeesStructurePdf",
])->name("online_application.fees_structure_pdf");
Route::get("online-application/apply", [
    OnlineApplicationController::class,
    "showApplicationForm",
])->name("online_application.apply");
Route::post("online-application/apply", [
    OnlineApplicationController::class,
    "storeApplication",
])->name("online_application.store");
Route::get("online-application/application-success", [
    OnlineApplicationController::class,
    "applicationSuccess",
])->middleware('online.application.auth')->name("online_application.application_success");

// Online Application Account (separate from school users)
Route::prefix('online-application/account')->name('online_application.auth.')->group(function () {
    Route::get('register', [OnlineApplicationAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [OnlineApplicationAuthController::class, 'register']);
    Route::get('check-phone', [OnlineApplicationAuthController::class, 'checkPhone'])->name('check_phone');
    Route::get('login', [OnlineApplicationAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [OnlineApplicationAuthController::class, 'login']);
    Route::get('verify-otp/{token}', [OnlineApplicationAuthController::class, 'showVerifyOtp'])->name('verify_otp');
    Route::post('verify-otp/{token}', [OnlineApplicationAuthController::class, 'verifyOtp']);
    Route::post('resend-otp/{token}', [OnlineApplicationAuthController::class, 'resendOtp'])->name('resend_otp');
    Route::post('logout', [OnlineApplicationAuthController::class, 'logout'])->name('logout');
});

Route::prefix('online-application/portal')->name('online_application.portal.')->middleware('online.application.auth')->group(function () {
    Route::get('/', [OnlineApplicationDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/applications', [OnlineApplicationDashboardController::class, 'myApplications'])->name('applications');
    Route::get('/applications/{id}/edit', [OnlineApplicationDashboardController::class, 'editApplication'])->name('applications.edit');
    Route::post('/applications/{id}', [OnlineApplicationDashboardController::class, 'updateApplication'])->name('applications.update');
    Route::post('/applications/{id}/delete', [OnlineApplicationDashboardController::class, 'deleteApplication'])->name('applications.delete');
    Route::get('/apply', [OnlineApplicationDashboardController::class, 'applyNow'])->name('apply');
    Route::get('/account', [OnlineApplicationDashboardController::class, 'account'])->name('account');
    Route::get('/change-password', [OnlineApplicationDashboardController::class, 'showChangePassword'])->name('change_password');
    Route::post('/change-password', [OnlineApplicationDashboardController::class, 'changePassword']);
});

// Sponsor Management Routes
Route::get("manage_sponsors", [SponsorController::class, "index"])->name(
    "manage_sponsors",
);
Route::post("sponsors/store", [SponsorController::class, "store"])->name(
    "sponsors.store",
);
Route::post("sponsors/update/{id}", [SponsorController::class, "update"])->name(
    "sponsors.update",
);
Route::post("sponsors/delete/{id}", [
    SponsorController::class,
    "destroy",
])->name("sponsors.delete");
Route::get("get_sponsors", [SponsorController::class, "getSponsors"])->name(
    "get_sponsors",
);

// school management route
Route::get("school", [SchoolController::class, "school"])->name("school");
Route::post("update_school", [SchoolController::class, "updateSchool"])->name(
    "update_school",
);
Route::get("get_school_details", [
    SchoolController::class,
    "get_school_details",
])->name("get_school_details");
// Configuration Routes
Route::get("3345", [ConfigurationController::class, "index"])->name(
    "configuration.index",
);
Route::post("schools", [ConfigurationController::class, "storeSchool"])->name(
    "save_school",
);

// Super Admin Routes
Route::get("super-admin/dashboard", [
    SuperAdminController::class,
    "dashboard",
])->name("superAdminDashboard");

// Customer inquiries from public contact form
Route::get("super-admin/customer-inquiries", [
    SuperAdminController::class,
    "customerInquiries",
])->name("superadmin.customer_inquiries");
Route::get("super-admin/customer-inquiries/data", [
    SuperAdminController::class,
    "getCustomerInquiriesData",
])->name("superadmin.customer_inquiries.data");
Route::get("super-admin/customer-inquiries/{id}", [
    SuperAdminController::class,
    "showCustomerInquiry",
])->name("superadmin.customer_inquiries.show");
Route::post("super-admin/customer-inquiries/{id}/read", [
    SuperAdminController::class,
    "markCustomerInquiryRead",
])->name("superadmin.customer_inquiries.read");
Route::get("super-admin/customer-inquiries-notifications", [
    SuperAdminController::class,
    "getCustomerInquiryNotifications",
])->name("superadmin.customer_inquiries.notifications");

// Bank registration & school bank account verification (Multi Account Fees)
Route::get("super-admin/banks", [SuperAdminController::class, "banks"])->name("superadmin.banks.index");
Route::post("super-admin/banks", [SuperAdminController::class, "storeBank"])->name("superadmin.banks.store");
Route::put("super-admin/banks/{bankID}", [SuperAdminController::class, "updateBank"])->name("superadmin.banks.update");
Route::delete("super-admin/banks/{bankID}", [SuperAdminController::class, "deleteBank"])->name("superadmin.banks.delete");
Route::get("super-admin/bank-account-verification", [SuperAdminController::class, "bankAccountVerification"])->name("superadmin.bank_accounts.verification");
Route::get("super-admin/bank-accounts/list", [SuperAdminController::class, "getSchoolBankAccounts"])->name("superadmin.bank_accounts.list");
Route::post("super-admin/bank-accounts/{accountID}/approve", [SuperAdminController::class, "approveSchoolBankAccount"])->name("superadmin.bank_accounts.approve");
Route::post("super-admin/bank-accounts/{accountID}/reject", [SuperAdminController::class, "rejectSchoolBankAccount"])->name("superadmin.bank_accounts.reject");

// NBC payment integration documentation (EN / SW PDF downloads)
Route::get("super-admin/nbc-integration-docs", [SuperAdminController::class, "nbcIntegrationDocs"])->name("superadmin.nbc_integration_docs");
Route::get("super-admin/nbc-integration-docs/download/{lang?}", [SuperAdminController::class, "downloadNbcIntegrationDocs"])
    ->where("lang", "en|sw")
    ->name("superadmin.nbc_integration_docs.download");

Route::get("super-admin/schools/register", [
    ConfigurationController::class,
    "index",
])->name("superadmin.schools.register");
Route::get("super-admin/schools/registration-requests", [
    SuperAdminController::class,
    "schoolRegistrationRequests",
])->name("superadmin.schools.registration_requests");
Route::post("super-admin/schools/registration-requests/{id}/verify", [
    SuperAdminController::class,
    "verifySchoolRegistrationRequest",
])->name("superadmin.schools.registration_requests.verify");
Route::post("super-admin/schools/registration-requests/{id}/reject", [
    SuperAdminController::class,
    "rejectSchoolRegistrationRequest",
])->name("superadmin.schools.registration_requests.reject");
Route::get("super-admin/schools", [
    SuperAdminController::class,
    "schools",
])->name("superadmin.schools.index");
Route::post("super-admin/schools/update-settings", [
    SuperAdminController::class,
    "updateSchoolSettings",
])->name("superadmin.schools.update_settings");
Route::post("super-admin/schools/update-logo", [
    SuperAdminController::class,
    "updateSchoolLogo",
])->name("superadmin.schools.update_logo");
Route::post("super-admin/schools/update-stamp", [
    SuperAdminController::class,
    "updateSchoolStamp",
])->name("superadmin.schools.update_stamp");
Route::post("super-admin/schools/update-signature", [
    SuperAdminController::class,
    "updateSchoolSignature",
])->name("superadmin.schools.update_signature");
Route::post("super-admin/schools/update-comprehensive", [
    SuperAdminController::class,
    "updateSchoolComprehensive",
])->name("superadmin.schools.update_comprehensive");
Route::get("super-admin/change-password", [
    SuperAdminController::class,
    "changePasswordForm",
])->name("superadmin.change_password");
Route::post("super-admin/change-password", [
    SuperAdminController::class,
    "changePassword",
])->name("superadmin.change_password.store");

// Super Admin: User Password (Send Credentials)
Route::get("super-admin/user-password", [
    SuperAdminController::class,
    "userPassword",
])->name("superadmin.user_password");
Route::get("super-admin/user-password/users", [
    SuperAdminController::class,
    "getSchoolUsers",
])->name("superadmin.user_password.users");
Route::post("super-admin/user-password/send-sms", [
    SuperAdminController::class,
    "sendUserCredentialsSms",
])->name("superadmin.user_password.send_sms");

// Super Admin: Customer Care (Bulk SMS)
Route::get("super-admin/customer-care", [
    SuperAdminController::class,
    "customerCare",
])->name("superadmin.customer_care");
Route::get("super-admin/customer-care/users", [
    SuperAdminController::class,
    "getSchoolUsers",
])->name("superadmin.customer_care.users");
Route::post("super-admin/customer-care/send-sms", [
    SuperAdminController::class,
    "sendCustomerCareSms",
])->name("superadmin.customer_care.send_sms");

// Super Admin: Attendance SMS Schedule per school
Route::get("super-admin/attendance-sms-schedule", [
    SuperAdminController::class,
    "attendanceSmsSchedule",
])->name("superadmin.attendance_sms_schedule");
Route::get("super-admin/attendance-sms-schedule/{schoolID}", [
    SuperAdminController::class,
    "getAttendanceSmsSchedule",
])->name("superadmin.attendance_sms_schedule.get");
Route::post("super-admin/attendance-sms-schedule", [
    SuperAdminController::class,
    "saveAttendanceSmsSchedule",
])->name("superadmin.attendance_sms_schedule.save");
Route::get("super-admin/fingerprint-attendance-sms-schedule/{schoolID}", [
    SuperAdminController::class,
    "getFingerprintAttendanceSmsSchedule",
])->name("superadmin.fingerprint_attendance_sms_schedule.get");
Route::post("super-admin/fingerprint-attendance-sms-schedule", [
    SuperAdminController::class,
    "saveFingerprintAttendanceSmsSchedule",
])->name("superadmin.fingerprint_attendance_sms_schedule.save");
Route::get("super-admin/parent-fingerprint-portal", [
    SuperAdminController::class,
    "parentFingerprintPortalSettings",
])->name("superadmin.parent_fingerprint_portal");
Route::get("super-admin/parent-fingerprint-portal/{schoolID}", [
    SuperAdminController::class,
    "getParentFingerprintPortalSettings",
])->name("superadmin.parent_fingerprint_portal.get");
Route::post("super-admin/parent-fingerprint-portal", [
    SuperAdminController::class,
    "saveParentFingerprintPortalSettings",
])->name("superadmin.parent_fingerprint_portal.save");

Route::get("super-admin/fingerprint-attendance-setup", [
    FingerprintAttendanceSetupController::class,
    "index",
])->name("superadmin.fingerprint_attendance_setup.index");
Route::post("super-admin/fingerprint-attendance-setup", [
    FingerprintAttendanceSetupController::class,
    "store",
])->name("superadmin.fingerprint_attendance_setup.store");
Route::put("super-admin/fingerprint-attendance-setup/{setupID}", [
    FingerprintAttendanceSetupController::class,
    "update",
])->name("superadmin.fingerprint_attendance_setup.update");
Route::delete("super-admin/fingerprint-attendance-setup/{setupID}", [
    FingerprintAttendanceSetupController::class,
    "destroy",
])->name("superadmin.fingerprint_attendance_setup.destroy");
Route::get("fingerprint-attendance-setup/download/{setupID}", [
    FingerprintAttendanceSetupController::class,
    "download",
])->name("fingerprint_attendance_setup.download");

// Super Admin: System Alert (Header Alerts)
Route::get("super-admin/system-alerts", [
    SuperAdminController::class,
    "systemAlerts",
])->name("superadmin.system_alerts");
Route::get("super-admin/system-alerts/options", [
    SuperAdminController::class,
    "getSystemAlertOptions",
])->name("superadmin.system_alerts.options");
Route::get("super-admin/system-alerts/list", [
    SuperAdminController::class,
    "listSystemAlerts",
])->name("superadmin.system_alerts.list");
Route::post("super-admin/system-alerts/store", [
    SuperAdminController::class,
    "storeSystemAlert",
])->name("superadmin.system_alerts.store");
Route::post("super-admin/system-alerts/update", [
    SuperAdminController::class,
    "updateSystemAlert",
])->name("superadmin.system_alerts.update");
Route::delete("super-admin/system-alerts/delete/{id}", [
    SuperAdminController::class,
    "deleteSystemAlert",
])->name("superadmin.system_alerts.delete");

// Super Admin: Users
Route::get("super-admin/users", [SuperAdminController::class, "users"])->name(
    "superadmin.users",
);
Route::get("super-admin/users/data", [
    SuperAdminController::class,
    "getUsersData",
])->name("superadmin.users.data");

// Super Admin: System Logs
Route::get("super-admin/system-logs", [
    SuperAdminController::class,
    "systemLogs",
])->name("superadmin.system_logs");
Route::get("super-admin/system-logs/data", [
    SuperAdminController::class,
    "getSystemLogsData",
])->name("superadmin.system_logs.data");
Route::get("super-admin/system-logs/visitors/data", [
    SuperAdminController::class,
    "getWebVisitorsData",
])->name("superadmin.system_logs.visitors_data");
Route::get("super-admin/system-logs/visitors/details", [
    SuperAdminController::class,
    "getWebVisitorDetails",
])->name("superadmin.system_logs.visitors_details");
Route::get("super-admin/system-logs/details/{id}", [
    SuperAdminController::class,
    "getLogDetails",
])->name("superadmin.system_logs.details");
Route::post("super-admin/system-logs/block-ip", [
    SuperAdminController::class,
    "blockIp",
])->name("superadmin.system_logs.block_ip");
Route::post("super-admin/system-logs/unblock-ip", [
    SuperAdminController::class,
    "unblockIp",
])->name("superadmin.system_logs.unblock_ip");
Route::post("super-admin/system-logs/login-photo-capture", [
    SuperAdminController::class,
    "updateLoginPhotoCaptureSetting",
])->name("superadmin.system_logs.login_photo_capture");

// login Routes
Route::get("login", [Auth::class, "login"])->name("login");
Route::view("forgot-password", "auth.forgot-password")->name("forgot_password");
Route::post("auth", [Auth::class, "auth"])->name("auth");
Route::post("auth/otp/verify", [Auth::class, "verifyOtp"])->name(
    "auth.otp.verify",
);
Route::post("auth/otp/resend", [Auth::class, "resendOtp"])->name(
    "auth.otp.resend",
);
// Google may GET this POST-only API — send crawlers to login (noindex) instead of 405.
Route::get("auth/otp/{any?}", function () {
    return redirect()->route('login', [], 301);
})->where('any', '.*');
Route::post("auth/forgot-password", [Auth::class, "forgotPassword"])->name(
    "auth.forgot_password",
);

// logut route
Route::match(["get", "post"], "logout", [Auth::class, "logout"])->name(
    "logout",
);

// Admin: Change Password
Route::get("admin/change-password", [
    AdminController::class,
    "changePasswordForm",
])->name("admin.change_password");
Route::post("admin/change-password", [
    AdminController::class,
    "changePassword",
])->name("admin.change_password.store");

// Admin Routes
Route::get("AdminDashboard", [AdminController::class, "AdminDashboard"])->name(
    "AdminDashboard",
);
Route::get("admin/scheme-of-work", [
    AdminController::class,
    "adminSchemeOfWork",
])->name("admin.schemeOfWork");
Route::get("admin/academic-years", [
    AcademicYearsController::class,
    "index",
])->name("admin.academicYears");
Route::post("admin/academic-years/store", [
    AcademicYearsController::class,
    "store",
])->name("admin.academicYears.store");
Route::get("admin/academic-years/{academicYearID}", [
    AcademicYearsController::class,
    "show",
])->name("admin.academicYears.show");
Route::post("admin/academic-years/{academicYearID}/update", [
    AcademicYearsController::class,
    "update",
])->name("admin.academicYears.update");
Route::post("admin/academic-years/{academicYearID}/delete", [
    AcademicYearsController::class,
    "destroy",
])->name("admin.academicYears.delete");
Route::get("selected-academic-year", [
    SelectedAcademicYearController::class,
    "show",
])->name("selected_academic_year.show");
Route::post("selected-academic-year", [
    SelectedAcademicYearController::class,
    "update",
])->name("selected_academic_year.update");
Route::get("admin/scheme-of-work/view/{schemeOfWorkID}", [
    AdminController::class,
    "adminViewSchemeOfWork",
])->name("admin.viewSchemeOfWork");
Route::get("admin/lesson-plans", [
    AdminController::class,
    "adminLessonPlans",
])->name("admin.lessonPlans");
Route::get("admin/lesson-plans/get", [
    AdminController::class,
    "getLessonPlansForAdmin",
])->name("admin.get_lesson_plans");
Route::get("admin/lesson-plan/get", [
    AdminController::class,
    "getLessonPlanForAdmin",
])->name("admin.get_lesson_plan");
Route::post("admin/lesson-plan/sign", [
    AdminController::class,
    "signLessonPlan",
])->name("admin.sign_lesson_plan");
Route::post("admin/lesson-plan/remove-signature", [
    AdminController::class,
    "removeLessonPlanSignature",
])->name("admin.remove_lesson_plan_signature");
Route::get("task-management", [AdminController::class, "taskManagement"])->name(
    "taskManagement",
);
Route::get("admin/get-teacher-tasks", [
    AdminController::class,
    "getTeacherTasks",
])->name("admin.get_teacher_tasks");
Route::post("approve-task/{taskID}", [
    AdminController::class,
    "approveTask",
])->name("approve_task");
Route::post("reject-task/{taskID}", [
    AdminController::class,
    "rejectTask",
])->name("reject_task");
Route::get("admin/student-id-cards/{classID?}", [
    ManageStudentController::class,
    "studentIdCards",
])->name("admin.student_id_cards");
Route::get("admin/student-id-cards/download/{id?}", [
    ManageStudentController::class,
    "downloadStudentIdCard",
])->name("admin.download_student_id_card");

// Parents Routes
Route::get("parentDashboard", [
    ParentsContoller::class,
    "parentDashboard",
])->name("parentDashboard");
Route::get("parentResults", [ParentsContoller::class, "parentResults"])->name(
    "parentResults",
);
Route::get("parentReport", [ParentsContoller::class, "parentResults"])->name(
    "parentReport",
); // Reusing parentResults logic with a type flag
Route::get("parentAttendance", [
    ParentsContoller::class,
    "parentAttendance",
])->name("parentAttendance");
Route::get("parentAttendance/{studentID}", [
    ParentsContoller::class,
    "parentStudentAttendance",
])->name("parentStudentAttendance");
Route::get("parentFingerprintAttendance", [
    ParentsContoller::class,
    "parentFingerprintAttendance",
])->name("parentFingerprintAttendance");
Route::get("parentFingerprintAttendance/{studentID}", [
    ParentsContoller::class,
    "parentStudentFingerprintAttendance",
])->name("parentStudentFingerprintAttendance");
Route::get("parentPayments", [ParentsContoller::class, "parentPayments"])->name(
    "parentPayments",
);
Route::get("parentFeesSummary", [
    ParentsContoller::class,
    "parentFeesSummary",
])->name("parentFeesSummary");
Route::get("parentSubjects", [ParentsContoller::class, "parentSubjects"])->name(
    "parentSubjects",
);
Route::get("parentHomePackages", [
    HomePackageController::class,
    "parentIndex",
])->name("parentHomePackages");
Route::get("parentHomePackages/{holidayID}/packages", [
    HomePackageController::class,
    "parentHolidayPackages",
])->name("parentHomePackages.holiday");
Route::get("parent/home-package-notifications", [
    HomePackageController::class,
    "parentNotifications",
])->name("parent.home_package_notifications");
Route::post("parent/home-package-notifications/mark-read/{id?}", [
    HomePackageController::class,
    "markNotificationRead",
])->name("parent.home_package_notifications.mark_read");
Route::get("parent/header-notifications", [
    ParentsContoller::class,
    "headerNotifications",
])->name("parent.header_notifications");
Route::post("parent/header-notifications/mark-read", [
    ParentsContoller::class,
    "markHeaderNotificationsRead",
])->name("parent.header_notifications.mark_read");
Route::get("parentPermissions", [
    ParentsContoller::class,
    "manageParentPermissions",
])->name("parent.permissions");
Route::post("parentPermissions", [
    ParentsContoller::class,
    "storeParentPermission",
])->name("parent.permissions.store");
Route::get("parent/get-published-exams", [
    ParentsContoller::class,
    "getPublishedExams",
])->name("get_published_exams");
Route::get("parent/fetch-student-results-ajax", [
    ParentsContoller::class,
    "fetchStudentResultsAjax",
])->name("fetch_student_results_ajax");
Route::post("parent/download-student-result", [
    ParentsContoller::class,
    "downloadStudentResult",
])->name("download_student_result");
Route::get("parent/fetch-term-report-ajax", [
    ParentsContoller::class,
    "fetchStudentResultsAjax",
])->name("fetch_term_report_ajax"); // Reusing same logic
Route::post("parent/download-term-report", [
    ParentsContoller::class,
    "downloadStudentResult",
])->name("download_term_report"); // Reusing same logic
Route::get("get_student_subjects/{studentID}", [
    ParentsContoller::class,
    "getStudentSubjects",
])->name("get_student_subjects");
Route::post("elect_subject", [ParentsContoller::class, "electSubject"])->name(
    "elect_subject",
);
Route::post("deselect_subject", [
    ParentsContoller::class,
    "deselectSubject",
])->name("deselect_subject");
Route::post("change-language", [
    ParentsContoller::class,
    "changeLanguage",
])->name("change_language");
Route::get("get_parent_payments_ajax", [
    ParentsContoller::class,
    "get_parent_payments_ajax",
])->name("get_parent_payments_ajax");
Route::post("request_control_number/{studentID}", [
    ParentsContoller::class,
    "request_control_number",
])->name("request_control_number");
Route::get("get_fees_summary_ajax", [
    ParentsContoller::class,
    "get_fees_summary_ajax",
])->name("get_fees_summary_ajax");

// Parents API Routes
Route::get("api/parent/results", [
    ParentsContoller::class,
    "apiGetParentResults",
])->name("api.parent.results");
Route::post("api/parent/results", [
    ParentsContoller::class,
    "apiGetParentResults",
])->name("api.parent.results.post");
Route::get("api/parent/attendance", [
    ParentsContoller::class,
    "apiGetParentAttendance",
])->name("api.parent.attendance");
Route::post("api/parent/attendance", [
    ParentsContoller::class,
    "apiGetParentAttendance",
])->name("api.parent.attendance.post");

// teachers Management Routes
Route::get("manageTeachers", [
    ManageTeachersController::class,
    "manageTeachers",
])->name("manageTeachers");
Route::get("manage_watchman", [WatchmanController::class, "manage"])->name(
    "manage_watchman",
);
Route::post("save_watchman", [WatchmanController::class, "store"])->name(
    "save_watchman",
);
Route::get("get_watchman/{id}", [WatchmanController::class, "get"])->name(
    "get_watchman",
);
Route::post("update_watchman", [WatchmanController::class, "update"])->name(
    "update_watchman",
);
Route::delete("delete_watchman/{id}", [
    WatchmanController::class,
    "destroy",
])->name("delete_watchman");
Route::get("watchmanDashboard", [WatchmanController::class, "dashboard"])->name(
    "watchmanDashboard",
);
Route::get("watchman/visitors", [WatchmanController::class, "visitors"])->name(
    "watchman.visitors",
);
Route::get("watchman/school-visitors/today", [
    WatchmanController::class,
    "todayVisitors",
])->name("watchman.school_visitors.today");
Route::post("watchman/school-visitors/store", [
    WatchmanController::class,
    "storeVisitors",
])->name("watchman.school_visitors.store");
Route::post("save_teachers", [
    ManageTeachersController::class,
    "save_teachers",
])->name("save_teachers");
Route::get("get_teacher/{id}", [
    ManageTeachersController::class,
    "get_teacher",
])->name("get_teacher");
Route::post("update_teacher", [
    ManageTeachersController::class,
    "update_teacher",
])->name("update_teacher");
Route::delete("delete_teacher/{id}", [
    ManageTeachersController::class,
    "delete_teacher",
])->name("delete_teacher");
Route::post("send_teacher_to_fingerprint", [
    ManageTeachersController::class,
    "sendTeacherToFingerprint",
])->name("send_teacher_to_fingerprint");
Route::get("admin/teacher/profile/download-pdf/{id}", [
    ManageTeachersController::class,
    "downloadTeacherProfilePdf",
])->name("admin.teacher.profile.download_pdf");
Route::get("admin/teachers/tange/download-all", [
    ManageTeachersController::class,
    "downloadAllTeachersTangePdf",
])->name("admin.teachers.tange.download_all");
Route::post("admin/teacher/profile/update-image", [
    ManageTeachersController::class,
    "updateTeacherImage",
])->name("admin.teacher.profile.update_image");
Route::post("send_student_to_fingerprint", [
    ManageClassessController::class,
    "sendStudentToFingerprint",
])->name("send_student_to_fingerprint");

// Export teacher attendance (web routes to access session)
Route::get("attendance/export-teachers-excel", [
    AttendanceApiController::class,
    "exportTeacherAttendanceExcel",
])->name("attendance.export_teachers_excel");
Route::get("attendance/export-teachers-pdf", [
    AttendanceApiController::class,
    "exportTeacherAttendancePdf",
])->name("attendance.export_teachers_pdf");

// Classess Routes
Route::get("manageClasses", [
    ManageClassessController::class,
    "manageClasses",
])->name("manageClasses");
Route::get("get_class/{classID}", [
    ManageClassessController::class,
    "get_class",
])->name("get_class");
Route::post("save_class", [
    ManageClassessController::class,
    "save_class",
])->name("save_class");
Route::post("update_class", [
    ManageClassessController::class,
    "update_class",
])->name("update_class");
Route::post("activate_class/{classID}", [
    ManageClassessController::class,
    "activate_class",
])->name("activate_class");
Route::delete("delete_class/{classID}", [
    ManageClassessController::class,
    "delete_class",
])->name("delete_class");
Route::post("save_sub_lass", [
    ManageClassessController::class,
    "save_sub_lass",
])->name("save_sub_lass");
Route::post("save_combie", [
    ManageClassessController::class,
    "save_combie",
])->name("save_combie");
Route::post("update_combie", [
    ManageClassessController::class,
    "update_combie",
])->name("update_combie");
Route::delete("delete_combie/{combieID}", [
    ManageClassessController::class,
    "delete_combie",
])->name("delete_combie");
Route::get("get_classes", [
    ManageClassessController::class,
    "get_classes",
])->name("get_classes");
Route::get("admin/get-classes", [
    ManageClassessController::class,
    "get_classes",
])->name("admin.get_classes");
Route::get("admin/get-terms-for-year", [
    ManageAttendanceController::class,
    "getTermsForYear",
])->name("admin.get_terms_for_year");
Route::get("admin/get-exams-for-year-term", [
    ManageAttendanceController::class,
    "getExamsForYearTerm",
])->name("admin.get_exams_for_year_term");
Route::get("admin/get-subclasses-for-class", [
    ManageAttendanceController::class,
    "getSubclassesForClass",
])->name("admin.get_subclasses_for_class");
Route::get("admin/get-subjects-for-class", [
    ManageAttendanceController::class,
    "getSubjectsForClass",
])->name("admin.get_subjects_for_class");
Route::get("admin/get-exam-attendance-data", [
    ManageAttendanceController::class,
    "getExamAttendanceData",
])->name("admin.get_exam_attendance_data");
Route::get("admin/get-student-missed-subjects", [
    ManageAttendanceController::class,
    "getStudentMissedSubjects",
])->name("admin.get_student_missed_subjects");
Route::get("get_class_subclasses/{classID}", [
    ManageClassessController::class,
    "get_class_subclasses",
])->name("get_class_subclasses");
Route::get("get_subclass/{subclassID}", [
    ManageClassessController::class,
    "get_subclass",
])->name("get_subclass");
Route::post("update_subclass", [
    ManageClassessController::class,
    "update_subclass",
])->name("update_subclass");
Route::post("activate_subclass/{subclassID}", [
    ManageClassessController::class,
    "activate_subclass",
])->name("activate_subclass");
Route::get("get_subclass_students/{subclassID}", [
    ManageClassessController::class,
    "get_subclass_students",
])->name("get_subclass_students");
Route::get("get_subclass_subjects/{subclassID}", [
    ManageClassessController::class,
    "get_subclass_subjects",
])->name("get_subclass_subjects");
Route::get("get_class_grading", [
    ManageClassessController::class,
    "get_class_grading",
])->name("get_class_grading");
Route::post("update_grade_range", [
    ManageClassessController::class,
    "update_grade_range",
])->name("update_grade_range");
Route::delete("delete_subclass/{subclassID}", [
    ManageClassessController::class,
    "delete_subclass",
])->name("delete_subclass");

// Subjects Routes
Route::get("manageSubjects", [
    ManageSubjectController::class,
    "manageSubjects",
])->name("manageSubjects");
Route::post("save_school_subject", [
    ManageSubjectController::class,
    "save_school_subject",
])->name("save_school_subject");
Route::get("check_subject_code", [
    ManageSubjectController::class,
    "check_subject_code",
])->name("check_subject_code");
Route::post("update_school_subject", [
    ManageSubjectController::class,
    "update_school_subject",
])->name("update_school_subject");
Route::delete("delete_school_subject/{subjectID}", [
    ManageSubjectController::class,
    "delete_school_subject",
])->name("delete_school_subject");
Route::post("activate_subject/{subjectID}", [
    ManageSubjectController::class,
    "activate_subject",
])->name("activate_subject");
Route::get("get_class_subjects_by_subclass/{subclassID}", [
    ManageSubjectController::class,
    "get_class_subjects_by_subclass",
])->name("get_class_subjects_by_subclass");
Route::get("get_subclass_subject_defaults/{subclassID}", [
    ManageSubjectController::class,
    "get_subclass_subject_defaults",
])->name("get_subclass_subject_defaults");
Route::post("save_class_subject", [
    ManageSubjectController::class,
    "save_class_subject",
])->name("save_class_subject");
Route::post("activate_class_subject/{classSubjectID}", [
    ManageSubjectController::class,
    "activate_class_subject",
])->name("activate_class_subject");
Route::get("get_class_subjects", [
    ManageSubjectController::class,
    "get_class_subjects",
])->name("get_class_subjects");
Route::post("update_class_subject", [
    ManageSubjectController::class,
    "update_class_subject",
])->name("update_class_subject");
Route::delete("delete_class_subject/{classSubjectID}", [
    ManageSubjectController::class,
    "delete_class_subject",
])->name("delete_class_subject");
// Subject Election Routes
Route::get("get_subject_electors/{classSubjectID}", [
    ManageSubjectController::class,
    "get_subject_electors",
])->name("get_subject_electors");
Route::post("save_subject_election", [
    ManageSubjectController::class,
    "save_subject_election",
])->name("save_subject_election");
Route::post("deselect_student", [
    ManageSubjectController::class,
    "deselect_student",
])->name("deselect_student");

// Teachers Routes
Route::get("teachersDashboard", [
    TeachersController::class,
    "teachersDashboard",
])->name("teachersDashboard");
Route::get("teacher/my-attendance", [
    ManageAttendanceController::class,
    "teacherMyAttendance",
])->name("teacher.my_attendance");
Route::get("teacher/my-attendance/data", [
    ManageAttendanceController::class,
    "teacherMyAttendanceData",
])->name("teacher.my_attendance.data");
Route::get("teacher/my-attendance/pdf", [
    ManageAttendanceController::class,
    "exportTeacherMyAttendancePdf",
])->name("teacher.my_attendance.pdf");
Route::get("teacher/my-sessions", [
    TeachersController::class,
    "mySessions",
])->name("teacher.mySessions");
Route::get("teacher/my-sessions/pdf", [
    TeachersController::class,
    "exportMySessionsPdf",
])->name("teacher.mySessions.pdf");
Route::get("teacher/suggestions", [
    TeachersController::class,
    "manageTeacherFeedback",
])->name("teacher.suggestions");
Route::get("teacher/incidents", [
    TeachersController::class,
    "manageTeacherFeedback",
])->name("teacher.incidents");
Route::post("teacher/feedback", [
    TeachersController::class,
    "storeTeacherFeedback",
])->name("teacher.feedback.store");
Route::get("teacher/permissions", [
    TeachersController::class,
    "manageTeacherPermissions",
])->name("teacher.permissions");
Route::get("teacher/student-permission", [
    TeachersController::class,
    "showStudentPermissionForm",
])->name("teacher.student_permission");
Route::get("teacher/student-permission/index", [
    TeachersController::class,
    "getTeacherPermissionRequests",
])->name("teacher.student_permission.index");
Route::get("teacher/student-permission/search", [
    TeachersController::class,
    "searchStudentsForPermission",
])->name("teacher.student_permission.search");
Route::get("teacher/student-permission/details/{id}", [
    TeachersController::class,
    "getStudentPermissionDetails",
])->name("teacher.student_permission.details");
Route::get("teacher/student-permission/progress/{id}", [
    TeachersController::class,
    "getStudentPermissionProgress",
])->name("teacher.student_permission.progress");
Route::post("teacher/student-permission", [
    TeachersController::class,
    "storeStudentPermission",
])->name("teacher.student_permission.store");
Route::post("teacher/permissions", [
    TeachersController::class,
    "storeTeacherPermission",
])->name("teacher.permissions.store");
Route::get("teacher/my-tasks", [TeachersController::class, "myTasks"])->name(
    "teacher.myTasks",
);
Route::get("teacher/get-session-students", [
    TeachersController::class,
    "getSessionStudents",
])->name("teacher.get_session_students");
Route::post("teacher/collect-session-attendance", [
    TeachersController::class,
    "collectSessionAttendance",
])->name("teacher.collect_session_attendance");
Route::post("teacher/assign-session-task", [
    TeachersController::class,
    "assignSessionTask",
])->name("teacher.assign_session_task");
Route::post("teacher/update-session-task/{taskID}", [
    TeachersController::class,
    "updateSessionTask",
])->name("teacher.update_session_task");
Route::get("teacher/session-attendance/{classSubjectID}", [
    TeachersController::class,
    "sessionAttendance",
])->name("teacher.session_attendance");
Route::get("teacher/get-session-attendance-data", [
    TeachersController::class,
    "getSessionAttendanceData",
])->name("teacher.get_session_attendance_data");
Route::get("teacher/update-attendance", [
    TeachersController::class,
    "updateAttendance",
])->name("teacher.update_attendance");
Route::get("teacher/get-collected-attendance", [
    TeachersController::class,
    "getCollectedAttendance",
])->name("teacher.get_collected_attendance");
Route::get("teacher/get-session-attendance-for-update", [
    TeachersController::class,
    "getSessionAttendanceForUpdate",
])->name("teacher.get_session_attendance_for_update");
Route::get("teacher/logbook", [TeachersController::class, "logbook"])->name(
    "teacher.logbook",
);
Route::get("teacher/get-logbook-data/{classSubjectID}", [
    TeachersController::class,
    "getLogbookData",
])->name("teacher.get_logbook_data");
Route::post("teacher/save-logbook-entry", [
    TeachersController::class,
    "saveLogbookEntry",
])->name("teacher.save_logbook_entry");
Route::get("teacher/department-logbook", [
    TeachersController::class,
    "departmentLogbook",
])->name("teacher.departmentLogbook");
Route::get("teacher/get-department-logbook-data", [
    TeachersController::class,
    "getDepartmentLogbookData",
])->name("teacher.getDepartmentLogbookData");
Route::post("teacher/sign-logbook", [
    TeachersController::class,
    "signLogbook",
])->name("teacher.signLogbook");
Route::get("teacher/download-logbook-pdf/{classSubjectID}", [
    TeachersController::class,
    "downloadLogbookPdf",
])->name("teacher.downloadLogbookPdf");
Route::get("admin/logbook", [TeachersController::class, "adminLogbook"])->name(
    "admin.logbook",
);
Route::get("admin/get-logbook-data", [
    TeachersController::class,
    "getAdminLogbookData",
])->name("admin.getLogbookData");
Route::post("admin/sign-logbook", [
    TeachersController::class,
    "adminSignLogbook",
])->name("admin.adminSignLogbook");
Route::get("teacherSubjects", [
    TeachersController::class,
    "teacherSubjects",
])->name("teacherSubjects");
Route::get("teacher/scheme-of-work", [
    TeachersController::class,
    "schemeOfWork",
])->name("teacher.schemeOfWork");
Route::get("teacher/scheme-of-work/create/{classSubjectID}", [
    TeachersController::class,
    "createNewScheme",
])->name("teacher.createSchemeOfWork");
Route::post("teacher/scheme-of-work/store", [
    TeachersController::class,
    "storeNewScheme",
])->name("teacher.storeSchemeOfWork");
Route::post("teacher/scheme-of-work/check-existing", [
    TeachersController::class,
    "checkExistingScheme",
])->name("teacher.checkExistingScheme");
Route::get("teacher/scheme-of-work/view/{schemeOfWorkID}", [
    TeachersController::class,
    "viewSchemeOfWork",
])->name("teacher.viewSchemeOfWork");
Route::get("teacher/scheme-of-work/manage/{schemeOfWorkID}", [
    TeachersController::class,
    "manageSchemeOfWork",
])->name("teacher.manageSchemeOfWork");
Route::post("teacher/scheme-of-work/update/{schemeOfWorkID}", [
    TeachersController::class,
    "updateSchemeOfWork",
])->name("teacher.updateSchemeOfWork");
Route::post("teacher/scheme-of-work/update/{schemeOfWorkID}/remark", [
    TeachersController::class,
    "updateSchemeOfWorkRemark",
])->name("teacher.updateSchemeOfWorkRemark");
Route::get("teacher/scheme-of-work/use-existing/{classSubjectID}", [
    TeachersController::class,
    "useExistingSchemes",
])->name("teacher.useExistingSchemes");
Route::post("teacher/scheme-of-work/use-this/{schemeOfWorkID}", [
    TeachersController::class,
    "useThisScheme",
])->name("teacher.useThisScheme");
Route::delete("teacher/scheme-of-work/delete/{schemeOfWorkID}", [
    TeachersController::class,
    "deleteSchemeOfWork",
])->name("teacher.deleteSchemeOfWork");
Route::get("teacher/scheme-of-work/export-pdf/{schemeOfWorkID}", [
    TeachersController::class,
    "exportSchemeOfWorkPDF",
])->name("teacher.exportSchemeOfWorkPDF");
Route::get("teacher/scheme-of-work/export-excel/{schemeOfWorkID}", [
    TeachersController::class,
    "exportSchemeOfWorkExcel",
])->name("teacher.exportSchemeOfWorkExcel");
Route::get("teacher/lesson-plans", [
    TeachersController::class,
    "lessonPlans",
])->name("teacher.lessonPlans");
Route::get("teacher/get-session-attendance-stats", [
    TeachersController::class,
    "getSessionAttendanceStats",
])->name("teacher.get_session_attendance_stats");
Route::get("teacher/get-all-sessions-for-year", [
    TeachersController::class,
    "getAllSessionsForYear",
])->name("teacher.get_all_sessions_for_year");
Route::get("teacher/get-sessions-by-subject", [
    TeachersController::class,
    "getSessionsBySubject",
])->name("teacher.get_sessions_by_subject");
Route::post("teacher/lesson-plan/store", [
    TeachersController::class,
    "storeLessonPlan",
])->name("teacher.store_lesson_plan");
Route::get("teacher/lesson-plan/get", [
    TeachersController::class,
    "getLessonPlan",
])->name("teacher.get_lesson_plan");
Route::get("teacher/lesson-plan/previous", [
    TeachersController::class,
    "getPreviousLessonPlan",
])->name("teacher.get_previous_lesson_plan");
Route::get("teacher/lesson-plan/check-exists", [
    TeachersController::class,
    "checkLessonPlanExists",
])->name("teacher.check_lesson_plan_exists");
Route::get("teacher/lesson-plan/download-pdf/{lessonPlanID}", [
    TeachersController::class,
    "downloadLessonPlanPDF",
])->name("teacher.download_lesson_plan_pdf");
Route::post("teacher/lesson-plan/update/{lessonPlanID}", [
    TeachersController::class,
    "updateLessonPlan",
])->name("teacher.update_lesson_plan");
Route::get("teacher/lesson-plan/get-by-filter", [
    TeachersController::class,
    "getLessonPlansByFilter",
])->name("teacher.get_lesson_plans_by_filter");
Route::get("teacher/lesson-plan/get-by-id", [
    TeachersController::class,
    "getLessonPlanById",
])->name("teacher.get_lesson_plan_by_id");
Route::post("teacher/lesson-plan/send-to-admin", [
    TeachersController::class,
    "sendLessonPlanToAdmin",
])->name("teacher.send_lesson_plan_to_admin");
Route::post("teacher/lesson-plan/download-bulk-pdf", [
    TeachersController::class,
    "downloadBulkLessonPlansPDF",
])->name("teacher.download_bulk_lesson_plans_pdf");
// Route::get('teacher/exam-attendance/{classSubjectID}', [TeachersController::class, 'examAttendance'])->name('teacher.exam_attendance');
Route::get("teacher/get-terms-for-year", [
    TeachersController::class,
    "getTermsForYear",
])->name("teacher.get_terms_for_year");
Route::get("teacher/get-exams-for-year-term", [
    TeachersController::class,
    "getExamsForYearTerm",
])->name("teacher.get_exams_for_year_term");
Route::get("teacher/get-exam-attendance-data", [
    TeachersController::class,
    "getExamAttendanceData",
])->name("teacher.get_exam_attendance_data");
Route::get("teacher/profile", [TeachersController::class, "profile"])->name(
    "teacher.profile",
);
Route::get("teacher/profile/download-pdf", [
    TeachersController::class,
    "downloadMyProfilePdf",
])->name("teacher.profile.download_pdf");
Route::post("teacher/profile/update-image", [
    TeachersController::class,
    "updateMyImage",
])->name("teacher.profile.update_image");
Route::post("teacher/change-password", [
    TeachersController::class,
    "changePassword",
])->name("teacher.change_password");
Route::get("get_subject_students/{classSubjectID}", [
    TeachersController::class,
    "getSubjectStudents",
])->name("get_subject_students");
Route::get("get_class_subject_papers/{classSubjectID}", [
    TeachersController::class,
    "getClassSubjectPapers",
])->name("get_class_subject_papers");
Route::get("get_subject_results/{classSubjectID}", [
    TeachersController::class,
    "getSubjectResults",
])->name("get_subject_results");
Route::get("get_subject_results/{classSubjectID}/{examID}", [
    TeachersController::class,
    "getSubjectResults",
])->name("get_subject_results_by_exam");
Route::get("export_subject_results_pdf/{classSubjectID}/{examID?}", [
    TeachersController::class,
    "exportSubjectResultsPdf",
])->name("export_subject_results_pdf");
Route::get("get_examinations_for_subject/{classSubjectID}", [
    TeachersController::class,
    "getExaminationsForSubject",
])->name("get_examinations_for_subject");
Route::get("get_exam_allowed_classes/{examID}", [
    ManageExaminationController::class,
    "getExamAllowedClasses",
])->name("get_exam_allowed_classes");
Route::get("get_exam_paper_question_data/{classSubjectID}/{examID}", [
    TeachersController::class,
    "getExamPaperQuestionData",
])->name("get_exam_paper_question_data");
Route::get("check_exam_paper_status/{examID}/{classSubjectID}", [
    TeachersController::class,
    "checkExamPaperStatus",
])->name("check_exam_paper_status");
Route::post("dismiss_exam_rejection_notification", [
    TeachersController::class,
    "dismissExamRejectionNotification",
])->name("dismiss_exam_rejection_notification");
Route::post("save_subject_results", [
    TeachersController::class,
    "saveSubjectResults",
])->name("save_subject_results");
Route::get("get_class_subject_students_all/{classID}/{subjectID}", [
    TeachersController::class,
    "getClassSubjectStudentsAll",
])->name("get_class_subject_students_all");
Route::get("approve_result/{examID}", [
    TeachersController::class,
    "approveResult",
])->name("approve_result");
Route::get("get_filtered_results_for_approval/{examID}", [
    TeachersController::class,
    "getFilteredResultsForApproval",
])->name("get_filtered_results_for_approval");
Route::post("submit_result_approval/{examID}", [
    TeachersController::class,
    "submitResultApproval",
])->name("submit_result_approval");
Route::get("view_approval_chain/{examID}", [
    TeachersController::class,
    "viewApprovalChain",
])->name("view_approval_chain");
Route::get("get_class_teacher_approvals/{examID}", [
    TeachersController::class,
    "getClassTeacherApprovals",
])->name("get_class_teacher_approvals");
Route::get("get_coordinator_approvals/{examID}", [
    TeachersController::class,
    "getCoordinatorApprovals",
])->name("get_coordinator_approvals");
Route::post("get_teachers_for_subject", [
    TeachersController::class,
    "getTeachersForSubject",
])->name("get_teachers_for_subject");
Route::post("send_message_to_teachers", [
    TeachersController::class,
    "sendMessageToTeachers",
])->name("send_message_to_teachers");
Route::get("download_excel_template/{classSubjectID}/{examID}", [
    TeachersController::class,
    "downloadExcelTemplate",
])->name("download_excel_template");
Route::post("upload_excel_results", [
    TeachersController::class,
    "uploadExcelResults",
])->name("upload_excel_results");
Route::post("parse_excel_results", [
    TeachersController::class,
    "parseExcelResultsForForm",
])->name("parse_excel_results");

// Staff Routes
Route::get("staffDashboard", [StaffController::class, "staffDashboard"])->name(
    "staffDashboard",
);
Route::get("staff/suggestions", [StaffController::class, "suggestions"])->name(
    "staff.suggestions",
);
Route::get("staff/incidents", [StaffController::class, "incidents"])->name(
    "staff.incidents",
);
Route::post("staff/feedback", [
    StaffController::class,
    "storeStaffFeedback",
])->name("staff.feedback.store");
Route::get("staff/permissions", [StaffController::class, "permissions"])->name(
    "staff.permissions",
);
Route::post("staff/permissions", [
    StaffController::class,
    "storeStaffPermission",
])->name("staff.permissions.store");
Route::get("staff/student-permission", [
    StaffController::class,
    "showStudentPermissionForm",
])->name("staff.student_permission");
Route::get("staff/student-permission/index", [
    StaffController::class,
    "getStaffPermissionRequests",
])->name("staff.student_permission.index");
Route::get("staff/student-permission/search", [
    StaffController::class,
    "searchStudentsForPermission",
])->name("staff.student_permission.search");
Route::get("staff/student-permission/details/{id}", [
    StaffController::class,
    "getStudentPermissionDetails",
])->name("staff.student_permission.details");
Route::get("staff/student-permission/progress/{id}", [
    StaffController::class,
    "getStudentPermissionProgress",
])->name("staff.student_permission.progress");
Route::post("staff/student-permission", [
    StaffController::class,
    "storeStudentPermission",
])->name("staff.student_permission.store");
Route::prefix("accountant")
    ->name("accountant.")
    ->group(function () {
        Route::get("expenses", [ExpenseController::class, "index"])->name(
            "expenses.index",
        );
        Route::get("expenses/create", [
            ExpenseController::class,
            "create",
        ])->name("expenses.create");
        Route::post("expenses", [ExpenseController::class, "store"])->name(
            "expenses.store",
        );

        Route::get("income", [IncomeController::class, "index"])->name(
            "income.index",
        );
        Route::get("budget", [BudgetController::class, "index"])->name(
            "budget.index",
        );
    });
Route::get("staff/leave", [StaffController::class, "leave"])->name(
    "staff.leave",
);
Route::get("staff/payroll", [StaffController::class, "payroll"])->name(
    "staff.payroll",
);
Route::get("staff/profile", [StaffController::class, "profile"])->name(
    "staff.profile",
);
Route::post("staff/profile/update", [
    StaffController::class,
    "updateProfile",
])->name("staff.profile.update");

// Users Roles Routes
Route::post("save_teacher_role", [
    ManageTeachersController::class,
    "save_teacher_role",
])->name("save_teacher_role");
Route::post("change_teacher_role", [
    ManageTeachersController::class,
    "change_teacher_role",
])->name("change_teacher_role");
Route::delete("remove_teacher_role/{id}", [
    ManageTeachersController::class,
    "remove_teacher_role",
])->name("remove_teacher_role");

// Roles and Permissions Routes (Spatie)
Route::post("create_role", [
    ManageTeachersController::class,
    "create_role",
])->name("create_role");
Route::post("update_role", [
    ManageTeachersController::class,
    "update_role",
])->name("update_role");
Route::post("create_permission", [
    ManageTeachersController::class,
    "create_permission",
])->name("create_permission");
Route::post("create_bulk_permissions", [
    ManageTeachersController::class,
    "create_bulk_permissions",
])->name("create_bulk_permissions");
Route::post("update_role_permissions", [
    ManageTeachersController::class,
    "update_role_permissions",
])->name("update_role_permissions");
Route::delete("delete_role/{id}", [
    ManageTeachersController::class,
    "delete_role",
])->name("delete_role");
Route::get("get_permissions", [
    ManageTeachersController::class,
    "get_permissions",
])->name("get_permissions");
Route::get("get_role_with_permissions/{id}", [
    ManageTeachersController::class,
    "get_role_with_permissions",
])->name("get_role_with_permissions");

// class teacher routes
Route::get("AdmitedClasses", [
    ManageClassessController::class,
    "AdmitedClasses",
])->name("AdmitedClasses");
Route::get("ClassMangement/{subclassID?}", [
    ManageClassessController::class,
    "ClassMangement",
])->name("ClassMangement");
Route::post("teacher/upload-signature", [
    ManageClassessController::class,
    "uploadTeacherSignature",
])->name("teacher.upload_signature");
Route::post("teacher/delete-signature", [
    ManageClassessController::class,
    "deleteTeacherSignature",
])->name("teacher.delete_signature");
Route::get("get_examinations_for_subclass/{subclassID}", [
    ManageClassessController::class,
    "getExaminationsForSubclass",
])->name("get_examinations_for_subclass");
Route::get("get_subclass_results/{subclassID}", [
    ManageClassessController::class,
    "getSubclassResults",
])->name("get_subclass_results");
Route::get("get_subclass_results/{subclassID}/{examID}", [
    ManageClassessController::class,
    "getSubclassResults",
])->name("get_subclass_results_by_exam");
Route::get("get_student_detailed_results/{studentID}", [
    ManageClassessController::class,
    "getStudentDetailedResults",
])->name("get_student_detailed_results");
Route::get("get_student_detailed_results/{studentID}/{examID}", [
    ManageClassessController::class,
    "getStudentDetailedResults",
])->name("get_student_detailed_results_by_exam");
Route::get("download_student_results_pdf/{studentID}/{examID}", [
    ManageClassessController::class,
    "downloadStudentResultsPDF",
])->name("download_student_results_pdf");

// Grade Definitions Routes
Route::get("manage_grade_definitions", [
    GradeDefinitionController::class,
    "index",
])->name("grade_definitions.index");
Route::post("grade_definitions", [
    GradeDefinitionController::class,
    "store",
])->name("grade_definitions.store");
Route::post("grade_definitions/bulk", [
    GradeDefinitionController::class,
    "bulkStore",
])->name("grade_definitions.bulk_store");
Route::get("grade_definitions/{id}", [
    GradeDefinitionController::class,
    "show",
])->name("grade_definitions.show");
Route::put("grade_definitions/{id}", [
    GradeDefinitionController::class,
    "update",
])->name("grade_definitions.update");
Route::delete("grade_definitions/{id}", [
    GradeDefinitionController::class,
    "destroy",
])->name("grade_definitions.destroy");

// Student Routes
// Student Management Routes
Route::get("manage_student", [
    ManageStudentController::class,
    "manage_student",
])->name("manage_student");
Route::get("manage_student_page.js", [
    ManageStudentController::class,
    "manage_student_page_js",
])->name("manage_student_page_js");

// School Online Admission (Admin configuration + approvals)
Route::get("online-admission", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "index",
])->name("school.online_admission.index");
Route::get("online-admission/form-fields", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "formFields",
])->name("school.online_admission.form_fields");
Route::post("online-admission/form-fields/sync", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "saveFormFields",
])->name("school.online_admission.form_fields.sync");
Route::post("online-admission/form-fields", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "storeFormField",
])->name("school.online_admission.form_fields.store");
Route::put("online-admission/form-fields/{id}", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "updateFormField",
])->name("school.online_admission.form_fields.update");
Route::delete("online-admission/form-fields/{id}", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "deleteFormField",
])->name("school.online_admission.form_fields.delete");

Route::get("online-admission/form-sections", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "formSections",
])->name("school.online_admission.form_sections");
Route::post("online-admission/form-sections", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "storeFormSection",
])->name("school.online_admission.form_sections.store");
Route::put("online-admission/form-sections/{id}", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "updateFormSection",
])->name("school.online_admission.form_sections.update");
Route::delete("online-admission/form-sections/{id}", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "deleteFormSection",
])->name("school.online_admission.form_sections.delete");

Route::get("online-admission/spaces", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "admissionSpaces",
])->name("school.online_admission.spaces");
Route::post("online-admission/spaces", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "saveAdmissionSpaces",
])->name("school.online_admission.spaces.save");

Route::get("online-admission/applications", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "applications",
])->name("school.online_admission.applications");
Route::get("online-admission/applications/{id}", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "showApplication",
])->name("school.online_admission.applications.show");
Route::get("online-admission/applications/{id}/pdf", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "downloadApplicationPdf",
])->name("school.online_admission.applications.pdf");
Route::post("online-admission/applications/{id}/confirm", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "confirmApplication",
])->name("school.online_admission.applications.confirm");
Route::post("online-admission/applications/{id}/reject", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "rejectApplication",
])->name("school.online_admission.applications.reject");
Route::delete("online-admission/applications/{id}", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "cancelApplication",
])->name("school.online_admission.applications.cancel");

Route::get("online-admission/preview", [
    \App\Http\Controllers\SchoolOnlineAdmissionController::class,
    "previewForm",
])->name("school.online_admission.preview");
Route::get("get_students_list", [
    ManageStudentController::class,
    "get_students",
])->name("get_students_list");
Route::get("get_student_statistics", [
    ManageStudentController::class,
    "get_student_statistics",
])->name("get_student_statistics");
Route::get("export_students_pdf", [
    ManageStudentController::class,
    "export_students_pdf",
])->name("export_students_pdf");
Route::get("admin/export-student-id-card-pdf/{studentID}", [
    ManageStudentController::class,
    "exportStudentIDCardPdf",
])->name("admin.export_student_id_card_pdf");
Route::get("export_students_excel", [
    ManageStudentController::class,
    "export_students_excel",
])->name("export_students_excel");
Route::get("download_student_template", [
    ManageStudentController::class,
    "downloadTemplate",
])->name("download_student_template");
Route::post("upload_students", [
    ManageStudentController::class,
    "uploadStudents",
])->name("upload_students");
Route::get("get_subclasses_with_stats", [
    ManageStudentController::class,
    "getSubclassesWithStats",
])->name("get_subclasses_with_stats");
// Student Registration Routes
Route::get("student/registration/step1", [
    StudentRegistrationController::class,
    "showStep1",
])->name("student.registration.step1");
Route::post("student/registration/store-step1", [
    StudentRegistrationController::class,
    "storeStep1",
])->name("student.registration.store-step1");
Route::get("student/registration/step2", [
    StudentRegistrationController::class,
    "showStep2",
])->name("student.registration.step2");
Route::post("student/registration/store-step2", [
    StudentRegistrationController::class,
    "storeStep2",
])->name("student.registration.store-step2");
Route::get("student/registration/step3", [
    StudentRegistrationController::class,
    "showStep3",
])->name("student.registration.step3");
Route::post("student/registration/store-step3", [
    StudentRegistrationController::class,
    "storeStep3",
])->name("student.registration.store-step3");
Route::get("student/registration/step4", [
    StudentRegistrationController::class,
    "showStep4",
])->name("student.registration.step4");
Route::post("student/registration/store-step4", [
    StudentRegistrationController::class,
    "storeStep4",
])->name("student.registration.store-step4");
Route::get("student/registration/step5", [
    StudentRegistrationController::class,
    "showStep5",
])->name("student.registration.step5");
Route::post("student/registration/store-step5", [
    StudentRegistrationController::class,
    "storeStep5",
])->name("student.registration.store-step5");
Route::get("student/registration/success/{studentID}", [
    StudentRegistrationController::class,
    "showSuccess",
])->name("student.registration.success");
Route::get("student/registration/cancel", [
    StudentRegistrationController::class,
    "cancelRegistration",
])->name("student.registration.cancel");
Route::post("student/registration/search-parent", [
    StudentRegistrationController::class,
    "searchParentByPhone",
])->name("student.registration.search-parent");
Route::post("student/registration/store-complete", [
    StudentRegistrationController::class,
    "storeComplete",
])->name("student.registration.store-complete");
Route::get("get_student_details/{studentID}", [
    ManageStudentController::class,
    "get_student_details",
])->name("get_student_details");
Route::post("save_student", [
    ManageStudentController::class,
    "save_student",
])->name("save_student");

// Quick student registration + enroll (no full-form required)
Route::post("quick_register_student", [
    \App\Http\Controllers\QuickStudentController::class,
    "quickRegister",
])->name("quick_register_student");
Route::get("quick_register_template", [
    \App\Http\Controllers\QuickStudentController::class,
    "downloadQuickTemplate",
])->name("quick_register_template");
Route::get("quick_register_export", [
    \App\Http\Controllers\QuickStudentController::class,
    "exportUnenrolledStudents",
])->name("quick_register_export");
Route::post("quick_register_import", [
    \App\Http\Controllers\QuickStudentController::class,
    "importQuickStudents",
])->name("quick_register_import");
Route::get("quick_enroll_students_list", [
    \App\Http\Controllers\QuickStudentController::class,
    "listForEnroll",
])->name("quick_enroll_students_list");
Route::post("quick_enroll_students", [
    \App\Http\Controllers\QuickStudentController::class,
    "enrollStudents",
])->name("quick_enroll_students");
Route::get("quick_enrolled_students_list", [
    \App\Http\Controllers\QuickStudentController::class,
    "listEnrolled",
])->name("quick_enrolled_students_list");
Route::post("quick_remove_enrollment", [
    \App\Http\Controllers\QuickStudentController::class,
    "removeEnrollment",
])->name("quick_remove_enrollment");
Route::post("test_device_connection", [
    ManageStudentController::class,
    "test_device_connection",
])->name("test_device_connection");
Route::post("retrieve_users_from_device", [
    ManageStudentController::class,
    "retrieve_users_from_device",
])->name("retrieve_users_from_device");
Route::post("check_fingerprint_progress", [
    ManageStudentController::class,
    "check_fingerprint_progress",
])->name("check_fingerprint_progress");
Route::get("get_student/{studentID}", [
    ManageStudentController::class,
    "get_student",
])->name("get_student");
Route::post("update_student", [
    ManageStudentController::class,
    "update_student",
])->name("update_student");
Route::post("transfer_student", [
    ManageStudentController::class,
    "transfer_student",
])->name("transfer_student");
Route::get("move_students_preview", [
    ManageStudentController::class,
    "move_students_preview",
])->name("move_students_preview");
Route::post("move_students_execute", [
    ManageStudentController::class,
    "move_students_execute",
])->name("move_students_execute");
Route::get("move_students_batches", [
    ManageStudentController::class,
    "move_students_batches",
])->name("move_students_batches");
Route::post("move_students_undo", [
    ManageStudentController::class,
    "move_students_undo",
])->name("move_students_undo");
Route::post("move_students_undo_item", [
    ManageStudentController::class,
    "move_students_undo_item",
])->name("move_students_undo_item");
Route::get("move_student_options/{studentID}", [
    ManageStudentController::class,
    "move_student_options",
])->name("move_student_options");
Route::post("move_student_single", [
    ManageStudentController::class,
    "move_student_single",
])->name("move_student_single");
Route::post("update_enrollment_exit_status", [
    ManageStudentController::class,
    "update_enrollment_exit_status",
])->name("update_enrollment_exit_status");
Route::delete("delete_student/{studentID}", [
    ManageStudentController::class,
    "delete_student",
])->name("delete_student");
Route::delete("delete_not_enrolled_student/{studentID}", [
    ManageStudentController::class,
    "delete_not_enrolled_student",
])->name("delete_not_enrolled_student");
Route::post("delete_all_not_enrolled_students", [
    ManageStudentController::class,
    "delete_all_not_enrolled_students",
])->name("delete_all_not_enrolled_students");
Route::post("delete_selected_not_enrolled_students", [
    ManageStudentController::class,
    "delete_selected_not_enrolled_students",
])->name("delete_selected_not_enrolled_students");
Route::post("activate_student/{studentID}", [
    ManageStudentController::class,
    "activate_student",
])->name("activate_student");

// Student Enrollment History (view student modal)
Route::get("get_student_enrollment_history/{studentID}", [
    ManageStudentController::class,
    "get_student_enrollment_history",
])->name("get_student_enrollment_history");

// Student Academic Details Routes
Route::get("get_student_academic_years/{studentID}", [
    ManageStudentController::class,
    "get_student_academic_years",
])->name("get_student_academic_years");
Route::get("get_student_classes_for_year", [
    ManageStudentController::class,
    "get_student_classes_for_year",
])->name("get_student_classes_for_year");
Route::get("get_student_subclasses_for_class", [
    ManageStudentController::class,
    "get_student_subclasses_for_class",
])->name("get_student_subclasses_for_class");
Route::get("get_student_terms_for_year", [
    ManageStudentController::class,
    "get_student_terms_for_year",
])->name("get_student_terms_for_year");
Route::get("get_exams_for_term", [
    ManageStudentController::class,
    "get_exams_for_term",
])->name("get_exams_for_term");
Route::get("get_student_exam_results", [
    ManageStudentController::class,
    "get_student_exam_results",
])->name("get_student_exam_results");
Route::get("get_student_term_report", [
    ManageStudentController::class,
    "get_student_term_report",
])->name("get_student_term_report");
Route::get("get_student_attendance", [
    ManageStudentController::class,
    "get_student_attendance",
])->name("get_student_attendance");
Route::get("get_student_payments_for_year", [
    ManageStudentController::class,
    "get_student_payments_for_year",
])->name("get_student_payments_for_year");
Route::get("get_student_debts_for_year", [
    ManageStudentController::class,
    "get_student_debts_for_year",
])->name("get_student_debts_for_year");
Route::get("get_student_library_for_year", [
    ManageStudentController::class,
    "get_student_library_for_year",
])->name("get_student_library_for_year");
Route::get("get_student_fees_for_year", [
    ManageStudentController::class,
    "get_student_fees_for_year",
])->name("get_student_fees_for_year");
Route::get("export_student_results_pdf", [
    ManageStudentController::class,
    "export_student_results_pdf",
])->name("export_student_results_pdf");
Route::get("export_student_attendance_pdf", [
    ManageStudentController::class,
    "export_student_attendance_pdf",
])->name("export_student_attendance_pdf");
Route::get("export_student_attendance_excel", [
    ManageStudentController::class,
    "export_student_attendance_excel",
])->name("export_student_attendance_excel");
Route::post("revert_transfer/{studentID}", [
    ManageStudentController::class,
    "revert_transfer",
])->name("revert_transfer");
Route::get("download_students_pdf/{subclassID}", [
    ManageStudentController::class,
    "download_students_pdf",
])->name("download_students_pdf");
Route::get("get_subclasses_for_school", [
    ManageClassessController::class,
    "get_subclasses_for_school",
])->name("get_subclasses_for_school");
Route::get("get_eligible_subclasses_for_transfer/{studentID}", [
    ManageClassessController::class,
    "get_eligible_subclasses_for_transfer",
])->name("get_eligible_subclasses_for_transfer");

// Parent Routes
Route::get("manage_parents", [
    ManageParentsController::class,
    "manage_parents",
])->name("manage_parents");

// Other Staff Routes
Route::get("manage_other_staff", [
    ManageOtherStaffController::class,
    "manageOtherStaff",
])->name("manage_other_staff");
Route::post("save_other_staff", [
    ManageOtherStaffController::class,
    "save_other_staff",
])->name("save_other_staff");
Route::get("get_other_staff/{id}", [
    ManageOtherStaffController::class,
    "get_other_staff",
])->name("get_other_staff");
Route::post("update_other_staff", [
    ManageOtherStaffController::class,
    "update_other_staff",
])->name("update_other_staff");
Route::delete("delete_other_staff/{id}", [
    ManageOtherStaffController::class,
    "delete_other_staff",
])->name("delete_other_staff");
Route::post("send_staff_to_fingerprint", [
    ManageOtherStaffController::class,
    "send_staff_to_fingerprint",
])->name("send_staff_to_fingerprint");

// Staff Profession Routes
Route::post("save_staff_profession", [
    ManageOtherStaffController::class,
    "save_staff_profession",
])->name("save_staff_profession");
Route::get("get_staff_profession/{id}", [
    ManageOtherStaffController::class,
    "get_staff_profession",
])->name("get_staff_profession");
Route::post("update_staff_profession", [
    ManageOtherStaffController::class,
    "update_staff_profession",
])->name("update_staff_profession");
Route::delete("delete_staff_profession/{id}", [
    ManageOtherStaffController::class,
    "delete_staff_profession",
])->name("delete_staff_profession");
Route::get("get_staff_profession_with_permissions/{id}", [
    ManageOtherStaffController::class,
    "get_staff_profession_with_permissions",
])->name("get_staff_profession_with_permissions");

// Temporary debug route to verify server receives browser requests
Route::get("debug/ping", function () {
    \Illuminate\Support\Facades\Log::info("debug/ping hit from browser", [
        "path" => request()->path(),
        "ip" => request()->ip(),
    ]);
    return response()->json([
        "pong" => true,
        "time" => now()->toDateTimeString(),
    ]);
});

// Staff Permissions/Duties Routes
Route::post("save_staff_permissions", [
    ManageOtherStaffController::class,
    "save_staff_permissions",
])->name("save_staff_permissions");

// Teacher Duties Routes
use App\Http\Controllers\TeacherDutyController;
Route::get("admin/teacher-duties", [
    TeacherDutyController::class,
    "index",
])->name("admin.teacher_duties");
Route::post("admin/teacher-duties/store", [
    TeacherDutyController::class,
    "store",
])->name("admin.teacher_duties.store");
Route::delete("admin/teacher-duties/destroy", [
    TeacherDutyController::class,
    "destroy",
])->name("admin.teacher_duties.destroy");
Route::get("admin/teacher-duties/export-pdf", [
    TeacherDutyController::class,
    "export_pdf",
])->name("admin.teacher_duties.export_pdf");
Route::get("admin/teacher-duties/report", [
    TeacherDutyController::class,
    "report",
])->name("admin.teacher_duties.report");
Route::post("admin/teacher-duties/approve", [
    TeacherDutyController::class,
    "approveReport",
])->name("admin.duty_book.approve");
Route::get("teacher/duty-book", [
    TeacherDutyController::class,
    "teacherIndex",
])->name("teacher.duty_book");
Route::get("teacher/duty-book/export", [
    TeacherDutyController::class,
    "export_pdf",
])->name("teacher.duty_book.export");
Route::get("teacher/duty-book/export-report", [
    TeacherDutyController::class,
    "exportDailyReportPdf",
])->name("teacher.duty_book.export_report");
Route::get("teacher/duty-book/report", [
    TeacherDutyController::class,
    "getDailyReport",
])->name("teacher.duty_book.report");
Route::post("teacher/duty-book/save", [
    TeacherDutyController::class,
    "saveDailyReport",
])->name("teacher.duty_book.save");

// Accountant Module Routes
Route::prefix("accountant")
    ->name("accountant.")
    ->group(function () {
        Route::get("expenses", [ExpenseController::class, "index"])->name(
            "expenses.index",
        );
        Route::get("expenses/create", [
            ExpenseController::class,
            "create",
        ])->name("expenses.create");
        Route::post("expenses", [ExpenseController::class, "store"])->name(
            "expenses.store",
        );

        // Expense Categories Management
        Route::get("expense-categories", [
            ExpenseCategoryController::class,
            "index",
        ])->name("expense_categories.index");
        Route::post("expense-categories", [
            ExpenseCategoryController::class,
            "store",
        ])->name("expense_categories.store");
        Route::post("expense-categories/{id}", [
            ExpenseCategoryController::class,
            "update",
        ])->name("expense_categories.update");
        Route::delete("expense-categories/{id}", [
            ExpenseCategoryController::class,
            "destroy",
        ])->name("expense_categories.destroy");

        Route::get("expenses/{id}", [ExpenseController::class, "show"])->name(
            "expenses.show",
        );
        Route::get("expenses/{id}/edit", [
            ExpenseController::class,
            "edit",
        ])->name("expenses.edit");
        Route::put("expenses/{id}", [ExpenseController::class, "update"])->name(
            "expenses.update",
        );
        Route::delete("expenses/{id}", [
            ExpenseController::class,
            "destroy",
        ])->name("expenses.destroy");
        Route::post("expenses/{id}/approve", [
            ExpenseController::class,
            "approve",
        ])->name("expenses.approve");
        Route::post("expenses/{id}/reject", [
            ExpenseController::class,
            "reject",
        ])->name("expenses.reject");

        // Income Categories Management
        Route::get("income-categories", [
            IncomeCategoryController::class,
            "index",
        ])->name("income_categories.index");
        Route::post("income-categories", [
            IncomeCategoryController::class,
            "store",
        ])->name("income_categories.store");
        Route::post("income-categories/{id}", [
            IncomeCategoryController::class,
            "update",
        ])->name("income_categories.update");
        Route::delete("income-categories/{id}", [
            IncomeCategoryController::class,
            "destroy",
        ])->name("income_categories.destroy");

        Route::get("income", [IncomeController::class, "index"])->name(
            "income.index",
        );
        Route::get("income/create", [IncomeController::class, "create"])->name(
            "income.create",
        );
        Route::post("income", [IncomeController::class, "store"])->name(
            "income.store",
        );
        Route::get("income/{id}", [IncomeController::class, "show"])->name(
            "income.show",
        );
        Route::get("income/{id}/edit", [IncomeController::class, "edit"])->name(
            "income.edit",
        );
        Route::put("income/{id}", [IncomeController::class, "update"])->name(
            "income.update",
        );
        Route::delete("income/{id}", [
            IncomeController::class,
            "destroy",
        ])->name("income.destroy");

        Route::get("budget", [BudgetController::class, "index"])->name(
            "budget.index",
        );
        Route::get("budget/create", [BudgetController::class, "create"])->name(
            "budget.create",
        );
        Route::post("budget", [BudgetController::class, "store"])->name(
            "budget.store",
        );
        Route::get("budget/{id}", [BudgetController::class, "show"])->name(
            "budget.show",
        );
        Route::get("budget/{id}/edit", [BudgetController::class, "edit"])->name(
            "budget.edit",
        );
        Route::put("budget/{id}", [BudgetController::class, "update"])->name(
            "budget.update",
        );
        Route::delete("budget/{id}", [
            BudgetController::class,
            "destroy",
        ])->name("budget.destroy");

        // Reports
        Route::get("reports", [
            AccountantReportController::class,
            "index",
        ])->name("reports.index");
        Route::get("reports/chart-data", [
            AccountantReportController::class,
            "getChartData",
        ])->name("reports.chart_data");
        Route::get("reports/export/expenses", [
            AccountantReportController::class,
            "exportExpenses",
        ])->name("reports.export_expenses");
        Route::get("reports/export/income", [
            AccountantReportController::class,
            "exportIncome",
        ])->name("reports.export_income");

        // PDF Exports
        Route::get("reports/export-pdf", [
            AccountantReportController::class,
            "exportReportPdf",
        ])->name("reports.export_pdf");
        Route::get("reports/export-expenses-pdf", [
            AccountantReportController::class,
            "exportExpensesPdf",
        ])->name("reports.export_expenses_pdf");
        Route::get("reports/export-income-pdf", [
            AccountantReportController::class,
            "exportIncomePdf",
        ])->name("reports.export_income_pdf");
        Route::get("reports/export-budget-pdf", [
            AccountantReportController::class,
            "exportBudgetPdf",
        ])->name("reports.export_budget_pdf");
    });

// Revenue and Expenses Routes
Route::get("manage_revenue", [AdminController::class, "manageRevenue"])->name(
    "manage_revenue",
);
Route::post("revenue-sources", [
    AdminController::class,
    "storeRevenueSource",
])->name("revenue_sources.store");
Route::post("revenue-sources/update", [
    AdminController::class,
    "updateRevenueSource",
])->name("revenue_sources.update");
Route::post("revenue-sources/delete", [
    AdminController::class,
    "deleteRevenueSource",
])->name("revenue_sources.delete");
Route::post("revenue-records", [
    AdminController::class,
    "storeRevenueRecord",
])->name("revenue_records.store");
Route::get("revenue-report-data", [
    AdminController::class,
    "revenueReportData",
])->name("revenue_report.data");
Route::get("manage_expenses", [AdminController::class, "manageExpenses"])->name(
    "manage_expenses",
);
Route::post("expense-budgets", [
    AdminController::class,
    "storeExpenseBudget",
])->name("expense_budgets.store");
Route::post("expense-budgets/update", [
    AdminController::class,
    "updateExpenseBudget",
])->name("expense_budgets.update");
Route::post("expense-records", [
    AdminController::class,
    "storeExpenseRecord",
])->name("expense_records.store");
Route::post("expense-records/update", [
    AdminController::class,
    "updateExpenseRecord",
])->name("expense_records.update");
Route::post("expense-records/delete", [
    AdminController::class,
    "deleteExpenseRecord",
])->name("expense_records.delete");

// School Resources Routes
Route::get("manage_incoming_resources", [
    AdminController::class,
    "manageIncomingResources",
])->name("manage_incoming_resources");
Route::post("school-resources", [
    AdminController::class,
    "storeSchoolResources",
])->name("school_resources.store");
Route::post("school-resources/update", [
    AdminController::class,
    "updateSchoolResource",
])->name("school_resources.update");
Route::post("school-resources/delete", [
    AdminController::class,
    "deleteSchoolResource",
])->name("school_resources.delete");
Route::post("incoming-resources", [
    AdminController::class,
    "storeIncomingResource",
])->name("incoming_resources.store");
Route::get("manage_outgoing_resources", [
    AdminController::class,
    "manageOutgoingResources",
])->name("manage_outgoing_resources");
Route::post("outgoing-resources", [
    AdminController::class,
    "storeOutgoingResource",
])->name("outgoing_resources.store");
Route::post("outgoing-resources/update", [
    AdminController::class,
    "updateOutgoingResource",
])->name("outgoing_resources.update");
Route::post("outgoing-resources/delete", [
    AdminController::class,
    "deleteOutgoingResource",
])->name("outgoing_resources.delete");
Route::post("outgoing-resources/return", [
    AdminController::class,
    "returnOutgoingResource",
])->name("outgoing_resources.return");
Route::get("manage_buildings_infrastructure", [
    AdminController::class,
    "manageBuildingsInfrastructure",
])->name("manage_buildings_infrastructure");
Route::get("manage_desks", [AdminController::class, "manageDesks"])->name(
    "manage_desks",
);
Route::get("manage_chairs", [AdminController::class, "manageChairs"])->name(
    "manage_chairs",
);
Route::get("manage_chalk", [AdminController::class, "manageChalk"])->name(
    "manage_chalk",
);
Route::get("manage_books", [AdminController::class, "manageBooks"])->name(
    "manage_books",
);
Route::get("manage_teaching_aids", [
    AdminController::class,
    "manageTeachingAids",
])->name("manage_teaching_aids");
Route::get("inventory_list", [AdminController::class, "inventoryList"])->name(
    "inventory_list",
);
Route::get("manage_damaged_lost_items", [
    AdminController::class,
    "manageDamagedLostItems",
])->name("manage_damaged_lost_items");

// Store / Requisition
Route::get("manage_store", [StoreController::class, "manageStore"])->name("manage_store");
Route::get("items_requests", [StoreController::class, "itemsRequests"])->name("items_requests");
Route::get("store/measurement-types/{measurementTypeID}/units", [
    StoreController::class,
    "getUnitsByType",
])->name("store.units_by_type");
Route::post("store/categories", [StoreController::class, "storeCategory"])->name("store.categories.store");
Route::post("store/categories/{categoryID}/update", [StoreController::class, "updateCategory"])->name("store.categories.update");
Route::post("store/categories/{categoryID}/delete", [StoreController::class, "deleteCategory"])->name("store.categories.delete");
Route::post("store/measurement-types", [StoreController::class, "storeMeasurementType"])->name("store.measurement_types.store");
Route::post("store/measurement-types/{measurementTypeID}/update", [StoreController::class, "updateMeasurementType"])->name("store.measurement_types.update");
Route::post("store/measurement-types/{measurementTypeID}/delete", [StoreController::class, "deleteMeasurementType"])->name("store.measurement_types.delete");
Route::post("store/units", [StoreController::class, "storeUnit"])->name("store.units.store");
Route::post("store/units/{unitID}/update", [StoreController::class, "updateUnit"])->name("store.units.update");
Route::post("store/units/{unitID}/delete", [StoreController::class, "deleteUnit"])->name("store.units.delete");
Route::post("store/items", [StoreController::class, "storeItem"])->name("store.items.store");
Route::post("store/items/{itemID}/update", [StoreController::class, "updateItem"])->name("store.items.update");
Route::post("store/items/{itemID}/delete", [StoreController::class, "deleteItem"])->name("store.items.delete");
Route::post("store/packages/bulk", [StoreController::class, "storePackagesBulk"])->name("store.packages.bulk");
Route::post("store/packages/{packageID}/update", [StoreController::class, "updatePackage"])->name("store.packages.update");
Route::post("store/packages/{packageID}/delete", [StoreController::class, "deletePackage"])->name("store.packages.delete");
Route::post("store/stock", [StoreController::class, "storeItemStock"])->name("store.stock.store");
Route::post("store/stock/{stockEntryID}/delete", [StoreController::class, "deleteItemStock"])->name("store.stock.delete");
Route::post("store/requisitions", [StoreController::class, "storeRequisition"])->name("store.requisitions.store");
Route::post("store/requisitions/{requisitionID}/return", [StoreController::class, "returnRequisition"])->name("store.requisitions.return");
Route::post("store/incidents", [StoreController::class, "storeIncident"])->name("store.incidents.store");
Route::post("store/incidents/{incidentID}/update", [StoreController::class, "updateIncident"])->name("store.incidents.update");
Route::post("store/incidents/{incidentID}/delete", [StoreController::class, "deleteIncident"])->name("store.incidents.delete");
Route::post("store/incidents/{incidentID}/resolve", [StoreController::class, "resolveIncident"])->name("store.incidents.resolve");
Route::get("store/dashboard", [StoreController::class, "storeDashboard"])->name("store.dashboard");
Route::get("store/reports/all-inventory", [StoreController::class, "allInventoryReport"])->name("store.reports.all_inventory");
Route::get("store/reports/requisitions", [StoreController::class, "requisitionReport"])->name("store.reports.requisitions");
Route::get("store/reports/specific-item", [StoreController::class, "specificItemReport"])->name("store.reports.specific_item");
Route::get("store/export-pdf", [StoreController::class, "exportStorePdf"])->name("store.export_pdf");
Route::post("store/item-requests/{requestID}/approve", [StoreController::class, "approveItemRequest"])->name("store.item_requests.approve");
Route::post("store/item-requests/{requestID}/reject", [StoreController::class, "rejectItemRequest"])->name("store.item_requests.reject");
Route::post("store/item-requests/mark-read", [StoreController::class, "markItemRequestsRead"])->name("store.item_requests.mark_read");
Route::get("store/inventory-actors", [StoreController::class, "listInventoryActors"])->name("store.inventory_actors.index");
Route::post("store/inventory-actors", [StoreController::class, "storeInventoryActor"])->name("store.inventory_actors.store");
Route::post("store/inventory-actors/{actorID}/update", [StoreController::class, "updateInventoryActor"])->name("store.inventory_actors.update");
Route::post("store/inventory-actors/{actorID}/delete", [StoreController::class, "deleteInventoryActor"])->name("store.inventory_actors.delete");

// Black Book / Discipline
Route::get("manage_black_book", [BlackBookController::class, "manageBlackBook"])->name("manage_black_book");
Route::get("black_book/report-student", [BlackBookController::class, "reportStudentPage"])->name("black_book.report_student");
Route::get("black_book/dashboard-stats", [BlackBookController::class, "dashboardStats"])->name("black_book.dashboard_stats");
Route::get("black_book/problem-categories", [BlackBookController::class, "listProblemCategories"])->name("black_book.problem_categories.index");
Route::post("black_book/problem-categories", [BlackBookController::class, "storeProblemCategory"])->name("black_book.problem_categories.store");
Route::post("black_book/problem-categories/{categoryID}/update", [BlackBookController::class, "updateProblemCategory"])->name("black_book.problem_categories.update");
Route::post("black_book/problem-categories/{categoryID}/delete", [BlackBookController::class, "deleteProblemCategory"])->name("black_book.problem_categories.delete");
Route::get("black_book/punishment-categories", [BlackBookController::class, "listPunishmentCategories"])->name("black_book.punishment_categories.index");
Route::post("black_book/punishment-categories", [BlackBookController::class, "storePunishmentCategory"])->name("black_book.punishment_categories.store");
Route::post("black_book/punishment-categories/{punishmentCategoryID}/update", [BlackBookController::class, "updatePunishmentCategory"])->name("black_book.punishment_categories.update");
Route::post("black_book/punishment-categories/{punishmentCategoryID}/delete", [BlackBookController::class, "deletePunishmentCategory"])->name("black_book.punishment_categories.delete");
Route::get("black_book/students/search", [BlackBookController::class, "searchStudents"])->name("black_book.students.search");
Route::post("black_book/reports", [BlackBookController::class, "storeReport"])->name("black_book.reports.store");
Route::get("black_book/reports", [BlackBookController::class, "listReports"])->name("black_book.reports.index");
Route::post("black_book/reports/{reportID}/view", [BlackBookController::class, "markViewed"])->name("black_book.reports.view");
Route::post("black_book/reports/{reportID}/resolve", [BlackBookController::class, "resolveReport"])->name("black_book.reports.resolve");
Route::post("black_book/reports/{reportID}/end-monitoring", [BlackBookController::class, "endMonitoring"])->name("black_book.reports.end_monitoring");
Route::get("black_book/student-summary", [BlackBookController::class, "studentReportSummary"])->name("black_book.student_summary");
Route::get("black_book/report-analytics", [BlackBookController::class, "reportAnalytics"])->name("black_book.report_analytics");
Route::get("black_book/export-pdf", [BlackBookController::class, "exportPdf"])->name("black_book.export_pdf");
Route::get("black_book/actors", [BlackBookController::class, "listBlackBookActors"])->name("black_book.actors.index");
Route::post("black_book/actors", [BlackBookController::class, "storeBlackBookActor"])->name("black_book.actors.store");
Route::post("black_book/actors/{actorID}/update", [BlackBookController::class, "updateBlackBookActor"])->name("black_book.actors.update");
Route::post("black_book/actors/{actorID}/delete", [BlackBookController::class, "deleteBlackBookActor"])->name("black_book.actors.delete");

Route::get("teacher/store-requisitions", [StorePortalController::class, "teacherPortal"])->name("teacher.store_requisitions");
Route::post("teacher/store-requisitions", [StorePortalController::class, "teacherStoreRequest"])->name("teacher.store_requisitions.store");
Route::get("teacher/store-requisitions/export-pdf", [StorePortalController::class, "teacherExportPdf"])->name("teacher.store_requisitions.export_pdf");

Route::get("staff/store-requisitions", [StorePortalController::class, "staffPortal"])->name("staff.store_requisitions");
Route::post("staff/store-requisitions", [StorePortalController::class, "staffStoreRequest"])->name("staff.store_requisitions.store");
Route::get("staff/store-requisitions/export-pdf", [StorePortalController::class, "staffExportPdf"])->name("staff.store_requisitions.export_pdf");

Route::get("admin/suggestions", [
    AdminController::class,
    "manageTeacherFeedbackAdmin",
])->name("admin.suggestions");
Route::get("admin/incidents", [
    AdminController::class,
    "manageTeacherFeedbackAdmin",
])->name("admin.incidents");
Route::post("admin/feedback/approve", [
    AdminController::class,
    "approveTeacherFeedback",
])->name("admin.feedback.approve");
Route::post("admin/feedback/reject", [
    AdminController::class,
    "rejectTeacherFeedback",
])->name("admin.feedback.reject");
Route::get("admin/staff-suggestions", [
    AdminController::class,
    "manageStaffFeedbackAdmin",
])->name("admin.staff.suggestions");
Route::get("admin/staff-incidents", [
    AdminController::class,
    "manageStaffFeedbackAdmin",
])->name("admin.staff.incidents");
Route::post("admin/staff-feedback/approve", [
    AdminController::class,
    "approveStaffFeedback",
])->name("admin.staff.feedback.approve");
Route::post("admin/staff-feedback/reject", [
    AdminController::class,
    "rejectStaffFeedback",
])->name("admin.staff.feedback.reject");
Route::get("admin/performance", [
    AdminController::class,
    "performanceDashboard",
])->name("admin.performance");
Route::post("admin/performance/teacher/term", [
    AdminController::class,
    "teacherTermPerformance",
])->name("admin.performance.teacher.term");
Route::post("admin/performance/teacher/exam", [
    AdminController::class,
    "teacherExamPerformance",
])->name("admin.performance.teacher.exam");
Route::post("admin/performance/teacher/year", [
    AdminController::class,
    "teacherYearPerformance",
])->name("admin.performance.teacher.year");
Route::get("admin/performance/teacher/subjects", [
    AdminController::class,
    "teacherSubjectsForPerformance",
])->name("admin.performance.teacher.subjects");
Route::get("admin/performance/class/subjects", [
    AdminController::class,
    "classSubjectsForPerformance",
])->name("admin.performance.class.subjects");
Route::get("admin/performance/class/subclasses", [
    AdminController::class,
    "classSubclassesForPerformance",
])->name("admin.performance.class.subclasses");
Route::get("admin/performance/class/students", [
    AdminController::class,
    "classStudentsForPerformance",
])->name("admin.performance.class.students");
Route::post("admin/performance/student/term", [
    AdminController::class,
    "studentTermPerformance",
])->name("admin.performance.student.term");
Route::post("admin/performance/student/exam", [
    AdminController::class,
    "studentExamPerformance",
])->name("admin.performance.student.exam");
Route::post("admin/performance/student/year", [
    AdminController::class,
    "studentYearPerformance",
])->name("admin.performance.student.year");

Route::get("admin/hr/permissions", [
    AdminController::class,
    "managePermissionsAdmin",
])->name("admin.hr.permission");
Route::get("admin/hr/permissions/attachment/{permissionID}", [
    AdminController::class,
    "viewPermissionAttachment",
])->name("admin.permissions.attachment");
Route::post("admin/hr/permissions/approve", [
    AdminController::class,
    "approvePermission",
])->name("admin.permissions.approve");
Route::post("admin/hr/permissions/reject", [
    AdminController::class,
    "rejectPermission",
])->name("admin.permissions.reject");
Route::post("admin/hr/permissions/chain/save", [
    AdminController::class,
    "savePermissionApprovalChain",
])->name("admin.permissions.chain.save");
Route::get("admin/hr/permissions/chain/get", [
    AdminController::class,
    "getPermissionApprovalChain",
])->name("admin.permissions.chain.get");
Route::get("admin/hr/permissions/chain/view", [
    AdminController::class,
    "viewPermissionApprovalChainSettings",
])->name("admin.permissions.chain.view");
Route::get("admin/hr/permissions/chain/progress", [
    AdminController::class,
    "getPermissionProgress",
])->name("admin.permissions.progress");
Route::delete("admin/hr/permissions/chain/delete", [
    AdminController::class,
    "deletePermissionApprovalChain",
])->name("admin.permissions.chain.delete");
Route::get("admin/hr/permissions/export-pdf/{permissionID}", [
    AdminController::class,
    "exportPermissionPdf",
])->name("admin.permissions.download");
Route::get("admin/hr/roles-positions", [
    AdminController::class,
    "getRolesAndPositions",
])->name("admin.hr.roles_positions");
Route::get("admin/hr/permissions/pending-approvals", [
    AdminController::class,
    "getPendingApprovals",
])->name("admin.hr.permissions.pending_approvals");
Route::get("admin/student-permission-requesters/users", [
    AdminController::class,
    "getStudentPermissionRequestersUsers",
])->name("admin.student_permission_requesters.get_users");
Route::get("admin/student-permission-requesters", [
    AdminController::class,
    "indexStudentPermissionRequesters",
])->name("admin.student_permission_requesters.index");
Route::post("admin/student-permission-requesters", [
    AdminController::class,
    "storeStudentPermissionRequesters",
])->name("admin.student_permission_requesters.store");
Route::delete("admin/student-permission-requesters/{id}", [
    AdminController::class,
    "destroyStudentPermissionRequester",
])->name("admin.student_permission_requesters.destroy");
Route::get("admin/student-permissions/export-pdf/{id}", [
    AdminController::class,
    "exportStudentPermissionPdf",
])->name("admin.student_permissions.download");

Route::get("admin/school-visitors", [
    AdminController::class,
    "manageSchoolVisitors",
])->name("admin.school_visitors");
Route::get("admin/school-visitors/today", [
    AdminController::class,
    "todaySchoolVisitors",
])->name("admin.school_visitors.today");
Route::get("admin/school-visitors/list", [
    AdminController::class,
    "listSchoolVisitors",
])->name("admin.school_visitors.list");
Route::post("admin/school-visitors/store", [
    AdminController::class,
    "storeSchoolVisitors",
])->name("admin.school_visitors.store");
Route::post("admin/school-visitors/update", [
    AdminController::class,
    "updateSchoolVisitor",
])->name("admin.school_visitors.update");
Route::post("admin/school-visitors/delete", [
    AdminController::class,
    "deleteSchoolVisitor",
])->name("admin.school_visitors.delete");
Route::post("admin/school-visitors/sms", [
    AdminController::class,
    "sendVisitorSms",
])->name("admin.school_visitors.sms");
Route::get("admin/visitor-notifications-count", [
    AdminController::class,
    "getVisitorNotificationCount",
])->name("admin.visitor_notifications_count");
Route::get("admin/get-recent-visitors", [
    AdminController::class,
    "getRecentVisitors",
])->name("admin.get_recent_visitors");
Route::post("admin/mark-visitor-notifications-read", [
    AdminController::class,
    "markVisitorNotificationsRead",
])->name("admin.mark_visitor_notifications_read");

Route::post("damaged-lost", [
    AdminController::class,
    "storeDamagedLostRecord",
])->name("damaged_lost.store");
Route::post("damaged-lost/update", [
    AdminController::class,
    "updateDamagedLostRecord",
])->name("damaged_lost.update");
Route::post("damaged-lost/delete", [
    AdminController::class,
    "deleteDamagedLostRecord",
])->name("damaged_lost.delete");
Route::get("resource_report", [AdminController::class, "resourceReport"])->name(
    "resource_report",
);
Route::get("usage_report", [AdminController::class, "usageReport"])->name(
    "usage_report",
);
Route::post("save_parent", [
    ManageParentsController::class,
    "save_parent",
])->name("save_parent");
Route::get("get_parents", [
    ManageParentsController::class,
    "get_parents",
])->name("get_parents");
Route::get("get_parent/{parentID}", [
    ManageParentsController::class,
    "get_parent",
])->name("get_parent");
Route::post("update_parent", [
    ManageParentsController::class,
    "update_parent",
])->name("update_parent");
Route::delete("delete_parent/{parentID}", [
    ManageParentsController::class,
    "delete_parent",
])->name("delete_parent");
Route::get("get_parents_for_pdf/{subclassID}", [
    ManageParentsController::class,
    "get_parents_for_pdf",
])->name("get_parents_for_pdf");
Route::get("get_parent_details/{parentID}", [
    ManageParentsController::class,
    "get_parent_details",
])->name("get_parent_details");
Route::post("register_parent_account", [
    ManageParentsController::class,
    "register_parent_account",
])->name("register_parent_account");
Route::post("register_all_parents", [
    ManageParentsController::class,
    "register_all_parents",
])->name("register_all_parents");
Route::post("send_password_sms", [
    ManageParentsController::class,
    "send_parent_password",
])->name("send_password_sms");

// Result Management Routes
Route::get("admin/add-results", [
    ResultManagementController::class,
    "addResultsView",
])->name("admin.add_results");

// Examination Routes
Route::get("exam_paper", [
    ManageExaminationController::class,
    "exam_paper",
])->name("exam_paper");
Route::get("manageExamination", [
    ManageExaminationController::class,
    "manageExamination",
])->name("manageExamination");
Route::post("search_examinations", [
    ManageExaminationController::class,
    "searchExaminations",
])->name("search_examinations");
Route::post("store_examination", [
    ManageExaminationController::class,
    "store",
])->name("store_examination");
Route::get("get_exam/{examID}", [
    ManageExaminationController::class,
    "getExam",
])->name("get_exam");
Route::get("get_exam_details/{examID}", [
    ManageExaminationController::class,
    "getExamDetails",
])->name("get_exam_details");
Route::post("update_examination/{examID}", [
    ManageExaminationController::class,
    "update",
])->name("update_examination");
Route::post("change_exam_status/{examID}", [
    ManageExaminationController::class,
    "changeStatus",
])->name("change_exam_status");
Route::post("approve_exam/{examID}", [
    ManageExaminationController::class,
    "approveExam",
])->name("approve_exam");
Route::delete("delete_examination/{examID}", [
    ManageExaminationController::class,
    "destroy",
])->name("delete_examination");
Route::get("get_subclasses_for_exam", [
    ManageExaminationController::class,
    "getSubclasses",
])->name("get_subclasses_for_exam");
Route::get("get_class_subjects_for_exam", [
    ManageExaminationController::class,
    "getClassSubjects",
])->name("get_class_subjects_for_exam");
Route::post("get_class_subjects_by_subclass", [
    ManageExaminationController::class,
    "getClassSubjects",
])->name("get_class_subjects_by_subclass_post");
Route::post("update_results_status/{examID}", [
    ManageExaminationController::class,
    "updateResultsStatus",
])->name("update_results_status");
Route::post("toggle_enter_result/{examID}", [
    ManageExaminationController::class,
    "toggleEnterResult",
])->name("toggle_enter_result");
Route::post("toggle_teacher_result_permission", [
    ManageExaminationController::class,
    "toggleTeacherResultPermission",
])->name("toggle_teacher_result_permission");
Route::post("toggle_publish_result/{examID}", [
    ManageExaminationController::class,
    "togglePublishResult",
])->name("toggle_publish_result");
Route::post("toggle_upload_paper/{examID}", [
    ManageExaminationController::class,
    "toggleUploadPaper",
])->name("toggle_upload_paper");
Route::post("auto_shift_students/{examID}", [
    ManageExaminationController::class,
    "autoShiftStudents",
])->name("auto_shift_students");
Route::get("exam_shift_preview/{examID}", [
    ManageExaminationController::class,
    "examShiftPreview",
])->name("exam_shift_preview");
Route::post("unshift_students/{examID}", [
    ManageExaminationController::class,
    "unshiftStudents",
])->name("unshift_students");
Route::post("update_exam_attendance/{examID}", [
    ManageExaminationController::class,
    "updateExamAttendance",
])->name("update_exam_attendance");
Route::get("get_class_student_counts/{classID}", [
    ManageExaminationController::class,
    "getClassStudentCounts",
])->name("get_class_student_counts");
Route::get("my_supervise_exams", [
    ManageExaminationController::class,
    "getMySuperviseExams",
])->name("my_supervise_exams");
Route::get("supervise_exam/view_students", [
    ManageExaminationController::class,
    "viewHallStudentsPage",
])->name("supervise_exam.view_students");
Route::get("supervise_exam/take_attendance", [
    ManageExaminationController::class,
    "takeAttendancePage",
])->name("supervise_exam.take_attendance");
Route::get("hall_students/{examHallID}", [
    ManageExaminationController::class,
    "getHallStudents",
])->name("get_hall_students");
Route::get("admin/get-exam-halls/{examID}", [
    ManageExaminationController::class,
    "getExamHalls",
])->name("admin.get_exam_halls");
Route::post("hall_attendance/{examHallID}", [
    ManageExaminationController::class,
    "updateHallAttendance",
])->name("update_hall_attendance");
Route::post("move_student_hall/{examHallID}", [
    ManageExaminationController::class,
    "moveStudentHall",
])->name("move_student_hall");

// Result Management Routes
Route::get("manageResults", [ResultManagementController::class, "index"])->name(
    "manageResults",
);
Route::post("send_teacher_reminder", [
    ResultManagementController::class,
    "sendTeacherReminder",
])->name("send_teacher_reminder");
Route::post("send_broadcast_reminder", [
    ResultManagementController::class,
    "sendBroadcastReminder",
])->name("send_broadcast_reminder");
Route::get("admin/subject-analysis", [
    ResultManagementController::class,
    "subjectAnalysis",
])->name("admin.subject_analysis");
Route::get("admin/add-results", [
    ResultManagementController::class,
    "addResultsView",
])->name("admin.add_results");
Route::get("admin/get-class-subject-id", [
    ResultManagementController::class,
    "getClassSubjectId",
])->name("admin.get_class_subject_id");
Route::get("admin/export-subject-analysis-pdf", [
    ResultManagementController::class,
    "exportSubjectAnalysisPdf",
])->name("admin.export_subject_analysis_pdf");
Route::post("admin/export-subject-performance-statistics-pdf", [
    ResultManagementController::class,
    "exportSubjectPerformanceStatisticsPdf",
])->name("admin.export_subject_performance_statistics_pdf");
Route::get("admin/get-class-subjects", [
    ResultManagementController::class,
    "getClassSubjectsForAnalysis",
])->name("admin.get_class_subjects_for_analysis");
Route::post("admin/send-subject-analysis-comment", [
    ResultManagementController::class,
    "sendSubjectAnalysisComment",
])->name("admin.send_subject_analysis_comment");
Route::post("admin/send-result-sms", [
    ResultManagementController::class,
    "sendResultSms",
])->name("admin.send_result_sms");
Route::get("admin/download-results-pdf", [
    ResultManagementController::class,
    "downloadPdf",
])->name("admin.download_results_pdf");
Route::get("admin/download-results-excel", [
    ResultManagementController::class,
    "downloadExcel",
])->name("admin.download_results_excel");
Route::post("save_report_definition", [
    ResultManagementController::class,
    "saveReportDefinition",
])->name("save_report_definition");
Route::get("get_report_definitions", [
    ResultManagementController::class,
    "getReportDefinitions",
])->name("get_report_definitions");
Route::delete("delete_report_definition/{id}", [
    ResultManagementController::class,
    "deleteReportDefinition",
])->name("delete_report_definition");
Route::get("get_exams_for_term_list", [
    ResultManagementController::class,
    "getExamsForTerm",
])->name("get_exams_for_term_list");
Route::get("check_report_definition_exists", [
    ResultManagementController::class,
    "checkReportDefinitionExists",
])->name("check_report_definition_exists");

// CA Definitions
Route::post("save_ca_definition", [
    ResultManagementController::class,
    "saveCaDefinition",
])->name("save_ca_definition");
Route::get("get_ca_definitions", [
    ResultManagementController::class,
    "getCaDefinitions",
])->name("get_ca_definitions");
Route::delete("delete_ca_definition/{id}", [
    ResultManagementController::class,
    "deleteCaDefinition",
])->name("delete_ca_definition");
Route::get("get_school_exams_for_term_list", [
    ResultManagementController::class,
    "getSchoolExamsForTermList",
])->name("get_school_exams_for_term_list");
Route::get("get_tests_for_term_list", [
    ResultManagementController::class,
    "getTestsForTermList",
])->name("get_tests_for_term_list");
Route::get("check_exam_ca_exists", [
    ResultManagementController::class,
    "checkExamCaExists",
])->name("check_exam_ca_exists");
Route::post("toggle_exam_publication", [
    ResultManagementController::class,
    "toggleExamPublication",
])->name("toggle_exam_publication");
Route::post("toggle_report_publication", [
    ResultManagementController::class,
    "toggleReportPublication",
])->name("toggle_report_publication");
Route::get("get_published_reports", [
    ResultManagementController::class,
    "getPublishedReports",
])->name("get_published_reports");

// Teacher Result Entry Permission & Assignments
Route::post("toggle_teacher_result_permission", [
    ResultManagementController::class,
    "toggleTeacherResultPermission",
])->name("toggle_teacher_result_permission");
Route::get("admin/get-teachers-for-assignment", [
    ResultManagementController::class,
    "getTeachersForAssignment",
])->name("admin.get_teachers_for_assignment");
Route::get("admin/get-subjects-for-assignment", [
    ResultManagementController::class,
    "getSubjectsForAssignment",
])->name("admin.get_subjects_for_assignment");
Route::post("admin/assign-teacher-to-result", [
    ResultManagementController::class,
    "assignTeacherToResult",
])->name("admin.assign_teacher_to_result");
Route::get("admin/get-teacher-assignments", [
    ResultManagementController::class,
    "getTeacherAssignments",
])->name("admin.get_teacher_assignments");
Route::get("admin/get-assignment-details/{id}", [
    ResultManagementController::class,
    "getAssignmentDetails",
])->name("admin.get_assignment_details");
Route::post("admin/toggle-assignment-status", [
    ResultManagementController::class,
    "toggleAssignmentStatus",
])->name("admin.toggle_assignment_status");
Route::post("admin/delete-assignment", [
    ResultManagementController::class,
    "deleteAssignment",
])->name("admin.delete_assignment");

// Exam Attendance Routes
Route::prefix("admin/exam-attendance")->group(function () {
    Route::get("/classes/{examID}", [
        ExamAttendanceController::class,
        "getExamClasses",
    ])->name("admin.exam_attendance.classes");
    Route::get("/subclasses/{classID}", [
        ExamAttendanceController::class,
        "getSubclasses",
    ])->name("admin.exam_attendance.subclasses");
    Route::post("/subjects", [
        ExamAttendanceController::class,
        "getSubjectsForAttendance",
    ])->name("admin.exam_attendance.subjects");
    Route::post("/assign", [
        ExamAttendanceController::class,
        "assignTeacher",
    ])->name("admin.exam_attendance.assign");
    Route::get("/assigned/{examID}", [
        ExamAttendanceController::class,
        "getAssignedTeachers",
    ])->name("admin.exam_attendance.assigned");
    Route::get("/assignment-details/{id}", [
        ExamAttendanceController::class,
        "getAssignmentDetails",
    ])->name("admin.exam_attendance.assignment_details");
    Route::post("/save", [
        ExamAttendanceController::class,
        "saveAttendance",
    ])->name("admin.exam_attendance.save");
    Route::get("/download-template", [
        ExamAttendanceController::class,
        "downloadTemplate",
    ])->name("admin.exam_attendance.download_template");
    Route::get("/summary/{examID}", [
        ExamAttendanceController::class,
        "getAttendanceSummary",
    ])->name("admin.exam_attendance.summary");
    Route::post("/admin-students", [
        ExamAttendanceController::class,
        "getStudentsForAdminCollection",
    ])->name("admin.exam_attendance.admin_students");
    Route::get("/stats/{examID}", [
        ExamAttendanceController::class,
        "getAttendanceStats",
    ])->name("admin.exam_attendance.stats");
    Route::delete("/delete-assignment/{id}", [
        ExamAttendanceController::class,
        "deleteAssignment",
    ])->name("admin.exam_attendance.delete_assignment");
    Route::post("/toggle-status/{id}", [
        ExamAttendanceController::class,
        "toggleStatus",
    ])->name("admin.exam_attendance.toggle_status");
});

// Teacher Exam Attendance Routes
Route::get("teacher/exam-attendance", [
    ExamAttendanceController::class,
    "teacherIndex",
])->name("teacher.exam_attendance");
Route::get("teacher/exam-attendance/details/{id}", [
    ExamAttendanceController::class,
    "getAssignmentDetailsForTeacher",
])->name("teacher.exam_attendance.details");

// Character Assessment Management
Route::get("character_assessment", [
    CharacterAssessmentController::class,
    "index",
])->name("character_assessment");
Route::post("store_assessment_category", [
    CharacterAssessmentController::class,
    "store_category",
])->name("store_assessment_category");
Route::post("update_assessment_category/{id}", [
    CharacterAssessmentController::class,
    "update_category",
])->name("update_assessment_category");
Route::delete("delete_assessment_category/{id}", [
    CharacterAssessmentController::class,
    "delete_category",
])->name("delete_assessment_category");
Route::post("update_character_categories_limit", [
    CharacterAssessmentController::class,
    "update_categories_limit",
])->name("update_character_categories_limit");
Route::post("store_assessment_choice", [
    CharacterAssessmentController::class,
    "store_choice",
])->name("store_assessment_choice");
Route::post("update_assessment_choice/{id}", [
    CharacterAssessmentController::class,
    "update_choice",
])->name("update_assessment_choice");
Route::delete("delete_assessment_choice/{id}", [
    CharacterAssessmentController::class,
    "delete_choice",
])->name("delete_assessment_choice");
Route::get("get_assessment_timetables", [
    CharacterAssessmentController::class,
    "get_timetables",
])->name("get_assessment_timetables");
Route::post("store_assessment_timetable", [
    CharacterAssessmentController::class,
    "store_timetable",
])->name("store_assessment_timetable");
Route::delete("delete_assessment_timetable/{id}", [
    CharacterAssessmentController::class,
    "delete_timetable",
])->name("delete_assessment_timetable");

// Admin Character Assessment Dashboard/Report Routes
Route::get("get_timetables_by_term", [
    CharacterAssessmentController::class,
    "get_timetables_by_term",
])->name("admin.character.timetables_by_term");
Route::get("get_classes_by_timetable", [
    CharacterAssessmentController::class,
    "get_classes_by_timetable",
])->name("admin.character.classes_by_timetable");
Route::get("get_assessment_report_data", [
    CharacterAssessmentController::class,
    "get_report_data",
])->name("admin.character.report_data");
Route::get("download_student_assessment_pdf", [
    CharacterAssessmentController::class,
    "download_pdf",
])->name("admin.character.download_pdf");
Route::get("download_student_assessment_excel", [
    CharacterAssessmentController::class,
    "download_excel",
])->name("admin.character.download_excel");

// Teacher Character Assessment Routes
Route::get("teacher/character-assessment", [
    CharacterAssessmentController::class,
    "teacher_index",
])->name("teacher.character_assessment");
Route::get("teacher/get-assessment-students", [
    CharacterAssessmentController::class,
    "get_assessment_students",
])->name("teacher.get_assessment_students");
Route::post("teacher/store-student-assessment", [
    CharacterAssessmentController::class,
    "store_student_assessment",
])->name("teacher.store_student_assessment");
Route::get("teacher/get-student-assessment-history", [
    CharacterAssessmentController::class,
    "get_student_assessment_history",
])->name("teacher.get_student_assessment_history");

// Exam Papers Routes
Route::post("store_exam_paper", [
    ManageExaminationController::class,
    "storeExamPaper",
])->name("store_exam_paper");
Route::post("update_exam_paper/{examPaperID}", [
    ManageExaminationController::class,
    "updateExamPaper",
])->name("update_exam_paper");
Route::get("get_exam_paper_questions/{examPaperID}", [
    ManageExaminationController::class,
    "getExamPaperQuestions",
])->name("get_exam_paper_questions");
Route::get("get_admin_exam_paper_review/{examPaperID}", [
    ManageExaminationController::class,
    "getAdminExamPaperReview",
])->name("admin.get_exam_paper_review");
Route::post("update_exam_paper_questions/{examPaperID}", [
    ManageExaminationController::class,
    "updateExamPaperQuestions",
])->name("update_exam_paper_questions");
Route::post("resubmit_exam_paper/{examPaperID}", [
    ManageExaminationController::class,
    "resubmitExamPaper",
])->name("resubmit_exam_paper");
Route::post("mark_exam_paper_notifications_read", [
    ManageExaminationController::class,
    "markExamPaperNotificationsRead",
])->name("mark_exam_paper_notifications_read");
Route::get("admin/exam-paper-notifications-count", [
    ManageExaminationController::class,
    "getExamPaperNotificationCount",
])->name("admin.exam_paper_notifications_count");
Route::get("admin/get-recent-exam-paper-notifications", [
    ManageExaminationController::class,
    "getRecentExamPaperNotifications",
])->name("admin.get_recent_exam_paper_notifications");
Route::get("admin/exam-paper-notifications-by-exam", [
    ManageExaminationController::class,
    "getExamPaperNotificationCountsByExam",
])->name("admin.exam_paper_notifications_by_exam");

Route::post("admin/mark-exam-paper-notifications-read/{examID}", [
    ManageExaminationController::class,
    "markExamPaperNotificationsReadForExam",
])->name("admin.mark_exam_paper_notifications_read_exam");
Route::get("admin/exam-paper-approval", [
    ManageExaminationController::class,
    "examPaperApproval",
])->name("admin.exam_paper_approval");
Route::get("get_examinations_for_filter", [
    ManageExaminationController::class,
    "getExaminationsForFilter",
])->name("get_examinations_for_filter");
Route::get("view_paper_approval_chain/{examID}", [
    ManageExaminationController::class,
    "viewApprovalChain",
])->name("view_paper_approval_chain");
Route::get("get_exam_available_weeks/{examID}", [
    ManageExaminationController::class,
    "getAvailableWeeksForExam",
])->name("get_exam_available_weeks");
Route::get("get_exam_papers/{examID}", [
    ManageExaminationController::class,
    "getExamPapers",
])->name("get_exam_papers");
Route::get("teacher/get-exam-paper-summary/{examID}", [
    ManageExaminationController::class,
    "getTeacherExamPaperSummary",
])->name("teacher.get_exam_paper_summary");
Route::post("approve_reject_exam_paper/{examPaperID}", [
    ManageExaminationController::class,
    "approveRejectExamPaper",
])->name("approve_reject_exam_paper");
Route::get("get_my_exam_papers", [
    ManageExaminationController::class,
    "getMyExamPapers",
])->name("get_my_exam_papers");
Route::get("download_exam_paper/{examPaperID}", [
    ManageExaminationController::class,
    "downloadExamPaper",
])->name("download_exam_paper");
Route::delete("delete_exam_paper/{examPaperID}", [
    ManageExaminationController::class,
    "deleteExamPaper",
])->name("delete_exam_paper");
Route::get("supervise_exams", [
    ManageExaminationController::class,
    "supervise_exams",
])->name("supervise_exams");
Route::get("admin/printing-unit", [
    ManageExaminationController::class,
    "printingUnit",
])->name("admin.printing_unit");
Route::get("admin/printing-unit/filter", [
    ManageExaminationController::class,
    "filterPrintingUnit",
])->name("admin.printing_unit.filter");
Route::get("get_test_by_type_year", [
    ManageExaminationController::class,
    "getTestByTypeYear",
])->name("get_test_by_type_year");
Route::get("get_available_periods", [
    ManageExaminationController::class,
    "getAvailablePeriods",
])->name("get_available_periods");
Route::get("get_scheduled_subjects", [
    ManageExaminationController::class,
    "getScheduledSubjects",
])->name("get_scheduled_subjects");
// Attendance Routes
Route::post("save_attendance", [
    ManageClassessController::class,
    "saveAttendance",
])->name("save_attendance");
Route::get("get_attendance", [
    ManageClassessController::class,
    "getAttendance",
])->name("get_attendance");
Route::get("get_attendance/{attendanceID}", [
    ManageClassessController::class,
    "getAttendanceById",
])->name("get_attendance_by_id");
Route::post("update_attendance", [
    ManageClassessController::class,
    "updateAttendance",
])->name("update_attendance");
Route::delete("delete_attendance/{attendanceID}", [
    ManageClassessController::class,
    "deleteAttendance",
])->name("delete_attendance");
Route::get("get_attendance_overview", [
    ManageClassessController::class,
    "getAttendanceOverview",
])->name("get_attendance_overview");

// Time table routes
Route::get("timeTable", [TimeTableController::class, "timeTable"])->name(
    "timeTable",
);
Route::get("teacher_time_table", [
    TimeTableController::class,
    "teacher_time_table",
])->name("teacher_time_table");
Route::get("supervise_exam_time_table", [
    TimeTableController::class,
    "supervise_exam_time_table",
])->name("supervise_exam_time_table");

// Exam Timetable Routes
Route::post("store_exam_timetable", [
    TimeTableController::class,
    "storeExamTimetable",
])->name("store_exam_timetable");
Route::get("get_exam_timetables", [
    TimeTableController::class,
    "getExamTimetables",
])->name("get_exam_timetables");
Route::get("get_exam_details_timetable", [
    TimeTableController::class,
    "getExamDetails",
])->name("get_exam_details_timetable");
Route::get("get_exam_supervisors", [
    TimeTableController::class,
    "getExamSupervisors",
])->name("get_exam_supervisors");
Route::post("update_supervise_teacher", [
    TimeTableController::class,
    "updateSuperviseTeacher",
])->name("update_supervise_teacher");
Route::get("get_subclass_subjects_timetable", [
    TimeTableController::class,
    "getSubclassSubjects",
])->name("get_subclass_subjects_timetable");
Route::get("get_school_subjects_timetable", [
    TimeTableController::class,
    "getSchoolSubjects",
])->name("get_school_subjects_timetable");
Route::delete("delete_exam_timetable/{examTimetableID}", [
    TimeTableController::class,
    "deleteExamTimetable",
])->name("delete_exam_timetable");
Route::delete("delete_all_exam_timetable/{examID}", [
    TimeTableController::class,
    "deleteAllExamTimetable",
])->name("delete_all_exam_timetable");
Route::get("get_subject_hall_supervisors", [
    TimeTableController::class,
    "getSubjectHallSupervisors",
])->name("get_subject_hall_supervisors");
Route::put("update_exam_timetable_time/{examTimetableID}", [
    TimeTableController::class,
    "updateExamTimetableTime",
])->name("update_exam_timetable_time");
Route::get("get_supervise_teachers", [
    TimeTableController::class,
    "getSuperviseTeachers",
])->name("get_supervise_teachers");
Route::put("update_hall_supervisor/{supervisorID}", [
    TimeTableController::class,
    "updateHallSupervisor",
])->name("update_hall_supervisor");
Route::post("shuffle_exam_timetable/{examID}", [
    TimeTableController::class,
    "shuffleExamTimetable",
])->name("shuffle_exam_timetable");
Route::post("preview_automatic_timetable", [
    TimeTableController::class,
    "previewAutomaticTimetable",
])->name("preview_automatic_timetable");
Route::post("swap_exam_subjects", [
    TimeTableController::class,
    "swapExamSubjects",
])->name("swap_exam_subjects");

// Session Timetable Routes
Route::get("admin/get-session-timetable-definition", [
    TimeTableController::class,
    "getSessionTimetableDefinition",
])->name("get_session_timetable_definition");
Route::post("admin/save-session-timetable-definition", [
    TimeTableController::class,
    "saveSessionTimetableDefinition",
])->name("save_session_timetable_definition");
Route::get("admin/get-session-types", [
    TimeTableController::class,
    "getSessionTypes",
])->name("get_session_types");
Route::get("admin/get-selected-subjects-for-subclass", [
    TimeTableController::class,
    "getSelectedSubjectsForSubclass",
])->name("get_selected_subjects_for_subclass");
Route::get("admin/check-subclass-has-timetable", [
    TimeTableController::class,
    "checkSubclassHasTimetable",
])->name("check_subclass_has_timetable");
Route::get("admin/get-all-subclasses-with-timetables", [
    TimeTableController::class,
    "getAllSubclassesWithTimetables",
])->name("get_all_subclasses_with_timetables");
Route::get("admin/class-session-teacher-conflicts", [
    TimeTableController::class,
    "getClassSessionTeacherConflicts",
])->name("admin.class_session_teacher_conflicts");
Route::get("admin/get-session-timetable", [
    TimeTableController::class,
    "getSessionTimetable",
])->name("get_session_timetable");
Route::get("admin/export-session-timetable-pdf", [
    TimeTableController::class,
    "exportSessionTimetablePdf",
])->name("admin.export_session_timetable_pdf");
Route::post("admin/save-class-session-timetables", [
    TimeTableController::class,
    "saveClassSessionTimetables",
])->name("save_class_session_timetables");
Route::post("admin/delete-class-session-timetable", [
    TimeTableController::class,
    "deleteClassSessionTimetable",
])->name("delete_class_session_timetable");
Route::post("admin/shuffle-session-timetable", [
    TimeTableController::class,
    "shuffleSessionTimetable",
])->name("shuffle_session_timetable");
Route::post("admin/swap-session-timetable", [
    TimeTableController::class,
    "swapSessionTimetable",
])->name("swap_session_timetable");

// New route for fetching subjects for timetable builder (Weekly Tests etc.)
Route::get("admin/api/get-subjects-for-timetable", [
    TimeTableController::class,
    "getSubjectsForTimetable",
])->name("admin.get_subjects_for_timetable");
Route::get("admin/api/get-test-schedules", [
    TimeTableController::class,
    "getTestSchedules",
])->name("admin.get_test_schedules");
Route::post("admin/api/delete-all-test-schedules", [
    TimeTableController::class,
    "deleteAllTestSchedules",
])->name("admin.delete_all_test_schedules");

// Calendar routes
Route::get("admin/calendar", [
    CalendarController::class,
    "adminCalendar",
])->name("admin.calendar");
Route::get("admin/calendar/export-pdf", [
    CalendarController::class,
    "exportCalendarPdf",
])->name("admin.export_calendar_pdf");
Route::get("teacher/calendar", [
    CalendarController::class,
    "teacherCalendar",
])->name("teacher.calendar");
Route::get("holidays/{holidayID}", [
    CalendarController::class,
    "getHoliday",
])->name("holidays.show");
Route::post("holidays", [CalendarController::class, "storeHoliday"])->name(
    "holidays.store",
);
Route::post("holidays/bulk", [
    CalendarController::class,
    "storeBulkHolidays",
])->name("holidays.bulk");
Route::put("holidays/{holidayID}", [
    CalendarController::class,
    "updateHoliday",
])->name("holidays.update");
Route::delete("holidays/{holidayID}", [
    CalendarController::class,
    "deleteHoliday",
])->name("holidays.delete");
Route::get("events/{eventID}", [CalendarController::class, "getEvent"])->name(
    "events.show",
);
Route::post("events", [CalendarController::class, "storeEvent"])->name(
    "events.store",
);
Route::put("events/{eventID}", [
    CalendarController::class,
    "updateEvent",
])->name("events.update");
Route::delete("events/{eventID}", [
    CalendarController::class,
    "deleteEvent",
])->name("events.delete");
Route::get("calendar-data", [
    CalendarController::class,
    "getCalendarData",
])->name("calendar.data");

// Home Package (teacher + shared view/download)
Route::get("home-packages/holidays", [
    HomePackageController::class,
    "holidaysForLevel",
])->name("home_packages.holidays");
Route::get("home-packages", [
    HomePackageController::class,
    "teacherIndex",
])->name("home_packages.index");
Route::post("home-packages", [
    HomePackageController::class,
    "store",
])->name("home_packages.store");
Route::post("home-packages/{homePackageID}", [
    HomePackageController::class,
    "update",
])->name("home_packages.update");
Route::delete("home-packages/{homePackageID}", [
    HomePackageController::class,
    "destroy",
])->name("home_packages.destroy");
Route::get("home-packages/{homePackageID}/view", [
    HomePackageController::class,
    "viewFile",
])->name("home_packages.view");
Route::get("home-packages/{homePackageID}/download", [
    HomePackageController::class,
    "downloadFile",
])->name("home_packages.download");

//manage Attendance routes
Route::get("manageAttendance", [
    ManageAttendanceController::class,
    "manageAttendance",
])->name("manageAttendance");
Route::get("attendance-sms/schedule-info", [
    AttendanceSmsController::class,
    "scheduleInfo",
])->name("attendance_sms.schedule_info");
Route::get("attendance-sms/recipients", [
    AttendanceSmsController::class,
    "recipients",
])->name("attendance_sms.recipients");
Route::post("attendance-sms/send", [
    AttendanceSmsController::class,
    "send",
])->name("attendance_sms.send");
Route::get("fingerprint-attendance-sms/schedule-info", [
    FingerprintAttendanceSmsController::class,
    "scheduleInfo",
])->name("fingerprint_attendance_sms.schedule_info");
Route::get("fingerprint-attendance-sms/recipients", [
    FingerprintAttendanceSmsController::class,
    "recipients",
])->name("fingerprint_attendance_sms.recipients");
Route::post("fingerprint-attendance-sms/send", [
    FingerprintAttendanceSmsController::class,
    "send",
])->name("fingerprint_attendance_sms.send");
Route::post("fingerprint-attendance-sms/send-bulk", [
    FingerprintAttendanceSmsController::class,
    "sendBulk",
])->name("fingerprint_attendance_sms.send_bulk");
Route::get("manageTeacherStaffAttendance", [
    ManageAttendanceController::class,
    "manageTeacherStaffAttendance",
])->name("manageTeacherStaffAttendance");
Route::get("manageStudentFingerprintAttendance", [
    ManageAttendanceController::class,
    "manageStudentFingerprintAttendance",
])->name("manageStudentFingerprintAttendance");
Route::post("search_attendance", [
    ManageAttendanceController::class,
    "searchAttendance",
])->name("search_attendance");
Route::post("search_fingerprint_attendance", [
    ManageAttendanceController::class,
    "searchFingerprintAttendance",
])->name("search_fingerprint_attendance");
Route::get("student_attendance_details/{studentID}", [
    ManageAttendanceController::class,
    "getStudentAttendanceDetails",
])->name("student_attendance_details");
Route::get("export_attendance_report_pdf", [
    ManageAttendanceController::class,
    "exportAttendanceReportPdf",
])->name("export_attendance_report_pdf");
Route::get("export_student_attendance_pdf/{studentID}", [
    ManageAttendanceController::class,
    "exportStudentAttendancePdf",
])->name("export_student_attendance_pdf");
Route::get("student_fingerprint_attendance_details/{studentID}", [
    ManageAttendanceController::class,
    "getStudentFingerprintAttendanceDetails",
])->name("student_fingerprint_attendance_details");

//library routes
Route::get("manage_library", [
    LibraryController::class,
    "manage_library",
])->name("manage_library");
Route::get("get_books", [LibraryController::class, "get_books"])->name(
    "get_books",
);
Route::get("check_isbn", [LibraryController::class, "check_isbn"])->name(
    "check_isbn",
);
Route::get("get_book_by_isbn", [
    LibraryController::class,
    "get_book_by_isbn",
])->name("get_book_by_isbn");
Route::get("get_subjects_by_class", [
    LibraryController::class,
    "get_subjects_by_class",
])->name("get_subjects_by_class");
Route::post("store_book", [LibraryController::class, "store_book"])->name(
    "store_book",
);
Route::post("update_book/{bookID}", [
    LibraryController::class,
    "update_book",
])->name("update_book");
Route::post("bulk_update_book_details", [
    LibraryController::class,
    "bulk_update_book_details",
])->name("bulk_update_book_details");
Route::delete("delete_book/{bookID}", [
    LibraryController::class,
    "delete_book",
])->name("delete_book");
Route::post("borrow_book", [LibraryController::class, "borrow_book"])->name(
    "borrow_book",
);
Route::post("return_book/{borrowID}", [
    LibraryController::class,
    "return_book",
])->name("return_book");
Route::get("get_book_borrows", [
    LibraryController::class,
    "get_book_borrows",
])->name("get_book_borrows");
Route::get("get_book_statistics", [
    LibraryController::class,
    "get_book_statistics",
])->name("get_book_statistics");
Route::get("get_students", [LibraryController::class, "get_students"])->name(
    "get_students",
);
Route::get("get_book_losses", [
    LibraryController::class,
    "get_book_losses",
])->name("get_book_losses");
Route::post("store_book_loss", [
    LibraryController::class,
    "store_book_loss",
])->name("store_book_loss");
Route::get("get_book_damages", [
    LibraryController::class,
    "get_book_damages",
])->name("get_book_damages");
Route::post("store_book_damage", [
    LibraryController::class,
    "store_book_damage",
])->name("store_book_damage");
Route::post("send_parent_message", [
    LibraryController::class,
    "send_parent_message",
])->name("send_parent_message");
Route::post("update_book_loss_payment/{lossID}", [
    LibraryController::class,
    "update_book_loss_payment",
])->name("update_book_loss_payment");
Route::post("update_book_damage_payment/{damageID}", [
    LibraryController::class,
    "update_book_damage_payment",
])->name("update_book_damage_payment");
Route::post("resolve_book_incident", [
    LibraryController::class,
    "resolve_book_incident",
])->name("resolve_book_incident");
Route::get("get_library_students", [
    LibraryController::class,
    "get_library_students",
])->name("get_library_students");
Route::get("search_teachers", [
    LibraryController::class,
    "search_teachers",
])->name("search_teachers");
Route::get("search_staff", [LibraryController::class, "search_staff"])->name(
    "search_staff",
);
Route::get("export_library_pdf", [
    LibraryController::class,
    "export_library_pdf",
])->name("export_library_pdf");

// Character Assessment Routes
Route::get("character_assessment", [
    CharacterAssessmentController::class,
    "index",
])->name("character_assessment");
Route::post("store_assessment_category", [
    CharacterAssessmentController::class,
    "store_category",
])->name("store_assessment_category");
Route::post("update_assessment_category/{id}", [
    CharacterAssessmentController::class,
    "update_category",
])->name("update_assessment_category");
Route::delete("delete_assessment_category/{id}", [
    CharacterAssessmentController::class,
    "delete_category",
])->name("delete_assessment_category");
Route::post("store_assessment_choice", [
    CharacterAssessmentController::class,
    "store_choice",
])->name("store_assessment_choice");
Route::post("update_assessment_choice/{id}", [
    CharacterAssessmentController::class,
    "update_choice",
])->name("update_assessment_choice");
Route::delete("delete_assessment_choice/{id}", [
    CharacterAssessmentController::class,
    "delete_choice",
])->name("delete_assessment_choice");

//fees routes
Route::get("manage_fees", [FeesController::class, "manage_fees"])->name(
    "manage_fees",
);
Route::post("store_fee", [FeesController::class, "store_fee"])->name(
    "store_fee",
);
Route::post("update_fee/{feeID}", [FeesController::class, "update_fee"])->name(
    "update_fee",
);
Route::delete("delete_fee/{feeID}", [
    FeesController::class,
    "delete_fee",
])->name("delete_fee");
Route::post("toggle_fee_status/{feeID}", [
    FeesController::class,
    "toggle_fee_status",
])->name("toggle_fee_status");
Route::get("get_fee/{feeID}", [FeesController::class, "get_fee"])->name(
    "get_fee",
);

// School bank accounts (Multi Account Fees) — Admin only for register/OTP
Route::get("fees/active_banks", [FeesController::class, "list_active_banks"])->name("fees.active_banks");
Route::get("fees/school_bank_accounts", [FeesController::class, "list_school_bank_accounts"])->name("fees.school_bank_accounts");
Route::get("fees/approved_bank_accounts", [FeesController::class, "list_approved_school_bank_accounts"])->name("fees.approved_bank_accounts");
Route::post("fees/bank_accounts/send_otp", [FeesController::class, "send_bank_account_otp"])->name("fees.bank_accounts.send_otp");
Route::post("fees/bank_accounts/verify_otp", [FeesController::class, "verify_bank_account_otp"])->name("fees.bank_accounts.verify_otp");
Route::delete("fees/bank_accounts/{accountID}", [FeesController::class, "delete_school_bank_account"])->name("fees.bank_accounts.delete");

// Fee Conditions & Agreements Routes
Route::get("get_class_conditions/{classID}", [
    FeesController::class,
    "get_class_conditions",
])->name("get_class_conditions");
Route::get("get_class_students/{classID}", [
    FeesController::class,
    "get_class_students",
])->name("get_class_students");
Route::get("get_class_fees/{classID}", [
    FeesController::class,
    "get_class_fees",
])->name("get_class_fees");
Route::get("fees/installments/{feeID}", [
    FeesController::class,
    "get_fee_installments",
])->name("fees.installments.show");
Route::post("fees/installments", [
    FeesController::class,
    "store_fee_installments",
])->name("fees.installments.store");
Route::get("fees/deadlines/class/{classID}", [
    FeesController::class,
    "get_class_fee_deadlines",
])->name("fees.deadlines.class");
Route::post("fees/deadlines", [
    FeesController::class,
    "store_fee_deadlines",
])->name("fees.deadlines.store");
Route::delete("fees/deadlines/{deadlineID}", [
    FeesController::class,
    "delete_fee_deadline",
])->name("fees.deadlines.delete");
Route::get("get_existing_agreements/{classID}", [
    FeesController::class,
    "get_existing_agreements",
])->name("get_existing_agreements");
Route::post("assign_students_to_conditions", [
    FeesController::class,
    "assign_students_to_conditions",
])->name("assign_students_to_conditions");
Route::post("store_fee_agreements", [
    FeesController::class,
    "store_fee_agreements",
])->name("store_fee_agreements");
Route::post("assign_sponsor_students", [
    FeesController::class,
    "assign_sponsor_students",
])->name("assign_sponsor_students");

//payments routes
Route::get("view_payments", [FeesController::class, "view_payments"])->name(
    "view_payments",
);
Route::get("get_payments_ajax", [
    FeesController::class,
    "get_payments_ajax",
])->name("get_payments_ajax");
Route::get("student_enrollment_payments/{studentID}", [
    FeesController::class,
    "get_student_enrollment_payments",
])->name("student_enrollment_payments");
Route::post("generate_control_numbers", [
    FeesController::class,
    "generate_control_numbers",
])->name("generate_control_numbers");
Route::post("send_control_numbers_sms", [
    FeesController::class,
    "send_control_numbers_sms",
])->name("send_control_numbers_sms");
Route::post("resend_control_number/{paymentID}", [
    FeesController::class,
    "resend_control_number",
])->name("resend_control_number");
Route::post("update_payment_status/{paymentID}", [
    FeesController::class,
    "update_payment_status",
])->name("update_payment_status");
Route::get("export_payment_invoice_pdf/{studentID}", [
    FeesController::class,
    "exportPaymentInvoicePDF",
])->name("export_payment_invoice_pdf");
Route::get("admin/export_filtered_payments_pdf", [
    FeesController::class,
    "exportFilteredPaymentsPDF",
])->name("admin.export_filtered_payments_pdf");
Route::get("admin/export-fees-structure-pdf", [
    FeesController::class,
    "exportFeesStructurePdf",
])->name("admin.export_fees_structure_pdf");

// Payments Report routes (UI-only; data aggregated client-side from get_payments_ajax)
Route::get("payments/report", function () {
    return view("Admin.payments_report");
})->name("payments.report");
Route::post("record_payment", [FeesController::class, "record_payment"])->name(
    "record_payment",
);
Route::get("quick_record_search_students", [
    FeesController::class,
    "quick_record_search_students",
])->name("quick_record_search_students");
Route::get("quick_record_student_fees/{studentID}", [
    FeesController::class,
    "quick_record_student_fees",
])->name("quick_record_student_fees");
Route::get("get_payment_records", [
    FeesController::class,
    "get_payment_records",
])->name("get_payment_records");
Route::post("update_payment_record", [
    FeesController::class,
    "update_payment_record",
])->name("update_payment_record");
Route::post("delete_payment_record", [
    FeesController::class,
    "delete_payment_record",
])->name("delete_payment_record");
Route::post("verify_payment", [FeesController::class, "verify_payment"])->name(
    "verify_payment",
);
Route::post("unverify_payment", [
    FeesController::class,
    "unverify_payment",
])->name("unverify_payment");
Route::post("send_debt_reminders_sms", [
    FeesController::class,
    "send_debt_reminders_sms",
])->name("send_debt_reminders_sms");
Route::post("send_single_debt_reminder", [
    FeesController::class,
    "send_single_debt_reminder",
])->name("send_single_debt_reminder");
Route::get("get_fee_sms_recipients", [
    FeesController::class,
    "get_fee_sms_recipients",
])->name("get_fee_sms_recipients");
Route::post("send_single_fee_sms", [
    FeesController::class,
    "send_single_fee_sms",
])->name("send_single_fee_sms");

// Sponsor Management Routes
Route::get("manage_sponsors", [SponsorController::class, "index"])->name(
    "manage_sponsors",
);
Route::post("sponsors/store", [SponsorController::class, "store"])->name(
    "sponsors.store",
);
Route::post("sponsors/update/{id}", [SponsorController::class, "update"])->name(
    "sponsors.update",
);
Route::post("sponsors/delete/{id}", [
    SponsorController::class,
    "destroy",
])->name("sponsors.destroy");
Route::get("get_sponsors", [SponsorController::class, "getSponsors"])->name(
    "get_sponsors",
);

//sms notification
// SMS Notification Routes
Route::get("sms_notification", [
    SMS_InformationController::class,
    "sms_notification",
])->name("sms_notification");
Route::get("get_all_parents_sms", [
    SMS_InformationController::class,
    "get_all_parents",
])->name("get_all_parents_sms");
Route::get("get_parents_by_class_sms", [
    SMS_InformationController::class,
    "get_parents_by_class",
])->name("get_parents_by_class_sms");
Route::get("get_all_teachers_sms", [
    SMS_InformationController::class,
    "get_all_teachers",
])->name("get_all_teachers_sms");
Route::get("get_all_staff_sms", [
    SMS_InformationController::class,
    "get_all_staff",
])->name("get_all_staff_sms");
Route::get("get_parent_by_student_sms", [
    SMS_InformationController::class,
    "get_parent_by_student",
])->name("get_parent_by_student_sms");
Route::get("search_students_sms", [
    SMS_InformationController::class,
    "search_students",
])->name("search_students_sms");
Route::get("search_teachers_sms", [
    SMS_InformationController::class,
    "search_teachers",
])->name("search_teachers_sms");
Route::get("search_staff_sms", [
    SMS_InformationController::class,
    "search_staff",
])->name("search_staff_sms");
Route::get("get_sms_balance", [
    SMS_InformationController::class,
    "get_sms_balance",
])->name("get_sms_balance");
Route::post("send_sms", [SMS_InformationController::class, "send_sms"])->name(
    "send_sms",
);

//Accomodation routes
Route::get("manage_accomodation", [
    AccomodationController::class,
    "manage_accomodation",
])->name("manage_accomodation");

// ZKTeco Push SDK Routes (No authentication required - called by device)
// These routes must be accessible from the device network
Route::get("iclock/getrequest", [
    ZKTecoPushController::class,
    "getRequest",
])->name("zkteco.push.getrequest");
Route::post("iclock/cdata", [ZKTecoPushController::class, "cdata"])->name(
    "zkteco.push.cdata",
);

// Fingerprint Device Settings Routes
Route::get("fingerprint_device_settings", [
    ZKTecoController::class,
    "index",
])->name("fingerprint_device_settings");
Route::get("zkteco/devices", [
    ZKTecoController::class,
    "listDevices",
])->name("zkteco.devices.index");
Route::post("zkteco/devices", [
    ZKTecoController::class,
    "storeDevice",
])->name("zkteco.devices.store");
Route::get("zkteco/devices/school-sync-guide-pdf", [
    ZKTecoController::class,
    "downloadUf200SchoolSyncGuide",
])->name("zkteco.devices.school_sync_guide_pdf");
Route::get("zkteco/devices/{id}", [
    ZKTecoController::class,
    "showDevice",
])->name("zkteco.devices.show");
Route::put("zkteco/devices/{id}", [
    ZKTecoController::class,
    "updateDevice",
])->name("zkteco.devices.update");
Route::delete("zkteco/devices/{id}", [
    ZKTecoController::class,
    "deleteDevice",
])->name("zkteco.devices.destroy");
Route::post("zkteco/test-connection", [
    ZKTecoController::class,
    "testConnection",
])->name("zkteco.test_connection");
Route::post("zkteco/device-info", [
    ZKTecoController::class,
    "getDeviceInfo",
])->name("zkteco.device_info");
Route::post("zkteco/attendance", [
    ZKTecoController::class,
    "getAttendance",
])->name("zkteco.attendance");

// Push SDK Setup Wizard Routes
Route::get("zkteco/setup/server-info", [
    ZKTecoController::class,
    "getServerInfo",
])->name("zkteco.setup.server-info");
Route::post("zkteco/setup/test-connection", [
    ZKTecoController::class,
    "testDeviceConnection",
])->name("zkteco.setup.test-connection");
Route::post("zkteco/setup/import-users", [
    ZKTecoController::class,
    "importUsersFromDevice",
])->name("zkteco.setup.import-users");
Route::get("zkteco/setup/check-activity", [
    ZKTecoController::class,
    "checkRecentActivity",
])->name("zkteco.setup.check-activity");

// Attendance Routes (from device - raw format)
Route::post("zkteco/attendance/today", [
    ZKTecoController::class,
    "getTodayAttendance",
])->name("zkteco.attendance.today");
Route::post("zkteco/attendance/by-date", [
    ZKTecoController::class,
    "getAttendanceByDate",
])->name("zkteco.attendance.by-date");
Route::get("zkteco/attendance/for-class", [
    ZKTecoController::class,
    "getFingerprintAttendanceForClass",
])->name("zkteco.attendance.for-class");
Route::get("zkteco/attendance/from-db", [
    ZKTecoController::class,
    "getFingerprintAttendanceFromDB",
])->name("zkteco.attendance.from-db");
Route::post("zkteco/attendance/sync-all", [
    ZKTecoController::class,
    "syncAllAttendanceFromDevice",
])->name("zkteco.attendance.sync-all");

// Live attendance (today) sync + fetch for class (used by teacher view)
Route::get("zkteco/attendance/live-today", [
    ZKTecoController::class,
    "syncLiveAttendanceToday",
])->name("zkteco.attendance.live-today");
Route::get("zkteco/attendance/teacher-staff-live-today", [
    ZKTecoController::class,
    "syncLiveTeacherStaffAttendanceToday",
])->name("zkteco.attendance.teacher-staff-live-today");

// Live teacher/staff attendance for Today when device sync happens on local LAN agent.
// This endpoint only reads from database.
Route::get("zkteco/attendance/student-live-today-from-db", [
    ZKTecoController::class,
    "getStudentLiveTodayFromDb",
])->name("zkteco.attendance.student-live-today-from-db");
Route::get("zkteco/attendance/teacher-staff-live-today-from-db", [
    ZKTecoController::class,
    "getTeacherStaffLiveTodayFromDb",
])->name("zkteco.attendance.teacher-staff-live-today-from-db");
Route::post("zkteco/attendance/teacher-staff-sync", [
    ZKTecoController::class,
    "syncTeacherStaffAttendanceFromDevice",
])->name("zkteco.attendance.teacher-staff-sync");
Route::get("attendance/student-fingerprint-saved", [
    ManageAttendanceController::class,
    "getStudentFingerprintSavedRecords",
])->name("attendance.student_fingerprint_saved");
Route::get("attendance/student-fingerprint-report", [
    ManageAttendanceController::class,
    "studentFingerprintAttendanceReport",
])->name("attendance.student_fingerprint_report");
Route::get("attendance/student-fingerprint-report-pdf", [
    ManageAttendanceController::class,
    "exportStudentFingerprintAttendanceReportPdf",
])->name("attendance.student_fingerprint_report_pdf");
Route::get("attendance/student-work-time", [
    ManageAttendanceController::class,
    "getStudentWorkTime",
])->name("attendance.student_work_time");
Route::post("attendance/student-work-time", [
    ManageAttendanceController::class,
    "saveStudentWorkTime",
])->name("attendance.student_work_time.save");
Route::get("attendance/teacher-staff-fingerprint", [
    AttendanceApiController::class,
    "getTeacherStaffFingerprintAttendance",
])->name("attendance.teacher_staff_fingerprint");
Route::get("attendance/teacher-staff-report", [
    ManageAttendanceController::class,
    "teacherStaffAttendanceReport",
])->name("attendance.teacher_staff_report");
Route::get("attendance/teacher-staff-report-detail", [
    ManageAttendanceController::class,
    "teacherStaffAttendanceReportDetail",
])->name("attendance.teacher_staff_report_detail");
Route::get("attendance/teacher-staff-report-pdf", [
    ManageAttendanceController::class,
    "exportTeacherStaffAttendanceReportPdf",
])->name("attendance.teacher_staff_report_pdf");
Route::get("attendance/teacher-staff-report-person-pdf", [
    ManageAttendanceController::class,
    "exportTeacherStaffAttendancePersonPdf",
])->name("attendance.teacher_staff_report_person_pdf");
Route::post("attendance/teacher-staff-report-sms", [
    ManageAttendanceController::class,
    "sendTeacherStaffAttendanceSms",
])->name("attendance.teacher_staff_report_sms");
Route::get("attendance/teacher-staff-work-time", [
    ManageAttendanceController::class,
    "getTeacherStaffWorkTime",
])->name("attendance.teacher_staff_work_time");
Route::post("attendance/teacher-staff-work-time", [
    ManageAttendanceController::class,
    "saveTeacherStaffWorkTime",
])->name("attendance.teacher_staff_work_time.save");

// User Management Routes
Route::post("zkteco/user/check-device-status", [
    ZKTecoController::class,
    "checkDeviceStatus",
])->name("zkteco.user.check-device-status");
Route::post("zkteco/user/register", [
    ZKTecoController::class,
    "registerUserToDevice",
])->name("zkteco.user.register");
Route::post("zkteco/user/list-device-users", [
    ZKTecoController::class,
    "listDeviceUsers",
])->name("zkteco.user.list-device-users");
Route::post("zkteco/user/delete", [
    ZKTecoController::class,
    "deleteUserFromDevice",
])->name("zkteco.user.delete");
Route::post("zkteco/attendance/today", [
    ZKTecoController::class,
    "getTodayAttendance",
])->name("zkteco.attendance.today");
Route::post("zkteco/attendance/by-date", [
    ZKTecoController::class,
    "getAttendanceByDate",
])->name("zkteco.attendance.by-date");

// Student Device Registration Routes (similar to sample project)
Route::post("students/{id}/register-device", [
    ZKTecoController::class,
    "registerStudentToDevice",
])->name("students.register-device");

// Temporary route to populate results (remove after use)
Route::get("populate-results", function () {
    $output = [];
    $output[] = "Starting to populate results...<br>";

    // Check how many records need updating
    $count = DB::table("results")
        ->whereNull("marks")
        ->where("status", "not_allowed")
        ->count();

    $output[] = "Records to update: $count<br>";

    if ($count > 0) {
        $output[] = "Updating marks...<br>";

        // Update marks in batches
        $updated = 0;
        DB::table("results")
            ->whereNull("marks")
            ->where("status", "not_allowed")
            ->orderBy("resultID")
            ->chunk(500, function ($results) use (&$updated, &$output) {
                foreach ($results as $result) {
                    $rand = mt_rand(1, 100);
                    if ($rand <= 30) {
                        $marks = mt_rand(0, 29);
                    } elseif ($rand <= 70) {
                        $marks = mt_rand(30, 64);
                    } else {
                        $marks = mt_rand(65, 100);
                    }

                    DB::table("results")
                        ->where("resultID", $result->resultID)
                        ->update(["marks" => $marks]);
                    $updated++;
                }
            });

        $output[] = "Updated $updated marks.<br>";
        $output[] = "Updating grades...<br>";

        // Update grades
        DB::statement("
            UPDATE results r
            INNER JOIN examinations e ON r.examID = e.examID
            INNER JOIN subclasses s ON r.subclassID = s.subclassID
            INNER JOIN classes c ON s.classID = c.classID
            INNER JOIN schools sch ON c.schoolID = sch.schoolID
            SET r.grade = CASE
                WHEN sch.school_type = 'Secondary' AND LOWER(REPLACE(REPLACE(c.class_name, ' ', '_'), '-', '_')) IN ('form_one', 'form_two', 'form_three', 'form_four', 'form_1', 'form_2', 'form_3', 'form_4') THEN
                    CASE
                        WHEN r.marks >= 75 THEN 'A'
                        WHEN r.marks >= 65 THEN 'B'
                        WHEN r.marks >= 45 THEN 'C'
                        WHEN r.marks >= 30 THEN 'D'
                        WHEN r.marks >= 20 THEN 'E'
                        ELSE 'F'
                    END
                WHEN sch.school_type = 'Primary' THEN
                    CASE
                        WHEN r.marks >= 75 THEN 'A'
                        WHEN r.marks >= 65 THEN 'B'
                        WHEN r.marks >= 45 THEN 'C'
                        WHEN r.marks >= 30 THEN 'D'
                        ELSE 'F'
                    END
                ELSE
                    CASE
                        WHEN r.marks >= 75 THEN 'A'
                        WHEN r.marks >= 65 THEN 'B'
                        WHEN r.marks >= 45 THEN 'C'
                        WHEN r.marks >= 30 THEN 'D'
                        ELSE 'F'
                    END
            END
            WHERE r.marks IS NOT NULL AND r.grade IS NULL
        ");

        $output[] = "Grades updated.<br>";
        $output[] = "Updating remarks...<br>";

        // Update remarks
        DB::table("results")
            ->whereNotNull("marks")
            ->whereNull("remark")
            ->update([
                "remark" => DB::raw(
                    "CASE WHEN marks >= 30 THEN 'Pass' ELSE 'Fail' END",
                ),
            ]);

        $output[] = "Remarks updated.<br>";
        $output[] = "Updating status...<br>";

        // Update status
        DB::table("results")
            ->whereNotNull("marks")
            ->where("status", "not_allowed")
            ->update(["status" => "allowed"]);

        $output[] = "Status updated.<br>";
    }

    // Show summary
    $summary = DB::table("results")
        ->selectRaw(
            '
            COUNT(*) as total,
            COUNT(marks) as with_marks,
            COUNT(grade) as with_grades,
            COUNT(CASE WHEN marks >= 30 THEN 1 END) as passed,
            COUNT(CASE WHEN marks < 30 THEN 1 END) as failed
        ',
        )
        ->first();

    $output[] = "<br><strong>Summary:</strong><br>";
    $output[] = "Total records: {$summary->total}<br>";
    $output[] = "With marks: {$summary->with_marks}<br>";
    $output[] = "With grades: {$summary->with_grades}<br>";
    $output[] = "Passed (>=30): {$summary->passed}<br>";
    $output[] = "Failed (<30): {$summary->failed}<br>";

    return implode("", $output);
})->name("populate.results");

// Strategic Goal & Performance Management (SGPM) Routes
Route::prefix("sgpm")
    ->name("sgpm.")
    ->group(function () {
        // Departments — static paths must be registered before {id} routes
        Route::post("departments/hods/send-sms", [
            \App\Http\Controllers\DepartmentController::class,
            "sendSmsToHODs",
        ])->name("departments.hods.sms");
        Route::delete("departments/members/{memberId}", [
            \App\Http\Controllers\DepartmentController::class,
            "removeMember",
        ])->name("departments.members.remove");

        Route::resource(
            "departments",
            \App\Http\Controllers\DepartmentController::class,
        )->except(["show"]);

        Route::get("departments/{id}/members", [
            \App\Http\Controllers\DepartmentController::class,
            "manageMembers",
        ])->name("departments.members");
        Route::post("departments/{id}/members", [
            \App\Http\Controllers\DepartmentController::class,
            "addMember",
        ])->name("departments.members.add");
        Route::delete("departments/{id}/subjects/{subjectId}", [
            \App\Http\Controllers\DepartmentController::class,
            "removeSubject",
        ])->name("departments.subjects.remove");
        Route::post("departments/{id}/send-sms", [
            \App\Http\Controllers\DepartmentController::class,
            "sendSmsToMembers",
        ])->name("departments.members.sms");

        // Strategic Goals
        Route::resource(
            "goals",
            \App\Http\Controllers\StrategicGoalController::class,
        );
        Route::post("goals/{id}/publish", [
            \App\Http\Controllers\StrategicGoalController::class,
            "publish",
        ])->name("goals.publish");

        // Departmental Objectives
        Route::resource(
            "objectives",
            \App\Http\Controllers\DepartmentalObjectiveController::class,
        );

        // Action Plans
        Route::post("action-plans", [
            \App\Http\Controllers\DepartmentalObjectiveController::class,
            "storeActionPlan",
        ])->name("objectives.storeActionPlan");

        // Tasks
        Route::resource(
            "tasks",
            \App\Http\Controllers\SgpmTaskController::class,
        );
        Route::post("tasks/{id}/submit", [
            \App\Http\Controllers\SgpmTaskController::class,
            "submitProgress",
        ])->name("tasks.submit");
        Route::post("tasks/{id}/evaluate", [
            \App\Http\Controllers\SgpmTaskController::class,
            "evaluate",
        ])->name("tasks.evaluate");

        // Subtasks
        Route::post("subtasks", [
            \App\Http\Controllers\SgpmTaskController::class,
            "storeSubtask",
        ])->name("subtasks.store");
        Route::post("subtasks/{id}/submit", [
            \App\Http\Controllers\SgpmTaskController::class,
            "submitSubtask",
        ])->name("subtasks.submit");
        Route::post("subtasks/{id}/approve", [
            \App\Http\Controllers\SgpmTaskController::class,
            "approveSubtask",
        ])->name("subtasks.approve");
        Route::post("subtasks/{id}/reject", [
            \App\Http\Controllers\SgpmTaskController::class,
            "rejectSubtask",
        ])->name("subtasks.reject");

        // Performance Dashboard
        Route::get("performance", [
            \App\Http\Controllers\SgpmPerformanceController::class,
            "index",
        ])->name("performance.index");
        Route::get("performance/hod", [
            \App\Http\Controllers\SgpmPerformanceController::class,
            "hodDashboard",
        ])->name("performance.hod");
        Route::get("performance/staff", [
            \App\Http\Controllers\SgpmPerformanceController::class,
            "staffDashboard",
        ])->name("performance.staff");
        Route::get("performance/goal/{id}", [
            \App\Http\Controllers\SgpmPerformanceController::class,
            "adminReviewGoal",
        ])->name("performance.goal.review");
        Route::post("performance/subtasks/{id}/approve", [
            \App\Http\Controllers\SgpmPerformanceController::class,
            "adminApproveSubtask",
        ])->name("performance.subtasks.approve");
        Route::post("performance/subtasks/{id}/reject", [
            \App\Http\Controllers\SgpmPerformanceController::class,
            "adminRejectSubtask",
        ])->name("performance.subtasks.reject");

        // Notifications
        Route::post("/notifications/read", [
            App\Http\Controllers\SgpmNotificationController::class,
            "markAsRead",
        ])->name("notifications.read");
        Route::post("/notifications/read-all", [
            App\Http\Controllers\SgpmNotificationController::class,
            "markAllAsRead",
        ])->name("notifications.read_all");
    });

// -------------------------------------------------------
// SMS Management Routes
// -------------------------------------------------------
Route::get("manage_sms", [SMS_InformationController::class, "index"])->name(
    "manage_sms",
);

// Recipient loaders
Route::get("sms/get-all-parents", [
    SMS_InformationController::class,
    "get_all_parents",
])->name("get_all_parents_sms");
Route::get("sms/get-all-teachers", [
    SMS_InformationController::class,
    "get_all_teachers",
])->name("get_all_teachers_sms");
Route::get("sms/get-all-staff", [
    SMS_InformationController::class,
    "get_all_staff",
])->name("get_all_staff_sms");
Route::get("sms/get-parents-by-class", [
    SMS_InformationController::class,
    "get_parents_by_class",
])->name("get_parents_by_class_sms");

// Sending
Route::post("sms/send", [SMS_InformationController::class, "send_sms"])->name(
    "send_sms",
);

// Balance
Route::get("sms/balance", [
    SMS_InformationController::class,
    "get_sms_balance",
])->name("get_sms_balance");

// Delivery Report
Route::get("sms/logs", [
    SMS_InformationController::class,
    "get_sms_logs",
])->name("get_sms_logs");
Route::get("sms/delivery-status", [
    SMS_InformationController::class,
    "get_delivery_status",
])->name("get_delivery_status");
