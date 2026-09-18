<?php
require_once 'header.php';

if (!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

$statement = $pdo->prepare('DELETE FROM tbl_promo_code WHERE id = ?');
$statement->execute([(int) $_REQUEST['id']]);

header('location: promo-code.php');
exit;
