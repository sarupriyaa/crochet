<?php
$amount = $_GET['amount'] * 100; // khalti uses paisa
?>

<button onclick="payWithKhalti()">Pay with Khalti</button>

<script src="https://khalti.com/static/khalti-checkout.js"></script>

<script>

var config = {
    "publicKey": "test_public_key",
    "productIdentity": "123456",
    "productName": "Crochet Bouquet",
    "productUrl": "http://localhost/crochet",
    "paymentPreference": ["KHALTI"],
    "eventHandler": {
        onSuccess (payload) {
            alert("Payment Successful");
        },
        onError (error) {
            console.log(error);
        }
    }
};

var checkout = new KhaltiCheckout(config);

function payWithKhalti(){
checkout.show({amount: <?php echo $amount ?>});
}

</script>