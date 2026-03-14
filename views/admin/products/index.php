<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$stmt = $connection->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 style="color:#6b3a1f;">All Products</h4>
    <a href="index.php?page=admin_products_add" class="btn btn-primary">Add New Product</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td>
                        <?php if ($product['image']): ?>
                            <img src="public/images/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="50">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                    <td>
                        <span class="badge bg-<?= $product['is_available'] ? 'success' : 'danger' ?>">
                            <?= $product['is_available'] ? 'Yes' : 'No' ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?page=admin_products_edit&id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="index.php?page=admin_products_toggle&id=<?= $product['id'] ?>" class="btn btn-sm btn-secondary">Toggle</a>
                        <a href="index.php?page=admin_products_delete&id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>