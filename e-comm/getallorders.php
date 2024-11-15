<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 20;
    $offset = ($page - 1) * $limit;

    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;

    $query = "SELECT o.order_id, o.order_date, o.status, o.total_amount, u.name AS customer_name, 
              p.name AS product_name, oi.quantity, oi.price 
              FROM orders o
              JOIN users u ON o.user_id = u.user_id
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN products p ON oi.product_id = p.product_id
              WHERE 1=1";

    if ($start_date && $end_date) {
        $query .= " AND o.order_date BETWEEN ? AND ?";
    } elseif ($start_date) {
        $query .= " AND o.order_date >= ?";
    } elseif ($end_date) {
        $query .= " AND o.order_date <= ?";
    }

    if ($status) {
        $query .= " AND o.status = ?";
    }

    $query .= " LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($query);

    $params = [];
    if ($start_date && $end_date) {
        $params = [$start_date, $end_date, $limit, $offset];
    } elseif ($start_date) {
        $params = [$start_date, $limit, $offset];
    } elseif ($end_date) {
        $params = [$end_date, $limit, $offset];
    } elseif ($status) {
        $params = [$status, $limit, $offset];
    } else {
        $params = [$limit, $offset];
    }
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo json_encode(["message" => "No orders found."]);
        exit;
    }
    $count_query = "SELECT COUNT(*) AS total_orders FROM orders o WHERE 1=1";
    if ($start_date && $end_date) {
        $count_query .= " AND o.order_date BETWEEN ? AND ?";
    } elseif ($start_date) {
        $count_query .= " AND o.order_date >= ?";
    } elseif ($end_date) {
        $count_query .= " AND o.order_date <= ?";
    }

    if ($status) {
        $count_query .= " AND o.status = ?";
    }

    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
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
