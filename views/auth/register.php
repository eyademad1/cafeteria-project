<?php
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $room_no  = $_POST['room_no'];
    $ext      = $_POST['ext'];

    $profile_pic = '';
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/www/playground/cafeteria-project/public/images/users/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
        $uploadFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
            $profile_pic = $filename;
        } else {
            $error = "Failed to upload profile picture";
        }
    }

    if (empty($error)) {
        $stmt = $connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            $error = "Email already exists";
        } else {
            $stmt = $connection->prepare("INSERT INTO users (name, email, password, room_no, ext, profile_pic, role) VALUES (:name, :email, :password, :room_no, :ext, :profile_pic, 'user')");
            $stmt->execute([':name' => $name, ':email' => $email, ':password' => $password, ':room_no' => $room_no, ':ext' => $ext, ':profile_pic' => $profile_pic]);
            $success = "Registration successful. You can now login.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <h4 class="mb-4" style="color:#6b3a1f;">Register</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <input type="text" name="room_no" class="form-control" placeholder="Room Number" required>
            </div>
            <div class="mb-3">
                <input type="text" name="ext" class="form-control" placeholder="Extension" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control" accept="image/*">
            </div>
            <button class="btn w-100" style="background:#c8813a; color:#fff;">Register</button>
        </form>

        <p class="text-center mt-3 small">
            Already have an account? <a href="index.php?page=login" style="color:#c8813a;">Login</a>
        </p>
    </div>
</div>