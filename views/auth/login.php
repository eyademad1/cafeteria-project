<?php
include __DIR__ . "/../layouts/header.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role']; 

        if ($user['role'] === 'admin') {
        header("Location: index.php?page=admin");
    } else {
        header("Location: index.php?page=home");
    }
        exit;
    } else {
        $error = "Invalid Email or Password";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <h4 class="mb-4" style="color:#6b3a1f;">Login</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button class="btn w-100" style="background:#c8813a; color:#fff;">Login</button>
        </form>

        <p class="text-center mt-3 small">
            Don't have an account? <a href="index.php?page=register" style="color:#c8813a;">Register</a>
        </p>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>