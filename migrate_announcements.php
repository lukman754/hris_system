<?php
require_once 'config/database.php';
$pdo = db();

echo "Adding expires_at to announcements table...\n";

try {
    $pdo->exec("ALTER TABLE announcements ADD COLUMN expires_at DATE DEFAULT NULL");
    echo "Column added successfully.\n";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}

echo "Migration Complete.\n";
