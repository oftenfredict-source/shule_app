<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\School;

/**
 * @deprecated Use OnlineAdmissionSpaceService directly.
 */
class OnlineApplicationCapacityService
{
    public function __construct(
        private OnlineAdmissionSpaceService $spaces
    ) {
    }

    public function isLiveSchool(?School $school): bool
    {
        return $this->spaces->isLiveSchool($school);
    }

    public function calculateForClass(ClassModel $class, $subclasses = null, array $studentCounts = []): array
    {
        $availability = $this->spaces->classAvailability($class);

        return [
            'classID' => (int) $class->classID,
            'className' => $class->class_name,
            'subclassesCount' => $subclasses ? $subclasses->count() : 0,
            'currentStudents' => $availability['used_spaces'],
            'totalCapacity' => $availability['allocated_spaces'],
            'availableSpaces' => $availability['available_spaces'],
            'hasSpaces' => $availability['has_spaces'],
            'academicYearID' => $availability['academic_yearID'],
            'academicYearName' => $availability['academic_year_name'],
        ];
    }

    public function availableSpacesForClass(int $classID): int
    {
        $class = ClassModel::where('classID', $classID)->where('status', 'Active')->first();
        if (!$class) {
            return 0;
        }

        return $this->spaces->classAvailability($class)['available_spaces'];
    }
}
