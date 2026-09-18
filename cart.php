<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('cart');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: cart.php');
        exit;
    }

    if ($_POST['action'] === 'update') {
        foreach (($_POST['notes'] ?? []) as $productId => $note) {
            $id = (int)$productId;
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = 1;
                $_SESSION['cart'][$id]['notes'] = trim((string)$note);
            }
        }
        setFlash('success', loadLang('booking_updated'));
    }

    if ($_POST['action'] === 'clear') {
        unset($_SESSION['cart']);
        setFlash('success', loadLang('booking_cleared'));
    }

    header('Location: cart.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        setFlash('success', loadLang('service_removed_from_booking'));
    }
    header('Location: cart.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $id = (int)($_GET['id'] ?? 0);
    $qty = max(1, (int)($_GET['qty'] ?? 1));
    $redirect = trim((string)($_GET['redirect'] ?? 'cart.php'));
    if ($id) {
        $stmt = $pdo->prepare('SELECT p_id, p_name, p_featured_photo FROM tbl_product WHERE p_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            // Cleaning bookings are always one visit per selected service
            $_SESSION['cart'][$id] = ['product_id' => $product['p_id'], 'product_name' => $product['p_name'], 'photo' => $product['p_featured_photo'], 'quantity' => 1, 'notes' => $_SESSION['cart'][$id]['notes'] ?? ''];
            setFlash('success', loadLang('added_to_booking'));
        }
    }
    $redirectUrl = $redirect !== '' ? $redirect : 'cart.php';
    header('Location: ' . $redirectUrl);
    exit;
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('cart'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartItems[] = $item;
    }
}
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><?php echo t('your_booking'); ?></h3>
        <form method="post">
          <input type="hidden" name="action" value="clear">
          <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
          <button class="btn btn-outline-secondary btn-sm"><?php echo t('clear_booking'); ?></button>
        </form>
      </div>
      <?php if ($cartItems) { ?>
      <form method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr><th><?php echo t('product'); ?></th><th><?php echo t('notes'); ?></th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item) { ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?php echo getProductImage($item['photo']); ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:0.75rem;">
                      <div>
                        <div class="fw-semibold"><?php echo e($item['product_name']); ?></div>
                        <div class="text-muted small"><?php echo t('cleaning_service'); ?></div>
                      </div>
                    </div>
                  </td>
                  <td><input type="text" class="form-control" name="notes[<?php echo (int)$item['product_id']; ?>]" value="<?php echo e($item['notes']); ?>" placeholder="<?php echo t('any_request'); ?>"></td>
                  <td><a href="cart.php?action=remove&id=<?php echo (int)$item['product_id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></a></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between mt-4">
          <a href="products.php" class="btn btn-outline-dark"><?php echo t('continue_browsing'); ?></a>
          <button class="btn btn-dark"><?php echo t('update_booking'); ?></button>
        </div>
      </form>
      <?php } else { ?>
        <div class="alert alert-light rounded-4"><?php echo t('booking_empty'); ?></div>
      <?php } ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('proceed_booking'); ?></h4>
      <p class="text-muted"><?php echo t('confirm_schedule_text'); ?></p>
      <a href="checkout.php" class="btn btn-dark w-100 <?php echo empty($cartItems) ? 'disabled' : ''; ?>"><?php echo t('proceed_booking'); ?></a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
