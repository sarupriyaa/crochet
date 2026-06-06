<?php
session_start();
include "db.php";

$message = "Invalid Transaction Request.";
$show_link = false;

if (isset($_GET['data'])) {

    $data = $_GET['data'];
    $decoded_data = json_decode(base64_decode($data), true);

    if (isset($decoded_data['status']) && $decoded_data['status'] === 'COMPLETE') {

        $user_id = $_SESSION['user_id'] ?? 0;
        $amount  = $decoded_data['total_amount'] ?? 0;

        $pid     = $_SESSION['checkout_product_id'] ?? 0;
        $name    = $_SESSION['checkout_name'] ?? 'Guest';
        $region  = $_SESSION['checkout_region'] ?? 'N/A';
        $city    = $_SESSION['checkout_city'] ?? 'N/A';
        $area    = $_SESSION['checkout_area'] ?? 'N/A';
        $address = $_SESSION['checkout_address'] ?? 'N/A';
        $phone   = $_SESSION['checkout_phone'] ?? 'N/A';

        $full_address = $area . ", " . $address;

        $sql = "INSERT INTO orders 
                (user_id, product_id, amount, payment_method, status, customer_name, region, city, area, phone_number, created_at) 
                VALUES (?, ?, ?, 'esewa', 'success', ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "iidsssss",
            $user_id,
            $pid,
            $amount,
            $name,
            $region,
            $city,
            $full_address,
            $phone
        );

        if ($stmt->execute()) {
            unset(
                $_SESSION['checkout_product_id'],
                $_SESSION['checkout_quantity'],
                $_SESSION['checkout_type'],
                $_SESSION['checkout_name'],
                $_SESSION['checkout_region'],
                $_SESSION['checkout_city'],
                $_SESSION['checkout_area'],
                $_SESSION['checkout_address'],
                $_SESSION['checkout_phone'],
                $_SESSION['checkout_transaction_uuid']
            );

            $message = "Payment Successful! Your order is confirmed.";
            $show_link = true;
        } else {
            $message = "Database Error: " . $conn->error;
        }

    } else {
        $message = "Payment Failed or Cancelled by user.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Status</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    text-align: center;
    padding: 60px;
}

.box {
    background: white;
    padding: 40px;
    display: inline-block;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

a {
    display: inline-block;
    margin-top: 20px;
    background: #635bff;
    color: white;
    padding: 12px 22px;
    text-decoration: none;
    border-radius: 6px;
}
</style>
</head>

<body>
<div class="box">
    <h1><?php echo htmlspecialchars($message); ?></h1>

    <?php if ($show_link): ?>
        <a href="orders.php">View My Orders</a>
    <?php else: ?>
        <a href="home.php">Return to Home</a>
    <?php endif; ?>
</div>
</body>
</html>