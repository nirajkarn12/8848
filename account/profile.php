<?php
require_once __DIR__ . '/../inc/functions.php';
ensureCustomerProfileColumns();
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
$profileMessage = '';
$profileMessageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $profileMessage = 'Invalid request. Please try again.';
        $profileMessageType = 'danger';
    } elseif (isset($_POST['update_profile'])) {
        $name = trim($_POST['cust_name'] ?? '');
        $phone = trim($_POST['cust_phone'] ?? '');
        $address = trim($_POST['cust_address'] ?? '');
        $city = trim($_POST['cust_city'] ?? '');
        $state = trim($_POST['cust_state'] ?? '');
        $zip = trim($_POST['cust_zip'] ?? '');
        if ($name === '' || $phone === '') {
            $profileMessage = 'Name and phone are required.';
            $profileMessageType = 'danger';
        } else {
            $update = $pdo->prepare('UPDATE tbl_customer SET cust_name = ?, cust_phone = ?, cust_address = ?, cust_city = ?, cust_state = ?, cust_zip = ? WHERE cust_id = ?');
            $update->execute([$name, $phone, $address, $city, $state, $zip, $customerId]);
            $_SESSION['customer_name'] = $name;
            $profileMessage = 'Profile details updated successfully.';
        }
    } elseif (isset($_POST['upload_profile_photo'])) {
        $file = $_FILES['profile_photo'] ?? null;
        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            $profileMessage = $uploadError === UPLOAD_ERR_NO_FILE ? 'Please choose an image.' : 'The image upload failed.';
            $profileMessageType = 'danger';
        } elseif ((int) $file['size'] > 4 * 1024 * 1024) {
            $profileMessage = 'Profile images must be 4 MB or smaller.';
            $profileMessageType = 'danger';
        } else {
            $imageInfo = @getimagesize($file['tmp_name']);
            $allowedTypes = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
            $imageType = (int) ($imageInfo[2] ?? 0);
            if (!$imageInfo || !isset($allowedTypes[$imageType])) {
                $profileMessage = 'Only JPG, PNG, and WEBP images are allowed.';
                $profileMessageType = 'danger';
            } else {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = 'customer-' . $customerId . '-' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$imageType];
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $profileMessage = 'The image could not be saved.';
                    $profileMessageType = 'danger';
                } else {
                    $oldPhoto = basename((string) ($customer['cust_photo'] ?? ''));
                    $update = $pdo->prepare('UPDATE tbl_customer SET cust_photo = ? WHERE cust_id = ?');
                    $update->execute([$filename, $customerId]);
                    if ($oldPhoto !== '' && is_file($uploadDir . $oldPhoto)) {
                        unlink($uploadDir . $oldPhoto);
                    }
                    $profileMessage = 'Profile photo updated successfully.';
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        if (!verifyPassword($currentPassword, $customer['cust_password'] ?? '')) {
            $profileMessage = 'Your current password is incorrect.';
            $profileMessageType = 'danger';
        } elseif (strlen($newPassword) < 6) {
            $profileMessage = 'Your new password must be at least 6 characters.';
            $profileMessageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $profileMessage = 'The new passwords do not match.';
            $profileMessageType = 'danger';
        } else {
            $update = $pdo->prepare('UPDATE tbl_customer SET cust_password = ? WHERE cust_id = ?');
            $update->execute([hashCustomerPassword($newPassword), $customerId]);
            $profileMessage = 'Password changed successfully.';
        }
    }
    $customer = currentCustomer();
}

