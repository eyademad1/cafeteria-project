<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $errors[] = "Category name is required";
    }

    $stmt = $connection->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    if ($name !== '' && $stmt->fetch()) {
        $errors[] = "Category already exists";
    }

    if (empty($errors)) {
        $stmt = $connection->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: index.php?page=admin_categories&success=Category added successfully");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h4 class="mb-4" style="color:#6b3a1f;">Add New Category</h4>

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
                <input type="text" name="name" class="form-control" required>
            </div>
            <button type="submit" class="btn" style="background:#c8813a; color:#fff;">Add Category</button>
            <a href="index.php?page=admin_categories" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
