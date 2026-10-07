<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSmsLog;
use App\Models\ParentModel;
use App\Models\School;
use App\Models\SchoolAttendanceSmsSchedule;
use App\Models\Student;
use App\Models\Subclass;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceSmsService
{
    public function __construct(
        private SchoolWorkingDaysService $workingDaysService
    ) {
    }

    public function getScheduleForSchool(int $schoolID): string
    {
        return $this->getConfigForSchool($schoolID)?->schedule
            ?? SchoolAttendanceSmsSchedule::SCHEDULE_NONE;
    }

    public function getConfigForSchool(int $schoolID): ?SchoolAttendanceSmsSchedule
    {
        return SchoolAttendanceSmsSchedule::where('schoolID', $schoolID)->first();
    }

    public function isFeatureEnabled(int $schoolID): bool
    {
        return $this->getScheduleForSchool($schoolID) !== SchoolAttendanceSmsSchedule::SCHEDULE_NONE;
    }

    public function isManualSendAllowed(int $schoolID): bool
    {
        // Manual send stays available even when auto is enabled (e.g. no internet).
        return $this->isFeatureEnabled($schoolID);
    }

    public function isAutoSendEnabled(int $schoolID): bool
    {
        $config = $this->getConfigForSchool($schoolID);
        if (!$config || $config->schedule === SchoolAttendanceSmsSchedule::SCHEDULE_NONE) {
            return false;
        }

        return $config->send_method === SchoolAttendanceSmsSchedule::METHOD_AUTO;
    }

    public function sendMethodLabel(?string $method): string
    {
        return match ($method) {
            SchoolAttendanceSmsSchedule::METHOD_AUTO => 'Automatic',
            SchoolAttendanceSmsSchedule::METHOD_MANUAL => 'Manual',
            default => 'Manual',
        };
    }

    /**
     * Whether the auto job should fire for this school right now.
     */
    public function shouldRunAutoNow(SchoolAttendanceSmsSchedule $config, ?Carbon $now = null): bool
    {
        $now = ($now ?? Carbon::now())->copy();

        if ($config->schedule === SchoolAttendanceSmsSchedule::SCHEDULE_NONE) {
            return false;
        }
        if ($config->send_method !== SchoolAttendanceSmsSchedule::METHOD_AUTO) {
            return false;
        }
        if (empty($config->send_time)) {
            return false;
        }

        $configuredTime = Carbon::parse($config->send_time)->format('H:i');
        if ($now->format('H:i') !== $configuredTime) {
            return false;
        }

        if (!$this->autoDayMatches($config, $now)) {
            return false;
        }

        $reference = $this->resolveAutoReferenceDate($config, $now);
        $periodKey = $this->periodKey($config->schedule, $reference);

        return $config->last_auto_period_key !== $periodKey;
    }

    public function resolveAutoReferenceDate(SchoolAttendanceSmsSchedule $config, ?Carbon $now = null): Carbon
    {
        return ($now ?? Carbon::now())->copy()->startOfDay();
    }

    /**
     * Send auto SMS to all active students in a school for the current period.
     *
     * @return array{sent: int, skipped: int, failed: int}
     */
    public function runAutoBatchForSchool(SchoolAttendanceSmsSchedule $config, ?Carbon $now = null): array
    {
        $now = ($now ?? Carbon::now())->copy();
        $schoolID = (int) $config->schoolID;
        $reference = $this->resolveAutoReferenceDate($config, $now);
        $periodKey = $this->periodKey($config->schedule, $reference);

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $studentIds = Student::where('schoolID', $schoolID)
            ->whereIn(
                'studentID',
                app(\App\Services\StudentEnrollmentService::class)
                    ->activeEnrollments($schoolID)
                    ->pluck('studentID')
                    ->unique()
                    ->values()
                    ->all()
            )
            ->pluck('studentID');

        if ($studentIds->isEmpty()) {
            return ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        }

        foreach ($studentIds as $studentID) {
            if ($this->alreadySent($schoolID, (int) $studentID, $config->schedule, $periodKey)) {
                $skipped++;
                continue;
            }

            $result = $this->sendForStudent($schoolID, (int) $studentID, null, $reference);
            if ($result['success'] ?? false) {
                $sent++;
            } elseif ($result['already_sent'] ?? false) {
                $skipped++;
            } else {
                $failed++;
            }

            usleep(150000);
        }

        $config->update([
            'last_auto_period_key' => $periodKey,
            'last_auto_run_at' => Carbon::now(),
        ]);

        return compact('sent', 'skipped', 'failed');
    }

    private function autoDayMatches(SchoolAttendanceSmsSchedule $config, Carbon $now): bool
    {
        return match ($config->schedule) {
            SchoolAttendanceSmsSchedule::SCHEDULE_DAILY => true,
            SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY => $this->storedWeekday($now) === (int) $config->send_day_of_week,
            SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY => $this->monthlySendDayMatches($now, (int) $config->send_day_of_month),
            SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY => (int) $now->month === (int) $config->send_month
                && $this->monthlySendDayMatches($now, (int) $config->send_day_of_month),
            default => false,
        };
    }

    private function storedWeekday(Carbon $date): int
    {
        $dow = (int) $date->dayOfWeek;

        return $dow === 0 ? 7 : $dow;
    }

    private function monthlySendDayMatches(Carbon $date, int $configuredDay): bool
    {
        if ($configuredDay < 1) {
            return false;
        }

        $lastDay = (int) $date->copy()->endOfMonth()->day;
        $effectiveDay = min($configuredDay, $lastDay);

        return (int) $date->day === $effectiveDay;
    }

    public function formatAutoScheduleSummary(?SchoolAttendanceSmsSchedule $config): string
    {
        if (!$config || $config->schedule === SchoolAttendanceSmsSchedule::SCHEDULE_NONE) {
            return 'Disabled';
        }

        $time = $config->send_time
            ? Carbon::parse($config->send_time)->format('H:i')
            : '—';

        if ($config->send_method === SchoolAttendanceSmsSchedule::METHOD_MANUAL) {
            return ucfirst($config->schedule) . ' · Manual';
        }

        $days = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return match ($config->schedule) {
            SchoolAttendanceSmsSchedule::SCHEDULE_DAILY => "Daily · Auto @ {$time}",
            SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY => 'Weekly · Auto ' . ($days[$config->send_day_of_week] ?? '?') . " @ {$time}",
            SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY => "Monthly · Auto day {$config->send_day_of_month} @ {$time}",
            SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY => 'Yearly · Auto '
                . ($months[$config->send_month] ?? '?') . " {$config->send_day_of_month} @ {$time}",
            default => '—',
        };
    }

    public function scheduleLabel(string $schedule): string
    {
        return match ($schedule) {
            SchoolAttendanceSmsSchedule::SCHEDULE_DAILY => 'Daily',
            SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY => 'Weekly',
            SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY => 'Monthly',
            SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY => 'Yearly',
            default => 'None',
        };
    }

    public function periodKey(string $schedule, Carbon $reference): string
    {
        return match ($schedule) {
            SchoolAttendanceSmsSchedule::SCHEDULE_DAILY => $reference->format('Y-m-d'),
            SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY => $reference->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY => $reference->format('Y-m'),
            SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY => $reference->format('Y'),
            default => '',
        };
    }

    /**
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function periodRange(string $schedule, Carbon $reference): array
    {
        $ref = $reference->copy()->startOfDay();

        return match ($schedule) {
            SchoolAttendanceSmsSchedule::SCHEDULE_DAILY => [
                'start' => $ref->copy(),
                'end' => $ref->copy(),
                'label' => $ref->format('d/m/Y'),
            ],
            SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY => [
                'start' => $ref->copy()->startOfWeek(Carbon::MONDAY),
                'end' => $ref->copy()->endOfWeek(Carbon::SUNDAY),
                'label' => $ref->copy()->startOfWeek(Carbon::MONDAY)->format('d/m')
                    . '-' . $ref->copy()->endOfWeek(Carbon::SUNDAY)->format('d/m/Y'),
            ],
            SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY => [
                'start' => $ref->copy()->startOfMonth(),
                'end' => $ref->copy()->endOfMonth(),
                'label' => $ref->format('m/Y'),
            ],
            SchoolAttendanceSmsSchedule::SCHEDULE_YEARLY => [
                'start' => $ref->copy()->startOfYear(),
                'end' => $ref->copy()->endOfYear(),
                'label' => $ref->format('Y'),
            ],
            default => [
                'start' => $ref->copy(),
                'end' => $ref->copy(),
                'label' => $ref->format('d/m/Y'),
            ],
        };
    }

    public function alreadySent(int $schoolID, int $studentID, string $schedule, string $periodKey): bool
    {
        if ($periodKey === '') {
            return false;
        }

        return AttendanceSmsLog::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->where('schedule_type', $schedule)
            ->where('period_key', $periodKey)
            ->where('status', 'sent')
            ->exists();
    }

    public function resolveParentPhone(Student $student): ?string
    {
        $phone = null;

        if ($student->parent && !empty(trim((string) $student->parent->phone))) {
            $phone = $student->parent->phone;
        } elseif ($student->parentID) {
            $parent = $student->relationLoaded('parent')
                ? $student->parent
                : ParentModel::find($student->parentID);
            if ($parent && !empty(trim((string) $parent->phone))) {
                $phone = $parent->phone;
            }
        }

        if (!$phone && !empty($student->emergency_contact_phone)) {
            $phone = $student->emergency_contact_phone;
        }

        $phone = trim((string) $phone);
        if ($phone === '' || in_array(strtolower($phone), ['null', 'undefined', 'n/a'], true)) {
            return null;
        }

        return $phone;
    }

    /**
     * @return array{success: bool, message?: string, error?: string, already_sent?: bool}
     */
    public function sendForStudent(
        int $schoolID,
        int $studentID,
        ?int $sentBy,
        ?Carbon $referenceDate = null
    ): array {
        $schedule = $this->getScheduleForSchool($schoolID);
        if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_NONE) {
            return [
                'success' => false,
                'error' => 'This feature is not allowed in your school.',
            ];
        }

        $reference = ($referenceDate ?? Carbon::today())->copy()->startOfDay();
        $periodKey = $this->periodKey($schedule, $reference);

        if ($this->alreadySent($schoolID, $studentID, $schedule, $periodKey)) {
            return [
                'success' => false,
                'error' => 'SMS for this period was already sent to this parent.',
                'already_sent' => true,
            ];
        }

        $student = Student::with(['parent', 'subclass.class'])
            ->where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->first();

        if (!$student) {
            return ['success' => false, 'error' => 'Student not found.'];
        }

        $phone = $this->resolveParentPhone($student);
        if (!$phone) {
            return ['success' => false, 'error' => 'No valid parent phone number.'];
        }

        $message = $this->buildMessage($schoolID, $student, $schedule, $reference);
        if ($message === '') {
            return ['success' => false, 'error' => 'No attendance data to send.'];
        }

        $smsService = new SmsService();
        $result = $smsService->sendSms($phone, $message, null, $schoolID);

        if ($result['success'] ?? false) {
            AttendanceSmsLog::updateOrCreate(
                [
                    'schoolID' => $schoolID,
                    'studentID' => $studentID,
                    'schedule_type' => $schedule,
                    'period_key' => $periodKey,
                ],
                [
                    'phone' => $phone,
                    'status' => 'sent',
                    'message' => $message,
                    'sent_by' => $sentBy,
                    'sent_at' => Carbon::now(),
                ]
            );

            return ['success' => true, 'message' => $message];
        }

        return [
            'success' => false,
            'error' => $result['message'] ?? 'Failed to send SMS',
        ];
    }

    public function buildMessage(int $schoolID, Student $student, string $schedule, Carbon $reference): string
    {
        $school = School::find($schoolID);
        $schoolName = $school?->school_name ?? 'Shule';
        $studentName = trim(implode(' ', array_filter([
            $student->first_name,
            $student->middle_name,
            $student->last_name,
        ])));

        $range = $this->periodRange($schedule, $reference);
        $firstName = $student->first_name ?: $studentName;

        $lines = [];
        if (!($school && $school->senderID)) {
            $lines[] = $schoolName;
        }

        if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_DAILY) {
            $date = $range['start'];
            $reason = $this->workingDaysService->getNonWorkingReason($schoolID, $date);
            if ($reason) {
                $lines[] = "MZAZI WA {$firstName}, {$date->format('d/m')}: {$firstName} hajahudhuria kwa sababu ni {$reason}.";
            } else {
                $status = $this->attendanceStatusForDate($schoolID, $student->studentID, $date);
                $short = $this->shortStatus($status);
                $lines[] = "MZAZI WA {$firstName}, {$date->format('d/m')}: {$firstName} {$short}.";
            }
        } elseif ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_WEEKLY) {
            $summary = $this->buildPeriodDaySummary($schoolID, $student->studentID, $range['start'], $range['end']);
            $lines[] = "Mahudhurio ya {$firstName} wiki {$range['label']}: {$summary}";
        } elseif ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_MONTHLY) {
            $stats = $this->buildPeriodStats($schoolID, $student->studentID, $range['start'], $range['end']);
            $lines[] = "Mahudhurio {$firstName} mwezi {$range['label']}: P{$stats['present']} H{$stats['absent']} L{$stats['late']} E{$stats['excused']}";
        } else {
            $stats = $this->buildPeriodStats($schoolID, $student->studentID, $range['start'], $range['end']);
            $lines[] = "Mahudhurio {$firstName} mwaka {$range['label']}: P{$stats['present']} H{$stats['absent']} L{$stats['late']} E{$stats['excused']}";
        }

        $msg = implode("\n", $lines);

        return strlen($msg) > 480 ? substr($msg, 0, 477) . '...' : $msg;
    }

    private function attendanceStatusForDate(int $schoolID, int $studentID, Carbon $date): string
    {
        $record = Attendance::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->whereDate('attendance_date', $date->format('Y-m-d'))
            ->first();

        return $record?->status ?? 'NotRecorded';
    }

    private function shortStatus(string $status): string
    {
        return match ($status) {
            'Present' => 'PRESENT',
            'Absent' => 'ABSENT',
            'Late' => 'LATE',
            'Excused' => 'EXCUSED',
            default => 'HAJACHUKULIWA',
        };
    }

    private function statusAbbrev(string $status): string
    {
        return match ($status) {
            'Present' => 'P',
            'Absent' => 'H',
            'Late' => 'L',
            'Excused' => 'E',
            default => '-',
        };
    }

    private function weekdayAbbrev(Carbon $date): string
    {
        return match ((int) $date->dayOfWeek) {
            1 => 'Sen',
            2 => 'Jne',
            3 => 'Jtn',
            4 => 'Alh',
            5 => 'Ij',
            default => $date->format('D'),
        };
    }

    private function buildPeriodDaySummary(int $schoolID, int $studentID, Carbon $start, Carbon $end): string
    {
        $records = Attendance::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->whereBetween('attendance_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->attendance_date)->format('Y-m-d'));

        $parts = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $dateStr = $current->format('Y-m-d');
            $reason = $this->workingDaysService->getNonWorkingReason($schoolID, $current);

            if ($reason) {
                $parts[] = $this->weekdayAbbrev($current) . ' MAP';
            } elseif ($records->has($dateStr)) {
                $parts[] = $this->weekdayAbbrev($current) . ' ' . $this->statusAbbrev($records[$dateStr]->status);
            } else {
                $parts[] = $this->weekdayAbbrev($current) . ' -';
            }

            $current->addDay();
        }

        return $parts ? implode(', ', $parts) : 'Hakuna rekodi';
    }

    /**
     * @return array{present: int, absent: int, late: int, excused: int}
     */
    private function buildPeriodStats(int $schoolID, int $studentID, Carbon $start, Carbon $end): array
    {
        $records = Attendance::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->whereBetween('attendance_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        return [
            'present' => $records->where('status', 'Present')->count(),
            'absent' => $records->where('status', 'Absent')->count(),
            'late' => $records->where('status', 'Late')->count(),
            'excused' => $records->where('status', 'Excused')->count(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function buildRecipientRows(
        int $schoolID,
        string $schedule,
        Carbon $reference,
        ?int $subclassID = null,
        ?array $studentIds = null
    ): Collection {
        $periodKey = $this->periodKey($schedule, $reference);

        $enrollmentService = app(\App\Services\StudentEnrollmentService::class);
        $query = Student::with(['parent', 'subclass.class'])
            ->where('schoolID', $schoolID);

        if ($studentIds) {
            $query->whereIn('studentID', $studentIds);
        } elseif ($subclassID) {
            $enrolledIds = $enrollmentService->studentIDsInSubclass((int) $subclassID, $schoolID, 'Active');
            if (empty($enrolledIds)) {
                return collect();
            }
            $query->whereIn('studentID', $enrolledIds);
        } else {
            $enrolledIds = $enrollmentService
                ->activeEnrollments($schoolID)
                ->pluck('studentID')
                ->unique()
                ->values()
                ->all();
            if (empty($enrolledIds)) {
                return collect();
            }
            $query->whereIn('studentID', $enrolledIds);
        }

        return $query->orderBy('first_name')->get()->map(function (Student $student) use ($schoolID, $schedule, $periodKey, $reference) {
            $phone = $this->resolveParentPhone($student);
            $name = trim(implode(' ', array_filter([
                $student->first_name,
                $student->middle_name,
                $student->last_name,
            ])));
            $alreadySent = $this->alreadySent($schoolID, $student->studentID, $schedule, $periodKey);
            $preview = $this->buildMessage($schoolID, $student, $schedule, $reference);

            return [
                'studentID' => $student->studentID,
                'studentName' => $name,
                'parentPhone' => $phone,
                'subclassID' => $student->subclassID,
                'classLabel' => $student->subclass?->class
                    ? trim($student->subclass->class->class_name . ' ' . ($student->subclass->subclass_name ?? ''))
                    : '',
                'already_sent' => $alreadySent,
                'preview' => strlen($preview) > 120 ? substr($preview, 0, 117) . '...' : $preview,
            ];
        });
    }

    /**
     * Resolve start/end for filtered attendance SMS (manual status filter).
     *
     * @return array{start: Carbon, end: Carbon, label: string}|null
     */
    public function resolveFilterPeriod(
        string $searchType,
        ?string $date = null,
        ?string $year = null,
        ?string $month = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): ?array {
        if ($searchType === 'date' && $date) {
            $d = Carbon::parse($date)->startOfDay();

            return [
                'start' => $d->copy(),
                'end' => $d->copy(),
                'label' => 'tarehe ' . $d->format('d/m/Y'),
            ];
        }

        if ($searchType === 'date_range' && $fromDate && $toDate) {
            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::parse($toDate)->startOfDay();

            return [
                'start' => $start,
                'end' => $end,
                'label' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
            ];
        }

        if ($searchType === 'month' && $year && $month) {
            $start = Carbon::create((int) $year, (int) $month, 1)->startOfMonth();

            return [
                'start' => $start->copy(),
                'end' => $start->copy()->endOfMonth()->startOfDay(),
                'label' => 'mwezi ' . $start->format('F Y'),
            ];
        }

        if ($searchType === 'year' && $year) {
            $start = Carbon::create((int) $year, 1, 1)->startOfYear();

            return [
                'start' => $start->copy(),
                'end' => $start->copy()->endOfYear()->startOfDay(),
                'label' => 'mwaka ' . $year,
            ];
        }

        return null;
    }

    public function statusFilterLabel(string $status): string
    {
        return match ($status) {
            'Present' => 'Present',
            'Absent' => 'Absent',
            'Sick' => 'Sick',
            'Excused' => 'Permission',
            default => $status,
        };
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function buildStatusFilterRecipients(
        int $schoolID,
        string $status,
        string $searchType,
        ?string $date = null,
        ?string $year = null,
        ?string $month = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $subclassID = null,
        ?int $classID = null,
        bool $isCoordinatorView = false,
        string $enrollmentStatus = 'Active'
    ): Collection {
        $range = $this->resolveFilterPeriod($searchType, $date, $year, $month, $fromDate, $toDate);
        if (!$range || !in_array($status, ['Present', 'Absent', 'Sick', 'Excused'], true)) {
            return collect();
        }

        $query = Attendance::query()
            ->where('schoolID', $schoolID)
            ->where('status', $status)
            ->whereBetween('attendance_date', [
                $range['start']->format('Y-m-d'),
                $range['end']->format('Y-m-d'),
            ]);

        if ($subclassID) {
            $query->where('subclassID', (int) $subclassID);
        } elseif ($isCoordinatorView && $classID) {
            $subclassIds = Subclass::where('classID', $classID)->pluck('subclassID');
            if ($subclassIds->isEmpty()) {
                return collect();
            }
            $query->whereIn('subclassID', $subclassIds);
        }

        $counts = $query
            ->select('studentID', DB::raw('COUNT(*) as days_count'))
            ->groupBy('studentID')
            ->having('days_count', '>', 0)
            ->pluck('days_count', 'studentID');

        if ($counts->isEmpty()) {
            return collect();
        }

        $enrollmentService = app(\App\Services\StudentEnrollmentService::class);
        $allowedIds = null;
        if ($subclassID) {
            $allowedIds = $enrollmentService->studentIDsInSubclass((int) $subclassID, $schoolID, $enrollmentStatus);
        } elseif ($isCoordinatorView && $classID) {
            $allowedIds = [];
            $subclassIds = Subclass::where('classID', $classID)->pluck('subclassID');
            foreach ($subclassIds as $sid) {
                $allowedIds = array_merge(
                    $allowedIds,
                    $enrollmentService->studentIDsInSubclass((int) $sid, $schoolID, $enrollmentStatus)
                );
            }
            $allowedIds = array_values(array_unique($allowedIds));
        }

        $students = Student::with(['parent', 'subclass.class'])
            ->where('schoolID', $schoolID)
            ->whereIn('studentID', $counts->keys()->all())
            ->when($allowedIds !== null, fn ($q) => $q->whereIn('studentID', $allowedIds ?: [0]))
            ->orderBy('first_name')
            ->get();

        return $students->map(function (Student $student) use ($counts) {
            $name = trim(implode(' ', array_filter([
                $student->first_name,
                $student->middle_name,
                $student->last_name,
            ])));

            return [
                'studentID' => $student->studentID,
                'studentName' => $name,
                'parentPhone' => $this->resolveParentPhone($student),
                'days_count' => (int) ($counts[$student->studentID] ?? 0),
                'subclassID' => $student->subclassID,
            ];
        })->values();
    }

    public function buildStatusFilterMessage(
        int $schoolID,
        Student $student,
        string $status,
        array $range,
        ?string $template = null
    ): string {
        $tokens = $this->buildStatusFilterTokens($schoolID, $student, $status, $range);
        if ($tokens === null) {
            return '';
        }

        $tpl = $template !== null && trim($template) !== ''
            ? $template
            : $this->defaultStatusFilterTemplate(!empty($tokens['SCHOOL_NAME']));

        return $this->fillStatusFilterTemplate($tpl, $tokens);
    }

    public function defaultStatusFilterTemplate(bool $includeSchool = false): string
    {
        $lines = [];
        if ($includeSchool) {
            $lines[] = '{SCHOOL_NAME}';
        }
        $lines[] = 'MZAZI WA {STUDENT_NAME}';
        $lines[] = 'Yafuatayo ni mahudhurio ya {PERIOD}';
        $lines[] = '{STATUS_LINE}';
        $lines[] = '{DAY_LIST}';

        return implode("\n", $lines);
    }

    /**
     * @return array<string, string>|null
     */
    public function buildStatusFilterTokens(
        int $schoolID,
        Student $student,
        string $status,
        array $range
    ): ?array {
        $school = School::find($schoolID);
        $schoolName = $school?->school_name ?? 'Shule';
        $firstName = $student->first_name ?: trim(implode(' ', array_filter([
            $student->first_name,
            $student->middle_name,
            $student->last_name,
        ])));

        $records = Attendance::where('schoolID', $schoolID)
            ->where('studentID', $student->studentID)
            ->where('status', $status)
            ->whereBetween('attendance_date', [
                $range['start']->format('Y-m-d'),
                $range['end']->format('Y-m-d'),
            ])
            ->orderBy('attendance_date')
            ->get();

        $daysCount = $records->count();
        if ($daysCount === 0) {
            return null;
        }

        $dayPhrase = $this->statusDayPhrase($status);
        $dayLines = [];
        foreach ($records as $rec) {
            $d = Carbon::parse($rec->attendance_date);
            $dayLines[] = $d->format('d/m/Y') . ' ' . $d->format('l') . ' - ' . $dayPhrase;
        }

        $tokens = [
            'STUDENT_NAME' => $firstName,
            'PERIOD' => (string) $range['label'],
            'STATUS_LINE' => $this->statusTitleLine($status, $daysCount),
            'DAY_LIST' => implode("\n", $dayLines),
            'DAYS_COUNT' => (string) $daysCount,
        ];

        if (!($school && $school->senderID)) {
            $tokens['SCHOOL_NAME'] = $schoolName;
        }

        return $tokens;
    }

    public function statusTitleLine(string $status, int $daysCount): string
    {
        return match ($status) {
            'Present' => "Amehudhuria siku {$daysCount}",
            'Absent' => "Haja hudhuria siku {$daysCount}",
            'Sick' => "Ameugua siku {$daysCount}",
            'Excused' => "Alikuwa na ruhusa siku {$daysCount}",
            default => "Siku {$daysCount}",
        };
    }

    public function statusDayPhrase(string $status): string
    {
        return match ($status) {
            'Present' => 'alikuwepo',
            'Absent' => 'hakuwepo',
            'Sick' => 'ameugua',
            'Excused' => 'ruhusa',
            default => strtolower($status),
        };
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function fillStatusFilterTemplate(string $template, array $tokens): string
    {
        $msg = $template;
        foreach ($tokens as $key => $value) {
            $msg = str_replace('{' . $key . '}', $value, $msg);
        }

        // Drop unused school token line if present
        $msg = str_replace('{SCHOOL_NAME}', '', $msg);
        $msg = preg_replace("/\n{3,}/", "\n\n", $msg) ?? $msg;
        $msg = trim($msg);

        return strlen($msg) > 480 ? substr($msg, 0, 477) . '...' : $msg;
    }

    /**
     * @return array{template: string, sample_tokens: array<string, string>, required_tokens: array<int, string>}|null
     */
    public function buildStatusFilterSample(
        int $schoolID,
        int $studentID,
        string $status,
        array $range
    ): ?array {
        $student = Student::where('schoolID', $schoolID)->where('studentID', $studentID)->first();
        if (!$student) {
            return null;
        }

        $tokens = $this->buildStatusFilterTokens($schoolID, $student, $status, $range);
        if ($tokens === null) {
            return null;
        }

        $includeSchool = isset($tokens['SCHOOL_NAME']);
        $template = $this->defaultStatusFilterTemplate($includeSchool);

        return [
            'template' => $template,
            'sample_tokens' => $tokens,
            'sample_message' => $this->fillStatusFilterTemplate($template, $tokens),
            'required_tokens' => ['STUDENT_NAME', 'PERIOD', 'STATUS_LINE', 'DAY_LIST'],
        ];
    }

    /**
     * @return array{success: bool, message?: string, error?: string}
     */
    public function sendStatusFilterForStudent(
        int $schoolID,
        int $studentID,
        string $status,
        string $searchType,
        ?int $sentBy = null,
        ?string $date = null,
        ?string $year = null,
        ?string $month = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?string $messageTemplate = null
    ): array {
        $range = $this->resolveFilterPeriod($searchType, $date, $year, $month, $fromDate, $toDate);
        if (!$range) {
            return ['success' => false, 'error' => 'Invalid period filter.'];
        }

        if (!in_array($status, ['Present', 'Absent', 'Sick', 'Excused'], true)) {
            return ['success' => false, 'error' => 'Invalid attendance status.'];
        }

        if ($messageTemplate !== null && trim($messageTemplate) !== '') {
            foreach (['{STUDENT_NAME}', '{PERIOD}', '{STATUS_LINE}', '{DAY_LIST}'] as $required) {
                if (stripos($messageTemplate, $required) === false) {
                    return [
                        'success' => false,
                        'error' => 'SMS sample must keep locked values: ' . $required,
                    ];
                }
            }
        }

        $student = Student::with(['parent'])
            ->where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->first();

        if (!$student) {
            return ['success' => false, 'error' => 'Student not found.'];
        }

        $phone = $this->resolveParentPhone($student);
        if (!$phone) {
            return ['success' => false, 'error' => 'No valid parent phone number.'];
        }

        $message = $this->buildStatusFilterMessage($schoolID, $student, $status, $range, $messageTemplate);
        if ($message === '') {
            return ['success' => false, 'error' => 'No matching attendance days to send.'];
        }

        $smsService = new SmsService();
        $result = $smsService->sendSms($phone, $message, null, $schoolID);

        if ($result['success'] ?? false) {
            $periodKey = substr(md5($status . '|' . $searchType . '|' . $range['start']->format('Y-m-d') . '|' . $range['end']->format('Y-m-d')), 0, 16);
            AttendanceSmsLog::updateOrCreate(
                [
                    'schoolID' => $schoolID,
                    'studentID' => $studentID,
                    'schedule_type' => 'status_filter',
                    'period_key' => $periodKey,
                ],
                [
                    'phone' => $phone,
                    'status' => 'sent',
                    'message' => $message,
                    'sent_by' => $sentBy,
                    'sent_at' => Carbon::now(),
                ]
            );

            return ['success' => true, 'message' => $message];
        }

        return [
            'success' => false,
            'error' => $result['message'] ?? 'Failed to send SMS',
        ];
    }
}
