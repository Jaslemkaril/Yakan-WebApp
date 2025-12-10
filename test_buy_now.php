<?php
/**
 * Test Buy Now Functionality
 * 
 * This file helps debug the Buy Now button issue
 * 
 * Usage:
 * 1. Visit: http://localhost:8000/test_buy_now.php
 * 2. This will show you what's happening with the Buy Now flow
 */

// Load Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

use Illuminate\Support\Facades\Auth;
use App\Models\Product;

$isAuthenticated = Auth::check();
$userId = Auth::id();
$testProduct = Product::find(12); // Yakan Cap

echo "<!DOCTYPE html>
<html>
<head>
    <title>Buy Now Debug Test</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
        .form-test { margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Buy Now Functionality Debug</h1>
        
        <div class='status " . ($isAuthenticated ? 'ok' : 'error') . "'>
            <strong>Authentication Status:</strong> " . ($isAuthenticated ? "✓ LOGGED IN (User ID: $userId)" : "✗ NOT LOGGED IN") . "
        </div>
        
        <div class='status " . ($testProduct ? 'ok' : 'error') . "'>
            <strong>Test Product (ID: 12):</strong> " . ($testProduct ? 
                $testProduct->name . " (Stock: " . $testProduct->stock . ")" 
                : "NOT FOUND") . "
        </div>";

if ($isAuthenticated && $testProduct) {
    $cartRoute = route('cart.add', $testProduct);
    echo "<div class='form-test'>
        <h3>Test Buy Now Form</h3>
        <p>This form mimics the actual Buy Now button on the product page:</p>
        <form method='POST' action='$cartRoute'>
            " . csrf_field() . "
            <input type='hidden' name='quantity' value='1'>
            <input type='hidden' name='buy_now' value='1'>
            <button type='submit' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;'>
                Test Buy Now
            </button>
        </form>
        <p style='margin-top: 10px; font-size: 12px; color: #666;'>
            <strong>Form Action:</strong> <code>$cartRoute</code><br>
            <strong>Method:</strong> <code>POST</code><br>
            <strong>CSRF:</strong> ✓ Included
        </p>
    </div>";
} elseif (!$isAuthenticated) {
    echo "<div class='status error'>
        <strong>⚠️ Cannot test:</strong> You must be logged in to test Buy Now functionality.
        <a href='" . route('login') . "'>Click here to login</a>
    </div>";
} else {
    echo "<div class='status error'>
        <strong>⚠️ Test Product Not Found:</strong> Product with ID 12 does not exist.
    </div>";
}

echo "</div>
</body>
</html>";
?>
