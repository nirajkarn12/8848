<?php
require_once __DIR__ . '/inc/bootstrap.php';
staffEnsureResetFields();

if (staffIsLoggedIn()) {
    header('Location: ' . STAFF_URL . 'index.php');
    exit;
}

$email = trim((string) ($_GET['email'] ?? $_POST['email'] ?? $_SESSION['staff_reset_email'] ?? ''));
if ($email === '') {
    header('Location: ' . STAFF_URL . 'forgot-password.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim((string) ($_POST['code'] ?? ''));
    if ($code === '') {
        $error = 'Sorry, code is not valid or incorrect.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_staff WHERE email = ? AND status = 'Active' LIMIT 1");
        $stmt->execute(array($email));
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            $error = 'Incorrect email address.';
        } else {
            $storedCode = trim((string) ($staff['reset_code'] ?? ''));
            $expiresAt = $staff['reset_code_expires_at'] ?? null;
            $isExpired = false;

            if ($expiresAt) {
                try {
                    $expiresAtDt = new DateTimeImmutable($expiresAt);
                    $isExpired = (new DateTimeImmutable('now')) > $expiresAtDt;
                } catch (Exception $e) {
                    $isExpired = true;
                }
            }

            if ($storedCode !== $code || $isExpired) {
                $error = 'Sorry, code is not valid or incorrect.';
            } else {
                $_SESSION['staff_reset_verified_email'] = $email;
                header('Location: ' . STAFF_URL . 'reset-password.php?email=' . urlencode($email));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Staff Reset Code</title>
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
            <h1>Verify code</h1>
            <p>Enter the reset code sent to your email to continue with a new password.</p>
            <div class="login-features">
                <span><i class="fa fa-check-circle"></i> Secure verification</span>
                <span><i class="fa fa-check-circle"></i> One-time code</span>
                <span><i class="fa fa-check-circle"></i> Quick recovery</span>
            </div>
        </div>

        <div class="login-box-body login-panel">
            <div class="panel-header">
                <span class="panel-icon"><i class="fa fa-code"></i></span>
                <h3>Enter Code</h3>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" style="margin-bottom: 18px;">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo STAFF_URL; ?>verify-code.php">
                <div class="form-group has-feedback">
                    <input class="form-control login-input" type="text" name="code" placeholder="Enter reset code" maxlength="20" required>
                    <span class="fa fa-shield form-control-feedback"></span>
                </div>
                <div class="row login-actions">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-success btn-block btn-flat login-button">Verify Code</button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="<?php echo STAFF_URL; ?>forgot-password.php" class="small text-decoration-none" style="color: #173a7a; font-weight: 600;">Back</a>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>admin/js/jquery-2.2.4.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/js/bootstrap.min.js"></script>
</body>
</html>
