<?php
require_once __DIR__ . '/inc/config.php';

function columnExists(PDO $pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch();
}

$messages = [];

try {
    if (!columnExists($pdo, 'tbl_settings', 'site_name')) {
        $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN site_name varchar(255) NOT NULL DEFAULT '8848 Cleaning Service' AFTER logo");
        $messages[] = 'Added column tbl_settings.site_name';
    } else {
        $messages[] = 'Column tbl_settings.site_name already exists';
    }

    $pdo->prepare("UPDATE tbl_settings SET site_name = ? WHERE id = 1 AND (site_name = '' OR site_name IS NULL OR site_name LIKE '%Koshi%')")
        ->execute(['8848 Cleaning Service']);
    $messages[] = 'Updated site_name to 8848 Cleaning Service where needed';

    $current = $pdo->query("SELECT site_name, footer_copyright FROM tbl_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $messages[] = 'Current site_name: ' . ($current['site_name'] ?? '(empty)');
    $messages[] = 'Current footer_copyright: ' . ($current['footer_copyright'] ?? '(empty)');
} catch (Throwable $e) {
    $messages[] = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Site name migration</title></head>
<body>
<h1>Site name migration</h1>
<ul>
<?php foreach ($messages as $msg) { echo '<li>' . htmlspecialchars($msg) . '</li>'; } ?>
</ul>
<p><a href="settings.php">Go to Settings</a></p>
</body>
</html>
