<?php
include __DIR__ . "/../layouts/header.php";

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    }
    elseif ($password !== $confirm) {
    $error = "Passwords do not match";
    }
    else {

        $stmt = $connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            $error = "Email already exists";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $connection->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

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

        <form method="POST">
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
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <button class="btn w-100" style="background:#c8813a; color:#fff;">Register</button>
        </form>

        <p class="text-center mt-3 small">
            Already have an account? <a href="index.php?page=login" style="color:#c8813a;">Login</a>
        </p>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>