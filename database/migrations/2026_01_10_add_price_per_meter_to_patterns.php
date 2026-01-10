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
            if (!Schema::hasColumn('yakan_patterns', 'price_per_meter')) {
                $table->decimal('price_per_meter', 10, 2)->default(0)->after('pattern_price')->comment('Price per meter for this pattern');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yakan_patterns', function (Blueprint $table) {
            if (Schema::hasColumn('yakan_patterns', 'price_per_meter')) {
                $table->dropColumn('price_per_meter');
            }
        });
    }
};
