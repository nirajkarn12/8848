<?php
require_once __DIR__ . '/inc/functions.php';
$keyword = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$siteName = (string) getSiteSetting('site_name', SITE_NAME);
$pageTitle = $keyword !== '' ? (loadLang('search') . ': ' . $keyword) : loadLang('search');
$metaDescription = seoCleanText(
    ($keyword !== '' ? ('Search results for ' . $keyword . ' — ') : 'Search services — ') . $siteName,
    160
);
$metaKeywords = getHomeSeo()['keywords'];
include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('search'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

$sql = 'SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.p_is_featured, p.p_total_view, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1';
$params = [];
if ($keyword !== '') {
    $sql .= ' AND p.p_name LIKE ?';
    $params[] = '%' . $keyword . '%';
}

switch ($sort) {
    case 'featured': $sql .= ' ORDER BY p.p_is_featured DESC, p.p_id DESC'; break;
    case 'popular': $sql .= ' ORDER BY p.p_total_view DESC, p.p_id DESC'; break;
    case 'alphabetical': $sql .= ' ORDER BY p.p_name ASC'; break;
    default: $sql .= ' ORDER BY p.p_id DESC'; break;
}

$stmt = $pdo->prepare($sql . ' LIMIT 12');
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<div class="card card-hover p-4 mb-4">
  <form method="get" class="row g-3 align-items-end">
    <div class="col-md-6">
      <label class="form-label"><?php echo t('search_term'); ?></label>
      <input class="form-control" name="q" value="<?php echo e($keyword); ?>" placeholder="<?php echo t('type_service_name'); ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label"><?php echo t('sort'); ?></label>
      <select class="form-select" name="sort">
        <?php echo sortOptions($sort); ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-dark w-100"><?php echo t('search'); ?></button>
    </div>
  </form>
</div>
<div class="row g-4">
  <?php if ($products) { foreach ($products as $product) { include __DIR__ . '/pages/product-card.php'; } } else { ?><div class="col-12"><div class="alert alert-light rounded-4"><?php echo t('no_results_found'); ?></div></div><?php } ?>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
