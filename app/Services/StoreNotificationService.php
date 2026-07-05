<?php

namespace App\Services;

use App\Models\OtherStaff;
use App\Models\ParentModel;
use App\Models\School;
use App\Models\StoreInventoryActor;
use App\Models\StoreItemIncident;
use App\Models\StoreRequisition;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StoreNotificationService
{
    private SmsService $smsService;

    public function __construct(?SmsService $smsService = null)
    {
        $this->smsService = $smsService ?? new SmsService();
    }

    /**
     * @param  Collection<int, StoreRequisition>|array<int, StoreRequisition>  $requisitions
     */
    public function notifyItemIssued(
        int $schoolID,
        string $recipientType,
        int $recipientId,
        string $recipientName,
        $requisitions
    ): void {
        $school = School::find($schoolID);
        if (!$school) {
            return;
        }

        collect($requisitions)->each(fn (StoreRequisition $req) => $req->loadMissing(['item']));

        $itemsSummary = $this->summarizeRequisitions($requisitions);
        $issuedBy = collect($requisitions)->first()?->issued_by ?: 'Administrator';

        $recipientMsg = "Umepewa vitu kutoka store: {$itemsSummary}. Imetolewa na {$issuedBy}.";
        $adminMsg = "{$recipientName} amechukua vitu kutoka store: {$itemsSummary}.";

        $this->sendToRecipient($school, $recipientType, $recipientId, $recipientMsg);
        $this->sendToAdmin($school, $adminMsg, 'Store issue notification');
    }

    public function notifyItemReturned(int $schoolID, StoreRequisition $requisition, string $condition): void
    {
        $school = School::find($schoolID);
        if (!$school) {
            return;
        }

        $requisition->loadMissing(['item']);
        $itemName = $requisition->item->item_name ?? 'Item';
        $takeDisplay = $requisition->take_display ?: $this->formatQty($requisition->quantity_taken) . ' item(s)';
        $conditionLabel = match ($condition) {
            'damaged' => 'Damaged',
            'lost' => 'Lost',
            default => 'Good',
        };

        $recipientMsg = "Item {$itemName} ({$takeDisplay}) imerudishwa katika hali: {$conditionLabel}.";
        $adminMsg = "{$requisition->recipient_name} amerejesha {$itemName} ({$takeDisplay}) — hali: {$conditionLabel}.";

        $this->sendToRecipient(
            $school,
            $requisition->recipient_type,
            (int) $requisition->recipient_id,
            $recipientMsg
        );
        $this->sendToAdmin($school, $adminMsg, 'Store return notification');
    }

    /**
     * @param  Collection<int, StoreItemIncident>|array<int, StoreItemIncident>  $incidents
     */
    public function notifyIncidentsRecorded(int $schoolID, $incidents, array $responsible): void
    {
        $school = School::find($schoolID);
        if (!$school || empty($incidents)) {
            return;
        }

        $first = collect($incidents)->first();
        collect($incidents)->each(fn (StoreItemIncident $inc) => $inc->loadMissing(['item']));

        $incidentType = $first->incident_type ?? 'damaged';
        $typeLabel = $incidentType === 'lost' ? 'Lost' : 'Damaged';
        $typeLabelSw = $incidentType === 'lost' ? 'kupotea' : 'kuharibiwa';

        $itemsSummary = collect($incidents)->map(function (StoreItemIncident $inc) {
            $itemName = $inc->item->item_name ?? 'Item';
            $take = $inc->take_display ?: $this->formatQty($inc->quantity_affected) . ' item(s)';
            return "{$itemName} ({$take})";
        })->implode('; ');

        $reason = trim((string) ($first->reason ?? ''));
        $reasonPart = $reason !== '' ? " Sababu: {$reason}." : '';

        if ($responsible['type'] !== 'other') {
            $recipientMsg = "Tunakutaarifu kuwa vitu vya store vime{$typeLabelSw}: {$itemsSummary}.{$reasonPart}";
            $this->sendToResponsible(
                $school,
                $responsible['type'],
                $responsible['id'] ?? null,
                $recipientMsg
            );
        }

        $adminMsg = "Record mpya ya {$typeLabel}: {$itemsSummary}. Mhusika: {$responsible['name']}.{$reasonPart}";
        $this->sendToAdmin($school, $adminMsg, 'Store incident notification');
    }

    public function notifyItemRequested(
        int $schoolID,
        string $requesterType,
        int $requesterId,
        string $requesterName,
        string $itemsSummary,
        array $categoryIds = []
    ): void {
        $school = School::find($schoolID);
        if (!$school) {
            return;
        }

        $adminMsg = "{$requesterName} ameomba vitu kutoka store: {$itemsSummary}. Angalia Requisition kwenye inventory.";
        $requesterMsg = "Ombi lako la vitu kutoka store limewasilishwa: {$itemsSummary}. Utataarifiwa baada ya idhini.";

        $this->sendToRecipient($school, $requesterType, $requesterId, $requesterMsg);
        $this->sendToAdmin($school, $adminMsg, 'Store item request notification');

        $this->notifyInventoryActorsForRequest($school, $schoolID, $requesterName, $itemsSummary, $categoryIds);
    }

    public function notifyItemRequestRejected(
        int $schoolID,
        string $requesterType,
        int $requesterId,
        string $requesterName,
        string $itemsSummary,
        ?string $reviewNotes = null
    ): void {
        $school = School::find($schoolID);
        if (!$school) {
            return;
        }

        $notesPart = trim((string) $reviewNotes) !== '' ? ' Maelezo: ' . trim($reviewNotes) . '.' : '';
        $recipientMsg = "Ombi lako la vitu kutoka store limekataliwa: {$itemsSummary}.{$notesPart}";
        $this->sendToRecipient($school, $requesterType, $requesterId, $recipientMsg);
    }

    private function notifyInventoryActorsForRequest(
        School $school,
        int $schoolID,
        string $requesterName,
        string $itemsSummary,
        array $categoryIds
    ): void {
        $actors = StoreInventoryActor::where('schoolID', $schoolID)
            ->where('status', 'active')
            ->get()
            ->filter(function (StoreInventoryActor $actor) use ($categoryIds) {
                $privileges = array_merge(
                    StoreInventoryActor::defaultPrivileges(),
                    is_array($actor->privileges) ? $actor->privileges : []
                );
                if (empty($privileges['approve_requests'])) {
                    return false;
                }
                if ($actor->category_scope === 'all' || empty($categoryIds)) {
                    return true;
                }
                $allowed = collect($actor->category_ids ?? [])->map(fn ($id) => (int) $id)->all();
                foreach ($categoryIds as $catId) {
                    if (in_array((int) $catId, $allowed, true)) {
                        return true;
                    }
                }

                return false;
            });

        $actorMsg = "{$requesterName} ameomba vitu: {$itemsSummary}. Fungua Inventory > Requisition kuapprove.";
        foreach ($actors as $actor) {
            $phone = $this->resolveRecipientPhone($actor->actor_type, (int) $actor->actor_ref_id, $schoolID);
            if (!$phone) {
                continue;
            }
            $this->sendSafe($phone, $actorMsg, $school, 'Store actor request notification');
        }
    }

    private function summarizeRequisitions($requisitions): string
    {
        return collect($requisitions)->map(function (StoreRequisition $req) {
            $req->loadMissing(['item']);
            $itemName = $req->item->item_name ?? 'Item';
            $take = $req->take_display ?: $this->formatQty($req->quantity_taken) . ' item(s)';
            return "{$itemName} — {$take}";
        })->implode('; ');
    }

    private function sendToRecipient(School $school, string $type, int $id, string $message): void
    {
        $phone = $this->resolveRecipientPhone($type, $id, (int) $school->schoolID);
        if (!$phone) {
            return;
        }

        $this->sendSafe($phone, $message, $school, "Store recipient SMS ({$type})");
    }

    private function sendToResponsible(School $school, string $type, ?int $id, string $message): void
    {
        if ($type === 'other') {
            return;
        }

        $phone = $this->resolveRecipientPhone($type, (int) $id, (int) $school->schoolID);
        if (!$phone) {
            return;
        }

        $this->sendSafe($phone, $message, $school, "Store responsible SMS ({$type})");
    }

    private function sendToAdmin(School $school, string $message, string $context): void
    {
        $phone = $this->normalizePhone($school->phone ?? '');
        if (!$phone) {
            Log::info("{$context}: school admin phone not configured.");
            return;
        }

        $this->sendSafe($phone, $message, $school, $context);
    }

    private function sendSafe(string $phone, string $message, School $school, string $context): void
    {
        try {
            $formatted = $this->smsService->formatMessage($message, $school->school_name);
            $result = $this->smsService->sendSms($phone, $formatted);

            if (!($result['success'] ?? false)) {
                Log::error("{$context} SMS failed for {$phone}: " . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Throwable $e) {
            Log::error("{$context} SMS exception for {$phone}: " . $e->getMessage());
        }
    }

    private function resolveRecipientPhone(string $type, int $id, int $schoolID): ?string
    {
        if ($type === 'teacher') {
            $person = Teacher::where('id', $id)->where('schoolID', $schoolID)->first();
            return $this->normalizePhone($person->phone_number ?? '');
        }

        if ($type === 'staff') {
            $person = OtherStaff::where('id', $id)->where('schoolID', $schoolID)->first();
            return $this->normalizePhone($person->phone_number ?? '');
        }

        if ($type === 'student') {
            $student = Student::where('studentID', $id)->where('schoolID', $schoolID)->with('parent')->first();
            if (!$student) {
                return null;
            }

            return $this->resolveParentPhoneForStudent($student);
        }

        return null;
    }

    private function resolveParentPhoneForStudent(Student $student): ?string
    {
        $phone = null;
        if ($student->parent && !empty(trim((string) $student->parent->phone))) {
            $phone = $student->parent->phone;
        } elseif ($student->parentID) {
            $parent = $student->relationLoaded('parent')
                ? $student->parent
                : ParentModel::find($student->parentID);
            if ($parent && !empty(trim((string) $parent->phone))) {
                $phone = $parent->phone;
            }
        }

        if (!$phone && !empty($student->emergency_contact_phone)) {
            $phone = $student->emergency_contact_phone;
        }

        return $this->normalizePhone((string) $phone);
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = trim($phone);
        if (
            $phone === '' ||
            in_array(strtolower($phone), ['null', 'undefined', 'n/a'], true)
        ) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $phone) ?: null;
    }

    private function formatQty($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 4, '.', ''), '0'), '.');
    }
}
