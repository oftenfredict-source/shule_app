<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BoardPortalController;
use App\Models\School;
use App\Models\SchoolBoardActionItem;
use App\Models\SchoolBoardCommittee;
use App\Models\SchoolBoardDocument;
use App\Models\SchoolBoardMeeting;
use App\Models\SchoolBoardMeetingComment;
use App\Models\SchoolBoardMember;
use App\Models\SchoolBoardMessage;
use App\Models\SchoolBoardMessageRecipient;
use App\Models\SchoolBoardMinute;
use App\Models\SchoolBoardResolution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * JSON API for the Board Member portal (/board-portal on the website).
 *
 * Write actions (compose/reply message, schedule meeting, comment, minutes, resolutions,
 * action items, documents, SMS to members, create committee) are inherited from
 * BoardPortalController. They already return JSON and use the same permission checks as the web.
 * The mobile.auth:board middleware sets the same session values as the web login.
 */
class BoardMobileApiController extends BoardPortalController
{
    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * @return array{0: ?SchoolBoardMember, 1: ?array, 2: ?JsonResponse}
     */
    private function context(?string $flag = null, string $denyMessage = 'You do not have access to this section.'): array
    {
        $member = $this->currentMember();
        if (!$member) {
            return [null, null, response()->json([
                'success' => false,
                'message' => 'Board member account is inactive or missing.',
            ], 401)];
        }

        $caps = $this->capabilities($member);
        if ($flag !== null && empty($caps[$flag])) {
            return [$member, $caps, response()->json([
                'success' => false,
                'message' => $denyMessage,
            ], 403)];
        }

        return [$member, $caps, null];
    }

    private function date($value, string $format = 'Y-m-d'): ?string
    {
        return $value ? $value->format($format) : null;
    }

    private function time($value): ?string
    {
        return $value ? substr((string) $value, 0, 5) : null;
    }

    private function memberBrief(?SchoolBoardMember $m): ?array
    {
        if (!$m) {
            return null;
        }

        return [
            'memberID' => (int) $m->memberID,
            'full_name' => $m->full_name,
            'position_name' => $m->position->position_name ?? 'Member',
            'position_code' => $m->position->position_code ?? 'MEMBER',
        ];
    }

    private function meetingBrief(?SchoolBoardMeeting $m): ?array
    {
        if (!$m) {
            return null;
        }

        return [
            'meetingID' => (int) $m->meetingID,
            'title' => $m->title,
            'meeting_date' => $this->date($m->meeting_date),
            'status' => $m->status,
        ];
    }

    private function meetingPayload(SchoolBoardMeeting $m): array
    {
        return [
            'meetingID' => (int) $m->meetingID,
            'title' => $m->title,
            'meeting_type' => $m->meeting_type,
            'meeting_date' => $this->date($m->meeting_date),
            'start_time' => $this->time($m->start_time),
            'end_time' => $this->time($m->end_time),
            'venue' => $m->venue,
            'agenda' => $m->agenda,
            'status' => $m->status,
            'notes' => $m->notes,
            'committee' => $m->committee ? [
                'committeeID' => (int) $m->committee->committeeID,
                'committee_name' => $m->committee->committee_name,
            ] : null,
            'chair' => $this->memberBrief($m->chair),
            'comments_count' => $m->comments_count ?? null,
            'attendees_count' => $m->relationLoaded('attendees') ? $m->attendees->count() : null,
        ];
    }

    private function minutePayload(SchoolBoardMinute $m): array
    {
        return [
            'minuteID' => (int) $m->minuteID,
            'meeting' => $this->meetingBrief($m->meeting),
            'title' => $m->title,
            'content' => $m->content,
            'decisions_summary' => $m->decisions_summary,
            'status' => $m->status,
            'prepared_by' => $this->memberBrief($m->preparer),
            'approved_by' => $this->memberBrief($m->approver),
            'approved_at' => $this->date($m->approved_at, 'Y-m-d H:i:s'),
            'notes' => $m->notes,
            'updated_at' => $this->date($m->updated_at, 'Y-m-d H:i:s'),
        ];
    }

