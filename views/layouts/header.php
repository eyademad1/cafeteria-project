<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/main.css">
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/footer.css">
    <style>

.role-badge {
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 0.03em;
  padding: 2px 8px;
  border-radius: 20px;
  text-transform: uppercase;
  vertical-align: middle;
}
.role-badge.admin {
  background: #6b3a1f;
  color: #fff;
}
.role-badge.user {
  background: #f3e0cc;
  color: #6b3a1f;
}
.nav-user-name {
  font-weight: 500;
  color: #3d2b1a;
}
.navbar .nav-link {
  transition: color 0.15s;
}
.navbar .nav-link:hover {
  color: #c8813a !important;
}
    </style>
</head>
<body>

<?php

$isLoggedIn = isset($_SESSION['user_id']);
$role       = $_SESSION['role']  ?? '';
$userName   = $_SESSION['name']  ?? '';
$isAdmin    = $role === 'admin';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">

        <!-- Brand -->
        <a class="navbar-brand fw-semibold" href="index.php?page=home">
            <i class="fas fa-mug-hot me-1" style="color:var(--cafe-gold, #c8813a);"></i> Cafeteria
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">

                <?php if ($isLoggedIn && $isAdmin): ?>
                    <!-- Admin links -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=home">
                            <i class="fas fa-house fa-sm me-1 opacity-75"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=products">
                            <i class="fas fa-box fa-sm me-1 opacity-75"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=users">
                            <i class="fas fa-users fa-sm me-1 opacity-75"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=manual-order">
                            <i class="fas fa-pen-to-square fa-sm me-1 opacity-75"></i>Manual Order
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=checks">
                            <i class="fas fa-receipt fa-sm me-1 opacity-75"></i>Checks
                        </a>
                    </li>

                <?php elseif ($isLoggedIn): ?>
                    <!-- User links -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=home">
                            <i class="fas fa-house fa-sm me-1 opacity-75"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=my-orders">
                            <i class="fas fa-bag-shopping fa-sm me-1 opacity-75"></i>My Orders
                        </a>
                    </li>

                <?php else: ?>
                    <!-- No role links -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=menu">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contact">Contact</a>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Right side -->
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <?php if ($isLoggedIn): ?>
                    <!-- Name + role badge -->
                    <li class="nav-item d-flex align-items-center gap-2 me-2">
                        <i class="fas fa-circle-user" style="color:#c8813a; font-size:1.1rem;"></i>
                        <span class="nav-user-name small">
                            <?= htmlspecialchars($userName) ?>
                        </span>
                        <span class="role-badge <?= htmlspecialchars($role) ?>">
                            <?= htmlspecialchars($role) ?>
                        </span>
                    </li>
                    <!-- Logout -->
                    <li class="nav-item">
                        <a class="nav-link text-danger fw-medium" href="index.php?page=logout">
                            <i class="fas fa-right-from-bracket fa-sm me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm ms-1 px-3" href="index.php?page=register"
                           style="background:#c8813a; color:#fff; border-radius:20px;">
                            Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

        </div>
    </div>
</nav>

<main class="container py-4">