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
foreach ($result as $row) {
    $name = $row['name'];
    $logo = $row['logo'];
    $website_url = $row['website_url'];
    $status = $row['status'];
    $sort_order = (int)$row['sort_order'];
}

if (isset($_POST['form1'])) {
    $valid = 1;
    $path = $_FILES['logo']['name'] ?? '';
    $path_tmp = $_FILES['logo']['tmp_name'] ?? '';
    $ext = '';

    if ($path !== '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'), true)) {
            $valid = 0;
            $error_message .= 'Logo must be jpg, jpeg, png, gif, webp or svg<br>';
        }
    }

    if ($valid == 1) {
        $final_name = $logo;
        if ($path !== '') {
            if ($logo !== '' && file_exists('../assets/uploads/' . $logo)) {
                unlink('../assets/uploads/' . $logo);
            }
            $final_name = 'client-' . (int)$_REQUEST['id'] . '.' . $ext;
            move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);
        }

        $statement = $pdo->prepare("UPDATE tbl_client SET name=?, logo=?, website_url=?, status=?, sort_order=? WHERE id=?");
        $statement->execute(array(
            trim($_POST['name'] ?? ''),
            $final_name,
            trim($_POST['website_url'] ?? ''),
            ($_POST['status'] ?? '') === 'Inactive' ? 'Inactive' : 'Active',
            (int)($_POST['sort_order'] ?? 0),
            (int)$_REQUEST['id'],
        ));

        header('location: client.php?updated=1');
        exit;
    }

    $name = trim($_POST['name'] ?? $name);
    $website_url = trim($_POST['website_url'] ?? $website_url);
    $status = $_POST['status'] ?? $status;
    $sort_order = (int)($_POST['sort_order'] ?? $sort_order);
}
?>

<section class="content-header">
    <div class="content-header-left"><h1>Edit Client Logo</h1></div>
    <div class="content-header-right"><a href="client.php" class="btn btn-primary btn-sm">View All</a></div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($error_message): ?>
            <div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
            <?php endif; ?>
            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Current Logo</label>
                            <div class="col-sm-6" style="padding-top:5px;">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($logo); ?>" style="max-width:160px;max-height:70px;object-fit:contain;background:#f5f5f5;padding:8px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Change Logo</label>
                            <div class="col-sm-6" style="padding-top:5px;">
                                <input type="file" name="logo"> (optional)
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Website URL</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="website_url" value="<?php echo htmlspecialchars($website_url); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-3">
                                <select name="status" class="form-control">
                                    <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo $status === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Sort Order</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" name="sort_order" value="<?php echo (int)$sort_order; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
