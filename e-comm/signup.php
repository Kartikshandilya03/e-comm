<?php
include "config.php";
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$address = $_POST['address'] ?? '';
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
    exit;
}
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO users (name, email, password, address) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $hashed_password, $address);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User registered successfully.', 'user_id' => $stmt->insert_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error registering user.']);
}
$stmt->close();
$conn->close();
?>
