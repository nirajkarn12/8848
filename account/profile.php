<?php
require_once __DIR__ . '/../inc/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}
$pageTitle = t('profile');
$customer = currentCustomer();
if (!$customer) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name']);
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}
$customerId = (int)$customer['cust_id'];
linkGuestBookingsByEmail($customerId, $customer['cust_email']);
$orderStatsStmt = $pdo->prepare('SELECT COUNT(*) AS total_orders, COALESCE(SUM(grand_total), 0) AS total_spent, SUM(CASE WHEN payment_status = "Completed" THEN 1 ELSE 0 END) AS completed_orders, SUM(CASE WHEN payment_status = "Pending" THEN 1 ELSE 0 END) AS pending_orders, MAX(payment_date) AS last_order_date FROM tbl_payment WHERE customer_id = ?');
$orderStatsStmt->execute([$customerId]);
$orderStats = $orderStatsStmt->fetch();
$recentOrdersStmt = $pdo->prepare('SELECT * FROM tbl_payment WHERE customer_id = ? ORDER BY id DESC LIMIT 3');
$recentOrdersStmt->execute([$customerId]);
$recentOrders = $recentOrdersStmt->fetchAll();
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('profile'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('my_account'); ?></h4>
      <div class="d-grid gap-2">
        <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-dark btn-sm"><?php echo t('profile'); ?></a>
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-outline-secondary btn-sm"><?php echo t('order_history'); ?></a>
        <a href="<?php echo BASE_URL; ?>account/logout.php" class="btn btn-outline-danger btn-sm"><?php echo t('logout'); ?></a>
      </div>
      <hr>
      <p class="mb-1"><strong><?php echo t('name'); ?>:</strong> <?php echo e($customer['cust_name']); ?></p>
      <p class="mb-1"><strong><?php echo t('email_address'); ?>:</strong> <?php echo e($customer['cust_email']); ?></p>
      <p class="mb-1"><strong><?php echo t('phone'); ?>:</strong> <?php echo e($customer['cust_phone']); ?></p>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold mb-1"><?php echo t('profile_dashboard'); ?></h4>
          <p class="text-muted mb-0"><?php echo t('profile_dashboard_description'); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-outline-secondary btn-sm"><?php echo t('view_all_orders'); ?></a>
      </div>
      <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
          <div class="border rounded-4 p-3 h-100">
            <div class="small text-muted mb-1"><?php echo t('total_orders'); ?></div>
            <div class="fw-semibold fs-4"><?php echo e($orderStats['total_orders']); ?></div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="border rounded-4 p-3 h-100">
            <div class="small text-muted mb-1"><?php echo t('completed_orders'); ?></div>
            <div class="fw-semibold fs-4"><?php echo e($orderStats['completed_orders']); ?></div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="border rounded-4 p-3 h-100">
            <div class="small text-muted mb-1"><?php echo t('pending_orders'); ?></div>
            <div class="fw-semibold fs-4"><?php echo e($orderStats['pending_orders']); ?></div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="border rounded-4 p-3 h-100">
            <div class="small text-muted mb-1"><?php echo t('total_spent'); ?></div>
            <div class="fw-semibold fs-4">Rs. <?php echo number_format((float)$orderStats['total_spent'], 2); ?></div>
          </div>
        </div>
      </div>
      <div class="border rounded-4 p-4 mt-4">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="border rounded-4 p-3">
              <div class="small text-muted mb-1"><?php echo t('customer_name'); ?></div>
              <div class="fw-semibold"><?php echo e($customer['cust_name']); ?></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="border rounded-4 p-3">
              <div class="small text-muted mb-1"><?php echo t('email_address'); ?></div>
              <div class="fw-semibold"><?php echo e($customer['cust_email']); ?></div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <h5 class="fw-semibold mb-3"><?php echo t('recent_orders'); ?></h5>
        <?php if ($recentOrders) { ?>
          <?php foreach ($recentOrders as $order) { ?>
            <div class="border rounded-4 p-3 mb-3 shadow-sm">
              <div class="d-flex justify-content-between align-items-start flex-column flex-md-row gap-2">
                <div>
                  <div class="fw-semibold"><?php echo t('order'); ?> <?php echo e($order['payment_id']); ?></div>
                  <div class="small text-muted"><?php echo t('placed_on'); ?> <?php echo e($order['payment_date']); ?></div>
                  <?php
                    $schedule = trim(($order['preferred_date'] ?? '') . ' ' . ($order['preferred_time'] ?? ''));
                    if ($schedule !== '') {
                        echo '<div class="small text-muted">' . t('preferred_date') . ': ' . e($schedule) . '</div>';
                    }
                  ?>
                </div>
                <div class="text-md-end">
                  <?php $statusLabel = $order['booking_status'] ?? $order['shipping_status'] ?? $order['payment_status']; ?>
                  <span class="badge bg-<?php echo strtolower((string)$order['payment_status']) === 'completed' ? 'success' : 'warning'; ?> me-1"><?php echo e($order['payment_status']); ?></span>
                  <span class="badge bg-secondary"><?php echo e($statusLabel); ?></span>
                  <div class="mt-2"><?php echo t('total'); ?>: Rs. <?php echo number_format((float)($order['grand_total'] ?? 0), 2); ?></div>
                </div>
              </div>
            </div>
          <?php } ?>
        <?php } else { ?>
          <div class="alert alert-light rounded-4"><?php echo t('no_recent_orders_found'); ?></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
