<?php require_once('header.php'); ?>

<?php
if (!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
}

$statement = $pdo->prepare("SELECT * FROM tbl_gallery WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
if ($total == 0) {
	header('location: logout.php');
	exit;
}

$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$photo = '';
foreach ($result as $row) {
	$photo = $row['photo'];
}

if ($photo !== '' && file_exists('../assets/uploads/' . $photo)) {
	@unlink('../assets/uploads/' . $photo);
}

$statement = $pdo->prepare("DELETE FROM tbl_gallery WHERE id=?");
$statement->execute(array($_REQUEST['id']));

header('location: gallery.php?deleted=1');
exit;
?>
