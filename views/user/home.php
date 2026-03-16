<?php
// include __DIR__ . "/../layouts/header.php";
require_once "./controllers/orderController.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

$orderController = new orderController($connection);
$products = $orderController->index();

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

$roomStmt = $connection->prepare("SELECT id, room_number FROM rooms ORDER BY room_number ASC");
$roomStmt->execute();
$rooms = $roomStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <div class="page active" id="page-home">
        <div class="content-grid">
            <aside class="order-panel" id="order-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-shopping-cart"></i> Current Order</h2>
                </div>
                <div class="order-items" id="order-items">
                    <div class="empty-order" id="empty-order">
                        <i class="fas fa-coffee"></i>
                        <p>Select a drink from the menu</p>
                    </div>
                </div>
                <div class="order-notes">
                    <label for="order-notes-input">
                        <i class="fas fa-sticky-note"></i> Notes
                    </label>
                    <textarea id="order-notes-input" placeholder="Example: Tea with extra sugar..."></textarea>
                </div>
                <div class="order-room">
                    <label for="room-select">
                        <i class="fas fa-door-open"></i> Room
                    </label>
                    <select name="" id="room-select">
                        <option value="">Select Room</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= (int) $room['id'] ?>"><?= htmlspecialchars($room['room_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="order-total">
                    <span class="total-label">Total</span>
                    <span class="total-amount" id="total-amount">0 EGP</span>
                </div>

                <button class="btn-confirm" id="btn-confirm">
                    <i class="fas fa-check"></i> Confirm Order
                </button>
            </aside>
            <div class="products-section">
                <div class="section-header">
                    <h1><i class="fas fa-mug-saucer"></i> Available Drinks</h1>
                    <p> Click on a drink to add it to your order</p>
                </div>
                <div class="latest-order-banner" id="latest-order-banner" style="display:none;">
                    <div class="banner-header">
                        <i class="fas fa-bell"></i>
                        <h3>Latest Order</h3>
                        <span class="banner-time" id="banner-time"></span>
                    </div>
                    <div class="banner-details" id="banner-details">
                    </div>
                </div>
                <div class="product-grid" id="product-grid">
                <?php if(empty($products)):?>
                    <div class="empty-state">
                        <i class="fas fa-coffee"></i>
                        <p>No products available</p>
                    </div>
                    <?php else:?>
                    <?php foreach($products as $product):?>
                        <?php
                            $imageName = trim((string) ($product['image'] ?? ''));
                            $imageSrc = $basePath . '/public/images/default-product.svg';
                            if ($imageName !== '') {
                                $productImageDisk = __DIR__ . '/../../public/images/products/' . $imageName;
                                if (file_exists($productImageDisk)) {
                                    $imageSrc = $basePath . '/public/images/products/' . $imageName;
                                } elseif (file_exists($legacyImageDisk)) {
                                    $imageSrc = $basePath . '/public/images/' . $imageName;
                                }
                            }
                        ?>
                        <div class="product-card" data-id="<?php echo $product['id']?>" data-name="<?php echo $product['name']?>" data-price="<?php echo $product['price']?>">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-price"><?= htmlspecialchars($product['price']) ?> EGP</p>
                            </div>
                            <button class="btn-add-to-cart ">
                                <i class="fas fa-plus"></i> Add to Cart
                            </button>
                        </div>
                    <?php endforeach;?>
                <?php endif;?>
             
            </div>
        </div>
    </div>
</main>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toast-message">Added successfully</span>
</div>
<script src="<?= htmlspecialchars($basePath . '/public/js/home.js?v=' . filemtime(__DIR__ . '/../../public/js/home.js')) ?>"></script>
