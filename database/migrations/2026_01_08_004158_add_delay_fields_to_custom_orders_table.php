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
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->boolean('is_delayed')->default(false)->after('status');
            $table->text('delay_reason')->nullable()->after('is_delayed');
            $table->timestamp('delay_notified_at')->nullable()->after('delay_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['is_delayed', 'delay_reason', 'delay_notified_at']);
        });
    }
};
