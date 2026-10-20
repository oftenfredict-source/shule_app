<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolBoardActionItem;
use App\Models\SchoolBoardCommittee;
use App\Models\SchoolBoardCommitteeMember;
use App\Models\SchoolBoardDocument;
use App\Models\SchoolBoardMeeting;
use App\Models\SchoolBoardMeetingAttendee;
use App\Models\SchoolBoardMember;
use App\Models\SchoolBoardMinute;
use App\Models\SchoolBoardPosition;
use App\Models\SchoolBoardResolution;
use App\Models\SchoolBoardRole;
use App\Models\SchoolBoardRolePermission;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SchoolBoardController extends Controller
{
    private function authorizeBoardAccess(?Request $request = null)
    {
        $user = Session::get('user_type');
        if (!$user || !in_array($user, ['Admin', 'Staff', 'Teacher'], true)) {
            if ($request && ($request->ajax() || $request->expectsJson() || $request->wantsJson())) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Unauthorized access');
        }

        return null;
    }

    private function normalizeMemberPayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'memberID', 'positionID', 'middle_name', 'gender', 'email',
            'occupation', 'organization', 'address',
            'appointed_date', 'term_end_date', 'notes',
            'username', 'password', 'password_confirmation',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    private function boardLoginUsernameFromEmail(string $email): string
    {
        return trim($email);
    }

    private function boardDefaultPasswordFromLastName(string $lastName): string
    {
        $password = strtoupper(trim($lastName));

        return $password !== '' ? $password : 'MEMBER';
    }

    private function assertBoardLoginEmailAvailable(string $email, int $schoolID, ?int $ignoreMemberID = null): ?string
    {
        $email = trim($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Valid email is required — it is used as the portal username.';
        }

        $loginName = $this->boardLoginUsernameFromEmail($email);

        $memberQuery = SchoolBoardMember::where('schoolID', $schoolID)
            ->where(function ($q) use ($loginName, $email) {
                $q->where('username', $loginName)
                    ->orWhere('username', $email)
                    ->orWhereRaw('LOWER(email) = ?', [strtolower($email)]);
            });
        if ($ignoreMemberID) {
            $memberQuery->where('memberID', '!=', $ignoreMemberID);
        }
        if ($memberQuery->exists()) {
            return 'This email is already used by another board member in this school.';
        }

        $ignoreUserId = null;
        if ($ignoreMemberID) {
            $current = SchoolBoardMember::where('memberID', $ignoreMemberID)->first();
            $ignoreUserId = $current?->user_id;
        }

        $emailLower = strtolower($email);
        $userConflict = User::query()
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->where(function ($q) use ($loginName, $email, $emailLower) {
                $q->where('name', $loginName)
                    ->orWhere('name', $email)
                    ->orWhere('email', $email)
                    ->orWhereRaw('LOWER(name) = ?', [$emailLower])
                    ->orWhereRaw('LOWER(email) = ?', [$emailLower]);
            })
            ->first();

        if ($userConflict && $userConflict->user_type !== 'BoardMember') {
            return 'This email is already used by another account in the system.';
        }
        if ($userConflict && $userConflict->user_type === 'BoardMember') {
            return 'This email is already linked to another board login.';
        }

        return null;
    }

    private function createOrUpdateBoardLoginUser(
        SchoolBoardMember $member,
        string $loginEmail,
        ?string $plainPassword,
        int $schoolID
    ): User {
        $loginEmail = trim($loginEmail);
        $loginName = $this->boardLoginUsernameFromEmail($loginEmail);

        if ($member->user_id) {
            $user = User::find($member->user_id);
            if ($user) {
                $user->name = $loginName;
                $user->email = $loginEmail;
                $user->user_type = 'BoardMember';
                if ($plainPassword !== null && $plainPassword !== '') {
                    $user->password = $plainPassword;
                }
                $user->save();

                $member->username = $loginName;
                $member->save();

                return $user;
            }
        }

        if ($plainPassword === null || $plainPassword === '') {
            throw new \InvalidArgumentException('Password is required to create a login account.');
        }

        // users table has no schoolID column — school is linked via school_board_members
        $user = User::create([
            'name' => $loginName,
            'email' => $loginEmail,
            'password' => $plainPassword,
            'user_type' => 'BoardMember',
        ]);

        $member->user_id = $user->id;
        $member->username = $loginName;
        $member->save();

        return $user;
    }

    private function sendBoardCredentialsSms(SchoolBoardMember $member, string $username, string $plainPassword, int $schoolID): ?array
    {
        if (empty($member->phone)) {
            return null;
        }

        try {
            $school = School::find($schoolID);
            $schoolName = $school->school_name ?? 'ShuleXpert';
            $prefix = ($school && $school->senderID) ? '' : "{$schoolName}. ";
            $message = "{$prefix}School Board portal. Username: {$username}. Password: {$plainPassword}. Login: https://shulexpert.com/login";

            return (new SmsService())->sendSms($member->phone, $message, null, $schoolID);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function storeMember(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeMemberPayload($request), [
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
                'phone' => 'required|string|max:40',
                'email' => 'required|email|max:150',
                'occupation' => 'nullable|string|max:150',
                'organization' => 'nullable|string|max:150',
                'national_id' => 'required|string|max:50',
                'address' => 'nullable|string|max:500',
                'positionID' => 'nullable|integer',
                'appointed_date' => 'nullable|date',
                'term_end_date' => 'nullable|date|after_or_equal:appointed_date',
                'status' => 'nullable|in:Active,Inactive,Former',
                'notes' => 'nullable|string|max:1000',
            ], [
                'email.required' => 'Email is required — it is used as the portal username.',
            ])->validate();

            if (!empty($validated['positionID'])) {
                $ownsPosition = SchoolBoardPosition::where('schoolID', $schoolID)
                    ->where('positionID', $validated['positionID'])
                    ->exists();
                if (!$ownsPosition) {
                    return response()->json(['success' => false, 'message' => 'Invalid position selected.'], 422);
                }
            }

            $loginEmail = trim($validated['email']);
            if ($err = $this->assertBoardLoginEmailAvailable($loginEmail, (int) $schoolID)) {
                return response()->json(['success' => false, 'message' => $err], 422);
            }

            $loginUsername = $this->boardLoginUsernameFromEmail($loginEmail);
            $plainPassword = $this->boardDefaultPasswordFromLastName($validated['last_name']);
            $smsResult = null;

            $member = DB::transaction(function () use ($validated, $schoolID, $loginEmail, $loginUsername, $plainPassword) {
                $member = SchoolBoardMember::create([
                    'schoolID' => $schoolID,
                    'positionID' => $validated['positionID'] ?? null,
                    'first_name' => trim($validated['first_name']),
                    'middle_name' => isset($validated['middle_name']) ? trim((string) $validated['middle_name']) : null,
                    'last_name' => trim($validated['last_name']),
                    'gender' => $validated['gender'] ?? null,
                    'phone' => trim($validated['phone']),
                    'email' => $loginEmail,
                    'occupation' => $validated['occupation'] ?? null,
                    'organization' => $validated['organization'] ?? null,
                    'national_id' => trim($validated['national_id']),
                    'address' => $validated['address'] ?? null,
                    'appointed_date' => $validated['appointed_date'] ?? null,
                    'term_end_date' => $validated['term_end_date'] ?? null,
                    'status' => $validated['status'] ?? 'Active',
                    'notes' => $validated['notes'] ?? null,
                    'username' => $loginUsername,
                ]);

                $this->createOrUpdateBoardLoginUser($member, $loginEmail, $plainPassword, (int) $schoolID);
                $this->syncPortalRoleFromPosition($member->fresh());

                return $member->fresh(['position', 'boardRoles']);
            });

            $smsResult = $this->sendBoardCredentialsSms($member, $loginEmail, $plainPassword, (int) $schoolID);

            return response()->json([
                'success' => true,
                'message' => 'Board member registered with portal login.',
                'memberID' => $member->memberID,
                'username' => $loginEmail,
                'password' => $plainPassword,
                'sms_sent' => (bool) ($smsResult['success'] ?? false),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not save member: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateMember(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeMemberPayload($request), [
                'memberID' => 'required|integer',
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name' => 'required|string|max:100',
                'gender' => 'nullable|in:Male,Female,Other',
                'phone' => 'required|string|max:40',
                'email' => 'required|email|max:150',
                'occupation' => 'nullable|string|max:150',
                'organization' => 'nullable|string|max:150',
                'national_id' => 'required|string|max:50',
                'address' => 'nullable|string|max:500',
                'positionID' => 'nullable|integer',
                'appointed_date' => 'nullable|date',
                'term_end_date' => 'nullable|date|after_or_equal:appointed_date',
                'status' => 'nullable|in:Active,Inactive,Former',
                'notes' => 'nullable|string|max:1000',
            ], [
                'email.required' => 'Email is required — it is used as the portal username.',
            ])->validate();

            $member = SchoolBoardMember::where('schoolID', $schoolID)
                ->where('memberID', $validated['memberID'])
                ->firstOrFail();

            if (!empty($validated['positionID'])) {
                $ownsPosition = SchoolBoardPosition::where('schoolID', $schoolID)
                    ->where('positionID', $validated['positionID'])
                    ->exists();
                if (!$ownsPosition) {
                    return response()->json(['success' => false, 'message' => 'Invalid position selected.'], 422);
                }
            }

            $loginEmail = trim($validated['email']);
            if ($err = $this->assertBoardLoginEmailAvailable($loginEmail, (int) $schoolID, (int) $member->memberID)) {
                return response()->json(['success' => false, 'message' => $err], 422);
            }

            $loginUsername = $this->boardLoginUsernameFromEmail($loginEmail);
            $needsLogin = !$member->user_id || empty($member->username);
            $plainPassword = $needsLogin
                ? $this->boardDefaultPasswordFromLastName($validated['last_name'])
                : null;

            DB::transaction(function () use ($member, $validated, $schoolID, $loginEmail, $loginUsername, $plainPassword, $needsLogin) {
                $member->update([
                    'positionID' => $validated['positionID'] ?? null,
                    'first_name' => trim($validated['first_name']),
                    'middle_name' => isset($validated['middle_name']) ? trim((string) $validated['middle_name']) : null,
                    'last_name' => trim($validated['last_name']),
                    'gender' => $validated['gender'] ?? null,
                    'phone' => trim($validated['phone']),
                    'email' => $loginEmail,
                    'occupation' => $validated['occupation'] ?? null,
                    'organization' => $validated['organization'] ?? null,
                    'national_id' => trim($validated['national_id']),
                    'address' => $validated['address'] ?? null,
                    'appointed_date' => $validated['appointed_date'] ?? null,
                    'term_end_date' => $validated['term_end_date'] ?? null,
                    'status' => $validated['status'] ?? $member->status,
                    'notes' => $validated['notes'] ?? null,
                    'username' => $loginUsername,
                ]);

                if ($needsLogin || $member->user_id) {
                    $this->createOrUpdateBoardLoginUser(
                        $member->fresh(),
                        $loginEmail,
                        $plainPassword,
                        (int) $schoolID
                    );
                }

                $this->syncPortalRoleFromPosition($member->fresh(['position']));
            });

            if ($needsLogin && $plainPassword) {
                $this->sendBoardCredentialsSms(
                    $member->fresh(),
                    $loginEmail,
                    (string) $plainPassword,
                    (int) $schoolID
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Board member updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update member: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function schoolId()
    {
        return Session::get('schoolID');
    }

    private function boardPage(string $section, string $title, string $subtitle = '')
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        return view('Admin.school_board.page', [
            'section' => $section,
            'title' => $title,
            'subtitle' => $subtitle,
            'menu' => $this->menuItems(),
        ]);
    }

    private function menuItems(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label' => 'Board Dashboard',
                'route' => 'admin.school_board.dashboard',
                'icon' => 'fa-dashboard',
            ],
            [
                'key' => 'members',
                'label' => 'Board Members',
                'route' => 'admin.school_board.members',
                'icon' => 'fa-users',
            ],
            [
                'key' => 'committees',
                'label' => 'Committees',
                'route' => 'admin.school_board.committees',
                'icon' => 'fa-sitemap',
            ],
            [
                'key' => 'meetings',
                'label' => 'Meetings',
                'route' => 'admin.school_board.meetings',
                'icon' => 'fa-calendar',
            ],
            [
                'key' => 'minutes',
                'label' => 'Minutes',
                'route' => 'admin.school_board.minutes',
                'icon' => 'fa-file-text-o',
            ],
            [
                'key' => 'resolutions',
                'label' => 'Resolutions',
                'route' => 'admin.school_board.resolutions',
                'icon' => 'fa-gavel',
            ],
            [
                'key' => 'action_items',
                'label' => 'Action Items',
                'route' => 'admin.school_board.action_items',
                'icon' => 'fa-check-square-o',
            ],
            [
                'key' => 'documents',
                'label' => 'Documents',
                'route' => 'admin.school_board.documents',
                'icon' => 'fa-folder-open-o',
            ],
            [
                'key' => 'roles',
                'label' => 'Roles & Permissions',
                'route' => 'admin.school_board.roles',
                'icon' => 'fa-lock',
            ],
            [
                'key' => 'reports',
                'label' => 'Reports',
                'route' => 'admin.school_board.reports',
                'icon' => 'fa-bar-chart',
            ],
        ];
    }

    private function ensureDefaultPositions(int $schoolID): void
    {
        if (!Schema::hasTable('school_board_positions')) {
            return;
        }

        $defaults = [
            ['Chairperson', 'CHAIR', 1],
            ['Vice Chairperson', 'VICE_CHAIR', 2],
            ['Secretary', 'SECRETARY', 3],
            ['Treasurer', 'TREASURER', 4],
            ['Member', 'MEMBER', 10],
            ['Parent Representative', 'PARENT_REP', 11],
            ['Community Representative', 'COMMUNITY_REP', 12],
            ['Head of School (Ex-Officio)', 'HEAD_EXOFFICIO', 20],
        ];

        foreach ($defaults as [$name, $code, $order]) {
            SchoolBoardPosition::firstOrCreate(
                [
                    'schoolID' => $schoolID,
                    'position_name' => $name,
                ],
                [
                    'position_code' => $code,
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }

    public function members()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        $this->ensureDefaultPositions((int) $schoolID);

        $positions = SchoolBoardPosition::where('schoolID', $schoolID)
            ->orderBy('sort_order')
            ->orderBy('position_name')
            ->get();

        $members = SchoolBoardMember::with('position')
            ->where('schoolID', $schoolID)
            ->orderByRaw("CASE status WHEN 'Active' THEN 1 WHEN 'Inactive' THEN 2 ELSE 3 END")
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.school_board.members', [
            'section' => 'members',
            'title' => 'Board Members',
            'subtitle' => 'Register board members and assign official positions.',
            'menu' => $this->menuItems(),
            'members' => $members,
            'positions' => $positions,
        ]);
    }

    public function assignMemberPosition(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $this->normalizeMemberPayload($request);
        $validated = validator($data, [
            'memberID' => 'required|integer',
            'positionID' => 'nullable|integer',
        ])->validate();

        $member = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('memberID', $validated['memberID'])
            ->firstOrFail();

        if (!empty($validated['positionID'])) {
            $ownsPosition = SchoolBoardPosition::where('schoolID', $schoolID)
                ->where('positionID', $validated['positionID'])
                ->exists();
            if (!$ownsPosition) {
                return response()->json(['success' => false, 'message' => 'Invalid position selected.'], 422);
            }
        }

        $member->update([
            'positionID' => $validated['positionID'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position assigned successfully.',
        ]);
    }

    public function deleteMember(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'memberID' => 'required|integer',
        ]);

        $member = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('memberID', $validated['memberID'])
            ->firstOrFail();

        DB::transaction(function () use ($member) {
            if ($member->user_id) {
                User::where('id', $member->user_id)
                    ->where('user_type', 'BoardMember')
                    ->delete();
            } elseif ($member->username) {
                User::where('name', $member->username)
                    ->where('user_type', 'BoardMember')
                    ->delete();
            }
            $member->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Board member deleted successfully.',
        ]);
    }

    public function generateMemberLoginPassword(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'memberID' => 'required|integer',
        ]);

        $member = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('memberID', $validated['memberID'])
            ->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Board member not found.'], 404);
        }

        if (!$member->user_id && !$member->username) {
            return response()->json([
                'success' => false,
                'message' => 'This member has no portal login yet. Edit the member and set username/password first.',
            ], 422);
        }

        $user = null;
        if ($member->user_id) {
            $user = User::where('id', $member->user_id)->where('user_type', 'BoardMember')->first();
        }
        if (!$user && $member->username) {
            $user = User::where('name', $member->username)->where('user_type', 'BoardMember')->first();
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Login account not found for this member.'], 404);
        }

        $plainPassword = $this->boardDefaultPasswordFromLastName($member->last_name);
        $user->password = $plainPassword;
        $user->save();

        $username = $member->email ?: $member->username ?: $user->name;
        $smsResult = $this->sendBoardCredentialsSms($member, $username, $plainPassword, (int) $schoolID);
        $smsSent = (bool) ($smsResult['success'] ?? false);

        return response()->json([
            'success' => true,
            'message' => $smsSent ? 'New password generated and sent via SMS.' : 'New password generated.',
            'username' => $username,
            'password' => $plainPassword,
            'sms_sent' => $smsSent,
            'sms_note' => $smsSent
                ? 'Password was also sent by SMS.'
                : ($smsResult['message'] ?? 'SMS not sent — share the password manually.'),
        ]);
    }

    public function storePosition(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'position_name' => 'required|string|max:120',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]);

        $name = trim($validated['position_name']);
        $exists = SchoolBoardPosition::where('schoolID', $schoolID)
            ->where('position_name', $name)
            ->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'That position already exists.'], 422);
        }

        $position = SchoolBoardPosition::create([
            'schoolID' => $schoolID,
            'position_name' => $name,
            'position_code' => strtoupper(str_replace(' ', '_', substr($name, 0, 40))),
            'sort_order' => $validated['sort_order'] ?? 50,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position created successfully.',
            'position' => [
                'positionID' => $position->positionID,
                'position_name' => $position->position_name,
            ],
        ]);
    }

    private function normalizeCommitteePayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'committeeID', 'committee_code', 'description', 'formed_date',
            'memberID', 'committee_role', 'assigned_date',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    private function ensureDefaultCommittees(int $schoolID): void
    {
        if (!Schema::hasTable('school_board_committees')) {
            return;
        }

        $defaults = [
            ['Finance Committee', 'FINANCE', 'Oversees school budgets, fees, and financial reporting.'],
            ['Academic Affairs Committee', 'ACADEMIC', 'Reviews academic performance and curriculum matters.'],
            ['Discipline & Welfare Committee', 'DISCIPLINE', 'Handles student discipline and welfare issues.'],
            ['Infrastructure & Development', 'INFRA', 'Plans and monitors school facilities and development projects.'],
            ['Audit & Risk Committee', 'AUDIT', 'Reviews internal controls, compliance, and risk.'],
        ];

        foreach ($defaults as [$name, $code, $description]) {
            SchoolBoardCommittee::firstOrCreate(
                [
                    'schoolID' => $schoolID,
                    'committee_name' => $name,
                ],
                [
                    'committee_code' => $code,
                    'description' => $description,
                    'status' => 'Active',
                    'formed_date' => now()->toDateString(),
                ]
            );
        }
    }

    public function committees()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        $this->ensureDefaultCommittees((int) $schoolID);

        $committees = SchoolBoardCommittee::with(['members.position'])
            ->where('schoolID', $schoolID)
            ->orderByRaw("CASE status WHEN 'Active' THEN 1 ELSE 2 END")
            ->orderBy('committee_name')
            ->get();

        $boardMembers = SchoolBoardMember::with('position')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.school_board.committees', [
            'section' => 'committees',
            'title' => 'Committees',
            'subtitle' => 'Organize board committees and assign members to each committee.',
            'menu' => $this->menuItems(),
            'committees' => $committees,
            'boardMembers' => $boardMembers,
        ]);
    }

    public function storeCommittee(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeCommitteePayload($request), [
                'committee_name' => 'required|string|max:150',
                'committee_code' => 'nullable|string|max:50',
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|in:Active,Inactive',
                'formed_date' => 'nullable|date',
            ])->validate();

            $name = trim($validated['committee_name']);
            $exists = SchoolBoardCommittee::where('schoolID', $schoolID)
                ->where('committee_name', $name)
                ->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'That committee already exists.'], 422);
            }

            $committee = SchoolBoardCommittee::create([
                'schoolID' => $schoolID,
                'committee_name' => $name,
                'committee_code' => $validated['committee_code']
                    ?? strtoupper(str_replace(' ', '_', substr($name, 0, 40))),
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'Active',
                'formed_date' => $validated['formed_date'] ?? now()->toDateString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Committee created successfully.',
                'committee' => [
                    'committeeID' => $committee->committeeID,
                    'committee_name' => $committee->committee_name,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create committee: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateCommittee(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeCommitteePayload($request), [
                'committeeID' => 'required|integer',
                'committee_name' => 'required|string|max:150',
                'committee_code' => 'nullable|string|max:50',
                'description' => 'nullable|string|max:1000',
                'status' => 'nullable|in:Active,Inactive',
                'formed_date' => 'nullable|date',
            ])->validate();

            $committee = SchoolBoardCommittee::where('schoolID', $schoolID)
                ->where('committeeID', $validated['committeeID'])
                ->firstOrFail();

            $name = trim($validated['committee_name']);
            $duplicate = SchoolBoardCommittee::where('schoolID', $schoolID)
                ->where('committee_name', $name)
                ->where('committeeID', '!=', $committee->committeeID)
                ->exists();
            if ($duplicate) {
                return response()->json(['success' => false, 'message' => 'That committee name is already used.'], 422);
            }

            $committee->update([
                'committee_name' => $name,
                'committee_code' => $validated['committee_code'] ?? $committee->committee_code,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? $committee->status,
                'formed_date' => $validated['formed_date'] ?? $committee->formed_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Committee updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update committee: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCommittee(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeCommitteePayload($request), [
            'committeeID' => 'required|integer',
        ])->validate();

        $committee = SchoolBoardCommittee::where('schoolID', $schoolID)
            ->where('committeeID', $validated['committeeID'])
            ->firstOrFail();

        SchoolBoardCommitteeMember::where('schoolID', $schoolID)
            ->where('committeeID', $committee->committeeID)
            ->delete();

        $committee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Committee deleted successfully.',
        ]);
    }

    public function assignCommitteeMember(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeCommitteePayload($request), [
                'committeeID' => 'required|integer',
                'memberID' => 'required|integer',
                'committee_role' => 'required|in:Chair,Secretary,Member',
                'assigned_date' => 'nullable|date',
            ])->validate();

            $committee = SchoolBoardCommittee::where('schoolID', $schoolID)
                ->where('committeeID', $validated['committeeID'])
                ->firstOrFail();

            $member = SchoolBoardMember::where('schoolID', $schoolID)
                ->where('memberID', $validated['memberID'])
                ->firstOrFail();

            $existing = SchoolBoardCommitteeMember::where('committeeID', $committee->committeeID)
                ->where('memberID', $member->memberID)
                ->first();

            if ($existing) {
                $existing->update([
                    'committee_role' => $validated['committee_role'],
                    'assigned_date' => $validated['assigned_date'] ?? $existing->assigned_date,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Committee membership updated.',
                ]);
            }

            SchoolBoardCommitteeMember::create([
                'schoolID' => $schoolID,
                'committeeID' => $committee->committeeID,
                'memberID' => $member->memberID,
                'committee_role' => $validated['committee_role'],
                'assigned_date' => $validated['assigned_date'] ?? now()->toDateString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member assigned to committee successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not assign member: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function removeCommitteeMember(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeCommitteePayload($request), [
            'committeeID' => 'required|integer',
            'memberID' => 'required|integer',
        ])->validate();

        $deleted = SchoolBoardCommitteeMember::where('schoolID', $schoolID)
            ->where('committeeID', $validated['committeeID'])
            ->where('memberID', $validated['memberID'])
            ->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Membership not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Member removed from committee.',
        ]);
    }

    public function meetings()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

        $meetings = SchoolBoardMeeting::with(['committee', 'chair', 'attendees'])
            ->where('schoolID', $schoolID)
            ->orderByDesc('meeting_date')
            ->orderByDesc('start_time')
            ->get();

        $committees = SchoolBoardCommittee::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('committee_name')
            ->get();

        $boardMembers = SchoolBoardMember::with('position')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.school_board.meetings', [
            'section' => 'meetings',
            'title' => 'Meetings',
            'subtitle' => 'Schedule and track school board meetings and agendas.',
            'menu' => $this->menuItems(),
            'meetings' => $meetings,
            'committees' => $committees,
            'boardMembers' => $boardMembers,
        ]);
    }

    private function normalizeMeetingPayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'meetingID', 'committeeID', 'chair_memberID', 'start_time', 'end_time',
            'venue', 'agenda', 'notes', 'memberID', 'attendance_status',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        foreach (['start_time', 'end_time'] as $timeKey) {
            if (!empty($data[$timeKey]) && is_string($data[$timeKey]) && preg_match('/^\d{1,2}:\d{2}/', $data[$timeKey])) {
                $parts = explode(':', $data[$timeKey]);
                $data[$timeKey] = sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
            }
        }

        return $data;
    }

    public function storeMeeting(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeMeetingPayload($request), [
                'title' => 'required|string|max:200',
                'meeting_type' => 'required|in:Full Board,Committee',
                'committeeID' => 'nullable|integer',
                'meeting_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                'venue' => 'nullable|string|max:200',
                'agenda' => 'nullable|string|max:5000',
                'status' => 'nullable|in:Scheduled,Ongoing,Completed,Cancelled,Postponed',
                'chair_memberID' => 'nullable|integer',
                'notes' => 'nullable|string|max:2000',
                'attendee_ids' => 'nullable|array',
                'attendee_ids.*' => 'integer',
            ])->validate();

            if (($validated['meeting_type'] ?? '') === 'Committee') {
                if (empty($validated['committeeID'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Select a committee for committee meetings.',
                    ], 422);
                }
            } else {
                $validated['committeeID'] = null;
            }

            if (!empty($validated['committeeID'])) {
                $owns = SchoolBoardCommittee::where('schoolID', $schoolID)
                    ->where('committeeID', $validated['committeeID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid committee.'], 422);
                }
            }

            if (!empty($validated['chair_memberID'])) {
                $ownsChair = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['chair_memberID'])
                    ->exists();
                if (!$ownsChair) {
                    return response()->json(['success' => false, 'message' => 'Invalid chairperson.'], 422);
                }
            }

            $status = $validated['status'] ?? 'Scheduled';
            // Auto-promote if already past start time on create
            $tempMeeting = new SchoolBoardMeeting([
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'] ?? null,
                'status' => $status,
            ]);
            if ($status === 'Scheduled' && $tempMeeting->hasStarted()) {
                $status = 'Ongoing';
            }

            $meeting = SchoolBoardMeeting::create([
                'schoolID' => $schoolID,
                'committeeID' => $validated['committeeID'] ?? null,
                'title' => trim($validated['title']),
                'meeting_type' => $validated['meeting_type'],
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'venue' => $validated['venue'] ?? null,
                'agenda' => $validated['agenda'] ?? null,
                'status' => $status,
                'chair_memberID' => $validated['chair_memberID'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncMeetingAttendees(
                (int) $schoolID,
                (int) $meeting->meetingID,
                $validated['attendee_ids'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Meeting scheduled successfully.',
                'meeting' => [
                    'meetingID' => $meeting->meetingID,
                    'title' => $meeting->title,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not schedule meeting: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateMeeting(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeMeetingPayload($request), [
                'meetingID' => 'required|integer',
                'title' => 'required|string|max:200',
                'meeting_type' => 'required|in:Full Board,Committee',
                'committeeID' => 'nullable|integer',
                'meeting_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                'venue' => 'nullable|string|max:200',
                'agenda' => 'nullable|string|max:5000',
                'status' => 'nullable|in:Scheduled,Ongoing,Completed,Cancelled,Postponed',
                'chair_memberID' => 'nullable|integer',
                'notes' => 'nullable|string|max:2000',
                'attendee_ids' => 'nullable|array',
                'attendee_ids.*' => 'integer',
            ])->validate();

            $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                ->where('meetingID', $validated['meetingID'])
                ->firstOrFail();

            if (($validated['meeting_type'] ?? '') === 'Committee') {
                if (empty($validated['committeeID'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Select a committee for committee meetings.',
                    ], 422);
                }
            } else {
                $validated['committeeID'] = null;
            }

            if (!empty($validated['committeeID'])) {
                $owns = SchoolBoardCommittee::where('schoolID', $schoolID)
                    ->where('committeeID', $validated['committeeID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid committee.'], 422);
                }
            }

            if (!empty($validated['chair_memberID'])) {
                $ownsChair = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['chair_memberID'])
                    ->exists();
                if (!$ownsChair) {
                    return response()->json(['success' => false, 'message' => 'Invalid chairperson.'], 422);
                }
            }

            $status = $validated['status'] ?? $meeting->status;
            $meeting->fill([
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'] ?? null,
            ]);
            if ($status === 'Scheduled' && $meeting->hasStarted()) {
                $status = 'Ongoing';
            }

            $meeting->update([
                'committeeID' => $validated['committeeID'] ?? null,
                'title' => trim($validated['title']),
                'meeting_type' => $validated['meeting_type'],
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'venue' => $validated['venue'] ?? null,
                'agenda' => $validated['agenda'] ?? null,
                'status' => $status,
                'chair_memberID' => $validated['chair_memberID'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncMeetingAttendees(
                (int) $schoolID,
                (int) $meeting->meetingID,
                $validated['attendee_ids'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Meeting updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update meeting: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteMeeting(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeMeetingPayload($request), [
            'meetingID' => 'required|integer',
        ])->validate();

        $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('meetingID', $validated['meetingID'])
            ->firstOrFail();

        SchoolBoardMeetingAttendee::where('schoolID', $schoolID)
            ->where('meetingID', $meeting->meetingID)
            ->delete();

        $meeting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meeting deleted successfully.',
        ]);
    }

    public function updateAttendance(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeMeetingPayload($request), [
            'meetingID' => 'required|integer',
            'memberID' => 'required|integer',
            'attendance_status' => 'required|in:Expected,Present,Absent,Excused',
        ])->validate();

        $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('meetingID', $validated['meetingID'])
            ->firstOrFail();

        $attendee = SchoolBoardMeetingAttendee::where('schoolID', $schoolID)
            ->where('meetingID', $meeting->meetingID)
            ->where('memberID', $validated['memberID'])
            ->firstOrFail();

        $attendee->update([
            'attendance_status' => $validated['attendance_status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated.',
        ]);
    }

    private function syncMeetingAttendees(int $schoolID, int $meetingID, array $memberIds): void
    {
        $memberIds = array_values(array_unique(array_filter(array_map('intval', $memberIds))));

        $validIds = SchoolBoardMember::where('schoolID', $schoolID)
            ->whereIn('memberID', $memberIds)
            ->pluck('memberID')
            ->all();

        SchoolBoardMeetingAttendee::where('schoolID', $schoolID)
            ->where('meetingID', $meetingID)
            ->whereNotIn('memberID', $validIds)
            ->delete();

        foreach ($validIds as $memberID) {
            SchoolBoardMeetingAttendee::firstOrCreate(
                [
                    'meetingID' => $meetingID,
                    'memberID' => $memberID,
                ],
                [
                    'schoolID' => $schoolID,
                    'attendance_status' => 'Expected',
                ]
            );
        }
    }

    public function minutes()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

        $minutes = SchoolBoardMinute::with(['meeting.committee', 'preparer', 'approver'])
            ->where('schoolID', $schoolID)
            ->orderByDesc('updated_at')
            ->get();

        $upcomingMeetings = SchoolBoardMeeting::with('committee')
            ->where('schoolID', $schoolID)
            ->where('status', 'Scheduled')
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->get();

        $recordableMeetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->whereDoesntHave('minute')
            ->orderByDesc('meeting_date')
            ->get();

        $editableMeetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->orderByDesc('meeting_date')
            ->get();

        $boardMembers = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.school_board.minutes', [
            'section' => 'minutes',
            'title' => 'Minutes',
            'subtitle' => 'Record and archive official minutes from board meetings.',
            'menu' => $this->menuItems(),
            'minutes' => $minutes,
            'upcomingMeetings' => $upcomingMeetings,
            'recordableMeetings' => $recordableMeetings,
            'editableMeetings' => $editableMeetings,
            'boardMembers' => $boardMembers,
        ]);
    }

    private function normalizeMinutePayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'minuteID', 'meetingID', 'content', 'decisions_summary',
            'prepared_by', 'approved_by', 'notes',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    public function storeMinute(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeMinutePayload($request), [
                'meetingID' => 'required|integer',
                'title' => 'required|string|max:200',
                'content' => 'nullable|string|max:50000',
                'decisions_summary' => 'nullable|string|max:5000',
                'prepared_by' => 'nullable|integer',
                'status' => 'nullable|in:Draft,Submitted,Approved',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                ->where('meetingID', $validated['meetingID'])
                ->firstOrFail();

            SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);
            $meeting->refresh();

            if ($meeting->isUpcoming() || !$meeting->allowsMinutes()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minutes can only be recorded after the meeting has started (Ongoing or Completed). This meeting is still upcoming.',
                ], 422);
            }

            $exists = SchoolBoardMinute::where('schoolID', $schoolID)
                ->where('meetingID', $meeting->meetingID)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minutes already exist for this meeting. Edit the existing record instead.',
                ], 422);
            }

            if (!empty($validated['prepared_by'])) {
                $owns = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['prepared_by'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid preparer.'], 422);
                }
            }

            $status = $validated['status'] ?? 'Draft';
            $approvedBy = null;
            $approvedAt = null;
            if ($status === 'Approved') {
                $approvedBy = $validated['prepared_by'] ?? null;
                $approvedAt = now();
            }

            $minute = SchoolBoardMinute::create([
                'schoolID' => $schoolID,
                'meetingID' => $meeting->meetingID,
                'title' => trim($validated['title']),
                'content' => $validated['content'] ?? null,
                'decisions_summary' => $validated['decisions_summary'] ?? null,
                'prepared_by' => $validated['prepared_by'] ?? null,
                'approved_by' => $approvedBy,
                'status' => $status,
                'approved_at' => $approvedAt,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Minutes saved successfully.',
                'minute' => [
                    'minuteID' => $minute->minuteID,
                    'title' => $minute->title,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not save minutes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateMinute(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeMinutePayload($request), [
                'minuteID' => 'required|integer',
                'meetingID' => 'required|integer',
                'title' => 'required|string|max:200',
                'content' => 'nullable|string|max:50000',
                'decisions_summary' => 'nullable|string|max:5000',
                'prepared_by' => 'nullable|integer',
                'approved_by' => 'nullable|integer',
                'status' => 'nullable|in:Draft,Submitted,Approved',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            $minute = SchoolBoardMinute::where('schoolID', $schoolID)
                ->where('minuteID', $validated['minuteID'])
                ->firstOrFail();

            $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                ->where('meetingID', $validated['meetingID'])
                ->firstOrFail();

            SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);
            $meeting->refresh();

            if ($meeting->isUpcoming() || !$meeting->allowsMinutes()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minutes cannot be edited while the meeting is still upcoming. Wait until it is Ongoing or Completed.',
                ], 422);
            }

            $duplicate = SchoolBoardMinute::where('schoolID', $schoolID)
                ->where('meetingID', $meeting->meetingID)
                ->where('minuteID', '!=', $minute->minuteID)
                ->exists();
            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another minutes record already exists for that meeting.',
                ], 422);
            }

            if (!empty($validated['prepared_by'])) {
                $owns = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['prepared_by'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid preparer.'], 422);
                }
            }

            if (!empty($validated['approved_by'])) {
                $ownsApprover = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['approved_by'])
                    ->exists();
                if (!$ownsApprover) {
                    return response()->json(['success' => false, 'message' => 'Invalid approver.'], 422);
                }
            }

            $status = $validated['status'] ?? $minute->status;
            $approvedBy = $minute->approved_by;
            $approvedAt = $minute->approved_at;

            if ($status === 'Approved') {
                $approvedBy = $validated['approved_by'] ?? $minute->approved_by ?? $validated['prepared_by'];
                $approvedAt = $minute->approved_at ?? now();
            } elseif (in_array($status, ['Draft', 'Submitted'], true)) {
                $approvedBy = null;
                $approvedAt = null;
            }

            $minute->update([
                'meetingID' => $meeting->meetingID,
                'title' => trim($validated['title']),
                'content' => $validated['content'] ?? null,
                'decisions_summary' => $validated['decisions_summary'] ?? null,
                'prepared_by' => $validated['prepared_by'] ?? null,
                'approved_by' => $approvedBy,
                'status' => $status,
                'approved_at' => $approvedAt,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Minutes updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update minutes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteMinute(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeMinutePayload($request), [
            'minuteID' => 'required|integer',
        ])->validate();

        $minute = SchoolBoardMinute::where('schoolID', $schoolID)
            ->where('minuteID', $validated['minuteID'])
            ->firstOrFail();

        $minute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Minutes deleted successfully.',
        ]);
    }

    public function resolutions()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

        $resolutions = SchoolBoardResolution::with(['meeting.committee', 'proposer', 'seconder', 'assignee'])
            ->where('schoolID', $schoolID)
            ->orderByDesc('resolution_date')
            ->orderByDesc('resolutionID')
            ->get();

        $sourceMeetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->orderByDesc('meeting_date')
            ->get();

        $upcomingMeetings = SchoolBoardMeeting::with('committee')
            ->where('schoolID', $schoolID)
            ->where('status', 'Scheduled')
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->get();

        $boardMembers = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.school_board.resolutions', [
            'section' => 'resolutions',
            'title' => 'Resolutions',
            'subtitle' => 'Capture board resolutions and decisions for follow-up.',
            'menu' => $this->menuItems(),
            'resolutions' => $resolutions,
            'sourceMeetings' => $sourceMeetings,
            'upcomingMeetings' => $upcomingMeetings,
            'boardMembers' => $boardMembers,
        ]);
    }

    private function normalizeResolutionPayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'resolutionID', 'meetingID', 'resolution_number', 'description',
            'proposed_by', 'seconded_by', 'assigned_to',
            'resolution_date', 'implementation_deadline', 'notes',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    private function nextResolutionNumber(int $schoolID): string
    {
        $year = now()->format('Y');
        $prefix = 'RES-' . $year . '-';
        $last = SchoolBoardResolution::where('schoolID', $schoolID)
            ->where('resolution_number', 'like', $prefix . '%')
            ->orderByDesc('resolutionID')
            ->value('resolution_number');

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    public function storeResolution(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeResolutionPayload($request), [
                'meetingID' => 'required|integer',
                'resolution_number' => 'nullable|string|max:50',
                'title' => 'required|string|max:250',
                'description' => 'nullable|string|max:10000',
                'proposed_by' => 'nullable|integer',
                'seconded_by' => 'nullable|integer',
                'assigned_to' => 'nullable|integer',
                'resolution_date' => 'nullable|date',
                'implementation_deadline' => 'nullable|date',
                'status' => 'nullable|in:Proposed,Adopted,Rejected,Deferred,Implemented',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

            $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                ->where('meetingID', $validated['meetingID'])
                ->firstOrFail();

            if (!$meeting->allowsMinutes()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resolutions can only be recorded for Ongoing or Completed meetings.',
                ], 422);
            }

            foreach (['proposed_by', 'seconded_by', 'assigned_to'] as $memberKey) {
                if (empty($validated[$memberKey])) {
                    continue;
                }
                $owns = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated[$memberKey])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid board member selected.'], 422);
                }
            }

            $number = $validated['resolution_number'] ?? $this->nextResolutionNumber((int) $schoolID);
            $dup = SchoolBoardResolution::where('schoolID', $schoolID)
                ->where('resolution_number', $number)
                ->exists();
            if ($dup) {
                return response()->json(['success' => false, 'message' => 'That resolution number already exists.'], 422);
            }

            $resolution = SchoolBoardResolution::create([
                'schoolID' => $schoolID,
                'meetingID' => $meeting->meetingID,
                'resolution_number' => $number,
                'title' => trim($validated['title']),
                'description' => $validated['description'] ?? null,
                'proposed_by' => $validated['proposed_by'] ?? null,
                'seconded_by' => $validated['seconded_by'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'resolution_date' => $validated['resolution_date'] ?? ($meeting->meeting_date?->format('Y-m-d') ?? now()->toDateString()),
                'implementation_deadline' => $validated['implementation_deadline'] ?? null,
                'status' => $validated['status'] ?? 'Proposed',
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resolution saved successfully.',
                'resolution' => [
                    'resolutionID' => $resolution->resolutionID,
                    'resolution_number' => $resolution->resolution_number,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not save resolution: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateResolution(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeResolutionPayload($request), [
                'resolutionID' => 'required|integer',
                'meetingID' => 'required|integer',
                'resolution_number' => 'nullable|string|max:50',
                'title' => 'required|string|max:250',
                'description' => 'nullable|string|max:10000',
                'proposed_by' => 'nullable|integer',
                'seconded_by' => 'nullable|integer',
                'assigned_to' => 'nullable|integer',
                'resolution_date' => 'nullable|date',
                'implementation_deadline' => 'nullable|date',
                'status' => 'nullable|in:Proposed,Adopted,Rejected,Deferred,Implemented',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

            $resolution = SchoolBoardResolution::where('schoolID', $schoolID)
                ->where('resolutionID', $validated['resolutionID'])
                ->firstOrFail();

            $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                ->where('meetingID', $validated['meetingID'])
                ->firstOrFail();

            if (!$meeting->allowsMinutes()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resolutions can only be linked to Ongoing or Completed meetings.',
                ], 422);
            }

            foreach (['proposed_by', 'seconded_by', 'assigned_to'] as $memberKey) {
                if (empty($validated[$memberKey])) {
                    continue;
                }
                $owns = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated[$memberKey])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid board member selected.'], 422);
                }
            }

            $number = $validated['resolution_number'] ?? $resolution->resolution_number;
            if ($number) {
                $dup = SchoolBoardResolution::where('schoolID', $schoolID)
                    ->where('resolution_number', $number)
                    ->where('resolutionID', '!=', $resolution->resolutionID)
                    ->exists();
                if ($dup) {
                    return response()->json(['success' => false, 'message' => 'That resolution number already exists.'], 422);
                }
            }

            $resolution->update([
                'meetingID' => $meeting->meetingID,
                'resolution_number' => $number,
                'title' => trim($validated['title']),
                'description' => $validated['description'] ?? null,
                'proposed_by' => $validated['proposed_by'] ?? null,
                'seconded_by' => $validated['seconded_by'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'resolution_date' => $validated['resolution_date'] ?? $resolution->resolution_date,
                'implementation_deadline' => $validated['implementation_deadline'] ?? null,
                'status' => $validated['status'] ?? $resolution->status,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resolution updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update resolution: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteResolution(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeResolutionPayload($request), [
            'resolutionID' => 'required|integer',
        ])->validate();

        $resolution = SchoolBoardResolution::where('schoolID', $schoolID)
            ->where('resolutionID', $validated['resolutionID'])
            ->firstOrFail();

        $resolution->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resolution deleted successfully.',
        ]);
    }

    public function actionItems()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

        $actionItems = SchoolBoardActionItem::with(['meeting', 'resolution', 'assignee'])
            ->where('schoolID', $schoolID)
            ->orderByRaw("CASE status WHEN 'Open' THEN 1 WHEN 'In Progress' THEN 2 WHEN 'Completed' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->orderByDesc('actionItemID')
            ->get();

        $sourceMeetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->orderByDesc('meeting_date')
            ->get();

        $resolutions = SchoolBoardResolution::where('schoolID', $schoolID)
            ->orderByDesc('resolution_date')
            ->orderByDesc('resolutionID')
            ->get();

        $upcomingMeetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('status', 'Scheduled')
            ->orderBy('meeting_date')
            ->get();

        $boardMembers = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('Admin.school_board.action_items', [
            'section' => 'action_items',
            'title' => 'Action Items',
            'subtitle' => 'Track assigned tasks arising from board meetings and resolutions.',
            'menu' => $this->menuItems(),
            'actionItems' => $actionItems,
            'sourceMeetings' => $sourceMeetings,
            'resolutions' => $resolutions,
            'upcomingMeetings' => $upcomingMeetings,
            'boardMembers' => $boardMembers,
        ]);
    }

    private function normalizeActionItemPayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'actionItemID', 'meetingID', 'resolutionID', 'description',
            'assigned_to', 'due_date', 'notes',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    public function storeActionItem(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeActionItemPayload($request), [
                'meetingID' => 'nullable|integer',
                'resolutionID' => 'nullable|integer',
                'title' => 'required|string|max:250',
                'description' => 'nullable|string|max:5000',
                'assigned_to' => 'nullable|integer',
                'due_date' => 'nullable|date',
                'priority' => 'nullable|in:Low,Medium,High',
                'status' => 'nullable|in:Open,In Progress,Completed,Cancelled',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            if (empty($validated['meetingID']) && empty($validated['resolutionID'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link the action item to a meeting or a resolution.',
                ], 422);
            }

            SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

            $meeting = null;
            if (!empty($validated['meetingID'])) {
                $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                    ->where('meetingID', $validated['meetingID'])
                    ->firstOrFail();
                if (!$meeting->allowsMinutes()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Action items can only be linked to Ongoing or Completed meetings.',
                    ], 422);
                }
            }

            $resolution = null;
            if (!empty($validated['resolutionID'])) {
                $resolution = SchoolBoardResolution::where('schoolID', $schoolID)
                    ->where('resolutionID', $validated['resolutionID'])
                    ->firstOrFail();
                if (!$meeting && $resolution->meetingID) {
                    $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                        ->where('meetingID', $resolution->meetingID)
                        ->first();
                }
            }

            if (!empty($validated['assigned_to'])) {
                $owns = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['assigned_to'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid assignee.'], 422);
                }
            }

            $status = $validated['status'] ?? 'Open';
            $item = SchoolBoardActionItem::create([
                'schoolID' => $schoolID,
                'meetingID' => $meeting?->meetingID ?? ($validated['meetingID'] ?? null),
                'resolutionID' => $resolution?->resolutionID ?? null,
                'title' => trim($validated['title']),
                'description' => $validated['description'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'priority' => $validated['priority'] ?? 'Medium',
                'status' => $status,
                'completed_at' => $status === 'Completed' ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Action item created successfully.',
                'actionItem' => [
                    'actionItemID' => $item->actionItemID,
                    'title' => $item->title,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create action item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateActionItem(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeActionItemPayload($request), [
                'actionItemID' => 'required|integer',
                'meetingID' => 'nullable|integer',
                'resolutionID' => 'nullable|integer',
                'title' => 'required|string|max:250',
                'description' => 'nullable|string|max:5000',
                'assigned_to' => 'nullable|integer',
                'due_date' => 'nullable|date',
                'priority' => 'nullable|in:Low,Medium,High',
                'status' => 'nullable|in:Open,In Progress,Completed,Cancelled',
                'notes' => 'nullable|string|max:2000',
            ])->validate();

            if (empty($validated['meetingID']) && empty($validated['resolutionID'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link the action item to a meeting or a resolution.',
                ], 422);
            }

            SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

            $item = SchoolBoardActionItem::where('schoolID', $schoolID)
                ->where('actionItemID', $validated['actionItemID'])
                ->firstOrFail();

            $meeting = null;
            if (!empty($validated['meetingID'])) {
                $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                    ->where('meetingID', $validated['meetingID'])
                    ->firstOrFail();
                if (!$meeting->allowsMinutes()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Action items can only be linked to Ongoing or Completed meetings.',
                    ], 422);
                }
            }

            $resolution = null;
            if (!empty($validated['resolutionID'])) {
                $resolution = SchoolBoardResolution::where('schoolID', $schoolID)
                    ->where('resolutionID', $validated['resolutionID'])
                    ->firstOrFail();
                if (!$meeting && $resolution->meetingID) {
                    $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
                        ->where('meetingID', $resolution->meetingID)
                        ->first();
                }
            }

            if (!empty($validated['assigned_to'])) {
                $owns = SchoolBoardMember::where('schoolID', $schoolID)
                    ->where('memberID', $validated['assigned_to'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid assignee.'], 422);
                }
            }

            $status = $validated['status'] ?? $item->status;
            $completedAt = $item->completed_at;
            if ($status === 'Completed') {
                $completedAt = $item->completed_at ?? now();
            } elseif (in_array($status, ['Open', 'In Progress', 'Cancelled'], true)) {
                $completedAt = null;
            }

            $item->update([
                'meetingID' => $meeting?->meetingID ?? ($validated['meetingID'] ?? null),
                'resolutionID' => $resolution?->resolutionID ?? null,
                'title' => trim($validated['title']),
                'description' => $validated['description'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'priority' => $validated['priority'] ?? $item->priority,
                'status' => $status,
                'completed_at' => $completedAt,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Action item updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update action item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteActionItem(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeActionItemPayload($request), [
            'actionItemID' => 'required|integer',
        ])->validate();

        $item = SchoolBoardActionItem::where('schoolID', $schoolID)
            ->where('actionItemID', $validated['actionItemID'])
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Action item deleted successfully.',
        ]);
    }

    public function documents()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        $documents = SchoolBoardDocument::with(['meeting', 'resolution', 'committee'])
            ->where('schoolID', $schoolID)
            ->orderByRaw("CASE status WHEN 'Active' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        $meetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->orderByDesc('meeting_date')
            ->limit(100)
            ->get();

        $resolutions = SchoolBoardResolution::where('schoolID', $schoolID)
            ->orderByDesc('resolutionID')
            ->limit(100)
            ->get();

        $committees = SchoolBoardCommittee::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('committee_name')
            ->get();

        return view('Admin.school_board.documents', [
            'section' => 'documents',
            'title' => 'Documents',
            'subtitle' => 'Store and organize board-related documents and attachments.',
            'menu' => $this->menuItems(),
            'documents' => $documents,
            'meetings' => $meetings,
            'resolutions' => $resolutions,
            'committees' => $committees,
        ]);
    }

    private function normalizeDocumentPayload(Request $request): array
    {
        $data = $request->all();
        foreach ([
            'documentID', 'meetingID', 'resolutionID', 'committeeID', 'description',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (is_string($data[$key]) && trim($data[$key]) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    public function storeDocument(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeDocumentPayload($request), [
                'title' => 'required|string|max:250',
                'category' => 'nullable|in:General,Agenda,Minutes,Resolution,Policy,Report,Other',
                'description' => 'nullable|string|max:2000',
                'meetingID' => 'nullable|integer',
                'resolutionID' => 'nullable|integer',
                'committeeID' => 'nullable|integer',
                'status' => 'nullable|in:Active,Archived',
                'document_file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,csv,zip',
            ])->validate();

            if (!empty($validated['meetingID'])) {
                $owns = SchoolBoardMeeting::where('schoolID', $schoolID)
                    ->where('meetingID', $validated['meetingID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid meeting.'], 422);
                }
            }
            if (!empty($validated['resolutionID'])) {
                $owns = SchoolBoardResolution::where('schoolID', $schoolID)
                    ->where('resolutionID', $validated['resolutionID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid resolution.'], 422);
                }
            }
            if (!empty($validated['committeeID'])) {
                $owns = SchoolBoardCommittee::where('schoolID', $schoolID)
                    ->where('committeeID', $validated['committeeID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid committee.'], 422);
                }
            }

            $file = $request->file('document_file');
            $path = $file->store('school_board_documents/' . $schoolID, 'public');

            $doc = SchoolBoardDocument::create([
                'schoolID' => $schoolID,
                'meetingID' => $validated['meetingID'] ?? null,
                'resolutionID' => $validated['resolutionID'] ?? null,
                'committeeID' => $validated['committeeID'] ?? null,
                'title' => trim($validated['title']),
                'category' => $validated['category'] ?? 'General',
                'description' => $validated['description'] ?? null,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'status' => $validated['status'] ?? 'Active',
                'uploaded_by' => Session::get('userID') ?? Session::get('staffID') ?? null,
                'uploaded_by_name' => Session::get('user_name')
                    ?? Session::get('fullname')
                    ?? Session::get('name')
                    ?? Session::get('user_type'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => [
                    'documentID' => $doc->documentID,
                    'title' => $doc->title,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not upload document: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateDocument(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $validated = validator($this->normalizeDocumentPayload($request), [
                'documentID' => 'required|integer',
                'title' => 'required|string|max:250',
                'category' => 'nullable|in:General,Agenda,Minutes,Resolution,Policy,Report,Other',
                'description' => 'nullable|string|max:2000',
                'meetingID' => 'nullable|integer',
                'resolutionID' => 'nullable|integer',
                'committeeID' => 'nullable|integer',
                'status' => 'nullable|in:Active,Archived',
                'document_file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,csv,zip',
            ])->validate();

            $doc = SchoolBoardDocument::where('schoolID', $schoolID)
                ->where('documentID', $validated['documentID'])
                ->firstOrFail();

            if (!empty($validated['meetingID'])) {
                $owns = SchoolBoardMeeting::where('schoolID', $schoolID)
                    ->where('meetingID', $validated['meetingID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid meeting.'], 422);
                }
            }
            if (!empty($validated['resolutionID'])) {
                $owns = SchoolBoardResolution::where('schoolID', $schoolID)
                    ->where('resolutionID', $validated['resolutionID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid resolution.'], 422);
                }
            }
            if (!empty($validated['committeeID'])) {
                $owns = SchoolBoardCommittee::where('schoolID', $schoolID)
                    ->where('committeeID', $validated['committeeID'])
                    ->exists();
                if (!$owns) {
                    return response()->json(['success' => false, 'message' => 'Invalid committee.'], 422);
                }
            }

            $filePath = $doc->file_path;
            $original = $doc->original_filename;
            $mime = $doc->mime_type;
            $size = $doc->file_size;

            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $newPath = $file->store('school_board_documents/' . $schoolID, 'public');
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $filePath = $newPath;
                $original = $file->getClientOriginalName();
                $mime = $file->getClientMimeType();
                $size = $file->getSize();
            }

            $doc->update([
                'meetingID' => $validated['meetingID'] ?? null,
                'resolutionID' => $validated['resolutionID'] ?? null,
                'committeeID' => $validated['committeeID'] ?? null,
                'title' => trim($validated['title']),
                'category' => $validated['category'] ?? $doc->category,
                'description' => $validated['description'] ?? null,
                'file_path' => $filePath,
                'original_filename' => $original,
                'mime_type' => $mime,
                'file_size' => $size,
                'status' => $validated['status'] ?? $doc->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update document: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDocument(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = validator($this->normalizeDocumentPayload($request), [
            'documentID' => 'required|integer',
        ])->validate();

        $doc = SchoolBoardDocument::where('schoolID', $schoolID)
            ->where('documentID', $validated['documentID'])
            ->firstOrFail();

        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully.',
        ]);
    }

    public function downloadDocument($documentID)
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $doc = SchoolBoardDocument::where('schoolID', $schoolID)
            ->where('documentID', $documentID)
            ->firstOrFail();

        if (!$doc->file_path || !Storage::disk('public')->exists($doc->file_path)) {
            return redirect()
                ->route('admin.school_board.documents')
                ->with('error', 'File not found on disk.');
        }

        $downloadName = $doc->original_filename ?: basename($doc->file_path);

        return Storage::disk('public')->download($doc->file_path, $downloadName);
    }

    public function dashboard()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

        $from = now()->subMonths(3)->toDateString();
        $to = now()->toDateString();
        $data = $this->buildBoardReportData((int) $schoolID, $from, $to);

        return view('Admin.school_board.reports', array_merge($data, [
            'section' => 'dashboard',
            'title' => 'Board Dashboard',
            'subtitle' => 'Overview of board activity, upcoming meetings, and open action items.',
            'menu' => $this->menuItems(),
            'from' => $from,
            'to' => $to,
            'isDashboard' => true,
        ]));
    }

    public function reports(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $schoolID);

        $from = $request->input('from') ?: now()->startOfYear()->toDateString();
        $to = $request->input('to') ?: now()->toDateString();

        try {
            $from = \Carbon\Carbon::parse($from)->toDateString();
            $to = \Carbon\Carbon::parse($to)->toDateString();
        } catch (\Throwable $e) {
            $from = now()->startOfYear()->toDateString();
            $to = now()->toDateString();
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $data = $this->buildBoardReportData((int) $schoolID, $from, $to);

        return view('Admin.school_board.reports', array_merge($data, [
            'section' => 'reports',
            'title' => 'Reports',
            'subtitle' => 'Generate summaries and reports for school board governance.',
            'menu' => $this->menuItems(),
            'from' => $from,
            'to' => $to,
            'isDashboard' => false,
        ]));
    }

    private function buildBoardReportData(int $schoolID, string $from, string $to): array
    {
        $members = SchoolBoardMember::with('position')
            ->where('schoolID', $schoolID)
            ->get();

        $committees = Schema::hasTable('school_board_committees')
            ? SchoolBoardCommittee::withCount('members')->where('schoolID', $schoolID)->orderBy('committee_name')->get()
            : collect();

        $meetingsQuery = SchoolBoardMeeting::with(['committee', 'chair'])
            ->where('schoolID', $schoolID)
            ->whereBetween('meeting_date', [$from, $to]);

        $meetings = (clone $meetingsQuery)->orderByDesc('meeting_date')->get();

        $upcomingMeetings = SchoolBoardMeeting::with('committee')
            ->where('schoolID', $schoolID)
            ->where('status', 'Scheduled')
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        $ongoingMeetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('status', 'Ongoing')
            ->orderByDesc('meeting_date')
            ->get();

        $minutes = Schema::hasTable('school_board_minutes')
            ? SchoolBoardMinute::with('meeting')
                ->where('schoolID', $schoolID)
                ->whereHas('meeting', function ($q) use ($from, $to) {
                    $q->whereBetween('meeting_date', [$from, $to]);
                })
                ->orderByDesc('updated_at')
                ->get()
            : collect();

        $resolutions = Schema::hasTable('school_board_resolutions')
            ? SchoolBoardResolution::with(['meeting', 'assignee'])
                ->where('schoolID', $schoolID)
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('resolution_date', [$from, $to])
                        ->orWhere(function ($q2) use ($from, $to) {
                            $q2->whereNull('resolution_date')
                                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                        });
                })
                ->orderByDesc('resolution_date')
                ->get()
            : collect();

        $actionItems = Schema::hasTable('school_board_action_items')
            ? SchoolBoardActionItem::with(['assignee', 'meeting', 'resolution'])
                ->where('schoolID', $schoolID)
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('due_date', [$from, $to])
                        ->orWhere(function ($q2) use ($from, $to) {
                            $q2->whereNull('due_date')
                                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                        });
                })
                ->orderBy('due_date')
                ->get()
            : collect();

        $overdueActions = Schema::hasTable('school_board_action_items')
            ? SchoolBoardActionItem::with('assignee')
                ->where('schoolID', $schoolID)
                ->whereNotIn('status', ['Completed', 'Cancelled'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString())
                ->orderBy('due_date')
                ->get()
            : collect();

        $documents = Schema::hasTable('school_board_documents')
            ? SchoolBoardDocument::where('schoolID', $schoolID)
                ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $summary = [
            'members_total' => $members->count(),
            'members_active' => $members->where('status', 'Active')->count(),
            'committees_total' => $committees->count(),
            'committees_active' => $committees->where('status', 'Active')->count(),
            'meetings_total' => $meetings->count(),
            'meetings_upcoming' => $upcomingMeetings->count(),
            'meetings_ongoing' => $ongoingMeetings->count(),
            'meetings_completed' => $meetings->where('status', 'Completed')->count(),
            'minutes_total' => $minutes->count(),
            'minutes_approved' => $minutes->where('status', 'Approved')->count(),
            'minutes_draft' => $minutes->where('status', 'Draft')->count(),
            'resolutions_total' => $resolutions->count(),
            'resolutions_adopted' => $resolutions->where('status', 'Adopted')->count(),
            'resolutions_implemented' => $resolutions->where('status', 'Implemented')->count(),
            'actions_total' => $actionItems->count(),
            'actions_open' => $actionItems->whereIn('status', ['Open', 'In Progress'])->count(),
            'actions_completed' => $actionItems->where('status', 'Completed')->count(),
            'actions_overdue' => $overdueActions->count(),
            'documents_total' => $documents->count(),
        ];

        return [
            'summary' => $summary,
            'members' => $members,
            'committees' => $committees,
            'meetings' => $meetings->take(15),
            'upcomingMeetings' => $upcomingMeetings,
            'ongoingMeetings' => $ongoingMeetings,
            'minutes' => $minutes->take(10),
            'resolutions' => $resolutions->take(15),
            'actionItems' => $actionItems->take(15),
            'overdueActions' => $overdueActions,
            'documents' => $documents->take(10),
        ];
    }

    /** @return array<string, array<string, string>> */
    private function availableBoardPermissions(): array
    {
        return config('board_portal_permissions', []);
    }

    /**
     * Map board position → default system portal role and assign if missing.
     * Chair / Vice Chair → CHAIR (create meetings, committees, etc.)
     * Secretary → SECRETARY (records: minutes, resolutions, …)
     */
    private function syncPortalRoleFromPosition(?SchoolBoardMember $member): void
    {
        if (!$member || !Schema::hasTable('school_board_roles') || !Schema::hasTable('school_board_member_roles')) {
            return;
        }

        $schoolID = (int) $member->schoolID;
        $this->ensureDefaultBoardRoles($schoolID);

        if (!$member->relationLoaded('position')) {
            $member->load('position');
        }

        $positionCode = strtoupper((string) ($member->position->position_code ?? 'MEMBER'));
        $roleCode = match ($positionCode) {
            'CHAIR', 'VICE_CHAIR' => 'CHAIR',
            'SECRETARY' => 'SECRETARY',
            'TREASURER' => 'TREASURER',
            default => 'MEMBER',
        };

        $role = SchoolBoardRole::where('schoolID', $schoolID)
            ->where('role_code', $roleCode)
            ->where('is_system', true)
            ->where('is_active', true)
            ->first();

        if (!$role) {
            return;
        }

        $exists = DB::table('school_board_member_roles')
            ->where('memberID', $member->memberID)
            ->where('boardRoleID', $role->boardRoleID)
            ->exists();

        if (!$exists) {
            DB::table('school_board_member_roles')->insert([
                'schoolID' => $schoolID,
                'memberID' => $member->memberID,
                'boardRoleID' => $role->boardRoleID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function ensureDefaultBoardRoles(int $schoolID): void
    {
        if (!Schema::hasTable('school_board_roles')) {
            return;
        }

        $defaults = [
            [
                'role_name' => 'Board Chair Access',
                'role_code' => 'CHAIR',
                'description' => 'Full board portal access for chair / vice chair.',
                'sort_order' => 1,
                'permissions' => [
                    'board_portal.access', 'board_portal.dashboard',
                    'board_portal.committees.view', 'board_portal.committees.manage',
                    'board_portal.meetings.view', 'board_portal.meetings.view_all', 'board_portal.meetings.manage', 'board_portal.meetings.comment',
                    'board_portal.minutes.view', 'board_portal.minutes.view_all', 'board_portal.minutes.manage',
                    'board_portal.resolutions.view', 'board_portal.resolutions.view_all', 'board_portal.resolutions.manage',
                    'board_portal.action_items.view', 'board_portal.action_items.view_all',
                    'board_portal.action_items.update_own', 'board_portal.action_items.update_any',
                    'board_portal.action_items.manage',
                    'board_portal.documents.view', 'board_portal.documents.download', 'board_portal.documents.manage',
                    'board_portal.members.view', 'board_portal.members.message',
                    'board_portal.messages.view', 'board_portal.messages.compose',
                ],
            ],
            [
                'role_name' => 'Board Secretary Access',
                'role_code' => 'SECRETARY',
                'description' => 'Keep records: meetings, minutes, resolutions, and follow-ups.',
                'sort_order' => 2,
                'permissions' => [
                    'board_portal.access', 'board_portal.dashboard',
                    'board_portal.committees.view', 'board_portal.committees.manage',
                    'board_portal.meetings.view', 'board_portal.meetings.view_all', 'board_portal.meetings.manage', 'board_portal.meetings.comment',
                    'board_portal.minutes.view', 'board_portal.minutes.view_all', 'board_portal.minutes.manage',
                    'board_portal.resolutions.view', 'board_portal.resolutions.view_all', 'board_portal.resolutions.manage',
                    'board_portal.action_items.view', 'board_portal.action_items.view_all',
                    'board_portal.action_items.update_own', 'board_portal.action_items.update_any',
                    'board_portal.action_items.manage',
                    'board_portal.documents.view', 'board_portal.documents.download', 'board_portal.documents.manage',
                    'board_portal.members.view', 'board_portal.members.message',
                    'board_portal.messages.view', 'board_portal.messages.compose',
                ],
            ],
            [
                'role_name' => 'Board Treasurer Access',
                'role_code' => 'TREASURER',
                'description' => 'Meetings, resolutions, documents, and actions.',
                'sort_order' => 3,
                'permissions' => [
                    'board_portal.access', 'board_portal.dashboard',
                    'board_portal.committees.view',
                    'board_portal.meetings.view', 'board_portal.meetings.view_all', 'board_portal.meetings.comment',
                    'board_portal.minutes.view',
                    'board_portal.resolutions.view', 'board_portal.resolutions.view_all',
                    'board_portal.action_items.view', 'board_portal.action_items.view_all',
                    'board_portal.action_items.update_own',
                    'board_portal.documents.view', 'board_portal.documents.download', 'board_portal.documents.manage',
                    'board_portal.members.view',
                    'board_portal.messages.view',
                ],
            ],
            [
                'role_name' => 'Board Member Access',
                'role_code' => 'MEMBER',
                'description' => 'Standard member: own meetings, actions, and documents.',
                'sort_order' => 10,
                'permissions' => [
                    'board_portal.access', 'board_portal.dashboard',
                    'board_portal.committees.view',
                    'board_portal.meetings.view', 'board_portal.meetings.comment',
                    'board_portal.minutes.view',
                    'board_portal.resolutions.view',
                    'board_portal.action_items.view', 'board_portal.action_items.update_own',
                    'board_portal.documents.view', 'board_portal.documents.download',
                    'board_portal.messages.view',
                ],
            ],
        ];

        $catalog = $this->availableBoardPermissions();
        $categoryByName = [];
        foreach ($catalog as $category => $perms) {
            foreach ($perms as $name => $label) {
                $categoryByName[$name] = $category;
            }
        }

        foreach ($defaults as $def) {
            $role = SchoolBoardRole::firstOrCreate(
                [
                    'schoolID' => $schoolID,
                    'role_name' => $def['role_name'],
                ],
                [
                    'role_code' => $def['role_code'],
                    'description' => $def['description'],
                    'is_system' => true,
                    'is_active' => true,
                    'sort_order' => $def['sort_order'],
                ]
            );

            // Seed missing permissions (keeps existing custom roles intact; adds new manage perms to system roles)
            foreach ($def['permissions'] as $permName) {
                SchoolBoardRolePermission::firstOrCreate(
                    [
                        'boardRoleID' => $role->boardRoleID,
                        'name' => $permName,
                    ],
                    [
                        'permission_category' => $categoryByName[$permName] ?? null,
                    ]
                );
            }
        }
    }

    public function roles()
    {
        if ($redirect = $this->authorizeBoardAccess()) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'School session missing.');
        }

        if (!Schema::hasTable('school_board_roles')) {
            return view('Admin.school_board.roles', [
                'section' => 'roles',
                'title' => 'Roles & Permissions',
                'subtitle' => 'Run database migration to enable board roles.',
                'menu' => $this->menuItems(),
                'roles' => collect(),
                'members' => collect(),
                'permissionCatalog' => $this->availableBoardPermissions(),
                'tablesReady' => false,
            ]);
        }

        $this->ensureDefaultBoardRoles((int) $schoolID);

        $members = SchoolBoardMember::with(['position', 'boardRoles'])
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Ensure Chair / Secretary / etc. have matching portal roles (create meetings, keep records)
        foreach ($members as $boardMember) {
            $this->syncPortalRoleFromPosition($boardMember);
        }
        $members->load('boardRoles');

        $roles = SchoolBoardRole::with(['permissions', 'members'])
            ->where('schoolID', $schoolID)
            ->orderBy('sort_order')
            ->orderBy('role_name')
            ->get();

        return view('Admin.school_board.roles', [
            'section' => 'roles',
            'title' => 'Roles & Permissions',
            'subtitle' => 'Create board roles, set portal permissions, and assign them to members.',
            'menu' => $this->menuItems(),
            'roles' => $roles,
            'members' => $members,
            'permissionCatalog' => $this->availableBoardPermissions(),
            'tablesReady' => true,
        ]);
    }

    public function storeBoardRole(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role_name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
        ]);

        $name = trim($validated['role_name']);
        $exists = SchoolBoardRole::where('schoolID', $schoolID)->where('role_name', $name)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'That role already exists.'], 422);
        }

        $role = SchoolBoardRole::create([
            'schoolID' => $schoolID,
            'role_name' => $name,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'is_active' => true,
            'sort_order' => 50,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created. Select permissions and save.',
            'boardRoleID' => $role->boardRoleID,
        ]);
    }

    public function updateBoardRolePermissions(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'boardRoleID' => 'required|integer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|max:120',
            'role_name' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $role = SchoolBoardRole::where('schoolID', $schoolID)
            ->where('boardRoleID', $validated['boardRoleID'])
            ->firstOrFail();

        if (!empty($validated['role_name'])) {
            $newName = trim($validated['role_name']);
            $dup = SchoolBoardRole::where('schoolID', $schoolID)
                ->where('role_name', $newName)
                ->where('boardRoleID', '!=', $role->boardRoleID)
                ->exists();
            if ($dup) {
                return response()->json(['success' => false, 'message' => 'Another role already uses that name.'], 422);
            }
            $role->role_name = $newName;
        }
        if (array_key_exists('description', $validated)) {
            $role->description = $validated['description'];
        }
        if (array_key_exists('is_active', $validated)) {
            $role->is_active = (bool) $validated['is_active'];
        }
        $role->save();

        $catalog = $this->availableBoardPermissions();
        $allowed = [];
        $categoryByName = [];
        foreach ($catalog as $category => $perms) {
            foreach ($perms as $name => $label) {
                $allowed[] = $name;
                $categoryByName[$name] = $category;
            }
        }

        $selected = array_values(array_unique(array_intersect($validated['permissions'] ?? [], $allowed)));

        SchoolBoardRolePermission::where('boardRoleID', $role->boardRoleID)->delete();
        foreach ($selected as $permName) {
            SchoolBoardRolePermission::create([
                'boardRoleID' => $role->boardRoleID,
                'name' => $permName,
                'permission_category' => $categoryByName[$permName] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role permissions saved.',
            'count' => count($selected),
        ]);
    }

    public function deleteBoardRole(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'boardRoleID' => 'required|integer',
        ]);

        $role = SchoolBoardRole::where('schoolID', $schoolID)
            ->where('boardRoleID', $validated['boardRoleID'])
            ->firstOrFail();

        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be deleted. You can deactivate them instead.',
            ], 422);
        }

        DB::transaction(function () use ($role) {
            DB::table('school_board_member_roles')->where('boardRoleID', $role->boardRoleID)->delete();
            SchoolBoardRolePermission::where('boardRoleID', $role->boardRoleID)->delete();
            $role->delete();
        });

        return response()->json(['success' => true, 'message' => 'Role deleted.']);
    }

    public function assignMemberRole(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'memberID' => 'required|integer',
            'boardRoleID' => 'required|integer',
        ]);

        $member = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('memberID', $validated['memberID'])
            ->firstOrFail();

        $role = SchoolBoardRole::where('schoolID', $schoolID)
            ->where('boardRoleID', $validated['boardRoleID'])
            ->where('is_active', true)
            ->firstOrFail();

        $exists = DB::table('school_board_member_roles')
            ->where('memberID', $member->memberID)
            ->where('boardRoleID', $role->boardRoleID)
            ->exists();

        if (!$exists) {
            DB::table('school_board_member_roles')->insert([
                'schoolID' => $schoolID,
                'memberID' => $member->memberID,
                'boardRoleID' => $role->boardRoleID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role assigned to ' . $member->full_name . '.',
        ]);
    }

    public function removeMemberRole(Request $request)
    {
        if ($redirect = $this->authorizeBoardAccess($request)) {
            return $redirect;
        }

        $schoolID = $this->schoolId();
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'memberID' => 'required|integer',
            'boardRoleID' => 'required|integer',
        ]);

        DB::table('school_board_member_roles')
            ->where('schoolID', $schoolID)
            ->where('memberID', $validated['memberID'])
            ->where('boardRoleID', $validated['boardRoleID'])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Role removed from member.']);
    }
}
