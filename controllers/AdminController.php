<?php
// controllers/AdminController.php

class AdminController
{
    private PDO $db;

    public function __construct(PDO $connection)
    {
        $this->db = $connection;
    }

    // ─────────────────────────────────────────────
    // SCREEN 10 – Admin Home: active orders
    // ─────────────────────────────────────────────
    public function getActiveOrders(): array
    {
        $sql = "
            SELECT
                o.id          AS order_id,
                o.created_at,
                o.notes,
                o.total,
                o.status,
                u.name        AS user_name,
                u.ext         AS user_ext,
                r.room_number
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN rooms r ON o.room_id = r.id
            WHERE o.status IN ('processing', 'out_for_delivery')
            ORDER BY o.created_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // attach items to each order
        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
        }
        return $orders;
    }

    // Mark order as "out_for_delivery" or "done"
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $allowed = ['processing', 'out_for_delivery', 'done', 'cancelled'];
        if (!in_array($status, $allowed)) return false;

        $stmt = $this->db->prepare(
            "UPDATE orders SET status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $orderId]);
    }

    // ─────────────────────────────────────────────
    // SCREEN 3 – Manual Order
    // ─────────────────────────────────────────────
    public function getAllUsers(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, room_no, ext FROM users WHERE role = 'user' ORDER BY name"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRooms(): array
    {
        $stmt = $this->db->prepare("SELECT id, room_number FROM rooms ORDER BY room_number");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableProducts(): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.price, p.image, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_available = 1
            ORDER BY c.name, p.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createManualOrder(int $userId, int $roomId, string $notes, array $items): array
    {
        // Validate items not empty
        if (empty($items)) {
            return ['success' => false, 'message' => 'Please select at least one product.'];
        }

        // Validate user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Invalid user selected.'];
        }

        // Validate room exists
        $stmt = $this->db->prepare("SELECT id FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Invalid room selected.'];
        }

        // Calculate total & validate products
        $total = 0;
        $validatedItems = [];
        foreach ($items as $productId => $quantity) {
            $quantity = (int) $quantity;
            if ($quantity <= 0) continue;

            $stmt = $this->db->prepare(
                "SELECT id, price, is_available FROM products WHERE id = ?"
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product || !$product['is_available']) continue;

            $total += $product['price'] * $quantity;
            $validatedItems[] = [
                'product_id' => (int) $productId,
                'quantity'   => $quantity,
                'price'      => (float) $product['price'],
            ];
        }

        if (empty($validatedItems)) {
            return ['success' => false, 'message' => 'No valid products selected.'];
        }

        try {
            $this->db->beginTransaction();

            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, room_id, notes, total, status)
                VALUES (?, ?, ?, ?, 'processing')
            ");
            $stmt->execute([$userId, $roomId, $notes, $total]);
            $orderId = (int) $this->db->lastInsertId();

            // Insert order items
            $stmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($validatedItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Order created successfully!', 'order_id' => $orderId, 'total' => $total];

        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────
    // SCREEN 9 – Checks
    // ─────────────────────────────────────────────
    public function getChecks(string $dateFrom = '', string $dateTo = '', int $userId = 0): array
    {
        $params = [];

        $sql = "
            SELECT
                u.id          AS user_id,
                u.name        AS user_name,
                SUM(o.total)  AS total_amount,
                COUNT(o.id)   AS order_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.status != 'cancelled'
        ";

        if ($dateFrom) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
        }
        if ($userId > 0) {
            $sql .= " AND o.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " GROUP BY u.id, u.name ORDER BY u.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOrders(int $userId, string $dateFrom = '', string $dateTo = ''): array
    {
        $params = [$userId];

        $sql = "
            SELECT o.id AS order_id, o.created_at, o.total, o.status, o.notes,
                   r.room_number
            FROM orders o
            LEFT JOIN rooms r ON o.room_id = r.id
            WHERE o.user_id = ?
              AND o.status != 'cancelled'
        ";

        if ($dateFrom) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $order['items'] = $this->getOrderItems($order['order_id']);
        }
        return $orders;
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT oi.quantity, oi.price, p.name AS product_name, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}