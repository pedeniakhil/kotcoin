<?php
// Kotcoin to Rupees calculator
$kotcoin = isset($_POST['kotcoin']) ? floatval($_POST['kotcoin']) : '';
$rupees = isset($_POST['rupees']) ? floatval($_POST['rupees']) : '';
$result = '';
$rate = 2.50;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($kotcoin !== '' && $kotcoin > 0) {
        $result = $kotcoin . ' Kotcoin = ' . number_format($kotcoin * $rate, 2) . ' Rupees';
    } elseif ($rupees !== '' && $rupees > 0) {
        $result = $rupees . ' Rupees = ' . number_format($rupees / $rate, 2) . ' Kotcoin';
    } else {
        $result = 'Please enter a value to convert.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kotcoin ↔ Rupees Calculator</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-green-500 via-blue-500 to-purple-500 flex items-center justify-center">
    <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-green-400 max-w-md w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Kotcoin ↔ Rupees Calculator</h2>
        <form method="post" class="space-y-5">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Kotcoin</label>
                <input type="number" name="kotcoin" step="0.01" min="0" value="<?php echo htmlspecialchars($kotcoin); ?>" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 transition" placeholder="Enter Kotcoin amount">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Rupees</label>
                <input type="number" name="rupees" step="0.01" min="0" value="<?php echo htmlspecialchars($rupees); ?>" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition" placeholder="Enter Rupees amount">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-green-500 text-white py-2.5 rounded-lg font-semibold shadow-lg hover:from-blue-600 hover:to-green-600 transition">Convert</button>
        </form>
        <?php if ($result): ?>
        <div class="mt-6 p-4 rounded-lg bg-green-100 text-green-700 text-center font-medium">
            <?php echo $result; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 