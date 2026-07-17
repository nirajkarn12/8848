<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireStaffLogin();

$error_message = '';
$success_message = '';
$staffId = (int)$_SESSION['staff']['staff_id'];

$statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE staff_id = ? LIMIT 1");
$statement->execute(array($staffId));
$row = $statement->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header('Location: ' . STAFF_URL . 'logout.php');
    exit;
}

$full_name = $row['full_name'];
$email = $row['email'];
$phone = $row['phone'];
$address = $row['address'];
$photo = $row['photo'] ?: 'user-1.jpg';

// Update profile details
if (isset($_POST['form1'])) {
    $valid = 1;
    $newName = trim(strip_tags($_POST['full_name'] ?? ''));
    $newEmail = trim(strip_tags($_POST['email'] ?? ''));
    $newPhone = trim(strip_tags($_POST['phone'] ?? ''));
    $newAddress = trim(strip_tags($_POST['address'] ?? ''));

    if ($newName === '') {
        $valid = 0;
        $error_message .= 'Name can not be empty<br>';
    }

    if ($newEmail === '') {
        $valid = 0;
        $error_message .= 'Email can not be empty<br>';
    } elseif (filter_var($newEmail, FILTER_VALIDATE_EMAIL) === false) {
        $valid = 0;
        $error_message .= 'Email address must be valid<br>';
    } else {
        $check = $pdo->prepare("SELECT staff_id FROM tbl_staff WHERE email = ? AND staff_id != ? LIMIT 1");
        $check->execute(array($newEmail, $staffId));
        if ($check->fetch()) {
            $valid = 0;
            $error_message .= 'Email address already exists<br>';
        }
    }

    if ($valid == 1) {
        $statement = $pdo->prepare("UPDATE tbl_staff SET full_name=?, email=?, phone=?, address=? WHERE staff_id=?");
        $statement->execute(array($newName, $newEmail, $newPhone, $newAddress, $staffId));

        $full_name = $newName;
        $email = $newEmail;
        $phone = $newPhone;
        $address = $newAddress;

        $_SESSION['staff']['full_name'] = $newName;
        $_SESSION['staff']['email'] = $newEmail;
        $_SESSION['staff']['phone'] = $newPhone;
        $_SESSION['staff']['address'] = $newAddress;

        $success_message = 'Profile information updated successfully.';
    }
}

// Update photo
if (isset($_POST['form2'])) {
    $valid = 1;
    $path = $_FILES['photo']['name'] ?? '';
    $path_tmp = $_FILES['photo']['tmp_name'] ?? '';

    if ($path === '') {
        $valid = 0;
        $error_message .= 'Please choose a photo to upload<br>';
    } else {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'), true)) {
            $valid = 0;
            $error_message .= 'You must upload a jpg, jpeg, gif or png file<br>';
        }
    }

    if ($valid == 1) {
        if ($photo && $photo !== 'user-1.jpg' && file_exists(__DIR__ . '/../assets/uploads/' . $photo)) {
            @unlink(__DIR__ . '/../assets/uploads/' . $photo);
        }

        $final_name = 'staff-' . $staffId . '.' . $ext;
        move_uploaded_file($path_tmp, __DIR__ . '/../assets/uploads/' . $final_name);

        $statement = $pdo->prepare("UPDATE tbl_staff SET photo=? WHERE staff_id=?");
        $statement->execute(array($final_name, $staffId));

        $photo = $final_name;
        $_SESSION['staff']['photo'] = $final_name;
        $success_message = 'Profile photo updated successfully.';
    }
}

// Update password
if (isset($_POST['form3'])) {
    $valid = 1;
    $password = $_POST['password'] ?? '';
    $rePassword = $_POST['re_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    if ($currentPassword === '') {
        $valid = 0;
        $error_message .= 'Current password is required<br>';
    } elseif (!password_verify($currentPassword, $row['password'])) {
        $valid = 0;
        $error_message .= 'Current password is incorrect<br>';
    }

    if ($password === '' || $rePassword === '') {
        $valid = 0;
        $error_message .= 'New password can not be empty<br>';
    } elseif ($password !== $rePassword) {
        $valid = 0;
        $error_message .= 'New passwords do not match<br>';
    } elseif (strlen($password) < 6) {
        $valid = 0;
        $error_message .= 'New password must be at least 6 characters<br>';
    }

    if ($valid == 1) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $statement = $pdo->prepare("UPDATE tbl_staff SET password=? WHERE staff_id=?");
        $statement->execute(array($hash, $staffId));
        $success_message = 'Password updated successfully.';
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/inc/header.php';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Profile</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if ($error_message !== '') { ?>
			<div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
			<?php } ?>
			<?php if ($success_message !== '') { ?>
			<div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
			<?php } ?>

			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#tab_1" data-toggle="tab">Update Information</a></li>
					<li><a href="#tab_2" data-toggle="tab">Update Photo</a></li>
					<li><a href="#tab_3" data-toggle="tab">Update Password</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="tab_1">
						<form class="form-horizontal" action="" method="post">
							<div class="box box-info" style="border-top:0;box-shadow:none;margin:0;">
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
										<label class="col-sm-2 control-label">Address</label>
										<div class="col-sm-6">
											<textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label"></label>
										<div class="col-sm-6">
											<button type="submit" class="btn btn-success pull-left" name="form1">Update Information</button>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>

					<div class="tab-pane" id="tab_2">
						<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
							<div class="box box-info" style="border-top:0;box-shadow:none;margin:0;">
								<div class="box-body">
									<div class="form-group">
										<label class="col-sm-2 control-label">Existing Photo</label>
										<div class="col-sm-6" style="padding-top:6px;">
											<img src="<?php echo htmlspecialchars(staffPhotoUrl($photo)); ?>" class="existing-photo" style="width:120px;height:120px;object-fit:cover;border-radius:4px;">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">New Photo</label>
										<div class="col-sm-4" style="padding-top:6px;">
											<input type="file" name="photo">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label"></label>
										<div class="col-sm-6">
											<button type="submit" class="btn btn-success pull-left" name="form2">Update Photo</button>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>

					<div class="tab-pane" id="tab_3">
						<form class="form-horizontal" action="" method="post">
							<div class="box box-info" style="border-top:0;box-shadow:none;margin:0;">
								<div class="box-body">
									<div class="form-group">
										<label class="col-sm-2 control-label">Current Password *</label>
										<div class="col-sm-4">
											<input type="password" class="form-control" name="current_password">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">New Password *</label>
										<div class="col-sm-4">
											<input type="password" class="form-control" name="password">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Retype Password *</label>
										<div class="col-sm-4">
											<input type="password" class="form-control" name="re_password">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label"></label>
										<div class="col-sm-6">
											<button type="submit" class="btn btn-success pull-left" name="form3">Update Password</button>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php include __DIR__ . '/inc/footer.php'; ?>
