<?php
require_once __DIR__ . '/../inc/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'account/login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . 'account/order-history.php');
    exit;
}

$customerId = (int)$_SESSION['customer_id'];
$paymentStmt = $pdo->prepare('SELECT * FROM tbl_payment WHERE id = ? AND customer_id = ?');
$paymentStmt->execute([$id, $customerId]);
$payment = $paymentStmt->fetch();
if (!$payment) {
    header('Location: ' . BASE_URL . 'account/order-history.php');
    exit;
}

$orderStmt = $pdo->prepare('SELECT o.*, p.p_featured_photo FROM tbl_order o LEFT JOIN tbl_product p ON p.p_id = o.product_id WHERE o.payment_id = ? ORDER BY o.id ASC');
$orderStmt->execute([$payment['payment_id']]);
$items = $orderStmt->fetchAll();

$pageTitle = t('invoice') . ' ' . e($payment['payment_id']);
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('order_history'), 'url' => BASE_URL . 'account/order-history.php'],
    ['label' => t('invoice'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-4 mb-4">
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('my_account'); ?></h4>
      <div class="d-grid gap-2">
        <a href="<?php echo BASE_URL; ?>account/profile.php" class="btn btn-outline-secondary btn-sm"><?php echo t('profile'); ?></a>
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-outline-secondary btn-sm"><?php echo t('order_history'); ?></a>
        <a href="<?php echo BASE_URL; ?>account/logout.php" class="btn btn-outline-danger btn-sm"><?php echo t('logout'); ?></a>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="fw-bold mb-1"><?php echo t('invoice'); ?></h3>
          <p class="text-muted mb-0"><?php echo sprintf(t('invoice_for_order'), e($payment['payment_id'])); ?></p>
        </div>
        <button class="btn btn-dark btn-sm" onclick="window.print();"><?php echo t('print_invoice'); ?></button>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-sm-6">
          <div class="border rounded-4 p-3">
            <h6 class="mb-2"><?php echo t('billing_details'); ?></h6>
            <p class="mb-1"><strong><?php echo e($payment['customer_name']); ?></strong></p>
            <p class="mb-1"><?php echo e($payment['customer_email']); ?></p>
            <p class="mb-1"><?php echo e($payment['customer_phone']); ?></p>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="border rounded-4 p-3">
            <h6 class="mb-2"><?php echo t('invoice_details'); ?></h6>
            <p class="mb-1"><strong><?php echo t('invoice_number'); ?>:</strong> <?php echo e($payment['payment_id']); ?></p>
            <p class="mb-1"><strong><?php echo t('date'); ?>:</strong> <?php echo e($payment['payment_date']); ?></p>
            <p class="mb-1"><strong><?php echo t('status'); ?>:</strong> <?php echo e($payment['payment_status']); ?></p>
            <p class="mb-0"><strong><?php echo t('shipping'); ?>:</strong> <?php echo e($payment['shipping_status']); ?></p>
          </div>
        </div>
      </div>

      <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th><?php echo t('product'); ?></th>
              <th><?php echo t('photo'); ?></th>
              <th><?php echo t('qty'); ?></th>
              <th><?php echo t('unit_price'); ?></th>
              <th><?php echo t('total'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if ($items) {
                $i = 1;
                foreach ($items as $item) {
                    $lineTotal = (float)$item['line_total'];
            ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($item['product_name']); ?></td>
                <td class="text-center" style="width:90px;">
                  <img src="<?php echo e(getProductImage($item['p_featured_photo'])); ?>" alt="<?php echo e($item['product_name']); ?>" class="img-fluid rounded-3" style="height:60px; object-fit:cover; width:60px;">
                </td>
                <td><?php echo (int)$item['quantity']; ?></td>
                <td>Rs. <?php echo number_format((float)$item['unit_price'], 2); ?></td>
                <td>Rs. <?php echo number_format($lineTotal, 2); ?></td>
              </tr>
            <?php }
            } else { ?>
              <tr>
                <td colspan="6" class="text-center"><?php echo t('no_products_found_for_invoice'); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <div class="border rounded-4 p-3 bg-light">
            <h6 class="mb-3"><?php echo t('notes'); ?></h6>
            <p class="small text-muted mb-0"><?php echo e($payment['notes'] ?: t('no_notes_available')); ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border rounded-4 p-3">
            <div class="d-flex justify-content-between mb-2"><span class="small text-muted"><?php echo t('subtotal'); ?></span><strong>Rs. <?php echo number_format((float)($payment['subtotal'] ?? 0), 2); ?></strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="small text-muted"><?php echo t('discount'); ?></span><strong>Rs. <?php echo number_format((float)($payment['discount_amount'] ?? 0), 2); ?></strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="small text-muted"><?php echo t('vat'); ?></span><strong>Rs. <?php echo number_format((float)($payment['vat_amount'] ?? 0), 2); ?></strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="small text-muted"><?php echo t('paid'); ?></span><strong>Rs. <?php echo number_format((float)($payment['paid_amount'] ?? 0), 2); ?></strong></div>
            <div class="d-flex justify-content-between border-top pt-2"><span class="fw-semibold"><?php echo t('grand_total'); ?></span><strong>Rs. <?php echo number_format((float)($payment['grand_total'] ?? 0), 2); ?></strong></div>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-outline-secondary btn-sm"><?php echo t('back_to_order_history'); ?></a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
