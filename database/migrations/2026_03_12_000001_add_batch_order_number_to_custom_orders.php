<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('batch_order_number', 50)->nullable()->after('id')->index()
                  ->comment('Groups multiple custom order items under a single order number');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropIndex(['batch_order_number']);
            $table->dropColumn('batch_order_number');
        });
    }
};
