<?php
require 'db.php';

$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$request = null;
$error = '';

if ($code) {
    $stmt = $conn->prepare('SELECT * FROM custom_integration_requests WHERE code = ? LIMIT 1');
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $request = $result->fetch_assoc();
    } else {
        $error = 'Invalid or expired payment link.';
    }
} else {
    $error = 'No payment code provided.';
}

// Check if payment is captured
$paid = false;
$transaction = null;
if ($request && $request['status'] === 'paid') {
    $paid = true;
    // Get transaction details
    $stmt = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = ? LIMIT 1');
    $stmt->bind_param('s', $request['transaction_code']);
    $stmt->execute();
    $tx_result = $stmt->get_result();
    if ($tx_result && $tx_result->num_rows > 0) {
        $transaction = $tx_result->fetch_assoc();
    }
}

// Handle AJAX check for payment status
if (isset($_GET['check_payment']) && $request) {
    if ($paid && $transaction) {
        header('Content-Type: application/json');
        echo json_encode([
            'paid' => true,
            'transaction_code' => $transaction['transaction_code'],
            'amount' => $transaction['amount'],
            'sender_id' => $transaction['sender_id'],
            'recipient_id' => $transaction['recipient_id'],
            'completed_at' => $transaction['completed_at'],
            'custom_html' => $request['custom_html'],
        ]);
    } else {
        echo json_encode(['paid' => false]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Custom Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-green-500 via-blue-500 to-purple-500 flex items-center justify-center">
    <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-orange-400 max-w-md w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Custom Payment</h2>
        <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 text-center font-medium"><?php echo $error; ?></div>
        <?php elseif ($request): ?>
            <div class="mb-6 text-center">
                <div class="text-lg text-gray-700 mb-2">Pay <span class="font-bold text-green-700"><?php echo number_format($request['amount'], 2); ?> Kotcoin</span> using UPI:</div>
                <div class="text-xl font-mono font-bold text-blue-700 mb-2"><a href="<?php echo htmlspecialchars($request['upi_url']); ?>" target="_blank"><?php echo htmlspecialchars($request['upi_url']); ?></a></div>
                <div class="text-gray-500 mb-2">After payment, this page will update automatically.</div>
            </div>
            <div id="liveStatus" class="mb-4 text-center text-lg font-semibold text-gray-700">Waiting for payment...</div>
            <div id="successBox" style="display:none;" class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 text-center font-medium">
                <div class="mb-2 font-bold">Payment Received!</div>
                <div id="jsonDetails"></div>
            </div>
            <script>
            function checkPayment() {
                fetch('custom_payment.php?code=<?php echo urlencode($code); ?>&check_payment=1')
                    .then(res => res.json())
                    .then(data => {
                        if (data.paid) {
                            document.getElementById('liveStatus').style.display = 'none';
                            document.getElementById('successBox').style.display = 'block';
                            document.getElementById('jsonDetails').textContent = JSON.stringify(data, null, 2);
                            if (data.custom_html) {
                                document.getElementById('jsonDetails').innerHTML += '<div class="mt-4">' + data.custom_html + '</div>';
                            }
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