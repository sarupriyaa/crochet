<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['quantity']) || !isset($_GET['type'])) {
    die("Missing required parameters.");
}

$product_id = intval($_GET['id']);
$quantity = intval($_GET['quantity']);
$type = $_GET['type'];
$user_id = $_SESSION["user_id"];

// Dynamic table selection
$table = ($type === 'decor') ? 'decors' : (($type === 'fashion') ? 'fashion' : 'bouquets');

$stmt = $conn->prepare("SELECT title, price FROM $table WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product record details structural integrity lookup failure.");
}

$total_price = $product['price'] * $quantity;
$transaction_successful = false;
$saved_method = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method'])) {
    $method = $_POST['payment_method'];
    $saved_method = $method;
    
    // Capture new inputs
    $name = $_POST['customer_name'];
    $phone = $_POST['phone_number'];
    $address = $_POST['address'];
    
    // Insert into orders
    $order_stmt = $conn->prepare("INSERT INTO orders (product_id, amount, payment_method, status, customer_name, phone_number, address) VALUES (?, ?, ?, 'Pending', ?, ?, ?)");
    $order_stmt->bind_param("idssss", $product_id, $total_price, $method, $name, $phone, $address);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    $order_stmt->close();

    // Insert into payments
    $payment_status = ($method === 'COD') ? 'Pending' : 'Success';
    $transaction_id = ($method === 'COD') ? null : 'TXN-' . strtoupper(uniqid());

    $pay_stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
    $pay_stmt->bind_param("iidsss", $order_id, $user_id, $total_price, $method, $payment_status, $transaction_id);
    
    if ($pay_stmt->execute()) {
        $transaction_successful = true;
    }
    $pay_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout Process Layout</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7fc; padding: 40px; color: #334155; }
        .checkout-card { max-width: 500px; background: white; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        h2 { color: #1e293b; margin-bottom: 20px; font-size: 22px; text-align: center; }
        hr { border: none; border-top: 1px solid #e2e8f0; margin: 15px 0; }
        .row-item { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .payment-option { display: block; background: #f8fafc; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; cursor: pointer; }
        .btn-confirm { width: 100%; padding: 14px; background: #2ecc71; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        /* New fields styling */
        input[type="text"], textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .input-group { margin-bottom: 15px; }
        /* Success Styles */
        .success-box { text-align: center; }
        .success-icon { font-size: 50px; margin-bottom: 15px; display: inline-block; }
        .btn-dashboard { display: inline-block; margin-top: 25px; padding: 12px 24px; background: #4f46e5; color: white; text-decoration: none; font-weight: bold; border-radius: 8px; }
    </style>
</head>
<body>

<div class="checkout-card">
    <?php if ($transaction_successful): ?>
        <div class="success-box">
            <span class="success-icon">🎉</span>
            <h2 style="color: #2ecc71;">Transaction Logged!</h2>
            <p>Your simulated payment via <strong><?php echo htmlspecialchars($saved_method); ?></strong> has been successfully registered into the core administrative ledger.</p>
            <hr>
            <div class="row-item"><span>Settled Summation:</span> <strong>Rs. <?php echo number_format($total_price, 2); ?></strong></div>
            <a href="profile.php" class="btn-dashboard">Return to Your Profile</a>
        </div>
    <?php else: ?>
        <h2>Invoice Settlement Summary</h2>
        <hr>
        <form method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="customer_name" required>
            </div>
            <div class="input-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" required>
            </div>
            <div class="input-group">
                <label>Shipping Address</label>
                <textarea name="address" required></textarea>
            </div>
            <hr>
            <h3>Select Gateway Channel:</h3>
            <label class="payment-option"><input type="radio" name="payment_method" value="eSewa" checked> eSewa Digital Wallet</label>
            <label class="payment-option"><input type="radio" name="payment_method" value="Khalti"> Khalti Instant Processing</label>
            <label class="payment-option"><input type="radio" name="payment_method" value="COD"> Cash On Delivery (COD)</label>
            <button type="submit" class="btn-confirm">CONFIRM TRANSACTION</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>