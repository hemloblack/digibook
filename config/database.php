<?php
$host = 'localhost';
$dbname = 'bookstore_db';
$username = 'root';  // نام کاربری دیتابیس خودت
$password = '';      // پسورد دیتابیس خودت

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}
?>