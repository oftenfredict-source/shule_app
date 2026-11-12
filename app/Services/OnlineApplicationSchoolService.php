<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\OnlineAdmission;
use App\Models\OnlineAdmissionSpace;
use App\Models\School;
use App\Models\Student;
use App\Models\Subclass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OnlineApplicationSchoolService
{
    private const DEFAULT_PUBLIC_SPACES = 50;

    public function __construct(
        private OnlineApplicationApplyTokenService $applyTokens,
        private SelectedAcademicYearService $yearService
    ) {
    }

    public function liveSchoolsQuery()
    {
        return School::query()
            ->where('status', 'Active')
            ->where('environment', 'Live')
            ->orderBy('school_name');
    }

    /**
     * @return array{schools: Collection, hasMore: bool, nextPage: int}
     */
    public function paginatedSchools(?string $search = null, int $page = 1, int $perPage = 10): array
    {
        $query = $this->liveSchoolsQuery();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', '%' . $search . '%')
                    ->orWhere('region', 'like', '%' . $search . '%')
                    ->orWhere('district', 'like', '%' . $search . '%');
            });
        }

        // Over-fetch so schools without spaces (after filter) don't empty the page.
        $fetch = max($perPage * 4, 20);
        $offset = max(0, ($page - 1) * $perPage);
        $schoolModels = (clone $query)->skip($offset)->take($fetch + 1)->get();
        $hasMoreSource = $schoolModels->count() > $fetch;
        if ($hasMoreSource) {
            $schoolModels = $schoolModels->take($fetch);
        }

        $schools = $this->buildSchoolCards($schoolModels)
            ->filter(fn ($school) => $school['hasAnySpaces'])
            ->take($perPage)
            ->values();

        return [
            'schools' => $schools,
            'hasMore' => $hasMoreSource || $schoolModels->count() >= $fetch,
            'nextPage' => $page + 1,
        ];
    }

    /**
     * Build cards for many schools with batched queries (public listing).
     */
    public function buildSchoolCards(Collection $schools): Collection
    {
        if ($schools->isEmpty()) {
            return collect();
        }

        $schoolIDs = $schools->pluck('schoolID')->map(fn ($id) => (int) $id)->unique()->values()->all();

        $classesBySchool = ClassModel::whereIn('schoolID', $schoolIDs)
            ->where('status', 'Active')
            ->get(['classID', 'schoolID', 'class_name'])
            ->groupBy('schoolID');

        $allClassIDs = $classesBySchool->flatten(1)->pluck('classID')->map(fn ($id) => (int) $id)->all();

        $subclassesByClass = empty($allClassIDs)
            ? collect()
            : Subclass::whereIn('classID', $allClassIDs)
                ->where('status', 'Active')
                ->get(['subclassID', 'classID'])
                ->groupBy('classID');

        $yearsBySchool = AcademicYear::whereIn('schoolID', $schoolIDs)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get()
            ->groupBy('schoolID');

        $spacesByKey = OnlineAdmissionSpace::whereIn('schoolID', $schoolIDs)
            ->get(['schoolID', 'classID', 'academic_yearID', 'spaces'])
            ->keyBy(fn ($row) => $this->spaceKey((int) $row->schoolID, (int) $row->classID, (int) $row->academic_yearID));

        $usedByKey = OnlineAdmission::whereIn('schoolID', $schoolIDs)
            ->whereIn('status', [OnlineAdmission::STATUS_PENDING, OnlineAdmission::STATUS_CONFIRMED])
            ->select('schoolID', 'classID', 'academic_yearID', DB::raw('COUNT(*) as used_count'))
            ->groupBy('schoolID', 'classID', 'academic_yearID')
            ->get()
            ->keyBy(fn ($row) => $this->spaceKey((int) $row->schoolID, (int) $row->classID, (int) $row->academic_yearID));

        $studentCountsBySchool = Student::whereIn('schoolID', $schoolIDs)
            ->where('status', 'Active')
            ->select('schoolID', DB::raw('COUNT(*) as count'))
            ->groupBy('schoolID')
            ->pluck('count', 'schoolID');

        return $schools->map(function (School $school) use (
            $classesBySchool,
            $subclassesByClass,
            $yearsBySchool,
            $spacesByKey,
            $usedByKey,
            $studentCountsBySchool
        ) {
            $schoolID = (int) $school->schoolID;
            $classes = $classesBySchool->get($schoolID, collect());

            if ($classes->isEmpty()) {
                return $this->emptySchoolCard($school);
            }

            $years = $yearsBySchool->get($schoolID, collect());
            $subclassesCount = 0;

            $classesWithSpaces = $classes->map(function ($class) use (
                $schoolID,
                $years,
                $subclassesByClass,
                $spacesByKey,
                $usedByKey,
                &$subclassesCount
            ) {
                $classID = (int) $class->classID;
                $classSubs = $subclassesByClass->get($classID, collect());
                $subclassesCount += $classSubs->count();

                $yearId = $this->resolveYearIdFromCollection($years, $class->class_name);
                $year = $yearId ? $years->firstWhere('academic_yearID', $yearId) : null;

                if (!$yearId) {
                    return [
                        'classID' => $classID,
                        'className' => $class->class_name,
                        'subclassesCount' => $classSubs->count(),
                        'currentStudents' => 0,
                        'totalCapacity' => 0,
                        'availableSpaces' => 0,
                        'hasSpaces' => false,
                        'academicYearID' => null,
                        'academicYearName' => null,
                        'applyUrl' => null,
                    ];
                }

                $key = $this->spaceKey($schoolID, $classID, $yearId);
                $allocated = $spacesByKey->has($key)
                    ? (int) $spacesByKey->get($key)->spaces
                    : self::DEFAULT_PUBLIC_SPACES;
                $used = $usedByKey->has($key) ? (int) $usedByKey->get($key)->used_count : 0;
                $available = $allocated > 0 ? max(0, $allocated - $used) : 0;
                $hasSpaces = $available > 0;

                return [
                    'classID' => $classID,
                    'className' => $class->class_name,
                    'subclassesCount' => $classSubs->count(),
                    'currentStudents' => $used,
                    'totalCapacity' => $allocated,
                    'availableSpaces' => $available,
                    'hasSpaces' => $hasSpaces,
                    'academicYearID' => $yearId,
                    'academicYearName' => $year?->year_name ?? $year?->year,
                    'applyUrl' => $hasSpaces
                        ? $this->applyTokens->applyUrl($schoolID, $classID, $yearId)
                        : null,
                ];
            });

            return [
                'schoolID' => $schoolID,
                'schoolName' => $school->school_name,
                'schoolType' => $school->school_type,
                'region' => $school->region,
                'district' => $school->district,
                'schoolLogo' => School::normalizePublicAssetPath($school->school_logo),
                'schoolLogoUrl' => School::publicAssetUrl($school->school_logo),
                'schoolUrl' => $this->applyTokens->localSchoolUrl($schoolID),
                'schoolShareUrl' => $this->applyTokens->schoolUrl($schoolID),
                'classesCount' => $classes->count(),
                'subclassesCount' => $subclassesCount,
                'activeStudentsCount' => (int) ($studentCountsBySchool[$schoolID] ?? 0),
                'classesWithSpaces' => $classesWithSpaces,
                'hasAnySpaces' => $classesWithSpaces->where('hasSpaces', true)->count() > 0,
            ];
        })->values();
    }

    public function buildSchoolCard(School $school): array
    {
        return $this->buildSchoolCards(collect([$school]))->first() ?? $this->emptySchoolCard($school);
    }

    private function spaceKey(int $schoolID, int $classID, int $academicYearID): string
    {
        return $schoolID . ':' . $classID . ':' . $academicYearID;
    }

    private function resolveYearIdFromCollection(Collection $years, ?string $className): ?int
    {
        if ($years->isEmpty()) {
            return null;
        }

        $isA = $this->yearService->isALevelClassName($className);
        $pool = $years->filter(function ($y) use ($isA) {
            return $isA ? $this->yearService->isALevelYear($y) : !$this->yearService->isALevelYear($y);
        })->values();

        if ($pool->isEmpty()) {
            $pool = $years;
        }

        $pick = $pool->firstWhere('is_current', true) ?: $pool->first();

        return $pick ? (int) $pick->academic_yearID : null;
    }

    private function emptySchoolCard(School $school): array
    {
        return [
            'schoolID' => $school->schoolID,
            'schoolName' => $school->school_name,
            'schoolType' => $school->school_type,
            'region' => $school->region,
            'district' => $school->district,
            'schoolLogo' => School::normalizePublicAssetPath($school->school_logo),
            'schoolLogoUrl' => School::publicAssetUrl($school->school_logo),
            'schoolUrl' => $this->applyTokens->localSchoolUrl((int) $school->schoolID),
            'schoolShareUrl' => $this->applyTokens->schoolUrl((int) $school->schoolID),
            'classesCount' => 0,
            'subclassesCount' => 0,
            'activeStudentsCount' => 0,
            'classesWithSpaces' => collect(),
            'hasAnySpaces' => false,
        ];
    }

    /**
     * Active fee structure for a school, grouped by class (public landing).
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection>
     */
    public function feesByClassForSchool(int $schoolID)
    {
        if (!class_exists(\App\Models\Fee::class)) {
            return collect();
        }

        return \App\Models\Fee::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->with(['breakdowns', 'bankAccount.bank', 'class'])
            ->orderBy('display_order')
            ->orderBy('fee_name')
            ->get()
            ->groupBy('classID');
    }
}
