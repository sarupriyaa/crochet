<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /crochet/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT 
        cart.id AS cart_id,
        cart.product_id,
        cart.product_type,
        cart.quantity,

        CASE
            WHEN cart.product_type = 'bouquet' THEN bouquets.title
            WHEN cart.product_type = 'decor' THEN decors.title
            WHEN cart.product_type = 'fashion' THEN fashion.title
        END AS title,

        CASE
            WHEN cart.product_type = 'bouquet' THEN bouquets.price
            WHEN cart.product_type = 'decor' THEN decors.price
            WHEN cart.product_type = 'fashion' THEN fashion.price
        END AS price,

        CASE
            WHEN cart.product_type = 'bouquet' THEN CONCAT('/crochet/images/', bouquets.image)
            WHEN cart.product_type = 'decor' THEN CONCAT('/crochet/decoration/', decors.decoration)
            WHEN cart.product_type = 'fashion' THEN CONCAT('/crochet/fashions/', fashion.fashions)
        END AS image_path,

        CASE
            WHEN cart.product_type = 'bouquet' THEN CONCAT('/crochet/bouquet/bouquet.php?id=', bouquets.id)
            WHEN cart.product_type = 'decor' THEN CONCAT('/crochet/decor/decor.php?id=', decors.id)
            WHEN cart.product_type = 'fashion' THEN CONCAT('/crochet/fashion/fashion.php?id=', fashion.id)
        END AS detail_link

    FROM cart

    LEFT JOIN bouquets
        ON cart.product_type = 'bouquet'
        AND cart.product_id = bouquets.id

    LEFT JOIN decors
        ON cart.product_type = 'decor'
        AND cart.product_id = decors.id

    LEFT JOIN fashion
        ON cart.product_type = 'fashion'
        AND cart.product_id = fashion.id

    WHERE cart.user_id = ?
    ORDER BY cart.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $row["quantity"] = isset($row["quantity"]) ? (int)$row["quantity"] : 1;
    $row["price"] = (float)$row["price"];
    $cart_items[] = $row;
}

$item_count = count($cart_items);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Cart</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="/crochet/style.css">
<link rel="stylesheet" href="/crochet/footer.css">
<link rel="stylesheet" href="/crochet/navbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f5f5;
}

.cart-page {
    max-width: 1320px;
    margin: 30px auto;
    display: grid;
    grid-template-columns: 1fr 370px;
    gap: 22px;
}

.cart-left {
    background: transparent;
}

.cart-item {
    background: #fff;
    margin-bottom: 14px;
}

.store-row {
    height: 52px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 18px;
    font-weight: 600;
    color: #333;
}

.product-row {
    display: grid;
    grid-template-columns: 35px 110px 1fr 150px 120px 60px;
    gap: 16px;
    align-items: center;
    padding: 20px 18px;
}

