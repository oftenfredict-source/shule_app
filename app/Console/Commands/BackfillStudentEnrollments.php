<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillStudentEnrollments extends Command
{
    protected $signature = 'students:backfill-enrollments
                            {--school= : Limit to one schoolID}
                            {--dry-run : Preview only, do not write}
                            {--force : Recreate enrollment if student already has one for that year+subclass}';

    protected $description = 'Backfill student_enrollments for existing students (O-Level/Primary -> 2026, A-Level -> 2025/2026)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $schoolFilter = $this->option('school');

        $schoolsQuery = School::query()->orderBy('schoolID');
        if ($schoolFilter) {
            $schoolsQuery->where('schoolID', (int) $schoolFilter);
        }

        $schools = $schoolsQuery->get();
        if ($schools->isEmpty()) {
            $this->warn('No schools found.');
            return self::SUCCESS;
        }

        $totals = [
            'students' => 0,
            'created' => 0,
            'skipped' => 0,
            'missing_subclass' => 0,
            'years_created' => 0,
        ];

        foreach ($schools as $school) {
            $this->info('----------------------------------------');
            $this->info("School #{$school->schoolID}: {$school->school_name} ({$school->school_type})");

            $isSecondary = strcasecmp((string) $school->school_type, 'Secondary') === 0;

            $oYear = $this->ensureAcademicYear($school->schoolID, '2026', 2026, '2026-01-01', '2026-12-31', true, $dryRun, $totals);
            $aYear = null;
            if ($isSecondary) {
                $aYear = $this->ensureAcademicYear($school->schoolID, '2025/2026', 2025, '2025-01-01', '2026-12-31', true, $dryRun, $totals);
            }

            $students = Student::with(['subclass.class'])
                ->where('schoolID', $school->schoolID)
                ->whereNotNull('subclassID')
                ->orderBy('studentID')
                ->get();

            $this->line("Students with subclass: {$students->count()}");

            foreach ($students as $student) {
                $totals['students']++;

                $subclass = $student->subclass;
                if (!$subclass || !$subclass->class) {
                    $totals['missing_subclass']++;
                    $this->warn("  Skip student {$student->studentID}: missing subclass/class");
                    continue;
                }

                $className = (string) ($subclass->class->class_name ?? '');
                $isALevel = $isSecondary && $this->isALevelClass($className);
                $academicYear = $isALevel ? $aYear : $oYear;

                if (!$academicYear) {
                    $totals['skipped']++;
                    $this->warn("  Skip student {$student->studentID}: academic year missing");
                    continue;
                }

                $existing = StudentEnrollment::where('studentID', $student->studentID)
                    ->where('academic_yearID', $academicYear->academic_yearID)
                    ->where('subclassID', $subclass->subclassID)
                    ->first();

                if ($existing && !$force) {
                    $totals['skipped']++;
                    continue;
                }

                [$enrollmentStatus, $promotionStatus] = $this->mapStudentStatus((string) $student->status);

                $payload = [
                    'studentID' => $student->studentID,
                    'schoolID' => $school->schoolID,
                    'academic_yearID' => $academicYear->academic_yearID,
                    'classID' => $subclass->classID,
                    'subclassID' => $subclass->subclassID,
                    'roll_number' => null,
                    'status' => $enrollmentStatus,
                    'promotion_status' => $promotionStatus,
                    'joined_date' => $student->admission_date,
                    'left_date' => null,
                    'notes' => 'Backfilled from students table',
                    'updated_at' => now(),
                ];

                if ($dryRun) {
                    $level = $isALevel ? 'A-Level' : ($isSecondary ? 'O-Level' : 'Primary');
                    $this->line("  [dry-run] student {$student->studentID} -> {$academicYear->year_name} | {$level} | class {$subclass->classID}/{$subclass->subclassID}");
                    $totals['created']++;
                    continue;
                }

                if ($existing && $force) {
                    $existing->fill($payload);
                    $existing->save();
                } else {
                    $payload['created_at'] = now();
                    StudentEnrollment::create($payload);
                }

                $totals['created']++;
            }
        }

        $this->info('----------------------------------------');
        $this->info('Backfill summary');
        $this->table(
            ['Metric', 'Count'],
            collect($totals)->map(fn ($v, $k) => [$k, $v])->values()->all()
        );

        if ($dryRun) {
            $this->comment('Dry-run only. Re-run without --dry-run to write data.');
        }

        return self::SUCCESS;
    }

    private function ensureAcademicYear(
        int $schoolID,
        string $yearName,
        int $year,
        string $startDate,
        string $endDate,
        bool $isCurrent,
        bool $dryRun,
        array &$totals
    ): ?AcademicYear {
        $existing = AcademicYear::where('schoolID', $schoolID)
            ->where('year_name', $yearName)
            ->first();

        if ($existing) {
            if (!$dryRun && $isCurrent && !$existing->is_current) {
                // Keep O/A current rules: only flip within same level group
                if (str_contains($yearName, '/')) {
                    AcademicYear::where('schoolID', $schoolID)
                        ->where('year_name', 'like', '%/%')
                        ->update(['is_current' => false]);
                } else {
                    AcademicYear::where('schoolID', $schoolID)
                        ->where('year_name', 'not like', '%/%')
                        ->update(['is_current' => false]);
                }
                $existing->is_current = true;
                $existing->status = 'Active';
                $existing->save();
            }
            return $existing;
        }

        if ($dryRun) {
            $this->line("  [dry-run] create academic year {$yearName}");
            $totals['years_created']++;
            $fake = new AcademicYear([
                'schoolID' => $schoolID,
                'year' => $year,
                'year_name' => $yearName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Active',
                'is_current' => $isCurrent,
            ]);
            $fake->academic_yearID = 0;
            return $fake;
        }

        DB::transaction(function () use ($schoolID, $yearName, $year, $startDate, $endDate, $isCurrent) {
            if ($isCurrent) {
                if (str_contains($yearName, '/')) {
                    AcademicYear::where('schoolID', $schoolID)
                        ->where('year_name', 'like', '%/%')
                        ->update(['is_current' => false]);
                } else {
                    AcademicYear::where('schoolID', $schoolID)
                        ->where('year_name', 'not like', '%/%')
                        ->update(['is_current' => false]);
                }
            }
        });

        $created = AcademicYear::create([
            'schoolID' => $schoolID,
            'year' => $year,
            'year_name' => $yearName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'Active',
            'is_current' => $isCurrent,
            'notes' => 'Auto-created during enrollment backfill',
        ]);

        $totals['years_created']++;
        $this->line("  Created academic year {$yearName} (#{$created->academic_yearID})");

        return $created;
    }

    private function isALevelClass(string $className): bool
    {
        $normalized = strtolower(trim($className));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        return (bool) preg_match('/form_?(5|five|6|six)\b/', $normalized)
            || in_array($normalized, ['form_five', 'form_six', 'form_5', 'form_6'], true);
    }

    private function mapStudentStatus(string $studentStatus): array
    {
        return match ($studentStatus) {
            'Graduated' => ['Completed', 'Graduated'],
            'Inactive' => ['Dropped', 'Pending'],
            'Transferred' => ['Active', 'Pending'], // still placed in a subclass in-school
            default => ['Active', 'Pending'], // Active, Applied, etc.
        };
    }
}
