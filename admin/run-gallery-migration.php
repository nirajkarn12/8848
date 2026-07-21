<?php
require_once('inc/config.php');

function migrationTableExists($pdo, $table) {
    $statement = $pdo->prepare("SHOW TABLES LIKE ?");
    $statement->execute(array($table));
    return $statement->rowCount() > 0;
}

$messages = array();
$errors = array();

try {
    if (!migrationTableExists($pdo, 'tbl_gallery')) {
        $pdo->exec("
            CREATE TABLE `tbl_gallery` (
              `id` int NOT NULL AUTO_INCREMENT,
              `title` varchar(200) NOT NULL DEFAULT '',
              `content` text,
              `photo` varchar(255) NOT NULL DEFAULT '',
              `status` varchar(20) NOT NULL DEFAULT 'Active',
              `sort_order` int NOT NULL DEFAULT 0,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $messages[] = 'Created tbl_gallery';
    } else {
        $messages[] = 'Skipped tbl_gallery (exists)';
    }

    $count = (int)$pdo->query("SELECT COUNT(*) FROM tbl_gallery")->fetchColumn();
    if ($count === 0 && migrationTableExists($pdo, 'tbl_service')) {
        $old = $pdo->query("SELECT title, content, photo FROM tbl_service ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        if ($old) {
            $insert = $pdo->prepare("INSERT INTO tbl_gallery (title, content, photo, status, sort_order, created_at) VALUES (?, ?, ?, 'Active', ?, NOW())");
            $order = 0;
            foreach ($old as $row) {
                $order++;
                $insert->execute(array(
                    (string)$row['title'],
                    (string)$row['content'],
                    (string)$row['photo'],
                    $order
                ));
            }
            $messages[] = 'Migrated ' . count($old) . ' item(s) from Homepage Service Blocks (tbl_service)';
        } else {
            $messages[] = 'No rows in tbl_service to migrate';
        }
    } elseif ($count > 0) {
        $messages[] = 'Skipped migrate/seed (gallery already has data)';
    } else {
        $messages[] = 'Gallery table is empty — add photos from Gallery admin';
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gallery Migration</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container" style="padding:40px;">
    <h2>Gallery Migration</h2>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
    <?php foreach ($errors as $e) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php } ?>
    <p><a class="btn btn-primary" href="gallery.php">Go to Gallery</a></p>
</body>
</html>
