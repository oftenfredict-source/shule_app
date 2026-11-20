<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ManageAttendanceController;
use App\Services\SchoolWorkingDaysService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Hub for Account/Main → My Attendance (web: /teacher/my-attendance).
 * Wraps teacherMyAttendanceData with friendly defaults + action hints.
 */
class TeacherMyAttendanceMobileApiController extends Controller
{
    /** GET /attendance */
    public function show(Request $request, SchoolWorkingDaysService $workingDaysService)
    {
        if (!$request->filled('filter_type')) {
            $request->query->set('filter_type', 'day');
            $request->merge(['filter_type' => 'day']);
        }

        if ($request->input('filter_type') === 'day' && !$request->filled('date')) {
            $today = Carbon::today()->toDateString();
            $request->query->set('date', $today);
            $request->merge(['date' => $today]);
        }

        if ($request->input('filter_type') === 'month') {
            if (!$request->filled('month')) {
                $m = (int) Carbon::today()->month;
                $request->query->set('month', $m);
                $request->merge(['month' => $m]);
            }
            if (!$request->filled('year')) {
                $y = (int) Carbon::today()->year;
                $request->query->set('year', $y);
                $request->merge(['year' => $y]);
            }
        }

        $result = $this->callWeb(ManageAttendanceController::class, 'teacherMyAttendanceData', [
            'request' => $request,
            'workingDaysService' => $workingDaysService,
        ]);

        if (!$result instanceof JsonResponse) {
            return $this->forbiddenOrPassthrough($result, 'My Attendance is not available.');
        }

        $payload = $result->getData(true);
        if (!($payload['success'] ?? false)) {
            return $result;
        }

        $filterType = $payload['filter_type'] ?? $request->input('filter_type');

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'My Attendance',
                'screen_title' => $payload['title'] ?? 'My Attendance',
                'filter_type' => $filterType,
                'filters' => [
                    'date' => $filterType === 'day' ? ($request->input('date') ?: null) : null,
                    'month' => $filterType === 'month' ? (int) $request->input('month') : null,
                    'year' => $filterType === 'month' ? (int) $request->input('year') : null,
                ],
                'person' => $payload['person'] ?? null,
                'statistics' => $payload['statistics'] ?? null,
                'work_start_time' => $payload['work_start_time'] ?? null,
                'work_end_time' => $payload['work_end_time'] ?? null,
                'period_incomplete' => (bool) ($payload['period_incomplete'] ?? false),
                'period_label' => $payload['period_label'] ?? null,
                'daily_records' => $payload['daily_records'] ?? [],
                'day_summary' => $payload['day_summary'] ?? null,
                'actions' => [
                    'day' => 'GET /attendance?filter_type=day&date=YYYY-MM-DD',
                    'month' => 'GET /attendance?filter_type=month&month=1-12&year=YYYY',
                    'pdf' => 'GET /attendance/pdf?filter_type=… (same query as report)',
                ],
            ],
        ]);
    }

    private function callWeb(string $class, string $method, array $params = [])
    {
        try {
            return app()->call([app($class), $method], $params);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    private function forbiddenOrPassthrough($result, string $fallbackMessage)
    {
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            $error = Session::get('error') ?: $fallbackMessage;
            $path = (string) parse_url($result->getTargetUrl(), PHP_URL_PATH);
            $status = str_ends_with($path, '/login') ? 403 : 422;

            return response()->json(['success' => false, 'message' => $error], $status);
        }

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json(['success' => false, 'message' => $fallbackMessage], 403);
    }
}
