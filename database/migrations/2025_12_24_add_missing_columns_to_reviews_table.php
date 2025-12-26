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
        Schema::table('reviews', function (Blueprint $table) {
            // Add custom_order_id column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'custom_order_id')) {
                $table->unsignedBigInteger('custom_order_id')->nullable()->after('order_id');
                $table->foreign('custom_order_id')->references('id')->on('custom_orders')->onDelete('cascade');
                $table->index('custom_order_id');
            }
            
            // Add order_item_id column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'order_item_id')) {
                $table->unsignedBigInteger('order_item_id')->nullable()->after('custom_order_id');
                $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
                $table->index('order_item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'order_item_id')) {
                $table->dropForeign(['order_item_id']);
                $table->dropIndex(['order_item_id']);
                $table->dropColumn('order_item_id');
            }
            
            if (Schema::hasColumn('reviews', 'custom_order_id')) {
                $table->dropForeign(['custom_order_id']);
                $table->dropIndex(['custom_order_id']);
                $table->dropColumn('custom_order_id');
            }
        });
    }
};
