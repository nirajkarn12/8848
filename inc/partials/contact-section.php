<?php
/**
 * Homepage contact block: form + image from DB (tbl_page.contact_banner).
 */
global $pdo;

$pageRow = [];
try {
    $pageRow = $pdo->query('SELECT contact_title, contact_banner FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $pageRow = [];
}

$contactTitle = trim((string) ($pageRow['contact_title'] ?? ''));
if ($contactTitle === '') {
    $contactTitle = loadLang('contact_us');
}

$banner = trim((string) ($pageRow['contact_banner'] ?? ''));
$contactImage = getProductImage($banner !== '' ? $banner : 'placeholder.svg');
$bannerPath = __DIR__ . '/../../assets/uploads/' . $banner;
if ($banner !== '' && is_file($bannerPath)) {
    $contactImage .= (strpos($contactImage, '?') === false ? '?' : '&') . 'v=' . (int) filemtime($bannerPath);
}
?>
<section class="section-block home-contact-section" id="contact">
  <div class="section-head">
    <div>
      <div class="section-kicker"><?php echo t('contact'); ?></div>
      <h2 class="section-title"><?php echo e($contactTitle); ?></h2>
      <p class="section-subtitle"><?php echo t('contact_intro'); ?></p>
    </div>
  </div>

  <?php echo renderFlash(); ?>

  <div class="home-contact-grid">
    <div class="home-contact-form-wrap">
      <form method="post" action="<?php echo e(BASE_URL . 'index.php'); ?>" class="home-contact-form">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <input type="hidden" name="contact_form" value="1">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label"><?php echo t('your_name'); ?> <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="contact_name" required>
          </div>
          <div class="col-md-6">
            <label class="form-label"><?php echo t('email_address'); ?> <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="contact_email" required>
          </div>
          <div class="col-md-6">
            <label class="form-label"><?php echo t('phone'); ?></label>
            <input type="text" class="form-control" name="contact_phone">
          </div>
          <div class="col-md-6">
            <label class="form-label"><?php echo t('subject'); ?></label>
            <input type="text" class="form-control" name="contact_subject">
          </div>
          <div class="col-12">
            <label class="form-label"><?php echo t('message'); ?> <span class="text-danger">*</span></label>
            <textarea class="form-control" name="contact_message" rows="5" required></textarea>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-dark btn-lg"><?php echo t('send_message'); ?></button>
          </div>
        </div>
      </form>
    </div>

    <figure class="home-contact-media">
      <img src="<?php echo e($contactImage); ?>" alt="<?php echo e($contactTitle); ?>" loading="lazy">
    </figure>
  </div>
</section>
