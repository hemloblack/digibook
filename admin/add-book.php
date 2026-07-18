<?php


if (session_status() === PHP_SESSION_NONE) session_start();

include '../config/database.php';
include '../includes/auth.php';
include '../includes/header.php';

// فقط ادمین اجازه دارد
requireAdmin();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $author      = trim($_POST['author']      ?? '');
    $price       = (float)($_POST['price']    ?? 0);
    $category    = trim($_POST['category']    ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($author) || $price <= 0) {
        $error = 'عنوان، نویسنده و قیمت الزامی هستند';
    } else {
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $error = 'فرمت تصویر مجاز نیست (jpg، png، webp)';
            } else {
                $imageName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                    $image = $imageName;
                }
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, price, category, description, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $price, $category, $description, $image]);
            $success = 'کتاب با موفقیت اضافه شد!';
        }
    }
}
?>

<div class="form-container">
    <h2>➕ افزودن کتاب جدید</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">عنوان کتاب *</label>
            <input type="text" class="form-control" name="title"
                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                   placeholder="نام کتاب" required>
        </div>
        <div class="mb-3">
            <label class="form-label">نویسنده *</label>
            <input type="text" class="form-control" name="author"
                   value="<?php echo htmlspecialchars($_POST['author'] ?? ''); ?>"
                   placeholder="نام نویسنده" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">قیمت (تومان) *</label>
                <input type="number" class="form-control" name="price"
                       value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                       step="1000" placeholder="مثلاً ۵۰۰۰۰۰" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">دسته‌بندی</label>
                <select class="form-control" name="category">
                    <?php
                    $cats = ['ادبیات','علمی','تاریخی','کودک و نوجوان','کامپیوتر','روانشناسی'];
                    foreach ($cats as $cat):
                        $sel = (($_POST['category'] ?? '') === $cat) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $cat; ?>" <?php echo $sel; ?>><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">توضیحات</label>
            <textarea class="form-control" name="description" rows="4"
                      placeholder="توضیح مختصری درباره کتاب..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">تصویر کتاب</label>
            <input class="form-control" type="file" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">💾 ذخیره کتاب</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
