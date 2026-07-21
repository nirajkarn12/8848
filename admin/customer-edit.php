<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id = ?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if($total == 0) {
        header('location: logout.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id = ?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
    $cust_name = $row['cust_name'];
    $cust_email = $row['cust_email'];
    $cust_phone = $row['cust_phone'];
    $cust_city = $row['cust_city'];
    $cust_state = $row['cust_state'];
    $cust_country = $row['cust_country'];
}

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

    if($valid == 1) {
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email = ? AND cust_id != ?");
        $statement->execute(array($_POST['cust_email'], $_REQUEST['id']));
        $total = $statement->rowCount();
        if($total) {
            $valid = 0;
            $error_message .= "Email already exists<br>";
        }
    }

    if($valid == 1 && !empty($_POST['cust_password']) && strlen($_POST['cust_password']) < 6) {
        $valid = 0;
        $error_message .= "Password must be at least 6 characters<br>";
    }

    if($valid == 1) {
        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_name = ?, cust_email = ?, cust_phone = ?, cust_city = ?, cust_state = ?, cust_country = ? WHERE cust_id = ?");
        $statement->execute(array(
            strip_tags($_POST['cust_name']),
            strip_tags($_POST['cust_email']),
            strip_tags($_POST['cust_phone']),
            strip_tags($_POST['cust_city']),
            strip_tags($_POST['cust_state']),
            strip_tags($_POST['cust_country']),
            $_REQUEST['id']
        ));

        if (!empty($_POST['cust_password'])) {
            $hashed = password_hash($_POST['cust_password'], PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE tbl_customer SET cust_password = ?, cust_token = '' WHERE cust_id = ?")
                ->execute(array($hashed, $_REQUEST['id']));
        }

        $success_message = 'Customer is updated successfully.';

        $cust_name = $_POST['cust_name'];
        $cust_email = $_POST['cust_email'];
        $cust_phone = $_POST['cust_phone'];
        $cust_city = $_POST['cust_city'];
        $cust_state = $_POST['cust_state'];
        $cust_country = $_POST['cust_country'];
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Customer</h1>
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

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">

                        <div class="form-group">
                            <label for="cust_name" class="col-sm-2 control-label">Name *</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_name" id="cust_name" value="<?php echo isset($cust_name) ? htmlspecialchars($cust_name) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_email" class="col-sm-2 control-label">Email *</label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" name="cust_email" id="cust_email" value="<?php echo isset($cust_email) ? htmlspecialchars($cust_email) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_phone" class="col-sm-2 control-label">Phone</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_phone" id="cust_phone" value="<?php echo isset($cust_phone) ? htmlspecialchars($cust_phone) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_password" class="col-sm-2 control-label">New Login Password</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" name="cust_password" id="cust_password" minlength="6">
                                <span class="help-block">Leave blank to keep the current password. Set this if the customer cannot log in.</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_city" class="col-sm-2 control-label">City</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_city" id="cust_city" value="<?php echo isset($cust_city) ? htmlspecialchars($cust_city) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cust_state" class="col-sm-2 control-label">State</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="cust_state" id="cust_state" value="<?php echo isset($cust_state) ? htmlspecialchars($cust_state) : ''; ?>">
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
                                        $selected = (isset($cust_country) && $cust_country == $country['country_id']) ? 'selected' : '';
                                        echo '<option value="' . $country['country_id'] . '" ' . $selected . '>' . $country['country_name'] . '</option>';
                                    }
                                    ?>
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