<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample orders with tracking numbers for track order page
        $orders = [
            [
                'order_ref' => 'ORD-2025-001',
                'tracking_number' => 'TRK-2025-001-001',
                'customer_name' => 'Juan Dela Cruz',
                'customer_email' => 'juan@example.com',
                'customer_phone' => '09123456789',
                'subtotal' => 2500.00,
                'shipping_fee' => 100.00,
                'discount' => 0.00,
                'total' => 2600.00,
                'total_amount' => 2600.00,
                'delivery_type' => 'deliver',
                'shipping_address' => '123 Main St, Barangay Sample',
                'shipping_city' => 'General Santos City',
                'shipping_province' => 'South Cotabato',
                'payment_method' => 'gcash',
                'payment_status' => 'paid',
                'status' => 'shipped',
                'source' => 'mobile',
                'confirmed_at' => now()->subDays(5),
                'shipped_at' => now()->subDays(2),
            ],
            [
                'order_ref' => 'ORD-2025-002',
                'tracking_number' => 'TRK-2025-002-001',
                'customer_name' => 'Maria Santos',
                'customer_email' => 'maria@example.com',
                'customer_phone' => '09198765432',
                'subtotal' => 3500.00,
                'shipping_fee' => 150.00,
                'discount' => 200.00,
                'total' => 3450.00,
                'total_amount' => 3450.00,
                'delivery_type' => 'deliver',
                'shipping_address' => '456 Oak Ave, Barangay Test',
                'shipping_city' => 'General Santos City',
                'shipping_province' => 'South Cotabato',
                'payment_method' => 'bank_transfer',
                'payment_status' => 'paid',
                'status' => 'processing',
                'source' => 'mobile',
                'confirmed_at' => now()->subDays(1),
            ],
            [
                'order_ref' => 'ORD-2025-003',
                'tracking_number' => 'TRK-2025-003-001',
                'customer_name' => 'Jose Martinez',
                'customer_email' => 'jose@example.com',
                'customer_phone' => '09156231456',
                'subtotal' => 1800.00,
                'shipping_fee' => 80.00,
                'discount' => 0.00,
                'total' => 1880.00,
                'total_amount' => 1880.00,
                'delivery_type' => 'pickup',
                'shipping_address' => 'Main Office, General Santos City',
                'shipping_city' => 'General Santos City',
                'shipping_province' => 'South Cotabato',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'status' => 'delivered',
                'source' => 'mobile',
                'confirmed_at' => now()->subDays(10),
                'shipped_at' => now()->subDays(8),
                'delivered_at' => now()->subDays(5),
            ],
        ];

        foreach ($orders as $order) {
            // Don't create if tracking number already exists
            if (!Order::where('tracking_number', $order['tracking_number'])->exists()) {
                Order::create($order);
            }
        }
    }
}
