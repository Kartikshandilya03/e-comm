<?php
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['quantity'];

    if (!is_numeric($product_id) || !is_numeric($new_quantity) || $new_quantity < 0) {
        echo json_encode(["error" => "Invalid product ID or quantity."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(["error" => "Product not found."]);
        exit;
    }

    $available_stock = $product['stock'];

    if ($new_quantity > $available_stock) {
        echo json_encode(["error" => "Insufficient stock for this product."]);
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

    if ($new_quantity == 0) {
        $delete_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $delete_stmt->execute([$user_id, $product_id]);

        echo json_encode(["success" => "Product removed from cart.", "cart" => getCartDetails($user_id)]);
    } else {
        $update_stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $update_stmt->execute([$new_quantity, $user_id, $product_id]);
        echo json_encode(["success" => "Cart updated successfully.", "cart" => getCartDetails($user_id)]);
    }
}

function getCartDetails($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.name, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
