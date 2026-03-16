<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php?page=admin_users");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $room_no = trim($_POST['room_no']);
    $ext = trim($_POST['ext']);
    $role = $_POST['role'];

    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 2) . '/public/images/users/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
            // Delete old image if exists
            if ($profile_pic && file_exists($uploadDir . $profile_pic)) {
                unlink($uploadDir . $profile_pic);
            }
            $profile_pic = $filename;
        } else {
            $errors[] = "Failed to upload profile picture";
        }
    }

    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (!in_array($role, ['admin', 'user'])) $errors[] = "Invalid role";

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) $errors[] = "Email already exists";

    if (empty($errors)) {
        if (empty($password)) {
            $stmt = $connection->prepare("UPDATE users SET name = ?, email = ?, room_no = ?, ext = ?, profile_pic = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $email, $room_no, $ext, $profile_pic, $role, $id]);
        } else {
            $stmt = $connection->prepare("UPDATE users SET name = ?, email = ?, password = ?, room_no = ?, ext = ?, profile_pic = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $email, $password, $room_no, $ext, $profile_pic, $role, $id]);
        }
        header("Location: index.php?page=admin_users&success=User updated successfully");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h4 class="mb-4" style="color:#6b3a1f;">Edit User</h4>

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
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password (leave empty to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_no" class="form-control" value="<?= htmlspecialchars($user['room_no'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Extension</label>
                <input type="text" name="ext" class="form-control" value="<?= htmlspecialchars($user['ext'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Current Profile Picture</label>
                <?php if ($user['profile_pic']): ?>
                    <div><img src="public/images/users/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile" width="100"></div>
                <?php else: ?>
                    <p>No profile picture</p>
                <?php endif; ?>
                <label class="form-label">New Profile Picture (leave empty to keep current)</label>
                <input type="file" name="profile_pic" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn" style="background:#c8813a; color:#fff;">Update User</button>
            <a href="index.php?page=admin_users" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>