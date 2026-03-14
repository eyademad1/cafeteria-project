<?php
require_once __DIR__ . "/config/database.php";

$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}

switch ($page) {
    case 'login':
        include __DIR__ . "/views/auth/login.php";
        break;
    case 'register':
        include __DIR__ . "/views/auth/register.php";
        break;
    case 'home':
        include __DIR__ . "/views/user/home.php";
        break;
    case 'admin':
        include __DIR__ . "/views/admin/dashboard.php";
        break;
    case 'cart':
        include __DIR__ . "/views/cart/myOrder.php";
        break;    
    default:
        include __DIR__ . "/views/auth/login.php";
}
?>