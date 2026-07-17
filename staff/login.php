<?php
require_once __DIR__ . '/inc/bootstrap.php';

$error_message = '';

if (isset($_POST['form1'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error_message = 'Email and/or Password can not be empty<br>';
    } else {
        try {
            $statement = $pdo->prepare("SELECT * FROM tbl_staff WHERE email = ? AND status = 'Active' LIMIT 1");
            $statement->execute(array($email));
            $staff = $statement->fetch(PDO::FETCH_ASSOC);

            if ($staff && password_verify($password, $staff['password'])) {
                unset($staff['password']);
                $_SESSION['staff'] = $staff;
                header('Location: ' . STAFF_URL . 'index.php');
                exit;
            }

            $error_message = 'Invalid login credentials.<br>';
        } catch (PDOException $e) {
            $error_message = 'Staff system is not ready. Please ask admin to run migration.<br>';
        }
    }
}

if (staffIsLoggedIn()) {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Staff Login</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<meta name="theme-color" content="#3c8dbc">
	<link rel="manifest" href="<?php echo STAFF_URL; ?>manifest.webmanifest">
	<link rel="apple-touch-icon" href="<?php echo STAFF_URL; ?>assets/icon-192.png">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/AdminLTE.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/_all-skins.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/style.css">
</head>
<body class="hold-transition login-page sidebar-mini">
	<div class="login-box">
		<div class="login-logo">
			<b>Staff Panel</b>
		</div>
		<div class="login-box-body">
			<p class="login-box-msg">Log in to start your session</p>
			<?php if ($error_message !== '') { ?>
				<div class="error"><?php echo $error_message; ?></div>
			<?php } ?>
			<form method="post">
				<div class="form-group has-feedback">
					<input class="form-control" placeholder="Email address" name="email" type="email" autocomplete="off" autofocus>
				</div>
				<div class="form-group has-feedback">
					<input class="form-control" placeholder="Password" name="password" type="password" autocomplete="off" value="">
				</div>
				<div class="row">
					<div class="col-xs-8"></div>
					<div class="col-xs-4">
						<input type="submit" class="btn btn-success btn-block btn-flat login-button" name="form1" value="Log In">
					</div>
				</div>
			</form>
		</div>
	</div>
	<script src="<?php echo BASE_URL; ?>admin/js/jquery-2.2.4.min.js"></script>
	<script src="<?php echo BASE_URL; ?>admin/js/bootstrap.min.js"></script>
	<script>
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('<?php echo STAFF_URL; ?>sw.js').catch(function () {});
	}
	</script>
</body>
</html>
