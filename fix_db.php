<?php
require 'config/database.php';
$pdo = db();

$table = 'attendance';
$columns = [
    'token_id' => "INT AFTER user_id",
    'location_id' => "INT AFTER token_id",
    'latitude' => "DECIMAL(10, 8) AFTER location_id",
    'longitude' => "DECIMAL(11, 8) AFTER latitude",
    'status' => "ENUM('valid', 'invalid', 'outside_radius', 'late', 'early') DEFAULT 'valid' AFTER approval_status",
    'attendance_flow' => "ENUM('in', 'out') DEFAULT 'in' AFTER status"
];

foreach ($columns as $col => $definition) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($check->rowCount() == 0) {
            echo "Adding column $col...\n";
            $pdo->exec("ALTER TABLE $table ADD COLUMN $col $definition");
        } else {
            echo "Column $col already exists.\n";
        }
    } catch (PDOException $e) {
        echo "Error on $col: " . $e->getMessage() . "\n";
    }
}

echo "Database Fix Complete.\n";
