<?php
/**
 * fix_admin.php — یک‌بار اجرا کنید، بعد حذفش کنید
 * مسیر: این فایل را مستقیم در پوشه bookstore بگذارید
 * سپس در مرورگر باز کنید: http://localhost/bookstore/fix_admin.php
 */

include 'config/database.php';

$phone    = '09000000000';
$name     = 'مدیر سایت';
$username = 'admin';
$password = 'admin1234';
$hash     = password_hash($password, PASSWORD_DEFAULT);

// اگر ادمین وجود داره آپدیت کن، نداره بساز
$check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);

if ($check->fetch()) {
    $stmt = $pdo->prepare("UPDATE users SET password = ?, phone = ?, name = ? WHERE username = ?");
    $stmt->execute([$hash, $phone, $name, $username]);
    echo "<p style='color:green;font-family:tahoma'>✅ رمز ادمین آپدیت شد.</p>";
} else {
    $stmt = $pdo->prepare("INSERT INTO users (phone, name, username, password, role) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute([$phone, $name, $username, $hash]);
    echo "<p style='color:green;font-family:tahoma'>✅ ادمین ساخته شد.</p>";
}

echo "<p style='font-family:tahoma'>
    نام کاربری: <b>admin</b><br>
    رمز عبور: <b>admin1234</b><br><br>
    ⚠️ بعد از لاگین این فایل را حذف کنید!<br><br>
    <a href='/bookstore/login.php'>← برو به صفحه لاگین</a>
</p>";
