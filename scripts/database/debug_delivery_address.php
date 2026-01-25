<?php
// Debug order 13 delivery address
$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

$query = "SELECT 
    id,
    delivery_address,
    delivery_city,
    delivery_province
FROM custom_orders
WHERE id = 13";

$result = $mysqli->query($query);
$row = $result->fetch_assoc();

echo "=== Order #13 Delivery Info ===\n";
echo "delivery_address: " . var_export($row['delivery_address'], true) . "\n";
echo "delivery_city: " . var_export($row['delivery_city'], true) . "\n";
echo "delivery_province: " . var_export($row['delivery_province'], true) . "\n";

echo "\n=== Checking if Zamboanga detected ===\n";
$address = strtolower($row['delivery_address'] ?? '');
$city = strtolower($row['delivery_city'] ?? '');
$province = strtolower($row['delivery_province'] ?? '');

echo "Address contains 'zamboanga': " . (str_contains($address, 'zamboanga') ? 'YES' : 'NO') . "\n";
echo "City contains 'zamboanga': " . (str_contains($city, 'zamboanga') ? 'YES' : 'NO') . "\n";
echo "Province contains 'zamboanga': " . (str_contains($province, 'zamboanga') ? 'YES' : 'NO') . "\n";

$mysqli->close();
