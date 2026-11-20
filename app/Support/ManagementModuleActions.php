<?php

namespace App\Support;

/**
 * Website Management action buttons for mobile hubs.
 * Each action mirrors a page or row button on the corresponding Admin blade.
 *
 * Shape returned to the app:
 * { key, label, method, path, scope, enabled, permission_any?, note? }
 */
class ManagementModuleActions
{
    /**
     * @param  array<int, string>  $permissionNames  Teacher permission names (unique)
     * @param  bool  $isAdmin
     * @param  bool  $categoryGranted  Hub already passed permission_category gate
     * @return array{page_actions: array, row_actions: array, permissions: array}
     */
    public static function forModule(string $moduleKey, array $permissionNames, bool $isAdmin = false, bool $categoryGranted = true): array
    {
        $names = array_values(array_unique(array_map('strval', $permissionNames)));
        // Prefer named permissions; if hub category is granted, still show the website buttons
        // (sibling routes / web controllers may still refuse a specific write).
        $can = fn (array $any) => $isAdmin
            || ($any === [] ? $categoryGranted : (self::hasAny($names, $any) || $categoryGranted));
        $categoryOk = $categoryGranted || $isAdmin;

        $defs = match ($moduleKey) {
            'examination' => self::examination($can),
            'result' => self::result($can, $categoryOk),
            'subject' => self::subject($can),
            'classes' => self::classes($can),
            'student' => self::student($can),
            'timetable' => self::timetable($can),
            'fees' => self::fees($can, $categoryOk, $isAdmin),
            'library' => self::library($categoryOk),
            'accommodation' => self::accommodation($categoryOk, $isAdmin),
            default => ['page' => [], 'row' => []],
        };

        return [
            'page_actions' => $defs['page'],
            'row_actions' => $defs['row'],
            'permissions' => $names,
            'ui_note' => 'Show only actions with enabled=true. Paths are under /api/v1/teacher (except accommodation /api/accommodation or /accommodation).',
        ];
    }

    private static function hasAny(array $names, array $any): bool
    {
        if ($any === []) {
            return true;
        }
        foreach ($any as $p) {
            if (in_array($p, $names, true)) {
                return true;
            }
        }

        return false;
    }

    private static function action(
        string $key,
        string $label,
        string $method,
        string $path,
        string $scope,
        bool $enabled,
        array $permissionAny = [],
        ?string $note = null
    ): array {
        $row = [
            'key' => $key,
            'label' => $label,
            'method' => strtoupper($method),
            'path' => $path,
            'scope' => $scope,
            'enabled' => $enabled,
        ];
        if ($permissionAny !== []) {
            $row['permission_any'] = array_values($permissionAny);
        }
        if ($note !== null) {
            $row['note'] = $note;
        }

        return $row;
    }

