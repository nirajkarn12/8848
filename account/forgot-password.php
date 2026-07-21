<?php
require_once __DIR__ . '/../inc/functions.php';
$pageTitle = loadLang('forgot_password_title');

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: ' . BASE_URL . 'account/forgot-password.php');
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', loadLang('newsletter_invalid_email'));
        header('Location: ' . BASE_URL . 'account/forgot-password.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT cust_id, cust_name, cust_email, cust_status FROM tbl_customer WHERE cust_email = ? LIMIT 1');
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    // Always show success to avoid email enumeration
    $genericMessage = loadLang('forgot_password_sent');

    if ($customer && (string) ($customer['cust_status'] ?? '1') === '1') {
        $token = bin2hex(random_bytes(32));
        $pdo->prepare('UPDATE tbl_customer SET cust_token = ? WHERE cust_id = ?')->execute([$token, $customer['cust_id']]);

        $resetUrl = BASE_URL . 'account/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($customer['cust_email']);
        $customMessage = trim((string) getSiteSetting('forget_password_message', ''));
        $body = '<p>Hi ' . htmlspecialchars($customer['cust_name'], ENT_QUOTES, 'UTF-8') . ',</p>';
        if ($customMessage !== '') {
            $body .= '<p>' . nl2br(htmlspecialchars($customMessage, ENT_QUOTES, 'UTF-8')) . '</p>';
        } else {
            $body .= '<p>We received a request to reset your password for ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '.</p>';
        }
        $body .= '<p><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '">Reset your password</a></p>';
        $body .= '<p>If the button does not work, copy this link:<br>' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '</p>';
        $body .= '<p>If you did not request this, you can ignore this email.</p>';

        sendCustomerEmail($customer['cust_email'], $customer['cust_name'], SITE_NAME . ' - Password reset', $body);
    }

    setFlash('success', $genericMessage);
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}

include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('login'), 'url' => BASE_URL . 'account/login.php'],
    ['label' => t('forgot_password_title'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-2"><?php echo t('forgot_password_title'); ?></h3>
      <p class="text-muted mb-4"><?php echo t('forgot_password_help'); ?></p>
      <form method="post" class="d-grid gap-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div><label class="form-label"><?php echo t('email'); ?></label><input type="email" class="form-control" name="email" required></div>
        <button class="btn btn-dark"><?php echo t('send_reset_link'); ?></button>
        <div class="text-center small">
          <a href="<?php echo BASE_URL; ?>account/login.php" class="text-decoration-none"><?php echo t('back_to_login'); ?></a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
