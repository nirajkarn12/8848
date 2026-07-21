<?php
require_once __DIR__ . '/../inc/functions.php';
$pageTitle = loadLang('login');
$redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? '');

if (isLoggedIn()) {
    header('Location: ' . safeAccountRedirect($redirect));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: ' . BASE_URL . 'account/login.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        setFlash('danger', loadLang('email_password_required'));
        header('Location: ' . BASE_URL . 'account/login.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM tbl_customer WHERE cust_email = ? LIMIT 1');
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer || empty($customer['cust_password']) || !verifyPassword($password, $customer['cust_password'])) {
        setFlash('danger', loadLang('invalid_credentials'));
        header('Location: ' . BASE_URL . 'account/login.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
        exit;
    }

    if ((string) ($customer['cust_status'] ?? '1') !== '1') {
        setFlash('danger', loadLang('account_inactive'));
        header('Location: ' . BASE_URL . 'account/login.php');
        exit;
    }

    // Upgrade legacy plain/md5 passwords to bcrypt on successful login
    $info = password_get_info((string) $customer['cust_password']);
    if (empty($info['algo'])) {
        $pdo->prepare('UPDATE tbl_customer SET cust_password = ? WHERE cust_id = ?')
            ->execute([hashCustomerPassword($password), $customer['cust_id']]);
    }

    $_SESSION['customer_id'] = $customer['cust_id'];
    $_SESSION['customer_name'] = $customer['cust_name'];
    linkGuestBookingsByEmail((int) $customer['cust_id'], $customer['cust_email']);
    setFlash('success', loadLang('welcome_back'));
    header('Location: ' . safeAccountRedirect($redirect));
    exit;
}

include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('login'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('customer_login'); ?></h3>
      <form method="post" class="d-grid gap-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">
        <div><label class="form-label"><?php echo t('email'); ?></label><input type="email" class="form-control" name="email" required></div>
        <div>
          <div class="d-flex justify-content-between align-items-center">
            <label class="form-label mb-0"><?php echo t('password'); ?></label>
            <a href="<?php echo BASE_URL; ?>account/forgot-password.php" class="small text-decoration-none"><?php echo t('forgot_password'); ?></a>
          </div>
          <input type="password" class="form-control mt-2" name="password" required>
        </div>
        <button class="btn btn-dark"><?php echo t('login'); ?></button>
        <div class="d-flex justify-content-center small text-muted">
          <a href="<?php echo BASE_URL; ?>account/register.php" class="text-decoration-none"><?php echo t('create_account'); ?></a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
