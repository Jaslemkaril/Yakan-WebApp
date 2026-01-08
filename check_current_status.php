<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomOrder;

$order = CustomOrder::find(2);
echo "Current status: " . $order->status . "\n";
