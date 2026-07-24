<?php
require_once __DIR__ . '/../inc/functions.php';
ensureAuthIntegrations();
$pageTitle = loadLang('register');

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    if (!verifyRecaptcha($_POST['g-recaptcha-response'] ?? '')) {
        setFlash('danger', loadLang('recaptcha_failed'));
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    $name = trim($_POST['cust_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($name === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        setFlash('danger', loadLang('fill_all_fields'));
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    if (strlen($password) < 6) {
        setFlash('danger', loadLang('password_too_short'));
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    if ($password !== $confirm) {
        setFlash('danger', loadLang('passwords_do_not_match'));
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    $check = $pdo->prepare('SELECT cust_id FROM tbl_customer WHERE cust_email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        setFlash('danger', loadLang('email_already_exists'));
        header('Location: ' . BASE_URL . 'account/register.php');
        exit;
    }

    $hashed = hashCustomerPassword($password);
    $hasGoogleCol = ensureAuthIntegrations();
    if ($hasGoogleCol) {
        $stmt = $pdo->prepare('INSERT INTO tbl_customer (cust_name, cust_cname, cust_email, cust_google_id, cust_phone, cust_country, cust_address, cust_city, cust_state, cust_zip, cust_b_name, cust_b_cname, cust_b_phone, cust_b_country, cust_b_address, cust_b_city, cust_b_state, cust_b_zip, cust_s_name, cust_s_cname, cust_s_phone, cust_s_country, cust_s_address, cust_s_city, cust_s_state, cust_s_zip, cust_password, cust_token, cust_datetime, cust_timestamp, cust_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $name, '', $email, '', $phone, 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '',
            $hashed, '', date('Y-m-d H:i:s'), time(), 1,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO tbl_customer (cust_name, cust_cname, cust_email, cust_phone, cust_country, cust_address, cust_city, cust_state, cust_zip, cust_b_name, cust_b_cname, cust_b_phone, cust_b_country, cust_b_address, cust_b_city, cust_b_state, cust_b_zip, cust_s_name, cust_s_cname, cust_s_phone, cust_s_country, cust_s_address, cust_s_city, cust_s_state, cust_s_zip, cust_password, cust_token, cust_datetime, cust_timestamp, cust_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $name, '', $email, $phone, 0, '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '', '', '',
            $hashed, '', date('Y-m-d H:i:s'), time(), 1,
        ]);
    }

    $customerId = $pdo->lastInsertId();
    linkGuestBookingsByEmail((int) $customerId, $email);

    $_SESSION['customer_id'] = $customerId;
    $_SESSION['customer_name'] = $name;
    setFlash('success', loadLang('registration_successful'));
    header('Location: ' . BASE_URL . 'account/profile.php');
    exit;
}

include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('register'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
echo renderFlash();
echo renderRecaptchaScript();
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('create_account'); ?></h3>
      <?php if (isGoogleAuthEnabled()): ?>
        <div class="d-grid gap-3 mb-3">
          <?php echo renderGoogleAuthButton(''); ?>
          <?php echo renderAuthDivider(); ?>
        </div>
      <?php endif; ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-6"><label class="form-label"><?php echo t('full_name'); ?></label><input class="form-control" name="cust_name" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('email'); ?></label><input class="form-control" type="email" name="email" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('phone'); ?></label><input class="form-control" name="phone" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('password'); ?></label><input class="form-control" type="password" name="password" minlength="6" required></div>
        <div class="col-12"><label class="form-label"><?php echo t('confirm_password'); ?></label><input class="form-control" type="password" name="confirm" minlength="6" required></div>
        <?php if (isRecaptchaEnabled()): ?>
          <div class="col-12"><?php echo renderRecaptchaWidget(); ?></div>
        <?php endif; ?>
        <div class="col-12"><button class="btn btn-dark"><?php echo t('register'); ?></button></div>
        <div class="col-12 text-center small text-muted">
          <?php echo t('already_have_account'); ?> <a href="<?php echo BASE_URL; ?>account/login.php" class="text-decoration-none fw-bold"><?php echo t('login_here'); ?></a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
