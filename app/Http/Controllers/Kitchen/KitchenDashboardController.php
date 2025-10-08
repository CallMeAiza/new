<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Dashboard\BaseDashboardController;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\PreOrder;
use App\Services\WeekCycleService;
use App\Services\DashboardViewService;
use Carbon\Carbon;

class KitchenDashboardController extends BaseDashboardController
{
    public function __construct()
    {
        parent::__construct('kitchen', 'kitchen');
    }

    protected function getDashboardData()
    {
        $data = parent::getDashboardData();
        
        // Get data from Cook's menu planning to execute in kitchen
        $today = now()->format('Y-m-d');
        $currentDay = strtolower(now()->format('l'));

        // FIXED: Use consistent week cycle calculation via service
        $weekInfo = WeekCycleService::getWeekInfo();
        $weekOfMonth = $weekInfo['week_of_month'];
        $weekCycle = $weekInfo['week_cycle'];
        
        // Get today's menu from centralized DailyMenuUpdate table (single source of truth)
        $todayMenusQuery = \App\Models\DailyMenuUpdate::where('menu_date', $today)
            ->orderBy('meal_type')
            ->get();
        
        // If no menu exists for today, auto-populate from Meal planning
        if ($todayMenusQuery->isEmpty()) {
            $meals = \App\Models\Meal::forWeekCycle($weekCycle)
                ->forDay($currentDay)
                ->get();
            
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
                $todayMenusQuery->push($menuItem);
            }
        }

        // Apply highlighting for new menu items
        $todayMenusWithHighlighting = DashboardViewService::processMenuDataWithHighlighting(
            $todayMenusQuery,
            'new_menu_items_kitchen'
        );

        $todayMenus = $todayMenusWithHighlighting->groupBy('meal_type');
        
        // Get inventory items that need preparation
        $inventoryItems = \App\Models\Inventory::whereRaw('quantity <= reorder_point * 1.5')
            ->orderBy('quantity')
            ->take(5)
            ->get();
        
        // Get pre-orders for today to prepare correct quantities
        $preOrders = PreOrder::where('date', $today)
            ->where('is_attending', true)
            ->get()
            ->groupBy('meal_type');
        
        // Count expected attendance for each meal type
        $mealAttendance = [
            'breakfast' => $preOrders->get('breakfast') ? $preOrders->get('breakfast')->count() : 0,
            'lunch' => $preOrders->get('lunch') ? $preOrders->get('lunch')->count() : 0,
            'dinner' => $preOrders->get('dinner') ? $preOrders->get('dinner')->count() : 0,
        ];
        
        // Add kitchen-specific data to the dashboard
        $data['todayMenus'] = $todayMenus;
        $data['inventoryItems'] = $inventoryItems;
        $data['mealAttendance'] = $mealAttendance;

        // Get recent pre-orders instead of orders - with "show once" logic
        $data['recentOrders'] = DashboardViewService::processDashboardData(
            PreOrder::with(['user', 'menu'])->orderBy('created_at', 'desc')->take(5),
            'recent_orders'
        );

        // Get recent post meal reports - with "show once" logic
        $data['recentPostMealReports'] = DashboardViewService::processDashboardData(
            \App\Models\PostAssessment::with(['assessedBy'])->orderBy('created_at', 'desc')->take(3),
            'recent_post_meal_reports'
        );

        // Get recent student feedback - with "show once" logic
        $data['recentFeedback'] = DashboardViewService::processDashboardData(
            \App\Models\Feedback::with(['student', 'meal'])->orderBy('created_at', 'desc')->take(3),
            'recent_feedback'
        );

        // Get recent inventory reports (using InventoryHistory) - with "show once" logic
        $data['recentInventoryReports'] = DashboardViewService::processDashboardData(
            \App\Models\InventoryHistory::with(['user', 'item'])->orderBy('created_at', 'desc')->take(3),
            'recent_inventory_reports'
        );

        // Get today's menu for display
        $data['todaysMenu'] = collect();
        foreach ($todayMenus as $mealType => $menuUpdates) {
            foreach ($menuUpdates as $menuUpdate) {
                $data['todaysMenu']->push((object)[
                    'meal_type' => $mealType,
                    'meal_name' => $menuUpdate->meal_name ?? 'No meal planned',
                    'ingredients' => $menuUpdate->ingredients ?? 'No ingredients listed',
                    'created_at' => $menuUpdate->created_at,
                    'is_highlighted' => $menuUpdate->is_highlighted ?? false
                ]);
            }
        }

