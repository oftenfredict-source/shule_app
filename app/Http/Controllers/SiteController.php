<?php

namespace App\Http\Controllers;

use App\Services\OnlineApplicationApplyTokenService;
use App\Services\SmsService;
use App\Models\OtpPhoneBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    /**
     * Absolute public-site URL (always https://shulexpert.com) for canonicals / sitemap alignment.
     */
    private function publicUrl(string $path = '/'): string
    {
        return app(OnlineApplicationApplyTokenService::class)->publicAbsoluteUrl($path);
    }

    /**
     * Public marketing modules mapped from real ShuleXpert Admin / Teacher features
     * (see resources/views/includes/Admin_nav.blade.php).
     */
    public static function modules(): array
    {
        $modules = [
            'student-management' => [
                'slug' => 'student-management',
                'title' => 'Student Management',
                'short' => 'Register, enroll, move, promote, and track students by academic year.',
                'icon' => 'fa-graduation-cap',
                'image' => 'images/student.png',
                'hero' => 'images/class.png',
                'body' => 'Manage Students is the school’s student registry for the selected academic year. Admins filter by class, subclass, gender, health, enrollment status and promotion status; quick-register or bulk-upload students; enroll them into classes; run Move Students between streams; and update exit outcomes (promoted, repeated, graduated, transferred, dropout). Each profile keeps enrollment history, results, attendance, payments, library and fees, with PDF/Excel export.',
                'points' => [
                    'Filter by class, enrollment & promotion status for the selected year',
                    'Quick Register, Enroll Student, and Excel bulk upload',
                    'Move Students wizard with preview, execute and undo',
                    'Promotion outcomes: promoted, repeated, graduated, transferred, dropout',
                    'Full student profile: results, attendance, payments, library, fees',
                ],
                'keywords' => 'student management software, school enrollment system, student promotion transfer Tanzania',
            ],
            'online-admission' => [
                'slug' => 'online-admission',
                'title' => 'Online Admission',
                'short' => 'Public apply link, configurable forms, review Pending/Confirmed/Rejected applications.',
                'icon' => 'fa-wpforms',
                'image' => 'images/student.png',
                'hero' => 'images/class.png',
                'body' => 'Schools share a public online application link. Admins configure form questions, sections and application spaces per class for the active year, then review applications (Pending, Confirmed, Rejected), download application PDFs, and show applicants the school fee structure, without paper queues.',
                'points' => [
                    'Copy/share public application link per school',
                    'Configurable form questions, sections & class application spaces',
                    'Status workflow: Pending, Confirmed, Rejected (with PDF)',
                    'Fee structure visibility for applicants',
                ],
                'keywords' => 'online school admission, digital student application, school enrollment portal Tanzania',
            ],
            'student-id-cards' => [
                'slug' => 'student-id-cards',
                'title' => 'Student Identity Cards',
                'short' => 'Class-based ID cards with photo, branding, and single or bulk PDF download.',
                'icon' => 'fa-id-card-o',
                'image' => 'images/student.png',
                'hero' => 'images/student.png',
                'body' => 'From Student Identity Card in the admin menu, open any active class to preview school-branded cards (photo, name, class, expiry rules). Download one card or Download All Cards (PDF) for the whole class using secure encrypted routes.',
                'points' => [
                    'Per-class ID card gallery from active classes',
                    'Photo, name, class & school branding on card',
                    'Download single card or all cards PDF',
                    'Expiry derived from form/class level rules',
                ],
                'keywords' => 'student ID card system, school identity card printer, student badge PDF',
            ],
            'fees-payments' => [
                'slug' => 'fees-payments',
                'title' => 'Fees & Payments',
                'short' => 'Fee structures, control numbers, discounts, agreements, and payment ledgers.',
                'icon' => 'fa-money',
                'image' => 'images/fees.png',
                'hero' => 'images/fees.png',
                'body' => 'Define official fee structures (single or multi-account), installments and deadlines; apply discount conditions and Student Fee Agreements; generate and SMS control numbers; record and verify payments with invoices and filtered PDF reports, including pending, incomplete, paid, overpaid and outstanding balances.',
                'points' => [
                    'Single & multi-account fee structures with installments',
                    'Discount conditions and Student Fee Agreements',
                    'Control number generate/resend + parent SMS & debt reminders',
                    'Payment verification, invoices and status dashboards',
                    'Fee structure and payment PDF reports',
                ],
                'keywords' => 'school fees management, control number fees Tanzania, school payment system',
            ],
            'attendance-biometric' => [
                'slug' => 'attendance-biometric',
                'title' => 'Attendance & Biometric',
                'short' => 'System attendance, ZKTeco fingerprint sync, exam attendance, and SMS alerts.',
                'icon' => 'fa-hand-paper-o',
                'image' => 'images/biometric.jpg',
                'hero' => 'images/biometric.jpg',
                'body' => 'Capture attendance three ways: System Attendance (day/week/month class rolls), Fingerprint Attendance synced from ZKTeco devices (check-in/out, work-time settings, device registration), and Exam Attendance by term/exam/subject. Separate teacher/staff attendance reports, PDF exports, and parent SMS notifications.',
                'points' => [
                    'System attendance by date/week/month with PDF & SMS',
                    'ZKTeco device settings, user import and sync',
                    'Fingerprint check-in/out and student fingerprint PDF',
                    'Exam attendance & missed-subject tracking',
                    'Teacher and staff attendance reports',
                ],
                'keywords' => 'biometric attendance school, ZKTeco school attendance, fingerprint student attendance',
            ],
            'exams-results' => [
                'slug' => 'exams-results',
                'title' => 'Exams & Results',
                'short' => 'Examinations, halls, papers, result entry, term reports, and parent publishing.',
                'icon' => 'fa-file-text-o',
                'image' => 'images/exams.png',
                'hero' => 'images/exam.jpg',
                'body' => 'Manage Examinations covers exams, halls, papers, supervisors and approval. Results show pass/fail, rankings and missing marks; admins assign teachers or enter marks; generate Term Reports (divisions/points for secondary & A-Level); Publish/Unpublish to parents; and send result SMS, alongside Topic Analysis for question-level insight.',
                'points' => [
                    'Exam papers, halls, supervisors & approval workflow',
                    'Result entry, rankings, missing-result reminders',
                    'Term Report with divisions/points and PDF export',
                    'Publish / unpublish exams and term reports to parents',
                    'Result SMS and Add Results assignment tools',
                ],
                'keywords' => 'school exam management, student results system, report card software Tanzania',
            ],
            'topic-analysis' => [
                'slug' => 'topic-analysis',
                'title' => 'Topic Analysis',
                'short' => 'Break down exam papers by question/topic: averages, weak areas, PDF & teacher comments.',
                'icon' => 'fa-line-chart',
                'image' => 'images/results.png',
                'hero' => 'images/results.png',
                'body' => 'Topic Analysis (Results menu) analyses entered exam marks question by question. Filter by academic year (header), term, exam, class, subclass and subject. Only Active enrollments are included, including optional-subject electors. You see overall pass/fail, average and % per question, best and weakest topics, optional-question uptake, stream aggregation, and a student-by-question marks matrix. Export a PDF report and send performance comments to subject teachers.',
                'points' => [
                    'Filters: year, term, exam, class, subclass & subject',
                    'Per-question averages, % scores, best & worst topics',
                    'Pass/fail for Active enrollments (incl. optional subjects)',
                    'Optional question uptake & student×question matrix',
                    'PDF export + comments to subject teachers',
                ],
                'keywords' => 'topic analysis school exams, exam question performance, weakest topics school, ShuleXpert topic analysis',
            ],
            'character-assessment' => [
                'slug' => 'character-assessment',
                'title' => 'Character Assessment',
                'short' => 'Categories, rating choices, assessment timetables, teacher scoring, PDF/Excel reports.',
                'icon' => 'fa-heartbeat',
                'image' => 'images/H.jpg',
                'hero' => 'images/H.jpg',
                'body' => 'Define character categories and rating choices, create assessment timetables by term/class, let teachers score students per timetable, then use Assessment Reporting (assessed vs pending) and export class/student character reports to PDF and Excel.',
                'points' => [
                    'Configurable categories & rating choices',
                    'Character assessment timetables by term/class',
                    'Teacher scoring per timetable',
                    'Admin assessed vs pending reporting',
                    'PDF and Excel character report export',
                ],
                'keywords' => 'student character assessment, behaviour evaluation school, school character report',
            ],
            'timetable-academics' => [
                'slug' => 'timetable-academics',
                'title' => 'Timetable (Class & Exam Sessions)',
                'short' => 'Weekly class sessions (optional subjects in one period) plus exam day timetables.',
                'icon' => 'fa-clock-o',
                'image' => 'images/time_table.png',
                'hero' => 'images/timetable.jpg',
                'body' => 'Build Session Timetables with session types, times, prepo and free periods. Optional subjects may share one period (e.g. Physics + History) with validation so only Optional subjects run in parallel. Exam Timetable covers school-wide exam days, halls, supervisors, shuffle/swap, and supervisor SMS, synced with exam result schedules.',
                'points' => [
                    'Class session timetable with breaks, prepo & free sessions',
                    'Optional subjects sharing the same period (validated)',
                    'Shuffle/swap session slots before save',
                    'Exam timetable: halls, supervisors, auto/manual schedule',
                    'Supervisor SMS when assigned',
                ],
                'keywords' => 'school timetable software, class session timetable, exam timetable optional subjects',
            ],
            'lesson-plans' => [
                'slug' => 'lesson-plans',
                'title' => 'Lesson Plans',
                'short' => 'Digital lesson plan software for teachers, create, manage, send to admin, sign, and download PDF.',
                'icon' => 'fa-file-text-o',
                'image' => 'images/time_table.png',
                'hero' => 'images/timetable.jpg',
                'body' => 'ShuleXpert Lesson Plans is a complete digital lesson plan system for schools. Teachers create lesson plans from class sessions (competence, objectives, teaching/learning activities, resources, reflection and evaluation), reuse previous plans, save drafts, filter and manage plans by subject/class/date, send plans to administrators for review, and download individual or bulk PDF lesson plans. Admins and supervisors open Lesson Plans Management, filter by teacher/class/subject, view full plans, and Sign Lesson Plan digitally. Built for Tanzanian and African schools that need accountable, printable, curriculum-aligned lesson planning, not paper folders.',
                'points' => [
                    'Create lesson plans from timetable sessions (date, subject, class)',
                    'Competence, objectives, activities, resources, reflection & evaluation',
                    'Reuse previous lesson plans and restore unsaved drafts',
                    'Manage, filter and search lesson plans by subject/class/year',
                    'Send lesson plan to admin for supervisor review',
                    'Admin Sign Lesson Plan with digital supervisor signature',
                    'Download single lesson plan PDF or bulk PDF export',
                    'Works with Active academic-year enrollments and teacher portal',
                ],
                'keywords' => 'lesson plan software, lesson plan system, digital lesson plan Tanzania, teacher lesson plan, school lesson plan PDF, lesson plan for teachers, create lesson plan online, lesson plan management system, mpango wa somo, lesson plan template school, supervisor sign lesson plan, best lesson plan software Africa, ShuleXpert lesson plans, EmCa Techonology lesson plan',
                'seo_priority' => 'highest',
                'seo_sections' => [
                    [
                        'heading' => 'Why schools choose ShuleXpert Lesson Plans',
                        'text' => 'Paper lesson plans get lost, unsigned, and hard to audit. ShuleXpert Lesson Plans keeps every plan online: tied to the real class session, ready for PDF download, and ready for admin signature. Teachers spend less time rewriting the same plan and more time teaching. School leaders see who sent plans, who signed them, and what was taught, with a clear digital trail.',
                    ],
                    [
                        'heading' => 'Lesson plan workflow teachers actually use',
                        'text' => 'From My Sessions or Lesson Plans, a teacher opens a session date, fills competence and objectives, teaching and learning activities, teaching aids, and reflection. They can load a previous lesson plan for the same subject, edit, save, then send to admin. Admins filter Lesson Plans by subject and class, open the full plan, and sign as supervisor. Teachers download polished PDF lesson plans for files, inspections, and school quality assurance.',
                    ],
                    [
                        'heading' => 'Lesson plan software for Tanzania and beyond',
                        'text' => 'Search for lesson plan software, digital lesson plan system, teacher lesson plan PDF, mpango wa somo, or school lesson plan management, ShuleXpert delivers a production module already used inside live schools: create, manage, sign, and export. Pair it with Scheme of Work, Timetable, Logbook, and Topic Analysis for a full academic planning stack.',
                    ],
                ],
            ],
            'scheme-of-work' => [
                'slug' => 'scheme-of-work',
                'title' => 'Scheme of Work',
                'short' => 'Digital scheme of work for teachers, create, manage, reuse, track progress, export PDF/Excel.',
                'icon' => 'fa-book',
                'image' => 'images/time_table.png',
                'hero' => 'images/timetable.jpg',
                'body' => 'ShuleXpert Scheme of Work is a dedicated module for termly and yearly teaching schemes. Teachers create a scheme of work per subject/class, fill month-ordered topic rows, manage and update schemes, reuse an existing scheme for a new year, add remarks, and export PDF or Excel. Admins open Scheme of Work Progress to see completion percentage per teacher and subject, then open detailed schemes for supervision. Separate from Lesson Plans, scheme sets the long-range plan; lesson plans deliver each period.',
                'points' => [
                    'Create scheme of work per subject and class',
                    'Month-ordered scheme table with topics and coverage rows',
                    'Use existing scheme / copy into a new academic year',
                    'Manage, update, remark and delete schemes',
                    'Admin Scheme of Work Progress (% complete) dashboard',
                    'Export scheme of work to PDF and Excel',
                    'Teacher portal + admin supervision views',
                ],
                'keywords' => 'scheme of work software, scheme of work system, digital scheme of work Tanzania, teacher scheme of work, school scheme of work PDF, create scheme of work online, scheme of work template, scheme of work progress, mpango wa kazi wa mwaka, best scheme of work software Africa, ShuleXpert scheme of work, EmCa Techonology scheme of work',
                'seo_priority' => 'high',
                'seo_sections' => [
                    [
                        'heading' => 'Scheme of Work: long-range teaching plan',
                        'text' => 'A scheme of work maps what will be taught across months for each subject. ShuleXpert digitises that table: teachers create and maintain schemes, reuse last year’s scheme, and export for files and inspections. Admins monitor progress so incomplete schemes are visible early, not at the end of term.',
                    ],
                    [
                        'heading' => 'Separate from Lesson Plans (on purpose)',
                        'text' => 'Scheme of Work is the roadmap. Lesson Plans are the daily/period delivery. Keeping them as separate ShuleXpert modules matches how schools and inspectors work: approve the scheme, then check lesson plans against it. Link both with Timetable sessions and Logbook records for full academic accountability.',
                    ],
                    [
                        'heading' => 'Scheme of work software schools search for',
                        'text' => 'Whether you search scheme of work software, digital scheme of work Tanzania, teacher scheme of work PDF, or mpango wa kazi, ShuleXpert Scheme of Work is a live school module with create, manage, reuse, progress tracking, and export, not a static template download.',
                    ],
                ],
            ],
            'school-calendar' => [
                'slug' => 'school-calendar',
                'title' => 'School Calendar & Academic Years',
                'short' => '12-month calendar with holidays/events, plus academic year & term setup.',
                'icon' => 'fa-calendar',
                'image' => 'images/time_table.png',
                'hero' => 'images/timetable.jpg',
                'body' => 'Academic Years Management registers years used across enrollment, fees and admissions. The School Calendar shows a 12-month grid for the selected year, add holidays (including bulk) and events, view month stats, and download a calendar PDF so the whole school shares one official plan.',
                'points' => [
                    'Register and set current/active academic years',
                    'Terms linked to each academic year',
                    'Holidays and school events on a 12-month grid',
                    'Bulk holiday entry and calendar PDF download',
                ],
                'keywords' => 'school academic calendar, academic year management, school holiday calendar',
            ],
            'parent-portal' => [
                'slug' => 'parent-portal',
                'title' => 'Parent Portal',
                'short' => 'Parent login for results, attendance, fees, subjects, and permission requests.',
                'icon' => 'fa-users',
                'image' => 'images/parents.png',
                'hero' => 'images/parents.png',
                'body' => 'Parents sign in to a dedicated portal (and mobile app) linked to their children: dashboard, Results and Term Report downloads, system and fingerprint attendance, subjects (including elective election), Payments and Fees Summary with control-number requests, and Permission request submission.',
                'points' => [
                    'Linked students dashboard',
                    'Results & term report downloads',
                    'System and fingerprint attendance views',
                    'Fees summary, payments & control number requests',
                    'Parent permission requests & elective subjects',
                ],
                'keywords' => 'parent portal school, school parent app, student results for parents',
            ],
            'library' => [
                'slug' => 'library',
                'title' => 'Library Management',
                'short' => 'Catalog, borrow/return for students/teachers/staff, losses, damages, PDF reports.',
                'icon' => 'fa-book',
                'image' => 'images/school.png',
                'hero' => 'images/school.png',
                'body' => 'Catalog books (ISBN check, class/subject links), borrow and return with borrower type and due dates, record losses and damages with payment/resolution and parent notify, filter availability, and export Library Inventory & Circulation Report PDF.',
                'points' => [
                    'Book catalog with ISBN and inventory stats',
                    'Borrow/return for students, teachers and staff',
                    'Loss & damage records with resolution',
                    'Availability filters and bulk book updates',
                    'Inventory & circulation PDF report',
                ],
                'keywords' => 'school library management system, book borrowing software, library catalog school',
            ],
            'store-inventory' => [
                'slug' => 'store-inventory',
                'title' => 'Store & Inventory',
                'short' => 'Categories, stock, packages, issue/return, and staff requisition approvals.',
                'icon' => 'fa-shopping-cart',
                'image' => 'images/roles.png',
                'hero' => 'images/roles.png',
                'body' => 'Manage Store tracks categories, units, items, packages and stock with a dashboard for in-stock, issued, overdue returns and low stock. Staff submit Requisition Requests for approve/reject; storekeepers issue and return stock and resolve damaged/lost incidents.',
                'points' => [
                    'Categories, items, packages & stock entries',
                    'Dashboard: in-stock, issued, low stock, overdue',
                    'Staff requisitions with approve/reject',
                    'Issue/return workflow and damage/loss handling',
                ],
                'keywords' => 'school inventory system, store requisition software, school stock management',
            ],
            'hostel' => [
                'slug' => 'hostel',
                'title' => 'Hostel & Accommodation',
                'short' => 'Blocks, rooms and beds with gender-aware student assignment and moves.',
                'icon' => 'fa-bed',
                'image' => 'images/admin.jpg',
                'hero' => 'images/admin.jpg',
                'body' => 'Register hostel blocks, rooms and beds, then search students to assign, release or move beds. Gender rules on blocks are enforced; occupancy is visible by block/room, with bulk release when needed.',
                'points' => [
                    'Blocks, rooms & beds registration',
                    'Assign, release or move students between beds',
                    'Gender validation on block assignment',
                    'Occupancy views and bulk release from a block',
                ],
                'keywords' => 'school hostel management, boarding school beds, student accommodation software',
            ],
            'sms-communication' => [
                'slug' => 'sms-communication',
                'title' => 'SMS Information & Alerts',
                'short' => 'Targeted SMS to parents, teachers and staff with delivery reports and balance.',
                'icon' => 'fa-bell',
                'image' => 'images/PP.jpg',
                'hero' => 'images/PP.jpg',
                'body' => 'SMS Information sends to all parents, class parents, teachers, staff or searched students (≈160-char counter). Check SMS balance, browse logs and Delivery Reports. Fees (control numbers, debt reminders) and attendance modules also trigger SMS from the same stack.',
                'points' => [
                    'Broadcast to parents, class, teachers or staff',
                    'Character counter and modal send flow',
                    'SMS balance, logs and delivery reports',
                    'Integrated fee & attendance SMS triggers',
                ],
                'keywords' => 'school SMS system, parent SMS alerts Tanzania, school announcement SMS',
            ],
            'discipline-blackbook' => [
                'slug' => 'discipline-blackbook',
                'title' => 'Discipline (Black Book)',
                'short' => 'Report students, categories, actors, resolve workflow, PDF and parent SMS.',
                'icon' => 'fa-gavel',
                'image' => 'images/H.jpg',
                'hero' => 'images/H.jpg',
                'body' => 'Black Book records discipline incidents against enrolled students. Configure problem/punishment categories and Black Book Actors; track pending → viewed → resolved → end monitoring; export analytics PDF; optionally SMS admin on new reports and parents when a student is reported.',
                'points' => [
                    'Report Student incident form',
                    'Problem & punishment categories',
                    'Actors and resolve / end-monitoring workflow',
                    'Dashboard stats and PDF export',
                    'Optional SMS to admin and parents',
                ],
                'keywords' => 'school discipline system, black book school, student incident reporting',
            ],
            'duties-book' => [
                'slug' => 'duties-book',
                'title' => 'Duties Book',
                'short' => 'Monthly teacher-on-duty roster, daily duty reports, approve and PDF.',
                'icon' => 'fa-calendar-check-o',
                'image' => 'images/teacher.png',
                'hero' => 'images/teacher.png',
                'body' => 'Admins assign Teacher on Duties by week/month. Assigned teachers submit Daily Duty Reports (attendance counts, notes). Admins review pending reports (nav badge), approve them, print the duties roster, and export daily report PDFs.',
                'points' => [
                    'Teacher-on-duty week/month roster',
                    'Daily duty report submission by teachers',
                    'Pending report badge and approve flow',
                    'Printable roster and daily report PDF',
                ],
                'keywords' => 'teacher duty book, school duties roster, teacher on duty report',
            ],
            'permission-requests' => [
                'slug' => 'permission-requests',
                'title' => 'Permission Requests',
                'short' => 'Student, teacher and staff permissions with chain approvals and attachments.',
                'icon' => 'fa-check-square-o',
                'image' => 'images/teacher.png',
                'hero' => 'images/teacher.png',
                'body' => 'HR Permission lists student vs teacher/staff requests. Permission Approvals queues requests through configurable approval chains, approve/reject with comments, view attachments and progress. Parents and staff/teachers submit from their portals; approvers see a pending count badge.',
                'points' => [
                    'Student and teacher/staff permission tabs',
                    'Multi-level permission_approval_chains',
                    'Approve/reject with comments & attachments',
                    'Pending approvals dashboard with badge',
                ],
                'keywords' => 'school permission request system, teacher leave permission, student permission slip digital',
            ],
            'school-visitors' => [
                'slug' => 'school-visitors',
                'title' => 'School Visitors',
                'short' => 'Log daily visitors from admin or watchman gate, with SMS notify option.',
                'icon' => 'fa-id-badge',
                'image' => 'images/admin.jpg',
                'hero' => 'images/admin.jpg',
                'body' => 'School Visitors Management records visitors for today (purpose, contacts), supports edit and bulk actions, and can SMS notify. Works with Watchman accounts so gate staff log visitors from their own dashboard.',
                'points' => [
                    'Record multiple visitors for today',
                    'Purpose & contact capture with edit/bulk actions',
                    'Admin and watchman visitor views',
                    'Optional visitor SMS notification',
                ],
                'keywords' => 'school visitor management, visitor log book digital, school gate visitors',
            ],
            'printing-unit' => [
                'slug' => 'printing-unit',
                'title' => 'Printing Unit',
                'short' => 'Queue of approved exam papers by year, term, class and examination.',
                'icon' => 'fa-print',
                'image' => 'images/roles.png',
                'hero' => 'images/roles.png',
                'body' => 'Printing Unit lists only approved exam papers filtered by academic year, term, class, subclass and examination. Expandable cards show paper details per subject/class so the print team knows the workload; staff can be notified when papers are ready.',
                'points' => [
                    'Filter by year, term, class, subclass & exam',
                    'Approved exam papers only',
                    'Expandable subject/class paper cards',
                    'Notify printing-unit staff when ready',
                ],
                'keywords' => 'school printing unit software, exam paper printing workflow, approved papers queue',
            ],
            'accountant' => [
                'slug' => 'accountant',
                'title' => 'Accountant Module',
                'short' => 'School income, expenses, categories, budgets and financial PDF reports.',
                'icon' => 'fa-calculator',
                'image' => 'images/accountant.png',
                'hero' => 'images/accountant.png',
                'body' => 'Separate from student fees: record School Income and School Expenses by category (expenses support pending approval), maintain categories, plan School Budget (allocated/spent/remaining), and open Financial Reports with charts and PDF exports for leadership.',
                'points' => [
                    'Income & expense ledgers by category',
                    'Expense pending-approval workflow',
                    'Budget allocated / spent / remaining',
                    'Financial reports with charts and PDFs',
                ],
                'keywords' => 'school accounting software, school budget expenses income, school financial reports',
            ],
            'school-budget' => [
                'slug' => 'school-budget',
                'title' => 'School Budget',
                'short' => 'Create yearly budgets by category and monitor allocated vs spent.',
                'icon' => 'fa-pie-chart',
                'image' => 'images/accountant.png',
                'hero' => 'images/accountant.png',
                'body' => 'Under Accountant Module, create school budgets for the academic year by category, see allocated, spent and remaining amounts, and download budget PDFs so spending stays aligned with the plan.',
                'points' => [
                    'Budget planning by year and category',
                    'Allocated, spent and remaining overview',
                    'Linked to real income & expenses',
                    'Budget PDF for management',
                ],
                'keywords' => 'school budget planning software, school financial budget system',
            ],
            'school-expenses' => [
                'slug' => 'school-expenses',
                'title' => 'School Expenses',
                'short' => 'Record categorized expenses with pending approval and searchable history.',
                'icon' => 'fa-money',
                'image' => 'images/accountant.png',
                'hero' => 'images/accountant.png',
                'body' => 'Capture school expenses with category tags and a pending-approval badge for review. Keep searchable history and an audit trail for accountants and school leaders, distinct from student fee payments.',
                'points' => [
                    'Expense recording with categories',
                    'Pending approval badge and workflow',
                    'Expense history & search',
                    'Audit-ready school spending records',
                ],
                'keywords' => 'school expense management, school spending tracker, expense approval school',
            ],
            'financial-reports' => [
                'slug' => 'financial-reports',
                'title' => 'Financial Reports',
                'short' => 'Charts and PDFs for income, expenses, and budget vs actual.',
                'icon' => 'fa-line-chart',
                'image' => 'images/accountant.png',
                'hero' => 'images/accountant.png',
                'body' => 'Financial Reports summarize school income and expenses, show budget vs actual, and export expenses/income/report PDFs for board meetings, audits and management decisions.',
                'points' => [
                    'Income & expense summaries with charts',
                    'Budget vs actual views',
                    'Export expenses, income and report PDFs',
                    'Period-based filtering',
                ],
                'keywords' => 'school financial reports, school income expense report, school audit report PDF',
            ],
            'sgpm' => [
                'slug' => 'sgpm',
                'title' => 'SGPM & Departments',
                'short' => 'Departments with HOD/members, goals cascade, and task review workflows.',
                'icon' => 'fa-sitemap',
                'image' => 'images/results.png',
                'hero' => 'images/results.png',
                'body' => 'Departments Management sets HODs, members and linked subjects (with HOD SMS). Goals and departmental tasks cascade: HODs assign work, members submit steps for review, and admins approve operational tasks, keeping strategic performance visible across the school.',
                'points' => [
                    'Departments with HOD, members & linked subjects',
                    'Goal and task cascades across departments',
                    'Member subtasks with review submission',
                    'Admin task approve/reject with notifications',
                ],
                'keywords' => 'school performance management, SGPM school, school department goals',
            ],
            'goal-management' => [
                'slug' => 'goal-management',
                'title' => 'Goal Management',
                'short' => 'Create goals with target % and deadline, list progress, and goal reports.',
                'icon' => 'fa-bullseye',
                'image' => 'images/results.png',
                'hero' => 'images/results.png',
                'body' => 'Goal Management lets leadership Create Goal (target percentage and deadline), browse Goal List progress, and open Goal Reports so institutional targets stay tracked and reported.',
                'points' => [
                    'Create school goals with target % and deadline',
                    'Goal List with progress tracking',
                    'Goal Reports for leadership',
                    'Alignment with department task workflows',
                ],
                'keywords' => 'school goal management, institutional goals tracking, school KPI goals',
            ],
            'task-management' => [
                'slug' => 'task-management',
                'title' => 'Task Management',
                'short' => 'Admin review of teacher/department tasks, approve, reject, notify.',
                'icon' => 'fa-tasks',
                'image' => 'images/roles.png',
                'hero' => 'images/roles.png',
                'body' => 'Under Services → Tasks, admins review teacher- and department-submitted tasks, approve or reject with notifications, and keep operational follow-up from falling through the cracks alongside goal and department workflows.',
                'points' => [
                    'Admin task review queue',
                    'Approve / reject with notifications',
                    'Tied to department and goal work',
                    'Operational accountability trail',
                ],
                'keywords' => 'school task management, school staff tasks, school work assignment system',
            ],
            'teacher-portal' => [
                'slug' => 'teacher-portal',
                'title' => 'Teacher Portal',
                'short' => 'Results entry, Scheme of Work, Lesson Plans, logbook, duties, permissions.',
                'icon' => 'fa-pencil-square-o',
                'image' => 'images/teacher.png',
                'hero' => 'images/teacher.png',
                'body' => 'Teachers work from their portal: enter and manage subject results, view Topic Analysis where permitted, prepare Scheme of Work and Lesson Plans as separate modules, fill teaching logbooks, submit permissions, and complete Daily Duty Reports when on duty, all under role-based menu permissions.',
                'points' => [
                    'Subject result entry and related tools',
                    'Dedicated Scheme of Work module',
                    'Dedicated Lesson Plans module (create, send, PDF)',
                    'Teaching logbook, permissions and teacher-on-duty reports',
                ],
                'keywords' => 'teacher portal school software, teacher results entry, teacher lesson plan system, teacher scheme of work',
            ],
            'watchman-security' => [
                'slug' => 'watchman-security',
                'title' => 'Watchman & Gate Security',
                'short' => 'Watchman accounts with dedicated visitor logging at the school gate.',
                'icon' => 'fa-shield',
                'image' => 'images/admin.jpg',
                'hero' => 'images/admin.jpg',
                'body' => 'Watchman Management registers gate-staff accounts. Watchmen use their dashboard to log School Visitors (same visitor records admins see), strengthening campus security with digital check-in history instead of paper gate books.',
                'points' => [
                    'Register, edit and delete watchman users',
                    'Watchman dashboard for gate visitor intake',
                    'Shared visitor history with admin',
                    'Security-focused daily logging',
                ],
                'keywords' => 'school watchman system, school gate security software, school visitor gate log',
            ],
            'sponsors-management' => [
                'slug' => 'sponsors-management',
                'title' => 'Sponsors Management',
                'short' => 'Sponsor profiles linked into student payments and fee support.',
                'icon' => 'fa-handshake-o',
                'image' => 'images/fees.png',
                'hero' => 'images/fees.png',
                'body' => 'Manage Sponsors registers organizations or individuals (contact person, phone, email, status). Sponsors appear on student payment views so fee support is tracked beside billing, with Active/Inactive control and normalized phone numbers.',
                'points' => [
                    'Sponsor name, contact, phone, email & description',
                    'Active/Inactive status',
                    'Visible on student payment workflows',
                    'DataTable list with edit/delete',
                ],
                'keywords' => 'school sponsor management, student sponsorship system, school fees sponsor tracking',
            ],
            'feedback-incidents' => [
                'slug' => 'feedback-incidents',
                'title' => 'Suggestions & Incidents',
                'short' => 'Teacher and staff suggestion/incident inbox with unread badges and approve/reject.',
                'icon' => 'fa-lightbulb-o',
                'image' => 'images/H.jpg',
                'hero' => 'images/H.jpg',
                'body' => 'Admins open separate Suggestions and Incidents lists for teachers and for staff, with unread count badges. Mark read, approve or reject staff feedback, and use Suggestions/Incidents Report sections for an actionable trail.',
                'points' => [
                    'Teacher suggestions & incidents lists',
                    'Staff suggestions & incidents lists',
                    'Unread badges on admin navigation',
                    'Mark read, approve/reject, and report sections',
                ],
                'keywords' => 'school incident reporting, teacher suggestions system, staff feedback school',
            ],
        ];

        foreach ($modules as $slug => &$module) {
            $titleKey = 'modules.'.$slug.'.title';
            $shortKey = 'modules.'.$slug.'.short';
            if (\Illuminate\Support\Facades\Lang::has($titleKey)) {
                $module['title'] = __($titleKey);
            }
            if (\Illuminate\Support\Facades\Lang::has($shortKey)) {
                $module['short'] = __($shortKey);
            }
        }
        unset($module);

        return $modules;
    }

    /**
     * EmCa Techonology team behind ShuleXpert.
     */
    public static function teamMembers(): array
    {
        return [
            [
                'slug' => 'senior-developer',
                'name' => 'Developer',
                'role' => 'Senior Developer',
                'tagline' => 'ShuleXpert web platform, mobile applications & school software delivery.',
                'bio' => 'Builds and maintains ShuleXpert on web and mobile, from school portals and APIs to parent and teacher apps schools use every day.',
                'more_bio' => "Full-stack Web & App Developer (Backend + Frontend).\n\nDesigns and ships production systems for schools and organisations across Tanzania, secure APIs, responsive dashboards, and reliable mobile experiences.\n\nFocus: secure APIs, responsive dashboards, clean UX, and reliable deployments that schools trust every day.",
                'image' => 'images/H.jpg',
                'featured' => false,
                'phone' => '0614863345',
                'email' => null,
                'location' => 'Moshi, Kilimanjaro, Tanzania',
                'skills' => [
                    'Laravel & PHP',
                    'Flutter / Mobile',
                    'MySQL',
                ],
                'projects' => [
                    'USHIRIKA PLUSS, partnership-union management backend',
                    'ShuleXpert, full-stack school platform (25+ schools)',
                    'Hostel Booking System, web + mobile with 20+ payment options',
                    'OFICELink, office operations backend (20+ offices)',
                ],
                'social' => [
                    ['icon' => 'fa-phone', 'label' => 'Call', 'url' => 'tel:+255614863345'],
                    ['icon' => 'fa-github', 'label' => 'GitHub', 'url' => 'https://github.com/'],
                    ['icon' => 'fa-linkedin', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/'],
                ],
            ],
            [
                'slug' => 'system-analyst',
                'name' => 'System Analyst',
                'role' => 'System Analyst',
                'tagline' => 'Requirements, workflows & school operations mapping.',
                'bio' => 'Translates real school processes into ShuleXpert modules, fees, attendance, exams, and reporting that match how administrators actually work.',
                'more_bio' => "System Analyst & workflow designer.\n\nKey contributions:\n- Requirement mapping: translate real school operations (fees, attendance, exams, reporting) into clear module workflows.\n- Process definition: define data flows, validation rules, and reporting logic.\n- Integration planning: coordinate with frontend/backend teams to keep modules consistent.\n\nFocus: stable workflows, scalability, and accurate school operations data.",
                'image' => 'images/B.jpg',
                'featured' => false,
                'phone' => null,
                'email' => 'emca@emca.tech',
                'location' => 'Moshi, Kilimanjaro, Tanzania',
                'skills' => [
                    'Requirements',
                    'Workflows',
                    'Data mapping',
                    'QA planning',
                ],
                'projects' => [
                    'School fees & reporting workflow design',
                    'Attendance and exam process mapping',
                    'Module consistency across ShuleXpert',
                ],
                'social' => [
                    ['icon' => 'fa-linkedin', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/'],
                    ['icon' => 'fa-envelope', 'label' => 'Email', 'url' => 'mailto:emca@emca.tech'],
                ],
            ],
            [
                'slug' => 'ui-ux-designer',
                'name' => 'UI/UX Designer',
                'role' => 'UI & UX Designer',
                'tagline' => 'Interfaces parents, teachers & admins love to use.',
                'bio' => 'Shapes the ShuleXpert experience, clear portals, mobile-friendly layouts, and brand-consistent design across the public site and school dashboards.',
                'more_bio' => "UI/UX Designer.\n\nKey contributions:\n- Design parent, teacher and admin experiences that are easy to use on mobile and web.\n- Create wireframes and consistent components across ShuleXpert portals.\n- Improve forms, navigation, and accessibility to reduce user effort.\n\nFocus: usability, responsive design, and brand-consistent UI across the public site and school dashboards.",
                'image' => 'images/L.jpg',
                'featured' => false,
                'phone' => null,
                'email' => 'emca@emca.tech',
                'location' => 'Moshi, Kilimanjaro, Tanzania',
                'skills' => [
                    'UI design',
                    'UX research',
                    'Wireframes',
                    'Mobile-first',
                ],
                'projects' => [
                    'Parent / teacher / admin portal UX',
                    'Public site brand & component system',
                    'Forms and accessibility improvements',
                ],
                'social' => [
                    ['icon' => 'fa-dribbble', 'label' => 'Dribbble', 'url' => 'https://dribbble.com/'],
                    ['icon' => 'fa-behance', 'label' => 'Behance', 'url' => 'https://behance.net/'],
                    ['icon' => 'fa-envelope', 'label' => 'Email', 'url' => 'mailto:emca@emca.tech'],
                ],
            ],
        ];
    }

    public static function teamMemberBySlug(string $slug): ?array
    {
        foreach (self::teamMembers() as $member) {
            if (($member['slug'] ?? '') === $slug) {
                return $member;
            }
        }

        return null;
    }

    public function home()
    {
        $keywords = implode(', ', [
            'Mfumo bora wa shule Tanzania',
            'Better school management system',
            'ShuleXpert',
            'shulesoft',
            'shule',
            'Mifumo ya shule',
            'Mfumo wa shule',
            'School system',
            'school software',
            'Mfumo wa taaluma',
            'Mifumo bora ya shule Tanzania',
            'Mfumo bora wa shule Tanzania',
            'Mifumo bora ya shule Duniani',
            'Mifumo Bora ya Shule Duniani',
            'Mfumo namba moja wa shule Tanzania',
            'Mfumo wa shule Tanzania',
            'school management system Tanzania',
            'school management software Africa',
            'mfumo wa usimamizi wa shule',
            'school ERP Tanzania',
            'mfumo wa mitihani shule',
            'school fees control number Tanzania',
            'biometric attendance school Tanzania',
            'parent portal school Tanzania',
            'lesson plan software Tanzania',
            'scheme of work software',
            'online school application Tanzania',
            'school admin software',
            'EmCa Techonology',
        ]);

        return view('site.home', [
            'modules' => array_values(self::modules()),
            'teamMembers' => self::teamMembers(),
            'seo' => [
                'title' => 'Mfumo Bora wa Shule Tanzania | ShuleXpert School Management System',
                'description' => 'ShuleXpert ni mfumo bora wa shule Tanzania na duniani, better school management system & school software: fees, biometric attendance, exams, lesson plans, scheme of work, parent portal. Mfumo wa taaluma na usimamizi wa shule. Powered by EmCa Techonology.',
                'keywords' => $keywords,
                'canonical' => $this->publicUrl('/'),
                'type' => 'website',
                'image' => $this->publicUrl('/images/shuleXpert.jpg'),
                'image_alt' => 'ShuleXpert, mfumo bora wa shule Tanzania na school management system',
                'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->publicUrl('/')],
                ],
            ],
        ]);
    }

    public function team()
    {
        $members = self::teamMembers();

        return view('site.team', [
            'teamMembers' => $members,
            'seo' => [
                'title' => 'Our Team | ShuleXpert | EmCa Techonology',
                'description' => 'Meet the EmCa Techonology team behind ShuleXpert, Senior Developer, System Analyst, and UI/UX Designer building school management for the world.',
                'keywords' => 'ShuleXpert team, Senior Developer, EmCa Techonology developers, school software engineers Tanzania, UI UX designer ShuleXpert',
                'canonical' => $this->publicUrl('/team'),
                'type' => 'website',
            ],
        ]);
    }

    public function teamShow(string $slug)
    {
        $member = self::teamMemberBySlug($slug);
        if (!$member) {
            abort(404);
        }

        $others = array_values(array_filter(
            self::teamMembers(),
            fn ($m) => ($m['slug'] ?? '') !== $slug
        ));

        $name = $member['name'];
        $role = $member['role'];
        $canonical = $this->publicUrl('/team/' . $slug);
        $image = $this->publicUrl('/' . ltrim($member['image'], '/'));
        $desc = \Illuminate\Support\Str::limit(
            trim(preg_replace('/\s+/', ' ', $member['more_bio'] ?? $member['bio'] ?? $member['tagline'] ?? '')),
            160,
            ''
        );

        return view('site.team-member', [
            'member' => $member,
            'otherMembers' => $others,
            'seo' => [
                'title' => $name . ' | ' . $role . ' | ShuleXpert · EmCa Techonology',
                'description' => $desc,
                'keywords' => implode(', ', array_filter([
                    $name,
                    $role,
                    'ShuleXpert developer',
                    'EmCa Techonology',
                    'school management Tanzania',
                    'Moshi Kilimanjaro',
                    $member['phone'] ?? null,
                    $member['email'] ?? null,
                ])),
                'canonical' => $canonical,
                'type' => 'profile',
                'image' => $image,
                'favicon' => $image,
                'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            ],
        ]);
    }

    public function about()
    {
        return view('site.about', [
            'seo' => [
                'title' => 'About ShuleXpert | EmCa Techonology',
                'description' => 'Learn about ShuleXpert, the school management platform built by EmCa Techonology for schools worldwide, from preschool to advanced level.',
                'keywords' => 'About ShuleXpert, EmCa Techonology, school software worldwide',
                'canonical' => $this->publicUrl('/about'),
                'type' => 'website',
            ],
        ]);
    }

    public function modulesIndex()
    {
        $modules = array_values(self::modules());
        $count = count($modules);
        $highlights = collect($modules)->take(8)->pluck('title')->implode(', ');

        return view('site.modules', [
            'modules' => $modules,
            'seo' => [
                'title' => 'ShuleXpert Modules (' . $count . ') | School Management Features',
                'description' => 'Browse ' . $count . ' ShuleXpert modules led by Lesson Plans and Scheme of Work, plus ' . $highlights . ', and more. Live school capabilities by EmCa Techonology.',
                'keywords' => 'ShuleXpert modules, lesson plan software, scheme of work software, school management system modules, school fees control number, biometric attendance, exam results, topic analysis, timetable, duty book, library, inventory, character assessment, student ID cards, printing unit, permission requests, school visitors, SMS alerts, budget expenses financial reports, EmCa Techonology',
                'canonical' => $this->publicUrl('/modules'),
                'type' => 'website',
                'breadcrumb_name' => 'Modules',
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->publicUrl('/')],
                    ['name' => 'Modules', 'url' => $this->publicUrl('/modules')],
                ],
                'image' => $this->publicUrl('/images/shuleXpert.jpg'),
                'image_alt' => 'ShuleXpert school management modules',
                'modified_time' => now()->toAtomString(),
            ],
        ]);
    }

    public function moduleShow(string $slug)
    {
        $legacyRedirects = [
            'subject-analysis' => 'topic-analysis',
            'scheme-lesson-planning' => 'lesson-plans',
        ];
        if (isset($legacyRedirects[$slug])) {
            return redirect()->route('site.module', ['slug' => $legacyRedirects[$slug]], 301);
        }

        $all = self::modules();
        if (!isset($all[$slug])) {
            abort(404);
        }

        $module = $all[$slug];
        $keywordBase = $module['keywords'] ?? ($module['title'] . ', ShuleXpert, school management');
        $canonical = $this->publicUrl('/modules/' . $slug);
        $imagePath = $module['hero'] ?? $module['image'] ?? 'images/shuleXpert.jpg';
        $ogImage = str_starts_with($imagePath, 'http') ? $imagePath : $this->publicUrl('/' . ltrim($imagePath, '/'));

        // Lesson Plans: strongest SEO page on the site. Scheme of Work: very strong.
        if (($module['seo_priority'] ?? '') === 'highest') {
            $title = 'Lesson Plan Software for Schools | Digital Lesson Plans, PDF & Admin Sign-Off | ShuleXpert';
            $description = 'Best digital lesson plan software for schools in Tanzania & Africa. Teachers create lesson plans from sessions, reuse plans, send to admin, get supervisor signature, and download PDF. ShuleXpert Lesson Plans by EmCa Techonology, mpango wa somo online.';
            $keywords = $keywordBase . ', lesson plan software Tanzania, digital lesson plan system, teacher lesson plan PDF download, school lesson plan management, create lesson plan online free school, lesson plan template competence based, supervisor signed lesson plan, mpango wa somo shule, best lesson plan app for teachers Africa, ShuleXpert, EmCa Techonology';
        } elseif (($module['seo_priority'] ?? '') === 'high') {
            $title = 'Scheme of Work Software for Schools | Digital Schemes, Progress & PDF/Excel | ShuleXpert';
            $description = 'Digital scheme of work software for teachers and admins. Create schemes by subject, reuse yearly schemes, track Scheme of Work Progress, export PDF & Excel. Separate from Lesson Plans. ShuleXpert by EmCa Techonology, mpango wa kazi wa mwaka.';
            $keywords = $keywordBase . ', scheme of work software Tanzania, digital scheme of work system, teacher scheme of work PDF, scheme of work progress dashboard, create scheme of work online, scheme of work template school, mpango wa kazi wa mwaka, ShuleXpert, EmCa Techonology';
        } else {
            $title = $module['title'] . ' Module | ShuleXpert School Management System';
            $description = \Illuminate\Support\Str::limit(
                trim($module['short'] . ' ' . $module['body']),
                158,
                ''
            );
            $keywords = $keywordBase . ', ShuleXpert module, EmCa Techonology, school management software Tanzania';
        }

        return view('site.module-detail', [
            'module' => $module,
            'modules' => array_values($all),
            'seo' => [
                'title' => $title,
                'description' => \Illuminate\Support\Str::limit($description, 165, ''),
                'keywords' => $keywords,
                'canonical' => $canonical,
                'type' => 'article',
                'image' => $ogImage,
                'image_alt' => $module['title'] . ', ShuleXpert module',
                'modified_time' => now()->toAtomString(),
                'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
                'breadcrumb_name' => $module['title'],
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->publicUrl('/')],
                    ['name' => 'Modules', 'url' => $this->publicUrl('/modules')],
                    ['name' => $module['title'], 'url' => $canonical],
                ],
            ],
        ]);
    }

    public function pricing()
    {
        return view('site.pricing', [
            'seo' => [
                'title' => 'ShuleXpert Pricing | TZS 1,000,000 Installation + TZS 500/Student/Month',
                'description' => 'ShuleXpert pricing by EmCa Techonology: one-time installation fee of TZS 1,000,000 (training, experts, cloud storage, setup) and monthly TZS 500 per student for SMS, support and platform services.',
                'keywords' => 'ShuleXpert pricing, school management cost Tanzania, installation fee 1000000, 500 TZS per student, EmCa Techonology',
                'canonical' => $this->publicUrl('/pricing'),
                'type' => 'website',
                'breadcrumb_name' => 'Pricing',
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => $this->publicUrl('/')],
                    ['name' => 'Pricing', 'url' => $this->publicUrl('/pricing')],
                ],
            ],
        ]);
    }

    public function services()
    {
        return view('site.services', [
            'seo' => [
                'title' => 'ShuleXpert Services | Implementation, Training & Support',
                'description' => 'ShuleXpert services by EmCa Techonology: school digitization, training, biometric support, SMS communication, hosting and continuous user support.',
                'keywords' => 'ShuleXpert services, school software training worldwide, biometric attendance support, EmCa Techonology',
                'canonical' => $this->publicUrl('/services'),
                'type' => 'website',
            ],
        ]);
    }

    public function faq()
    {
        return view('site.faq', [
            'seo' => [
                'title' => 'ShuleXpert FAQ | Pricing, Modules & Support',
                'description' => 'Frequently asked questions about ShuleXpert installation fee (TZS 1,000,000), monthly TZS 500 per student, control numbers, biometric attendance and EmCa Techonology support.',
                'keywords' => 'ShuleXpert FAQ, school software pricing worldwide, control number fees, EmCa Techonology support',
                'canonical' => $this->publicUrl('/faq'),
                'type' => 'website',
            ],
        ]);
    }

    public function download()
    {
        return view('site.download', [
            'seo' => [
                'title' => 'Download ShuleXpert App & Online School Application | Tanzania',
                'description' => 'Download the ShuleXpert Android APK for teachers and parents, or apply to participating schools online. Real-time alerts, admissions, fees and school communication, powered by EmCa Techonology, Moshi Tanzania.',
                'keywords' => 'download ShuleXpert APK, ShuleXpert Android app, school management app Tanzania, online school application Tanzania, apply to school online, ShuleXpert mobile app, EmCa Techonology, school admission online',
                'canonical' => $this->publicUrl('/download'),
                'type' => 'website',
                'image' => $this->publicUrl('/images/shuleXpert.jpg'),
                'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            ],
        ]);
    }

    public function contact()
    {
        return view('site.contact', [
            'seo' => [
                'title' => 'Contact ShuleXpert | EmCa Techonology',
                'description' => 'Contact ShuleXpert / EmCa Techonology in Moshi, Kilimanjaro. Call '.config('shulexpert.phone_display').' or email emca@emca.tech.',
                'keywords' => 'Contact ShuleXpert, EmCa Techonology Moshi, school software support',
                'canonical' => $this->publicUrl('/contact'),
                'type' => 'website',
            ],
        ]);
    }

    /**
     * Public school self-registration (landing), OTP required; saved as pending (no login credentials).
     */
    public function registerSchool()
    {
        return view('site.register-school', [
            'seo' => [
                'title' => 'Register Your School | ShuleXpert',
                'description' => 'Register your school on ShuleXpert. Fill the form, submit, then verify your phone with OTP. Our team verifies before account setup.',
                'keywords' => 'Register school Tanzania, sajili shule, ShuleXpert onboarding',
                'canonical' => $this->publicUrl('/register-school'),
                'type' => 'website',
            ],
        ]);
    }

    public function registerSchoolSendOtp(Request $request)
    {
        $ipKey = 'school-reg-otp-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return response()->json([
                'success' => false,
                'message' => "Too many OTP requests. Try again in {$seconds} seconds.",
            ], 429);
        }

        $phone = $this->normalizeContactPhone($request->input('phone'));
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid Tanzania mobile number starting with 255 (e.g. 2557XXXXXXXX).',
            ], 422);
        }

        $phoneTaken = $this->isSchoolRegistrationFieldTaken('phone', $phone);
        if ($phoneTaken) {
            return response()->json([
                'success' => false,
                'message' => $phoneTaken,
                'available' => false,
            ], 422);
        }

        $otpGate = $this->assertSchoolRegOtpPhoneAllowed($phone);
        if ($otpGate !== null) {
            return $otpGate;
        }

        $phoneKey = 'school-reg-otp-phone:' . $phone;
        if (RateLimiter::tooManyAttempts($phoneKey, 3)) {
            $seconds = RateLimiter::availableIn($phoneKey);
            return response()->json([
                'success' => false,
                'message' => "OTP already sent recently. Wait {$seconds} seconds before requesting again.",
            ], 429);
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($phoneKey, 120);

        $otp = (string) random_int(100000, 999999);
        $token = (string) Str::uuid();

        session([
            'school_reg_otp' => [
                'token' => $token,
                'code_hash' => Hash::make($otp),
                'expires_at' => time() + (10 * 60),
                'phone' => $phone,
                'verified' => false,
                'attempts' => 0,
            ],
        ]);
        session()->forget('school_reg_otp_verified');

        $msg = "ShuleXpert School Registration OTP: {$otp}. Do not share. Expires in 10 minutes.";
        $smsResult = (new SmsService())->sendSms($phone, $msg);

        if (is_array($smsResult) && isset($smsResult['success']) && !$smsResult['success']) {
            session()->forget('school_reg_otp');
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP SMS. Please try again shortly.',
            ], 500);
        }

        $this->recordSchoolRegOtpSend($phone);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to ' . $this->maskPhone($phone),
            'otp_token' => $token,
            'phone' => $phone,
            'masked_phone' => $this->maskPhone($phone),
        ]);
    }

    public function registerSchoolVerifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:4|max:10',
            'otp_token' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
        ]);

        $phone = $this->normalizeContactPhone($request->input('phone'));
        $pending = session('school_reg_otp');

        if (!$pending || !$phone) {
            return response()->json([
                'success' => false,
                'message' => 'OTP session expired. Please request a new OTP.',
            ], 401);
        }

        if ((string) ($pending['token'] ?? '') !== (string) $request->otp_token) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP session.'], 401);
        }

        if ((string) ($pending['phone'] ?? '') !== $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number does not match the OTP session. Request a new OTP.',
            ], 401);
        }

        if (empty($pending['expires_at']) || time() > (int) $pending['expires_at']) {
            session()->forget(['school_reg_otp', 'school_reg_otp_verified']);
            return response()->json(['success' => false, 'message' => 'OTP expired. Please request a new OTP.'], 401);
        }

        $attempts = (int) ($pending['attempts'] ?? 0);
        if ($attempts >= 5) {
            session()->forget(['school_reg_otp', 'school_reg_otp_verified']);
            return response()->json([
                'success' => false,
                'message' => 'Too many incorrect attempts. Request a new OTP.',
            ], 429);
        }

        $otp = preg_replace('/\s+/', '', (string) $request->otp);
        if (!Hash::check($otp, (string) ($pending['code_hash'] ?? ''))) {
            $pending['attempts'] = $attempts + 1;
            session(['school_reg_otp' => $pending]);
            return response()->json(['success' => false, 'message' => 'Incorrect OTP.'], 401);
        }

        $pending['verified'] = true;
        $pending['verified_at'] = time();
        session([
            'school_reg_otp' => $pending,
            'school_reg_otp_verified' => [
                'phone' => $phone,
                'token' => $pending['token'],
                'expires_at' => time() + (30 * 60),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone verified. Saving your registration...',
            'phone' => $phone,
        ]);
    }

    /**
     * Realtime uniqueness check for phone / email / registration_number.
     */
    public function registerSchoolCheckField(Request $request)
    {
        $field = strtolower(trim((string) $request->input('field', '')));
        if (!in_array($field, ['phone', 'email', 'registration_number'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field.',
            ], 422);
        }

        $raw = trim((string) $request->input('value', ''));
        if ($raw === '') {
            return response()->json([
                'success' => true,
                'available' => true,
                'message' => '',
            ]);
        }

        $value = $raw;
        if ($field === 'phone') {
            $normalized = $this->normalizeContactPhone($raw);
            if (!$normalized) {
                return response()->json([
                    'success' => true,
                    'available' => true,
                    'message' => '',
                    'incomplete' => true,
                ]);
            }
            $value = $normalized;
        } elseif ($field === 'email') {
            if (!filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => true,
                    'available' => true,
                    'message' => '',
                    'incomplete' => true,
                ]);
            }
            $value = strtolower($raw);
        } else {
            // registration_number, wait until a few chars typed
            if (mb_strlen($raw) < 2) {
                return response()->json([
                    'success' => true,
                    'available' => true,
                    'message' => '',
                    'incomplete' => true,
                ]);
            }
            $value = $raw;
        }

        $takenMessage = $this->isSchoolRegistrationFieldTaken($field, $value);
        if ($takenMessage) {
            return response()->json([
                'success' => true,
                'available' => false,
                'message' => $takenMessage,
            ]);
        }

        return response()->json([
            'success' => true,
            'available' => true,
            'message' => 'Available.',
        ]);
    }

    public function registerSchoolSubmit(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:200',
            'registration_number' => 'nullable|string|max:100',
            'exam_center_number' => 'nullable|string|max:100',
            'school_type' => 'required|in:Primary,Secondary',
            'ownership' => 'required|in:Public,Private',
            'established_year' => 'nullable|string|max:10',
            'region' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'ward' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:160',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'number_of_students' => 'required|integer|min:1|max:100000',
            'contact_person' => 'nullable|string|max:160',
            'notes' => 'nullable|string|max:2000',
            'otp_token' => 'required|string|max:100',
        ]);

        $phone = $this->normalizeContactPhone($request->input('phone'));
        if (!$phone) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'Enter a valid Tanzania mobile number starting with 255.']);
        }

        $verified = session('school_reg_otp_verified');
        $pending = session('school_reg_otp');

        $ok = $verified
            && ($verified['phone'] ?? null) === $phone
            && (string) ($verified['token'] ?? '') === (string) $request->otp_token
            && !empty($verified['expires_at'])
            && time() <= (int) $verified['expires_at']
            && !empty($pending['verified']);

        if (!$ok) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'Verify your phone number with OTP before submitting the application.']);
        }

        $dupErrors = [];
        $phoneTaken = $this->isSchoolRegistrationFieldTaken('phone', $phone);
        if ($phoneTaken) {
            $dupErrors['phone'] = $phoneTaken;
        }
        $emailRaw = trim((string) $request->input('email', ''));
        if ($emailRaw !== '') {
            $emailTaken = $this->isSchoolRegistrationFieldTaken('email', strtolower($emailRaw));
            if ($emailTaken) {
                $dupErrors['email'] = $emailTaken;
            }
        }
        $regNo = trim((string) $request->input('registration_number', ''));
        if ($regNo !== '') {
            $regTaken = $this->isSchoolRegistrationFieldTaken('registration_number', $regNo);
            if ($regTaken) {
                $dupErrors['registration_number'] = $regTaken;
            }
        }
        if ($dupErrors !== []) {
            return back()->withInput()->withErrors($dupErrors);
        }

        $submitKey = 'school-reg-submit:' . $request->ip() . ':' . $phone;
        if (RateLimiter::tooManyAttempts($submitKey, 3)) {
            return back()
                ->withInput()
                ->withErrors(['school_name' => 'Too many submissions. Please wait a few minutes and try again.']);
        }
        RateLimiter::hit($submitKey, 600);

        \App\Models\SchoolRegistrationRequest::create([
            'school_name' => $request->school_name,
            'registration_number' => $request->registration_number,
            'exam_center_number' => $request->exam_center_number,
            'school_type' => $request->school_type,
            'ownership' => $request->ownership,
            'established_year' => $request->established_year,
            'region' => $request->region,
            'district' => $request->district,
            'ward' => $request->ward,
            'email' => $request->email,
            'phone' => $phone,
            'address' => $request->address,
            'number_of_students' => (int) $request->number_of_students,
            'contact_person' => $request->contact_person,
            'notes' => $request->notes,
            'status' => 'pending',
            'phone_verified_at' => now(),
        ]);

        session()->forget(['school_reg_otp', 'school_reg_otp_verified']);
        $this->clearSchoolRegOtpTracking($phone);

        return redirect()
            ->route('site.register_school')
            ->with('success', 'Thank you! Your school registration application has been received and is Pending. The ShuleXpert team will verify it. You will not receive login credentials until after verification.');
    }

    /**
     * Block check before sending school-registration OTP.
     */
    private function assertSchoolRegOtpPhoneAllowed(string $phone): ?\Illuminate\Http\JsonResponse
    {
        $row = OtpPhoneBlock::query()
            ->where('phone', $phone)
            ->where('context', OtpPhoneBlock::CONTEXT_SCHOOL_REGISTRATION)
            ->first();

        if ($row && $row->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'This phone number is blocked from receiving registration OTPs. Contact ShuleXpert support / admin to release it.',
                'blocked' => true,
            ], 403);
        }

        return null;
    }

    /**
     * Count OTP sends; auto-block after MAX_OTP_SENDS until admin releases.
     */
    private function recordSchoolRegOtpSend(string $phone): void
    {
        $row = OtpPhoneBlock::query()->firstOrCreate(
            [
                'phone' => $phone,
                'context' => OtpPhoneBlock::CONTEXT_SCHOOL_REGISTRATION,
            ],
            [
                'otp_send_count' => 0,
                'is_blocked' => false,
            ],
        );

        if ($row->is_blocked) {
            return;
        }

        $row->otp_send_count = (int) $row->otp_send_count + 1;
        $row->last_otp_at = now();

        if ($row->otp_send_count >= OtpPhoneBlock::MAX_OTP_SENDS) {
            $row->is_blocked = true;
            $row->blocked_at = now();
            $row->block_reason = 'Exceeded ' . OtpPhoneBlock::MAX_OTP_SENDS . ' OTP requests';
        }

        $row->save();
    }

    private function clearSchoolRegOtpTracking(string $phone): void
    {
        OtpPhoneBlock::query()
            ->where('phone', $phone)
            ->where('context', OtpPhoneBlock::CONTEXT_SCHOOL_REGISTRATION)
            ->where('is_blocked', false)
            ->delete();
    }

    /**
     * Normalize TZ phone to 255XXXXXXXXX (12 digits).
     */
    private function normalizeContactPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '255')) {
            $digits = substr($digits, 3);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        $digits = '255' . $digits;

        // Tanzania mobile: 255 + 9 digits (usually starting with 6 or 7)
        if (!preg_match('/^255[67]\d{8}$/', $digits)) {
            return null;
        }

        return $digits;
    }

    /**
     * Return an error message if phone/email/registration_number is already used
     * by an active school or a pending/ready registration request; otherwise null.
     */
    private function isSchoolRegistrationFieldTaken(string $field, string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $labels = [
            'phone' => 'Phone number',
            'email' => 'Email',
            'registration_number' => 'Registration number',
        ];
        $label = $labels[$field] ?? 'Value';

        if ($field === 'phone') {
            $normalized = $this->normalizeContactPhone($value) ?? $value;
            $local9 = str_starts_with($normalized, '255')
                ? substr($normalized, 3)
                : $normalized;
            $variants = array_values(array_unique(array_filter([
                $normalized,
                '0' . $local9,
                $local9,
                '+' . $normalized,
            ])));

            $existsSchool = \App\Models\School::query()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where(function ($q) use ($variants) {
                    foreach ($variants as $v) {
                        $q->orWhere('phone', $v);
                    }
                })
                ->exists();
            if ($existsSchool) {
                return "{$label} is already taken.";
            }

            $existsRequest = \App\Models\SchoolRegistrationRequest::query()
                ->whereIn('status', ['pending', 'ready'])
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where(function ($q) use ($variants) {
                    foreach ($variants as $v) {
                        $q->orWhere('phone', $v);
                    }
                })
                ->exists();
            if ($existsRequest) {
                return "{$label} is already used in a pending registration.";
            }

            return null;
        }

        if ($field === 'email') {
            $email = strtolower($value);
            $existsSchool = \App\Models\School::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->exists();
            if ($existsSchool) {
                return "{$label} is already taken.";
            }
            $existsRequest = \App\Models\SchoolRegistrationRequest::query()
                ->whereIn('status', ['pending', 'ready'])
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->exists();
            if ($existsRequest) {
                return "{$label} is already used in a pending registration.";
            }

            return null;
        }

        // registration_number
        $reg = mb_strtolower($value);
        $existsSchool = \App\Models\School::query()
            ->whereNotNull('registration_number')
            ->where('registration_number', '!=', '')
            ->whereRaw('LOWER(TRIM(registration_number)) = ?', [$reg])
            ->exists();
        if ($existsSchool) {
            return "{$label} is already taken.";
        }
        $existsRequest = \App\Models\SchoolRegistrationRequest::query()
            ->whereIn('status', ['pending', 'ready'])
            ->whereNotNull('registration_number')
            ->where('registration_number', '!=', '')
            ->whereRaw('LOWER(TRIM(registration_number)) = ?', [$reg])
            ->exists();
        if ($existsRequest) {
            return "{$label} is already used in a pending registration.";
        }

        return null;
    }

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) < 8) {
            return $digits;
        }

        return substr($digits, 0, 6) . '****' . substr($digits, -2);
    }

    public function contactSendOtp(Request $request)
    {
        $ipKey = 'contact-otp-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return response()->json([
                'success' => false,
                'message' => "Too many OTP requests. Try again in {$seconds} seconds.",
            ], 429);
        }

        $phone = $this->normalizeContactPhone($request->input('phone'));
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid Tanzania mobile number starting with 255 (e.g. 2557XXXXXXXX).',
            ], 422);
        }

        $phoneKey = 'contact-otp-phone:' . $phone;
        if (RateLimiter::tooManyAttempts($phoneKey, 3)) {
            $seconds = RateLimiter::availableIn($phoneKey);
            return response()->json([
                'success' => false,
                'message' => "OTP already sent recently. Wait {$seconds} seconds before requesting again.",
            ], 429);
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($phoneKey, 120);

        $otp = (string) random_int(100000, 999999);
        $token = (string) Str::uuid();

        session([
            'contact_otp' => [
                'token' => $token,
                'code_hash' => Hash::make($otp),
                'expires_at' => time() + (10 * 60),
                'phone' => $phone,
                'verified' => false,
                'attempts' => 0,
            ],
        ]);
        session()->forget('contact_otp_verified');

        $msg = "ShuleXpert Contact OTP: {$otp}. Use this code to send your contact request. Do not share. Expires in 10 minutes.";
        $smsResult = (new SmsService())->sendSms($phone, $msg);

        if (is_array($smsResult) && isset($smsResult['success']) && !$smsResult['success']) {
            session()->forget('contact_otp');
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP SMS. Please try again shortly.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to ' . $this->maskPhone($phone),
            'otp_token' => $token,
            'phone' => $phone,
            'masked_phone' => $this->maskPhone($phone),
        ]);
    }

    public function contactVerifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:4|max:10',
            'otp_token' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
        ]);

        $phone = $this->normalizeContactPhone($request->input('phone'));
        $pending = session('contact_otp');

        if (!$pending || !$phone) {
            return response()->json([
                'success' => false,
                'message' => 'OTP session expired. Please request a new OTP.',
            ], 401);
        }

        if ((string) ($pending['token'] ?? '') !== (string) $request->otp_token) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP session.'], 401);
        }

        if ((string) ($pending['phone'] ?? '') !== $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number does not match the OTP session. Request a new OTP.',
            ], 401);
        }

        if (empty($pending['expires_at']) || time() > (int) $pending['expires_at']) {
            session()->forget(['contact_otp', 'contact_otp_verified']);
            return response()->json(['success' => false, 'message' => 'OTP expired. Please request a new OTP.'], 401);
        }

        $attempts = (int) ($pending['attempts'] ?? 0);
        if ($attempts >= 5) {
            session()->forget(['contact_otp', 'contact_otp_verified']);
            return response()->json([
                'success' => false,
                'message' => 'Too many incorrect attempts. Request a new OTP.',
            ], 429);
        }

        $otp = preg_replace('/\s+/', '', (string) $request->otp);
        if (!Hash::check($otp, (string) ($pending['code_hash'] ?? ''))) {
            $pending['attempts'] = $attempts + 1;
            session(['contact_otp' => $pending]);
            return response()->json(['success' => false, 'message' => 'Incorrect OTP.'], 401);
        }

        $pending['verified'] = true;
        $pending['verified_at'] = time();
        session([
            'contact_otp' => $pending,
            'contact_otp_verified' => [
                'phone' => $phone,
                'token' => $pending['token'],
                'expires_at' => time() + (30 * 60),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone verified. You can now send your message.',
            'phone' => $phone,
        ]);
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'phone' => 'required|string|max:30',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'otp_token' => 'required|string|max:100',
        ]);

        $phone = $this->normalizeContactPhone($request->input('phone'));
        if (!$phone) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'Enter a valid Tanzania mobile number starting with 255.']);
        }

        $verified = session('contact_otp_verified');
        $pending = session('contact_otp');

        $ok = $verified
            && ($verified['phone'] ?? null) === $phone
            && (string) ($verified['token'] ?? '') === (string) $request->otp_token
            && !empty($verified['expires_at'])
            && time() <= (int) $verified['expires_at']
            && !empty($pending['verified']);

        if (!$ok) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'Verify your phone number with OTP before sending the message.']);
        }

        // Extra guard: one contact submission per verified phone/IP window
        $submitKey = 'contact-submit:' . $request->ip() . ':' . $phone;
        if (RateLimiter::tooManyAttempts($submitKey, 2)) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Too many contact requests. Please wait a few minutes and try again.']);
        }
        RateLimiter::hit($submitKey, 600);

        \App\Models\CustomerRequest::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        \App\Models\WebVisitor::rememberVisitorEmail($request->email, $request->ip());

        session()->forget(['contact_otp', 'contact_otp_verified']);

        return back()->with('success', 'Thank you! Your message has been received. We will contact you soon.');
    }

    /**
     * Lightweight floating "Ask us" popup on the public site.
     * Saves into the same customer_requests queue used by Contact.
     */
    public function quickAsk(Request $request)
    {
        // Honeypot — bots fill this, humans never see it
        if (filled($request->input('website'))) {
            return response()->json(['success' => true, 'message' => 'OK']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:160',
            'message' => 'required|string|max:2000',
        ]);

        $phone = $this->normalizeContactPhone($validated['phone']);
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid Tanzania mobile number starting with 255 (e.g. 2557XXXXXXXX).',
                'errors' => ['phone' => ['Enter a valid Tanzania mobile number starting with 255.']],
            ], 422);
        }

        $ipKey = 'quick-ask:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);

            return response()->json([
                'success' => false,
                'message' => "Too many questions. Try again in {$seconds} seconds.",
            ], 429);
        }
        RateLimiter::hit($ipKey, 600);

        $email = trim((string) ($validated['email'] ?? ''));
        if ($email === '') {
            $email = 'ask+' . $phone . '@shulexpert.local';
        }

        \App\Models\CustomerRequest::create([
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $phone,
            'subject' => 'Quick Ask (website float)',
            'message' => $validated['message'],
        ]);

        if (!str_ends_with($email, '@shulexpert.local')) {
            \App\Models\WebVisitor::rememberVisitorEmail($email, $request->ip());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you! We received your question and will reply soon.',
        ]);
    }
}
