<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philippine_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('philippine_provinces')->onDelete('cascade');
            $table->string('city_code', 20)->unique();
            $table->string('name');
            $table->timestamps();
            
            $table->index('province_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philippine_cities');
    }
};
