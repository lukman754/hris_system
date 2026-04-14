<?php
require_once 'config/database.php';
$pdo = db();

try {
    $pdo->query("SELECT phone_number FROM users LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) AFTER email");
}

try {
    $pdo->query("SELECT photo_profile FROM users LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE users ADD COLUMN photo_profile VARCHAR(255) AFTER phone_number");
}

$pdo->exec("UPDATE users SET phone_number = '81234567890' WHERE phone_number IS NULL OR phone_number = ''");
echo "DB Updated successfully.";
