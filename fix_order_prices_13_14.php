<?php
// Fix order prices to include fabric cost
$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

// Order #13 and #14: Pattern 17 (simple), 2 meters, Zamboanga
// Correct: 1200 (simple) + 600 (fabric) + 0 (Zamboanga) = 1800

$updates = [
    13 => 1800,
    14 => 1800,
];

foreach ($updates as $orderId => $correctPrice) {
    $query = "UPDATE custom_orders SET estimated_price = $correctPrice, final_price = $correctPrice WHERE id = $orderId";
    $result = $mysqli->query($query);
    
    if ($result) {
        echo "✓ Order #$orderId updated to ₱" . number_format($correctPrice, 2) . "\n";
    } else {
        echo "✗ Error updating Order #$orderId: " . $mysqli->error . "\n";
    }
}

// Verify
echo "\n=== Verification ===\n";
$result = $mysqli->query("SELECT id, estimated_price, final_price FROM custom_orders WHERE id IN (13, 14)");
while ($row = $result->fetch_assoc()) {
    echo "Order #" . $row['id'] . ": ₱" . number_format($row['estimated_price'], 2) . "\n";
}

$mysqli->close();
echo "\n✓ Done. New orders should now calculate prices correctly with all components.\n";
