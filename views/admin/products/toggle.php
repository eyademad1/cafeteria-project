<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $connection->prepare("UPDATE products SET is_available = 1 - is_available WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php?page=admin_products&success=Product availability toggled");
exit;
?>