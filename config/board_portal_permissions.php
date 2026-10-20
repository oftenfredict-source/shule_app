<?php

/**
 * Catalog of School Board portal permissions.
 * Admins assign these to board roles under /admin/school-board/roles.
 */
return [
    'Portal' => [
        'board_portal.access' => 'Access board portal',
        'board_portal.dashboard' => 'View dashboard',
    ],
    'Committees' => [
        'board_portal.committees.view' => 'View committees',
        'board_portal.committees.manage' => 'Create and manage committees',
    ],
    'Meetings' => [
        'board_portal.meetings.view' => 'View meetings (own / invited)',
        'board_portal.meetings.view_all' => 'View all meetings',
        'board_portal.meetings.manage' => 'Schedule and manage meetings',
        'board_portal.meetings.comment' => 'Comment on meetings (even if absent)',
    ],
    'Minutes' => [
        'board_portal.minutes.view' => 'View minutes',
        'board_portal.minutes.view_all' => 'View all minutes (incl. drafts)',
        'board_portal.minutes.manage' => 'Record and update minutes',
    ],
    'Resolutions' => [
        'board_portal.resolutions.view' => 'View resolutions linked to me',
        'board_portal.resolutions.view_all' => 'View all resolutions',
        'board_portal.resolutions.manage' => 'Record and manage resolutions',
    ],
    'Action items' => [
        'board_portal.action_items.view' => 'View my action items',
        'board_portal.action_items.view_all' => 'View all action items',
        'board_portal.action_items.update_own' => 'Update my action items',
        'board_portal.action_items.update_any' => 'Update any action item',
        'board_portal.action_items.manage' => 'Create action items',
    ],
    'Documents' => [
        'board_portal.documents.view' => 'View documents',
        'board_portal.documents.download' => 'Download documents',
        'board_portal.documents.manage' => 'Upload documents',
    ],
    'Members' => [
        'board_portal.members.view' => 'View all board members',
        'board_portal.members.message' => 'Send SMS to board members',
    ],
    'Messages' => [
        'board_portal.messages.view' => 'View in-app board messages',
        'board_portal.messages.compose' => 'Compose and send in-app messages',
    ],
];
