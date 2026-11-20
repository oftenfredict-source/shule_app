<?php

namespace App\Support;

/**
 * Website-faithful toolbar + card action builders for Management hubs.
 * Labels match the Blade SETTINGS / ACTION / card buttons exactly.
 */
class ManagementScreenUi
{
    /**
     * @param  array<int, string>  $names
     */
    public static function can(array $names, array $any, bool $isAdmin, bool $categoryOk = true): bool
    {
        if ($isAdmin) {
            return true;
        }
        if ($any === []) {
            return $categoryOk;
        }
        foreach ($any as $p) {
            if (in_array($p, $names, true)) {
                return true;
            }
        }

        // Hub already passed category gate — show the same buttons the Management role opens.
        return $categoryOk;
    }

    public static function btn(
        string $key,
        string $label,
        string $method,
        string $path,
        bool $enabled,
        ?string $icon = null,
        ?string $note = null
    ): array {
        $row = [
            'key' => $key,
            'label' => $label,
            'method' => strtoupper($method),
            'path' => $path,
            'enabled' => $enabled,
        ];
        if ($icon) {
            $row['icon'] = $icon;
        }
        if ($note) {
            $row['note'] = $note;
        }

        return $row;
    }

    /** SETTINGS panel on manageClasses. */
    public static function classesToolbar(array $names, bool $isAdmin, bool $isSecondary): array
    {
        $view = self::can($names, ['view_all_class', 'classes_read_only', 'view_class_details', 'classes_create', 'classes_update', 'classes_delete', 'create_class', 'edit_class', 'delete_class'], $isAdmin);
        $grading = self::can($names, ['view_grading', 'classes_read_only', 'classes_update'], $isAdmin);
        $viewCombies = self::can($names, ['view_combies', 'classes_read_only', 'classes_create', 'classes_update'], $isAdmin);
        $addCombie = self::can($names, ['create_combie', 'classes_create'], $isAdmin);
        $addSubclass = self::can($names, ['create_subclass', 'classes_create'], $isAdmin);
        $addClass = self::can($names, ['create_class', 'classes_create'], $isAdmin);

        $items = [
            self::btn('view_classes', 'VIEW CLASSES', 'GET', '/get_classes', $view, 'grid'),
            self::btn('grading', 'GRADING', 'GET', '/get_class_grading', $grading, 'award'),
        ];
        if ($isSecondary) {
            $items[] = self::btn('view_combies', 'VIEW COMBIES', 'GET', '/get_classes', $viewCombies, 'eye', 'Open combies list from hub.combies');
            $items[] = self::btn('add_combies', 'ADD COMBIES', 'POST', '/save_combie', $addCombie, 'layers');
        }
        $items[] = self::btn('add_subclass', 'ADD SUBCLASS', 'POST', '/save_sub_lass', $addSubclass, 'plus-circle');
        $items[] = self::btn('add_class', 'ADD CLASS', 'POST', '/save_class', $addClass, 'plus-square');

        return array_values(array_filter($items, fn ($b) => $b['enabled']));
    }

    public static function classCardActions(array $names, bool $isAdmin, string $status): array
    {
        $edit = self::can($names, ['edit_class', 'update_class', 'classes_update'], $isAdmin);
        $activate = self::can($names, ['activate_class', 'update_class', 'classes_update'], $isAdmin);
        $delete = self::can($names, ['delete_class', 'classes_delete'], $isAdmin);
        $isActive = strcasecmp($status, 'Active') === 0;

        return array_values(array_filter([
            self::btn('view_subclasses', 'View Subclasses', 'GET', '/get_classes', true, 'eye', 'Use card.subclasses from hub, or refresh via GET /get_classes'),
            self::btn('edit', 'Edit', 'GET', '/get_class/{classID}', $edit, 'pencil', 'Then POST /update_class'),
            self::btn(
                $isActive ? 'deactivate' : 'activate',
                $isActive ? 'Deactivate' : 'Activate',
                'POST',
                '/activate_class/{classID}',
                $activate,
                $isActive ? 'x-circle' : 'check-circle'
            ),
            self::btn('delete', 'Delete', 'DELETE', '/delete_class/{classID}', $delete, 'trash'),
        ], fn ($b) => $b['enabled']));
    }

