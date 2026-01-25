<?php

$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check if production_days column exists
$result = $mysqli->query("SHOW COLUMNS FROM yakan_patterns WHERE Field = 'production_days'");
$row = $result->fetch_assoc();

if ($row) {
    echo "✓ production_days column exists!\n";
    echo "  Type: " . $row['Type'] . "\n";
    echo "  Default: " . $row['Default'] . "\n";
} else {
    echo "✗ production_days column not found\n";
}

$mysqli->close();
