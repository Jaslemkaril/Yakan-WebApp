<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ENUM, so we'll just ensure the column exists as string
        // The status values will be validated at the application level
        if (Schema::hasColumn('custom_orders', 'status')) {
            // Column already exists, no need to modify for SQLite
            return;
        }
        
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse for SQLite
    }
};
