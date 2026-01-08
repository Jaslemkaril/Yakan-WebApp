<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomOrder;

$order = CustomOrder::find(2);
if ($order) {
    echo "Order #2 Status:\n";
    echo "- status: " . $order->status . "\n";
    echo "- payment_status: " . $order->payment_status . "\n";
    echo "- final_price: " . $order->final_price . "\n";
    echo "- payment_receipt: " . ($order->payment_receipt ? 'exists' : 'null') . "\n";
} else {
    echo "Order not found\n";
}
