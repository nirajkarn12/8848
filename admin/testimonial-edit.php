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
foreach ($result as $row) {
    $name = $row['name'];
    $designation = $row['designation'];
    $company = $row['company'];
    $review = $row['review'];
    $rating = (int)$row['rating'];
    $photo = $row['photo'];
    $status = $row['status'];
    $sort_order = (int)$row['sort_order'];
}

if (isset($_POST['form1'])) {
    $valid = 1;

    if (empty($_POST['name'])) {
        $valid = 0;
        $error_message .= 'Name can not be empty<br>';
    }
    if (empty($_POST['review'])) {
        $valid = 0;
        $error_message .= 'Review can not be empty<br>';
    }

    $rating = (int)($_POST['rating'] ?? 5);
    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }

    $path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';
    $ext = '';
    if ($path !== '') {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'), true)) {
            $valid = 0;
            $error_message .= 'Photo must be jpg, jpeg, gif or png<br>';
        }
    }

    if ($valid == 1) {
        $final_name = $photo;
        if ($path !== '') {
            if ($photo !== '' && file_exists('../assets/uploads/' . $photo)) {
                unlink('../assets/uploads/' . $photo);
            }
            $final_name = 'testimonial-' . (int)$_REQUEST['id'] . '.' . $ext;
            move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);
        }

        $statement = $pdo->prepare("UPDATE tbl_testimonial SET name=?, designation=?, company=?, review=?, rating=?, photo=?, status=?, sort_order=? WHERE id=?");
        $statement->execute(array(
            trim($_POST['name']),
            trim($_POST['designation'] ?? ''),
            trim($_POST['company'] ?? ''),
            trim($_POST['review']),
            $rating,
            $final_name,
            $_POST['status'] === 'Inactive' ? 'Inactive' : 'Active',
            (int)($_POST['sort_order'] ?? 0),
            (int)$_REQUEST['id'],
        ));

        header('location: testimonial.php?updated=1');
        exit;
    }

    $name = trim($_POST['name'] ?? $name);
    $designation = trim($_POST['designation'] ?? $designation);
    $company = trim($_POST['company'] ?? $company);
    $review = trim($_POST['review'] ?? $review);
    $status = $_POST['status'] ?? $status;
    $sort_order = (int)($_POST['sort_order'] ?? $sort_order);
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Testimonial</h1>
    </div>
    <div class="content-header-right">
        <a href="testimonial.php" class="btn btn-primary btn-sm">View All</a>
    </div>
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
                            <label class="col-sm-2 control-label">Name <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Designation</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="designation" value="<?php echo htmlspecialchars($designation); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Company / Area</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="company" value="<?php echo htmlspecialchars($company); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Review <span>*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="review" style="height:140px;"><?php echo htmlspecialchars($review); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Rating</label>
                            <div class="col-sm-3">
                                <select name="rating" class="form-control">
                                    <?php for ($r = 5; $r >= 1; $r--) { ?>
                                        <option value="<?php echo $r; ?>" <?php echo $rating === $r ? 'selected' : ''; ?>><?php echo $r; ?> star<?php echo $r > 1 ? 's' : ''; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Existing Photo</label>
                            <div class="col-sm-6" style="padding-top:5px;">
                                <?php if ($photo !== '') { ?>
                                    <img src="../assets/uploads/<?php echo htmlspecialchars($photo); ?>" style="width:90px;height:90px;object-fit:cover;border-radius:50%;">
                                <?php } else { ?>
                                    <span class="text-muted">No photo</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Change Photo</label>
                            <div class="col-sm-6" style="padding-top:5px;">
                                <input type="file" name="photo"> (optional)
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
