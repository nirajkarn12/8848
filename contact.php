<?php
require_once __DIR__ . '/inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['contact_form'])) {
    handleContactFormSubmission(BASE_URL . 'contact.php');
}

$contactSeo = getStaticPageSeo('contact');
$pageTitle = $contactSeo['title'];
$metaKeywords = $contactSeo['keywords'];
$metaDescription = $contactSeo['description'];

$settings = $pdo->query('SELECT * FROM tbl_settings LIMIT 1')->fetch();
if ($metaDescription === '' || $metaDescription === getHomeSeo()['description']) {
    $bits = array_filter([
        loadLang('contact_intro'),
        $settings['contact_address'] ?? '',
        $settings['contact_phone'] ?? '',
        $settings['contact_email'] ?? '',
    ]);
    $metaDescription = seoCleanText(implode(' ', $bits), 160);
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('contact'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
echo renderFlash();
?>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('contact_us'); ?></h3>
      <p class="text-muted"><?php echo t('contact_intro'); ?></p>
      <form method="post" class="d-grid gap-3">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <input type="hidden" name="contact_form" value="1">
        <input class="form-control" name="contact_name" placeholder="<?php echo t('your_name'); ?>" required>
        <input class="form-control" type="email" name="contact_email" placeholder="<?php echo t('email_address'); ?>" required>
        <input class="form-control" name="contact_subject" placeholder="<?php echo t('subject'); ?>">
        <textarea class="form-control" name="contact_message" rows="4" placeholder="<?php echo t('message'); ?>" required></textarea>
        <button class="btn btn-dark" type="submit"><?php echo t('send_message'); ?></button>
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
        <?php echo !empty($settings['contact_map_iframe']) ? $settings['contact_map_iframe'] : '<iframe loading="lazy" title="' . e(loadLang('store_location')) . '" src="https://www.google.com/maps?q=Kathmandu,Nepal&output=embed"></iframe>'; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
