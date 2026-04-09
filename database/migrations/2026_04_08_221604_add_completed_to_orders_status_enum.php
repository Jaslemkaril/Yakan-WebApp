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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending_confirmation',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'refunded'
        ) NOT NULL DEFAULT 'pending_confirmation'");
    }
};
