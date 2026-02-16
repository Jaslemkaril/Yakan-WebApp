<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philippine_barangays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('philippine_cities')->onDelete('cascade');
            $table->string('barangay_code', 20)->unique();
            $table->string('name');
            $table->timestamps();
            
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philippine_barangays');
    }
};
