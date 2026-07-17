<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('contact');
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('contact'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch();
?>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('contact_us'); ?></h3>
      <p class="text-muted"><?php echo t('contact_intro'); ?></p>
      <form class="d-grid gap-3">
        <input class="form-control" placeholder="<?php echo t('your_name'); ?>">
        <input class="form-control" placeholder="<?php echo t('email_address'); ?>">
        <input class="form-control" placeholder="<?php echo t('subject'); ?>">
        <textarea class="form-control" rows="4" placeholder="<?php echo t('message'); ?>"></textarea>
        <button class="btn btn-dark"><?php echo t('send_message'); ?></button>
      </form>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('company_information'); ?></h3>
      <p class="text-muted mb-3"><?php echo e($settings['contact_address'] ?? ''); ?></p>
      <p class="mb-2"><i class="fa fa-phone me-2"></i><?php echo e($settings['contact_phone'] ?? ''); ?></p>
      <p class="mb-2"><i class="fa fa-envelope me-2"></i><?php echo e($settings['contact_email'] ?? ''); ?></p>
      <div class="mt-3 map-shell">
        <?php echo !empty($settings['contact_map_iframe']) ? $settings['contact_map_iframe'] : '<iframe loading="lazy" title="Store location" src="https://www.google.com/maps?q=Kathmandu,Nepal&output=embed"></iframe>'; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
