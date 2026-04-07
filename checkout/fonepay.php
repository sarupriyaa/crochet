<?php
$amount = $_GET['amount'];
?>

<h2>Scan QR to Pay</h2>

<img src="generate_fonepay_qr.php?amount=<?php echo $amount ?>">