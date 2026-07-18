<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('SMS_API_KEY',    'S8rT72DEEi5paD1UbRRsPNZBOWtes1KCSlPyx7ofygZazUNY');
define('SMS_LINE_NUMBER', '30002108027251');   
function generateOtpCode(): string {
    return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}


function sendOtp(PDO $pdo, string $phone): array {

    // حذف کدهای قدیمی این شماره
    $pdo->prepare("DELETE FROM otp_codes WHERE phone = ?")->execute([$phone]);

    
    $code      = generateOtpCode();
    $expiresAt = date('Y-m-d H:i:s', time() + 120);

    $pdo->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, ?)")
        ->execute([$phone, $code, $expiresAt]);

    
    $sent = sendSmsIr($phone, $code);

    if ($sent['success']) {
        return ['success' => true, 'message' => 'کد تایید به ' . $phone . ' ارسال شد'];
    }

    
    $_SESSION['dev_otp_' . $phone] = $code;
    return [
        'success' => true,
        'message' => '⚠️ کد (حالت تست - SMS ارسال نشد): ' . $code,
        'dev'     => true,
    ];
}


function sendSmsIr(string $phone, string $code): array {

    
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

   
    if ($httpCode === 200 && isset($data['status']) && $data['status'] === 1) {
        return ['success' => true];
    }

    error_log('[SMS.IR] failed: ' . $response);
    return ['success' => false, 'error' => $response];
}


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

    
    $devKey = 'dev_otp_' . $phone;
    if (isset($_SESSION[$devKey]) && $_SESSION[$devKey] === $code) {
        unset($_SESSION[$devKey]);
        return ['success' => true];
    }

    return ['success' => false, 'message' => 'کد وارد شده اشتباه یا منقضی شده است'];
}


function normalizePhone(string $phone): string {
    
    $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $en = ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'];
    $phone = str_replace($fa, $en, $phone);

   
    $phone = preg_replace('/\D/', '', $phone);

    
    if (str_starts_with($phone, '98') && strlen($phone) === 12) {
        $phone = '0' . substr($phone, 2);
    }
   
    if (strlen($phone) === 10 && str_starts_with($phone, '9')) {
        $phone = '0' . $phone;
    }

    return $phone;
}
