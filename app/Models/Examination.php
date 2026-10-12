<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;

    protected $table = 'examinations';
    protected $primaryKey = 'examID';

    protected $fillable = [
        'exam_name',
        'exam_category',
        'term',
        'except_class_ids',
        'start_date',
        'end_date',
        'status',
        'approval_status',
        'rejection_reason',
        'exam_type',
        'student_shifting_status',
        'schoolID',
        'academic_yearID',
        'academic_year_ids',
        'year',
        'details',
        'created_by',
        'enter_result',
        'publish_result',
        'upload_paper',
        'use_paper_approval',
        'allow_no_format',
        'allow_no_paper',
        'track_school_calendar',
        'school_closing_date',
        'school_opening_date',
        'no_approval_required',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'school_closing_date' => 'date',
        'school_opening_date' => 'date',
        'year' => 'integer',
        'except_class_ids' => 'array',
        'academic_year_ids' => 'array',
        'track_school_calendar' => 'boolean',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'schoolID', 'schoolID');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_yearID', 'academic_yearID');
    }

    /**
     * Linked academic years for this exam (O-Level and/or A-Level).
     * Ignores orphan IDs in academic_year_ids (deleted AYs) and falls back to academic_yearID.
     */
    public function linkedAcademicYearIds(): array
    {
        $ids = array_values(array_filter(array_map('intval', (array) ($this->academic_year_ids ?? []))));
        if ($this->academic_yearID) {
            $ids[] = (int) $this->academic_yearID;
        }
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return [];
        }

        // Drop deleted / missing academic years (e.g. stale JSON [17] after AY was removed)
        $existing = AcademicYear::whereIn('academic_yearID', $ids)
            ->pluck('academic_yearID')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($existing !== []) {
            return array_values(array_unique($existing));
        }

        return $this->academic_yearID ? [(int) $this->academic_yearID] : [];
    }

    /**
     * Display year for a class context:
     * Form 5 / Form 6 → A-Level year (e.g. 2026/2027)
     * Other classes → O-Level year (e.g. 2027)
     */
    public function displayYearLabelForClass(?string $className = null): string
    {
        $yearService = app(\App\Services\SelectedAcademicYearService::class);
        $wantALevel = $yearService->isALevelClassName($className);
        $linkedIds = $this->linkedAcademicYearIds();

        $years = $linkedIds !== []
            ? AcademicYear::whereIn('academic_yearID', $linkedIds)->get()
            : collect();

        if ($years->isNotEmpty()) {
            $pick = $years->first(function ($y) use ($yearService, $wantALevel) {
                return $wantALevel
                    ? $yearService->isALevelYear($y)
                    : ! $yearService->isALevelYear($y);
            });

            // Dual exam missing one level in academic_year_ids → fall back to header year for that level
            if (! $pick) {
                $headerId = $yearService->yearIdForClassName($className, (int) $this->schoolID);
                if ($headerId) {
                    $pick = AcademicYear::find($headerId);
                }
            }

            if ($pick) {
                return (string) ($pick->year_name ?: $pick->year);
            }
        }

        // No linked years: use header Active year for this class level
        $headerId = $yearService->yearIdForClassName($className, (int) $this->schoolID);
        if ($headerId) {
            $headerYear = AcademicYear::find($headerId);
            if ($headerYear) {
                return (string) ($headerYear->year_name ?: $headerYear->year);
            }
        }

        if ($this->relationLoaded('academicYear') && $this->academicYear) {
            return (string) ($this->academicYear->year_name ?: $this->academicYear->year);
        }

        if ($this->academic_yearID) {
            $ay = AcademicYear::find($this->academic_yearID);
            if ($ay) {
                return (string) ($ay->year_name ?: $ay->year);
            }
        }

        return (string) ($this->year ?? '');
    }

    /**
     * All linked year labels (for exam lists without a class context).
     * Dual schools (Form 5/6): "O-Level 2027 · A-Level 2026/2027"
     * O-only / no Form 5-6: plain year like header (e.g. "2026") - no O/A prefixes.
     */
    public function displayYearLabelAll(bool $withLevelPrefix = true): string
    {
        $yearService = app(\App\Services\SelectedAcademicYearService::class);
        $schoolID = (int) ($this->schoolID ?? 0);
        $hasALevel = $schoolID > 0 && $yearService->schoolHasALevelClasses($schoolID);

        $linkedIds = $this->linkedAcademicYearIds();
        if ($linkedIds === []) {
            return (string) ($this->year ?? '');
        }

        $years = AcademicYear::whereIn('academic_yearID', $linkedIds)->get();
        if ($years->isEmpty()) {
            // Last resort: primary FK then legacy calendar year column
            if ($this->academic_yearID) {
                $ay = AcademicYear::find($this->academic_yearID);
                if ($ay) {
                    return (string) ($ay->year_name ?: $ay->year);
                }
            }
            return (string) ($this->year ?? '');
        }

        // School has no Form Five / Six → show one label like header (no O-Level / A-Level)
        if (!$hasALevel) {
            $pick = $years->first(fn ($y) => ! $yearService->isALevelYear($y));
            if (!$pick && $this->academic_yearID) {
                $pick = $years->firstWhere('academic_yearID', (int) $this->academic_yearID);
            }
            if (!$pick) {
                $pick = $years->first();
            }

            return (string) ($pick->year_name ?: $pick->year ?: $this->year);
        }

        $o = $years->first(fn ($y) => ! $yearService->isALevelYear($y));
        $a = $years->first(fn ($y) => $yearService->isALevelYear($y));
        $parts = [];

        if ($o) {
            $label = (string) ($o->year_name ?: $o->year);
            $parts[] = $withLevelPrefix && $a ? 'O-Level '.$label : $label;
        }
        if ($a) {
            $label = (string) ($a->year_name ?: $a->year);
            $parts[] = $withLevelPrefix && $o ? 'A-Level '.$label : $label;
        }

        if ($parts === []) {
            return (string) ($years->first()->year_name ?: $years->first()->year ?: $this->year);
        }

        return implode(' · ', $parts);
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'examID', 'examID');
    }

    public function resultApprovals()
    {
        return $this->hasMany(ResultApproval::class, 'examID', 'examID')->orderBy('approval_order');
    }

    public function examPapers()
    {
        return $this->hasMany(ExamPaper::class, 'examID', 'examID');
    }

    public function paperApprovalChains()
    {
        return $this->hasMany(PaperApprovalChain::class, 'examID', 'examID')->orderBy('approval_order');
    }
}
