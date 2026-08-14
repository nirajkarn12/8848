<?php require_once('header.php'); ?>

<?php
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

    if (empty($_POST['password'])) {
        $valid = 0;
        $error_message .= 'Password can not be empty<br>';
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE email = ?");
        $statement->execute(array($_POST['email']));
        if ($statement->rowCount()) {
            $valid = 0;
            $error_message .= 'Email already exists<br>';
        }
    }

    if ($valid == 1) {
        $photo = 'user-1.jpg';
        if (!empty($_FILES['photo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif'), true)) {
                $photo = 'staff-' . time() . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../assets/uploads/' . $photo);
            }
        }

        $rating = (int)($_POST['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }

        $statement = $pdo->prepare("
            INSERT INTO tbl_staff (
                full_name, email, phone, password, photo, address,
                default_commission_type, default_commission_value, status,
                designation, rating, facebook_url, instagram_url, show_on_website, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $statement->execute(array(
            strip_tags($_POST['full_name']),
            strip_tags($_POST['email']),
            strip_tags($_POST['phone']),
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $photo,
            strip_tags($_POST['address']),
            $_POST['default_commission_type'],
            (float)$_POST['default_commission_value'],
            $_POST['status'],
            strip_tags($_POST['designation'] ?? ''),
            $rating,
            strip_tags($_POST['facebook_url'] ?? ''),
            strip_tags($_POST['instagram_url'] ?? ''),
            !empty($_POST['show_on_website']) ? 1 : 0
        ));

        $success_message = 'Staff is added successfully.';
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Staff</h1>
    </div>
    <div class="content-header-right">
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
                                <input type="text" class="form-control" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Email *</label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Password *</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="address" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Photo</label>
                            <div class="col-sm-4">
                                <input type="file" name="photo"> (jpg, jpeg, png, gif)
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Designation / Role</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="designation" value="<?php echo isset($_POST['designation']) ? htmlspecialchars($_POST['designation']) : ''; ?>" placeholder="e.g. Senior Cleaner">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Website Rating</label>
                            <div class="col-sm-4">
                                <select name="rating" class="form-control">
                                    <?php for ($r = 5; $r >= 1; $r--) { ?>
                                        <option value="<?php echo $r; ?>" <?php echo ((int)($_POST['rating'] ?? 5) === $r) ? 'selected' : ''; ?>><?php echo $r; ?> star<?php echo $r > 1 ? 's' : ''; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Facebook URL</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="facebook_url" value="<?php echo isset($_POST['facebook_url']) ? htmlspecialchars($_POST['facebook_url']) : ''; ?>" placeholder="https://facebook.com/...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Instagram URL</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="instagram_url" value="<?php echo isset($_POST['instagram_url']) ? htmlspecialchars($_POST['instagram_url']) : ''; ?>" placeholder="https://instagram.com/...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Show on Website</label>
                            <div class="col-sm-4">
                                <label><input type="checkbox" name="show_on_website" value="1" <?php echo !isset($_POST['form1']) || !empty($_POST['show_on_website']) ? 'checked' : ''; ?>> Display in Our Team section</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Default Commission Type</label>
                            <div class="col-sm-4">
                                <select name="default_commission_type" class="form-control">
                                    <option value="percent" <?php echo (($_POST['default_commission_type'] ?? 'percent') === 'percent') ? 'selected' : ''; ?>>Percentage</option>
                                    <option value="fixed" <?php echo (($_POST['default_commission_type'] ?? '') === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Default Commission Value</label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01" min="0" class="form-control" name="default_commission_value" value="<?php echo htmlspecialchars($_POST['default_commission_value'] ?? '35'); ?>">
                                <p class="help-block">Use % for percentage or fixed amount in NZ$</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Status</label>
                            <div class="col-sm-4">
                                <select name="status" class="form-control">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
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
