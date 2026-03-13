<?php
include __DIR__ . "/../layouts/header.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}
?>

<main class="main-content">
    <div class="page active" id="page-home">
        <div class="content-grid">
            <aside calss="order-panel" id="order-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-shopping-cart"></i> Current Order</h2>
                </div>
                <div class="orders-items" id="orders-items">
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
                        <option value="101">Room 101</option>
                        <option value="102">Room 102</option>
                        <option value="103">Room 103</option>
                        <option value="104">Room 104</option>
                        <option value="Meeting Room">Meeting Room</option>
                    </select>
                </div>
                <div class="order-total">
                    <span class="total-label">Total</span>
                    <span class="total-amount">0 EGP</span>
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
                    <div class="banner-details"
                             id="banner-details">
                        </div>


                </div>
            </div>
        </div>
    </div>
</main>

<!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toast-message">Added successfully</span>
    </div>

    <script src="app.js"></script>

<?php
include __DIR__ . "/../layouts/footer.php";
?>