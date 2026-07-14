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
     * Manual send from report / teacher widget — always allowed (period from report filters).
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
            : '—';

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
                    .' – '.$reference->copy()->endOfWeek(Carbon::SUNDAY)->format('d M Y'),
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
        $result = $smsService->sendSms($phone, $message);

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
}
