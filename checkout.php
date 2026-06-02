<?php
include 'config/database.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    
    // محاسبه قیمت کل (از کوکی سمت کاربر)
    // در عمل باید از دیتابیس چک شود
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
    
    echo '<div class="alert alert-success">سفارش شما با موفقیت ثبت شد. شماره سفارش: ' . $orderId . '</div>';
    include 'includes/footer.php';
    exit;
}
?>

<h2>📝 تکمیل اطلاعات سفارش</h2>
<form method="POST" class="bg-light p-4 rounded shadow">
    <div class="mb-3">
        <label class="form-label">نام کامل *</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">ایمیل *</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">آدرس *</label>
        <textarea name="address" class="form-control" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-success btn-lg w-100">پرداخت و ثبت سفارش</button>
</form>

<?php include 'includes/footer.php'; ?>