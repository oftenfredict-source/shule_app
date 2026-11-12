<?php

namespace App\Http\Controllers;

use App\Models\AiTrainedQa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AiTrainingController extends Controller
{
    private function ensureSuperAdmin()
    {
        if (Session::get('user_type') !== 'SuperAdmin') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access. Please login as Super Admin.',
                ], 403);
            }

            return redirect()->route('login')->with('error', 'Unauthorized access. Please login as Super Admin.');
        }

        return null;
    }

    public function index()
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $items = AiTrainedQa::orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        return view('SuperAdmin.ai_training', compact('items'));
    }

    public function store(Request $request)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'locale' => 'required|in:en,sw,both',
            'keywords' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $item = AiTrainedQa::create([
            'question' => trim($request->question),
            'answer' => trim($request->answer),
            'locale' => $request->locale,
            'keywords' => $request->keywords ? trim($request->keywords) : null,
            'priority' => (int) ($request->priority ?? 10),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Session::get('userID'),
        ]);

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Common question trained successfully.',
            'item' => $item,
        ]);
    }

    public function update(Request $request, int $id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $item = AiTrainedQa::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'error' => 'Training item not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'locale' => 'required|in:en,sw,both',
            'keywords' => 'nullable|string|max:500',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $item->update([
            'question' => trim($request->question),
            'answer' => trim($request->answer),
            'locale' => $request->locale,
            'keywords' => $request->keywords ? trim($request->keywords) : null,
            'priority' => (int) ($request->priority ?? $item->priority),
            'is_active' => $request->boolean('is_active', $item->is_active),
        ]);

        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Training updated successfully.',
            'item' => $item->fresh(),
        ]);
    }

    public function destroy(int $id)
    {
        if ($resp = $this->ensureSuperAdmin()) {
            return $resp;
        }

        $item = AiTrainedQa::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'error' => 'Training item not found.'], 404);
        }

        $item->delete();
        $this->bustCache();

        return response()->json([
            'success' => true,
            'message' => 'Training deleted successfully.',
        ]);
    }

    private function bustCache(): void
    {
        Cache::forget('ai_trained_qa_chunks_en');
        Cache::forget('ai_trained_qa_chunks_sw');
        Cache::forget('ai_trained_qa_chunks_all');
    }
}
