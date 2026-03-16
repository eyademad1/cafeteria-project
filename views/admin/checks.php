<?php
// views/admin/checks.php
// include __DIR__ . "/../layouts/header.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

require_once __DIR__ . "/../../controllers/AdminController.php";
$adminCtrl = new AdminController($connection);

// ── Filters from GET ──────────────────────────────────────────
$dateFrom     = trim($_GET['date_from']   ?? '');
$dateTo       = trim($_GET['date_to']     ?? '');
$filterUserId = (int) ($_GET['user_id']  ?? 0);
$expandUserId = (int) ($_GET['expand']   ?? 0);   // which user row is expanded

// Basic date validation
$errors = [];
if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
    $errors[] = '"Date From" cannot be after "Date To".';
}

// ── Fetch data ────────────────────────────────────────────────
$allUsers   = $adminCtrl->getAllUsers();
$checksData = (!empty($errors)) ? [] : $adminCtrl->getChecks($dateFrom, $dateTo, $filterUserId);

// If a user row is expanded, load their orders
$expandedOrders = [];
if ($expandUserId > 0) {
    $expandedOrders = $adminCtrl->getUserOrders($expandUserId, $dateFrom, $dateTo);
}

// Pagination
$perPage     = 10;
$currentPage = max(1, (int) ($_GET['pg'] ?? 1));
$totalRows   = count($checksData);
$totalPages  = max(1, (int) ceil($totalRows / $perPage));
$currentPage = min($currentPage, $totalPages);
$paged       = array_slice($checksData, ($currentPage - 1) * $perPage, $perPage);

