<?php
require_once __DIR__ . '/../inc/functions.php';
$pageTitle = loadLang('login');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: ' . BASE_URL . 'account/login.php');
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        setFlash('danger', loadLang('email_password_required'));
        header('Location: ' . BASE_URL . 'account/login.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM tbl_customer WHERE cust_email = ? LIMIT 1');
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer || !verifyPassword($password, $customer['cust_password'])) {
        setFlash('danger', loadLang('invalid_credentials'));
        header('Location: ' . BASE_URL . 'account/login.php');
        exit;
    }

    $_SESSION['customer_id'] = $customer['cust_id'];
    $_SESSION['customer_name'] = $customer['cust_name'];
    setFlash('success', loadLang('welcome_back'));
    header('Location: ' . BASE_URL . 'account/profile.php');
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
        <div><label class="form-label"><?php echo t('email'); ?></label><input type="email" class="form-control" name="email" required></div>
        <div><label class="form-label"><?php echo t('password'); ?></label><input type="password" class="form-control" name="password" required></div>
        <button class="btn btn-dark"><?php echo t('login'); ?></button>
        <div class="d-flex justify-content-center small text-muted">
          <a href="<?php echo BASE_URL; ?>account/register.php" class="text-decoration-none"><?php echo t('create_account'); ?></a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
