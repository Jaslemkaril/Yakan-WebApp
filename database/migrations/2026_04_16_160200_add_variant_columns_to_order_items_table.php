<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }

            if (!Schema::hasColumn('order_items', 'variant_size')) {
                $table->string('variant_size')->nullable()->after('variant_id');
            }

            if (!Schema::hasColumn('order_items', 'variant_color')) {
                $table->string('variant_color')->nullable()->after('variant_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variant_color')) {
                $table->dropColumn('variant_color');
            }

            if (Schema::hasColumn('order_items', 'variant_size')) {
                $table->dropColumn('variant_size');
            }

            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }
        });
    }
};
