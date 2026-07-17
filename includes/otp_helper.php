<?php
/**
 * otp_helper.php - ارسال و مدیریت کد OTP
 * سرویس: sms.ir با خط اشتراکی
 * مسیر: includes/otp_helper.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// تنظیمات sms.ir — فقط همین بخش رو عوض کنید
// ============================================
define('SMS_API_KEY',    'S8rT72DEEi5paD1UbRRsPNZBOWtes1KCSlPyx7ofygZazUNY');
define('SMS_LINE_NUMBER', '30002108027251');   // شماره خط اشتراکی‌تون رو اینجا بذارید، مثلاً: '3000xxxxx'
// ============================================

/**
 * تولید کد ۶ رقمی تصادفی
 */
function generateOtpCode(): string {
    return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * ارسال OTP و ذخیره در دیتابیس
 */
function sendOtp(PDO $pdo, string $phone): array {

    // حذف کدهای قدیمی این شماره
    $pdo->prepare("DELETE FROM otp_codes WHERE phone = ?")->execute([$phone]);

    // ساخت کد جدید با ۲ دقیقه اعتبار
    $code      = generateOtpCode();
    $expiresAt = date('Y-m-d H:i:s', time() + 120);

    $pdo->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, ?)")
        ->execute([$phone, $code, $expiresAt]);

    // ارسال SMS
    $sent = sendSmsIr($phone, $code);

    if ($sent['success']) {
        return ['success' => true, 'message' => 'کد تایید به ' . $phone . ' ارسال شد'];
    }

    // حالت توسعه: کد رو روی صفحه نشون بده
    $_SESSION['dev_otp_' . $phone] = $code;
    return [
        'success' => true,
        'message' => '⚠️ کد (حالت تست - SMS ارسال نشد): ' . $code,
        'dev'     => true,
    ];
}

/**
 * ارسال SMS از طریق sms.ir
 * مستندات: https://app.sms.ir/developer/help/send
 */
function sendSmsIr(string $phone, string $code): array {

    // متن پیامک
    $message = "کد تایید دیجی بوک:\n{$code}\nاین کد ۲ دقیقه اعتبار دارد.";

    $payload = json_encode([
        'lineNumber' => SMS_LINE_NUMBER,
        'messageText' => $message,
        'mobiles'    => [$phone],
    ]);

    $ch = curl_init('https://api.sms.ir/v1/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: text/plain',
            'x-api-key: ' . SMS_API_KEY,
        ],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log('[SMS.IR] curl error: ' . $curlErr);
        return ['success' => false, 'error' => $curlErr];
    }

    $data = json_decode($response, true);

    // sms.ir در موفقیت status=1 برمی‌گردونه
    if ($httpCode === 200 && isset($data['status']) && $data['status'] === 1) {
        return ['success' => true];
    }

    error_log('[SMS.IR] failed: ' . $response);
    return ['success' => false, 'error' => $response];
}

/**
 * تایید کد OTP
 */
function verifyOtp(PDO $pdo, string $phone, string $code): array {

    $stmt = $pdo->prepare("
        SELECT id FROM otp_codes
        WHERE phone = ? AND code = ? AND used = 0 AND expires_at > NOW()
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$phone, $code]);
    $otp = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($otp) {
        $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?")
            ->execute([$otp['id']]);
        return ['success' => true];
    }

    // چک حالت توسعه (session)
    $devKey = 'dev_otp_' . $phone;
    if (isset($_SESSION[$devKey]) && $_SESSION[$devKey] === $code) {
        unset($_SESSION[$devKey]);
        return ['success' => true];
    }

    return ['success' => false, 'message' => 'کد وارد شده اشتباه یا منقضی شده است'];
}

/**
 * نرمال‌سازی شماره موبایل
 * ورودی‌های مختلف رو به فرمت 09xxxxxxxxx تبدیل می‌کنه
 */
function normalizePhone(string $phone): string {
    // تبدیل اعداد فارسی/عربی به انگلیسی
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $en = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
    $phone = str_replace($fa, $en, $phone);

    // فقط اعداد
    $phone = preg_replace('/\D/', '', $phone);

    // +98 یا 98 → 0
    if (str_starts_with($phone, '98') && strlen($phone) === 12) {
        $phone = '0' . substr($phone, 2);
    }
    // بدون صفر اول (9xxxxxxxxx)
    if (strlen($phone) === 10 && str_starts_with($phone, '9')) {
        $phone = '0' . $phone;
    }

    return $phone;
}
