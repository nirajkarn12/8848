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

$company = getInvoiceCompanyProfile();
$dueDays = (int) $company['due_days'];
$isPending = in_array(strtolower((string) $payment['payment_status']), ['incomplete', 'pending'], true);
$qrPayload = rawurlencode('Invoice ' . $payment['payment_id'] . ' | ' . $company['site_name']);

$pageTitle = t('invoice') . ' ' . e($payment['payment_id']);
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('order_history'), 'url' => BASE_URL . 'account/order-history.php'],
    ['label' => t('invoice'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="row g-4 mb-4 no-print">
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
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h3 class="fw-bold mb-1"><?php echo t('invoice'); ?></h3>
        <p class="text-muted mb-0"><?php echo sprintf(t('invoice_for_order'), e($payment['payment_id'])); ?></p>
      </div>
      <button class="btn btn-dark btn-sm" onclick="window.print();"><?php echo t('print_invoice'); ?></button>
    </div>

    <div class="customer-invoice-card">
      <div class="invoice-watermark" aria-hidden="true">
        <img src="<?php echo e($company['logo_url']); ?>" alt="">
      </div>

      <div class="invoice-header">
        <div class="invoice-brand">
          <img class="invoice-logo" src="<?php echo e($company['logo_url']); ?>" alt="<?php echo e($company['site_name']); ?>">
          <div>
            <h2><?php echo e($company['site_name']); ?></h2>
            <p>
              <?php if ($company['address'] !== ''): ?><?php echo nl2br(e($company['address'])); ?><br><?php endif; ?>
              <?php if ($company['phone'] !== ''): ?><?php echo t('phone'); ?>: <?php echo e($company['phone']); ?><br><?php endif; ?>
              <?php if ($company['email'] !== ''): ?><?php echo t('email'); ?>: <?php echo e($company['email']); ?><br><?php endif; ?>
              <?php if ($company['vat_no'] !== ''): ?>VAT/PAN: <?php echo e($company['vat_no']); ?><?php endif; ?>
            </p>
          </div>
        </div>
        <div class="invoice-meta">
          <h3><?php echo t('invoice'); ?></h3>
          <p>
            <strong><?php echo t('invoice_number'); ?>:</strong> <?php echo e($payment['payment_id']); ?><br>
            <strong><?php echo t('date'); ?>:</strong> <?php echo e($payment['payment_date']); ?><br>
            <strong><?php echo t('status'); ?>:</strong> <?php echo e($payment['payment_status']); ?><br>
            <strong><?php echo t('shipping'); ?>:</strong> <?php echo e($payment['booking_status'] ?? $payment['shipping_status']); ?>
          </p>
        </div>
      </div>

      <div class="invoice-blocks">
        <div class="invoice-block">
          <h4><?php echo t('billing_details'); ?></h4>
          <p>
            <strong><?php echo e($payment['customer_name']); ?></strong><br>
            <?php echo e($payment['customer_email']); ?><br>
            <?php echo e($payment['customer_phone']); ?>
          </p>
        </div>
        <div class="invoice-block">
          <h4><?php echo t('service_address'); ?></h4>
          <p>
            <?php if (!empty($payment['service_address'])): ?>
              <?php echo nl2br(e($payment['service_address'])); ?><br>
            <?php else: ?>
              —
            <?php endif; ?>
            <?php if (!empty($payment['preferred_date']) || !empty($payment['preferred_time'])): ?>
              <?php echo t('preferred_date'); ?>: <?php echo e(trim(($payment['preferred_date'] ?? '') . ' ' . ($payment['preferred_time'] ?? ''))); ?>
            <?php endif; ?>
          </p>
        </div>
      </div>

      <div class="table-responsive invoice-table">
        <table class="table table-bordered align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th><?php echo t('product'); ?></th>
              <th><?php echo t('photo'); ?></th>
              <th><?php echo t('unit_price'); ?></th>
              <th><?php echo t('total'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if ($items) {
                $i = 1;
                foreach ($items as $item) {
                    $lineTotal = (float)$item['line_total'];
                    if ($lineTotal <= 0) {
                        $lineTotal = (float)$item['unit_price'];
                    }
            ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo e($item['product_name']); ?></td>
                <td class="text-center" style="width:90px;">
                  <img src="<?php echo e(getProductImage($item['p_featured_photo'])); ?>" alt="<?php echo e($item['product_name']); ?>" class="img-fluid rounded-3" style="height:60px; object-fit:cover; width:60px;">
                </td>
                <td>Rs. <?php echo number_format((float)$item['unit_price'], 2); ?></td>
                <td>Rs. <?php echo number_format($lineTotal, 2); ?></td>
              </tr>
            <?php }
            } else { ?>
              <tr>
                <td colspan="5" class="text-center"><?php echo t('no_products_found_for_invoice'); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="invoice-totals">
        <div class="qr-notes-box">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?php echo $qrPayload; ?>" alt="QR" class="qr-code">
        </div>
        <div class="totals-box">
          <div class="d-flex justify-content-between mb-2"><span><?php echo t('subtotal'); ?></span><strong>Rs. <?php echo number_format((float)($payment['subtotal'] ?? 0), 2); ?></strong></div>
          <div class="d-flex justify-content-between mb-2"><span><?php echo t('discount'); ?></span><strong>Rs. <?php echo number_format((float)($payment['discount_amount'] ?? 0), 2); ?></strong></div>
          <div class="d-flex justify-content-between mb-2"><span><?php echo t('vat'); ?></span><strong>Rs. <?php echo number_format((float)($payment['vat_amount'] ?? 0), 2); ?></strong></div>
          <div class="d-flex justify-content-between mb-2"><span><?php echo t('paid'); ?></span><strong>Rs. <?php echo number_format((float)($payment['paid_amount'] ?? 0), 2); ?></strong></div>
          <div class="d-flex justify-content-between border-top pt-2"><span class="fw-semibold"><?php echo t('grand_total'); ?></span><strong>Rs. <?php echo number_format((float)($payment['grand_total'] ?? 0), 2); ?></strong></div>
          <div class="d-flex justify-content-between mt-2"><span><?php echo t('due'); ?></span><strong>Rs. <?php echo number_format((float)($payment['due_amount'] ?? 0), 2); ?></strong></div>
        </div>
      </div>

      <div class="invoice-status-section">
        <?php if ($isPending): ?>
          <div class="status-message incomplete">
            Please pay within <strong><?php echo $dueDays; ?> days</strong> from the invoice date.
          </div>
        <?php else: ?>
          <div class="status-message completed"><?php echo e($company['footer_note']); ?></div>
        <?php endif; ?>
        <?php if (!empty($payment['notes'])): ?>
          <div class="mt-2 small text-muted"><strong><?php echo t('notes'); ?>:</strong> <?php echo nl2br(e($payment['notes'])); ?></div>
        <?php endif; ?>
        <?php if ($company['copyright'] !== ''): ?>
          <div class="mt-2 small text-muted"><?php echo e($company['copyright']); ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-3 no-print">
      <a href="<?php echo BASE_URL; ?>account/order-history.php" class="btn btn-outline-secondary btn-sm"><?php echo t('back_to_order_history'); ?></a>
    </div>
  </div>
</div>

<style>
.customer-invoice-card {
  position: relative;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  border-radius: 1rem;
  background: #fff;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}
.customer-invoice-card .invoice-watermark {
  display: none;
}
.customer-invoice-card .invoice-header {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #062a62 0%, #0b4f9c 100%);
  color: #fff;
}
.customer-invoice-card .invoice-brand {
  display: flex;
  gap: 0.75rem;
}
.customer-invoice-card .invoice-logo {
  width: 64px;
  height: 64px;
  object-fit: contain;
  background: #fff;
  border-radius: 0.75rem;
  padding: 0.35rem;
}
.customer-invoice-card .invoice-header h2,
.customer-invoice-card .invoice-header h3 {
  margin: 0;
  font-size: 1.25rem;
}
.customer-invoice-card .invoice-header p {
  margin: 0.35rem 0 0;
  font-size: 0.85rem;
  color: rgba(255,255,255,0.92);
  line-height: 1.4;
}
.customer-invoice-card .invoice-meta {
  text-align: right;
}
.customer-invoice-card .invoice-blocks {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
}
.customer-invoice-card .invoice-block h4 {
  font-size: 0.85rem;
  color: #062a62;
  margin-bottom: 0.35rem;
}
.customer-invoice-card .invoice-block p {
  margin: 0;
  font-size: 0.9rem;
  color: #374151;
}
.customer-invoice-card .invoice-table {
  padding: 1rem 1.25rem;
}
.customer-invoice-card .invoice-totals {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0 1.25rem 1rem;
}
.customer-invoice-card .qr-code {
  width: 90px;
  height: 90px;
  border: 1px solid #062a62;
  border-radius: 0.5rem;
  padding: 4px;
  background: #f8fafc;
}
.customer-invoice-card .totals-box {
  min-width: 240px;
}
.customer-invoice-card .invoice-status-section {
  padding: 1rem 1.25rem 1.25rem;
  border-top: 1px solid #e5e7eb;
}
.customer-invoice-card .status-message {
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
}
.customer-invoice-card .status-message.incomplete {
  background: #fef3c7;
  color: #92400e;
}
.customer-invoice-card .status-message.completed {
  background: #dcfce7;
  color: #065f46;
}
@media (max-width: 767px) {
  .customer-invoice-card .invoice-header,
  .customer-invoice-card .invoice-totals,
  .customer-invoice-card .invoice-blocks {
    grid-template-columns: 1fr;
    flex-direction: column;
  }
  .customer-invoice-card .invoice-meta {
    text-align: left;
  }
}
@media print {
  * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
  .no-print,
  .site-header,
  .site-footer,
  .back-to-top,
  .floating-wa,
  .breadcrumb,
  nav { display: none !important; }
  body { background: #fff !important; }
  .customer-invoice-card {
    box-shadow: none;
    border-radius: 0;
  }
  .customer-invoice-card .invoice-watermark {
    display: block !important;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(70%, 340px);
    opacity: 0.08;
    z-index: 0;
    pointer-events: none;
  }
  .customer-invoice-card .invoice-watermark img {
    width: 100%;
    height: auto;
  }
  .customer-invoice-card .invoice-header,
  .customer-invoice-card .invoice-blocks,
  .customer-invoice-card .invoice-table,
  .customer-invoice-card .invoice-totals,
  .customer-invoice-card .invoice-status-section {
    position: relative;
    z-index: 1;
  }
}
</style>
<?php include __DIR__ . '/../inc/footer.php'; ?>
