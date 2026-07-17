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
    if (!migrationTableExists($pdo, 'tbl_client')) {
        $pdo->exec("
            CREATE TABLE `tbl_client` (
              `id` int NOT NULL AUTO_INCREMENT,
              `name` varchar(150) NOT NULL DEFAULT '',
              `logo` varchar(255) NOT NULL,
              `website_url` varchar(255) NOT NULL DEFAULT '',
              `status` varchar(20) NOT NULL DEFAULT 'Active',
              `sort_order` int NOT NULL DEFAULT 0,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $messages[] = 'Created tbl_client';
    } else {
        $messages[] = 'Skipped tbl_client (exists)';
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Clients Migration</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container" style="padding:40px;">
    <h2>Clients Logo Migration</h2>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
    <?php foreach ($errors as $e) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php } ?>
    <p><a class="btn btn-primary" href="client.php">Go to Clients</a></p>
</body>
</html>