    private function resolutionPayload(SchoolBoardResolution $r): array
    {
        return [
            'resolutionID' => (int) $r->resolutionID,
            'resolution_number' => $r->resolution_number,
            'title' => $r->title,
            'description' => $r->description,
            'meeting' => $this->meetingBrief($r->meeting),
            'proposed_by' => $this->memberBrief($r->proposer),
            'seconded_by' => $this->memberBrief($r->seconder),
            'assigned_to' => $this->memberBrief($r->assignee),
            'resolution_date' => $this->date($r->resolution_date),
            'implementation_deadline' => $this->date($r->implementation_deadline),
            'status' => $r->status,
            'notes' => $r->notes,
        ];
    }

    private function actionItemPayload(SchoolBoardActionItem $a, SchoolBoardMember $viewer, array $caps): array
    {
        $canUpdate = !empty($caps['can_update_any_actions'])
            || ((int) $a->assigned_to === (int) $viewer->memberID && !empty($caps['can_update_own_actions']));

        return [
            'actionItemID' => (int) $a->actionItemID,
            'title' => $a->title,
            'description' => $a->description,
            'meeting' => $this->meetingBrief($a->meeting),
            'assigned_to' => $this->memberBrief($a->assignee),
            'due_date' => $this->date($a->due_date),
            'priority' => $a->priority,
            'status' => $a->status,
            'is_overdue' => $a->isOverdue(),
            'completed_at' => $this->date($a->completed_at, 'Y-m-d H:i:s'),
            'notes' => $a->notes,
            'can_update' => $canUpdate,
        ];
    }

    private function documentPayload(SchoolBoardDocument $d, array $caps): array
    {
        $canDownload = !empty($caps['can_download_documents']) || !empty($caps['can_view_documents']);

        return [
            'documentID' => (int) $d->documentID,
            'title' => $d->title,
            'category' => $d->category,
            'description' => $d->description,
            'original_filename' => $d->original_filename,
            'mime_type' => $d->mime_type,
            'file_size' => (int) $d->file_size,
            'file_size_label' => $d->file_size_label,
            'uploaded_by_name' => $d->uploaded_by_name,
            'created_at' => $this->date($d->created_at, 'Y-m-d H:i:s'),
            'download_url' => $canDownload ? url('api/v1/board/documents/' . $d->documentID . '/download') : null,
        ];
    }

    private function messagePayload(SchoolBoardMessage $m, bool $withBody = true): array
    {
        $data = [
            'messageID' => (int) $m->messageID,
            'parent_messageID' => $m->parent_messageID ? (int) $m->parent_messageID : null,
            'subject' => $m->subject,
            'sender' => $this->memberBrief($m->sender),
            'audience' => $m->audience,
            'recipient_count' => (int) $m->recipient_count,
            'created_at' => $this->date($m->created_at, 'Y-m-d H:i:s'),
        ];

        if ($withBody) {
            $data['body'] = $m->body;
        } else {
            $data['preview'] = mb_substr(trim((string) $m->body), 0, 120);
        }

        return $data;
    }

    private function publicCapabilities(array $caps): array
    {
        $menu = array_map(fn ($item) => [
            'key' => $item['key'],
            'label' => $item['label'],
        ], $caps['menu'] ?? []);

        unset($caps['menu']);
        $caps['menu'] = $menu;

        return $caps;
    }

