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
        Schema::table('yakan_patterns', function (Blueprint $table) {
            $table->unsignedInteger('production_days')->default(14)->after('difficulty_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yakan_patterns', function (Blueprint $table) {
            $table->dropColumn('production_days');
        });
    }
};
