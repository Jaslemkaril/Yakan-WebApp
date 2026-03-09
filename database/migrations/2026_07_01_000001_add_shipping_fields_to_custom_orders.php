<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('delivery_city')->nullable()->after('delivery_address');
            $table->string('delivery_province')->nullable()->after('delivery_city');
            $table->decimal('shipping_fee', 8, 2)->nullable()->default(0)->after('delivery_province');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_city', 'delivery_province', 'shipping_fee']);
        });
    }
};
