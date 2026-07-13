<?php

namespace App\Http\Controllers;

use App\Models\StoreInventoryActor;
use App\Models\StoreItem;
use App\Models\StoreItemCategory;
use App\Models\StoreItemIncident;
use App\Models\StoreItemPackageSpec;
use App\Models\StoreItemStockEntry;
use App\Models\StoreMeasurementType;
use App\Models\StorePackage;
use App\Models\StoreItemRequest;
use App\Models\StoreRequisition;
use App\Models\StoreStockUnit;
use App\Models\StoreUnitOfMeasure;
use App\Models\Teacher;
use App\Models\OtherStaff;
use App\Models\School;
use App\Models\Student;
use App\Services\StoreAccessService;
use App\Services\StoreDefaultsService;
use App\Services\StoreNotificationService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Session;

class StoreController extends Controller
{
    private function authorizeSchool(?string $requiredPrivilege = null): ?int
    {
        $access = $this->getStoreAccess($requiredPrivilege);

        return $access ? (int) $access['schoolID'] : null;
    }

    protected function getStoreAccess(?string $requiredPrivilege = null): ?array
    {
        return app(StoreAccessService::class)->resolve($requiredPrivilege);
    }

    protected function jsonError(string $message, int $code = 403)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    protected function formatPersonName(?string $first, ?string $middle, ?string $last): string
    {
        return trim(implode(' ', array_filter([$first, $middle, $last])));
    }

    private function formatItemUi(StoreItem $item): array
    {
        $item->loadMissing(['category', 'measurementType', 'units', 'packageSpecs.package', 'packageSpecs.unit']);
        $allSpecs = $item->packageSpecs;

        $buildPayload = null;
        $buildPayload = function ($spec) use (&$buildPayload, $allSpecs) {
            $hasSub = (bool) $spec->has_sub_package;
            $node = [
                'packageID' => (int) $spec->packageID,
                'quantity' => (float) $spec->quantity,
                'content_quantity' => $spec->content_quantity !== null ? (float) $spec->content_quantity : null,
                'has_sub_package' => $hasSub,
                'unitID' => $spec->unitID ? (int) $spec->unitID : null,
            ];
            if ($hasSub) {
                $child = $allSpecs->first(fn ($c) => (int) $c->parent_specID === (int) $spec->specID);
                if ($child) {
                    $node['child'] = $buildPayload($child);
                }
            }
            return $node;
        };

        $buildLines = null;
        $buildLines = function ($spec, $depth = 0) use (&$buildLines, $allSpecs) {
            $lines = [];
            $pad = str_repeat('— ', $depth);
            if ($spec->has_sub_package) {
                $child = $allSpecs->first(fn ($c) => (int) $c->parent_specID === (int) $spec->specID);
                $childName = $child?->package?->package_name ?? 'sub-package';
                $childQty = $child ? $this->formatQty($child->quantity) : '0';
                $lines[] = $pad . ($spec->package->package_name ?? 'Package') . ' has ' . $childQty . ' ' . $childName;
                if ($child) {
                    $lines = array_merge($lines, $buildLines($child, $depth + 1));
                }
            } else {
                $items = $spec->content_quantity !== null ? $spec->content_quantity : $spec->quantity;
                $lines[] = $pad . ($spec->package->package_name ?? 'Package') . ' has ' . $this->formatQty($items) . ' ' . ($spec->unit->unit_name ?? 'item');
            }
            return $lines;
        };

        $packageSpecsPayload = [];
        $packageLines = [];
        if ($item->has_packages && $allSpecs->count()) {
            foreach ($allSpecs->filter(fn ($s) => $s->parent_specID === null) as $root) {
                $packageSpecsPayload[] = $buildPayload($root);
                $packageLines = array_merge($packageLines, $buildLines($root));
            }
        }

        $packageTrees = $this->buildPackageSpecTrees($allSpecs);

        return [
            'itemID' => (int) $item->itemID,
            'item_name' => $item->item_name,
            'categoryID' => (int) $item->categoryID,
            'category_name' => $item->category->category_name ?? '',
            'measurement_typeID' => (int) $item->measurement_typeID,
            'measurement_type' => $item->measurementType->type_name ?? '',
            'has_packages' => (bool) $item->has_packages,
            'unitIDs' => $item->units->pluck('unitID')->map(fn ($id) => (int) $id)->values()->all(),
            'unit_names' => $item->units->pluck('unit_name')->values()->all(),
            'package_specs_payload' => $packageSpecsPayload,
            'package_lines' => $packageLines,
            'list_item' => [
                'itemID' => (int) $item->itemID,
                'item_name' => $item->item_name,
                'measurement_type' => $item->measurementType->type_name ?? '',
                'has_packages' => (bool) $item->has_packages,
                'units' => $item->units->map(fn ($u) => [
                    'unitID' => (int) $u->unitID,
                    'unit_name' => $u->unit_name,
                ])->values()->all(),
                'package_specs' => $packageTrees,
                'stockable_packages' => $this->flattenStockablePackages($packageTrees),
            ],
        ];
    }

    protected function formatAvailableStockUnitUi(StoreStockUnit $u, ?string $itemName = null): array
    {
        $u->loadMissing([
            'item.category',
            'item.packageSpecs.package',
            'item.packageSpecs.unit',
            'package',
            'unit',
            'stockEntry',
        ]);

        return [
            'stock_unitID' => (int) $u->stock_unitID,
            'itemID' => (int) $u->itemID,
            'item_name' => $itemName ?? ($u->item->item_name ?? ''),
            'categoryID' => $u->item->categoryID ? (int) $u->item->categoryID : null,
            'category_name' => $u->item->category->category_name ?? '',
            'label' => $u->label,
            'store_method' => $u->store_method,
            'packageID' => $u->packageID,
            'package_specID' => $u->package_specID,
            'package_unit_display' => $this->buildPackageUnitDisplay($u),
            'requisition_status' => $u->requisition_status,
            'initial_items' => (float) $u->initial_items,
            'remaining_items' => (float) $u->remaining_items,
            'leaf_unit_name' => $u->unit->unit_name ?? 'item',
            'take_structure' => $this->buildTakeStructureForStockUnit($u),
            'take_options' => $this->buildTakeOptionsForStockUnit($u),
        ];
    }

    /**
     * Validate take keys, per-level quantities, and aggregate deductions per label.
     *
     * @return array{ok: bool, error?: string, planned?: array<int, array>}
     */
    protected function planValidatedTakeIssues(iterable $issues, $units): array
    {
        $planned = [];
        foreach ($issues as $issue) {
            $unit = $units->get((int) $issue['stock_unitID']);
            if (!$unit) {
                return ['ok' => false, 'error' => 'Stock unit not found.'];
            }

            $options = collect($this->buildTakeOptionsForStockUnit($unit));
            $option = $options->firstWhere('key', $issue['take_key']);
            if (!$option) {
                return ['ok' => false, 'error' => 'Invalid take option for label ' . $unit->label . '.'];
            }

            $qtyOfLevel = (float) $issue['quantity'];
            if ($qtyOfLevel <= 0 || $qtyOfLevel > (float) $option['max_qty'] + 0.00001) {
                return [
                    'ok' => false,
                    'error' => 'Quantity for ' . $unit->label . ' must be between 1 and '
                        . $this->formatQty($option['max_qty']) . '.',
                ];
            }

            $itemsTaken = $qtyOfLevel * (float) $option['items_each'];
            $planned[] = [
                'issue' => $issue,
                'unit' => $unit,
                'option' => $option,
                'qty_of_level' => $qtyOfLevel,
                'items_taken' => $itemsTaken,
            ];
        }

        $deductionsByUnit = [];
        foreach ($planned as $plannedIssue) {
            $uid = (int) $plannedIssue['unit']->stock_unitID;
            $deductionsByUnit[$uid] = ($deductionsByUnit[$uid] ?? 0) + $plannedIssue['items_taken'];
        }
        foreach ($deductionsByUnit as $uid => $totalItems) {
            $unit = $units->get($uid);
            if ($totalItems > (float) $unit->remaining_items + 0.00001) {
                return [
                    'ok' => false,
                    'error' => 'Total quantity exceeds remaining content on label ' . $unit->label
                        . ' (need ' . $this->formatQty($totalItems) . ', have '
                        . $this->formatQty($unit->remaining_items) . ').',
                ];
            }
        }

        return ['ok' => true, 'planned' => $planned];
    }

    private function formatStockEntryUi(StoreItemStockEntry $entry): array
    {
        $entry->loadMissing(['item', 'package', 'unit', 'stockUnits']);
        $availableUnits = $entry->stockUnits->filter(fn ($u) => (float) $u->remaining_items > 0);
        $inStockLabels = $availableUnits->map(function ($u) use ($entry) {
            $label = $u->label;
            if ($entry->store_method === 'package') {
                $label .= ' (' . $this->formatQty($u->remaining_items) . '/' . $this->formatQty($u->initial_items)
                    . ' ' . ($entry->unit->unit_name ?? 'item') . ')';
            }
            return $label;
        })->values()->all();
        $issuedLabels = $entry->stockUnits
            ->filter(fn ($u) => (float) $u->remaining_items < (float) $u->initial_items)
            ->map(fn ($u) => $u->label . ' (used ' . $this->formatQty((float) $u->initial_items - (float) $u->remaining_items) . ')')
            ->values()->all();
        $availableQty = $availableUnits->count();
        $remainingItems = (float) $availableUnits->sum('remaining_items');
        $issuedQty = $entry->stockUnits->count() - $availableQty;

        $pkgUnitLabel = '-';
        if ($entry->store_method === 'package' && $entry->package) {
            $pkgUnitLabel = $entry->package->package_name;
            if ($entry->content_per_unit) {
                $pkgUnitLabel .= ' (' . $this->formatQty($entry->content_per_unit) . ' ' . ($entry->unit->unit_name ?? '') . ')';
            }
        } else {
            $pkgUnitLabel = $entry->unit->unit_name ?? '-';
        }

        return [
            'stock_entryID' => (int) $entry->stock_entryID,
            'item_name' => $entry->item->item_name ?? '-',
            'pkg_unit_label' => $pkgUnitLabel,
            'available_qty' => $availableQty,
            'remaining_items' => $remainingItems,
            'issued_qty' => $issuedQty,
            'quantity' => (int) $entry->quantity,
            'requisition_status' => $entry->requisition_status,
            'store_method' => $entry->store_method,
            'created_at' => $entry->created_at ? $entry->created_at->format('d M Y, H:i') : '—',
            'in_stock_labels' => $inStockLabels,
            'issued_labels' => $issuedLabels,
            'stock_units' => $entry->stockUnits->map(function ($u) use ($entry) {
                return array_merge(
                    $this->formatAvailableStockUnitUi($u, $entry->item->item_name ?? ''),
                    ['stock_status' => $u->stock_status]
                );
            })->values()->all(),
        ];
    }

    protected function formatRequisitionUi(StoreRequisition $req, ?string $className = null): array
    {
        $req->loadMissing(['item', 'package', 'unit']);
        $recipientTypeLabel = match ($req->recipient_type) {
            'teacher' => 'Teacher',
            'staff' => 'Staff',
            'student' => 'Student',
            default => ucfirst((string) $req->recipient_type),
        };

        $isOverdue = false;
        $returnStatusText = "Can't Return";
        $returnStatusHtml = '<span class="store-badge-pill store-badge-return-no">Can\'t Return</span>';
        $expectedReturnHtml = '<span class="text-muted">—</span>';
        $expectedReturnText = '—';

        if ($req->requisition_status === 'can_return') {
            if ($req->status === 'returned') {
                $condition = $req->return_condition === 'bad' ? 'damaged' : $req->return_condition;
                if ($condition === 'damaged') {
                    $returnStatusText = 'Returned — Damaged';
                    $returnStatusHtml = '<span class="store-badge-pill store-badge-defective">Returned — Damaged</span>';
                } elseif ($condition === 'lost') {
                    $returnStatusText = 'Returned — Lost';
                    $returnStatusHtml = '<span class="store-badge-pill store-badge-overdue">Returned — Lost</span>';
                } else {
                    $returnStatusText = 'Returned — Good';
                    $returnStatusHtml = '<span class="store-badge-pill store-badge-return-yes">Returned — Good</span>';
                }
            } else {
                $isOverdue = $req->expected_return_date && $req->expected_return_date->isPast();
                $returnStatusText = $isOverdue ? 'Not Returned — Overdue' : 'Not Returned — Pending';
                $returnStatusHtml = $isOverdue
                    ? '<span class="store-badge-pill store-badge-overdue">Not Returned — Overdue</span>'
                    : '<span class="store-badge-pill store-badge-pending">Not Returned — Pending</span>';
            }
            if ($req->expected_return_date) {
                $expectedReturnText = $req->expected_return_date->format('d M Y');
                $expectedReturnHtml = '<span class="' . ($isOverdue && $req->status === 'issued' ? 'req-return-overdue' : '') . '">'
                    . e($expectedReturnText) . '</span>';
            }
        }

        return [
            'requisitionID' => (int) $req->requisitionID,
            'recipient_name' => $req->recipient_name,
            'recipient_type' => $req->recipient_type,
            'recipient_type_label' => $recipientTypeLabel,
            'recipient_id' => (int) $req->recipient_id,
            'class_name' => $className,
            'item_name' => $req->item->item_name ?? '-',
            'label' => $req->label,
            'package_unit_display' => $req->package_unit_display ?? '-',
            'take_display' => $req->take_display ?: ($req->package_unit_display ?? '—'),
            'quantity_taken' => (float) ($req->quantity_taken ?: 1),
            'quantity_returned' => $req->quantity_returned !== null ? (float) $req->quantity_returned : null,
            'requisition_status' => $req->requisition_status,
            'status' => $req->status,
            'return_condition' => $req->return_condition,
            'return_description' => $req->return_description,
            'bad_description' => $req->bad_description,
            'issued_by' => $req->issued_by,
            'notes' => $req->notes,
            'issued_at' => $req->issued_at ? $req->issued_at->format('d M Y, H:i') : '—',
            'returned_at' => $req->returned_at ? $req->returned_at->format('d M Y') : '—',
            'expected_return_date' => $expectedReturnText,
            'return_status_text' => $returnStatusText,
            'return_status_html' => $returnStatusHtml,
            'expected_return_html' => $expectedReturnHtml,
            'is_overdue' => $isOverdue && $req->status === 'issued',
            'can_return_action' => $req->status === 'issued' && $req->requisition_status === 'can_return',
        ];
    }

    protected function formatIncidentUi(StoreItemIncident $inc, ?string $className = null): array
    {
        $inc->loadMissing(['item', 'package', 'unit']);

        $responsibleTypeLabel = match ($inc->responsible_type) {
            'teacher' => 'Teacher',
            'staff' => 'Staff',
            'student' => 'Student',
            'other' => 'Other',
            default => ucfirst((string) $inc->responsible_type),
        };

        $paymentHtml = $inc->payment_status === 'paid'
            ? '<span class="store-badge-pill store-badge-return-yes">Paid</span>'
            : '<span class="store-badge-pill store-badge-pending">Pending</span>';

        $resolveHtml = $inc->resolve_status === 'resolved'
            ? '<span class="store-badge-pill store-badge-return-yes">Resolved</span>'
            : '<span class="store-badge-pill store-badge-pending">Open</span>';

        $resolveMethodLabel = match ($inc->resolve_method) {
            'cash' => 'Cash' . ($inc->payment_amount !== null ? ' (' . number_format((float) $inc->payment_amount, 2) . ')' : ''),
            'replace_new' => 'Replace New',
            default => '—',
        };

        return [
            'incidentID' => (int) $inc->incidentID,
            'incident_type' => $inc->incident_type,
            'stock_unitID' => (int) $inc->stock_unitID,
            'item_name' => $inc->item->item_name ?? '—',
            'label' => $inc->label,
            'package_unit_display' => $inc->package_unit_display ?? '—',
            'take_display' => $inc->take_display ?: ($inc->package_unit_display ?? '—'),
            'quantity_affected' => (float) ($inc->quantity_affected ?: 1),
            'responsible_type' => $inc->responsible_type,
            'responsible_type_label' => $responsibleTypeLabel,
            'responsible_id' => $inc->responsible_id ? (int) $inc->responsible_id : null,
            'responsible_name' => $inc->responsible_name,
            'class_name' => $className,
            'reason' => $inc->reason,
            'payment_status' => $inc->payment_status,
            'payment_amount' => $inc->payment_amount !== null ? (float) $inc->payment_amount : null,
            'resolve_status' => $inc->resolve_status,
            'resolve_method' => $inc->resolve_method,
            'resolve_method_label' => $resolveMethodLabel,
            'resolved_at' => $inc->resolved_at ? $inc->resolved_at->format('d M Y, H:i') : '—',
            'resolved_by' => $inc->resolved_by,
            'recorded_by' => $inc->recorded_by,
            'notes' => $inc->notes,
            'recorded_at' => $inc->created_at ? $inc->created_at->format('d M Y, H:i') : '—',
            'payment_status_html' => $paymentHtml,
            'resolve_status_html' => $resolveHtml,
            'status_html' => $paymentHtml . ' ' . $resolveHtml,
            'can_edit' => $inc->resolve_status === 'open',
            'can_delete' => $inc->resolve_status === 'open',
            'can_resolve' => $inc->resolve_status === 'open',
        ];
    }

