<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AcademicYearsController extends Controller
{
    public function index()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || !in_array($userType, ['Admin', 'Staff', 'Teacher'])) {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $school = School::find($schoolID);
        $academicYears = AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get();

        return view('Admin.academic_years', compact('school', 'academicYears'));
    }

    public function store(Request $request)
    {
        $access = $this->authorizeAdminAccess();
        if ($access !== true) {
            return $access;
        }

        $schoolID = (int) Session::get('schoolID');
        $school = School::find($schoolID);
        $isSecondary = $school && strcasecmp((string) $school->school_type, 'Secondary') === 0;

        $payload = $this->validateYearPayload($request, $isSecondary);
        if ($payload['error']) {
            return response()->json([
                'success' => false,
                'message' => $payload['message'],
                'errors' => $payload['errors'] ?? null,
            ], 422);
        }

        $exists = AcademicYear::where('schoolID', $schoolID)
            ->where('year_name', $payload['year_name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Academic year "' . $payload['year_name'] . '" already exists for this school.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $isCurrent = (bool) $payload['is_current'];
            if ($isCurrent) {
                $this->clearOtherCurrentYears(
                    $schoolID,
                    $isSecondary,
                    $payload['level_type'] ?? null
                );
            }

            $year = AcademicYear::create([
                'schoolID' => $schoolID,
                'year' => $payload['year'],
                'year_name' => $payload['year_name'],
                'start_date' => $payload['start_date'],
                'end_date' => $payload['end_date'],
                'status' => $isCurrent ? 'Active' : 'Draft',
                'is_current' => $isCurrent,
                'notes' => $payload['notes'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Academic year registered successfully.',
                'year' => $this->formatYear($year),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register academic year: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($academicYearID)
    {
        $access = $this->authorizeSchoolAccess();
        if ($access !== true) {
            return $access;
        }

        $schoolID = (int) Session::get('schoolID');
        $year = AcademicYear::where('schoolID', $schoolID)
            ->where('academic_yearID', $academicYearID)
                ->first();

        if (!$year) {
                    return response()->json([
                        'success' => false,
                'message' => 'Academic year not found.',
            ], 404);
        }

            return response()->json([
                'success' => true,
            'year' => $this->formatYear($year),
        ]);
    }

    public function update(Request $request, $academicYearID)
    {
        $access = $this->authorizeAdminAccess();
        if ($access !== true) {
            return $access;
        }

        $schoolID = (int) Session::get('schoolID');
            $school = School::find($schoolID);
        $isSecondary = $school && strcasecmp((string) $school->school_type, 'Secondary') === 0;

        $year = AcademicYear::where('schoolID', $schoolID)
            ->where('academic_yearID', $academicYearID)
                    ->first();

        if (!$year) {
            return response()->json([
                'success' => false,
                'message' => 'Academic year not found.',
            ], 404);
        }

        $payload = $this->validateYearPayload($request, $isSecondary);
        if ($payload['error']) {
            return response()->json([
                'success' => false,
                'message' => $payload['message'],
                'errors' => $payload['errors'] ?? null,
            ], 422);
        }

        $exists = AcademicYear::where('schoolID', $schoolID)
            ->where('year_name', $payload['year_name'])
            ->where('academic_yearID', '!=', $year->academic_yearID)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Academic year "' . $payload['year_name'] . '" already exists for this school.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $isCurrent = (bool) $payload['is_current'];
            if ($isCurrent) {
                $this->clearOtherCurrentYears(
                    $schoolID,
                    $isSecondary,
                    $payload['level_type'] ?? null,
                    $year->academic_yearID
                );
            }

            $year->year = $payload['year'];
            $year->year_name = $payload['year_name'];
            $year->start_date = $payload['start_date'];
            $year->end_date = $payload['end_date'];
            $year->is_current = $isCurrent;
            if ($isCurrent) {
                $year->status = 'Active';
            } elseif ($year->status === 'Active') {
                $year->status = 'Draft';
            }
            $year->notes = $payload['notes'];
            $year->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Academic year updated successfully.',
                'year' => $this->formatYear($year->fresh()),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update academic year: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($academicYearID)
    {
        $access = $this->authorizeAdminAccess();
        if ($access !== true) {
            return $access;
        }

        $schoolID = (int) Session::get('schoolID');
        $year = AcademicYear::where('schoolID', $schoolID)
                    ->where('academic_yearID', $academicYearID)
                    ->first();

        if (!$year) {
            return response()->json([
                'success' => false,
                'message' => 'Academic year not found.',
            ], 404);
        }

        if ($year->is_current) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the current academic year. Set another year as current first.',
            ], 422);
        }

        try {
            $year->delete();
            return response()->json([
                'success' => true,
                'message' => 'Academic year deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete academic year: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function authorizeSchoolAccess()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || !in_array($userType, ['Admin', 'Staff', 'Teacher'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        return true;
    }

    private function authorizeAdminAccess()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only Admin can manage academic years.',
            ], 403);
        }

        return true;
    }

    /**
     * Primary: only one current year for the school.
     * Secondary: one current O-Level and one current A-Level (both may be current together).
     */
    private function clearOtherCurrentYears(
        int $schoolID,
        bool $isSecondary,
        ?string $levelType,
        $exceptAcademicYearID = null
    ): void {
        $query = AcademicYear::where('schoolID', $schoolID);

        if ($exceptAcademicYearID) {
            $query->where('academic_yearID', '!=', $exceptAcademicYearID);
        }

        if ($isSecondary) {
            if ($levelType === 'A_Level') {
                // Clear other A-Level currents only (year_name like 2025/2026)
                $query->where('year_name', 'like', '%/%');
            } else {
                // Clear other O-Level currents only (single year name)
                $query->where('year_name', 'not like', '%/%');
            }
        }

        $query->update(['is_current' => false]);
    }

    private function validateYearPayload(Request $request, bool $isSecondary): array
    {
        $rules = [
            'is_current' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ];

        if ($isSecondary) {
            $rules['level_type'] = 'required|in:O_Level,A_Level';
            $rules['year'] = 'nullable|integer|min:1990|max:2100';
            $rules['start_year'] = 'nullable|integer|min:1990|max:2100';
            $rules['end_year'] = 'nullable|integer|min:1990|max:2100';
        } else {
            $rules['year'] = 'required|integer|min:1990|max:2100';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ];
        }

        $isCurrent = $request->boolean('is_current');
        $notes = $request->input('notes');

        if ($isSecondary) {
            $levelType = $request->input('level_type');

            if ($levelType === 'O_Level') {
                $year = (int) $request->input('year');
                if ($year < 1990) {
                    return [
                        'error' => true,
                        'message' => 'Please provide a valid O-Level year.',
                    ];
                }

                return [
                    'error' => false,
                    'year' => $year,
                    'year_name' => (string) $year,
                    'start_date' => $year . '-01-01',
                    'end_date' => $year . '-12-31',
                    'is_current' => $isCurrent,
                    'notes' => $notes,
                    'level_type' => 'O_Level',
                ];
            }

            $startYear = (int) $request->input('start_year');
            $endYear = (int) $request->input('end_year');

            if ($startYear < 1990 || $endYear < 1990) {
                return [
                    'error' => true,
                    'message' => 'Please provide valid A-Level start and end years.',
                ];
            }

            if ($endYear !== $startYear + 1) {
                return [
                    'error' => true,
                    'message' => 'A-Level end year must be start year + 1 (e.g. 2025/2026).',
                ];
            }

            return [
                'error' => false,
                'year' => $startYear,
                'year_name' => $startYear . '/' . $endYear,
                'start_date' => $startYear . '-01-01',
                'end_date' => $endYear . '-12-31',
                'is_current' => $isCurrent,
                'notes' => $notes,
                'level_type' => 'A_Level',
            ];
        }

        $year = (int) $request->input('year');

        return [
            'error' => false,
            'year' => $year,
            'year_name' => (string) $year,
            'start_date' => $year . '-01-01',
            'end_date' => $year . '-12-31',
            'is_current' => $isCurrent,
            'notes' => $notes,
            'level_type' => null,
        ];
    }

    private function formatYear(AcademicYear $year): array
    {
        $levelType = null;
        if (str_contains((string) $year->year_name, '/')) {
            $levelType = 'A_Level';
            [$startYear, $endYear] = array_pad(explode('/', $year->year_name, 2), 2, null);
                } else {
            $levelType = 'O_Level';
            $startYear = $year->year;
            $endYear = null;
            }

            return [
            'academic_yearID' => $year->academic_yearID,
            'schoolID' => $year->schoolID,
            'year' => $year->year,
            'year_name' => $year->year_name,
            'is_current' => (bool) $year->is_current,
            'status' => $year->status,
            'start_date' => optional($year->start_date)->format('Y-m-d'),
            'end_date' => optional($year->end_date)->format('Y-m-d'),
            'notes' => $year->notes,
            'level_type' => $levelType,
            'start_year' => $startYear ? (int) $startYear : null,
            'end_year' => $endYear ? (int) $endYear : null,
            'created_at' => optional($year->created_at)->format('Y-m-d H:i'),
            'updated_at' => optional($year->updated_at)->format('Y-m-d H:i'),
        ];
    }
}
