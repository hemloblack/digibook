-- ============================================
-- جداول احراز هویت - دیجی بوک
-- این دستورات را در phpMyAdmin روی bookstore_db اجرا کنید
-- ============================================

-- جدول کاربران
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `phone`      VARCHAR(15)  NOT NULL UNIQUE,
  `name`       VARCHAR(100) NOT NULL,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('user','admin') NOT NULL DEFAULT 'user',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- جدول کدهای OTP
CREATE TABLE IF NOT EXISTS `otp_codes` (
  `id`         INT(11)     NOT NULL AUTO_INCREMENT,
  `phone`      VARCHAR(15) NOT NULL,
  `code`       VARCHAR(6)  NOT NULL,
  `expires_at` DATETIME    NOT NULL,
  `used`       TINYINT(1)  NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- ادمین پیش‌فرض (رمز: admin1234)
-- بعد از اجرا می‌توانید رمز را از پنل تغییر دهید
-- ============================================
INSERT INTO `users` (`phone`, `name`, `username`, `password`, `role`) VALUES
('09000000000', 'مدیر سایت', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- رمز بالا: password  — حتماً تغییر دهید!
