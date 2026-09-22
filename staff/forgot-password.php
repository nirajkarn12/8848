<?php
require_once __DIR__ . '/inc/bootstrap.php';
staffEnsureResetFields();

if (staffIsLoggedIn()) {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Incorrect email address.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_staff WHERE email = ? AND status = 'Active' LIMIT 1");
        $stmt->execute(array($email));
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            $error = 'Incorrect email address.';
        } else {
            $code = strtoupper(bin2hex(random_bytes(4)));
            $expiresAt = date('Y-m-d H:i:s', time() + 900);

            $update = $pdo->prepare("UPDATE tbl_staff SET reset_code = ?, reset_code_expires_at = ? WHERE staff_id = ?");
            $update->execute(array($code, $expiresAt, $staff['staff_id']));

            $message = '<p>Hello ' . htmlspecialchars($staff['full_name'] ?? $staff['email'], ENT_QUOTES, 'UTF-8') . ',</p>';
            $message .= '<p>Your password reset code is: <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong></p>';
            $message .= '<p>This code will expire in 15 minutes.</p>';
            $message .= '<p>Use this code on the next page to continue with your password reset.</p>';

            $emailSent = sendCustomerEmail($email, $staff['full_name'] ?? $email, 'Staff password reset code', $message);

            if ($emailSent) {
                $_SESSION['staff_reset_email'] = $email;
                header('Location: ' . STAFF_URL . 'verify-code.php?email=' . urlencode($email));
                exit;
            }

            $error = 'We could not send the reset email. Please contact support.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Staff Forgot Password</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="theme-color" content="#3c8dbc">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/AdminLTE.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/css/_all-skins.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/style.css">
</head>
<body class="hold-transition login-page sidebar-mini" style="background-image: linear-gradient(rgba(6, 20, 48, 0.58), rgba(10, 23, 51, 0.72)), url('<?php echo e('../assets/images/cleaning-side.jpg'); ?>');">
<div class="login-shell">
    <div class="login-box login-modern">
        <div class="login-visual">
            <div class="login-brand">
                <span class="brand-mark"><i class="fa fa-shield"></i></span>
                <div>
                    <small>Business Control Center</small>
                    <strong><?php echo e((string) getSiteSetting('site_name', '8848 Admin')); ?></strong>
                </div>
            </div>
            <h1>Reset access</h1>
            <p>Use your staff email to receive a secure reset code and continue the password reset process.</p>
            <div class="login-features">
                <span><i class="fa fa-check-circle"></i> Secure reset</span>
                <span><i class="fa fa-check-circle"></i> Staff only</span>
                <span><i class="fa fa-check-circle"></i> Private access</span>
            </div>
        </div>

        <div class="login-box-body login-panel">
            <div class="panel-header">
                <span class="panel-icon"><i class="fa fa-key"></i></span>
                <h3>Forgot Password</h3>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" style="margin-bottom: 18px;">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo STAFF_URL; ?>forgot-password.php">
                <div class="form-group has-feedback">
                    <input class="form-control login-input" type="email" name="email" placeholder="Enter your staff email" required>
                    <span class="fa fa-envelope form-control-feedback"></span>
                </div>
                <div class="row login-actions">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-success btn-block btn-flat login-button">Send Code</button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="<?php echo STAFF_URL; ?>login.php" class="small text-decoration-none" style="color: #173a7a; font-weight: 600;">Back to login</a>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>admin/js/jquery-2.2.4.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/bootstrap.min.js"></script>
</body>
</html>
