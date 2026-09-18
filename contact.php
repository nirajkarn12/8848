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
      <form method="post" class="d-grid gap-3" id="contactForm">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
        <input type="hidden" name="contact_form" value="1">
        <input class="form-control" name="contact_name" placeholder="<?php echo t('your_name'); ?>" required>
        <input class="form-control" type="email" name="contact_email" placeholder="<?php echo t('email_address'); ?>" required>
        <input class="form-control" name="contact_subject" placeholder="<?php echo t('subject'); ?>">

        <?php
        $referralCode = trim((string) ($_GET['referral'] ?? ''));
        $hasPromoCode = $referralCode !== '' || (!empty($_POST['has_promo_code']) && $_POST['has_promo_code'] == '1');
        ?>
        <div class="d-grid gap-2">
          <label class="form-label mb-0">Do you have a promo code?</label>
          <div class="d-flex gap-4">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="has_promo_code" id="has_promo_code_yes" value="1" <?php echo $hasPromoCode ? 'checked' : ''; ?>>
              <label class="form-check-label" for="has_promo_code_yes">Yes</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="has_promo_code" id="has_promo_code_no" value="0" <?php echo !$hasPromoCode ? 'checked' : ''; ?>>
              <label class="form-check-label" for="has_promo_code_no">No</label>
            </div>
          </div>
        </div>

        <div id="promo-code-field" class="card border-0 bg-light p-3" <?php echo $hasPromoCode ? '' : 'style="display:none;"'; ?>>
          <label class="form-label fw-semibold mb-2" for="promo_code">Promo code</label>
          <input class="form-control" id="promo_code" name="promo_code" value="<?php echo e($referralCode); ?>" placeholder="<?php echo t('promo_code_optional'); ?>" <?php echo $hasPromoCode ? 'required' : ''; ?>>
        </div>

        <textarea class="form-control" name="contact_message" rows="4" placeholder="<?php echo t('message'); ?>" required></textarea>
        <button class="btn btn-dark" type="submit"><?php echo t('send_message'); ?></button>
      </form>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const form = document.getElementById('contactForm');
          if (!form) return;

          const yesRadio = document.getElementById('has_promo_code_yes');
          const noRadio = document.getElementById('has_promo_code_no');
          const promoBlock = document.getElementById('promo-code-field');
          const promoInput = document.getElementById('promo_code');

          function togglePromoCodeField() {
            const show = yesRadio.checked;
            promoBlock.style.display = show ? 'block' : 'none';
            promoInput.required = show;

            if (!show) {
              promoInput.value = '';
            }
          }

          yesRadio.addEventListener('change', togglePromoCodeField);
          noRadio.addEventListener('change', togglePromoCodeField);
          togglePromoCodeField();
        });
      </script>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <h3 class="fw-bold mb-3"><?php echo t('company_information'); ?></h3>
      <p class="text-muted mb-3"><?php echo e($settings['contact_address'] ?? ''); ?></p>
      <p class="mb-2"><i class="fa fa-phone me-2"></i><?php echo e($settings['contact_phone'] ?? ''); ?></p>
      <p class="mb-2"><i class="fa fa-envelope me-2"></i><?php echo e($settings['contact_email'] ?? ''); ?></p>
      <div class="mt-3 map-shell">
        <?php echo !empty($settings['contact_map_iframe']) ? $settings['contact_map_iframe'] : '<iframe loading="lazy" title="' . e(loadLang('store_location')) . '" src="https://www.google.com/maps?q=auckland,New Zealand&output=embed"></iframe>'; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
