<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$msg = '';
$step = 1;
$receiver_username = isset($_GET['to']) ? trim($_GET['to']) : '';
$receiver_id = '';
$receiver_code = isset($_GET['receiver_code']) ? trim($_GET['receiver_code']) : '';
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$rate = 2.50;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_code'])) {
        // Step 1: Check receiver code
        $receiver_code = $conn->real_escape_string(trim($_POST['receiver_code']));
        $receiver_username = $_POST['receiver_username'];
        $amount = floatval($_POST['amount']);
        $code_query = $conn->query("
            SELECT pc.*, u.username, u.id as user_id 
            FROM pending_codes pc 
            JOIN users u ON pc.user_id = u.id 
            WHERE pc.code = '$receiver_code' 
            AND pc.status = 'active' 
            AND pc.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        if ($code_query && $code_query->num_rows > 0) {
            $receiver = $code_query->fetch_assoc();
            $receiver_id = $receiver['user_id'];
            if ($receiver['username'] !== $receiver_username) {
                $msg = 'Receiver code does not match the intended user!';
            } else {
                $step = 2;
            }
        } else {
            $msg = 'Invalid or expired receiver code!';
        }
    } elseif (isset($_POST['send_money'])) {
        // Step 2: Send money
        $receiver_id = intval($_POST['receiver_id']);
        $receiver_username = $_POST['receiver_username'];
        $receiver_code = $conn->real_escape_string(trim($_POST['receiver_code']));
        $amount = floatval($_POST['amount']);
        if ($receiver_id == $_SESSION['user_id']) {
            $msg = 'You cannot send money to yourself!';
            $step = 2;
        } elseif ($amount <= 0) {
            $msg = 'Amount must be greater than 0!';
            $step = 2;
        } else {
            // Check sender's balance
            $sender_query = $conn->query("SELECT balance FROM users WHERE id = " . $_SESSION['user_id']);
            $sender = $sender_query->fetch_assoc();
            if ($sender['balance'] < $amount) {
                $msg = 'Insufficient balance!';
                $step = 2;
            } else {
                // Generate 5-letter transaction code with robust uniqueness check
                $max_attempts = 10;
                $attempts = 0;
                $inserted = false;
                while (!$inserted && $attempts < $max_attempts) {
                    $transaction_code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5));
                    $code_exists = $conn->query("SELECT id FROM transactions WHERE transaction_code = '$transaction_code'");
                    if ($code_exists->num_rows == 0) {
                        $result = $conn->query("INSERT INTO transactions (sender_id, recipient_id, amount, transaction_code, status, completed_at) VALUES (" . $_SESSION['user_id'] . ", $receiver_id, $amount, '$transaction_code', 'completed', NOW())");
                        if ($result) {
                            $inserted = true;
                        }
                    }
                    $attempts++;
                }
                if (!$inserted) {
                    $msg = 'Failed to generate a unique transaction code. Please try again.';
                    $step = 2;
                } else {
                    // Deduct from sender
                    $conn->query("UPDATE users SET balance = balance - $amount WHERE id = " . $_SESSION['user_id']);
                    // Add to receiver
                    $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $receiver_id");
                    // Mark the receiver code as used
                    $conn->query("UPDATE pending_codes SET status = 'used', used_at = NOW() WHERE code = '$receiver_code'");

                    $msg = 'Money sent successfully! Transaction code: ' . $transaction_code . ' to ' . $receiver_username;
                    $step = 1; // Reset to step 1 after sending
                }
            }
        }
    }
}

// Get current balance
$balance_query = $conn->query("SELECT balance FROM users WHERE id = " . $_SESSION['user_id']);
$balance = 0;
if ($balance_query && $balance_query->num_rows > 0) {
    $balance = $balance_query->fetch_assoc()['balance'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money (Fixed Amount)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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

            <!-- Send Money Form (Fixed Amount) -->
            <div class="bg-white bg-opacity-90 p-8 rounded-2xl shadow-2xl border-t-4 border-blue-400">
                <div class="flex flex-col items-center mb-6">
                    <span class="material-icons text-blue-500 text-5xl mb-2">send</span>
                    <h2 class="text-3xl font-extrabold text-gray-800">Send Money (Fixed Amount)</h2>
                    <p class="text-gray-500">Send exactly <span class="font-bold text-green-700"><?php echo number_format($amount, 2); ?> Kotcoin</span> to <span class="font-bold text-blue-700"><?php echo htmlspecialchars($receiver_username); ?></span></p>
                </div>

                <?php if ($msg): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo strpos($msg, 'successfully') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> text-center font-medium">
                        <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                <!-- Step 1: Enter receiver code -->
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="receiver_username" value="<?php echo htmlspecialchars($receiver_username); ?>">
                    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
                    <div class="relative">
                        <span class="material-icons absolute left-3 top-2.5 text-gray-400">qr_code</span>
                        <input type="text" name="receiver_code" required placeholder="Receiver's Code (6 digits)" maxlength="6"
                               class="pl-10 w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition" value="<?php echo htmlspecialchars($receiver_code); ?>" />
                    </div>
                    <button type="submit" name="check_code" class="w-full bg-gradient-to-r from-blue-500 to-green-500 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-green-600 transition flex items-center justify-center gap-2">
                        <span class="material-icons">search</span>Check
                    </button>
                </form>
                <?php elseif ($step === 2): ?>
                <!-- Step 2: Confirm and send money -->
                <form method="POST" class="space-y-5">
                    <div class="mb-4 text-center">
                        <span class="material-icons text-green-500 text-4xl">person</span>
                        <div class="text-lg font-semibold text-gray-700">Receiver: <span class="text-blue-700"><?php echo htmlspecialchars($receiver_username); ?></span></div>
                    </div>
                    <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($receiver_id); ?>">
                    <input type="hidden" name="receiver_username" value="<?php echo htmlspecialchars($receiver_username); ?>">
                    <input type="hidden" name="receiver_code" value="<?php echo htmlspecialchars($receiver_code); ?>">
                    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
                    <div class="relative">
                        <span class="material-icons absolute left-3 top-2.5 text-gray-400">attach_money</span>
                        <input type="number" name="amount_display" value="<?php echo htmlspecialchars($amount); ?>" readonly
                               class="pl-10 w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition bg-gray-100 cursor-not-allowed" />
                        <div class="text-xs text-gray-500 mt-1">Amount is fixed and cannot be changed</div>
                    </div>
                    <button type="submit" name="send_money" class="w-full bg-gradient-to-r from-blue-500 to-green-500 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-green-600 transition flex items-center justify-center gap-2">
                        <span class="material-icons">send</span>Send Money
                    </button>
                </form>
                <?php endif; ?>

                <div class="mt-6 space-y-2">
                    <a href="dashboard.php" class="block text-center text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
                    <a href="receive_money.php" class="block text-center text-green-600 font-semibold hover:underline">Receive Money</a>
                    <a href="transactions.php" class="block text-center text-purple-600 font-semibold hover:underline">View Transactions</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 