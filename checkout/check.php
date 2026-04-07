<?php
session_start();

$product = $_GET['product'] ?? 'Bouquet';
$price = $_GET['price'] ?? 1000;

$order_id = rand(1000,9999);
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>

<style>

body{
font-family:Arial;
background:#f2f2f2;
}

.container{
width:420px;
margin:80px auto;
padding:30px;
background:white;
text-align:center;
box-shadow:0 0 10px rgba(0,0,0,0.2);
border-radius:8px;
}

button{
padding:12px 20px;
margin:10px;
border:none;
cursor:pointer;
font-size:16px;
width:200px;
border-radius:5px;
}

.esewa{background:#60bb46;color:white;}
.khalti{background:#5C2D91;color:white;}
.fonepay{background:#009245;color:white;}

</style>
</head>

<body>

<div class="container">

<h2>Checkout</h2>

<p><b>Product:</b> <?php echo $product; ?></p>
<p><b>Price:</b> Rs <?php echo $price; ?></p>

<h3>Select Payment Method</h3>

<!-- eSewa Payment -->

<form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">

<input type="hidden" name="tAmt" value="<?php echo $price ?>">
<input type="hidden" name="amt" value="<?php echo $price ?>">
<input type="hidden" name="txAmt" value="0">
<input type="hidden" name="psc" value="0">
<input type="hidden" name="pdc" value="0">

<input type="hidden" name="scd" value="EPAYTEST">
<input type="hidden" name="pid" value="<?php echo $order_id ?>">

<input type="hidden" name="su" value="http://localhost/crochet/checkout/payment_success.php">
<input type="hidden" name="fu" value="http://localhost/crochet/checkout/payment_fail.php">

<button class="esewa">Pay with eSewa</button>

</form>


<!-- Khalti -->

<form action="payment_success.php" method="POST">

<input type="hidden" name="method" value="khalti">
<input type="hidden" name="price" value="<?php echo $price ?>">

<button class="khalti">Pay with Khalti</button>

</form>


<!-- Fonepay -->

<form action="payment_success.php" method="POST">

<input type="hidden" name="method" value="fonepay">
<input type="hidden" name="price" value="<?php echo $price ?>">

<button class="fonepay">Pay with Fonepay</button>

</form>

</div>

</body>
</html>