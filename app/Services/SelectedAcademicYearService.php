<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class SelectedAcademicYearService
{
    public const SESSION_O_LEVEL = 'selected_o_level_academic_yearID';
    public const SESSION_A_LEVEL = 'selected_a_level_academic_yearID';
    public const SESSION_PRIMARY = 'selected_academic_yearID';

    /**
     * Build header academic-year context for the current school session.
     */
    public function headerContext(?int $schoolID = null): array
    {
        $schoolID = $schoolID ?: (int) Session::get('schoolID');
        if (!$schoolID) {
            return $this->emptyContext();
        }

        $school = School::find($schoolID);
        if (!$school) {
            return $this->emptyContext();
        }

        $isSecondary = ($school->school_type ?? 'Secondary') === 'Secondary';
        $hasALevel = $isSecondary && $this->schoolHasALevelClasses($schoolID);

        $years = AcademicYear::where('schoolID', $schoolID)
            ->orderByDesc('is_current')
            ->orderByDesc('year')
            ->orderByDesc('academic_yearID')
            ->get();

        $oLevelYears = $years->filter(fn ($y) => !$this->isALevelYear($y))->values();
        $aLevelYears = $years->filter(fn ($y) => $this->isALevelYear($y))->values();

        $this->ensureDefaults($schoolID, $isSecondary, $hasALevel, $oLevelYears, $aLevelYears, $years);

        if (!$isSecondary) {
            $selectedId = (int) Session::get(self::SESSION_PRIMARY);
            $selected = $years->firstWhere('academic_yearID', $selectedId);

            return [
                'mode' => 'primary',
                'has_a_level' => false,
                'years' => $years,
                'o_level_years' => $years,
                'a_level_years' => collect(),
                'selected_academic_yearID' => $selectedId ?: null,
                'selected_o_level_academic_yearID' => null,
                'selected_a_level_academic_yearID' => null,
                'selected_year' => $selected,
                'selected_o_level_year' => null,
                'selected_a_level_year' => null,
                'selected_year_ids' => $selectedId ? [$selectedId] : [],
            ];
        }

        if (!$hasALevel) {
            $selectedId = (int) (Session::get(self::SESSION_O_LEVEL) ?: Session::get(self::SESSION_PRIMARY));
            $selected = $oLevelYears->firstWhere('academic_yearID', $selectedId)
                ?: $years->firstWhere('academic_yearID', $selectedId);

            return [
                'mode' => 'secondary_o_only',
                'has_a_level' => false,
                'years' => $years,
                'o_level_years' => $oLevelYears->isNotEmpty() ? $oLevelYears : $years,
                'a_level_years' => collect(),
                'selected_academic_yearID' => $selectedId ?: null,
                'selected_o_level_academic_yearID' => $selectedId ?: null,
                'selected_a_level_academic_yearID' => null,
                'selected_year' => $selected,
                'selected_o_level_year' => $selected,
                'selected_a_level_year' => null,
                'selected_year_ids' => $selectedId ? [$selectedId] : [],
            ];
        }

        $oId = (int) Session::get(self::SESSION_O_LEVEL);
        $aId = (int) Session::get(self::SESSION_A_LEVEL);
        $oSelected = $oLevelYears->firstWhere('academic_yearID', $oId);
        $aSelected = $aLevelYears->firstWhere('academic_yearID', $aId);
        $ids = array_values(array_filter([$oId ?: null, $aId ?: null]));

        return [
            'mode' => 'secondary_dual',
            'has_a_level' => true,
            'years' => $years,
            'o_level_years' => $oLevelYears,
            'a_level_years' => $aLevelYears,
            'selected_academic_yearID' => $oId ?: null,
            'selected_o_level_academic_yearID' => $oId ?: null,
            'selected_a_level_academic_yearID' => $aId ?: null,
            'selected_year' => $oSelected,
            'selected_o_level_year' => $oSelected,
            'selected_a_level_year' => $aSelected,
            'selected_year_ids' => $ids,
        ];
    }

    /**
     * Persist header selection(s) into session.
     */
    public function setSelection(int $schoolID, array $payload): array
    {
        $school = School::find($schoolID);
        if (!$school) {
            return ['success' => false, 'message' => 'School not found'];
        }

        $isSecondary = ($school->school_type ?? 'Secondary') === 'Secondary';
        $hasALevel = $isSecondary && $this->schoolHasALevelClasses($schoolID);

        if (!$isSecondary) {
            $yearID = (int) ($payload['academic_yearID'] ?? 0);
            if (!$this->belongsToSchool($yearID, $schoolID)) {
                return ['success' => false, 'message' => 'Invalid academic year'];
            }
            Session::put(self::SESSION_PRIMARY, $yearID);
            Session::forget([self::SESSION_O_LEVEL, self::SESSION_A_LEVEL]);
        } elseif (!$hasALevel) {
            $yearID = (int) ($payload['academic_yearID'] ?? $payload['o_level_academic_yearID'] ?? 0);
            if (!$this->belongsToSchool($yearID, $schoolID)) {
                return ['success' => false, 'message' => 'Invalid academic year'];
            }
            Session::put(self::SESSION_O_LEVEL, $yearID);
            Session::put(self::SESSION_PRIMARY, $yearID);
            Session::forget(self::SESSION_A_LEVEL);
        } else {
            if (array_key_exists('o_level_academic_yearID', $payload) && $payload['o_level_academic_yearID'] !== '') {
                $oId = (int) $payload['o_level_academic_yearID'];
                if (!$this->belongsToSchool($oId, $schoolID) || $this->isALevelYear(AcademicYear::find($oId))) {
                    return ['success' => false, 'message' => 'Invalid O-Level academic year'];
                }
                Session::put(self::SESSION_O_LEVEL, $oId);
                Session::put(self::SESSION_PRIMARY, $oId);
            }
            if (array_key_exists('a_level_academic_yearID', $payload) && $payload['a_level_academic_yearID'] !== '') {
                $aId = (int) $payload['a_level_academic_yearID'];
                if (!$this->belongsToSchool($aId, $schoolID) || !$this->isALevelYear(AcademicYear::find($aId))) {
                    return ['success' => false, 'message' => 'Invalid A-Level academic year'];
                }
                Session::put(self::SESSION_A_LEVEL, $aId);
            }
        }

        return [
            'success' => true,
            'message' => 'Academic year updated',
            'context' => $this->headerContext($schoolID),
        ];
    }

    /**
     * Selected academic_yearID list for filters (O and/or A).
     */
    public function selectedYearIds(?int $schoolID = null): array
    {
        return $this->headerContext($schoolID)['selected_year_ids'] ?? [];
    }

    /**
     * Resolve which selected year applies to a class (O vs A).
     */
    public function yearIdForClassName(?string $className, ?int $schoolID = null): ?int
    {
        $ctx = $this->headerContext($schoolID);
        if (($ctx['mode'] ?? '') === 'secondary_dual' && $this->isALevelClassName($className)) {
            return $ctx['selected_a_level_academic_yearID'] ?: null;
        }

        return $ctx['selected_o_level_academic_yearID']
            ?: $ctx['selected_academic_yearID']
            ?: null;
    }

    public function schoolHasALevelClasses(int $schoolID): bool
    {
        $classes = ClassModel::where('schoolID', $schoolID)
            ->where('status', 'Active')
            ->pluck('class_name');

        foreach ($classes as $name) {
            if ($this->isALevelClassName($name)) {
                return true;
            }
        }

        return false;
    }

    public function isALevelClassName(?string $className): bool
    {
        if (!$className) {
            return false;
        }

        $normalized = strtolower(preg_replace('/[\s\-]+/', '_', trim($className)));
        return in_array($normalized, ['form_five', 'form_six', 'form_5', 'form_6'], true)
            || str_contains($normalized, 'form_five')
            || str_contains($normalized, 'form_six');
    }

    public function isALevelYear(?AcademicYear $year): bool
    {
        if (!$year) {
            return false;
        }

        $name = (string) ($year->year_name ?? '');
        return str_contains($name, '/');
    }

    private function ensureDefaults(
        int $schoolID,
        bool $isSecondary,
        bool $hasALevel,
        Collection $oLevelYears,
        Collection $aLevelYears,
        Collection $allYears
    ): void {
        if (!$isSecondary) {
            $current = (int) Session::get(self::SESSION_PRIMARY);
            if ($current && $allYears->contains('academic_yearID', $current)) {
                return;
            }
            $fallback = $allYears->firstWhere('is_current', true) ?: $allYears->first();
            if ($fallback) {
                Session::put(self::SESSION_PRIMARY, (int) $fallback->academic_yearID);
            }
            return;
        }

        if (!$hasALevel) {
            $current = (int) (Session::get(self::SESSION_O_LEVEL) ?: Session::get(self::SESSION_PRIMARY));
            $pool = $oLevelYears->isNotEmpty() ? $oLevelYears : $allYears;
            if ($current && $pool->contains('academic_yearID', $current)) {
                return;
            }
            $fallback = $pool->firstWhere('is_current', true) ?: $pool->first();
            if ($fallback) {
                Session::put(self::SESSION_O_LEVEL, (int) $fallback->academic_yearID);
                Session::put(self::SESSION_PRIMARY, (int) $fallback->academic_yearID);
            }
            return;
        }

        $oCurrent = (int) Session::get(self::SESSION_O_LEVEL);
        if (!$oCurrent || !$oLevelYears->contains('academic_yearID', $oCurrent)) {
            $fallback = $oLevelYears->firstWhere('is_current', true) ?: $oLevelYears->first();
            if ($fallback) {
                Session::put(self::SESSION_O_LEVEL, (int) $fallback->academic_yearID);
                Session::put(self::SESSION_PRIMARY, (int) $fallback->academic_yearID);
            }
        }

        $aCurrent = (int) Session::get(self::SESSION_A_LEVEL);
        if (!$aCurrent || !$aLevelYears->contains('academic_yearID', $aCurrent)) {
            $fallback = $aLevelYears->firstWhere('is_current', true) ?: $aLevelYears->first();
            if ($fallback) {
                Session::put(self::SESSION_A_LEVEL, (int) $fallback->academic_yearID);
            }
        }
    }

    private function belongsToSchool(int $yearID, int $schoolID): bool
    {
        if ($yearID <= 0) {
            return false;
        }

        return AcademicYear::where('academic_yearID', $yearID)
            ->where('schoolID', $schoolID)
            ->exists();
    }

    private function emptyContext(): array
    {
        return [
            'mode' => 'none',
            'has_a_level' => false,
            'years' => collect(),
            'o_level_years' => collect(),
            'a_level_years' => collect(),
            'selected_academic_yearID' => null,
            'selected_o_level_academic_yearID' => null,
            'selected_a_level_academic_yearID' => null,
            'selected_year' => null,
            'selected_o_level_year' => null,
            'selected_a_level_year' => null,
            'selected_year_ids' => [],
        ];
    }
}
