<?php
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $customer_id = isset($_GET['customerId']) ? (int)$_GET['customerId'] : null;

    if (!$customer_id) {
        echo json_encode(["error" => "Customer ID is required."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();

    if (!$customer) {
        echo json_encode(["error" => "Customer not found."]);
        exit;
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 20;
    $offset = ($page - 1) * $limit;
    $query = "SELECT o.order_id, o.order_date, o.status, o.total_amount, u.name AS customer_name, 
              p.name AS product_name, oi.quantity, oi.price, o.shipping_address 
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN products p ON oi.product_id = p.product_id
              WHERE o.user_id = ?";

    $query .= " LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$customer_id, $limit, $offset]);

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo json_encode(["message" => "No orders found for this customer."]);
        exit;
    }
    $count_stmt = $pdo->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?");
    $count_stmt->execute([$customer_id]);
    $total_orders = $count_stmt->fetchColumn();
    $total_pages = ceil($total_orders / $limit);
    echo json_encode([
        "page" => $page,
        "total_pages" => $total_pages,
        "total_orders" => $total_orders,
        "orders" => $orders
    ]);
}
?>
