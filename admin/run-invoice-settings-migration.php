<?php
require_once __DIR__ . '/inc/config.php';

function columnExists(PDO $pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch();
}

$messages = [];

try {
    $adds = [
        'invoice_vat_no' => "varchar(100) NOT NULL DEFAULT ''",
        'invoice_due_days' => "int NOT NULL DEFAULT 30",
        'invoice_footer_note' => "text NULL",
    ];
    foreach ($adds as $column => $definition) {
        if (!columnExists($pdo, 'tbl_settings', $column)) {
            $pdo->exec("ALTER TABLE tbl_settings ADD COLUMN `{$column}` {$definition}");
            $messages[] = "Added tbl_settings.{$column}";
        } else {
            $messages[] = "Skipped tbl_settings.{$column} (exists)";
        }
    }

    $pdo->exec("UPDATE tbl_settings SET invoice_footer_note = 'Thank you for choosing our cleaning service. We appreciate your trust.' WHERE id = 1 AND (invoice_footer_note IS NULL OR invoice_footer_note = '')");
    $messages[] = 'Default invoice footer note ensured';
} catch (Throwable $e) {
    $messages[] = 'Error: ' . $e->getMessage();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Invoice Settings Migration\n===========================\n\n";
foreach ($messages as $message) {
    echo $message . "\n";
}
echo "\nDone.\n";