        // Add week cycle information for display
        $data['weekCycle'] = $weekCycle;
        $data['currentDay'] = ucfirst($currentDay);
        $data['today'] = $today;

        return $data;
    }

    // Recipe & Meal - Execute recipes created by cooks
    public function recipes()
    {
        // Get recipes created by cooks to be executed by kitchen staff
        $recipes = Menu::where('is_available', true)
            ->orderBy('name')
            ->get()
            ->unique('name');
            
        return view('kitchen.recipes', ['recipes' => $recipes]);
    }

    public function mealPlanning()
    {
        // Get meal planning from cooks to prepare for execution
        // FIXED: Use consistent week cycle calculation via service
        $weekCycle = WeekCycleService::getCurrentWeekCycle();
        
        // Get the current week's menu created by cooks
        $menus = [
            1 => [], // Week 1 & 3
            2 => []  // Week 2 & 4
        ];
        
        // Get menus for current cycle to execute
        $cycleMenus = Menu::where('week_cycle', $weekCycle)->get();
        
        foreach ($cycleMenus as $menu) {
            if (isset($menu->day) && isset($menu->meal_type)) {
                if (!isset($menus[$weekCycle][$menu->day])) {
                    $menus[$weekCycle][$menu->day] = [];
                }
                $menus[$weekCycle][$menu->day][$menu->meal_type] = [
                    'name' => $menu->name,
                    'ingredients' => $menu->description,
                    'id' => $menu->id
                ];
            }
        }
        
        return view('kitchen.meal-planning', [
            'menus' => $menus,
            'currentCycle' => $weekCycle
        ]);
    }

    public function preparation()
    {
        // Get today's date and current week cycle
        $today = now()->format('Y-m-d');
        $currentDay = now()->format('l');
        // FIXED: Use consistent week cycle calculation via service
        $weekInfo = WeekCycleService::getWeekInfo();
        $weekOfMonth = $weekInfo['week_of_month'];
        $weekCycle = $weekInfo['week_cycle'];
        
        // Get today's menu items from cook's planning
        $todayMenuItems = Menu::where('day', $currentDay)
            ->where('week_cycle', $weekCycle)
            ->get()
            ->groupBy('meal_type');
            
        // Get pre-orders for today to prepare correct quantities
        $preOrders = PreOrder::where('date', $today)
            ->where('is_attending', true)
            ->get()
            ->groupBy('meal_type');
        
        // Count expected attendance for each meal type
        $mealAttendance = [
            'breakfast' => $preOrders->get('breakfast') ? $preOrders->get('breakfast')->count() : 0,
            'lunch' => $preOrders->get('lunch') ? $preOrders->get('lunch')->count() : 0,
            'dinner' => $preOrders->get('dinner') ? $preOrders->get('dinner')->count() : 0,
        ];
        
        // Get inventory items needed for today's meals
        $requiredIngredients = [];
        
        return view('kitchen.preparation', compact(
            'todayMenuItems',
            'mealAttendance',
            'requiredIngredients'
        ));
    }

    public function orders()
    {
        // Get pre-orders that need to be prepared by kitchen staff
        $pendingOrders = PreOrder::where('is_prepared', false)
            ->orderBy('created_at')
            ->with(['user', 'menu'])
            ->get();

        // Get completed pre-orders for reference
        $completedOrders = PreOrder::where('is_prepared', true)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->with(['user', 'menu'])
            ->get();

        return view('kitchen.orders', [
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders
        ]);
    }

    // Inventory Management - Execute inventory tasks assigned by cook/admin
    public function inventory()
    {
        // Get inventory items that need attention
        $lowStockItems = \App\Models\Inventory::where('quantity', '<=', \DB::raw('reorder_point'))
            ->orderBy('quantity')
            ->get();
            
        // Get all inventory items for reference
        $allItems = \App\Models\Inventory::orderBy('name')->get();
        
        return view('kitchen.inventory', [
            'lowStockItems' => $lowStockItems,
            'allItems' => $allItems
        ]);
    }

    public function suppliers()
    {
        return view('kitchen.suppliers');
    }

    public function purchases()
    {
        return view('kitchen.purchases');
    }

    // Reports & Analytics
    public function reports()
    {
        return view('kitchen.reports');
    }

    public function analytics()
    {
        $data = $this->getAnalyticsData();
        return view('kitchen.analytics', $data);
    }

    // Alerts & Notifications
    public function notifications()
    {
        return view('kitchen.notifications');
    }

    public function alerts()
    {
        return view('kitchen.alerts');
    }

    // Settings
    public function settings()
    {
        return view('kitchen.settings');
    }
    
    /**
     * Display the daily menu for the kitchen staff based on cook's planning.
     * Kitchen staff uses this to execute the menu created by cooks.
     *
     * @return \Illuminate\Http\Response
     */
    public function dailyMenu()
    {
        $today = now()->format('l'); // Get current day name

        // FIXED: Use consistent week cycle calculation via service
        $weekInfo = WeekCycleService::getWeekInfo();
        $weekOfMonth = $weekInfo['week_of_month'];
        $weekCycle = $weekInfo['week_cycle'];

        // Check if cook has created any meals using the Meal model
        $hasMeals = \App\Models\Meal::exists();

        if (!$hasMeals) {
            // No meals created by cook yet
            return view('kitchen.daily-menu', [
                'todayMenu' => null,
                'weeklyMenu' => collect(),
                'today' => $today,
                'weekCycle' => $weekCycle,
                'weekOfMonth' => $weekOfMonth,
                'hasMeals' => false,
                'waitingForCook' => true
            ]);
        }

        // Sync today's menu to daily updates for real-time tracking
        $this->syncTodaysMenuToDailyUpdates();

        return view('kitchen.daily-menu', [
            'todayMenu' => null, // Will be loaded dynamically via AJAX
            'weeklyMenu' => collect(), // Will be loaded dynamically via AJAX
            'today' => $today,
            'weekCycle' => $weekCycle,
            'weekOfMonth' => $weekOfMonth,
            'hasMeals' => true,
            'waitingForCook' => false
        ]);
    }



    /**
     * Sync today's menu from cook's meals to daily menu updates
     *
     * @return void
     */
    private function syncTodaysMenuToDailyUpdates()
    {
        try {
            $today = now()->toDateString();
            $currentDayOfWeek = strtolower(now()->format('l'));
            // FIXED: Use consistent week cycle calculation via service
            $currentWeekCycle = WeekCycleService::getCurrentWeekCycle();

            // Get today's meals from cook's menu
            $todaysMeals = \App\Models\Meal::where('day_of_week', $currentDayOfWeek)
                ->where('week_cycle', $currentWeekCycle)
                ->get();

            foreach ($todaysMeals as $meal) {
                // Check if daily update already exists
                $existingUpdate = \App\Models\DailyMenuUpdate::where('menu_date', $today)
                    ->where('meal_type', $meal->meal_type)
                    ->first();

                if (!$existingUpdate) {
                    // Create new daily menu update
                    \App\Models\DailyMenuUpdate::create([
                        'menu_date' => $today,
                        'meal_type' => $meal->meal_type,
                        'meal_name' => $meal->name,
                        'ingredients' => $this->formatIngredients($meal->ingredients),
                        'status' => 'planned',
                        'estimated_portions' => $meal->serving_size ?? 50,
                        'actual_portions' => 0,
                        'updated_by' => auth()->id() ?? 1 // Default to admin if no auth
                    ]);
                }
            }

            \Log::info('Today\'s menu synced to daily updates', [
                'date' => $today,
                'meals_synced' => $todaysMeals->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to sync today\'s menu to daily updates', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format ingredients for display - convert arrays to strings
     *
     * @param mixed $ingredients
     * @return string
     */
    private function formatIngredients($ingredients)
    {
        // Handle null or undefined
        if ($ingredients === null || $ingredients === '') {
            return 'No ingredients listed';
        }

        // Handle arrays
        if (is_array($ingredients)) {
            return implode(', ', $ingredients);
        }

        // Handle strings
        if (is_string($ingredients)) {
            return $ingredients;
        }

        // Handle objects (like stdClass) - try to convert to string
        if (is_object($ingredients)) {
            if (method_exists($ingredients, '__toString')) {
                return (string) $ingredients;
            }
            return 'No ingredients listed';
        }

        // Fallback for any other type
        return 'No ingredients listed';
    }
}