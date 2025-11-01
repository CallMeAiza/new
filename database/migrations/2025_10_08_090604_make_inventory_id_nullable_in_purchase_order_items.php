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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['inventory_id']);
            
            // Make inventory_id nullable
            $table->unsignedBigInteger('inventory_id')->nullable()->change();
            
            // Re-add foreign key with nullable support
            $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['inventory_id']);
            
            // Make inventory_id not nullable again
            $table->unsignedBigInteger('inventory_id')->nullable(false)->change();
            
            // Re-add foreign key
            $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
        });
    }
};
