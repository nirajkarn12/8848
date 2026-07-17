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

try {
    $messages[] = migrationAddColumn($pdo, 'tbl_settings', 'marquee_on_off', "tinyint NOT NULL DEFAULT 1");
    $messages[] = migrationAddColumn($pdo, 'tbl_settings', 'marquee_notices', "text NULL");
} catch (PDOException $e) {
    $messages[] = 'Error: ' . $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Marquee notices migration\n=========================\n\n";
foreach ($messages as $message) {
    echo $message . "\n";
}
echo "\nDone.\n";
