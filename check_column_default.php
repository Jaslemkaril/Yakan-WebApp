<?php

$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check column definition
$result = $mysqli->query("SHOW COLUMNS FROM yakan_patterns WHERE Field = 'base_price_multiplier'");
$row = $result->fetch_assoc();

echo "Column Information:\n";
echo "Field: " . $row['Field'] . "\n";
echo "Type: " . $row['Type'] . "\n";
echo "Null: " . $row['Null'] . "\n";
echo "Key: " . $row['Key'] . "\n";
echo "Default: " . $row['Default'] . "\n";
echo "Extra: " . $row['Extra'] . "\n";

if ($row['Default'] == 1) {
    echo "\n✓ Default value is correctly set!\n";
} else {
    echo "\n✗ Default value not set correctly\n";
}

$mysqli->close();
