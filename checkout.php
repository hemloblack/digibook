<?php
include 'config/database.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // محاسبه قیمت کل (از کوکی سمت کاربر)
    $totalPrice = 0;
    $cartItems = json_decode($_COOKIE['cart'] ?? '[]', true);
    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    // ذخیره سفارش
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $address, $totalPrice]);
    $orderId = $pdo->lastInsertId();

    // ذخیره آیتم‌های سفارش
    foreach ($cartItems as $item) {
        $stmt2 = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
    }

    // خالی کردن سبد خرید
    setcookie('cart', '', time() - 3600, '/');
    ?>
    <div class="success-message">
        <div class="success-icon">✅</div>
        <h2>سفارش شما ثبت شد!</h2>
        <p>شماره سفارش شما: <strong style="color: var(--accent)">#<?php echo $orderId; ?></strong></p>
        <p>به زودی با شما تماس خواهیم گرفت.</p>
        <a href="index.php" class="btn btn-primary btn-lg" style="margin-top:20px">🏠 بازگشت به فروشگاه</a>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}
?>

<div class="form-container">
    <h2>📝 تکمیل اطلاعات سفارش</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">نام کامل *</label>
            <input type="text" name="name" class="form-control" placeholder="نام و نام خانوادگی" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ایمیل *</label>
            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">آدرس *</label>
            <textarea name="address" class="form-control" rows="3" placeholder="آدرس کامل برای ارسال..." required></textarea>
        </div>
        <button type="submit" class="btn btn-success btn-lg w-100">✅ پرداخت و ثبت سفارش</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
