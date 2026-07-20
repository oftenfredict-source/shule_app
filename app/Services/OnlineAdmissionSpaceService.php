<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\OnlineAdmission;
use App\Models\OnlineAdmissionSpace;
use App\Models\School;

class OnlineAdmissionSpaceService
{
    public function __construct(
        private SelectedAcademicYearService $yearService
    ) {
    }

    public function resolveCurrentYearIdForClass(int $schoolID, ?string $className): ?int
    {
        $isA = $this->yearService->isALevelClassName($className);
        $years = AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get();

        if ($years->isEmpty()) {
            return null;
        }

        $pool = $years->filter(function ($y) use ($isA) {
            return $isA ? $this->yearService->isALevelYear($y) : !$this->yearService->isALevelYear($y);
        })->values();

        if ($pool->isEmpty()) {
            $pool = $years;
        }

        $pick = $pool->firstWhere('is_current', true) ?: $pool->first();

        return $pick ? (int) $pick->academic_yearID : null;
    }

    public function currentAcademicYearForSchool(int $schoolID): ?AcademicYear
    {
        return AcademicYear::where('schoolID', $schoolID)
            ->where('is_current', true)
            ->orderByDesc('academic_yearID')
            ->first();
    }

    public function allocatedSpaces(int $schoolID, int $classID, int $academicYearID): int
    {
        return (int) OnlineAdmissionSpace::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('academic_yearID', $academicYearID)
            ->value('spaces');
    }

    public function usedSpaces(int $schoolID, int $classID, int $academicYearID): int
    {
        return OnlineAdmission::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('academic_yearID', $academicYearID)
            ->whereIn('status', [OnlineAdmission::STATUS_PENDING, OnlineAdmission::STATUS_CONFIRMED])
            ->count();
    }

    public function availableSpaces(int $schoolID, int $classID, int $academicYearID): int
    {
        $allocated = $this->allocatedSpaces($schoolID, $classID, $academicYearID);
        if ($allocated <= 0) {
            return 0;
        }

        return max(0, $allocated - $this->usedSpaces($schoolID, $classID, $academicYearID));
    }

    public function classAvailability(ClassModel $class, ?int $academicYearID = null): array
    {
        $yearId = $academicYearID ?? $this->resolveCurrentYearIdForClass(
            (int) $class->schoolID,
            $class->class_name
        );

        if (!$yearId) {
            return [
                'academic_yearID' => null,
                'academic_year_name' => null,
                'allocated_spaces' => 0,
                'used_spaces' => 0,
                'available_spaces' => 0,
                'has_spaces' => false,
            ];
        }

        $year = AcademicYear::find($yearId);
        $allocated = $this->allocatedSpaces((int) $class->schoolID, (int) $class->classID, $yearId);
        $used = $this->usedSpaces((int) $class->schoolID, (int) $class->classID, $yearId);
        $available = max(0, $allocated - $used);

        return [
            'academic_yearID' => $yearId,
            'academic_year_name' => $year?->year_name ?? $year?->year,
            'allocated_spaces' => $allocated,
            'used_spaces' => $used,
            'available_spaces' => $available,
            'has_spaces' => $available > 0,
        ];
    }

    public function isLiveSchool(?School $school): bool
    {
        return $school
            && $school->status === 'Active'
            && strcasecmp((string) $school->environment, 'Live') === 0;
    }

    /**
     * Ensure each active class has a spaces row for its resolved academic year.
     * Default is 50 when missing (does not overwrite existing values).
     */
    public function ensureDefaultSpacesForSchool(int $schoolID, int $defaultSpaces = 50): void
    {
        $defaultSpaces = max(0, $defaultSpaces);
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get(['classID', 'schoolID', 'class_name']);

        foreach ($classes as $class) {
            $yearId = $this->resolveCurrentYearIdForClass($schoolID, $class->class_name);
            if (!$yearId) {
                continue;
            }

            $exists = OnlineAdmissionSpace::where('schoolID', $schoolID)
                ->where('classID', (int) $class->classID)
                ->where('academic_yearID', $yearId)
                ->exists();

            if (!$exists) {
                OnlineAdmissionSpace::create([
                    'schoolID' => $schoolID,
                    'classID' => (int) $class->classID,
                    'academic_yearID' => $yearId,
                    'spaces' => $defaultSpaces,
                ]);
            }
        }
    }

    public function saveSpacesForYear(int $schoolID, int $academicYearID, array $classSpaces): void
    {
        foreach ($classSpaces as $classID => $spaces) {
            $classID = (int) $classID;
            $spaces = max(0, (int) $spaces);

            OnlineAdmissionSpace::updateOrCreate(
                [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'academic_yearID' => $academicYearID,
                ],
                ['spaces' => $spaces]
            );
        }
    }

    /**
     * Save spaces using each class's resolved academic year (O-level / A-level aware).
     *
     * @param  array<int|string, int|string>  $classSpaces
     */
    public function saveSpacesForClasses(int $schoolID, array $classSpaces): void
    {
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereIn('classID', array_map('intval', array_keys($classSpaces)))
            ->get()
            ->keyBy('classID');

        foreach ($classSpaces as $classID => $spaces) {
            $classID = (int) $classID;
            $spaces = max(0, (int) $spaces);
            $class = $classes->get($classID);
            if (!$class) {
                continue;
            }

            $yearId = $this->resolveCurrentYearIdForClass($schoolID, $class->class_name);
            if (!$yearId) {
                continue;
            }

            OnlineAdmissionSpace::updateOrCreate(
                [
                    'schoolID' => $schoolID,
                    'classID' => $classID,
                    'academic_yearID' => $yearId,
                ],
                ['spaces' => $spaces]
            );
        }
    }
}
