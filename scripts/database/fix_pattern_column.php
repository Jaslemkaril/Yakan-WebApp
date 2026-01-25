<?php

$mysqli = new mysqli('localhost', 'root', '', 'yakan_db');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$sql = 'ALTER TABLE yakan_patterns MODIFY base_price_multiplier DECIMAL(10,2) NOT NULL DEFAULT 1.00';

if ($mysqli->query($sql)) {
    echo "✓ Column modified successfully with default value\n";
} else {
    echo "✗ Error: " . $mysqli->error . "\n";
}

$mysqli->close();