.check-box {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.product-img {
    width: 95px;
    height: 95px;
    object-fit: cover;
}

.product-title {
    color: #222;
    text-decoration: none;
    font-size: 16px;
    line-height: 1.4;
}

.product-title:hover {
    color: #f85606;
}

.product-meta {
    color: #888;
    font-size: 13px;
    margin-top: 8px;
}

.price {
    color: #f85606;
    font-size: 20px;
    font-weight: 500;
}

.old-price {
    color: #999;
    text-decoration: line-through;
    margin-top: 8px;
}

.qty-box {
    display: flex;
    align-items: center;
    gap: 12px;
}

.qty-btn {
    width: 34px;
    height: 34px;
    border: none;
    background: #f3f3f3;
    font-size: 20px;
    color: #777;
    cursor: pointer;
}

.remove-icon {
    color: #999;
    font-size: 20px;
    text-decoration: none;
}

.remove-icon:hover {
    color: #f85606;
}

.summary-box {
    background: #fff;
    padding: 22px;
    height: fit-content;
}

.summary-box h2 {
    font-size: 22px;
    margin-bottom: 22px;
    font-weight: 500;
}

.selected-product {
    min-height: 80px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
    padding-bottom: 16px;
    color: #555;
}

.selected-product strong {
    display: block;
    color: #222;
    margin-bottom: 6px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 22px;
    color: #555;
    font-size: 16px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 18px;
    margin: 25px 0;
}

.total-price {
    color: #f85606;
    font-size: 24px;
}

.checkout-btn {
    width: 100%;
    height: 48px;
    border: none;
    background: #f85606;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

.checkout-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.checkout-btn:hover:not(:disabled) {
    background: #e14b00;
}

.empty {
    background: white;
    padding: 60px;
    text-align: center;
    color: #777;
    font-size: 20px;
}

@media(max-width: 900px) {
    .cart-page {
        grid-template-columns: 1fr;
        margin: 18px;
    }

    .product-row {
        grid-template-columns: 35px 90px 1fr;
    }

    .price,
    .qty-box,
    .remove-wrap {
        grid-column: span 3;
    }
}
</style>
</head>

<body>

<?php include "navbar.php"; ?>

<div class="cart-page">

    <div class="cart-left">

        <?php if ($item_count > 0): ?>

            <?php foreach ($cart_items as $item): ?>

                <div class="cart-item">

                    <div class="store-row">
                        <i class="fa-solid fa-store"></i>
                        DaisyHook Store
                        <i class="fa-solid fa-angle-right"></i>
                    </div>

                    <div class="product-row">

                        <input 
                            type="checkbox"
                            class="check-box item-check"
                            data-title="<?php echo htmlspecialchars($item["title"]); ?>"
                            data-price="<?php echo $item["price"]; ?>"
                            data-quantity="<?php echo $item["quantity"]; ?>"
                            data-product-id="<?php echo $item["product_id"]; ?>"
                            data-type="<?php echo $item["product_type"]; ?>"
                        >

                        <a href="<?php echo htmlspecialchars($item["detail_link"]); ?>">
                            <img class="product-img" src="<?php echo htmlspecialchars($item["image_path"]); ?>" alt="Product">
                        </a>

                        <div>
                            <a class="product-title" href="<?php echo htmlspecialchars($item["detail_link"]); ?>">
                                <?php echo htmlspecialchars($item["title"]); ?>
                            </a>

                            <div class="product-meta">
                                Product Type: <?php echo htmlspecialchars(ucfirst($item["product_type"])); ?>
                            </div>
                        </div>

                        <div>
                            <div class="price">
                                Rs. <?php echo number_format($item["price"], 0); ?>
                            </div>
                            <div class="old-price">
                                Rs. <?php echo number_format($item["price"] + 100, 0); ?>
                            </div>
                        </div>

                        <div class="qty-box">
                            <button type="button" class="qty-btn">−</button>
                            <span><?php echo $item["quantity"]; ?></span>
                            <button type="button" class="qty-btn">+</button>
                        </div>

                        <div class="remove-wrap">
                            <a class="remove-icon" href="/crochet/remove_cart.php?id=<?php echo $item["cart_id"]; ?>">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty">
                Your cart is empty.
            </div>

        <?php endif; ?>

    </div>

    <div class="summary-box">

        <h2>Order Summary</h2>

        <div class="selected-product" id="selectedProduct">
            Select an item to checkout.
        </div>

        <div class="summary-row">
            <span>Subtotal</span>
            <span id="subtotal">Rs. 0</span>
        </div>

        <div class="summary-row">
            <span>Shipping Fee</span>
            <span>Rs. 0</span>
        </div>

        <div class="total-row">
            <strong>Total</strong>
            <span class="total-price" id="total">Rs. 0</span>
        </div>

        <button type="button" class="checkout-btn" id="checkoutBtn" disabled>
            PROCEED TO CHECKOUT
        </button>

    </div>

</div>

<?php include "footer.php"; ?>

<script>
let selectedProductId = null;
let selectedType = null;
let selectedQuantity = 1;

document.querySelectorAll(".item-check").forEach(function (box) {
    box.addEventListener("change", function () {

        document.querySelectorAll(".item-check").forEach(function (other) {
            if (other !== box) {
                other.checked = false;
            }
        });

        if (box.checked) {
            const title = box.dataset.title;
            const price = parseFloat(box.dataset.price);
            const quantity = parseInt(box.dataset.quantity);

            selectedProductId = box.dataset.productId;
            selectedType = box.dataset.type;
            selectedQuantity = quantity;

            const subtotal = price * quantity;

            document.getElementById("selectedProduct").innerHTML =
                "<strong>" + title + "</strong>" +
                "Quantity: " + quantity + "<br>" +
                "Price: Rs. " + price.toLocaleString();

            document.getElementById("subtotal").innerText =
                "Rs. " + subtotal.toLocaleString();

            document.getElementById("total").innerText =
                "Rs. " + subtotal.toLocaleString();

            document.getElementById("checkoutBtn").disabled = false;
        } else {
            selectedProductId = null;
            selectedType = null;
            selectedQuantity = 1;

            document.getElementById("selectedProduct").innerText =
                "Select an item to checkout.";

            document.getElementById("subtotal").innerText = "Rs. 0";
            document.getElementById("total").innerText = "Rs. 0";

            document.getElementById("checkoutBtn").disabled = true;
        }
    });
});

document.getElementById("checkoutBtn").addEventListener("click", function () {
    if (!selectedProductId || !selectedType) {
        alert("Please select one item first.");
        return;
    }

    window.location.href =
        "/crochet/payment.php?id=" + encodeURIComponent(selectedProductId) +
        "&type=" + encodeURIComponent(selectedType) +
        "&quantity=" + encodeURIComponent(selectedQuantity);
});
</script>

</body>
</html>