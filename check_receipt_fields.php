<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::find(9);

echo "Order #9 Receipt Fields:\n";
echo "- bank_receipt: " . ($order->bank_receipt ?? 'NULL') . "\n";
echo "- gcash_receipt: " . ($order->gcash_receipt ?? 'NULL') . "\n";
echo "- payment_proof_path: " . ($order->payment_proof_path ?? 'NULL') . "\n";
echo "- payment_method: " . $order->payment_method . "\n";
echo "- payment_status: " . $order->payment_status . "\n";
