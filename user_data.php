<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

function error_response($msg) {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}

// Get params (GET or POST)
$apikey = isset($_REQUEST['apikey']) ? trim($_REQUEST['apikey']) : null;
$username = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : null;
$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;

$user = null;

if ($apikey) {
    // Authenticate by API key
    $stmt = $conn->prepare('SELECT id, username, balance FROM users WHERE api_key = ? LIMIT 1');
    $stmt->bind_param('s', $apikey);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        error_response('Invalid API key.');
    }
    $stmt->close();
} elseif ($username && $password) {
    // Authenticate by username/password
    $stmt = $conn->prepare('SELECT id, username, password, balance FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $user = $row;
        } else {
            error_response('Invalid username or password.');
        }
    } else {
        error_response('Invalid username or password.');
    }
    $stmt->close();
} else {
    error_response('Please provide either apikey or username and password.');
}

$user_id = $user['id'];
$balance = $user['balance'];

// Get last 10 transactions (sent or received)
$stmt = $conn->prepare('
    SELECT t.*, sender.username as sender_username, recipient.username as recipient_username,
        CASE WHEN t.sender_id = ? THEN "sent" ELSE "received" END as transaction_type
    FROM transactions t
    JOIN users sender ON t.sender_id = sender.id
    JOIN users recipient ON t.recipient_id = recipient.id
    WHERE t.sender_id = ? OR t.recipient_id = ?
    ORDER BY t.created_at DESC
    LIMIT 10
');
$stmt->bind_param('iii', $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        'id' => $row['id'],
        'type' => $row['transaction_type'],
        'amount' => $row['amount'],
        'status' => $row['status'],
        'code' => $row['transaction_code'],
        'sender' => $row['sender_username'],
        'recipient' => $row['recipient_username'],
        'created_at' => $row['created_at'],
        'completed_at' => $row['completed_at'],
    ];
}
$stmt->close();

// Success response
$response = [
    'success' => true,
    'username' => $user['username'],
    'balance' => $balance,
    'transactions' => $transactions
];
echo json_encode($response); 