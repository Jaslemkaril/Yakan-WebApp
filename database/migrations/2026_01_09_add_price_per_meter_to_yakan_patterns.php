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
            $table->decimal('price_per_meter', 10, 2)->nullable()->after('base_price_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yakan_patterns', function (Blueprint $table) {
            $table->dropColumn('price_per_meter');
        });
    }
};
