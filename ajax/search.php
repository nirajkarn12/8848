<?php
require_once __DIR__ . '/../inc/functions.php';
$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo '<div class="small text-muted p-2">' . t('type_at_least_two_chars') . '</div>';
    exit;
}
$stmt = $pdo->prepare('SELECT p_id, p_name, p_featured_photo FROM tbl_product WHERE p_is_active = 1 AND p_name LIKE ? LIMIT 5');
$stmt->execute(['%' . $q . '%']);
$products = $stmt->fetchAll();
if (!$products) {
    echo '<div class="small text-muted p-2">' . t('no_suggestions') . '</div>';
    exit;
}
foreach ($products as $product) {
    echo '<a href="' . BASE_URL . 'product.php?id=' . (int)$product['p_id'] . '" class="d-flex align-items-center gap-2 text-decoration-none text-dark p-2 rounded-3 hover-bg">';
    echo '<img src="' . getProductImage($product['p_featured_photo']) . '" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:0.5rem;">';
    echo '<span class="small">' . e($product['p_name']) . '</span>';
    echo '</a>';
}
