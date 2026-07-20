<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Public marketing modules mapped from real ShuleXpert system features.
     */
    public static function modules(): array
    {
        return [
            'student-management' => [
                'slug' => 'student-management',
                'title' => 'Student Management',
                'short' => 'Enrollment, promotions, profiles, and student lifecycle in one place.',
                'icon' => 'fa-graduation-cap',
                'image' => 'images/student.png',
                'hero' => 'images/class.png',
                'body' => 'Register students, manage enrollments by academic year, handle promotions and transfers, and keep complete student profiles with parent links and sponsorship support.',
                'points' => [
                    'Multi-step student registration',
                    'Academic-year enrollments & promotions',
                    'Parent and sponsor linking',
                    'Student ID cards & records',
                ],
            ],
            'fees-payments' => [
                'slug' => 'fees-payments',
                'title' => 'Fees & Payments',
                'short' => 'Fee structures, control numbers, discounts, deadlines, and payment ledgers.',
                'icon' => 'fa-money',
                'image' => 'images/fees.png',
                'hero' => 'images/fees.png',
                'body' => 'Define class fee structures with single or multi bank accounts, generate unique control numbers, apply agreements and discounts, track deadlines, and record payments with automatic allocation.',
                'points' => [
                    'Official fee structure per class',
                    'Control number billing',
                    'Agreements, discounts & sponsors',
                    'Payment verification & SMS reminders',
                ],
            ],
            'attendance-biometric' => [
                'slug' => 'attendance-biometric',
                'title' => 'Attendance & Biometric',
                'short' => 'Manual attendance plus ZKTeco fingerprint sync and parent SMS alerts.',
                'icon' => 'fa-hand-paper-o',
                'image' => 'images/biometric.jpg',
                'hero' => 'images/biometric.jpg',
                'body' => 'Capture student, teacher, and staff attendance manually or via fingerprint devices. Sync through School Agent, notify parents by SMS, and review attendance reports instantly.',
                'points' => [
                    'Manual & fingerprint attendance',
                    'ZKTeco device integration',
                    'Parent fingerprint portal',
                    'Auto attendance SMS schedules',
                ],
            ],
            'exams-results' => [
                'slug' => 'exams-results',
                'title' => 'Exams & Results',
                'short' => 'Exam papers, halls, grading, publishing, and printable report cards.',
                'icon' => 'fa-file-text-o',
                'image' => 'images/exams.png',
                'hero' => 'images/exam.jpg',
                'body' => 'Plan examinations, approve papers, manage halls and supervisors, enter results, apply grade scales, publish to parents, and export professional PDF reports.',
                'points' => [
                    'Exam paper workflow',
                    'Result entry & approvals',
                    'Subject analysis & statistics',
                    'Parent-facing result portal',
                ],
            ],
            'timetable-academics' => [
                'slug' => 'timetable-academics',
                'title' => 'Timetable & Academics',
                'short' => 'Class sessions, academic years, schemes of work, and lesson plans.',
                'icon' => 'fa-calendar',
                'image' => 'images/time_table.png',
                'hero' => 'images/timetable.jpg',
                'body' => 'Build class and exam timetables, manage academic years and terms, run schemes of work, and capture lesson plans with supervisory oversight.',
                'points' => [
                    'Class & exam session timetables',
                    'Academic year selection',
                    'Scheme of work & lesson plans',
                    'School calendar & holidays',
                ],
            ],
            'parent-portal' => [
                'slug' => 'parent-portal',
                'title' => 'Parent Portal',
                'short' => 'Fees, results, attendance, and communication for parents — web & mobile.',
                'icon' => 'fa-users',
                'image' => 'images/parents.png',
                'hero' => 'images/parents.png',
                'body' => 'Give parents secure access to student results, attendance, fee balances, control numbers, and permission requests through web portal and Flutter mobile experience.',
                'points' => [
                    'Results & report cards',
                    'Attendance & fingerprint views',
                    'Fees summary & payments',
                    'Permission requests',
                ],
            ],
            'library' => [
                'slug' => 'library',
                'title' => 'Library Management',
                'short' => 'Books catalog, borrowing, returns, damage and loss tracking.',
                'icon' => 'fa-book',
                'image' => 'images/school.png',
                'hero' => 'images/school.png',
                'body' => 'Catalog books, issue and return loans, track overdue items, and manage damaged or lost books with clear school-level reporting.',
                'points' => [
                    'Book catalog & categories',
                    'Borrow & return workflow',
                    'Damage & loss records',
                    'Library reports & PDF export',
                ],
            ],
            'store-inventory' => [
                'slug' => 'store-inventory',
                'title' => 'Store & Inventory',
                'short' => 'Stock items, packages, requisitions, and resource movement.',
                'icon' => 'fa-cubes',
                'image' => 'images/roles.png',
                'hero' => 'images/roles.png',
                'body' => 'Manage school store items, packages, stock levels, and staff/teacher requisitions with approval flows and notifications.',
                'points' => [
                    'Item & package catalog',
                    'Stock tracking',
                    'Staff/teacher requisitions',
                    'Incoming & outgoing resources',
                ],
            ],
            'hostel' => [
                'slug' => 'hostel',
                'title' => 'Hostel & Accommodation',
                'short' => 'Blocks, rooms, beds, and student bed assignments.',
                'icon' => 'fa-bed',
                'image' => 'images/admin.jpg',
                'hero' => 'images/admin.jpg',
                'body' => 'Organize boarding facilities by blocks and rooms, assign beds to students, and keep accommodation status up to date.',
                'points' => [
                    'Blocks, rooms & beds',
                    'Student bed assignment',
                    'Accommodation status tracking',
                    'API-ready accommodation data',
                ],
            ],
            'sms-communication' => [
                'slug' => 'sms-communication',
                'title' => 'SMS Communication',
                'short' => 'School-branded SMS for fees, attendance, results, and announcements.',
                'icon' => 'fa-commenting',
                'image' => 'images/PP.jpg',
                'hero' => 'images/PP.jpg',
                'body' => 'Send targeted SMS to parents, teachers, and staff using each school’s sender ID — including control numbers, debt reminders, and attendance alerts.',
                'points' => [
                    'Broadcast & targeted SMS',
                    'Fee & debt reminders',
                    'Attendance auto-SMS',
                    'Delivery logs & balance checks',
                ],
            ],
            'discipline-blackbook' => [
                'slug' => 'discipline-blackbook',
                'title' => 'Discipline (Black Book)',
                'short' => 'Incident reporting, actors, categories, and disciplinary follow-up.',
                'icon' => 'fa-gavel',
                'image' => 'images/H.jpg',
                'hero' => 'images/H.jpg',
                'body' => 'Record discipline incidents, categorize problems and punishments, assign actors, and produce Black Book reports for school leadership.',
                'points' => [
                    'Incident reports',
                    'Problem & punishment categories',
                    'Role-based access',
                    'Printable discipline reports',
                ],
            ],
            'accountant' => [
                'slug' => 'accountant',
                'title' => 'Accountant Module',
                'short' => 'Income, expenses, budgets, categories, and financial reports.',
                'icon' => 'fa-pie-chart',
                'image' => 'images/accountant.png',
                'hero' => 'images/accountant.png',
                'body' => 'Track school income and expenses, organize categories, plan budgets, and generate financial PDF reports for management.',
                'points' => [
                    'Income & expense ledgers',
                    'Budget planning',
                    'Category management',
                    'Financial PDF reports',
                ],
            ],
            'sgpm' => [
                'slug' => 'sgpm',
                'title' => 'SGPM & Performance',
                'short' => 'Strategic goals, departments, tasks, evidence, and performance tracking.',
                'icon' => 'fa-bullseye',
                'image' => 'images/results.png',
                'hero' => 'images/results.png',
                'body' => 'Run strategic goal and performance management across departments — assign tasks, capture evidence, and monitor progress.',
                'points' => [
                    'Strategic goals & objectives',
                    'Departmental task cascades',
                    'Evidence & action plans',
                    'Performance dashboards',
                ],
            ],
            'teacher-portal' => [
                'slug' => 'teacher-portal',
                'title' => 'Teacher Portal',
                'short' => 'Subjects, results, sessions, schemes, permissions, and duties.',
                'icon' => 'fa-pencil-square-o',
                'image' => 'images/teacher.png',
                'hero' => 'images/teacher.png',
                'body' => 'Empower teachers with subject tools, result entry, session supervision, schemes of work, leave/permissions, and duty management.',
                'points' => [
                    'Subject & result tools',
                    'Session supervision',
                    'Scheme & lesson planning',
                    'Permissions & teacher duties',
                ],
            ],
        ];
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
                'bio' => 'Builds and maintains ShuleXpert on web and mobile — from school portals and APIs to parent and teacher apps schools use every day.',
                'more_bio' => "Full-stack Web & App Developer (Backend + Frontend).\n\nDesigns and ships production systems for schools and organisations across Tanzania — secure APIs, responsive dashboards, and reliable mobile experiences.\n\nKey projects:\n- USHIRIKA PLUSS: backend development supporting partnership-union management (unions of cooperative societies).\n- ShuleXpert: full-stack development for a school management platform used by 25+ schools.\n- Hostel Booking System: online room booking with web + a mobile app. Payments via mobile networks and cards (20+ payment options).\n- OFICELink: backend development for an office operations system used across 20+ offices.\n\nFocus: secure APIs, responsive dashboards, clean UX, and reliable deployments that schools trust every day.",
                'image' => 'images/H.jpg',
                'featured' => true,
                'phone' => '0614863345',
                'email' => null,
                'location' => 'Moshi, Kilimanjaro, Tanzania',
                'skills' => [
                    'Laravel & PHP',
                    'Flutter / Mobile',
                    'MySQL',
                ],
                'projects' => [
                    'USHIRIKA PLUSS — partnership-union management backend',
                    'ShuleXpert — full-stack school platform (25+ schools)',
                    'Hostel Booking System — web + mobile with 20+ payment options',
                    'OFICELink — office operations backend (20+ offices)',
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
                'bio' => 'Translates real school processes into ShuleXpert modules — fees, attendance, exams, and reporting that match how administrators actually work.',
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
                'bio' => 'Shapes the ShuleXpert experience — clear portals, mobile-friendly layouts, and brand-consistent design across the public site and school dashboards.',
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
        return view('site.home', [
            'modules' => array_values(self::modules()),
            'teamMembers' => self::teamMembers(),
            'seo' => [
                'title' => 'ShuleXpert — School Management System for the World',
                'description' => 'ShuleXpert is a complete school management platform for schools worldwide — fees, attendance, exams, parents, library, store, SMS, biometric fingerprint, and more. Powered by EmCa Techonology.',
                'keywords' => 'ShuleXpert, school management system worldwide, school fees control number, biometric attendance, exam results, parent portal, EmCa Techonology',
                'canonical' => url('/'),
                'type' => 'website',
            ],
        ]);
    }

    public function team()
    {
        $members = self::teamMembers();

        return view('site.team', [
            'teamMembers' => $members,
            'seo' => [
                'title' => 'Our Team — ShuleXpert | EmCa Techonology',
                'description' => 'Meet the EmCa Techonology team behind ShuleXpert — Senior Developer, System Analyst, and UI/UX Designer building school management for the world.',
                'keywords' => 'ShuleXpert team, Senior Developer, EmCa Techonology developers, school software engineers Tanzania, UI UX designer ShuleXpert',
                'canonical' => url('/team'),
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
        $canonical = url('/team/' . $slug);
        $image = url($member['image']);
        $desc = \Illuminate\Support\Str::limit(
            trim(preg_replace('/\s+/', ' ', $member['more_bio'] ?? $member['bio'] ?? $member['tagline'] ?? '')),
            160,
            ''
        );

        return view('site.team-member', [
            'member' => $member,
            'otherMembers' => $others,
            'seo' => [
                'title' => $name . ' — ' . $role . ' | ShuleXpert · EmCa Techonology',
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
                'title' => 'About ShuleXpert — EmCa Techonology',
                'description' => 'Learn about ShuleXpert, the school management platform built by EmCa Techonology for schools worldwide — from preschool to advanced level.',
                'keywords' => 'About ShuleXpert, EmCa Techonology, school software worldwide',
                'canonical' => url('/about'),
                'type' => 'website',
            ],
        ]);
    }

    public function modulesIndex()
    {
        $modules = array_values(self::modules());

        return view('site.modules', [
            'modules' => $modules,
            'seo' => [
                'title' => 'ShuleXpert Modules — Complete School Features',
                'description' => 'Explore ShuleXpert modules: fees & payments, attendance, exams, parent portal, library, store, hostel, SMS, discipline, accountant, and SGPM.',
                'keywords' => 'ShuleXpert modules, school fees system, attendance biometric, exam results software',
                'canonical' => url('/modules'),
                'type' => 'website',
            ],
        ]);
    }

    public function moduleShow(string $slug)
    {
        $all = self::modules();
        if (!isset($all[$slug])) {
            abort(404);
        }

        $module = $all[$slug];

        return view('site.module-detail', [
            'module' => $module,
            'modules' => array_values($all),
            'seo' => [
                'title' => $module['title'] . ' — ShuleXpert Module',
                'description' => $module['short'] . ' ' . $module['body'],
                'keywords' => $module['title'] . ', ShuleXpert, school management',
                'canonical' => url('/modules/' . $slug),
                'type' => 'article',
            ],
        ]);
    }

    public function pricing()
    {
        return view('site.pricing', [
            'seo' => [
                'title' => 'ShuleXpert Pricing — TZS 1,000,000 Installation + TZS 500/Student/Month',
                'description' => 'ShuleXpert pricing by EmCa Techonology: one-time installation fee of TZS 1,000,000 (training, experts, cloud storage, setup) and monthly TZS 500 per student for SMS, support and platform services.',
                'keywords' => 'ShuleXpert pricing, school management cost worldwide, installation fee 1000000, 500 TZS per student, EmCa Techonology',
                'canonical' => url('/pricing'),
                'type' => 'website',
            ],
        ]);
    }

    public function services()
    {
        return view('site.services', [
            'seo' => [
                'title' => 'ShuleXpert Services — Implementation, Training & Support',
                'description' => 'ShuleXpert services by EmCa Techonology: school digitization, training, biometric support, SMS communication, hosting and continuous user support.',
                'keywords' => 'ShuleXpert services, school software training worldwide, biometric attendance support, EmCa Techonology',
                'canonical' => url('/services'),
                'type' => 'website',
            ],
        ]);
    }

    public function faq()
    {
        return view('site.faq', [
            'seo' => [
                'title' => 'ShuleXpert FAQ — Pricing, Modules & Support',
                'description' => 'Frequently asked questions about ShuleXpert installation fee (TZS 1,000,000), monthly TZS 500 per student, control numbers, biometric attendance and EmCa Techonology support.',
                'keywords' => 'ShuleXpert FAQ, school software pricing worldwide, control number fees, EmCa Techonology support',
                'canonical' => url('/faq'),
                'type' => 'website',
            ],
        ]);
    }

    public function download()
    {
        return view('site.download', [
            'seo' => [
                'title' => 'Download ShuleXpert App & Online School Application — Tanzania',
                'description' => 'Download the ShuleXpert Android APK for teachers and parents, or apply to participating schools online. Real-time alerts, admissions, fees and school communication — powered by EmCa Techonology, Moshi Tanzania.',
                'keywords' => 'download ShuleXpert APK, ShuleXpert Android app, school management app Tanzania, online school application Tanzania, apply to school online, ShuleXpert mobile app, EmCa Techonology, school admission online',
                'canonical' => url('/download'),
                'type' => 'website',
                'image' => url('/images/shuleXpert.jpg'),
                'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            ],
        ]);
    }

    public function contact()
    {
        return view('site.contact', [
            'seo' => [
                'title' => 'Contact ShuleXpert — EmCa Techonology',
                'description' => 'Contact ShuleXpert / EmCa Techonology in Moshi, Kilimanjaro. Call '.config('shulexpert.phone_display').' or email emca@emca.tech.',
                'keywords' => 'Contact ShuleXpert, EmCa Techonology Moshi, school software support',
                'canonical' => url('/contact'),
                'type' => 'website',
            ],
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
        ]);

        \App\Models\CustomerRequest::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Asante! Ujumbe wako umepokelewa. Tutawasiliana nawe hivi karibuni.');
    }
}
