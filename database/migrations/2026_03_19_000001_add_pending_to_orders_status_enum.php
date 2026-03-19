<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        // Add 'pending' to the status ENUM so older code using 'pending' doesn't cause truncation errors
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','pending_confirmation','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasColumn('orders', 'status')) {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending_confirmation','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation'");
    }
};
