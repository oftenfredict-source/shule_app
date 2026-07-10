<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PermissionRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherStaffPermissionAttendanceService
{
    public function __construct(
        private SchoolWorkingDaysService $workingDaysService
    ) {
    }

    /**
     * @return array<string, array<int, string>> e.g. teacher_5 => ['2026-07-10', ...]
     */
    public function loadApprovedPermissionDatesMap(
        int $schoolID,
        string $fromDate,
        string $toDate,
        string $personType = 'all'
    ): array {
        $requesterTypes = match ($personType) {
            'teacher' => ['teacher'],
            'staff' => ['staff'],
            default => ['teacher', 'staff'],
        };

        $permissions = PermissionRequest::query()
            ->where('schoolID', $schoolID)
            ->where('status', 'approved')
            ->whereIn('requester_type', $requesterTypes)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->where(function ($daysQuery) use ($fromDate, $toDate) {
                    $daysQuery->where('time_mode', 'days')
                        ->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $toDate)
                        ->whereDate('end_date', '>=', $fromDate);
                })->orWhere(function ($hoursQuery) use ($fromDate, $toDate) {
                    $hoursQuery->where('time_mode', 'hours')
                        ->where(function ($dateQuery) use ($fromDate, $toDate) {
                            $dateQuery->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
                                ->orWhere(function ($startDateQuery) use ($fromDate, $toDate) {
                                    $startDateQuery->whereNotNull('start_date')
                                        ->whereDate('start_date', '>=', $fromDate)
                                        ->whereDate('start_date', '<=', $toDate);
                                });
                        });
                });
            })
            ->get();

        $map = [];

        foreach ($permissions as $permission) {
            $personKey = $this->personKeyFromPermission($permission);
            if ($personKey === null) {
                continue;
            }

            $dates = $this->resolvePermissionWorkingDates($schoolID, $permission, $fromDate, $toDate);
            if ($dates === []) {
                continue;
            }

            if (!isset($map[$personKey])) {
                $map[$personKey] = [];
            }

            $map[$personKey] = array_values(array_unique(array_merge($map[$personKey], $dates)));
        }

        return $map;
    }

    public function personKey(string $personType, int $personId): string
    {
        return $personType . '_' . $personId;
    }

    /**
     * @param array<string, array<int, string>> $permissionDatesMap
     */
    public function isPermittedOnDate(array $permissionDatesMap, string $personKey, string $date): bool
    {
        return isset($permissionDatesMap[$personKey])
            && in_array($date, $permissionDatesMap[$personKey], true);
    }

    public function isPermittedToday(int $schoolID, string $personType, int $personId, ?string $date = null): bool
    {
        $date = $date ?? Carbon::today()->toDateString();
        $map = $this->loadApprovedPermissionDatesMap($schoolID, $date, $date, $personType);

        return $this->isPermittedOnDate($map, $this->personKey($personType, $personId), $date);
    }

    /**
     * @return array<int, string>
     */
    public function permittedDatesForPerson(array $permissionDatesMap, string $personKey): array
    {
        return $permissionDatesMap[$personKey] ?? [];
    }

    private function personKeyFromPermission(PermissionRequest $permission): ?string
    {
        if ($permission->requester_type === 'teacher' && $permission->teacherID) {
            return 'teacher_' . (int) $permission->teacherID;
        }

        if ($permission->requester_type === 'staff' && $permission->staffID) {
            return 'staff_' . (int) $permission->staffID;
        }

        return null;
    }

    /**
     * Approved permission applies on school working days only.
     *
     * @return array<int, string>
     */
    private function resolvePermissionWorkingDates(
        int $schoolID,
        PermissionRequest $permission,
        string $fromDate,
        string $toDate
    ): array {
        if ($permission->time_mode === 'days' && $permission->start_date && $permission->end_date) {
            $rangeStart = Carbon::parse($permission->start_date)->startOfDay();
            $rangeEnd = Carbon::parse($permission->end_date)->startOfDay();
            $filterStart = Carbon::parse($fromDate)->startOfDay();
            $filterEnd = Carbon::parse($toDate)->startOfDay();

            if ($rangeStart->lt($filterStart)) {
                $rangeStart = $filterStart->copy();
            }
            if ($rangeEnd->gt($filterEnd)) {
                $rangeEnd = $filterEnd->copy();
            }

            if ($rangeEnd->lt($rangeStart)) {
                return [];
            }

            return $this->workingDaysService->getWorkingDates($schoolID, $rangeStart, $rangeEnd);
        }

        if ($permission->time_mode === 'hours') {
            $day = $permission->start_date
                ? Carbon::parse($permission->start_date)->startOfDay()
                : Carbon::parse($permission->created_at)->startOfDay();
            $dateStr = $day->format('Y-m-d');

            if ($dateStr < $fromDate || $dateStr > $toDate) {
                return [];
            }

            return $this->workingDaysService->getWorkingDates($schoolID, $day, $day);
        }

        return [];
    }
}