$orderStatsStmt = $pdo->prepare('SELECT COUNT(*) AS total_orders, COALESCE(SUM(grand_total), 0) AS total_spent, SUM(CASE WHEN payment_status = "Completed" THEN 1 ELSE 0 END) AS completed_orders, SUM(CASE WHEN payment_status = "Pending" THEN 1 ELSE 0 END) AS pending_orders, MAX(payment_date) AS last_order_date FROM tbl_payment WHERE customer_id = ?');
$orderStatsStmt->execute([$customerId]);
$orderStats = $orderStatsStmt->fetch();
$ordersPerPage = 5;
$ordersPage = max(1, (int) ($_GET['orders_page'] ?? 1));
$orderCountStmt = $pdo->prepare('SELECT COUNT(*) FROM tbl_payment WHERE customer_id = ?');
$orderCountStmt->execute([$customerId]);
$totalOrders = (int) $orderCountStmt->fetchColumn();
$totalOrderPages = max(1, (int) ceil($totalOrders / $ordersPerPage));
$ordersPage = min($ordersPage, $totalOrderPages);
$ordersOffset = ($ordersPage - 1) * $ordersPerPage;
$recentOrdersStmt = $pdo->prepare('SELECT * FROM tbl_payment WHERE customer_id = ? ORDER BY id DESC LIMIT ' . $ordersPerPage . ' OFFSET ' . $ordersOffset);
$recentOrdersStmt->execute([$customerId]);
$recentOrders = $recentOrdersStmt->fetchAll();
ensureReferralTables();
$referralsStmt = $pdo->prepare('SELECT referral_code, referee_name, referee_email, status, created_at FROM tbl_referral WHERE referrer_customer_id = ? ORDER BY id DESC LIMIT 8');
$referralsStmt->execute([$customerId]);
$referrals = $referralsStmt->fetchAll();
include __DIR__ . '/../inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('profile'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="card card-hover p-4 mb-4 account-dashboard-hero">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
    <div class="d-flex align-items-center gap-3">
      <img src="<?php echo e(customerProfileImageUrl($customer['cust_photo'] ?? '')); ?>" alt="<?php echo e($customer['cust_name']); ?>" class="rounded-circle shadow-sm" style="width:72px;height:72px;object-fit:cover;">
      <div>
        <div class="small text-uppercase text-muted fw-semibold">Account dashboard</div>
        <h1 class="h3 fw-bold mb-1">Welcome back, <?php echo e($customer['cust_name']); ?></h1>
        <p class="text-muted mb-0">Manage your profile, bookings, and referrals in one place.</p>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="#profileEditModal" data-bs-toggle="modal" class="btn btn-dark"><i class="fa fa-user-edit me-2"></i>Edit profile</a>
      <a href="#passwordModal" data-bs-toggle="modal" class="btn btn-outline-secondary"><i class="fa fa-lock me-2"></i>Change password</a>
      <a href="#booking-history" class="btn btn-outline-dark"><i class="fa fa-calendar-check me-2"></i>Booking history</a>
      <a href="#referral-history" class="btn btn-outline-primary"><i class="fa fa-share-alt me-2"></i>Referral history</a>
    </div>
  </div>
