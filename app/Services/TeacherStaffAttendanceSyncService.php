<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OtherStaff;
use App\Models\StaffFingerprintAttendance;
use App\Models\Teacher;
use App\Models\TeacherFingerprintAttendance;
use Carbon\Carbon;

class TeacherStaffAttendanceSyncService
{
    /**
     * @return array<int, array{type: string, model: Teacher|OtherStaff}>
     */
    public function getPeopleMap(int $schoolID): array
    {
        $map = [];

        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'Active')->get();
        foreach ($teachers as $teacher) {
            $map[(int) $teacher->id] = ['type' => 'teacher', 'model' => $teacher];
            if ($teacher->fingerprint_id) {
                $map[(int) $teacher->fingerprint_id] = ['type' => 'teacher', 'model' => $teacher];
            }
        }

        $staffMembers = OtherStaff::where('schoolID', $schoolID)->where('status', 'Active')->get();
        foreach ($staffMembers as $staff) {
            $map[(int) $staff->id] = ['type' => 'staff', 'model' => $staff];
            if ($staff->fingerprint_id) {
                $map[(int) $staff->fingerprint_id] = ['type' => 'staff', 'model' => $staff];
            }
        }

        return $map;
    }

    /**
     * @param array<int, array<string, mixed>> $logs
     * @param array<int, array{type: string, model: Teacher|OtherStaff}> $peopleMap
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function extractUserDateRecords(array $logs, array $peopleMap, ?string $onlyDate, string $deviceIp): array
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

            $personEntry = $peopleMap[$userId] ?? null;
            if (!$personEntry) {
                continue;
            }

            $person = $personEntry['model'];
            $personType = $personEntry['type'];
            $key = $personType . '_' . (int) $person->id . '_' . $recordDate;

            if (!isset($userDateRecords[$key])) {
                $userDateRecords[$key] = [];
            }

            $userDateRecords[$key][] = [
                'time' => $recordTime,
                'type' => $record['type'] ?? 'Fingerprint',
                'device_ip' => $record['device_ip'] ?? $deviceIp,
                'person' => $person,
                'person_type' => $personType,
            ];
        }

        return $userDateRecords;
    }

    /**
     * Bulk sync: insert only person/day rows not already in DB; update check-out when missing.
     *
     * @param array<string, array<int, array<string, mixed>>> $userDateRecords
     * @return array{synced:int,skipped_existing:int,updated_checkout:int,device_days_matched:int}
     */
    public function persistBulkSyncSkipExisting(int $schoolID, array $userDateRecords): array
    {
        $teacherIds = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('id');

        $staffIds = OtherStaff::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('id');

        $existingTeacherPairs = [];
        if ($teacherIds->isNotEmpty()) {
            TeacherFingerprintAttendance::whereIn('teacherID', $teacherIds)
                ->select(['teacherID', 'attendance_date', 'check_out_time'])
                ->cursor()
                ->each(function ($row) use (&$existingTeacherPairs) {
                    $date = $row->attendance_date instanceof Carbon
                        ? $row->attendance_date->toDateString()
                        : (string) $row->attendance_date;
                    $existingTeacherPairs['teacher_' . $row->teacherID . '_' . $date] = true;
                });
        }

        $existingStaffPairs = [];
        if ($staffIds->isNotEmpty()) {
            StaffFingerprintAttendance::whereIn('staffID', $staffIds)
                ->select(['staffID', 'attendance_date', 'check_out_time'])
                ->cursor()
                ->each(function ($row) use (&$existingStaffPairs) {
                    $date = $row->attendance_date instanceof Carbon
                        ? $row->attendance_date->toDateString()
                        : (string) $row->attendance_date;
                    $existingStaffPairs['staff_' . $row->staffID . '_' . $date] = true;
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
            $personType = $records[0]['person_type'] ?? '';
            $person = $records[0]['person'];
            $recordDate = $records[0]['time']->toDateString();

            if ($personType === 'teacher') {
                /** @var Teacher $person */
                $pairKey = 'teacher_' . $person->id . '_' . $recordDate;
                $personName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));

                $existing = isset($existingTeacherPairs[$pairKey])
                    ? TeacherFingerprintAttendance::where('teacherID', $person->id)
                        ->whereDate('attendance_date', $recordDate)
                        ->first()
                    : null;

                $times = $this->resolveDayCheckInOut($records, $existing);

                if ($existing) {
                    $needsCheckout = !$existing->check_out_time && $times['check_out'];
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

                TeacherFingerprintAttendance::create([
                    'teacherID' => $person->id,
                    'user_id' => $person->id,
                    'user_name' => $personName ?: 'N/A',
                    'enroll_id' => $person->fingerprint_id ?? $person->id,
                    'attendance_date' => $recordDate,
                    'check_in_time' => $times['check_in'],
                    'check_out_time' => $times['check_out'],
                    'status' => $times['check_out'] ? 1 : 0,
                    'verify_mode' => $times['verify_mode'],
                    'device_ip' => $times['device_ip'],
                ]);

                $existingTeacherPairs[$pairKey] = true;
                $synced++;
                continue;
            }

            if ($personType === 'staff') {
                /** @var OtherStaff $person */
                $pairKey = 'staff_' . $person->id . '_' . $recordDate;
                $personName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));

                $existing = isset($existingStaffPairs[$pairKey])
                    ? StaffFingerprintAttendance::where('staffID', $person->id)
                        ->whereDate('attendance_date', $recordDate)
                        ->first()
                    : null;

                $times = $this->resolveDayCheckInOut($records, $existing);

                if ($existing) {
                    $needsCheckout = !$existing->check_out_time && $times['check_out'];
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

                StaffFingerprintAttendance::create([
                    'staffID' => $person->id,
                    'user_id' => $person->id,
                    'user_name' => $personName ?: 'N/A',
                    'enroll_id' => $person->fingerprint_id ?? $person->id,
                    'attendance_date' => $recordDate,
                    'check_in_time' => $times['check_in'],
                    'check_out_time' => $times['check_out'],
                    'status' => $times['check_out'] ? 1 : 0,
                    'verify_mode' => $times['verify_mode'],
                    'device_ip' => $times['device_ip'],
                ]);

                $existingStaffPairs[$pairKey] = true;
                $synced++;
            }
        }

        return [
            'synced' => $synced,
            'skipped_existing' => $skippedExisting,
            'updated_checkout' => $updatedCheckout,
            'device_days_matched' => $deviceDaysMatched,
        ];
    }

    /**
     * First punch = check-in, second punch = check-out.
     *
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
            if (!$checkOut && $existing->check_out_time) {
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