    private function resolveIncidentResponsible(Request $request, int $schoolID): ?array
    {
        $type = $request->responsible_type;
        $name = null;
        $id = $request->filled('responsible_id') ? (int) $request->responsible_id : null;

        if ($type === 'other') {
            $name = trim((string) $request->responsible_name);
            if ($name === '') {
                return null;
            }

            return ['type' => 'other', 'id' => null, 'name' => $name];
        }

        if (!$id) {
            return null;
        }

        if ($type === 'teacher') {
            $person = Teacher::where('id', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
            if ($person) {
                $name = $this->formatPersonName($person->first_name, $person->middle_name, $person->last_name);
            }
        } elseif ($type === 'staff') {
            $person = OtherStaff::where('id', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
            if ($person) {
                $name = $this->formatPersonName($person->first_name, $person->middle_name, $person->last_name);
            }
        } elseif ($type === 'student') {
            $person = Student::where('studentID', $id)->where('schoolID', $schoolID)->where('status', 'Active')->first();
            if ($person) {
                $name = $this->formatPersonName($person->first_name, $person->middle_name, $person->last_name);
            }
        }

        if (!$name) {
            return null;
        }

        return ['type' => $type, 'id' => $id, 'name' => $name];
    }

    private function restoreStockFromIncident(StoreItemIncident $incident, StoreStockUnit $unit): void
    {
        $qty = (float) $incident->quantity_affected;
        $unit->remaining_items = (float) $unit->remaining_items + $qty;
        if ($unit->remaining_items > 0) {
            $unit->stock_status = 'in_stock';
        }
        $unit->save();
    }

    private function deductStockForIncident(StoreStockUnit $unit, float $itemsTaken, string $incidentType): void
    {
        $unit->remaining_items = max(0, (float) $unit->remaining_items - $itemsTaken);
        if ($unit->remaining_items <= 0) {
            $unit->stock_status = $incidentType === 'damaged' ? 'defective' : 'requisitioned';
        } else {
            $unit->stock_status = 'in_stock';
        }
        $unit->save();
    }

    private function createIncidentFromRequisitionReturn(
        StoreRequisition $requisition,
        string $incidentType,
        string $reason,
        ?string $notes,
        int $schoolID,
        string $recordedBy
    ): StoreItemIncident {
        return StoreItemIncident::create([
            'schoolID' => $schoolID,
            'incident_type' => $incidentType,
            'stock_unitID' => $requisition->stock_unitID,
            'requisitionID' => $requisition->requisitionID,
            'itemID' => $requisition->itemID,
            'label' => $requisition->label,
            'store_method' => $requisition->store_method,
            'packageID' => $requisition->packageID,
            'unitID' => $requisition->unitID,
            'package_unit_display' => $requisition->package_unit_display,
            'take_level' => $requisition->take_level,
            'take_packageID' => $requisition->take_packageID,
            'take_display' => $requisition->take_display,
            'quantity_affected' => (float) ($requisition->quantity_taken ?: 1),
            'responsible_type' => $requisition->recipient_type,
            'responsible_id' => $requisition->recipient_id,
            'responsible_name' => $requisition->recipient_name,
            'reason' => $reason,
            'payment_status' => 'pending',
            'resolve_status' => 'open',
            'recorded_by' => $recordedBy,
            'notes' => $notes,
        ]);
    }

    private function formatQty($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 4, '.', ''), '0'), '.');
    }

    private function buildPackageUnitDisplay(StoreStockUnit $unit): string
    {
        $unit->loadMissing(['package', 'unit', 'stockEntry', 'item.packageSpecs.package', 'item.packageSpecs.unit']);

        if ($unit->store_method === 'package' && $unit->package) {
            $trees = $this->buildPackageSpecTrees($unit->item->packageSpecs ?? collect());
            $root = collect($trees)->firstWhere('packageID', (int) $unit->packageID);
            if ($root) {
                return $root['chain_summary'] ?? $unit->package->package_name;
            }

            $label = $unit->package->package_name;
            $content = $unit->stockEntry->content_per_unit ?? null;
            if ($content) {
                $label .= ' (' . $this->formatQty($content) . ' ' . ($unit->unit->unit_name ?? '') . ')';
            }
            return $label;
        }

        return $unit->unit->unit_name ?? '-';
    }

    /**
     * Build nested package chains from flat specs (roots only in returned list).
     */
    private function buildPackageSpecTrees($specs): array
    {
        $specs = collect($specs);
        if ($specs->isEmpty()) {
            return [];
        }

        $byParent = $specs->groupBy(fn ($s) => $s->parent_specID === null ? 0 : (int) $s->parent_specID);

        $buildNode = function ($spec) use (&$buildNode, $byParent) {
            $hasSub = (bool) $spec->has_sub_package;
            $children = $hasSub
                ? ($byParent[(int) $spec->specID] ?? collect())->map(fn ($child) => $buildNode($child))->values()->all()
                : [];

            $node = [
                'specID' => (int) $spec->specID,
                'packageID' => (int) $spec->packageID,
                'package_name' => $spec->package->package_name ?? '',
                'quantity' => (float) $spec->quantity,
                'content_quantity' => $spec->content_quantity !== null ? (float) $spec->content_quantity : null,
                'has_sub_package' => $hasSub,
                'unitID' => $spec->unitID ? (int) $spec->unitID : null,
                'unit_name' => $spec->unit->unit_name ?? null,
                'children' => $children,
            ];

            $node['levels'] = $this->flattenPackageLevels($node);
            $node['leaf_unitID'] = $this->resolveLeafUnitId($node);
            $node['leaf_unit_name'] = $this->resolveLeafUnitName($node);
            $node['items_per_package'] = $this->resolveItemsPerPackage($node);
            $node['chain_summary'] = $this->formatPackageChainSummary($node);
            $node['contains_line'] = $this->formatPackageContainsLine($node);

            return $node;
        };

        return ($byParent[0] ?? collect())->map(fn ($spec) => $buildNode($spec))->values()->all();
    }

    /**
     * Every package level in every chain can be stocked separately
     * (e.g. Set, Carton, Box — or Roboter and its own sub-levels).
     */
    private function flattenStockablePackages(array $trees): array
    {
        $result = [];
        $walk = function (array $node) use (&$walk, &$result) {
            $result[] = [
                'specID' => $node['specID'],
                'packageID' => $node['packageID'],
                'package_name' => $node['package_name'],
                'quantity' => $node['quantity'],
                'content_quantity' => $node['content_quantity'] ?? null,
                'has_sub_package' => $node['has_sub_package'],
                'unitID' => $node['unitID'],
                'unit_name' => $node['unit_name'],
                'levels' => $node['levels'],
                'leaf_unitID' => $node['leaf_unitID'],
                'leaf_unit_name' => $node['leaf_unit_name'],
                'items_per_package' => $node['items_per_package'],
                'chain_summary' => $node['chain_summary'],
                'contains_line' => $node['contains_line'],
            ];
            if (!empty($node['children'][0])) {
                $walk($node['children'][0]);
            }
        };
        foreach ($trees as $tree) {
            $walk($tree);
        }
        return $result;
    }

    private function findStockablePackage(array $trees, ?int $specID, ?int $packageID): ?array
    {
        if (!$specID && !$packageID) {
            return null;
        }

        $search = function (array $node) use (&$search, $specID, $packageID): ?array {
            if ($specID && (int) $node['specID'] === $specID) {
                return $node;
            }
            if (!$specID && $packageID && (int) $node['packageID'] === $packageID) {
                return $node;
            }
            foreach ($node['children'] ?? [] as $child) {
                $found = $search($child);
                if ($found) {
                    return $found;
                }
            }

            return null;
        };

        foreach ($trees as $tree) {
            $found = $search($tree);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    private function resolveLeafPackageContentQuantity(array $node): float
    {
        if ($node['has_sub_package'] && !empty($node['children'][0])) {
            return $this->resolveLeafPackageContentQuantity($node['children'][0]);
        }

        $content = $node['content_quantity'] ?? null;
        if ($content !== null && (float) $content > 0) {
            return (float) $content;
        }

        return (float) ($node['quantity'] ?? 0);
    }

    private function resolveDeepestPackageNode(array $node): array
    {
        if ($node['has_sub_package'] && !empty($node['children'][0])) {
            return $this->resolveDeepestPackageNode($node['children'][0]);
        }

        return $node;
    }

    /**
     * Request structure for issuing from a labelled stock unit.
     * Groups options by request method: main package, sub-package, or item inside.
     */
    private function formatSubPackageTakeDetail(array $levelNode, int $maxQty, string $leafName): string
    {
        $parts = ['Max ' . $this->formatQty($maxQty) . ' available'];
        if (!empty($levelNode['contains_line'])) {
            $parts[] = $levelNode['contains_line'];
        }
        $itemsEach = (float) ($levelNode['items_per_package'] ?? 0);
        if ($itemsEach > 0) {
            $parts[] = 'each = ' . $this->formatQty($itemsEach) . ' ' . ($levelNode['leaf_unit_name'] ?: $leafName);
        }

        return implode(' · ', $parts);
    }

    private function buildTakeStructureForStockUnit(StoreStockUnit $unit): array
    {
        $remaining = (float) $unit->remaining_items;
        $leafName = $unit->unit->unit_name ?? 'item';

        if ($unit->store_method !== 'package' || $remaining <= 0) {
            $option = [
                'key' => 'full',
                'level' => 'full',
                'packageID' => null,
                'specID' => null,
                'label' => 'Whole unit',
                'detail' => '1 ' . $leafName,
                'items_each' => 1,
                'max_qty' => $remaining >= 1 ? 1 : 0,
            ];

            return [
                'chain_summary' => null,
                'contains_line' => null,
                'show_method_select' => false,
                'methods' => [],
                'take_options' => [$option],
            ];
        }

        $unit->loadMissing(['item.packageSpecs.package', 'item.packageSpecs.unit']);
        $trees = $this->buildPackageSpecTrees($unit->item->packageSpecs ?? collect());
        $node = $this->findStockablePackage(
            $trees,
            $unit->package_specID ? (int) $unit->package_specID : null,
            $unit->packageID ? (int) $unit->packageID : null
        );

        if (!$node) {
            $option = [
                'key' => 'item',
                'level' => 'item',
                'packageID' => null,
                'specID' => null,
                'label' => $leafName,
                'detail' => 'Request by unit measure',
                'items_each' => 1,
                'max_qty' => $remaining,
            ];

            return [
                'chain_summary' => null,
                'contains_line' => null,
                'show_method_select' => false,
                'methods' => [],
                'take_options' => [$option],
            ];
        }

        $mainOptions = [];
        $itemOptions = [];
        $leafContentQty = $this->resolveLeafPackageContentQuantity($node);
        $deepestNode = $this->resolveDeepestPackageNode($node);
        $deepestName = $deepestNode['package_name'] ?: 'package';
        $stockable = $this->flattenStockablePackages([$node]);

        foreach ($stockable as $levelNode) {
            $itemsEach = (float) $levelNode['items_per_package'];
            if ($itemsEach <= 0) {
                continue;
            }

            $isRootLevel = (int) $levelNode['specID'] === (int) $node['specID'];

            if ($isRootLevel && $remaining >= $itemsEach) {
                $mainOptions[] = [
                    'key' => 'full_' . $levelNode['specID'],
                    'level' => 'package',
                    'packageID' => $levelNode['packageID'],
                    'specID' => $levelNode['specID'],
                    'label' => $levelNode['package_name'],
                    'detail' => $levelNode['contains_line'] ?: $levelNode['chain_summary'],
                    'items_each' => $itemsEach,
                    'max_qty' => (int) floor($remaining / $itemsEach),
                ];
            }
        }

        $subHierarchy = $this->buildSubPackageHierarchy($node, $remaining, $leafName);
        if (!$subHierarchy || empty($subHierarchy['units'])) {
            $subHierarchy = null;
            $subOptions = $this->buildSubPackageTakeOptions($node, $remaining, $leafName);
        } else {
            $subOptions = $subHierarchy['take_options'];
        }

        $itemMaxQty = $leafContentQty > 0
            ? min($remaining, $leafContentQty)
            : $remaining;

        if ($itemMaxQty > 0) {
            $itemOptions[] = [
                'key' => 'item',
                'level' => 'item',
                'packageID' => null,
                'specID' => null,
                'label' => $node['leaf_unit_name'] ?: $leafName,
                'detail' => 'Request by unit measure'
                    . ($leafContentQty > 0
                        ? ' (max ' . $this->formatQty($leafContentQty) . ' per ' . $deepestName . ')'
                        : ''),
                'items_each' => 1,
                'max_qty' => $itemMaxQty,
            ];
        }

        $methods = [];
        if (!empty($mainOptions)) {
            $methods[] = [
                'key' => 'main',
                'label' => 'By Main Package',
                'options' => $mainOptions,
            ];
        }
        if (!empty($subOptions)) {
            $methods[] = [
                'key' => 'sub',
                'label' => 'By Sub-Package',
                'allow_multiple' => true,
                'hierarchy' => $subHierarchy,
                'options' => $subOptions,
            ];
        }
        if (!empty($itemOptions)) {
            $methods[] = [
                'key' => 'item',
                'label' => 'By Item Inside',
                'options' => $itemOptions,
            ];
        }

        $takeOptions = array_merge($mainOptions, $subOptions, $itemOptions);
        $uniqueOptions = collect($takeOptions)->unique('key')->values()->all();

        return [
            'chain_summary' => $node['chain_summary'] ?? null,
            'contains_line' => $node['contains_line'] ?? null,
            'show_method_select' => count($methods) > 0,
            'methods' => $methods,
            'take_options' => $uniqueOptions,
        ];
    }

    /**
     * Flat take options for validation (derived from buildTakeStructureForStockUnit).
     */
    private function buildTakeOptionsForStockUnit(StoreStockUnit $unit): array
    {
        return $this->buildTakeStructureForStockUnit($unit)['take_options'];
    }

    private function flattenPackageLevels(array $node): array
    {
        $levels = [[
            'packageID' => $node['packageID'],
            'package_name' => $node['package_name'],
            'quantity' => $node['quantity'],
            'content_quantity' => $node['content_quantity'] ?? null,
            'has_sub_package' => $node['has_sub_package'],
            'unitID' => $node['unitID'],
            'unit_name' => $node['unit_name'],
        ]];

        if ($node['has_sub_package'] && !empty($node['children'][0])) {
            $levels = array_merge($levels, $this->flattenPackageLevels($node['children'][0]));
        }

        return $levels;
    }

    private function resolveLeafUnitId(array $node): ?int
    {
        if (!$node['has_sub_package']) {
            return $node['unitID'];
        }
        if (!empty($node['children'][0])) {
            return $this->resolveLeafUnitId($node['children'][0]);
        }
        return null;
    }

    private function resolveLeafUnitName(array $node): ?string
    {
        if (!$node['has_sub_package']) {
            return $node['unit_name'];
        }
        if (!empty($node['children'][0])) {
            return $this->resolveLeafUnitName($node['children'][0]);
        }
        return null;
    }

    private function resolveItemsPerPackageFromLevelIndex(array $rootNode, int $levelIndex): float
    {
        $levels = $rootNode['levels'] ?? [];
        if (!isset($levels[$levelIndex])) {
            return 0;
        }

        $total = 1.0;
        for ($i = $levelIndex + 1; $i < count($levels); $i++) {
            $total *= (float) ($levels[$i]['quantity'] ?? 0);
        }

        $last = $levels[count($levels) - 1];
        if (empty($last['has_sub_package'])) {
            $content = $last['content_quantity'] ?? $last['quantity'] ?? 0;
            $total *= (float) $content;
        }

        return $total;
    }

    private function formatLevelContainsFrom(array $levels, int $startIndex): string
    {
        $bits = [];
        for ($i = $startIndex; $i < count($levels); $i++) {
            $level = $levels[$i];
            if (!empty($level['has_sub_package']) && isset($levels[$i + 1])) {
                $next = $levels[$i + 1];
                $bits[] = $level['package_name'] . ' has ' . $this->formatQty($next['quantity']) . ' ' . $next['package_name'];
            } elseif (empty($level['has_sub_package'])) {
                $items = !empty($level['content_quantity'])
                    ? (float) $level['content_quantity']
                    : (float) ($level['quantity'] ?? 0);
                $bits[] = $level['package_name'] . ' has ' . $this->formatQty($items) . ' ' . ($level['unit_name'] ?: 'item');
            }
        }

        return implode(' · ', $bits);
    }

    private function formatSubPackageOptionDetail(
        array $rootNode,
        int $levelIndex,
        float $maxQty,
        float $itemsEach,
        string $leafName
    ): string {
        $levels = $rootNode['levels'] ?? [];
        $level = $levels[$levelIndex] ?? [];
        $packageName = $level['package_name'] ?? 'Package';
        $mainName = $levels[0]['package_name'] ?? 'Set';
        $capacity = $this->resolveCapacityPerMainPackage($rootNode, $levelIndex);

        $parts = [];
        if ($capacity > 0) {
            $parts[] = '1 ' . $mainName . ' contains ' . $this->formatQty($capacity) . ' ' . $packageName . '(s)';
        }
        $parts[] = $this->formatQty($maxQty) . ' ' . $packageName . '(s) available in this label';
        $parts[] = 'each ' . $packageName . ' = ' . $this->formatQty($itemsEach) . ' ' . ($rootNode['leaf_unit_name'] ?: $leafName);

        return implode(' · ', $parts);
    }

    private function resolveCapacityPerMainPackage(array $node, int $levelIndex): float
    {
        $rootItemsEach = (float) $this->resolveItemsPerPackageFromLevelIndex($node, 0);
        $levelItemsEach = (float) $this->resolveItemsPerPackageFromLevelIndex($node, $levelIndex);
        if ($rootItemsEach <= 0 || $levelItemsEach <= 0) {
            return 0;
        }

        return $rootItemsEach / $levelItemsEach;
    }

    private function buildSubPackageTakeOptions(array $node, float $remaining, string $leafName): array
    {
        $options = [];
        $levels = $node['levels'] ?? [];
        $stockable = collect($this->flattenStockablePackages([$node]));
        $mainName = $levels[0]['package_name'] ?? 'Main package';

        foreach ($levels as $index => $level) {
            if ($index === 0) {
                continue;
            }

            $levelNode = $stockable->first(function ($stockLevel) use ($level) {
                return (int) $stockLevel['packageID'] === (int) $level['packageID'];
            });

            if (!$levelNode) {
                continue;
            }

            $itemsEach = (float) $this->resolveItemsPerPackageFromLevelIndex($node, $index);
            if ($itemsEach <= 0) {
                continue;
            }

            $maxQty = (int) floor($remaining / $itemsEach);
            if ($maxQty < 1) {
                continue;
            }

            $capacityPerMain = $this->resolveCapacityPerMainPackage($node, $index);
            $packageName = $levelNode['package_name'];

            $options[] = [
                'key' => 'pkg_' . $levelNode['specID'],
                'level' => 'sub_package',
                'packageID' => $levelNode['packageID'],
                'specID' => $levelNode['specID'],
                'label' => $packageName,
                'detail' => $this->formatSubPackageOptionDetail($node, $index, $maxQty, $itemsEach, $leafName),
                'items_each' => $itemsEach,
                'max_qty' => $maxQty,
                'available_qty' => $maxQty,
                'capacity_per_main' => (int) floor($capacityPerMain),
                'main_package_name' => $mainName,
                'chain_position' => $index,
                'is_root' => false,
            ];
        }

        $leafContentQty = $this->resolveLeafPackageContentQuantity($node);
        $deepestNode = $this->resolveDeepestPackageNode($node);
        $deepestName = $deepestNode['package_name'] ?: 'package';
        $itemMaxQty = $leafContentQty > 0
            ? min($remaining, $leafContentQty)
            : $remaining;

        if ($itemMaxQty > 0 && $remaining < (float) $this->resolveItemsPerPackageFromLevelIndex($node, 0)) {
            $options[] = [
                'key' => 'remainder_' . ($node['specID'] ?? 0),
                'level' => 'item',
                'packageID' => null,
                'specID' => null,
                'label' => $node['leaf_unit_name'] ?: $leafName,
                'detail' => 'Loose ' . ($node['leaf_unit_name'] ?: $leafName)
                    . ' remaining in this ' . $mainName
                    . ' (' . $this->formatQty($remaining) . ' available'
                    . ($leafContentQty > 0 ? ', max ' . $this->formatQty($leafContentQty) . ' per ' . $deepestName : '')
                    . ')',
                'items_each' => 1,
                'max_qty' => $itemMaxQty,
                'available_qty' => $itemMaxQty,
                'capacity_per_main' => 0,
                'main_package_name' => $mainName,
                'chain_position' => count($levels),
                'is_root' => false,
            ];
        }

        return $options;
    }

    /**
     * Hierarchical sub-package picker: individual Carton 1..N checkboxes,
     * each expandable to full unit or partial child level (Box, etc.).
     */
    private function buildSubPackageHierarchy(array $node, float $remaining, string $leafName): ?array
    {
        $levels = $node['levels'] ?? [];
        if (count($levels) < 2) {
            return null;
        }

        $stockable = $this->flattenStockablePackages([$node]);
        if (count($stockable) < 2) {
            return null;
        }

        $mainName = $levels[0]['package_name'] ?? 'Main package';
        $firstSubIndex = 1;
        $firstSubStockable = $stockable[1];
        $firstSubName = $firstSubStockable['package_name'] ?: 'Sub-package';
        $itemsPerFirstSub = (float) $this->resolveItemsPerPackageFromLevelIndex($node, $firstSubIndex);

        if ($itemsPerFirstSub <= 0) {
            return null;
        }

        $capacityPerMain = (int) floor($this->resolveCapacityPerMainPackage($node, $firstSubIndex));
        $childStockable = $stockable[2] ?? null;
        $childLevelIndex = 2;
        $hasChildSub = $childStockable !== null && isset($levels[$childLevelIndex]);
        $itemsPerChild = $hasChildSub
            ? (float) $this->resolveItemsPerPackageFromLevelIndex($node, $childLevelIndex)
            : 0;
        $childCapacityInParent = $hasChildSub
            ? (int) floor((float) ($levels[$childLevelIndex]['quantity'] ?? 0))
            : 0;
        $childName = $hasChildSub ? ($childStockable['package_name'] ?: 'Sub-package') : null;
        $leafUnitName = $node['leaf_unit_name'] ?: $leafName;

        $availableFull = (int) floor($remaining / $itemsPerFirstSub);
        $remainder = $remaining - ($availableFull * $itemsPerFirstSub);

        $units = [];
        $takeOptions = [];

        for ($i = 1; $i <= $availableFull; $i++) {
            $fullKey = 'hier_' . $firstSubStockable['specID'] . '_' . $i . '_full';
            $unit = [
                'index' => $i,
                'key' => $fullKey,
                'label' => $firstSubName . ' ' . $i,
                'is_partial' => false,
                'can_take_full' => true,
                'full_take' => [
                    'key' => $fullKey,
                    'specID' => $firstSubStockable['specID'],
                    'packageID' => $firstSubStockable['packageID'],
                    'label' => 'Full ' . $firstSubName,
                    'items_each' => $itemsPerFirstSub,
                    'level' => 'sub_package',
                ],
                'child' => null,
                'loose_take' => null,
            ];

            $takeOptions[] = [
                'key' => $fullKey,
                'level' => 'sub_package',
                'packageID' => $firstSubStockable['packageID'],
                'specID' => $firstSubStockable['specID'],
                'label' => $firstSubName . ' ' . $i . ' (full)',
                'detail' => 'Take complete ' . $firstSubName,
                'items_each' => $itemsPerFirstSub,
                'max_qty' => 1,
            ];

            if ($hasChildSub && $itemsPerChild > 0 && $childCapacityInParent > 0) {
                $childPartialKey = 'hier_' . $childStockable['specID'] . '_' . $i . '_child';
                $unit['child'] = [
                    'specID' => $childStockable['specID'],
                    'packageID' => $childStockable['packageID'],
                    'package_name' => $childName,
                    'capacity_in_parent' => $childCapacityInParent,
                    'items_each' => $itemsPerChild,
                    'partial_take' => [
                        'key' => $childPartialKey,
                        'label' => $childName,
                        'max_qty' => $childCapacityInParent,
                        'items_each' => $itemsPerChild,
                        'level' => 'sub_package',
                    ],
                ];
                $takeOptions[] = [
                    'key' => $childPartialKey,
                    'level' => 'sub_package',
                    'packageID' => $childStockable['packageID'],
                    'specID' => $childStockable['specID'],
                    'label' => $firstSubName . ' ' . $i . ' — ' . $childName,
                    'detail' => 'Max ' . $childCapacityInParent . ' ' . $childName . '(s) in this ' . $firstSubName
                        . ' · each = ' . $this->formatQty($itemsPerChild) . ' ' . $leafUnitName,
                    'items_each' => $itemsPerChild,
                    'max_qty' => $childCapacityInParent,
                ];
            }

            $units[] = $unit;
        }

        if ($remainder > 0.00001) {
            $partialIndex = $availableFull + 1;
            $unit = [
                'index' => $partialIndex,
                'key' => 'hier_partial_' . $firstSubStockable['specID'] . '_' . $partialIndex,
                'label' => $firstSubName . ' ' . $partialIndex . ' (partial — ' . $this->formatQty($remainder) . ' ' . $leafUnitName . ' left)',
                'is_partial' => true,
                'can_take_full' => false,
                'full_take' => null,
                'remaining_items' => $remainder,
                'child' => null,
                'loose_take' => null,
            ];

            if ($hasChildSub && $itemsPerChild > 0) {
                $maxChildQty = (int) floor($remainder / $itemsPerChild);
                if ($childCapacityInParent > 0) {
                    $maxChildQty = min($maxChildQty, $childCapacityInParent);
                }
                if ($maxChildQty > 0) {
                    $childPartialKey = 'hier_' . $childStockable['specID'] . '_' . $partialIndex . '_child';
                    $unit['child'] = [
                        'specID' => $childStockable['specID'],
                        'packageID' => $childStockable['packageID'],
                        'package_name' => $childName,
                        'capacity_in_parent' => $maxChildQty,
                        'items_each' => $itemsPerChild,
                        'partial_take' => [
                            'key' => $childPartialKey,
                            'label' => $childName,
                            'max_qty' => $maxChildQty,
                            'items_each' => $itemsPerChild,
                            'level' => 'sub_package',
                        ],
                    ];
                    $takeOptions[] = [
                        'key' => $childPartialKey,
                        'level' => 'sub_package',
                        'packageID' => $childStockable['packageID'],
                        'specID' => $childStockable['specID'],
                        'label' => $firstSubName . ' ' . $partialIndex . ' (partial) — ' . $childName,
                        'detail' => 'Max ' . $maxChildQty . ' ' . $childName . '(s) available in partial ' . $firstSubName
                            . ' · each = ' . $this->formatQty($itemsPerChild) . ' ' . $leafUnitName,
                        'items_each' => $itemsPerChild,
                        'max_qty' => $maxChildQty,
                    ];
                }
            } else {
                $looseKey = 'hier_loose_' . $firstSubStockable['specID'] . '_' . $partialIndex;
                $maxLoose = (int) floor($remainder);
                if ($maxLoose > 0) {
                    $unit['loose_take'] = [
                        'key' => $looseKey,
                        'label' => $leafUnitName,
                        'max_qty' => $maxLoose,
                        'items_each' => 1,
                        'level' => 'item',
                    ];
                    $takeOptions[] = [
                        'key' => $looseKey,
                        'level' => 'item',
                        'packageID' => null,
                        'specID' => null,
                        'label' => $firstSubName . ' ' . $partialIndex . ' — loose ' . $leafUnitName,
                        'detail' => 'Max ' . $maxLoose . ' ' . $leafUnitName . '(s) in partial ' . $firstSubName,
                        'items_each' => 1,
                        'max_qty' => $maxLoose,
                    ];
                }
            }

            $units[] = $unit;
        }

        if (empty($units)) {
            return null;
        }

        $summary = $availableFull . ' full ' . $firstSubName . '(s) available';
        if ($remainder > 0.00001) {
            $summary .= ' + 1 partial';
        }

        return [
            'main_package_name' => $mainName,
            'first_sub_name' => $firstSubName,
            'child_sub_name' => $childName,
            'capacity_per_main' => $capacityPerMain,
            'contains_line' => $node['contains_line'] ?? null,
            'available_summary' => $summary,
            'units' => $units,
            'take_options' => $takeOptions,
        ];
    }

    private function resolveItemsPerPackage(array $node): float
    {
        $qty = (float) $node['quantity'];
        if ($node['has_sub_package'] && !empty($node['children'][0])) {
            return $qty * $this->resolveItemsPerPackage($node['children'][0]);
        }
        $content = $node['content_quantity'] ?? null;
        if ($content !== null && (float) $content > 0) {
            return $qty * (float) $content;
        }
        return $qty;
    }

    private function formatPackageChainSummary(array $node): string
    {
        $parts = [$node['package_name'] ?: 'Package'];
        $levels = $node['levels'] ?? [];
        foreach ($levels as $i => $level) {
            if ($i === 0) {
                continue;
            }
            $parts[] = $this->formatQty($level['quantity']) . ' ' . ($level['package_name'] ?: 'Package');
        }
        $leaf = end($levels) ?: $node;
        $leafItems = !empty($leaf['content_quantity'])
            ? (float) $leaf['content_quantity']
            : (float) ($leaf['quantity'] ?? 0);
        $parts[] = $this->formatQty($leafItems) . ' ' . ($node['leaf_unit_name'] ?: 'item');

        return implode(' → ', $parts);
    }

    private function formatPackageContainsLine(array $node): string
    {
        $bits = [];
        $levels = $node['levels'] ?? [];
        foreach ($levels as $i => $level) {
            if (!empty($level['has_sub_package']) && isset($levels[$i + 1])) {
                $next = $levels[$i + 1];
                $bits[] = $level['package_name'] . ' has ' . $this->formatQty($next['quantity']) . ' ' . $next['package_name'];
            } elseif (empty($level['has_sub_package'])) {
                $items = !empty($level['content_quantity'])
                    ? (float) $level['content_quantity']
                    : (float) $level['quantity'];
                $bits[] = $level['package_name'] . ' has ' . $this->formatQty($items) . ' ' . ($level['unit_name'] ?: 'item');
            }
        }
        return implode(' · ', $bits);
    }

    private function formatPackageTreeLines(array $node, int $depth = 0): array
    {
        $lines = [];
        $indent = str_repeat('— ', $depth);
        if ($node['has_sub_package'] && !empty($node['children'][0])) {
            $child = $node['children'][0];
            $lines[] = $indent . $node['package_name'] . ' has ' . $this->formatQty($child['quantity']) . ' ' . $child['package_name'];
            $lines = array_merge($lines, $this->formatPackageTreeLines($child, $depth + 1));
        } else {
            $items = !empty($node['content_quantity'])
                ? (float) $node['content_quantity']
                : (float) $node['quantity'];
            $lines[] = $indent . $node['package_name'] . ' has ' . $this->formatQty($items) . ' ' . ($node['unit_name'] ?: 'item');
        }
        return $lines;
    }

    public function manageStore()
    {
        $access = $this->getStoreAccess();
        if (!$access) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $schoolID = (int) $access['schoolID'];
        $accessService = app(StoreAccessService::class);

        StoreDefaultsService::seedForSchool($schoolID);

        $categories = StoreItemCategory::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('category_name')
            ->get();

        $measurementTypes = StoreMeasurementType::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['units' => function ($q) {
                $q->where('status', 'Active')->orderBy('unit_name');
            }])
            ->orderBy('type_name')
            ->get();

        $items = StoreItem::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['category', 'measurementType', 'units', 'packageSpecs.package', 'packageSpecs.unit'])
            ->orderBy('item_name')
            ->get();

        $items = $accessService->filterItemsByAccess($items, $access);

        $allowedCategoryIds = $accessService->allowedCategoryIds($access);
        if ($allowedCategoryIds !== null) {
            $categories = $categories->filter(
                fn ($c) => in_array((int) $c->categoryID, $allowedCategoryIds, true)
            )->values();
        }

        $packages = StorePackage::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('package_name')
            ->get();

        $storePackagesList = $packages->map(function ($p) {
            return [
                'packageID' => $p->packageID,
                'package_name' => $p->package_name,
            ];
        })->values();

        $storeItemsList = $items->map(function ($item) {
            $packageTrees = $this->buildPackageSpecTrees($item->packageSpecs);
            return [
                'itemID' => $item->itemID,
                'item_name' => $item->item_name,
                'measurement_type' => $item->measurementType->type_name ?? '',
                'has_packages' => (bool) $item->has_packages,
                'units' => $item->units->map(fn ($u) => [
                    'unitID' => $u->unitID,
                    'unit_name' => $u->unit_name,
                ])->values(),
                'package_specs' => $packageTrees,
                'stockable_packages' => $this->flattenStockablePackages($packageTrees),
            ];
        })->values();

        $stockEntries = StoreItemStockEntry::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['item', 'package', 'unit', 'stockUnits'])
            ->withCount([
                'stockUnits as available_count' => function ($q) {
                    $q->where('stock_status', 'in_stock');
                },
                'stockUnits as issued_count' => function ($q) {
                    $q->where('stock_status', '!=', 'in_stock');
                },
            ])
            ->orderByDesc('created_at')
            ->get();

        if ($allowedCategoryIds !== null) {
            $stockEntries = $stockEntries->filter(
                fn ($e) => $accessService->itemAllowed($access, $e->item->categoryID ?? null)
            )->values();
        }

        $requisitions = StoreRequisition::where('schoolID', $schoolID)
            ->with(['item', 'package', 'unit'])
            ->orderByDesc('issued_at')
            ->get();

        $damagedIncidents = StoreItemIncident::where('schoolID', $schoolID)
            ->where('incident_type', 'damaged')
            ->with(['item', 'package', 'unit'])
            ->orderByDesc('created_at')
            ->get();

        $lostIncidents = StoreItemIncident::where('schoolID', $schoolID)
            ->where('incident_type', 'lost')
            ->with(['item', 'package', 'unit'])
            ->orderByDesc('created_at')
            ->get();

        $studentClassMap = [];
        $studentRecipientIds = $requisitions->where('recipient_type', 'student')->pluck('recipient_id')->unique()->filter()->all();
        $studentIncidentIds = $damagedIncidents->concat($lostIncidents)
            ->where('responsible_type', 'student')
            ->pluck('responsible_id')
            ->unique()
            ->filter()
            ->all();
        $allStudentIds = array_unique(array_merge($studentRecipientIds, $studentIncidentIds));
        if (!empty($allStudentIds)) {
            $studentClassMap = Student::where('schoolID', $schoolID)
                ->whereIn('studentID', $allStudentIds)
                ->with(['subclass.class'])
                ->get(['studentID', 'subclassID'])
                ->mapWithKeys(fn ($st) => [
                    $st->studentID => $st->subclass?->display_name ?: null,
                ])
                ->all();
        }

        $availableStockUnits = StoreStockUnit::where('schoolID', $schoolID)
            ->where('remaining_items', '>', 0)
            ->with(['item.category', 'item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
            ->orderBy('itemID')
            ->get();

        if ($allowedCategoryIds !== null) {
            $availableStockUnits = $availableStockUnits->filter(
                fn ($u) => $accessService->itemAllowed($access, $u->item->categoryID ?? null)
            )->values();
        }

        $teachers = Teacher::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_number']);

        $staffMembers = OtherStaff::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_number']);

        $students = Student::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['subclass.class'])
            ->orderBy('first_name')
            ->get(['studentID', 'subclassID', 'first_name', 'middle_name', 'last_name', 'admission_number']);

        $requestersList = $teachers->map(fn ($t) => [
            'type' => 'teacher',
            'id' => $t->id,
            'name' => $this->formatPersonName($t->first_name, $t->middle_name, $t->last_name),
            'employee_number' => $t->employee_number,
            'class_name' => null,
            'group' => 'Teachers',
        ])->concat($staffMembers->map(fn ($s) => [
            'type' => 'staff',
            'id' => $s->id,
            'name' => $this->formatPersonName($s->first_name, $s->middle_name, $s->last_name),
            'employee_number' => $s->employee_number,
            'class_name' => null,
            'group' => 'Staff',
        ]))->concat($students->map(fn ($st) => [
            'type' => 'student',
            'id' => $st->studentID,
            'name' => $this->formatPersonName($st->first_name, $st->middle_name, $st->last_name),
            'employee_number' => $st->admission_number,
            'class_name' => $st->subclass?->display_name ?: null,
            'group' => 'Students',
        ]))->values();

        $availableStockList = $availableStockUnits
            ->map(fn ($u) => $this->formatAvailableStockUnitUi($u))
            ->values();

        $measurementTypesState = $measurementTypes->map(function ($type) {
            return [
                'measurement_typeID' => $type->measurement_typeID,
                'type_name' => $type->type_name,
                'is_system' => (bool) $type->is_system,
                'units' => $type->units->map(fn ($u) => [
                    'unitID' => $u->unitID,
                    'unit_name' => $u->unit_name,
                    'abbreviation' => $u->abbreviation,
                ])->values(),
            ];
        })->values();

        // Package chains for edit forms (avoid fragile HTML data attributes).
        $itemPackageSpecsMap = $items->mapWithKeys(function ($item) {
            return [(string) $item->itemID => $this->formatItemUi($item)['package_specs_payload']];
        });

        $dashboardStats = $this->buildStoreDashboardStats($schoolID, null, $allowedCategoryIds);

        $damagedIncidentsUi = $damagedIncidents->map(function ($inc) use ($studentClassMap) {
            $className = $inc->responsible_type === 'student'
                ? ($studentClassMap[$inc->responsible_id] ?? null)
                : null;
            return $this->formatIncidentUi($inc, $className);
        })->values()->all();

        $lostIncidentsUi = $lostIncidents->map(function ($inc) use ($studentClassMap) {
            $className = $inc->responsible_type === 'student'
                ? ($studentClassMap[$inc->responsible_id] ?? null)
                : null;
            return $this->formatIncidentUi($inc, $className);
        })->values()->all();

        $reportItemsList = $items->map(fn ($item) => [
            'itemID' => (int) $item->itemID,
            'item_name' => $item->item_name,
            'categoryID' => $item->categoryID ? (int) $item->categoryID : null,
        ])->values();

        if ($allowedCategoryIds !== null) {
            $requisitions = $requisitions->filter(
                fn ($r) => $accessService->itemAllowed($access, $r->item->categoryID ?? null)
            )->values();
            $damagedIncidents = $damagedIncidents->filter(
                fn ($inc) => $accessService->itemAllowed($access, $inc->item->categoryID ?? null)
            )->values();
            $lostIncidents = $lostIncidents->filter(
                fn ($inc) => $accessService->itemAllowed($access, $inc->item->categoryID ?? null)
            )->values();
        }

        $allCategories = StoreItemCategory::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->orderBy('category_name')
            ->get();

        $inventoryActors = collect();
        if (!empty($access['can_manage_actors'])) {
            $inventoryActors = StoreInventoryActor::where('schoolID', $schoolID)
                ->where('status', 'active')
                ->orderBy('actor_name')
                ->get()
                ->map(fn ($a) => $this->formatInventoryActorUi($a))
                ->values();
        }

        $itemRequests = StoreItemRequest::where('schoolID', $schoolID)
            ->where('status', 'requested')
            ->orderByDesc('requested_at')
            ->get()
            ->filter(fn ($r) => $this->requestVisibleToAccess($r, $access, $schoolID))
            ->map(fn ($r) => $this->formatAdminItemRequestUi($r, $access))
            ->values();

        $recentReviewedRequests = StoreItemRequest::where('schoolID', $schoolID)
            ->whereIn('status', ['rejected', 'cancelled'])
            ->orderByDesc('reviewed_at')
            ->limit(30)
            ->get()
            ->filter(fn ($r) => $this->requestVisibleToAccess($r, $access, $schoolID))
            ->map(fn ($r) => $this->formatAdminItemRequestUi($r, $access))
            ->values();

        $unreadRequestCount = $itemRequests->filter(fn ($r) => !empty($r['is_new']))->count();

        $existingActorKeys = StoreInventoryActor::where('schoolID', $schoolID)
            ->where('status', 'active')
            ->get()
            ->mapWithKeys(fn ($a) => [$a->actor_type . ':' . $a->actor_ref_id => true])
            ->all();

        $actorPickerTeachers = $teachers->filter(
            fn ($t) => !isset($existingActorKeys['teacher:' . $t->id])
        )->map(fn ($t) => [
            'type' => 'teacher',
            'id' => $t->id,
            'name' => $this->formatPersonName($t->first_name, $t->middle_name, $t->last_name),
            'employee_number' => $t->employee_number,
        ])->values();

        $actorPickerStaff = $staffMembers->filter(
            fn ($s) => !isset($existingActorKeys['staff:' . $s->id])
        )->map(fn ($s) => [
            'type' => 'staff',
            'id' => $s->id,
            'name' => $this->formatPersonName($s->first_name, $s->middle_name, $s->last_name),
            'employee_number' => $s->employee_number,
        ])->values();

        $storeAccess = $access;

        return view('Admin.manage_store', compact(
            'schoolID',
            'categories',
            'allCategories',
            'measurementTypes',
            'measurementTypesState',
            'itemPackageSpecsMap',
            'items',
            'packages',
            'storePackagesList',
            'storeItemsList',
            'reportItemsList',
            'stockEntries',
            'requisitions',
            'damagedIncidentsUi',
            'lostIncidentsUi',
            'studentClassMap',
            'requestersList',
            'availableStockList',
            'dashboardStats',
            'storeAccess',
            'inventoryActors',
            'itemRequests',
            'recentReviewedRequests',
            'unreadRequestCount',
            'actorPickerTeachers',
            'actorPickerStaff'
        ));
    }

    public function storeDashboard(Request $request)
    {
        $access = $this->getStoreAccess();
        if (!$access) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $accessService = app(StoreAccessService::class);
        $allowedCategoryIds = $accessService->allowedCategoryIds($access);

        $request->validate([
            'categoryID' => 'nullable|integer',
        ]);

        $categoryID = $request->filled('categoryID') ? (int) $request->categoryID : null;
        if ($categoryID && $allowedCategoryIds !== null && !in_array($categoryID, $allowedCategoryIds, true)) {
            return $this->jsonError('Category not allowed for your access.', 403);
        }

        return response()->json([
            'success' => true,
            'dashboard' => $this->buildStoreDashboardStats($schoolID, $categoryID, $allowedCategoryIds),
        ]);
    }

    private function categoryItemIds(int $schoolID, ?int $categoryID): ?array
    {
        if (!$categoryID) {
            return null;
        }

        return StoreItem::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->where('categoryID', $categoryID)
            ->pluck('itemID')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return array{
     *     categoryID: ?int,
     *     category_name: ?string,
     *     filter_label: string,
     *     scoped_item_ids: ?array,
     *     scoped_category_ids: ?array,
     *     item_detail_mode: string
     * }
     */
    private function resolveDashboardScope(int $schoolID, ?int $categoryID, ?array $allowedCategoryIds): array
    {
        if ($categoryID) {
            if ($allowedCategoryIds !== null && !in_array($categoryID, $allowedCategoryIds, true)) {
                return [
                    'categoryID' => null,
                    'category_name' => null,
                    'filter_label' => 'No Access',
                    'scoped_item_ids' => [0],
                    'scoped_category_ids' => [],
                    'item_detail_mode' => 'by_category',
                ];
            }

            $categoryName = StoreItemCategory::where('schoolID', $schoolID)
                ->where('categoryID', $categoryID)
                ->where('status', 'Active')
                ->value('category_name');

            return [
                'categoryID' => $categoryID,
                'category_name' => $categoryName,
                'filter_label' => $categoryName ?: 'Category',
                'scoped_item_ids' => $this->categoryItemIds($schoolID, $categoryID) ?: [0],
                'scoped_category_ids' => [$categoryID],
                'item_detail_mode' => 'by_item',
            ];
        }

        if ($allowedCategoryIds !== null) {
            if ($allowedCategoryIds === []) {
                return [
                    'categoryID' => null,
                    'category_name' => null,
                    'filter_label' => 'No Categories',
                    'scoped_item_ids' => [0],
                    'scoped_category_ids' => [],
                    'item_detail_mode' => 'by_category',
                ];
            }

            $itemIds = StoreItem::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereIn('categoryID', $allowedCategoryIds)
                ->pluck('itemID')
                ->map(fn ($id) => (int) $id)
                ->all();

            $filterLabel = count($allowedCategoryIds) === 1
                ? (StoreItemCategory::where('schoolID', $schoolID)
                    ->where('categoryID', $allowedCategoryIds[0])
                    ->where('status', 'Active')
                    ->value('category_name') ?: 'My Category')
                : 'All My Categories';

            return [
                'categoryID' => null,
                'category_name' => null,
                'filter_label' => $filterLabel,
                'scoped_item_ids' => $itemIds ?: [0],
                'scoped_category_ids' => $allowedCategoryIds,
                'item_detail_mode' => 'by_category',
            ];
        }

        return [
            'categoryID' => null,
            'category_name' => null,
            'filter_label' => 'All Categories',
            'scoped_item_ids' => null,
            'scoped_category_ids' => null,
            'item_detail_mode' => 'by_category',
        ];
    }

    private function buildStoreDashboardStats(int $schoolID, ?int $categoryID = null, ?array $allowedCategoryIds = null): array
    {
        $scope = $this->resolveDashboardScope($schoolID, $categoryID, $allowedCategoryIds);
        $categoryID = $scope['categoryID'];
        $categoryName = $scope['category_name'];
        $filterLabel = $scope['filter_label'];
        $scopedItemIds = $scope['scoped_item_ids'];
        $scopedCategoryIds = $scope['scoped_category_ids'];

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $itemsQuery = StoreItem::where('schoolID', $schoolID)->where('status', 'Active');
        if ($scopedItemIds !== null) {
            $itemsQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $registeredItems = $itemsQuery->count();

        if ($scopedCategoryIds !== null) {
            $categoriesCount = count($scopedCategoryIds);
        } else {
            $categoriesCount = StoreItemCategory::where('schoolID', $schoolID)->where('status', 'Active')->count();
        }
        $packagesCount = StorePackage::where('schoolID', $schoolID)->where('status', 'Active')->count();

        $stockBatchesQuery = StoreItemStockEntry::where('schoolID', $schoolID)->where('status', 'Active');
        if ($scopedItemIds !== null) {
            $stockBatchesQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $stockBatches = $stockBatchesQuery->count();

        $unitsQuery = StoreStockUnit::where('schoolID', $schoolID);
        if ($scopedItemIds !== null) {
            $unitsQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $allUnits = $unitsQuery->get();

        $inStockUnits = $allUnits->filter(fn ($u) => $u->stock_status === 'in_stock' && (float) $u->remaining_items > 0);
        $depletedUnits = $allUnits->filter(fn ($u) => $u->stock_status === 'requisitioned' || (float) $u->remaining_items <= 0);
        $defectiveUnits = $allUnits->filter(fn ($u) => $u->stock_status === 'defective');
        $partialUnits = $allUnits->filter(fn ($u) =>
            $u->stock_status === 'in_stock'
            && (float) $u->remaining_items > 0
            && (float) $u->remaining_items < (float) $u->initial_items
        );

        $totalRemainingItems = (float) $inStockUnits->sum('remaining_items');
        $itemsWithStock = $inStockUnits->pluck('itemID')->unique()->count();

        $issuedQuery = StoreRequisition::where('schoolID', $schoolID)
            ->whereBetween('issued_at', [$monthStart, $monthEnd]);
        if ($scopedItemIds !== null) {
            $issuedQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $issuedThisMonth = $issuedQuery->count();

        $activeIssuedQuery = StoreRequisition::where('schoolID', $schoolID)->where('status', 'issued');
        if ($scopedItemIds !== null) {
            $activeIssuedQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $activeIssued = $activeIssuedQuery->get();

        $pendingReturns = $activeIssued->filter(fn ($r) => $r->requisition_status === 'can_return')->count();

        $overdueReturns = $activeIssued->filter(function ($r) {
            return $r->requisition_status === 'can_return'
                && $r->expected_return_date
                && $r->expected_return_date->isPast();
        });

        $returnedQuery = StoreRequisition::where('schoolID', $schoolID)
            ->where('status', 'returned')
            ->whereBetween('returned_at', [$monthStart, $monthEnd]);
        if ($scopedItemIds !== null) {
            $returnedQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $returnedThisMonth = $returnedQuery->count();

        $returnedDefectiveQuery = StoreRequisition::where('schoolID', $schoolID)
            ->where('status', 'returned')
            ->whereIn('return_condition', ['bad', 'damaged']);
        if ($scopedItemIds !== null) {
            $returnedDefectiveQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $returnedDefective = $returnedDefectiveQuery->count();

        $returnedLostQuery = StoreRequisition::where('schoolID', $schoolID)
            ->where('status', 'returned')
            ->where('return_condition', 'lost');
        if ($scopedItemIds !== null) {
            $returnedLostQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $returnedLost = $returnedLostQuery->count();

        $damagedQuery = StoreItemIncident::where('schoolID', $schoolID)->where('incident_type', 'damaged');
        if ($scopedItemIds !== null) {
            $damagedQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $damagedRecords = $damagedQuery->count();

        $lostQuery = StoreItemIncident::where('schoolID', $schoolID)->where('incident_type', 'lost');
        if ($scopedItemIds !== null) {
            $lostQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $lostRecords = $lostQuery->count();

        $recentQuery = StoreRequisition::where('schoolID', $schoolID)->with(['item']);
        if ($scopedItemIds !== null) {
            $recentQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $recentRequisitions = $recentQuery->orderByDesc('issued_at')
            ->limit(8)
            ->get()
            ->map(function ($req) {
                $isOverdue = $req->status === 'issued'
                    && $req->requisition_status === 'can_return'
                    && $req->expected_return_date
                    && $req->expected_return_date->isPast();

                return [
                    'requisitionID' => (int) $req->requisitionID,
                    'item_name' => $req->item->item_name ?? '—',
                    'recipient_name' => $req->recipient_name,
                    'recipient_type' => $req->recipient_type,
                    'label' => $req->label,
                    'take_display' => $req->take_display,
                    'status' => $req->status,
                    'requisition_status' => $req->requisition_status,
                    'issued_at' => $req->issued_at ? $req->issued_at->format('d M Y') : '—',
                    'is_overdue' => $isOverdue,
                ];
            })
            ->values()
            ->all();

        $overdueList = $overdueReturns->take(6)->map(function ($req) {
            return [
                'requisitionID' => (int) $req->requisitionID,
                'item_name' => $req->item->item_name ?? '—',
                'recipient_name' => $req->recipient_name,
                'label' => $req->label,
                'expected_return_date' => $req->expected_return_date
                    ? $req->expected_return_date->format('d M Y')
                    : '—',
                'days_overdue' => $req->expected_return_date
                    ? (int) now()->diffInDays($req->expected_return_date)
                    : 0,
            ];
        })->values()->all();

        if ($scope['item_detail_mode'] === 'by_item' && $categoryID) {
            $categoryBreakdown = StoreItem::where('schoolID', $schoolID)
                ->where('categoryID', $categoryID)
                ->where('status', 'Active')
                ->orderBy('item_name')
                ->get()
                ->map(function ($item) use ($inStockUnits) {
                    $labelsInStock = $inStockUnits->where('itemID', $item->itemID)->count();
                    return [
                        'category_name' => $item->item_name,
                        'item_count' => $labelsInStock,
                    ];
                })
                ->values()
                ->all();
        } else {
            $breakdownQuery = StoreItem::where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->with('category');
            if ($scopedCategoryIds !== null) {
                $breakdownQuery->whereIn('categoryID', $scopedCategoryIds);
            }
            $categoryBreakdown = $breakdownQuery
                ->get()
                ->groupBy(fn ($item) => $item->category->category_name ?? 'Uncategorized')
                ->map(fn ($group, $name) => [
                    'category_name' => $name,
                    'item_count' => $group->count(),
                ])
                ->sortByDesc('item_count')
                ->values()
                ->take(6)
                ->all();
        }

        $topIssuedQuery = StoreRequisition::where('schoolID', $schoolID)
            ->whereBetween('issued_at', [$monthStart, $monthEnd]);
        if ($scopedItemIds !== null) {
            $topIssuedQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }
        $topIssuedItems = $topIssuedQuery
            ->select('itemID', DB::raw('COUNT(*) as issue_count'))
            ->groupBy('itemID')
            ->orderByDesc('issue_count')
            ->limit(5)
            ->get();

        $itemNames = StoreItem::whereIn('itemID', $topIssuedItems->pluck('itemID'))
            ->pluck('item_name', 'itemID');

        $topIssued = $topIssuedItems->map(fn ($row) => [
            'item_name' => $itemNames[$row->itemID] ?? '—',
            'issue_count' => (int) $row->issue_count,
        ])->values()->all();

        $inventoryDetail = $this->buildDashboardInventoryDetail($schoolID, $scopedItemIds);

        return [
            'generated_at' => $now->format('d M Y, H:i'),
            'categoryID' => $categoryID,
            'category_name' => $categoryName,
            'filter_label' => $filterLabel,
            'summary' => [
                'registered_items' => $registeredItems,
                'categories' => $categoriesCount,
                'packages' => $packagesCount,
                'stock_batches' => $stockBatches,
                'items_with_stock' => $itemsWithStock,
                'labels_in_stock' => $inStockUnits->count(),
                'labels_partial' => $partialUnits->count(),
                'labels_depleted' => $depletedUnits->count(),
                'labels_defective' => $defectiveUnits->count(),
                'total_remaining_items' => $this->formatQty($totalRemainingItems),
                'issued_this_month' => $issuedThisMonth,
                'active_issued' => $activeIssued->count(),
                'pending_returns' => $pendingReturns,
                'overdue_returns' => $overdueReturns->count(),
                'returned_this_month' => $returnedThisMonth,
                'returned_defective' => $returnedDefective,
                'returned_lost' => $returnedLost,
                'damaged_records' => $damagedRecords,
                'lost_records' => $lostRecords,
            ],
            'recent_requisitions' => $recentRequisitions,
            'overdue_list' => $overdueList,
            'category_breakdown' => $categoryBreakdown,
            'top_issued_this_month' => $topIssued,
            'inventory_detail' => $inventoryDetail,
        ];
    }

    /**
     * Per-item inventory rows for dashboard/PDF — package type, sub-packages, in store vs issued.
     */
    private function buildDashboardInventoryDetail(int $schoolID, ?array $scopedItemIds = null): array
    {
        $unitsQuery = StoreStockUnit::where('schoolID', $schoolID)
            ->with([
                'item.category',
                'item.measurementType',
                'item.units',
                'item.packageSpecs.package',
                'item.packageSpecs.unit',
                'package',
                'unit',
                'stockEntry',
            ]);

        if ($scopedItemIds !== null) {
            $unitsQuery->whereIn('itemID', $scopedItemIds ?: [0]);
        }

        $allUnits = $unitsQuery->get();
        $rows = [];

        foreach ($allUnits->groupBy('itemID') as $units) {
            $item = $units->first()?->item;
            if (!$item) {
                continue;
            }

            $hasPackages = (bool) $item->has_packages;
            $packageDisplay = '—';
            $hasSubPack = 'No';
            $packageStructure = '—';

            if ($hasPackages) {
                $itemUi = $this->formatItemUi($item);
                $lines = $itemUi['package_lines'] ?? [];
                $packageStructure = !empty($lines) ? implode(' · ', $lines) : '—';

                $roots = $item->packageSpecs->filter(fn ($s) => $s->parent_specID === null);
                $hasSubPack = $roots->contains(fn ($s) => $s->has_sub_package) ? 'Yes' : 'No';

                $packageNames = $units->where('store_method', 'package')
                    ->map(fn ($u) => $this->buildPackageUnitDisplay($u))
                    ->unique()
                    ->filter()
                    ->values();

                if ($packageNames->isNotEmpty()) {
                    $packageDisplay = $packageNames->implode('; ');
                } else {
                    $packageDisplay = $roots->first()?->package?->package_name ?? 'Package';
                }
            } else {
                $unitNames = $item->units->pluck('unit_name')->filter()->unique()->values();
                $packageDisplay = $unitNames->isNotEmpty()
                    ? $unitNames->implode(', ')
                    : ($item->measurementType->type_name ?? 'Unit');
            }

            $inStore = $units->filter(
                fn ($u) => $u->stock_status === 'in_stock' && (float) $u->remaining_items > 0
            );
            $fullyIssued = $units->filter(
                fn ($u) => $u->stock_status === 'requisitioned' || (float) $u->remaining_items <= 0
            );
            $partial = $units->filter(
                fn ($u) => $u->stock_status === 'in_stock'
                    && (float) $u->remaining_items > 0
                    && (float) $u->remaining_items < (float) $u->initial_items
            );
            $defective = $units->where('stock_status', 'defective');

            $itemsRemaining = (float) $inStore->sum('remaining_items');
            $itemsTaken = (float) $units->sum(
                fn ($u) => max(0, (float) $u->initial_items - (float) $u->remaining_items)
            );

            $rows[] = [
                'item_name' => $item->item_name,
                'category_name' => $item->category->category_name ?? '—',
                'store_type' => $hasPackages ? 'Package' : 'Unit',
                'package_display' => $packageDisplay,
                'has_sub_package' => $hasSubPack,
                'package_structure' => $packageStructure,
                'labels_in_store' => $inStore->count(),
                'labels_issued' => $fullyIssued->count(),
                'labels_partial' => $partial->count(),
                'labels_defective' => $defective->count(),
                'labels_total' => $units->count(),
                'items_in_store' => $this->formatQty($itemsRemaining),
                'items_taken' => $this->formatQty($itemsTaken),
            ];
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['item_name'], $b['item_name']));

        return $rows;
    }

    public function itemsRequests()
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        return redirect()->route('manage_store', ['open' => 'requisitions']);
    }

    protected function formatAdminItemRequestUi(StoreItemRequest $req, ?array $access = null): array
    {
        $accessService = app(StoreAccessService::class);
        $canApprove = $access
            ? $accessService->can($access, 'approve_requests')
            && $this->requestVisibleToAccess($req, $access, (int) $req->schoolID)
            : true;

        $isNew = $req->status === 'requested' && !$req->is_read_by_operator;

        $statusHtml = match ($req->status) {
            'requested' => $isNew
                ? '<span class="store-badge-pill store-badge-new-request">Requested — New</span>'
                : '<span class="store-badge-pill store-badge-pending">Requested</span>',
            'approved' => '<span class="store-badge-pill store-badge-return-yes">Approved</span>',
            'rejected' => '<span class="store-badge-pill store-badge-return-no">Rejected</span>',
            'cancelled' => '<span class="store-badge-pill store-badge-return-no">Cancelled</span>',
            default => '<span class="store-badge-pill store-badge-pending">' . e(ucfirst($req->status)) . '</span>',
        };

        return [
            'requestID' => (int) $req->requestID,
            'requester_type' => $req->requester_type,
            'requester_type_label' => ucfirst($req->requester_type),
            'requester_id' => (int) $req->requester_id,
            'requester_name' => $req->requester_name,
            'status' => $req->status,
            'status_label' => ucfirst($req->status),
            'status_html' => $statusHtml,
            'is_new' => $isNew,
            'is_read_by_operator' => (bool) $req->is_read_by_operator,
            'can_approve' => $canApprove,
            'items_summary' => $req->items_summary,
            'notes' => $req->notes,
            'expected_return_date' => $req->expected_return_date
                ? $req->expected_return_date->format('d M Y')
                : null,
            'requested_at' => $req->requested_at ? $req->requested_at->format('d M Y, H:i') : '—',
            'requested_by' => $req->requested_by,
            'reviewed_at' => $req->reviewed_at ? $req->reviewed_at->format('d M Y, H:i') : null,
            'reviewed_by' => $req->reviewed_by,
            'review_notes' => $req->review_notes,
        ];
    }

    protected function formatInventoryActorUi(StoreInventoryActor $actor): array
    {
        $privilegeLabels = StoreInventoryActor::privilegeLabels();
        $privileges = array_merge(
            StoreInventoryActor::defaultPrivileges(),
            is_array($actor->privileges) ? $actor->privileges : []
        );
        $enabled = collect($privileges)->filter()->keys()->map(
            fn ($k) => $privilegeLabels[$k] ?? ucfirst(str_replace('_', ' ', $k))
        )->values()->all();

        $categorySummary = $actor->category_scope === 'all'
            ? 'All categories'
            : (collect($actor->category_ids ?? [])->count() . ' specific categor' . (count($actor->category_ids ?? []) === 1 ? 'y' : 'ies'));

        return [
            'actorID' => (int) $actor->actorID,
            'actor_type' => $actor->actor_type,
            'actor_type_label' => ucfirst($actor->actor_type),
            'actor_ref_id' => (int) $actor->actor_ref_id,
            'actor_name' => $actor->actor_name,
            'category_scope' => $actor->category_scope,
            'category_ids' => $actor->category_ids ?? [],
            'category_summary' => $categorySummary,
            'privileges' => $privileges,
            'privileges_summary' => implode(', ', $enabled) ?: 'No privileges',
            'granted_by' => $actor->granted_by,
            'created_at' => $actor->created_at ? $actor->created_at->format('d M Y, H:i') : '—',
        ];
    }

    protected function resolveRequestCategoryIds(StoreItemRequest $req, int $schoolID): array
    {
        $issues = is_array($req->issues) ? $req->issues : [];
        $stockUnitIds = collect($issues)->pluck('stock_unitID')->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        if (empty($stockUnitIds)) {
            return [];
        }

        return StoreStockUnit::where('schoolID', $schoolID)
            ->whereIn('stock_unitID', $stockUnitIds)
            ->with('item')
            ->get()
            ->map(fn ($u) => $u->item->categoryID ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function requestVisibleToAccess(StoreItemRequest $req, array $access, int $schoolID): bool
    {
        $accessService = app(StoreAccessService::class);
        if ($accessService->isAdmin($access)) {
            return true;
        }
        if (!$accessService->can($access, 'approve_requests')) {
            return false;
        }

        $categoryIds = $this->resolveRequestCategoryIds($req, $schoolID);
        if (empty($categoryIds)) {
            return true;
        }

        foreach ($categoryIds as $categoryId) {
            if ($accessService->itemAllowed($access, $categoryId)) {
                return true;
            }
        }

        return false;
    }

    public function markItemRequestsRead(Request $request)
    {
        $access = $this->getStoreAccess('approve_requests');
        if (!$access) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $requestIds = collect($request->input('request_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $query = StoreItemRequest::where('schoolID', $schoolID)
            ->where('status', 'requested')
            ->where('is_read_by_operator', false);

        if (!empty($requestIds)) {
            $query->whereIn('requestID', $requestIds);
        }

        $requests = $query->get()->filter(
            fn ($r) => $this->requestVisibleToAccess($r, $access, $schoolID)
        );

        StoreItemRequest::whereIn('requestID', $requests->pluck('requestID')->all())
            ->update(['is_read_by_operator' => true]);

        return response()->json([
            'success' => true,
            'marked' => $requests->count(),
            'unread_count' => $this->countUnreadItemRequests($access, $schoolID),
        ]);
    }

    protected function countUnreadItemRequests(array $access, int $schoolID): int
    {
        return StoreItemRequest::where('schoolID', $schoolID)
            ->where('status', 'requested')
            ->where('is_read_by_operator', false)
            ->get()
            ->filter(fn ($r) => $this->requestVisibleToAccess($r, $access, $schoolID))
            ->count();
    }

    public function listInventoryActors()
    {
        $access = $this->getStoreAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $actors = StoreInventoryActor::where('schoolID', $schoolID)
            ->where('status', 'active')
            ->orderBy('actor_name')
            ->get()
            ->map(fn ($a) => $this->formatInventoryActorUi($a))
            ->values();

        return response()->json(['success' => true, 'actors' => $actors]);
    }

    public function storeInventoryActor(Request $request)
    {
        $access = $this->getStoreAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $request->validate([
            'actor_type' => 'required|in:teacher,staff',
            'actor_ref_id' => 'required|integer',
            'category_scope' => 'required|in:all,specific',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer',
            'privileges' => 'required|array',
        ]);

        $accessService = app(StoreAccessService::class);
        $name = $accessService->resolvePersonName($request->actor_type, (int) $request->actor_ref_id, $schoolID);
        if (!$name) {
            return $this->jsonError('Selected person not found or inactive.', 404);
        }

        $exists = StoreInventoryActor::where('schoolID', $schoolID)
            ->where('actor_type', $request->actor_type)
            ->where('actor_ref_id', (int) $request->actor_ref_id)
            ->where('status', 'active')
            ->exists();
        if ($exists) {
            return $this->jsonError('This person already has inventory access.', 422);
        }

        $categoryScope = $request->category_scope;
        $categoryIds = $categoryScope === 'specific'
            ? collect($request->category_ids ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all()
            : null;

        if ($categoryScope === 'specific' && empty($categoryIds)) {
            return $this->jsonError('Select at least one category for specific access.', 422);
        }

        $privileges = array_merge(
            StoreInventoryActor::defaultPrivileges(),
            collect($request->privileges)->mapWithKeys(fn ($v, $k) => [$k => (bool) $v])->all()
        );

        if (!collect($privileges)->contains(true)) {
            return $this->jsonError('Select at least one privilege.', 422);
        }

        $actor = StoreInventoryActor::create([
            'schoolID' => $schoolID,
            'actor_type' => $request->actor_type,
            'actor_ref_id' => (int) $request->actor_ref_id,
            'actor_name' => $name,
            'category_scope' => $categoryScope,
            'category_ids' => $categoryIds,
            'privileges' => $privileges,
            'status' => 'active',
            'granted_by' => $access['actor_name'] ?? Session::get('name') ?: 'Administrator',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory access granted to ' . $name . '.',
            'actor' => $this->formatInventoryActorUi($actor),
        ]);
    }

    public function updateInventoryActor(Request $request, $actorID)
    {
        $access = $this->getStoreAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $actor = StoreInventoryActor::where('schoolID', $schoolID)
            ->where('actorID', (int) $actorID)
            ->where('status', 'active')
            ->first();

        if (!$actor) {
            return $this->jsonError('Actor not found.', 404);
        }

        $request->validate([
            'category_scope' => 'required|in:all,specific',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer',
            'privileges' => 'required|array',
        ]);

        $categoryScope = $request->category_scope;
        $categoryIds = $categoryScope === 'specific'
            ? collect($request->category_ids ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all()
            : null;

        if ($categoryScope === 'specific' && empty($categoryIds)) {
            return $this->jsonError('Select at least one category for specific access.', 422);
        }

        $privileges = array_merge(
            StoreInventoryActor::defaultPrivileges(),
            collect($request->privileges)->mapWithKeys(fn ($v, $k) => [$k => (bool) $v])->all()
        );

        if (!collect($privileges)->contains(true)) {
            return $this->jsonError('Select at least one privilege.', 422);
        }

        $actor->update([
            'category_scope' => $categoryScope,
            'category_ids' => $categoryIds,
            'privileges' => $privileges,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory access updated.',
            'actor' => $this->formatInventoryActorUi($actor->fresh()),
        ]);
    }

    public function deleteInventoryActor($actorID)
    {
        $access = $this->getStoreAccess();
        if (!$access || empty($access['can_manage_actors'])) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $actor = StoreInventoryActor::where('schoolID', $schoolID)
            ->where('actorID', (int) $actorID)
            ->where('status', 'active')
            ->first();

        if (!$actor) {
            return $this->jsonError('Actor not found.', 404);
        }

        $name = $actor->actor_name;
        $actor->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Inventory access removed from ' . $name . '.',
        ]);
    }

    public function approveItemRequest(Request $request, $requestID)
    {
        $access = $this->getStoreAccess('approve_requests');
        if (!$access) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $schoolID = (int) $access['schoolID'];
        $itemRequest = StoreItemRequest::where('schoolID', $schoolID)
            ->where('requestID', (int) $requestID)
            ->where('status', 'requested')
            ->first();

        if (!$itemRequest) {
            return $this->jsonError('Requested item request not found.', 404);
        }

        if (!$this->requestVisibleToAccess($itemRequest, $access, $schoolID)) {
            return $this->jsonError('You do not have access to approve this request.', 403);
        }

        $issueRequest = Request::create('/store/requisitions', 'POST', [
            'recipient_type' => $itemRequest->requester_type,
            'recipient_id' => $itemRequest->requester_id,
            'issues' => $itemRequest->issues,
            'notes' => $itemRequest->notes,
            'expected_return_date' => $itemRequest->expected_return_date
                ? $itemRequest->expected_return_date->format('Y-m-d')
                : null,
        ]);
        $issueRequest->headers->set('Accept', 'application/json');
        $issueRequest->setLaravelSession($request->session());

        $response = $this->executeStoreRequisitionIssue($issueRequest, $access);
        $payload = json_decode($response->getContent(), true);

        if (!$response->isSuccessful() || empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['message'] ?? 'Failed to issue items for this request.',
            ], $response->getStatusCode() >= 400 ? $response->getStatusCode() : 422);
        }

        $reviewedBy = $access['actor_name'] ?? Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';
        $itemRequest->update([
            'status' => 'approved',
            'is_read_by_operator' => true,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'review_notes' => $request->input('review_notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request approved and items issued successfully.',
            'request' => $this->formatAdminItemRequestUi($itemRequest->fresh(), $access),
            'requisitions' => $payload['requisitions'] ?? [],
        ]);
    }

    public function rejectItemRequest(Request $request, $requestID)
    {
        $access = $this->getStoreAccess('approve_requests');
        if (!$access) {
            return $this->jsonError('Unauthorized');
        }

        $schoolID = (int) $access['schoolID'];
        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $itemRequest = StoreItemRequest::where('schoolID', $schoolID)
            ->where('requestID', (int) $requestID)
            ->where('status', 'requested')
            ->first();

        if (!$itemRequest) {
            return $this->jsonError('Requested item request not found.', 404);
        }

        if (!$this->requestVisibleToAccess($itemRequest, $access, $schoolID)) {
            return $this->jsonError('You do not have access to reject this request.', 403);
        }

        $reviewedBy = $access['actor_name'] ?? Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';
        $itemRequest->update([
            'status' => 'rejected',
            'is_read_by_operator' => true,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy,
            'review_notes' => $request->input('review_notes'),
        ]);

        try {
            app(StoreNotificationService::class)->notifyItemRequestRejected(
                $schoolID,
                $itemRequest->requester_type,
                (int) $itemRequest->requester_id,
                $itemRequest->requester_name,
                $itemRequest->items_summary,
                $request->input('review_notes')
            );
        } catch (\Throwable $e) {
            \Log::error('Store request rejection SMS failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Request rejected.',
            'request' => $this->formatAdminItemRequestUi($itemRequest->fresh(), $access),
        ]);
    }

    public function getUnitsByType($measurementTypeID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $type = StoreMeasurementType::where('measurement_typeID', $measurementTypeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$type) {
            return $this->jsonError('Measurement type not found', 404);
        }

        $units = StoreUnitOfMeasure::where('measurement_typeID', $type->measurement_typeID)
            ->where('status', 'Active')
            ->orderBy('unit_name')
            ->get(['unitID', 'unit_name', 'abbreviation']);

        return response()->json(['success' => true, 'units' => $units]);
    }

    public function storeCategory(Request $request)
    {
        $schoolID = $this->authorizeSchool('manage_categories');
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate(['category_name' => 'required|string|max:255']);

        $exists = StoreItemCategory::where('schoolID', $schoolID)
            ->where('category_name', $request->category_name)
            ->where('status', 'Active')
            ->exists();

        if ($exists) {
            return $this->jsonError('Category already exists', 422);
        }

        $category = StoreItemCategory::create([
            'schoolID' => $schoolID,
            'category_name' => $request->category_name,
            'status' => 'Active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category added successfully.',
            'category' => $category,
        ]);
    }

    public function updateCategory(Request $request, $categoryID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate(['category_name' => 'required|string|max:255']);

        $category = StoreItemCategory::where('categoryID', $categoryID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$category) {
            return $this->jsonError('Category not found', 404);
        }

        $duplicate = StoreItemCategory::where('schoolID', $schoolID)
            ->where('category_name', $request->category_name)
            ->where('status', 'Active')
            ->where('categoryID', '!=', $categoryID)
            ->exists();

        if ($duplicate) {
            return $this->jsonError('Category name already exists', 422);
        }

        $category->category_name = $request->category_name;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'category' => $category,
        ]);
    }

    public function deleteCategory($categoryID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $category = StoreItemCategory::where('categoryID', $categoryID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$category) {
            return $this->jsonError('Category not found', 404);
        }

        $inUse = StoreItem::where('categoryID', $categoryID)
            ->where('status', 'Active')
            ->exists();

        if ($inUse) {
            return $this->jsonError('Cannot delete category — it is used by store items.', 422);
        }

        $category->status = 'Deleted';
        $category->save();

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }

    public function storeMeasurementType(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate(['type_name' => 'required|string|max:255']);

        $exists = StoreMeasurementType::where('schoolID', $schoolID)
            ->where('type_name', $request->type_name)
            ->where('status', 'Active')
            ->exists();

        if ($exists) {
            return $this->jsonError('Measurement type already exists', 422);
        }

        $type = StoreMeasurementType::create([
            'schoolID' => $schoolID,
            'type_name' => $request->type_name,
            'is_system' => false,
            'status' => 'Active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Measurement type added successfully.',
            'measurement_type' => $type,
        ]);
    }

    public function updateMeasurementType(Request $request, $measurementTypeID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate(['type_name' => 'required|string|max:255']);

        $type = StoreMeasurementType::where('measurement_typeID', $measurementTypeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$type) {
            return $this->jsonError('Measurement type not found', 404);
        }

        $duplicate = StoreMeasurementType::where('schoolID', $schoolID)
            ->where('type_name', $request->type_name)
            ->where('status', 'Active')
            ->where('measurement_typeID', '!=', $measurementTypeID)
            ->exists();

        if ($duplicate) {
            return $this->jsonError('Measurement type name already exists', 422);
        }

        $type->type_name = $request->type_name;
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Measurement type updated successfully.',
            'measurement_type' => $type,
        ]);
    }

    public function deleteMeasurementType($measurementTypeID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $type = StoreMeasurementType::where('measurement_typeID', $measurementTypeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$type) {
            return $this->jsonError('Measurement type not found', 404);
        }

        $inUse = StoreItem::where('measurement_typeID', $measurementTypeID)
            ->where('status', 'Active')
            ->exists();

        if ($inUse) {
            return $this->jsonError('Cannot delete — measurement type is used by store items.', 422);
        }

        $type->status = 'Deleted';
        $type->save();

        StoreUnitOfMeasure::where('measurement_typeID', $measurementTypeID)
            ->update(['status' => 'Deleted']);

        return response()->json(['success' => true, 'message' => 'Measurement type deleted successfully.']);
    }

    public function storeUnit(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'measurement_typeID' => 'required|integer',
            'unit_name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:50',
        ]);

        $type = StoreMeasurementType::where('measurement_typeID', $request->measurement_typeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$type) {
            return $this->jsonError('Measurement type not found', 404);
        }

        $exists = StoreUnitOfMeasure::where('measurement_typeID', $type->measurement_typeID)
            ->where('unit_name', $request->unit_name)
            ->where('status', 'Active')
            ->exists();

        if ($exists) {
            return $this->jsonError('Unit already exists for this measurement type', 422);
        }

        $unit = StoreUnitOfMeasure::create([
            'measurement_typeID' => $type->measurement_typeID,
            'unit_name' => $request->unit_name,
            'abbreviation' => $request->abbreviation,
            'is_system' => false,
            'status' => 'Active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit of measure added successfully.',
            'unit' => $unit,
        ]);
    }

    public function updateUnit(Request $request, $unitID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'unit_name' => 'required|string|max:255',
            'abbreviation' => 'nullable|string|max:50',
        ]);

        $unit = StoreUnitOfMeasure::where('unitID', $unitID)
            ->where('status', 'Active')
            ->first();

        if (!$unit) {
            return $this->jsonError('Unit not found', 404);
        }

        $type = StoreMeasurementType::where('measurement_typeID', $unit->measurement_typeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$type) {
            return $this->jsonError('Unauthorized', 403);
        }

        $duplicate = StoreUnitOfMeasure::where('measurement_typeID', $unit->measurement_typeID)
            ->where('unit_name', $request->unit_name)
            ->where('status', 'Active')
            ->where('unitID', '!=', $unitID)
            ->exists();

        if ($duplicate) {
            return $this->jsonError('Unit name already exists', 422);
        }

        $unit->unit_name = $request->unit_name;
        $unit->abbreviation = $request->abbreviation;
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully.',
            'unit' => $unit,
        ]);
    }

    public function deleteUnit($unitID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $unit = StoreUnitOfMeasure::where('unitID', $unitID)
            ->where('status', 'Active')
            ->first();

        if (!$unit) {
            return $this->jsonError('Unit not found', 404);
        }

        $type = StoreMeasurementType::where('measurement_typeID', $unit->measurement_typeID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$type) {
            return $this->jsonError('Unauthorized', 403);
        }

        $inUse = StoreItem::where('unitID', $unitID)
            ->where('status', 'Active')
            ->exists();

        if (!$inUse) {
            $inUse = DB::table('store_item_units')
                ->join('store_items', 'store_item_units.itemID', '=', 'store_items.itemID')
                ->where('store_item_units.unitID', $unitID)
                ->where('store_items.status', 'Active')
                ->exists();
        }

        if ($inUse) {
            return $this->jsonError('Cannot delete — unit is used by store items.', 422);
        }

        $typeID = (int) $unit->measurement_typeID;
        $unit->status = 'Deleted';
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully.',
            'unitID' => (int) $unitID,
            'measurement_typeID' => $typeID,
        ]);
    }

    public function storeItem(Request $request)
    {
        $schoolID = $this->authorizeSchool('register_items');
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'item_name' => 'required|string|max:255',
            'categoryID' => 'required|integer',
            'measurement_typeID' => 'required|integer',
            'unitIDs' => 'required|array|min:1',
            'unitIDs.*' => 'integer',
        ]);

        $category = StoreItemCategory::where('categoryID', $request->categoryID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$category) {
            return $this->jsonError('Invalid category', 422);
        }

        $type = StoreMeasurementType::where('measurement_typeID', $request->measurement_typeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$type) {
            return $this->jsonError('Invalid measurement type', 422);
        }

        $unitIDs = array_values(array_unique(array_map('intval', $request->unitIDs)));
        $validUnits = StoreUnitOfMeasure::where('measurement_typeID', $type->measurement_typeID)
            ->whereIn('unitID', $unitIDs)
            ->where('status', 'Active')
            ->get();

        if ($validUnits->count() !== count($unitIDs)) {
            return $this->jsonError('One or more selected units are invalid for this measurement type', 422);
        }

        $exists = StoreItem::where('schoolID', $schoolID)
            ->where('item_name', $request->item_name)
            ->where('status', 'Active')
            ->exists();

        if ($exists) {
            return $this->jsonError('Item with this name already exists', 422);
        }

        $item = StoreItem::create([
            'schoolID' => $schoolID,
            'item_name' => $request->item_name,
            'categoryID' => $category->categoryID,
            'measurement_typeID' => $type->measurement_typeID,
            'unitID' => $validUnits->first()->unitID,
            'status' => 'Active',
        ]);

        $item->units()->sync($unitIDs);

        $packageError = $this->syncItemPackageSpecs($request, $item, $unitIDs, $schoolID);
        if ($packageError) {
            return $packageError;
        }

        $item->load(['category', 'measurementType', 'units', 'packageSpecs.package', 'packageSpecs.unit']);

        return response()->json([
            'success' => true,
            'message' => 'Store item saved successfully.',
            'item' => $this->formatItemUi($item),
        ]);
    }

    public function updateItem(Request $request, $itemID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'item_name' => 'required|string|max:255',
            'categoryID' => 'required|integer',
            'measurement_typeID' => 'required|integer',
            'unitIDs' => 'required|array|min:1',
            'unitIDs.*' => 'integer',
        ]);

        $item = StoreItem::where('itemID', $itemID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$item) {
            return $this->jsonError('Item not found', 404);
        }

        $category = StoreItemCategory::where('categoryID', $request->categoryID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        $type = StoreMeasurementType::where('measurement_typeID', $request->measurement_typeID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$category || !$type) {
            return $this->jsonError('Invalid category or measurement type', 422);
        }

        $unitIDs = array_values(array_unique(array_map('intval', $request->unitIDs)));
        $validUnits = StoreUnitOfMeasure::where('measurement_typeID', $type->measurement_typeID)
            ->whereIn('unitID', $unitIDs)
            ->where('status', 'Active')
            ->get();

        if ($validUnits->count() !== count($unitIDs)) {
            return $this->jsonError('One or more selected units are invalid for this measurement type', 422);
        }

        $duplicate = StoreItem::where('schoolID', $schoolID)
            ->where('item_name', $request->item_name)
            ->where('status', 'Active')
            ->where('itemID', '!=', $itemID)
            ->exists();

        if ($duplicate) {
            return $this->jsonError('Item name already exists', 422);
        }

        $item->item_name = $request->item_name;
        $item->categoryID = $category->categoryID;
        $item->measurement_typeID = $type->measurement_typeID;
        $item->unitID = $validUnits->first()->unitID;
        $item->save();

        $item->units()->sync($unitIDs);

        $packageError = $this->syncItemPackageSpecs($request, $item, $unitIDs, $schoolID);
        if ($packageError) {
            return $packageError;
        }

        $item->load(['category', 'measurementType', 'units', 'packageSpecs.package', 'packageSpecs.unit']);

        return response()->json([
            'success' => true,
            'message' => 'Store item updated successfully.',
            'item' => $this->formatItemUi($item),
        ]);
    }

    public function deleteItem($itemID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $item = StoreItem::where('itemID', $itemID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$item) {
            return $this->jsonError('Item not found', 404);
        }

        $item->status = 'Deleted';
        $item->save();

        StoreItemPackageSpec::where('itemID', $item->itemID)->delete();

        return response()->json(['success' => true, 'message' => 'Store item deleted successfully.']);
    }

    private function syncItemPackageSpecs(Request $request, StoreItem $item, array $unitIDs, int $schoolID)
    {
        $hasPackages = filter_var($request->input('has_packages', false), FILTER_VALIDATE_BOOLEAN);
        $item->has_packages = $hasPackages;
        $item->save();

        StoreItemPackageSpec::where('itemID', $item->itemID)->delete();

        if (!$hasPackages) {
            return null;
        }

        $specs = $request->input('package_specs', []);
        if (!is_array($specs) || count($specs) < 1) {
            return $this->jsonError('Add at least one package chain.', 422);
        }

        $rootPackageIds = [];
        foreach ($specs as $index => $spec) {
            $error = $this->savePackageSpecChain(
                $item->itemID,
                null,
                $spec,
                $unitIDs,
                $schoolID,
                $rootPackageIds,
                $index + 1
            );
            if ($error) {
                return $this->jsonError($error, 422);
            }
        }

        return null;
    }

    private function savePackageSpecChain(
        int $itemID,
        ?int $parentSpecID,
        array $spec,
        array $unitIDs,
        int $schoolID,
        array &$rootPackageIds,
        int $chainNo
    ): ?string {
        $packageID = (int) ($spec['packageID'] ?? 0);
        $quantity = $spec['quantity'] ?? null;
        $contentQuantity = $spec['content_quantity'] ?? null;
        $hasSub = filter_var($spec['has_sub_package'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $isRoot = $parentSpecID === null;
        $unitID = isset($spec['unitID']) && $spec['unitID'] !== '' && $spec['unitID'] !== null
            ? (int) $spec['unitID']
            : null;

        if (!$packageID) {
            return "Package chain #{$chainNo}: select a package name.";
        }

        $package = StorePackage::where('packageID', $packageID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$package) {
            return "Package chain #{$chainNo}: invalid package selected.";
        }

        if ($isRoot) {
            if (in_array($packageID, $rootPackageIds, true)) {
                return 'Each top-level package can only be used once per item.';
            }
            $rootPackageIds[] = $packageID;
        }

        if ($hasSub) {
            $child = $spec['child'] ?? null;
            if (!is_array($child) || empty($child['packageID'])) {
                return "Package chain #{$chainNo}: add the sub-package details.";
            }

            // Root with sub-packages has no quantity (stored as 1). Child packages carry quantity.
            $storedQty = $isRoot ? 1 : $quantity;
            if ($storedQty === null || $storedQty === '' || (float) $storedQty <= 0) {
                return "Package chain #{$chainNo}: enter how many of this package are inside the parent.";
            }

            $created = StoreItemPackageSpec::create([
                'itemID' => $itemID,
                'parent_specID' => $parentSpecID,
                'packageID' => $packageID,
                'has_sub_package' => true,
                'unitID' => null,
                'quantity' => $storedQty,
                'content_quantity' => null,
            ]);

            return $this->savePackageSpecChain(
                $itemID,
                (int) $created->specID,
                $child,
                $unitIDs,
                $schoolID,
                $rootPackageIds,
                $chainNo
            );
        }

        if (!$unitID) {
            return "Package chain #{$chainNo}: select unit of measure for the last package.";
        }

        if (!in_array($unitID, $unitIDs, true)) {
            return "Package chain #{$chainNo}: unit must be one of the item units selected.";
        }

        if ($isRoot) {
            // Simple package: quantity = items inside, no content_quantity.
            if ($quantity === null || $quantity === '' || (float) $quantity <= 0) {
                return "Package chain #{$chainNo}: enter quantity of items.";
            }
            $storedQty = $quantity;
            $storedContent = null;
        } else {
            // Leaf sub-package: quantity = how many in parent, content_quantity = items inside.
            if ($quantity === null || $quantity === '' || (float) $quantity <= 0) {
                return "Package chain #{$chainNo}: enter how many of this package are inside the parent.";
            }
            if ($contentQuantity === null || $contentQuantity === '' || (float) $contentQuantity <= 0) {
                return "Package chain #{$chainNo}: enter how many items are inside this package.";
            }
            $storedQty = $quantity;
            $storedContent = $contentQuantity;
        }

        StoreItemPackageSpec::create([
            'itemID' => $itemID,
            'parent_specID' => $parentSpecID,
            'packageID' => $packageID,
            'has_sub_package' => false,
            'unitID' => $unitID,
            'quantity' => $storedQty,
            'content_quantity' => $storedContent,
        ]);

        return null;
    }

    public function storePackagesBulk(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'package_names' => 'required|array|min:1',
            'package_names.*' => 'required|string|max:255',
        ]);

        $names = array_values(array_unique(array_filter(array_map(
            fn ($n) => trim((string) $n),
            $request->package_names
        ))));

        if (count($names) < 1) {
            return $this->jsonError('Enter at least one package name.', 422);
        }

        $created = [];
        $skipped = [];

        foreach ($names as $name) {
            $exists = StorePackage::where('schoolID', $schoolID)
                ->where('package_name', $name)
                ->where('status', 'Active')
                ->exists();

            if ($exists) {
                $skipped[] = $name;
                continue;
            }

            $created[] = StorePackage::create([
                'schoolID' => $schoolID,
                'package_categoryID' => null,
                'package_name' => $name,
                'status' => 'Active',
            ]);
        }

        if (count($created) === 0) {
            return $this->jsonError('All package names already exist: ' . implode(', ', $skipped), 422);
        }

        $message = count($created) . ' package(s) added successfully.';
        if (count($skipped) > 0) {
            $message .= ' Skipped duplicates: ' . implode(', ', $skipped) . '.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'packages' => $created,
        ]);
    }

    public function updatePackage(Request $request, $packageID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'package_name' => 'required|string|max:255',
        ]);

        $package = StorePackage::where('packageID', $packageID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$package) {
            return $this->jsonError('Package not found', 404);
        }

        $duplicate = StorePackage::where('schoolID', $schoolID)
            ->where('package_name', $request->package_name)
            ->where('status', 'Active')
            ->where('packageID', '!=', $packageID)
            ->exists();

        if ($duplicate) {
            return $this->jsonError('Package name already exists', 422);
        }

        $package->package_name = $request->package_name;
        $package->save();

        return response()->json([
            'success' => true,
            'message' => 'Package updated successfully.',
            'package' => $package,
        ]);
    }

    public function deletePackage($packageID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $package = StorePackage::where('packageID', $packageID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->first();

        if (!$package) {
            return $this->jsonError('Package not found', 404);
        }

        $inUse = StoreItemPackageSpec::where('packageID', $packageID)->exists();
        if ($inUse) {
            return $this->jsonError('Cannot delete — package is used by store items.', 422);
        }

        $package->status = 'Deleted';
        $package->save();

        return response()->json(['success' => true, 'message' => 'Package deleted successfully.']);
    }

    public function storeItemStock(Request $request)
    {
        $schoolID = $this->authorizeSchool('manage_stock');
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        if ($request->has('batches') && is_array($request->batches)) {
            return $this->storeMultiplePackageStock($request, $schoolID);
        }

        $request->validate([
            'itemID' => 'required|integer',
            'store_method' => 'required|in:package,unit',
            'packageID' => 'nullable|integer',
            'package_specID' => 'nullable|integer',
            'unitID' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:500',
            'labels' => 'required|array|min:1',
            'labels.*' => 'required|string|max:255',
            'requisition_status' => 'required|in:can_return,cant_return',
        ]);

        $item = $this->findActiveStoreItem($schoolID, (int) $request->itemID);
        if (!$item) {
            return $this->jsonError('Item not found.', 404);
        }

        try {
            $entry = $this->createStockEntryFromPayload($schoolID, $item, [
                'store_method' => $request->store_method,
                'packageID' => $request->packageID,
                'package_specID' => $request->package_specID,
                'unitID' => (int) $request->unitID,
                'quantity' => (int) $request->quantity,
                'labels' => $request->labels,
                'requisition_status' => $request->requisition_status,
            ]);

            return response()->json([
                'success' => true,
                'message' => count($request->labels) . ' item(s) added to stock successfully.',
                'entry' => $this->formatStockEntryUi($entry),
                'entries' => [$this->formatStockEntryUi($entry)],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->jsonError('Failed to save stock: ' . $e->getMessage(), 500);
        }
    }

    private function storeMultiplePackageStock(Request $request, int $schoolID)
    {
        $request->validate([
            'itemID' => 'required|integer',
            'store_method' => 'required|in:package',
            'requisition_status' => 'required|in:can_return,cant_return',
            'batches' => 'required|array|min:1',
            'batches.*.packageID' => 'nullable|integer',
            'batches.*.package_specID' => 'nullable|integer',
            'batches.*.unitID' => 'required|integer',
            'batches.*.quantity' => 'required|integer|min:1|max:500',
            'batches.*.labels' => 'required|array|min:1',
            'batches.*.labels.*' => 'required|string|max:255',
        ]);

        $item = $this->findActiveStoreItem($schoolID, (int) $request->itemID);
        if (!$item) {
            return $this->jsonError('Item not found.', 404);
        }

        if (!$item->has_packages) {
            return $this->jsonError('No package in this item.', 422);
        }

        $allLabels = [];
        foreach ($request->batches as $batch) {
            $labels = array_values(array_filter(array_map(
                fn ($l) => trim((string) $l),
                $batch['labels'] ?? []
            ), fn ($l) => $l !== ''));

            if (count($labels) !== (int) $batch['quantity']) {
                return $this->jsonError('Number of labels must match the quantity for each package receipt.', 422);
            }

            $allLabels = array_merge($allLabels, $labels);
        }

        if (count($allLabels) !== count(array_unique($allLabels))) {
            return $this->jsonError('Each label must be unique across all package receipts.', 422);
        }

        $duplicateLabel = StoreStockUnit::where('schoolID', $schoolID)
            ->where('itemID', $item->itemID)
            ->whereIn('label', $allLabels)
            ->where('stock_status', 'in_stock')
            ->exists();

        if ($duplicateLabel) {
            return $this->jsonError('One or more labels already exist in stock for this item.', 422);
        }

        DB::beginTransaction();
        try {
            $entries = [];
            foreach ($request->batches as $batch) {
                $entries[] = $this->createStockEntryFromPayload($schoolID, $item, [
                    'store_method' => 'package',
                    'packageID' => $batch['packageID'] ?? null,
                    'package_specID' => $batch['package_specID'] ?? null,
                    'unitID' => (int) $batch['unitID'],
                    'quantity' => (int) $batch['quantity'],
                    'labels' => $batch['labels'],
                    'requisition_status' => $request->requisition_status,
                ], false);
            }

            DB::commit();

            $entriesUi = collect($entries)->map(fn ($entry) => $this->formatStockEntryUi($entry))->values()->all();
            $totalLabels = count($allLabels);

            return response()->json([
                'success' => true,
                'message' => $totalLabels . ' item(s) added to stock across ' . count($entriesUi) . ' package receipt(s).',
                'entries' => $entriesUi,
                'entry' => $entriesUi[0] ?? null,
            ]);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return $this->jsonError($e->getMessage(), 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to save stock: ' . $e->getMessage(), 500);
        }
    }

    private function findActiveStoreItem(int $schoolID, int $itemID): ?StoreItem
    {
        return StoreItem::where('itemID', $itemID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['units', 'packageSpecs.package', 'packageSpecs.unit'])
            ->first();
    }

    private function createStockEntryFromPayload(
        int $schoolID,
        StoreItem $item,
        array $payload,
        bool $checkDuplicateLabels = true
    ): StoreItemStockEntry {
        $labels = array_values(array_map(fn ($l) => trim((string) $l), $payload['labels'] ?? []));
        $labels = array_filter($labels, fn ($l) => $l !== '');

        if (count($labels) !== (int) $payload['quantity']) {
            throw new \InvalidArgumentException('Number of labels must match the quantity entered.');
        }

        if (count($labels) !== count(array_unique($labels))) {
            throw new \InvalidArgumentException('Each label must be unique within this stock entry.');
        }

        if ($checkDuplicateLabels) {
            $duplicateLabel = StoreStockUnit::where('schoolID', $schoolID)
                ->where('itemID', $item->itemID)
                ->whereIn('label', $labels)
                ->where('stock_status', 'in_stock')
                ->exists();

            if ($duplicateLabel) {
                throw new \InvalidArgumentException('One or more labels already exist in stock for this item.');
            }
        }

        $unitIds = $item->units->pluck('unitID')->map(fn ($id) => (int) $id)->all();
        if (!in_array((int) $payload['unitID'], $unitIds, true)) {
            throw new \InvalidArgumentException('Selected unit is not configured for this item.');
        }

        $packageID = null;
        $packageSpecID = null;
        $contentPerUnit = null;

        if ($payload['store_method'] === 'package') {
            if (!$item->has_packages) {
                throw new \InvalidArgumentException('No package in this item.');
            }

            $specID = !empty($payload['package_specID']) ? (int) $payload['package_specID'] : null;
            $reqPackageID = !empty($payload['packageID']) ? (int) $payload['packageID'] : null;

            if (!$specID && !$reqPackageID) {
                throw new \InvalidArgumentException('Select a package for this item.');
            }

            $trees = $this->buildPackageSpecTrees($item->packageSpecs);
            $selected = $this->findStockablePackage($trees, $specID, $reqPackageID);
            if (!$selected) {
                throw new \InvalidArgumentException('Selected package is not configured for this item.');
            }

            $leafUnitID = $selected['leaf_unitID'] ?? null;
            if (!$leafUnitID) {
                throw new \InvalidArgumentException('Package chain is incomplete — last package needs a unit of measure.');
            }

            if ((int) $payload['unitID'] !== (int) $leafUnitID) {
                throw new \InvalidArgumentException('Package unit mismatch.');
            }

            $packageID = (int) $selected['packageID'];
            $packageSpecID = (int) $selected['specID'];
            $contentPerUnit = (float) $selected['items_per_package'];
        } elseif ($item->has_packages && (!empty($payload['packageID']) || !empty($payload['package_specID']))) {
            throw new \InvalidArgumentException('Use package store method when stocking packaged items.');
        }

        $initialItems = $payload['store_method'] === 'package'
            ? (float) ($contentPerUnit ?: 1)
            : 1;

        $entry = StoreItemStockEntry::create([
            'schoolID' => $schoolID,
            'itemID' => $item->itemID,
            'store_method' => $payload['store_method'],
            'packageID' => $packageID,
            'unitID' => (int) $payload['unitID'],
            'content_per_unit' => $contentPerUnit,
            'quantity' => (int) $payload['quantity'],
            'requisition_status' => $payload['requisition_status'],
            'status' => 'Active',
        ]);

        foreach ($labels as $label) {
            StoreStockUnit::create([
                'stock_entryID' => $entry->stock_entryID,
                'schoolID' => $schoolID,
                'itemID' => $item->itemID,
                'store_method' => $payload['store_method'],
                'packageID' => $packageID,
                'package_specID' => $packageSpecID,
                'unitID' => (int) $payload['unitID'],
                'label' => $label,
                'requisition_status' => $payload['requisition_status'],
                'stock_status' => 'in_stock',
                'initial_items' => $initialItems,
                'remaining_items' => $initialItems,
            ]);
        }

        $entry->load(['item', 'package', 'unit', 'stockUnits']);

        return $entry;
    }

    public function deleteItemStock($stockEntryID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $entry = StoreItemStockEntry::where('stock_entryID', $stockEntryID)
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with('stockUnits')
            ->first();

        if (!$entry) {
            return $this->jsonError('Stock entry not found.', 404);
        }

        $touched = $entry->stockUnits->contains(
            fn ($u) => (float) $u->remaining_items < (float) $u->initial_items
        );
        if ($touched) {
            return $this->jsonError('Cannot delete — some content has already been issued from this stock entry.', 422);
        }

        $removedUnitIds = $entry->stockUnits->pluck('stock_unitID')->map(fn ($id) => (int) $id)->values()->all();

        DB::beginTransaction();
        try {
            StoreStockUnit::where('stock_entryID', $entry->stock_entryID)->delete();
            $entry->status = 'Deleted';
            $entry->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock entry removed successfully.',
                'removed_stock_unitIDs' => $removedUnitIds,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to delete stock entry.', 500);
        }
    }

    public function storeRequisition(Request $request)
    {
        $access = $this->getStoreAccess('issue_items');
        if (!$access) {
            return $this->jsonError('Unauthorized');
        }

        return $this->executeStoreRequisitionIssue($request, $access);
    }

    protected function executeStoreRequisitionIssue(Request $request, array $access)
    {
        $schoolID = (int) $access['schoolID'];

        $request->validate([
            'recipient_type' => 'required|in:teacher,staff,student',
            'recipient_id' => 'required|integer',
            'issues' => 'required|array|min:1',
            'issues.*.stock_unitID' => 'required|integer',
            'issues.*.take_key' => 'required|string',
            'issues.*.quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string|max:1000',
            'expected_return_date' => 'nullable|date|after_or_equal:today',
        ]);

        $recipientName = null;
        if ($request->recipient_type === 'teacher') {
            $person = Teacher::where('id', $request->recipient_id)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->first();
            if ($person) {
                $recipientName = $this->formatPersonName($person->first_name, $person->middle_name, $person->last_name);
            }
        } elseif ($request->recipient_type === 'staff') {
            $person = OtherStaff::where('id', $request->recipient_id)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->first();
            if ($person) {
                $recipientName = $this->formatPersonName($person->first_name, $person->middle_name, $person->last_name);
            }
        } else {
            $person = Student::where('studentID', $request->recipient_id)
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->first();
            if ($person) {
                $recipientName = $this->formatPersonName($person->first_name, $person->middle_name, $person->last_name);
            }
        }

        if (!$recipientName) {
            return $this->jsonError('Selected recipient not found.', 404);
        }

        $issues = collect($request->issues);
        $stockUnitIDs = $issues->pluck('stock_unitID')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $units = StoreStockUnit::where('schoolID', $schoolID)
            ->whereIn('stock_unitID', $stockUnitIDs)
            ->where('remaining_items', '>', 0)
            ->with(['item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
            ->get()
            ->keyBy('stock_unitID');

        if ($units->count() !== count($stockUnitIDs)) {
            return $this->jsonError('One or more selected units are no longer available in stock.', 422);
        }

        $hasCanReturn = $units->contains(fn ($u) => $u->requisition_status === 'can_return');
        if ($hasCanReturn && !$request->filled('expected_return_date')) {
            return $this->jsonError('Expected return date is required for items that can be returned.', 422);
        }

        $issuedBy = $access['actor_name'] ?? Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';

        $studentEnrollmentID = null;
        if ($request->recipient_type === 'student') {
            $studentEnrollmentID = app(\App\Services\StudentEnrollmentService::class)
                ->bestEnrollmentID((int) $request->recipient_id, $schoolID);
        }

        $planResult = $this->planValidatedTakeIssues($issues, $units);
        if (!$planResult['ok']) {
            return $this->jsonError($planResult['error'], 422);
        }
        $plannedIssues = $planResult['planned'];

        DB::beginTransaction();
        try {
            $created = [];
            foreach ($plannedIssues as $planned) {
                $issue = $planned['issue'];
                $unit = $planned['unit'];
                $option = $planned['option'];
                $qtyOfLevel = $planned['qty_of_level'];
                $itemsTaken = $planned['items_taken'];

                $takeDisplay = $option['level'] === 'item'
                    ? $this->formatQty($itemsTaken) . ' ' . ($unit->unit->unit_name ?? 'item')
                    : $this->formatQty($qtyOfLevel) . ' × ' . $option['label']
                        . ' (' . $this->formatQty($itemsTaken) . ' ' . ($unit->unit->unit_name ?? 'item') . ')';

                $requisition = StoreRequisition::create([
                    'schoolID' => $schoolID,
                    'stock_unitID' => $unit->stock_unitID,
                    'itemID' => $unit->itemID,
                    'label' => $unit->label,
                    'store_method' => $unit->store_method,
                    'packageID' => $unit->packageID,
                    'unitID' => $unit->unitID,
                    'package_unit_display' => $this->buildPackageUnitDisplay($unit),
                    'take_level' => $option['level'],
                    'take_packageID' => $option['packageID'] ?? null,
                    'take_display' => $takeDisplay,
                    'quantity_taken' => $itemsTaken,
                    'recipient_type' => $request->recipient_type,
                    'recipient_id' => (int) $request->recipient_id,
                    'enrollmentID' => $studentEnrollmentID,
                    'recipient_name' => $recipientName,
                    'requisition_status' => $unit->requisition_status,
                    'expected_return_date' => $unit->requisition_status === 'can_return'
                        ? $request->expected_return_date
                        : null,
                    'status' => 'issued',
                    'issued_by' => $issuedBy,
                    'notes' => $request->notes,
                    'issued_at' => now(),
                ]);

                $unit->remaining_items = max(0, (float) $unit->remaining_items - $itemsTaken);
                $unit->stock_status = $unit->remaining_items > 0 ? 'in_stock' : 'requisitioned';
                $unit->save();
                $created[] = $requisition;
            }

            DB::commit();

            try {
                app(StoreNotificationService::class)->notifyItemIssued(
                    $schoolID,
                    $request->recipient_type,
                    (int) $request->recipient_id,
                    $recipientName,
                    $created
                );
            } catch (\Throwable $e) {
                \Log::error('Store issue SMS failed: ' . $e->getMessage());
            }

            $count = count($created);
            $className = null;
            if ($request->recipient_type === 'student') {
                $student = Student::where('studentID', (int) $request->recipient_id)
                    ->where('schoolID', $schoolID)
                    ->with(['subclass.class'])
                    ->first();
                $className = $student?->subclass?->display_name;
            }

            $requisitionsUi = collect($created)->map(function ($req) use ($className) {
                $req->load(['item', 'package', 'unit']);
                return $this->formatRequisitionUi($req, $className);
            })->values()->all();

            $affectedStockEntryIds = $units->pluck('stock_entryID')->unique()->filter()->values()->all();
            $stockEntriesUi = StoreItemStockEntry::where('schoolID', $schoolID)
                ->whereIn('stock_entryID', $affectedStockEntryIds)
                ->with(['item', 'package', 'unit', 'stockUnits'])
                ->get()
                ->map(fn ($e) => $this->formatStockEntryUi($e))
                ->values()
                ->all();

            $updatedUnits = StoreStockUnit::whereIn('stock_unitID', $stockUnitIDs)
                ->with(['item.category', 'item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
                ->get();

            $availableUnitsUi = $updatedUnits
                ->filter(fn ($u) => $u->remaining_items > 0)
                ->map(fn ($u) => $this->formatAvailableStockUnitUi($u))
                ->values()
                ->all();

            $depletedUnitIds = $updatedUnits->filter(fn ($u) => $u->remaining_items <= 0)
                ->pluck('stock_unitID')->map(fn ($id) => (int) $id)->values()->all();

            return response()->json([
                'success' => true,
                'message' => $count . ' issue(s) recorded for ' . $recipientName . ' successfully.',
                'requisitions' => $requisitionsUi,
                'stock_entries' => $stockEntriesUi,
                'available_stock_units' => $availableUnitsUi,
                'depleted_stock_unitIDs' => $depletedUnitIds,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to issue item(s): ' . $e->getMessage(), 500);
        }
    }

    public function returnRequisition(Request $request, $requisitionID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'returned_at' => 'required|date',
            'return_description' => 'nullable|string|max:2000',
            'return_condition' => 'required|in:good,damaged,lost',
            'bad_description' => 'nullable|string|max:2000',
        ]);

        if (in_array($request->return_condition, ['damaged', 'lost'], true)
            && !trim((string) $request->bad_description)) {
            return $this->jsonError('Please enter a reason for this return status.', 422);
        }

        $requisition = StoreRequisition::where('requisitionID', $requisitionID)
            ->where('schoolID', $schoolID)
            ->where('status', 'issued')
            ->first();

        if (!$requisition) {
            return $this->jsonError('Requisition not found.', 404);
        }

        if ($requisition->requisition_status !== 'can_return') {
            return $this->jsonError('This item cannot be returned.', 422);
        }

        $stockUnit = StoreStockUnit::where('stock_unitID', $requisition->stock_unitID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$stockUnit) {
            return $this->jsonError('Stock unit not found.', 404);
        }

        $returnedAt = Carbon::parse($request->returned_at)->endOfDay();
        $recordedBy = Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';

        DB::beginTransaction();
        try {
            $qtyTaken = (float) ($requisition->quantity_taken ?: 1);
            $condition = $request->return_condition;
            $incidentReason = trim((string) $request->bad_description);

            $requisition->status = 'returned';
            $requisition->returned_at = $returnedAt;
            $requisition->quantity_returned = $qtyTaken;
            $requisition->return_description = $request->return_description;
            $requisition->return_condition = $condition;
            $requisition->bad_description = in_array($condition, ['damaged', 'lost'], true)
                ? $incidentReason
                : null;
            $requisition->save();

            $createdIncident = null;

            if ($condition === 'good') {
                $stockUnit->remaining_items = min(
                    (float) $stockUnit->initial_items,
                    (float) $stockUnit->remaining_items + $qtyTaken
                );
                $stockUnit->stock_status = 'in_stock';
            } else {
                $createdIncident = $this->createIncidentFromRequisitionReturn(
                    $requisition,
                    $condition,
                    $incidentReason,
                    $request->return_description,
                    $schoolID,
                    $recordedBy
                );
                $stockUnit->stock_status = $stockUnit->remaining_items > 0
                    ? 'in_stock'
                    : ($condition === 'damaged' ? 'defective' : 'requisitioned');
            }
            $stockUnit->save();

            DB::commit();

            try {
                app(StoreNotificationService::class)->notifyItemReturned($schoolID, $requisition->fresh(['item']), $condition);
            } catch (\Throwable $e) {
                \Log::error('Store return SMS failed: ' . $e->getMessage());
            }

            $message = match ($condition) {
                'good' => 'Returned ' . $this->formatQty($qtyTaken) . ' item(s) to stock successfully.',
                'damaged' => 'Item returned as damaged. Record added to Damaged Items for ' . $requisition->recipient_name . '.',
                'lost' => 'Item returned as lost. Record added to Lost Items for ' . $requisition->recipient_name . '.',
                default => 'Return recorded successfully.',
            };

            $className = null;
            if ($requisition->recipient_type === 'student') {
                $student = Student::where('studentID', $requisition->recipient_id)
                    ->where('schoolID', $schoolID)
                    ->with(['subclass.class'])
                    ->first();
                $className = $student?->subclass?->display_name;
            }

            $requisition->load(['item', 'package', 'unit']);
            $stockEntry = StoreItemStockEntry::where('stock_entryID', $stockUnit->stock_entryID)
                ->with(['item', 'package', 'unit', 'stockUnits'])
                ->first();

            $returnedUnitUi = null;
            if ($condition === 'good' && $stockUnit->remaining_items > 0) {
                $returnedUnitUi = $this->formatAvailableStockUnitUi($stockUnit);
            }

            $incidentUi = null;
            if ($createdIncident) {
                $incidentUi = $this->formatIncidentUi($createdIncident, $className);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'requisition' => $this->formatRequisitionUi($requisition, $className),
                'incident' => $incidentUi,
                'stock_entry' => $stockEntry ? $this->formatStockEntryUi($stockEntry) : null,
                'returned_stock_unit' => $returnedUnitUi,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to return item.', 500);
        }
    }

    public function allInventoryReport(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'return_filter' => 'nullable|in:all,can_return,cant_return',
            'as_of_date' => 'nullable|date',
            'categoryID' => 'nullable|integer',
        ]);

        $returnFilter = $request->input('return_filter', 'all');
        $categoryID = $request->filled('categoryID') ? (int) $request->categoryID : null;
        $categoryName = null;
        if ($categoryID) {
            $categoryName = StoreItemCategory::where('schoolID', $schoolID)
                ->where('categoryID', $categoryID)
                ->where('status', 'Active')
                ->value('category_name');
        }
        $itemIds = $this->categoryItemIds($schoolID, $categoryID);
        $asOf = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)->endOfDay()
            : now()->endOfDay();

        $useLiveStatus = $asOf->toDateString() === now()->toDateString();

        $unitsQuery = StoreStockUnit::where('schoolID', $schoolID)
            ->with(['item', 'package', 'unit', 'stockEntry']);

        if ($returnFilter !== 'all') {
            $unitsQuery->where('requisition_status', $returnFilter);
        }

        if ($categoryID) {
            $unitsQuery->whereIn('itemID', $itemIds ?: [0]);
        }

        $allUnits = $unitsQuery->get();

        $inStockUnits = $allUnits->filter(function ($unit) use ($useLiveStatus, $asOf) {
            if ($useLiveStatus) {
                return $unit->stock_status === 'in_stock';
            }

            return $this->wasUnitInStockOnDate($unit, $asOf);
        })->values();

        $monthStart = $asOf->copy()->startOfMonth()->startOfDay();
        $monthEnd = $asOf->copy()->endOfMonth()->endOfDay();
        $issuedEnd = $asOf->lt($monthEnd) ? $asOf : $monthEnd;

        $issuedQuery = StoreRequisition::where('schoolID', $schoolID)
            ->whereBetween('issued_at', [$monthStart, $issuedEnd]);

        if ($returnFilter !== 'all') {
            $issuedQuery->where('requisition_status', $returnFilter);
        }

        if ($categoryID) {
            $issuedQuery->whereIn('itemID', $itemIds ?: [0]);
        }

        $issuedThisMonth = $issuedQuery->count();
        $availableCount = $inStockUnits->count();

        $items = [];
        foreach ($inStockUnits->groupBy('itemID') as $itemID => $itemUnits) {
            $itemName = $itemUnits->first()->item->item_name ?? '—';
            $rows = [];

            foreach ($itemUnits->where('store_method', 'unit')->groupBy('unitID') as $group) {
                $rows[] = [
                    'row_type' => 'unit',
                    'unit_name' => $group->first()->unit->unit_name ?? '—',
                    'quantity' => $group->count(),
                    'package_display' => null,
                    'package_quantity' => null,
                ];
            }

            foreach ($itemUnits->where('store_method', 'package')->groupBy(function ($u) {
                return $u->packageID . '_' . $u->unitID . '_' . ($u->stockEntry->content_per_unit ?? '0');
            }) as $group) {
                $sample = $group->first();
                $rows[] = [
                    'row_type' => 'package',
                    'unit_name' => null,
                    'quantity' => null,
                    'package_display' => $this->buildPackageUnitDisplay($sample),
                    'package_quantity' => $group->count(),
                ];
            }

            usort($rows, function ($a, $b) {
                $aKey = $a['row_type'] === 'unit' ? '0_' . $a['unit_name'] : '1_' . $a['package_display'];
                $bKey = $b['row_type'] === 'unit' ? '0_' . $b['unit_name'] : '1_' . $b['package_display'];
                return strcasecmp($aKey, $bKey);
            });

            if (!empty($rows)) {
                $items[] = [
                    'itemID' => (int) $itemID,
                    'item_name' => $itemName,
                    'rowspan' => count($rows),
                    'rows' => $rows,
                ];
            }
        }

        usort($items, fn ($a, $b) => strcasecmp($a['item_name'], $b['item_name']));

        return response()->json([
            'success' => true,
            'summary' => [
                'total_in_stock' => $availableCount,
                'issued_this_month' => $issuedThisMonth,
                'available_in_stock' => $availableCount,
                'as_of_date' => $asOf->toDateString(),
                'as_of_label' => $asOf->format('d M Y'),
                'month_label' => $asOf->format('F Y'),
                'categoryID' => $categoryID,
                'category_name' => $categoryName,
                'filter_label' => $categoryName ? $categoryName : 'All Categories',
            ],
            'items' => $items,
        ]);
    }

    private function wasUnitInStockOnDate(StoreStockUnit $unit, Carbon $asOf): bool
    {
        if ($unit->created_at && $unit->created_at->gt($asOf)) {
            return false;
        }

        $requisitions = StoreRequisition::where('stock_unitID', $unit->stock_unitID)
            ->where('issued_at', '<=', $asOf)
            ->orderBy('issued_at')
            ->get();

        if ($requisitions->isEmpty()) {
            return true;
        }

        $inStock = true;
        foreach ($requisitions as $req) {
            $inStock = false;
            if ($req->status === 'returned' && $req->returned_at && $req->returned_at->lte($asOf)) {
                $inStock = true;
            }
        }

        return $inStock;
    }

    private function parseReportDateRange(Request $request): array
    {
        $request->validate([
            'period_type' => 'nullable|in:current_month,month,year,range',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $periodType = $request->input('period_type', 'current_month');
        $now = now();

        if ($periodType === 'month') {
            $month = (int) ($request->input('month') ?: $now->month);
            $year = (int) ($request->input('year') ?: $now->year);
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->endOfDay();
            $label = $start->format('F Y');
        } elseif ($periodType === 'year') {
            $year = (int) ($request->input('year') ?: $now->year);
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
            $label = (string) $year;
        } elseif ($periodType === 'range') {
            if (!$request->filled('date_from') || !$request->filled('date_to')) {
                throw new \InvalidArgumentException('Both start and end dates are required for a date range.');
            }
            $start = Carbon::parse($request->date_from)->startOfDay();
            $end = Carbon::parse($request->date_to)->endOfDay();
            $label = $start->format('d M Y') . ' — ' . $end->format('d M Y');
        } else {
            $start = $now->copy()->startOfMonth()->startOfDay();
            $end = $now->copy()->endOfMonth()->endOfDay();
            $label = $now->format('F Y');
            $periodType = 'current_month';
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
            'period_type' => $periodType,
        ];
    }

    private function studentClassMapForIds(int $schoolID, array $studentIds): array
    {
        if (empty($studentIds)) {
            return [];
        }

        return Student::where('schoolID', $schoolID)
            ->whereIn('studentID', $studentIds)
            ->with(['subclass.class'])
            ->get(['studentID', 'subclassID'])
            ->mapWithKeys(fn ($st) => [
                $st->studentID => $st->subclass?->display_name ?: null,
            ])
            ->all();
    }

    private function matchesRequisitionReturnStatusFilter(StoreRequisition $req, string $filter): bool
    {
        if ($filter === 'all') {
            return true;
        }

        if ($req->requisition_status !== 'can_return') {
            return $filter === 'not_applicable';
        }

        $condition = $req->return_condition === 'bad' ? 'damaged' : $req->return_condition;

        return match ($filter) {
            'not_returned' => $req->status === 'issued',
            'overdue' => $req->status === 'issued'
                && $req->expected_return_date
                && $req->expected_return_date->isPast(),
            'returned' => $req->status === 'returned',
            'returned_good' => $req->status === 'returned' && ($condition === 'good' || !$condition),
            'returned_damaged' => $req->status === 'returned' && $condition === 'damaged',
            'returned_lost' => $req->status === 'returned' && $condition === 'lost',
            default => true,
        };
    }

    public function requisitionReport(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'categoryID' => 'nullable|integer',
            'return_filter' => 'nullable|in:all,can_return,cant_return',
            'return_status_filter' => 'nullable|in:all,not_returned,overdue,returned,returned_good,returned_damaged,returned_lost',
        ]);

        try {
            $period = $this->parseReportDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        $categoryID = $request->filled('categoryID') ? (int) $request->categoryID : null;
        $categoryName = null;
        if ($categoryID) {
            $categoryName = StoreItemCategory::where('schoolID', $schoolID)
                ->where('categoryID', $categoryID)
                ->where('status', 'Active')
                ->value('category_name');
        }

        $itemIds = $this->categoryItemIds($schoolID, $categoryID);
        $returnFilter = $request->input('return_filter', 'all');
        $returnStatusFilter = $request->input('return_status_filter', 'all');

        $query = StoreRequisition::where('schoolID', $schoolID)
            ->whereBetween('issued_at', [$period['start'], $period['end']])
            ->with(['item.category', 'package', 'unit']);

        if ($categoryID) {
            $query->whereIn('itemID', $itemIds ?: [0]);
        }

        if ($returnFilter !== 'all') {
            $query->where('requisition_status', $returnFilter);
        }

        $requisitions = $query->orderByDesc('issued_at')->get();

        if ($returnStatusFilter !== 'all' && $returnFilter !== 'cant_return') {
            $requisitions = $requisitions->filter(
                fn ($req) => $this->matchesRequisitionReturnStatusFilter($req, $returnStatusFilter)
            )->values();
        }

        $studentIds = $requisitions->where('recipient_type', 'student')
            ->pluck('recipient_id')->unique()->filter()->all();
        $studentClassMap = $this->studentClassMapForIds($schoolID, $studentIds);

        $rows = $requisitions->map(function ($req) use ($studentClassMap) {
            $className = $req->recipient_type === 'student'
                ? ($studentClassMap[$req->recipient_id] ?? null)
                : null;
            $ui = $this->formatRequisitionUi($req, $className);
            $ui['category_name'] = $req->item->category->category_name ?? '—';
            return $ui;
        })->values()->all();

        $canReturn = $requisitions->where('requisition_status', 'can_return');
        $summary = [
            'total_issues' => $requisitions->count(),
            'can_return_issues' => $canReturn->count(),
            'cant_return_issues' => $requisitions->where('requisition_status', 'cant_return')->count(),
            'not_returned' => $canReturn->where('status', 'issued')->count(),
            'overdue' => $canReturn->filter(fn ($r) =>
                $r->status === 'issued'
                && $r->expected_return_date
                && $r->expected_return_date->isPast()
            )->count(),
            'returned_good' => $canReturn->filter(fn ($r) =>
                $r->status === 'returned'
                && in_array($r->return_condition, [null, 'good'], true)
            )->count(),
            'returned_damaged' => $canReturn->filter(fn ($r) =>
                $r->status === 'returned'
                && in_array($r->return_condition, ['bad', 'damaged'], true)
            )->count(),
            'returned_lost' => $canReturn->where('status', 'returned')->where('return_condition', 'lost')->count(),
            'unique_recipients' => $requisitions->pluck('recipient_name')->unique()->count(),
            'period_label' => $period['label'],
            'period_type' => $period['period_type'],
            'categoryID' => $categoryID,
            'category_name' => $categoryName,
            'filter_label' => $categoryName ?: 'All Categories',
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    public function specificItemReport(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'itemID' => 'required|integer',
            'categoryID' => 'nullable|integer',
        ]);

        try {
            $period = $this->parseReportDateRange($request);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        $item = StoreItem::where('schoolID', $schoolID)
            ->where('itemID', $request->itemID)
            ->where('status', 'Active')
            ->with(['category', 'measurementType'])
            ->first();

        if (!$item) {
            return $this->jsonError('Item not found.', 404);
        }

        if ($request->filled('categoryID') && (int) $item->categoryID !== (int) $request->categoryID) {
            return $this->jsonError('Selected item does not belong to the chosen category.', 422);
        }

        $stockUnits = StoreStockUnit::where('schoolID', $schoolID)
            ->where('itemID', $item->itemID)
            ->with(['package', 'unit'])
            ->get();

        $inStock = $stockUnits->filter(fn ($u) => $u->stock_status === 'in_stock' && (float) $u->remaining_items > 0);
        $defective = $stockUnits->where('stock_status', 'defective')->count();
        $depleted = $stockUnits->filter(fn ($u) =>
            $u->stock_status === 'requisitioned' || (float) $u->remaining_items <= 0
        )->count();

        $requisitions = StoreRequisition::where('schoolID', $schoolID)
            ->where('itemID', $item->itemID)
            ->whereBetween('issued_at', [$period['start'], $period['end']])
            ->with(['package', 'unit'])
            ->orderByDesc('issued_at')
            ->get();

        $studentIds = $requisitions->where('recipient_type', 'student')
            ->pluck('recipient_id')->unique()->filter()->all();
        $studentClassMap = $this->studentClassMapForIds($schoolID, $studentIds);

        $reqRows = $requisitions->map(function ($req) use ($studentClassMap) {
            $className = $req->recipient_type === 'student'
                ? ($studentClassMap[$req->recipient_id] ?? null)
                : null;
            return $this->formatRequisitionUi($req, $className);
        })->values()->all();

        $incidents = StoreItemIncident::where('schoolID', $schoolID)
            ->where('itemID', $item->itemID)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->orderByDesc('created_at')
            ->get();

        $incidentStudentIds = $incidents->where('responsible_type', 'student')
            ->pluck('responsible_id')->unique()->filter()->all();
        $incidentClassMap = $this->studentClassMapForIds($schoolID, $incidentStudentIds);

        $incidentRows = $incidents->map(function ($inc) use ($incidentClassMap) {
            $className = $inc->responsible_type === 'student'
                ? ($incidentClassMap[$inc->responsible_id] ?? null)
                : null;
            return $this->formatIncidentUi($inc, $className);
        })->values()->all();

        $stockBreakdown = [];
        foreach ($inStock->groupBy(fn ($u) => $u->store_method === 'package'
            ? 'pkg_' . $this->buildPackageUnitDisplay($u)
            : 'unit_' . ($u->unit->unit_name ?? 'item')) as $group) {
            $sample = $group->first();
            $stockBreakdown[] = [
                'display' => $sample->store_method === 'package'
                    ? $this->buildPackageUnitDisplay($sample)
                    : ($sample->unit->unit_name ?? 'item'),
                'labels' => $group->count(),
                'remaining_items' => $this->formatQty($group->sum('remaining_items')),
            ];
        }

        return response()->json([
            'success' => true,
            'item' => [
                'itemID' => (int) $item->itemID,
                'item_name' => $item->item_name,
                'category_name' => $item->category->category_name ?? '—',
                'measurement_type' => $item->measurementType->type_name ?? '—',
            ],
            'summary' => [
                'labels_in_stock' => $inStock->count(),
                'total_remaining_items' => $this->formatQty($inStock->sum('remaining_items')),
                'labels_defective' => $defective,
                'labels_depleted' => $depleted,
                'issues_in_period' => $requisitions->count(),
                'returned_in_period' => $requisitions->where('status', 'returned')->count(),
                'active_issues' => $requisitions->where('status', 'issued')->count(),
                'damaged_incidents' => $incidents->where('incident_type', 'damaged')->count(),
                'lost_incidents' => $incidents->where('incident_type', 'lost')->count(),
                'period_label' => $period['label'],
                'period_type' => $period['period_type'],
            ],
            'stock_breakdown' => $stockBreakdown,
            'requisitions' => $reqRows,
            'incidents' => $incidentRows,
        ]);
    }

    public function storeIncident(Request $request)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'incident_type' => 'required|in:damaged,lost',
            'responsible_type' => 'required|in:teacher,staff,student,other',
            'responsible_id' => 'nullable|integer',
            'responsible_name' => 'nullable|string|max:255',
            'reason' => 'required|string|max:2000',
            'issues' => 'required|array|min:1',
            'issues.*.stock_unitID' => 'required|integer',
            'issues.*.take_key' => 'required|string',
            'issues.*.quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->responsible_type !== 'other' && !$request->filled('responsible_id')) {
            return $this->jsonError('Select the responsible person.', 422);
        }

        $responsible = $this->resolveIncidentResponsible($request, $schoolID);
        if (!$responsible) {
            return $this->jsonError('Selected responsible person not found.', 404);
        }

        $incidentType = $request->incident_type;
        $issues = collect($request->issues);
        $stockUnitIDs = $issues->pluck('stock_unitID')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $units = StoreStockUnit::where('schoolID', $schoolID)
            ->whereIn('stock_unitID', $stockUnitIDs)
            ->where('remaining_items', '>', 0)
            ->with(['item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
            ->get()
            ->keyBy('stock_unitID');

        if ($units->count() !== count($stockUnitIDs)) {
            return $this->jsonError('One or more selected units are no longer available in stock.', 422);
        }

        $recordedBy = Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';

        $planResult = $this->planValidatedTakeIssues($issues, $units);
        if (!$planResult['ok']) {
            return $this->jsonError($planResult['error'], 422);
        }
        $plannedIssues = $planResult['planned'];

        DB::beginTransaction();
        try {
            $created = [];
            foreach ($plannedIssues as $planned) {
                $issue = $planned['issue'];
                $unit = $planned['unit'];
                $option = $planned['option'];
                $qtyOfLevel = $planned['qty_of_level'];
                $itemsTaken = $planned['items_taken'];

                $takeDisplay = $option['level'] === 'item'
                    ? $this->formatQty($itemsTaken) . ' ' . ($unit->unit->unit_name ?? 'item')
                    : $this->formatQty($qtyOfLevel) . ' × ' . $option['label']
                        . ' (' . $this->formatQty($itemsTaken) . ' ' . ($unit->unit->unit_name ?? 'item') . ')';

                $incident = StoreItemIncident::create([
                    'schoolID' => $schoolID,
                    'incident_type' => $incidentType,
                    'stock_unitID' => $unit->stock_unitID,
                    'itemID' => $unit->itemID,
                    'label' => $unit->label,
                    'store_method' => $unit->store_method,
                    'packageID' => $unit->packageID,
                    'unitID' => $unit->unitID,
                    'package_unit_display' => $this->buildPackageUnitDisplay($unit),
                    'take_level' => $option['level'],
                    'take_packageID' => $option['packageID'] ?? null,
                    'take_display' => $takeDisplay,
                    'quantity_affected' => $itemsTaken,
                    'responsible_type' => $responsible['type'],
                    'responsible_id' => $responsible['id'],
                    'responsible_name' => $responsible['name'],
                    'reason' => $request->reason,
                    'payment_status' => 'pending',
                    'resolve_status' => 'open',
                    'recorded_by' => $recordedBy,
                    'notes' => $request->notes,
                ]);

                $this->deductStockForIncident($unit, $itemsTaken, $incidentType);
                $created[] = $incident;
            }

            DB::commit();

            try {
                app(StoreNotificationService::class)->notifyIncidentsRecorded($schoolID, $created, $responsible);
            } catch (\Throwable $e) {
                \Log::error('Store incident SMS failed: ' . $e->getMessage());
            }

            $className = null;
            if ($responsible['type'] === 'student' && $responsible['id']) {
                $student = Student::where('studentID', $responsible['id'])
                    ->where('schoolID', $schoolID)
                    ->with(['subclass.class'])
                    ->first();
                $className = $student?->subclass?->display_name;
            }

            $incidentsUi = collect($created)->map(fn ($inc) => $this->formatIncidentUi($inc, $className))->values()->all();

            $affectedStockEntryIds = $units->pluck('stock_entryID')->unique()->filter()->values()->all();
            $stockEntriesUi = StoreItemStockEntry::where('schoolID', $schoolID)
                ->whereIn('stock_entryID', $affectedStockEntryIds)
                ->with(['item', 'package', 'unit', 'stockUnits'])
                ->get()
                ->map(fn ($e) => $this->formatStockEntryUi($e))
                ->values()
                ->all();

            $updatedUnits = StoreStockUnit::whereIn('stock_unitID', $stockUnitIDs)
                ->with(['item.category', 'item.packageSpecs.package', 'item.packageSpecs.unit', 'package', 'unit', 'stockEntry'])
                ->get();

            $availableUnitsUi = $updatedUnits
                ->filter(fn ($u) => $u->remaining_items > 0)
                ->map(fn ($u) => $this->formatAvailableStockUnitUi($u))
                ->values()
                ->all();

            $depletedUnitIds = $updatedUnits->filter(fn ($u) => $u->remaining_items <= 0)
                ->pluck('stock_unitID')->map(fn ($id) => (int) $id)->values()->all();

            $typeLabel = $incidentType === 'damaged' ? 'damaged' : 'lost';

            return response()->json([
                'success' => true,
                'message' => count($created) . ' ' . $typeLabel . ' record(s) saved for ' . $responsible['name'] . '.',
                'incidents' => $incidentsUi,
                'stock_entries' => $stockEntriesUi,
                'available_stock_units' => $availableUnitsUi,
                'depleted_stock_unitIDs' => $depletedUnitIds,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to record incident: ' . $e->getMessage(), 500);
        }
    }

    public function updateIncident(Request $request, $incidentID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'responsible_type' => 'required|in:teacher,staff,student,other',
            'responsible_id' => 'nullable|integer',
            'responsible_name' => 'nullable|string|max:255',
            'reason' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $incident = StoreItemIncident::where('incidentID', $incidentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$incident) {
            return $this->jsonError('Record not found.', 404);
        }

        if ($incident->resolve_status !== 'open') {
            return $this->jsonError('Only open records can be edited.', 422);
        }

        if ($request->responsible_type !== 'other' && !$request->filled('responsible_id')) {
            return $this->jsonError('Select the responsible person.', 422);
        }

        $responsible = $this->resolveIncidentResponsible($request, $schoolID);
        if (!$responsible) {
            return $this->jsonError('Selected responsible person not found.', 404);
        }

        $incident->responsible_type = $responsible['type'];
        $incident->responsible_id = $responsible['id'];
        $incident->responsible_name = $responsible['name'];
        $incident->reason = $request->reason;
        $incident->notes = $request->notes;
        $incident->save();

        $className = null;
        if ($responsible['type'] === 'student' && $responsible['id']) {
            $student = Student::where('studentID', $responsible['id'])
                ->where('schoolID', $schoolID)
                ->with(['subclass.class'])
                ->first();
            $className = $student?->subclass?->display_name;
        }

        return response()->json([
            'success' => true,
            'message' => 'Record updated successfully.',
            'incident' => $this->formatIncidentUi($incident, $className),
        ]);
    }

    public function deleteIncident($incidentID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $incident = StoreItemIncident::where('incidentID', $incidentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$incident) {
            return $this->jsonError('Record not found.', 404);
        }

        if ($incident->resolve_status !== 'open') {
            return $this->jsonError('Only open records can be deleted.', 422);
        }

        $unit = StoreStockUnit::where('stock_unitID', $incident->stock_unitID)
            ->where('schoolID', $schoolID)
            ->first();

        DB::beginTransaction();
        try {
            if ($unit && !$incident->requisitionID) {
                $this->restoreStockFromIncident($incident, $unit);
            }
            $stockEntryId = $unit?->stock_entryID;
            $incident->delete();
            DB::commit();

            $stockEntryUi = null;
            if ($stockEntryId) {
                $entry = StoreItemStockEntry::where('stock_entryID', $stockEntryId)
                    ->where('schoolID', $schoolID)
                    ->with(['item', 'package', 'unit', 'stockUnits'])
                    ->first();
                if ($entry) {
                    $stockEntryUi = $this->formatStockEntryUi($entry);
                }
            }

            $availableUnitUi = null;
            if ($unit && $unit->remaining_items > 0) {
                $availableUnitUi = $this->formatAvailableStockUnitUi($unit);
            }

            return response()->json([
                'success' => true,
                'message' => $incident->requisitionID
                    ? 'Record deleted.'
                    : 'Record deleted and stock restored.',
                'incidentID' => (int) $incidentID,
                'stock_entry' => $stockEntryUi,
                'available_stock_unit' => $availableUnitUi,
                'restored_stock_unitID' => $unit ? (int) $unit->stock_unitID : null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to delete record: ' . $e->getMessage(), 500);
        }
    }

    public function resolveIncident(Request $request, $incidentID)
    {
        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return $this->jsonError('Unauthorized');
        }

        $request->validate([
            'resolve_method' => 'required|in:cash,replace_new',
            'payment_amount' => 'required_if:resolve_method,cash|nullable|numeric|min:0',
        ]);

        $incident = StoreItemIncident::where('incidentID', $incidentID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$incident) {
            return $this->jsonError('Record not found.', 404);
        }

        if ($incident->resolve_status !== 'open') {
            return $this->jsonError('This record is already resolved.', 422);
        }

        $resolvedBy = Session::get('admin_name') ?: Session::get('name') ?: 'Administrator';

        DB::beginTransaction();
        try {
            $stockEntryUi = null;
            $availableUnitUi = null;

            if ($request->resolve_method === 'replace_new') {
                $unit = StoreStockUnit::where('stock_unitID', $incident->stock_unitID)
                    ->where('schoolID', $schoolID)
                    ->first();
                if ($unit) {
                    if ($incident->requisitionID) {
                        $qty = (float) $incident->quantity_affected;
                        $unit->remaining_items = min(
                            (float) $unit->initial_items,
                            (float) $unit->remaining_items + $qty
                        );
                        $unit->stock_status = 'in_stock';
                        $unit->save();
                    } else {
                        $this->restoreStockFromIncident($incident, $unit);
                    }
                    $entry = StoreItemStockEntry::where('stock_entryID', $unit->stock_entryID)
                        ->where('schoolID', $schoolID)
                        ->with(['item', 'package', 'unit', 'stockUnits'])
                        ->first();
                    if ($entry) {
                        $stockEntryUi = $this->formatStockEntryUi($entry);
                    }
                    $availableUnitUi = $this->formatAvailableStockUnitUi($unit);
                }
            }

            $incident->resolve_status = 'resolved';
            $incident->resolve_method = $request->resolve_method;
            $incident->resolved_at = now();
            $incident->resolved_by = $resolvedBy;

            if ($request->resolve_method === 'cash') {
                $incident->payment_status = 'paid';
                $incident->payment_amount = (float) $request->payment_amount;
            } else {
                $incident->payment_status = 'pending';
                $incident->payment_amount = null;
            }

            $incident->save();
            DB::commit();

            $className = null;
            if ($incident->responsible_type === 'student' && $incident->responsible_id) {
                $student = Student::where('studentID', $incident->responsible_id)
                    ->where('schoolID', $schoolID)
                    ->with(['subclass.class'])
                    ->first();
                $className = $student?->subclass?->display_name;
            }

            return response()->json([
                'success' => true,
                'message' => $request->resolve_method === 'cash'
                    ? 'Record resolved with cash payment.'
                    : 'Record resolved — stock replaced.',
                'incident' => $this->formatIncidentUi($incident, $className),
                'stock_entry' => $stockEntryUi,
                'available_stock_unit' => $availableUnitUi,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonError('Failed to resolve record: ' . $e->getMessage(), 500);
        }
    }

    public function exportStorePdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $schoolID = $this->authorizeSchool();
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Unauthorized access');
        }

        $access = $this->getStoreAccess();
        $accessService = app(StoreAccessService::class);
        $allowedCategoryIds = $access ? $accessService->allowedCategoryIds($access) : null;

        $section = $request->get('section', 'dashboard');
        $validSections = [
            'dashboard', 'items', 'stock', 'requisitions', 'damaged', 'lost',
            'categories', 'units', 'packages', 'report',
        ];

        if (!in_array($section, $validSections, true)) {
            return redirect()->route('manage_store')->with('error', 'Invalid store section for PDF export.');
        }

        $school = School::find($schoolID);
        $categoryID = $request->filled('categoryID') ? (int) $request->categoryID : null;
        if ($categoryID && $allowedCategoryIds !== null && !in_array($categoryID, $allowedCategoryIds, true)) {
            return redirect()->route('manage_store')->with('error', 'Category not allowed for your access.');
        }
        $categoryName = null;
        if ($categoryID) {
            $categoryName = StoreItemCategory::where('schoolID', $schoolID)
                ->where('categoryID', $categoryID)
                ->where('status', 'Active')
                ->value('category_name');
        }

        $filterLabel = $categoryName ?: (
            $allowedCategoryIds !== null
                ? (count($allowedCategoryIds) === 1
                    ? (StoreItemCategory::where('schoolID', $schoolID)->where('categoryID', $allowedCategoryIds[0])->value('category_name') ?: 'My Category')
                    : 'All My Categories')
                : 'All Categories'
        );
        $sectionLabels = [
            'dashboard' => 'Store Dashboard',
            'items' => 'Registered Items',
            'stock' => 'Item Stock',
            'requisitions' => 'Requisitions',
            'damaged' => 'Damaged Items',
            'lost' => 'Lost Items',
            'categories' => 'Item Categories',
            'units' => 'Measurement Types & Units',
            'packages' => 'Packages',
            'report' => 'Store Report',
        ];

        $data = [
            'school' => $school,
            'section' => $section,
            'section_label' => $sectionLabels[$section] ?? ucfirst($section),
            'title' => 'Store — ' . ($sectionLabels[$section] ?? ucfirst($section)),
            'generated_at' => now()->format('d M Y, H:i'),
            'filter_label' => $filterLabel,
            'period_label' => null,
            'summary_cards' => [],
            'table_headers' => [],
            'table_rows' => [],
            'dashboard' => null,
            'report_type' => null,
            'report_items' => [],
            'report_item' => null,
            'report_rows' => [],
            'incident_rows' => [],
            'stock_breakdown' => [],
        ];

        switch ($section) {
            case 'dashboard':
                $dashboard = $this->buildStoreDashboardStats($schoolID, $categoryID, $allowedCategoryIds);
                $data['dashboard'] = $dashboard;
                $data['summary_cards'] = [
                    ['label' => 'Registered Items', 'value' => $dashboard['summary']['registered_items'] ?? 0],
                    ['label' => 'Labels In Stock', 'value' => $dashboard['summary']['labels_in_stock'] ?? 0],
                    ['label' => 'Issued This Month', 'value' => $dashboard['summary']['issued_this_month'] ?? 0],
                    ['label' => 'Overdue Returns', 'value' => $dashboard['summary']['overdue_returns'] ?? 0],
                ];
                break;

            case 'items':
                $itemsQuery = StoreItem::where('schoolID', $schoolID)->where('status', 'Active')
                    ->with(['category', 'measurementType']);
                if ($categoryID) {
                    $itemsQuery->where('categoryID', $categoryID);
                }
                $items = $itemsQuery->orderBy('item_name')->get();
                $data['table_headers'] = ['#', 'Item Name', 'Category', 'Measurement Type'];
                $data['table_rows'] = $items->values()->map(fn ($item, $i) => [
                    (string) ($i + 1),
                    e($item->item_name),
                    e($item->category->category_name ?? '—'),
                    e($item->measurementType->type_name ?? '—'),
                ])->all();
                break;

            case 'stock':
                $stockQuery = StoreItemStockEntry::where('schoolID', $schoolID)->where('status', 'Active')
                    ->with(['item', 'package', 'unit', 'stockUnits']);
                if ($categoryID) {
                    $itemIds = $this->categoryItemIds($schoolID, $categoryID) ?: [0];
                    $stockQuery->whereIn('itemID', $itemIds);
                }
                $entries = $stockQuery->orderByDesc('created_at')->get();
                $data['table_headers'] = ['#', 'Item', 'Package / Unit', 'Available Labels', 'Requisition'];
                $data['table_rows'] = $entries->values()->map(function ($entry, $i) {
                    $available = $entry->stockUnits->filter(fn ($u) => (float) $u->remaining_items > 0)->count();
                    $reqStatus = $entry->stockUnits->first()?->requisition_status ?? '—';
                    $pkgUnit = $entry->store_method === 'package'
                        ? ($entry->package->package_name ?? 'Package')
                        : ($entry->unit->unit_name ?? 'Unit');
                    return [
                        (string) ($i + 1),
                        e($entry->item->item_name ?? '—'),
                        e($pkgUnit),
                        (string) $available,
                        e($reqStatus === 'can_return' ? 'Can Return' : "Can't Return"),
                    ];
                })->all();
                break;

            case 'requisitions':
                $reqQuery = StoreRequisition::where('schoolID', $schoolID)->with(['item']);
                if ($categoryID) {
                    $itemIds = $this->categoryItemIds($schoolID, $categoryID) ?: [0];
                    $reqQuery->whereIn('itemID', $itemIds);
                }
                if ($request->filled('return_filter') && $request->return_filter !== 'all') {
                    $reqQuery->where('requisition_status', $request->return_filter);
                }
                $reqs = $reqQuery->orderByDesc('issued_at')->get();
                $data['table_headers'] = ['#', 'Recipient', 'Item', 'Take', 'Issued', 'Return Status', 'Expected Return'];
                $data['table_rows'] = $reqs->values()->map(function ($req, $i) {
                    $returnStatus = '—';
                    if ($req->requisition_status === 'can_return') {
                        if ($req->status === 'returned') {
                            $cond = $req->return_condition === 'bad' ? 'damaged' : $req->return_condition;
                            $returnStatus = match ($cond) {
                                'damaged' => 'Returned — Damaged',
                                'lost' => 'Returned — Lost',
                                default => 'Returned — Good',
                            };
                        } else {
                            $overdue = $req->expected_return_date && $req->expected_return_date->isPast();
                            $returnStatus = $overdue ? 'Not Returned — Overdue' : 'Not Returned — Pending';
                        }
                    } else {
                        $returnStatus = "Can't Return";
                    }
                    return [
                        (string) ($i + 1),
                        e($req->recipient_name),
                        e($req->item->item_name ?? '—'),
                        e($req->take_display ?? '—'),
                        e($req->issued_at ? $req->issued_at->format('d M Y') : '—'),
                        e($returnStatus),
                        e($req->expected_return_date ? $req->expected_return_date->format('d M Y') : '—'),
                    ];
                })->all();
                break;

            case 'damaged':
            case 'lost':
                $incQuery = StoreItemIncident::where('schoolID', $schoolID)
                    ->where('incident_type', $section === 'damaged' ? 'damaged' : 'lost')
                    ->with(['item']);
                if ($categoryID) {
                    $itemIds = $this->categoryItemIds($schoolID, $categoryID) ?: [0];
                    $incQuery->whereIn('itemID', $itemIds);
                }
                $incidents = $incQuery->orderByDesc('created_at')->get();
                $data['table_headers'] = ['#', 'Item', 'Responsible', 'Take', 'Reason', 'Status', 'Recorded'];
                $data['table_rows'] = $incidents->values()->map(function ($inc, $i) {
                    $status = ucfirst($inc->resolve_status ?? 'open');
                    if ($inc->payment_status === 'paid') {
                        $status .= ' (Paid)';
                    }
                    return [
                        (string) ($i + 1),
                        e($inc->item->item_name ?? '—'),
                        e($inc->responsible_name ?? '—'),
                        e($inc->take_display ?? '—'),
                        e($inc->reason ?? '—'),
                        e($status),
                        e($inc->created_at ? $inc->created_at->format('d M Y') : '—'),
                    ];
                })->all();
                break;

            case 'categories':
                $cats = StoreItemCategory::where('schoolID', $schoolID)->where('status', 'Active')
                    ->orderBy('category_name')->get();
                $data['filter_label'] = null;
                $data['table_headers'] = ['#', 'Category Name'];
                $data['table_rows'] = $cats->values()->map(fn ($cat, $i) => [
                    (string) ($i + 1),
                    e($cat->category_name),
                ])->all();
                break;

            case 'units':
                $types = StoreMeasurementType::where('schoolID', $schoolID)->where('status', 'Active')
                    ->with(['units' => fn ($q) => $q->where('status', 'Active')->orderBy('unit_name')])
                    ->orderBy('type_name')->get();
                $data['filter_label'] = null;
                $data['table_headers'] = ['#', 'Type Name', 'Units'];
                $data['table_rows'] = $types->values()->map(function ($type, $i) {
                    $units = $type->units->pluck('unit_name')->implode(', ');
                    return [
                        (string) ($i + 1),
                        e($type->type_name),
                        e($units ?: '—'),
                    ];
                })->all();
                break;

            case 'packages':
                $pkgs = StorePackage::where('schoolID', $schoolID)->where('status', 'Active')
                    ->orderBy('package_name')->get();
                $data['filter_label'] = null;
                $data['table_headers'] = ['#', 'Package Name'];
                $data['table_rows'] = $pkgs->values()->map(fn ($pkg, $i) => [
                    (string) ($i + 1),
                    e($pkg->package_name),
                ])->all();
                break;

            case 'report':
                $reportType = $request->get('report_type', 'all_inventory');
                $data['report_type'] = $reportType;
                $data['title'] = 'Store Report — ' . match ($reportType) {
                    'requisition' => 'Requisition Report',
                    'specific_item' => 'Specific Item Report',
                    default => 'All Items Inventory',
                };

                if ($reportType === 'all_inventory') {
                    $fakeRequest = new Request([
                        'return_filter' => $request->get('return_filter', 'all'),
                        'as_of_date' => $request->get('as_of_date', now()->toDateString()),
                        'categoryID' => $categoryID,
                    ]);
                    $response = $this->allInventoryReport($fakeRequest);
                    $payload = json_decode($response->getContent(), true);
                    if ($payload['success'] ?? false) {
                        $summary = $payload['summary'] ?? [];
                        $data['period_label'] = 'As of ' . ($summary['as_of_label'] ?? now()->format('d M Y'));
                        $data['filter_label'] = ($summary['filter_label'] ?? $filterLabel)
                            . ' | Return: ' . ucfirst(str_replace('_', ' ', $request->get('return_filter', 'all')));
                        $data['summary_cards'] = [
                            ['label' => 'In Stock', 'value' => $summary['total_in_stock'] ?? 0],
                            ['label' => 'Issued This Month', 'value' => $summary['issued_this_month'] ?? 0],
                            ['label' => 'Available', 'value' => $summary['available_in_stock'] ?? 0],
                            ['label' => 'Month', 'value' => $summary['month_label'] ?? '—'],
                        ];
                        $data['report_items'] = $payload['items'] ?? [];
                    }
                } elseif ($reportType === 'requisition') {
                    try {
                        $period = $this->parseReportDateRange($request);
                    } catch (\InvalidArgumentException $e) {
                        return redirect()->route('manage_store')->with('error', $e->getMessage());
                    }
                    $fakeRequest = new Request(array_merge($request->all(), [
                        'categoryID' => $categoryID,
                    ]));
                    $response = $this->requisitionReport($fakeRequest);
                    $payload = json_decode($response->getContent(), true);
                    if ($payload['success'] ?? false) {
                        $summary = $payload['summary'] ?? [];
                        $data['period_label'] = $summary['period_label'] ?? $period['label'];
                        $data['filter_label'] = ($summary['filter_label'] ?? $filterLabel)
                            . ' | Issues: ' . ($summary['total_issues'] ?? 0);
                        $data['summary_cards'] = [
                            ['label' => 'Total Issues', 'value' => $summary['total_issues'] ?? 0],
                            ['label' => 'Not Returned', 'value' => $summary['not_returned'] ?? 0],
                            ['label' => 'Overdue', 'value' => $summary['overdue'] ?? 0],
                            ['label' => 'Recipients', 'value' => $summary['unique_recipients'] ?? 0],
                        ];
                        $data['table_headers'] = ['#', 'Recipient', 'Item', 'Category', 'Take', 'Issued', 'Return Status'];
                        $data['table_rows'] = collect($payload['rows'] ?? [])->values()->map(function ($row, $i) {
                            return [
                                (string) ($i + 1),
                                e($row['recipient_name'] ?? '—'),
                                e($row['item_name'] ?? '—'),
                                e($row['category_name'] ?? '—'),
                                e($row['take_display'] ?? '—'),
                                e($row['issued_at'] ?? '—'),
                                e($row['return_status_text'] ?? '—'),
                            ];
                        })->all();
                    }
                } else {
                    if (!$request->filled('itemID')) {
                        return redirect()->route('manage_store')->with('error', 'Select an item for the specific item report PDF.');
                    }
                    try {
                        $period = $this->parseReportDateRange($request);
                    } catch (\InvalidArgumentException $e) {
                        return redirect()->route('manage_store')->with('error', $e->getMessage());
                    }
                    $fakeRequest = new Request(array_merge($request->all(), [
                        'categoryID' => $categoryID,
                    ]));
                    $response = $this->specificItemReport($fakeRequest);
                    $payload = json_decode($response->getContent(), true);
                    if ($payload['success'] ?? false) {
                        $summary = $payload['summary'] ?? [];
                        $data['report_item'] = $payload['item'] ?? null;
                        $data['period_label'] = $summary['period_label'] ?? $period['label'];
                        $data['filter_label'] = ($payload['item']['item_name'] ?? 'Item') . ' — ' . ($summary['period_label'] ?? '');
                        $data['summary_cards'] = [
                            ['label' => 'In Stock', 'value' => $summary['labels_in_stock'] ?? 0],
                            ['label' => 'Issues', 'value' => $summary['issues_in_period'] ?? 0],
                            ['label' => 'Returned', 'value' => $summary['returned_in_period'] ?? 0],
                            ['label' => 'Incidents', 'value' => ($summary['damaged_incidents'] ?? 0) + ($summary['lost_incidents'] ?? 0)],
                        ];
                        $data['stock_breakdown'] = $payload['stock_breakdown'] ?? [];
                        $data['report_rows'] = $payload['requisitions'] ?? [];
                        $data['incident_rows'] = $payload['incidents'] ?? [];
                    }
                }
                break;
        }

        $pdf = PDF::loadView('Admin.pdf.store_section_pdf', $data);
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Store_' . ucfirst(str_replace('_', '', $section))
            . '_' . date('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }
}
