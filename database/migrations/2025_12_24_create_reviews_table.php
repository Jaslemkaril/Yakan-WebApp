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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('custom_order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            
            // Review content
            $table->integer('rating')->unsigned()->min(1)->max(5);
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            
            // Review metadata
            $table->boolean('verified_purchase')->default(true);
            $table->integer('helpful_count')->default(0);
            $table->integer('unhelpful_count')->default(0);
            
            // Admin moderation
            $table->boolean('is_approved')->default(true);
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('custom_order_id')->references('id')->on('custom_orders')->onDelete('cascade');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('user_id');
            $table->index('product_id');
            $table->index('order_id');
            $table->index('custom_order_id');
            $table->index('is_approved');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
