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
        Schema::table('users', function (Blueprint $table) {
            // Add social authentication fields if they don't exist
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('users', 'provider_token')) {
                $table->text('provider_token')->nullable()->after('provider_id');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropIndex(['provider', 'provider_id']);
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
            
            // Safely drop columns if they exist
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }
            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropColumn('provider_id');
            }
            if (Schema::hasColumn('users', 'provider_token')) {
                $table->dropColumn('provider_token');
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
        });
    }
};