    public function profilePayload(SchoolBoardMember $member): array
    {
        $member->loadMissing(['position', 'committees']);

        return [
            'memberID' => (int) $member->memberID,
            'schoolID' => (int) $member->schoolID,
            'full_name' => $member->full_name,
            'first_name' => $member->first_name,
            'middle_name' => $member->middle_name,
            'last_name' => $member->last_name,
            'email' => $member->email,
            'phone' => $member->phone,
            'gender' => $member->gender,
            'occupation' => $member->occupation,
            'organization' => $member->organization,
            'appointed_date' => $this->date($member->appointed_date),
            'term_end_date' => $this->date($member->term_end_date),
            'status' => $member->status,
            'position_name' => $member->position->position_name ?? 'Member',
            'position_code' => $member->position->position_code ?? 'MEMBER',
            'committees' => $member->committees->map(fn ($c) => [
                'committeeID' => (int) $c->committeeID,
                'committee_name' => $c->committee_name,
                'committee_role' => $c->pivot->committee_role ?? null,
            ])->values(),
        ];
    }

    // ------------------------------------------------------------------
    // Profile + dashboard
    // ------------------------------------------------------------------

    public function profile(Request $request)
    {
        [$member, $caps, $error] = $this->context();
        if ($error) {
            return $error;
        }

        $school = School::find($member->schoolID);
        $token = $request->attributes->get('mobile_token');

        return response()->json([
            'success' => true,
            'data' => [
                'member' => $this->profilePayload($member),
                'school' => $school ? [
                    'schoolID' => (int) $school->schoolID,
                    'school_name' => $school->school_name,
                ] : null,
                'capabilities' => $this->publicCapabilities($caps),
                'unread_messages' => !empty($caps['can_view_messages']) ? $this->unreadMessageCount($member) : 0,
                'token_expires_at' => optional($token->expires_at ?? null)->toIso8601String(),
            ],
        ]);
    }

    public function dashboard()
    {
        [$member, $caps, $error] = $this->context();
        if ($error) {
            return $error;
        }

        $schoolID = (int) $member->schoolID;
        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);

        $meetingsQuery = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->with(['committee', 'chair.position'])
            ->orderByDesc('meeting_date')
            ->orderByDesc('start_time');

