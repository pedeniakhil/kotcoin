<?php
header('Content-Type: application/json');
require '../db.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit();
}

$api_key = isset($_POST['api_key']) ? $_POST['api_key'] : '';
$upi_url = isset($_POST['upi_url']) ? trim($_POST['upi_url']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$custom_html = isset($_POST['custom_html']) ? $_POST['custom_html'] : null;

if (!$api_key || !$upi_url || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid parameters.']);
    exit();
}

// Authenticate user by API key
$user_query = $conn->query("SELECT id FROM users WHERE api_key = '$api_key'");
if (!$user_query || $user_query->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key.']);
    exit();
}
$user = $user_query->fetch_assoc();
$user_id = $user['id'];

// Generate unique code for the payment request
$code = '';
do {
    $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    $exists = $conn->query("SELECT id FROM custom_integration_requests WHERE code = '$code'");
} while ($exists && $exists->num_rows > 0);

// Store the request
$stmt = $conn->prepare("INSERT INTO custom_integration_requests (user_id, api_key, upi_url, amount, custom_html, code) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('issdss', $user_id, $api_key, $upi_url, $amount, $custom_html, $code);
if ($stmt->execute()) {
    $payment_link = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/../custom_payment.php?code=' . urlencode($code);
    echo json_encode(['success' => true, 'payment_link' => $payment_link, 'code' => $code]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create payment request.']);
} 