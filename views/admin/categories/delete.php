<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

try {
    $stmt = $connection->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?page=admin_categories&success=Category deleted successfully");
} catch (PDOException $e) {
    header("Location: index.php?page=admin_categories&error=Category cannot be deleted while products use it");
}
exit;
