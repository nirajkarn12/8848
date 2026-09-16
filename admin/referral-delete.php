<?php
require_once 'header.php';
ensureReferralTables();
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $statement = $pdo->prepare("UPDATE tbl_referral SET status = 'Cancelled' WHERE id = ? AND status = 'Pending'");
    $statement->execute([$id]);
}
header('Location: referral.php');
exit;
