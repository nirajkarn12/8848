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
    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'service_lat', 'DECIMAL(10,7) NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'service_lng', 'DECIMAL(10,7) NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'service_lat', 'DECIMAL(10,7) NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'service_lng', 'DECIMAL(10,7) NULL');
} catch (PDOException $e) {
    $errors[] = $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Service Location Migration\n==========================\n\n";
foreach ($messages as $message) {
    echo $message . "\n";
}
if ($errors) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
} else {
    echo "\nMigration completed.\n";
}
