<?php
include "config.php";

$productId = $_GET['productId'] ?? 0;

if (!$productId || !is_numeric($productId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
    exit;
}

$sql = "SELECT id FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    exit;
}
$stmt->close();

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error deleting product.']);
}

$stmt->close();
$conn->close();
?>
