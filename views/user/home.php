<?php
include __DIR__ . "/../layouts/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}
?>

<h4 style="color:#6b3a1f;">Welcome <?= htmlspecialchars($_SESSION['name']) ?></h4>
<p class="text-muted">This is your home page.</p>

<a href="index.php?page=logout" class="btn btn-danger btn-sm">Logout</a>

<?php include __DIR__ . "/../layouts/footer.php"; ?>