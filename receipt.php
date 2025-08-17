<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if receipt data exists
if (!isset($_SESSION['receipt_data'])) {
    header('Location: dashboard.php');
    exit();
}

$receipt_data = $_SESSION['receipt_data'];

// Clear the receipt data from session after displaying
unset($_SESSION['receipt_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-green-500 via-blue-500 to-purple-500">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <!-- Receipt Card -->
            <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-green-400">
                <div class="flex flex-col items-center mb-6">
                    <span class="material-icons text-green-500 text-5xl mb-2">receipt</span>
                    <h2 class="text-3xl font-extrabold text-gray-800">Payment Receipt</h2>
                    <p class="text-gray-500">Transaction completed successfully</p>
                </div>

                <!-- Success Icon -->
                <div class="flex justify-center mb-6">
                    <div class="bg-green-100 rounded-full p-4">
                        <span class="material-icons text-green-500 text-4xl">check_circle</span>
                    </div>
                </div>

                <!-- Receipt Details -->
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-medium">Transaction ID:</span>
                        <span class="font-bold text-gray-800">#<?php echo $receipt_data['transaction_id']; ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-medium">Amount:</span>
                        <span class="font-bold text-green-600 text-xl">$<?php echo number_format($receipt_data['amount'], 2); ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-medium">From:</span>
                        <span class="font-bold text-gray-800"><?php echo htmlspecialchars($receipt_data['sender_name']); ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-medium">To:</span>
                        <span class="font-bold text-gray-800"><?php echo htmlspecialchars($receipt_data['receiver_name']); ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-200">
                        <span class="text-gray-600 font-medium">Transaction Code:</span>
                        <span class="font-bold text-blue-600"><?php echo $receipt_data['transaction_code']; ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3">
                        <span class="text-gray-600 font-medium">Date:</span>
                        <span class="font-bold text-gray-800"><?php echo date('M j, Y g:i A'); ?></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button onclick="window.print()" class="w-full bg-gradient-to-r from-blue-500 to-purple-500 text-white py-3 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-purple-600 transition flex items-center justify-center gap-2">
                        <span class="material-icons">print</span>Print Receipt
                    </button>
                    
                    <a href="dashboard.php" class="block w-full bg-gradient-to-r from-green-500 to-blue-500 text-white py-3 rounded-lg font-semibold shadow-lg hover:from-green-600 hover:to-blue-600 transition flex items-center justify-center gap-2">
                        <span class="material-icons">home</span>Back to Dashboard
                    </a>
                    
                    <a href="transactions.php" class="block w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 rounded-lg font-semibold shadow-lg hover:from-purple-600 hover:to-pink-600 transition flex items-center justify-center gap-2">
                        <span class="material-icons">receipt_long</span>View All Transactions
                    </a>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center text-gray-500 text-sm">
                    <p>Thank you for using our payment system!</p>
                    <p class="mt-1">Keep this receipt for your records.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body {
                background: white !important;
            }
            .container {
                max-width: none !important;
            }
            .bg-gradient-to-tr {
                background: white !important;
            }
            .bg-white {
                background: white !important;
                box-shadow: none !important;
            }
        }
    </style>
</body>
</html> 