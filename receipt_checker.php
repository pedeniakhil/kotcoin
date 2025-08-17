<?php
// Improved receipt checker with better UI
$conn = new mysqli('localhost', 'root', '', 'login_system');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$code = '';
$error = '';
$row = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    if ($code) {
        $stmt = $conn->prepare('SELECT t.*, sender.username AS sender_username, receiver.username AS receiver_username FROM transactions t LEFT JOIN users sender ON t.sender_id = sender.id LEFT JOIN users receiver ON t.recipient_id = receiver.id WHERE t.transaction_code = ? LIMIT 1');
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            $error = 'Transaction not found!';
        }
        $stmt->close();
    } else {
        $error = 'Please enter a transaction code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-blue-500 via-green-500 to-purple-500">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-blue-400 mb-8">
                <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Transaction Checker</h2>
                <form method="POST" class="mb-6">
                    <div class="flex items-center gap-2">
                        <input type="text" name="code" value="<?php echo htmlspecialchars($code); ?>" placeholder="Enter transaction code" class="flex-1 border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition" required />
                        <button type="submit" class="bg-gradient-to-r from-blue-500 to-green-500 text-white px-4 py-2 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-green-600 transition">Check</button>
                    </div>
                </form>
                <?php if ($error): ?>
                    <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 text-center font-medium">
                        <?php echo $error; ?>
                    </div>
                <?php elseif ($row): ?>
                    <div class="bg-white rounded-xl shadow p-4">
                        <h3 class="text-lg font-bold text-blue-700 mb-4 flex items-center gap-2">
                            <span class="material-icons text-blue-500">receipt_long</span>Transaction Details
                        </h3>
                        <table class="w-full text-sm mb-2">
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Transaction Code</td>
                                <td class="text-gray-800 py-1"><?php echo htmlspecialchars($row['transaction_code']); ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Sender</td>
                                <td class="text-gray-800 py-1"><?php echo htmlspecialchars($row['sender_username'] ?? 'Unknown'); ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Receiver</td>
                                <td class="text-gray-800 py-1"><?php echo htmlspecialchars($row['receiver_username'] ?? 'Unknown'); ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Amount</td>
                                <td class="text-green-700 font-bold py-1"><?php echo number_format($row['amount'], 2); ?> Kotcoin</td>
                            </tr>
                            <?php if (isset($row['cancel_code']) && $row['cancel_code']): ?>
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Cancel Code</td>
                                <td class="text-red-700 font-bold py-1"><?php echo htmlspecialchars($row['cancel_code']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Status</td>
                                <td class="py-1 font-bold
                                    <?php
                                        if ($row['status'] === 'completed') echo 'bg-green-100 text-green-700 rounded px-2';
                                        elseif ($row['status'] === 'cancelled') echo 'bg-red-100 text-red-700 rounded px-2';
                                        else echo 'bg-yellow-100 text-yellow-700 rounded px-2';
                                    ?>
                                ">
                                    <?php
                                        if ($row['status'] === 'completed') echo 'Completed';
                                        elseif ($row['status'] === 'cancelled') echo 'Cancelled';
                                        else echo ucfirst($row['status']);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-semibold text-gray-600 pr-2 py-1">Date</td>
                                <td class="text-gray-800 py-1"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        </table>
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