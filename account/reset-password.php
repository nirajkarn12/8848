<?php
require_once __DIR__ . '/../inc/functions.php';
$pageTitle = loadLang('reset_password_title');

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/profile.php');
    exit;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');

$customer = null;
if ($token !== '' && $email !== '') {
    $stmt = $pdo->prepare('SELECT cust_id, cust_email, cust_token, cust_status FROM tbl_customer WHERE cust_email = ? AND cust_token = ? LIMIT 1');
    $stmt->execute([$email, $token]);
    $customer = $stmt->fetch();
}

if (!$customer || (string) ($customer['cust_status'] ?? '1') !== '1' || empty($customer['cust_token'])) {
    setFlash('danger', loadLang('reset_link_invalid'));
    header('Location: ' . BASE_URL . 'account/forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: ' . BASE_URL . 'account/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email));
        exit;
    }

    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($password === '' || $confirm === '') {
        setFlash('danger', loadLang('fill_all_fields'));
        header('Location: ' . BASE_URL . 'account/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email));
        exit;
    }

    if (strlen($password) < 6) {
        setFlash('danger', loadLang('password_too_short'));
        header('Location: ' . BASE_URL . 'account/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email));
        exit;
    }

    if ($password !== $confirm) {
        setFlash('danger', loadLang('passwords_do_not_match'));
        header('Location: ' . BASE_URL . 'account/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email));
        exit;
    }

    $pdo->prepare('UPDATE tbl_customer SET cust_password = ?, cust_token = ? WHERE cust_id = ?')
        ->execute([hashCustomerPassword($password), '', $customer['cust_id']]);

    setFlash('success', loadLang('password_reset_success'));
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}

include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('login'), 'url' => BASE_URL . 'account/login.php'],
    ['label' => t('reset_password_title'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('reset_password_title'); ?></h3>
      <form method="post" class="d-grid gap-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <input type="hidden" name="token" value="<?php echo e($token); ?>">
        <input type="hidden" name="email" value="<?php echo e($email); ?>">
        <div><label class="form-label"><?php echo t('password'); ?></label><input type="password" class="form-control" name="password" minlength="6" required></div>
        <div><label class="form-label"><?php echo t('confirm_password'); ?></label><input type="password" class="form-control" name="confirm" minlength="6" required></div>
        <button class="btn btn-dark"><?php echo t('update_password'); ?></button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
