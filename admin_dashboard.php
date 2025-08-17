<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin_login.php');
    exit();
}
require_once 'db.php';
// Handle credit/debit
$msg = '';
$prepare_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['amount'], $_POST['action'], $_POST['note'])) {
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $action = $_POST['action'];
    $note = trim($_POST['note']);
    if ($amount > 0 && ($action === 'credit' || $action === 'debit')) {
        $amount_signed = $action === 'credit' ? $amount : -$amount;
        // Update balance
        $stmt = $conn->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
        $stmt->bind_param('di', $amount_signed, $user_id);
        $stmt->execute();
        // Add transaction (admin as sender for debit, as recipient for credit)
        $admin_id = 0; // Use 0 for admin
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
        $stmt2 = $conn->prepare('INSERT INTO transactions (sender_id, recipient_id, amount, transaction_code, status, note, created_at) VALUES (?, ?, ?, ?, "completed", ?, NOW())');
        if ($stmt2 === false) {
            $prepare_error = 'Prepare failed: ' . $conn->error;
        } else {
            if ($action === 'credit') {
                $stmt2->bind_param('iidss', $admin_id, $user_id, $amount, $code, $note);
            } else {
                $stmt2->bind_param('iidss', $user_id, $admin_id, $amount, $code, $note);
            }
            $stmt2->execute();
            $msg = 'Transaction successful!';
        }
    } else {
        $msg = 'Invalid input.';
    }
}
// Fetch users
$users = $conn->query('SELECT id, username, balance FROM users');
$users_error = '';
if ($users === false) {
    $users_error = $conn->error;
}
// Fetch transactions (show sender and recipient usernames)
$transactions = $conn->query('SELECT t.id, s.username AS sender, r.username AS recipient, t.amount, t.status, t.transaction_code, t.note, t.created_at FROM transactions t LEFT JOIN users s ON t.sender_id = s.id LEFT JOIN users r ON t.recipient_id = r.id ORDER BY t.created_at DESC LIMIT 50');
$transactions_error = '';
if ($transactions === false) {
    $transactions_error = $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Kotcoin Bank</title>
    <style>
        body { background: #111; color: #fff; font-family: Arial, sans-serif; }
        .container { max-width: 1100px; margin: 2rem auto; padding: 2rem; background: #222; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { padding: 0.7rem; border-bottom: 1px solid #333; text-align: left; }
        th { background: #333; }
        tr:last-child td { border-bottom: none; }
        .form-inline { display: flex; gap: 0.5rem; align-items: center; }
        input[type=number] { width: 90px; padding: 0.4rem; border-radius: 4px; border: none; background: #333; color: #fff; }
        input[type=text] { width: 180px; padding: 0.4rem; border-radius: 4px; border: none; background: #333; color: #fff; }
        select { padding: 0.4rem; border-radius: 4px; border: none; background: #333; color: #fff; }
        button { padding: 0.4rem 1.2rem; border-radius: 4px; border: none; background: #ffb300; color: #222; font-weight: bold; cursor: pointer; }
        .msg { color: #4caf50; margin-bottom: 1rem; }
        .logout { float: right; color: #ffb300; text-decoration: none; }
        .error { color: #ff5252; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_logout.php" class="logout">Logout</a>
        <h1>Admin Dashboard</h1>
        <?php if ($msg): ?><div class="msg"><?php echo $msg; ?></div><?php endif; ?>
        <?php if ($prepare_error): ?><div class="error"><?php echo htmlspecialchars($prepare_error); ?></div><?php endif; ?>
        <h2>Users</h2>
        <?php if ($users_error): ?>
            <div class="error">Error loading users: <?php echo htmlspecialchars($users_error); ?></div>
        <?php else: ?>
        <table>
            <tr><th>Username</th><th>Balance</th><th>Credit/Debit</th></tr>
            <?php while($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo number_format($user['balance'], 2); ?></td>
                <td>
                    <form class="form-inline" method="post" style="margin:0;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="number" name="amount" step="0.01" min="0.01" required placeholder="Amount">
                        <select name="action">
                            <option value="credit">Credit</option>
                            <option value="debit">Debit</option>
                        </select>
                        <input type="text" name="note" required placeholder="Note (e.g. Admin credited)">
                        <button type="submit">Submit</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
        <h2>Recent Transactions</h2>
        <?php if ($transactions_error): ?>
            <div class="error">Error loading transactions: <?php echo htmlspecialchars($transactions_error); ?></div>
        <?php else: ?>
        <table>
            <tr><th>ID</th><th>Sender</th><th>Recipient</th><th>Amount</th><th>Status</th><th>Code</th><th>Note</th><th>Date</th></tr>
            <?php while($txn = $transactions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $txn['id']; ?></td>
                <td><?php echo $txn['sender'] ? htmlspecialchars($txn['sender']) : '<span style="color:#ffb300;font-weight:bold;">Admin</span>'; ?></td>
                <td><?php echo $txn['recipient'] ? htmlspecialchars($txn['recipient']) : '<span style="color:#ffb300;font-weight:bold;">Admin</span>'; ?></td>
                <td><?php echo number_format($txn['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($txn['status']); ?></td>
                <td><?php echo htmlspecialchars($txn['transaction_code']); ?></td>
                <td><?php if (!empty($txn['note'])): ?><span style="color:#4caf50;font-weight:bold;"><?php echo htmlspecialchars($txn['note']); ?></span><?php endif; ?></td>
                <td><?php echo $txn['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>
</body>
</html> 