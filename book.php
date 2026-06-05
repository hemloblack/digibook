<?php
include 'config/database.php';
include 'includes/header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$_GET['id']]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book):
?>
    <div class="empty-state">
        <div class="empty-state-icon">📖</div>
        <h3>کتاب مورد نظر یافت نشد</h3>
        <p>کتابی که به دنبال آن هستید وجود ندارد یا حذف شده است</p>
        <a href="index.php" class="btn btn-primary">🏠 بازگشت به فروشگاه</a>
    </div>
<?php
    include 'includes/footer.php';
    exit;
endif;
?>

<div class="book-detail">
    <div class="book-detail-image">
        <?php
        $imagePath = 'uploads/' . htmlspecialchars($book['image'] ?? '');
        if (!empty($book['image']) && file_exists($imagePath)): ?>
            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
        <?php else: ?>
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='400' viewBox='0 0 300 400'%3E%3Crect width='300' height='400' fill='%231e2040'/%3E%3Ctext x='150' y='200' text-anchor='middle' fill='%237c6bff' font-size='60'%3E📖%3C/text%3E%3C/svg%3E" alt="بدون تصویر">
        <?php endif; ?>
    </div>

    <div class="book-detail-info">
        <span class="category">📂 <?php echo htmlspecialchars($book['category'] ?? 'متفرقه'); ?></span>
        <h1><?php echo htmlspecialchars($book['title']); ?></h1>
        <p class="author">✍️ نویسنده: <?php echo htmlspecialchars($book['author']); ?></p>

        <div class="price-box">
            <span class="price"><?php echo number_format($book['price']); ?></span>
        </div>

        <?php if (!empty($book['description'])): ?>
        <div class="description">
            <h4>📖 درباره کتاب</h4>
            <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="actions">
            <button class="btn btn-success btn-lg add-to-cart-btn"
                data-id="<?php echo $book['id']; ?>"
                data-title="<?php echo htmlspecialchars($book['title']); ?>"
                data-price="<?php echo $book['price']; ?>"
                data-image="<?php echo htmlspecialchars($book['image'] ?? ''); ?>">
                🛒 افزودن به سبد خرید
            </button>
            <a href="index.php" class="btn btn-outline btn-lg">🏠 بازگشت به فروشگاه</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
