<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = loadLang('compare');

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $productId = (int)($_GET['id'] ?? 0);
    if ($productId) {
        if (!isset($_SESSION['compare'])) {
            $_SESSION['compare'] = [];
        }
        if (!in_array($productId, $_SESSION['compare'], true) && count($_SESSION['compare']) < 4) {
            $_SESSION['compare'][] = $productId;
        }
        setFlash('success', loadLang('added_to_compare'));
    }
    header('Location: compare.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $productId = (int)($_GET['id'] ?? 0);
    if ($productId && isset($_SESSION['compare'])) {
        $_SESSION['compare'] = array_values(array_filter($_SESSION['compare'], fn($id) => (int)$id !== $productId));
        setFlash('success', loadLang('removed_from_compare'));
    }
    header('Location: compare.php');
    exit;
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('compare'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$products = [];
if (!empty($_SESSION['compare'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['compare']), '?'));
    $stmt = $pdo->prepare('SELECT p_id, p_name, p_short_description, p_featured_photo, p_qty, p_feature, p_description, p_condition, p_return_policy FROM tbl_product WHERE p_id IN (' . $placeholders . ')');
    $stmt->execute($_SESSION['compare']);
    $products = $stmt->fetchAll();
}
?>
<div class="row g-4">
  <?php if ($products) { foreach ($products as $product) { ?>
    <div class="col-lg-3">
      <div class="compare-card p-4">
        <img src="<?php echo getProductImage($product['p_featured_photo']); ?>" alt="" class="img-fluid rounded-3 mb-3" style="height:220px; object-fit:cover; width:100%;">
        <h5 class="fw-bold"><?php echo e($product['p_name']); ?></h5>
        <p class="text-muted small"><?php echo e(excerpt($product['p_short_description'], 120)); ?></p>
        <p class="mb-2"><strong><?php echo t('availability'); ?>:</strong> <?php echo (int)$product['p_qty'] > 0 ? t('in_stock') : t('out_of_stock'); ?></p>
        <p class="mb-2"><strong><?php echo t('features'); ?>:</strong> <?php echo e(excerpt($product['p_feature'], 140)); ?></p>
        <a href="compare.php?action=remove&id=<?php echo (int)$product['p_id']; ?>" class="btn btn-outline-danger btn-sm"><?php echo t('remove'); ?></a>
      </div>
    </div>
  <?php } } else { ?><div class="col-12"><div class="alert alert-light rounded-4"><?php echo t('compare_empty'); ?></div></div><?php } ?>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
