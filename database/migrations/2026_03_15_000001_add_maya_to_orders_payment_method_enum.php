<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment_method')) {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('gcash','maya','bank_transfer','cash') NOT NULL DEFAULT 'gcash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'payment_method')) {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('gcash','bank_transfer','cash') NOT NULL DEFAULT 'gcash'");
    }
};