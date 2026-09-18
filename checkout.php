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

$loggedCustomer = currentCustomer();
$useBonusPoints = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
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
    $serviceLat = normalizeMapCoordinate($_POST['service_lat'] ?? null, -90, 90);
    $serviceLng = normalizeMapCoordinate($_POST['service_lng'] ?? null, -180, 180);
    $preferredDate = trim($_POST['preferred_date'] ?? '');
    $preferredTime = trim($_POST['preferred_time'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $accessNotes = trim($_POST['access_notes'] ?? '');
    $referralCode = strtoupper(trim($_POST['referral_code'] ?? ''));
    $promoCode = strtoupper(trim($_POST['promo_code'] ?? ''));
    $useBonusPoints = !empty($_POST['use_bonus_points']) && isLoggedIn();

    if ($customerName === '' || $phone === '' || $email === '' || $serviceAddress === '') {
        setFlash('danger', loadLang('booking_fields_required'));
        header('Location: checkout.php');
        exit;
    }

    if ($serviceLat === null || $serviceLng === null) {
        setFlash('danger', loadLang('map_pin_required'));
        header('Location: checkout.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('danger', loadLang('newsletter_invalid_email'));
        header('Location: checkout.php');
        exit;
    }

    $customerId = 0;
    if (isLoggedIn() && $loggedCustomer) {
        $customerId = (int) $loggedCustomer['cust_id'];
        if ($customerName === '') {
            $customerName = $loggedCustomer['cust_name'];
        }
        if ($email === '') {
            $email = $loggedCustomer['cust_email'];
        }
        if ($phone === '') {
            $phone = $loggedCustomer['cust_phone'];
        }
    } else {
        $existing = $pdo->prepare('SELECT cust_id FROM tbl_customer WHERE cust_email = ? LIMIT 1');
        $existing->execute([$email]);
        $existingRow = $existing->fetch();
        if ($existingRow) {
            $customerId = (int) $existingRow['cust_id'];
        }
    }

    $paymentId = 'ORD-' . date('YmdHis');
    $notesParts = [];
    if ($company !== '') {
        $notesParts[] = 'Company: ' . $company;
    }
    if ($remarks !== '') {
        $notesParts[] = $remarks;
    }
    if ($accessNotes !== '') {
        $notesParts[] = 'Access: ' . $accessNotes;
    }
    if ($province || $district || $municipality) {
        $notesParts[] = 'Area: ' . trim("{$province}, {$district}, {$municipality}", ' ,');
    }
    foreach ($_SESSION['cart'] as $cartItem) {
        $itemNote = trim((string) ($cartItem['notes'] ?? ''));
        if ($itemNote !== '') {
            $notesParts[] = ($cartItem['product_name'] ?? 'Service') . ': ' . $itemNote;
        }
    }
    $notes = implode("\n", $notesParts);

    $priceStmt = $pdo->prepare('SELECT p_current_price FROM tbl_product WHERE p_id = ? LIMIT 1');
    $lineItems = [];
    $subtotal = 0.0;
    foreach ($_SESSION['cart'] as $item) {
        $unitPrice = 0.0;
        $priceStmt->execute([(int) $item['product_id']]);
        $priceRow = $priceStmt->fetch();
        if ($priceRow) {
            $unitPrice = (float) $priceRow['p_current_price'];
        }
        $lineTotal = $unitPrice; // one visit per service
        $subtotal += $lineTotal;
        $lineItems[] = [
            'product_id' => (int) $item['product_id'],
            'product_name' => $item['product_name'],
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }
    $grandTotal = $subtotal;
    $discountType = 'percent';
    $discountValue = 0.0;
    $discountAmount = 0.0;
    $bonusPoints = 0;
    $pointsDiscount = 0.0;
    $pointsSpent = 0;
    $dueAmount = $grandTotal;
    $promoCodeDetails = null;
    $promoDiscountAmount = 0.0;

    try {
        ensureServiceLocationColumns($pdo);
        ensureReferralTables();
        $pdo->beginTransaction();

        $referral = null;
        if ($referralCode !== '') {
            $referralStmt = $pdo->prepare("SELECT * FROM tbl_referral WHERE referral_code = ? AND status = 'Pending' AND (LOWER(referee_email) = LOWER(?) OR REPLACE(REPLACE(REPLACE(referee_phone, ' ', ''), '-', ''), '+', '') = REPLACE(REPLACE(REPLACE(?, ' ', ''), '-', ''), '+', '')) ORDER BY id ASC LIMIT 1 FOR UPDATE");
            $referralStmt->execute([$referralCode, $email, $phone]);
            $referral = $referralStmt->fetch(PDO::FETCH_ASSOC);
            $matchesReferee = $referral && strcasecmp(trim((string) $referral['referee_email']), $email) === 0;
            if ($referral && !$matchesReferee) {
                $matchesReferee = preg_replace('/\D+/', '', (string) $referral['referee_phone']) === preg_replace('/\D+/', '', $phone);
            }
            if (!$referral || !$matchesReferee) {
                throw new RuntimeException('invalid_referral_code');
            }

            $referralSettings = getReferralSettings();
            if (!$referralSettings || (int) $referralSettings['is_active'] !== 1) {
                throw new RuntimeException('invalid_referral_code');
            }
            $discountType = $referral['discount_type'];
            $discountValue = (float) $referral['discount_value'];
            $discountAmount = referralDiscountAmount($subtotal, [
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'minimum_order_amount' => $referralSettings['minimum_order_amount'],
            ]);
            if ($discountAmount <= 0) {
                throw new RuntimeException('referral_minimum_not_met');
            }
            $bonusPoints = max(0, (int) ($referralSettings['bonus_points'] ?? 0));
            $discountType = $referral['discount_type'];
            $discountValue = (float) $referral['discount_value'];
            $notesParts[] = 'Referral code: ' . $referralCode;
        }

        if ($promoCode !== '') {
            $promoCodeDetails = getPromoCodeDetails($promoCode);
            if (!$promoCodeDetails) {
                throw new RuntimeException('invalid_promo_code');
            }
            $promoDiscountAmount = promoDiscountAmount($subtotal, [
                'discount_type' => $promoCodeDetails['discount_type'],
                'discount_value' => $promoCodeDetails['discount_value'],
                'minimum_order_amount' => $promoCodeDetails['minimum_order_amount'],
            ]);
            if ($promoDiscountAmount <= 0) {
                throw new RuntimeException('promo_minimum_not_met');
            }
            $discountAmount = round($discountAmount + $promoDiscountAmount, 2);
            if ($referralCode === '') {
                $discountType = $promoCodeDetails['discount_type'];
                $discountValue = (float) $promoCodeDetails['discount_value'];
            }
            $notesParts[] = 'Promo code: ' . $promoCode;
            $pdo->prepare('UPDATE tbl_promo_code SET used_count = used_count + 1, updated_at = NOW() WHERE code = ? AND is_active = 1')->execute([$promoCode]);
        }

        $grandTotal = round(max(0, $subtotal - $discountAmount), 2);
        $dueAmount = $grandTotal;

        if ($useBonusPoints) {
            $pointsSettings = getReferralSettings();
            $pointsRate = max(1, (int) ($pointsSettings['points_per_dollar'] ?? 100));
            $availablePoints = getReferralPoints($customerId);
            $pointsSpent = min($availablePoints, (int) floor($subtotal * $pointsRate));
            $pointsDiscount = round($pointsSpent / $pointsRate, 2);
            if ($pointsDiscount > 0) {
                $discountAmount = round($discountAmount + $pointsDiscount, 2);
                $grandTotal = round(max(0, $subtotal - $discountAmount), 2);
                $dueAmount = $grandTotal;
                $notesParts[] = 'Bonus points used: ' . $pointsSpent;
            }
        }

        $stmt = $pdo->prepare('INSERT INTO tbl_payment (customer_id, customer_name, customer_email, payment_date, txnid, paid_amount, card_number, card_cvv, card_month, card_year, bank_transaction_info, payment_method, payment_status, shipping_status, payment_id, subtotal, discount_type, discount_value, discount_amount, vat_percent, vat_amount, grand_total, due_amount, notes, created_at, updated_at, customer_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $customerId,
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
            $subtotal,
            $discountType,
            $discountValue,
            $discountAmount,
            0,
            0,
            $grandTotal,
            $dueAmount,
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
        if (paymentHasColumn($pdo, 'service_lat')) {
            $updates[] = 'service_lat = ?';
            $params[] = $serviceLat;
        }
        if (paymentHasColumn($pdo, 'service_lng')) {
            $updates[] = 'service_lng = ?';
            $params[] = $serviceLng;
        }
        if ($updates) {
            $params[] = $paymentIdDb;
            $pdo->prepare('UPDATE tbl_payment SET ' . implode(', ', $updates) . ' WHERE id = ?')->execute($params);
        }

        foreach ($lineItems as $item) {
            $orderStmt = $pdo->prepare('INSERT INTO tbl_order (product_id, product_name, size, color, quantity, unit_price, payment_id, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $orderStmt->execute([
                $item['product_id'],
                $item['product_name'],
                '',
                '',
                $item['quantity'],
                $item['unit_price'],
                $paymentId,
                $item['line_total'],
            ]);
        }

        if ($referral) {
            $pdo->prepare("UPDATE tbl_referral SET discount_amount = ?, awarded_points = ?, referee_customer_id = ?, payment_id = ? WHERE id = ? AND status = 'Pending'")
                ->execute([$discountAmount, $bonusPoints, $customerId ?: null, $paymentIdDb, $referral['id']]);
        }
        if ($pointsSpent > 0) {
            $pdo->prepare("INSERT INTO tbl_referral_points (customer_id, referral_id, points, reason, created_at) VALUES (?, NULL, ?, 'Points redeemed on service booking', NOW())")
                ->execute([$customerId, -$pointsSpent]);
        }

        $pdo->commit();
        unset($_SESSION['cart'], $_SESSION['booking_pref']);
        setFlash('success', loadLang('booking_submitted'));

        notifyAdminBooking($paymentId, $customerName, $email, $phone, $lineItems, $grandTotal, $serviceAddress, $preferredDate, $preferredTime, $remarks, $notes);

        if (isLoggedIn()) {
            header('Location: account/order-history.php');
        } else {
            header('Location: account/login.php?redirect=' . urlencode('account/order-history.php'));
        }
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof RuntimeException && in_array($e->getMessage(), ['invalid_referral_code', 'referral_minimum_not_met', 'invalid_promo_code', 'promo_minimum_not_met'], true)) {
            $msg = 'This booking code is invalid, expired, already used, or does not meet the minimum total.';
            if ($e->getMessage() === 'invalid_referral_code') {
                $msg = 'That referral code is invalid, already used, or does not match this customer.';
            } elseif ($e->getMessage() === 'referral_minimum_not_met') {
                $msg = 'This referral code requires a higher booking total.';
            } elseif ($e->getMessage() === 'invalid_promo_code') {
                $msg = 'That promo code is invalid, expired, or no longer active.';
            } elseif ($e->getMessage() === 'promo_minimum_not_met') {
                $msg = 'This promo code requires a higher booking total.';
            }
            setFlash('danger', $msg);
        } else {
            setFlash('danger', loadLang('booking_save_failed'));
        }
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
$defaultName = $pref['customer_name'] ?? ($loggedCustomer['cust_name'] ?? '');
$defaultPhone = $pref['phone'] ?? ($loggedCustomer['cust_phone'] ?? '');
$defaultEmail = $pref['email'] ?? ($loggedCustomer['cust_email'] ?? '');
$defaultAddress = $pref['service_address'] ?? ($loggedCustomer['cust_address'] ?? '');
$defaultLat = $pref['service_lat'] ?? '';
$defaultLng = $pref['service_lng'] ?? '';
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card card-hover p-4 booking-panel">
      <h3 class="fw-bold mb-4"><?php echo t('booking_details'); ?></h3>
      <?php if (!isLoggedIn()) { ?>
        <div class="alert alert-light border rounded-4 mb-4">
          <?php echo t('login_to_track_booking'); ?>
          <a href="<?php echo BASE_URL; ?>account/login.php?redirect=<?php echo urlencode('checkout.php'); ?>" class="fw-semibold text-decoration-none"><?php echo t('login_here'); ?></a>
        </div>
      <?php } ?>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-6"><label class="form-label"><?php echo t('customer_name'); ?></label><input class="form-control" name="customer_name" value="<?php echo e($defaultName); ?>" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('company_optional'); ?></label><input class="form-control" name="company"></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('phone'); ?></label><input class="form-control" name="phone" value="<?php echo e($defaultPhone); ?>" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('email_address'); ?></label><input class="form-control" type="email" name="email" value="<?php echo e($defaultEmail); ?>" required></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('referral_code'); ?> <span class="text-muted">(optional)</span></label><input class="form-control text-uppercase" name="referral_code" value="<?php echo e($_POST['referral_code'] ?? ($_GET['referral'] ?? '')); ?>" placeholder="8848-XXXXXXXX"></div>
        <div class="col-md-6"><label class="form-label">Promo code <span class="text-muted">(optional)</span></label><input class="form-control text-uppercase" name="promo_code" value="<?php echo e($_POST['promo_code'] ?? ($_GET['promo_code'] ?? '')); ?>" placeholder="SAVE15"></div>
        <?php if (isLoggedIn()): ?><div class="col-md-6 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="use_bonus_points" id="useBonusPoints" <?php echo $useBonusPoints ? 'checked' : ''; ?>><label class="form-check-label" for="useBonusPoints"><?php echo tf('use_bonus_points', number_format(getReferralPoints((int) $loggedCustomer['cust_id']))); ?></label></div></div><?php endif; ?>
        <div class="col-md-4"><label class="form-label"><?php echo t('province'); ?></label><input class="form-control" name="province"></div>
        <div class="col-md-4"><label class="form-label"><?php echo t('district'); ?></label><input class="form-control" name="district"></div>
        <div class="col-md-4"><label class="form-label"><?php echo t('municipality'); ?></label><input class="form-control" name="municipality"></div>
        <div class="col-12"><label class="form-label"><?php echo t('service_address'); ?></label><textarea class="form-control" id="service_address" name="service_address" rows="3" required placeholder="<?php echo t('service_address_placeholder'); ?>"><?php echo e($defaultAddress); ?></textarea></div>
        <div class="col-12">
          <label class="form-label fw-semibold"><?php echo t('map_pin_location'); ?></label>
          <?php echo renderServiceLocationPicker([
              'address_input' => '#service_address',
              'lat' => $defaultLat,
              'lng' => $defaultLng,
              'id' => 'checkoutMapPicker',
              'required' => true,
          ]); ?>
        </div>
        <div class="col-md-6"><label class="form-label"><?php echo t('preferred_date'); ?></label><input class="form-control" type="date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($pref['preferred_date'] ?? ''); ?>"></div>
        <div class="col-md-6"><label class="form-label"><?php echo t('preferred_time'); ?></label><input class="form-control" type="time" name="preferred_time" value="<?php echo e($pref['preferred_time'] ?? ''); ?>"></div>
        <div class="col-12"><label class="form-label"><?php echo t('notes'); ?></label><textarea class="form-control" name="remarks" rows="2" placeholder="<?php echo t('notes_placeholder'); ?>"></textarea></div>
        <div class="col-12"><label class="form-label"><?php echo t('access_notes'); ?></label><textarea class="form-control" name="access_notes" rows="2" placeholder="<?php echo t('access_notes_placeholder'); ?>"></textarea></div>
        <div class="col-12"><button class="btn btn-dark"><?php echo t('submit_booking'); ?></button></div>
      </form>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('booking_summary'); ?></h4>
      <ul class="list-group list-group-flush">
        <?php
        $summaryTotal = 0.0;
        foreach ($_SESSION['cart'] as $item) {
            $unit = 0.0;
            $priceLookup = $pdo->prepare('SELECT p_current_price FROM tbl_product WHERE p_id = ? LIMIT 1');
            $priceLookup->execute([(int) $item['product_id']]);
            $priceRow = $priceLookup->fetch();
            if ($priceRow) {
                $unit = (float) $priceRow['p_current_price'];
            }
            $summaryTotal += $unit;
        ?>
          <li class="list-group-item d-flex justify-content-between px-0">
            <span><?php echo e($item['product_name']); ?></span>
            <span>NZ$ <?php echo number_format($unit, 2); ?></span>
          </li>
        <?php } ?>
      </ul>
      <div class="d-flex justify-content-between fw-semibold mt-3">
        <span><?php echo t('total'); ?></span>
        <span>NZ$ <?php echo number_format($summaryTotal, 2); ?></span>
      </div>
            <?php if (!empty($_POST['referral_code']) || !empty($_GET['referral'])): ?>
                <div class="small text-muted mt-2"><?php echo t('referral_discount_pending'); ?></div>
            <?php endif; ?>
    </div>
  </div>
</div>
<?php echo serviceLocationAssets(); ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
