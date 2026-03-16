<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$stmt = $connection->prepare("SELECT * FROM categories ORDER BY id DESC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 style="color:#6b3a1f;">All Categories</h4>
    <a href="index.php?page=admin_categories_add" class="btn btn-primary">Add New Category</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">No categories found in database</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= (int) $category['id'] ?></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td>
                            <a href="index.php?page=admin_categories_edit&id=<?= (int) $category['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=admin_categories_delete&id=<?= (int) $category['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
