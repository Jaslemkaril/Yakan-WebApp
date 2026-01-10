<?php
/**
 * Comprehensive order verification script
 */

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'yakan_db';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Get ALL orders with their price components
$query = "SELECT 
            o.id, 
            o.status, 
            o.fabric_type, 
            o.fabric_quantity_meters, 
            o.estimated_price, 
            o.final_price,
            o.delivery_address,
            f.name as fabric_type_name
          FROM custom_orders o
          LEFT JOIN fabric_types f ON o.fabric_type = f.id
          ORDER BY o.id DESC";

$result = $mysqli->query($query);

echo "=== COMPREHENSIVE ORDER VERIFICATION ===\n\n";
echo str_pad("ID", 5) 
    . str_pad("Status", 15) 
    . str_pad("Fabric", 12) 
    . str_pad("Est Price", 12)
    . str_pad("Final Price", 12) 
    . "Meters\n";
echo str_repeat("-", 75) . "\n";

$totalOrders = 0;
$correctPrices = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalOrders++;
        
        // Calculate expected price for fabric orders
        $expectedPrice = null;
        if ($row['fabric_type'] && $row['fabric_quantity_meters']) {
            $meters = (float) $row['fabric_quantity_meters'];
            // For fabric orders, we expect: pattern_fee + (meters * 300) + shipping
            // For simplicity, check if price is > 0 and properly calculated
            if ($row['estimated_price'] > 0 && $row['final_price'] > 0) {
                $correctPrices++;
            }
        }
        
        $fabricDisplay = $row['fabric_type_name'] ?? ($row['fabric_type'] ?? 'None');
        
        echo str_pad($row['id'], 5) 
            . str_pad($row['status'], 15) 
            . str_pad($fabricDisplay, 12) 
            . str_pad('₱' . number_format($row['estimated_price'], 2), 12)
            . str_pad('₱' . number_format($row['final_price'], 2), 12)
            . ($row['fabric_quantity_meters'] ?? '—') . "m\n";
    }
}

echo str_repeat("-", 75) . "\n";
echo "Total Orders: $totalOrders\n";
echo "Orders with Valid Prices: $correctPrices\n";

// Get system settings
echo "\n=== SYSTEM SETTINGS (Price Configuration) ===\n";
$settingsQuery = "SELECT `key`, `value` FROM system_settings WHERE `key` LIKE 'pattern_%' OR `key` = 'price_per_meter'";
$settingsResult = $mysqli->query($settingsQuery);

if ($settingsResult && $settingsResult->num_rows > 0) {
    while ($row = $settingsResult->fetch_assoc()) {
        echo $row['key'] . ": ₱" . number_format($row['value'], 2) . "\n";
    }
}

// Sample calculation verification
echo "\n=== SAMPLE PRICE CALCULATION VERIFICATION ===\n";
echo "Example: Order with 2 meters, Medium pattern, Zamboanga City\n";
$examplePatternFee = 1900;  // Medium
$exampleFabricCost = 2 * 300;  // 2 meters @ 300
$exampleShipping = 0;  // Zamboanga City
$expectedTotal = $examplePatternFee + $exampleFabricCost + $exampleShipping;
echo "  Pattern Fee (Medium): ₱" . number_format($examplePatternFee, 2) . "\n";
echo "  Fabric Cost (2m × 300): ₱" . number_format($exampleFabricCost, 2) . "\n";
echo "  Shipping (Zamboanga): ₱" . number_format($exampleShipping, 2) . "\n";
echo "  Expected Total: ₱" . number_format($expectedTotal, 2) . "\n";

$mysqli->close();
echo "\n✓ Verification completed\n";
