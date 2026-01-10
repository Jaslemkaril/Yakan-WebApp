<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize difficulty levels to only: simple, medium, complex
        DB::table('yakan_patterns')->where('difficulty_level', 'advanced')->update(['difficulty_level' => 'complex']);
        DB::table('yakan_patterns')->where('difficulty_level', 'expert')->update(['difficulty_level' => 'complex']);
        DB::table('yakan_patterns')->where('difficulty_level', 'master')->update(['difficulty_level' => 'complex']);
        DB::table('yakan_patterns')->where('difficulty_level', 'intermediate')->update(['difficulty_level' => 'medium']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed as we've lost the original values
        // But we can revert to a common state
    }
};
