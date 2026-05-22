<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function staffHasPermission(string $permissionName): bool
    {
        if (\Illuminate\Support\Facades\Session::get('user_type') !== 'Staff') {
            return false;
        }

        $staffID = \Illuminate\Support\Facades\Session::get('staffID');
        if (!$staffID) {
            return false;
        }

        $professionId = \Illuminate\Support\Facades\DB::table('other_staff')
            ->where('id', $staffID)
            ->value('profession_id');

        if (!$professionId) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::table('staff_permissions')
            ->where('profession_id', $professionId)
            ->where('name', $permissionName)
            ->exists();
    }

    protected function staffHasAnyPermission(array $permissionNames): bool
    {
        foreach ($permissionNames as $permissionName) {
            if ($this->staffHasPermission($permissionName)) {
                return true;
            }
        }
        return false;
    }

    protected function calculateWorkingDays($startDate, $endDate)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $days = 0;

        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }

    /**
     * Format SMS message with school name prefix.
     * If school has senderID, no school name prefix is added.
     * If school has no senderID, school name is added as prefix.
     *
     * @param string $message The message content (without school prefix)
     * @param string|null $schoolName School name for fallback
     * @return string Formatted message
     */
    protected function formatSmsMessage(string $message, ?string $schoolName = null): string
    {
        $schoolID = \Illuminate\Support\Facades\Session::get('schoolID');
        $hasSenderID = false;

        if ($schoolID) {
            $school = \App\Models\School::find($schoolID);
            $hasSenderID = $school && !empty($school->senderID);
        }

        // If school has senderID, don't add school name prefix
        if ($hasSenderID) {
            return $message;
        }

        // Add school name prefix if no senderID
        $prefix = $schoolName ? "{$schoolName}. " : '';
        return $prefix . $message;
    }
}
