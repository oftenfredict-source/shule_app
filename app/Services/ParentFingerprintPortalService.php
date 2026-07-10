<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClassModel;
use App\Models\ParentFingerprintPortalClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ParentFingerprintPortalService
{
    /**
     * @return array<int, bool> classID => enabled
     */
    public function getClassEnabledMap(int $schoolID): array
    {
        return ParentFingerprintPortalClass::where('schoolID', $schoolID)
            ->pluck('enabled', 'classID')
            ->map(fn ($v) => (bool) $v)
            ->all();
    }

    public function isClassEnabled(int $schoolID, int $classID): bool
    {
        return ParentFingerprintPortalClass::where('schoolID', $schoolID)
            ->where('classID', $classID)
            ->where('enabled', true)
            ->exists();
    }

    public function isStudentEnabled(Student $student): bool
    {
        $student->loadMissing('subclass.class');
        $classID = (int) ($student->subclass->classID ?? 0);
        if ($classID <= 0) {
            return false;
        }

        return $this->isClassEnabled((int) $student->schoolID, $classID);
    }

    /**
     * @param array<int, int|string> $enabledClassIds
     */
    public function syncSchoolClasses(int $schoolID, array $enabledClassIds): void
    {
        $validClassIds = ClassModel::where('schoolID', $schoolID)
            ->pluck('classID')
            ->map(fn ($id) => (int) $id)
            ->all();

        $enabledSet = array_flip(array_map('intval', $enabledClassIds));

        DB::transaction(function () use ($schoolID, $validClassIds, $enabledSet) {
            foreach ($validClassIds as $classID) {
                ParentFingerprintPortalClass::updateOrCreate(
                    ['schoolID' => $schoolID, 'classID' => $classID],
                    ['enabled' => isset($enabledSet[$classID])]
                );
            }
        });
    }
}
