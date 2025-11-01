<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\BaseController;
use App\Models\Meal;
use App\Models\KitchenMenuPoll;
use App\Models\KitchenPollResponse;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends BaseController
{
    public function index()
    {
        return view('cook.menu');
    }

    public function getMenu($weekCycle)
    {
        return $this->safeApiResponse(function () use ($weekCycle) {
            $this->logUserAction('get_menu', ['week_cycle' => $weekCycle]);

            $meals = $this->safeTableQuery('meals', function () use ($weekCycle) {
                return Meal::forWeekCycle($weekCycle)
                    ->get()
                    ->groupBy('day_of_week')
                    ->map(function ($dayMeals) {
                        return $dayMeals->groupBy('meal_type')
                            ->map(function ($meal) {
                                $mealData = $meal->first()->toArray();
                                // Use safe status getter
                                $mealData['status'] = $this->getMealStatus($meal->first());
                                return $mealData;
                            });
                    });
            }, collect());

            // Return just the menu data - safeApiResponse will wrap it properly
            return $meals;
        }, 'Failed to load menu data');
    }

    public function getMeal($weekCycle, $day, $mealType)
    {
        $meal = Meal::forWeekCycle($weekCycle)
            ->forDay($day)
            ->forMealType($mealType)
            ->first();

        if (!$meal) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'meal' => $meal
        ]);
    }

    public function update(Request $request)
    {
        // Add debugging
        \Log::info('Menu update request received', [
            'data' => $request->all(),
            'user' => auth()->id()
        ]);

        $validator = Validator::make($request->all(), [
            'day' => 'required|string',
            'meal_type' => 'required|string|in:breakfast,lunch,dinner',
            'week_cycle' => 'required|integer|in:1,2',
            'name' => 'required|string|max:255',
            'ingredients' => 'required|string',
            // Make serving_size optional; we set a sensible default below
            'serving_size' => 'nullable|integer|min:1',
            'meal_ingredients' => 'nullable|array',
            'meal_ingredients.*.inventory_id' => 'required_with:meal_ingredients|exists:inventory,id',
            'meal_ingredients.*.quantity_per_serving' => 'required_with:meal_ingredients|numeric|min:0.001'
        ]);

        if ($validator->fails()) {
            \Log::warning('Menu update validation failed', [
                'errors' => $validator->errors(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Process ingredients - convert string to array if needed
        $ingredients = $request->ingredients;
        if (is_string($ingredients)) {
            // Split by comma and clean up
            $ingredients = array_map('trim', explode(',', $ingredients));
            $ingredients = array_filter($ingredients); // Remove empty values
        }

        try {
            // Check ingredient availability if meal ingredients are provided
            if ($request->has('meal_ingredients') && !empty($request->meal_ingredients)) {
                $missingIngredients = [];
                $servingSize = $request->serving_size;

                foreach ($request->meal_ingredients as $ingredientData) {
                    $inventoryItem = \App\Models\Inventory::find($ingredientData['inventory_id']);
                    if ($inventoryItem) {
                        $requiredQuantity = $ingredientData['quantity_per_serving'] * $servingSize;
                        if ($inventoryItem->quantity < $requiredQuantity) {
                            $missingIngredients[] = [
                                'name' => $inventoryItem->name,
                                'required' => $requiredQuantity,
                                'available' => $inventoryItem->quantity,
                                'shortage' => $requiredQuantity - $inventoryItem->quantity,
                                'unit' => $inventoryItem->unit
                            ];
                        }
                    }
                }

                if (!empty($missingIngredients)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient ingredients to create this menu',
                        'missing_ingredients' => $missingIngredients,
                        'suggestion' => 'Please create a purchase order for the missing ingredients first.'
                    ], 400);
                }
            }

            $meal = Meal::updateOrCreate(
                [
                    'day_of_week' => strtolower($request->day),
                    'meal_type' => strtolower($request->meal_type),
                    'week_cycle' => $request->week_cycle
                ],
                [
                    'name' => $request->name,
                    'ingredients' => $ingredients,
                    'prep_time' => $request->prep_time ?? 30, // Default 30 minutes
                    'cooking_time' => $request->cooking_time ?? 30, // Default 30 minutes
                    'serving_size' => $request->serving_size ?? 50 // Default 50 servings
                ]
            );

            // Update meal ingredients if provided
            if ($request->has('meal_ingredients') && !empty($request->meal_ingredients)) {
                // Delete existing meal ingredients
                $meal->mealIngredients()->delete();

                // Create new meal ingredients
                foreach ($request->meal_ingredients as $ingredientData) {
                    $inventoryItem = \App\Models\Inventory::find($ingredientData['inventory_id']);
                    if ($inventoryItem) {
                        \App\Models\MealIngredient::create([
                            'meal_id' => $meal->id,
                            'inventory_id' => $ingredientData['inventory_id'],
                            'quantity_per_serving' => $ingredientData['quantity_per_serving'],
                            'unit' => $inventoryItem->unit
                        ]);
                    }
                }
            }

            \Log::info('Menu updated successfully', [
                'meal_id' => $meal->id,
                'data' => $meal->toArray()
            ]);

            // Automatically deduct ingredients from inventory when meal is added to plan
            if ($request->has('deduct_ingredients') && $request->deduct_ingredients === true) {
                $meal->deductIngredients($meal->serving_size);
            }

            // IMPORTANT: Sync to DailyMenuUpdate for today if this meal applies to today
            $this->syncMealToDailyUpdate($meal);

            // Send notifications to kitchen and students about menu update
            $notificationService = new \App\Services\NotificationService();
            $notificationService->menuUpdated([
                'day' => $request->day,
                'meal_type' => $request->meal_type,
                'meal_name' => $request->name,
                'week_cycle' => $request->week_cycle
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meal updated successfully' .
                           ($request->has('deduct_ingredients') && $request->deduct_ingredients ?
                            ' and ingredients deducted from inventory' : ''),
                'meal' => $meal
            ]);
        } catch (\Exception $e) {
            \Log::error('Menu update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update meal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created meal in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ingredients' => 'required|array',
            'prep_time' => 'required|integer|min:0',
            'cooking_time' => 'required|integer|min:0',
            'serving_size' => 'required|integer|min:1',
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'day_of_week' => 'required|string',
            'week_cycle' => 'required|integer|in:1,2,3,4',
            'meal_ingredients' => 'nullable|array',
            'meal_ingredients.*.inventory_id' => 'required_with:meal_ingredients|exists:inventory,id',
            'meal_ingredients.*.quantity_per_serving' => 'required_with:meal_ingredients|numeric|min:0.001'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check ingredient availability if meal ingredients are provided
        if ($request->has('meal_ingredients') && !empty($request->meal_ingredients)) {
            $missingIngredients = [];
            $servingSize = $request->serving_size;

            foreach ($request->meal_ingredients as $ingredientData) {
                $inventoryItem = \App\Models\Inventory::find($ingredientData['inventory_id']);
                if ($inventoryItem) {
                    $requiredQuantity = $ingredientData['quantity_per_serving'] * $servingSize;
                    if ($inventoryItem->quantity < $requiredQuantity) {
                        $missingIngredients[] = [
                            'name' => $inventoryItem->name,
                            'required' => $requiredQuantity,
                            'available' => $inventoryItem->quantity,
                            'shortage' => $requiredQuantity - $inventoryItem->quantity,
                            'unit' => $inventoryItem->unit
                        ];
                    }
                }
            }

            if (!empty($missingIngredients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient ingredients to create this menu',
                    'missing_ingredients' => $missingIngredients,
                    'suggestion' => 'Please create a purchase order for the missing ingredients first.'
                ], 400);
            }
        }

        $meal = Meal::create([
            'name' => $request->name,
            'ingredients' => $request->ingredients,
            'prep_time' => $request->prep_time,
            'cooking_time' => $request->cooking_time,
            'serving_size' => $request->serving_size,
            'meal_type' => $request->meal_type,
            'day_of_week' => $request->day_of_week,
            'week_cycle' => $request->week_cycle,
        ]);

        // Create meal ingredients if provided
        if ($request->has('meal_ingredients') && !empty($request->meal_ingredients)) {
            foreach ($request->meal_ingredients as $ingredientData) {
                $inventoryItem = \App\Models\Inventory::find($ingredientData['inventory_id']);
                if ($inventoryItem) {
                    \App\Models\MealIngredient::create([
                        'meal_id' => $meal->id,
                        'inventory_id' => $ingredientData['inventory_id'],
                        'quantity_per_serving' => $ingredientData['quantity_per_serving'],
                        'unit' => $inventoryItem->unit
                    ]);
                }
            }
        }

        // Automatically deduct ingredients from inventory when meal is added to plan
        if ($request->has('deduct_ingredients') && $request->deduct_ingredients === true) {
            $meal->deductIngredients($meal->serving_size);
        }

        // IMPORTANT: Sync to DailyMenuUpdate for today if this meal applies to today
        $this->syncMealToDailyUpdate($meal);

        // Send notifications to kitchen and students
        $notificationService = new \App\Services\NotificationService();
        $notificationService->menuCreated([
            'day' => $request->day_of_week,
            'meal_type' => $request->meal_type,
            'meal_name' => $request->name,
            'week_cycle' => $request->week_cycle
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meal created successfully' .
                       ($request->has('deduct_ingredients') && $request->deduct_ingredients ?
                        ' and ingredients deducted from inventory' : ''),
            'meal' => $meal
        ]);
    }

    /**
     * Check ingredient availability for a meal
     */
    public function checkIngredientAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meal_id' => 'required|exists:meals,id',
            'serving_size' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $meal = Meal::with('mealIngredients.inventoryItem')->find($request->meal_id);
        $servingSize = $request->serving_size ?? $meal->serving_size;

        $canBePrepared = $meal->canBePrepared($servingSize);
        $missingIngredients = $meal->getMissingIngredients($servingSize);

        return response()->json([
            'success' => true,
            'can_be_prepared' => $canBePrepared,
            'missing_ingredients' => $missingIngredients,
            'serving_size' => $servingSize
        ]);
    }

    /**
     * Get available inventory items for meal creation
     */
    public function getAvailableIngredients()
    {
        $ingredients = \App\Models\Inventory::where('quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'is_low_stock' => $item->isLowStock()
                ];
            });

        return response()->json([
            'success' => true,
            'ingredients' => $ingredients
        ]);
    }

    /**
     * Manually deduct ingredients for a meal
     */
    public function deductIngredients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meal_id' => 'required|exists:meals,id',
            'serving_size' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $meal = Meal::with('mealIngredients.inventoryItem')->find($request->meal_id);
        $servingSize = $request->serving_size ?? $meal->serving_size;

        if (!$meal->canBePrepared($servingSize)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient ingredients to prepare this meal',
                'missing_ingredients' => $meal->getMissingIngredients($servingSize)
            ], 400);
        }

        try {
            $meal->deductIngredients($servingSize);

            return response()->json([
                'success' => true,
                'message' => "Ingredients deducted for {$servingSize} servings of {$meal->name}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deduct ingredients: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified meal from storage.
     */
    public function destroy($id)
    {
        $meal = Meal::find($id);
        if (!$meal) {
            return response()->json([
                'success' => false,
                'message' => 'Meal not found.'
            ], 404);
        }
        $meal->delete();
        return response()->json([
            'success' => true,
            'message' => 'Meal deleted successfully.'
        ]);
    }

    /**
     * Get kitchen status for today's meals
     */
    public function getKitchenStatus()
    {
        try {
            // UNIFIED: Use WeekCycleService for consistent calculation
            $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
            $today = now()->toDateString();
            $dayOfWeek = $weekInfo['current_day'];
            $weekCycle = $weekInfo['week_cycle'];

            $todayMeals = Meal::forWeekCycle($weekCycle)
                ->forDay($dayOfWeek)
                ->get();

            $status = [];
            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                $meal = $todayMeals->where('meal_type', $mealType)->first();
                if ($meal) {
                    // Since meal_statuses table was removed, use a simple status based on meal existence
                    $status[$mealType] = 'Planned';
                } else {
                    $status[$mealType] = 'Not Planned';
                }
            }

            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get kitchen status', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load kitchen status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all meals for a specific week cycle
     */
    public function clearWeek(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'week_cycle' => 'required|integer|in:1,2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid week cycle',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deletedCount = Meal::where('week_cycle', $request->week_cycle)->delete();

            \Log::info('Week meals cleared', [
                'week_cycle' => $request->week_cycle,
                'deleted_count' => $deletedCount,
                'user' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$deletedCount} meals for Week {$request->week_cycle}",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to clear week meals', [
                'error' => $e->getMessage(),
                'week_cycle' => $request->week_cycle
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear meals: ' . $e->getMessage()
            ], 500);
        }
    }





    /**
     * Get cross-system integration data
     */
    public function getCrossSystemData()
    {
        try {
            // Get connected users count
            $connectedUsers = [
                'kitchen_staff' => User::where('user_role', 'kitchen')->count(),
                'students' => User::where('user_role', 'student')->count(),
                'total_users' => User::whereIn('user_role', ['kitchen', 'student'])->count()
            ];

            // Get kitchen status for today's meals
            $today = now()->toDateString();
            $dayOfWeek = strtolower(now()->format('l'));
            $weekOfMonth = now()->weekOfMonth;
            $weekCycle = ($weekOfMonth % 2 === 1) ? 1 : 2;

            $todayMeals = Meal::forWeekCycle($weekCycle)
                ->forDay($dayOfWeek)
                ->get();

            $kitchenStatus = [];
            foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                $meal = $todayMeals->where('meal_type', $mealType)->first();
                if ($meal) {
                    // Since meal_statuses table was removed, use simple status
                    $kitchenStatus[$mealType] = 'Planned';
                } else {
                    $kitchenStatus[$mealType] = 'Not Planned';
                }
            }

            // Get active polls
            $activePolls = KitchenMenuPoll::where('is_active', true)
                ->get()
                ->map(function ($poll) {
                    return [
                        'id' => $poll->id,
                        'meal_name' => $poll->meal_name, // Uses accessor
                        'poll_date' => $poll->poll_date->format('Y-m-d'),
                        'meal_type' => $poll->meal_type,
                        'status' => $poll->status, // Uses accessor
                        'responses_count' => $poll->total_responses
                    ];
                });

            // Get poll responses summary
            $pollResponses = KitchenMenuPollResponse::whereHas('poll', function ($query) {
                $query->where('status', '!=', 'draft');
            })->get()->groupBy('poll_id');

            // Get recent menu updates
            $recentMenuUpdates = Meal::orderBy('updated_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($meal) {
                    return [
                        'name' => $meal->name,
                        'day_of_week' => $meal->day_of_week,
                        'meal_type' => $meal->meal_type,
                        'week_cycle' => $meal->week_cycle,
                        'updated_at' => $meal->updated_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'connected_users' => $connectedUsers,
                    'kitchen_status' => $kitchenStatus,
                    'active_polls' => $activePolls,
                    'poll_responses' => $pollResponses,
                    'recent_menu_updates' => $recentMenuUpdates,
                    'integration_status' => [
                        'kitchen_connected' => $connectedUsers['kitchen_staff'] > 0,
                        'students_connected' => $connectedUsers['students'] > 0,
                        'polls_active' => $activePolls->count() > 0,
                        'real_time_sync' => true
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get cross-system data', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load cross-system data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync a meal from the Meal planning to DailyMenuUpdate table
     * SIMPLIFIED: Always sync to today if day matches, regardless of week cycle
     */
    private function syncMealToDailyUpdate($meal)
    {
        try {
            $today = now()->format('Y-m-d');
            $currentDay = strtolower(now()->format('l'));

            // Sync if this meal is for today's day of week (ignore week cycle)
            if ($meal->day_of_week === $currentDay) {
                \App\Models\DailyMenuUpdate::updateOrCreate(
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

                \Log::info('Meal synced to daily menu (simplified)', [
                    'meal_id' => $meal->id,
                    'date' => $today,
                    'day' => $currentDay,
                    'meal_type' => $meal->meal_type,
                    'meal_name' => $meal->name
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to sync meal to daily menu', [
                'error' => $e->getMessage(),
                'meal_id' => $meal->id ?? null
            ]);
        }
    }
}

