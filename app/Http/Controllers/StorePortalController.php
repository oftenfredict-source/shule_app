<?php

namespace App\Http\Controllers;

use App\Models\OtherStaff;
use App\Models\StoreItem;
use App\Models\StoreItemCategory;
use App\Models\StoreItemIncident;
use App\Models\StoreItemRequest;
use App\Models\StoreRequisition;
use App\Models\StoreStockUnit;
use App\Models\Teacher;
use App\Services\StoreNotificationService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class StorePortalController extends StoreController
{
    protected function authorizePortalUser(string $role): ?array
    {
        $userType = Session::get('user_type');
        $schoolID = (int) Session::get('schoolID');
        if (!$schoolID) {
            return null;
        }

        if ($role === 'teacher' && $userType === 'Teacher') {
            $id = (int) Session::get('teacherID');
            if (!$id) {
                return null;
            }
            $teacher = Teacher::where('id', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
            if (!$teacher) {
                return null;
            }

            return [
                'schoolID' => $schoolID,
                'type' => 'teacher',
                'id' => $id,
                'name' => $this->formatPersonName($teacher->first_name, $teacher->middle_name, $teacher->last_name),
                'employee_number' => $teacher->employee_number,
            ];
        }

        if ($role === 'staff' && $userType === 'Staff') {
            $id = (int) Session::get('staffID');
            if (!$id) {
                return null;
            }
            $staff = OtherStaff::where('id', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
            if (!$staff) {
                return null;
            }

            return [
                'schoolID' => $schoolID,
                'type' => 'staff',
                'id' => $id,
                'name' => $this->formatPersonName($staff->first_name, $staff->middle_name, $staff->last_name),
                'employee_number' => $staff->employee_number,
            ];
        }

        return null;
    }

    public function teacherPortal()
    {
        return $this->renderPortal('teacher');
    }

    public function staffPortal()
    {
        return $this->renderPortal('staff');
    }

    public function teacherStoreRequest(Request $request)
    {
        return $this->storePortalRequest($request, 'teacher');
    }

    public function staffStoreRequest(Request $request)
    {
        return $this->storePortalRequest($request, 'staff');
    }

    public function teacherExportPdf(Request $request)
    {
        return $this->exportPortalPdf($request, 'teacher');
    }

    public function staffExportPdf(Request $request)
    {
        return $this->exportPortalPdf($request, 'staff');
    }

    protected function renderPortal(string $role)
    {
        $ctx = $this->authorizePortalUser($role);
        if (!$ctx) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = $ctx['schoolID'];
        $data = $this->buildPortalViewData($ctx);

        $view = $role === 'teacher'
            ? 'Teacher.store_requisitions'   // GXON teacher_corp layout
            : 'portal.store_requisitions';  // legacy staff_nav layout

        return view($view, array_merge($data, [
            'portalRole' => $role,
            'requester' => $ctx,
        ]));
    }

    protected function buildPortalViewData(array $ctx): array
    {
        $schoolID = $ctx['schoolID'];

        $categories = StoreItemCategory::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('category_name')
            ->get();

        $availableStockUnits = StoreStockUnit::where('schoolID', $schoolID)
            ->where('remaining_items', '>', 0)
            ->with(['item.category', 'item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
            ->orderBy('itemID')
            ->get();

        $availableStockList = $availableStockUnits
            ->map(fn ($u) => $this->formatAvailableStockUnitUi($u))
            ->values();

        $reportItemsList = StoreItem::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('item_name')
            ->get(['itemID', 'item_name', 'categoryID'])
            ->map(fn ($item) => [
                'itemID' => (int) $item->itemID,
                'item_name' => $item->item_name,
                'categoryID' => $item->categoryID ? (int) $item->categoryID : null,
            ])->values();

        $requisitions = StoreRequisition::where('schoolID', $schoolID)
            ->where('recipient_type', $ctx['type'])
            ->where('recipient_id', $ctx['id'])
            ->with(['item', 'package', 'unit'])
            ->orderByDesc('issued_at')
            ->get()
            ->map(fn ($r) => $this->formatRequisitionUi($r))
            ->values();

        $pendingRequests = StoreItemRequest::where('schoolID', $schoolID)
            ->where('requester_type', $ctx['type'])
            ->where('requester_id', $ctx['id'])
            ->where('status', 'requested')
            ->orderByDesc('requested_at')
            ->get()
            ->map(fn ($r) => $this->formatPortalRequestUi($r))
            ->values();

        $requestHistory = StoreItemRequest::where('schoolID', $schoolID)
            ->where('requester_type', $ctx['type'])
            ->where('requester_id', $ctx['id'])
            ->whereNotIn('status', ['requested'])
            ->orderByDesc('requested_at')
            ->limit(50)
            ->get()
            ->map(fn ($r) => $this->formatPortalRequestUi($r))
            ->values();

        $incidents = StoreItemIncident::where('schoolID', $schoolID)
            ->where('responsible_type', $ctx['type'])
            ->where('responsible_id', $ctx['id'])
            ->with(['item'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => $this->formatIncidentUi($i))
            ->values();

        return [
            'schoolID' => $schoolID,
            'categories' => $categories,
            'availableStockList' => $availableStockList,
            'reportItemsList' => $reportItemsList,
            'portalRequisitions' => $requisitions,
            'portalPendingRequests' => $pendingRequests,
            'portalRequestHistory' => $requestHistory,
            'portalIncidents' => $incidents,
            'portalDashboard' => $this->buildPortalDashboardStats($ctx, $requisitions, $pendingRequests, $incidents),
        ];
    }

    protected function formatPortalRequestUi(StoreItemRequest $req): array
    {
        return [
            'requestID' => (int) $req->requestID,
            'status' => $req->status,
            'status_label' => $req->status === 'requested' ? 'Requested' : ucfirst($req->status),
            'items_summary' => $req->items_summary,
            'notes' => $req->notes,
            'expected_return_date' => $req->expected_return_date
                ? $req->expected_return_date->format('d M Y')
                : null,
            'requested_at' => $req->requested_at ? $req->requested_at->format('d M Y, H:i') : '-',
            'reviewed_at' => $req->reviewed_at ? $req->reviewed_at->format('d M Y, H:i') : null,
            'review_notes' => $req->review_notes,
        ];
    }

    protected function buildPortalDashboardStats($ctx, $requisitions, $pendingRequests, $incidents): array
    {
        $reqs = collect($requisitions);
        $active = $reqs->filter(fn ($r) => ($r['status'] ?? '') === 'issued');
        $overdue = $reqs->filter(fn ($r) => !empty($r['is_overdue']));
        $returned = $reqs->filter(fn ($r) => ($r['status'] ?? '') === 'returned');

        $openIncidents = collect($incidents)->filter(fn ($i) => ($i['resolve_status'] ?? '') === 'open');

        return [
            'total_requests' => StoreItemRequest::where('schoolID', $ctx['schoolID'])
                ->where('requester_type', $ctx['type'])
                ->where('requester_id', $ctx['id'])
                ->count(),
            'pending_requests' => $pendingRequests->count(),
            'total_requisitions' => $reqs->count(),
            'active_items' => $active->count(),
            'returned_items' => $returned->count(),
            'overdue_items' => $overdue->count(),
            'open_incidents' => $openIncidents->count(),
            'generated_at' => now()->format('d M Y, H:i'),
        ];
    }

    public function portalDashboard(Request $request, string $role)
    {
        $ctx = $this->authorizePortalUser($role);
        if (!$ctx) {
            return $this->jsonError('Unauthorized');
        }

        $data = $this->buildPortalViewData($ctx);

        return response()->json([
            'success' => true,
            'dashboard' => $data['portalDashboard'],
        ]);
    }

    public function storePortalRequest(Request $request, string $role)
    {
        $ctx = $this->authorizePortalUser($role);
        if (!$ctx) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'issues' => 'required|array|min:1',
            'issues.*.stock_unitID' => 'required|integer',
            'issues.*.take_key' => 'required|string',
            'issues.*.quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string|max:1000',
            'expected_return_date' => 'nullable|date|after_or_equal:today',
        ]);

        $schoolID = $ctx['schoolID'];
        $issues = collect($request->issues);
        $stockUnitIDs = $issues->pluck('stock_unitID')->map(fn ($id) => (int) $id)->unique()->values()->all();

        $units = StoreStockUnit::where('schoolID', $schoolID)
            ->whereIn('stock_unitID', $stockUnitIDs)
            ->where('remaining_items', '>', 0)
            ->with(['item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
            ->get()
            ->keyBy('stock_unitID');

        if ($units->count() !== count($stockUnitIDs)) {
            return $this->jsonError('One or more selected units are no longer available.', 422);
        }

        $hasCanReturn = $units->contains(fn ($u) => $u->requisition_status === 'can_return');
        if ($hasCanReturn && !$request->filled('expected_return_date')) {
            return $this->jsonError('Expected return date is required for items that can be returned.', 422);
        }

        $planResult = $this->planValidatedTakeIssues($issues, $units);
        if (!$planResult['ok']) {
            return $this->jsonError($planResult['error'], 422);
        }

        $itemsSummary = collect($planResult['planned'])->map(function ($p) {
            $unit = $p['unit'];
            $opt = $p['option'];
            $itemName = $unit->item->item_name ?? 'Item';
            $take = $opt['level'] === 'item'
                ? $this->formatQtyPortal($p['items_taken']) . ' ' . ($unit->unit->unit_name ?? 'item')
                : $this->formatQtyPortal($p['qty_of_level']) . ' × ' . $opt['label']
                    . ' (' . $this->formatQtyPortal($p['items_taken']) . ' ' . ($unit->unit->unit_name ?? 'item') . ')';

            return "{$itemName} [{$unit->label}] - {$take}";
        })->implode('; ');

        $requestedBy = Session::get('name') ?: $ctx['name'];

        DB::beginTransaction();
        try {
            $itemRequest = StoreItemRequest::create([
                'schoolID' => $schoolID,
                'requester_type' => $ctx['type'],
                'requester_id' => $ctx['id'],
                'requester_name' => $ctx['name'],
                'status' => 'requested',
                'is_read_by_operator' => false,
                'issues' => $issues->values()->all(),
                'items_summary' => $itemsSummary,
                'notes' => $request->notes,
                'expected_return_date' => $request->expected_return_date,
                'requested_by' => $requestedBy,
                'requested_at' => now(),
            ]);

            DB::commit();

            try {
                $categoryIds = collect($stockUnitIDs)->isEmpty() ? [] : StoreStockUnit::where('schoolID', $schoolID)
                    ->whereIn('stock_unitID', $stockUnitIDs)
                    ->with('item')
                    ->get()
                    ->map(fn ($u) => $u->item->categoryID ?? null)
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                app(StoreNotificationService::class)->notifyItemRequested(
                    $schoolID,
                    $ctx['type'],
                    $ctx['id'],
                    $ctx['name'],
                    $itemsSummary,
                    $categoryIds
                );
            } catch (\Throwable $e) {
                \Log::error('Store request SMS failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Ombi lako limewasilishwa. Utataarifiwa baada ya idhini kutoka kwa msimamizi wa store.',
                'request' => $this->formatPortalRequestUi($itemRequest),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to submit request: ' . $e->getMessage(), 500);
        }
    }

    public function exportPortalPdf(Request $request, string $role)
    {
        $ctx = $this->authorizePortalUser($role);
        if (!$ctx) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $section = $request->input('section', 'dashboard');
        $data = $this->buildPortalViewData($ctx);
        $data['portalRole'] = $role;
        $data['requester'] = $ctx;
        $data['section'] = $section;
        $data['generated_at'] = now()->format('d M Y, H:i');
        $data['school'] = \App\Models\School::find($ctx['schoolID']);

        $pdf = PDF::loadView('portal.pdf.store_portal_pdf', $data);
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'My_Store_' . ucfirst($section) . '_' . date('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    protected function formatQtyPortal($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 4, '.', ''), '0'), '.');
    }
}
