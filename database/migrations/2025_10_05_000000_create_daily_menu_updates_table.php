<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('daily_menu_updates')) {
            Schema::create('daily_menu_updates', function (Blueprint $table) {
                $table->id();
                $table->date('menu_date');
                $table->string('meal_type'); // breakfast, lunch, dinner
                $table->string('meal_name');
                $table->text('ingredients')->nullable();
                $table->string('status')->default('planned'); // planned, preparing, ready, served
                $table->integer('estimated_portions')->default(0);
                $table->integer('actual_portions')->default(0);
                $table->string('updated_by')->nullable();
                $table->timestamps();

                // Add foreign key for updated_by
                $table->foreign('updated_by')->references('user_id')->on('pnph_users')->onDelete('set null');
                
                // Add unique constraint to prevent duplicate entries for same date and meal type
                $table->unique(['menu_date', 'meal_type']);
                
                // Add indexes for better query performance
                $table->index('menu_date');
                $table->index('meal_type');
                $table->index(['menu_date', 'meal_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_menu_updates');
    }
};
