<?php
include '../config/database.php';
include '../includes/header.php';

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // آپلود عکس
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $imageName;
        }
    }
    
    // ذخیره در دیتابیس
    $stmt = $pdo->prepare("INSERT INTO books (title, author, price, category, description, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $author, $price, $category, $description, $image]);
    
    echo '<div class="alert alert-success">کتاب با موفقیت اضافه شد!</div>';
}
?>

<h2>➕ افزودن کتاب جدید</h2>
<form method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded shadow">
    <div class="mb-3">
        <label for="title" class="form-label">عنوان کتاب *</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
        <label for="author" class="form-label">نویسنده *</label>
        <input type="text" class="form-control" id="author" name="author" required>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="price" class="form-label">قیمت (تومان) *</label>
            <input type="number" class="form-control" id="price" name="price" step="1000" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="category" class="form-label">دسته‌بندی</label>
            <select class="form-select" id="category" name="category">
                <option value="ادبیات">ادبیات</option>
                <option value="علمی">علمی</option>
                <option value="تاریخی">تاریخی</option>
                <option value="کودک و نوجوان">کودک و نوجوان</option>
                <option value="کامپیوتر">کامپیوتر</option>
                <option value="روانشناسی">روانشناسی</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">توضیحات</label>
        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">تصویر کتاب</label>
        <input class="form-control" type="file" id="image" name="image" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">ذخیره کتاب</button>
</form>

<?php include '../includes/footer.php'; ?>