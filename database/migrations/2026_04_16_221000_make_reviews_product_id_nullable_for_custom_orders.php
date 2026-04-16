<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reviews') || !Schema::hasColumn('reviews', 'product_id')) {
            return;
        }

        $foreignKeyName = $this->getReviewsProductForeignKeyName();

        if ($foreignKeyName) {
            Schema::table('reviews', function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        }

        DB::statement('ALTER TABLE `reviews` MODIFY `product_id` BIGINT UNSIGNED NULL');

        if (Schema::hasTable('products') && !$this->hasReviewsProductForeignKey()) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('reviews') || !Schema::hasColumn('reviews', 'product_id')) {
            return;
        }

        if (DB::table('reviews')->whereNull('product_id')->exists()) {
            // Keep rollback safe when nullable data already exists.
            return;
        }

        $foreignKeyName = $this->getReviewsProductForeignKeyName();

        if ($foreignKeyName) {
            Schema::table('reviews', function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        }

        DB::statement('ALTER TABLE `reviews` MODIFY `product_id` BIGINT UNSIGNED NOT NULL');

        if (Schema::hasTable('products') && !$this->hasReviewsProductForeignKey()) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    private function getReviewsProductForeignKeyName(): ?string
    {
        $row = DB::selectOne(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'reviews'
               AND COLUMN_NAME = 'product_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1"
        );

        return $row->CONSTRAINT_NAME ?? null;
    }

    private function hasReviewsProductForeignKey(): bool
    {
        return $this->getReviewsProductForeignKeyName() !== null;
    }
};
