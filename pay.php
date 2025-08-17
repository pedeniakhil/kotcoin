<?php
header('Content-Type: application/json');
require '../db.php';

$rate = 2.50;

// Allow POST and GET for testing
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST or GET.']);
    exit();
}

// Get parameters from either POST or GET
$api_key = isset($_REQUEST['api_key']) ? $_REQUEST['api_key'] : '';
$receiver_code = isset($_REQUEST['receiver_code']) ? $_REQUEST['receiver_code'] : '';
$amount = isset($_REQUEST['amount']) ? floatval($_REQUEST['amount']) : 0;

if (!$api_key || !$receiver_code || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid parameters.']);
    exit();
}

// Authenticate user by API key
$user_query = $conn->query("SELECT id, balance FROM users WHERE api_key = '$api_key'");
if (!$user_query || $user_query->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key.']);
    exit();
}
$user = $user_query->fetch_assoc();
$sender_id = $user['id'];

// Check receiver code
$code_query = $conn->query("
    SELECT pc.*, u.username, u.id as user_id 
    FROM pending_codes pc 
    JOIN users u ON pc.user_id = u.id 
    WHERE pc.code = '$receiver_code' 
    AND pc.status = 'active' 
    AND pc.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
");
if (!$code_query || $code_query->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid or expired receiver code.']);
    exit();
}
$receiver = $code_query->fetch_assoc();
$receiver_id = $receiver['user_id'];

if ($receiver_id == $sender_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Cannot send money to yourself.']);
    exit();
}

// Check sender's balance
if ($user['balance'] < $amount) {
    http_response_code(400);
    echo json_encode(['error' => 'Insufficient balance.']);
    exit();
}

// Generate 5-letter transaction code
$transaction_code = '';
do {
    $transaction_code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5));
    $code_exists = $conn->query("SELECT id FROM transactions WHERE transaction_code = '$transaction_code'");
} while ($code_exists && $code_exists->num_rows > 0);

// Deduct from sender
$conn->query("UPDATE users SET balance = balance - $amount WHERE id = $sender_id");
// Add to receiver
$conn->query("UPDATE users SET balance = balance + $amount WHERE id = $receiver_id");
// Create transaction record as completed
$conn->query("INSERT INTO transactions (sender_id, recipient_id, amount, transaction_code, status, completed_at) VALUES ($sender_id, $receiver_id, $amount, '$transaction_code', 'completed', NOW())");
// Mark the receiver code as used
$conn->query("UPDATE pending_codes SET status = 'used', used_at = NOW() WHERE code = '$receiver_code'");

// Success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'transaction_code' => $transaction_code,
    'message' => 'Money sent successfully!'
]); 