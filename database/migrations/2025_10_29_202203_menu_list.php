<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create menu_list table to store seeded recipes
        if (!Schema::hasTable('menu_list')) {
            Schema::create('menu_list', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('ingredients')->nullable();
                $table->integer('prep_time')->nullable();
                $table->integer('cooking_time')->nullable();
                $table->integer('serving_size')->nullable();
                $table->string('meal_type');
                $table->string('day_of_week');
                $table->integer('week_cycle')->default(1);
                $table->timestamps();
            });
        }

        // Create menu_list_ingredients pivot table linking menu_list to ingredients (after menu_list)
        if (!Schema::hasTable('menu_list_ingredients')) {
            Schema::create('menu_list_ingredients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('menu_list_id');
                $table->unsignedBigInteger('ingredient_id');
                $table->decimal('quantity_required', 10, 2)->nullable();
                $table->string('unit')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('menu_list_id')->references('id')->on('menu_list')->onDelete('cascade');
                $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
                $table->unique(['menu_list_id', 'ingredient_id']);
            });
        }

        // Create menu_ingredients pivot table for menu planning
        if (!Schema::hasTable('menu_ingredients')) {
            Schema::create('menu_ingredients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('menu_id');
                $table->unsignedBigInteger('ingredient_id');
                $table->decimal('quantity_required', 10, 2);
                $table->string('unit');
                $table->text('notes')->nullable();
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
                $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
                
                // Unique constraint to prevent duplicate menu-ingredient combinations
                $table->unique(['menu_id', 'ingredient_id']);
            });
        }

        // Create meal_ingredients table for existing meals
        if (!Schema::hasTable('meal_ingredients')) {
            Schema::create('meal_ingredients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('meal_id');
                $table->unsignedBigInteger('ingredient_id');
                $table->decimal('quantity_required', 10, 2);
                $table->string('unit');
                $table->text('notes')->nullable();
                $table->timestamps();

                // Foreign key constraints
                $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade');
                $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
                
                // Unique constraint to prevent duplicate meal-ingredient combinations
                $table->unique(['meal_id', 'ingredient_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('menu_list_ingredients');
        Schema::dropIfExists('meal_ingredients');
        Schema::dropIfExists('menu_ingredients');
    }
};
