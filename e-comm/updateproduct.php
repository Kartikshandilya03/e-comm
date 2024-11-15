<?php
include "config.php";

$productId = $_GET['productId'] ?? 0;
$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;
$category = $_POST['category'] ?? null;

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
$updateFields = [];
$params = [];

if ($name) {
    $updateFields[] = "name = ?";
    $params[] = $name;
}

if ($description) {
    $updateFields[] = "description = ?";
    $params[] = $description;
}

if ($price !== null) {
    if (!is_numeric($price) || $price <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Price must be a positive number.']);
        exit;
    }
    $updateFields[] = "price = ?";
    $params[] = $price;
}

if ($category) {
    $updateFields[] = "category = ?";
    $params[] = $category;
}

if (empty($updateFields)) {
    echo json_encode(['status' => 'error', 'message' => 'No fields to update.']);
    exit;
}

$sql = "UPDATE products SET " . implode(", ", $updateFields) . " WHERE id = ?";
$params[] = $productId;
$stmt = $conn->prepare($sql);

$stmt->bind_param(str_repeat('s', count($updateFields) - 1) . 'i', ...$params);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error updating product.']);
}

$stmt->close();
$conn->close();
?>