    private static function examination(callable $can): array
    {
        $create = ['examination_create', 'create_exam', 'create_examination', 'manage_exam'];
        $update = ['examination_update', 'edit_exam', 'manage_exam'];
        $delete = ['examination_delete', 'delete_exam', 'manage_exam'];
        $view = ['examination_read_only', 'examination_create', 'examination_update', 'examination_delete', 'manage_exam', 'view_exams', 'view_exam_details'];
        $papers = ['view_exam_papers', 'approve_exam_paper', 'reject_exam_paper', 'examination_read_only', 'manage_exam'];
        $approve = ['approve_exam', 'examination_update'];
        $reject = ['reject_exam', 'examination_update'];

        return [
            'page' => [
                self::action('create', 'Create New Examination', 'POST', '/store_examination', 'page', $can($create), $create),
                self::action('search', 'Search Examinations', 'POST', '/search_examinations', 'page', $can($view), $view),
                self::action('list', 'List / Filter', 'GET', '/manageExamination', 'page', $can($view), $view),
            ],
            'row' => [
                self::action('view_more', 'View More', 'GET', '/get_exam_details/{examID}', 'row', $can($view), $view),
                self::action('get', 'Get Exam (edit form)', 'GET', '/get_exam/{examID}', 'row', $can($update), $update),
                self::action('edit', 'Edit', 'POST', '/update_examination/{examID}', 'row', $can($update), $update),
                self::action('exam_papers', 'Exam Papers', 'GET', '/get_exam_papers/{examID}', 'row', $can($papers), $papers),
                self::action('exam_halls', 'Exam Halls', 'GET', '/get_exam_details/{examID}', 'row', $can($view), $view, 'Halls section in exam details'),
                self::action('attendance', 'Attendance', 'GET', '/admin/exam-attendance/{examID}', 'row', $can($view), $view),
                self::action('toggle_enter_result', 'Allow / Disallow Enter Result', 'POST', '/toggle_enter_result/{examID}', 'row', $can($update), $update),
                self::action('toggle_publish_result', 'Publish / Unpublish Result', 'POST', '/toggle_publish_result/{examID}', 'row', $can($update), $update),
                self::action('toggle_upload_paper', 'Allow / Disallow Upload Paper', 'POST', '/toggle_upload_paper/{examID}', 'row', $can($update), $update),
                self::action('shift_preview', 'Shift Students (preview)', 'GET', '/exam_shift_preview/{examID}', 'row', $can($update), $update),
                self::action('shift_students', 'Shift Students', 'POST', '/auto_shift_students/{examID}', 'row', $can($update), $update),
                self::action('unshift_students', 'Undo Shift', 'POST', '/unshift_students/{examID}', 'row', $can($update), $update),
                self::action('approve', 'Approve Exam', 'POST', '/approve_exam/{examID}', 'row', $can($approve), $approve, 'Only when approval_status is Pending'),
                self::action('reject', 'Reject Exam', 'POST', '/approve_exam/{examID}', 'row', $can($reject), $reject, 'Send reject body; only when Pending'),
                self::action('delete', 'Delete', 'DELETE', '/delete_examination/{examID}', 'row', $can($delete), $delete),
                self::action('approve_paper', 'Approve Paper', 'POST', '/approve_reject_exam_paper/{examPaperID}', 'row', $can(['approve_exam_paper']), ['approve_exam_paper']),
                self::action('reject_paper', 'Reject Paper', 'POST', '/approve_reject_exam_paper/{examPaperID}', 'row', $can(['reject_exam_paper']), ['reject_exam_paper']),
            ],
        ];
    }

    private static function result(callable $can, bool $categoryOk): array
    {
        $view = ['view_results', 'result_read_only', 'result_create', 'result_update', 'result_delete', 'manage_results', 'manageResults'];

        return [
            'page' => [
                self::action('filter', 'Filter / Search', 'GET', '/manageResults', 'page', $can($view) || $categoryOk, $view),
                self::action('define_grades', 'Define Grades', 'POST', '/grade_definitions', 'page', $categoryOk),
                self::action('get_grades', 'Get Grade Definitions', 'GET', '/grade_definitions', 'page', $categoryOk),
                self::action('define_report', 'Define Report', 'POST', '/save_report_definition', 'page', $categoryOk),
                self::action('get_reports', 'Get Report Definitions', 'GET', '/get_report_definitions', 'page', $categoryOk),
                self::action('delete_report', 'Delete Report Definition', 'DELETE', '/delete_report_definition/{id}', 'page', $categoryOk),
                self::action('define_ca', 'Define CA', 'POST', '/save_ca_definition', 'page', $categoryOk),
                self::action('get_ca', 'Get CA Definitions', 'GET', '/get_ca_definitions', 'page', $categoryOk),
                self::action('delete_ca', 'Delete CA Definition', 'DELETE', '/delete_ca_definition/{id}', 'page', $categoryOk),
                self::action('export_all_pdf', 'Export All PDF', 'GET', '/admin/download-results-pdf', 'page', $categoryOk),
                self::action('export_all_excel', 'Export All Excel', 'GET', '/admin/download-results-excel', 'page', $categoryOk),
                self::action('send_sms_all', 'Send SMS (all)', 'POST', '/admin/send-result-sms', 'page', $categoryOk),
                self::action('remind_teacher', 'Remind Teacher', 'POST', '/send_teacher_reminder', 'page', $categoryOk),
                self::action('remind_broadcast', 'Broadcast Reminder', 'POST', '/send_broadcast_reminder', 'page', $categoryOk),
                self::action('assign_teacher', 'Assign Teacher to Result', 'POST', '/admin/assign-teacher-to-result', 'page', $categoryOk),
                self::action('export_subject_stats', 'Subject Stats PDF', 'POST', '/admin/export-subject-performance-statistics-pdf', 'page', $categoryOk),
            ],
            'row' => [
                self::action('download_student_pdf', 'Download Student PDF', 'GET', '/admin/download-results-pdf', 'row', $categoryOk),
                self::action('download_student_excel', 'Download Student Excel', 'GET', '/admin/download-results-excel', 'row', $categoryOk),
                self::action('send_sms', 'Send SMS', 'POST', '/admin/send-result-sms', 'row', $categoryOk),
                self::action('publish_exam', 'Publish / Unpublish Exam', 'POST', '/toggle_exam_publication', 'row', $categoryOk),
                self::action('publish_report', 'Publish / Unpublish Report', 'POST', '/toggle_report_publication', 'row', $categoryOk),
            ],
        ];
    }