// Build query string helper (keeps all params except the one being changed)
function buildQuery(array $overrides = []): string
{
    $params = array_merge([
        'page'      => 'checks',
        'date_from' => $_GET['date_from'] ?? '',
        'date_to'   => $_GET['date_to']   ?? '',
        'user_id'   => $_GET['user_id']   ?? '',
        'expand'    => $_GET['expand']    ?? '',
        'pg'        => $_GET['pg']        ?? 1,
    ], $overrides);
    return 'index.php?' . http_build_query(array_filter($params, fn($v) => $v !== ''));
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark">
        <i class="fas fa-file-invoice-dollar me-2 text-cafe"></i>Checks
    </h3>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-danger py-2">
        <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($err) ?>
    </div>
<?php endforeach; ?>

<!-- ── Filter Form ── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" id="filterForm" novalidate>
            <input type="hidden" name="page" value="checks">
            <div class="row g-3 align-items-end">

                <!-- Date From -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1 text-dark">
                        <i class="fas fa-calendar me-1 text-cafe"></i>Date From
                    </label>
                    <input type="date" name="date_from" id="dateFrom"
                           class="form-control form-control-sm"
                           value="<?= htmlspecialchars($dateFrom) ?>">
                </div>

                <!-- Date To -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1 text-dark">
                        <i class="fas fa-calendar-check me-1 text-cafe"></i>Date To
                    </label>
                    <input type="date" name="date_to" id="dateTo"
                           class="form-control form-control-sm"
                           value="<?= htmlspecialchars($dateTo) ?>">
                </div>

                <!-- User Filter -->
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1 text-dark">
                        <i class="fas fa-user me-1 text-cafe"></i>User
                    </label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">— All Users —</option>
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= $u['id'] ?>"
                                <?= ($filterUserId === (int)$u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-cafe btn-sm fw-semibold flex-fill">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="index.php?page=checks" class="btn btn-sm btn-outline-secondary flex-fill">
                        <i class="fas fa-rotate-left me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Results Table ── -->
<?php if (empty($paged)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
        <h5>No records found</h5>
        <p class="small">Try adjusting the date range or user filter.</p>
    </div>
<?php else: ?>

    <!-- Grand total bar -->
    <?php $grandTotal = array_sum(array_column($checksData, 'total_amount')); ?>
    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <span class="small text-muted"><?= $totalRows ?> user(s) found</span>
        <span class="fw-bold text-dark">
            Grand Total: <?= number_format($grandTotal, 2) ?> EGP
        </span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 ps-3 text-dark fw-semibold border-bottom border-cafe" style="border-bottom-width: 2px !important;">
                            <i class="fas fa-user me-1 text-cafe opacity-75"></i>Name
                        </th>
                        <th class="py-3 text-center text-dark fw-semibold border-bottom border-cafe" style="border-bottom-width: 2px !important;">
                            Orders
                        </th>
                        <th class="py-3 text-end pe-3 text-dark fw-semibold border-bottom border-cafe" style="border-bottom-width: 2px !important;">
                            Total Amount
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paged as $row): ?>
                        <?php $isExpanded = ($expandUserId === (int)$row['user_id']); ?>
                        <!-- User row -->
                        <tr style="cursor:pointer;"
                            onclick="window.location='<?= buildQuery(['expand' => $isExpanded ? 0 : $row['user_id'], 'pg' => $currentPage]) ?>'">
                            <td class="ps-3 py-3">
                                <i class="fas fa-<?= $isExpanded ? 'minus' : 'plus' ?>-square me-2 text-cafe"></i>
                                <strong class="text-dark"><?= htmlspecialchars($row['user_name']) ?></strong>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    <?= $row['order_count'] ?>
                                </span>
                            </td>
                            <td class="text-end pe-3 py-3 fw-bold text-cafe">
                                <?= number_format($row['total_amount'], 2) ?> EGP
                            </td>
                        </tr>

                        <!-- Expanded orders sub-table -->
                        <?php if ($isExpanded && !empty($expandedOrders)): ?>
                            <tr class="table-light">
                                <td colspan="3" class="p-0">
                                    <div class="p-3">
                                        <?php foreach ($expandedOrders as $order): ?>
                                            <div class="card border-0 mb-2" style="background:#fff8f3;">
                                                <div class="card-body p-3">
                                                    <!-- Order header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="small fw-semibold" style="color:#6b3a1f;">
                                                            <i class="fas fa-calendar-alt me-1 opacity-75"></i>
                                                            <?= date('Y/m/d H:i', strtotime($order['created_at'])) ?>
                                                        </span>
                                                        <span class="badge
                                                            <?= match($order['status']) {
                                                                'processing'       => 'bg-warning text-dark',
                                                                'out_for_delivery' => 'bg-info text-dark',
                                                                'done'             => 'bg-success',
                                                                default            => 'bg-secondary'
                                                            } ?>">
                                                            <?= ucwords(str_replace('_', ' ', $order['status'])) ?>
                                                        </span>
                                                    </div>

                                                    <!-- Items -->
                                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                                        <?php foreach ($order['items'] as $item): ?>
                                                            <div class="text-center" style="min-width:60px;">
                                                                <div class="rounded-3 d-flex align-items-center justify-content-center mb-1"
                                                                     style="width:50px;height:50px;background:#f8ede0;margin:0 auto;">
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
                                                                             style="max-height:46px;object-fit:cover;">
                                                                    <?php else: ?>
                                                                        <i class="fas fa-mug-hot text-cafe"></i>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="small fw-semibold" style="color:#3d2b1a; font-size:.75rem;">
                                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                                </div>
                                                                <div class="small text-muted" style="font-size:.75rem;">
                                                                    x<?= $item['quantity'] ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>

                                                    <!-- Notes + Total -->
                                                    <?php if (!empty($order['notes'])): ?>
                                                        <p class="small text-muted mb-1">
                                                            <i class="fas fa-sticky-note me-1"></i>
                                                            <?= htmlspecialchars($order['notes']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <div class="text-end fw-bold small" style="color:#c8813a;">
                                                        <?= number_format($order['total'], 2) ?> EGP
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <!-- User total -->
                                        <div class="text-end fw-bold pt-2 border-top" style="color:#6b3a1f;">
                                            Total: <?= number_format($row['total_amount'], 2) ?> EGP
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php elseif ($isExpanded && empty($expandedOrders)): ?>
                            <tr class="table-light">
                                <td colspan="3" class="text-center text-muted small py-3">
                                    No orders found in this date range.
                                </td>
                            </tr>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Pagination ── -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4 d-flex justify-content-center">
            <ul class="pagination pagination-sm">
                <!-- First -->
                <li class="page-item <?= ($currentPage === 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildQuery(['pg' => 1]) ?>">
                        <i class="fas fa-angles-left"></i>
                    </a>
                </li>
                <!-- Prev -->
                <li class="page-item <?= ($currentPage === 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildQuery(['pg' => $currentPage - 1]) ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                <!-- Pages -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage   = min($totalPages, $currentPage + 2);
                if ($startPage > 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif;
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                        <a class="page-link <?= ($i === $currentPage) ? '' : '' ?>"
                           href="<?= buildQuery(['pg' => $i]) ?>"
                           <?php if ($i === $currentPage): ?>
                               style="background:#c8813a; border-color:#c8813a;"
                           <?php endif; ?>>
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor;
                if ($endPage < $totalPages): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <!-- Next -->
                <li class="page-item <?= ($currentPage === $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildQuery(['pg' => $currentPage + 1]) ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                <!-- Last -->
                <li class="page-item <?= ($currentPage === $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildQuery(['pg' => $totalPages]) ?>">
                        <i class="fas fa-angles-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

<?php endif; ?>

<!-- Frontend date validation -->
<script>
document.getElementById('filterForm').addEventListener('submit', function(e) {
    const from = document.getElementById('dateFrom').value;
    const to   = document.getElementById('dateTo').value;
    if (from && to && from > to) {
        e.preventDefault();
        alert('"Date From" cannot be after "Date To".');
    }
});
</script>

<?php // include __DIR__ . "/../layouts/footer.php"; ?>