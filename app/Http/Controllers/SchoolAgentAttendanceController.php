<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StaffFingerprintAttendance;
use App\Models\Teacher;
use App\Models\TeacherFingerprintAttendance;
use App\Services\TeacherStaffLiveEventBuffer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchoolAgentAttendanceController extends Controller
{
    /**
     * Local attendance-agent sends live punches (teacher + staff) here.
     * Each punch is applied in order: 1st of the day = Sign In, 2nd = Sign Out.
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
        $peopleMap = $this->getTeacherStaffPeopleMap($schoolID);

        $normalized = [];
        foreach ($validated['punches'] as $p) {
            $normalized[] = [
                'user_id' => (int) ($p['user_id'] ?? 0),
                'record_time' => Carbon::parse((string) ($p['record_time'] ?? '')),
                'type' => (string) ($p['type'] ?? 'Fingerprint'),
                'device_ip' => trim((string) ($p['device_ip'] ?? $deviceIpTop)),
            ];
        }

        usort($normalized, function ($a, $b) {
            if ($a['record_time']->eq($b['record_time'])) {
                return 0;
            }

            return $a['record_time']->lt($b['record_time']) ? -1 : 1;
        });

        $events = [];
        $syncedCount = 0;
        $updatedCount = 0;

        foreach ($normalized as $punch) {
            $event = $this->applySingleLivePunch($peopleMap, $punch, $deviceIpTop);
            if ($event === null) {
                continue;
            }

            $events[] = $event;
            TeacherStaffLiveEventBuffer::push($schoolID, $event);
            if (!empty($event['matched'])) {
                if (!empty($event['created'])) {
                    $syncedCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($events)
                ? 'Live attendance updated.'
                : 'No new punches to save.',
            'events' => $events,
            'synced' => $syncedCount,
            'updated' => $updatedCount,
        ]);
    }

    /**
     * @param array<int, array{type:string, model: Teacher|OtherStaff}> $peopleMap
     * @param array{user_id:int,record_time:Carbon,type:string,device_ip:string} $punch
     * @return array<string, mixed>|null
     */
    private function applySingleLivePunch(array $peopleMap, array $punch, string $deviceIpTop): ?array
    {
        $userId = $punch['user_id'];
        $punchTime = $punch['record_time'];
        $recordDate = $punchTime->toDateString();
        $punchFormatted = $punchTime->format('Y-m-d H:i:s');

        $entry = $peopleMap[$userId] ?? null;
        if (!$entry) {
            return [
                'matched' => false,
                'user_id' => $userId,
                'record_time' => $punchFormatted,
                'action' => 'Unknown',
                'message' => 'Enroll ID not found in school teachers/staff.',
            ];
        }

        $personType = $entry['type'];
        /** @var Teacher|OtherStaff $person */
        $person = $entry['model'];
        $name = trim(($person->first_name ?? '') . ' ' . ($person->middle_name ?? '') . ' ' . ($person->last_name ?? ''));
        $verifyMode = $punch['type'] ?: 'Fingerprint';
        $resolvedDeviceIp = $punch['device_ip'] !== '' ? $punch['device_ip'] : ($deviceIpTop !== '' ? $deviceIpTop : null);

        if ($personType === 'teacher') {
            return $this->applyTeacherLivePunch($person, $recordDate, $punchTime, $punchFormatted, $name, $verifyMode, $resolvedDeviceIp);
        }

        return $this->applyStaffLivePunch($person, $recordDate, $punchTime, $punchFormatted, $name, $verifyMode, $resolvedDeviceIp);
    }

    /**
     * @return array<string, mixed>
     */
    private function applyTeacherLivePunch(
        Teacher $person,
        string $recordDate,
        Carbon $punchTime,
        string $punchFormatted,
        string $name,
        string $verifyMode,
        ?string $deviceIp
    ): array {
        $existing = TeacherFingerprintAttendance::where('teacherID', $person->id)
            ->whereDate('attendance_date', $recordDate)
            ->first();

        $action = 'Sign In';
        $created = false;

        if (!$existing || !$existing->check_in_time) {
            TeacherFingerprintAttendance::updateOrCreate(
                ['teacherID' => $person->id, 'attendance_date' => $recordDate],
                [
                    'user_id' => $person->id,
                    'user_name' => $name !== '' ? $name : 'N/A',
                    'enroll_id' => $person->fingerprint_id ?? $person->id,
                    'check_in_time' => $punchFormatted,
                    'check_out_time' => null,
                    'status' => 0,
                    'verify_mode' => $verifyMode,
                    'device_ip' => $deviceIp,
                ]
            );
            $created = !$existing;
        } elseif (!$existing->check_out_time) {
            $checkIn = Carbon::parse($existing->check_in_time);
            if ($punchTime->lte($checkIn)) {
                return $this->skipEvent('teacher', $person, $punchFormatted, $name, false);
            }

            $existing->update([
                'check_out_time' => $punchFormatted,
                'status' => 1,
                'verify_mode' => $verifyMode,
                'device_ip' => $deviceIp ?? $existing->device_ip,
            ]);
            $action = 'Sign Out';
        } else {
            return $this->skipEvent('teacher', $person, $punchFormatted, $name, true);
        }

        $hasCheckOut = $action === 'Sign Out';

        return [
            'matched' => true,
            'created' => $created,
            'person_type' => 'teacher',
            'user_id' => (int) $person->id,
            'user_name' => $name !== '' ? $name : 'N/A',
            'record_time' => $punchFormatted,
            'action' => $action,
            'status' => $hasCheckOut ? 'Complete' : 'Check In Only',
            'sent_to_live' => true,
            'message' => 'Sent to live system',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function applyStaffLivePunch(
        OtherStaff $person,
        string $recordDate,
        Carbon $punchTime,
        string $punchFormatted,
        string $name,
        string $verifyMode,
        ?string $deviceIp
    ): array {
        $existing = StaffFingerprintAttendance::where('staffID', $person->id)
            ->whereDate('attendance_date', $recordDate)
            ->first();

        $action = 'Sign In';
        $created = false;

        if (!$existing || !$existing->check_in_time) {
            StaffFingerprintAttendance::updateOrCreate(
                ['staffID' => $person->id, 'attendance_date' => $recordDate],
                [
                    'user_id' => $person->id,
                    'user_name' => $name !== '' ? $name : 'N/A',
                    'enroll_id' => $person->fingerprint_id ?? $person->id,
                    'check_in_time' => $punchFormatted,
                    'check_out_time' => null,
                    'status' => 0,
                    'verify_mode' => $verifyMode,
                    'device_ip' => $deviceIp,
                ]
            );
            $created = !$existing;
        } elseif (!$existing->check_out_time) {
            $checkIn = Carbon::parse($existing->check_in_time);
            if ($punchTime->lte($checkIn)) {
                return $this->skipEvent('staff', $person, $punchFormatted, $name, false);
            }

            $existing->update([
                'check_out_time' => $punchFormatted,
                'status' => 1,
                'verify_mode' => $verifyMode,
                'device_ip' => $deviceIp ?? $existing->device_ip,
            ]);
            $action = 'Sign Out';
        } else {
            return $this->skipEvent('staff', $person, $punchFormatted, $name, true);
        }

        $hasCheckOut = $action === 'Sign Out';

        return [
            'matched' => true,
            'created' => $created,
            'person_type' => 'staff',
            'user_id' => (int) $person->id,
            'user_name' => $name !== '' ? $name : 'N/A',
            'record_time' => $punchFormatted,
            'action' => $action,
            'status' => $hasCheckOut ? 'Complete' : 'Check In Only',
            'sent_to_live' => true,
            'message' => 'Sent to live system',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function skipEvent(string $personType, $person, string $punchFormatted, string $name, bool $dayComplete = false): array
    {
        if ($dayComplete) {
            return [
                'matched' => true,
                'created' => false,
                'skipped' => true,
                'person_type' => $personType,
                'user_id' => (int) $person->id,
                'user_name' => $name !== '' ? $name : 'N/A',
                'record_time' => $punchFormatted,
                'action' => 'Ready',
                'status' => 'Complete',
                'message' => 'This user already signed in and signed out.',
            ];
        }

        return [
            'matched' => true,
            'created' => false,
            'skipped' => true,
            'person_type' => $personType,
            'user_id' => (int) $person->id,
            'user_name' => $name !== '' ? $name : 'N/A',
            'record_time' => $punchFormatted,
            'action' => 'Ignored',
            'message' => 'Duplicate punch ignored.',
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
