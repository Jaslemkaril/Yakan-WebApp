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
            if (!Schema::hasColumn('yakan_patterns', 'pattern_price')) {
                $table->decimal('pattern_price', 10, 2)->default(0)->after('base_price_multiplier')->comment('Individual price for this pattern');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yakan_patterns', function (Blueprint $table) {
            if (Schema::hasColumn('yakan_patterns', 'pattern_price')) {
                $table->dropColumn('pattern_price');
            }
        });
    }
};
