<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$stmt = $connection->prepare("SELECT * FROM rooms ORDER BY id DESC");
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 style="color:#6b3a1f;">All Rooms</h4>
    <a href="index.php?page=admin_rooms_add" class="btn btn-primary">Add New Room</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Room Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rooms)): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">No rooms found in database</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?= (int) $room['id'] ?></td>
                        <td><?= htmlspecialchars($room['room_number']) ?></td>
                        <td>
                            <a href="index.php?page=admin_rooms_edit&id=<?= (int) $room['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="index.php?page=admin_rooms_delete&id=<?= (int) $room['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
