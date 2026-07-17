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

function migrationTableExists($pdo, $table) {
    $statement = $pdo->prepare("SHOW TABLES LIKE ?");
    $statement->execute(array($table));
    return $statement->rowCount() > 0;
}

$messages = array();
$errors = array();

try {
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'arrived_at', 'datetime DEFAULT NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'checkin_lat', 'decimal(10,7) DEFAULT NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'checkin_lng', 'decimal(10,7) DEFAULT NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_booking_assignment', 'commission_share_percent', "decimal(5,2) NOT NULL DEFAULT '100.00'");

    if (!migrationTableExists($pdo, 'tbl_staff_availability')) {
        $pdo->exec("
            CREATE TABLE `tbl_staff_availability` (
              `availability_id` int NOT NULL AUTO_INCREMENT,
              `staff_id` int NOT NULL,
              `day_of_week` tinyint NOT NULL,
              `start_time` time NOT NULL DEFAULT '08:00:00',
              `end_time` time NOT NULL DEFAULT '18:00:00',
              `is_available` tinyint NOT NULL DEFAULT 1,
              PRIMARY KEY (`availability_id`),
              KEY `idx_staff_day` (`staff_id`, `day_of_week`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $messages[] = 'Created tbl_staff_availability';
    } else {
        $messages[] = 'Skipped tbl_staff_availability (exists)';
    }

    if (!migrationTableExists($pdo, 'tbl_staff_auto_assign')) {
        $pdo->exec("
            CREATE TABLE `tbl_staff_auto_assign` (
              `id` int NOT NULL DEFAULT 1,
              `last_staff_id` int NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("INSERT INTO `tbl_staff_auto_assign` (`id`, `last_staff_id`) VALUES (1, 0)");
        $messages[] = 'Created tbl_staff_auto_assign';
    } else {
        $messages[] = 'Skipped tbl_staff_auto_assign (exists)';
    }
} catch (PDOException $e) {
    $errors[] = $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Staff Phase 4 Migration\n=======================\n\n";
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
