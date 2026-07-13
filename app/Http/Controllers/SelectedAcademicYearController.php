<?php

namespace App\Http\Controllers;

use App\Services\SelectedAcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SelectedAcademicYearController extends Controller
{
    public function show(SelectedAcademicYearService $service)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School not found'], 400);
        }

        $context = $service->headerContext((int) $schoolID);

        return response()->json([
            'success' => true,
            'context' => $this->serializeContext($context),
        ]);
    }

    public function update(Request $request, SelectedAcademicYearService $service)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return response()->json(['success' => false, 'message' => 'School not found'], 400);
        }

        $result = $service->setSelection((int) $schoolID, $request->all());
        if (!($result['success'] ?? false)) {
            return response()->json($result, 422);
        }

        $result['context'] = $this->serializeContext($result['context']);

        return response()->json($result);
    }

    private function serializeContext(array $context): array
    {
        $mapYear = function ($year) {
            if (!$year) {
                return null;
            }

            return [
                'academic_yearID' => $year->academic_yearID,
                'year' => $year->year,
                'year_name' => $year->year_name ?: (string) $year->year,
                'is_current' => (bool) $year->is_current,
                'status' => $year->status,
            ];
        };

        return [
            'mode' => $context['mode'] ?? 'none',
            'has_a_level' => (bool) ($context['has_a_level'] ?? false),
            'selected_academic_yearID' => $context['selected_academic_yearID'] ?? null,
            'selected_o_level_academic_yearID' => $context['selected_o_level_academic_yearID'] ?? null,
            'selected_a_level_academic_yearID' => $context['selected_a_level_academic_yearID'] ?? null,
            'selected_year_ids' => $context['selected_year_ids'] ?? [],
            'selected_year' => $mapYear($context['selected_year'] ?? null),
            'selected_o_level_year' => $mapYear($context['selected_o_level_year'] ?? null),
            'selected_a_level_year' => $mapYear($context['selected_a_level_year'] ?? null),
            'years' => collect($context['years'] ?? [])->map($mapYear)->values(),
            'o_level_years' => collect($context['o_level_years'] ?? [])->map($mapYear)->values(),
            'a_level_years' => collect($context['a_level_years'] ?? [])->map($mapYear)->values(),
        ];
    }
}
