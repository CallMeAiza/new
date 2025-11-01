<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('post_assessments', function (Blueprint $table) {
            // Add new column for multiple images
            $table->json('image_paths')->nullable()->after('image_path');
        });

        // Migrate existing single image_path to image_paths array
        DB::table('post_assessments')
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->update([
                'image_paths' => DB::raw("JSON_ARRAY(image_path)")
            ]);

        // Keep the old column for backward compatibility (can drop later if needed)
        // Schema::table('post_assessments', function (Blueprint $table) {
        //     $table->dropColumn('image_path');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_assessments', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
