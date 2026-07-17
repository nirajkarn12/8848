<?php require_once('header.php'); ?>

<?php
if (!isset($_REQUEST['id'])) {
    header('location: staff.php');
    exit;
}

$staffId = (int)$_REQUEST['id'];
$statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE staff_id = ?");
$statement->execute(array($staffId));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header('location: staff.php');
    exit;
}

$full_name = $row['full_name'];
$email = $row['email'];
$phone = $row['phone'];
$address = $row['address'];
$photo = $row['photo'];
$default_commission_type = $row['default_commission_type'];
$default_commission_value = $row['default_commission_value'];
$status = $row['status'];
$designation = $row['designation'] ?? '';
$rating = (int)($row['rating'] ?? 5);
$facebook_url = $row['facebook_url'] ?? '';
$instagram_url = $row['instagram_url'] ?? '';
$show_on_website = (int)($row['show_on_website'] ?? 1);

if (isset($_POST['form1'])) {
    $valid = 1;

    if (empty($_POST['full_name'])) {
        $valid = 0;
        $error_message .= 'Staff name can not be empty<br>';
    }

    if (empty($_POST['email'])) {
        $valid = 0;
        $error_message .= 'Email can not be empty<br>';
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE email = ? AND staff_id != ?");
        $statement->execute(array($_POST['email'], $staffId));
        if ($statement->rowCount()) {
            $valid = 0;
            $error_message .= 'Email already exists<br>';
        }
    }

    if ($valid == 1) {
        if (!empty($_FILES['photo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif'), true)) {
                if ($photo && $photo !== 'user-1.jpg' && file_exists('../assets/uploads/' . $photo)) {
                    @unlink('../assets/uploads/' . $photo);
                }
                $photo = 'staff-' . $staffId . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../assets/uploads/' . $photo);
            }
        }

        $rating = (int)($_POST['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }
        $designation = strip_tags($_POST['designation'] ?? '');
        $facebook_url = strip_tags($_POST['facebook_url'] ?? '');
        $instagram_url = strip_tags($_POST['instagram_url'] ?? '');
        $show_on_website = !empty($_POST['show_on_website']) ? 1 : 0;

        $sql = "UPDATE tbl_staff SET full_name=?, email=?, phone=?, photo=?, address=?, default_commission_type=?, default_commission_value=?, status=?, designation=?, rating=?, facebook_url=?, instagram_url=?, show_on_website=?";
        $params = array(
            strip_tags($_POST['full_name']),
            strip_tags($_POST['email']),
            strip_tags($_POST['phone']),
            $photo,
            strip_tags($_POST['address']),
            $_POST['default_commission_type'],
            (float)$_POST['default_commission_value'],
            $_POST['status'],
            $designation,
            $rating,
            $facebook_url,
            $instagram_url,
            $show_on_website
        );
        if (!empty($_POST['password'])) {
            $sql .= ", password=?";
            $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        $sql .= " WHERE staff_id=?";
        $params[] = $staffId;

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $success_message = 'Staff is updated successfully.';
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $default_commission_type = $_POST['default_commission_type'];
        $default_commission_value = $_POST['default_commission_value'];
        $status = $_POST['status'];
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Staff</h1>
    </div>
    <div class="content-header-right">
        <a href="staff-availability.php?id=<?php echo $staffId; ?>" class="btn btn-warning btn-sm">Availability</a>
        <a href="staff.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($error_message)) { ?>
            <div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
            <?php } ?>
            <?php if (!empty($success_message)) { ?>
            <div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
            <?php } ?>

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Full Name *</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Email *</label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">New Password</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="password">
                                <p class="help-block">Leave blank to keep current password.</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($address); ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Current Photo</label>
                            <div class="col-sm-4">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($photo); ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:50%;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Change Photo</label>
                            <div class="col-sm-4">
                                <input type="file" name="photo">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Designation / Role</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="designation" value="<?php echo htmlspecialchars($designation); ?>" placeholder="e.g. Senior Cleaner">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Website Rating</label>
                            <div class="col-sm-4">
                                <select name="rating" class="form-control">
                                    <?php for ($r = 5; $r >= 1; $r--) { ?>
                                        <option value="<?php echo $r; ?>" <?php echo $rating === $r ? 'selected' : ''; ?>><?php echo $r; ?> star<?php echo $r > 1 ? 's' : ''; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Facebook URL</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="facebook_url" value="<?php echo htmlspecialchars($facebook_url); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Instagram URL</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="instagram_url" value="<?php echo htmlspecialchars($instagram_url); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Show on Website</label>
                            <div class="col-sm-4">
                                <label><input type="checkbox" name="show_on_website" value="1" <?php echo $show_on_website ? 'checked' : ''; ?>> Display in Our Team section</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Default Commission Type</label>
                            <div class="col-sm-4">
                                <select name="default_commission_type" class="form-control">
                                    <option value="percent" <?php echo ($default_commission_type === 'percent') ? 'selected' : ''; ?>>Percentage</option>
                                    <option value="fixed" <?php echo ($default_commission_type === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Default Commission Value</label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01" min="0" class="form-control" name="default_commission_value" value="<?php echo htmlspecialchars($default_commission_value); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-4">
                                <select name="status" class="form-control">
                                    <option value="Active" <?php echo ($status === 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($status === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
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
