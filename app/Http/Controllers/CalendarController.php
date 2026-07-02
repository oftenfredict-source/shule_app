<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Holiday;
use App\Models\Event;
use App\Models\School;
use App\Services\TanzaniaHolidaysService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class CalendarController extends Controller
{
    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Holiday> $holidays
     * @return array{holiday_dates: array<int,string>, holiday_working_dates: array<int,string>}
     */
    private function buildHolidayDateSets($holidays): array
    {
        $holidayDates = [];
        $holidayWorkingDates = [];

        foreach ($holidays as $holiday) {
            $current = Carbon::parse($holiday->start_date)->startOfDay();
            $end = Carbon::parse($holiday->end_date)->startOfDay();

            $workingAllowance = 0;
            if (!empty($holiday->has_working_days) && (int) ($holiday->working_days_count ?? 0) > 0) {
                $workingAllowance = (int) $holiday->working_days_count;
            }

            while ($current->lte($end)) {
                $dateStr = $current->format('Y-m-d');
                $holidayDates[] = $dateStr;

                // If holiday has working days, count the first N weekdays in this range as working days.
                if ($workingAllowance > 0 && $current->isWeekday()) {
                    $holidayWorkingDates[] = $dateStr;
                    $workingAllowance--;
                }

                $current->addDay();
            }
        }

        return [
            'holiday_dates' => array_values(array_unique($holidayDates)),
            'holiday_working_dates' => array_values(array_unique($holidayWorkingDates)),
        ];
    }

    /**
     * Admin Calendar View - Full year calendar with CRUD operations
     */
    public function adminCalendar()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        $calendarPermissions = ['calendar_create', 'calendar_update', 'calendar_delete', 'calendar_read_only'];
        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasAnyPermission($calendarPermissions))) {
            return redirect()->back()->with('error', 'Access denied');
        }

        $currentYear = request()->input('year', date('Y'));

        // Get all holidays for the year
        $holidays = Holiday::where('schoolID', $schoolID)
            ->whereYear('start_date', '<=', $currentYear)
            ->whereYear('end_date', '>=', $currentYear)
            ->orderBy('start_date')
            ->get();

        // Get all events for the year
        $events = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $currentYear)
            ->orderBy('event_date')
            ->get();

        // Get auto-detected Tanzania holidays
        $autoHolidays = TanzaniaHolidaysService::getHolidaysForYear($currentYear);

        // Calculate statistics
        $stats = $this->calculateYearStatistics($schoolID, $currentYear);

        // Calculate monthly working days
        $monthlyStats = $this->calculateMonthlyStatistics($schoolID, $currentYear, $holidays, $events);

        return view('Admin.calendar', compact('holidays', 'events', 'currentYear', 'stats', 'monthlyStats', 'autoHolidays'));
    }

    /**
     * Teacher Calendar View - View-only with summary
     */
    public function teacherCalendar()
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Teacher') {
            return redirect()->route('login')->with('error', 'Access denied');
        }

        $currentYear = request()->input('year', date('Y'));

        // Get all holidays for the year
        $holidays = Holiday::where('schoolID', $schoolID)
            ->whereYear('start_date', '<=', $currentYear)
            ->whereYear('end_date', '>=', $currentYear)
            ->orderBy('start_date')
            ->get();

        // Get all events for the year
        $events = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $currentYear)
            ->orderBy('event_date')
            ->get();

        // Get auto-detected Tanzania holidays
        $autoHolidays = TanzaniaHolidaysService::getHolidaysForYear($currentYear);

        // Calculate statistics
        $stats = $this->calculateYearStatistics($schoolID, $currentYear);

        // Calculate monthly working days
        $monthlyStats = $this->calculateMonthlyStatistics($schoolID, $currentYear, $holidays, $events);

        return view('Teacher.calendar', compact('holidays', 'events', 'currentYear', 'stats', 'monthlyStats', 'autoHolidays'));
    }

    /**
     * Store Holiday (Admin only)
     */
    public function storeHoliday(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasPermission('calendar_create'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'holiday_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'type' => 'required|in:Public Holiday,School Holiday,Other',
            'has_working_days' => 'nullable|boolean',
            'working_days_count' => 'nullable|integer|min:0',
        ]);

        try {
            $holiday = Holiday::create([
                'schoolID' => $schoolID,
                'holiday_name' => $request->holiday_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'type' => $request->type,
                'has_working_days' => (bool) $request->input('has_working_days', false),
                'working_days_count' => $request->input('has_working_days') ? (int) $request->input('working_days_count', 0) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Holiday added successfully',
                'holiday' => $holiday
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add holiday: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Holiday (Admin only)
     */
    public function getHoliday($holidayID)
    {
        $schoolID = Session::get('schoolID');

        $holiday = Holiday::where('holidayID', $holidayID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$holiday) {
            return response()->json(['error' => 'Holiday not found'], 404);
        }

        return response()->json($holiday);
    }

    /**
     * Store Bulk Holidays (Admin only)
     */
    public function storeBulkHolidays(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || $userType !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'holidays' => 'required|array|min:1',
            'holidays.*.holiday_name' => 'required|string|max:255',
            'holidays.*.start_date' => 'required|date',
            'holidays.*.end_date' => 'required|date|after_or_equal:holidays.*.start_date',
            'holidays.*.type' => 'required|in:Public Holiday,School Holiday,Other',
            'holidays.*.description' => 'nullable|string'
        ]);

        try {
            $created = [];
            $errors = [];

            foreach ($request->holidays as $index => $holidayData) {
                try {
                    $holiday = Holiday::create([
                        'schoolID' => $schoolID,
                        'holiday_name' => $holidayData['holiday_name'],
                        'start_date' => $holidayData['start_date'],
                        'end_date' => $holidayData['end_date'],
                        'description' => $holidayData['description'] ?? null,
                        'type' => $holidayData['type']
                    ]);
                    $created[] = $holiday->holiday_name;
                } catch (\Exception $e) {
                    $errors[] = "Holiday #" . ($index + 1) . " (" . ($holidayData['holiday_name'] ?? 'Unknown') . "): " . $e->getMessage();
                }
            }

            if (count($created) > 0) {
                $message = count($created) . " holiday(s) added successfully";
                if (count($errors) > 0) {
                    $message .= ". " . count($errors) . " error(s) occurred.";
                }
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'created' => $created,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to add holidays: ' . implode(', ', $errors)
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add holidays: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Holiday (Admin only)
     */
    public function updateHoliday(Request $request, $holidayID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasPermission('calendar_update'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $holiday = Holiday::where('holidayID', $holidayID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$holiday) {
            return response()->json(['error' => 'Holiday not found'], 404);
        }

        $request->validate([
            'holiday_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'type' => 'required|in:Public Holiday,School Holiday,Other',
            'has_working_days' => 'nullable|boolean',
            'working_days_count' => 'nullable|integer|min:0',
        ]);

        try {
            $holiday->update([
                'holiday_name' => $request->holiday_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'type' => $request->type,
                'has_working_days' => (bool) $request->input('has_working_days', false),
                'working_days_count' => $request->input('has_working_days') ? (int) $request->input('working_days_count', 0) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Holiday updated successfully',
                'holiday' => $holiday
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update holiday: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Holiday (Admin only)
     */
    public function deleteHoliday($holidayID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasPermission('calendar_delete'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $holiday = Holiday::where('holidayID', $holidayID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$holiday) {
            return response()->json(['error' => 'Holiday not found'], 404);
        }

        try {
            $holiday->delete();
            return response()->json([
                'success' => true,
                'message' => 'Holiday deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete holiday: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store Event (Admin only)
     */
    public function storeEvent(Request $request)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasPermission('calendar_create'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
            'type' => 'required|in:School Event,Sports,Academic,Cultural,Other',
            'is_non_working_day' => 'boolean'
        ]);

        try {
            $event = Event::create([
                'schoolID' => $schoolID,
                'event_name' => $request->event_name,
                'event_date' => $request->event_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description,
                'type' => $request->type,
                'is_non_working_day' => $request->is_non_working_day ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event added successfully',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Event (Admin only)
     */
    public function getEvent($eventID)
    {
        $schoolID = Session::get('schoolID');

        $event = Event::where('eventID', $eventID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json($event);
    }

    /**
     * Update Event (Admin only)
     */
    public function updateEvent(Request $request, $eventID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasPermission('calendar_update'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = Event::where('eventID', $eventID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
            'type' => 'required|in:School Event,Sports,Academic,Cultural,Other',
            'is_non_working_day' => 'boolean'
        ]);

        try {
            $event->update($request->only([
                'event_name', 'event_date', 'start_time', 'end_time',
                'description', 'type', 'is_non_working_day'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete Event (Admin only)
     */
    public function deleteEvent($eventID)
    {
        $userType = Session::get('user_type');
        $schoolID = Session::get('schoolID');

        if (!$schoolID || ($userType !== 'Admin' && ! $this->staffHasPermission('calendar_delete'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = Event::where('eventID', $eventID)
            ->where('schoolID', $schoolID)
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        try {
            $event->delete();
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Calculate Year Statistics
     */
    private function calculateYearStatistics($schoolID, $year)
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        // Get all manual holidays for the year
        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        // Get auto-detected Tanzania holidays
        $autoHolidays = TanzaniaHolidaysService::getHolidaysForYear($year);

        // Get all non-working events
        $nonWorkingEvents = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $year)
            ->where('is_non_working_day', true)
            ->get();

        // Calculate total days in year (exact integer)
        $totalDays = (int)($startDate->diffInDays($endDate) + 1);

        // Get all holiday dates + holiday working-days overrides (manual holidays)
        $dateSets = $this->buildHolidayDateSets($holidays);
        $holidayDates = $dateSets['holiday_dates'];
        $holidayWorkingDates = $dateSets['holiday_working_dates'];

        // Add auto-detected holidays
        foreach ($autoHolidays as $autoHoliday) {
            $holidayDates[] = $autoHoliday['date']->format('Y-m-d');
        }

        $holidayDates = array_unique($holidayDates);

        // Get non-working event dates
        $nonWorkingEventDates = $nonWorkingEvents->pluck('event_date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->unique()
            ->toArray();

        // Calculate working days (excluding weekends, holidays, and non-working events)
        $workingDays = 0;
        $weekendDays = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dayOfWeek = $current->dayOfWeek; // 0 = Sunday, 6 = Saturday

            if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                $weekendDays++;
            } elseif ((!in_array($dateStr, $holidayDates) || in_array($dateStr, $holidayWorkingDates))
                && !in_array($dateStr, $nonWorkingEventDates)) {
                $workingDays++;
            }
            $current->addDay();
        }

        // Calculate total holidays count
        $totalHolidays = count($holidayDates);

        // Calculate total events count
        $totalEvents = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $year)
            ->count();

        // Calculate teacher sessions per year (assuming 5 sessions per week)
        // This can be customized based on school schedule
        $sessionsPerWeek = 5;
        $weeksInYear = floor($workingDays / 5); // Approximate weeks
        $totalSessions = $weeksInYear * $sessionsPerWeek;

        return (object) [
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'weekend_days' => $weekendDays,
            'holiday_days' => $totalHolidays,
            'event_days' => $totalEvents,
            'non_working_event_days' => count($nonWorkingEventDates),
            'total_sessions' => $totalSessions,
            'year' => $year
        ];
    }

    /**
     * Calculate Monthly Statistics - Working days per month
     */
    private function calculateMonthlyStatistics($schoolID, $year, $holidays, $events)
    {
        $monthlyStats = [];

        // Get all holiday dates + working overrides from manual holidays
        $dateSets = $this->buildHolidayDateSets($holidays);
        $holidayDates = $dateSets['holiday_dates'];
        $holidayWorkingDates = $dateSets['holiday_working_dates'];

        // Add auto-detected holidays
        $autoHolidays = TanzaniaHolidaysService::getHolidaysForYear($year);
        foreach ($autoHolidays as $autoHoliday) {
            $holidayDates[] = $autoHoliday['date']->format('Y-m-d');
        }

        $holidayDates = array_unique($holidayDates);

        // Get non-working event dates
        $nonWorkingEventDates = $events->where('is_non_working_day', true)
            ->pluck('event_date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->unique()
            ->toArray();

        // Calculate for each month
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

            $workingDays = 0;
            $weekendDays = 0;
            $holidayDays = 0;
            $current = $startDate->copy();

            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $dayOfWeek = $current->dayOfWeek; // 0 = Sunday, 6 = Saturday

                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                    $weekendDays++;
                } elseif (in_array($dateStr, $holidayDates) && !in_array($dateStr, $holidayWorkingDates)) {
                    $holidayDays++;
                } elseif (in_array($dateStr, $nonWorkingEventDates)) {
                    $holidayDays++;
                } else {
                    $workingDays++;
                }
                $current->addDay();
            }

            $monthlyStats[$month] = [
                'working_days' => $workingDays,
                'weekend_days' => $weekendDays,
                'holiday_days' => $holidayDays,
                'total_days' => (int)($startDate->diffInDays($endDate) + 1)
            ];
        }

        return $monthlyStats;
    }

    /**
     * Get Calendar Data (AJAX) - Returns holidays and events for a specific month
     */
    public function getCalendarData(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

        $holidays = Holiday::where('schoolID', $schoolID)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        $events = Event::where('schoolID', $schoolID)
            ->whereBetween('event_date', [$startDate, $endDate])
            ->get();

        return response()->json([
            'success' => true,
            'holidays' => $holidays,
            'events' => $events
        ]);
    }

    /**
     * Export Calendar to PDF
     */
    public function exportCalendarPdf(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login');
        }

        $currentYear = $request->input('year', date('Y'));
        $school = School::find($schoolID);

        // Get all holidays for the year
        $holidays = Holiday::where('schoolID', $schoolID)
            ->whereYear('start_date', '<=', $currentYear)
            ->whereYear('end_date', '>=', $currentYear)
            ->orderBy('start_date')
            ->get();

        // Get all events for the year
        $events = Event::where('schoolID', $schoolID)
            ->whereYear('event_date', $currentYear)
            ->orderBy('event_date')
            ->get();

        // Get auto-detected Tanzania holidays
        $autoHolidays = TanzaniaHolidaysService::getHolidaysForYear($currentYear);

        // Calculate statistics
        $stats = $this->calculateYearStatistics($schoolID, $currentYear);

        // Calculate monthly working days
        $monthlyStats = $this->calculateMonthlyStatistics($schoolID, $currentYear, $holidays, $events);

        $data = [
            'school' => $school,
            'currentYear' => $currentYear,
            'holidays' => $holidays,
            'events' => $events,
            'autoHolidays' => $autoHolidays,
            'stats' => $stats,
            'monthlyStats' => $monthlyStats,
            'title' => 'School Calendar - ' . $currentYear
        ];

        $pdf = PDF::loadView('Admin.pdf.calendar_pdf', $data);
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => base_path(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'School_Calendar_' . $currentYear . '_' . date('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }
}
