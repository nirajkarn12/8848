<?php
require_once __DIR__ . '/inc/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/login.php?redirect=' . urlencode('referral-offer.php'));
    exit;
}

ensureReferralTables();
$customer = currentCustomer();
$settings = getReferralSettings() ?: [
    'is_active' => 0,
    'discount_type' => 'percent',
    'discount_value' => 0,
    'minimum_order_amount' => 0,
    'terms' => '',
];
$createdCode = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = loadLang('referral_invalid_request');
    } else {
        $refereeName = trim($_POST['referee_name'] ?? '');
        $refereeEmail = strtolower(trim($_POST['referee_email'] ?? ''));
        $refereePhone = trim($_POST['referee_phone'] ?? '');
        if ($refereeName === '' || !filter_var($refereeEmail, FILTER_VALIDATE_EMAIL) || $refereePhone === '') {
            $errorMessage = loadLang('referral_fields_required');
        } elseif (strcasecmp($refereeEmail, (string) $customer['cust_email']) === 0) {
            $errorMessage = loadLang('referral_own_email');
        } elseif ((int) $settings['is_active'] !== 1) {
            $errorMessage = loadLang('referral_unavailable');
        } else {
            $createdCode = generateReferralCode();
            $insert = $pdo->prepare('INSERT INTO tbl_referral (referral_code, referrer_customer_id, referrer_name, referrer_email, referee_name, referee_email, referee_phone, status, discount_type, discount_value, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, \'Pending\', ?, ?, NOW())');
            $insert->execute([
                $createdCode,
                (int) $customer['cust_id'],
                $customer['cust_name'],
                $customer['cust_email'],
                $refereeName,
                $refereeEmail,
                $refereePhone,
                $settings['discount_type'],
                (float) $settings['discount_value'],
            ]);
        }
    }
}

$referralsStmt = $pdo->prepare('SELECT * FROM tbl_referral WHERE referrer_customer_id = ? ORDER BY id DESC');
$referralsStmt->execute([(int) $customer['cust_id']]);
$referrals = $referralsStmt->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = loadLang('refer_a_friend');
include __DIR__ . '/inc/header.php';
echo renderBreadcrumbs([
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('refer_a_friend'), 'url' => ''],
]);
echo renderFlash();
?>
<div class="row g-4 align-items-stretch">
  <div class="col-lg-7">
    <div class="card card-hover p-4 h-100">
      <span class="text-uppercase small fw-bold text-primary"><?php echo t('referral_offer'); ?></span>
      <h1 class="display-6 fw-bold mt-2 mb-3"><?php echo t('share_a_cleaner_start'); ?></h1>
      <p class="text-muted mb-4"><?php echo tf('referral_intro', $settings['discount_type'] === 'amount' ? 'NZ$ ' . number_format((float) $settings['discount_value'], 2) : number_format((float) $settings['discount_value'], 2) . '%'); ?></p>
      <?php if ($createdCode !== ''): ?>
        <div class="alert alert-success border-0 rounded-4">
          <strong><?php echo t('referral_code_ready'); ?></strong>
          <div class="d-flex gap-2 align-items-center mt-2">
            <code class="fs-5 flex-grow-1" id="referralCode"><?php echo e($createdCode); ?></code>
            <button type="button" class="btn btn-dark btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('referralCode').textContent)"><?php echo t('copy'); ?></button>
          </div>
          <a class="btn btn-link px-0" href="mailto:?subject=Cleaning offer from 8848&body=Use referral code <?php echo rawurlencode($createdCode); ?> at <?php echo rawurlencode(BASE_URL . 'checkout.php?referral=' . $createdCode); ?>"><?php echo t('share_by_email'); ?></a>
        </div>
      <?php elseif ($errorMessage !== ''): ?>
        <div class="alert alert-danger rounded-4"><?php echo e($errorMessage); ?></div>
      <?php endif; ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-12"><h5 class="fw-bold mb-0"><?php echo t('who_are_you_referring'); ?></h5></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('full_name_required'); ?></label><input class="form-control" name="referee_name" required></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input class="form-control" type="email" name="referee_email" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('phone_required'); ?></label><input class="form-control" name="referee_phone" required></div>
        <div class="col-12"><button class="btn btn-dark px-4" <?php echo (int) $settings['is_active'] !== 1 ? 'disabled' : ''; ?>><?php echo t('create_referral_code'); ?></button></div>
      </form>
      <?php if (trim((string) $settings['terms']) !== ''): ?><p class="small text-muted border-top pt-3 mt-4 mb-0"><?php echo nl2br(e($settings['terms'])); ?></p><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card card-hover p-4 h-100 bg-body-tertiary border-0">
      <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
          <span class="text-uppercase small fw-bold text-primary">8848</span>
          <h2 class="h4 fw-bold mb-1 mt-1"><?php echo t('referral_how_it_works'); ?></h2>
          <p class="small text-muted mb-0"><?php echo t('referral_three_steps'); ?></p>
        </div>
        <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;"><i class="fa fa-arrow-right"></i></span>
      </div>
      <div class="row g-3">
        <div class="col-12">
          <div class="d-flex gap-3 align-items-start bg-white border rounded-4 p-3 h-100 shadow-sm">
            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:36px;height:36px;">1</span>
            <div><h3 class="h6 fw-bold mb-1"><?php echo t('refer_someone'); ?></h3><p class="small text-muted mb-0"><?php echo t('refer_someone_text'); ?></p></div>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex gap-3 align-items-start bg-white border rounded-4 p-3 h-100 shadow-sm">
            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:36px;height:36px;">2</span>
            <div><h3 class="h6 fw-bold mb-1"><?php echo t('they_book'); ?></h3><p class="small text-muted mb-0"><?php echo t('they_book_text'); ?></p></div>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex gap-3 align-items-start bg-white border rounded-4 p-3 h-100 shadow-sm">
            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width:36px;height:36px;">3</span>
            <div><h3 class="h6 fw-bold mb-1"><?php echo t('they_save'); ?></h3><p class="small text-muted mb-0"><?php echo t('they_save_text'); ?></p></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="card card-hover p-4 mt-4" id="referrals">
  <h4 class="fw-bold mb-3"><?php echo t('your_referrals'); ?></h4>
  <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th><?php echo t('person'); ?></th><th><?php echo t('referral_code'); ?></th><th><?php echo t('status'); ?></th><th><?php echo t('created'); ?></th></tr></thead><tbody>
  <?php foreach ($referrals as $referral): ?><tr><td><?php echo e($referral['referee_name']); ?><br><small class="text-muted"><?php echo e($referral['referee_email']); ?></small></td><td><code><?php echo e($referral['referral_code']); ?></code></td><td><span class="badge text-bg-<?php echo $referral['status'] === 'Converted' ? 'success' : ($referral['status'] === 'Cancelled' ? 'secondary' : 'warning'); ?>"><?php echo e($referral['status']); ?></span></td><td><?php echo e(date('M j, Y', strtotime($referral['created_at']))); ?></td></tr><?php endforeach; ?>
  <?php if (!$referrals): ?><tr><td colspan="4" class="text-muted"><?php echo t('referral_history_empty'); ?></td></tr><?php endif; ?>
  </tbody></table></div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
