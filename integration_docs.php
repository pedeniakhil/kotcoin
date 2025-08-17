<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kotcoin Payment Integration Docs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-tr from-blue-500 via-green-500 to-purple-500 flex items-center justify-center">
    <div class="bg-white bg-opacity-95 p-8 rounded-2xl shadow-2xl border-t-4 border-blue-400 max-w-2xl w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center">Kotcoin Payment Integration</h2>
        <h3 class="text-xl font-bold text-blue-700 mb-2">1. Create a Payment Link</h3>
        <p class="mb-4">To request a payment, generate a link like:</p>
        <pre class="bg-gray-100 p-2 rounded mb-4">/pay_request.php?amount=20&to=USERNAME</pre>
        <p class="mb-4">Replace <code>USERNAME</code> with your username. You can share this link with anyone who wants to pay you.</p>
        <h3 class="text-xl font-bold text-blue-700 mb-2">2. Payment Page Flow</h3>
        <ol class="list-decimal ml-8 mb-4">
            <li>The payer visits the link and sees the amount and a generated receiver code.</li>
            <li>The payer uses their wallet/app to send the specified amount to the code.</li>
            <li>After sending, the payer enters the transaction code to verify the payment.</li>
            <li>If the payment is found and matches the amount, it is marked as verified.</li>
        </ol>
        <h3 class="text-xl font-bold text-blue-700 mb-2">3. Example Integration</h3>
        <pre class="bg-gray-100 p-2 rounded mb-4">&lt;a href="/pay_request.php?amount=20&to=YOUR_USERNAME"&gt;
  Pay 20 Kotcoin to Me
&lt;/a&gt;</pre>
        <h3 class="text-xl font-bold text-blue-700 mb-2">4. Verifying Payment</h3>
        <p class="mb-4">After payment, the payer can enter the transaction code on the payment page to verify the payment instantly.</p>
        <h3 class="text-xl font-bold text-blue-700 mb-2 mt-6">5. Send Money Integration (Fixed Amount)</h3>
        <p class="mb-4">To allow someone to send you a fixed amount, generate a link like:</p>
        <pre class="bg-gray-100 p-2 rounded mb-4">/send_money_request.php?amount=20&to=USERNAME</pre>
        <p class="mb-4">Replace <code>USERNAME</code> with the sender's username. The amount will be fixed and cannot be changed by the sender.</p>
        <h4 class="font-semibold mb-1">Example Integration</h4>
        <pre class="bg-gray-100 p-2 rounded mb-4">&lt;a href="/send_money_request.php?amount=20&amp;to=RECEIVER_USERNAME"&gt;
  Send 20 Kotcoin to RECEIVER_USERNAME
&lt;/a&gt;</pre>
        <h3 class="text-xl font-bold text-blue-700 mb-2 mt-6">6. Send Money Integration (Live)</h3>
        <p class="mb-4">You can generate a code and share it with a sender. The sender uses the normal Send Money page to send the specified amount to your code. This page will check every 2 seconds if the money is received and show the transaction code live.</p>
        <pre class="bg-gray-100 p-2 rounded mb-4">/integration_send.php?amount=50&to=YOUR_USERNAME</pre>
        <p class="mb-4">Share the generated code with the sender. The sender should go to the Send Money page, enter the code and amount. This integration page will automatically detect when the payment is received.</p>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="text-blue-600 font-semibold hover:underline">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 