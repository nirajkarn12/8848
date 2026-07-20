<?php
require_once __DIR__ . '/../inc/functions.php';
$productId = (int)($_GET['id'] ?? 0);
if (!$productId) { echo '<div class="alert alert-danger">' . t('invalid_product') . '</div>'; exit; }
$stmt = $pdo->prepare('SELECT * FROM tbl_product WHERE p_id = ? LIMIT 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) { echo '<div class="alert alert-danger">' . t('product_not_found') . '</div>'; exit; }
$category = getCategoryName($product['ecat_id']);
$imageUrl = getProductImage($product['p_featured_photo'] ?? '');
?>
<div class="row g-4 align-items-center">
  <div class="col-md-6">
    <div class="quick-view-media">
      <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($product['p_name']); ?>" class="img-fluid" onerror="this.onerror=null;this.src='<?php echo e(ASSET_URL . 'images/placeholder.png'); ?>';">
    </div>
  </div>
  <div class="col-md-6">
    <h4 class="fw-bold mb-2"><?php echo e($product['p_name']); ?></h4>
    <p class="text-muted mb-3"><?php echo e(excerpt($product['p_short_description'], 140)); ?></p>
    <p class="mb-2"><strong><?php echo e(t('categories')); ?>:</strong> <?php echo e($category['ecat_name'] ?? ''); ?></p>
    <p class="mb-3"><strong><?php echo e(t('availability')); ?>:</strong> <?php echo (int)$product['p_qty'] > 0 ? e(t('in_stock')) : e(t('out_of_stock')); ?></p>
    <a href="product.php?id=<?php echo (int)$productId; ?>" class="btn btn-dark"><?php echo t('view_details'); ?></a>
  </div>
</div>
