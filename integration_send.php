<?php
require 'db.php';

$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$to = isset($_GET['to']) ? trim($_GET['to']) : '';
$msg = '';
$receiver_code = '';
$transaction_code = '';
$transaction_found = false;

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

// Step 2: AJAX check for transaction (API endpoint)
if (isset($_GET['check_code']) && isset($_GET['receiver_code']) && isset($_GET['amount']) && isset($_GET['to'])) {
    $receiver_code = $conn->real_escape_string($_GET['receiver_code']);
    $amount = floatval($_GET['amount']);
    $to = $conn->real_escape_string($_GET['to']);
    $user_query = $conn->query("SELECT id FROM users WHERE username = '$to'");
    if ($user_query && $user_query->num_rows > 0) {
        $recipient_id = $user_query->fetch_assoc()['id'];
        // Get the pending_code row for this code and user
        $code_query = $conn->query("SELECT * FROM pending_codes WHERE code = '$receiver_code' AND user_id = $recipient_id");
        if ($code_query && $code_query->num_rows > 0) {
            $code_row = $code_query->fetch_assoc();
            // Only proceed if the code is used and has a used_at timestamp
            if ($code_row['status'] === 'used' && $code_row['used_at']) {
                // Only accept a transaction that was completed within 2 seconds of the code being used, and after the code was created
                $tx_query = $conn->query("SELECT * FROM transactions WHERE recipient_id = $recipient_id AND amount = $amount AND status = 'completed' AND ABS(TIMESTAMPDIFF(SECOND, completed_at, '" . $code_row['used_at'] . "')) <= 2 AND completed_at > '" . $code_row['created_at'] . "' LIMIT 1");
                if ($tx_query && $tx_query->num_rows > 0) {
                    $tx = $tx_query->fetch_assoc();
                    echo json_encode(['found' => true, 'transaction_code' => $tx['transaction_code']]);
                } else {
                    echo json_encode(['found' => false]);
                }
            } else {
                echo json_encode(['found' => false]);
            }
        } else {
            echo json_encode(['found' => false]);
        }
    } else {
        echo json_encode(['found' => false]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Money Integration (Live)</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-green-500 via-blue-500 to-purple-500 flex items-center justify-center">
    <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-orange-400 max-w-md w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Send Money Integration (Live)</h2>
        <?php if ($msg): ?>
        <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 text-center font-medium"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if ($amount > 0 && $receiver_code): ?>
            <div class="mb-6 text-center">
                <div class="text-lg text-gray-700 mb-2">Ask the sender to send <span class="font-bold text-green-700"><?php echo number_format($amount, 2); ?> Kotcoin</span> to:</div>
                <div class="text-3xl font-mono font-bold text-blue-700 mb-2" id="receiverCodeDisplay"><?php echo $receiver_code; ?></div>
                <div class="text-gray-500 mb-2">Sender should use the <b>Send Money</b> page, enter this code and the amount.</div>
                <div class="mt-4">
                    <a href="send_money_request.php?amount=<?php echo urlencode($amount); ?>&to=<?php echo urlencode($to); ?>&receiver_code=<?php echo urlencode($receiver_code); ?>" target="_blank" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">Pay Now (Recommended Link)</a>
                </div>
            </div>
            <div id="liveStatus" class="mb-4 text-center text-lg font-semibold text-gray-700">Waiting for payment...</div>
            <div id="successBox" style="display:none;" class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 text-center font-medium">
                <div class="mb-2 font-bold">Payment Received!</div>
                <div>Transaction Code: <span class="font-mono text-blue-700" id="txCode"></span></div>
            </div>
            <script>
            function checkPayment() {
                fetch(`integration_send.php?check_code=1&receiver_code=<?php echo $receiver_code; ?>&amount=<?php echo $amount; ?>&to=<?php echo urlencode($to); ?>`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.found) {
                            document.getElementById('liveStatus').style.display = 'none';
                            document.getElementById('successBox').style.display = 'block';
                            document.getElementById('txCode').textContent = data.transaction_code;
                            // Redirect to receipt page after short delay
                            setTimeout(function() {
                                window.location.href = 'show_receipt.php?code=' + encodeURIComponent(data.transaction_code);
                            }, 2000);
                        } else {
                            setTimeout(checkPayment, 2000);
                        }
                    });
            }
            checkPayment();
            </script>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 