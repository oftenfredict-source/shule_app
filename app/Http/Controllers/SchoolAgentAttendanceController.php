<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StaffFingerprintAttendance;
use App\Models\Student;
use App\Models\StudentFingerprintAttendance;
use App\Models\Teacher;
use App\Models\TeacherFingerprintAttendance;
use App\Models\TeacherStaffWorkTimeSetting;
use App\Services\StudentAttendanceSyncService;
use App\Services\StudentLiveEventBuffer;
use App\Services\TeacherStaffAttendanceSyncService;
use App\Services\TeacherStaffLiveEventBuffer;
use App\Services\TeacherStaffPermissionAttendanceService;
use App\Services\StudentPermissionAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchoolAgentAttendanceController extends Controller
{
    /**
     * Local attendance-agent sends live punches (teacher + staff) here.
     * Server interprets punches using the check-in window (see TeacherStaffAttendanceSyncService).
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
            $event = $this->applySingleLivePunch($peopleMap, $punch, $deviceIpTop, $schoolID);
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
     * Local attendance-agent bulk sync: all device logs → live DB.
     * Uses the same skip-existing + check-out update rules as the web sync.
     */
    public function syncTeacherStaffBulk(Request $request, TeacherStaffAttendanceSyncService $syncService)
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
            'device_label' => 'nullable|string|max:120',
            'records' => 'required|array|min:1',
            'records.*.user_id' => 'required|integer',
            'records.*.record_time' => 'required|string',
            'records.*.type' => 'nullable|string|max:80',
            'records.*.device_ip' => 'nullable|string|max:45',
        ]);

        set_time_limit(300);

        $peopleMap = $syncService->getPeopleMap($schoolID);
        if (empty($peopleMap)) {
            return response()->json([
                'success' => false,
                'message' => 'No active teachers or staff found for this school.',
            ], 400);
        }

        $deviceIp = trim((string) ($validated['device_ip'] ?? ''));
        $deviceLabel = trim((string) ($validated['device_label'] ?? ''));
        $logs = [];

        foreach ($validated['records'] as $record) {
            $logs[] = [
                'user_id' => (int) ($record['user_id'] ?? 0),
                'record_time' => (string) ($record['record_time'] ?? ''),
                'type' => (string) ($record['type'] ?? 'Fingerprint'),
                'device_ip' => trim((string) ($record['device_ip'] ?? $deviceIp)),
            ];
        }

        $userDateRecords = $syncService->extractUserDateRecords($logs, $peopleMap, null, $deviceIp);
        $persistStats = $syncService->persistBulkSyncSkipExisting($schoolID, $userDateRecords);

        $label = $deviceLabel !== '' ? $deviceLabel : ($deviceIp !== '' ? $deviceIp : 'device');
        $synced = $persistStats['synced'];
        $skipped = $persistStats['skipped_existing'];
        $updatedCheckout = $persistStats['updated_checkout'];

        $message = "Synced from {$label}. {$synced} new day(s) saved to database.";
        if ($updatedCheckout > 0) {
            $message .= " {$updatedCheckout} check-out time(s) updated.";
        }
        if ($skipped > 0) {
            $message .= " {$skipped} already complete in database (skipped).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'synced' => $synced,
            'skipped_existing' => $skipped,
            'updated_checkout' => $updatedCheckout,
            'device_days_matched' => $persistStats['device_days_matched'],
            'records_received' => count($logs),
        ]);
    }

    /**
     * @param array<int, array{type:string, model: Teacher|OtherStaff}> $peopleMap
     * @param array{user_id:int,record_time:Carbon,type:string,device_ip:string} $punch
     * @return array<string, mixed>|null
     */
    private function applySingleLivePunch(array $peopleMap, array $punch, string $deviceIpTop, int $schoolID): ?array
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
            return $this->applyTeacherLivePunch($person, $recordDate, $punchTime, $punchFormatted, $name, $verifyMode, $resolvedDeviceIp, $schoolID);
        }

        return $this->applyStaffLivePunch($person, $recordDate, $punchTime, $punchFormatted, $name, $verifyMode, $resolvedDeviceIp, $schoolID);
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
        ?string $deviceIp,
        int $schoolID
    ): array {
        $permissionService = app(TeacherStaffPermissionAttendanceService::class);
        if ($permissionService->isPermittedToday($schoolID, 'teacher', (int) $person->id, $recordDate)) {
            return $this->permittedEvent('teacher', $person, $punchFormatted, $name);
        }

        /** @var TeacherStaffAttendanceSyncService $syncService */
        $syncService = app(TeacherStaffAttendanceSyncService::class);
        $workStartTime = (string) TeacherStaffWorkTimeSetting::forSchool($schoolID)->work_start_time;

        $existing = TeacherFingerprintAttendance::where('teacherID', $person->id)
            ->whereDate('attendance_date', $recordDate)
            ->first();

        if ($existing && $existing->check_out_time) {
            $bounds = $syncService->getCheckInWindowBounds($recordDate, $workStartTime);
            if (Carbon::parse($existing->check_out_time)->lte($bounds['end'])) {
                $existing->update(['check_out_time' => null, 'status' => 0]);
                $existing->refresh();
            }
        }

        $hasCheckIn = $existing && $existing->check_in_time;
        $hasCheckOut = $existing && $existing->check_out_time;
        $classification = $syncService->classifyLivePunch(
            $punchTime,
            $recordDate,
            $workStartTime,
            (bool) $hasCheckIn,
            (bool) $hasCheckOut
        );

        if ($classification === 'ignore') {
            return $this->skipEvent('teacher', $person, $punchFormatted, $name, (bool) $hasCheckOut);
        }

        $action = $classification === 'sign_out' ? 'Sign Out' : 'Sign In';
        $created = false;

        if ($classification === 'sign_in') {
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
        } else {
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
        ?string $deviceIp,
        int $schoolID
    ): array {
        $permissionService = app(TeacherStaffPermissionAttendanceService::class);
        if ($permissionService->isPermittedToday($schoolID, 'staff', (int) $person->id, $recordDate)) {
            return $this->permittedEvent('staff', $person, $punchFormatted, $name);
        }

        /** @var TeacherStaffAttendanceSyncService $syncService */
        $syncService = app(TeacherStaffAttendanceSyncService::class);
        $workStartTime = (string) TeacherStaffWorkTimeSetting::forSchool($schoolID)->work_start_time;

        $existing = StaffFingerprintAttendance::where('staffID', $person->id)
            ->whereDate('attendance_date', $recordDate)
            ->first();

        if ($existing && $existing->check_out_time) {
            $bounds = $syncService->getCheckInWindowBounds($recordDate, $workStartTime);
            if (Carbon::parse($existing->check_out_time)->lte($bounds['end'])) {
                $existing->update(['check_out_time' => null, 'status' => 0]);
                $existing->refresh();
            }
        }

        $hasCheckIn = $existing && $existing->check_in_time;
        $hasCheckOut = $existing && $existing->check_out_time;
        $classification = $syncService->classifyLivePunch(
            $punchTime,
            $recordDate,
            $workStartTime,
            (bool) $hasCheckIn,
            (bool) $hasCheckOut
        );

        if ($classification === 'ignore') {
            return $this->skipEvent('staff', $person, $punchFormatted, $name, (bool) $hasCheckOut);
        }

        $action = $classification === 'sign_out' ? 'Sign Out' : 'Sign In';
        $created = false;

        if ($classification === 'sign_in') {
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
        } else {
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
    private function permittedEvent(string $personType, $person, string $punchFormatted, string $name): array
    {
        return [
            'matched' => true,
            'created' => false,
            'skipped' => true,
            'person_type' => $personType,
            'user_id' => (int) $person->id,
            'user_name' => $name !== '' ? $name : 'N/A',
            'record_time' => $punchFormatted,
            'action' => 'Permitted',
            'status' => 'Permitted',
            'message' => 'Approved permission — counted as Permitted.',
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
     * Local attendance-agent sends live student punches here.
     */
    public function ingestStudentLive(Request $request)
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
        $studentsMap = $this->getStudentsMap($schoolID);

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
            $event = $this->applySingleStudentLivePunch($studentsMap, $punch, $deviceIpTop);
            if ($event === null) {
                continue;
            }

            $events[] = $event;
            StudentLiveEventBuffer::push($schoolID, $event);
            if (! empty($event['matched'])) {
                if (! empty($event['created'])) {
                    $syncedCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($events)
                ? 'Live student attendance updated.'
                : 'No new punches to save.',
            'events' => $events,
            'synced' => $syncedCount,
            'updated' => $updatedCount,
        ]);
    }

    /**
     * Local attendance-agent bulk sync: all student device logs → live DB.
     */
    public function syncStudentBulk(Request $request, StudentAttendanceSyncService $syncService)
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
            'device_label' => 'nullable|string|max:120',
            'records' => 'required|array|min:1',
            'records.*.user_id' => 'required|integer',
            'records.*.record_time' => 'required|string',
            'records.*.type' => 'nullable|string|max:80',
            'records.*.device_ip' => 'nullable|string|max:45',
        ]);

        set_time_limit(300);

        $studentsMap = $syncService->getStudentsMap($schoolID);
        if (empty($studentsMap)) {
            return response()->json([
                'success' => false,
                'message' => 'No active students found for this school.',
            ], 400);
        }

        $deviceIp = trim((string) ($validated['device_ip'] ?? ''));
        $deviceLabel = trim((string) ($validated['device_label'] ?? ''));
        $logs = [];

        foreach ($validated['records'] as $record) {
            $logs[] = [
                'user_id' => (int) ($record['user_id'] ?? 0),
                'record_time' => (string) ($record['record_time'] ?? ''),
                'type' => (string) ($record['type'] ?? 'Fingerprint'),
                'device_ip' => trim((string) ($record['device_ip'] ?? $deviceIp)),
            ];
        }

        $userDateRecords = $syncService->extractUserDateRecords($logs, $studentsMap, null, $deviceIp);
        $persistStats = $syncService->persistBulkSyncSkipExisting($schoolID, $userDateRecords);

        $label = $deviceLabel !== '' ? $deviceLabel : ($deviceIp !== '' ? $deviceIp : 'device');
        $synced = $persistStats['synced'];
        $skipped = $persistStats['skipped_existing'];
        $updatedCheckout = $persistStats['updated_checkout'];

        $message = "Synced from {$label}. {$synced} new day(s) saved to database.";
        if ($updatedCheckout > 0) {
            $message .= " {$updatedCheckout} check-out time(s) updated.";
        }
        if ($skipped > 0) {
            $message .= " {$skipped} already complete in database (skipped).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'synced' => $synced,
            'skipped_existing' => $skipped,
            'updated_checkout' => $updatedCheckout,
            'device_days_matched' => $persistStats['device_days_matched'],
            'records_received' => count($logs),
        ]);
    }

    /**
     * @param array<int, array{model: Student}> $studentsMap
     * @param array{user_id:int,record_time:Carbon,type:string,device_ip:string} $punch
     * @return array<string, mixed>|null
     */
    private function applySingleStudentLivePunch(array $studentsMap, array $punch, string $deviceIpTop): ?array
    {
        $userId = $punch['user_id'];
        $punchTime = $punch['record_time'];
        $recordDate = $punchTime->toDateString();
        $punchFormatted = $punchTime->format('Y-m-d H:i:s');

        $entry = $studentsMap[$userId] ?? null;
        if (! $entry) {
            return [
                'matched' => false,
                'user_id' => $userId,
                'record_time' => $punchFormatted,
                'action' => 'Unknown',
                'message' => 'Enroll ID not found in school students.',
            ];
        }

        /** @var Student $student */
        $student = $entry['model'];
        $name = trim(($student->first_name ?? '').' '.($student->middle_name ?? '').' '.($student->last_name ?? ''));
        $verifyMode = $punch['type'] ?: 'Fingerprint';
        $resolvedDeviceIp = $punch['device_ip'] !== '' ? $punch['device_ip'] : ($deviceIpTop !== '' ? $deviceIpTop : null);

        $classLabel = 'N/A';
        if ($student->subclass) {
            $classLabel = trim(($student->subclass->class->class_name ?? '').' '.($student->subclass->subclass_name ?? ''));
        }

        $permissionService = app(StudentPermissionAttendanceService::class);
        $permissionMap = $permissionService->loadApprovedStatusMap(
            (int) $student->schoolID,
            $recordDate,
            $recordDate,
            [(int) $student->studentID]
        );
        $permStatus = $permissionService->statusForStudentOnDate(
            $permissionMap,
            (int) $student->studentID,
            $recordDate
        );
        if ($permStatus) {
            $label = $permStatus === 'Sick' ? 'Sick' : 'Permission';

            return [
                'matched' => true,
                'created' => false,
                'skipped' => true,
                'person_type' => 'student',
                'user_id' => (int) $student->studentID,
                'user_name' => $name !== '' ? $name : 'N/A',
                'class_name' => $classLabel,
                'subclassID' => (int) ($student->subclassID ?? 0),
                'record_time' => $punchFormatted,
                'action' => $label,
                'status' => $label,
                'attendance_status' => $label,
                'message' => 'Approved ruksa — counted as '.$label.'.',
            ];
        }

        $existing = StudentFingerprintAttendance::where('studentID', $student->studentID)
            ->whereDate('attendance_date', $recordDate)
            ->first();

        $action = 'Sign In';
        $created = false;

        if (! $existing || ! $existing->check_in_time) {
            StudentFingerprintAttendance::updateOrCreate(
                ['studentID' => $student->studentID, 'attendance_date' => $recordDate],
                [
                    'enrollmentID' => app(\App\Services\StudentEnrollmentService::class)
                        ->bestEnrollmentID($student->studentID, (int) ($student->schoolID ?? 0) ?: null),
                    'user_id' => $student->studentID,
                    'user_name' => $name !== '' ? $name : 'N/A',
                    'enroll_id' => $student->fingerprint_id ?? $student->studentID,
                    'check_in_time' => $punchFormatted,
                    'check_out_time' => null,
                    'status' => 0,
                    'verify_mode' => $verifyMode,
                    'device_ip' => $resolvedDeviceIp,
                ]
            );
            $created = ! $existing;
        } elseif (! $existing->check_out_time) {
            $checkIn = Carbon::parse($existing->check_in_time);
            if ($punchTime->lte($checkIn)) {
                return $this->skipStudentEvent($student, $punchFormatted, $name, $classLabel, false);
            }

            $existing->update([
                'check_out_time' => $punchFormatted,
                'status' => 1,
                'verify_mode' => $verifyMode,
                'device_ip' => $resolvedDeviceIp ?? $existing->device_ip,
            ]);
            $action = 'Sign Out';
        } else {
            return $this->skipStudentEvent($student, $punchFormatted, $name, $classLabel, true);
        }

        return [
            'matched' => true,
            'created' => $created,
            'person_type' => 'student',
            'user_id' => (int) $student->studentID,
            'user_name' => $name !== '' ? $name : 'N/A',
            'class_name' => $classLabel,
            'subclassID' => (int) ($student->subclassID ?? 0),
            'record_time' => $punchFormatted,
            'action' => $action,
            'status' => $action === 'Sign Out' ? 'Complete' : 'Check In Only',
            'sent_to_live' => true,
            'message' => 'Sent to live system',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function skipStudentEvent(Student $student, string $punchFormatted, string $name, string $classLabel, bool $dayComplete = false): array
    {
        if ($dayComplete) {
            return [
                'matched' => true,
                'created' => false,
                'skipped' => true,
                'person_type' => 'student',
                'user_id' => (int) $student->studentID,
                'user_name' => $name !== '' ? $name : 'N/A',
                'class_name' => $classLabel,
                'record_time' => $punchFormatted,
                'action' => 'Ready',
                'status' => 'Complete',
                'message' => 'This student already signed in and signed out.',
            ];
        }

        return [
            'matched' => true,
            'created' => false,
            'skipped' => true,
            'person_type' => 'student',
            'user_id' => (int) $student->studentID,
            'user_name' => $name !== '' ? $name : 'N/A',
            'class_name' => $classLabel,
            'record_time' => $punchFormatted,
            'action' => 'Ignored',
            'message' => 'Duplicate punch ignored.',
        ];
    }

    /**
     * @return array<int, array{model: Student}>
     */
    private function getStudentsMap(int $schoolID): array
    {
        return app(StudentAttendanceSyncService::class)->getStudentsMap($schoolID);
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
