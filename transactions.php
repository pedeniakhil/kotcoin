<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get all transactions for this user (both sent and received)
$transactions_query = $conn->query("
    SELECT 
        t.*,
        sender.username as sender_username,
        recipient.username as recipient_username,
        t.note,
        CASE 
            WHEN t.sender_id = " . $_SESSION['user_id'] . " THEN 'sent'
            ELSE 'received'
        END as transaction_type
    FROM transactions t
    JOIN users sender ON t.sender_id = sender.id
    JOIN users recipient ON t.recipient_id = recipient.id
    WHERE t.sender_id = " . $_SESSION['user_id'] . " OR t.recipient_id = " . $_SESSION['user_id'] . "
    ORDER BY t.created_at DESC
    LIMIT 50
");

$transactions = [];
while ($row = $transactions_query->fetch_assoc()) {
    $transactions[] = $row;
}

// Get current balance
$balance_query = $conn->query("SELECT balance FROM users WHERE id = " . $_SESSION['user_id']);
$balance = $balance_query->fetch_assoc()['balance'];

// Get transaction statistics
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_transactions,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions,
        SUM(CASE WHEN recipient_id = " . $_SESSION['user_id'] . " AND status = 'completed' THEN amount ELSE 0 END) as total_received,
        SUM(CASE WHEN sender_id = " . $_SESSION['user_id'] . " THEN amount ELSE 0 END) as total_sent
    FROM transactions 
    WHERE sender_id = " . $_SESSION['user_id'] . " OR recipient_id = " . $_SESSION['user_id'] . "
");
$stats = $stats_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-purple-500 via-blue-500 to-green-500">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
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

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white bg-opacity-90 p-6 rounded-2xl shadow-2xl border-t-4 border-blue-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Total Sent</h3>
                            <p class="text-2xl font-bold text-red-600">$<?php echo number_format($stats['total_sent'], 2); ?></p>
                        </div>
                        <span class="material-icons text-red-500 text-3xl">send</span>
                    </div>
                </div>
                
                <div class="bg-white bg-opacity-90 p-6 rounded-2xl shadow-2xl border-t-4 border-green-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Total Received</h3>
                            <p class="text-2xl font-bold text-green-600">$<?php echo number_format($stats['total_received'], 2); ?></p>
                        </div>
                        <span class="material-icons text-green-500 text-3xl">download</span>
                    </div>
                </div>
                
                <div class="bg-white bg-opacity-90 p-6 rounded-2xl shadow-2xl border-t-4 border-purple-400">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700">Total Transactions</h3>
                            <p class="text-2xl font-bold text-purple-600"><?php echo $stats['total_transactions']; ?></p>
                        </div>
                        <span class="material-icons text-purple-500 text-3xl">receipt</span>
                    </div>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="bg-white bg-opacity-90 p-8 rounded-2xl shadow-2xl border-t-4 border-purple-400">
                <div class="flex flex-col items-center mb-6">
                    <span class="material-icons text-purple-500 text-5xl mb-2">receipt_long</span>
                    <h2 class="text-3xl font-extrabold text-gray-800">Transaction History</h2>
                    <p class="text-gray-500">Your recent transactions</p>
                </div>

                <?php if (empty($transactions)): ?>
                    <div class="text-center py-8">
                        <span class="material-icons text-gray-400 text-6xl mb-4">receipt</span>
                        <p class="text-gray-500 text-lg">No transactions yet</p>
                        <a href="send_money.php" class="inline-block mt-4 text-blue-600 font-semibold hover:underline">Send your first transaction</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="material-icons text-sm <?php echo $transaction['transaction_type'] === 'sent' ? 'text-red-500' : 'text-green-500'; ?>">
                                                <?php echo $transaction['transaction_type'] === 'sent' ? 'send' : 'download'; ?>
                                            </span>
                                            <span class="text-sm font-medium text-gray-500 uppercase">
                                                <?php echo $transaction['transaction_type']; ?>
                                            </span>
                                            <span class="px-2 py-1 text-xs rounded-full <?php 
                                                echo $transaction['status'] === 'completed' ? 'bg-green-100 text-green-700' : 
                                                    ($transaction['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'); 
                                            ?>">
                                                <?php echo $transaction['status']; ?>
                                            </span>
                                        </div>
                                        
                                        <p class="font-semibold text-gray-800">
                                            <?php if ($transaction['transaction_type'] === 'sent'): ?>
                                                To: <?php echo htmlspecialchars($transaction['recipient_username']); ?>
                                            <?php else: ?>
                                                From: <?php echo htmlspecialchars($transaction['sender_username']); ?>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <p class="text-lg font-bold <?php echo $transaction['transaction_type'] === 'sent' ? 'text-red-600' : 'text-green-600'; ?>">
                                            <?php echo $transaction['transaction_type'] === 'sent' ? '-' : '+'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                                        </p>
                                        
                                        <div class="flex items-center gap-4 text-sm text-gray-500 mt-2">
                                            <span>Code: <?php echo $transaction['transaction_code']; ?></span>
                                            <span><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></span>
                                        </div>
                                        <?php if (!empty($transaction['note'])): ?>
                                            <div class="mt-2 text-sm text-gray-700 bg-gray-100 bg-opacity-60 rounded px-3 py-1">
                                                <span class="font-semibold">Note:</span> <?php echo htmlspecialchars($transaction['note']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6 space-y-2 text-center">
                <a href="dashboard.php" class="block text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
                <a href="send_money.php" class="inline-block mx-2 text-green-600 font-semibold hover:underline">Send Money</a>
                <a href="receive_money.php" class="inline-block mx-2 text-purple-600 font-semibold hover:underline">Receive Money</a>
            </div>

            <a href="download_transactions_pdf.php" class="inline-flex items-center bg-gradient-to-r from-green-500 to-blue-500 text-white px-4 py-2 rounded-lg font-semibold shadow-lg hover:from-green-600 hover:to-blue-600 transition gap-2" target="_blank">
                <span class="material-icons">download</span>Download PDF
            </a>
        </div>
    </div>
</body>
</html> 