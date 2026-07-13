<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    /**
     * Get the current enrollment for a student, preferring header-selected academic year(s).
     */
    public function currentEnrollment($studentID, ?int $schoolID = null): ?StudentEnrollment
    {
        $query = StudentEnrollment::with(['academicYear', 'classModel', 'subclass'])
            ->where('studentID', $studentID)
            ->orderByDesc('enrollmentID');

        if ($schoolID) {
            $query->where('schoolID', $schoolID);
        }

        $yearIDs = [];
        try {
            $yearIDs = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID ? (int) $schoolID : null);
        } catch (\Throwable $e) {
            $yearIDs = [];
        }

        if (!empty($yearIDs)) {
            $inSelectedYear = (clone $query)
                ->whereIn('academic_yearID', $yearIDs)
                ->orderByRaw("CASE WHEN status = 'Active' THEN 0 ELSE 1 END")
                ->orderByDesc('enrollmentID')
                ->first();

            if ($inSelectedYear) {
                return $inSelectedYear;
            }
        }

        return (clone $query)
            ->where('status', 'Active')
            ->orderByDesc('enrollmentID')
            ->first();
    }

    /**
     * Resolve enrollmentID for writes. Returns null if none found.
     */
    public function resolveEnrollmentID($studentID, ?int $schoolID = null): ?int
    {
        $enrollment = $this->currentEnrollment($studentID, $schoolID);
        return $enrollment ? (int) $enrollment->enrollmentID : null;
    }

    /**
     * Student IDs enrolled in a subclass (scoped to selected academic year when set).
     */
    public function studentIDsInSubclass(int $subclassID, ?int $schoolID = null): array
    {
        $query = StudentEnrollment::query()
            ->where('subclassID', $subclassID);

        $this->applySelectedYearScope($query, $schoolID);

        if ($schoolID) {
            $query->where('schoolID', $schoolID);
        }

        return $query->pluck('studentID')->unique()->values()->all();
    }

    /**
     * Student IDs enrolled in a main class.
     */
    public function studentIDsInClass(int $classID, ?int $schoolID = null): array
    {
        $query = StudentEnrollment::query()
            ->where('classID', $classID);

        $this->applySelectedYearScope($query, $schoolID);

        if ($schoolID) {
            $query->where('schoolID', $schoolID);
        }

        return $query->pluck('studentID')->unique()->values()->all();
    }

    /**
     * Enrollments for a school, optionally filtered by class/subclass.
     */
    public function activeEnrollments(
        int $schoolID,
        ?int $classID = null,
        ?int $subclassID = null
    ): Collection {
        $query = StudentEnrollment::with(['student', 'classModel', 'subclass', 'academicYear'])
            ->where('schoolID', $schoolID);

        $this->applySelectedYearScope($query, $schoolID);

        if ($subclassID) {
            $query->where('subclassID', $subclassID);
        } elseif ($classID) {
            $query->where('classID', $classID);
        }

        return $query->orderBy('subclassID')->orderBy('studentID')->get();
    }

    /**
     * Students in subclass via enrollment (fallback to students.subclassID if no enrollments).
     */
    public function studentsInSubclass(int $subclassID, ?int $schoolID = null, array $statuses = ['Active'])
    {
        $studentIDs = $this->studentIDsInSubclass($subclassID, $schoolID);

        $query = Student::query()->whereIn('status', $statuses);

        if ($schoolID) {
            $query->where('schoolID', $schoolID);
        }

        if (!empty($studentIDs)) {
            return $query->whereIn('studentID', $studentIDs);
        }

        // Fallback while enrollments are being adopted
        return $query->where('subclassID', $subclassID);
    }

    /**
     * Map studentID => enrollmentID for a school.
     */
    public function enrollmentMapForSchool(int $schoolID): array
    {
        $query = StudentEnrollment::where('schoolID', $schoolID)
            ->orderByDesc('enrollmentID');

        $this->applySelectedYearScope($query, $schoolID);

        return $query
            ->get(['studentID', 'enrollmentID'])
            ->unique('studentID')
            ->pluck('enrollmentID', 'studentID')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Prefer resolved enrollment; else latest enrollment for student.
     */
    public function bestEnrollmentID($studentID, ?int $schoolID = null): ?int
    {
        $id = $this->resolveEnrollmentID($studentID, $schoolID);
        if ($id) {
            return $id;
        }

        $query = StudentEnrollment::where('studentID', $studentID)->orderByDesc('enrollmentID');
        if ($schoolID) {
            $query->where('schoolID', $schoolID);
        }
        $enrollment = $query->first();
        return $enrollment ? (int) $enrollment->enrollmentID : null;
    }

    /**
     * Create Active enrollment when a student is registered (form or template import).
     * Idempotent for unique (studentID, academic_yearID, subclassID).
     *
     * @param  \App\Models\Subclass|int  $subclass
     */
    public function enrollOnRegistration(
        Student $student,
        int $schoolID,
        $subclass,
        ?string $notes = null
    ): StudentEnrollment {
        if (!$subclass instanceof \App\Models\Subclass) {
            $subclass = \App\Models\Subclass::with('class')->find($subclass);
        } else {
            $subclass->loadMissing('class');
        }

        if (!$subclass) {
            throw new \InvalidArgumentException('Subclass not found for student enrollment.');
        }

        $className = $subclass->class->class_name ?? null;
        $yearId = $this->resolveAcademicYearIdForClass($className, $schoolID);

        if (!$yearId) {
            throw new \RuntimeException(
                'No academic year is available for this class. Set/select the academic year in the header, then register again.'
            );
        }

        $existing = StudentEnrollment::where('studentID', $student->studentID)
            ->where('academic_yearID', $yearId)
            ->where('subclassID', $subclass->subclassID)
            ->first();

        if ($existing) {
            return $existing;
        }

        $joinedDate = null;
        if (!empty($student->admission_date)) {
            try {
                $joinedDate = \Carbon\Carbon::parse($student->admission_date)->toDateString();
            } catch (\Throwable $e) {
                $joinedDate = null;
            }
        }

        return StudentEnrollment::create([
            'studentID' => $student->studentID,
            'schoolID' => $schoolID,
            'academic_yearID' => $yearId,
            'classID' => (int) $subclass->classID,
            'subclassID' => (int) $subclass->subclassID,
            'roll_number' => null,
            'status' => 'Active',
            'promotion_status' => 'Pending',
            'joined_date' => $joinedDate ?: now()->toDateString(),
            'left_date' => null,
            'notes' => $notes,
        ]);
    }

    /**
     * Resolve academic year for registration: header selection (O/A aware), then current year fallback.
     */
    private function resolveAcademicYearIdForClass(?string $className, int $schoolID): ?int
    {
        $yearService = app(SelectedAcademicYearService::class);
        $yearId = $yearService->yearIdForClassName($className, $schoolID);
        if ($yearId) {
            return (int) $yearId;
        }

        $isA = $yearService->isALevelClassName($className);
        $years = \App\Models\AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get();

        if ($years->isEmpty()) {
            return null;
        }

        $pool = $years->filter(function ($y) use ($yearService, $isA) {
            return $isA ? $yearService->isALevelYear($y) : !$yearService->isALevelYear($y);
        })->values();

        if ($pool->isEmpty()) {
            $pool = $years;
        }

        $pick = $pool->firstWhere('is_current', true) ?: $pool->first();

        return $pick ? (int) $pick->academic_yearID : null;
    }

    /**
     * Sync students.subclassID from enrollment.
     */
    public function syncStudentSubclassFromEnrollment(StudentEnrollment $enrollment): void
    {
        DB::table('students')
            ->where('studentID', $enrollment->studentID)
            ->update([
                'subclassID' => $enrollment->subclassID,
                'updated_at' => now(),
            ]);
    }

    /**
     * When header years are selected, scope to those years; otherwise Active only.
     */
    private function applySelectedYearScope($query, ?int $schoolID = null): void
    {
        $yearIDs = [];
        try {
            $yearIDs = app(SelectedAcademicYearService::class)->selectedYearIds($schoolID ? (int) $schoolID : null);
        } catch (\Throwable $e) {
            $yearIDs = [];
        }

        if (!empty($yearIDs)) {
            $query->whereIn('academic_yearID', $yearIDs);
        } else {
            $query->where('status', 'Active');
        }
    }
}
