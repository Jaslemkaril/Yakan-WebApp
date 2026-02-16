<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philippine_provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('philippine_regions')->onDelete('cascade');
            $table->string('province_code', 20)->unique();
            $table->string('name');
            $table->timestamps();
            
            $table->index('region_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philippine_provinces');
    }
};
