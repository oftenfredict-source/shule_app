<?php

namespace App\Services;

use App\Models\FingerprintAttendanceSmsLog;
use App\Models\School;
use App\Models\SchoolFingerprintAttendanceSmsSchedule;
use App\Models\Student;
use App\Models\StudentFingerprintAttendance;
use App\Models\StudentWorkTimeSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentFingerprintAttendanceSmsService
{
    public function __construct(
        private SchoolWorkingDaysService $workingDaysService
    ) {
    }

    public function getConfigForSchool(int $schoolID): ?SchoolFingerprintAttendanceSmsSchedule
    {
        return SchoolFingerprintAttendanceSmsSchedule::where('schoolID', $schoolID)->first();
    }

    public function getScheduleForSchool(int $schoolID): string
    {
        return $this->getConfigForSchool($schoolID)?->schedule
            ?? SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE;
    }

    /**
     * School config wins; otherwise derive schedule from report filter for manual send.
     */
    public function resolveEffectiveSchedule(int $schoolID, ?string $filterType = null): string
    {
        $configured = $this->getScheduleForSchool($schoolID);
        if ($configured !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE) {
            return $configured;
        }

        return match ($filterType) {
            'month' => SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY,
            'year' => SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY,
            'date_range' => SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY,
            default => SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY,
        };
    }

    public function isFeatureEnabled(int $schoolID): bool
    {
        return $this->getScheduleForSchool($schoolID) !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE;
    }

    /**
     * Manual send from report / teacher widget - always allowed (period from report filters).
     */
    public function isManualSendAllowed(int $schoolID): bool
    {
        return true;
    }

    public function isAutoSendEnabled(int $schoolID): bool
    {
        $config = $this->getConfigForSchool($schoolID);

        return $config
            && $config->schedule !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE
            && $config->send_method === SchoolFingerprintAttendanceSmsSchedule::METHOD_AUTO;
    }

    public function formatAutoScheduleSummary(?SchoolFingerprintAttendanceSmsSchedule $config): string
    {
        if (! $config || $config->schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE) {
            return 'Disabled';
        }

        $time = $config->send_time
            ? Carbon::parse($config->send_time)->format('H:i')
            : '-';

        if ($config->send_method === SchoolFingerprintAttendanceSmsSchedule::METHOD_MANUAL) {
            return ucfirst($config->schedule).' · Manual';
        }

        $days = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return match ($config->schedule) {
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY => "Daily · Auto @ {$time}",
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY => 'Weekly · Auto '.($days[$config->send_day_of_week] ?? '?')." @ {$time}",
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY => "Monthly · Auto day {$config->send_day_of_month} @ {$time}",
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY => 'Yearly · Auto '
                .($days[$config->send_month] ?? $config->send_month)." day {$config->send_day_of_month} @ {$time}",
            default => ucfirst($config->schedule),
        };
    }

    public function scheduleLabel(string $schedule): string
    {
        return match ($schedule) {
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY => 'Daily',
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY => 'Weekly',
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY => 'Monthly',
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY => 'Yearly',
            default => 'Disabled',
        };
    }

    public function periodKey(string $schedule, Carbon $reference): string
    {
        return match ($schedule) {
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY => $reference->format('Y-m-d'),
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY => $reference->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY => $reference->format('Y-m'),
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY => $reference->format('Y'),
            default => $reference->format('Y-m-d'),
        };
    }

    public function periodRange(string $schedule, Carbon $reference): array
    {
        $reference = $reference->copy()->startOfDay();

        return match ($schedule) {
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY => [
                'start' => $reference->copy(),
                'end' => $reference->copy(),
                'label' => $reference->format('d M Y'),
            ],
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY => [
                'start' => $reference->copy()->startOfWeek(Carbon::MONDAY),
                'end' => $reference->copy()->endOfWeek(Carbon::SUNDAY),
                'label' => $reference->copy()->startOfWeek(Carbon::MONDAY)->format('d M')
                    .' - '.$reference->copy()->endOfWeek(Carbon::SUNDAY)->format('d M Y'),
            ],
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY => [
                'start' => $reference->copy()->startOfMonth(),
                'end' => $reference->copy()->endOfMonth(),
                'label' => $reference->format('F Y'),
            ],
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY => [
                'start' => $reference->copy()->startOfYear(),
                'end' => $reference->copy()->endOfYear(),
                'label' => $reference->format('Y'),
            ],
            default => [
                'start' => $reference->copy(),
                'end' => $reference->copy(),
                'label' => $reference->format('d M Y'),
            ],
        };
    }

    public function alreadySent(int $schoolID, int $studentID, string $schedule, string $periodKey): bool
    {
        return FingerprintAttendanceSmsLog::where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->where('schedule_type', $schedule)
            ->where('period_key', $periodKey)
            ->where('status', 'sent')
            ->exists();
    }

    public function shouldRunAutoNow(SchoolFingerprintAttendanceSmsSchedule $config, ?Carbon $now = null): bool
    {
        $now = ($now ?? Carbon::now())->copy();

        if ($config->schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE) {
            return false;
        }
        if ($config->send_method !== SchoolFingerprintAttendanceSmsSchedule::METHOD_AUTO) {
            return false;
        }
        if (empty($config->send_time)) {
            return false;
        }

        if ($now->format('H:i') !== Carbon::parse($config->send_time)->format('H:i')) {
            return false;
        }

        if (! $this->autoDayMatches($config, $now)) {
            return false;
        }

        $reference = $now->copy()->startOfDay();
        $periodKey = $this->periodKey($config->schedule, $reference);

        return $config->last_auto_period_key !== $periodKey;
    }

    public function runAutoBatchForSchool(SchoolFingerprintAttendanceSmsSchedule $config, ?Carbon $now = null): array
    {
        $now = ($now ?? Carbon::now())->copy();
        $schoolID = (int) $config->schoolID;
        $reference = $now->copy()->startOfDay();
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

    /**
     * @param array<int, int> $studentIds
     * @return array{sent: int, skipped: int, failed: int, errors: array<int, string>}
     */
    public function sendBulkForStudents(
        int $schoolID,
        array $studentIds,
        ?int $sentBy,
        ?Carbon $referenceDate = null,
        ?string $filterType = null
    ): array {
        $schedule = $this->resolveEffectiveSchedule($schoolID, $filterType);

        $reference = ($referenceDate ?? Carbon::today())->copy()->startOfDay();
        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        foreach ($studentIds as $studentID) {
            $result = $this->sendForStudent($schoolID, (int) $studentID, $sentBy, $reference, $filterType);
            if ($result['success'] ?? false) {
                $sent++;
            } elseif ($result['already_sent'] ?? false) {
                $skipped++;
            } else {
                $failed++;
                if (! empty($result['error'])) {
                    $errors[] = $result['error'];
                }
            }
            usleep(100000);
        }

        return compact('sent', 'skipped', 'failed', 'errors');
    }

    /**
     * @return array{success: bool, message?: string, error?: string, already_sent?: bool}
     */
    public function sendForStudent(
        int $schoolID,
        int $studentID,
        ?int $sentBy,
        ?Carbon $referenceDate = null,
        ?string $filterType = null
    ): array {
        $schedule = $this->resolveEffectiveSchedule($schoolID, $filterType);

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

        if (! $student) {
            return ['success' => false, 'error' => 'Student not found.'];
        }

        $phone = $this->resolveParentPhone($student);
        if (! $phone) {
            return ['success' => false, 'error' => 'No valid parent phone number.'];
        }

        $message = $this->buildMessage($schoolID, $student, $schedule, $reference);
        if ($message === '') {
            return ['success' => false, 'error' => 'No fingerprint attendance data to send.'];
        }

        $smsService = new SmsService();
        $result = $smsService->sendSms($phone, $message, null, $schoolID);

        if ($result['success'] ?? false) {
            FingerprintAttendanceSmsLog::updateOrCreate(
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

        return ['success' => false, 'error' => $result['message'] ?? 'Failed to send SMS'];
    }

    public function buildMessage(int $schoolID, Student $student, string $schedule, Carbon $reference): string
    {
        $school = School::find($schoolID);
        $schoolName = $school?->school_name ?? 'Shule';
        $firstName = $student->first_name ?: trim(implode(' ', array_filter([
            $student->first_name,
            $student->middle_name,
            $student->last_name,
        ])));

        $workTime = StudentWorkTimeSetting::forSchool($schoolID);
        $range = $this->periodRange($schedule, $reference);
        $stats = $this->buildFingerprintPeriodStats(
            $schoolID,
            (int) $student->studentID,
            $range['start'],
            $range['end'],
            (string) $workTime->work_start_time,
            (string) $workTime->work_end_time
        );

        $lines = [];
        if (! ($school && $school->senderID)) {
            $lines[] = $schoolName;
        }

        if ($schedule === SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY) {
            $date = $range['start'];
            if ($stats['present'] > 0) {
                $detail = [];
                if ($stats['late'] > 0) {
                    $detail[] = 'chelewa';
                }
                if ($stats['early_arrival'] > 0) {
                    $detail[] = 'wahi kuingia';
                }
                if ($stats['early_departure'] > 0) {
                    $detail[] = 'wahi kutoka';
                }
                if ($stats['no_checkout'] > 0) {
                    $detail[] = 'hakuna sign out';
                }
                $extra = $detail ? ' ('.implode(', ', $detail).')' : '';
                $lines[] = "MZAZI WA {$firstName}, {$date->format('d/m')} (Fingerprint): {$firstName} PRESENT{$extra}.";
            } else {
                $lines[] = "MZAZI WA {$firstName}, {$date->format('d/m')} (Fingerprint): {$firstName} ABSENT.";
            }
        } else {
            $lines[] = "Mahudhurio fingerprint {$firstName} {$range['label']}: "
                ."P{$stats['present']} H{$stats['absent']} L{$stats['late']} "
                ."WA{$stats['early_arrival']} WD{$stats['early_departure']} NS{$stats['no_checkout']}";
        }

        $msg = implode("\n", $lines);

        return strlen($msg) > 480 ? substr($msg, 0, 477).'...' : $msg;
    }

    /**
     * @param array<int, int>|null $studentIds
     * @return Collection<int, array<string, mixed>>
     */
    public function buildRecipientRows(
        int $schoolID,
        Carbon $reference,
        ?string $filterType = null,
        ?int $subclassID = null,
        ?int $classID = null,
        ?array $studentIds = null
    ): Collection {
        $schedule = $this->resolveEffectiveSchedule($schoolID, $filterType);
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
        } elseif ($classID) {
            $enrolledIds = $enrollmentService->studentIDsInClass((int) $classID, $schoolID, 'Active');
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

        return $query->orderBy('first_name')->get()->map(function (Student $student) use (
            $schoolID,
            $schedule,
            $periodKey,
            $reference,
            $filterType
        ) {
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
                    ? trim($student->subclass->class->class_name.' '.($student->subclass->subclass_name ?? ''))
                    : '',
                'already_sent' => $alreadySent,
                'has_message' => $preview !== '',
                'preview' => strlen($preview) > 120 ? substr($preview, 0, 117).'...' : $preview,
            ];
        });
    }

    /**
     * @return array{present: int, absent: int, late: int, early_arrival: int, early_departure: int, no_checkout: int}
     */
    private function buildFingerprintPeriodStats(
        int $schoolID,
        int $studentID,
        Carbon $start,
        Carbon $end,
        string $workStartTime,
        string $workEndTime
    ): array {
        $workingDates = $this->workingDaysService->getWorkingDates($schoolID, $start, $end);
        $workingSet = array_flip($workingDates);
        $now = Carbon::now();

        $records = StudentFingerprintAttendance::where('studentID', $studentID)
            ->where('attendance_date', '>=', $start->toDateString())
            ->where('attendance_date', '<=', $end->toDateString())
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->attendance_date)->format('Y-m-d'));

        $stats = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'early_arrival' => 0,
            'early_departure' => 0,
            'no_checkout' => 0,
        ];

        foreach ($workingDates as $date) {
            $record = $records->get($date);
            if (! $record || ! $record->check_in_time) {
                $stats['absent']++;

                continue;
            }

            $stats['present']++;
            $checkIn = Carbon::parse($record->check_in_time);
            $officialStart = Carbon::parse($date.' '.substr($workStartTime, 0, 8));
            $officialEnd = Carbon::parse($date.' '.substr($workEndTime, 0, 8));

            if ($checkIn->gt($officialStart)) {
                $stats['late']++;
            } elseif ($checkIn->lt($officialStart)) {
                $stats['early_arrival']++;
            }

            if ($record->check_out_time) {
                if (Carbon::parse($record->check_out_time)->lt($officialEnd)) {
                    $stats['early_departure']++;
                }
            } elseif ($this->isDayCompletedForCheckout($date, $workEndTime, $now)) {
                $stats['no_checkout']++;
            }
        }

        return $stats;
    }

    private function isDayCompletedForCheckout(string $date, string $workEndTime, Carbon $now): bool
    {
        if ($date < $now->toDateString()) {
            return true;
        }
        if ($date === $now->toDateString()) {
            return $now->gt(Carbon::parse($date.' '.substr($workEndTime, 0, 8)));
        }

        return false;
    }

    private function resolveParentPhone(Student $student): ?string
    {
        $phone = trim((string) ($student->parent->phone ?? $student->parent_phone ?? ''));
        if ($phone === '' || in_array(strtolower($phone), ['null', 'undefined', 'n/a'], true)) {
            return null;
        }

        return $phone;
    }

    private function autoDayMatches(SchoolFingerprintAttendanceSmsSchedule $config, Carbon $now): bool
    {
        return match ($config->schedule) {
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_DAILY => true,
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_WEEKLY => $this->storedWeekday($now) === (int) $config->send_day_of_week,
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_MONTHLY => $this->monthlySendDayMatches($now, (int) $config->send_day_of_month),
            SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_YEARLY => (int) $now->month === (int) $config->send_month
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

        return (int) $date->day === min($configuredDay, $lastDay);
    }

    /**
     * @return array{start: Carbon, end: Carbon, label: string}|null
     */
    public function resolveReportFilterPeriod(
        string $filterType,
        ?string $date = null,
        ?string $year = null,
        ?string $month = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): ?array {
        if ($filterType === 'day' && $date) {
            $d = Carbon::parse($date)->startOfDay();

            return [
                'start' => $d->copy(),
                'end' => $d->copy(),
                'label' => 'tarehe '.$d->format('d/m/Y'),
            ];
        }

        if ($filterType === 'date_range' && $fromDate && $toDate) {
            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::parse($toDate)->startOfDay();

            return [
                'start' => $start,
                'end' => $end,
                'label' => $start->format('d/m/Y').' - '.$end->format('d/m/Y'),
            ];
        }

        if ($filterType === 'month' && $year && $month) {
            $start = Carbon::create((int) $year, (int) $month, 1)->startOfMonth();

            return [
                'start' => $start->copy(),
                'end' => $start->copy()->endOfMonth()->startOfDay(),
                'label' => 'mwezi '.$start->format('F Y'),
            ];
        }

        if ($filterType === 'year' && $year) {
            $start = Carbon::create((int) $year, 1, 1)->startOfYear();

            return [
                'start' => $start->copy(),
                'end' => $start->copy()->endOfYear()->startOfDay(),
                'label' => 'mwaka '.$year,
            ];
        }

        return null;
    }

    public function statusFilterLabel(string $status): string
    {
        return match ($status) {
            'Present' => 'Present',
            'Absent' => 'Absent',
            default => $status,
        };
    }

    public function defaultStatusFilterTemplate(bool $includeSchool = false): string
    {
        $lines = [];
        if ($includeSchool) {
            $lines[] = '{SCHOOL_NAME}';
        }
        $lines[] = 'MZAZI WA {STUDENT_NAME}';
        $lines[] = 'Yafuatayo ni mahudhurio ya fingerprint {PERIOD}';
        $lines[] = '{STATUS_LINE}';
        $lines[] = '{DAY_LIST}';

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, int>|null  $studentIds
     * @return Collection<int, array<string, mixed>>
     */
    public function buildStatusFilterRecipients(
        int $schoolID,
        string $status,
        array $range,
        ?array $studentIds = null
    ): Collection {
        if (! in_array($status, ['Present', 'Absent'], true)) {
            return collect();
        }

        $workTime = StudentWorkTimeSetting::forSchool($schoolID);
        $workingDates = $this->workingDaysService->getWorkingDates(
            $schoolID,
            $range['start'],
            $range['end']
        );

        $query = Student::with(['parent', 'subclass.class'])->where('schoolID', $schoolID);
        if ($studentIds !== null) {
            if (empty($studentIds)) {
                return collect();
            }
            $query->whereIn('studentID', $studentIds);
        } else {
            $enrolled = app(\App\Services\StudentEnrollmentService::class)
                ->activeEnrollments($schoolID)
                ->pluck('studentID')
                ->unique()
                ->values()
                ->all();
            if (empty($enrolled)) {
                return collect();
            }
            $query->whereIn('studentID', $enrolled);
        }

        $students = $query->orderBy('first_name')->get();
        $rows = collect();

        foreach ($students as $student) {
            $tokens = $this->buildStatusFilterTokens(
                $schoolID,
                $student,
                $status,
                $range,
                $workingDates,
                (string) $workTime->work_start_time
            );
            if ($tokens === null) {
                continue;
            }
            $daysCount = (int) ($tokens['DAYS_COUNT'] ?? 0);
            if ($daysCount < 1) {
                continue;
            }

            $name = trim(implode(' ', array_filter([
                $student->first_name,
                $student->middle_name,
                $student->last_name,
            ])));

            $rows->push([
                'studentID' => $student->studentID,
                'studentName' => $name,
                'parentPhone' => $this->resolveParentPhone($student),
                'days_count' => $daysCount,
            ]);
        }

        return $rows->values();
    }

    /**
     * @param  array<int, string>  $workingDates
     * @return array<string, string>|null
     */
    public function buildStatusFilterTokens(
        int $schoolID,
        Student $student,
        string $status,
        array $range,
        array $workingDates,
        string $workStartTime = '07:30:00'
    ): ?array {
        $school = School::find($schoolID);
        $firstName = $student->first_name ?: trim(implode(' ', array_filter([
            $student->first_name,
            $student->middle_name,
            $student->last_name,
        ])));

        $records = StudentFingerprintAttendance::where('studentID', $student->studentID)
            ->whereBetween('attendance_date', [
                $range['start']->format('Y-m-d'),
                $range['end']->format('Y-m-d'),
            ])
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->attendance_date)->format('Y-m-d'));

        $dayLines = [];
        $dayPhrase = $status === 'Present' ? 'alikuwepo (check-in)' : 'hakuwepo (hakuna check-in)';

        if ($status === 'Absent') {
            foreach ($workingDates as $workDate) {
                $rec = $records->get($workDate);
                if (! $rec || empty($rec->check_in_time)) {
                    $d = Carbon::parse($workDate);
                    $dayLines[] = $d->format('d/m/Y').' '.$d->format('l').' - '.$dayPhrase;
                }
            }
        } else {
            foreach ($records as $dateStr => $rec) {
                if (empty($rec->check_in_time)) {
                    continue;
                }
                $d = Carbon::parse($dateStr);
                $dayLines[] = $d->format('d/m/Y').' '.$d->format('l').' - '.$dayPhrase;
            }
        }

        $daysCount = count($dayLines);
        if ($daysCount < 1) {
            return null;
        }

        $statusLine = $status === 'Present'
            ? "Amehudhuria siku {$daysCount}"
            : "Haja hudhuria siku {$daysCount}";

        $tokens = [
            'STUDENT_NAME' => $firstName,
            'PERIOD' => (string) $range['label'],
            'STATUS_LINE' => $statusLine,
            'DAY_LIST' => implode("\n", $dayLines),
            'DAYS_COUNT' => (string) $daysCount,
        ];

        if (! ($school && $school->senderID)) {
            $tokens['SCHOOL_NAME'] = $school?->school_name ?? 'Shule';
        }

        return $tokens;
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function fillStatusFilterTemplate(string $template, array $tokens): string
    {
        $msg = $template;
        foreach ($tokens as $key => $value) {
            $msg = str_replace('{'.$key.'}', $value, $msg);
        }
        $msg = str_replace('{SCHOOL_NAME}', '', $msg);
        $msg = preg_replace("/\n{3,}/", "\n\n", $msg) ?? $msg;
        $msg = trim($msg);

        return strlen($msg) > 480 ? substr($msg, 0, 477).'...' : $msg;
    }

    /**
     * @return array{template: string, sample_tokens: array<string, string>, sample_message: string, required_tokens: array<int, string>}|null
     */
    public function buildStatusFilterSample(
        int $schoolID,
        int $studentID,
        string $status,
        array $range
    ): ?array {
        $student = Student::where('schoolID', $schoolID)->where('studentID', $studentID)->first();
        if (! $student) {
            return null;
        }

        $workTime = StudentWorkTimeSetting::forSchool($schoolID);
        $workingDates = $this->workingDaysService->getWorkingDates($schoolID, $range['start'], $range['end']);
        $tokens = $this->buildStatusFilterTokens(
            $schoolID,
            $student,
            $status,
            $range,
            $workingDates,
            (string) $workTime->work_start_time
        );
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
        string $filterType,
        ?int $sentBy = null,
        ?string $date = null,
        ?string $year = null,
        ?string $month = null,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?string $messageTemplate = null
    ): array {
        $range = $this->resolveReportFilterPeriod($filterType, $date, $year, $month, $fromDate, $toDate);
        if (! $range) {
            return ['success' => false, 'error' => 'Invalid period filter.'];
        }
        if (! in_array($status, ['Present', 'Absent'], true)) {
            return ['success' => false, 'error' => 'Invalid status.'];
        }

        if ($messageTemplate !== null && trim($messageTemplate) !== '') {
            foreach (['{STUDENT_NAME}', '{PERIOD}', '{STATUS_LINE}', '{DAY_LIST}'] as $required) {
                if (stripos($messageTemplate, $required) === false) {
                    return ['success' => false, 'error' => 'SMS sample must keep locked values: '.$required];
                }
            }
        }

        $student = Student::with(['parent'])->where('schoolID', $schoolID)->where('studentID', $studentID)->first();
        if (! $student) {
            return ['success' => false, 'error' => 'Student not found.'];
        }

        $phone = $this->resolveParentPhone($student);
        if (! $phone) {
            return ['success' => false, 'error' => 'No valid parent phone number.'];
        }

        $workTime = StudentWorkTimeSetting::forSchool($schoolID);
        $workingDates = $this->workingDaysService->getWorkingDates($schoolID, $range['start'], $range['end']);
        $tokens = $this->buildStatusFilterTokens(
            $schoolID,
            $student,
            $status,
            $range,
            $workingDates,
            (string) $workTime->work_start_time
        );
        if ($tokens === null) {
            return ['success' => false, 'error' => 'No matching fingerprint days to send.'];
        }

        $template = ($messageTemplate !== null && trim($messageTemplate) !== '')
            ? $messageTemplate
            : $this->defaultStatusFilterTemplate(isset($tokens['SCHOOL_NAME']));
        $message = $this->fillStatusFilterTemplate($template, $tokens);

        $smsService = new SmsService();
        $result = $smsService->sendSms($phone, $message, null, $schoolID);

        if ($result['success'] ?? false) {
            $periodKey = substr(md5($status.'|'.$filterType.'|'.$range['start']->format('Y-m-d').'|'.$range['end']->format('Y-m-d')), 0, 16);
            FingerprintAttendanceSmsLog::updateOrCreate(
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

        return ['success' => false, 'error' => $result['message'] ?? 'Failed to send SMS'];
    }
}
