<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Menu;
use App\Models\Meal;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the student feedback form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get student's previous feedback
        $studentFeedback = Feedback::where('student_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Build today's meal options for dropdown (based on unified week cycle service)
        try {
            $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
            $todayDay = $weekInfo['current_day'];
            $currentCycle = $weekInfo['week_cycle'];

            $todayMeals = Meal::forWeekCycle($currentCycle)
                ->forDay($todayDay)
                ->get(['id', 'name', 'meal_type']);

            // Provide just names for the dropdown; duplicate names will still show
            $mealOptions = $todayMeals->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'meal_type' => $m->meal_type,
                ];
            });
        } catch (\Throwable $e) {
            $mealOptions = collect();
        }

        return view('student.feedback.index', compact('studentFeedback', 'mealOptions'));
    }



    /**
     * Store a newly created feedback in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'meal_name' => 'required|string|max:255',
            'meal_type' => 'required|in:breakfast,lunch,dinner',
            'meal_date' => 'required|date|before_or_equal:today',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'suggestions' => 'nullable|string|max:500',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // Allow multiple feedback submissions - students can provide feedback as many times as they want
        // Create new feedback entry each time
        $feedback = Feedback::create([
            'student_id' => $user->user_id, // Use the actual user_id primary key
            'meal_id' => null, // No longer required since we're allowing manual input
            'meal_date' => $request->meal_date,
            'meal_type' => $request->meal_type,
            'meal_name' => $request->meal_name,
            'rating' => $request->rating,
            'comments' => $request->comment,
            'suggestions' => $request->suggestions,
            'food_quality' => [],
            // Removed dietary_concerns field
            'is_anonymous' => $request->has('is_anonymous') && $request->is_anonymous,
        ]);

        // Send notifications to cook and kitchen staff
        $notificationService = new NotificationService();
        $notificationService->feedbackSubmitted([
            'meal_name' => $request->meal_name,
            'meal_type' => $request->meal_type,
            'rating' => $request->rating,
            'student_name' => $request->has('is_anonymous') && $request->is_anonymous ? 'Anonymous' : $user->name,
            'feedback_id' => $feedback->id
        ]);

        return redirect()->route('student.feedback')->with('success', 'Thank you for your feedback!');
    }

    /**
     * Return meals available for a given date for populating the dropdown.
     */
    public function mealsForDate(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);

            $date = \Carbon\Carbon::parse($request->input('date'));
            $weekInfo = \App\Services\WeekCycleService::getWeekInfo($date);

            $day = $weekInfo['current_day'];
            $cycle = $weekInfo['week_cycle'];

            $meals = Meal::forWeekCycle($cycle)
                ->forDay($day)
                ->get(['id','name','meal_type'])
                ->map(function($m){
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'meal_type' => $m->meal_type,
                    ];
                });

            return response()->json([
                'success' => true,
                'meals' => $meals
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load meals',
            ], 400);
        }
    }

    /**
     * Delete a specific feedback entry (only the student's own feedback)
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            // Only allow students to delete their own feedback
            $feedback = Feedback::where('student_id', $user->user_id)->findOrFail($id);

            $feedback->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feedback deleted successfully'
                ]);
            }

            return redirect()->route('student.feedback')
                ->with('success', 'Feedback deleted successfully');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete feedback'
                ], 500);
            }

            return redirect()->route('student.feedback')
                ->with('error', 'Failed to delete feedback');
        }
    }

    /**
     * Delete all feedback entries for the current student
     */
    public function destroyAll()
    {
        \Log::info('Student feedback destroyAll method called', [
            'user_id' => Auth::user()->user_id ?? 'not_authenticated',
            'request_method' => request()->method(),
            'request_url' => request()->url()
        ]);

        try {
            $user = Auth::user();

            if (!$user) {
                \Log::error('User not authenticated in destroyAll');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            \Log::info('About to query feedback', [
                'user_id' => $user->user_id,
                'student_id_field' => 'student_id'
            ]);

            // Only delete the current student's feedback
            $count = Feedback::where('student_id', $user->user_id)->count();

            \Log::info('Feedback count found', ['count' => $count]);

            Feedback::where('student_id', $user->user_id)->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "All {$count} feedback records deleted successfully"
                ]);
            }

            return redirect()->route('student.feedback')
                ->with('success', "All {$count} feedback records deleted successfully");

        } catch (\Exception $e) {
            \Log::error('Failed to delete all student feedback', [
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->user_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete all feedback: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('student.feedback')
                ->with('error', 'Failed to delete all feedback');
        }
    }
}
