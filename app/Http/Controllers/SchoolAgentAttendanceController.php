<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StaffFingerprintAttendance;
use App\Models\Teacher;
use App\Models\TeacherFingerprintAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchoolAgentAttendanceController extends Controller
{
    /**
     * Local attendance-agent sends live punches (teacher + staff) here.
     *
     * Payload:
     * {
     *   "device_ip": "192.168.100.10",
     *   "punches": [
     *     { "user_id": 5, "record_time": "2026-07-02 08:10:00", "type": "Fingerprint" }
     *   ]
     * }
     */
    public function ingestTeacherStaffLive(Request $request)
    {
        $schoolAgent = $request->attributes->get('school_agent');
        $schoolID = (int) (($schoolAgent['schoolID'] ?? 0));
        if ($schoolID <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'School session not found. Please log in again.',
            ], 403);
        }

        $validated = $request->validate([
            'device_ip' => 'nullable|string|max:45',
            'punches' => 'required|array|min:1',
            'punches.*.user_id' => 'required|integer',
            'punches.*.record_time' => 'required|string',
            'punches.*.type' => 'nullable|string|max:80',
            'punches.*.device_ip' => 'nullable|string|max:45',
        ]);

        $deviceIpTop = trim((string) ($validated['device_ip'] ?? ''));
        $punches = $validated['punches'] ?? [];

        $peopleMap = $this->getTeacherStaffPeopleMap($schoolID);

        // Group by person + date so we can reuse first/second logic.
        $groups = [];
        $unknownPunches = [];

        foreach ($punches as $p) {
            $userId = (int) ($p['user_id'] ?? 0);
            $recordTime = Carbon::parse((string) ($p['record_time'] ?? ''));
            $recordDate = $recordTime->toDateString();

            $entry = $peopleMap[$userId] ?? null;
            if (!$entry) {
                $unknownPunches[] = [
                    'user_id' => $userId,
                    'record_time' => $recordTime->format('Y-m-d H:i:s'),
                    'matched' => false,
                    'message' => 'Enroll ID not found in school teachers/staff.',
                ];
                continue;
            }

            $personType = $entry['type']; // teacher|staff
            $person = $entry['model'];    // Teacher|OtherStaff
            $key = $personType . '_' . (int) $person->id . '_' . $recordDate;

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'person_type' => $personType,
                    'person' => $person,
                    'date' => $recordDate,
                    'records' => [],
                ];
            }

            $groups[$key]['records'][] = [
                'time' => $recordTime,
                'type' => (string) ($p['type'] ?? 'Fingerprint'),
                'device_ip' => trim((string) ($p['device_ip'] ?? $deviceIpTop)),
            ];
        }

        if (empty($groups)) {
            return response()->json([
                'success' => true,
                'message' => 'No matching teacher/staff found for the sent punches.',
                'processed_groups' => 0,
                'saved_groups' => 0,
                'events' => $unknownPunches,
            ]);
        }

        $syncedCount = 0;
        $updatedCount = 0;
        $events = $unknownPunches;

        foreach ($groups as $group) {
            $records = $group['records'];
            if (count($records) < 1) {
                continue;
            }

            usort($records, function ($a, $b) {
                if ($a['time']->eq($b['time'])) return 0;
                return $a['time']->lt($b['time']) ? -1 : 1;
            });

            $firstRecord = $records[0];
            $secondRecord = count($records) >= 2 ? $records[1] : null;

            $checkIn = $firstRecord['time']->format('Y-m-d H:i:s');
            $checkOut = $secondRecord ? $secondRecord['time']->format('Y-m-d H:i:s') : null;
            $verifyMode = $firstRecord['type'] ?? 'Fingerprint';
            $resolvedDeviceIp = $firstRecord['device_ip'] ?? ($deviceIpTop !== '' ? $deviceIpTop : null);

            $personType = $group['person_type'];
            $person = $group['person'];
            $recordDate = $group['date'];

            if ($personType === 'teacher') {
                /** @var Teacher $person */
                $existing = TeacherFingerprintAttendance::where('teacherID', $person->id)
                    ->whereDate('attendance_date', $recordDate)
                    ->first();

                if ($existing && $existing->check_in_time) {
                    $existingIn = Carbon::parse($existing->check_in_time);
                    if ($existingIn->lt($firstRecord['time'])) {
                        $checkIn = $existingIn->format('Y-m-d H:i:s');
                    }
                }

                if (!$checkOut && $existing && $existing->check_out_time) {
                    $checkOut = $existing->check_out_time->format('Y-m-d H:i:s');
                }

                $teacherName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));
                $status = $checkOut ? 1 : 0;

                $wasExisting = (bool) $existing;
                TeacherFingerprintAttendance::updateOrCreate(
                    [
                        'teacherID' => $person->id,
                        'attendance_date' => $recordDate,
                    ],
                    [
                        'user_id' => $person->id,
                        'user_name' => $teacherName !== '' ? $teacherName : 'N/A',
                        'enroll_id' => $person->fingerprint_id ?? $person->id,
                        'check_in_time' => $checkIn,
                        'check_out_time' => $checkOut,
                        'status' => $status,
                        'verify_mode' => $verifyMode,
                        'device_ip' => $resolvedDeviceIp,
                    ]
                );

                if ($wasExisting) {
                    $updatedCount++;
                } else {
                    $syncedCount++;
                }

                foreach ($records as $rec) {
                    $events[] = $this->buildLiveEventFromRecord(
                        'teacher',
                        $person,
                        $rec['time'],
                        $checkIn,
                        $checkOut
                    );
                }
                continue;
            }

            if ($personType === 'staff') {
                /** @var OtherStaff $person */
                $existing = StaffFingerprintAttendance::where('staffID', $person->id)
                    ->whereDate('attendance_date', $recordDate)
                    ->first();

                if ($existing && $existing->check_in_time) {
                    $existingIn = Carbon::parse($existing->check_in_time);
                    if ($existingIn->lt($firstRecord['time'])) {
                        $checkIn = $existingIn->format('Y-m-d H:i:s');
                    }
                }

                if (!$checkOut && $existing && $existing->check_out_time) {
                    $checkOut = $existing->check_out_time->format('Y-m-d H:i:s');
                }

                $staffName = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));
                $status = $checkOut ? 1 : 0;

                $wasExisting = (bool) $existing;
                StaffFingerprintAttendance::updateOrCreate(
                    [
                        'staffID' => $person->id,
                        'attendance_date' => $recordDate,
                    ],
                    [
                        'user_id' => $person->id,
                        'user_name' => $staffName !== '' ? $staffName : 'N/A',
                        'enroll_id' => $person->fingerprint_id ?? $person->id,
                        'check_in_time' => $checkIn,
                        'check_out_time' => $checkOut,
                        'status' => $status,
                        'verify_mode' => $verifyMode,
                        'device_ip' => $resolvedDeviceIp,
                    ]
                );

                if ($wasExisting) {
                    $updatedCount++;
                } else {
                    $syncedCount++;
                }

                foreach ($records as $rec) {
                    $events[] = $this->buildLiveEventFromRecord(
                        'staff',
                        $person,
                        $rec['time'],
                        $checkIn,
                        $checkOut
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Live teacher/staff punches saved to database.',
            'processed_groups' => count($groups),
            'saved_groups' => $syncedCount + $updatedCount,
            'synced' => $syncedCount,
            'updated' => $updatedCount,
            'events' => $events,
        ]);
    }

    /**
     * @param Teacher|OtherStaff $person
     * @return array<string, mixed>
     */
    private function buildLiveEventFromRecord(
        string $personType,
        $person,
        Carbon $punchTime,
        string $checkIn,
        ?string $checkOut
    ): array {
        $name = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));
        $punchFormatted = $punchTime->format('Y-m-d H:i:s');

        $action = 'Sign In';
        if ($checkOut && $punchFormatted === $checkOut) {
            $action = 'Sign Out';
        } elseif ($checkIn && $punchFormatted !== $checkIn && $checkOut) {
            $action = 'Sign Out';
        }

        $statusLabel = $checkOut ? 'Complete' : 'Check In Only';

        return [
            'matched' => true,
            'person_type' => $personType,
            'user_id' => (int) $person->id,
            'user_name' => $name !== '' ? $name : 'N/A',
            'record_time' => $punchFormatted,
            'action' => $action,
            'status' => $statusLabel,
            'sent_to_live' => true,
            'message' => 'Sent to live system',
        ];
    }

    /**
     * @return array<int, array{type:string, model: Teacher|OtherStaff}>
     */
    private function getTeacherStaffPeopleMap(int $schoolID): array
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
}

