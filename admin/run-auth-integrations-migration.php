<?php
require_once __DIR__ . '/inc/config.php';

$messages = [];

try {
    $custCol = $pdo->query("SHOW COLUMNS FROM `tbl_customer` LIKE 'cust_google_id'");
    if ($custCol && $custCol->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `tbl_customer` ADD COLUMN `cust_google_id` varchar(64) NOT NULL DEFAULT '' AFTER `cust_email`");
        $messages[] = 'Added tbl_customer.cust_google_id';
    } else {
        $messages[] = 'tbl_customer.cust_google_id already exists';
    }

    $settingCols = [
        'google_client_id' => "varchar(255) NOT NULL DEFAULT ''",
        'google_client_secret' => "varchar(255) NOT NULL DEFAULT ''",
        'recaptcha_site_key' => "varchar(255) NOT NULL DEFAULT ''",
        'recaptcha_secret_key' => "varchar(255) NOT NULL DEFAULT ''",
    ];
    foreach ($settingCols as $column => $definition) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `tbl_settings` LIKE " . $pdo->quote($column));
        if ($stmt && $stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `tbl_settings` ADD COLUMN `{$column}` {$definition}");
            $messages[] = "Added tbl_settings.{$column}";
        } else {
            $messages[] = "tbl_settings.{$column} already exists";
        }
    }
} catch (Throwable $e) {
    $messages[] = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Auth Integrations Migration</title></head>
<body style="font-family:sans-serif;padding:40px;">
<h1>Google Login &amp; reCAPTCHA Migration</h1>
<ul>
<?php foreach ($messages as $msg): ?>
    <li><?php echo htmlspecialchars($msg); ?></li>
<?php endforeach; ?>
</ul>
<p><a href="settings.php">Go to Settings → Google &amp; reCAPTCHA</a></p>
</body>
</html>
