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
    case 'checkout':
        require_once __DIR__ . "/controllers/orderController.php";
        $orderController = new orderController($connection);
        
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            // we assume the logged in user id is in the session, if not fallback to 1 as placeholder
            $userId = $_SESSION['user_id'] ?? 1;
            $data['user_id'] = $userId;
            $result = $orderController->saveOrder($data);
            header("Content-Type: application/json");
            echo json_encode(['success' => $result]);
        }
        exit;
    default:
        include __DIR__ . "/views/auth/login.php";
}
?>