    private static function subject(callable $can): array
    {
        $create = ['subject_create', 'create_subject', 'create_class_subject'];
        $edit = ['subject_update', 'edit_subject', 'update_class_subject'];
        $delete = ['subject_delete', 'delete_subject', 'delete_class_subject'];
        $view = ['subject_read_only', 'view_class_subjects', 'view_subjects', 'subject_create', 'subject_update', 'subject_delete'];
        $activate = ['activate_subject', 'activate_class_subject', 'subject_update'];

        return [
            'page' => [
                self::action('view_class_subjects', 'View Class Subjects', 'GET', '/get_class_subjects', 'page', $can($view), $view),
                self::action('add_school_subject', 'Add School Subject', 'POST', '/save_school_subject', 'page', $can($create), $create),
                self::action('add_class_subject', 'Add Class Subject', 'POST', '/save_class_subject', 'page', $can($create), $create),
                self::action('by_subclass', 'Subjects by Subclass', 'GET', '/get_class_subjects_by_subclass/{subclassID}', 'page', $can($view), $view),
            ],
            'row' => [
                self::action('edit_school_subject', 'Edit School Subject', 'POST', '/update_school_subject', 'row', $can($edit), $edit),
                self::action('delete_school_subject', 'Delete School Subject', 'DELETE', '/delete_school_subject/{subjectID}', 'row', $can($delete), $delete),
                self::action('activate_subject', 'Activate / Deactivate Subject', 'POST', '/activate_subject/{subjectID}', 'row', $can($activate), $activate),
                self::action('edit_class_subject', 'Edit Class Subject', 'POST', '/update_class_subject', 'row', $can($edit), $edit),
                self::action('delete_class_subject', 'Delete Class Subject', 'DELETE', '/delete_class_subject/{classSubjectID}', 'row', $can($delete), $delete),
                self::action('activate_class_subject', 'Activate Class Subject', 'POST', '/activate_class_subject/{classSubjectID}', 'row', $can($activate), $activate),
                self::action('election_students', 'Subject Election Students', 'GET', '/get_subclass_students/{subclassID}', 'row', $can($view), $view),
                self::action('save_election', 'Save Subject Election', 'POST', '/save_subject_election', 'row', $can($edit), $edit),
                self::action('deselect_student', 'Deselect Student', 'POST', '/deselect_student', 'row', $can($edit), $edit),
            ],
        ];
    }

