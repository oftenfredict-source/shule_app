<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TeacherDutyController;
use App\Models\DailyDutyReport;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * GET /api/v1/teacher/duty-book
 *
 * Reshapes the data of the website "Duty Book" page (TeacherDutyController::teacherIndex)
 * into a structure the mobile app can render directly: weeks -> days -> report state.
 * All other duty-book endpoints are plain bridges (see routes/mobile_teacher.php).
 */
class TeacherDutyBookMobileApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $result = app()->call([app(TeacherDutyController::class), 'teacherIndex']);
        } catch (HttpResponseException $e) {
            $result = $e->getResponse();
        }

        if (!$result instanceof View) {
            return response()->json(['success' => false, 'message' => 'Duty book is not available.'], 403);
        }

        $d = $result->getData();
        $teacherID = (int) Session::get('teacherID');
        $today = Carbon::today();

        $duties = collect($d['duties'] ?? []);
        $school = $d['school_details'] ?? null;
        $isSecondary = $school && ($school->school_type ?? null) === 'Secondary';

        // Reports for every day of the listed weeks (a week can run past the filter dates).
        $weeksRaw = $duties->groupBy(fn ($duty) => Carbon::parse($duty->start_date)->format('Y-m-d') . '_' . Carbon::parse($duty->end_date)->format('Y-m-d'));
        $reports = collect();
        if ($weeksRaw->isNotEmpty()) {
            $min = $duties->min(fn ($duty) => Carbon::parse($duty->start_date)->format('Y-m-d'));
            $max = $duties->max(fn ($duty) => Carbon::parse($duty->end_date)->format('Y-m-d'));
            $reports = DailyDutyReport::where('schoolID', Session::get('schoolID'))
                ->where('teacherID', $teacherID)
                ->whereBetween('report_date', [$min, $max])
                ->get()
                ->keyBy(fn ($r) => $r->report_date->format('Y-m-d'));
        }

        $weekNo = 1;
        $weeks = $weeksRaw->map(function ($group) use (&$weekNo, $today, $reports, $teacherID) {
            $start = Carbon::parse($group[0]->start_date)->startOfDay();
            $end = Carbon::parse($group[0]->end_date)->endOfDay();

            $days = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $start->copy()->addDays($i);
                $key = $day->format('Y-m-d');
                $report = $reports->get($key);
                $isFuture = $day->isAfter($today);
                $status = $report?->status;

                if ($report) {
                    $state = strtolower($status);               // draft | sent | approved
                } else {
                    $state = $isFuture ? 'upcoming' : 'waiting';
                }

                $editable = !$isFuture && (!$report || $status === 'Draft');

                $days[] = [
                    'date' => $key,
                    'day' => $day->format('l'),
                    'is_today' => $day->isSameDay($today),
                    'is_future' => $isFuture,
                    'state' => $state,
                    'report' => $report ? [
                        'reportID' => (int) $report->reportID,
                        'status' => $status,
                        'attendance_percentage' => $report->attendance_percentage,
                        'signed_by' => $report->signed_by,
                        'signed_at' => $report->signed_at?->toDateTimeString(),
                    ] : null,
                    'can_view' => (bool) $report,
                    'can_fill' => $editable,
                    'can_send' => $editable,
                    'can_download_pdf' => (bool) $report,
                ];
            }

            return [
                'week_number' => $weekNo++,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'is_active_week' => $today->between($start, $end),
                'teachers' => $group->map(fn ($duty) => [
                    'teacher_id' => (int) $duty->teacherID,
                    'name' => $duty->teacher ? trim($duty->teacher->first_name . ' ' . $duty->teacher->last_name) : null,
                    'is_me' => (int) $duty->teacherID === $teacherID,
                ])->values(),
                'days' => $days,
            ];
        })->values();

        $currentDuty = $d['currentDuty'] ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from_date' => $d['fromDate'] ?? null,
                    'to_date' => $d['toDate'] ?? null,
                    'title' => $d['monthTitle'] ?? null,
                ],
                'school_type' => $school->school_type ?? null,
                'student_label' => $isSecondary ? 'student' : 'pupil',
                'is_class_teacher' => (bool) ($d['isClassTeacher'] ?? false),
                'is_on_duty_today' => (bool) $currentDuty,
                'current_duty' => $currentDuty ? [
                    'start_date' => Carbon::parse($currentDuty->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($currentDuty->end_date)->format('Y-m-d'),
                ] : null,
                'weeks' => $weeks,
                'classes' => collect($d['classes'] ?? [])->map(fn ($c) => [
                    'classID' => (int) $c->classID,
                    'class_name' => $c->class_name,
                ])->values(),
                // Approved parent permissions for the teacher's own class (only when is_class_teacher)
                'class_teacher_permissions' => collect($d['classTeacherPermissions'] ?? [])->map(fn ($p) => [
                    'id' => (int) $p->id,
                    'student_name' => trim($p->first_name . ' ' . ($p->last_name ?? '')),
                    'parent_name' => trim(($p->parent_first_name ?? '') . ' ' . ($p->parent_last_name ?? '')) ?: 'Parent',
                    'reason_type' => $p->reason_type,
                    'start_date' => $p->start_date,
                    'end_date' => $p->end_date,
                    'days_count' => $p->days_count,
                    'status' => $p->status,
                ])->values(),
            ],
        ]);
    }
}
