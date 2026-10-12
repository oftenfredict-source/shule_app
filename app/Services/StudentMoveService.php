<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentMoveBatch;
use App\Models\StudentMoveBatchItem;
use App\Models\Subclass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentMoveService
{
    /**
     * @deprecated Cutoff corrections removed - students follow normal promote/graduate rules.
     * Kept false so any leftover callers stay inactive.
     */
    public const ALEVEL_MAY_CUTOFF_CORRECTION_ENABLED = false;

    /** @deprecated Unused after cutoff removal. */
    public const ALEVEL_REGISTRATION_MAY_CUTOFF = 6;

    /** @deprecated Cutoff corrections removed. */
    public const OLEVEL_JULY_CUTOFF_CORRECTION_ENABLED = false;

    /** @deprecated Unused after cutoff removal. */
    public const OLEVEL_REGISTRATION_JUNE_CUTOFF = 6;

    public function normalizeClassName(?string $className): string
    {
        return strtolower(preg_replace('/[\s\-]+/', '_', trim((string) $className)));
    }

    public function isALevelClass(?string $className): bool
    {
        $n = $this->normalizeClassName($className);
        return in_array($n, ['form_five', 'form_5', 'form_six', 'form_6'], true);
    }

    public function isOLevelClass(?string $className): bool
    {
        $n = $this->normalizeClassName($className);
        return in_array($n, [
            'pre_form_one',
            'form_one', 'form_1',
            'form_two', 'form_2',
            'form_three', 'form_3',
            'form_four', 'form_4',
        ], true);
    }

    public function isFormFour(?string $className): bool
    {
        $n = $this->normalizeClassName($className);
        return in_array($n, ['form_four', 'form_4'], true);
    }

    public function isFormFive(?string $className): bool
    {
        $n = $this->normalizeClassName($className);
        return in_array($n, ['form_five', 'form_5'], true);
    }

    public function isFormSix(?string $className): bool
    {
        $n = $this->normalizeClassName($className);
        return in_array($n, ['form_six', 'form_6'], true);
    }

    /**
     * Display label like manageClasses: "Science (SCI)" or name/code alone.
     */
    public function formatCombieLabel($combie): ?string
    {
        if (!$combie) {
            return null;
        }

        $name = trim((string) ($combie->combie_name ?? ''));
        $code = trim((string) ($combie->combie_code ?? ''));

        if ($name === '' && $code === '') {
            return null;
        }
        if ($name !== '' && $code !== '' && strcasecmp($name, $code) !== 0) {
            return $name . ' (' . $code . ')';
        }

        return $name !== '' ? $name : $code;
    }

    /**
     * Short combie text for brackets (name preferred), e.g. "Business".
     */
    public function formatCombieShort($combie): ?string
    {
        if (!$combie) {
            return null;
        }
        if (is_string($combie)) {
            $text = trim($combie);
            return $text !== '' ? $text : null;
        }

        $name = trim((string) ($combie->combie_name ?? ''));
        $code = trim((string) ($combie->combie_code ?? ''));

        if ($name !== '') {
            return $name;
        }
        if ($code !== '') {
            return $code;
        }

        return null;
    }

    /**
     * Append combie in brackets: "Form One A (Business)".
     */
    public function withCombieBrackets(?string $label, $combie): string
    {
        $base = trim((string) $label);
        $short = $this->formatCombieShort($combie);
        if ($short === null || $short === '') {
            return $base;
        }

        $suffix = '(' . $short . ')';
        if ($base === '') {
            return $suffix;
        }
        if (str_ends_with(mb_strtolower($base), mb_strtolower($suffix))) {
            return $base;
        }

        return $base . ' ' . $suffix;
    }

    public function isGraduationClass(?string $className): bool
    {
        $n = $this->normalizeClassName($className);
        return in_array($n, [
            'form_four', 'form_4',
            'form_six', 'form_6',
            'standard_7', 'standard_vii', 'std_7',
        ], true);
    }

    /**
     * Next class key for promotion. Null means graduate.
     */
    public function nextClassKey(?string $className): ?string
    {
        if ($this->isGraduationClass($className)) {
            return null;
        }

        $map = [
            'pre_form_one' => 'form_one',
            'form_one' => 'form_two',
            'form_1' => 'form_two',
            'form_two' => 'form_three',
            'form_2' => 'form_three',
            'form_three' => 'form_four',
            'form_3' => 'form_four',
            'form_five' => 'form_six',
            'form_5' => 'form_six',
            'nursery' => 'baby_class',
            'baby_class' => 'middle_class',
            'middle_class' => 'top_class',
            'top_class' => 'standard_1',
            'standard_1' => 'standard_2',
            'standard_2' => 'standard_3',
            'standard_3' => 'standard_4',
            'standard_4' => 'standard_5',
            'standard_5' => 'standard_6',
            'standard_6' => 'standard_7',
        ];

        $n = $this->normalizeClassName($className);
        return $map[$n] ?? null;
    }

    public function findClassByKey(int $schoolID, string $classKey): ?ClassModel
    {
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->get();

        foreach ($classes as $class) {
            if ($this->normalizeClassName($class->class_name) === $classKey) {
                return $class;
            }
        }

        return null;
    }

    public function resolveDestinationSubclass(Subclass $currentSubclass, ClassModel $nextClass, bool $requireCombieMatch = true): array
    {
        $currentName = trim((string) $currentSubclass->subclass_name);
        $currentCombieID = $currentSubclass->combieID ? (int) $currentSubclass->combieID : null;

        $candidates = Subclass::with('combie')
            ->where('classID', $nextClass->classID)
            ->where(function ($q) {
                $q->where('status', 'Active')->orWhereNull('status');
            })
            ->orderBy('subclass_name')
            ->get();

        if ($candidates->isEmpty()) {
            return [
                'subclass' => null,
                'error' => 'No subclass in ' . $nextClass->class_name,
                'options' => [],
                'needs_choice' => false,
            ];
        }

        $options = $this->mapDestinationOptions($nextClass, $candidates);

        $pick = function (?Subclass $sub, bool $needsChoice = false, ?string $error = null) use ($options) {
            return [
                'subclass' => $sub,
                'error' => $error,
                'options' => $options,
                'needs_choice' => $needsChoice,
            ];
        };

        // 1) Same subclass name
        $sameName = null;
        if ($currentName !== '') {
            $sameName = $candidates->first(function (Subclass $s) use ($currentName) {
                return trim((string) $s->subclass_name) === $currentName;
            });
        }

        if ($sameName) {
            $destCombieID = $sameName->combieID ? (int) $sameName->combieID : null;
            $nameCombieClash = $requireCombieMatch
                && $currentCombieID
                && $destCombieID
                && $currentCombieID !== $destCombieID;

            if (!$nameCombieClash) {
                $needsChoice = !$currentCombieID && $this->destinationHasMultipleCombies($candidates);
                return $pick($sameName, $needsChoice);
            }
            // Name matches but combie clash → try same combie elsewhere below
        }

        // 2) Same combieID anywhere in next class
        if ($currentCombieID) {
            $sameCombieMatches = $candidates->filter(function (Subclass $s) use ($currentCombieID) {
                return (int) $s->combieID === $currentCombieID;
            })->values();

            if ($sameCombieMatches->count() === 1) {
                return $pick($sameCombieMatches->first(), false);
            }
            if ($sameCombieMatches->count() > 1) {
                return $pick($sameCombieMatches->first(), true);
            }

            return $pick(null, true, 'Combies not match - select destination combie/subclass');
        }

        // 3) Same stream_code
        if (!empty($currentSubclass->stream_code)) {
            $sameStream = $candidates->first(function (Subclass $s) use ($currentSubclass) {
                return !empty($s->stream_code) && $s->stream_code === $currentSubclass->stream_code;
            });
            if ($sameStream) {
                $needsChoice = $this->destinationHasMultipleCombies($candidates);
                return $pick($sameStream, $needsChoice);
            }
        }

        // 4) No current combie (e.g. Form Two → Form Three): choose if multiple
        if ($candidates->count() === 1) {
            return $pick($candidates->first(), false);
        }

        return $pick(null, true, 'Select destination subclass / combie');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Subclass>  $candidates
     */
    private function destinationHasMultipleCombies($candidates): bool
    {
        $combieIds = $candidates
            ->map(fn (Subclass $s) => $s->combieID ? (int) $s->combieID : 0)
            ->unique()
            ->values();

        return $combieIds->count() > 1 || $candidates->count() > 1;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Subclass>  $candidates
     */
    private function mapDestinationOptions(ClassModel $nextClass, $candidates): array
    {
        return $candidates->map(function (Subclass $sub) use ($nextClass) {
            $combieLabel = $this->formatCombieLabel($sub->combie);
            $stream = trim((string) ($sub->subclass_name ?? ''));
            $label = $this->withCombieBrackets(
                trim($nextClass->class_name . ' ' . $stream),
                $sub->combie
            );

            return [
                'subclassID' => (int) $sub->subclassID,
                'classID' => (int) $nextClass->classID,
                'subclass_name' => $sub->subclass_name,
                'combie' => $combieLabel,
                'label' => $label,
            ];
        })->values()->all();
    }

    public function classMatchesLevel(?string $className, ?string $level): bool
    {
        if (!$level) {
            return true;
        }
        $isA = $this->isALevelClass($className);
        if ($level === 'a_level') {
            return $isA;
        }
        if ($level === 'o_level') {
            return !$isA;
        }
        return true;
    }

    /**
     * Preview rows for active enrollments (optionally filtered by class/subclass/level).
     * When $excludeAcademicYearID is set, enrollments already in that year are hidden.
     */
    public function previewActiveEnrollments(
        int $schoolID,
        ?int $classID = null,
        ?int $subclassID = null,
        ?int $excludeAcademicYearID = null,
        ?string $level = null
    ): Collection {
        $level = $level ? strtolower(trim($level)) : null;
        if ($level && !in_array($level, ['o_level', 'a_level'], true)) {
            $level = null;
        }

        $query = StudentEnrollment::with(['student', 'classModel', 'subclass.combie', 'academicYear'])
            ->where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->whereHas('student', function ($q) {
                $q->whereIn('status', ['Active', 'Transferred']);
            });

        if (!empty($excludeAcademicYearID)) {
            $query->where('academic_yearID', '!=', (int) $excludeAcademicYearID);
        }

        if (!empty($subclassID)) {
            $query->where('subclassID', (int) $subclassID);
        } elseif (!empty($classID)) {
            $query->where('classID', (int) $classID);
        }

        return $query->orderBy('classID')->orderBy('subclassID')->orderBy('studentID')->get()
            ->map(function (StudentEnrollment $enrollment) use ($schoolID) {
                return $this->buildPreviewRow($enrollment, $schoolID, true);
            })
            ->filter(function ($row) use ($level) {
                if (!$row) {
                    return false;
                }
                if (!$level) {
                    return true;
                }
                // Infer level from current class label
                $classPart = explode(' ', $row['current_class'] ?? '')[0] ?? '';
                // Better: use class name from row - we need class key. Store class_key in preview.
                return $this->classMatchesLevel($row['current_class_key'] ?? $row['current_class'] ?? '', $level);
            })
            ->values();
    }

    /**
     * Registration date used for cutoff corrections (admission_date → joined_date → created_at).
     */
    public function resolveStudentRegistrationDate(StudentEnrollment $enrollment): ?\Carbon\Carbon
    {
        $student = $enrollment->student;
        if ($student && !empty($student->admission_date)) {
            return \Carbon\Carbon::parse($student->admission_date)->startOfDay();
        }
        if (!empty($enrollment->joined_date)) {
            return \Carbon\Carbon::parse($enrollment->joined_date)->startOfDay();
        }
        if (!empty($enrollment->created_at)) {
            return \Carbon\Carbon::parse($enrollment->created_at)->startOfDay();
        }
        if ($student && !empty($student->created_at)) {
            return \Carbon\Carbon::parse($student->created_at)->startOfDay();
        }
        return null;
    }

    /**
     * Current-year A-Level correction: registered in July or later → stay in same class.
     */
    public function shouldStaySameClassForALevelCorrection(StudentEnrollment $enrollment, string $currentClassName): bool
    {
        if (!self::ALEVEL_MAY_CUTOFF_CORRECTION_ENABLED) {
            return false;
        }
        if (!$this->isALevelClass($currentClassName)) {
            return false;
        }

        $year = $enrollment->academicYear;
        if (!$year && $enrollment->academic_yearID) {
            $year = \App\Models\AcademicYear::find($enrollment->academic_yearID);
        }
        if (!$year || !$year->is_current) {
            return false;
        }

        $registeredAt = $this->resolveStudentRegistrationDate($enrollment);
        if (!$registeredAt) {
            // Unknown date: keep normal promotion/graduation (safer default for eligible cohort)
            return false;
        }

        return (int) $registeredAt->month > self::ALEVEL_REGISTRATION_MAY_CUTOFF;
    }

    /**
     * Current-year O-Level correction: registered in July or later → stay in same class.
     * On/before 30 June → promote (Form 1-3) or graduate (Form 4).
     */
    public function shouldStaySameClassForOLevelCorrection(StudentEnrollment $enrollment, string $currentClassName): bool
    {
        if (!self::OLEVEL_JULY_CUTOFF_CORRECTION_ENABLED) {
            return false;
        }
        if (!$this->isOLevelClass($currentClassName)) {
            return false;
        }

        $year = $enrollment->academicYear;
        if (!$year && $enrollment->academic_yearID) {
            $year = \App\Models\AcademicYear::find($enrollment->academic_yearID);
        }
        if (!$year || !$year->is_current) {
            return false;
        }

        // O-Level years are non-slash names (A-Level years contain "/")
        $yearName = (string) ($year->year_name ?? '');
        if (str_contains($yearName, '/')) {
            return false;
        }

        $registeredAt = $this->resolveStudentRegistrationDate($enrollment);
        if (!$registeredAt) {
            return false;
        }

        return (int) $registeredAt->month > self::OLEVEL_REGISTRATION_JUNE_CUTOFF;
    }

    /**
     * Any active cutoff that forces Repeated / stay in current class.
     */
    public function shouldStaySameClassForCutoffCorrection(StudentEnrollment $enrollment, string $currentClassName): ?string
    {
        if ($this->shouldStaySameClassForALevelCorrection($enrollment, $currentClassName)) {
            return 'a_level';
        }
        if ($this->shouldStaySameClassForOLevelCorrection($enrollment, $currentClassName)) {
            return 'o_level';
        }
        return null;
    }

    public function buildPreviewRow(StudentEnrollment $enrollment, int $schoolID, bool $requireCombieMatch = true): ?array
    {
        $student = $enrollment->student;
        if (!$student) {
            return null;
        }

        $currentClassName = $enrollment->classModel->class_name
            ?? $enrollment->subclass->class->class_name
            ?? 'N/A';
        $currentSubclassName = $enrollment->subclass->subclass_name ?? '';
        $currentCombie = optional(optional($enrollment->subclass)->combie);
        $currentCombieLabel = $this->formatCombieLabel($currentCombie);
        $currentLabel = $this->withCombieBrackets(
            trim($currentClassName . ' ' . $currentSubclassName),
            $currentCombie
        );

        $willGraduate = $this->isGraduationClass($currentClassName);
        $nextKey = $this->nextClassKey($currentClassName);

        $destinationLabel = 'Graduated';
        $destinationClassID = null;
        $destinationSubclassID = null;
        $destinationCombieLabel = null;
        $destinationOptions = [];
        $needsDestinationChoice = false;
        $action = 'Graduated';
        $error = null;
        $note = null;

        if (!$willGraduate && $nextKey) {
            $nextClass = $this->findClassByKey($schoolID, $nextKey);
            if (!$nextClass) {
                $error = 'Next class not found: ' . $nextKey;
                $destinationLabel = '-';
            } elseif (!$enrollment->subclass) {
                $error = 'Current subclass missing';
                $destinationLabel = '-';
            } else {
                if (!$enrollment->relationLoaded('subclass') || !$enrollment->subclass->relationLoaded('combie')) {
                    $enrollment->load('subclass.combie');
                }
                $resolved = $this->resolveDestinationSubclass($enrollment->subclass, $nextClass, $requireCombieMatch);
                $destinationOptions = $resolved['options'] ?? [];
                $needsDestinationChoice = !empty($resolved['needs_choice']);

                if (!empty($resolved['subclass'])) {
                    $destSubclass = $resolved['subclass'];
                    $destSubclass->loadMissing('combie');
                    $destinationClassID = $nextClass->classID;
                    $destinationSubclassID = (int) $destSubclass->subclassID;
                    $destinationLabel = $this->withCombieBrackets(
                        trim($nextClass->class_name . ' ' . ($destSubclass->subclass_name ?? '')),
                        $destSubclass->combie
                    );
                    $destinationCombieLabel = $this->formatCombieLabel($destSubclass->combie);
                    $action = 'Promoted';
                    if ($needsDestinationChoice) {
                        $note = 'You can change destination subclass below';
                    }
                } elseif ($needsDestinationChoice && !empty($destinationOptions)) {
                    // e.g. Form Two → Form Three: must pick subclass/combie
                    $destinationClassID = $nextClass->classID;
                    $destinationSubclassID = null;
                    $destinationLabel = $nextClass->class_name;
                    $destinationCombieLabel = null;
                    $action = 'Promoted';
                    $error = $resolved['error'] ?: 'Select destination subclass';
                    $note = 'Choose which subclass this student will join';
                } else {
                    $error = $resolved['error'] ?? ('No subclass in ' . $nextClass->class_name);
                    $destinationLabel = $resolved['suggested_label'] ?? $nextClass->class_name;
                }
            }
        }

        $canMove = empty($error) && (
            $action === 'Graduated'
            || !empty($destinationSubclassID)
        );

        return [
            'enrollmentID' => $enrollment->enrollmentID,
            'studentID' => $student->studentID,
            'admission_number' => $student->admission_number,
            'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'gender' => $student->gender,
            'current_class' => $currentLabel,
            'current_class_key' => $currentClassName,
            'current_classID' => $enrollment->classID,
            'current_subclassID' => $enrollment->subclassID,
            'current_combie' => $currentCombieLabel,
            'destination_class' => $destinationLabel,
            'destination_classID' => $destinationClassID,
            'destination_subclassID' => $destinationSubclassID,
            'destination_combie' => $destinationCombieLabel,
            'destination_options' => $destinationOptions,
            'needs_destination_choice' => $needsDestinationChoice || (count($destinationOptions) > 1 && $action === 'Promoted'),
            'action' => $action,
            'error' => $error,
            'note' => $note,
            'registration_date' => null,
            'already_moved' => false,
            'can_move' => $canMove,
            'can_undo' => false,
        ];
    }

    /**
     * Students already moved into target year (Completed batches) - show with revert icon.
     */
    public function alreadyMovedForTargetYear(int $schoolID, int $toAcademicYearID, ?string $level = null): Collection
    {
        $level = $level ? strtolower(trim($level)) : null;

        $items = StudentMoveBatchItem::with([
            'student',
            'batch',
            'oldEnrollment.classModel',
            'oldEnrollment.subclass.combie',
            'newEnrollment.classModel',
            'newEnrollment.subclass.combie',
        ])
            ->whereHas('batch', function ($q) use ($schoolID, $toAcademicYearID) {
                $q->where('schoolID', $schoolID)
                    ->where('to_academic_yearID', $toAcademicYearID)
                    ->where('status', 'Completed');
            })
            ->orderByDesc('itemID')
            ->get();

        return $items->map(function (StudentMoveBatchItem $item) {
            $student = $item->student;
            if (!$student) {
                return null;
            }
            $oldClass = optional(optional($item->oldEnrollment)->classModel)->class_name
                ?? optional(optional($item->oldEnrollment)->subclass)->class->class_name
                ?? '';
            $oldSub = optional(optional($item->oldEnrollment)->subclass)->subclass_name ?? '';
            $newClass = optional(optional($item->newEnrollment)->classModel)->class_name
                ?? optional(optional($item->newEnrollment)->subclass)->class->class_name
                ?? '';
            $newSub = optional(optional($item->newEnrollment)->subclass)->subclass_name ?? '';
            if ($item->action === 'Graduated') {
                $newLabel = 'Graduated';
            } else {
                $newLabel = $this->withCombieBrackets(
                    trim($newClass . ' ' . $newSub),
                    optional(optional($item->newEnrollment)->subclass)->combie
                ) ?: '-';
            }

            $fromCombie = $this->formatCombieLabel(optional(optional($item->oldEnrollment)->subclass)->combie);
            $toCombie = $item->action === 'Graduated'
                ? null
                : $this->formatCombieLabel(optional(optional($item->newEnrollment)->subclass)->combie);

            return [
                'enrollmentID' => $item->old_enrollmentID,
                'itemID' => $item->itemID,
                'batchID' => $item->batchID,
                'studentID' => $student->studentID,
                'admission_number' => $student->admission_number,
                'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                'gender' => $student->gender,
                'current_class' => $this->withCombieBrackets(
                    trim($oldClass . ' ' . $oldSub),
                    optional(optional($item->oldEnrollment)->subclass)->combie
                ),
                'current_class_key' => $oldClass,
                'current_combie' => $fromCombie,
                'destination_class' => $newLabel,
                'destination_combie' => $toCombie,
                'action' => 'AlreadyMoved',
                'error' => null,
                'note' => 'Already moved - undo to move again',
                'already_moved' => true,
                'can_move' => false,
                'can_undo' => true,
            ];
        })
            ->filter()
            ->filter(function ($row) use ($level) {
                if (!$level) {
                    return true;
                }
                return $this->classMatchesLevel($row['current_class_key'] ?? '', $level);
            })
            ->unique('studentID')
            ->values();
    }

    public function undoBatchItem(int $schoolID, int $itemID, $undoneBy = null): array
    {
        return DB::transaction(function () use ($schoolID, $itemID, $undoneBy) {
            $item = StudentMoveBatchItem::with('batch')->find($itemID);
            if (!$item || !$item->batch || (int) $item->batch->schoolID !== (int) $schoolID) {
                return ['success' => false, 'message' => 'Move item not found.'];
            }
            if ($item->batch->status === 'Undone') {
                return ['success' => false, 'message' => 'This move batch was already undone.'];
            }

            if ($item->new_enrollmentID) {
                $new = StudentEnrollment::find($item->new_enrollmentID);
                if ($new) {
                    $new->status = 'Cancelled';
                    $new->left_date = now()->toDateString();
                    $new->notes = trim(($new->notes ? $new->notes . ' | ' : '') . 'Undone item #' . $itemID);
                    $new->save();
                }
            }

            $old = StudentEnrollment::find($item->old_enrollmentID);
            if ($old) {
                $old->status = 'Active';
                $old->promotion_status = 'Pending';
                $old->left_date = null;
                $old->save();
            }

            $student = Student::find($item->studentID);
            if ($student) {
                if ($item->old_subclassID) {
                    $student->subclassID = $item->old_subclassID;
                }
                $student->status = $item->old_student_status ?: 'Active';
                $student->old_subclassID = null;
                $student->save();
            }

            $batch = $item->batch;
            $item->delete();

            $remaining = StudentMoveBatchItem::where('batchID', $batch->batchID)->count();
            if ($remaining === 0) {
                $batch->status = 'Undone';
                $batch->undone_at = now();
                $batch->undone_by = $undoneBy;
                $batch->moved_count = 0;
                $batch->graduated_count = 0;
            } else {
                $batch->moved_count = StudentMoveBatchItem::where('batchID', $batch->batchID)
                    ->where('action', '!=', 'Graduated')
                    ->count();
                $batch->graduated_count = StudentMoveBatchItem::where('batchID', $batch->batchID)
                    ->where('action', 'Graduated')
                    ->count();
            }
            $batch->save();

            return [
                'success' => true,
                'message' => 'Student move undone. Enrollment restored.',
                'itemID' => $itemID,
                'batchID' => $batch->batchID,
            ];
        });
    }

    /**
     * Execute move for selected enrollment IDs into target academic year.
     */
    public function executeMove(
        int $schoolID,
        int $toAcademicYearID,
        array $enrollmentIDs,
        $movedBy = null,
        array $destinationOverrides = []
    ): array {
        $enrollmentIDs = array_values(array_unique(array_map('intval', $enrollmentIDs)));
        if (empty($enrollmentIDs)) {
            return ['success' => false, 'message' => 'No students selected.'];
        }

        // Normalize overrides: enrollmentID => subclassID
        $normalizedOverrides = [];
        foreach ($destinationOverrides as $enrollmentID => $subclassID) {
            $eid = (int) $enrollmentID;
            $sid = (int) $subclassID;
            if ($eid > 0 && $sid > 0) {
                $normalizedOverrides[$eid] = $sid;
            }
        }

        return DB::transaction(function () use ($schoolID, $toAcademicYearID, $enrollmentIDs, $movedBy, $normalizedOverrides) {
            $enrollments = StudentEnrollment::with(['student', 'classModel', 'subclass.combie', 'academicYear'])
                ->where('schoolID', $schoolID)
                ->where('status', 'Active')
                ->whereIn('enrollmentID', $enrollmentIDs)
                ->get();

            if ($enrollments->isEmpty()) {
                return ['success' => false, 'message' => 'No active enrollments found for selection.'];
            }

            $batch = StudentMoveBatch::create([
                'schoolID' => $schoolID,
                'to_academic_yearID' => $toAcademicYearID,
                'moved_by' => $movedBy,
                'moved_count' => 0,
                'graduated_count' => 0,
                'status' => 'Completed',
                'notes' => 'Bulk student move',
            ]);

            $promoted = 0;
            $graduated = 0;
            $repeated = 0;
            $errors = [];

            foreach ($enrollments as $enrollment) {
                $preview = $this->buildPreviewRow($enrollment, $schoolID, true);
                if (!$preview) {
                    $errors[] = [
                        'enrollmentID' => $enrollment->enrollmentID,
                        'message' => 'Cannot move',
                    ];
                    continue;
                }

                // Apply per-student destination override (combie/subclass choice)
                $overrideSubclassID = $normalizedOverrides[(int) $enrollment->enrollmentID] ?? null;
                if ($overrideSubclassID && ($preview['action'] ?? '') === 'Promoted') {
                    $chosen = Subclass::with(['class', 'combie'])->find($overrideSubclassID);
                    $expectedClassID = (int) ($preview['destination_classID'] ?? 0);
                    if (
                        !$chosen
                        || !$chosen->class
                        || (int) $chosen->class->schoolID !== (int) $schoolID
                        || ($expectedClassID && (int) $chosen->classID !== $expectedClassID)
                    ) {
                        $errors[] = [
                            'enrollmentID' => $enrollment->enrollmentID,
                            'message' => 'Invalid destination subclass/combie selected.',
                        ];
                        continue;
                    }

                    $preview['destination_subclassID'] = (int) $chosen->subclassID;
                    $preview['destination_classID'] = (int) $chosen->classID;
                    $preview['destination_class'] = trim(($chosen->class->class_name ?? '') . ' ' . ($chosen->subclass_name ?? ''));
                    $preview['destination_combie'] = $this->formatCombieLabel($chosen->combie);
                    $preview['error'] = null;
                    $preview['can_move'] = true;
                    $preview['action'] = 'Promoted';
                }

                if (empty($preview['can_move']) || (($preview['action'] ?? '') === 'Promoted' && empty($preview['destination_subclassID']))) {
                    $errors[] = [
                        'enrollmentID' => $enrollment->enrollmentID,
                        'message' => $preview['error'] ?? 'Select destination subclass/combie before moving.',
                    ];
                    continue;
                }

                $student = $enrollment->student;
                $oldStudentStatus = $student->status ?? 'Active';

                if ((int) $enrollment->academic_yearID === (int) $toAcademicYearID) {
                    $errors[] = [
                        'enrollmentID' => $enrollment->enrollmentID,
                        'message' => 'Student is already enrolled in the selected academic year.',
                    ];
                    continue;
                }

                if ($preview['action'] !== 'Graduated') {
                    $alreadyActiveInTarget = StudentEnrollment::where('studentID', $student->studentID)
                        ->where('academic_yearID', $toAcademicYearID)
                        ->where('status', 'Active')
                        ->exists();

                    if ($alreadyActiveInTarget) {
                        $errors[] = [
                            'enrollmentID' => $enrollment->enrollmentID,
                            'message' => 'Student already has an active enrollment in the target year.',
                        ];
                        continue;
                    }
                }

                $oldPromotionStatus = match ($preview['action']) {
                    'Graduated' => 'Graduated',
                    'Repeated' => 'Repeated',
                    default => 'Promoted',
                };

                // Close current enrollment (history preserved)
                $enrollment->status = 'Completed';
                $enrollment->promotion_status = $oldPromotionStatus;
                $enrollment->left_date = now()->toDateString();
                if (!empty($preview['note'])) {
                    $enrollment->notes = trim(($enrollment->notes ? $enrollment->notes . ' | ' : '') . $preview['note']);
                }
                $enrollment->save();

                $newEnrollmentID = null;
                $newStudentStatus = $oldStudentStatus;
                $batchAction = match ($preview['action']) {
                    'Graduated' => 'Graduated',
                    'Repeated' => 'Repeated',
                    default => 'Promoted',
                };

                if ($preview['action'] === 'Graduated') {
                    $student->status = 'Graduated';
                    $student->old_subclassID = $student->subclassID;
                    $student->save();
                    $newStudentStatus = 'Graduated';
                    $graduated++;
                } else {
                    // Reuse non-active row for same year+subclass if present; else create
                    $existing = StudentEnrollment::where('studentID', $student->studentID)
                        ->where('academic_yearID', $toAcademicYearID)
                        ->where('subclassID', $preview['destination_subclassID'])
                        ->first();

                    if ($existing) {
                        $existing->status = 'Active';
                        $existing->promotion_status = 'Pending';
                        $existing->classID = $preview['destination_classID'];
                        $existing->joined_date = now()->toDateString();
                        $existing->left_date = null;
                        $existing->notes = trim(($existing->notes ? $existing->notes . ' | ' : '') . 'Moved via batch #' . $batch->batchID);
                        $existing->save();
                        $newEnrollment = $existing;
                    } else {
                        $newEnrollment = StudentEnrollment::create([
                            'studentID' => $student->studentID,
                            'schoolID' => $schoolID,
                            'academic_yearID' => $toAcademicYearID,
                            'classID' => $preview['destination_classID'],
                            'subclassID' => $preview['destination_subclassID'],
                            'status' => 'Active',
                            'promotion_status' => 'Pending',
                            'joined_date' => now()->toDateString(),
                            'notes' => 'Moved via batch #' . $batch->batchID . ($preview['note'] ? ' | ' . $preview['note'] : ''),
                        ]);
                    }

                    $student->subclassID = $preview['destination_subclassID'];
                    $student->status = 'Active';
                    $student->old_subclassID = $preview['current_subclassID'];
                    $student->save();

                    $newEnrollmentID = $newEnrollment->enrollmentID;
                    $newStudentStatus = 'Active';
                    if ($preview['action'] === 'Repeated') {
                        $repeated++;
                    } else {
                        $promoted++;
                    }
                }

                StudentMoveBatchItem::create([
                    'batchID' => $batch->batchID,
                    'studentID' => $student->studentID,
                    'old_enrollmentID' => $enrollment->enrollmentID,
                    'new_enrollmentID' => $newEnrollmentID,
                    'action' => $batchAction,
                    'old_classID' => $preview['current_classID'],
                    'old_subclassID' => $preview['current_subclassID'],
                    'new_classID' => $preview['destination_classID'],
                    'new_subclassID' => $preview['destination_subclassID'],
                    'old_student_status' => $oldStudentStatus,
                    'new_student_status' => $newStudentStatus,
                ]);
            }

            $batch->moved_count = $promoted + $repeated;
            $batch->graduated_count = $graduated;
            $batch->notes = "Bulk student move. Promoted: {$promoted}, Stayed: {$repeated}, Graduated: {$graduated}.";
            $batch->save();

            return [
                'success' => true,
                'message' => "Move completed. Promoted: {$promoted}, Stayed (same class): {$repeated}, Graduated: {$graduated}.",
                'batchID' => $batch->batchID,
                'promoted' => $promoted,
                'repeated' => $repeated,
                'graduated' => $graduated,
                'errors' => $errors,
            ];
        });
    }

    public function undoBatch(int $schoolID, int $batchID, $undoneBy = null): array
    {
        return DB::transaction(function () use ($schoolID, $batchID, $undoneBy) {
            $batch = StudentMoveBatch::with('items')
                ->where('schoolID', $schoolID)
                ->where('batchID', $batchID)
                ->first();

            if (!$batch) {
                return ['success' => false, 'message' => 'Move batch not found.'];
            }
            if ($batch->status === 'Undone') {
                return ['success' => false, 'message' => 'This move was already undone.'];
            }

            foreach ($batch->items as $item) {
                // Remove / cancel new enrollment
                if ($item->new_enrollmentID) {
                    $new = StudentEnrollment::find($item->new_enrollmentID);
                    if ($new) {
                        $new->status = 'Cancelled';
                        $new->left_date = now()->toDateString();
                        $new->notes = trim(($new->notes ? $new->notes . ' | ' : '') . 'Undone batch #' . $batchID);
                        $new->save();
                    }
                }

                // Restore old enrollment
                $old = StudentEnrollment::find($item->old_enrollmentID);
                if ($old) {
                    $old->status = 'Active';
                    $old->promotion_status = 'Pending';
                    $old->left_date = null;
                    $old->save();
                }

                // Restore student
                $student = Student::find($item->studentID);
                if ($student) {
                    if ($item->old_subclassID) {
                        $student->subclassID = $item->old_subclassID;
                    }
                    $student->status = $item->old_student_status ?: 'Active';
                    $student->old_subclassID = null;
                    $student->save();
                }
            }

            $batch->status = 'Undone';
            $batch->undone_at = now();
            $batch->undone_by = $undoneBy;
            $batch->save();

            return [
                'success' => true,
                'message' => 'Move undone. Students restored to previous enrollments.',
                'batchID' => $batch->batchID,
                'restored' => $batch->items->count(),
            ];
        });
    }

    public function recentBatches(int $schoolID, int $limit = 10): Collection
    {
        return StudentMoveBatch::with('academicYear')
            ->where('schoolID', $schoolID)
            ->orderByDesc('batchID')
            ->limit($limit)
            ->get()
            ->map(function (StudentMoveBatch $batch) {
                return [
                    'batchID' => $batch->batchID,
                    'to_year' => $batch->academicYear->year_name ?? $batch->academicYear->year ?? '',
                    'moved_count' => $batch->moved_count,
                    'graduated_count' => $batch->graduated_count,
                    'status' => $batch->status,
                    'created_at' => optional($batch->created_at)->format('Y-m-d H:i'),
                    'can_undo' => $batch->status === 'Completed',
                    'notes' => $batch->notes,
                ];
            });
    }

    /**
     * Class progression rank for compare (higher = more advanced).
     */
    public function classRank(?string $className): ?int
    {
        $order = [
            'nursery' => 1,
            'baby_class' => 2,
            'middle_class' => 3,
            'top_class' => 4,
            'standard_1' => 5,
            'standard_2' => 6,
            'standard_3' => 7,
            'standard_4' => 8,
            'standard_5' => 9,
            'standard_6' => 10,
            'standard_7' => 11,
            'pre_form_one' => 19,
            'form_one' => 20,
            'form_1' => 20,
            'form_two' => 21,
            'form_2' => 21,
            'form_three' => 22,
            'form_3' => 22,
            'form_four' => 23,
            'form_4' => 23,
            'form_five' => 24,
            'form_5' => 24,
            'form_six' => 25,
            'form_6' => 25,
        ];

        $n = $this->normalizeClassName($className);
        return $order[$n] ?? null;
    }

    /**
     * Destinations for single-student move (internal = same class; external = other classes).
     */
    public function getSingleMoveDestinations(int $schoolID, int $studentID, string $type): array
    {
        $type = strtolower(trim($type));
        if (!in_array($type, ['internal', 'external'], true)) {
            return ['success' => false, 'message' => 'Invalid move type. Choose internal or external.'];
        }

        $enrollment = StudentEnrollment::with(['student', 'classModel', 'subclass.combie'])
            ->where('schoolID', $schoolID)
            ->where('studentID', $studentID)
            ->where('status', 'Active')
            ->orderByDesc('enrollmentID')
            ->first();

        if (!$enrollment || !$enrollment->student) {
            return ['success' => false, 'message' => 'No active enrollment found for this student.'];
        }

        $student = $enrollment->student;
        $currentClassID = (int) $enrollment->classID;
        $currentSubclassID = (int) $enrollment->subclassID;
        $currentClassName = $enrollment->classModel->class_name
            ?? optional($enrollment->subclass)->class->class_name
            ?? '';
        $currentSubclassName = optional($enrollment->subclass)->subclass_name ?? '';
        $currentLabel = $this->withCombieBrackets(
            trim($currentClassName . ' ' . $currentSubclassName),
            optional($enrollment->subclass)->combie
        );
        $currentCombieLabel = $this->formatCombieLabel(optional($enrollment->subclass)->combie);
        $currentRank = $this->classRank($currentClassName);

        $query = Subclass::with(['class', 'combie'])
            ->whereHas('class', function ($q) use ($schoolID) {
                $q->where('schoolID', $schoolID)->where('status', 'Active');
            })
            ->where(function ($q) {
                $q->where('status', 'Active')->orWhereNull('status');
            })
            ->where('subclassID', '!=', $currentSubclassID);

        if ($type === 'internal') {
            $query->where('classID', $currentClassID);
        } else {
            $query->where('classID', '!=', $currentClassID);
        }

        $destinations = $query->get()->map(function (Subclass $sub) use ($type, $currentRank) {
            $className = $sub->class->class_name ?? '';
            $combieLabel = $this->formatCombieLabel($sub->combie);
            $label = $this->withCombieBrackets(
                trim($className . ' ' . ($sub->subclass_name ?? '')),
                $sub->combie
            );
            $action = 'Internal';
            $actionLabel = 'Internal move';

            if ($type === 'external') {
                $destRank = $this->classRank($className);
                if ($currentRank !== null && $destRank !== null && $destRank < $currentRank) {
                    $action = 'Reverted';
                    $actionLabel = 'Revert student (back class)';
                } else {
                    $action = 'Moved';
                    $actionLabel = 'Move to higher / other class';
                }
            }

            return [
                'subclassID' => $sub->subclassID,
                'classID' => $sub->classID,
                'class_name' => $className,
                'subclass_name' => $sub->subclass_name,
                'combie' => $combieLabel,
                'label' => $label,
                'action' => $action,
                'action_label' => $actionLabel,
            ];
        })->sortBy('label')->values();

        // Group by class for external UI convenience
        $classes = $destinations->groupBy('classID')->map(function ($items, $classID) {
            $first = $items->first();
            return [
                'classID' => (int) $classID,
                'class_name' => $first['class_name'],
                'action' => $first['action'],
                'action_label' => $first['action_label'],
                'subclasses' => $items->values(),
            ];
        })->values();

        return [
            'success' => true,
            'type' => $type,
            'student' => [
                'studentID' => $student->studentID,
                'admission_number' => $student->admission_number,
                'full_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                'current_class' => $currentLabel,
                'current_combie' => $currentCombieLabel,
                'current_classID' => $currentClassID,
                'current_subclassID' => $currentSubclassID,
                'enrollmentID' => $enrollment->enrollmentID,
            ],
            'destinations' => $destinations,
            'classes' => $classes,
            'count' => $destinations->count(),
        ];
    }

    /**
     * Single-student internal/external move within the same academic year (history kept).
     */
    public function executeSingleMove(
        int $schoolID,
        int $studentID,
        int $newSubclassID,
        string $type,
        $movedBy = null
    ): array {
        $type = strtolower(trim($type));
        if (!in_array($type, ['internal', 'external'], true)) {
            return ['success' => false, 'message' => 'Invalid move type.'];
        }

        return DB::transaction(function () use ($schoolID, $studentID, $newSubclassID, $type, $movedBy) {
            $enrollment = StudentEnrollment::with(['student', 'classModel', 'subclass'])
                ->where('schoolID', $schoolID)
                ->where('studentID', $studentID)
                ->where('status', 'Active')
                ->orderByDesc('enrollmentID')
                ->lockForUpdate()
                ->first();

            if (!$enrollment || !$enrollment->student) {
                return ['success' => false, 'message' => 'No active enrollment found for this student.'];
            }

            $student = $enrollment->student;
            $newSubclass = Subclass::with('class')->find($newSubclassID);
            if (!$newSubclass || !$newSubclass->class || (int) $newSubclass->class->schoolID !== (int) $schoolID) {
                return ['success' => false, 'message' => 'Invalid destination subclass.'];
            }

            $oldClassID = (int) $enrollment->classID;
            $oldSubclassID = (int) $enrollment->subclassID;
            $newClassID = (int) $newSubclass->classID;

            if ($newSubclassID === $oldSubclassID) {
                return ['success' => false, 'message' => 'Student is already in the selected subclass.'];
            }

            if ($type === 'internal' && $newClassID !== $oldClassID) {
                return ['success' => false, 'message' => 'Internal move must stay within the same main class.'];
            }
            if ($type === 'external' && $newClassID === $oldClassID) {
                return ['success' => false, 'message' => 'External move must change the main class. Use Internal for stream change.'];
            }

            $oldClassName = $enrollment->classModel->class_name
                ?? optional($enrollment->subclass)->class->class_name
                ?? '';
            $oldSubclassName = optional($enrollment->subclass)->subclass_name ?? '';
            $oldLabel = trim($oldClassName . ' ' . $oldSubclassName);
            $newClassName = $newSubclass->class->class_name ?? '';
            $newLabel = trim($newClassName . ' ' . ($newSubclass->subclass_name ?? ''));

            $action = 'Internal';
            // Internal stream change is NOT school transfer - keep Pending.
            // Transferred promotion_status = left the school.
            $promotionStatus = 'Pending';
            if ($type === 'external') {
                $oldRank = $this->classRank($oldClassName);
                $newRank = $this->classRank($newClassName);
                if ($oldRank !== null && $newRank !== null && $newRank < $oldRank) {
                    $action = 'Reverted';
                    $promotionStatus = 'Repeated';
                } else {
                    $action = 'Moved';
                    $promotionStatus = 'Promoted';
                }
            }

            $oldStudentStatus = $student->status ?? 'Active';

            $enrollment->status = 'Completed';
            $enrollment->promotion_status = $promotionStatus;
            $enrollment->left_date = now()->toDateString();
            $enrollment->notes = trim(($enrollment->notes ? $enrollment->notes . ' | ' : '') . "Single {$type} move → {$newLabel}");
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
                'notes' => "Single {$type} move from {$oldLabel} ({$action})",
            ]);

            $student->old_subclassID = $oldSubclassID;
            $student->subclassID = $newSubclassID;
            if ($student->status !== 'Active') {
                $student->status = 'Active';
            }
            $student->save();

            $batch = StudentMoveBatch::create([
                'schoolID' => $schoolID,
                'to_academic_yearID' => $enrollment->academic_yearID,
                'moved_by' => $movedBy,
                'moved_count' => $action === 'Reverted' ? 0 : 1,
                'graduated_count' => 0,
                'status' => 'Completed',
                'notes' => "Single student {$type} move ({$action})",
            ]);

            StudentMoveBatchItem::create([
                'batchID' => $batch->batchID,
                'studentID' => $student->studentID,
                'old_enrollmentID' => $enrollment->enrollmentID,
                'new_enrollmentID' => $newEnrollment->enrollmentID,
                'action' => $action === 'Moved' ? 'Promoted' : ($action === 'Reverted' ? 'Reverted' : 'Internal'),
                'old_classID' => $oldClassID,
                'old_subclassID' => $oldSubclassID,
                'new_classID' => $newClassID,
                'new_subclassID' => $newSubclassID,
                'old_student_status' => $oldStudentStatus,
                'new_student_status' => 'Active',
            ]);

            return [
                'success' => true,
                'message' => $action === 'Reverted'
                    ? "Student reverted from {$oldLabel} to {$newLabel}."
                    : ($action === 'Internal'
                        ? "Student moved internally from {$oldLabel} to {$newLabel}."
                        : "Student moved from {$oldLabel} to {$newLabel}."),
                'action' => $action,
                'type' => $type,
                'batchID' => $batch->batchID,
                'old_class' => $oldLabel,
                'new_class' => $newLabel,
                'student_name' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                'admission_number' => $student->admission_number,
            ];
        });
    }
}
