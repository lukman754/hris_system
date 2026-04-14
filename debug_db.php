<?php
require 'config/database.php';
$pdo = db();
$stmt = $pdo->query("DESC attendance");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
