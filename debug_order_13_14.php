<?php
// Check order 13 and 14 details
$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

$query = "SELECT 
    id, 
    status,
    fabric_type,
    fabric_quantity_meters,
    estimated_price,
    patterns,
    design_metadata
FROM custom_orders
WHERE id IN (13, 14)";

$result = $mysqli->query($query);

echo "=== Order #13 and #14 Details ===\n\n";

while ($row = $result->fetch_assoc()) {
    echo "Order #" . $row['id'] . ":\n";
    echo "  Fabric Quantity Meters: " . var_export($row['fabric_quantity_meters'], true) . "\n";
    echo "  Type: " . gettype($row['fabric_quantity_meters']) . "\n";
    echo "  Estimated Price: ₱" . number_format($row['estimated_price'], 2) . "\n";
    echo "  Patterns: " . ($row['patterns'] ?? 'null') . "\n";
    if ($row['design_metadata']) {
        $metadata = json_decode($row['design_metadata'], true);
        echo "  Pattern ID from metadata: " . ($metadata['pattern_id'] ?? 'N/A') . "\n";
        echo "  Pattern Name from metadata: " . ($metadata['pattern_name'] ?? 'N/A') . "\n";
        echo "  Pattern Difficulty (if in pattern table): ";
        if (isset($metadata['pattern_id'])) {
            $diffQuery = "SELECT difficulty_level FROM yakan_patterns WHERE id = " . $metadata['pattern_id'];
            $diffResult = $mysqli->query($diffQuery);
            if ($diffResult && $row = $diffResult->fetch_assoc()) {
                echo $row['difficulty_level'] . "\n";
            }
        }
    }
    echo "\n";
}

// Calculate what the price SHOULD be
echo "=== Expected Price Calculation ===\n";
echo "For 2.00 meters, 1 Medium pattern (₱1,900), Zamboanga shipping (FREE):\n";
echo "  Pattern Fee: ₱1,900\n";
echo "  Fabric Cost: 2 × ₱300 = ₱600\n";
echo "  Shipping: ₱0 (Zamboanga)\n";
echo "  Expected Total: ₱2,500\n";
echo "\nActual showing: ₱1,900 (only pattern fee!)\n";

$mysqli->close();
