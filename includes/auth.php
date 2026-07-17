<?php
/**
 * auth.php - بررسی وضعیت احراز هویت
 * این فایل را در includes/ قرار دهید
 * استفاده: include 'includes/auth.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * آیا کاربر لاگین کرده؟
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * آیا کاربر ادمین است؟
 */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * اگر لاگین نشده، به صفحه لاگین بفرست
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /bookstore/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * اگر ادمین نیست، برگردان
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /bookstore/index.php?error=access_denied');
        exit;
    }
}

/**
 * اطلاعات کاربر لاگین‌شده
 */
function currentUser(): array {
    if (!isLoggedIn()) return [];
    return [
        'id'       => $_SESSION['user_id'],
        'name'     => $_SESSION['user_name'] ?? '',
        'username' => $_SESSION['user_username'] ?? '',
        'role'     => $_SESSION['user_role'] ?? 'user',
    ];
}
