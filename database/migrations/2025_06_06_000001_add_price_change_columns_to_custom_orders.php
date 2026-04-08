<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_orders', 'previous_price')) {
                $table->decimal('previous_price', 10, 2)->nullable()->after('final_price');
            }
            if (!Schema::hasColumn('custom_orders', 'price_change_reason')) {
                $table->text('price_change_reason')->nullable()->after('previous_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['previous_price', 'price_change_reason']);
        });
    }
};
