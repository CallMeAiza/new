<?php

namespace App\Services;

use Carbon\Carbon;

class WeekCycleService
{
    /**
     * Get the current week cycle based on weekly date ranges
     *
     * Week 1 = Week Cycle 1 (Week 1 & 3)
     * Week 2 = Week Cycle 2 (Week 2 & 4)
     * Week 3 = Week Cycle 1 (Week 1 & 3)
     * Week 4 = Week Cycle 2 (Week 2 & 4)
     * Week 5+ = Week 4 (capped at week 4)
     *
     * @param Carbon|null $date Optional date, defaults to now()
     * @return int Week cycle (1 or 2)
     */
    public static function getCurrentWeekCycle($date = null)
    {
        $weekInfo = self::calculateWeekFromDateRange($date);
        $weekOfMonth = $weekInfo['week_of_month'];

        // Odd weeks (1, 3) = Cycle 1
        // Even weeks (2, 4) = Cycle 2
        return ($weekOfMonth % 2 === 1) ? 1 : 2;
    }

    /**
     * Get the current week of month using date range calculation
     *
     * @param Carbon|null $date Optional date, defaults to now()
     * @return int Week of month (1-4, capped at 4)
     */
    public static function getCurrentWeekOfMonth($date = null)
    {
        $weekInfo = self::calculateWeekFromDateRange($date);
        return $weekInfo['week_of_month'];
    }

    /**
     * Calculate week number based on weekly date ranges
     * This ensures proper week transitions and caps at week 4
     *
     * @param Carbon|null $date Optional date, defaults to now()
     * @return array Week calculation details
     */
    public static function calculateWeekFromDateRange($date = null)
    {
        $date = $date ?: now();

        // Simple approach: use the standard week of month but cap at 4
        $standardWeek = $date->weekOfMonth;

        // Cap at week 4 - any week 5+ becomes week 4
        $weekNumber = min($standardWeek, 4);

        // Calculate the actual week start (Monday) for this week
        $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY);

        // If the week starts in the previous month, adjust to show the current month's portion
        if ($startOfWeek->month !== $date->month) {
            $startOfWeek = $date->copy()->startOfMonth();
            // Find the first Monday of the month
            while ($startOfWeek->dayOfWeek !== Carbon::MONDAY) {
                $startOfWeek->addDay();
            }
            // Move to the appropriate week
            $startOfWeek->addWeeks($weekNumber - 1);
        }

        $endOfWeek = $startOfWeek->copy()->addDays(6);

        // If this pushes us into the next month, adjust the end date
        if ($endOfWeek->month !== $startOfWeek->month && $endOfWeek->month !== $date->month) {
            $endOfWeek = $date->copy()->endOfMonth();
        }

