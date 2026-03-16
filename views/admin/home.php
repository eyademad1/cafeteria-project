<?php
// views/admin/home.php
// include __DIR__ . "/../layouts/header.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

require_once __DIR__ . "/../../controllers/AdminController.php";
$adminCtrl = new AdminController($connection);

// Handle status update (deliver button)
$successMsg = '';
$errorMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $orderId = (int) $_POST['order_id'];
    $action  = $_POST['action'];

    $newStatus = match ($action) {
        'deliver' => 'out_for_delivery',
        'done'    => 'done',
        default   => ''
    };

    if ($newStatus) {
        $adminCtrl->updateOrderStatus($orderId, $newStatus);
        header("Location: index.php?page=home");
        exit;
    }
}

$orders = $adminCtrl->getActiveOrders();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark">
        <i class="fas fa-receipt me-2 text-cafe"></i>Current Orders
    </h3>
    <span class="badge bg-cafe rounded-pill fs-6">
        <?= count($orders) ?> active
    </span>
</div>

<?php if ($successMsg): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($successMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-check-circle fa-3x mb-3 text-cafe"></i>
        <h5>All orders are done!</h5>
        <p>No active orders at the moment.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($orders as $order): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <!-- Order Header -->
                    <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light border-bottom border-cafe"
                         style="border-bottom-width: 2px !important;">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-semibold text-dark">
                                <i class="fas fa-calendar-alt me-1 text-cafe opacity-75"></i>
                                <?= date('Y/m/d H:i', strtotime($order['created_at'])) ?>
                            </span>
                            <span class="fw-bold text-dark">
                                <i class="fas fa-user me-1 text-cafe opacity-75"></i>
                                <?= htmlspecialchars($order['user_name']) ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($order['room_number']): ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-door-open me-1"></i>Room <?= htmlspecialchars($order['room_number']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($order['user_ext']): ?>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-phone me-1"></i>Ext. <?= htmlspecialchars($order['user_ext']) ?>
                                </span>
                            <?php endif; ?>
                            <!-- Status Badge -->
                            <?php
                            $statusClass = match ($order['status']) {
                                'processing'       => 'warning text-dark',
                                'out_for_delivery' => 'info text-dark',
                                default            => 'secondary'
                            };
                            $statusLabel = match ($order['status']) {
                                'processing'       => 'Processing',
                                'out_for_delivery' => 'Out for Delivery',
                                default            => ucfirst($order['status'])
                            };
                            ?>
                            <span class="badge bg-<?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body py-3">
                        <!-- Products -->
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="text-center" style="min-width:70px;">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-1 bg-light"
                                         style="width:64px;height:64px;margin:0 auto;">
                                        <?php if ($item['image']): ?>
                                            <?php
                                            $imageSrc = htmlspecialchars($item['image']);
                                            // Check if it's a full URL
                                            if (!filter_var($item['image'], FILTER_VALIDATE_URL)) {
                                                $imageSrc = 'public/images/' . $imageSrc;
                                            }
                                            ?>
                                            <img src="<?= $imageSrc ?>"
                                                 class="img-fluid rounded-3"
                                                 style="max-height:60px;object-fit:cover;">
                                        <?php else: ?>
                                            <i class="fas fa-mug-hot fa-2x text-cafe"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small fw-semibold text-dark">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </div>
                                    <div class="small text-muted">x<?= $item['quantity'] ?></div>
                                    <div class="small text-cafe">
                                        <?= number_format($item['price'] * $item['quantity'], 2) ?> EGP
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Notes -->
                        <?php if (!empty($order['notes'])): ?>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-sticky-note me-1"></i>
                                <?= htmlspecialchars($order['notes']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Footer: total + actions -->
                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                            <span class="fw-bold text-dark">
                                Total: <?= number_format($order['total'], 2) ?> EGP
                            </span>
                            <div class="d-flex gap-2">
                                <?php if ($order['status'] === 'processing'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <input type="hidden" name="action"   value="deliver">
                                        <button type="submit" class="btn btn-sm btn-warning fw-semibold">
                                            <i class="fas fa-truck me-1"></i>Deliver
                                        </button>
                                    </form>
                                <?php elseif ($order['status'] === 'out_for_delivery'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <input type="hidden" name="action"   value="done">
                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                            <i class="fas fa-check me-1"></i>Done
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php // include __DIR__ . "/../layouts/footer.php"; ?>