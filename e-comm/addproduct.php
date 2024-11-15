<?php

include "config.php";
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? 0;
$category = $_POST['category'] ?? '';

if (empty($name) || empty($price) || empty($category)) {
    echo json_encode(['status' => 'error', 'message' => 'Name, price, and category are required.']);
    exit;
}

if (!is_numeric($price) || $price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Price must be a positive number.']);
    exit;
}

$sql = "INSERT INTO products (name, description, price, category) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssds", $name, $description, $price, $category);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product added successfully.', 'product_id' => $stmt->insert_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error adding product.']);
}
$stmt->close();
$conn->close();
?>
