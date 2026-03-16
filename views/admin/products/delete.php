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

    $stmt = $connection->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$id]);
    $usedCount = (int) $stmt->fetchColumn();

    if ($usedCount > 0) {
        header("Location: index.php?page=admin_products&error=" . urlencode("Cannot delete: product exists in orders."));
        exit;
    }

    try {
        $stmt = $connection->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

     
        $imageName = trim((string) ($product['image'] ?? ''));
        if ($imageName !== '') {
            $imagePath = __DIR__ . '/../../../public/images/products/' . $imageName;
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
    } catch (PDOException $e) {

        if ($e->getCode() === '23000') {
            header("Location: index.php?page=admin_products&error=" . urlencode("Cannot delete: product exists in orders."));
            exit;
        }
        throw $e;
    }
}

header("Location: index.php?page=admin_products&success=Product deleted successfully");
exit;
?>