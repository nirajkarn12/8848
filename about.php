<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('about');
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('about'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

$aboutPage = $pdo->query('SELECT about_title, about_content, about_banner FROM tbl_page LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
$aboutCompact = false;
include __DIR__ . '/inc/partials/about-section.php';

include __DIR__ . '/inc/footer.php';
?>
