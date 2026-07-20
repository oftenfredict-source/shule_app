<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\School;
use App\Models\Student;
use App\Models\Subclass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OnlineApplicationSchoolService
{
    public function __construct(
        private OnlineApplicationCapacityService $capacity,
        private OnlineApplicationApplyTokenService $applyTokens
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

        $offset = max(0, ($page - 1) * $perPage);
        $schoolModels = (clone $query)->skip($offset)->take($perPage + 1)->get();
        $hasMore = $schoolModels->count() > $perPage;

        if ($hasMore) {
            $schoolModels = $schoolModels->take($perPage);
        }

        $schools = $schoolModels
            ->map(fn ($school) => $this->buildSchoolCard($school))
            ->filter(fn ($school) => $school['hasAnySpaces'])
            ->values();

        return [
            'schools' => $schools,
            'hasMore' => $hasMore,
            'nextPage' => $page + 1,
        ];
    }

    public function buildSchoolCard(School $school): array
    {
        $classes = ClassModel::where('schoolID', $school->schoolID)
            ->where('status', 'Active')
            ->get();

        $classIDs = $classes->pluck('classID')->toArray();

        if (empty($classIDs)) {
            return $this->emptySchoolCard($school);
        }

        $allSubclasses = Subclass::whereIn('classID', $classIDs)
            ->where('status', 'Active')
            ->get()
            ->groupBy('classID');

        $subclassIDs = $allSubclasses->flatten()->pluck('subclassID')->toArray();
        $studentCounts = [];

        if (!empty($subclassIDs)) {
            $studentCounts = Student::whereIn('subclassID', $subclassIDs)
                ->where('status', 'Active')
                ->select(DB::raw('subclassID, COUNT(*) as count'))
                ->groupBy('subclassID')
                ->pluck('count', 'subclassID')
                ->toArray();
        }

        $activeStudentsCount = Student::where('schoolID', $school->schoolID)
            ->where('status', 'Active')
            ->count();

        $classesWithSpaces = $classes->map(function ($class) use ($allSubclasses, $studentCounts) {
            $classSubclasses = $allSubclasses->get($class->classID, collect());
            $stats = $this->capacity->calculateForClass($class, $classSubclasses, $studentCounts);

            return array_merge($stats, [
                'applyUrl' => $stats['hasSpaces']
                    ? $this->applyTokens->applyUrl($class->schoolID, $class->classID, $stats['academicYearID'] ?? null)
                    : null,
            ]);
        });

        return [
            'schoolID' => $school->schoolID,
            'schoolName' => $school->school_name,
            'schoolType' => $school->school_type,
            'region' => $school->region,
            'district' => $school->district,
            'schoolLogo' => $school->school_logo,
            'schoolUrl' => $this->applyTokens->localSchoolUrl((int) $school->schoolID),
            'schoolShareUrl' => $this->applyTokens->schoolUrl((int) $school->schoolID),
            'classesCount' => $classes->count(),
            'subclassesCount' => $allSubclasses->flatten()->count(),
            'activeStudentsCount' => $activeStudentsCount,
            'classesWithSpaces' => $classesWithSpaces,
            'hasAnySpaces' => $classesWithSpaces->where('hasSpaces', true)->count() > 0,
        ];
    }

    private function emptySchoolCard(School $school): array
    {
        return [
            'schoolID' => $school->schoolID,
            'schoolName' => $school->school_name,
            'schoolType' => $school->school_type,
            'region' => $school->region,
            'district' => $school->district,
            'schoolLogo' => $school->school_logo,
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
