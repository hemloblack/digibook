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
    <title>📚 دیجی بوک</title>

    <!-- Vazirmatn Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- استایل‌ها -->
    <link rel="stylesheet" href="/bookstore/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="/bookstore/index.php" class="navbar-brand">
            <span class="logo-icon">📚</span>
           دیجی بوک
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


<div class="site-banner-wrap">
    <div class="site-banner-inner">
        <a href="#" class="site-banner-link" id="bannerLink">
            <img src="/bookstore/uploads/gpt-image-2_سلام_یه_بنر_تبلیغاتی_برای_سایت_کتابخونه_ای_ب-0 (1).jpg" alt="بنر تبلیغاتی" class="site-banner-img" id="bannerImg"
                 onerror="this.style.display='none'; document.getElementById('bannerPlaceholder').style.display='flex';">
        
        </a>
    </div>
</div>

<main>
    <div class="container">
