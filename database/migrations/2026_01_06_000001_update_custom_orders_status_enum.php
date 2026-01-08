<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add all workflow statuses to the enum used by MySQL.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE custom_orders MODIFY status ENUM('pending','price_quoted','approved','rejected','processing','in_production','production_complete','out_for_delivery','delivered','completed','cancelled') DEFAULT 'pending'");
    }

    /**
     * Revert to the previous enum set.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE custom_orders MODIFY status ENUM('pending','price_quoted','approved','rejected','processing','in_production','completed','cancelled') DEFAULT 'pending'");
    }
};
