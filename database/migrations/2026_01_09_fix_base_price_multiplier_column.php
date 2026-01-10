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
        Schema::table('yakan_patterns', function (Blueprint $table) {
            // Change the column type to support larger prices
            $table->decimal('base_price_multiplier', 10, 2)->default(1.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yakan_patterns', function (Blueprint $table) {
            // Revert back to original size
            $table->decimal('base_price_multiplier', 3, 2)->change();
        });
    }
};
