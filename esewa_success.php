<?php
// esewa_success.php
if (isset($_GET['data'])) {
    $encodedData = $_GET['data'];
    $decodedData = json_decode(base64_decode($encodedData), true);

    // Check if payment was successful
    if ($decodedData['status'] === 'COMPLETE') {
        $refId = $decodedData['ref_id'];
        $transactionUuid = $decodedData['transaction_uuid'];
        $totalAmount = $decodedData['total_amount'];

        // --- SECURITY NOTE ---
        // 1. Verify the signature here by reconstructing the message string 
        //    (exactly as you did in the payment form) and comparing hashes.
        // 2. Perform a Server-Side Status Check (cURL) to eSewa API to confirm 
        //    the payment before updating your database status to 'paid'.

        echo "<h1>Payment Successful!</h1>";
        echo "<p>Thank you. Your transaction ID is: " . htmlspecialchars($refId) . "</p>";
        echo '<a href="index.php">Return to Homepage</a>';
    } else {
        echo "<h1>Payment Incomplete</h1>";
        echo "<p>The transaction was not completed. Please check your eSewa account.</p>";
    }
} else {
    echo "<h1>Invalid Request</h1>";
}
?>