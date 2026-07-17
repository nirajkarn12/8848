<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('proceed_booking');

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

function paymentHasColumn(PDO $pdo, string $column): bool
{
    static $cache = [];
    if (array_key_exists($column, $cache)) {
        return $cache[$column];
    }
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_payment LIKE " . $pdo->quote($column));
        $cache[$column] = $stmt && $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        $cache[$column] = false;
    }
    return $cache[$column];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
        header('Location: checkout.php');
        exit;
    }

    $customerName = trim($_POST['customer_name'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $municipality = trim($_POST['municipality'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $serviceAddress = trim($_POST['service_address'] ?? $address);
    $preferredDate = trim($_POST['preferred_date'] ?? '');
    $preferredTime = trim($_POST['preferred_time'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $accessNotes = trim($_POST['access_notes'] ?? '');

    if ($customerName === '' || $phone === '' || $email === '' || $serviceAddress === '') {
        setFlash('danger', 'Please complete the required booking fields.');
        header('Location: checkout.php');
        exit;
    }

    $paymentId = 'ORD-' . date('YmdHis');
    $notes = trim($remarks . "\n" . $accessNotes);
    if ($company !== '') {
        $notes = "Company: {$company}\n" . $notes;
    }
    if ($province || $district || $municipality) {
        $notes .= "\nArea: " . trim("{$province}, {$district}, {$municipality}", ' ,');
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO tbl_payment (customer_id, customer_name, customer_email, payment_date, txnid, paid_amount, card_number, card_cvv, card_month, card_year, bank_transaction_info, payment_method, payment_status, shipping_status, payment_id, subtotal, discount_type, discount_value, discount_amount, vat_percent, vat_amount, grand_total, due_amount, notes, created_at, updated_at, customer_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            isLoggedIn() ? $_SESSION['customer_id'] : 0,
            $customerName,
            $email,
            date('Y-m-d H:i:s'),
            '',
            0,
            '',
            '',
            '',
            '',
            '',
            'enquiry',
            'Pending',
            'Pending',
            $paymentId,
            0,
            'percent',
            0,
            0,
            0,
            0,
            0,
            0,
            $notes,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            $phone,
        ]);
        $paymentIdDb = $pdo->lastInsertId();

        $updates = [];
        $params = [];
        if (paymentHasColumn($pdo, 'service_address')) {
            $updates[] = 'service_address = ?';
            $params[] = $serviceAddress;
        }
        if (paymentHasColumn($pdo, 'preferred_date')) {
            $updates[] = 'preferred_date = ?';
            $params[] = $preferredDate !== '' ? $preferredDate : null;
        }
        if (paymentHasColumn($pdo, 'preferred_time')) {
            $updates[] = 'preferred_time = ?';
            $params[] = $preferredTime !== '' ? $preferredTime : null;
        }
        if (paymentHasColumn($pdo, 'booking_status')) {
            $updates[] = "booking_status = 'Pending'";
        }
        if (paymentHasColumn($pdo, 'assignment_status')) {
            $updates[] = "assignment_status = 'Unassigned'";
        }
        if ($updates) {
            $params[] = $paymentIdDb;
            $pdo->prepare('UPDATE tbl_payment SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);
        }

        foreach ($_SESSION['cart'] as $item) {
            $orderStmt = $pdo->prepare('INSERT INTO tbl_order (product_id, product_name, size, color, quantity, unit_price, payment_id, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $orderStmt->execute([
                $item['product_id'],
                $item['product_name'],
                '',
                '',
                $item['quantity'],
                0,
                $paymentId,
                0,
            ]);
        }

        $pdo->commit();
        unset($_SESSION['cart'], $_SESSION['booking_pref']);
        setFlash('success', 'Your cleaning booking request has been submitted. We will confirm shortly.');
        header('Location: account/order-history.php');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        setFlash('danger', 'Could not save your booking right now.');
        header('Location: checkout.php');
        exit;
    }
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('cart'), 'url' => BASE_URL . 'cart.php'],
    ['label' => t('proceed_booking'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$pref = $_SESSION['booking_pref'] ?? [];
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card card-hover p-4 booking-panel">
      <h3 class="fw-bold mb-4"><?php echo t('booking_details'); ?></h3>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-6"><label class="form-label"><?php echo t('customer_name'); ?></label><input class="form-control" name="customer_name" value="<?php echo e($pref['customer_name'] ?? ''); ?>" required></div>
        <div class="col-md-6"><label class="form-label">Company (optional)</label><input class="form-control" name="company"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo e($pref['phone'] ?? ''); ?>" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('email_address'); ?></label><input class="form-control" type="email" name="email" value="<?php echo e($pref['email'] ?? ''); ?>" required></div>
        <div class="col-md-4"><label class="form-label">Province</label><input class="form-control" name="province"></div>
        <div class="col-md-4"><label class="form-label">District</label><input class="form-control" name="district"></div>
        <div class="col-md-4"><label class="form-label">Municipality</label><input class="form-control" name="municipality"></div>
        <div class="col-12"><label class="form-label"><?php echo t('service_address'); ?></label><textarea class="form-control" name="service_address" rows="3" required placeholder="Full address where cleaning should happen"><?php echo e($pref['service_address'] ?? ''); ?></textarea></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('preferred_date'); ?></label><input class="form-control" type="date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($pref['preferred_date'] ?? ''); ?>"></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('preferred_time'); ?></label><input class="form-control" type="time" name="preferred_time" value="<?php echo e($pref['preferred_time'] ?? ''); ?>"></div>
        <div class="col-12"><label class="form-label"><?php echo t('notes'); ?></label><textarea class="form-control" name="remarks" rows="2" placeholder="Rooms, pets, parking…"></textarea></div>
        <div class="col-12"><label class="form-label"><?php echo t('access_notes'); ?></label><textarea class="form-control" name="access_notes" rows="2" placeholder="Gate code, landmark, contact on site…"></textarea></div>
        <div class="col-12"><button class="btn btn-dark"><?php echo t('submit_booking'); ?></button></div>
      </form>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('booking_summary'); ?></h4>
      <ul class="list-group list-group-flush">
        <?php foreach ($_SESSION['cart'] as $item) { ?>
          <li class="list-group-item d-flex justify-content-between px-0">
            <span><?php echo e($item['product_name']); ?></span>
            <span>x<?php echo (int)$item['quantity']; ?></span>
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