</div>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('my_account'); ?></h4>
      <p class="mb-1"><strong><?php echo t('name'); ?>:</strong> <?php echo e($customer['cust_name']); ?></p>
      <p class="mb-1"><strong><?php echo t('email_address'); ?>:</strong> <?php echo e($customer['cust_email']); ?></p>
      <p class="mb-0"><strong><?php echo t('phone'); ?>:</strong> <?php echo e($customer['cust_phone']); ?></p>
      <?php $customerArea = trim(implode(', ', array_filter([$customer['cust_city'] ?? '', $customer['cust_state'] ?? '', $customer['cust_zip'] ?? '']))); ?>
      <p class="mb-0 mt-2"><strong>Address:</strong><br><?php echo nl2br(e($customer['cust_address'] ?? '')); ?><?php if ($customerArea !== ''): ?><br><?php echo e($customerArea); ?><?php endif; ?></p>
      <hr>
      <a href="<?php echo BASE_URL; ?>account/logout.php" class="btn btn-outline-danger btn-sm w-100"><i class="fa fa-sign-out-alt me-2"></i><?php echo t('logout'); ?></a>
    </div>
  </div>
  <div class="col-lg-8">
    <?php if ($profileMessage !== ''): ?><div class="alert alert-<?php echo e($profileMessageType); ?> rounded-4"><?php echo e($profileMessage); ?></div><?php endif; ?>
    <div class="card card-hover p-4 mb-4 d-none" id="profile-details">
      <h4 class="fw-bold mb-3">Profile details</h4>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-6"><label class="form-label">Full name *</label><input class="form-control" name="cust_name" value="<?php echo e($customer['cust_name']); ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="<?php echo e($customer['cust_email']); ?>" disabled></div>
        <div class="col-md-6"><label class="form-label">Phone *</label><input class="form-control" name="cust_phone" value="<?php echo e($customer['cust_phone']); ?>" required></div>
        <div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="cust_city" value="<?php echo e($customer['cust_city']); ?>"></div>
        <div class="col-md-6"><label class="form-label">State / region</label><input class="form-control" name="cust_state" value="<?php echo e($customer['cust_state']); ?>"></div>
        <div class="col-md-6"><label class="form-label">Postcode</label><input class="form-control" name="cust_zip" value="<?php echo e($customer['cust_zip']); ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="cust_address" rows="2"><?php echo e($customer['cust_address']); ?></textarea></div>
        <div class="col-12"><button class="btn btn-dark" name="update_profile" value="1">Save profile</button></div>
      </form>
    </div>
    <div class="card card-hover p-4 mb-4 d-none" id="profile-photo">
      <h4 class="fw-bold mb-3">Profile photo</h4>
      <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-md-8"><label class="form-label">Choose image</label><input class="form-control" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required><div class="form-text">JPG, PNG, or WEBP up to 4 MB.</div></div>
        <div class="col-md-4"><button class="btn btn-outline-dark w-100" name="upload_profile_photo" value="1">Upload photo</button></div>
      </form>
    </div>
    <div class="card card-hover p-4" id="booking-history">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold mb-1">Booking activity</h4>
          <p class="text-muted mb-0">Your account activity and booking history.</p>
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
            <div class="fw-semibold fs-4">NZ$ <?php echo number_format((float)$orderStats['total_spent'], 2); ?></div>
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
                  <div class="mt-2"><?php echo t('total'); ?>: NZ$ <?php echo number_format((float)($order['grand_total'] ?? 0), 2); ?></div>
                </div>
              </div>
            </div>
          <?php } ?>
        <?php } else { ?>
          <div class="alert alert-light rounded-4"><?php echo t('no_recent_orders_found'); ?></div>
        <?php } ?>
        <?php if ($totalOrderPages > 1): ?>
          <nav aria-label="Booking pages" class="mt-4"><ul class="pagination pagination-sm mb-0 justify-content-center">
            <li class="page-item <?php echo $ordersPage <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="?orders_page=<?php echo max(1, $ordersPage - 1); ?>">Previous</a></li>
            <?php for ($page = 1; $page <= $totalOrderPages; $page++): ?><li class="page-item <?php echo $page === $ordersPage ? 'active' : ''; ?>"><a class="page-link" href="?orders_page=<?php echo $page; ?>"><?php echo $page; ?></a></li><?php endfor; ?>
            <li class="page-item <?php echo $ordersPage >= $totalOrderPages ? 'disabled' : ''; ?>"><a class="page-link" href="?orders_page=<?php echo min($totalOrderPages, $ordersPage + 1); ?>">Next</a></li>
          </ul></nav>
        <?php endif; ?>
      </div>
    </div>
    <div class="card card-hover p-4 mt-4" id="referral-history">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="fw-bold mb-1">Referral history</h4>
          <p class="text-muted mb-0">Track the people you have referred and their status.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>referral-offer.php" class="btn btn-outline-primary btn-sm"><i class="fa fa-plus me-1"></i>New referral</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Referred person</th><th>Code</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($referrals as $referral): ?>
            <tr>
              <td><?php echo e($referral['referee_name']); ?><div class="small text-muted"><?php echo e($referral['referee_email']); ?></div></td>
              <td><code><?php echo e($referral['referral_code']); ?></code></td>
              <td><span class="badge text-bg-<?php echo $referral['status'] === 'Converted' ? 'success' : ($referral['status'] === 'Cancelled' ? 'secondary' : 'warning'); ?>"><?php echo e($referral['status']); ?></span></td>
              <td><?php echo e(date('M j, Y', strtotime($referral['created_at']))); ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$referrals): ?><tr><td colspan="4" class="text-muted">No referrals yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="profileEditModal" tabindex="-1" aria-labelledby="profileEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header"><h5 class="modal-title fw-bold" id="profileEditModalLabel">Edit profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body">
        <form method="post" class="row g-3">
          <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
          <div class="col-md-6"><label class="form-label">Full name *</label><input class="form-control" name="cust_name" value="<?php echo e($customer['cust_name']); ?>" required></div>
          <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="<?php echo e($customer['cust_email']); ?>" disabled></div>
          <div class="col-md-6"><label class="form-label">Phone *</label><input class="form-control" name="cust_phone" value="<?php echo e($customer['cust_phone']); ?>" required></div>
          <div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="cust_city" value="<?php echo e($customer['cust_city']); ?>"></div>
          <div class="col-md-6"><label class="form-label">State / region</label><input class="form-control" name="cust_state" value="<?php echo e($customer['cust_state']); ?>"></div>
          <div class="col-md-6"><label class="form-label">Postcode</label><input class="form-control" name="cust_zip" value="<?php echo e($customer['cust_zip']); ?>"></div>
          <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="cust_address" rows="2"><?php echo e($customer['cust_address']); ?></textarea></div>
          <div class="col-12 d-flex justify-content-end gap-2"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-dark" name="update_profile" value="1">Save profile</button></div>
        </form>
        <hr>
        <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
          <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
          <div class="col-md-8"><label class="form-label">Upload a new photo</label><input class="form-control" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required></div>
          <div class="col-md-4"><button class="btn btn-outline-dark w-100" name="upload_profile_photo" value="1">Upload photo</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header"><h5 class="modal-title fw-bold" id="passwordModalLabel">Change password</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form method="post">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
          <div class="mb-3"><label class="form-label">Current password</label><input class="form-control" type="password" name="current_password" autocomplete="current-password" required></div>
          <div class="mb-3"><label class="form-label">New password</label><input class="form-control" type="password" name="new_password" minlength="6" autocomplete="new-password" required><div class="form-text">Use at least 6 characters.</div></div>
          <div><label class="form-label">Confirm new password</label><input class="form-control" type="password" name="confirm_password" minlength="6" autocomplete="new-password" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-dark" name="change_password" value="1">Update password</button></div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../inc/footer.php'; ?>
