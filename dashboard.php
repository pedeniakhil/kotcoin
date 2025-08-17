<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get current balance with proper error handling
require 'db.php';
$balance = 0;
$username = '';

try {
    $balance_query = $conn->query("SELECT balance, username FROM users WHERE id = " . $_SESSION['user_id']);
    if ($balance_query && $balance_query->num_rows > 0) {
        $user_data = $balance_query->fetch_assoc();
        $balance = $user_data['balance'];
        $username = $user_data['username'];
        $_SESSION['username'] = $username; // Store username in session
    }
} catch (Exception $e) {
    // Handle database errors silently
    $balance = 0;
    $username = 'User';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kotcoin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 min-h-screen">
    <!-- Header -->
    <header class="bg-white bg-opacity-90 shadow-md py-4 px-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-icons text-indigo-600 text-3xl">account_balance_wallet</span>
            <span class="text-2xl font-extrabold text-indigo-700 tracking-wide">Kotcoin Dashboard</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-gray-700 font-semibold">Hi, <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="text-red-600 font-semibold hover:underline flex items-center gap-1"><span class="material-icons text-sm">logout</span>Logout</a>
        </div>
    </header>
    <div class="flex flex-col md:flex-row min-h-[calc(100vh-64px)]">
        <!-- Sidebar Navigation (Desktop) -->
        <aside class="hidden md:flex flex-col w-64 bg-white bg-opacity-90 shadow-lg p-6 space-y-4">
            <a href="dashboard.php" class="flex items-center gap-2 text-indigo-700 font-bold text-lg mb-2"><span class="material-icons">dashboard</span>Dashboard</a>
            <nav class="flex flex-col gap-2">
                <a href="send_money.php" class="flex items-center gap-2 text-gray-700 hover:text-indigo-600 font-medium"><span class="material-icons">send</span>Send Money</a>
                <a href="receive_money.php" class="flex items-center gap-2 text-gray-700 hover:text-green-600 font-medium"><span class="material-icons">download</span>Receive Money</a>
                <a href="transactions.php" class="flex items-center gap-2 text-gray-700 hover:text-purple-600 font-medium"><span class="material-icons">receipt_long</span>Transactions</a>
                <a href="kotcoin_calculator.php" class="flex items-center gap-2 text-gray-700 hover:text-yellow-600 font-medium"><span class="material-icons">calculate</span>Kotcoin Calculator</a>
                <a href="receipt_checker.php" class="flex items-center gap-2 text-gray-700 hover:text-pink-600 font-medium"><span class="material-icons">search</span>Search Receipt</a>
                <a href="api_key.php" class="flex items-center gap-2 text-gray-700 hover:text-indigo-600 font-medium"><span class="material-icons">vpn_key</span>API Key</a>
                <a href="api_docs.php" class="flex items-center gap-2 text-gray-700 hover:text-gray-600 font-medium"><span class="material-icons">description</span>API Docs</a>
                <a href="integration_docs.php" class="flex items-center gap-2 text-gray-700 hover:text-green-600 font-medium"><span class="material-icons">link</span>Integration Docs</a>
                <a href="user_dashboard.html" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 font-medium"><span class="material-icons">insights</span>Live User Data API</a>
                <a href="integration_send.php" class="flex items-center gap-2 text-gray-700 hover:text-orange-600 font-medium"><span class="material-icons">send</span>Send Money Integration (Live)</a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-10">
            <!-- Balance Card -->
            <div class="bg-white bg-opacity-95 rounded-2xl shadow-xl p-8 mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2"><span class="material-icons text-green-500">account_balance_wallet</span>Your Balance</h2>
                    <p class="text-4xl font-extrabold text-green-600">₹<?php echo number_format($balance, 2); ?></p>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="send_money.php" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg shadow flex items-center gap-2 justify-center"><span class="material-icons">send</span>Send Money</a>
                    <a href="receive_money.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-2 rounded-lg shadow flex items-center gap-2 justify-center"><span class="material-icons">download</span>Receive Money</a>
                </div>
            </div>
            <!-- Feature Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card Template Example -->
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-blue-500 text-5xl mb-3">send</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Send Money</h3>
                    <p class="text-gray-600 mb-4">Transfer money to other users instantly.</p>
                    <a href="send_money.php" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">send</span>Send</a>
                </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-green-500 text-5xl mb-3">download</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Receive Money</h3>
                    <p class="text-gray-600 mb-4">Generate codes to receive money.</p>
                    <a href="receive_money.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">download</span>Receive</a>
                </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-purple-500 text-5xl mb-3">receipt_long</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Transactions</h3>
                    <p class="text-gray-600 mb-4">View your transaction history.</p>
                    <a href="transactions.php" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">receipt</span>History</a>
                    </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-yellow-500 text-5xl mb-3">calculate</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Kotcoin Calculator</h3>
                    <p class="text-gray-600 mb-4">Convert Kotcoin ↔ Rupees.</p>
                    <a href="kotcoin_calculator.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">calculate</span>Convert</a>
                </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-pink-500 text-5xl mb-3">search</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Search Receipt</h3>
                    <p class="text-gray-600 mb-4">Check transaction by code.</p>
                    <a href="receipt_checker.php" class="bg-pink-500 hover:bg-pink-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">search</span>Search</a>
                    </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-indigo-500 text-5xl mb-3">vpn_key</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">API Key</h3>
                    <p class="text-gray-600 mb-4">Generate & manage your API key.</p>
                    <a href="api_key.php" class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">vpn_key</span>API Key</a>
                </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-gray-700 text-5xl mb-3">description</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">API Docs</h3>
                    <p class="text-gray-600 mb-4">How to use the payment API.</p>
                    <a href="api_docs.php" class="bg-gray-700 hover:bg-gray-800 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">description</span>Docs</a>
                    </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-green-500 text-5xl mb-3">link</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Integration Docs</h3>
                    <p class="text-gray-600 mb-4">Create payment links for your site.</p>
                    <a href="integration_docs.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">link</span>Docs</a>
                </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-blue-500 text-5xl mb-3">insights</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Live User Data API</h3>
                    <p class="text-gray-600 mb-4">Get your balance & transactions in real time using API key or username/password.<br><span class='text-xs text-blue-700'>Updates every 2 seconds!</span></p>
                    <a href="user_dashboard.html" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">insights</span>Live API Docs</a>
            </div>
                <div class="bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 flex flex-col items-center text-center hover:shadow-2xl transition">
                    <span class="material-icons text-orange-500 text-5xl mb-3">send</span>
                    <h3 class="text-xl font-bold text-gray-800 mb-1">Send Money Integration (Live)</h3>
                    <p class="text-gray-600 mb-4">Generate a code and check live if money is received.</p>
                    <a href="integration_send.php" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded-lg shadow flex items-center gap-2 justify-center w-full"><span class="material-icons">send</span>Integration</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 