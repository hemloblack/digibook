<?php
include 'config/database.php';
include 'includes/header.php';

// دریافت همه کتاب‌ها
$stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="books-grid">
    <?php if (empty($books)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <h3>هنوز کتابی اضافه نشده است</h3>
            <p>اولین کتاب را به فروشگاه خود اضافه کنید</p>
            <a href="admin/add-book.php" class="btn btn-primary">➕ افزودن کتاب جدید</a>
        </div>
    <?php endif; ?>
    
    <?php foreach ($books as $book): ?>
        <div class="book-card">
            <div class="book-card-image">
                <?php 
                $imagePath = 'uploads/' . htmlspecialchars($book['image'] ?? '');
                if (!empty($book['image']) && file_exists($imagePath)): ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <?php else: ?>
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='240' viewBox='0 0 200 240'%3E%3Crect width='200' height='240' fill='%23f0f0f0'/%3E%3Ctext x='100' y='120' text-anchor='middle' fill='%23999' font-size='14'%3Eبدون تصویر%3C/text%3E%3C/svg%3E" alt="بدون تصویر">
                <?php endif; ?>
                <span class="category-badge"><?php echo htmlspecialchars($book['category'] ?? 'متفرقه'); ?></span>
            </div>
            <div class="book-card-body">
                <h3 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p class="book-card-author">✍️ <?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-card-price"><?php echo number_format($book['price']); ?></div>
                <div class="book-card-actions">
                    <a href="book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">📖 جزئیات</a>
                    <button class="btn btn-secondary btn-sm add-to-cart-btn" 
                        data-id="<?php echo $book['id']; ?>" 
                        data-title="<?php echo htmlspecialchars($book['title']); ?>" 
                        data-price="<?php echo $book['price']; ?>" 
                        data-image="<?php echo htmlspecialchars($book['image'] ?? ''); ?>">
                        🛒 افزودن
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>