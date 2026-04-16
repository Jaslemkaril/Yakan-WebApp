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
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_option')) {
                $table->enum('payment_option', ['full', 'downpayment'])
                    ->default('full')
                    ->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'downpayment_rate')) {
                $table->decimal('downpayment_rate', 5, 2)
                    ->nullable()
                    ->after('payment_option');
            }

            if (!Schema::hasColumn('orders', 'downpayment_amount')) {
                $table->decimal('downpayment_amount', 10, 2)
                    ->default(0)
                    ->after('downpayment_rate');
            }

            if (!Schema::hasColumn('orders', 'remaining_balance')) {
                $table->decimal('remaining_balance', 10, 2)
                    ->default(0)
                    ->after('downpayment_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'remaining_balance')) {
                $table->dropColumn('remaining_balance');
            }

            if (Schema::hasColumn('orders', 'downpayment_amount')) {
                $table->dropColumn('downpayment_amount');
            }

            if (Schema::hasColumn('orders', 'downpayment_rate')) {
                $table->dropColumn('downpayment_rate');
            }

            if (Schema::hasColumn('orders', 'payment_option')) {
                $table->dropColumn('payment_option');
            }
        });
    }
};
