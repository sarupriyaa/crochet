<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$product_id = isset($_GET['id']) ? intval($_GET['id']) : ($_SESSION['checkout_product_id'] ?? 0);
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : ($_SESSION['checkout_quantity'] ?? 1);
$type = isset($_GET['type']) ? $_GET['type'] : ($_SESSION['checkout_type'] ?? "");

$table = ($type == "decor") ? "decors" : (($type == "fashion") ? "fashion" : "bouquets");

$stmt = $conn->prepare("SELECT title, price FROM $table WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found");
}

$total_price = $product['price'] * $quantity;
$formatted_amount = number_format((float)$total_price, 2, '.', '');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_method = $_POST['payment_method'];

    $name = $_POST['customer_name'];
    $phone = $_POST['phone_number'];
    $region = $_POST['region'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $address = $_POST['address'];

    $full_area = $area . " - " . $address;

    if ($payment_method === "cod") {

        $status = "pending";

        $insert = $conn->prepare("
            INSERT INTO orders 
            (user_id, product_id, amount, payment_method, status, customer_name, region, city, area, phone_number, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $insert->bind_param(
            "iidsssssss",
            $user_id,
            $product_id,
            $total_price,
            $payment_method,
            $status,
            $name,
            $region,
            $city,
            $full_area,
            $phone
        );

        if ($insert->execute()) {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Order Placed</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background: #f5f5f5;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                        margin: 0;
                    }

                    .success-box {
                        background: white;
                        padding: 40px;
                        border-radius: 12px;
                        text-align: center;
                        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
                    }

                    .success-box h1 {
                        color: #28a745;
                    }

                    .success-box a {
                        display: inline-block;
                        margin-top: 20px;
                        background: #f85606;
                        color: white;
                        text-decoration: none;
                        padding: 12px 22px;
                        border-radius: 6px;
                    }
                </style>
            </head>
            <body>
                <div class="success-box">
                    <h1>Order Placed Successfully!</h1>
                    <p>Your order is pending. Please pay cash on delivery.</p>
                    <a href="orders.php">View My Orders</a>
                </div>
            </body>
            </html>
            <?php
            exit();
        } else {
            die("Database Error: " . $conn->error);
        }

    } else {

        $_SESSION['checkout_product_id'] = $product_id;
        $_SESSION['checkout_quantity'] = $quantity;
        $_SESSION['checkout_type'] = $type;

        $_SESSION['checkout_name'] = $name;
        $_SESSION['checkout_phone'] = $phone;
        $_SESSION['checkout_region'] = $region;
        $_SESSION['checkout_city'] = $city;
        $_SESSION['checkout_area'] = $area;
        $_SESSION['checkout_address'] = $address;

        $transaction_uuid = uniqid();

        $_SESSION['checkout_transaction_uuid'] = $transaction_uuid;

        $product_code = "EPAYTEST";
        $secret = "8gBm/:&EnhH.1/q";

        $message = "total_amount={$formatted_amount},transaction_uuid={$transaction_uuid},product_code={$product_code}";
        $signature = base64_encode(hash_hmac('sha256', $message, $secret, true));
        ?>

        <!DOCTYPE html>
        <html>
        <body onload="document.getElementById('esewaForm').submit();">

            <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
                <input type="hidden" name="amount" value="<?php echo $formatted_amount; ?>">
                <input type="hidden" name="tax_amount" value="0.00">
                <input type="hidden" name="total_amount" value="<?php echo $formatted_amount; ?>">
                <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
                <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
                <input type="hidden" name="product_service_charge" value="0.00">
                <input type="hidden" name="product_delivery_charge" value="0.00">

                <input type="hidden" name="success_url" value="http://localhost:8080/crochet/esewa_handler.php">
                <input type="hidden" name="failure_url" value="http://localhost:8080/crochet/esewa_handler.php">

                <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
                <input type="hidden" name="signature" value="<?php echo $signature; ?>">
            </form>

        </body>
        </html>

        <?php
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>

<style>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
}

.navbar {
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    padding: 0 70px;
    border-bottom: 1px solid #eee;
}

.logo {
    font-size: 28px;
    font-weight: bold;
    color: #f85606;
}

.wrapper {
    max-width: 1180px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: 1fr 390px;
    gap: 24px;
}

.card {
    background: white;
    padding: 35px;
    border-radius: 10px;
}

.card h2 {
    font-size: 28px;
    margin-bottom: 25px;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px 28px;
}

.field label {
    display: block;
    font-size: 16px;
    margin-bottom: 10px;
}

.field input,
.field select {
    width: 100%;
    height: 48px;
    border: 1px solid #ddd;
    padding: 0 14px;
    font-size: 15px;
    outline: none;
}

.field input:focus,
.field select:focus {
    border-color: #f85606;
}

.full {
    grid-column: span 2;
}

.order-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    font-size: 17px;
    margin-bottom: 20px;
}

.order-row span:last-child {
    text-align: right;
}

.total-row {
    border-top: 1px solid #eee;
    padding-top: 22px;
    margin-top: 22px;
    display: flex;
    justify-content: space-between;
    font-size: 24px;
    font-weight: bold;
}

.total-price {
    color: #f85606;
}

.payment-methods {
    margin-top: 30px;
    border-top: 1px solid #eee;
    padding-top: 25px;
}

.payment-option {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 14px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.payment-option:hover {
    border-color: #f85606;
}

.payment-option input {
    width: 18px;
    height: 18px;
}

.payment-title {
    font-weight: bold;
    font-size: 16px;
}

.payment-desc {
    font-size: 13px;
    color: #777;
    margin-top: 4px;
}

.btn {
    width: 100%;
    height: 50px;
    background: #f85606;
    color: white;
    border: none;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 25px;
}

.btn:hover {
    background: #e24d00;
}

@media(max-width: 900px) {
    .wrapper {
        grid-template-columns: 1fr;
        margin: 20px;
    }

    .grid {
        grid-template-columns: 1fr;
    }

    .full {
        grid-column: span 1;
    }

    .navbar {
        padding: 0 25px;
    }
}
</style>
</head>

<body>

<div class="navbar">
    <div class="logo">DaisyHook</div>
</div>

<form method="POST">

<div class="wrapper">

    <div class="card">
        <h2>Delivery Information</h2>

        <div class="grid">

            <div class="field">
                <label>Full Name</label>
                <input type="text" name="customer_name" required>
            </div>

            <div class="field">
                <label>Phone Number</label>
                <input type="text" name="phone_number" required>
            </div>

            <div class="field">
                <label>Region</label>
                <select name="region" required>
                    <option value="">Choose province</option>
                    <option value="Koshi Province">Koshi Province</option>
                    <option value="Madhesh Province">Madhesh Province</option>
                    <option value="Bagmati Province">Bagmati Province</option>
                    <option value="Gandaki Province">Gandaki Province</option>
                    <option value="Lumbini Province">Lumbini Province</option>
                    <option value="Karnali Province">Karnali Province</option>
                    <option value="Sudurpashchim Province">Sudurpashchim Province</option>
                </select>
            </div>

            <div class="field">
                <label>City</label>
                <input type="text" name="city" required>
            </div>

            <div class="field">
                <label>Area</label>
                <input type="text" name="area" required>
            </div>

            <div class="field full">
                <label>Address</label>
                <input type="text" name="address" required>
            </div>

        </div>
    </div>

    <div class="card">
        <h2>Order Detail</h2>

        <div class="order-row">
            <span>Product</span>
            <span><?php echo htmlspecialchars($product['title']); ?></span>
        </div>

        <div class="order-row">
            <span>Quantity</span>
            <span><?php echo $quantity; ?></span>
        </div>

        <div class="total-row">
            <span>Total</span>
            <span class="total-price">Rs. <?php echo number_format($total_price, 2); ?></span>
        </div>

        <div class="payment-methods">
            <h3>Payment Method</h3>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="esewa" checked>
                <div>
                    <div class="payment-title">Pay with eSewa</div>
                    <div class="payment-desc">You will be redirected to eSewa payment gateway.</div>
                </div>
            </label>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="cod">
                <div>
                    <div class="payment-title">Cash on Delivery</div>
                    <div class="payment-desc">Pay when your order is delivered.</div>
                </div>
            </label>
        </div>

        <button type="submit" class="btn">Proceed to Pay</button>
    </div>

</div>

</form>

</body>
</html>