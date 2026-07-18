<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}


function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}


function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /bookstore/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}


function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /bookstore/index.php?error=access_denied');
        exit;
    }
}


function currentUser(): array {
    if (!isLoggedIn()) return [];
    return [
        'id'       => $_SESSION['user_id'],
        'name'     => $_SESSION['user_name'] ?? '',
        'username' => $_SESSION['user_username'] ?? '',
        'role'     => $_SESSION['user_role'] ?? 'user',
    ];
}
