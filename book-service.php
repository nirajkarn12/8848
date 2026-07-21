<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('book_now');

$preselect = (int)($_GET['service'] ?? 0);
$services = $pdo->query('SELECT p_id, p_name, p_short_description, p_featured_photo, p_qty, ecat_id FROM tbl_product WHERE p_is_active = 1 ORDER BY p_is_featured DESC, p_name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', loadLang('invalid_request'));
        header('Location: book-service.php');
        exit;
    }

    $serviceId = (int)($_POST['service_id'] ?? 0);
    $serviceLat = normalizeMapCoordinate($_POST['service_lat'] ?? null, -90, 90);
    $serviceLng = normalizeMapCoordinate($_POST['service_lng'] ?? null, -180, 180);
    if ($serviceLat === null || $serviceLng === null) {
        setFlash('danger', loadLang('map_pin_required'));
        header('Location: book-service.php');
        exit;
    }
    if ($serviceId > 0) {
        $stmt = $pdo->prepare('SELECT p_id, p_name, p_featured_photo FROM tbl_product WHERE p_id = ? AND p_is_active = 1 LIMIT 1');
        $stmt->execute([$serviceId]);
        $product = $stmt->fetch();
        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $id = (int)$product['p_id'];
            $_SESSION['cart'][$id] = [
                'product_id' => $id,
                'product_name' => $product['p_name'],
                'photo' => $product['p_featured_photo'],
                'quantity' => 1,
                'notes' => trim($_POST['notes'] ?? ($_SESSION['cart'][$id]['notes'] ?? '')),
            ];
            $_SESSION['booking_pref'] = [
                'service_address' => trim($_POST['service_address'] ?? ''),
                'service_lat' => $serviceLat,
                'service_lng' => $serviceLng,
                'preferred_date' => trim($_POST['preferred_date'] ?? ''),
                'preferred_time' => trim($_POST['preferred_time'] ?? ''),
                'customer_name' => trim($_POST['customer_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
            ];
            setFlash('success', loadLang('service_added_complete_booking'));
            header('Location: checkout.php');
            exit;
        }
    }

    setFlash('danger', loadLang('choose_valid_service'));
    header('Location: book-service.php');
    exit;
}

$pref = $_SESSION['booking_pref'] ?? [];
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('book_now'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
echo renderFlash();
?>
<div class="book-page-head mb-4">
  <div class="section-kicker"><?php echo t('book_service'); ?></div>
  <h1 class="section-title mb-2"><?php echo t('book_now'); ?></h1>
  <p class="text-muted mb-0"><?php echo t('shop_collection_subtitle'); ?></p>
</div>

<div class="row g-4 align-items-start book-page-columns">
  <div class="col-lg-7">
    <div class="card card-hover p-4 booking-panel">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <div class="col-12">
          <label class="form-label"><?php echo t('product'); ?></label>
          <select class="form-select" name="service_id" required>
            <option value=""><?php echo t('select_service'); ?></option>
            <?php foreach ($services as $svc) { ?>
              <option value="<?php echo (int)$svc['p_id']; ?>" <?php echo $preselect === (int)$svc['p_id'] ? 'selected' : ''; ?>>
                <?php echo e($svc['p_name']); ?>
              </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label"><?php echo t('customer_name'); ?></label>
          <input class="form-control" name="customer_name" value="<?php echo e($pref['customer_name'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label"><?php echo t('phone'); ?></label>
          <input class="form-control" name="phone" value="<?php echo e($pref['phone'] ?? ''); ?>">
        </div>
        <div class="col-12">
          <label class="form-label"><?php echo t('email_address'); ?></label>
          <input class="form-control" type="email" name="email" value="<?php echo e($pref['email'] ?? ''); ?>">
        </div>
        <div class="col-12">
          <label class="form-label"><?php echo t('service_address'); ?></label>
          <textarea class="form-control" id="service_address" name="service_address" rows="3" required><?php echo e($pref['service_address'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold"><?php echo t('map_pin_location'); ?></label>
          <?php echo renderServiceLocationPicker([
              'address_input' => '#service_address',
              'lat' => $pref['service_lat'] ?? '',
              'lng' => $pref['service_lng'] ?? '',
              'id' => 'bookServiceMapPicker',
              'required' => true,
          ]); ?>
        </div>
        <div class="col-md-6">
          <label class="form-label"><?php echo t('preferred_date'); ?></label>
          <input class="form-control" type="date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($pref['preferred_date'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label"><?php echo t('preferred_time'); ?></label>
          <input class="form-control" type="time" name="preferred_time" value="<?php echo e($pref['preferred_time'] ?? ''); ?>">
        </div>
        <div class="col-12">
          <label class="form-label"><?php echo t('notes'); ?></label>
          <textarea class="form-control" name="notes" rows="2" placeholder="<?php echo t('notes_placeholder'); ?>"></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-dark btn-lg"><?php echo t('proceed_booking'); ?></button>
        </div>
      </form>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="book-page-sidebar">
    <div class="cta-band book-side-cta">
      <div class="cta-band-inner">
        <h2><?php echo t('cta_ready_title'); ?></h2>
        <p><?php echo t('cta_ready_text'); ?></p>
        <a href="products.php" class="btn btn-light btn-sm"><?php echo t('shop_collection'); ?></a>
      </div>
    </div>
    <div class="how-grid book-side-steps">
      <article class="how-card">
        <div class="how-step-num">01</div>
        <h3><?php echo t('how_step_1_title'); ?></h3>
        <p><?php echo t('how_step_1_text'); ?></p>
      </article>
      <article class="how-card">
        <div class="how-step-num">02</div>
        <h3><?php echo t('how_step_2_title'); ?></h3>
        <p><?php echo t('how_step_2_text'); ?></p>
      </article>
      <article class="how-card">
        <div class="how-step-num">03</div>
        <h3><?php echo t('how_step_3_title'); ?></h3>
        <p><?php echo t('how_step_3_text'); ?></p>
      </article>
    </div>
    </div>
  </div>
</div>
<?php echo serviceLocationAssets(); ?>
<?php include __DIR__ . '/inc/footer.php'; ?>
