<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = t('about');
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('about'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$page = $pdo->query('SELECT * FROM tbl_page LIMIT 1')->fetch();
?>
<div class="card card-hover p-4">
  <div class="section-kicker mb-2"><?php echo t('our_story'); ?></div>
  <h2 class="fw-bold mb-3"><?php echo t('about_koshi_supplier'); ?></h2>
  <p class="text-muted mb-4"><?php echo t('crafted_with_care'); ?></p>
  <div class="text-muted rich-content"><?php echo renderRichHtml($page['about_content'] ?? ''); ?></div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
