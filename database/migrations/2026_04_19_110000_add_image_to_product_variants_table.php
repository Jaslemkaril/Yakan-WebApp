<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variants')) {
            return;
        }

        if (!Schema::hasColumn('product_variants', 'image')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->string('image')->nullable()->after('color');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_variants')) {
            return;
        }

        if (Schema::hasColumn('product_variants', 'image')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
