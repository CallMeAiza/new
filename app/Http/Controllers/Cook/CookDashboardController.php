<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Dashboard\BaseDashboardController;
use Illuminate\Http\Request;
use App\Models\PreOrder;
use App\Models\Menu;
use App\Models\Inventory;
use App\Services\DashboardViewService;

class CookDashboardController extends BaseDashboardController
{
    public function __construct()
    {
        parent::__construct('cook', 'cook');
    }

    protected function getDashboardData()
    {
        // Get pre-order statistics (replacing old order system)
        $pendingPreOrders = PreOrder::where('is_prepared', false)->count();
        $completedPreOrders = PreOrder::where('is_prepared', true)->count();

        // Get menu statistics
        $activeMenuItems = Menu::where('is_available', true)->count();
        $totalMenuItems = Menu::count();
        $menuItems = Menu::where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Get inventory statistics
        $lowStockItems = Inventory::where('quantity', '<=', \DB::raw('reorder_point'))->count();
        $totalItems = Inventory::count();

        // Get today's menu directly from Meal planning (same source as Daily & Weekly Menu)
        $today = now()->format('Y-m-d');
        $currentDay = strtolower(now()->format('l')); // e.g., "monday"
        $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
        $weekCycle = $weekInfo['week_cycle'];
        
        // Get today's menu directly from Meal table (same as menu planning)
        $meals = \App\Models\Meal::where('week_cycle', $weekCycle)
            ->where('day_of_week', $currentDay)
            ->orderBy('meal_type')
            ->get();
        
        // Format today's menu for display
        $todaysMenu = collect();
        foreach ($meals as $meal) {
            $todaysMenu->push((object)[
                'meal_type' => $meal->meal_type ?? 'N/A',
                'meal_name' => $meal->name ?? 'No meal set',
                'ingredients' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : (is_string($meal->ingredients) ? $meal->ingredients : 'No ingredients listed'),
                'created_at' => $meal->created_at,
            ]);
        }

        // Get low stock items list - with "show once" logic
        $lowStockItemsList = DashboardViewService::processDashboardData(
            Inventory::where('quantity', '<=', \DB::raw('reorder_point'))
                ->orderBy('quantity')
                ->take(3),
            'low_stock_items'
        );

        // Get recent pre-orders (replacing old orders) - with "show once" logic
        $recentOrders = DashboardViewService::processDashboardData(
            PreOrder::with(['user', 'menu'])->orderBy('created_at', 'desc')->take(5),
            'recent_orders'
        );
            
        // Get meal attendance data for food waste prevention
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');
        $lastWeekStart = now()->subWeek()->startOfWeek()->format('Y-m-d');
        $lastWeekEnd = now()->subWeek()->endOfWeek()->format('Y-m-d');
        
        // Get today's meal attendance
        $todayAttendance = \App\Models\PreOrder::where('date', $today)
            ->where('is_attending', true)
            ->count();
            
        // Get this week's meal attendance
        $thisWeekAttendance = \App\Models\PreOrder::whereBetween('date', [$weekStart, $weekEnd])
            ->where('is_attending', true)
            ->count();
            
        // Get last week's meal attendance
        $lastWeekAttendance = \App\Models\PreOrder::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->where('is_attending', true)
            ->count();
            
        // Calculate percentage increase in attendance responses
        $attendanceIncrease = $lastWeekAttendance > 0 ? 
            round((($thisWeekAttendance - $lastWeekAttendance) / $lastWeekAttendance) * 100) : 0;
            
        // Get meal poll responses
        $mealPolls = \App\Models\Announcement::where('is_poll', true)
            ->where('is_active', true)
            ->get();
            
        $totalPollResponses = 0;
        foreach ($mealPolls as $poll) {
            $totalPollResponses += $poll->pollResponses()->count();
        }
        
        // Calculate waste reduction metrics
        // Assuming each accurate attendance record saves approximately 0.5kg of food waste
        $kgSaved = $thisWeekAttendance * 0.5;
        
        // Assuming each kg of food costs approximately $5
        $costSaved = $kgSaved * 5;
        
        // Assuming each meal is approximately 0.3kg
        $mealsSaved = round($kgSaved / 0.3);
        
        // Get tomorrow's meal with lowest attendance percentage for recommendation
        $tomorrowMeals = \App\Models\Menu::where('date', $tomorrow)->get();
        $lowestAttendanceMeal = 'dinner';
        $lowestAttendancePercentage = 100;
        
        foreach ($tomorrowMeals as $meal) {
            $totalStudents = \App\Models\User::where('user_role', 'student')->count();
            $attendingStudents = \App\Models\PreOrder::where('date', $tomorrow)
                ->where('meal_type', $meal->meal_type)
                ->where('is_attending', true)
                ->count();
                
            $attendancePercentage = $totalStudents > 0 ? ($attendingStudents / $totalStudents) * 100 : 0;
            
            if ($attendancePercentage < $lowestAttendancePercentage) {
                $lowestAttendancePercentage = $attendancePercentage;
                $lowestAttendanceMeal = $meal->meal_type;
            }
        }
        
        // Calculate recommended reduction percentage
        $recommendedReduction = round(100 - $lowestAttendancePercentage);
        if ($recommendedReduction < 10) $recommendedReduction = 10;
        if ($recommendedReduction > 40) $recommendedReduction = 40;

        // Prepare meal attendance data for the view
        $mealAttendance = [
            'today' => $todayAttendance,
            'total' => $thisWeekAttendance,
            'increase' => $attendanceIncrease,
            'poll_responses' => $totalPollResponses
        ];
        
        // Prepare waste reduction data for the view
        $wasteReduction = [
            'percentage' => round(($thisWeekAttendance / max(1, $thisWeekAttendance + $lastWeekAttendance)) * 100),
            'kg_saved' => round($kgSaved),
            'cost_saved' => round($costSaved),
            'meals_saved' => $mealsSaved,
            'recommendation' => $recommendedReduction . '%',
            'meal_type' => ucfirst($lowestAttendanceMeal)
        ];

        // Recent post meal reports - with "show once" logic
        $recentPostMealReports = DashboardViewService::processDashboardData(
            \App\Models\PostAssessment::with(['assessedBy', 'menu'])
                ->where('is_completed', true)
                ->orderBy('date', 'desc')
                ->take(3),
            'recent_post_meal_reports'
        );

        // Recent inventory reports - with "show once" logic
        $recentInventoryReports = DashboardViewService::processDashboardData(
            \App\Models\InventoryCheck::with(['user', 'items'])
                ->orderBy('created_at', 'desc')
                ->take(3),
            'recent_inventory_reports'
        );

        // Recent student feedback - with "show once" logic
        $recentFeedback = DashboardViewService::processDashboardData(
            \App\Models\Feedback::with(['student', 'meal'])
                ->orderBy('created_at', 'desc')
                ->take(3),
            'recent_feedback'
        );

        return compact(
            'pendingPreOrders',
            'completedPreOrders',
            'activeMenuItems',
            'totalMenuItems',
            'menuItems',
            'lowStockItems',
            'totalItems',
            'lowStockItemsList',
            'recentOrders',
            'mealAttendance',
            'wasteReduction',
            'recentPostMealReports',
            'recentInventoryReports',
            'recentFeedback',
            'todaysMenu'
        );
    }

