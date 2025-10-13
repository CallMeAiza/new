<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostAssessment;
use App\Models\Menu;
use App\Models\PreOrder;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostAssessmentController extends Controller
{
    /**
     * Display a listing of post-assessments for the kitchen team.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $mealType = $request->input('meal_type', 'lunch');
        
        // Get the menu items for the selected date and meal type
        $menuItems = Menu::where('date', $date)
            ->where('meal_type', $mealType)
            ->get();
            
        // Get pre-order counts for each menu item
        $preOrderCounts = PreOrder::where('date', $date)
            ->where('meal_type', $mealType)
            ->select('menu_id', DB::raw('count(*) as total_orders'))
            ->groupBy('menu_id')
            ->pluck('total_orders', 'menu_id')
            ->toArray();
            
        // Get post-assessments for the selected date and meal type
        $postAssessments = PostAssessment::where('date', $date)
            ->where('meal_type', $mealType)
            ->get()
            ->keyBy('menu_id');
            
        // Get dates with menus for the filter
        $menuDates = Menu::select('date')
            ->distinct()
            ->where('date', '<=', now()->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->limit(14)
            ->pluck('date');
            
        // Get recent report history for the current user
        $reportHistory = PostAssessment::where('assessed_by', Auth::user()->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return view('kitchen.post-assessment', compact(
            'menuItems', 
            'preOrderCounts', 
            'postAssessments', 
            'date', 
            'mealType', 
            'menuDates',
            'reportHistory'
        ));
    }
    
    /**
     * Store a newly created post-assessment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \Log::info('🍽️ Kitchen Post-Assessment Store Request', [
            'user_id' => Auth::user()->user_id,
            'date' => $request->input('date'),
            'meal_type' => $request->input('meal_type'),
            'items_count' => $request->has('items') ? count($request->input('items', [])) : 0,
            'has_image' => $request->hasFile('report_image')
        ]);

        // Handle multiple image upload validation
        $hasValidImages = false;
        if ($request->hasFile('report_images')) {
            $files = $request->file('report_images');
            foreach ($files as $file) {
                if ($file->isValid() && $file->getSize() > 0) {
                    $hasValidImages = true;
                    break;
                }
            }
        }

        $rules = [
            'date' => 'required|date|before_or_equal:today',
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'reported_by' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
        ];

        // Validate multiple images if present
        if ($hasValidImages) {
            $rules['report_images'] = 'array|max:5'; // Max 5 images
            $rules['report_images.*'] = 'image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB max per image
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::warning('❌ Kitchen Post-Assessment Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'user_id' => Auth::user()->user_id
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Additional validation: Check if the meal has already occurred
            $selectedDate = \Carbon\Carbon::parse($request->date);
            $now = now();
            
            // If selecting today's date, check if meal time has passed
            if ($selectedDate->isToday()) {
                $currentHour = $now->hour;
                $mealTimePassed = false;
                
                switch ($request->meal_type) {
                    case 'breakfast':
                        $mealTimePassed = $currentHour >= 6; // 6:00 AM
                        break;
                    case 'lunch':
                        $mealTimePassed = $currentHour >= 10; // 10:00 AM
                        break;
                    case 'dinner':
                        $mealTimePassed = $currentHour >= 15; // 3:00 PM
                        break;
                }
                
                if (!$mealTimePassed) {
                    DB::rollBack();
                    $message = 'Cannot report leftovers for "' . ucfirst($request->meal_type) . '" as the meal time has not yet occurred today.';
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 422);
                    }
                    
                    return redirect()->back()
                        ->with('error', $message)
                        ->withInput();
                }
            }

            // Check if assessment already exists for this date and meal type (database has unique constraint)
            $existingAssessment = PostAssessment::where('date', $request->date)
                ->where('meal_type', $request->meal_type)
                ->first();

            if ($existingAssessment) {
                DB::rollBack();

                $message = 'A leftover report for this date and meal type already exists. Only one report per meal per day is allowed.';

                \Log::warning('❌ Duplicate post-assessment attempt', [
                    'date' => $request->date,
                    'meal_type' => $request->meal_type,
                    'existing_assessment_id' => $existingAssessment->id,
                    'existing_assessed_by' => $existingAssessment->assessed_by,
                    'current_user' => Auth::user()->user_id
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', $message)
                    ->withInput();
            }

            // Process each food item
            $itemsData = [];

            foreach ($request->items as $item) {
                $itemsData[] = [
                    'name' => $item['name'],
                    'prepared_quantity' => 0,
                    'leftover_quantity' => 0
                ];
            }

            \Log::info('🍽️ Kitchen Post-Assessment Items Data', [
                'items_count' => count($itemsData),
                'items_data' => $itemsData
            ]);

            // Calculate wastage percentage
            $wastagePercentage = 0;

            // Handle multiple image uploads
            $imagePaths = [];
            $firstImagePath = null;
            if ($hasValidImages && $request->hasFile('report_images')) {
                try {
                    $images = $request->file('report_images');
                    
                    // Create directory if it doesn't exist
                    $uploadPath = public_path('uploads/post-assessments');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    foreach ($images as $index => $image) {
                        if ($image->isValid()) {
                            $imageName = 'leftover_report_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $image->move($uploadPath, $imageName);
                            $imagePath = 'uploads/post-assessments/' . $imageName;
                            $imagePaths[] = $imagePath;
                            
                            // Store first image in old column for backward compatibility
                            if ($index === 0) {
                                $firstImagePath = $imagePath;
                            }

                            \Log::info('📸 Image uploaded successfully', [
                                'index' => $index,
                                'original_name' => $image->getClientOriginalName(),
                                'saved_as' => $imageName,
                                'path' => $imagePath
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('❌ Image upload failed', [
                        'error' => $e->getMessage()
                    ]);
                    // Continue without images if upload fails
                }
            }

            // Find the menu for this date and meal type
            $menu = Menu::where('date', $request->date)
                ->where('meal_type', $request->meal_type)
                ->first();

            // Create the PostAssessment record
            $assessment = PostAssessment::create([
                'date' => $request->date,
                'meal_type' => $request->meal_type,
                'menu_id' => $menu ? $menu->id : null,
                'notes' => $request->notes,
                'image_path' => $firstImagePath, // Backward compatibility
                'image_paths' => $imagePaths, // New multiple images field
                'items' => $itemsData,
                'assessed_by' => Auth::user()->user_id, // Use the actual user_id primary key
                'reported_by' => $request->reported_by,
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            // Notify the cook
            app(NotificationService::class)->postMealReportSubmitted([
                'assessment_id' => $assessment->id,
                'meal_type' => $request->meal_type,
                'date' => $request->date,
                'items_count' => count($itemsData),
                'items' => $itemsData,
                'submitted_by' => Auth::user()->name,
            ]);

            \Log::info('✅ Kitchen Post-Assessment Created Successfully', [
                'assessment_id' => $assessment->id,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leftover report submitted successfully!',
                    'assessment_id' => $assessment->id
                ]);
            }

            return redirect()->route('kitchen.post-assessment')
                ->with('success', 'Leftover report submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Kitchen Post-Assessment Creation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit leftover report. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to submit leftover report. Please try again.')
                ->withInput();
        }
    }

    /**
     * Get meals for a specific date and meal type for auto-population
     */
    public function getMealsForDate(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'meal_type' => 'required|in:breakfast,lunch,dinner'
            ]);

            $date = $request->input('date');
            $mealType = $request->input('meal_type');

            $meals = collect();

            // Method 1: Check DailyMenuUpdate table first (most accurate for today's menu)
            $dailyMenus = \App\Models\DailyMenuUpdate::where('menu_date', $date)
                ->where('meal_type', $mealType)
                ->get(['id', 'meal_name as name', 'ingredients as description']);

            if ($dailyMenus->isNotEmpty()) {
                $meals = $dailyMenus->map(function($menu) {
                    return [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'description' => $menu->description,
                        'source' => 'daily_menu'
                    ];
                });
            } else {
                // Method 2: Check Meal model for weekly planning (convert date to day of week)
                $selectedDate = \Carbon\Carbon::parse($date);
                $dayOfWeek = strtolower($selectedDate->format('l')); // monday, tuesday, etc.
                
                // Get week cycle for the selected date
                $weekInfo = \App\Services\WeekCycleService::getWeekInfo($selectedDate);
                $weekCycle = $weekInfo['week_cycle'];

                $weeklyMeals = \App\Models\Meal::where('day_of_week', $dayOfWeek)
                    ->where('week_cycle', $weekCycle)
                    ->where('meal_type', $mealType)
                    ->get(['id', 'name', 'ingredients']);

                if ($weeklyMeals->isNotEmpty()) {
                    // Auto-populate DailyMenuUpdate for future reference
                    foreach ($weeklyMeals as $meal) {
                        \App\Models\DailyMenuUpdate::firstOrCreate(
                            [
                                'menu_date' => $date,
                                'meal_type' => $mealType
                            ],
                            [
                                'meal_name' => $meal->name,
                                'ingredients' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : $meal->ingredients,
                                'estimated_portions' => $meal->serving_size ?? 0,
                                'updated_by' => Auth::user()->user_id ?? null
                            ]
                        );
                    }

                    $meals = $weeklyMeals->map(function($meal) {
                        return [
                            'id' => $meal->id,
                            'name' => $meal->name,
                            'description' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : $meal->ingredients,
                            'source' => 'weekly_meal_auto_populated'
                        ];
                    });
                } else {
                    // Method 3: Check Menu model for weekly planning (with day field)
                    $weeklyMenus = Menu::where('day', ucfirst($dayOfWeek))
                        ->where('week_cycle', $weekCycle)
                        ->where('meal_type', $mealType)
                        ->where('is_available', true)
                        ->get(['id', 'name', 'description']);

                    if ($weeklyMenus->isNotEmpty()) {
                        // Auto-populate DailyMenuUpdate for future reference
                        foreach ($weeklyMenus as $menu) {
                            \App\Models\DailyMenuUpdate::firstOrCreate(
                                [
                                    'menu_date' => $date,
                                    'meal_type' => $mealType
                                ],
                                [
                                    'meal_name' => $menu->name,
                                    'ingredients' => $menu->description,
                                    'estimated_portions' => 0,
                                    'updated_by' => Auth::user()->user_id ?? null
                                ]
                            );
                        }

                        $meals = $weeklyMenus->map(function($menu) {
                            return [
                                'id' => $menu->id,
                                'name' => $menu->name,
                                'description' => $menu->description,
                                'source' => 'weekly_menu_auto_populated'
                            ];
                        });
                    }
                }
            }

            \Log::info('🍽️ Fetching meals for date', [
                'date' => $date,
                'meal_type' => $mealType,
                'day_of_week' => isset($dayOfWeek) ? $dayOfWeek : null,
                'week_cycle' => isset($weekCycle) ? $weekCycle : null,
                'meals_found' => $meals->count(),
                'meals' => $meals->toArray()
            ]);

            return response()->json([
                'success' => true,
                'meals' => $meals,
                'date' => $date,
                'meal_type' => $mealType
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch meals for date', [
                'error' => $e->getMessage(),
                'date' => $request->input('date'),
                'meal_type' => $request->input('meal_type'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch meals for selected date',
                'meals' => []
            ]);
        }
    }

    /**
     * Display the specified post-assessment report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $report = PostAssessment::where('id', $id)
                ->where('assessed_by', Auth::user()->user_id)
                ->first();

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found or access denied'
                ], 404);
            }

            // Format the report data for display
            $imagePaths = [];
            if ($report->image_paths && is_array($report->image_paths)) {
                foreach ($report->image_paths as $path) {
                    $imagePaths[] = asset($path);
                }
            } elseif ($report->image_path) {
                // Backward compatibility - if only old single image exists
                $imagePaths[] = asset($report->image_path);
            }

            $reportData = [
                'id' => $report->id,
                'date' => $report->date->format('M d, Y'),
                'meal_type' => ucfirst($report->meal_type),
                'food_item' => $report->items[0]['name'] ?? 'N/A',
                'notes' => $report->notes,
                'image_path' => $report->image_path ? asset($report->image_path) : null, // Backward compatibility
                'image_paths' => $imagePaths, // Multiple images
                'submitted_at' => $report->created_at->format('M d, Y h:i A'),
                'items' => $report->items
            ];

            return response()->json([
                'success' => true,
                'report' => $reportData
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Failed to fetch report details', [
                'report_id' => $id,
                'user_id' => Auth::user()->user_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load report details'
            ], 500);
        }
    }

    /**
     * Update the specified post-assessment report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        \Log::info('🔄 POST-ASSESSMENT UPDATE REQUEST RECEIVED', [
            'report_id' => $id,
            'user_id' => Auth::user()->user_id,
            'has_notes' => $request->has('notes'),
            'notes_value' => $request->input('notes'),
            'has_file' => $request->hasFile('report_image'),
            'all_request_data' => $request->all(),
            'files' => $request->allFiles()
        ]);

        try {
            $report = PostAssessment::where('id', $id)
                ->where('assessed_by', Auth::user()->user_id)
                ->first();

            if (!$report) {
                \Log::warning('❌ Report not found or access denied', [
                    'report_id' => $id,
                    'user_id' => Auth::user()->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found or access denied'
                ], 404);
            }

            \Log::info('📋 Current report data before update', [
                'report_id' => $report->id,
                'current_notes' => $report->notes,
                'current_image_path' => $report->image_path
            ]);

            // Validate the request
            $rules = [
                'notes' => 'nullable|string|max:1000',
                'report_images' => 'nullable|array|max:5', // Max 5 images
                'report_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
                'delete_images' => 'nullable|array', // Array of image paths to delete
                'delete_images.*' => 'string'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                \Log::warning('❌ Validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update notes
            if ($request->has('notes')) {
                $oldNotes = $report->notes;
                $report->notes = $request->notes;
                \Log::info('📝 Updating notes', [
                    'report_id' => $report->id,
                    'old_notes' => $oldNotes,
                    'new_notes' => $request->notes
                ]);
            }

            // Get current image paths
            $currentImagePaths = $report->image_paths ?? [];
            if (!is_array($currentImagePaths)) {
                $currentImagePaths = [];
            }
            
            // Handle image deletions
            if ($request->has('delete_images')) {
                $imagesToDelete = $request->input('delete_images');
                foreach ($imagesToDelete as $imageToDelete) {
                    // Remove from array
                    $currentImagePaths = array_filter($currentImagePaths, function($path) use ($imageToDelete) {
                        return $path !== $imageToDelete;
                    });
                    
                    // Delete physical file
                    if (file_exists(public_path($imageToDelete))) {
                        unlink(public_path($imageToDelete));
                        \Log::info('🗑️ Deleted image', ['path' => $imageToDelete]);
                    }
                }
                $currentImagePaths = array_values($currentImagePaths); // Re-index array
            }

            // Handle new image uploads
            if ($request->hasFile('report_images')) {
                try {
                    $images = $request->file('report_images');
                    
                    // Create directory if it doesn't exist
                    $uploadPath = public_path('uploads/post-assessments');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                        \Log::info('📁 Created upload directory', ['path' => $uploadPath]);
                    }

                    foreach ($images as $index => $image) {
                        if ($image->isValid()) {
                            \Log::info('📸 Processing image upload', [
                                'index' => $index,
                                'original_name' => $image->getClientOriginalName(),
                                'size' => $image->getSize(),
                                'mime_type' => $image->getMimeType()
                            ]);

                            $imageName = 'leftover_report_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $image->move($uploadPath, $imageName);
                            $imagePath = 'uploads/post-assessments/' . $imageName;
                            
                            $currentImagePaths[] = $imagePath;

                            \Log::info('✅ Image uploaded successfully', [
                                'index' => $index,
                                'path' => $imagePath
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('❌ Image upload failed during update', [
                        'report_id' => $report->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Image upload failed: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Update image_paths and image_path (backward compatibility)
            $report->image_paths = $currentImagePaths;
            $report->image_path = !empty($currentImagePaths) ? $currentImagePaths[0] : null;

            $report->save();

            \Log::info('✅ Post-Assessment Updated Successfully', [
                'report_id' => $report->id,
                'final_notes' => $report->notes,
                'final_image_path' => $report->image_path,
                'changes_made' => $report->getChanges()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully!',
                'report_id' => $report->id,
                'updated_image_path' => $report->image_path ? asset($report->image_path) : null
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Post-Assessment Update Failed', [
                'report_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update report. Please try again.'
            ], 500);
        }
    }

    /**
     * Submit the report to cook (mark as completed and ready for cook review)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function submit($id)
    {
        \Log::info('📤 POST-ASSESSMENT SUBMIT REQUEST', [
            'report_id' => $id,
            'user_id' => Auth::user()->user_id
        ]);

        try {
            $report = PostAssessment::where('id', $id)
                ->where('assessed_by', Auth::user()->user_id)
                ->first();

            if (!$report) {
                \Log::warning('❌ Report not found or access denied', [
                    'report_id' => $id,
                    'user_id' => Auth::user()->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found or access denied'
                ], 404);
            }

            // Mark report as completed and submitted
            $report->is_completed = true;
            $report->completed_at = now();
            $report->save();

            \Log::info('✅ Post-Assessment Submitted Successfully', [
                'report_id' => $report->id,
                'submitted_by' => Auth::user()->name,
                'submitted_at' => $report->completed_at
            ]);

            // Notify cook about the submission
            app(\App\Services\NotificationService::class)->postMealReportSubmitted([
                'assessment_id' => $report->id,
                'meal_type' => $report->meal_type,
                'date' => $report->date->format('Y-m-d'),
                'items_count' => count($report->items ?? []),
                'items' => $report->items ?? [],
                'submitted_by' => Auth::user()->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully to Cook!',
                'report_id' => $report->id
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Post-Assessment Submit Failed', [
                'report_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete the specified post-assessment report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        \Log::info('🗑️ POST-ASSESSMENT DELETE REQUEST', [
            'report_id' => $id,
            'user_id' => Auth::user()->user_id
        ]);

        try {
            $report = PostAssessment::where('id', $id)
                ->where('assessed_by', Auth::user()->user_id)
                ->first();

            if (!$report) {
                \Log::warning('❌ Report not found or access denied', [
                    'report_id' => $id,
                    'user_id' => Auth::user()->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found or access denied'
                ], 404);
            }

            DB::beginTransaction();

            // Delete all images
            $imagePaths = $report->image_paths ?? [];
            if (!is_array($imagePaths) && $report->image_path) {
                $imagePaths = [$report->image_path];
            }

            foreach ($imagePaths as $imagePath) {
                if ($imagePath && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                    \Log::info('🗑️ Deleted image file', ['path' => $imagePath]);
                }
            }

            // Delete the report
            $report->delete();

            DB::commit();

            \Log::info('✅ Post-Assessment Deleted Successfully', [
                'report_id' => $id,
                'deleted_by' => Auth::user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Post-Assessment Delete Failed', [
                'report_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report. Please try again.'
            ], 500);
        }
    }
}
