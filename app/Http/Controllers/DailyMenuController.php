<?php

namespace App\Http\Controllers;

use App\Models\DailyMenuUpdate;
use App\Models\Meal;
use App\Services\WeekCycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyMenuController extends Controller
{
    /**
     * Get menu for a specific date and meal type (for post-meal report selection)
     */
    public function getMenuByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner'
        ]);

        $date = $request->input('date');
        $mealType = $request->input('meal_type');

        // Try to get from daily_menu_updates
        $menu = DailyMenuUpdate::where('menu_date', $date)
            ->where('meal_type', $mealType)
            ->first();

        // If not found, try to auto-populate from planning
        if (!$menu) {
            $autoMenu = $this->autoPopulateMenuFromPlanning($date);
            $menu = $autoMenu->where('meal_type', $mealType)->first();
        }

        if ($menu) {
            return response()->json([
                'success' => true,
                'meal_name' => $menu->meal_name,
                'ingredients' => $menu->ingredients,
                'meal_type' => $menu->meal_type,
                'menu_date' => $menu->menu_date
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No meal planned for this date and meal type.'
            ]);
        }
    }
    
    /**
     * Get today's menu for all users (Cook, Kitchen, Student)
     * This is the single source of truth for daily menus
     */
    public function getTodaysMenu(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        
        // Get menu from daily_menu_updates table
        $menu = DailyMenuUpdate::where('menu_date', $date)
            ->orderBy('meal_type')
            ->get();
        
        // If no menu exists for this date, try to auto-populate from Meal planning
        if ($menu->isEmpty()) {
            $menu = $this->autoPopulateMenuFromPlanning($date);
        }
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'menu' => $menu,
            'can_edit' => $this->canEditMenu($date)
        ]);
    }
    
    /**
     * Update menu for a specific date (Cook only)
     */
    public function updateMenu(Request $request)
    {
        // Check if user is Cook
        if (Auth::user()->user_role !== 'cook') {
            return response()->json([
                'success' => false,
                'message' => 'Only cooks can update the menu'
            ], 403);
        }
        
        $request->validate([
            'menu_date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'meal_name' => 'required|string|max:255',
            'ingredients' => 'nullable|string',
            'estimated_portions' => 'nullable|integer|min:0'
        ]);
        
        $date = $request->input('menu_date');
        
        // Check if date is in the past (cannot edit past dates)
        if (!$this->canEditMenu($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit menu for past dates'
            ], 403);
        }
        
        try {
            $menu = DailyMenuUpdate::updateOrCreate(
                [
                    'menu_date' => $date,
                    'meal_type' => $request->input('meal_type')
                ],
                [
                    'meal_name' => $request->input('meal_name'),
                    'ingredients' => $request->input('ingredients'),
                    'estimated_portions' => $request->input('estimated_portions', 0),
                    'updated_by' => Auth::user()->user_id
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Menu updated successfully',
                'menu' => $menu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update menu: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get menu for a date range
     */
    public function getMenuRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $menus = DailyMenuUpdate::whereBetween('menu_date', [$startDate, $endDate])
            ->orderBy('menu_date')
            ->orderBy('meal_type')
            ->get()
            ->groupBy('menu_date');
        
        // Auto-populate missing dates from planning
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        
        while ($currentDate->lte($endDateCarbon)) {
            $dateString = $currentDate->format('Y-m-d');
            if (!isset($menus[$dateString]) || $menus[$dateString]->isEmpty()) {
                $autoMenu = $this->autoPopulateMenuFromPlanning($dateString);
                if (!$autoMenu->isEmpty()) {
                    $menus[$dateString] = $autoMenu;
                }
            }
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'menus' => $menus
        ]);
    }
    
    /**
     * Delete a menu entry (Cook only, future dates only)
     */
    public function deleteMenu(Request $request)
    {
        // Check if user is Cook
        if (Auth::user()->user_role !== 'cook') {
            return response()->json([
                'success' => false,
                'message' => 'Only cooks can delete menu entries'
            ], 403);
        }
        
        $request->validate([
            'menu_date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner'
        ]);
        
        $date = $request->input('menu_date');
        
        // Check if date is in the past
        if (!$this->canEditMenu($date)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete menu for past dates'
            ], 403);
        }
        
        try {
            DailyMenuUpdate::where('menu_date', $date)
                ->where('meal_type', $request->input('meal_type'))
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Menu deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete menu: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Auto-populate menu from Meal planning based on week cycle
     */
    private function autoPopulateMenuFromPlanning($date)
    {
        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = strtolower($dateCarbon->format('l'));
        
        // Get week cycle for the date
        $weekInfo = WeekCycleService::getWeekInfoForDate($dateCarbon);
        $weekCycle = $weekInfo['week_cycle'];
        
        // Get meals from planning
        $meals = Meal::where('day_of_week', $dayOfWeek)
            ->where('week_cycle', $weekCycle)
            ->get();
        
        $menuItems = collect();
        
        foreach ($meals as $meal) {
            // Create DailyMenuUpdate entry
            $menuItem = DailyMenuUpdate::firstOrCreate(
                [
                    'menu_date' => $date,
                    'meal_type' => $meal->meal_type
                ],
                [
                    'meal_name' => $meal->name,
                    'ingredients' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : $meal->ingredients,
                    'estimated_portions' => $meal->serving_size ?? 0,
                    'updated_by' => Auth::user()->user_id ?? null
                ]
            );
            
            $menuItems->push($menuItem);
        }
        
        return $menuItems;
    }
    
    /**
     * Check if menu can be edited (only today and future dates)
     */
    private function canEditMenu($date)
    {
        $menuDate = Carbon::parse($date)->startOfDay();
        $today = Carbon::today();
        
        return $menuDate->gte($today);
    }
    
    /**
     * Sync all menus from Meal planning to DailyMenuUpdate
     * This is useful for initial setup or bulk updates
     */
    public function syncMenusFromPlanning(Request $request)
    {
        // Check if user is Cook or Admin
        if (!in_array(Auth::user()->user_role, ['cook', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only cooks or admins can sync menus'
            ], 403);
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        
        $syncedCount = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $menus = $this->autoPopulateMenuFromPlanning($currentDate->format('Y-m-d'));
            $syncedCount += $menus->count();
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'message' => "Synced {$syncedCount} menu entries",
            'synced_count' => $syncedCount
        ]);
    }
}