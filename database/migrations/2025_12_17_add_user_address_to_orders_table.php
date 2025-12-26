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
            // Add user_address_id if it doesn't exist
            if (!Schema::hasColumn('orders', 'user_address_id')) {
                $table->unsignedBigInteger('user_address_id')->nullable()->after('user_id');
                $table->foreign('user_address_id')->references('id')->on('user_addresses')->onDelete('set null');
            }

            // Add tracking columns if they don't exist
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->unique()->nullable()->after('order_ref');
            }

            if (!Schema::hasColumn('orders', 'tracking_status')) {
                $table->string('tracking_status')->nullable()->after('status');
            }

            if (!Schema::hasColumn('orders', 'tracking_history')) {
                $table->json('tracking_history')->nullable()->after('tracking_status');
            }

            // Add discount_amount if it doesn't exist
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('discount');
            }

            // Add coupon columns if they don't exist
            if (!Schema::hasColumn('orders', 'coupon_id')) {
                $table->unsignedBigInteger('coupon_id')->nullable()->after('discount_amount');
            }

            if (!Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('coupon_id');
            }

            // Add delivery_address if it doesn't exist
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('shipping_address');
            }

            // Add customer_notes if it doesn't exist
            if (!Schema::hasColumn('orders', 'customer_notes')) {
                $table->text('customer_notes')->nullable()->after('notes');
            }

            // Add bank_receipt if it doesn't exist
            if (!Schema::hasColumn('orders', 'bank_receipt')) {
                $table->string('bank_receipt')->nullable()->after('payment_reference');
            }

            // Add total_amount if it doesn't exist
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key if it exists
            if (Schema::hasColumn('orders', 'user_address_id')) {
                $table->dropForeign(['user_address_id']);
                $table->dropColumn('user_address_id');
            }

            // Drop other columns
            $columns = [
                'tracking_number',
                'tracking_status',
                'tracking_history',
                'discount_amount',
                'coupon_id',
                'coupon_code',
                'delivery_address',
                'customer_notes',
                'bank_receipt',
                'total_amount',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