    private static function classes(callable $can): array
    {
        $createClass = ['classes_create', 'create_class', 'create_subclass', 'create_combie'];
        $editClass = ['classes_update', 'edit_class', 'edit_subclass', 'edit_combie'];
        $deleteClass = ['classes_delete', 'delete_class', 'delete_subclass', 'delete_combie'];
        $view = ['classes_read_only', 'view_all_class', 'view_classes', 'view_students', 'view_subjects', 'view_combies', 'view_grading'];
        $activate = ['activate_class', 'classes_update'];

        return [
            'page' => [
                self::action('view_classes', 'View Classes', 'GET', '/get_classes', 'page', $can($view), $view),
                self::action('add_class', 'Add Class', 'POST', '/save_class', 'page', $can($createClass), $createClass),
                self::action('add_subclass', 'Add Subclass', 'POST', '/save_sub_lass', 'page', $can($createClass), $createClass),
                self::action('add_combie', 'Add Combie', 'POST', '/save_combie', 'page', $can($createClass), $createClass),
                self::action('grading_get', 'Get Grading', 'GET', '/get_class_grading', 'page', $can($view), $view),
                self::action('grading_update', 'Update Grade Range', 'POST', '/update_grade_range', 'page', $can($editClass), $editClass),
            ],
            'row' => [
                self::action('get_class', 'Get Class', 'GET', '/get_class/{classID}', 'row', $can($view), $view),
                self::action('edit_class', 'Edit Class', 'POST', '/update_class', 'row', $can($editClass), $editClass),
                self::action('activate_class', 'Activate / Deactivate Class', 'POST', '/activate_class/{classID}', 'row', $can($activate), $activate),
                self::action('delete_class', 'Delete Class', 'DELETE', '/delete_class/{classID}', 'row', $can($deleteClass), $deleteClass),
                self::action('get_subclass', 'Get Subclass', 'GET', '/get_subclass/{subclassID}', 'row', $can($view), $view),
                self::action('edit_subclass', 'Edit Subclass', 'POST', '/update_subclass', 'row', $can($editClass), $editClass),
                self::action('activate_subclass', 'Activate Subclass', 'POST', '/activate_subclass/{subclassID}', 'row', $can($activate), $activate),
                self::action('delete_subclass', 'Delete Subclass', 'DELETE', '/delete_subclass/{subclassID}', 'row', $can($deleteClass), $deleteClass),
                self::action('view_students', 'View Students', 'GET', '/get_subclass_students/{subclassID}', 'row', $can($view), $view),
                self::action('view_subjects', 'View Subjects', 'GET', '/get_subclass_subjects/{subclassID}', 'row', $can($view), $view),
                self::action('edit_combie', 'Edit Combie', 'POST', '/update_combie', 'row', $can($editClass), $editClass),
                self::action('delete_combie', 'Delete Combie', 'DELETE', '/delete_combie/{combieID}', 'row', $can($deleteClass), $deleteClass),
            ],
        ];
    }