        return [
            'week_of_month' => $weekNumber,
            'week_start' => $startOfWeek,
            'week_end' => $endOfWeek,
            'date_range' => $startOfWeek->format('M j') . '-' . $endOfWeek->format('M j'),
            'standard_week' => $standardWeek
        ];
    }
    
    /**
     * Get week cycle information for display
     *
     * @param Carbon|null $date Optional date, defaults to now()
     * @return array Array with week info
     */
    public static function getWeekInfo($date = null)
    {
        $date = $date ?: now();
        $weekCalc = self::calculateWeekFromDateRange($date);
        $weekOfMonth = $weekCalc['week_of_month'];
        $weekCycle = self::getCurrentWeekCycle($date);

        return [
            'week_of_month' => $weekOfMonth,
            'week_cycle' => $weekCycle,
            'cycle_description' => $weekCycle === 1 ? 'Week 1 & 3' : 'Week 2 & 4',
            'cycle_short' => "Week {$weekCycle}",
            'week_name' => "Week {$weekOfMonth} ({$weekCalc['date_range']})",
            'date_range' => $weekCalc['date_range'],
            'week_start' => $weekCalc['week_start']->format('Y-m-d'),
            'week_end' => $weekCalc['week_end']->format('Y-m-d'),
            'current_day' => strtolower($date->format('l')),
            'current_day_name' => $date->format('l'),
            'formatted_date' => $date->format('Y-m-d'),
            'display_date' => $date->format('l, F j, Y'),
            'is_current_week' => true, // Always true for current date
            'month_name' => $date->format('F'),
            'year' => $date->format('Y')
        ];
    }
    
    /**
     * JavaScript function to calculate week cycle on frontend
     * Returns JavaScript code that can be embedded in views
     *
     * @return string JavaScript function
     */
    public static function getJavaScriptFunction()
    {
        return "
        /**
         * Calculate week from date range (matches backend logic)
         * @param {Date} date Optional date, defaults to now
         * @returns {Object} Week calculation details
         */
        function calculateWeekFromDateRange(date = null) {
            const currentDate = date || new Date();

            // Match PHP's weekOfMonth calculation
            // Get the first day of the month
            const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            
            // Calculate week number based on which Monday-Sunday week the date falls in
            const dayOfMonth = currentDate.getDate();
            
            // Simple calculation: divide day by 7 and round up
            const standardWeek = Math.ceil(dayOfMonth / 7);

            // Cap at week 4
            const weekNumber = Math.min(standardWeek, 4);

            // Calculate the actual week start (Monday) for this week
            const startOfWeek = new Date(currentDate);
            const dayOfWeek = startOfWeek.getDay();
            const daysToMonday = (dayOfWeek === 0) ? 6 : dayOfWeek - 1; // Sunday = 0, so 6 days back to Monday
            startOfWeek.setDate(startOfWeek.getDate() - daysToMonday);

            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(endOfWeek.getDate() + 6);

            // Format date range
            const startMonth = startOfWeek.toLocaleDateString('en-US', { month: 'short' });
            const startDay = startOfWeek.getDate();
            const endMonth = endOfWeek.toLocaleDateString('en-US', { month: 'short' });
            const endDay = endOfWeek.getDate();
            const dateRange = startMonth + ' ' + startDay + '-' + endMonth + ' ' + endDay;

            return {
                weekOfMonth: weekNumber,
                weekStart: startOfWeek,
                weekEnd: endOfWeek,
                dateRange: dateRange,
                standardWeek: standardWeek
            };
        }

        /**
         * Calculate current week cycle consistently with backend
         * @param {Date} date Optional date, defaults to now
         * @returns {Object} Week cycle information
         */
        function getCurrentWeekCycle(date = null) {
            const now = date || new Date();
            const weekCalc = calculateWeekFromDateRange(now);
            const weekOfMonth = weekCalc.weekOfMonth;

            // Odd weeks = 1, Even weeks = 2
            const weekCycle = (weekOfMonth % 2 === 1) ? 1 : 2;

            // Dynamic naming
            const monthName = now.toLocaleDateString('en-US', { month: 'long' });
            const currentDayName = now.toLocaleDateString('en-US', { weekday: 'long' });

            return {
                weekOfMonth: weekOfMonth,
                weekCycle: weekCycle,
                cycleDescription: weekCycle === 1 ? 'Week 1 & 3' : 'Week 2 & 4',
                cycleShort: 'Week ' + weekCycle,
                weekName: 'Week ' + weekOfMonth + ' (' + weekCalc.dateRange + ')',
                dateRange: weekCalc.dateRange,
                weekStart: weekCalc.weekStart,
                weekEnd: weekCalc.weekEnd,
                currentDay: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][now.getDay()],
                currentDayName: currentDayName,
                formattedDate: now.toISOString().split('T')[0],
                displayDate: now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }),
                timeString: now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                }),
                isCurrentWeek: true,
                monthName: monthName,
                year: now.getFullYear()
            };
        }

        /**
         * UNIFIED: Get highlighting information for menu rows
         * @param {string} day - Day name (monday, tuesday, etc.)
         * @param {number} selectedWeekCycle - Currently selected week cycle
         * @returns {Object} Highlighting information
         */
        function getMenuHighlighting(day, selectedWeekCycle) {
            const weekInfo = getCurrentWeekCycle();
            const today = weekInfo.currentDay;
            const currentWeekCycle = weekInfo.weekCycle;

            const isToday = day === today && selectedWeekCycle === currentWeekCycle;
            const isCurrentWeek = selectedWeekCycle === currentWeekCycle;

            return {
                isToday: isToday,
                isCurrentWeek: isCurrentWeek,
                todayClass: isToday ? 'current-day' : (isCurrentWeek ? 'current-week-row' : ''),
                todayBadge: isToday ? '<span class=\"today-badge\"><i class=\"bi bi-star-fill\"></i> Today</span>' :
                           (isCurrentWeek ? '<span class=\"week-badge\"><i class=\"bi bi-calendar-check\"></i> This Week</span>' : ''),
                dayClass: isToday ? 'fw-bold text-primary' : (isCurrentWeek ? 'fw-bold text-success' : 'fw-bold'),
                weekStatus: isCurrentWeek ? 'Current Week' : 'Viewing Week ' + selectedWeekCycle
            };
        }
        ";
    }
    
    /**
     * Get week cycle information for a specific date
     * Alias for getWeekInfo() for clarity
     *
     * @param Carbon|string $date Date to get week info for
     * @return array Array with week info
     */
    public static function getWeekInfoForDate($date)
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        return self::getWeekInfo($date);
    }
    
    /**
     * Debug current week cycle calculation
     *
     * @param Carbon|null $date Optional date, defaults to now()
     * @return array Debug information
     */
    public static function debug($date = null)
    {
        $date = $date ?: now();
        $weekInfo = self::getWeekInfo($date);
        $weekCalc = self::calculateWeekFromDateRange($date);

        return [
            'input_date' => $date->toDateTimeString(),
            'old_carbon_week_of_month' => $date->weekOfMonth,
            'new_calculated_week' => $weekCalc['week_of_month'],
            'week_cycle' => $weekInfo['week_cycle'],
            'cycle_description' => $weekInfo['cycle_description'],
            'date_range' => $weekCalc['date_range'],
            'week_start' => $weekCalc['week_start']->toDateString(),
            'week_end' => $weekCalc['week_end']->toDateString(),
            'current_day' => $weekInfo['current_day'],
            'explanation' => "Week {$weekInfo['week_of_month']} ({$weekCalc['date_range']}) = Cycle {$weekInfo['week_cycle']} ({$weekInfo['cycle_description']})",
            'capped_at_week_4' => $weekCalc['week_of_month'] === 4 && $date->weekOfMonth > 4
        ];
    }
}
