<?php

$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if pattern_price column exists
$result = $mysqli->query("SHOW COLUMNS FROM yakan_patterns WHERE Field = 'pattern_price'");
$row = $result->fetch_assoc();

if ($row) {
    echo "✓ pattern_price column exists!\n";
    echo "  Type: " . $row['Type'] . "\n";
    echo "  Default: " . $row['Default'] . "\n";
} else {
    echo "✗ pattern_price column not found\n";
}

// Also check base_price_multiplier
$result2 = $mysqli->query("SHOW COLUMNS FROM yakan_patterns WHERE Field = 'base_price_multiplier'");
$row2 = $result2->fetch_assoc();

echo "\n✓ base_price_multiplier:\n";
echo "  Type: " . $row2['Type'] . "\n";
echo "  Default: " . $row2['Default'] . "\n";

$mysqli->close();
