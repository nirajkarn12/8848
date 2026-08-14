<?php require_once('header.php'); ?>

<?php
// Preventing the direct access of this page.
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {

    // Check if the ID is valid or not
    $statement = $pdo->prepare("SELECT * FROM tbl_contact_inquiry WHERE id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();

    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}
?>

<?php

// Delete contact inquiry
$statement = $pdo->prepare("DELETE FROM tbl_contact_inquiry WHERE id=?");
$statement->execute(array($_REQUEST['id']));

// Redirect back to contact inquiry list
header('location: contact-inquiry.php');
exit;

?>