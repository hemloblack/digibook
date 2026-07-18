<?php


if (session_status() === PHP_SESSION_NONE) session_start();

include 'config/database.php';
include 'includes/auth.php';

$orderDone = false;
$orderId   = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $address = trim($_POST['address'] ?? '');

    $totalPrice = 0;
    $cartItems  = json_decode($_COOKIE['cart'] ?? '[]', true);

    if (is_array($cartItems)) {
        foreach ($cartItems as $item) {
            $totalPrice += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }
    }

    // ذخیره سفارش
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_address, total_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $address, $totalPrice]);
    $orderId = $pdo->lastInsertId();

    // ذخیره آیتم‌های سفارش
    if (is_array($cartItems)) {
        foreach ($cartItems as $item) {
            $stmt2 = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$orderId, $item['id'] ?? 0, $item['quantity'] ?? 1, $item['price'] ?? 0]);
        }
    }

    // خالی کردن کوکی سبد خرید — قبل از هر output
    setcookie('cart', '', time() - 3600, '/');

    $orderDone = true;
}

// حالا که setcookie اجرا شد، هدر رو include می‌کنیم
include 'includes/header.php';
?>

<?php if ($orderDone): ?>
<div class="success-message">
    <div class="success-icon">✅</div>
    <h2>سفارش شما ثبت شد!</h2>
    <p>شماره سفارش شما: <strong style="color: var(--accent, #38b6ff)">#<?php echo $orderId; ?></strong></p>
    <p>به زودی با شما تماس خواهیم گرفت.</p>
    <a href="index.php" class="btn btn-primary btn-lg" style="margin-top:20px">🏠 بازگشت به فروشگاه</a>
</div>

<script>
    // پاک کردن localStorage سبد خرید
    localStorage.removeItem('bookstore_cart');
    document.getElementById('cart-count') && (document.getElementById('cart-count').textContent = '0');
</script>

<?php else: ?>

<div class="form-container">
    <h2>📝 تکمیل اطلاعات سفارش</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">نام کامل *</label>
            <input type="text" name="name" class="form-control"
                   placeholder="نام و نام خانوادگی"
                   value="<?php echo isLoggedIn() ? htmlspecialchars(currentUser()['name']) : ''; ?>"
                   required>
        </div>
        <div class="mb-3">
            <label class="form-label">ایمیل *</label>
            <input type="email" name="email" class="form-control"
                   placeholder="example@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">آدرس *</label>
            <textarea name="address" class="form-control" rows="3"
                      placeholder="آدرس کامل برای ارسال..." required></textarea>
        </div>
        <button type="submit" class="btn btn-success btn-lg w-100">✅ پرداخت و ثبت سفارش</button>
    </form>
</div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
