<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        // Check if cook has created any meals
        $hasMeals = Meal::exists();

        // Get current week cycle
        $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
        $weekCycle = $weekInfo['week_cycle'];

        return view('student.menu', [
            'hasMeals' => $hasMeals,
            'waitingForCook' => !$hasMeals,
            'weekCycle' => $weekCycle
        ]);
    }

    public function getMenu($weekCycle)
    {
        $meals = Meal::forWeekCycle($weekCycle)
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($dayMeals) {
                return $dayMeals->groupBy('meal_type')
                    ->map(function ($meal) {
                        return $meal->first();
                    });
            });

        return response()->json([
            'success' => true,
            'menu' => $meals
        ]);
    }
}