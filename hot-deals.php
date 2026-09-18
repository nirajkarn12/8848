<?php
require_once __DIR__ . '/inc/functions.php';

ensureReferralTables();

$customerId = isLoggedIn() ? (int) ($_SESSION['customer_id'] ?? 0) : 0;
$availablePoints = $customerId > 0 ? getReferralPoints($customerId) : 0;
$referralSettings = getReferralSettings();
$pointsPerDollar = max(1, (int) ($referralSettings['points_per_dollar'] ?? 100));

$services = $pdo->query(
    'SELECT p_id, p_name, p_short_description, p_featured_photo, p_current_price, p_qty, p_is_featured FROM tbl_product WHERE p_is_active = 1 ORDER BY p_is_featured DESC, p_id DESC LIMIT 12'
)->fetchAll();

$pageTitle = 'Hot Deals';
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => 'Hot Deals', 'url' => ''],
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="section-head mb-4">
  <div>
    <div class="section-kicker">Special offers</div>
    <h1 class="section-title mb-1">Hot Deals</h1>
    <p class="section-subtitle mb-0">Use your earned referral points on selected services and save more on your next booking.</p>
  </div>
  <div class="text-muted small"><?php echo $customerId > 0 ? 'Your available points: ' . number_format($availablePoints) : 'Log in to unlock referral rewards'; ?></div>
</div>

<div class="row g-4 mb-5">
  <div class="col-12">
    <div class="card border-0 bg-gradient-primary text-white p-4 shadow-sm">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <div class="small text-uppercase fw-semibold text-white-50">Referral reward</div>
          <h2 class="h4 mb-1 fw-bold">Bonus points are ready for your next service</h2>
          <p class="mb-0 text-white-50">Every successful referral adds points to your account. You can redeem them on eligible bookings.</p>
        </div>
        <div class="text-end">
          <div class="fs-3 fw-bold"><?php echo number_format($availablePoints); ?> pts</div>
          <div class="small text-white-50">Approx. NZ$ <?php echo number_format(round($availablePoints / $pointsPerDollar, 2), 2); ?> discount</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <?php foreach ($services as $product): ?>
    <?php
    $price = (float) ($product['p_current_price'] ?? 0);
    $dealDiscountRate = 10;
    $dealPrice = max(0, $price - ($price * ($dealDiscountRate / 100)));
    $pointsRequired = (int) ceil($price * $pointsPerDollar);
    $pointsDiscountValue = round($pointsRequired / $pointsPerDollar, 2);
    ?>
    <div class="col-lg-4 col-md-6">
      <div class="card card-hover h-100 border-0 shadow-sm">
        <div class="position-relative">
          <img src="<?php echo getProductImage($product['p_featured_photo']); ?>" alt="<?php echo e($product['p_name']); ?>" class="img-fluid w-100" style="height:220px;object-fit:cover;">
          <span class="position-absolute top-3 start-3 badge bg-danger rounded-pill">Hot Deal</span>
        </div>
        <div class="card-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge soft-pill"><?php echo !empty($product['p_is_featured']) ? 'Featured' : 'Popular'; ?></span>
            <span class="small text-muted">Save <?php echo $dealDiscountRate; ?>%</span>
          </div>
          <h3 class="h5 fw-bold mb-2"><?php echo e($product['p_name']); ?></h3>
          <p class="text-muted small mb-3"><?php echo e(excerpt($product['p_short_description'], 90)); ?></p>
          <div class="mb-3 small">
            <div class="text-muted">Referral points value:</div>
            <div class="fw-semibold text-dark"><?php echo number_format($pointsRequired); ?> pts = NZ$ <?php echo number_format($pointsDiscountValue, 2); ?> off</div>
          </div>
          <div class="mt-auto d-flex justify-content-between align-items-center">
            <div>
              <div class="text-decoration-line-through text-muted small">NZ$ <?php echo number_format($price, 2); ?></div>
              <div class="fw-bold fs-5">NZ$ <?php echo number_format($dealPrice, 2); ?></div>
            </div>
            <a href="cart.php?action=add&id=<?php echo (int) $product['p_id']; ?>&redirect=checkout.php" class="btn btn-dark btn-sm">Book now</a>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>
