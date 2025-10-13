<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Meal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    /**
     * Display feedback for cook
     */
    public function index(Request $request)
    {
        $query = Feedback::with(['student', 'meal'])
            ->orderBy('created_at', 'desc');

        // Filter by specific date if provided
        if ($request->has('date') && $request->date) {
            $query->whereDate('meal_date', $request->date);
        }

        // Filter by meal type if provided
        if ($request->has('meal_type') && $request->meal_type) {
            $query->where('meal_type', $request->meal_type);
        }

        // Filter by rating if provided
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Filter by anonymous status
        if ($request->has('anonymous_filter')) {
            if ($request->anonymous_filter === 'anonymous') {
                $query->where('is_anonymous', true);
            } elseif ($request->anonymous_filter === 'identified') {
                $query->where('is_anonymous', false);
            }
        }

        // Search in comments and suggestions
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comments', 'like', "%{$search}%")
                  ->orWhere('suggestions', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(15)->appends($request->query());

        // Get enhanced feedback statistics
        $stats = [
            'total_feedback' => Feedback::count(),
            'average_rating' => round(Feedback::avg('rating'), 1),
            'recent_feedback' => Feedback::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'anonymous_feedback' => Feedback::where('is_anonymous', true)->count(),
            'identified_feedback' => Feedback::where('is_anonymous', false)->count(),
            'rating_distribution' => [
                5 => Feedback::where('rating', 5)->count(),
                4 => Feedback::where('rating', 4)->count(),
                3 => Feedback::where('rating', 3)->count(),
                2 => Feedback::where('rating', 2)->count(),
                1 => Feedback::where('rating', 1)->count(),
            ],
            'meal_type_stats' => [
                'breakfast' => [
                    'count' => Feedback::where('meal_type', 'breakfast')->count(),
                    'avg_rating' => round(Feedback::where('meal_type', 'breakfast')->avg('rating'), 1)
                ],
                'lunch' => [
                    'count' => Feedback::where('meal_type', 'lunch')->count(),
                    'avg_rating' => round(Feedback::where('meal_type', 'lunch')->avg('rating'), 1)
                ],
                'dinner' => [
                    'count' => Feedback::where('meal_type', 'dinner')->count(),
                    'avg_rating' => round(Feedback::where('meal_type', 'dinner')->avg('rating'), 1)
                ]
            ]
        ];

        return view('cook.feedback.index', compact('feedbacks', 'stats'));
    }
    
    /**
     * Show detailed feedback for a specific meal
     */
    public function show($id)
    {
        $feedback = Feedback::with(['student', 'meal'])->findOrFail($id);

        return view('cook.feedback.show', compact('feedback'));
    }

    /**
     * Delete a specific feedback
     */
    public function destroy($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);

            \Log::info('Cook deleting feedback', [
                'feedback_id' => $id,
                'user_id' => auth()->id(),
                'feedback_rating' => $feedback->rating,
                'feedback_meal_type' => $feedback->meal_type
            ]);

            $feedback->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feedback deleted successfully'
                ]);
            }

            return redirect()->route('cook.feedback')
                ->with('success', 'Feedback deleted successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to delete feedback', [
                'feedback_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete feedback'
                ], 500);
            }

            return redirect()->route('cook.feedback')
                ->with('error', 'Failed to delete feedback');
        }
    }

    /**
     * Delete all feedback
     */
    public function destroyAll()
    {
        try {
            $count = Feedback::count();

            \Log::info('Cook deleting all feedback', [
                'total_feedback_count' => $count,
                'user_id' => auth()->id()
            ]);

            Feedback::truncate(); // This is faster than delete() for clearing all records

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "All {$count} feedback records deleted successfully"
                ]);
            }

            return redirect()->route('cook.feedback')
                ->with('success', "All {$count} feedback records deleted successfully");

        } catch (\Exception $e) {
            \Log::error('Failed to delete all feedback', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete all feedback'
                ], 500);
            }

            return redirect()->route('cook.feedback')
                ->with('error', 'Failed to delete all feedback');
        }
    }
}
