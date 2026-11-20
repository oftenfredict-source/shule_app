<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolBoardActionItem;
use App\Models\SchoolBoardCommittee;
use App\Models\SchoolBoardDocument;
use App\Models\SchoolBoardMeeting;
use App\Models\SchoolBoardMeetingAttendee;
use App\Models\SchoolBoardMeetingComment;
use App\Models\SchoolBoardMember;
use App\Models\SchoolBoardMessage;
use App\Models\SchoolBoardMessageRecipient;
use App\Models\SchoolBoardMinute;
use App\Models\SchoolBoardResolution;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class BoardPortalController extends Controller
{
    protected function authorizeMember(?Request $request = null)
    {
        if (Session::get('user_type') !== 'BoardMember' || !Session::get('boardMemberID')) {
            if ($request && ($request->ajax() || $request->expectsJson() || $request->wantsJson())) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            return redirect()->route('login')->with('error', 'Please sign in as a board member.');
        }

        return null;
    }

    protected function currentMember(): ?SchoolBoardMember
    {
        $memberID = (int) Session::get('boardMemberID');
        $schoolID = (int) Session::get('schoolID');
        if (!$memberID || !$schoolID) {
            return null;
        }

        return SchoolBoardMember::with(['position', 'committees', 'boardRoles.permissions'])
            ->where('memberID', $memberID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();
    }

    /**
     * Portal capabilities from assigned board roles (permissions),
     * falling back to position_code defaults when no roles are assigned.
     */
    protected function capabilities(SchoolBoardMember $member): array
    {
        $code = $member->position_code ?: 'MEMBER';
        $permissions = $member->boardPermissionNames();
        $hasRoles = count($permissions) > 0;

        $has = function (string $perm) use ($permissions, $hasRoles): bool {
            return $hasRoles && in_array($perm, $permissions, true);
        };

        $isChairLike = in_array($code, ['CHAIR', 'VICE_CHAIR'], true);
        $isSecretary = $code === 'SECRETARY';
        $canManageByPosition = $isChairLike || $isSecretary;

        $pos = [
            'is_officer' => in_array($code, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'TREASURER', 'HEAD_EXOFFICIO'], true),
            'is_chair' => $isChairLike,
            'is_secretary' => $isSecretary,
            'is_treasurer' => $code === 'TREASURER',
            'see_all_meetings' => in_array($code, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'TREASURER', 'HEAD_EXOFFICIO'], true),
            'see_all_minutes' => in_array($code, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'HEAD_EXOFFICIO'], true),
            'see_all_resolutions' => in_array($code, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'TREASURER', 'HEAD_EXOFFICIO'], true),
            'see_all_action_items' => in_array($code, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'TREASURER'], true),
            'see_all_documents' => in_array($code, ['CHAIR', 'VICE_CHAIR', 'SECRETARY', 'TREASURER', 'HEAD_EXOFFICIO'], true),
            'focus_minutes' => $isSecretary,
            'focus_finance_docs' => $code === 'TREASURER',
            'can_update_own_actions' => true,
            'can_update_any_actions' => $canManageByPosition,
            'can_manage_committees' => $canManageByPosition,
            'can_manage_meetings' => $canManageByPosition,
            'can_manage_minutes' => $canManageByPosition,
            'can_manage_resolutions' => $canManageByPosition,
            'can_manage_action_items' => $canManageByPosition,
            'can_manage_documents' => $canManageByPosition || $code === 'TREASURER',
            'can_view_committees' => true,
            'can_view_members' => $canManageByPosition || in_array($code, ['TREASURER', 'HEAD_EXOFFICIO'], true),
            'can_message_members' => $canManageByPosition,
            'can_view_messages' => true,
            'can_compose_messages' => $canManageByPosition,
            'can_comment_meetings' => true,
        ];

        if ($hasRoles) {
            $base = [
                'position_code' => $code,
                'permissions' => $permissions,
                'uses_roles' => true,
                'is_officer' => $has('board_portal.meetings.view_all') || $has('board_portal.minutes.view_all'),
                'is_chair' => $pos['is_chair'],
                'is_secretary' => $pos['is_secretary'],
                'is_treasurer' => $pos['is_treasurer'],
                'see_all_meetings' => $has('board_portal.meetings.view_all'),
                'see_all_minutes' => $has('board_portal.minutes.view_all'),
                'see_all_resolutions' => $has('board_portal.resolutions.view_all'),
                'see_all_action_items' => $has('board_portal.action_items.view_all'),
                'see_all_documents' => $has('board_portal.documents.view'),
                'focus_minutes' => $has('board_portal.minutes.manage') || ($has('board_portal.minutes.view_all') && $pos['is_secretary']),
                'focus_finance_docs' => $pos['is_treasurer'],
                'can_update_own_actions' => $has('board_portal.action_items.update_own') || $has('board_portal.action_items.update_any'),
                'can_update_any_actions' => $has('board_portal.action_items.update_any'),
                'can_view_meetings' => $has('board_portal.meetings.view') || $has('board_portal.meetings.view_all') || $has('board_portal.meetings.manage'),
                'can_view_minutes' => $has('board_portal.minutes.view') || $has('board_portal.minutes.view_all') || $has('board_portal.minutes.manage'),
                'can_view_resolutions' => $has('board_portal.resolutions.view') || $has('board_portal.resolutions.view_all') || $has('board_portal.resolutions.manage'),
                'can_view_action_items' => $has('board_portal.action_items.view') || $has('board_portal.action_items.view_all') || $has('board_portal.action_items.manage'),
                'can_view_documents' => $has('board_portal.documents.view') || $has('board_portal.documents.manage'),
                'can_download_documents' => $has('board_portal.documents.download') || $has('board_portal.documents.manage'),
                'can_view_committees' => $has('board_portal.committees.view') || $has('board_portal.committees.manage'),
                'can_manage_committees' => $has('board_portal.committees.manage'),
                'can_manage_meetings' => $has('board_portal.meetings.manage'),
                'can_manage_minutes' => $has('board_portal.minutes.manage'),
                'can_manage_resolutions' => $has('board_portal.resolutions.manage'),
                'can_manage_action_items' => $has('board_portal.action_items.manage'),
                'can_manage_documents' => $has('board_portal.documents.manage'),
                'can_view_members' => $has('board_portal.members.view') || $has('board_portal.members.message'),
                'can_message_members' => $has('board_portal.members.message'),
                'can_view_messages' => $has('board_portal.messages.view') || $has('board_portal.messages.compose'),
                'can_compose_messages' => $has('board_portal.messages.compose'),
                'can_comment_meetings' => $has('board_portal.meetings.comment')
                    || $has('board_portal.meetings.view')
                    || $has('board_portal.meetings.view_all')
                    || $has('board_portal.meetings.manage'),
            ];
        } else {
            $base = array_merge($pos, [
                'position_code' => $code,
                'permissions' => [],
                'uses_roles' => false,
                'can_view_meetings' => true,
                'can_view_minutes' => true,
                'can_view_resolutions' => true,
                'can_view_action_items' => true,
                'can_view_documents' => true,
                'can_download_documents' => true,
            ]);
        }

        $menu = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'board.portal.dashboard', 'icon' => 'fa-dashboard'],
        ];
        if (!empty($base['can_view_messages'])) {
            $menu[] = ['key' => 'messages', 'label' => 'Messages', 'route' => 'board.portal.messages', 'icon' => 'fa-envelope'];
        }
        if (!empty($base['can_view_members'])) {
            $menu[] = ['key' => 'members', 'label' => 'Members', 'route' => 'board.portal.members', 'icon' => 'fa-users'];
        }
        if ($base['can_view_committees']) {
            $menu[] = ['key' => 'committees', 'label' => 'Committees', 'route' => 'board.portal.committees', 'icon' => 'fa-sitemap'];
        }
        if ($base['can_view_meetings']) {
            $menu[] = ['key' => 'meetings', 'label' => 'Meetings', 'route' => 'board.portal.meetings', 'icon' => 'fa-calendar'];
        }
        if ($base['can_view_minutes']) {
            $menu[] = ['key' => 'minutes', 'label' => 'Minutes', 'route' => 'board.portal.minutes', 'icon' => 'fa-file-text-o'];
        }
        if ($base['can_view_resolutions']) {
            $menu[] = ['key' => 'resolutions', 'label' => 'Resolutions', 'route' => 'board.portal.resolutions', 'icon' => 'fa-gavel'];
        }
        if ($base['can_view_action_items']) {
            $menu[] = ['key' => 'action_items', 'label' => 'Action Items', 'route' => 'board.portal.action_items', 'icon' => 'fa-check-square-o'];
        }
        if ($base['can_view_documents']) {
            $menu[] = ['key' => 'documents', 'label' => 'Documents', 'route' => 'board.portal.documents', 'icon' => 'fa-folder-open-o'];
        }

        $base['menu'] = $menu;

        return $base;
    }

    protected function requireCapability(SchoolBoardMember $member, string $flag, ?Request $request = null)
    {
        $caps = $this->capabilities($member);
        if (!empty($caps[$flag])) {
            return null;
        }

        $msg = 'You do not have permission for this action.';
        if ($request && ($request->ajax() || $request->expectsJson() || $request->wantsJson())) {
            return response()->json(['success' => false, 'message' => $msg], 403);
        }

        return back()->with('error', $msg);
    }

    protected function unreadMessageCount(SchoolBoardMember $member): int
    {
        return SchoolBoardMessageRecipient::where('schoolID', $member->schoolID)
            ->where('memberID', $member->memberID)
            ->whereNull('read_at')
            ->count();
    }

    private function portalView(string $view, array $data = [])
    {
        $member = $this->currentMember();
        if (!$member) {
            Session::flush();

            return redirect()->route('login')->with('error', 'Board member account is inactive or missing.');
        }

        $caps = $this->capabilities($member);
        $school = School::find($member->schoolID);
        $unreadMessages = !empty($caps['can_view_messages']) ? $this->unreadMessageCount($member) : 0;

        $menu = $caps['menu'];
        foreach ($menu as &$item) {
            if (($item['key'] ?? '') === 'messages' && $unreadMessages > 0) {
                $item['badge'] = $unreadMessages;
            }
        }
        unset($item);

        return view($view, array_merge($data, [
            'member' => $member,
            'caps' => $caps,
            'menu' => $menu,
            'school' => $school,
            'schoolName' => $school->school_name ?? 'School Board',
            'unreadMessages' => $unreadMessages,
        ]));
    }

    protected function activeMembers(int $schoolID)
    {
        return SchoolBoardMember::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
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
                ['meetingID' => $meetingID, 'memberID' => $memberID],
                ['schoolID' => $schoolID, 'attendance_status' => 'Expected']
            );
        }
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

    public function dashboard()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login')->with('error', 'Board member not found.');
        }

        $schoolID = (int) $member->schoolID;
        $caps = $this->capabilities($member);
        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);

        $meetingsQuery = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->with(['committee', 'chair'])
            ->orderByDesc('meeting_date')
            ->orderByDesc('start_time');

        if (!$caps['see_all_meetings']) {
            $meetingsQuery->where(function ($q) use ($member) {
                $q->where('chair_memberID', $member->memberID)
                    ->orWhereHas('attendees', function ($aq) use ($member) {
                        $aq->where('school_board_meeting_attendees.memberID', $member->memberID);
                    });
            });
        }

        $upcomingMeetings = (clone $meetingsQuery)
            ->whereIn('status', ['Scheduled', 'Ongoing'])
            ->limit(5)
            ->get();

        $myActions = SchoolBoardActionItem::where('schoolID', $schoolID)
            ->where('assigned_to', $member->memberID)
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $openActionsCount = SchoolBoardActionItem::where('schoolID', $schoolID)
            ->when(!$caps['see_all_action_items'], fn ($q) => $q->where('assigned_to', $member->memberID))
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();

        $pendingMinutes = collect();
        if ($caps['can_manage_minutes'] || $caps['is_secretary'] || $caps['is_chair']) {
            $pendingMinutes = SchoolBoardMinute::where('schoolID', $schoolID)
                ->whereIn('status', ['Draft', 'Pending Approval', 'Submitted'])
                ->with('meeting')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get();
        }

        $recentResolutions = SchoolBoardResolution::where('schoolID', $schoolID)
            ->when(!$caps['see_all_resolutions'], function ($q) use ($member) {
                $q->where(function ($inner) use ($member) {
                    $inner->where('assigned_to', $member->memberID)
                        ->orWhere('proposed_by', $member->memberID)
                        ->orWhere('seconded_by', $member->memberID);
                });
            })
            ->orderByDesc('resolution_date')
            ->limit(5)
            ->get();

        $stats = [
            'upcoming_meetings' => (clone $meetingsQuery)->whereIn('status', ['Scheduled', 'Ongoing'])->count(),
            'open_actions' => $openActionsCount,
            'my_actions' => $myActions->count(),
            'documents' => SchoolBoardDocument::where('schoolID', $schoolID)->count(),
            'committees' => $member->committees->count(),
        ];

        $roleBlurb = match ($caps['position_code']) {
            'CHAIR', 'VICE_CHAIR' => 'Lead the board — schedule meetings, manage committees, and track decisions.',
            'SECRETARY' => 'Keep official records — meetings, minutes, resolutions, and action items.',
            'TREASURER' => 'Follow resolutions, documents, and action items related to board work.',
            'HEAD_EXOFFICIO' => 'School leadership view of board meetings, minutes, and decisions.',
            default => 'Your meetings, assigned action items, and board documents.',
        };

        return $this->portalView('BoardPortal.dashboard', [
            'section' => 'dashboard',
            'title' => 'Board Member Dashboard',
            'subtitle' => $roleBlurb,
            'stats' => $stats,
            'upcomingMeetings' => $upcomingMeetings,
            'myActions' => $myActions,
            'pendingMinutes' => $pendingMinutes,
            'recentResolutions' => $recentResolutions,
        ]);
    }

    public function committees()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        if (empty($caps['can_view_committees'])) {
            return redirect()->route('board.portal.dashboard')->with('error', 'You do not have access to committees.');
        }

        $committees = SchoolBoardCommittee::where('schoolID', $member->schoolID)
            ->withCount('members')
            ->orderBy('committee_name')
            ->get();

        return $this->portalView('BoardPortal.committees', [
            'section' => 'committees',
            'title' => 'Committees',
            'subtitle' => $caps['can_manage_committees']
                ? 'Create and manage board committees.'
                : 'Board committees.',
            'committees' => $committees,
        ]);
    }

    public function storeCommittee(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_manage_committees', $request)) {
            return $deny;
        }

        $validated = $request->validate([
            'committee_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:Active,Inactive',
            'formed_date' => 'nullable|date',
        ]);

        $name = trim($validated['committee_name']);
        if (SchoolBoardCommittee::where('schoolID', $member->schoolID)->where('committee_name', $name)->exists()) {
            return response()->json(['success' => false, 'message' => 'That committee already exists.'], 422);
        }

        SchoolBoardCommittee::create([
            'schoolID' => $member->schoolID,
            'committee_name' => $name,
            'committee_code' => strtoupper(str_replace(' ', '_', substr($name, 0, 40))),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'Active',
            'formed_date' => $validated['formed_date'] ?? now()->toDateString(),
        ]);

        return response()->json(['success' => true, 'message' => 'Committee created.']);
    }

    public function meetings(Request $request)
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        SchoolBoardMeeting::syncOngoingStatusesForSchool((int) $member->schoolID);

        // All members can browse school meetings so they can comment even if absent
        $query = SchoolBoardMeeting::where('schoolID', $member->schoolID)
            ->with(['committee', 'chair', 'attendees'])
            ->withCount('comments')
            ->orderByDesc('meeting_date');

        $meetings = $query->get();
        $openMeeting = null;
        $meetingComments = collect();
        $openId = (int) $request->input('id', $request->query('id', 0));
        if ($openId > 0) {
            $openMeeting = $meetings->firstWhere('meetingID', $openId)
                ?: SchoolBoardMeeting::with(['committee', 'chair', 'attendees'])
                    ->where('schoolID', $member->schoolID)
                    ->where('meetingID', $openId)
                    ->first();

            if ($openMeeting) {
                $meetingComments = SchoolBoardMeetingComment::with(['member.position'])
                    ->where('schoolID', $member->schoolID)
                    ->where('meetingID', $openMeeting->meetingID)
                    ->orderBy('created_at')
                    ->get();
            }
        }

        $isAttendee = false;
        if ($openMeeting) {
            $isAttendee = (int) $openMeeting->chair_memberID === (int) $member->memberID
                || $openMeeting->attendees->contains('memberID', $member->memberID);
        }

        return $this->portalView('BoardPortal.meetings', [
            'section' => 'meetings',
            'title' => 'Meetings',
            'subtitle' => $caps['can_manage_meetings']
                ? 'Schedule meetings, track attendance, and read member comments.'
                : 'Browse board meetings and leave comments — even if you missed the sitting.',
            'meetings' => $meetings,
            'openMeeting' => $openMeeting,
            'meetingComments' => $meetingComments,
            'isAttendeeOfOpen' => $isAttendee,
            'committees' => SchoolBoardCommittee::where('schoolID', $member->schoolID)->where('status', 'Active')->orderBy('committee_name')->get(),
            'boardMembers' => $this->activeMembers((int) $member->schoolID),
        ]);
    }

    public function storeMeetingComment(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_comment_meetings', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $validated = $request->validate([
            'meetingID' => 'required|integer',
            'body' => 'required|string|min:2|max:5000',
        ]);

        $meeting = SchoolBoardMeeting::with('attendees')
            ->where('schoolID', $schoolID)
            ->where('meetingID', $validated['meetingID'])
            ->first();

        if (!$meeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found.'], 404);
        }

        $wasAbsent = (int) $meeting->chair_memberID !== (int) $member->memberID
            && !$meeting->attendees->contains('memberID', $member->memberID);

        $comment = SchoolBoardMeetingComment::create([
            'schoolID' => $schoolID,
            'meetingID' => $meeting->meetingID,
            'memberID' => $member->memberID,
            'body' => trim($validated['body']),
            'was_absent' => $wasAbsent,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment posted.',
            'commentID' => $comment->commentID,
            'meetingID' => $meeting->meetingID,
        ]);
    }

    public function storeMeeting(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_manage_meetings', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'meeting_type' => 'required|in:Full Board,Committee',
            'committeeID' => 'nullable|integer',
            'meeting_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'venue' => 'nullable|string|max:200',
            'agenda' => 'nullable|string|max:5000',
            'chair_memberID' => 'nullable|integer',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'integer',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (($validated['meeting_type'] ?? '') === 'Committee') {
            if (empty($validated['committeeID'])) {
                return response()->json(['success' => false, 'message' => 'Select a committee for committee meetings.'], 422);
            }
        } else {
            $validated['committeeID'] = null;
        }

        if (!empty($validated['committeeID'])) {
            $owns = SchoolBoardCommittee::where('schoolID', $schoolID)->where('committeeID', $validated['committeeID'])->exists();
            if (!$owns) {
                return response()->json(['success' => false, 'message' => 'Invalid committee.'], 422);
            }
        }

        $chairId = $validated['chair_memberID'] ?? $member->memberID;
        if ($chairId) {
            $ownsChair = SchoolBoardMember::where('schoolID', $schoolID)->where('memberID', $chairId)->exists();
            if (!$ownsChair) {
                return response()->json(['success' => false, 'message' => 'Invalid chairperson.'], 422);
            }
        }

        $status = 'Scheduled';
        $temp = new SchoolBoardMeeting([
            'meeting_date' => $validated['meeting_date'],
            'start_time' => $validated['start_time'] ?? null,
            'status' => $status,
        ]);
        if ($temp->hasStarted()) {
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
            'chair_memberID' => $chairId,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->syncMeetingAttendees($schoolID, (int) $meeting->meetingID, $validated['attendee_ids'] ?? []);

        return response()->json(['success' => true, 'message' => 'Meeting scheduled successfully.']);
    }

    public function minutes()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        $query = SchoolBoardMinute::where('schoolID', $member->schoolID)
            ->with(['meeting', 'preparer'])
            ->orderByDesc('updated_at');

        if (!$caps['see_all_minutes']) {
            $query->where(function ($q) use ($member) {
                $q->where('prepared_by', $member->memberID)
                    ->orWhere('approved_by', $member->memberID)
                    ->orWhere('status', 'Approved');
            });
        }

        $eligibleMeetings = SchoolBoardMeeting::where('schoolID', $member->schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->whereDoesntHave('minute')
            ->orderByDesc('meeting_date')
            ->get();

        return $this->portalView('BoardPortal.minutes', [
            'section' => 'minutes',
            'title' => 'Minutes',
            'subtitle' => $caps['can_manage_minutes']
                ? 'Record meeting minutes and keep official records.'
                : 'Board meeting minutes available to you.',
            'minutes' => $query->get(),
            'eligibleMeetings' => $eligibleMeetings,
        ]);
    }

    public function storeMinute(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_manage_minutes', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $validated = $request->validate([
            'meetingID' => 'required|integer',
            'title' => 'required|string|max:200',
            'content' => 'nullable|string|max:50000',
            'decisions_summary' => 'nullable|string|max:5000',
            'status' => 'nullable|in:Draft,Submitted,Approved',
            'notes' => 'nullable|string|max:2000',
        ]);

        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);
        $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('meetingID', $validated['meetingID'])
            ->firstOrFail();

        if ($meeting->isUpcoming() || !$meeting->allowsMinutes()) {
            return response()->json([
                'success' => false,
                'message' => 'Minutes can only be recorded after the meeting has started (Ongoing or Completed).',
            ], 422);
        }

        if (SchoolBoardMinute::where('schoolID', $schoolID)->where('meetingID', $meeting->meetingID)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Minutes already exist for this meeting.',
            ], 422);
        }

        $status = $validated['status'] ?? 'Draft';
        SchoolBoardMinute::create([
            'schoolID' => $schoolID,
            'meetingID' => $meeting->meetingID,
            'title' => trim($validated['title']),
            'content' => $validated['content'] ?? null,
            'decisions_summary' => $validated['decisions_summary'] ?? null,
            'prepared_by' => $member->memberID,
            'approved_by' => $status === 'Approved' ? $member->memberID : null,
            'status' => $status,
            'approved_at' => $status === 'Approved' ? now() : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Minutes saved.']);
    }

    public function resolutions()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        $query = SchoolBoardResolution::where('schoolID', $member->schoolID)
            ->with(['meeting', 'assignee'])
            ->orderByDesc('resolution_date');

        if (!$caps['see_all_resolutions']) {
            $query->where(function ($q) use ($member) {
                $q->where('assigned_to', $member->memberID)
                    ->orWhere('proposed_by', $member->memberID)
                    ->orWhere('seconded_by', $member->memberID);
            });
        }

        $sourceMeetings = SchoolBoardMeeting::where('schoolID', $member->schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->orderByDesc('meeting_date')
            ->get();

        return $this->portalView('BoardPortal.resolutions', [
            'section' => 'resolutions',
            'title' => 'Resolutions',
            'subtitle' => $caps['can_manage_resolutions']
                ? 'Record board resolutions and assignments.'
                : ($caps['see_all_resolutions'] ? 'All board resolutions.' : 'Resolutions linked to you.'),
            'resolutions' => $query->get(),
            'sourceMeetings' => $sourceMeetings,
            'boardMembers' => $this->activeMembers((int) $member->schoolID),
        ]);
    }

    public function storeResolution(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_manage_resolutions', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $validated = $request->validate([
            'meetingID' => 'required|integer',
            'title' => 'required|string|max:250',
            'description' => 'nullable|string|max:10000',
            'proposed_by' => 'nullable|integer',
            'seconded_by' => 'nullable|integer',
            'assigned_to' => 'nullable|integer',
            'resolution_date' => 'nullable|date',
            'implementation_deadline' => 'nullable|date',
            'status' => 'nullable|in:Proposed,Adopted,Rejected,Deferred,Implemented',
            'notes' => 'nullable|string|max:2000',
        ]);

        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);
        $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('meetingID', $validated['meetingID'])
            ->firstOrFail();

        if (!$meeting->allowsMinutes()) {
            return response()->json([
                'success' => false,
                'message' => 'Resolutions can only be recorded for Ongoing or Completed meetings.',
            ], 422);
        }

        foreach (['proposed_by', 'seconded_by', 'assigned_to'] as $key) {
            if (empty($validated[$key])) {
                continue;
            }
            if (!SchoolBoardMember::where('schoolID', $schoolID)->where('memberID', $validated[$key])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid board member selected.'], 422);
            }
        }

        SchoolBoardResolution::create([
            'schoolID' => $schoolID,
            'meetingID' => $meeting->meetingID,
            'resolution_number' => $this->nextResolutionNumber($schoolID),
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'proposed_by' => $validated['proposed_by'] ?? $member->memberID,
            'seconded_by' => $validated['seconded_by'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'resolution_date' => $validated['resolution_date'] ?? ($meeting->meeting_date?->format('Y-m-d') ?? now()->toDateString()),
            'implementation_deadline' => $validated['implementation_deadline'] ?? null,
            'status' => $validated['status'] ?? 'Proposed',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Resolution saved.']);
    }

    public function actionItems()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        $query = SchoolBoardActionItem::where('schoolID', $member->schoolID)
            ->with(['meeting', 'assignee'])
            ->orderByRaw("FIELD(status, 'Open', 'Pending', 'In Progress', 'Completed', 'Cancelled')")
            ->orderBy('due_date');

        if (!$caps['see_all_action_items']) {
            $query->where('assigned_to', $member->memberID);
        }

        $sourceMeetings = SchoolBoardMeeting::where('schoolID', $member->schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->orderByDesc('meeting_date')
            ->get();

        return $this->portalView('BoardPortal.action_items', [
            'section' => 'action_items',
            'title' => 'Action Items',
            'subtitle' => $caps['can_manage_action_items']
                ? 'Create follow-ups and assign owners.'
                : ($caps['see_all_action_items'] ? 'Board follow-ups and assignments.' : 'Action items assigned to you.'),
            'actionItems' => $query->get(),
            'sourceMeetings' => $sourceMeetings,
            'boardMembers' => $this->activeMembers((int) $member->schoolID),
        ]);
    }

    public function storeActionItem(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_manage_action_items', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $validated = $request->validate([
            'meetingID' => 'required|integer',
            'title' => 'required|string|max:250',
            'description' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|integer',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:Low,Medium,High',
            'notes' => 'nullable|string|max:2000',
        ]);

        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);
        $meeting = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->where('meetingID', $validated['meetingID'])
            ->firstOrFail();

        if (!$meeting->allowsMinutes()) {
            return response()->json([
                'success' => false,
                'message' => 'Action items can only be linked to Ongoing or Completed meetings.',
            ], 422);
        }

        if (!empty($validated['assigned_to'])) {
            if (!SchoolBoardMember::where('schoolID', $schoolID)->where('memberID', $validated['assigned_to'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid assignee.'], 422);
            }
        }

        SchoolBoardActionItem::create([
            'schoolID' => $schoolID,
            'meetingID' => $meeting->meetingID,
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'] ?? 'Medium',
            'status' => 'Open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Action item created.']);
    }

    public function updateActionItemStatus(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $caps = $this->capabilities($member);
        $validated = $request->validate([
            'actionItemID' => 'required|integer',
            'status' => 'required|in:Open,Pending,In Progress,Completed,Cancelled',
        ]);

        $item = SchoolBoardActionItem::where('schoolID', $member->schoolID)
            ->where('actionItemID', $validated['actionItemID'])
            ->firstOrFail();

        $canEdit = $caps['can_update_any_actions']
            || ((int) $item->assigned_to === (int) $member->memberID && $caps['can_update_own_actions']);

        if (!$canEdit) {
            return response()->json(['success' => false, 'message' => 'You cannot update this action item.'], 403);
        }

        $item->status = $validated['status'] === 'Pending' ? 'Open' : $validated['status'];
        $item->completed_at = $item->status === 'Completed' ? now() : null;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Action item updated.']);
    }

    public function documents()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        $query = SchoolBoardDocument::where('schoolID', $member->schoolID)
            ->orderByDesc('created_at');

        if ($caps['focus_finance_docs']) {
            $query->orderByRaw("CASE WHEN category LIKE '%finance%' OR category LIKE '%budget%' OR title LIKE '%budget%' THEN 0 ELSE 1 END");
        }

        return $this->portalView('BoardPortal.documents', [
            'section' => 'documents',
            'title' => 'Documents',
            'subtitle' => $caps['can_manage_documents']
                ? 'Upload and share board documents.'
                : ($caps['is_treasurer']
                    ? 'Board documents — finance-related items highlighted first.'
                    : 'Shared school board documents.'),
            'documents' => $query->get(),
        ]);
    }

    public function storeDocument(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_manage_documents', $request)) {
            return $deny;
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('school_board/' . $member->schoolID, 'public');

        SchoolBoardDocument::create([
            'schoolID' => $member->schoolID,
            'title' => trim($validated['title']),
            'category' => trim((string) ($validated['category'] ?? '')) ?: 'General',
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'Active',
            'uploaded_by' => $member->memberID,
            'uploaded_by_name' => $member->full_name,
        ]);

        return response()->json(['success' => true, 'message' => 'Document uploaded.']);
    }

    public function downloadDocument($documentID)
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        if (empty($caps['can_download_documents']) && empty($caps['can_view_documents'])) {
            return back()->with('error', 'You cannot download documents.');
        }

        $doc = SchoolBoardDocument::where('schoolID', $member->schoolID)
            ->where('documentID', $documentID)
            ->firstOrFail();

        if (!$doc->file_path || !Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download(
            $doc->file_path,
            $doc->original_filename ?: basename($doc->file_path)
        );
    }

    public function members()
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        if (empty($caps['can_view_members'])) {
            return redirect()->route('board.portal.dashboard')
                ->with('error', 'You do not have access to the members directory.');
        }

        $school = School::find($member->schoolID);
        $members = SchoolBoardMember::with('position')
            ->where('schoolID', $member->schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return $this->portalView('BoardPortal.members', [
            'section' => 'members',
            'title' => 'Board Members',
            'subtitle' => !empty($caps['can_message_members'])
                ? 'Directory of all board members — send SMS through the school system.'
                : 'Directory of all active board members.',
            'boardMembers' => $members,
            'smsEnabled' => $school ? $school->isSmsEnabled() : false,
        ]);
    }

    public function sendMemberMessage(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_message_members', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $school = School::find($schoolID);
        if (!$school) {
            return response()->json(['success' => false, 'message' => 'School not found.'], 404);
        }
        if (!$school->isSmsEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'SMS is disabled for this school. Contact the school admin.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:3|max:480',
            'send_to' => 'required|in:all,selected',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer',
        ]);

        $query = SchoolBoardMember::with('position')
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($validated['send_to'] === 'selected') {
            $ids = array_values(array_unique(array_filter(array_map('intval', $validated['member_ids'] ?? []))));
            if ($ids === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select at least one member with a phone number.',
                ], 422);
            }
            $query->whereIn('memberID', $ids);
        }

        $recipients = $query->get();
        if ($recipients->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No members with phone numbers found for this selection.',
            ], 422);
        }

        $body = trim($validated['message']);
        $fullMessage = $school->senderID
            ? $body
            : (($school->school_name ?? 'School') . '. ' . $body);

        $smsService = new SmsService();
        $sent = 0;
        $failed = 0;
        $details = [];

        foreach ($recipients as $target) {
            $result = $smsService->sendSms($target->phone, $fullMessage, null, $schoolID);
            $ok = (bool) ($result['success'] ?? false);
            if ($ok) {
                $sent++;
            } else {
                $failed++;
            }

            $position = optional($target->position)->position_name;
            $displayName = trim($target->full_name . ($position ? ' (' . $position . ')' : ''));

            SmsLog::create([
                'schoolID' => $schoolID,
                'message_id' => $result['message_id'] ?? null,
                'message_type' => 'board_portal',
                'recipient_name' => $displayName,
                'recipient_phone' => $target->phone,
                'recipient_role' => 'board',
                'sent_by_name' => $member->full_name,
                'sent_by_role' => 'BoardMember',
                'message' => $fullMessage,
                'status' => $ok ? 'PENDING' : 'FAILED',
                'sent_date' => now()->toDateString(),
                'sent_time' => now()->toTimeString(),
            ]);

            $details[] = [
                'name' => $displayName,
                'phone' => $target->phone,
                'success' => $ok,
                'note' => $ok ? null : ($result['message'] ?? 'Failed'),
            ];
        }

        return response()->json([
            'success' => $sent > 0,
            'message' => $sent > 0
                ? "SMS sent to {$sent} member(s)" . ($failed ? " ({$failed} failed)." : '.')
                : 'No SMS could be sent.' . ($failed ? " {$failed} failed." : ''),
            'results' => [
                'success' => $sent,
                'failed' => $failed,
                'details' => $details,
            ],
        ], $sent > 0 ? 200 : 422);
    }

    public function messages(Request $request)
    {
        if ($redirect = $this->authorizeMember()) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return redirect()->route('login');
        }

        $caps = $this->capabilities($member);
        if (empty($caps['can_view_messages'])) {
            return redirect()->route('board.portal.dashboard')
                ->with('error', 'You do not have access to messages.');
        }

        $tab = $request->query('tab', 'inbox');
        if (!in_array($tab, ['inbox', 'sent'], true)) {
            $tab = 'inbox';
        }

        $inbox = SchoolBoardMessageRecipient::with(['message.sender.position'])
            ->where('schoolID', $member->schoolID)
            ->where('memberID', $member->memberID)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // Any member can see messages they sent (including replies)
        $sent = SchoolBoardMessage::withCount('recipients')
            ->where('schoolID', $member->schoolID)
            ->where('sender_memberID', $member->memberID)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $openMessage = null;
        $openRecipient = null;
        $canReply = false;
        $threadReplies = collect();
        $messageID = (int) $request->input('id', $request->query('id', 0));
        if ($messageID > 0) {
            $openRecipient = SchoolBoardMessageRecipient::with(['message.sender.position'])
                ->where('schoolID', $member->schoolID)
                ->where('memberID', $member->memberID)
                ->where('messageID', $messageID)
                ->first();

            if ($openRecipient) {
                if (!$openRecipient->read_at) {
                    $openRecipient->read_at = now();
                    $openRecipient->save();
                }
                $openMessage = $openRecipient->message;
                $tab = 'inbox';
                $canReply = $openMessage
                    && (int) $openMessage->sender_memberID !== (int) $member->memberID;

                $inbox->transform(function ($row) use ($messageID, $openRecipient) {
                    if ((int) $row->messageID === $messageID) {
                        $row->read_at = $openRecipient->read_at;
                    }

                    return $row;
                });
            } else {
                $openMessage = SchoolBoardMessage::with(['recipients.member.position', 'sender.position'])
                    ->where('schoolID', $member->schoolID)
                    ->where('sender_memberID', $member->memberID)
                    ->where('messageID', $messageID)
                    ->first();
                if ($openMessage) {
                    $tab = 'sent';
                }
            }

            if ($openMessage) {
                $rootId = $openMessage->parent_messageID ?: $openMessage->messageID;
                $threadReplies = SchoolBoardMessage::with('sender.position')
                    ->where('schoolID', $member->schoolID)
                    ->where(function ($q) use ($rootId, $openMessage) {
                        $q->where('parent_messageID', $rootId)
                            ->orWhere('messageID', $rootId)
                            ->orWhere('parent_messageID', $openMessage->messageID);
                    })
                    ->where('messageID', '!=', $openMessage->messageID)
                    ->orderBy('created_at')
                    ->get();
            }
        }

        return $this->portalView('BoardPortal.messages', [
            'section' => 'messages',
            'title' => 'Messages',
            'subtitle' => !empty($caps['can_compose_messages'])
                ? 'In-app board messages — inbox, compose, and reply (separate from SMS).'
                : 'Your in-app board messages — open and reply.',
            'tab' => $tab,
            'inbox' => $inbox,
            'sent' => $sent,
            'openMessage' => $openMessage,
            'openRecipient' => $openRecipient,
            'canReply' => $canReply,
            'threadReplies' => $threadReplies,
            'boardMembers' => !empty($caps['can_compose_messages'])
                ? $this->activeMembers((int) $member->schoolID)
                : collect(),
            'preselectMemberId' => (int) $request->query('to', 0),
        ]);
    }

    public function composeMessage(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        if ($deny = $this->requireCapability($member, 'can_compose_messages', $request)) {
            return $deny;
        }

        $schoolID = (int) $member->schoolID;
        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string|min:2|max:10000',
            'send_to' => 'required|in:all,selected',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer',
        ]);

        $query = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->where('memberID', '!=', $member->memberID);

        if ($validated['send_to'] === 'selected') {
            $ids = array_values(array_unique(array_filter(array_map('intval', $validated['member_ids'] ?? []))));
            if ($ids === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Select at least one recipient.',
                ], 422);
            }
            $query->whereIn('memberID', $ids);
        }

        $targets = $query->get();
        if ($targets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients found for this selection.',
            ], 422);
        }

        $message = SchoolBoardMessage::create([
            'schoolID' => $schoolID,
            'sender_memberID' => $member->memberID,
            'subject' => trim($validated['subject']),
            'body' => trim($validated['body']),
            'audience' => $validated['send_to'],
            'recipient_count' => $targets->count(),
        ]);

        $now = now();
        foreach ($targets as $target) {
            SchoolBoardMessageRecipient::create([
                'schoolID' => $schoolID,
                'messageID' => $message->messageID,
                'memberID' => $target->memberID,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent to ' . $targets->count() . ' member(s).',
            'messageID' => $message->messageID,
        ]);
    }

    public function replyMessage(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $caps = $this->capabilities($member);
        if (empty($caps['can_view_messages'])) {
            return response()->json(['success' => false, 'message' => 'You cannot reply to messages.'], 403);
        }

        $validated = $request->validate([
            'messageID' => 'required|integer',
            'body' => 'required|string|min:2|max:10000',
        ]);

        $schoolID = (int) $member->schoolID;

        // Must be a recipient of the original message to reply
        $recipientRow = SchoolBoardMessageRecipient::where('schoolID', $schoolID)
            ->where('memberID', $member->memberID)
            ->where('messageID', $validated['messageID'])
            ->first();

        if (!$recipientRow) {
            return response()->json([
                'success' => false,
                'message' => 'You can only reply to messages in your inbox.',
            ], 403);
        }

        $original = SchoolBoardMessage::where('schoolID', $schoolID)
            ->where('messageID', $validated['messageID'])
            ->first();

        if (!$original) {
            return response()->json(['success' => false, 'message' => 'Original message not found.'], 404);
        }

        $toMemberID = (int) $original->sender_memberID;
        if ($toMemberID === (int) $member->memberID) {
            return response()->json(['success' => false, 'message' => 'Cannot reply to your own message.'], 422);
        }

        $target = SchoolBoardMember::where('schoolID', $schoolID)
            ->where('memberID', $toMemberID)
            ->where('status', 'Active')
            ->first();

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Original sender is not available.',
            ], 422);
        }

        $subject = $original->subject;
        if (!str_starts_with(mb_strtolower($subject), 're:')) {
            $subject = 'Re: ' . $subject;
        }

        $parentId = $original->parent_messageID ?: $original->messageID;

        $reply = SchoolBoardMessage::create([
            'schoolID' => $schoolID,
            'sender_memberID' => $member->memberID,
            'parent_messageID' => $parentId,
            'subject' => mb_substr($subject, 0, 200),
            'body' => trim($validated['body']),
            'audience' => 'selected',
            'recipient_count' => 1,
        ]);

        SchoolBoardMessageRecipient::create([
            'schoolID' => $schoolID,
            'messageID' => $reply->messageID,
            'memberID' => $target->memberID,
            'read_at' => null,
        ]);

        if (!$recipientRow->read_at) {
            $recipientRow->read_at = now();
            $recipientRow->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent to ' . $target->full_name . '.',
            'messageID' => $reply->messageID,
        ]);
    }

    public function markMessageRead(Request $request)
    {
        if ($redirect = $this->authorizeMember($request)) {
            return $redirect;
        }

        $member = $this->currentMember();
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'messageID' => 'required|integer',
        ]);

        $row = SchoolBoardMessageRecipient::where('schoolID', $member->schoolID)
            ->where('memberID', $member->memberID)
            ->where('messageID', $validated['messageID'])
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
        }

        if (!$row->read_at) {
            $row->read_at = now();
            $row->save();
        }

        return response()->json(['success' => true, 'message' => 'Marked as read.']);
    }
}
