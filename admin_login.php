<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === 'admin' && $password === 'pedeniakhil') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Kotcoin Bank</title>
    <style>
        body { background: #111; color: #fff; font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: #222; padding: 2rem 2.5rem; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.5); }
        h2 { margin-top: 0; }
        input[type=text], input[type=password] { width: 100%; padding: 0.7rem; margin: 0.5rem 0 1rem 0; border: none; border-radius: 6px; background: #333; color: #fff; }
        button { width: 100%; padding: 0.7rem; background: #ffb300; color: #222; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .error { color: #ff5252; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <form class="login-box" method="post">
        <h2>Admin Login</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <input type="text" name="username" placeholder="Username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html> 