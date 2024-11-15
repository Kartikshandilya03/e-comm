<?php
include "config.php";
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

$sql = "SELECT id, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    exit;
}

$stmt->bind_result($user_id, $hashed_password);
$stmt->fetch();

if (!password_verify($password, $hashed_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
    exit;
}

$secret_key = "your_secret_key";
$header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
$issuer = "http://yourdomain.com"; 
$audience = "http://yourdomain.com";
$issued_at = time();
$expiration_time = $issued_at + (60 * 60);
$payload = json_encode([
    "iss" => $issuer,
    "aud" => $audience,
    "iat" => $issued_at,
    "exp" => $expiration_time,
    "user_id" => $user_id
]);

$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
$base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
$signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret_key, true);
$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
echo json_encode(['status' => 'success', 'message' => 'Login successful.', 'token' => $jwt]);
$stmt->close();
$conn->close();
?>
