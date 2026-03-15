<?php
require_once "./models/product.php";
class orderController{
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

   public function index(){
    $productModel=new product($this->conn);
    return $productModel->getAllProducts();
   }

   public function saveOrder($data){
    $userId = (int) ($data['user_id'] ?? 0);
    $roomId = (int) ($data['room_id'] ?? 0);
    $notes = trim((string) ($data['notes'] ?? ''));
    $items = $data['items'] ?? [];

    if ($userId <= 0) {
        return ['success' => false, 'message' => 'Invalid user'];
    }

    if ($roomId <= 0) {
        return ['success' => false, 'message' => 'Please select a room'];
    }

    if (!is_array($items) || count($items) === 0) {
        return ['success' => false, 'message' => 'Cart is empty'];
    }

    $roomStmt = $this->conn->prepare("SELECT id FROM rooms WHERE id = ?");
    $roomStmt->execute([$roomId]);
    if (!$roomStmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'Selected room does not exist'];
    }

    try {
        $this->conn->beginTransaction();

        $total = 0;
        $preparedItems = [];
        $productStmt = $this->conn->prepare("SELECT id, name, price, is_available FROM products WHERE id = ?");

        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                throw new Exception('Invalid cart item');
            }

            $productStmt->execute([$productId]);
            $product = $productStmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                throw new Exception('A selected product does not exist');
            }
            if ((int) $product['is_available'] !== 1) {
                throw new Exception('Product ' . $product['name'] . ' is not available');
            }

            $unitPrice = (float) $product['price'];
            $lineTotal = $unitPrice * $quantity;
            $total += $lineTotal;

            $preparedItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $unitPrice
            ];
        }

        // Step 1: Save order items first (without order_id yet)
        $createdOrderItemIds = [];
        $itemStmt = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (NULL, ?, ?, ?)");
        foreach ($preparedItems as $preparedItem) {
            $itemStmt->execute([
                $preparedItem['product_id'],
                $preparedItem['quantity'],
                $preparedItem['price']
            ]);
            $createdOrderItemIds[] = (int) $this->conn->lastInsertId();
        }

        // Step 2: Save order using user_id and total
        $orderStmt = $this->conn->prepare("INSERT INTO orders (user_id, room_id, notes, total, status) VALUES (?, ?, ?, ?, 'processing')");
        $orderStmt->execute([$userId, $roomId, $notes, $total]);
        $orderId = (int) $this->conn->lastInsertId();

        // Step 3: Link saved order items to the created order
        $linkStmt = $this->conn->prepare("UPDATE order_items SET order_id = ? WHERE id = ?");
        foreach ($createdOrderItemIds as $orderItemId) {
            $linkStmt->execute([$orderId, $orderItemId]);
        }

        $this->conn->commit();
        return [
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $orderId,
            'order_item_ids' => $createdOrderItemIds
        ];
    } catch (Exception $e) {
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
   }



}