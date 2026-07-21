<?php
require_once __DIR__ . '/../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => loadLang('method_not_allowed')]);
    exit;
}

$action = $_POST['action'] ?? '';
if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId) {
        $stmt = $pdo->prepare('SELECT p_id, p_name, p_featured_photo FROM tbl_product WHERE p_id = ? LIMIT 1');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            // One booking visit per selected service (no quantity)
            $_SESSION['cart'][$productId] = [
                'product_id' => $product['p_id'],
                'product_name' => $product['p_name'],
                'photo' => $product['p_featured_photo'],
                'quantity' => 1,
                'notes' => $_SESSION['cart'][$productId]['notes'] ?? '',
            ];
            echo json_encode(['success' => true, 'message' => loadLang('added_to_booking'), 'count' => count($_SESSION['cart'])]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => loadLang('unable_to_add_service')]);
