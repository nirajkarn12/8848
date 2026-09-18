<?php require_once('header.php'); ?>

<?php
ensureCustomerProfileColumns();

if(isset($_POST['form1'])) {
    $valid = 1;

    if(empty($_POST['cust_name'])) {
        $valid = 0;
        $error_message .= "Customer name can not be empty<br>";
    }

    if(empty($_POST['cust_email'])) {
        $valid = 0;
        $error_message .= "Email can not be empty<br>";
    }

    if(empty($_POST['cust_password'])) {
        $valid = 0;
        $error_message .= "Password can not be empty<br>";
    } elseif(strlen($_POST['cust_password']) < 6) {
        $valid = 0;
        $error_message .= "Password must be at least 6 characters<br>";
    }

    if($valid == 1) {
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email = ?");
        $statement->execute(array($_POST['cust_email']));
        $total = $statement->rowCount();
        if($total) {
            $valid = 0;
            $error_message .= "Email already exists<br>";
        }
    }

    if($valid == 1) {
        $hashed = password_hash($_POST['cust_password'], PASSWORD_DEFAULT);
        $statement = $pdo->prepare("INSERT INTO tbl_customer (cust_name, cust_email, cust_phone, cust_city, cust_state, cust_country, cust_password, cust_token, cust_datetime, cust_timestamp, cust_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->execute(array(
            strip_tags($_POST['cust_name']),
            strip_tags($_POST['cust_email']),
            strip_tags($_POST['cust_phone']),
            strip_tags($_POST['cust_city']),
            strip_tags($_POST['cust_state']),
            strip_tags($_POST['cust_country']),
            $hashed,
            '',
            date('Y-m-d H:i:s'),
            time(),
            1
        ));

        $customerId = (int) $pdo->lastInsertId();
        if (!empty($_FILES['cust_photo']['name'])) {
            $photoResult = adminSaveCustomerPhotoUpload($_FILES['cust_photo'], $customerId, '');
            if ($photoResult['ok']) {
                $pdo->prepare("UPDATE tbl_customer SET cust_photo = ? WHERE cust_id = ?")->execute(array($photoResult['filename'], $customerId));
            } else {
                $error_message .= $photoResult['error'];
            }
        }

        $success_message = 'Customer is added successfully. They can now log in with this email and password.';
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Customer</h1>
    </div>
    <div class="content-header-right">
        <a href="customer.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <?php if($error_message): ?>
            <div class="callout callout-danger">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <div class="box box-info">
                    <div class="box-body">

                        <div class="form-group">
                            <label for="cust_name" class="col-sm-2 control-label">Name *</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_name" id="cust_name" value="<?php echo isset($_POST['cust_name']) ? htmlspecialchars($_POST['cust_name']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_email" class="col-sm-2 control-label">Email *</label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="cust_email" id="cust_email" value="<?php echo isset($_POST['cust_email']) ? htmlspecialchars($_POST['cust_email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_phone" class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_phone" id="cust_phone" value="<?php echo isset($_POST['cust_phone']) ? htmlspecialchars($_POST['cust_phone']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_password" class="col-sm-2 control-label">Login Password *</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="cust_password" id="cust_password" minlength="6" required>
                                <span class="help-block">Required so the customer can log in on the website.</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_city" class="col-sm-2 control-label">City</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_city" id="cust_city" value="<?php echo isset($_POST['cust_city']) ? htmlspecialchars($_POST['cust_city']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_state" class="col-sm-2 control-label">State</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_state" id="cust_state" value="<?php echo isset($_POST['cust_state']) ? htmlspecialchars($_POST['cust_state']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_country" class="col-sm-2 control-label">Country</label>
                            <div class="col-sm-4">
                                <select name="cust_country" id="cust_country" class="form-control">
                                    <option value="">Select Country</option>
                                    <?php
                                    $statement = $pdo->prepare("SELECT * FROM tbl_country ORDER BY country_name ASC");
                                    $statement->execute();
                                    $countries = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($countries as $country) {
                                        $selected = (isset($_POST['cust_country']) && $_POST['cust_country'] == $country['country_id']) ? 'selected' : '';
                                        echo '<option value="' . $country['country_id'] . '" ' . $selected . '>' . $country['country_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_photo" class="col-sm-2 control-label">Profile Picture</label>
                            <div class="col-sm-4">
                                <div class="mb-2">
                                    <img id="customer-add-photo-preview" src="<?php echo htmlspecialchars(adminCustomerProfileImageUrl('')); ?>" alt="Customer preview" style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:1px solid #ddd;">
                                </div>
                                <input type="file" class="form-control" name="cust_photo" id="cust_photo" accept="image/jpeg,image/png,image/webp">
                                <span class="help-block">Optional. JPG, PNG, or WEBP up to 4 MB.</span>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('cust_photo');
    const preview = document.getElementById('customer-add-photo-preview');
    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            preview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
});
</script>

<?php require_once('footer.php'); ?>