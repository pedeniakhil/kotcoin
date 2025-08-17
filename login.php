<?php
session_start();
require 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $res = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $msg = 'Invalid password!';
        }
    } else {
        $msg = 'User not found!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500">
    <div class="bg-white bg-opacity-90 p-10 rounded-2xl shadow-2xl border-t-4 border-indigo-400 w-full max-w-md transition-all">
        <div class="flex flex-col items-center mb-6">
            <span class="material-icons text-indigo-500 text-5xl mb-2">login</span>
            <h2 class="text-3xl font-extrabold text-gray-800">Welcome Back</h2>
            <p class="text-gray-500">Login to your account</p>
        </div>
        <?php if ($msg) echo "<div class='mb-4 text-red-500 text-center font-medium'>$msg</div>"; ?>
        <form method="POST" class="space-y-5">
            <div class="relative">
                <span class="material-icons absolute left-3 top-2.5 text-gray-400">person</span>
                <input type="text" name="username" required placeholder="Username" class="pl-10 w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" />
            </div>
            <div class="relative">
                <span class="material-icons absolute left-3 top-2.5 text-gray-400">lock</span>
                <input type="password" name="password" required placeholder="Password" class="pl-10 w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition" />
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:from-indigo-600 hover:to-pink-500 transition flex items-center justify-center gap-2"><span class="material-icons">login</span>Login</button>
        </form>
        <p class="mt-6 text-center text-gray-600">Don't have an account? <a href="signup.php" class="text-indigo-600 font-semibold hover:underline">Signup</a></p>
    </div>
</body>
</html> 