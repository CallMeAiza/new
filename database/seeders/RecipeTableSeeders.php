<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MenuList;

class RecipeTableSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $week1And3Menus = [
            ['recipe_day' => 'monday', 'Breakfast' => 'Chicken Loaf & Hot Cereal', 'Lunch' => 'Crispy Fried Fish', 'Dinner' => 'Sautéed Cabbage'],
            ['recipe_day' => 'tuesday', 'Breakfast' => 'Odong Noodles with Sardines', 'Lunch' => 'Golden Fried Chicken', 'Dinner' => 'Stir-Fried Baguio Beans'],
            ['recipe_day' => 'wednesday', 'Breakfast' => 'Grilled Hotdogs', 'Lunch' => 'Braised Porkchop', 'Dinner' => 'Eggplant & Egg Stir-Fry'],
            ['recipe_day' => 'thursday', 'Breakfast' => 'Boiled Eggs & Hot Cereal', 'Lunch' => 'Savory Ground Pork', 'Dinner' => 'Mixed Vegetable Chopsuey'],
            ['recipe_day' => 'friday', 'Breakfast' => 'Smoked Ham Slices', 'Lunch' => 'Crispy Fried Chicken', 'Dinner' => 'Hearty Monggo Stew'],
            ['recipe_day' => 'saturday', 'Breakfast' => 'Sautéed Sardines & Egg', 'Lunch' => 'Burger Steak with Gravy', 'Dinner' => 'Utan Bisaya & Dried Fish'],
            ['recipe_day' => 'sunday', 'Breakfast' => 'Tomato & Egg Medley', 'Lunch' => 'Pan-Fried Fish Fillet', 'Dinner' => 'Sari-Sari Vegetable Mix'],
        ];

        $week2And4Menus = [
            ['recipe_day' => 'monday', 'Breakfast' => 'Grilled Chorizo', 'Lunch' => 'Classic Chicken Adobo', 'Dinner' => 'Sautéed String Beans'],
            ['recipe_day' => 'tuesday', 'Breakfast' => 'Fluffy Scrambled Eggs & Hot Cereal', 'Lunch' => 'Crispy Fried Fish', 'Dinner' => 'Eggplant & Egg Stir-Fry'],
            ['recipe_day' => 'wednesday', 'Breakfast' => 'Sautéed Sardines & Egg', 'Lunch' => 'Savory Ground Pork', 'Dinner' => 'Steamed Squash & Dried Fish'],
            ['recipe_day' => 'thursday', 'Breakfast' => 'Pan-Fried Luncheon Meat', 'Lunch' => 'Golden Fried Chicken', 'Dinner' => 'Mixed Vegetable Chopsuey'],
            ['recipe_day' => 'friday', 'Breakfast' => 'Sotanghon Noodle Stir-Fry', 'Lunch' => 'Pork Menudo Stew', 'Dinner' => 'Hearty Monggo Stew'],
            ['recipe_day' => 'saturday', 'Breakfast' => 'Grilled Hotdogs', 'Lunch' => 'Savory Meatballs', 'Dinner' => 'Utan Bisaya & Dried Fish'],
            ['recipe_day' => 'sunday', 'Breakfast' => 'Bitter Gourd & Egg with Hot Cereal', 'Lunch' => 'Pan-Fried Fish Fillet', 'Dinner' => 'Vegetable Pakbit'],
        ];

        // Create meals for Week 1 & 3
        foreach($week1And3Menus as $menu){
            foreach(['breakfast','lunch','dinner'] as $mealType){
                MenuList::create([
                    'name' => $menu[ucfirst($mealType)],
                    'meal_type' => $mealType,
                    'day_of_week' => $menu['recipe_day'],
                    'week_cycle' => 1,
                    'ingredients' => json_encode([]), // Empty ingredients array - will be filled by cook
                    'prep_time' => rand(15, 60), // Random prep time between 15-60 minutes
                    'cooking_time' => rand(20, 90), // Random cooking time between 20-90 minutes
                    'serving_size' => rand(50, 200), // Random serving size between 50-200 people
                ]);
            }
        }

        // Create meals for Week 2 & 4
        foreach($week2And4Menus as $menu){
            foreach(['breakfast','lunch','dinner'] as $mealType){
                MenuList::create([
                    'name' => $menu[ucfirst($mealType)],
                    'meal_type' => $mealType,
                    'day_of_week' => $menu['recipe_day'],
                    'week_cycle' => 2,
                    'ingredients' => json_encode([]), // Empty ingredients array - will be filled by cook
                    'prep_time' => rand(15, 60), // Random prep time between 15-60 minutes
                    'cooking_time' => rand(20, 90), // Random cooking time between 20-90 minutes
                    'serving_size' => rand(50, 200), // Random serving size between 50-200 people
                ]);
            }
        }
    }
}
