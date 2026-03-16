<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $connection->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: index.php?page=admin_categories&error=Category not found");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $errors[] = "Category name is required";
    }

    $stmt = $connection->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($name !== '' && $stmt->fetch()) {
        $errors[] = "Category already exists";
    }

    if (empty($errors)) {
        $stmt = $connection->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: index.php?page=admin_categories&success=Category updated successfully");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h4 class="mb-4" style="color:#6b3a1f;">Edit Category</h4>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            <button type="submit" class="btn" style="background:#c8813a; color:#fff;">Update Category</button>
            <a href="index.php?page=admin_categories" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
