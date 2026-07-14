<?php

namespace App\Services;

use App\Models\Examination;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentMoveBatch;
use App\Models\StudentMoveBatchItem;
use App\Models\Subclass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamShiftService
{
    public function __construct(
        private StudentMoveService $moveService
    ) {
    }

    /**
     * Preview students for exam shifting (checkbox UI).
     */
    public function preview(int $schoolID, int $examID): array
    {
        $exam = Examination::where('examID', $examID)->where('schoolID', $schoolID)->first();
        if (!$exam) {
            return ['success' => false, 'message' => 'Examination not found.'];
        }

        $shiftingStatus = $exam->student_shifting_status ?? 'none';
        if ($shiftingStatus === 'none') {
            return ['success' => false, 'message' => 'Student shifting is not enabled for this exam.'];
        }

        $school = School::find($schoolID);
        $schoolType = $school ? ($school->school_type ?? 'Secondary') : 'Secondary';

        $studentIDs = DB::table('results')
            ->where('examID', $examID)
            ->distinct()
            ->pluck('studentID')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rows = [];
        foreach ($studentIDs as $studentID) {
            $student = Student::with(['subclass.class', 'subclass.combie'])->find($studentID);
            if (!$student || (int) $student->schoolID !== $schoolID || $student->status !== 'Active') {
                continue;
            }

            $currentSubclass = $student->subclass;
            if (!$currentSubclass || !$currentSubclass->class) {
                continue;
            }

            $currentClass = $currentSubclass->class;
            $classNameNorm = strtolower(preg_replace('/[\s\-]+/', '_', $currentClass->class_name));
            $combieID = $currentSubclass->combieID;

            $marksInfo = $this->getStudentMarksSummary($studentID, $examID, $schoolType, $classNameNorm);
            $targets = $this->listTargetSubclasses(
                $schoolID,
                $currentClass,
                $currentSubclass,
                $classNameNorm,
                $shiftingStatus,
                $schoolType
            );

            $suggested = null;
            $alternates = [];
            foreach ($targets as $target) {
                $qualifies = $this->qualifiesForSubclass($target, $combieID, $marksInfo, $schoolType, $classNameNorm);
                $option = [
                    'subclassID' => (int) $target->subclassID,
                    'label' => trim(($target->class_name ?? '') . ' ' . ($target->subclass_name ?? '')),
                    'first_grade' => $target->first_grade,
                    'final_grade' => $target->final_grade,
                    'grade_range' => ($target->first_grade || $target->final_grade)
                        ? trim(($target->first_grade ?? '—') . ' to ' . ($target->final_grade ?? '—'))
                        : 'No grade limit',
                    'qualifies' => $qualifies,
                ];
                if ($qualifies && !$suggested) {
                    $suggested = $option;
                }
                $alternates[] = $option;
            }

            $fromLabel = trim($currentClass->class_name . ' ' . $currentSubclass->subclass_name);
            $rows[] = [
                'studentID' => (int) $student->studentID,
                'admission_number' => $student->admission_number,
                'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                'from_class' => $fromLabel,
                'from_subclassID' => (int) $currentSubclass->subclassID,
                'from_classID' => (int) $currentClass->classID,
                'suggested_subclassID' => $suggested['subclassID'] ?? null,
                'to_class' => $suggested['label'] ?? '—',
                'grade_range' => $suggested['grade_range'] ?? '—',
                'marks' => $marksInfo['display'] ?? 'N/A',
                'marks_value' => $marksInfo['compare_value'] ?? null,
                'qualifies' => (bool) $suggested,
                'checked_default' => (bool) $suggested,
                'alternates' => $alternates,
                'move_type' => $shiftingStatus === 'external' ? 'external' : 'internal',
            ];
        }

        $latestBatch = StudentMoveBatch::where('schoolID', $schoolID)
            ->where('examID', $examID)
            ->where('status', 'Completed')
            ->orderByDesc('batchID')
            ->first();

        return [
            'success' => true,
            'examID' => $examID,
            'exam_name' => $exam->exam_name,
            'shifting_status' => $shiftingStatus,
            'students' => $rows,
            'can_undo' => (bool) $latestBatch,
            'last_batchID' => $latestBatch?->batchID,
        ];
    }

    /**
     * Execute selected student shifts using enrollment history (same pattern as Move Student).
     *
     * @param  array<int, array{studentID:int, new_subclassID:int}>  $moves
     */
    public function execute(int $schoolID, int $examID, array $moves, $movedBy = null): array
    {
        $exam = Examination::where('examID', $examID)->where('schoolID', $schoolID)->first();
        if (!$exam) {
            return ['success' => false, 'message' => 'Examination not found.'];
        }

        $shiftingStatus = $exam->student_shifting_status ?? 'none';
        if ($shiftingStatus === 'none') {
            return ['success' => false, 'message' => 'Student shifting is not enabled for this exam.'];
        }

        if (empty($moves)) {
            return ['success' => false, 'message' => 'No students selected.'];
        }

        try {
            return DB::transaction(function () use ($schoolID, $exam, $examID, $moves, $movedBy, $shiftingStatus) {
                $moved = 0;
                $failed = [];
                $batch = null;

                foreach ($moves as $move) {
                    $studentID = (int) ($move['studentID'] ?? 0);
                    $newSubclassID = (int) ($move['new_subclassID'] ?? 0);
                    if (!$studentID || !$newSubclassID) {
                        continue;
                    }

                    $enrollment = StudentEnrollment::with(['classModel', 'subclass.class', 'student'])
                        ->where('schoolID', $schoolID)
                        ->where('studentID', $studentID)
                        ->where('status', 'Active')
                        ->orderByDesc('enrollmentID')
                        ->lockForUpdate()
                        ->first();

                    if (!$enrollment || !$enrollment->student) {
                        $failed[] = "Student #{$studentID}: no active enrollment";
                        continue;
                    }

                    $newSubclass = Subclass::with('class')->find($newSubclassID);
                    if (!$newSubclass || !$newSubclass->class || (int) $newSubclass->class->schoolID !== $schoolID) {
                        $failed[] = "Student #{$studentID}: invalid destination";
                        continue;
                    }

                    $oldClassID = (int) $enrollment->classID;
                    $oldSubclassID = (int) $enrollment->subclassID;
                    $newClassID = (int) $newSubclass->classID;

                    if ($newSubclassID === $oldSubclassID) {
                        continue;
                    }

                    $type = ($newClassID === $oldClassID) ? 'internal' : 'external';
                    if ($shiftingStatus === 'internal' && $type !== 'internal') {
                        $failed[] = "Student #{$studentID}: internal shift must stay in same class";
                        continue;
                    }

                    $oldClassName = $enrollment->classModel->class_name
                        ?? optional($enrollment->subclass)->class->class_name
                        ?? '';
                    $oldSubclassName = optional($enrollment->subclass)->subclass_name ?? '';
                    $oldLabel = trim($oldClassName . ' ' . $oldSubclassName);
                    $newLabel = trim(($newSubclass->class->class_name ?? '') . ' ' . ($newSubclass->subclass_name ?? ''));

                    $promotionStatus = 'Pending';
                    $action = 'Internal';
                    if ($type === 'external') {
                        $action = 'Moved';
                        $promotionStatus = 'Promoted';
                    }

                    $student = $enrollment->student;
                    $oldStudentStatus = $student->status ?? 'Active';

                    $enrollment->status = 'Completed';
                    $enrollment->promotion_status = $promotionStatus;
                    $enrollment->left_date = now()->toDateString();
                    $enrollment->notes = trim(($enrollment->notes ? $enrollment->notes . ' | ' : '') . "Exam shift #{$examID} → {$newLabel}");
                    $enrollment->save();

                    $newEnrollment = StudentEnrollment::create([
                        'studentID' => $student->studentID,
                        'schoolID' => $schoolID,
                        'academic_yearID' => $enrollment->academic_yearID,
                        'classID' => $newClassID,
                        'subclassID' => $newSubclassID,
                        'status' => 'Active',
                        'promotion_status' => 'Pending',
                        'joined_date' => now()->toDateString(),
                        'notes' => "Exam shift from {$oldLabel} (exam #{$examID})",
                    ]);

                    $student->old_subclassID = $oldSubclassID;
                    $student->subclassID = $newSubclassID;
                    if ($student->status !== 'Active') {
                        $student->status = 'Active';
                    }
                    $student->save();

                    if (!$batch) {
                        $batch = StudentMoveBatch::create([
                            'schoolID' => $schoolID,
                            'to_academic_yearID' => $enrollment->academic_yearID,
                            'examID' => $examID,
                            'moved_by' => $movedBy,
                            'moved_count' => 0,
                            'graduated_count' => 0,
                            'status' => 'Completed',
                            'notes' => "Exam shift: {$exam->exam_name} ({$shiftingStatus})",
                        ]);
                    }

                    StudentMoveBatchItem::create([
                        'batchID' => $batch->batchID,
                        'studentID' => $student->studentID,
                        'old_enrollmentID' => $enrollment->enrollmentID,
                        'new_enrollmentID' => $newEnrollment->enrollmentID,
                        'action' => $action === 'Moved' ? 'Promoted' : 'Internal',
                        'old_classID' => $oldClassID,
                        'old_subclassID' => $oldSubclassID,
                        'new_classID' => $newClassID,
                        'new_subclassID' => $newSubclassID,
                        'old_student_status' => $oldStudentStatus,
                        'new_student_status' => 'Active',
                    ]);

                    $moved++;
                }

                if ($batch) {
                    $batch->moved_count = $moved;
                    $batch->save();
                }

                if ($moved === 0) {
                    return [
                        'success' => false,
                        'message' => 'No students were shifted.' . (!empty($failed) ? ' ' . implode('; ', array_slice($failed, 0, 3)) : ''),
                        'failed' => $failed,
                    ];
                }

                return [
                    'success' => true,
                    'message' => "{$moved} student(s) shifted successfully (enrollment history saved).",
                    'moved_count' => $moved,
                    'batchID' => $batch?->batchID,
                    'failed' => $failed,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('ExamShiftService::execute failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ['success' => false, 'message' => 'Shift failed: ' . $e->getMessage()];
        }
    }

    public function undo(int $schoolID, int $examID, $undoneBy = null): array
    {
        $batch = StudentMoveBatch::where('schoolID', $schoolID)
            ->where('examID', $examID)
            ->where('status', 'Completed')
            ->orderByDesc('batchID')
            ->first();

        if (!$batch) {
            return ['success' => false, 'message' => 'No exam shift batch found to undo.'];
        }

        return $this->moveService->undoBatch((int) $schoolID, (int) $batch->batchID, $undoneBy);
    }

    private function listTargetSubclasses(
        int $schoolID,
        $currentClass,
        $currentSubclass,
        string $classNameNorm,
        string $shiftingStatus,
        string $schoolType
    ) {
        $query = DB::table('subclasses')
            ->join('classes', 'subclasses.classID', '=', 'classes.classID')
            ->leftJoin('combies', 'subclasses.combieID', '=', 'combies.combieID')
            ->where('classes.schoolID', $schoolID)
            ->where('subclasses.subclassID', '!=', $currentSubclass->subclassID)
            ->select(
                'subclasses.subclassID',
                'subclasses.subclass_name',
                'subclasses.first_grade',
                'subclasses.final_grade',
                'subclasses.combieID',
                'classes.classID',
                'classes.class_name',
                'combies.combie_name'
            );

        if ($shiftingStatus === 'internal') {
            $query->where('classes.classID', $currentClass->classID);
        } else {
            $next = $this->nextClassName($classNameNorm, $schoolType);
            if (!$next) {
                return collect();
            }
            $query->whereRaw(
                'LOWER(REPLACE(REPLACE(classes.class_name, " ", "_"), "-", "_")) LIKE ?',
                ['%' . $next . '%']
            );
        }

        return $query->orderBy('subclasses.subclass_name')->get();
    }

    private function nextClassName(string $className, string $schoolType): ?string
    {
        $map = $schoolType === 'Primary'
            ? [
                'standard_1' => 'standard_2', 'standard_2' => 'standard_3', 'standard_3' => 'standard_4',
                'standard_4' => 'standard_5', 'standard_5' => 'standard_6', 'standard_6' => 'standard_7',
            ]
            : [
                'form_one' => 'form_two', 'form_1' => 'form_two',
                'form_two' => 'form_three', 'form_2' => 'form_three',
                'form_three' => 'form_four', 'form_3' => 'form_four',
                'form_five' => 'form_six', 'form_5' => 'form_six',
            ];

        foreach ($map as $from => $to) {
            if (str_contains($className, $from)) {
                return $to;
            }
        }

        return null;
    }

    private function getStudentMarksSummary(int $studentID, int $examID, string $schoolType, string $className): array
    {
        $results = DB::table('results')
            ->where('studentID', $studentID)
            ->where('examID', $examID)
            ->whereNotNull('marks')
            ->pluck('marks');

        if ($results->isEmpty()) {
            return ['display' => 'No marks', 'compare_value' => null, 'mode' => 'none'];
        }

        $total = 0.0;
        foreach ($results as $m) {
            $total += (float) $m;
        }
        $avg = $total / max(1, $results->count());

        // Secondary O-level often uses points/division; fallback to average for range compare
        return [
            'display' => 'Avg ' . number_format($avg, 1) . ' (' . $results->count() . ' subjects)',
            'compare_value' => $avg,
            'mode' => 'average',
            'total_marks' => $total,
            'subject_count' => $results->count(),
        ];
    }

    private function qualifiesForSubclass($subclass, $combieID, array $marksInfo, string $schoolType, string $className): bool
    {
        if ($combieID && $subclass->combieID && (int) $combieID !== (int) $subclass->combieID) {
            return false;
        }

        $first = $subclass->first_grade;
        $final = $subclass->final_grade;
        if ($first === null || $first === '' || $final === null || $final === '') {
            return true;
        }

        $value = $marksInfo['compare_value'] ?? null;
        if ($value === null) {
            return false;
        }

        // Numeric grade bands (e.g. 60-100)
        if (is_numeric($first) && is_numeric($final)) {
            $low = min((float) $first, (float) $final);
            $high = max((float) $first, (float) $final);
            return $value >= $low && $value <= $high;
        }

        // Letter / division text ranges — treat as textual window when marks_info has division
        $token = strtoupper(trim((string) ($marksInfo['division'] ?? $marksInfo['display'] ?? '')));
        $a = strtoupper(trim((string) $first));
        $b = strtoupper(trim((string) $final));
        if ($token === '') {
            // Fallback: if non-numeric range configured but we only have average, allow selection
            // (user can override destination in UI)
            return true;
        }

        return $token === $a || $token === $b || str_contains($token, $a) || str_contains($token, $b);
    }
}
