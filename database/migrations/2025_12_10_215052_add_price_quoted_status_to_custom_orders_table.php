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
        DB::statement("ALTER TABLE `custom_orders` MODIFY COLUMN `status` ENUM('pending','price_quoted','approved','rejected','processing','in_production','production_complete','out_for_delivery','delivered','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `custom_orders` MODIFY COLUMN `status` ENUM('pending','approved','rejected','processing','in_production','production_complete','out_for_delivery','delivered','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
