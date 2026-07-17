<?php
require_once('inc/config.php');

function migrationColumnExists($pdo, $table, $column) {
    $statement = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
    $statement->execute(array($column));
    return $statement->rowCount() > 0;
}

function migrationAddColumn($pdo, $table, $column, $definition) {
    if (!migrationColumnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `" . $table . "` ADD COLUMN `" . $column . "` " . $definition);
        return 'Added ' . $table . '.' . $column;
    }
    return 'Skipped ' . $table . '.' . $column . ' (exists)';
}

$messages = array();
$errors = array();

try {
    $messages[] = migrationAddColumn($pdo, 'tbl_staff', 'designation', "varchar(150) NOT NULL DEFAULT ''");
    $messages[] = migrationAddColumn($pdo, 'tbl_staff', 'rating', "tinyint NOT NULL DEFAULT 5");
    $messages[] = migrationAddColumn($pdo, 'tbl_staff', 'facebook_url', "varchar(255) NOT NULL DEFAULT ''");
    $messages[] = migrationAddColumn($pdo, 'tbl_staff', 'instagram_url', "varchar(255) NOT NULL DEFAULT ''");
    $messages[] = migrationAddColumn($pdo, 'tbl_staff', 'show_on_website', "tinyint NOT NULL DEFAULT 1");
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Team Fields Migration</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="container" style="padding:40px;">
    <h2>Staff Team (Website) Fields Migration</h2>
    <?php foreach ($messages as $m) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php } ?>
    <?php foreach ($errors as $e) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php } ?>
    <p><a class="btn btn-primary" href="staff.php">Go to Staff</a></p>
</body>
</html>
