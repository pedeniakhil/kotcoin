<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$msg = '';
$pending_transactions = [];
$generated_code = '';

// Handle code generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_code'])) {
    // Generate unique 6-digit code
    do {
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $code_exists = $conn->query("SELECT id FROM pending_codes WHERE code = '$code' AND status = 'active'");
    } while ($code_exists && $code_exists->num_rows > 0);
    
    // Store the generated code
    $insert_result = $conn->query("INSERT INTO pending_codes (user_id, code, status, created_at) VALUES (" . $_SESSION['user_id'] . ", '$code', 'active', NOW())");
    
    if ($insert_result) {
        $generated_code = $code;
        $msg = 'Code generated successfully! Share this code with the sender.';
    } else {
        $msg = 'Error generating code. Please try again.';
    }
}

// Handle code cancellation
if (isset($_POST['cancel_code_id'])) {
    $cancel_id = intval($_POST['cancel_code_id']);
    // Generate unique 6-digit cancel code
    do {
        $cancel_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $exists = $conn->query("SELECT id FROM pending_codes WHERE cancel_code = '$cancel_code'");
    } while ($exists && $exists->num_rows > 0);
    $conn->query("UPDATE pending_codes SET status = 'cancelled', cancel_code = '$cancel_code' WHERE id = $cancel_id");
    $msg = 'Code cancelled! Cancel code: ' . $cancel_code;
}

// Check for pending transactions for this user
$pending_query = $conn->query("
    SELECT t.*, u.username as sender_username 
    FROM transactions t 
    JOIN users u ON t.sender_id = u.id 
    WHERE t.recipient_id = " . $_SESSION['user_id'] . " 
    AND t.status = 'pending'
    ORDER BY t.created_at DESC
");

if ($pending_query) {
        while ($row = $pending_query->fetch_assoc()) {
            $pending_transactions[] = $row;
    }
}

// Get current balance
$balance_query = $conn->query("SELECT balance FROM users WHERE id = " . $_SESSION['user_id']);
$balance = 0;
if ($balance_query && $balance_query->num_rows > 0) {
$balance = $balance_query->fetch_assoc()['balance'];
}

// Get username for session if not already set
if (!isset($_SESSION['username'])) {
    $username_query = $conn->query("SELECT username FROM users WHERE id = " . $_SESSION['user_id']);
    if ($username_query && $username_query->num_rows > 0) {
        $_SESSION['username'] = $username_query->fetch_assoc()['username'];
    }
}

// Get user's active generated codes
$active_codes = [];
$active_codes_query = $conn->query("
    SELECT * FROM pending_codes 
    WHERE user_id = " . $_SESSION['user_id'] . " 
    AND status = 'active' 
    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY created_at DESC
");

if ($active_codes_query) {
    while ($row = $active_codes_query->fetch_assoc()) {
        $active_codes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Money</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta http-equiv="refresh" content="30"> <!-- Auto refresh every 30 seconds -->
</head>
<body class="min-h-screen bg-gradient-to-tr from-green-500 via-blue-500 to-purple-500">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <!-- Balance Card -->
            <div class="bg-white bg-opacity-90 p-6 rounded-2xl shadow-2xl border-t-4 border-green-400 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Your Balance</h3>
                        <p class="text-3xl font-bold text-green-600">$<?php echo number_format($balance, 2); ?></p>
                    </div>
                    <span class="material-icons text-green-500 text-4xl">account_balance_wallet</span>
                </div>
            </div>

            <!-- Generate Code Section -->
            <div class="bg-white bg-opacity-90 p-8 rounded-2xl shadow-2xl border-t-4 border-blue-400 mb-6">
                <div class="flex flex-col items-center mb-6">
                    <span class="material-icons text-blue-500 text-5xl mb-2">qr_code</span>
                    <h2 class="text-3xl font-extrabold text-gray-800">Generate Code</h2>
                    <p class="text-gray-500">Create a code for someone to send you money</p>
                </div>

                <?php if ($msg && $generated_code): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-700 text-center font-medium">
                        <?php echo $msg; ?>
                        <div class="mt-2 text-2xl font-bold text-green-800"><?php echo $generated_code; ?></div>
                    </div>
                <?php elseif ($msg): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-700 text-center font-medium">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <button type="submit" name="generate_code" class="w-full bg-gradient-to-r from-blue-500 to-purple-500 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-purple-600 transition flex items-center justify-center gap-2">
                        <span class="material-icons">qr_code</span>Generate New Code
                    </button>
                </form>
            </div>

            <!-- Active Codes -->
            <?php if (!empty($active_codes)): ?>
            <div class="bg-white bg-opacity-90 p-6 rounded-2xl shadow-2xl border-t-4 border-blue-400 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="material-icons text-blue-500 mr-2">qr_code</span>
                    Your Active Codes
                </h3>
                <div class="space-y-3">
                    <?php foreach ($active_codes as $code): ?>
                    <div class="border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <p class="text-2xl font-bold text-blue-600"><?php echo $code['code']; ?></p>
                            <p class="text-sm text-gray-500">Generated: <?php echo date('M j, Y g:i A', strtotime($code['created_at'])); ?></p>
                        </div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this code?');">
                            <input type="hidden" name="cancel_code_id" value="<?php echo $code['id']; ?>">
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-lg font-semibold shadow hover:bg-red-600 transition">Cancel</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pending Transactions -->
            <?php if (!empty($pending_transactions)): ?>
            <div class="bg-white bg-opacity-90 p-6 rounded-2xl shadow-2xl border-t-4 border-yellow-400">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <span class="material-icons text-yellow-500 mr-2">pending</span>
                    Pending Transactions
                </h3>
                <div class="space-y-3">
                    <?php foreach ($pending_transactions as $transaction): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-800">From: <?php echo htmlspecialchars($transaction['sender_username']); ?></p>
                                <p class="text-green-600 font-bold">$<?php echo number_format($transaction['amount'], 2); ?></p>
                                <p class="text-sm text-gray-500">Code: <?php echo $transaction['transaction_code']; ?></p>
                            </div>
                            <span class="material-icons text-yellow-500">schedule</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="mt-6 space-y-2">
                <a href="dashboard.php" class="block text-center text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
                <a href="send_money.php" class="block text-center text-green-600 font-semibold hover:underline">Send Money</a>
                <a href="transactions.php" class="block text-center text-purple-600 font-semibold hover:underline">View All Transactions</a>
            </div>
        </div>
    </div>
</body>
</html> 