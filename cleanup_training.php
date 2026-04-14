<?php
require_once 'config/database.php';
$pdo = db();
if ($pdo) {
    echo "Dropping training tables...\n";
    $pdo->exec("DROP TABLE IF EXISTS training_participants;");
    $pdo->exec("DROP TABLE IF EXISTS training;");
    
    // Also remove any calendar events or announcements related to training if we want to be thorough
    $pdo->exec("DELETE FROM calendar_events WHERE category = 'training';");
    
    echo "Database training data removed.\n";
}
