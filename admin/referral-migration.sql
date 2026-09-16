CREATE TABLE IF NOT EXISTS tbl_referral_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 10.00,
    minimum_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    terms TEXT NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tbl_referral_settings (id, is_active, discount_type, discount_value, minimum_order_amount, terms, updated_at)
SELECT 1, 1, 'percent', 10.00, 0.00, 'Referral discount applies to the referred customer\'s first eligible booking only.', NOW()
WHERE NOT EXISTS (SELECT 1 FROM tbl_referral_settings WHERE id = 1);

CREATE TABLE IF NOT EXISTS tbl_referral (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    referral_code VARCHAR(32) NOT NULL,
    referrer_customer_id INT UNSIGNED NOT NULL,
    referrer_name VARCHAR(255) NOT NULL,
    referrer_email VARCHAR(255) NOT NULL,
    referee_name VARCHAR(255) NOT NULL,
    referee_email VARCHAR(255) NOT NULL,
    referee_phone VARCHAR(50) NOT NULL,
    status ENUM('Pending','Converted','Cancelled') NOT NULL DEFAULT 'Pending',
    discount_type ENUM('percent','amount') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    referee_customer_id INT UNSIGNED NULL,
    payment_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    converted_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_referral_code (referral_code),
    KEY idx_referral_referrer (referrer_customer_id),
    KEY idx_referral_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;