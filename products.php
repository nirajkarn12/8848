<?php
require_once __DIR__ . '/inc/functions.php';
$sort = $_GET['sort'] ?? 'newest';
$search = trim($_GET['q'] ?? '');
$mcatId = (int)($_GET['mcat_id'] ?? $_GET['ecat_id'] ?? 0);

$siteName = (string) getSiteSetting('site_name', SITE_NAME);
$pageTitle = loadLang('shop');
$metaDescription = getHomeSeo()['description'];
$metaKeywords = getHomeSeo()['keywords'];
if ($mcatId) {
    $catStmt = $pdo->prepare('SELECT mcat_name FROM tbl_mid_category WHERE mcat_id = ? LIMIT 1');
    $catStmt->execute([$mcatId]);
    $catName = (string) ($catStmt->fetchColumn() ?: '');
    if ($catName !== '') {
        $pageTitle = $catName;
        $metaDescription = seoCleanText($catName . ' cleaning services — book with ' . $siteName . '. ' . loadLang('meta_home_description'), 160);
        $metaKeywords = seoPick($catName . ', cleaning service, ' . $siteName, $metaKeywords);
    }
} elseif ($search !== '') {
    $pageTitle = loadLang('search') . ': ' . $search;
    $metaDescription = seoCleanText('Search results for ' . $search . ' — ' . $siteName, 160);
}
include __DIR__ . '/inc/header.php';
$limit = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$sql = 'SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.p_is_featured, p.p_total_view, p.ecat_id, p.p_featured_photo FROM tbl_product p WHERE p.p_is_active = 1';
$params = [];
if ($mcatId) {
    $sql .= ' AND p.ecat_id IN (SELECT ecat_id FROM tbl_end_category WHERE mcat_id = ?)';
    $params[] = $mcatId;
}
if ($search !== '') {
    $sql .= ' AND p.p_name LIKE ?';
    $params[] = '%' . $search . '%';
}

switch ($sort) {
    case 'featured': $sql .= ' ORDER BY p.p_is_featured DESC, p.p_id DESC'; break;
    case 'popular': $sql .= ' ORDER BY p.p_total_view DESC, p.p_id DESC'; break;
    case 'alphabetical': $sql .= ' ORDER BY p.p_name ASC'; break;
    default: $sql .= ' ORDER BY p.p_id DESC'; break;
}

$stmt = $pdo->prepare($sql . ' LIMIT ' . $limit . ' OFFSET ' . $offset);
$stmt->execute($params);
$products = $stmt->fetchAll();

$cntStmt = $pdo->prepare(str_replace('SELECT p.p_id, p.p_name, p.p_short_description, p.p_qty, p.p_is_featured, p.p_total_view, p.ecat_id, p.p_featured_photo FROM tbl_product p', 'SELECT COUNT(*) FROM tbl_product p', $sql));
$cntStmt->execute($params);
$totalProducts = (int)$cntStmt->fetchColumn();
$totalPages = (int)ceil($totalProducts / $limit);

$midCats = $pdo->query('SELECT mcat_id, mcat_name FROM tbl_mid_category ORDER BY mcat_name ASC')->fetchAll();

$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('shop'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>
<div class="section-head mb-4">
  <div>
    <div class="section-kicker"><?php echo t('shop'); ?></div>
    <h2 class="section-title"><?php echo t('shop_collection_title'); ?></h2>
    <p class="section-subtitle mb-0"><?php echo t('shop_collection_subtitle'); ?></p>
  </div>
  <div class="text-muted small"><?php echo sprintf(t('showing_products'), count($products), $totalProducts); ?></div>
</div>
<div class="row g-4">
  <div class="col-lg-3">
    <div class="card card-hover p-4">
      <h5 class="fw-bold mb-3"><?php echo t('filter'); ?></h5>
      <form method="get" class="d-grid gap-3">
        <input type="text" class="form-control" name="q" value="<?php echo e($search); ?>" placeholder="<?php echo t('search_products'); ?>">
        <select class="form-select" name="mcat_id">
          <option value="0"><?php echo t('all_categories'); ?></option>
          <?php foreach ($midCats as $cat) { ?>
            <option value="<?php echo (int)$cat['mcat_id']; ?>" <?php echo $mcatId === (int)$cat['mcat_id'] ? 'selected' : ''; ?>><?php echo e($cat['mcat_name']); ?></option>
          <?php } ?>
        </select>
        <select class="form-select" name="sort">
          <?php echo sortOptions($sort); ?>
        </select>
        <button class="btn btn-dark"><?php echo t('apply'); ?></button>
      </form>
    </div>
  </div>
  <div class="col-lg-9">
    <div class="row g-4">
      <?php if ($products) { foreach ($products as $product) { include __DIR__ . '/pages/product-card.php'; } } else { ?>
        <div class="col-12"><div class="alert alert-light rounded-4"><?php echo t('no_products_found'); ?></div></div>
      <?php } ?>
    </div>
    <?php if ($totalPages > 1) { ?>
      <nav class="mt-5">
        <ul class="pagination justify-content-center">
          <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="products.php?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&ecat_id=<?php echo $ecatId; ?>&sort=<?php echo urlencode($sort); ?>"><?php echo $i; ?></a></li>
          <?php } ?>
        </ul>
      </nav>
    <?php } ?>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
