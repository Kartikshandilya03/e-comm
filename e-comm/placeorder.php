<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $shipping_address = $_POST['shipping_address'];
    if (!is_numeric($user_id)) {
        echo json_encode(["error" => "Invalid user ID."]);
        exit;
    }
    if (empty($shipping_address['address']) || empty($shipping_address['phone']) || empty($shipping_address['postal_code'])) {
        echo json_encode(["error" => "Shipping address is incomplete."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["error" => "User not found."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        echo json_encode(["error" => "Your cart is empty."]);
        exit;
    }
    $order_id = 'ORD' . time() . $user_id;

    $order_stmt = $pdo->prepare("INSERT INTO orders (order_id, user_id, shipping_address, total_amount) VALUES (?, ?, ?, ?)");
    $total_amount = calculateTotalAmount($cart_items);
    $order_stmt->execute([$order_id, $user_id, json_encode($shipping_address), $total_amount]);

    foreach ($cart_items as $item) {
        $insert_item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $insert_item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    $delete_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_stmt->execute([$user_id]);
    echo json_encode([
        "success" => "Order placed successfully.",
        "order_id" => $order_id
    ]);
}

function calculateTotalAmount($cart_items) {
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }
    return number_format($total_amount, 2);
}
?>
