<?php

if (session_status() === PHP_SESSION_NONE) session_start();

include 'config/database.php';
include 'includes/auth.php';
include 'includes/otp_helper.php';

if (isLoggedIn()) {
    header('Location: /bookstore/index.php');
    exit;
}

$step  = $_SESSION['reg_step'] ?? 1;
$error = '';
$info  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /* ---- مرحله ۱: دریافت شماره موبایل ---- */
    if ($action === 'send_otp') {
        $phone = normalizePhone($_POST['phone'] ?? '');

        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            $error = 'شماره موبایل وارد شده معتبر نیست';
            $step  = 1;
        } else {
            $chk = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $chk->execute([$phone]);
            if ($chk->fetch()) {
                $error = 'این شماره موبایل قبلاً ثبت شده. وارد شوید';
                $step  = 1;
            } else {
                $result = sendOtp($pdo, $phone);
                if ($result['success']) {
                    $_SESSION['reg_phone'] = $phone;
                    $_SESSION['reg_step']  = 2;
                    $step = 2;
                    $info = isset($result['dev'])
                        ? $result['message']
                        : 'کد تایید به ' . $phone . ' ارسال شد';
                } else {
                    $error = 'خطا در ارسال کد. دوباره تلاش کنید';
                    $step  = 1;
                }
            }
        }
    }

    /* ---- ارسال مجدد کد ---- */
    elseif ($action === 'resend_otp') {
        $phone = $_SESSION['reg_phone'] ?? '';
        if ($phone) {
            $result = sendOtp($pdo, $phone);
            $step   = 2;
            $info   = isset($result['dev'])
                ? $result['message']
                : 'کد جدید به ' . $phone . ' ارسال شد';
        } else {
            $_SESSION['reg_step'] = 1;
            $step = 1;
        }
    }

    /* ---- مرحله ۲: تایید کد OTP ---- */
    elseif ($action === 'verify_otp') {
        $code  = trim($_POST['otp_code'] ?? '');
        $phone = $_SESSION['reg_phone'] ?? '';

        if (empty($phone)) {
            $_SESSION['reg_step'] = 1;
            $step  = 1;
            $error = 'لطفاً از ابتدا شروع کنید';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $error = 'کد باید ۶ رقم باشد';
            $step  = 2;
        } else {
            $result = verifyOtp($pdo, $phone, $code);
            if ($result['success']) {
                $_SESSION['reg_phone_verified'] = true;
                $_SESSION['reg_step']           = 3;
                $step = 3;
            } else {
                $error = $result['message'];
                $step  = 2;
            }
        }
    }

    /* ---- مرحله ۳: ثبت اطلاعات کاربر ---- */
    elseif ($action === 'complete_register') {
        $phone    = $_SESSION['reg_phone']          ?? '';
        $verified = $_SESSION['reg_phone_verified'] ?? false;

        if (!$phone || !$verified) {
            $_SESSION['reg_step'] = 1;
            header('Location: /bookstore/register.php');
            exit;
        }

        $name     = trim($_POST['name']     ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm']       ?? '';

        if (empty($name) || empty($username) || empty($password)) {
            $error = 'همه فیلدها الزامی هستند';
            $step  = 3;
        } elseif (mb_strlen($name) < 3) {
            $error = 'نام باید حداقل ۳ حرف باشد';
            $step  = 3;
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = 'نام کاربری فقط حروف انگلیسی، اعداد و _ (۳ تا ۲۰ کاراکتر)';
            $step  = 3;
        } elseif (strlen($password) < 6) {
            $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
            $step  = 3;
        } elseif ($password !== $confirm) {
            $error = 'رمز عبور و تکرار آن یکسان نیستند';
            $step  = 3;
        } else {
            $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $chk->execute([$username]);
            if ($chk->fetch()) {
                $error = 'این نام کاربری قبلاً گرفته شده، یکی دیگر انتخاب کنید';
                $step  = 3;
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins  = $pdo->prepare("INSERT INTO users (phone, name, username, password, role) VALUES (?, ?, ?, ?, 'user')");
                $ins->execute([$phone, $name, $username, $hash]);
                $userId = $pdo->lastInsertId();

                unset($_SESSION['reg_step'], $_SESSION['reg_phone'], $_SESSION['reg_phone_verified']);

                $_SESSION['user_id']       = $userId;
                $_SESSION['user_name']     = $name;
                $_SESSION['user_username'] = $username;
                $_SESSION['user_role']     = 'user';
                session_regenerate_id(true);

                header('Location: /bookstore/index.php?welcome=1');
                exit;
            }
        }
    }

    /* ---- برگشت به مرحله قبل ---- */
    elseif ($action === 'back') {
        $backTo = (int)($_POST['back_to'] ?? 1);
        // اگر برگشتیم به مرحله ۱، شماره رو هم پاک کن
        if ($backTo === 1) {
            unset($_SESSION['reg_phone'], $_SESSION['reg_phone_verified']);
        }
        $_SESSION['reg_step'] = $backTo;
        $step = $backTo;
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت‌نام - دیجی بوک</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/bookstore/assets/css/style.css">
    <style>
        body { background: #0d0e1a; margin: 0; }

        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background:
                radial-gradient(ellipse at 20% 40%, rgba(124,107,255,0.1) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 60%, rgba(56,182,255,0.07) 0%, transparent 55%);
        }
        .auth-card {
            background: #12142a;
            border: 1px solid rgba(124,107,255,0.2);
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        }
        .auth-logo { text-align: center; margin-bottom: 28px; }
        .auth-logo .icon { font-size: 46px; display: block; margin-bottom: 8px; }
        .auth-logo h1 { font-size: 22px; font-weight: 900; color: #f0f0ff; margin: 0 0 5px; }
        .auth-logo p  { font-size: 13px; color: #6b6d90; margin: 0; }

        /* استپ‌ها */
        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .step-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700;
            border: 2px solid rgba(124,107,255,0.2);
            background: #1a1c32;
            color: #555775;
            transition: all 0.3s;
        }
        .step-item.active .step-circle {
            background: linear-gradient(135deg, #7c6bff, #5c4de8);
            border-color: #7c6bff;
            color: white;
            box-shadow: 0 4px 16px rgba(124,107,255,0.4);
        }
        .step-item.done .step-circle {
            background: rgba(0,229,160,0.15);
            border-color: #00e5a0;
            color: #00e5a0;
        }
        .step-label { font-size: 11px; color: #555775; white-space: nowrap; }
        .step-item.active .step-label { color: #a99cff; }
        .step-item.done   .step-label { color: #00e5a0; }
        .step-line {
            flex: 1;
            height: 2px;
            background: rgba(124,107,255,0.12);
            margin: 0 6px 22px;
            min-width: 40px;
        }

        /* پیام‌ها */
        .msg-error {
            background: rgba(255,92,124,0.1);
            border: 1px solid rgba(255,92,124,0.3);
            color: #ff5c7c;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .msg-info {
            background: rgba(0,229,160,0.08);
            border: 1px solid rgba(0,229,160,0.25);
            color: #00e5a0;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* فرم */
        .form-group { margin-bottom: 17px; }
        .form-group label {
            display: block; font-size: 13px;
            font-weight: 600; color: #8b8db0; margin-bottom: 7px;
        }
        .form-group input {
            width: 100%; padding: 13px 16px;
            background: #1a1c32;
            border: 1px solid rgba(124,107,255,0.18);
            border-radius: 12px;
            font-size: 14px; color: #f0f0ff;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            transition: all 0.25s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #7c6bff;
            box-shadow: 0 0 0 4px rgba(124,107,255,0.12);
            background: rgba(124,107,255,0.04);
        }
        .form-group input::placeholder { color: #404268; }
        .form-group small { display: block; margin-top: 5px; font-size: 12px; color: #555775; }

        .otp-input {
            text-align: center !important;
            font-size: 26px !important;
            font-weight: 800 !important;
            letter-spacing: 10px;
            direction: ltr;
        }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #7c6bff, #5c4de8);
            color: white; border: none;
            border-radius: 12px;
            font-size: 15px; font-weight: 700;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 20px rgba(124,107,255,0.35);
            margin-top: 6px;
        }
        .btn-auth:hover {
            background: linear-gradient(135deg, #9b8dff, #7c6bff);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(124,107,255,0.45);
        }
        .btn-back {
            width: 100%;
            padding: 11px;
            background: transparent;
            color: #8b8db0;
            border: 1px solid rgba(124,107,255,0.2);
            border-radius: 12px;
            font-size: 13px; font-weight: 600;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            cursor: pointer;
            transition: all 0.25s;
            margin-top: 10px;
        }
        .btn-back:hover { border-color: rgba(124,107,255,0.5); color: #a99cff; }

        .otp-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
            font-size: 13px;
            color: #555775;
        }
        .btn-resend {
            background: none; border: none;
            color: #7c6bff; font-size: 13px; font-weight: 600;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            cursor: pointer; padding: 0;
            transition: color 0.2s;
        }
        .btn-resend:hover { color: #a99cff; }

        .login-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13.5px;
            color: #555775;
        }
        .login-link a {
            color: #7c6bff; font-weight: 600;
            text-decoration: none; transition: color 0.2s;
        }
        .login-link a:hover { color: #a99cff; }

        .phone-display {
            font-size: 20px; font-weight: 800;
            color: #38b6ff; direction: ltr;
            display: block; text-align: center;
            margin: 6px 0 20px;
            letter-spacing: 1px;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 30px 22px; }
            .step-line { min-width: 20px; }
        }
    </style>
</head>
<body>
<div class="auth-page">
<div class="auth-card">

    <div class="auth-logo">
        <span class="icon">📚</span>
        <h1>دیجی بوک</h1>
        <p>ثبت‌نام حساب کاربری جدید</p>
    </div>

    <!-- استپ‌ها -->
    <div class="steps">
        <div class="step-item <?php echo $step === 1 ? 'active' : ($step > 1 ? 'done' : ''); ?>">
            <div class="step-circle"><?php echo $step > 1 ? '✓' : '1'; ?></div>
            <span class="step-label">موبایل</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item <?php echo $step === 2 ? 'active' : ($step > 2 ? 'done' : ''); ?>">
            <div class="step-circle"><?php echo $step > 2 ? '✓' : '2'; ?></div>
            <span class="step-label">تایید کد</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item <?php echo $step === 3 ? 'active' : ''; ?>">
            <div class="step-circle">3</div>
            <span class="step-label">اطلاعات</span>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="msg-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($info): ?>
        <div class="msg-info"><?php echo htmlspecialchars($info); ?></div>
    <?php endif; ?>

    <!-- ========= مرحله ۱: شماره موبایل ========= -->
    <?php if ($step === 1): ?>
    <form method="POST" autocomplete="off">
        <input type="hidden" name="action" value="send_otp">
        <div class="form-group">
            <label>شماره موبایل</label>
            <input type="tel" name="phone"
                   value=""
                   placeholder="09xxxxxxxxx"
                   maxlength="11"
                   style="direction:ltr; text-align:center;"
                   autocomplete="off"
                   required autofocus>
            <small>کد تایید به این شماره پیامک می‌شود</small>
        </div>
        <button type="submit" class="btn-auth">📱 ارسال کد تایید</button>
    </form>

    <!-- ========= مرحله ۲: کد OTP ========= -->
    <?php elseif ($step === 2): ?>

    <p style="text-align:center; color:#8b8db0; font-size:14px; margin:0 0 4px;">
        کد ۶ رقمی ارسال‌شده به:
    </p>
    <span class="phone-display"><?php echo htmlspecialchars($_SESSION['reg_phone'] ?? ''); ?></span>

    <!-- فرم تایید کد — مجزا و بدون هیچ فرم دیگه‌ای داخلش -->
    <form method="POST" autocomplete="off" id="verifyForm">
        <input type="hidden" name="action" value="verify_otp">
        <div class="form-group">
            <label>کد تایید</label>
            <input type="text" name="otp_code"
                   id="otpInput"
                   class="otp-input"
                   maxlength="6"
                   inputmode="numeric"
                   placeholder="● ● ● ● ● ●"
                   autocomplete="one-time-code"
                   required autofocus>
        </div>
        <button type="submit" class="btn-auth">✅ تایید کد</button>

        <div class="otp-footer">
            <span>کد دریافت نکردید؟</span>
            <!-- دکمه ارسال مجدد: action رو با JS عوض می‌کنه -->
            <button type="button" class="btn-resend" onclick="resendOtp()">ارسال مجدد</button>
        </div>
    </form>

    <!-- فرم برگشت -->
    <form method="POST" id="backForm" style="margin-top:10px;">
        <input type="hidden" name="action"  value="back">
        <input type="hidden" name="back_to" value="1">
        <button type="submit" class="btn-back">← تغییر شماره موبایل</button>
    </form>

    <!-- فرم مخفی ارسال مجدد — کاملاً جدا -->
    <form method="POST" id="resendForm" style="display:none;">
        <input type="hidden" name="action" value="resend_otp">
    </form>

    <script>
    function resendOtp() {
        document.getElementById('resendForm').submit();
    }
    // فوکوس خودکار روی input
    document.addEventListener('DOMContentLoaded', function() {
        var inp = document.getElementById('otpInput');
        if (inp) inp.focus();
    });
    </script>

    <!-- ========= مرحله ۳: اطلاعات کاربر ========= -->
    <?php elseif ($step === 3): ?>
    <form method="POST" autocomplete="off">
        <input type="hidden" name="action" value="complete_register">

        <div class="form-group">
            <label>نام و نام خانوادگی</label>
            <input type="text" name="name"
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                   placeholder="مثلاً: علی احمدی"
                   required>
        </div>
        <div class="form-group">
            <label>نام کاربری</label>
            <input type="text" name="username"
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                   placeholder="مثلاً: ali_ahmadi"
                   style="direction:ltr; text-align:right;"
                   pattern="[a-zA-Z0-9_]{3,20}" required>
            <small>حروف انگلیسی، اعداد و _ (۳ تا ۲۰ کاراکتر)</small>
        </div>
        <div class="form-group">
            <label>رمز عبور</label>
            <input type="password" name="password"
                   placeholder="حداقل ۶ کاراکتر"
                   minlength="6" required>
        </div>
        <div class="form-group">
            <label>تکرار رمز عبور</label>
            <input type="password" name="confirm"
                   placeholder="رمز عبور را دوباره وارد کنید"
                   minlength="6" required>
        </div>

        <button type="submit" class="btn-auth">🎉 ثبت‌نام و ورود</button>
    </form>
    <?php endif; ?>

    <div class="login-link">
        قبلاً ثبت‌نام کردی؟ <a href="/bookstore/login.php">وارد شو</a>
    </div>

</div>
</div>
</body>
</html>
