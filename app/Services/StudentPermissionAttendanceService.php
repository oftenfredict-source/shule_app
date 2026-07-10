<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentPermissionAttendanceService
{
    /** @var array<int, string> */
    private const SICK_REASONS = ['sick', 'medical'];

    /** @var array<int, string> */
    private const PERMISSION_REASONS = ['permission', 'family', 'other', 'official', 'professional', 'emergency'];

    public function __construct(
        private SchoolWorkingDaysService $workingDaysService
    ) {
    }

    /**
     * System DB status: Sick | Excused (UI label: Permission).
     */
    public function mapReasonToAttendanceStatus(string $reasonType): string
    {
        $normalized = strtolower(trim($reasonType));
        if (in_array($normalized, self::SICK_REASONS, true)) {
            return 'Sick';
        }

        return 'Excused';
    }

    public function fingerprintStatusLabel(?string $attendanceStatus): string
    {
        return match ($attendanceStatus) {
            'Sick' => 'Sick',
            'Excused', 'Permission' => 'Permission',
            default => 'Absent',
        };
    }

    /**
     * @return array<string, array<string, string>> student_{id} => [Y-m-d => Sick|Excused]
     */
    public function loadApprovedStatusMap(
        int $schoolID,
        string $fromDate,
        string $toDate,
        ?array $studentIds = null
    ): array {
        $query = DB::table('student_permissions')
            ->where('schoolID', $schoolID)
            ->where('status', 'approved')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->where(function ($daysQuery) use ($fromDate, $toDate) {
                    $daysQuery->where('time_mode', 'days')
                        ->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $toDate)
                        ->whereDate('end_date', '>=', $fromDate);
                })->orWhere(function ($hoursQuery) use ($fromDate, $toDate) {
                    $hoursQuery->where('time_mode', 'hours')
                        ->where(function ($dateQuery) use ($fromDate, $toDate) {
                            $dateQuery->whereBetween('start_date_hours', [$fromDate, $toDate])
                                ->orWhereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate]);
                        });
                });
            });

        if ($studentIds !== null && $studentIds !== []) {
            $query->whereIn('student_id', $studentIds);
        }

        $permissions = $query->get();
        $map = [];

        foreach ($permissions as $permission) {
            $studentKey = 'student_' . (int) $permission->student_id;
            $status = $this->mapReasonToAttendanceStatus((string) ($permission->reason_type ?? 'permission'));
            $dates = $this->resolvePermissionWorkingDates($schoolID, $permission, $fromDate, $toDate);

            if (! isset($map[$studentKey])) {
                $map[$studentKey] = [];
            }

            foreach ($dates as $date) {
                $map[$studentKey][$date] = $status;
            }
        }

        return $map;
    }

    public function statusForStudentOnDate(array $statusMap, int $studentId, string $date): ?string
    {
        return $statusMap['student_' . $studentId][$date] ?? null;
    }

    /**
     * Present | Absent | Permission | Sick for fingerprint display.
     */
    public function resolveFingerprintDayStatus(?string $checkInTime, ?string $permissionStatus): string
    {
        if ($permissionStatus === 'Sick') {
            return 'Sick';
        }
        if ($permissionStatus === 'Excused') {
            return 'Permission';
        }
        if (! empty($checkInTime)) {
            return 'Present';
        }

        return 'Absent';
    }

    /**
     * @return array<string, int> pres_b, pres_g, abs_b, abs_g, perm_b, perm_g, sick_b, sick_g
     */
    public function aggregateSubclassStatsFromFingerprint(
        int $schoolID,
        array $subclassIds,
        string $date,
        array $permissionStatusMap
    ): array {
        $stats = $this->emptyGenderStats();
        if ($subclassIds === []) {
            return $stats;
        }

        $workingDates = $this->workingDaysService->getWorkingDates(
            $schoolID,
            Carbon::parse($date)->startOfDay(),
            Carbon::parse($date)->startOfDay()
        );
        $isWorkingDay = in_array($date, $workingDates, true);

        $students = DB::table('students')
            ->where('schoolID', $schoolID)
            ->whereIn('subclassID', $subclassIds)
            ->where('status', '!=', 'Transferred')
            ->select(['studentID', 'gender'])
            ->get();

        $fingerprints = DB::table('student_fingerprint_attendance')
            ->whereIn('studentID', $students->pluck('studentID'))
            ->whereDate('attendance_date', $date)
            ->pluck('check_in_time', 'studentID');

        foreach ($students as $student) {
            $gender = strtolower((string) $student->gender) === 'female' ? 'g' : 'b';
            $permStatus = $isWorkingDay
                ? $this->statusForStudentOnDate($permissionStatusMap, (int) $student->studentID, $date)
                : null;
            $checkIn = $fingerprints[$student->studentID] ?? null;
            $bucket = $this->resolveFingerprintDayStatus($checkIn, $permStatus);

            match ($bucket) {
                'Present' => $stats['pres_' . $gender]++,
                'Permission' => $stats['perm_' . $gender]++,
                'Sick' => $stats['sick_' . $gender]++,
                default => $stats['abs_' . $gender]++,
            };
        }

        return $stats;
    }

    /**
     * System attendances table + approved permissions for students without rows.
     *
     * @return array<string, int>
     */
    public function aggregateSubclassStatsFromSystem(
        int $schoolID,
        array $subclassIds,
        string $date,
        array $permissionStatusMap
    ): array {
        $stats = $this->emptyGenderStats();
        if ($subclassIds === []) {
            return $stats;
        }

        $rows = DB::table('attendances')
            ->leftJoin('students', 'attendances.studentID', '=', 'students.studentID')
            ->where('attendances.schoolID', $schoolID)
            ->whereIn('attendances.subclassID', $subclassIds)
            ->whereDate('attendances.attendance_date', $date)
            ->select(['students.gender', 'attendances.status', 'attendances.studentID'])
            ->get();

        $markedStudentIds = [];

        foreach ($rows as $row) {
            $markedStudentIds[] = (int) $row->studentID;
            $gender = strtolower((string) $row->gender) === 'female' ? 'g' : 'b';
            $this->incrementSystemStatusBucket($stats, $gender, (string) $row->status);
        }

        $workingDates = $this->workingDaysService->getWorkingDates(
            $schoolID,
            Carbon::parse($date)->startOfDay(),
            Carbon::parse($date)->startOfDay()
        );
        if (! in_array($date, $workingDates, true)) {
            return $stats;
        }

        $students = DB::table('students')
            ->where('schoolID', $schoolID)
            ->whereIn('subclassID', $subclassIds)
            ->where('status', '!=', 'Transferred')
            ->select(['studentID', 'gender'])
            ->get();

        foreach ($students as $student) {
            if (in_array((int) $student->studentID, $markedStudentIds, true)) {
                continue;
            }

            $permStatus = $this->statusForStudentOnDate($permissionStatusMap, (int) $student->studentID, $date);
            if (! $permStatus) {
                continue;
            }

            $gender = strtolower((string) $student->gender) === 'female' ? 'g' : 'b';
            $this->incrementSystemStatusBucket($stats, $gender, $permStatus);
        }

        return $stats;
    }

    public function schoolHasFingerprintAttendanceOnDate(int $schoolID, string $date): bool
    {
        return DB::table('student_fingerprint_attendance')
            ->join('students', 'student_fingerprint_attendance.studentID', '=', 'students.studentID')
            ->where('students.schoolID', $schoolID)
            ->where('students.status', '!=', 'Transferred')
            ->whereDate('student_fingerprint_attendance.attendance_date', $date)
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    private function resolvePermissionWorkingDates(
        int $schoolID,
        object $permission,
        string $fromDate,
        string $toDate
    ): array {
        if (($permission->time_mode ?? '') === 'days' && $permission->start_date && $permission->end_date) {
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

        if (($permission->time_mode ?? '') === 'hours') {
            $day = ! empty($permission->start_date_hours)
                ? Carbon::parse($permission->start_date_hours)->startOfDay()
                : Carbon::parse($permission->created_at)->startOfDay();
            $dateStr = $day->format('Y-m-d');
            if ($dateStr < $fromDate || $dateStr > $toDate) {
                return [];
            }

            return $this->workingDaysService->getWorkingDates($schoolID, $day, $day);
        }

        return [];
    }

    /**
     * @param array<string, int> $stats
     */
    private function incrementSystemStatusBucket(array &$stats, string $gender, string $status): void
    {
        $normalized = strtolower($status);
        if ($normalized === 'present') {
            $stats['pres_' . $gender]++;
        } elseif ($normalized === 'sick') {
            $stats['sick_' . $gender]++;
        } elseif (in_array($normalized, ['excused', 'permission'], true)) {
            $stats['perm_' . $gender]++;
        } else {
            $stats['abs_' . $gender]++;
        }
    }

    /**
     * @return array<string, int>
     */
    private function emptyGenderStats(): array
    {
        return [
            'pres_b' => 0,
            'pres_g' => 0,
            'abs_b' => 0,
            'abs_g' => 0,
            'perm_b' => 0,
            'perm_g' => 0,
            'sick_b' => 0,
            'sick_g' => 0,
        ];
    }
}