    private static function student(callable $can): array
    {
        $create = ['student_create', 'register_students'];
        $update = ['student_update', 'edit_student'];
        $delete = ['student_delete', 'delete_student'];
        $view = ['student_read_only', 'view_students', 'student_create', 'student_update', 'student_delete'];

        return [
            'page' => [
                self::action('list', 'Students List', 'GET', '/get_students_list', 'page', $can($view), $view),
                self::action('statistics', 'Statistics', 'GET', '/get_student_statistics', 'page', $can($view), $view),
                self::action('register', 'Register Student', 'POST', '/save_student', 'page', $can($create), $create),
                self::action('quick_register', 'Quick Registration', 'POST', '/save_student', 'page', $can($create), $create, 'Quick enroll mode'),
                self::action('template', 'Download Template', 'GET', '/download_student_template', 'page', $can($create), $create),
                self::action('import', 'Import Students', 'POST', '/upload_students', 'page', $can($create), $create),
                self::action('enroll', 'Enroll Student', 'POST', '/save_student', 'page', $can($create) || $can($update), array_merge($create, $update)),
                self::action('move_preview', 'Move Students Preview', 'GET', '/move_students_preview', 'page', $can($update), $update),
                self::action('move_execute', 'Move Students', 'POST', '/move_students_execute', 'page', $can($update), $update),
                self::action('move_batches', 'Move Batches', 'GET', '/move_students_batches', 'page', $can($update), $update),
                self::action('undo_move', 'Undo Move', 'POST', '/move_students_undo', 'page', $can($update), $update),
                self::action('delete_all_not_enrolled', 'Delete All Not Enrolled', 'POST', '/delete_all_not_enrolled_students', 'page', $can($delete), $delete),
                self::action('delete_selected_not_enrolled', 'Delete Selected Not Enrolled', 'POST', '/delete_selected_not_enrolled_students', 'page', $can($delete), $delete),
                self::action('duplicate_names', 'Duplicate Names', 'GET', '/get_duplicate_student_names', 'page', $can($view), $view),
                self::action('export_pdf', 'Export PDF', 'GET', '/export_students_pdf', 'page', $can($view), $view),
                self::action('export_excel', 'Export Excel', 'GET', '/export_students_excel', 'page', $can($view), $view),
                self::action('subclasses_stats', 'Subclasses with Stats', 'GET', '/get_subclasses_with_stats', 'page', $can($view), $view),
            ],
            'row' => [
                self::action('view', 'View Details', 'GET', '/get_student_details/{studentID}', 'row', $can($view), $view),
                self::action('get', 'Get Student (edit)', 'GET', '/get_student/{studentID}', 'row', $can($update), $update),
                self::action('edit', 'Edit Student', 'POST', '/update_student', 'row', $can($update), $update),
                self::action('edit_enrollment', 'Edit Enrollment', 'POST', '/update_enrollment_exit_status', 'row', $can($update), $update),
                self::action('move_options', 'Move Options', 'GET', '/move_student_options/{studentID}', 'row', $can($update), $update),
                self::action('move_single', 'Move Student', 'POST', '/move_student_single', 'row', $can($update), $update),
                self::action('transfer', 'Transfer', 'POST', '/transfer_student', 'row', $can($update), $update),
                self::action('delete', 'Delete', 'DELETE', '/delete_student/{studentID}', 'row', $can($delete), $delete),
                self::action('delete_not_enrolled', 'Delete Not Enrolled', 'DELETE', '/delete_not_enrolled_student/{studentID}', 'row', $can($delete), $delete),
                self::action('fingerprint', 'Send to Fingerprint', 'POST', '/send_student_to_fingerprint', 'row', $can($update), $update),
                self::action('export_results_pdf', 'Export Results PDF', 'GET', '/export_student_results_pdf', 'row', $can($view), $view),
                self::action('export_attendance_pdf', 'Export Attendance PDF', 'GET', '/export_student_attendance_pdf', 'row', $can($view), $view),
                self::action('export_attendance_excel', 'Export Attendance Excel', 'GET', '/export_student_attendance_excel', 'row', $can($view), $view),
            ],
        ];
    }

    private static function timetable(callable $can): array
    {
        $create = ['timetable_create', 'create_timetable'];
        $update = ['timetable_update', 'edit_timetable'];
        $delete = ['timetable_delete', 'delete_timetable'];
        $view = ['timetable_read_only', 'show_timetable', 'view_all_timetable', 'timetable_create', 'timetable_update', 'timetable_delete'];

        return [
            'page' => [
                self::action('create_exam_timetable', 'Create Exam Timetable', 'POST', '/store_exam_timetable', 'page', $can($create), $create),
                self::action('get_exam_timetables', 'Get Exam Timetables', 'GET', '/get_exam_timetables', 'page', $can($view), $view),
                self::action('preview_auto', 'Preview Automatic', 'POST', '/preview_automatic_timetable', 'page', $can($create), $create),
                self::action('save_session', 'Save Session Timetable', 'POST', '/admin/save-class-session-timetables', 'page', $can($create) || $can($update), array_merge($create, $update)),
                self::action('get_session', 'Get Session Timetable', 'GET', '/admin/get-session-timetable', 'page', $can($view), $view),
                self::action('get_definition', 'Get Session Definition', 'GET', '/admin/get-session-timetable-definition', 'page', $can($view), $view),
                self::action('save_definition', 'Save Session Definition', 'POST', '/admin/save-session-timetable-definition', 'page', $can($create) || $can($update), array_merge($create, $update)),
                self::action('export_session_pdf', 'Export Session PDF', 'GET', '/admin/export-session-timetable-pdf', 'page', $can($view), $view),
                self::action('export_school_pdf', 'Export School Session PDF', 'GET', '/admin/export-school-session-timetable-pdf', 'page', $can($view), $view),
                self::action('shuffle_session', 'Shuffle Session', 'POST', '/admin/shuffle-session-timetable', 'page', $can($update), $update),
                self::action('swap_session', 'Swap Session', 'POST', '/admin/swap-session-timetable', 'page', $can($update), $update),
                self::action('delete_session', 'Delete Session Timetable', 'POST', '/admin/delete-class-session-timetable', 'page', $can($delete), $delete),
                self::action('delete_all_test', 'Delete All Test Schedules', 'POST', '/admin/api/delete-all-test-schedules', 'page', $can($delete), $delete),
            ],
            'row' => [
                self::action('delete_slot', 'Delete Exam Slot', 'DELETE', '/delete_exam_timetable/{examTimetableID}', 'row', $can($delete), $delete),
                self::action('delete_all_exam', 'Delete All for Exam', 'DELETE', '/delete_all_exam_timetable/{examID}', 'row', $can($delete), $delete),
                self::action('update_time', 'Update Time', 'PUT', '/update_exam_timetable_time/{examTimetableID}', 'row', $can($update), $update),
                self::action('update_supervisor', 'Update Supervise Teacher', 'POST', '/update_supervise_teacher', 'row', $can($update), $update),
                self::action('update_hall_supervisor', 'Update Hall Supervisor', 'PUT', '/update_hall_supervisor/{id}', 'row', $can($update), $update),
                self::action('shuffle_exam', 'Shuffle Exam Timetable', 'POST', '/shuffle_exam_timetable/{examID}', 'row', $can($update), $update),
                self::action('swap_subjects', 'Swap Subjects', 'POST', '/swap_exam_subjects', 'row', $can($update), $update),
            ],
        ];
    }

