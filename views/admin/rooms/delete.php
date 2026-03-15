<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

try {
    $stmt = $connection->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?page=admin_rooms&success=Room deleted successfully");
} catch (PDOException $e) {
    header("Location: index.php?page=admin_rooms&error=Room cannot be deleted while orders use it");
}
exit;
