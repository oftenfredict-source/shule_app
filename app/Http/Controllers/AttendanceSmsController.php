<?php

namespace App\Http\Controllers;

use App\Models\SchoolAttendanceSmsSchedule;
use App\Models\Subclass;
use App\Models\ClassModel;
use App\Services\AttendanceSmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AttendanceSmsController extends Controller
{
    public function __construct(
        private AttendanceSmsService $attendanceSmsService
    ) {
    }

    public function scheduleInfo(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $schedule = $this->attendanceSmsService->getScheduleForSchool($schoolID);
        $config = $this->attendanceSmsService->getConfigForSchool($schoolID);
        $reference = $this->resolveReferenceDate($request);
        $range = $this->attendanceSmsService->periodRange($schedule, $reference);

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
            'schedule_label' => $this->attendanceSmsService->scheduleLabel($schedule),
            'send_method' => $config?->send_method ?? SchoolAttendanceSmsSchedule::METHOD_MANUAL,
            'send_method_label' => $this->attendanceSmsService->sendMethodLabel($config?->send_method),
            'manual_allowed' => $this->attendanceSmsService->isManualSendAllowed($schoolID),
            'auto_enabled' => $this->attendanceSmsService->isAutoSendEnabled($schoolID),
            'enabled' => $schedule !== SchoolAttendanceSmsSchedule::SCHEDULE_NONE,
            'period_key' => $this->attendanceSmsService->periodKey($schedule, $reference),
            'period_label' => $range['label'],
            'reference_date' => $reference->format('Y-m-d'),
            'auto_summary' => $this->attendanceSmsService->formatAutoScheduleSummary($config),
        ]);
    }

    public function recipients(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');

        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $schedule = $this->attendanceSmsService->getScheduleForSchool($schoolID);
        if ($schedule === SchoolAttendanceSmsSchedule::SCHEDULE_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'This feature is not allowed in your school.',
                'enabled' => false,
            ], 403);
        }

        if (!$this->attendanceSmsService->isManualSendAllowed($schoolID)) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance SMS is not enabled for your school.',
                'enabled' => false,
            ], 403);
        }

        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;

        if ($subclassID && !$this->canAccessSubclass($schoolID, $subclassID, $teacherID, $userType)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to this class.'], 403);
        }

        $reference = $this->resolveReferenceDate($request);
        $rows = $this->attendanceSmsService->buildRecipientRows(
            $schoolID,
            $schedule,
            $reference,
            $subclassID
        );

        $range = $this->attendanceSmsService->periodRange($schedule, $reference);

        return response()->json([
            'success' => true,
            'enabled' => true,
            'schedule' => $schedule,
            'schedule_label' => $this->attendanceSmsService->scheduleLabel($schedule),
            'period_key' => $this->attendanceSmsService->periodKey($schedule, $reference),
            'period_label' => $range['label'],
            'reference_date' => $reference->format('Y-m-d'),
            'recipients' => $rows->values(),
        ]);
    }

    public function send(Request $request)
    {
        $schoolID = (int) Session::get('schoolID');
        $teacherID = Session::get('teacherID');
        $userType = Session::get('user_type');
        $sentBy = Session::get('userID');

        if (!$schoolID) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        if (!$this->attendanceSmsService->isManualSendAllowed($schoolID)) {
            return response()->json([
                'success' => false,
                'error' => 'Attendance SMS is not enabled for your school.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'studentID' => 'required|integer|exists:students,studentID',
            'subclassID' => 'nullable|integer',
            'reference_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $studentID = (int) $request->input('studentID');
        $subclassID = $request->input('subclassID') ? (int) $request->input('subclassID') : null;

        if ($subclassID && !$this->canAccessSubclass($schoolID, $subclassID, $teacherID, $userType)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized access.'], 403);
        }

        $reference = $this->resolveReferenceDate($request);
        $result = $this->attendanceSmsService->sendForStudent(
            $schoolID,
            $studentID,
            $sentBy ? (int) $sentBy : null,
            $reference
        );

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
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
        if (!$subclass || !$subclass->class || (int) $subclass->class->schoolID !== $schoolID) {
            return false;
        }

        if (in_array($userType, ['Admin', 'Staff'], true)) {
            return true;
        }

        if ($userType === 'Teacher' && $teacherID) {
            if ((int) $subclass->teacherID === (int) $teacherID) {
                return true;
            }

            $mainClass = ClassModel::where('classID', $subclass->classID)
                ->where('teacherID', $teacherID)
                ->where('schoolID', $schoolID)
                ->first();

            return (bool) $mainClass;
        }

        return false;
    }
}
