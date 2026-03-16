<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}
?>

<h3 class="text-dark">Admin Dashboard</h3>

<p class="text-muted">
Welcome Admin <?= htmlspecialchars($_SESSION['name']) ?>
</p>

<div class="mt-4">
    <a href="index.php?page=home" class="btn btn-cafe me-2">
        <i class="fas fa-tachometer-alt me-1"></i>View Orders
    </a>
    <a href="index.php?page=logout" class="btn btn-danger">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
    </a>
</div>