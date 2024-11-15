<?php
include "config.php";

$sql = "SELECT id, name, description, price, category FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'category' => $row['category']
        ];
    }

    echo json_encode(['status' => 'success', 'products' => $products]);
} else {
    echo json_encode(['status' => 'success', 'message' => 'No products found.']);
}

// Close connection
$conn->close();
?>
