<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\SchoolFingerprintAttendanceSmsSchedule;
use App\Models\Subclass;
use App\Services\StudentFingerprintAttendanceSmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class FingerprintAttendanceSmsController extends Controller
{
    public function __construct(
        private StudentFingerprintAttendanceSmsService $smsService
    ) {
    }

    public function scheduleInfo(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $schedule = $this->smsService->getScheduleForSchool($schoolID);
        $config = $this->smsService->getConfigForSchool($schoolID);
        $reference = $this->resolveReferenceDate($request);
        $filterType = $request->input('filter_type');
        $effectiveSchedule = $this->smsService->resolveEffectiveSchedule($schoolID, $filterType);
        $range = $this->smsService->periodRange($effectiveSchedule, $reference);

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
            'effective_schedule' => $effectiveSchedule,
            'schedule_label' => $this->smsService->scheduleLabel($effectiveSchedule),
            'send_method' => $config?->send_method ?? SchoolFingerprintAttendanceSmsSchedule::METHOD_MANUAL,
            'auto_enabled' => $this->smsService->isAutoSendEnabled($schoolID),
            'configured' => $schedule !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE,
            'enabled' => $this->smsService->isManualSendAllowed($schoolID),
            'manual_allowed' => $this->smsService->isManualSendAllowed($schoolID),
            'period_key' => $this->smsService->periodKey($effectiveSchedule, $reference),
            'period_label' => $range['label'],
            'reference_date' => $reference->format('Y-m-d'),
            'auto_summary' => $this->smsService->formatAutoScheduleSummary($config),
        ]);
    }

    public function recipients(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if (! $schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        if (! $this->smsService->isManualSendAllowed($schoolID)) {
            return response()->json([
                'success' => false,
                'message' => 'Fingerprint attendance SMS is not enabled.',
                'enabled' => false,
            ], 403);
        }

        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;
        $classID = $request->input('classID') ? (int) $request->input('classID') : null;
        $filterType = $request->input('filter_type');
        $studentIds = $request->input('student_ids');
        if (is_array($studentIds)) {
            $studentIds = array_map('intval', $studentIds);
        } else {
            $studentIds = null;
        }

        if ($subclassID && ! $this->canAccessSubclass($schoolID, $subclassID, $teacherID, $userType)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to this class.'], 403);
        }

        if ($classID && $userType === 'Teacher' && $teacherID) {
            if (! ClassModel::where('classID', $classID)->where('schoolID', $schoolID)->where('teacherID', $teacherID)->exists()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to this class.'], 403);
            }
        }

        $reference = $this->resolveReferenceDate($request);
        $effectiveSchedule = $this->smsService->resolveEffectiveSchedule($schoolID, $filterType);
        $range = $this->smsService->periodRange($effectiveSchedule, $reference);

        $rows = $this->smsService->buildRecipientRows(
            $schoolID,
            $reference,
            $filterType,
            $subclassID,
            $classID,
            $studentIds
        );

        $configured = $this->smsService->getScheduleForSchool($schoolID) !== SchoolFingerprintAttendanceSmsSchedule::SCHEDULE_NONE;
        $config = $this->smsService->getConfigForSchool($schoolID);

        return response()->json([
            'success' => true,
            'enabled' => true,
            'configured' => $configured,
            'schedule' => $effectiveSchedule,
            'schedule_label' => $this->smsService->scheduleLabel($effectiveSchedule),
            'period_key' => $this->smsService->periodKey($effectiveSchedule, $reference),
            'period_label' => $range['label'],
            'reference_date' => $reference->format('Y-m-d'),
            'filter_type' => $filterType,
            'auto_summary' => $configured
                ? $this->smsService->formatAutoScheduleSummary($config)
                : 'Manual send (report period)',
            'recipients' => $rows->values(),
        ]);
    }

    public function send(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');
        $sentBy = Session::get('userID');

        if (! $schoolID) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        if (! $this->smsService->isManualSendAllowed($schoolID)) {
            return response()->json([
                'success' => false,
                'error' => 'Fingerprint attendance SMS is not enabled.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'studentID' => 'required|integer|exists:students,studentID',
            'subclassID' => 'nullable|integer',
            'classID' => 'nullable|integer',
            'reference_date' => 'nullable|date',
            'filter_type' => 'nullable|in:day,date_range,month,year',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $studentID = (int) $request->input('studentID');
        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;
        $classID = $request->input('classID') ? (int) $request->input('classID') : null;

        if ($subclassID && ! $this->canAccessSubclass($schoolID, $subclassID, $teacherID, $userType)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized access.'], 403);
        }

        $reference = $this->resolveReferenceDate($request);
        $result = $this->smsService->sendForStudent(
            $schoolID,
            $studentID,
            $sentBy ? (int) $sentBy : null,
            $reference,
            $request->input('filter_type')
        );

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }

    public function sendBulk(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');
        $sentBy = Session::get('userID');

        if (! $schoolID) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer',
            'subclassID' => 'nullable|integer',
            'reference_date' => 'nullable|date',
            'filter_type' => 'nullable|in:day,date_range,month,year',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;
        if ($subclassID && ! $this->canAccessSubclass($schoolID, $subclassID, $teacherID, $userType)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized access.'], 403);
        }

        $reference = $this->resolveReferenceDate($request);
        $result = $this->smsService->sendBulkForStudents(
            $schoolID,
            array_map('intval', $request->input('student_ids', [])),
            $sentBy ? (int) $sentBy : null,
            $reference,
            $request->input('filter_type')
        );

        return response()->json([
            'success' => ($result['sent'] ?? 0) > 0 || ($result['skipped'] ?? 0) > 0,
            'sent' => $result['sent'],
            'skipped' => $result['skipped'],
            'failed' => $result['failed'],
            'message' => "Sent: {$result['sent']}, Skipped (already sent): {$result['skipped']}, Failed: {$result['failed']}",
        ]);
    }

    private function resolveReferenceDate(Request $request): Carbon
    {
        $date = $request->input('reference_date');
        if ($date) {
            return Carbon::parse($date)->startOfDay();
        }

        return Carbon::today();
    }

    private function canAccessSubclass(int $schoolID, int $subclassID, $teacherID, $userType): bool
    {
        $subclass = Subclass::with('class')->find($subclassID);
        if (! $subclass || ! $subclass->class || (int) $subclass->class->schoolID !== $schoolID) {
            return false;
        }

        if (in_array($userType, ['Admin', 'Staff'], true)) {
            return true;
        }

        if ($userType === 'Teacher' && $teacherID) {
            if ((int) $subclass->teacherID === (int) $teacherID) {
                return true;
            }

            return ClassModel::where('classID', $subclass->classID)
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->exists();
        }

        return false;
    }
}
