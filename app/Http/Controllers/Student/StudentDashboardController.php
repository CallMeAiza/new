<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Dashboard\BaseDashboardController;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Menu;
use App\Models\Meal;
use App\Models\PreOrder;
use App\Models\Announcement;
use App\Models\Poll;
use App\Models\PollResponse;
use App\Services\DashboardViewService;
use App\Services\WeekCycleService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentDashboardController extends BaseDashboardController
{
    public function __construct()
    {
        parent::__construct('student', 'student');
    }
    
    /**
     * Handle the submission of student meal choices.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submitMealChoices(Request $request)
    {
        // Validate the request
        $request->validate([
            'week_cycle' => 'required|in:1,2',
        ]);
        
        $weekCycle = $request->week_cycle;
        $userId = Auth::id();
        $now = Carbon::now();
        $weekStart = Carbon::now()->startOfWeek();
        
        // Process all meal choices from the form
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $mealChoices = [];
        
        foreach ($days as $day) {
            $dayDate = clone $weekStart;
            
            // Map day names to day numbers (1 = Monday, 5 = Friday)
            switch ($day) {
                case 'monday': $dayOffset = 0; break;
                case 'tuesday': $dayOffset = 1; break;
                case 'wednesday': $dayOffset = 2; break;
                case 'thursday': $dayOffset = 3; break;
                case 'friday': $dayOffset = 4; break;
                default: $dayOffset = 0;
            }
            
            $dayDate->addDays($dayOffset);
            $dateString = $dayDate->format('Y-m-d');
            
            foreach ($mealTypes as $mealType) {
                // Handle both week cycles (with or without _w2 suffix)
                $fieldName = $weekCycle == 1 ? "{$day}_{$mealType}" : "{$day}_{$mealType}_w2";
                
                if ($request->has($fieldName)) {
                    $isAttending = $request->input($fieldName) === 'yes';
                    
                    // Check if the deadline has passed
                    $deadlinePassed = false;
                    $currentDay = $now->dayOfWeek; // 1 = Monday, 7 = Sunday
                    $dayNumber = $dayOffset + 1; // Convert to 1-based day number
                    
                    // Only check deadline if it's the current day
                    if ($currentDay == $dayNumber) {
                        switch ($mealType) {
                            case 'breakfast':
                                $deadlinePassed = $now->hour >= 6; // 6:00 AM deadline
                                break;
                            case 'lunch':
                                $deadlinePassed = $now->hour >= 10; // 10:00 AM deadline
                                break;
                            case 'dinner':
                                $deadlinePassed = $now->hour >= 15; // 3:00 PM deadline
                                break;
                        }
                    }
                    
                    if (!$deadlinePassed) {
                        // Create or update the pre-order record
                        PreOrder::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'date' => $dateString,
                                'meal_type' => $mealType
                            ],
                            [
                                'is_attending' => $isAttending,
                                'week_cycle' => $weekCycle
                            ]
                        );
                        
                        $mealChoices[] = [
                            'day' => ucfirst($day),
                            'meal_type' => ucfirst($mealType),
                            'attending' => $isAttending
                        ];
                    }
                }
            }
        }
        
        return redirect()->route('student.dashboard')
            ->with('success', 'Your meal choices have been submitted successfully!')
            ->with('meal_choices', $mealChoices);
    }

    /**
     * Display the student dashboard with today's menu.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $today = Carbon::today()->format('Y-m-d');
        $weekStart = Carbon::today()->startOfWeek();
        $weekEnd = Carbon::today()->endOfWeek();

        // Get today's menu items using the same approach as kitchen/cook dashboards
        $currentDay = strtolower(now()->format('l'));
        $weekInfo = WeekCycleService::getWeekInfo();
        $weekCycle = $weekInfo['week_cycle'];

        // Get today's menu from centralized DailyMenuUpdate table (single source of truth)
        $todayMenuQuery = \App\Models\DailyMenuUpdate::where('menu_date', $today)
            ->orderBy('meal_type')
            ->get();
        
        // If no menu exists for today, auto-populate from Meal planning
        if ($todayMenuQuery->isEmpty()) {
            $meals = Meal::forWeekCycle($weekCycle)
                ->forDay($currentDay)
                ->get();
            
            // If no meals for today (weekend) and no weekend meals found, get the most recent weekday
            if ($meals->isEmpty() && in_array($currentDay, ['saturday', 'sunday'])) {
                $weekdays = ['friday', 'thursday', 'wednesday', 'tuesday', 'monday'];
                foreach ($weekdays as $weekday) {
                    $fallbackMenu = Meal::forWeekCycle($weekCycle)
                        ->forDay($weekday)
                        ->get();
                    if (!$fallbackMenu->isEmpty()) {
                        $meals = $fallbackMenu;
                        $currentDay = $weekday;
                        break;
                    }
                }
            }
            
            foreach ($meals as $meal) {
                $menuItem = \App\Models\DailyMenuUpdate::firstOrCreate(
                    [
                        'menu_date' => $today,
                        'meal_type' => $meal->meal_type
                    ],
                    [
                        'meal_name' => $meal->name,
                        'ingredients' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : $meal->ingredients,
                        'estimated_portions' => $meal->serving_size ?? 0,
                        'updated_by' => auth()->user()->user_id ?? null
                    ]
                );
                $todayMenuQuery->push($menuItem);
            }
        }

        // Format menu items to match expected structure
        $formattedMenu = $todayMenuQuery->map(function($item) {
            return (object)[
                'name' => $item->meal_name,
                'meal_name' => $item->meal_name,
                'ingredients' => $item->ingredients,
                'meal_type' => $item->meal_type,
                'price' => 0, // Default price
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'is_highlighted' => false
            ];
        });

        // Apply highlighting for new menu items
        $todayMenuWithHighlighting = DashboardViewService::processMenuDataWithHighlighting(
            $formattedMenu,
            'new_menu_items_student'
        );

        $todayMenu = $todayMenuWithHighlighting->groupBy('meal_type');

        // Debug log to verify menu retrieval
        \Log::info('Student Dashboard - Today\'s Menu Retrieved', [
            'actual_day' => strtolower(now()->format('l')),
            'displayed_day' => $currentDay,
            'week_cycle' => $weekCycle,
            'menu_count' => $todayMenu->count(),
            'meal_types' => $todayMenu->keys()->toArray()
        ]);
        
        // Get student's recent pre-orders (for dashboard display) - with "show once" logic
        $studentPreOrders = DashboardViewService::processDashboardData(
            PreOrder::where('user_id', Auth::id())->orderBy('date', 'desc')->take(3),
            'student_recent_preorders'
        );

        // Get student's pre-orders for today (for meal attendance)
        $todayPreOrders = PreOrder::where('user_id', Auth::id())
            ->where('date', $today)
            ->get()
            ->keyBy('meal_type');

        // Get active announcements - with "show once" logic
        $announcements = DashboardViewService::processDashboardData(
            Announcement::where('is_active', true)
                ->where('expiry_date', '>=', $today)
                ->orderBy('created_at', 'desc')
                ->take(5),
            'active_announcements'
        );

        // Get active meal polls - with "show once" logic
        $activeMealPolls = DashboardViewService::processDashboardData(
            Announcement::where('is_active', true)->orderBy('created_at', 'desc'),
            'active_meal_polls'
        );
            
        // Get weekly menu for Week 1 and Week 2 using the same approach as kitchen/cook
        // Week 1 Menu (Week Cycle 1)
        $week1Menu = Meal::forWeekCycle(1)
            ->orderBy('day_of_week')
            ->orderBy('meal_type')
            ->get()
            ->groupBy('day_of_week')
            ->map(function($dayMeals) {
                return $dayMeals->groupBy('meal_type');
            });

        // Week 2 Menu (Week Cycle 2)
        $week2Menu = Meal::forWeekCycle(2)
            ->orderBy('day_of_week')
            ->orderBy('meal_type')
            ->get()
            ->groupBy('day_of_week')
            ->map(function($dayMeals) {
                return $dayMeals->groupBy('meal_type');
            });
            
        // Get meal types for display
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        
        // Get days of the week for display (matching Meal model format)
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        // Get cutoff times for each meal type
        $cutoffTimes = [
            'breakfast' => '6:00 AM',
            'lunch' => '10:00 AM',
            'dinner' => '3:00 PM'
        ];
            
        // Get student's responses to meal polls
        $pollResponses = [];
        foreach ($activeMealPolls as $poll) {
            $response = PollResponse::where('announcement_id', $poll->id)
                ->where('user_id', Auth::id())
                ->first();
            if ($response) {
                $pollResponses[$poll->id] = [
                    'response' => $response->response
                ];
            }
        }
        
        // Calculate meal costs
        $breakfastTotal = 0;
        $lunchTotal = 0;
        $dinnerTotal = 0;
        
        if (isset($todayMenu['breakfast'])) {
            foreach ($todayMenu['breakfast'] as $item) {
                $breakfastTotal += $item->price;
            }
        }
        
        if (isset($todayMenu['lunch'])) {
            foreach ($todayMenu['lunch'] as $item) {
                $lunchTotal += $item->price;
            }
        }
        
        if (isset($todayMenu['dinner'])) {
            foreach ($todayMenu['dinner'] as $item) {
                $dinnerTotal += $item->price;
            }
        }
        
        $mealCosts = [
            'breakfast' => $breakfastTotal,
            'lunch' => $lunchTotal,
            'dinner' => $dinnerTotal
        ];
        
        // Get food waste statistics to show impact of meal attendance tracking
        $studentResponses = PreOrder::where('user_id', Auth::id())
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('is_attending', true)
            ->count();
            
        $pollResponsesCount = 0;
        foreach ($activeMealPolls as $poll) {
            if (PollResponse::where('poll_id', $poll->id)->where('user_id', Auth::id())->exists()) {
                $pollResponsesCount++;
            }
        }
        
        $wasteStats = [
            'weekly_reduction' => rand(15, 25), // Placeholder for actual waste reduction percentage
            'monthly_savings' => rand(500, 1500), // Placeholder for actual cost savings
            'contribution' => ($studentResponses + $pollResponsesCount) * 0.5 // Each attendance response saves ~0.5kg of food waste
        ];
        
        // Get meal times for display
        $mealTimes = [
            'breakfast' => '7:00 AM - 8:30 AM',
            'lunch' => '11:30 AM - 1:00 PM',
            'dinner' => '5:30 PM - 7:00 PM'
        ];
        
        // Get next meal type based on current time
        $currentTime = Carbon::now();
        $nextMeal = 'breakfast';
        
        if ($currentTime->hour >= 8) {
            $nextMeal = 'lunch';
        }
        
        if ($currentTime->hour >= 13) {
            $nextMeal = 'dinner';
        }
        
        if ($currentTime->hour >= 19) {
            $nextMeal = 'breakfast';
            $today = Carbon::tomorrow()->format('Y-m-d');
        }
        
        
        

        // Handle AJAX requests for menu refresh
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'menu' => $todayMenu,
                'day' => strtoupper($currentDay),
                'actual_day' => strtolower(now()->format('l')),
                'date' => now()->format('l, F j, Y')
            ]);
        }

        return view('student.dashboard', compact(
            'todayMenu',
            'studentPreOrders',
            'todayPreOrders',
            'announcements',
            'activeMealPolls',
            'pollResponses',
            'mealTimes',
            'nextMeal',
            'today',
            'mealCosts',
            'wasteStats',
            'week1Menu',
            'week2Menu',
            'mealTypes',
            'daysOfWeek',
            'cutoffTimes'
        ));
    }
    
    protected function getDashboardData()
    {
        $reports = Report::where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return [
            'reports' => $reports,
            'recentOrders' => parent::getDashboardData()['recentOrders']
        ];
    }

    public function menu()
    {
        return view('student.menu');
    }

    public function orders()
    {
        return view('student.orders');
    }

    public function cart()
    {
        return view('student.cart');
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function notifications()
    {
        return view('student.notifications');
    }

    public function settings()
    {
        return view('student.settings');
    }

    public function reports()
    {
        $reports = Report::where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('student.reports', compact('reports'));
    }

    /**
     * Store a student's response to a meal poll.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePollResponse(Request $request)
    {
        $request->validate([
            'announcement_id' => 'required|exists:announcements,id',
            'response' => 'required|string|max:255',
        ]);
        
        // Check if user has already responded to this poll
        $existingResponse = \App\Models\PollResponse::where('announcement_id', $request->announcement_id)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($existingResponse) {
            // Update existing response
            $existingResponse->update([
                'response' => $request->response,
            ]);
            
            return redirect()->back()->with('success', 'Your poll response has been updated. Thank you for your feedback!');
        }
        
        // Create new response
        \App\Models\PollResponse::create([
            'announcement_id' => $request->announcement_id,
            'user_id' => Auth::id(),
            'response' => $request->response,
        ]);
        
        return redirect()->back()->with('success', 'Your poll response has been recorded. Thank you for your feedback!');
    }
    
    public function storeReport(Request $request)
    {
        $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'report_date' => 'required|date',
            'meal_items' => 'required|array',
            'meal_items.*' => 'required|string',
            'feedback' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $report = Report::create([
            'student_id' => Auth::id(),
            'meal_type' => $request->meal_type,
            'report_date' => $request->report_date,
            'meal_items' => json_encode($request->meal_items),
            'feedback' => $request->feedback,
            'rating' => $request->rating
        ]);

        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }
}