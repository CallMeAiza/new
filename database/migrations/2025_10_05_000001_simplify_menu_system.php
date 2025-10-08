<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration simplifies the menu system to use actual dates instead of week cycles
     */
    public function up(): void
    {
        // Add date column to meals table for direct date-based planning
        if (Schema::hasTable('meals') && !Schema::hasColumn('meals', 'menu_date')) {
            Schema::table('meals', function (Blueprint $table) {
                $table->date('menu_date')->nullable()->after('week_cycle');
                $table->index('menu_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('meals') && Schema::hasColumn('meals', 'menu_date')) {
            Schema::table('meals', function (Blueprint $table) {
                $table->dropColumn('menu_date');
            });
        }
    }
};
