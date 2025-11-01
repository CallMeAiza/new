<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostAssessment;
use App\Models\Menu;
use App\Models\PreOrder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostAssessmentController extends Controller
{
    /**
     * Display a listing of post-assessments.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        \Log::info('🍽️ Cook Post-Assessment Index Request', [
            'filters' => $request->all(),
            'user_id' => Auth::id()
        ]);

        $date = $request->input('date');
        $mealType = $request->input('meal_type');

        // Build query for post-assessments sent by kitchen team
        $query = PostAssessment::with(['assessedBy', 'menu'])
            ->where('is_completed', true)
            ->orderByRaw('(CASE WHEN completed_at IS NOT NULL THEN completed_at ELSE created_at END) DESC');

        // Apply filters if provided
        if ($date) {
            $query->where('date', $date);
        }

        if ($mealType) {
            $query->where('meal_type', $mealType);
        }

        // Get assessments (reports from kitchen)
        $assessments = $query->get();

        // Get dates with post-assessments for the filter
        $assessmentDates = PostAssessment::select('date')
            ->distinct()
            ->where('is_completed', true)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->pluck('date');

        \Log::info('📊 Cook Post-Assessment Data Loaded', [
            'total_assessments' => $assessments->count(),
            'date_filter' => $date,
            'meal_type_filter' => $mealType
        ]);

        return view('cook.post-assessment', compact(
            'assessments',
            'date',
            'mealType',
            'assessmentDates'
        ));
    }

    /**
     * Delete a post-assessment report
     */
    public function destroy($id)
    {
        \Log::info('🗑️ Cook Post-Assessment Delete Request', [
            'assessment_id' => $id,
            'user_id' => Auth::id(),
            'assessment_exists' => PostAssessment::where('id', $id)->exists()
        ]);

        try {
            $assessment = PostAssessment::findOrFail($id);

            // Store image path for cleanup
            $imagePath = $assessment->image_path;

            // Delete the assessment record
            $assessment->delete();

            // Clean up associated image file if it exists
            if ($imagePath && file_exists(public_path($imagePath))) {
                try {
                    unlink(public_path($imagePath));
                    \Log::info('📸 Image file deleted', ['path' => $imagePath]);
                } catch (\Exception $e) {
                    \Log::warning('⚠️ Failed to delete image file', [
                        'path' => $imagePath,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            \Log::info('✅ Cook Post-Assessment Deleted Successfully', [
                'assessment_id' => $id,
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment report deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('❌ Assessment not found for deletion', [
                'assessment_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Assessment report not found'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('❌ Cook Post-Assessment Delete Failed', [
                'assessment_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all post-assessment reports (bulk delete)
     */
    public function deleteAll(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No assessments selected for deletion.'
            ], 400);
        }

        $deleted = PostAssessment::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "$deleted assessment(s) deleted successfully."
        ]);
    }

}
