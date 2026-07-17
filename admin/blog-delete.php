<?php require_once('header.php'); ?>

<?php
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $statement = $pdo->prepare("SELECT photo FROM tbl_post WHERE post_id=?");
    $statement->execute(array($id));
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['photo'])) {
        $photo = $result['photo'];
        if (file_exists('../assets/uploads/' . $photo)) {
            unlink('../assets/uploads/' . $photo);
        }
    }

    $statement = $pdo->prepare("DELETE FROM tbl_post WHERE post_id=?");
    $statement->execute(array($id));

    header('location: blog.php?deleted=1');
    exit;
}

header('location: blog.php');
exit;
?>
