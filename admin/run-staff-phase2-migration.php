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
    $messages[] = migrationAddColumn($pdo, 'tbl_product', 'staff_commission_type', "varchar(20) NOT NULL DEFAULT 'inherit'");
    $messages[] = migrationAddColumn($pdo, 'tbl_product', 'staff_commission_value', "decimal(10,2) NOT NULL DEFAULT '0.00'");
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'approved_at', 'datetime DEFAULT NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'paid_at', 'datetime DEFAULT NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_settings', 'default_staff_commission_type', "varchar(20) DEFAULT 'percent'");
    $messages[] = migrationAddColumn($pdo, 'tbl_settings', 'default_staff_commission_value', "decimal(10,2) DEFAULT '35.00'");
} catch (PDOException $e) {
    $errors[] = $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Staff Phase 2 Migration\n=======================\n\n";
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