    private static function fees(callable $can, bool $categoryOk, bool $isAdmin): array
    {
        return [
            'page' => [
                self::action('create_fee', 'Create Fee', 'POST', '/store_fee', 'page', $categoryOk),
                self::action('payments', 'Payments', 'GET', '/view_payments', 'page', $categoryOk),
                self::action('payments_ajax', 'Payments AJAX', 'GET', '/get_payments_ajax', 'page', $categoryOk),
                self::action('record_payment', 'Record Payment', 'POST', '/record_payment', 'page', $categoryOk),
                self::action('control_numbers', 'Generate Control Numbers', 'POST', '/generate_control_numbers', 'page', $categoryOk),
                self::action('assign_conditions', 'Assign Fee Conditions', 'POST', '/assign_students_to_conditions', 'page', $categoryOk),
                self::action('agreements', 'Store Fee Agreements', 'POST', '/store_fee_agreements', 'page', $categoryOk),
                self::action('assign_sponsors', 'Assign Sponsors', 'POST', '/assign_sponsor_students', 'page', $categoryOk),
                self::action('export_structure_pdf', 'Export Fee Structure PDF', 'GET', '/admin/export-fees-structure-pdf', 'page', $categoryOk),
                self::action('active_banks', 'Active Banks', 'GET', '/fees/active_banks', 'page', $isAdmin, [], 'Admin only on website'),
                self::action('bank_send_otp', 'Bank Account OTP', 'POST', '/fees/bank_accounts/send_otp', 'page', $isAdmin, [], 'Admin only'),
                self::action('bank_verify_otp', 'Verify Bank OTP', 'POST', '/fees/bank_accounts/verify_otp', 'page', $isAdmin, [], 'Admin only'),
            ],
            'row' => [
                self::action('get', 'Get Fee', 'GET', '/get_fee/{feeID}', 'row', $categoryOk),
                self::action('edit', 'Edit Fee', 'POST', '/update_fee/{feeID}', 'row', $categoryOk),
                self::action('delete', 'Delete Fee', 'DELETE', '/delete_fee/{feeID}', 'row', $categoryOk),
                self::action('toggle', 'Toggle Fee Status', 'POST', '/toggle_fee_status/{feeID}', 'row', $categoryOk),
                self::action('class_fees', 'Class Fees', 'GET', '/get_class_fees/{classID}', 'row', $categoryOk),
                self::action('installments', 'View Installments', 'GET', '/fees/installments/{feeID}', 'row', $categoryOk),
                self::action('save_installments', 'Save Installments', 'POST', '/fees/installments', 'row', $categoryOk),
                self::action('agreements_by_class', 'Agreements by Class', 'GET', '/get_existing_agreements/{classID}', 'row', $categoryOk),
            ],
        ];
    }

