<?php
require_once __DIR__ . '/inc/functions.php';

$faqSeo = getStaticPageSeo('faq');
$pageTitle = $faqSeo['title'];
$metaKeywords = $faqSeo['keywords'];
$metaDescription = $faqSeo['description'];

$pageRow = $pdo->query('SELECT faq_title, faq_banner FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
$faqBanner = trim((string) ($pageRow['faq_banner'] ?? ''));
$faqBannerUrl = '';
if ($faqBanner !== '' && is_file(__DIR__ . '/assets/uploads/' . $faqBanner)) {
    $faqBannerUrl = getProductImage($faqBanner);
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('faqs'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

if ($faqBannerUrl !== '') {
    echo '<section class="page-banner" style="background-image:url(\'' . e($faqBannerUrl) . '\');">';
    echo '<div class="overlay"></div>';
    echo '<div class="container py-5 position-relative"><h1 class="mb-0 text-white text-center">' . e($pageRow['faq_title'] ?? $pageTitle) . '</h1></div>';
    echo '</section>';
}

$faqs = $pdo->query('SELECT faq_id, faq_title, faq_content FROM tbl_faq ORDER BY faq_id ASC')->fetchAll();
?>
<section class="section-block py-5">
  <div class="container">
    <?php if ($faqs) { ?>
      <div class="accordion faq-accordion" id="faqAccordion">
        <?php foreach ($faqs as $faq) { ?>
          <div class="accordion-item border-0 rounded-4 shadow-sm mb-3 overflow-hidden">
            <h2 class="accordion-header" id="faqHeading<?php echo (int)$faq['faq_id']; ?>">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?php echo (int)$faq['faq_id']; ?>" aria-expanded="false" aria-controls="faqCollapse<?php echo (int)$faq['faq_id']; ?>">
                <span class="faq-question-icon"><i class="fa fa-question-circle"></i></span>
                <?php echo e($faq['faq_title']); ?>
              </button>
            </h2>
            <div id="faqCollapse<?php echo (int)$faq['faq_id']; ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body faq-answer">
                <div class="faq-answer-text rich-content"><?php echo renderRichHtml($faq['faq_content']); ?></div>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="alert alert-light rounded-4"><?php echo t('no_faqs_yet'); ?></div>
    <?php } ?>
  </div>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