    public static function subjectsToolbar(array $names, bool $isAdmin): array
    {
        $view = self::can($names, ['view_class_subjects', 'subject_read_only', 'subject_create', 'subject_update', 'subject_delete', 'view_subjects'], $isAdmin);
        $addClass = self::can($names, ['create_class_subject', 'subject_create'], $isAdmin);
        $addSchool = self::can($names, ['create_subject', 'subject_create'], $isAdmin);

        return array_values(array_filter([
            self::btn('view_class_subjects', 'VIEW CLASS SUBJECTS', 'GET', '/get_class_subjects', $view),
            self::btn('add_class_subject', 'ADD CLASS SUBJECT', 'POST', '/save_class_subject', $addClass),
            self::btn('add_school_subject', 'ADD SCHOOL SUBJECT', 'POST', '/save_school_subject', $addSchool),
        ], fn ($b) => $b['enabled']));
    }

    public static function subjectCardActions(array $names, bool $isAdmin, string $status): array
    {
        $edit = self::can($names, ['edit_subject', 'subject_update', 'update_subject'], $isAdmin);
        $delete = self::can($names, ['delete_subject', 'subject_delete'], $isAdmin);
        $activate = self::can($names, ['activate_subject', 'subject_update'], $isAdmin);
        $isActive = strcasecmp($status, 'Active') === 0;

        return array_values(array_filter([
            self::btn('edit', 'Edit', 'POST', '/update_school_subject', $edit),
            self::btn($isActive ? 'deactivate' : 'activate', $isActive ? 'Deactivate' : 'Activate', 'POST', '/activate_subject/{subjectID}', $activate),
            self::btn('delete', 'Delete', 'DELETE', '/delete_school_subject/{subjectID}', $delete),
        ], fn ($b) => $b['enabled']));
    }

