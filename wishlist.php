<?php
require_once __DIR__ . '/inc/functions.php';
$pageTitle = loadLang('wishlist');

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $productId = (int)($_GET['id'] ?? 0);
    if ($productId) {
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }
        if (!in_array($productId, $_SESSION['wishlist'], true)) {
            $_SESSION['wishlist'][] = $productId;
        }
        setFlash('success', loadLang('added_to_wishlist'));
    }
    header('Location: wishlist.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $productId = (int)($_GET['id'] ?? 0);
    if ($productId && isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = array_values(array_filter($_SESSION['wishlist'], fn($id) => (int)$id !== $productId));
        setFlash('success', loadLang('removed_from_wishlist'));
    }
    header('Location: wishlist.php');
    exit;
}

include __DIR__ . '/inc/header.php';
$breadcrumbs = [
    ['label' => t('home'), 'url' => BASE_URL],
    ['label' => t('wishlist'), 'url' => '']
];
echo renderBreadcrumbs($breadcrumbs);
$products = [];
if (!empty($_SESSION['wishlist'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['wishlist']), '?'));
    $stmt = $pdo->prepare('SELECT p_id, p_name, p_short_description, p_featured_photo, p_qty, ecat_id FROM tbl_product WHERE p_id IN (' . $placeholders . ') AND p_is_active = 1');
    $stmt->execute($_SESSION['wishlist']);
    $products = $stmt->fetchAll();
}
?>
<div class="row g-4">
  <?php if ($products) { foreach ($products as $product) { include __DIR__ . '/pages/product-card.php'; } } else { ?>
    <div class="col-12"><div class="alert alert-light rounded-4"><?php echo t('wishlist_empty'); ?></div></div>
  <?php } ?>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
