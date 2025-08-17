<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Generate API key
if (isset($_POST['generate'])) {
    $api_key = bin2hex(random_bytes(32));
    $conn->query("UPDATE users SET api_key = '$api_key' WHERE id = $user_id");
    $msg = 'API key generated!';
}

// Get current API key
$key_query = $conn->query("SELECT api_key FROM users WHERE id = $user_id");
$api_key = '';
if ($key_query && $key_query->num_rows > 0) {
    $api_key = $key_query->fetch_assoc()['api_key'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Key Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-blue-500 via-green-500 to-purple-500 flex items-center justify-center">
    <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-blue-400 max-w-md w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">API Key Management</h2>
        <?php if ($msg): ?>
            <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 text-center font-medium"><?php echo $msg; ?></div>
        <?php endif; ?>
        <div class="mb-6 text-center">
            <div class="text-lg font-semibold text-gray-700 mb-2">Your API Key:</div>
            <div class="font-mono text-blue-700 text-sm break-all bg-gray-100 p-2 rounded select-all border border-blue-200 inline-block"><?php echo htmlspecialchars($api_key ?: 'No API key generated yet.'); ?></div>
        </div>
        <form method="POST" class="text-center mb-4">
            <button type="submit" name="generate" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow transition">Generate New API Key</button>
        </form>
        <div class="text-xs text-red-600 text-center mt-2">Regenerating your API key will invalidate the old key immediately.</div>
        <div class="text-center mt-6">
            <a href="dashboard.php" class="text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 