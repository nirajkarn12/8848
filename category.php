<?php
require_once __DIR__ . '/inc/functions.php';
$categoryId = (int)($_GET['id'] ?? 0);

if ($categoryId) {
    $stmt = $pdo->prepare('SELECT * FROM tbl_mid_category WHERE mcat_id = ? LIMIT 1');
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    if (!$category) {
        header('Location: products.php');
        exit;
    }
    $pageTitle = $category['mcat_name'];
    $metaDescription = seoCleanText(
        $category['mcat_name'] . ' cleaning services — ' .
        getSiteSetting('site_name', SITE_NAME) . '. ' . loadLang('meta_home_description'),
        160
    );
    $metaKeywords = seoPick(
        $category['mcat_name'] . ', cleaning service Kathmandu, ' . getSiteSetting('site_name', SITE_NAME) . ', Nepal',
        getHomeSeo()['keywords']
    );
} else {
    $pageTitle = loadLang('categories');
    $metaDescription = getHomeSeo()['description'];
    $metaKeywords = getHomeSeo()['keywords'];
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('categories'), 'url' => BASE_URL . 'products.php'],
    ['label' => $pageTitle, 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);

$sql = 'SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.p_is_featured, p.p_total_view, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1';
$params = [];
if ($categoryId) {
    $sql .= ' AND p.ecat_id IN (SELECT ecat_id FROM tbl_end_category WHERE mcat_id = ?)';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY p.p_id DESC LIMIT 12';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<div class="row g-4">
  <div class="col-lg-3">
    <div class="card card-hover p-4">
      <h5 class="fw-bold mb-3"><?php echo t('category'); ?></h5>
      <ul class="list-unstyled">
        <?php foreach (getTopCategories() as $top) { ?>
          <li class="mb-3"><strong><?php echo e($top['tcat_name']); ?></strong>
            <ul class="list-unstyled ms-3 mt-2">
              <?php foreach (getMidCategories($top['tcat_id']) as $mid) { ?>
                <li><a href="category.php?id=<?php echo (int)$mid['mcat_id']; ?>" class="text-decoration-none text-muted"><?php echo e($mid['mcat_name']); ?></a></li>
              <?php } ?>
            </ul>
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="col-lg-9">
    <div class="section-title"><?php echo e($pageTitle); ?></div>
    <div class="row g-4">
      <?php foreach ($products as $product) { include __DIR__ . '/pages/product-card.php'; } ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
