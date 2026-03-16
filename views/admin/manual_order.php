<?php
// views/admin/manual_order.php
// include __DIR__ . "/../layouts/header.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

require_once __DIR__ . "/../../controllers/AdminController.php";
$adminCtrl = new AdminController($connection);

$users    = $adminCtrl->getAllUsers();
$rooms    = $adminCtrl->getAllRooms();
$products = $adminCtrl->getAvailableProducts();

// Group products by category
$grouped = [];
foreach ($products as $p) {
    $grouped[$p['category_name']][] = $p;
}

$successMsg = '';
$errorMsg   = '';
$lastTotal  = 0;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id']  ?? 0);
    $roomId = (int) ($_POST['room_id']  ?? 0);
    $notes  = trim($_POST['notes']       ?? '');
    $items  = $_POST['items']            ?? [];

    // Frontend validation echoed back
    if ($userId === 0) {
        $errorMsg = 'Please select a user.';
    } elseif ($roomId === 0) {
        $errorMsg = 'Please select a room.';
    } elseif (empty($items)) {
        $errorMsg = 'Please add at least one product.';
    } else {
        $result = $adminCtrl->createManualOrder($userId, $roomId, $notes, $items);
        if ($result['success']) {
            $successMsg = $result['message'] . ' — Total: ' . number_format($result['total'], 2) . ' EGP';
        } else {
            $errorMsg = $result['message'];
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark">
        <i class="fas fa-pen-to-square me-2 text-cafe"></i>Manual Order
    </h3>
</div>

<?php if ($successMsg): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($successMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($errorMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- LEFT: Order Summary Panel -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header py-3 bg-light border-bottom border-cafe" style="border-bottom-width: 2px !important;">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-clipboard-list me-2 text-cafe"></i>Order Summary
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" id="orderForm" novalidate>

                    <!-- User Select -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            <i class="fas fa-user me-1 text-cafe"></i>User <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" id="userSelect" class="form-select form-select-sm" required>
                            <option value="">— Select User —</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"
                                    <?= (isset($_POST['user_id']) && $_POST['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['name']) ?>
                                    <?= $u['room_no'] ? '(Room ' . htmlspecialchars($u['room_no']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a user.</div>
                    </div>

                    <!-- Room Select -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            <i class="fas fa-door-open me-1 text-cafe"></i>Room <span class="text-danger">*</span>
                        </label>
                        <select name="room_id" id="roomSelect" class="form-select form-select-sm" required>
                            <option value="">— Select Room —</option>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                    <?= (isset($_POST['room_id']) && $_POST['room_id'] == $r['id']) ? 'selected' : '' ?>>
                                    Room <?= htmlspecialchars($r['room_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a room.</div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            <i class="fas fa-sticky-note me-1 text-cafe"></i>Notes
                        </label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                                  placeholder="e.g. Extra sugar, no milk..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                    </div>

                    <!-- Selected Items List -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            <i class="fas fa-shopping-basket me-1 text-cafe"></i>Selected Items
                        </label>
                        <div id="selectedItems" class="border rounded p-2 bg-light" style="min-height:60px; font-size:.85rem;">
                            <span class="text-muted fst-italic" id="emptyMsg">No items selected yet.</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
                        <span class="fw-bold text-dark">Total:</span>
                        <span class="fw-bold fs-5 text-cafe" id="totalDisplay">0.00 EGP</span>
                    </div>

                    <button type="submit" class="btn btn-cafe w-100 fw-semibold" id="confirmBtn" disabled>
                        <i class="fas fa-check me-1"></i>Confirm Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT: Products Grid -->
    <div class="col-lg-8">
        <?php foreach ($grouped as $categoryName => $catProducts): ?>
            <h6 class="fw-bold mb-2 mt-3 text-dark">
                <i class="fas fa-tag me-1 text-cafe opacity-75"></i><?= htmlspecialchars($categoryName) ?>
            </h6>
            <div class="row g-2 mb-3">
                <?php foreach ($catProducts as $p): ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="card border-0 shadow-sm h-100 product-card text-center"
                             data-id="<?= $p['id'] ?>"
                             data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                             data-price="<?= $p['price'] ?>"
                             style="cursor:pointer; transition:.2s;">
                            <div class="card-body p-2">
                                <div class="rounded-3 d-flex align-items-center justify-content-center mb-2 bg-light"
                                     style="height:64px; margin:0 auto; width:64px;">
                                    <?php if ($p['image']): ?>
                                        <?php
                                        $imageSrc = htmlspecialchars($p['image']);
                                        // Check if it's a full URL
                                        if (!filter_var($p['image'], FILTER_VALIDATE_URL)) {
                                            $imageSrc = 'public/images/' . $imageSrc;
                                        }
                                        ?>
                                        <img src="<?= $imageSrc ?>"
                                             class="img-fluid rounded-3" style="max-height:60px;object-fit:cover;">
                                    <?php else: ?>
                                        <i class="fas fa-mug-hot fa-2x text-cafe"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="fw-semibold small text-dark">
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                                <div class="small text-cafe">
                                    <?= number_format($p['price'], 2) ?> EGP
                                </div>
                                <!-- Quantity controls (hidden until selected) -->
                                <div class="qty-controls mt-2 d-none align-items-center justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 qty-minus">
                                        <i class="fas fa-minus fa-xs"></i>
                                    </button>
                                    <span class="qty-display fw-bold px-1" style="min-width:20px;">1</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0 qty-plus">
                                        <i class="fas fa-plus fa-xs"></i>
                                    </button>
                                </div>
                                <!-- Selected indicator -->
                                <div class="selected-badge d-none mt-1">
                                    <span class="badge bg-cafe" style="font-size:.7rem;">
                                        <i class="fas fa-check me-1"></i>Added
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Cart state: { productId: { name, price, qty } }
const cart = {};

function renderCart() {
    const container = document.getElementById('selectedItems');
    const emptyMsg  = document.getElementById('emptyMsg');
    const totalEl   = document.getElementById('totalDisplay');
    const confirmBtn = document.getElementById('confirmBtn');

    // Remove old hidden inputs
    document.querySelectorAll('input.cart-input').forEach(e => e.remove());

    if (Object.keys(cart).length === 0) {
        container.innerHTML = '<span class="text-muted fst-italic" id="emptyMsg">No items selected yet.</span>';
        totalEl.textContent = '0.00 EGP';
        confirmBtn.disabled = true;
        return;
    }

    let html = '';
    let total = 0;
    const form = document.getElementById('orderForm');

    Object.entries(cart).forEach(([id, item]) => {
        const lineTotal = item.price * item.qty;
        total += lineTotal;
        html += `<div class="d-flex justify-content-between align-items-center border-bottom py-1">
            <span>${item.name} <span class="text-muted">x${item.qty}</span></span>
            <span class="text-cafe">${lineTotal.toFixed(2)} EGP</span>
        </div>`;
        // Add hidden input for form submission
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `items[${id}]`;
        input.value = item.qty;
        input.className = 'cart-input';
        form.appendChild(input);
    });

    container.innerHTML = html;
    totalEl.textContent = total.toFixed(2) + ' EGP';
    confirmBtn.disabled = false;
}

document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Ignore clicks on qty buttons
        if (e.target.closest('.qty-controls')) return;

        const id    = this.dataset.id;
        const name  = this.dataset.name;
        const price = parseFloat(this.dataset.price);
        const qtyControls  = this.querySelector('.qty-controls');
        const selectedBadge = this.querySelector('.selected-badge');

        if (cart[id]) {
            // Deselect
            delete cart[id];
            qtyControls.classList.add('d-none');
            qtyControls.classList.remove('d-flex');
            selectedBadge.classList.add('d-none');
            this.style.borderColor = '';
            this.style.boxShadow = '';
        } else {
            // Add to cart with qty=1
            cart[id] = { name, price, qty: 1 };
            qtyControls.classList.remove('d-none');
            qtyControls.classList.add('d-flex');
            selectedBadge.classList.remove('d-none');
            this.style.outline = '2px solid #c8813a';
        }
        renderCart();
    });

    // Qty minus
    card.querySelector('.qty-minus').addEventListener('click', function(e) {
        e.stopPropagation();
        const id = card.dataset.id;
        if (!cart[id]) return;
        if (cart[id].qty <= 1) {
            // Remove from cart
            delete cart[id];
            card.querySelector('.qty-controls').classList.add('d-none');
            card.querySelector('.qty-controls').classList.remove('d-flex');
            card.querySelector('.selected-badge').classList.add('d-none');
            card.style.outline = '';
        } else {
            cart[id].qty--;
            card.querySelector('.qty-display').textContent = cart[id].qty;
        }
        renderCart();
    });

    // Qty plus
    card.querySelector('.qty-plus').addEventListener('click', function(e) {
        e.stopPropagation();
        const id = card.dataset.id;
        if (!cart[id]) return;
        cart[id].qty++;
        card.querySelector('.qty-display').textContent = cart[id].qty;
        renderCart();
    });
});

// Form validation
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const userId = document.getElementById('userSelect').value;
    const roomId = document.getElementById('roomSelect').value;

    let valid = true;

    if (!userId) {
        document.getElementById('userSelect').classList.add('is-invalid');
        valid = false;
    } else {
        document.getElementById('userSelect').classList.remove('is-invalid');
    }

    if (!roomId) {
        document.getElementById('roomSelect').classList.add('is-invalid');
        valid = false;
    } else {
        document.getElementById('roomSelect').classList.remove('is-invalid');
    }

    if (Object.keys(cart).length === 0) {
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>

<?php // include __DIR__ . "/../layouts/footer.php"; ?>