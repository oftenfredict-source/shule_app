<?php

namespace App\Services;

use App\Models\BlackBookActor;
use App\Models\OtherStaff;
use App\Models\Teacher;
use Illuminate\Support\Facades\Session;

class BlackBookAccessService
{
    public function resolve(?string $requiredPrivilege = null): ?array
    {
        $schoolID = (int) Session::get('schoolID');
        if (!$schoolID) {
            return null;
        }

        $userType = Session::get('user_type');

        if ($userType === 'Admin') {
            $access = $this->buildAdminAccess($schoolID);
        } else {
            $actor = $this->findActiveActor($schoolID, $userType);
            if (!$actor) {
                return null;
            }
            $access = $this->buildActorAccess($schoolID, $actor);
        }

        if ($requiredPrivilege && empty($access['privileges'][$requiredPrivilege])) {
            return null;
        }

        return $access;
    }

    public function can(array $access, string $privilege): bool
    {
        return !empty($access['privileges'][$privilege]);
    }

    public function isAdmin(array $access): bool
    {
        return ($access['role'] ?? '') === 'admin';
    }

    public function hasAnyManagePrivilege(array $access): bool
    {
        foreach (BlackBookActor::defaultPrivileges() as $key => $default) {
            if (!empty($access['privileges'][$key])) {
                return true;
            }
        }

        return false;
    }

    public function actorForCurrentUser(int $schoolID): ?BlackBookActor
    {
        return $this->findActiveActor($schoolID, Session::get('user_type'));
    }

    public function currentUserHasManageAccess(int $schoolID): bool
    {
        if (Session::get('user_type') === 'Admin') {
            return (int) Session::get('schoolID') === $schoolID;
        }

        $actor = $this->findActiveActor($schoolID, Session::get('user_type'));
        if (!$actor) {
            return false;
        }

        $access = $this->buildActorAccess($schoolID, $actor);

        return $this->hasAnyManagePrivilege($access);
    }

    public function canReportStudent(): bool
    {
        return in_array(Session::get('user_type'), ['Admin', 'Teacher'], true)
            && (int) Session::get('schoolID') > 0;
    }

    public function resolvePersonName(string $type, int $id, int $schoolID): ?string
    {
        if ($type === 'teacher') {
            $person = Teacher::where('id', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
        } else {
            $person = OtherStaff::where('id', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
        }

        if (!$person) {
            return null;
        }

        return trim(implode(' ', array_filter([
            $person->first_name,
            $person->middle_name,
            $person->last_name,
        ])));
    }

    public function firstAllowedSection(array $access): string
    {
        $map = [
            'view_dashboard' => 'dashboard',
            'manage_problem_categories' => 'categories',
            'manage_punishment_categories' => 'punishments',
            'manage_pending' => 'pending',
            'view_resolved' => 'resolved',
            'view_reports' => 'reports',
        ];

        foreach ($map as $privilege => $section) {
            if ($this->can($access, $privilege)) {
                return $section;
            }
        }

        if ($this->can($access, 'manage_pending')) {
            return 'monitoring';
        }

        return 'dashboard';
    }

    public function sectionPrivilege(string $section): ?string
    {
        if ($section === 'monitoring') {
            return 'manage_pending';
        }

        return match ($section) {
            'dashboard' => 'view_dashboard',
            'categories' => 'manage_problem_categories',
            'punishments' => 'manage_punishment_categories',
            'pending' => 'manage_pending',
            'resolved' => 'view_resolved',
            'reports' => 'view_reports',
            default => null,
        };
    }

    private function buildAdminAccess(int $schoolID): array
    {
        return [
            'schoolID' => $schoolID,
            'role' => 'admin',
            'actor_type' => null,
            'actor_ref_id' => null,
            'actor_name' => Session::get('admin_name') ?: Session::get('user_name') ?: 'Administrator',
            'privileges' => BlackBookActor::adminPrivileges(),
            'can_manage_actors' => true,
        ];
    }

    private function buildActorAccess(int $schoolID, BlackBookActor $actor): array
    {
        $privileges = array_merge(
            BlackBookActor::defaultPrivileges(),
            is_array($actor->privileges) ? $actor->privileges : []
        );

        return [
            'schoolID' => $schoolID,
            'role' => 'actor',
            'actorID' => (int) $actor->actorID,
            'actor_type' => $actor->actor_type,
            'actor_ref_id' => (int) $actor->actor_ref_id,
            'actor_name' => $actor->actor_name,
            'privileges' => $privileges,
            'can_manage_actors' => false,
        ];
    }

    private function findActiveActor(int $schoolID, ?string $userType): ?BlackBookActor
    {
        if ($userType === 'Teacher') {
            $id = (int) Session::get('teacherID');
            if (!$id) {
                return null;
            }

            return BlackBookActor::where('schoolID', $schoolID)
                ->where('actor_type', 'teacher')
                ->where('actor_ref_id', $id)
                ->where('status', 'active')
                ->first();
        }

        if ($userType === 'Staff') {
            $id = (int) Session::get('staffID');
            if (!$id) {
                return null;
            }

            return BlackBookActor::where('schoolID', $schoolID)
                ->where('actor_type', 'staff')
                ->where('actor_ref_id', $id)
                ->where('status', 'active')
                ->first();
        }

        return null;
    }
}
