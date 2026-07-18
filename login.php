<?php


if (session_status() === PHP_SESSION_NONE) session_start();

include 'config/database.php';
include 'includes/auth.php';

// اگر از قبل لاگین است
if (isLoggedIn()) {
    header('Location: /bookstore/index.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? '/bookstore/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'لطفاً همه فیلدها را پر کنید';
    } else {
        // جستجو با نام کاربری یا شماره موبایل
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR phone = ?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['name'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_role']     = $user['role'];
            session_regenerate_id(true);

            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به دیجی بوک</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/bookstore/assets/css/style.css">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: radial-gradient(ellipse at 30% 30%, rgba(124,107,255,0.1) 0%, transparent 60%),
                        radial-gradient(ellipse at 70% 70%, rgba(56,182,255,0.07) 0%, transparent 60%);
        }
        .auth-card {
            background: #12142a;
            border: 1px solid rgba(124,107,255,0.2);
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-logo .icon { font-size: 48px; display: block; margin-bottom: 10px; }
        .auth-logo h1 { font-size: 24px; font-weight: 900; color: #f0f0ff; margin: 0; }
        .auth-logo p  { font-size: 14px; color: #6b6d90; margin: 6px 0 0; }

        .auth-error {
            background: rgba(255,92,124,0.1);
            border: 1px solid rgba(255,92,124,0.3);
            color: #ff5c7c;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #8b8db0;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 13px 16px;
            background: #1a1c32;
            border: 1px solid rgba(124,107,255,0.18);
            border-radius: 12px;
            font-size: 14px;
            color: #f0f0ff;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            transition: all 0.25s;
            direction: ltr;
            text-align: right;
        }
        .form-group input:focus {
            outline: none;
            border-color: #7c6bff;
            box-shadow: 0 0 0 4px rgba(124,107,255,0.12);
            background: rgba(124,107,255,0.05);
        }
        .form-group input::placeholder { color: #404268; direction: rtl; }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #7c6bff, #5c4de8);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 20px rgba(124,107,255,0.35);
            margin-top: 6px;
        }
        .btn-auth:hover {
            background: linear-gradient(135deg, #9b8dff, #7c6bff);
            box-shadow: 0 8px 30px rgba(124,107,255,0.45);
            transform: translateY(-2px);
        }
        .auth-divider {
            text-align: center;
            margin: 22px 0;
            position: relative;
        }
        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%; left: 0; right: 0;
            height: 1px;
            background: rgba(124,107,255,0.15);
        }
        .auth-divider span {
            position: relative;
            background: #12142a;
            padding: 0 14px;
            font-size: 13px;
            color: #555775;
        }
        .register-link {
            text-align: center;
        }
        .register-link a {
            color: #7c6bff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }
        .register-link a:hover { color: #a99cff; }
    </style>
</head>
<body style="background:#0d0e1a; margin:0;">

<div class="auth-page">
    <div class="auth-card">

        <div class="auth-logo">
            <span class="icon">📚</span>
            <h1><a href="/bookstore">دیجی بوک</a></h1>
            <p>به حساب کاربری خود وارد شوید</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

            <div class="form-group">
                <label>نام کاربری یا شماره موبایل</label>
                <input type="text" name="login"
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>"
                       placeholder="username یا 09xxxxxxxxx"
                       autocomplete="username" required>
            </div>

            <div class="form-group">
                <label>رمز عبور</label>
                <input type="password" name="password"
                       placeholder="رمز عبور خود را وارد کنید"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn-auth">🔐 ورود به حساب</button>
        </form>

        <div class="auth-divider"><span>یا</span></div>

        <div class="register-link">
            <a href="/bookstore/register.php">حساب کاربری ندارم ← ثبت‌نام</a>
        </div>

    </div>
</div>

</body>
</html>
