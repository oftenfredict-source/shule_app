<?php

namespace App\Support;

use App\Models\ParentModel;

/**
 * Tanzania phone helpers for parent reuse (link FK only — never rewrite parent phone).
 */
class ParentPhone
{
    /**
     * Normalize to 255XXXXXXXXX digits when possible.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        // 255 + 9 digits
        if (str_starts_with($digits, '255') && strlen($digits) >= 12) {
            return substr($digits, 0, 12);
        }

        // 0XXXXXXXXX (10 digits)
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '255' . substr($digits, 1);
        }

        // 9 digits local mobile
        if (strlen($digits) === 9 && preg_match('/^[67]/', $digits)) {
            return '255' . $digits;
        }

        return $digits;
    }

    /**
     * Common stored variants for the same number.
     *
     * @return list<string>
     */
    public static function variants(?string $phone): array
    {
        $normalized = self::normalize($phone);
        if (!$normalized) {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $normalized) ?: $normalized;
        $local9 = str_starts_with($digits, '255') && strlen($digits) >= 12
            ? substr($digits, 3)
            : (str_starts_with($digits, '0') && strlen($digits) === 10 ? substr($digits, 1) : $digits);

        return array_values(array_unique(array_filter([
            $normalized,
            $digits,
            '255' . $local9,
            '0' . $local9,
            $local9,
            '+255' . $local9,
            '+255' . $digits,
        ], static fn ($v) => $v !== null && $v !== '')));
    }

    public static function findInSchool(int $schoolID, ?string $phone): ?ParentModel
    {
        $variants = self::variants($phone);
        if ($variants === []) {
            return null;
        }

        return ParentModel::where('schoolID', $schoolID)
            ->whereIn('phone', $variants)
            ->first();
    }

    public static function findAnywhere(?string $phone, ?int $exceptSchoolID = null): ?ParentModel
    {
        $variants = self::variants($phone);
        if ($variants === []) {
            return null;
        }

        $q = ParentModel::whereIn('phone', $variants);
        if ($exceptSchoolID !== null) {
            $q->where('schoolID', '!=', $exceptSchoolID);
        }

        return $q->first();
    }
}
