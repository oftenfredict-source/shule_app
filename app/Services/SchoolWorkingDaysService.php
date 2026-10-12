<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Holiday;
use Carbon\Carbon;

class SchoolWorkingDaysService
{
    /**
     * Working days in range - same rules as admin calendar:
     * exclude weekends, school holidays, Tanzania public holidays, non-working events.
     */
    public function getWorkingDaysCount(int $schoolID, Carbon $startDate, Carbon $endDate): int
    {
        return count($this->getWorkingDates($schoolID, $startDate, $endDate));
    }

    /**
     * Full calendar month working days (matches /admin/calendar month card).
     */
    public function getMonthWorkingDaysCount(int $schoolID, int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();

        return $this->getWorkingDaysCount($schoolID, $start, $end);
    }

    /**
     * Full calendar year working days (matches /admin/calendar year stat).
     */
    public function getYearWorkingDaysCount(int $schoolID, int $year): int
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->startOfDay();

        return $this->getWorkingDaysCount($schoolID, $start, $end);
    }

    /**
     * Human-readable reason when a date is not a working day, or null if it is working.
     */
    public function getNonWorkingReason(int $schoolID, Carbon $date): ?string
    {
        $day = $date->copy()->startOfDay();
        $dayOfWeek = $day->dayOfWeek;

        if ($dayOfWeek === 0) {
            return 'Jumapili (mapumziko)';
        }
        if ($dayOfWeek === 6) {
            return 'Jumamosi (mapumziko)';
        }

        $context = $this->buildCalendarContext($schoolID, (int) $day->year, (int) $day->year);
        $dateStr = $day->format('Y-m-d');

        if (in_array($dateStr, $context['non_working_event_dates'], true)) {
            return 'Siku ya tukio (hakuna masomo)';
        }

        if (in_array($dateStr, $context['holiday_working_dates'], true)) {
            return null;
        }

        if (in_array($dateStr, $context['holiday_dates'], true)) {
            $schoolHoliday = Holiday::where('schoolID', $schoolID)
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->first();

            if ($schoolHoliday && $schoolHoliday->holiday_name) {
                return $schoolHoliday->holiday_name;
            }

            foreach (TanzaniaHolidaysService::getHolidaysForYear((int) $day->year) as $autoHoliday) {
                if ($autoHoliday['date']->format('Y-m-d') === $dateStr) {
                    return $autoHoliday['name'];
                }
            }

            return 'Siku ya mapumziko';
        }

        return null;
    }

    /**
     * @return array<int, string> List of Y-m-d working dates
     */
    public function getWorkingDates(int $schoolID, Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        if ($end->lt($start)) {
            return [];
        }

        $context = $this->buildCalendarContext($schoolID, (int) $start->year, (int) $end->year);

        $workingDates = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $dayOfWeek = $current->dayOfWeek;

            $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;
            $isHoliday = in_array($dateStr, $context['holiday_dates'], true);
            $isHolidayWorkingOverride = in_array($dateStr, $context['holiday_working_dates'], true);
            $isNonWorkingEvent = in_array($dateStr, $context['non_working_event_dates'], true);

            if ($dayOfWeek !== 0 && $dayOfWeek !== 6
                && (!$isHoliday || $isHolidayWorkingOverride)
                && !$isNonWorkingEvent) {
                $workingDates[] = $dateStr;
            }

            $current->addDay();
        }

        return $workingDates;
    }

    /**
     * Build holiday/event sets using the same approach as CalendarController::calculateMonthlyStatistics.
     *
     * holiday_working_dates: dates within a holiday that should still count as working days
     *
     * @return array{holiday_dates: array<int, string>, holiday_working_dates: array<int, string>, non_working_event_dates: array<int, string>}
     */
    private function buildCalendarContext(int $schoolID, int $startYear, int $endYear): array
    {
        $holidayDates = [];
        $holidayWorkingDates = [];

        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function ($query) use ($startYear, $endYear) {
                $query->whereYear('start_date', '<=', $endYear)
                    ->whereYear('end_date', '>=', $startYear);
            })
            ->get();

        // Clamp iteration to the requested year range so malformed holiday
        // dates (e.g. NULL or far-future end_date) cannot trigger a runaway
        // loop that exhausts memory.
        $rangeStart = Carbon::create($startYear, 1, 1)->startOfDay();
        $rangeEnd = Carbon::create($endYear, 12, 31)->startOfDay();

        foreach ($holidays as $holiday) {
            try {
                $current = Carbon::parse($holiday->start_date)->startOfDay();
                $end = Carbon::parse($holiday->end_date)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }

            if ($end->lt($current)) {
                continue;
            }

            $workingAllowance = 0;
            if (!empty($holiday->has_working_days) && (int) ($holiday->working_days_count ?? 0) > 0) {
                $workingAllowance = (int) $holiday->working_days_count;
            }

            // Apply the working-day allowance from the holiday's real start,
            // but stop scanning once we pass the requested range.
            $allowanceCursor = $current->copy();
            while ($workingAllowance > 0 && $allowanceCursor->lte($end) && $allowanceCursor->lt($rangeStart)) {
                if ($allowanceCursor->isWeekday()) {
                    $workingAllowance--;
                }
                $allowanceCursor->addDay();
            }

            $iterStart = $current->lt($rangeStart) ? $rangeStart->copy() : $current->copy();
            $iterEnd = $end->gt($rangeEnd) ? $rangeEnd->copy() : $end->copy();

            while ($iterStart->lte($iterEnd)) {
                $holidayDates[] = $iterStart->format('Y-m-d');

                // If holiday has working days, treat the first N weekdays as working.
                if ($workingAllowance > 0 && $iterStart->isWeekday()) {
                    $holidayWorkingDates[] = $iterStart->format('Y-m-d');
                    $workingAllowance--;
                }

                $iterStart->addDay();
            }
        }

        for ($year = $startYear; $year <= $endYear; $year++) {
            foreach (TanzaniaHolidaysService::getHolidaysForYear($year) as $autoHoliday) {
                $holidayDates[] = $autoHoliday['date']->format('Y-m-d');
            }
        }

        $nonWorkingEventDates = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', '>=', $startYear)
            ->whereYear('event_date', '<=', $endYear)
            ->where('is_non_working_day', true)
            ->pluck('event_date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        return [
            'holiday_dates' => array_values(array_unique($holidayDates)),
            'holiday_working_dates' => array_values(array_unique($holidayWorkingDates)),
            'non_working_event_dates' => $nonWorkingEventDates,
        ];
    }
}
