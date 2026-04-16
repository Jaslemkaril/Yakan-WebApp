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
        if (!Schema::hasTable('order_refund_requests')) {
            return;
        }

        Schema::table('order_refund_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('order_refund_requests', 'refund_type')) {
                $table->string('refund_type', 40)->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('order_refund_requests', 'comment')) {
                $table->text('comment')->nullable()->after('reason');
            }

            if (!Schema::hasColumn('order_refund_requests', 'workflow_status')) {
                $table->string('workflow_status', 60)->nullable()->after('status');
            }

            if (!Schema::hasColumn('order_refund_requests', 'system_validation')) {
                $table->json('system_validation')->nullable()->after('workflow_status');
            }

            if (!Schema::hasColumn('order_refund_requests', 'fraud_flags')) {
                $table->json('fraud_flags')->nullable()->after('system_validation');
            }

            if (!Schema::hasColumn('order_refund_requests', 'fraud_risk_level')) {
                $table->string('fraud_risk_level', 20)->nullable()->after('fraud_flags');
            }

            if (!Schema::hasColumn('order_refund_requests', 'recommended_decision')) {
                $table->string('recommended_decision', 40)->nullable()->after('fraud_risk_level');
            }

            if (!Schema::hasColumn('order_refund_requests', 'recommended_refund_amount')) {
                $table->decimal('recommended_refund_amount', 12, 2)->nullable()->after('recommended_decision');
            }

            if (!Schema::hasColumn('order_refund_requests', 'return_required')) {
                $table->boolean('return_required')->default(false)->after('recommended_refund_amount');
            }

            if (!Schema::hasColumn('order_refund_requests', 'final_decision')) {
                $table->string('final_decision', 40)->nullable()->after('return_required');
            }

            if (!Schema::hasColumn('order_refund_requests', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->nullable()->after('final_decision');
            }

            if (!Schema::hasColumn('order_refund_requests', 'refund_channel')) {
                $table->string('refund_channel', 40)->nullable()->after('refund_amount');
            }

            if (!Schema::hasColumn('order_refund_requests', 'refund_reference')) {
                $table->string('refund_reference', 120)->nullable()->after('refund_channel');
            }

            if (!Schema::hasColumn('order_refund_requests', 'payout_status')) {
                $table->string('payout_status', 40)->nullable()->after('refund_reference');
            }

            if (!Schema::hasColumn('order_refund_requests', 'return_tracking_number')) {
                $table->string('return_tracking_number', 120)->nullable()->after('payout_status');
            }

            if (!Schema::hasColumn('order_refund_requests', 'return_shipped_at')) {
                $table->timestamp('return_shipped_at')->nullable()->after('return_tracking_number');
            }

            if (!Schema::hasColumn('order_refund_requests', 'return_received_at')) {
                $table->timestamp('return_received_at')->nullable()->after('return_shipped_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally keeps workflow columns to avoid data loss in production rollbacks.
    }
};