    private static function library(bool $categoryOk): array
    {
        return [
            'page' => [
                self::action('list_books', 'List / Filter Books', 'GET', '/get_books', 'page', $categoryOk),
                self::action('add_book', 'Add Book', 'POST', '/store_book', 'page', $categoryOk),
                self::action('borrow', 'Borrow / Lend', 'POST', '/borrow_book', 'page', $categoryOk),
                self::action('record_damage', 'Record Damage', 'POST', '/store_book_damage', 'page', $categoryOk),
                self::action('record_loss', 'Record Loss', 'POST', '/store_book_loss', 'page', $categoryOk),
                self::action('borrows', 'Borrow List', 'GET', '/get_book_borrows', 'page', $categoryOk),
                self::action('statistics', 'Statistics', 'GET', '/get_book_statistics', 'page', $categoryOk),
                self::action('export_pdf', 'Export PDF', 'GET', '/export_library_pdf', 'page', $categoryOk),
                self::action('bulk_update', 'Bulk Edit Details', 'POST', '/bulk_update_book_details', 'page', $categoryOk),
                self::action('get_by_isbn', 'Get Book by ISBN', 'GET', '/get_book_by_isbn', 'page', $categoryOk),
            ],
            'row' => [
                self::action('edit', 'Edit Book', 'POST', '/update_book/{bookID}', 'row', $categoryOk),
                self::action('delete', 'Delete Book', 'DELETE', '/delete_book/{bookID}', 'row', $categoryOk),
                self::action('lend', 'Lend Book', 'POST', '/borrow_book', 'row', $categoryOk),
                self::action('return', 'Return Book', 'POST', '/return_book/{borrowID}', 'row', $categoryOk),
                self::action('resolve_incident', 'Resolve Lost/Damaged', 'POST', '/resolve_book_incident', 'row', $categoryOk),
            ],
        ];
    }

    private static function accommodation(bool $categoryOk, bool $isAdmin): array
    {
        // Mobile bridges under /accommodation/*; website also has /api/accommodation/*
        $ok = $categoryOk || $isAdmin;

        return [
            'page' => [
                self::action('list_blocks', 'List Blocks', 'GET', '/accommodation/blocks', 'page', $ok),
                self::action('save_block', 'Save Block', 'POST', '/accommodation/blocks', 'page', $ok),
                self::action('list_rooms', 'List Rooms', 'GET', '/accommodation/rooms', 'page', $ok),
                self::action('save_room', 'Save Room', 'POST', '/accommodation/rooms', 'page', $ok),
                self::action('list_beds', 'List Beds', 'GET', '/accommodation/beds', 'page', $ok),
                self::action('save_beds', 'Save Beds', 'POST', '/accommodation/beds', 'page', $ok),
                self::action('bed_assignments', 'Bed Assignments', 'GET', '/accommodation/bed-assignments', 'page', $ok),
                self::action('search_students', 'Search Students', 'GET', '/accommodation/students', 'page', $ok),
            ],
            'row' => [
                self::action('update_block', 'Update Block', 'PUT', '/accommodation/blocks', 'row', $ok),
                self::action('delete_block', 'Delete Block', 'DELETE', '/accommodation/blocks/{blockID}', 'row', $ok),
                self::action('remove_students_block', 'Remove Students from Block', 'POST', '/accommodation/blocks/{blockID}/remove-students', 'row', $ok),
                self::action('update_room', 'Update Room', 'PUT', '/accommodation/rooms', 'row', $ok),
                self::action('delete_room', 'Delete Room', 'DELETE', '/accommodation/rooms/{roomID}', 'row', $ok),
                self::action('update_bed', 'Update Bed', 'PUT', '/accommodation/beds', 'row', $ok),
                self::action('delete_bed', 'Delete Bed', 'DELETE', '/accommodation/beds/{bedID}', 'row', $ok),
                self::action('assign', 'Assign Student', 'POST', '/accommodation/beds/assign', 'row', $ok),
                self::action('release', 'Release Student', 'POST', '/accommodation/beds/release', 'row', $ok),
                self::action('move', 'Move / Swap Student', 'POST', '/accommodation/beds/move', 'row', $ok),
            ],
        ];
    }
}