        if (empty($caps['see_all_meetings'])) {
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
            ->with(['meeting', 'assignee.position'])
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $openActionsCount = SchoolBoardActionItem::where('schoolID', $schoolID)
            ->when(empty($caps['see_all_action_items']), fn ($q) => $q->where('assigned_to', $member->memberID))
            ->whereNotIn('status', ['Completed', 'Cancelled'])
            ->count();

        $pendingMinutes = collect();
        if (!empty($caps['can_manage_minutes']) || !empty($caps['is_secretary']) || !empty($caps['is_chair'])) {
            $pendingMinutes = SchoolBoardMinute::where('schoolID', $schoolID)
                ->whereIn('status', ['Draft', 'Pending Approval', 'Submitted'])
                ->with(['meeting', 'preparer.position', 'approver.position'])
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get();
        }

        $recentResolutions = SchoolBoardResolution::where('schoolID', $schoolID)
            ->when(empty($caps['see_all_resolutions']), function ($q) use ($member) {
                $q->where(function ($inner) use ($member) {
                    $inner->where('assigned_to', $member->memberID)
                        ->orWhere('proposed_by', $member->memberID)
                        ->orWhere('seconded_by', $member->memberID);
                });
            })
            ->with(['meeting', 'proposer.position', 'seconder.position', 'assignee.position'])
            ->orderByDesc('resolution_date')
            ->limit(5)
            ->get();

        $roleBlurb = match ($caps['position_code']) {
            'CHAIR', 'VICE_CHAIR' => 'Lead the board — schedule meetings, manage committees, and track decisions.',
            'SECRETARY' => 'Keep official records — meetings, minutes, resolutions, and action items.',
            'TREASURER' => 'Follow resolutions, documents, and action items related to board work.',
            'HEAD_EXOFFICIO' => 'School leadership view of board meetings, minutes, and decisions.',
            default => 'Your meetings, assigned action items, and board documents.',
        };

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Board Member Dashboard',
                'subtitle' => $roleBlurb,
                'stats' => [
                    'upcoming_meetings' => (clone $meetingsQuery)->whereIn('status', ['Scheduled', 'Ongoing'])->count(),
                    'open_actions' => $openActionsCount,
                    'my_actions' => $myActions->count(),
                    'documents' => SchoolBoardDocument::where('schoolID', $schoolID)->count(),
                    'committees' => $member->committees->count(),
                ],
                'unread_messages' => !empty($caps['can_view_messages']) ? $this->unreadMessageCount($member) : 0,
                'upcoming_meetings' => $upcomingMeetings->map(fn ($m) => $this->meetingPayload($m))->values(),
                'my_action_items' => $myActions->map(fn ($a) => $this->actionItemPayload($a, $member, $caps))->values(),
                'pending_minutes' => $pendingMinutes->map(fn ($m) => $this->minutePayload($m))->values(),
                'recent_resolutions' => $recentResolutions->map(fn ($r) => $this->resolutionPayload($r))->values(),
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Members directory
    // ------------------------------------------------------------------

    public function members()
    {
        [$member, $caps, $error] = $this->context('can_view_members', 'You do not have access to the members directory.');
        if ($error) {
            return $error;
        }

        $school = School::find($member->schoolID);
        $members = SchoolBoardMember::with('position')
            ->where('schoolID', $member->schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'can_message_members' => !empty($caps['can_message_members']),
                'can_compose_messages' => !empty($caps['can_compose_messages']),
                'sms_enabled' => $school ? (bool) $school->isSmsEnabled() : false,
                'members' => $members->map(fn ($m) => array_merge($this->memberBrief($m), [
                    'phone' => $m->phone ?: null,
                    'email' => $m->email ?: null,
                    'is_me' => (int) $m->memberID === (int) $member->memberID,
                ]))->values(),
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Committees
    // ------------------------------------------------------------------

    public function committees()
    {
        [$member, $caps, $error] = $this->context('can_view_committees', 'You do not have access to committees.');
        if ($error) {
            return $error;
        }

        $committees = SchoolBoardCommittee::where('schoolID', $member->schoolID)
            ->withCount('members')
            ->orderBy('committee_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'can_manage' => !empty($caps['can_manage_committees']),
                'committees' => $committees->map(fn ($c) => [
                    'committeeID' => (int) $c->committeeID,
                    'committee_name' => $c->committee_name,
                    'committee_code' => $c->committee_code,
                    'description' => $c->description,
                    'status' => $c->status,
                    'formed_date' => $this->date($c->formed_date),
                    'members_count' => (int) $c->members_count,
                ])->values(),
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Meetings
    // ------------------------------------------------------------------

    public function meetings(Request $request)
    {
        [$member, $caps, $error] = $this->context('can_view_meetings', 'You do not have access to meetings.');
        if ($error) {
            return $error;
        }

        $schoolID = (int) $member->schoolID;
        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);

        // Everyone can browse school meetings so they can comment even if absent (same as web).
        $meetings = SchoolBoardMeeting::where('schoolID', $schoolID)
            ->with(['committee', 'chair.position', 'attendees'])
            ->withCount('comments')
            ->orderByDesc('meeting_date')
            ->get();

        $data = [
            'can_manage' => !empty($caps['can_manage_meetings']),
            'can_comment' => !empty($caps['can_comment_meetings']),
            'meetings' => $meetings->map(fn ($m) => $this->meetingPayload($m))->values(),
        ];

        if (!empty($caps['can_manage_meetings'])) {
            $data['options'] = [
                'meeting_types' => ['Full Board', 'Committee'],
                'committees' => SchoolBoardCommittee::where('schoolID', $schoolID)
                    ->where('status', 'Active')
                    ->orderBy('committee_name')
                    ->get(['committeeID', 'committee_name']),
                'board_members' => $this->activeMembers($schoolID)->load('position')
                    ->map(fn ($m) => $this->memberBrief($m))->values(),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function meetingShow(int $meetingID)
    {
        [$member, $caps, $error] = $this->context('can_view_meetings', 'You do not have access to meetings.');
        if ($error) {
            return $error;
        }

        $schoolID = (int) $member->schoolID;
        SchoolBoardMeeting::syncOngoingStatusesForSchool($schoolID);

        $meeting = SchoolBoardMeeting::with(['committee', 'chair.position', 'attendees.position'])
            ->withCount('comments')
            ->where('schoolID', $schoolID)
            ->where('meetingID', $meetingID)
            ->first();

        if (!$meeting) {
            return response()->json(['success' => false, 'message' => 'Meeting not found.'], 404);
        }

        $comments = SchoolBoardMeetingComment::with('member.position')
            ->where('schoolID', $schoolID)
            ->where('meetingID', $meeting->meetingID)
            ->orderBy('created_at')
            ->get();

        $isAttendee = (int) $meeting->chair_memberID === (int) $member->memberID
            || $meeting->attendees->contains('memberID', $member->memberID);

        return response()->json([
            'success' => true,
            'data' => [
                'meeting' => $this->meetingPayload($meeting),
                'attendees' => $meeting->attendees->map(fn ($a) => array_merge($this->memberBrief($a), [
                    'attendance_status' => $a->pivot->attendance_status ?? 'Expected',
                ]))->values(),
                'is_attendee' => $isAttendee,
                'can_comment' => !empty($caps['can_comment_meetings']),
                'comments' => $comments->map(fn ($c) => [
                    'commentID' => (int) $c->commentID,
                    'member' => $this->memberBrief($c->member),
                    'body' => $c->body,
                    'was_absent' => (bool) $c->was_absent,
                    'created_at' => $this->date($c->created_at, 'Y-m-d H:i:s'),
                ])->values(),
            ],
        ]);
    }

    // ------------------------------------------------------------------
    // Minutes
    // ------------------------------------------------------------------

    public function minutes()
    {
        [$member, $caps, $error] = $this->context('can_view_minutes', 'You do not have access to minutes.');
        if ($error) {
            return $error;
        }

        $query = SchoolBoardMinute::where('schoolID', $member->schoolID)
            ->with(['meeting', 'preparer.position', 'approver.position'])
            ->orderByDesc('updated_at');

        if (empty($caps['see_all_minutes'])) {
            $query->where(function ($q) use ($member) {
                $q->where('prepared_by', $member->memberID)
                    ->orWhere('approved_by', $member->memberID)
                    ->orWhere('status', 'Approved');
            });
        }

        $data = [
            'can_manage' => !empty($caps['can_manage_minutes']),
            'minutes' => $query->get()->map(fn ($m) => $this->minutePayload($m))->values(),
        ];

        if (!empty($caps['can_manage_minutes'])) {
            $data['options'] = [
                'statuses' => ['Draft', 'Submitted', 'Approved'],
                'eligible_meetings' => SchoolBoardMeeting::where('schoolID', $member->schoolID)
                    ->whereIn('status', ['Ongoing', 'Completed'])
                    ->whereDoesntHave('minute')
                    ->orderByDesc('meeting_date')
                    ->get()
                    ->map(fn ($m) => $this->meetingBrief($m))->values(),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ------------------------------------------------------------------
    // Resolutions
    // ------------------------------------------------------------------

    public function resolutions()
    {
        [$member, $caps, $error] = $this->context('can_view_resolutions', 'You do not have access to resolutions.');
        if ($error) {
            return $error;
        }

        $query = SchoolBoardResolution::where('schoolID', $member->schoolID)
            ->with(['meeting', 'proposer.position', 'seconder.position', 'assignee.position'])
            ->orderByDesc('resolution_date');

        if (empty($caps['see_all_resolutions'])) {
            $query->where(function ($q) use ($member) {
                $q->where('assigned_to', $member->memberID)
                    ->orWhere('proposed_by', $member->memberID)
                    ->orWhere('seconded_by', $member->memberID);
            });
        }

        $data = [
            'can_manage' => !empty($caps['can_manage_resolutions']),
            'resolutions' => $query->get()->map(fn ($r) => $this->resolutionPayload($r))->values(),
        ];

        if (!empty($caps['can_manage_resolutions'])) {
            $data['options'] = [
                'statuses' => ['Proposed', 'Adopted', 'Rejected', 'Deferred', 'Implemented'],
                'source_meetings' => $this->sourceMeetings((int) $member->schoolID),
                'board_members' => $this->activeMembers((int) $member->schoolID)->load('position')
                    ->map(fn ($m) => $this->memberBrief($m))->values(),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ------------------------------------------------------------------
    // Action items
    // ------------------------------------------------------------------

    public function actionItems()
    {
        [$member, $caps, $error] = $this->context('can_view_action_items', 'You do not have access to action items.');
        if ($error) {
            return $error;
        }

        $query = SchoolBoardActionItem::where('schoolID', $member->schoolID)
            ->with(['meeting', 'assignee.position'])
            ->orderByRaw("FIELD(status, 'Open', 'Pending', 'In Progress', 'Completed', 'Cancelled')")
            ->orderBy('due_date');

        if (empty($caps['see_all_action_items'])) {
            $query->where('assigned_to', $member->memberID);
        }

        $data = [
            'can_manage' => !empty($caps['can_manage_action_items']),
            'status_options' => ['Open', 'In Progress', 'Completed', 'Cancelled'],
            'action_items' => $query->get()->map(fn ($a) => $this->actionItemPayload($a, $member, $caps))->values(),
        ];

        if (!empty($caps['can_manage_action_items'])) {
            $data['options'] = [
                'priorities' => ['Low', 'Medium', 'High'],
                'source_meetings' => $this->sourceMeetings((int) $member->schoolID),
                'board_members' => $this->activeMembers((int) $member->schoolID)->load('position')
                    ->map(fn ($m) => $this->memberBrief($m))->values(),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function sourceMeetings(int $schoolID)
    {
        return SchoolBoardMeeting::where('schoolID', $schoolID)
            ->whereIn('status', ['Ongoing', 'Completed'])
            ->orderByDesc('meeting_date')
            ->get()
            ->map(fn ($m) => $this->meetingBrief($m))
            ->values();
    }

    // ------------------------------------------------------------------
    // Documents
    // ------------------------------------------------------------------

    public function documents()
    {
        [$member, $caps, $error] = $this->context('can_view_documents', 'You do not have access to documents.');
        if ($error) {
            return $error;
        }

        $query = SchoolBoardDocument::where('schoolID', $member->schoolID)->orderByDesc('created_at');

        if (!empty($caps['focus_finance_docs'])) {
            $query->orderByRaw("CASE WHEN category LIKE '%finance%' OR category LIKE '%budget%' OR title LIKE '%budget%' THEN 0 ELSE 1 END");
        }

        return response()->json([
            'success' => true,
            'data' => [
                'can_upload' => !empty($caps['can_manage_documents']),
                'documents' => $query->get()->map(fn ($d) => $this->documentPayload($d, $caps))->values(),
            ],
        ]);
    }

    public function documentFile(int $documentID)
    {
        [$member, $caps, $error] = $this->context();
        if ($error) {
            return $error;
        }

        if (empty($caps['can_download_documents']) && empty($caps['can_view_documents'])) {
            return response()->json(['success' => false, 'message' => 'You cannot download documents.'], 403);
        }

        $doc = SchoolBoardDocument::where('schoolID', $member->schoolID)
            ->where('documentID', $documentID)
            ->first();

        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
        }

        if (!$doc->file_path || !Storage::disk('public')->exists($doc->file_path)) {
            return response()->json(['success' => false, 'message' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download(
            $doc->file_path,
            $doc->original_filename ?: basename($doc->file_path)
        );
    }

    // ------------------------------------------------------------------
    // Messages (in-app)
    // ------------------------------------------------------------------

    public function messages(Request $request)
    {
        [$member, $caps, $error] = $this->context('can_view_messages', 'You do not have access to messages.');
        if ($error) {
            return $error;
        }

        $inbox = SchoolBoardMessageRecipient::with(['message.sender.position'])
            ->where('schoolID', $member->schoolID)
            ->where('memberID', $member->memberID)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->filter(fn ($row) => $row->message)
            ->map(fn ($row) => array_merge($this->messagePayload($row->message, false), [
                'is_read' => $row->read_at !== null,
                'read_at' => $this->date($row->read_at, 'Y-m-d H:i:s'),
            ]))
            ->values();

        $sent = SchoolBoardMessage::with('sender.position')
            ->withCount('recipients')
            ->where('schoolID', $member->schoolID)
            ->where('sender_memberID', $member->memberID)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn ($m) => array_merge($this->messagePayload($m, false), [
                'recipients_count' => (int) $m->recipients_count,
            ]))
            ->values();

        $data = [
            'can_compose' => !empty($caps['can_compose_messages']),
            'unread' => $this->unreadMessageCount($member),
            'inbox' => $inbox,
            'sent' => $sent,
        ];

        if (!empty($caps['can_compose_messages'])) {
            $data['options'] = [
                'board_members' => $this->activeMembers((int) $member->schoolID)->load('position')
                    ->filter(fn ($m) => (int) $m->memberID !== (int) $member->memberID)
                    ->map(fn ($m) => $this->memberBrief($m))->values(),
            ];
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Open one message (marks it read when it is in the member's inbox) with its reply thread.
     */
    public function messageShow(int $messageID)
    {
        [$member, $caps, $error] = $this->context('can_view_messages', 'You do not have access to messages.');
        if ($error) {
            return $error;
        }

        $schoolID = (int) $member->schoolID;
        $openMessage = null;
        $canReply = false;
        $box = null;

        $recipient = SchoolBoardMessageRecipient::with(['message.sender.position'])
            ->where('schoolID', $schoolID)
            ->where('memberID', $member->memberID)
            ->where('messageID', $messageID)
            ->first();

        if ($recipient && $recipient->message) {
            if (!$recipient->read_at) {
                $recipient->read_at = now();
                $recipient->save();
            }
            $openMessage = $recipient->message;
            $box = 'inbox';
            $canReply = (int) $openMessage->sender_memberID !== (int) $member->memberID;
        } else {
            $openMessage = SchoolBoardMessage::with(['recipients.member.position', 'sender.position'])
                ->where('schoolID', $schoolID)
                ->where('sender_memberID', $member->memberID)
                ->where('messageID', $messageID)
                ->first();
            $box = $openMessage ? 'sent' : null;
        }

        if (!$openMessage) {
            return response()->json(['success' => false, 'message' => 'Message not found.'], 404);
        }

        $rootId = $openMessage->parent_messageID ?: $openMessage->messageID;
        $thread = SchoolBoardMessage::with('sender.position')
            ->where('schoolID', $schoolID)
            ->where(function ($q) use ($rootId, $openMessage) {
                $q->where('parent_messageID', $rootId)
                    ->orWhere('messageID', $rootId)
                    ->orWhere('parent_messageID', $openMessage->messageID);
            })
            ->where('messageID', '!=', $openMessage->messageID)
            ->orderBy('created_at')
            ->get();

        $message = $this->messagePayload($openMessage);
        if ($box === 'sent') {
            $message['recipients'] = $openMessage->recipients->map(fn ($r) => array_merge(
                $this->memberBrief($r->member) ?? [],
                ['is_read' => $r->read_at !== null]
            ))->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'box' => $box,
                'message' => $message,
                'can_reply' => $canReply,
                'thread' => $thread->map(fn ($m) => $this->messagePayload($m))->values(),
                'unread' => $this->unreadMessageCount($member),
            ],
        ]);
    }
}
