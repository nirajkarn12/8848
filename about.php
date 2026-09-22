<?php
require_once __DIR__ . '/inc/functions.php';

$aboutSeo = getStaticPageSeo('about');
$pageTitle = $aboutSeo['title'];
$metaKeywords = $aboutSeo['keywords'];
$metaDescription = $aboutSeo['description'];

$aboutPage = $pdo->query('SELECT about_title, about_content, about_banner FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
if ($metaDescription === getHomeSeo()['description'] && !empty($aboutPage['about_content'])) {
    $metaDescription = seoCleanText($aboutPage['about_content'], 160);
}

$aboutBanner = trim((string) ($aboutPage['about_banner'] ?? ''));
$aboutBannerUrl = '';
if ($aboutBanner !== '' && is_file(__DIR__ . '/assets/uploads/' . $aboutBanner)) {
    $aboutBannerUrl = getProductImage($aboutBanner);
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('about'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

if ($aboutBannerUrl !== '') {
    echo '<section class="page-banner" style="background-image:url(\'' . e($aboutBannerUrl) . '\');">';
    echo '<div class="overlay"></div>';
    echo '<div class="container py-5 position-relative"><h1 class="mb-0 text-white text-center">' . e($aboutPage['about_title'] ?? $pageTitle) . '</h1></div>';
    echo '</section>';
}

$aboutCompact = false;
include __DIR__ . '/inc/partials/about-section.php';

include __DIR__ . '/inc/footer.php';
?>
