<?php
/**
 * Konfigurasi & koneksi database (Singleton PDO)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hris_system');
define('DB_USER', 'root');
define('DB_PASS', '');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            // DB tidak tersedia → pakai data demo, tidak error fatal
            return null;
        }
    }
    return $pdo;
}
