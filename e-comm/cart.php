<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        echo json_encode(["error" => "Invalid user ID."]);
        exit;
    }

    $user_id = $_GET['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["error" => "User not found."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT p.name, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        echo json_encode(["message" => "Your cart is empty."]);
        exit;
    }
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }
    echo json_encode([
        "cart" => $cart_items,
        "total_amount" => number_format($total_amount, 2)
    ]);
}
?>
