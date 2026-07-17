<?php require_once('header.php'); ?>

<?php
if (!isset($_REQUEST['id'])) {
    header('location: staff.php');
    exit;
}

$staffId = (int)$_REQUEST['id'];
$statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE staff_id = ?");
$statement->execute(array($staffId));
if (!$statement->rowCount()) {
    header('location: staff.php');
    exit;
}

$statement = $pdo->prepare("DELETE FROM tbl_staff WHERE staff_id = ?");
$statement->execute(array($staffId));

header('location: staff.php');
