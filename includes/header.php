<?php
/**
 * هدر مشترک سایت کتاب‌فروشی
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 کتاب‌فروشی من</title>
    
    <!-- استایل‌ها -->
    <link rel="stylesheet" href="/bookstore/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="/bookstore/index.php" class="navbar-brand">
            <span class="logo-icon">📚</span>
            کتاب‌فروشی من
        </a>
        
        <ul class="navbar-nav">
            <li>
                <a href="/bookstore/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    🏠 خانه
                </a>
            </li>
            <li>
                <a href="/bookstore/admin/add-book.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add-book.php' ? 'active' : ''; ?>">
                    ➕ افزودن کتاب
                </a>
            </li>
            <li>
                <a href="/bookstore/cart.php" class="cart-btn">
                    <span class="cart-icon">🛒</span>
                    سبد خرید
                    <span class="cart-count" id="cart-count">0</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<main>
    <div class="container">