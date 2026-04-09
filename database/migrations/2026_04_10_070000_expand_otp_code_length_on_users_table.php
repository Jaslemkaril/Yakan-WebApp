<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'otp_code')) {
            return;
        }

        DB::statement('ALTER TABLE users MODIFY otp_code VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'otp_code')) {
            return;
        }

        DB::statement('ALTER TABLE users MODIFY otp_code VARCHAR(6) NULL');
    }
};
