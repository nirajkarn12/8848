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
    if (!migrationTableExists($pdo, 'tbl_staff')) {
        $pdo->exec("
            CREATE TABLE `tbl_staff` (
              `staff_id` int NOT NULL AUTO_INCREMENT,
              `full_name` varchar(100) NOT NULL,
              `email` varchar(255) NOT NULL,
              `phone` varchar(50) NOT NULL DEFAULT '',
              `password` varchar(255) NOT NULL,
              `photo` varchar(255) NOT NULL DEFAULT 'user-1.jpg',
              `address` text,
              `default_commission_type` varchar(20) NOT NULL DEFAULT 'percent',
              `default_commission_value` decimal(10,2) NOT NULL DEFAULT '0.00',
              `status` varchar(20) NOT NULL DEFAULT 'Active',
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`staff_id`),
              UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $messages[] = 'Created tbl_staff';
    } else {
        $messages[] = 'Skipped tbl_staff (exists)';
    }

    if (!migrationTableExists($pdo, 'tbl_booking_assignment')) {
        $pdo->exec("
            CREATE TABLE `tbl_booking_assignment` (
              `assignment_id` int NOT NULL AUTO_INCREMENT,
              `payment_id` varchar(255) NOT NULL,
              `payment_row_id` int NOT NULL DEFAULT 0,
              `staff_id` int NOT NULL,
              `assigned_by` int NOT NULL DEFAULT 0,
              `assigned_at` datetime DEFAULT NULL,
              `job_status` varchar(30) NOT NULL DEFAULT 'Assigned',
              `service_address` text,
              `preferred_date` date DEFAULT NULL,
              `preferred_time` varchar(30) DEFAULT NULL,
              `client_name` varchar(255) NOT NULL DEFAULT '',
              `client_phone` varchar(50) NOT NULL DEFAULT '',
              `client_email` varchar(255) NOT NULL DEFAULT '',
              `service_name` varchar(255) NOT NULL DEFAULT '',
              `service_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
              `commission_type` varchar(20) NOT NULL DEFAULT 'percent',
              `commission_value` decimal(10,2) NOT NULL DEFAULT '0.00',
              `commission_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
              `commission_status` varchar(20) NOT NULL DEFAULT 'pending',
              `staff_notes` text,
              `admin_notes` text,
              `completed_at` datetime DEFAULT NULL,
              PRIMARY KEY (`assignment_id`),
              KEY `idx_payment_id` (`payment_id`),
              KEY `idx_staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $messages[] = 'Created tbl_booking_assignment';
    } else {
        $messages[] = 'Skipped tbl_booking_assignment (exists)';
    }

    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'service_address', 'text NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'preferred_date', 'date NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'preferred_time', 'varchar(30) NULL');
    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'booking_status', "varchar(25) DEFAULT 'Pending'");
    $messages[] = migrationAddColumn($pdo, 'tbl_payment', 'assignment_status', "varchar(25) DEFAULT 'Unassigned'");
    $messages[] = migrationAddColumn($pdo, 'tbl_settings', 'default_staff_commission_type', "varchar(20) DEFAULT 'percent'");
    $messages[] = migrationAddColumn($pdo, 'tbl_settings', 'default_staff_commission_value', "decimal(10,2) DEFAULT '35.00'");
} catch (PDOException $e) {
    $errors[] = $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Staff Phase 1 Migration\n=======================\n\n";
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

try {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM tbl_staff")->fetchColumn();
    if ($count === 0) {
        $hash = password_hash('staff123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO tbl_staff (full_name, email, phone, password, photo, default_commission_type, default_commission_value, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute(array('Demo Staff', 'staff@demo.com', '9800000000', $hash, 'user-1.jpg', 'percent', 35, 'Active'));
        echo "\nDemo staff created: staff@demo.com / staff123\n";
    }
} catch (PDOException $e) {
    echo "\nDemo staff seed skipped: " . $e->getMessage() . "\n";
}
