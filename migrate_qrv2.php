<?php
require_once 'config/database.php';
$pdo = db();

echo "Migrating for QR V2 System...\n";

$queries = [
    // 1. Table for Office Locations
    "CREATE TABLE IF NOT EXISTS locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        radius_meters INT DEFAULT 50,
        check_in_start TIME DEFAULT '07:00:00',
        check_in_end TIME DEFAULT '10:00:00',
        check_out_start TIME DEFAULT '16:00:00',
        check_out_end TIME DEFAULT '20:00:00',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // 2. Table for Steerable QR Tokens
    "CREATE TABLE IF NOT EXISTS qr_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        location_id INT,
        token VARCHAR(100) UNIQUE NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
    )",

    // 3. Enhance Attendance Table
    "ALTER TABLE attendance 
        ADD COLUMN IF NOT EXISTS token_id INT AFTER user_id,
        ADD COLUMN IF NOT EXISTS location_id INT AFTER token_id,
        ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) AFTER location_id,
        ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) AFTER latitude,
        ADD COLUMN IF NOT EXISTS status ENUM('valid', 'invalid', 'outside_radius', 'late', 'early') DEFAULT 'valid' AFTER approval_status,
        ADD COLUMN IF NOT EXISTS attendance_flow ENUM('in', 'out') DEFAULT 'in' AFTER status"
];

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
    } catch (Exception $e) {
        echo "Error on query: $q\n" . $e->getMessage() . "\n";
    }
}

// Initial Demo Location
$pdo->exec("INSERT IGNORE INTO locations (id, name, latitude, longitude, radius_meters) 
           VALUES (1, 'Main Office Jakarta', -6.2088, 106.8456, 100)");

// Initial Token
$randomToken = bin2hex(random_bytes(16));
$pdo->exec("INSERT IGNORE INTO qr_tokens (location_id, token) VALUES (1, '$randomToken')");

echo "Migration Complete. Sample Token Generated: $randomToken\n";
