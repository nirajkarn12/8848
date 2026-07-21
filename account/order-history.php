<?php
require_once __DIR__ . '/../inc/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/login.php?redirect=' . urlencode('account/order-history.php'));
    exit;
}
$pageTitle = t('order_history');
$customer = currentCustomer();
if (!$customer) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name']);
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}
$customerId = (int) $customer['cust_id'];
linkGuestBookingsByEmail($customerId, $customer['cust_email']);

$payments = $pdo->prepare('SELECT * FROM tbl_payment WHERE customer_id = ? ORDER BY id DESC');
$payments->execute([$customerId]);
$payments = $payments->fetchAll();
$ordersByPayment = [];
if ($payments) {
    $paymentIds = array_column($payments, 'payment_id');
    $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
    $ordersQuery = $pdo->prepare('SELECT o.*, p.p_featured_photo FROM tbl_order o LEFT JOIN tbl_product p ON p.p_id = o.product_id WHERE o.payment_id IN (' . $placeholders . ') ORDER BY o.id ASC');
    $ordersQuery->execute($paymentIds);
    foreach ($ordersQuery->fetchAll() as $item) {
        $ordersByPayment[$item['payment_id']][] = $item;
    }
}
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('order_history'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

function bookingStatusBadgeClass($status) {
    $status = strtolower((string) $status);
    if (in_array($status, ['completed', 'complete'], true)) {
        return 'success';
    }
    if (in_array($status, ['cancelled', 'canceled'], true)) {
        return 'danger';
    }
    if (in_array($status, ['assigned', 'confirmed', 'in progress', 'in_progress'], true)) {
        return 'info';
    }
    return 'warning';
}
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('my_account'); ?></h4>
      <div class="d-grid gap-2">
        <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-outline-secondary btn-sm"><?php echo t('profile'); ?></a>
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-dark btn-sm"><?php echo t('order_history'); ?></a>
        <a href="<?php echo BASE_URL; ?>account/logout.php" class="btn btn-outline-danger btn-sm"><?php echo t('logout'); ?></a>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold mb-1"><?php echo t('order_history'); ?></h3>
          <p class="text-muted mb-0"><?php echo t('order_history_description'); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-outline-secondary btn-sm"><?php echo t('back_to_profile'); ?></a>
      </div>
      <?php if ($payments) { foreach ($payments as $payment) {
            $items = $ordersByPayment[$payment['payment_id']] ?? [];
            $invoiceUrl = BASE_URL . 'account/invoice.php?id=' . (int)$payment['id'];
            $collapseId = 'order-details-' . md5($payment['payment_id']);
            $bookingStatus = $payment['booking_status'] ?? $payment['shipping_status'] ?? $payment['payment_status'];
            $schedule = trim(($payment['preferred_date'] ?? '') . ' ' . ($payment['preferred_time'] ?? ''));
      ?>
        <div class="border rounded-4 p-3 mb-3 shadow-sm">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-2">
            <div>
              <div class="fw-semibold"><?php echo tf('order_number', $payment['payment_id']); ?></div>
              <div class="small text-muted"><?php echo tf('placed_on_items', $payment['payment_date'], (string)count($items)); ?></div>
              <?php if ($schedule !== '') { ?>
                <div class="small text-muted mt-1"><?php echo t('preferred_date'); ?>: <?php echo e($schedule); ?></div>
              <?php } ?>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <span class="badge bg-<?php echo bookingStatusBadgeClass($bookingStatus); ?>"><?php echo e($bookingStatus); ?></span>
              <span class="badge bg-<?php echo strtolower((string)$payment['payment_status']) === 'completed' ? 'success' : 'secondary'; ?>"><?php echo e($payment['payment_status']); ?></span>
              <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>"><?php echo t('details'); ?></button>
            </div>
          </div>

          <div class="collapse" id="<?php echo $collapseId; ?>">
            <div class="pt-3 border-top">
              <?php if (!empty($payment['service_address'])) { ?>
                <div class="mb-3">
                  <div class="small text-muted"><?php echo t('service_address'); ?></div>
                  <div><?php echo nl2br(e($payment['service_address'])); ?></div>
                </div>
              <?php } ?>
              <?php
                $histLat = normalizeMapCoordinate($payment['service_lat'] ?? null, -90, 90);
                $histLng = normalizeMapCoordinate($payment['service_lng'] ?? null, -180, 180);
                if ($histLat !== null || $histLng !== null || !empty($payment['service_address'])) {
              ?>
                <div class="mb-3">
                  <div class="small text-muted mb-2"><?php echo t('map_service_location'); ?></div>
                  <?php echo renderServiceLocationViewer([
                      'lat' => $histLat,
                      'lng' => $histLng,
                      'address' => $payment['service_address'] ?? '',
                      'id' => 'customerMap-' . (int)$payment['id'],
                  ]); ?>
                </div>
              <?php } ?>
              <?php if ($items) { ?>
                <div class="row g-3 mb-3">
                  <?php foreach ($items as $item) { ?>
                    <div class="col-12 col-sm-6 col-xl-4">
                      <div class="d-flex gap-3 align-items-center border rounded-4 p-2">
                        <a href="<?php echo e(getProductImage($item['p_featured_photo'])); ?>" data-fancybox="order-<?php echo e($payment['payment_id']); ?>" data-caption="<?php echo e($item['product_name']); ?>">
                          <img src="<?php echo e(getProductImage($item['p_featured_photo'])); ?>" alt="<?php echo e($item['product_name']); ?>" class="rounded-3" style="width:72px; height:72px; object-fit:cover;">
                        </a>
                        <div>
                          <div class="fw-semibold"><?php echo e($item['product_name']); ?></div>
                          <div class="small text-muted">Rs. <?php echo number_format((float)$item['unit_price'], 2); ?></div>
                        </div>
                      </div>
                    </div>
                  <?php } ?>
                </div>
              <?php } else { ?>
                <div class="alert alert-light rounded-4 mb-3"><?php echo t('no_ordered_products_found'); ?></div>
              <?php } ?>
              <?php if (!empty($payment['notes'])) { ?>
                <div class="mb-3">
                  <div class="small text-muted"><?php echo t('notes'); ?></div>
                  <div class="small"><?php echo nl2br(e($payment['notes'])); ?></div>
                </div>
              <?php } ?>
              <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div class="small text-muted">
                  <div><strong><?php echo t('total'); ?>:</strong> Rs. <?php echo number_format((float)($payment['grand_total'] ?? 0), 2); ?></div>
                  <div><strong><?php echo t('due'); ?>:</strong> Rs. <?php echo number_format((float)($payment['due_amount'] ?? 0), 2); ?></div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <a href="<?php echo $invoiceUrl; ?>" class="btn btn-dark btn-sm"><?php echo t('view_invoice'); ?></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php } } else { ?><div class="alert alert-light rounded-4"><?php echo t('no_orders_yet'); ?></div><?php } ?>
    </div>
  </div>
</div>
<?php echo serviceLocationAssets(); ?>
<?php include __DIR__ . '/../inc/footer.php'; ?>
