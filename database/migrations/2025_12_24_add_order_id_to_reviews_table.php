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
            // Add order_id column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('product_id');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->index('order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropIndex(['order_id']);
                $table->dropColumn('order_id');
            }
        });
    }
};
