<?php
// session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>

<style>

body{
font-family: Arial;
background:#f5f5f5;
}

.checkout{
width:500px;
margin:auto;
background:white;
padding:30px;
border-radius:8px;
box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

input{
width:100%;
padding:10px;
margin:8px 0;
border:1px solid #ccc;
border-radius:5px;
}

button{
width:100%;
padding:12px;
background:#2bb7da;
color:white;
border:none;
border-radius:5px;
font-size:16px;
cursor:pointer;
}

#morePayment{
display:none;
margin-top:20px;
}

.paypal-btn{
background:#ffc439;
padding:12px;
border:none;
width:100%;
font-weight:bold;
cursor:pointer;
}

.more{
text-align:center;
margin-top:10px;
cursor:pointer;
color:#0070ba;
}

</style>

</head>

<body>

<div class="checkout">

<h2>Checkout</h2>

<label>Email</label>
<input type="email" placeholder="Email">

<label>First name</label>
<input type="text">

<label>Last name</label>
<input type="text">

<label>Address</label>
<input type="text">

<label>City</label>
<input type="text">

<label>Phone</label>
<input type="text">

<br>

<!-- PAYPAL BUTTON -->
<div id="paypal-button-container"></div>

<div class="more" onclick="showPayment()">
More payment options
</div>

<!-- CREDIT CARD FORM -->
<div id="morePayment">

<h3>Credit Card</h3>

<input type="text" placeholder="Card number">
<input type="text" placeholder="Expiry (MM/YY)">
<input type="text" placeholder="Security code">
<input type="text" placeholder="Name on card">

<button>Pay now</button>

</div>

</div>

<!-- PAYPAL SCRIPT -->
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_CLIENT_ID&currency=USD"></script>

<script>

function showPayment(){
document.getElementById("morePayment").style.display="block";
}

paypal.Buttons({

createOrder: function(data, actions) {
return actions.order.create({
purchase_units: [{
amount: {
value: '29.70'
}
}]
});
},

onApprove: function(data, actions) {
return actions.order.capture().then(function(details) {
alert('Payment completed by ' + details.payer.name.given_name);
});
}

}).render('#paypal-button-container');

</script>

</body>
</html>