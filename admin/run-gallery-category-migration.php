<?php
require_once('inc/config.php');

$messages = array();
$errors = array();

try {
    $cols = $pdo->query("SHOW COLUMNS FROM tbl_gallery LIKE 'mcat_id'")->fetchAll();
    if (!$cols) {
        $pdo->exec("ALTER TABLE `tbl_gallery` ADD COLUMN `mcat_id` int NOT NULL DEFAULT 0 AFTER `photo`");
        $messages[] = 'Added mcat_id column to tbl_gallery';
    } else {
        $messages[] = 'Skipped mcat_id (already exists)';
    }

    // If any mid category exists and gallery rows are uncategorized, leave them at 0 (All / Uncategorized)
    $midCount = (int)$pdo->query("SELECT COUNT(*) FROM tbl_mid_category")->fetchColumn();
    $messages[] = 'Mid categories available: ' . $midCount;
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gallery Category Migration</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container" style="padding:40px;">
    <h2>Gallery Category Migration</h2>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
    <?php foreach ($errors as $e) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php } ?>
    <p><a class="btn btn-primary" href="gallery.php">Go to Gallery</a></p>
</body>
</html>
