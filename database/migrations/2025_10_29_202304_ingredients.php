<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
            if (!Schema::hasTable('ingredients')) {
            Schema::create('ingredients', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->decimal('price', 8, 2);
                $table->string('category');
                $table->string('unit');
                $table->integer('quantity');
                $table->text('description')->nullable();
                $table->decimal('current_stock', 10, 2)->default(0);
                $table->decimal('minimum_stock', 10, 2)->default(10);
                $table->decimal('cost_per_unit', 8, 2)->default(0);
                $table->string('supplier_id')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();

                // Add foreign key constraints
                $table->foreign('supplier_id')->references('user_id')->on('pnph_users')->onDelete('set null');
                $table->foreign('created_by')->references('user_id')->on('pnph_users')->onDelete('set null');
                $table->foreign('updated_by')->references('user_id')->on('pnph_users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ingredients');
    }
};