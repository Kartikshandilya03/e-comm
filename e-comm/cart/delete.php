<?php
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    if (!is_numeric($product_id)) {
        echo json_encode(["error" => "Invalid product ID."]);
        exit;
    }
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();

    if (!$existing_item) {
        echo json_encode(["error" => "Product not found in your cart."]);
        exit;
    }

    $delete_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $delete_stmt->execute([$user_id, $product_id]);

    echo json_encode(["success" => "Product removed from cart.", "cart" => getCartDetails($user_id)]);
}

function getCartDetails($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.name, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
