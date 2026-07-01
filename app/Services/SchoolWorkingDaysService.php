<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Holiday;
use Carbon\Carbon;

class SchoolWorkingDaysService
{
    /**
     * Working days in range — same rules as admin calendar:
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

            if ($dayOfWeek !== 0 && $dayOfWeek !== 6
                && !in_array($dateStr, $context['holiday_dates'], true)
                && !in_array($dateStr, $context['non_working_event_dates'], true)) {
                $workingDates[] = $dateStr;
            }

            $current->addDay();
        }

        return $workingDates;
    }

    /**
     * Build holiday/event sets using the same approach as CalendarController::calculateMonthlyStatistics.
     *
     * @return array{holiday_dates: array<int, string>, non_working_event_dates: array<int, string>}
     */
    private function buildCalendarContext(int $schoolID, int $startYear, int $endYear): array
    {
        $holidayDates = [];

        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function ($query) use ($startYear, $endYear) {
                $query->whereYear('start_date', '<=', $endYear)
                    ->whereYear('end_date', '>=', $startYear);
            })
            ->get();

        foreach ($holidays as $holiday) {
            $current = Carbon::parse($holiday->start_date)->startOfDay();
            $end = Carbon::parse($holiday->end_date)->startOfDay();
            while ($current->lte($end)) {
                $holidayDates[] = $current->format('Y-m-d');
                $current->addDay();
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
            'non_working_event_dates' => $nonWorkingEventDates,
        ];
    }
}
