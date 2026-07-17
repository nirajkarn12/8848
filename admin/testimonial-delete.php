<?php require_once('header.php'); ?>

<?php
if (!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM tbl_testimonial WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if ($total == 0) {
    header('location: logout.php');
    exit;
}

$photo = $result[0]['photo'];
if ($photo !== '' && file_exists('../assets/uploads/' . $photo)) {
    unlink('../assets/uploads/' . $photo);
}

$statement = $pdo->prepare("DELETE FROM tbl_testimonial WHERE id=?");
$statement->execute(array($_REQUEST['id']));

header('location: testimonial.php?deleted=1');
exit;
