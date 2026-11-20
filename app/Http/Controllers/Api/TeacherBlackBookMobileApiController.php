<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BlackBookController;
use App\Http\Controllers\Controller;
use App\Services\BlackBookAccessService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hub for Report Student (web: /black_book/report-student).
 * Search + submit stay as plain bridges in routes/mobile_teacher.php.
 */
class TeacherBlackBookMobileApiController extends Controller
{
    /**
     * GET /black-book/report-form
     * Alias: GET /black-book/report-student
     */
    public function reportForm(Request $request)
    {
        $result = $this->callWeb(BlackBookController::class, 'reportStudentPage');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Report Student is not available.');
        }

        $d = $result->getData();
        $categories = collect($d['categories'] ?? []);
        $myReports = collect($d['myReports'] ?? []);
        $schoolID = (int) (Session::get('schoolID') ?? 0);
        $canManage = $schoolID > 0
            && app(BlackBookAccessService::class)->currentUserHasManageAccess($schoolID);

        $named = $categories
            ->filter(fn ($c) => strcasecmp((string) ($c->name ?? ''), 'Other') !== 0)
            ->map(fn ($c) => [
                'categoryID' => (int) $c->categoryID,
                'name' => $c->name,
                'weight' => (int) ($c->weight ?? 0),
                'status' => $c->status ?? 'Active',
            ])
            ->values();

        $tabs = [
            ['key' => 'all', 'status' => null, 'label' => 'All', 'count' => $myReports->count()],
            ['key' => 'pending', 'status' => 'pending', 'label' => 'Pending', 'count' => $myReports->where('status', 'pending')->count()],
            ['key' => 'viewed', 'status' => 'viewed', 'label' => 'Viewed', 'count' => $myReports->where('status', 'viewed')->count()],
            ['key' => 'monitored', 'status' => 'monitored', 'label' => 'Monitored', 'count' => $myReports->where('status', 'monitored')->count()],
            ['key' => 'resolved', 'status' => 'resolved', 'label' => 'Resolved', 'count' => $myReports->where('status', 'resolved')->count()],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Report Student',
                'can_report' => true,
                'can_manage' => $canManage,
                'manage_path' => $canManage ? '/black-book/manage' : null,
                'parent_sms_default' => false,
                'problem_categories' => $named,
                'other_category' => [
                    'value' => 'other',
                    'label' => 'Other',
                    'requires_custom_name' => true,
                    'custom_name_min' => 2,
                    'custom_name_max' => 150,
                ],
                'form_rules' => [
                    'student_search_min' => 2,
                    'description_min' => 5,
                    'description_max' => 2000,
                    'active_enrollments_only' => true,
                ],
                'tabs' => $tabs,
                'my_reports' => $myReports->values(),
                'actions' => [
                    'search_students' => 'GET /black-book/students/search?search=&for_report=1',
                    'submit_report' => 'POST /black-book/reports',
                    'refresh_my_reports' => 'GET /black-book/reports?mine=1',
                    'refresh_my_reports_filtered' => 'GET /black-book/reports?mine=1&status=pending',
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
