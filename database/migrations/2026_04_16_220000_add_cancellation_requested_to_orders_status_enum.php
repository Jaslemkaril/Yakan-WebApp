<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'pending_confirmation',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'completed',
            'cancellation_requested',
            'cancelled',
            'refunded'
        ) NOT NULL DEFAULT 'pending_confirmation'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'pending_confirmation',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'completed',
            'cancelled',
            'refunded'
        ) NOT NULL DEFAULT 'pending_confirmation'");
    }
};
