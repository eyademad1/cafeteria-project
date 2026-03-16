<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $category_id = $_POST['category_id'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    if (empty($name)) $errors[] = "Name is required";
    if (empty($price) || !is_numeric($price)) $errors[] = "Valid price is required";

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../public/images/products/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $errors[] = "Failed to create upload directory";
        } elseif (!is_writable($uploadDir)) {
            $errors[] = "Upload directory is not writable";
        } else {
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image']['name']));
            $filename = uniqid() . '_' . $safeName;
            $uploadFile = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image = $filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Image upload error code: " . (int) $_FILES['image']['error'];
    }

    if (empty($errors)) {
        $stmt = $connection->prepare("INSERT INTO products (name, price, category_id, image, is_available) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $category_id, $image, $is_available]);
        header("Location: index.php?page=admin_products&success=Product added successfully");
        exit;
    }
}

$stmt = $connection->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h4 class="mb-4" style="color:#6b3a1f;">Add New Product</h4>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_available" class="form-check-input" id="is_available" checked>
                <label class="form-check-label" for="is_available">Available</label>
            </div>
            <button type="submit" class="btn" style="background:#c8813a; color:#fff;">Add Product</button>
            <a href="index.php?page=admin_products" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>