<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add courier tracking columns if they don't exist
            if (!Schema::hasColumn('orders', 'courier_name')) {
                $table->string('courier_name')->nullable()->after('tracking_status');
            }
            if (!Schema::hasColumn('orders', 'courier_contact')) {
                $table->string('courier_contact')->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('orders', 'courier_tracking_url')) {
                $table->string('courier_tracking_url')->nullable()->after('courier_contact');
            }
            if (!Schema::hasColumn('orders', 'estimated_delivery_date')) {
                $table->date('estimated_delivery_date')->nullable()->after('courier_tracking_url');
            }
            if (!Schema::hasColumn('orders', 'tracking_notes')) {
                $table->text('tracking_notes')->nullable()->after('estimated_delivery_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'courier_name',
                'courier_contact',
                'courier_tracking_url',
                'estimated_delivery_date',
                'tracking_notes',
            ]);
        });
    }
};
