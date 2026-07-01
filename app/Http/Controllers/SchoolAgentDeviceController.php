<?php

namespace App\Http\Controllers;

use App\Models\FingerprintDevice;
use Illuminate\Http\Request;

class SchoolAgentDeviceController extends Controller
{
    public function index(Request $request)
    {
        $schoolID = $this->schoolId($request);

        $devices = FingerprintDevice::where('schoolID', $schoolID)
            ->orderBy('label')
            ->get()
            ->map(fn ($d) => $d->toApiArray());

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ]);
    }

    public function store(Request $request)
    {
        $schoolID = $this->schoolId($request);
        $agent = $this->agentPayload($request);
        $data = $this->validateDevice($request);

        if (
            FingerprintDevice::where('schoolID', $schoolID)
                ->where('label', $data['label'])
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'A device with this label already exists for your school.',
            ], 422);
        }

        $device = FingerprintDevice::create([
            ...$data,
            'schoolID' => $schoolID,
            'created_by' => (int) ($agent['user_id'] ?? 0) ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully.',
            'device' => $device->toApiArray(),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $device = $this->findSchoolDevice($request, $id);

        return response()->json([
            'success' => true,
            'device' => $device->toApiArray(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $schoolID = $this->schoolId($request);
        $device = $this->findSchoolDevice($request, $id);
        $data = $this->validateDevice($request);

        if (
            FingerprintDevice::where('schoolID', $schoolID)
                ->where('label', $data['label'])
                ->where('id', '!=', $device->id)
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Another device with this label already exists.',
            ], 422);
        }

        $device->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Device updated successfully.',
            'device' => $device->fresh()->toApiArray(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $device = $this->findSchoolDevice($request, $id);
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully.',
        ]);
    }

    private function schoolId(Request $request): int
    {
        return (int) ($this->agentPayload($request)['schoolID'] ?? 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function agentPayload(Request $request): array
    {
        $payload = $request->attributes->get('school_agent');
        return is_array($payload) ? $payload : [];
    }

    private function findSchoolDevice(Request $request, int $id): FingerprintDevice
    {
        return FingerprintDevice::where('schoolID', $this->schoolId($request))
            ->where('id', $id)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDevice(Request $request): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
            'brand' => 'required|string|max:60',
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'comm_key' => 'nullable|string|max:16',
            'usage_type' => 'nullable|string|max:40',
            'location' => 'nullable|string|max:150',
            'serial_number' => 'nullable|string|max:80',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:2000',
        ], [], [
            'comm_key' => 'comm key',
        ]);

        $data['comm_key'] = $data['comm_key'] ?? '0';

        return $data;
    }
}
