<?php
$productId = $product['p_id'];
$category = getCategoryName($product['ecat_id']);
$categoryName = $category['ecat_name'] ?? '';
$featuredBadge = !empty($product['p_is_featured']) ? '<span class="badge soft-pill">' . e(t('featured')) . '</span>' : '';
$availability = (int)$product['p_qty'] > 0
    ? '<span class="stock-pill in-stock"><i class="fa fa-check-circle"></i> ' . e(t('in_stock')) . '</span>'
    : '<span class="stock-pill out-stock">' . e(t('out_of_stock')) . '</span>';
?>
<div class="col-lg-4 col-md-6 col-sm-6 reveal">
  <div class="card card-hover product-card h-100">
    <div class="product-card-media">
      <img src="<?php echo getProductImage($product['p_featured_photo']); ?>" alt="<?php echo e($product['p_name']); ?>">
      <div class="product-card-overlay">
        <a href="product.php?id=<?php echo (int)$productId; ?>" class="action-pill view-pill"><?php echo t('view_details'); ?></a>
      </div>
    </div>
    <div class="card-body d-flex flex-column">
      <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
        <span class="badge soft-pill"><?php echo e($categoryName ?: 'Service'); ?></span>
        <?php echo $featuredBadge; ?>
      </div>
      <h5 class="fw-semibold mb-2"><?php echo e($product['p_name']); ?></h5>
      <p class="text-muted small mb-3"><?php echo e(excerpt($product['p_short_description'], 90)); ?></p>
      <div class="service-meta mb-3"><?php echo $availability; ?></div>
      <div class="mt-auto d-flex gap-2 flex-wrap align-items-center product-actions">
        <a href="book-service.php?service=<?php echo (int)$productId; ?>" class="btn btn-dark btn-sm flex-grow-1"><?php echo t('book_now'); ?></a>
        <a href="javascript:void(0);" class="btn btn-outline-dark btn-sm add-to-cart" data-product-id="<?php echo (int)$productId; ?>" title="<?php echo t('add_to_cart'); ?>"><i class="fa fa-plus"></i></a>
      </div>
    </div>
  </div>
</div>
