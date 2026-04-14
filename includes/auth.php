<?php
/**
 * Auth helper – session-based authentication
 */

session_start();

function auth_check(): void {
    if (empty($_SESSION['user'])) {
        header('Location: /hris_system/?page=login');
        exit;
    }
}

function auth_user(): array {
    return $_SESSION['user'] ?? [];
}

function auth_is_hrd(): bool {
    return (auth_user()['role'] ?? '') === 'hrd';
}

function auth_login(string $email, string $password): array|false {
    require_once __DIR__ . '/../config/database.php';
    $pdo = db();
    if (!$pdo) return false;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Sederhana dulu: direct password comparison (sebaiknya password_verify nanti)
    if ($user && ($user['password'] === $password)) {
        return $user;
    }
    
    return false;
}

function auth_verify_password(string $userId, string $password): bool {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return ($user && $user['password'] === $password);
}
