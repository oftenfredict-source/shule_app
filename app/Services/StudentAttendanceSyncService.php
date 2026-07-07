<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Models\StudentFingerprintAttendance;
use Carbon\Carbon;

class StudentAttendanceSyncService
{
    /**
     * @return array<int, array{model: Student}>
     */
    public function getStudentsMap(int $schoolID): array
    {
        $map = [];

        $students = Student::with('subclass.class')
            ->where('schoolID', $schoolID)
            ->where('status', '!=', 'Transferred')
            ->get();

        foreach ($students as $student) {
            $id = (int) $student->studentID;
            $map[$id] = ['model' => $student];
            if ($student->fingerprint_id && (int) $student->fingerprint_id !== $id) {
                $map[(int) $student->fingerprint_id] = ['model' => $student];
            }
        }

        return $map;
    }

    /**
     * @param array<int, array<string, mixed>> $logs
     * @param array<int, array{model: Student}> $studentsMap
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function extractUserDateRecords(array $logs, array $studentsMap, ?string $onlyDate, string $deviceIp): array
    {
        $userDateRecords = [];

        foreach ($logs as $record) {
            $userId = isset($record['user_id']) ? (int) $record['user_id'] : 0;
            if ($userId <= 0 || empty($record['record_time'])) {
                continue;
            }

            $recordTime = Carbon::parse((string) $record['record_time']);
            $recordDate = $recordTime->toDateString();

            if ($onlyDate !== null && $recordDate !== $onlyDate) {
                continue;
            }

            $entry = $studentsMap[$userId] ?? null;
            if (! $entry) {
                continue;
            }

            /** @var Student $student */
            $student = $entry['model'];
            $key = 'student_'.(int) $student->studentID.'_'.$recordDate;

            if (! isset($userDateRecords[$key])) {
                $userDateRecords[$key] = [];
            }

            $userDateRecords[$key][] = [
                'time' => $recordTime,
                'type' => $record['type'] ?? 'Fingerprint',
                'device_ip' => $record['device_ip'] ?? $deviceIp,
                'student' => $student,
            ];
        }

        return $userDateRecords;
    }

    /**
     * Bulk sync: insert only student/day rows not already in DB; update check-out when missing.
     *
     * @param array<string, array<int, array<string, mixed>>> $userDateRecords
     * @return array{synced:int,skipped_existing:int,updated_checkout:int,device_days_matched:int}
     */
    public function persistBulkSyncSkipExisting(int $schoolID, array $userDateRecords): array
    {
        $studentIds = Student::where('schoolID', $schoolID)
            ->where('status', '!=', 'Transferred')
            ->pluck('studentID');

        $existingPairs = [];
        if ($studentIds->isNotEmpty()) {
            StudentFingerprintAttendance::whereIn('studentID', $studentIds)
                ->select(['studentID', 'attendance_date', 'check_out_time'])
                ->cursor()
                ->each(function ($row) use (&$existingPairs) {
                    $date = $row->attendance_date instanceof Carbon
                        ? $row->attendance_date->toDateString()
                        : (string) $row->attendance_date;
                    $existingPairs['student_'.$row->studentID.'_'.$date] = true;
                });
        }

        $synced = 0;
        $skippedExisting = 0;
        $updatedCheckout = 0;
        $deviceDaysMatched = 0;

        foreach ($userDateRecords as $records) {
            if (count($records) < 1) {
                continue;
            }

            $deviceDaysMatched++;
            /** @var Student $student */
            $student = $records[0]['student'];
            $recordDate = $records[0]['time']->toDateString();
            $pairKey = 'student_'.$student->studentID.'_'.$recordDate;
            $studentName = trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''));

            $existing = isset($existingPairs[$pairKey])
                ? StudentFingerprintAttendance::where('studentID', $student->studentID)
                    ->whereDate('attendance_date', $recordDate)
                    ->first()
                : null;

            $times = $this->resolveDayCheckInOut($records, $existing);

            if ($existing) {
                $needsCheckout = ! $existing->check_out_time && $times['check_out'];
                if ($needsCheckout) {
                    $existing->update([
                        'check_in_time' => $times['check_in'],
                        'check_out_time' => $times['check_out'],
                        'status' => 1,
                        'verify_mode' => $times['verify_mode'],
                        'device_ip' => $times['device_ip'] ?? $existing->device_ip,
                    ]);
                    $updatedCheckout++;
                } else {
                    $skippedExisting++;
                }
                continue;
            }

            StudentFingerprintAttendance::create([
                'studentID' => $student->studentID,
                'user_id' => $student->studentID,
                'user_name' => $studentName !== '' ? $studentName : 'N/A',
                'enroll_id' => $student->fingerprint_id ?? $student->studentID,
                'attendance_date' => $recordDate,
                'check_in_time' => $times['check_in'],
                'check_out_time' => $times['check_out'],
                'status' => $times['check_out'] ? 1 : 0,
                'verify_mode' => $times['verify_mode'],
                'device_ip' => $times['device_ip'],
            ]);

            $existingPairs[$pairKey] = true;
            $synced++;
        }

        return [
            'synced' => $synced,
            'skipped_existing' => $skippedExisting,
            'updated_checkout' => $updatedCheckout,
            'device_days_matched' => $deviceDaysMatched,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array{check_in: string, check_out: string|null, verify_mode: string|null, device_ip: string|null}
     */
    public function resolveDayCheckInOut(array $records, $existing = null): array
    {
        usort($records, function ($a, $b) {
            if ($a['time']->eq($b['time'])) {
                return 0;
            }

            return $a['time']->lt($b['time']) ? -1 : 1;
        });

        $firstRecord = $records[0];
        $secondRecord = count($records) >= 2 ? $records[1] : null;

        $checkIn = $firstRecord['time']->format('Y-m-d H:i:s');
        $checkOut = $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null;

        if ($existing) {
            if ($existing->check_in_time) {
                $existingIn = Carbon::parse($existing->check_in_time);
                if ($existingIn->lt($firstRecord['time'])) {
                    $checkIn = $existingIn->format('Y-m-d H:i:s');
                }
            }
            if (! $checkOut && $existing->check_out_time) {
                $checkOut = $existing->check_out_time->format('Y-m-d H:i:s');
            }
        }

        return [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'verify_mode' => $firstRecord['type'] ?? 'Fingerprint',
            'device_ip' => $firstRecord['device_ip'] ?? null,
        ];
    }
}
