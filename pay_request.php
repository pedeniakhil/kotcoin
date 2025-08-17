<?php
require 'db.php';

$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$to = isset($_GET['to']) ? trim($_GET['to']) : '';
$msg = '';
$receiver_code = '';
$transaction_verified = false;
$transaction_details = null;

// Step 1: Generate receiver code for the user (if username is provided)
if ($amount > 0 && $to) {
    $user_query = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($to) . "'");
    if ($user_query && $user_query->num_rows > 0) {
        $user = $user_query->fetch_assoc();
        $user_id = $user['id'];
        // Generate unique 6-digit code
        do {
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $conn->query("SELECT id FROM pending_codes WHERE code = '$code' AND status = 'active'");
        } while ($exists && $exists->num_rows > 0);
        // Store the generated code with fixed_amount
        $conn->query("INSERT INTO pending_codes (user_id, code, status, created_at, fixed_amount) VALUES ($user_id, '$code', 'active', NOW(), $amount)");
        $receiver_code = $code;
    } else {
        $msg = 'Recipient user not found!';
    }
}

// Step 2: Verify transaction code
if (isset($_POST['verify_code'])) {
    $verify_code = strtoupper(trim($_POST['verify_code']));
    // Get user_id for 'to' username
    $recipient_id = null;
    $code_matches_amount = false;
    if ($to) {
        $user_query = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($to) . "'");
        if ($user_query && $user_query->num_rows > 0) {
            $recipient_id = $user_query->fetch_assoc()['id'];
        }
    }
    // Check if the code's fixed_amount matches the URL amount
    if ($receiver_code) {
        $code_query = $conn->query("SELECT fixed_amount FROM pending_codes WHERE code = '$receiver_code'");
        if ($code_query && $code_query->num_rows > 0) {
            $row = $code_query->fetch_assoc();
            if (floatval($row['fixed_amount']) == floatval($amount)) {
                $code_matches_amount = true;
            }
        }
    }
    if ($recipient_id && $code_matches_amount) {
        $query = $conn->query("SELECT * FROM transactions WHERE transaction_code = '$verify_code' AND amount = $amount AND recipient_id = $recipient_id");
        if ($query && $query->num_rows > 0) {
            $transaction_verified = true;
            $transaction_details = $query->fetch_assoc();
        } else {
            $msg = 'Transaction not found, amount mismatch, or not sent to the correct user!';
        }
    } else if (!$code_matches_amount) {
        $msg = 'The code does not match the requested amount!';
    } else {
        $msg = 'Recipient user not found!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay with Kotcoin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-green-500 via-blue-500 to-purple-500 flex items-center justify-center">
    <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-green-400 max-w-md w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Pay with Kotcoin</h2>
        <?php if ($msg): ?>
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 text-center font-medium"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if ($amount > 0 && $receiver_code): ?>
            <div class="mb-6 text-center">
                <div class="text-lg text-gray-700 mb-2">Send <span class="font-bold text-green-700"><?php echo number_format($amount, 2); ?> Kotcoin</span> to:</div>
                <div class="text-3xl font-mono font-bold text-blue-700 mb-2"><?php echo $receiver_code; ?></div>
                <div class="text-gray-500 mb-2">Use your wallet/app to send the amount to this code.</div>
            </div>
            <form method="POST" class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Enter Transaction Code to Verify Payment</label>
                <input type="text" name="verify_code" maxlength="5" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition uppercase mb-2" placeholder="Transaction Code (5 letters)" required />
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-green-500 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-green-600 transition">Verify Payment</button>
            </form>
        <?php endif; ?>
        <?php if ($transaction_verified && $transaction_details): ?>
            <div class="mt-6 p-4 rounded-lg bg-green-100 text-green-700 text-center font-medium">
                <div class="mb-2 font-bold">Payment Verified!</div>
                <div>Transaction Code: <span class="font-mono text-blue-700"><?php echo htmlspecialchars($transaction_details['transaction_code']); ?></span></div>
                <div>Amount: <span class="font-bold"><?php echo number_format($transaction_details['amount'], 2); ?> Kotcoin</span></div>
                <div>Status: <span class="font-bold text-green-700"><?php echo ucfirst($transaction_details['status']); ?></span></div>
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 