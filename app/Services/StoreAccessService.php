<?php

namespace App\Services;

use App\Models\OtherStaff;
use App\Models\StoreInventoryActor;
use App\Models\StoreItem;
use App\Models\Teacher;
use Illuminate\Support\Facades\Session;

class StoreAccessService
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

    public function allowedCategoryIds(array $access): ?array
    {
        if ($this->isAdmin($access) || ($access['category_scope'] ?? 'all') === 'all') {
            return null;
        }

        return collect($access['category_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    public function itemAllowed(array $access, ?int $categoryID): bool
    {
        $allowed = $this->allowedCategoryIds($access);
        if ($allowed === null) {
            return true;
        }
        if (!$categoryID) {
            return false;
        }

        return in_array((int) $categoryID, $allowed, true);
    }

    public function filterItemsByAccess($items, array $access)
    {
        $allowed = $this->allowedCategoryIds($access);
        if ($allowed === null) {
            return $items;
        }

        return $items->filter(fn ($item) => in_array((int) $item->categoryID, $allowed, true));
    }

    public function actorForCurrentUser(int $schoolID): ?StoreInventoryActor
    {
        return $this->findActiveActor($schoolID, Session::get('user_type'));
    }

    public function currentUserHasStoreAccess(int $schoolID): bool
    {
        if (Session::get('user_type') === 'Admin') {
            return (int) Session::get('schoolID') === $schoolID;
        }

        return $this->actorForCurrentUser($schoolID) !== null;
    }

    private function buildAdminAccess(int $schoolID): array
    {
        return [
            'schoolID' => $schoolID,
            'role' => 'admin',
            'actor_type' => null,
            'actor_ref_id' => null,
            'actor_name' => Session::get('admin_name') ?: Session::get('name') ?: 'Administrator',
            'category_scope' => 'all',
            'category_ids' => null,
            'privileges' => StoreInventoryActor::adminPrivileges(),
            'can_manage_actors' => true,
        ];
    }

    private function buildActorAccess(int $schoolID, StoreInventoryActor $actor): array
    {
        $privileges = array_merge(
            StoreInventoryActor::defaultPrivileges(),
            is_array($actor->privileges) ? $actor->privileges : []
        );

        return [
            'schoolID' => $schoolID,
            'role' => 'actor',
            'actorID' => (int) $actor->actorID,
            'actor_type' => $actor->actor_type,
            'actor_ref_id' => (int) $actor->actor_ref_id,
            'actor_name' => $actor->actor_name,
            'category_scope' => $actor->category_scope,
            'category_ids' => $actor->category_ids,
            'privileges' => $privileges,
            'can_manage_actors' => false,
        ];
    }

    private function findActiveActor(int $schoolID, ?string $userType): ?StoreInventoryActor
    {
        if ($userType === 'Teacher') {
            $id = (int) Session::get('teacherID');
            if (!$id) {
                return null;
            }

            return StoreInventoryActor::where('schoolID', $schoolID)
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

            return StoreInventoryActor::where('schoolID', $schoolID)
                ->where('actor_type', 'staff')
                ->where('actor_ref_id', $id)
                ->where('status', 'active')
                ->first();
        }

        return null;
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

    public function categoryIdsForItemIds(int $schoolID, array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        return StoreItem::where('schoolID', $schoolID)
            ->whereIn('itemID', $itemIds)
            ->pluck('categoryID', 'itemID')
            ->map(fn ($id) => $id ? (int) $id : null)
            ->all();
    }
}
