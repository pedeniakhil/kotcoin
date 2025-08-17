<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in.");
}

// Try to load mPDF
$mpdf_path = __DIR__ . '/vendor/autoload.php';
if (!file_exists($mpdf_path)) {
    die("mPDF library not found. Please run: composer require mpdf/mpdf");
}
require_once $mpdf_path;

$user_id = $_SESSION['user_id'];
$query = $conn->query("SELECT t.*, u.username as receiver FROM transactions t JOIN users u ON t.recipient_id = u.id WHERE t.sender_id = $user_id OR t.recipient_id = $user_id ORDER BY t.created_at DESC");

$html = '<h2 style="text-align:center;">Kotcoin Transaction History</h2><table border="1" cellpadding="5" cellspacing="0" width="100%"><tr><th>ID</th><th>Type</th><th>Amount (Kotcoin)</th><th>Receiver</th><th>Status</th><th>Date</th></tr>';
while ($row = $query->fetch_assoc()) {
    $type = ($row['sender_id'] == $user_id) ? 'Sent' : 'Received';
    $html .= '<tr>
        <td>' . $row['id'] . '</td>
        <td>' . $type . '</td>
        <td>' . number_format($row['amount'], 2) . '</td>
        <td>' . htmlspecialchars($row['receiver']) . '</td>
        <td>' . htmlspecialchars($row['status']) . '</td>
        <td>' . htmlspecialchars($row['created_at']) . '</td>
    </tr>';
}
$html .= '</table>';

try {
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('transaction_history.pdf', 'D');
} catch (Exception $e) {
    die('PDF generation failed: ' . $e->getMessage());
} 