<?php
require_once __DIR__ . "/config/database.php";

$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}


$noOutputPages = ['admin_products_delete', 'admin_products_toggle', 'admin_users_delete'];
if (in_array($page, $noOutputPages)) {
    if (strpos($page, 'admin_products_') === 0) {
        $subPage = str_replace('admin_products_', '', $page);
        include __DIR__ . "/views/admin/products/{$subPage}.php";
    } elseif (strpos($page, 'admin_users_') === 0) {
        $subPage = str_replace('admin_users_', '', $page);
        include __DIR__ . "/views/admin/users/{$subPage}.php";
    }
    exit;
}

include __DIR__ . "/views/layouts/header.php";

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
    case 'admin_products':
        include __DIR__ . "/views/admin/products/index.php";
        break;
    case 'admin_products_add':
        include __DIR__ . "/views/admin/products/add.php";
        break;
    case 'admin_products_edit':
        include __DIR__ . "/views/admin/products/edit.php";
        break;
    case 'admin_add_category':
        include __DIR__ . "/views/admin/products/add_category.php";
        break;
    case 'admin_users':
        include __DIR__ . "/views/admin/users/index.php";
        break;
    case 'admin_users_add':
        include __DIR__ . "/views/admin/users/add.php";
        break;
    case 'admin_users_edit':
        include __DIR__ . "/views/admin/users/edit.php";
        break;
    default:
        include __DIR__ . "/views/auth/login.php";
}

include __DIR__ . "/views/layouts/footer.php";
?>