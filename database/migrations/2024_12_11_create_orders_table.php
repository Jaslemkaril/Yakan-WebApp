<?php

/**
 * Migration: Create Orders Table
 * 
 * This migration creates the orders table to store order data from mobile app.
 * 
 * To run this migration:
 * php artisan migrate
 * 
 * File location: database/migrations/2024_12_11_create_orders_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_ref')->unique()->index(); // e.g., "ORD-20241211-001"
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            
            // Order details
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Delivery info
            $table->enum('delivery_type', ['pickup', 'deliver'])->default('deliver');
            $table->text('shipping_address');
            $table->string('shipping_city')->nullable();
            $table->string('shipping_province')->nullable();
            
            // Payment info
            $table->enum('payment_method', ['gcash', 'bank_transfer', 'cash'])->default('gcash');
            $table->enum('payment_status', ['pending', 'paid', 'verified', 'failed'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->datetime('payment_verified_at')->nullable();
            
            // Order status
            $table->enum('status', [
                'pending_confirmation',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'refunded'
            ])->default('pending_confirmation')->index();
            
            // Additional info
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('source')->default('mobile'); // Track if from mobile or web
            
            // Timestamps
            $table->datetime('confirmed_at')->nullable();
            $table->datetime('shipped_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->datetime('cancelled_at')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('status');
            $table->index('payment_status');
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
