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
        Schema::table('reviews', function (Blueprint $table) {
            // Add verified_purchase column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'verified_purchase')) {
                $table->boolean('verified_purchase')->default(true)->after('comment');
            }
            
            // Add helpful_count column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'helpful_count')) {
                $table->integer('helpful_count')->default(0)->after('verified_purchase');
            }
            
            // Add unhelpful_count column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'unhelpful_count')) {
                $table->integer('unhelpful_count')->default(0)->after('helpful_count');
            }
            
            // Add is_approved column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('unhelpful_count');
            }
            
            // Add rejection_reason column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('is_approved');
            }
            
            // Add approved_by column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('rejection_reason');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            }
            
            // Add approved_at column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $columns = [
                'verified_purchase',
                'helpful_count',
                'unhelpful_count',
                'is_approved',
                'rejection_reason',
                'approved_by',
                'approved_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    if ($column === 'approved_by') {
                        $table->dropForeign(['approved_by']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
