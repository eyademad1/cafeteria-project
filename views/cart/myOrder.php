<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId > 0) {
        $ownerStmt = $connection->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
        $ownerStmt->execute([$orderId, $userId]);
        $order = $ownerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $error = 'Order not found';
        } elseif ($order['status'] !== 'processing') {
            $error = 'Only processing orders can be edited or cancelled';
        } elseif ($action === 'cancel') {
            $cancelStmt = $connection->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $cancelStmt->execute([$orderId, $userId]);
            $message = 'Order cancelled successfully';
        } elseif ($action === 'edit') {
            $newRoomId = (int) ($_POST['room_id'] ?? 0);
            $newNotes = trim((string) ($_POST['notes'] ?? ''));

            if ($newRoomId <= 0) {
                $error = 'Please select a room';
            } else {
                $roomStmt = $connection->prepare("SELECT id FROM rooms WHERE id = ?");
                $roomStmt->execute([$newRoomId]);
                if (!$roomStmt->fetch(PDO::FETCH_ASSOC)) {
                    $error = 'Selected room does not exist';
                } else {
                    $editStmt = $connection->prepare("UPDATE orders SET room_id = ?, notes = ? WHERE id = ? AND user_id = ?");
                    $editStmt->execute([$newRoomId, $newNotes, $orderId, $userId]);
                    $message = 'Order updated successfully';
                }
            }
        }
    }
}

$roomsStmt = $connection->prepare("SELECT id, room_number FROM rooms ORDER BY room_number ASC");
$roomsStmt->execute();
$rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);

$ordersStmt = $connection->prepare("SELECT o.*, r.room_number FROM orders o LEFT JOIN rooms r ON r.id = o.room_id WHERE o.user_id = ? ORDER BY o.created_at DESC, o.id DESC");
$ordersStmt->execute([$userId]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$itemsStmt = $connection->prepare("SELECT oi.order_id, oi.quantity, oi.price, p.name AS product_name, p.image AS product_image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ? ORDER BY oi.id ASC");

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

function orderStatusBadgeClass($status) {
    if ($status === 'processing') {
        return 'warning text-dark';
    }
    if ($status === 'out_for_delivery') {
        return 'info text-dark';
    }
    if ($status === 'done') {
        return 'success';
    }
    if ($status === 'cancelled') {
        return 'danger';
    }
    return 'secondary';
}
?>

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 style="color:#6b3a1f;">My Orders</h3>
        <a href="index.php?page=home" class="btn btn-outline-secondary btn-sm">Back to Home</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">You do not have any orders yet.</div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php
                $itemsStmt->execute([(int) $order['id']]);
                $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
                $isProcessing = $order['status'] === 'processing';
            ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong>Order #<?= (int) $order['id'] ?></strong>
                        <span class="text-muted ms-2"><?= htmlspecialchars($order['created_at']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-<?= orderStatusBadgeClass($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span>
                        <span class="fw-bold"><?= number_format((float) $order['total'], 2) ?> EGP</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Room:</strong>
                        <span><?= htmlspecialchars($order['room_number'] ?? 'N/A') ?></span>
                        <span class="ms-3"><strong>Notes:</strong> <?= htmlspecialchars($order['notes'] ?: '-') ?></span>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="4" class="text-muted">No order items found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <?php
                                            $imgName = trim((string) ($item['product_image'] ?? ''));
                                            $imgSrc = $basePath . '/public/images/default-product.svg';
                                            if ($imgName !== '') {
                                                // Check if it's a full URL
                                                if (filter_var($imgName, FILTER_VALIDATE_URL)) {
                                                    $imgSrc = $imgName;
                                                } else {
                                                    // It's a local filename
                                                    $diskPath = __DIR__ . '/../../public/images/products/' . $imgName;
                                                    if (file_exists($diskPath)) {
                                                        $imgSrc = $basePath . '/public/images/products/' . $imgName;
                                                    }
                                                }
                                            }
                                            $qty = (int) $item['quantity'];
                                            $unitPrice = (float) $item['price'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>" width="40" height="40" style="object-fit:cover; border-radius:6px;">
                                                    <span><?= htmlspecialchars($item['product_name'] ?? 'Unknown Product') ?></span>
                                                </div>
                                            </td>
                                            <td><?= $qty ?></td>
                                            <td><?= number_format($unitPrice, 2) ?> EGP</td>
                                            <td><?= number_format($qty * $unitPrice, 2) ?> EGP</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($isProcessing): ?>
                        <div class="border rounded p-3 bg-light mb-2">
                            <form method="POST" class="row g-2 align-items-end">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Edit Room</label>
                                    <select class="form-select" name="room_id" required>
                                        <option value="">Select Room</option>
                                        <?php foreach ($rooms as $room): ?>
                                            <option value="<?= (int) $room['id'] ?>" <?= ((int) $room['id'] === (int) $order['room_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($room['room_number']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Edit Notes</label>
                                    <input type="text" class="form-control" name="notes" value="<?= htmlspecialchars((string) $order['notes']) ?>" placeholder="Order notes">
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">Save Edit</button>
                                </div>
                            </form>
                        </div>

                        <form method="POST" onsubmit="return confirm('Cancel this order?');">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-ban me-1"></i> Cancel Order
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-muted small">Only processing orders can be edited or cancelled.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>