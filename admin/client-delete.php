<?php require_once('header.php'); ?>

<?php
if (!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM tbl_client WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if ($total == 0) {
    header('location: logout.php');
    exit;
}

$logo = $result[0]['logo'];
if ($logo !== '' && file_exists('../assets/uploads/' . $logo)) {
    unlink('../assets/uploads/' . $logo);
}

$statement = $pdo->prepare("DELETE FROM tbl_client WHERE id=?");
$statement->execute(array($_REQUEST['id']));

header('location: client.php?deleted=1');
exit;
