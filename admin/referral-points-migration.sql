ALTER TABLE tbl_referral_settings ADD COLUMN IF NOT EXISTS bonus_points INT UNSIGNED NOT NULL DEFAULT 100 AFTER discount_value;
ALTER TABLE tbl_referral_settings ADD COLUMN IF NOT EXISTS points_per_dollar INT UNSIGNED NOT NULL DEFAULT 100 AFTER bonus_points;
ALTER TABLE tbl_referral ADD COLUMN IF NOT EXISTS awarded_points INT UNSIGNED NOT NULL DEFAULT 0 AFTER discount_amount;
CREATE TABLE IF NOT EXISTS tbl_referral_points (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id INT UNSIGNED NOT NULL,
    referral_id INT UNSIGNED NULL,
    points INT NOT NULL DEFAULT 0,
    reason VARCHAR(255) NOT NULL DEFAULT 'Successful referral',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_referral_points_referral (referral_id),
    KEY idx_referral_points_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE tbl_referral_points MODIFY COLUMN referral_id INT UNSIGNED NULL;
ALTER TABLE tbl_referral_points MODIFY COLUMN points INT NOT NULL DEFAULT 0;