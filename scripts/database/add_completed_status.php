<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding 'completed' to status enum...\n";

DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_confirmation','confirmed','processing','shipped','delivered','completed','cancelled','refunded') NOT NULL DEFAULT 'pending_confirmation'");

echo "✓ Status column updated successfully!\n";

// Verify
$result = DB::select('SHOW COLUMNS FROM orders WHERE Field = "status"');
echo "\nNew status column definition:\n";
print_r($result);
