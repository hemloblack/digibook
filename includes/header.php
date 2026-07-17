<?php
/**
 * header.php - هدر مشترک سایت با احراز هویت
 * مسیر: /bookstore/includes/header.php
 */

if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 دیجی بوک</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/bookstore/assets/css/style.css">
    <style>
        /* ---- منوی کاربر ---- */
        .user-menu-wrap { position: relative; }

        .user-menu-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 16px;
            background: rgba(124,107,255,0.1);
            border: 1px solid rgba(124,107,255,0.25);
            border-radius: 999px;
            color: #a99cff;
            font-size: 14px; font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
        }
        .user-menu-btn:hover {
            background: rgba(124,107,255,0.18);
            border-color: rgba(124,107,255,0.45);
        }
        .user-avatar {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, #7c6bff, #5c4de8);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 800; color: white;
        }
        .admin-badge {
            background: linear-gradient(135deg, #ff9b38, #ff6a00);
            color: white; font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 999px;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            background: #12142a;
            border: 1px solid rgba(124,107,255,0.2);
            border-radius: 14px;
            min-width: 180px;
            box-shadow: 0 16px 40px rgba(0,0,0,0.5);
            opacity: 0; visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.22s ease;
            z-index: 999;
            overflow: hidden;
        }
        .user-menu-wrap:hover .user-dropdown,
        .user-menu-wrap:focus-within .user-dropdown {
            opacity: 1; visibility: visible; transform: translateY(0);
        }
        .user-dropdown a {
            display: flex; align-items: center; gap: 9px;
            padding: 11px 16px;
            font-size: 13.5px; color: #c0c0e0;
            text-decoration: none;
            transition: all 0.2s;
        }
        .user-dropdown a:hover { background: rgba(124,107,255,0.1); color: #f0f0ff; }
        .user-dropdown a.danger { color: #ff7c9a; }
        .user-dropdown a.danger:hover { background: rgba(255,92,124,0.1); }
        .user-dropdown hr {
            margin: 4px 12px; border: none;
            border-top: 1px solid rgba(124,107,255,0.12);
        }
        .user-info-row {
            padding: 13px 16px 9px;
            border-bottom: 1px solid rgba(124,107,255,0.1);
        }
        .user-info-row .uname {
            font-size: 13px; font-weight: 700; color: #f0f0ff;
        }
        .user-info-row .urole {
            font-size: 11px; color: #555775; margin-top: 2px;
        }

        /* پیام خوش‌آمد */
        .welcome-bar {
            background: rgba(0,229,160,0.08);
            border-bottom: 1px solid rgba(0,229,160,0.15);
            text-align: center;
            padding: 9px 20px;
            font-size: 13px; color: #00e5a0;
        }
    </style>
</head>
<body>

<?php if (isset($_GET['welcome']) && isLoggedIn()): ?>
<div class="welcome-bar">
    🎉 خوش آمدید، <?php echo htmlspecialchars(currentUser()['name']); ?>! ثبت‌نام شما با موفقیت انجام شد.
</div>
<?php endif; ?>

<nav class="navbar">
    <div class="container">

        <a href="/bookstore/index.php" class="navbar-brand">
            <span class="logo-icon">📚</span>
            دیجی بوک
        </a>

        <ul class="navbar-nav">
            <li>
                <a href="/bookstore/index.php"
                   class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    🏠 خانه
                </a>
            </li>

            <?php if (isAdmin()): ?>
            <li>
                <a href="/bookstore/admin/add-book.php"
                   class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'add-book.php' ? 'active' : ''; ?>">
                    ➕ افزودن کتاب
                </a>
            </li>
            <?php endif; ?>

            <li>
                <a href="/bookstore/cart.php" class="cart-btn">
                    <span>🛒</span>
                    سبد خرید
                    <span class="cart-count" id="cart-count">0</span>
                </a>
            </li>

            <?php if (isLoggedIn()):
                $cu = currentUser();
                $initials = mb_substr($cu['name'], 0, 1);
            ?>
            <li class="user-menu-wrap">
                <button class="user-menu-btn">
                    <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <?php echo htmlspecialchars($cu['name']); ?>
                    <?php if (isAdmin()): ?>
                        <span class="admin-badge">ادمین</span>
                    <?php endif; ?>
                    ▾
                </button>
                <div class="user-dropdown">
                    <div class="user-info-row">
                        <div class="uname">@<?php echo htmlspecialchars($cu['username']); ?></div>
                        <div class="urole"><?php echo isAdmin() ? '👑 مدیر سایت' : '👤 کاربر عادی'; ?></div>
                    </div>
                    <?php if (isAdmin()): ?>
                    <a href="/bookstore/admin/add-book.php">➕ افزودن کتاب</a>
                    <?php endif; ?>
                    <a href="/bookstore/cart.php">🛒 سبد خرید</a>
                    <hr>
                    <a href="/bookstore/logout.php" class="danger">🚪 خروج</a>
                </div>
            </li>

            <?php else: ?>
            <li>
                <a href="/bookstore/login.php" class="nav-link">
                    🔐 ورود / ثبت‌نام
                </a>
            </li>
            <?php endif; ?>

        </ul>
    </div>
</nav>

<?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
<!-- بنر تبلیغاتی — فقط در صفحه اصلی (970×90 Leaderboard) -->
<div class="site-banner-wrap">
    <div class="site-banner-inner">
        <a href="#" class="site-banner-link">
            <img src="/bookstore/uploads/gpt-image-2_سلام_یه_بنر_تبلیغاتی_برای_سایت_کتابخونه_ای_ب-0 (1).jpg"
                 alt="بنر تبلیغاتی" class="site-banner-img"
                 onerror="this.style.display='none'; document.getElementById('bannerPlaceholder').style.display='flex';">
            <div class="banner-placeholder" id="bannerPlaceholder" style="display:none;">
                <span class="banner-placeholder-icon">🖼️</span>
                <div>
                    <p class="banner-placeholder-title">بنر تبلیغاتی شما اینجا نمایش داده می‌شود</p>
                    <p class="banner-placeholder-sub">اندازه استاندارد: ۹۷۰ × ۹۰ پیکسل</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<main>
    <div class="container">
