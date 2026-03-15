<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}
?>

<h3 style="color:#6b3a1f;">Admin Dashboard</h3>

<p class="text-muted">
Welcome Admin <?= htmlspecialchars($_SESSION['name']) ?>
</p>


<a href="index.php?page=logout" class="btn btn-danger mt-4">Logout</a>