    public static function libraryToolbar(bool $categoryOk): array
    {
        return array_values(array_filter([
            self::btn('add_book', 'ADD BOOK', 'POST', '/store_book', $categoryOk),
            self::btn('borrow_lend', 'BORROW / LEND', 'POST', '/borrow_book', $categoryOk),
            self::btn('record_damage', 'RECORD DAMAGE', 'POST', '/store_book_damage', $categoryOk),
            self::btn('record_loss', 'RECORD LOSS', 'POST', '/store_book_loss', $categoryOk),
            self::btn('edit_details', 'EDIT DETAILS', 'POST', '/bulk_update_book_details', $categoryOk),
            self::btn('export_pdf', 'PDF', 'GET', '/export_library_pdf', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function libraryCardActions(bool $categoryOk): array
    {
        return array_values(array_filter([
            self::btn('edit', 'EDIT', 'POST', '/update_book/{bookID}', $categoryOk),
            self::btn('delete', 'DELETE', 'DELETE', '/delete_book/{bookID}', $categoryOk),
            self::btn('lend', 'LEND BOOK', 'POST', '/borrow_book', $categoryOk),
            self::btn('return', 'RETURN', 'POST', '/return_book/{borrowID}', $categoryOk),
            self::btn('resolve', 'RESOLVE', 'POST', '/resolve_book_incident', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function feesToolbar(bool $categoryOk, bool $isAdmin): array
    {
        return array_values(array_filter([
            self::btn('create_fee', 'CREATE FEE', 'POST', '/store_fee', $categoryOk),
            self::btn('payments', 'PAYMENTS', 'GET', '/view_payments', $categoryOk),
            self::btn('discounts', 'DISCOUNTS', 'POST', '/assign_students_to_conditions', $categoryOk),
            self::btn('agreements', 'AGREEMENTS', 'POST', '/store_fee_agreements', $categoryOk),
            self::btn('assign_sponsors', 'ASSIGN SPONSORS', 'POST', '/assign_sponsor_students', $categoryOk),
            self::btn('account_numbers', 'ACCOUNT NUMBERS', 'GET', '/fees/active_banks', $isAdmin, null, 'Admin only'),
            self::btn('export_pdf', 'EXPORT PDF', 'GET', '/admin/export-fees-structure-pdf', $categoryOk),
            self::btn('installments', 'INSTALLMENTS', 'GET', '/fees/installments/{feeID}', $categoryOk),
            self::btn('deadlines', 'DEADLINES', 'GET', '/fees/deadlines', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function feeRowActions(bool $categoryOk): array
    {
        return array_values(array_filter([
            self::btn('installments', 'View Installments', 'GET', '/fees/installments/{feeID}', $categoryOk),
            self::btn('edit', 'Edit Fee', 'GET', '/get_fee/{feeID}', $categoryOk, null, 'Then POST /update_fee/{feeID}'),
            self::btn('delete', 'Delete Fee', 'DELETE', '/delete_fee/{feeID}', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function examinationToolbar(array $names, bool $isAdmin): array
    {
        $create = self::can($names, ['examination_create', 'create_exam', 'create_examination', 'manage_exam'], $isAdmin);

        return array_values(array_filter([
            self::btn('create', 'Create New Examination', 'POST', '/store_examination', $create),
            self::btn('search', 'Search', 'POST', '/search_examinations', true),
        ], fn ($b) => $b['enabled']));
    }

    public static function examinationCardActions(array $names, bool $isAdmin, ?string $approvalStatus): array
    {
        $view = self::can($names, ['examination_read_only', 'examination_create', 'examination_update', 'examination_delete', 'manage_exam', 'view_exams', 'view_exam_details'], $isAdmin);
        $update = self::can($names, ['examination_update', 'edit_exam', 'manage_exam'], $isAdmin);
        $delete = self::can($names, ['examination_delete', 'delete_exam', 'manage_exam'], $isAdmin);
        $papers = self::can($names, ['view_exam_papers', 'approve_exam_paper', 'reject_exam_paper', 'examination_read_only', 'manage_exam'], $isAdmin);
        $pending = strcasecmp((string) $approvalStatus, 'Pending') === 0;
        $approve = $pending && self::can($names, ['approve_exam', 'examination_update'], $isAdmin);
        $reject = $pending && self::can($names, ['reject_exam', 'examination_update'], $isAdmin);

        return array_values(array_filter([
            self::btn('view_more', 'View More', 'GET', '/get_exam_details/{examID}', $view),
            self::btn('exam_papers', 'Exam Papers', 'GET', '/get_exam_papers/{examID}', $papers),
            self::btn('exam_halls', 'Exam Halls', 'GET', '/get_exam_details/{examID}', $view),
            self::btn('attendance', 'Attendance', 'GET', '/admin/exam-attendance/{examID}', $view),
            self::btn('edit', 'Edit', 'GET', '/get_exam/{examID}', $update, null, 'Then POST /update_examination/{examID}'),
            self::btn('toggle_enter_result', 'Allow / Disallow Enter Result', 'POST', '/toggle_enter_result/{examID}', $update),
            self::btn('toggle_publish_result', 'Publish / Unpublish Result', 'POST', '/toggle_publish_result/{examID}', $update),
            self::btn('toggle_upload_paper', 'Allow / Disallow Upload Paper', 'POST', '/toggle_upload_paper/{examID}', $update),
            self::btn('shift_students', 'Shift Students', 'POST', '/auto_shift_students/{examID}', $update),
            self::btn('unshift_students', 'Undo Shift', 'POST', '/unshift_students/{examID}', $update),
            self::btn('approve', 'Approve', 'POST', '/approve_exam/{examID}', $approve),
            self::btn('reject', 'Reject', 'POST', '/approve_exam/{examID}', $reject),
            self::btn('delete', 'Delete', 'DELETE', '/delete_examination/{examID}', $delete),
        ], fn ($b) => $b['enabled']));
    }

    public static function studentToolbar(array $names, bool $isAdmin): array
    {
        $create = self::can($names, ['student_create', 'register_students'], $isAdmin);
        $update = self::can($names, ['student_update', 'edit_student'], $isAdmin);
        $delete = self::can($names, ['student_delete', 'delete_student'], $isAdmin);

        return array_values(array_filter([
            self::btn('register', 'Register', 'POST', '/save_student', $create),
            self::btn('quick_registration', 'Quick Registration', 'POST', '/save_student', $create),
            self::btn('quick_template', 'Quick Template', 'GET', '/download_student_template', $create),
            self::btn('quick_import', 'Quick Import', 'POST', '/upload_students', $create),
            self::btn('template', 'Template', 'GET', '/download_student_template', $create),
            self::btn('import', 'Import', 'POST', '/upload_students', $create),
            self::btn('enroll', 'Enroll Student', 'POST', '/save_student', $create || $update),
            self::btn('move', 'Move Students', 'POST', '/move_students_execute', $update),
            self::btn('undo_move', 'Undo Move', 'POST', '/move_students_undo', $update),
            self::btn('delete_all_not_enrolled', 'Delete All Not Enrolled', 'POST', '/delete_all_not_enrolled_students', $delete),
            self::btn('duplicate_names', 'Duplicate Names', 'GET', '/get_duplicate_student_names', true),
            self::btn('export_pdf', 'Export PDF', 'GET', '/export_students_pdf', true),
            self::btn('export_excel', 'Export Excel', 'GET', '/export_students_excel', true),
            self::btn('list', 'Load Students', 'GET', '/get_students_list', true),
        ], fn ($b) => $b['enabled']));
    }

    public static function studentRowActions(array $names, bool $isAdmin): array
    {
        $update = self::can($names, ['student_update', 'edit_student'], $isAdmin);
        $delete = self::can($names, ['student_delete', 'delete_student'], $isAdmin);

        return array_values(array_filter([
            self::btn('view', 'View Details', 'GET', '/get_student_details/{studentID}', true),
            self::btn('edit', 'Edit Student', 'GET', '/get_student/{studentID}', $update, null, 'Then POST /update_student'),
            self::btn('edit_enrollment', 'Edit Enrollment', 'POST', '/update_enrollment_exit_status', $update),
            self::btn('move_student', 'Move Student', 'POST', '/move_student_single', $update),
            self::btn('transfer', 'Transfer', 'POST', '/transfer_student', $update),
            self::btn('fingerprint', 'Send to Fingerprint', 'POST', '/send_student_to_fingerprint', $update),
            self::btn('delete', 'Delete Student', 'DELETE', '/delete_student/{studentID}', $delete),
        ], fn ($b) => $b['enabled']));
    }

    public static function resultsToolbar(bool $categoryOk, bool $isTeacherView): array
    {
        $adminTools = !$isTeacherView && $categoryOk;

        return array_values(array_filter([
            self::btn('define_grades', 'Define Grades', 'POST', '/grade_definitions', $adminTools),
            self::btn('define_report', 'Define Report', 'POST', '/save_report_definition', $adminTools),
            self::btn('define_ca', 'Define CA', 'POST', '/save_ca_definition', $adminTools),
            self::btn('filter', 'Filter', 'GET', '/manageResults', $categoryOk),
            self::btn('export_all_pdf', 'Export All PDF', 'GET', '/admin/download-results-pdf', $categoryOk),
            self::btn('export_all_excel', 'Export All Excel', 'GET', '/admin/download-results-excel', $categoryOk),
            self::btn('send_sms', 'Send SMS', 'POST', '/admin/send-result-sms', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function resultsRowActions(bool $categoryOk): array
    {
        return array_values(array_filter([
            self::btn('view_more', 'View More', 'GET', '/manageResults', $categoryOk),
            self::btn('download_pdf', 'Download PDF', 'GET', '/admin/download-results-pdf', $categoryOk),
            self::btn('download_excel', 'Download Excel', 'GET', '/admin/download-results-excel', $categoryOk),
            self::btn('publish', 'Publish / Unpublish', 'POST', '/toggle_exam_publication', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function timetableToolbar(array $names, bool $isAdmin): array
    {
        $create = self::can($names, ['timetable_create', 'create_timetable'], $isAdmin);
        $update = self::can($names, ['timetable_update', 'edit_timetable'], $isAdmin);
        $delete = self::can($names, ['timetable_delete', 'delete_timetable'], $isAdmin);

        return array_values(array_filter([
            self::btn('create', 'Create Timetable', 'POST', '/store_exam_timetable', $create, null, 'Or POST /admin/save-class-session-timetables for sessions'),
            self::btn('load_session', 'Load Timetable', 'GET', '/admin/get-session-timetable?subclassID={subclassID}', true, null, 'Required: pick a subclass from hub.subclasses first, then call with that subclassID. schoolID comes from the token — do not send it.'),
            self::btn('export_pdf', 'PDF', 'GET', '/admin/export-session-timetable-pdf', true),
            self::btn('shuffle', 'Shuffle', 'POST', '/admin/shuffle-session-timetable', $update),
            self::btn('swap', 'Swap', 'POST', '/admin/swap-session-timetable', $update),
            self::btn('delete_session', 'Delete', 'POST', '/admin/delete-class-session-timetable', $delete),
        ], fn ($b) => $b['enabled']));
    }

    public static function accommodationToolbar(bool $categoryOk): array
    {
        return array_values(array_filter([
            self::btn('register_block', 'Register Block', 'POST', '/accommodation/blocks', $categoryOk),
            self::btn('view_blocks', 'View Blocks', 'GET', '/accommodation/blocks', $categoryOk),
            self::btn('register_room', 'Register Room', 'POST', '/accommodation/rooms', $categoryOk),
            self::btn('register_beds', 'Register Beds', 'POST', '/accommodation/beds', $categoryOk),
            self::btn('view_beds', 'View Beds', 'GET', '/accommodation/beds', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }

    public static function accommodationBlockActions(bool $categoryOk): array
    {
        return array_values(array_filter([
            self::btn('edit', 'Edit', 'PUT', '/accommodation/blocks', $categoryOk),
            self::btn('remove_students', 'Remove Students', 'POST', '/accommodation/blocks/{blockID}/remove-students', $categoryOk),
            self::btn('delete', 'Delete', 'DELETE', '/accommodation/blocks/{blockID}', $categoryOk),
        ], fn ($b) => $b['enabled']));
    }
}
