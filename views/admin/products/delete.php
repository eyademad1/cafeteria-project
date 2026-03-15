<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $connection->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    // Delete image if exists
    if ($product['image']) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/www/playground/cafeteria-project/public/images/products/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $stmt = $connection->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php?page=admin_products&success=Product deleted successfully");
exit;
?>