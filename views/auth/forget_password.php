<?php
include __DIR__ . "/../layouts/header.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    }
    else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $connection->prepare("
            UPDATE users 
            SET password = :password 
            WHERE email = :email
        ");

        $stmt->execute([
            ':password' => $hashedPassword,
            ':email' => $email
        ]);

        $success = "Password updated successfully";
    }
}
?>

<div class="row justify-content-center">
<div class="col-md-4">

<h4 class="mb-4">Reset Password</h4>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<input type="email" name="email" class="form-control" placeholder="Email" required>
</div>

<div class="mb-3">
<input type="password" name="password" class="form-control" placeholder="New Password" required>
</div>

<div class="mb-3">
<input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
</div>

<button class="btn w-100" style="background:#c8813a;color:#fff;">
Reset Password
</button>

</form>

</div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>
