<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StorePortalController;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Reshaped hub for Main → Requisitions (web: /teacher/store-requisitions).
 * Submit + PDF stay as plain bridges in routes/mobile_teacher.php.
 */
class TeacherStoreRequisitionsMobileApiController extends Controller
{
    /** GET /store-requisitions */
    public function index()
    {
        $result = $this->callWeb(StorePortalController::class, 'teacherPortal');
        if (!$result instanceof View) {
            return $this->forbiddenOrPassthrough($result, 'Store requisitions are not available.');
        }

        $d = $result->getData();
        $requester = $d['requester'] ?? [];
        $stock = collect($d['availableStockList'] ?? []);
        $pending = collect($d['portalPendingRequests'] ?? []);
        $history = collect($d['portalRequestHistory'] ?? []);
        $issued = collect($d['portalRequisitions'] ?? [])->map(fn ($r) => $this->cleanRequisition($r))->values();
        $incidents = collect($d['portalIncidents'] ?? [])->map(fn ($i) => $this->cleanIncident($i))->values();
        $dashboard = $d['portalDashboard'] ?? [];

        $itemsGrouped = $stock->groupBy('itemID')->map(function ($units, $itemID) {
            $first = $units->first();

            return [
                'itemID' => (int) $itemID,
                'item_name' => $first['item_name'] ?? null,
                'categoryID' => $first['categoryID'] ?? null,
                'category_name' => $first['category_name'] ?? null,
                'available_labels' => $units->count(),
                'labels' => $units->map(fn ($u) => [
                    'stock_unitID' => (int) ($u['stock_unitID'] ?? 0),
                    'label' => $u['label'] ?? null,
                    'package_unit_display' => $u['package_unit_display'] ?? null,
                    'store_method' => $u['store_method'] ?? null,
                    'requisition_status' => $u['requisition_status'] ?? null,
                    'remaining_items' => (float) ($u['remaining_items'] ?? 0),
                    'leaf_unit_name' => $u['leaf_unit_name'] ?? 'item',
                    'requires_return_date' => ($u['requisition_status'] ?? '') === 'can_return',
                    'take_structure' => $u['take_structure'] ?? null,
                    'take_options' => $u['take_options'] ?? [],
                ])->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Requisitions',
                'requester' => [
                    'type' => $requester['type'] ?? 'teacher',
                    'id' => (int) ($requester['id'] ?? 0),
                    'name' => $requester['name'] ?? null,
                    'employee_number' => $requester['employee_number'] ?? null,
                ],
                'dashboard' => [
                    'total_requests' => (int) ($dashboard['total_requests'] ?? 0),
                    'pending_requests' => (int) ($dashboard['pending_requests'] ?? 0),
                    'total_requisitions' => (int) ($dashboard['total_requisitions'] ?? 0),
                    'active_items' => (int) ($dashboard['active_items'] ?? 0),
                    'returned_items' => (int) ($dashboard['returned_items'] ?? 0),
                    'overdue_items' => (int) ($dashboard['overdue_items'] ?? 0),
                    'open_incidents' => (int) ($dashboard['open_incidents'] ?? 0),
                    'generated_at' => $dashboard['generated_at'] ?? null,
                ],
                'sections' => [
                    ['key' => 'dashboard', 'title' => 'Dashboard', 'pdf_section' => 'dashboard'],
                    ['key' => 'my_requests', 'title' => 'My Requests', 'pdf_section' => 'my_requests'],
                    ['key' => 'my_items', 'title' => 'My Items', 'pdf_section' => 'my_items'],
                    ['key' => 'incidents', 'title' => 'Incidents', 'pdf_section' => 'incidents'],
                ],
                'categories' => collect($d['categories'] ?? [])->map(fn ($c) => [
                    'categoryID' => (int) $c->categoryID,
                    'category_name' => $c->category_name ?? null,
                ])->values(),
                'available_items' => $itemsGrouped,
                'pending_requests' => $pending->values(),
                'request_history' => $history->values(),
                'issued_items' => $issued,
                'incidents' => $incidents,
                'form_rules' => [
                    'issues_min' => 1,
                    'notes_max' => 1000,
                    'expected_return_date_required_if_can_return' => true,
                    'expected_return_date_min' => 'today',
                ],
                'actions' => [
                    'submit_request' => 'POST /store-requisitions',
                    'download_pdf' => 'GET /store-requisitions/pdf?section=dashboard|my_requests|my_items|incidents',
                ],
            ],
        ]);
    }

    private function cleanRequisition(array $r): array
    {
        unset($r['return_status_html'], $r['expected_return_html']);

        return $r;
    }

    private function cleanIncident(array $i): array
    {
        unset($i['payment_status_html'], $i['resolve_status_html'], $i['status_html']);

        return $i;
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