    public function consumption()
    {
        return view('cook.consumption');
    }

    public function orders()
    {
        return view('cook.orders');
    }

    public function menu()
    {
        $menuItems = \App\Models\Menu::with('ingredients')->get();
        return view('cook.menu', compact('menuItems'));
    }

    public function inventory()
    {
        return view('cook.inventory');
    }

    public function profile()
    {
        return view('cook.profile');
    }

    public function suppliers()
    {
        return view('cook.suppliers');
    }

    public function settings()
    {
        return view('cook.settings');
    }


    public function reports()
    {
        return view('cook.reports');
    }

    public function notifications()
    {
        return view('cook.notifications');
    }

    /**
     * System Integration Dashboard
     */
    public function systemIntegration()
    {
        return view('cook.system-integration');
    }

    /**
     * Daily & Weekly Menu View
     * Shows today's menu and weekly menu in one page
     */
    public function dailyWeeklyMenu()
    {
        $today = now()->format('Y-m-d');
        $currentDay = strtolower(now()->format('l'));
        $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
        $weekCycle = $weekInfo['week_cycle'];
        $weekOfMonth = $weekInfo['week_of_month'];

        // Get today's menu directly from Meal table (same as menu planning)
        $meals = \App\Models\Meal::where('week_cycle', $weekCycle)
            ->where('day_of_week', $currentDay)
            ->orderBy('meal_type')
            ->get();
        
        // Format today's menu for display
        $todaysMenu = collect();
        foreach ($meals as $meal) {
            $todaysMenu->push((object)[
                'meal_type' => $meal->meal_type ?? 'N/A',
                'meal_name' => $meal->name ?? 'No meal set',
                'ingredients' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : (is_string($meal->ingredients) ? $meal->ingredients : 'No ingredients listed'),
                'created_at' => $meal->created_at,
            ]);
        }

        return view('cook.daily-weekly-menu', compact(
            'todaysMenu',
            'today',
            'currentDay',
            'weekCycle',
            'weekOfMonth'
        ));
    }


}

