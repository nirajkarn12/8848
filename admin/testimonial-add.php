<?php require_once('header.php'); ?>

<?php
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
        $final_name = '';
        if ($path !== '') {
            $statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_testimonial'");
            $statement->execute();
            $result = $statement->fetchAll();
            $ai_id = 1;
            foreach ($result as $row) {
                $ai_id = $row[10];
            }
            $final_name = 'testimonial-' . $ai_id . '.' . $ext;
            move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);
        }

        $statement = $pdo->prepare("INSERT INTO tbl_testimonial (name, designation, company, review, rating, photo, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $statement->execute(array(
            trim($_POST['name']),
            trim($_POST['designation'] ?? ''),
            trim($_POST['company'] ?? ''),
            trim($_POST['review']),
            $rating,
            $final_name,
            $_POST['status'] === 'Inactive' ? 'Inactive' : 'Active',
            (int)($_POST['sort_order'] ?? 0),
        ));

        header('location: testimonial.php?success=1');
        exit;
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Testimonial</h1>
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
                                <input type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Designation</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="designation" value="<?php echo isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : ''; ?>" placeholder="Homeowner, Office Manager…">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Company / Area</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="company" value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Review <span>*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="review" style="height:140px;"><?php echo isset($_POST['review']) ? htmlspecialchars($_POST['review']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Rating</label>
                            <div class="col-sm-3">
                                <select name="rating" class="form-control">
                                    <?php for ($r = 5; $r >= 1; $r--) { ?>
                                        <option value="<?php echo $r; ?>" <?php echo ((int)($_POST['rating'] ?? 5) === $r) ? 'selected' : ''; ?>><?php echo $r; ?> star<?php echo $r > 1 ? 's' : ''; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Photo</label>
                            <div class="col-sm-6" style="padding-top:5px;">
                                <input type="file" name="photo"> (optional — jpg/jpeg/png/gif)
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-3">
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Sort Order</label>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" name="sort_order" value="<?php echo isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
