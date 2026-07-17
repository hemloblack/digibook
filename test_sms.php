<?php
/**
 * test_sms.php — تست ارسال پیامک
 * بعد از تست حتماً این فایل رو حذف کنید!
 */

include 'includes/otp_helper.php';

// شماره موبایل خودت رو اینجا بذار برای تست
$testPhone = '09135185409';  // ← شماره خودت

$result = sendSmsIr($testPhone, '123456');

echo '<pre style="font-family:tahoma; direction:rtl; padding:20px;">';
if ($result['success']) {
    echo "✅ پیامک با موفقیت ارسال شد!\n";
    echo "کد تست: 123456\n";
} else {
    echo "❌ خطا در ارسال:\n";
    print_r($result);
}
echo '</pre>';

echo '<p style="color:red; font-family:tahoma">⚠️ بعد از تست این فایل رو حذف کنید!</p>';
