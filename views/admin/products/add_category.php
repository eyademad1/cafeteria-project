<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $errors[] = "Category name is required";
    } else {
        $stmt = $connection->prepare("SELECT * FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            $errors[] = "Category already exists";
        } else {
            $stmt = $connection->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = "Category added successfully";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-4">
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

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button type="submit" class="btn" style="background:#c8813a; color:#fff;">Add Category</button>
            <a href="index.php?page=admin_products" class="btn btn-secondary">Back to Products</a>
        </form>
    </div>
</div>