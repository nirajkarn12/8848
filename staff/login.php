<?php
require_once __DIR__ . '/inc/bootstrap.php';

$error_message = '';

$loginBackground = '../assets/images/cleaning-side.jpg';
$loginSiteName = 'Staff Panel';

try {
    $settingsRow = $pdo->query("SELECT banner_login, site_name FROM tbl_settings WHERE id=1 LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: array();
    $loginBanner = trim((string) ($settingsRow['banner_login'] ?? ''));
    if ($loginBanner !== '' && is_file('../assets/uploads/' . $loginBanner)) {
        $loginBackground = '../assets/uploads/' . $loginBanner;
    }
    $siteNameFromDb = trim((string) ($settingsRow['site_name'] ?? ''));
    if ($siteNameFromDb !== '') {
        $loginSiteName = $siteNameFromDb;
    }
} catch (Throwable $e) {
    $loginBackground = '../assets/images/cleaning-side.jpg';
    $loginSiteName = 'Staff Panel';
}

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
    <?php
    $loginFavicon = '';
    try {
        $rowFav = $pdo->query("SELECT favicon, logo FROM tbl_settings WHERE id=1 LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: array();
        $loginFavicon = trim((string) ($rowFav['favicon'] ?? ''));
        if ($loginFavicon === '') {
            $loginFavicon = trim((string) ($rowFav['logo'] ?? ''));
        }
    } catch (Exception $e) {
        $loginFavicon = '';
    }
    if ($loginFavicon !== '' && is_file('../assets/uploads/' . $loginFavicon)) {
        $loginFaviconUrl = htmlspecialchars('../assets/uploads/' . $loginFavicon, ENT_QUOTES, 'UTF-8');
    }
    ?>
    <?php if ($loginFavicon !== '' && is_file('../assets/uploads/' . $loginFavicon)): ?>
    <link rel="icon" href="<?php echo $loginFaviconUrl; ?>">
    <link rel="shortcut icon" href="<?php echo $loginFaviconUrl; ?>">
    <?php endif; ?>
    <link rel="manifest" href="<?php echo STAFF_URL; ?>manifest.webmanifest">
    <link rel="apple-touch-icon" href="<?php echo STAFF_URL; ?>assets/icon-192.png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/ionicons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/datepicker3.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/all.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/select2.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/AdminLTE.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/_all-skins.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/style.css">
</head>

<body class="hold-transition login-page sidebar-mini" style="background-image: linear-gradient(rgba(6, 20, 48, 0.58), rgba(10, 23, 51, 0.72)), url('<?php echo e($loginBackground); ?>');">

<div class="login-shell">
    <div class="login-box login-modern">
        <div class="login-visual">
            <div class="login-brand">
                <span class="brand-mark"><i class="fa fa-shield"></i></span>
                <div>
                    <small>Business Control Center</small>
                    <strong><?php echo e($loginSiteName); ?></strong>
                </div>
            </div>
            <h1>Welcome back</h1>
            <p>Manage your store, customers, orders, and reports from one secure dashboard.</p>
            <div class="login-features">
                <span><i class="fa fa-check-circle"></i> Secure access</span>
                <span><i class="fa fa-check-circle"></i> Smart reporting</span>
                <span><i class="fa fa-check-circle"></i> Inventory control</span>
            </div>
        </div>

        <div class="login-box-body login-panel">
            <div class="panel-header">
                <span class="panel-icon"><i class="fa fa-user"></i></span>
                <h3>Sign In</h3>
            </div>
            <p class="login-box-msg">Log in to start your session</p>

            <?php 
            if (isset($error_message) && $error_message != ''):
                echo '<div class="error">' . $error_message . '</div>';
            endif;
            ?>

            <form action="" method="post">
                <div class="form-group has-feedback">
                    <input class="form-control login-input" placeholder="Email address" name="email" type="email" autocomplete="off" autofocus required>
                    <span class="fa fa-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input class="form-control login-input" placeholder="Password" name="password" type="password" autocomplete="off" value="" required>
                    <span class="fa fa-lock form-control-feedback"></span>
                </div>
                <div class="row login-actions">
                    <div class="col-xs-7 text-left">
                        <label class="remember-me"><input type="checkbox"> Keep me signed in</label>
                    </div>
                    <div class="col-xs-5">
                        <input type="submit" class="btn btn-success btn-block btn-flat login-button" name="form1" value="Log In">
                    </div>
                </div>
                <div class="text-center" style="margin-top: 18px; margin-bottom: 4px;">
                    <a href="<?php echo STAFF_URL; ?>forgot-password.php" class="small text-decoration-none" style="color: #173a7a; font-weight: 600; letter-spacing: 0.01em;">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>admin/js/jquery-2.2.3.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/bootstrap.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/select2.full.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/jquery.inputmask.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/jquery.inputmask.date.extensions.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/jquery.inputmask.extensions.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/moment.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/bootstrap-datepicker.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/icheck.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/fastclick.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/jquery.sparkline.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/jquery.slimscroll.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/app.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/demo.js"></script>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?php echo STAFF_URL; ?>sw.js').catch(function () {});
}
</script>

</body>
</html>
