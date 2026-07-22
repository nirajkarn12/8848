<?php
require_once __DIR__ . '/inc/functions.php';

$productId = (int)($_GET['id'] ?? 0);
if (!$productId) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM tbl_product WHERE p_id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: products.php');
    exit;
}

$pdo->prepare('UPDATE tbl_product SET p_total_view = p_total_view + 1 WHERE p_id = ?')->execute([$productId]);

$gallery = getProductGallery($productId);
$category = getCategoryName($product['ecat_id']);

$related = $pdo->prepare('SELECT p_id, p_name, p_featured_photo, p_short_description, p_qty, ecat_id 
                          FROM tbl_product 
                          WHERE p_is_active = 1 AND ecat_id = ? AND p_id != ? 
                          ORDER BY p_id DESC LIMIT 4');
$related->execute([$product['ecat_id'], $productId]);
$relatedProducts = $related->fetchAll();

$siteName = (string) getSiteSetting('site_name', SITE_NAME);
$pageTitle = $product['p_name'];
$metaDescription = seoPick(
    $product['p_short_description'] ?? '',
    ($product['p_name'] . ' — ' . loadLang('book_now') . ' with ' . $siteName),
    160
);
$metaKeywords = seoPick(
    implode(', ', array_filter([
        $product['p_name'],
        is_array($category) ? ($category['mcat_name'] ?? '') : '',
        'Kathmandu',
        'Nepal',
        '8848 Cleaning Service',
        is_array($category) ? ($category['tcat_name'] ?? '') : '',
        'cleaning service',
        $siteName,
    ])),
    getHomeSeo()['keywords']
);
$ogImage = $product['p_featured_photo'] ?? '';
$ogImageAlt = $product['p_name'];
include __DIR__ . '/inc/header.php';

$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('shop'), 'url' => BASE_URL . 'products.php'],
    ['label' => $product['p_name'], 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
?>

<div class="row g-5">
  <div class="col-lg-6">
    <div class="card card-hover p-3">
      <a href="<?php echo e(getProductImage($product['p_featured_photo'])); ?>" data-fancybox="product-gallery" data-caption="<?php echo e($product['p_name']); ?>">
        <img src="<?php echo getProductImage($product['p_featured_photo']); ?>"
             alt="<?php echo e($product['p_name']); ?>"
             class="img-fluid rounded-4 mb-3"
             style="height:450px; object-fit:cover; width:100%;">
      </a>
      <div class="row g-3">
        <?php foreach ($gallery as $photo) { ?>
          <div class="col-3">
            <a href="<?php echo e($photo['photo']); ?>" data-fancybox="product-gallery" data-caption="<?php echo e($product['p_name']); ?>">
              <img src="<?php echo e($photo['photo']); ?>"
                   alt="<?php echo e($product['p_name']); ?>"
                   class="img-fluid rounded-3 gallery-thumb"
                   style="height:120px; object-fit:cover; width:100%; cursor:pointer;">
            </a>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card card-hover p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="badge soft-pill"><?php echo e($category['ecat_name'] ?? t('categories')); ?></span>
        <?php if (!empty($product['p_is_featured'])) { ?><span class="badge soft-pill"><?php echo t('featured'); ?></span><?php } ?>
      </div>
      <h1 class="fw-bold mb-3"><?php echo e($product['p_name']); ?></h1>
      <div class="text-muted mb-4 rich-content"><?php echo renderRichHtml($product['p_short_description']); ?></div>
      <div class="mb-4">
        <div class="fw-semibold mb-2"><?php echo t('availability'); ?></div>
        <div class="<?php echo (int)$product['p_qty'] > 0 ? 'text-success' : 'text-warning'; ?>">
          <?php echo (int)$product['p_qty'] > 0 ? t('in_stock') : t('out_of_stock'); ?>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="book-service.php?service=<?php echo (int)$productId; ?>" class="btn btn-dark"><?php echo t('book_service'); ?></a>
        <a href="cart.php?action=add&id=<?php echo (int)$productId; ?>&qty=1" class="btn btn-outline-dark"><?php echo t('add_to_cart'); ?></a>
      </div>
      <div class="border-top pt-4">
        <p class="mb-0"><strong><?php echo t('categories'); ?>:</strong> <?php echo e($category['ecat_name'] ?? ''); ?></p>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mt-3">
  <div class="col-12">
    <div class="card card-hover p-4">
      <h4 class="fw-bold mb-3"><?php echo t('service_description'); ?></h4>
      <div class="text-muted rich-content"><?php echo renderRichHtml($product['p_description'], '<p>No description available.</p>'); ?></div>
      <h4 class="fw-bold mt-4 mb-3"><?php echo t('service_features'); ?></h4>
      <div class="text-muted rich-content"><?php echo renderRichHtml($product['p_feature'], '<p>No highlights available.</p>'); ?></div>
    </div>
  </div>
</div>

<?php if ($relatedProducts) { ?>
<div class="mt-5">
    <div class="section-title"><?php echo t('related_services'); ?></div>
    <div class="row g-4">
        <?php
        $currentProduct = $product;
        foreach ($relatedProducts as $product) {
            include __DIR__ . '/pages/product-card.php';
        }
        $product = $currentProduct;
        ?>
    </div>
</div>
<?php } ?>

<?php include __DIR__ . '/inc/footer.php'; ?>
