<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?page=login");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomNumber = trim($_POST['room_number'] ?? '');

    if ($roomNumber === '') {
        $errors[] = "Room number is required";
    }

    $stmt = $connection->prepare("SELECT id FROM rooms WHERE room_number = ?");
    $stmt->execute([$roomNumber]);
    if ($roomNumber !== '' && $stmt->fetch()) {
        $errors[] = "Room already exists";
    }

    if (empty($errors)) {
        $stmt = $connection->prepare("INSERT INTO rooms (room_number) VALUES (?)");
        $stmt->execute([$roomNumber]);
        header("Location: index.php?page=admin_rooms&success=Room added successfully");
        exit;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h4 class="mb-4" style="color:#6b3a1f;">Add New Room</h4>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_number" class="form-control" required>
            </div>
            <button type="submit" class="btn" style="background:#c8813a; color:#fff;">Add Room</button>
            <a href="index.php?page=admin_rooms" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
