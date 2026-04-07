<?php
$amount = $_GET['amount'];
$transaction_uuid = uniqid();
?>

<form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">

<input type="hidden" name="amount" value="<?php echo $amount ?>">
<input type="hidden" name="tax_amount" value="0">
<input type="hidden" name="total_amount" value="<?php echo $amount ?>">
<input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid ?>">
<input type="hidden" name="product_code" value="EPAYTEST">

<input type="hidden" name="success_url" value="http://localhost/crochet/payment_success.php">
<input type="hidden" name="failure_url" value="http://localhost/crochet/payment_failed.php">

<button type="submit">Pay with eSewa</button>

</form>