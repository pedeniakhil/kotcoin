<?php
require 'db.php';

$code = isset($_GET['code']) ? strtoupper(trim($_GET['code'])) : '';
$details = null;
$error = '';

if ($code) {
    $stmt = $conn->prepare('SELECT t.*, sender.username AS sender_username, receiver.username AS receiver_username FROM transactions t LEFT JOIN users sender ON t.sender_id = sender.id LEFT JOIN users receiver ON t.recipient_id = receiver.id WHERE t.transaction_code = ? LIMIT 1');
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $query = $stmt->get_result();
    if ($query && $query->num_rows > 0) {
        $details = $query->fetch_assoc();
        // Try to get the receiver code from pending_codes (if possible)
        $receiver_code = '';
        $code_query = $conn->query("SELECT code FROM pending_codes WHERE user_id = " . $details['recipient_id'] . " AND status = 'used' ORDER BY used_at DESC LIMIT 1");
        if ($code_query && $code_query->num_rows > 0) {
            $receiver_code = $code_query->fetch_assoc()['code'];
        }
        $details['receiver_code'] = $receiver_code;
    } else {
        $error = 'Transaction not found!';
    }
} else {
    $error = 'No transaction code provided!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-blue-500 via-green-500 to-purple-500">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-blue-400 mb-8">
                <div class="flex flex-col items-center mb-6">
                    <span class="material-icons text-blue-500 text-5xl mb-2">receipt_long</span>
                    <h2 class="text-3xl font-extrabold text-gray-800">Transaction Details</h2>
                </div>
                <?php if ($error): ?>
                    <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 text-center font-medium">
                        <?php echo $error; ?>
                    </div>
                <?php elseif ($details): ?>
                    <div class="bg-green-50 p-4 rounded-xl mt-4">
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Transaction Code:</span> <span class="font-mono text-blue-700"><?php echo htmlspecialchars($details['transaction_code']); ?></span></div>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Sender:</span> <span><?php echo htmlspecialchars($details['sender_username'] ?? 'Unknown User'); ?></span></div>
                        <?php if (!empty($details['sender_account'])): ?>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Sender Account:</span> <span class="font-mono"><?php echo htmlspecialchars($details['sender_account']); ?></span></div>
                        <?php endif; ?>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Receiver:</span> <span><?php echo htmlspecialchars($details['receiver_username'] ?? 'Unknown User'); ?></span></div>
                        <?php if (!empty($details['receiver_account'])): ?>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Receiver Account:</span> <span class="font-mono"><?php echo htmlspecialchars($details['receiver_account']); ?></span></div>
                        <?php endif; ?>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Amount:</span> <span class="font-bold text-green-700">$<?php echo number_format($details['amount'], 2); ?></span></div>
                        <?php if (!empty($details['receiver_code'])): ?>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Receiver Code Used:</span> <span class="font-mono text-purple-700"><?php echo htmlspecialchars($details['receiver_code']); ?></span></div>
                        <?php endif; ?>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Status:</span> <span class="font-bold text-<?php echo $details['status'] === 'completed' ? 'green' : 'yellow'; ?>-700"><?php echo ucfirst($details['status']); ?></span></div>
                        <div class="mb-2 flex justify-between"><span class="font-medium text-gray-600">Date:</span> <span><?php echo htmlspecialchars($details['created_at']); ?></span></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="dashboard.php" class="text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>