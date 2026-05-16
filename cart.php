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
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/crochet/style.css">
    <link rel="stylesheet" href="/crochet/footer.css">
    <link rel="stylesheet" href="/crochet/navbar.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: rgba(0, 0, 0, 0.45);
        }

        .cart-wrapper {
            max-width: 1380px;
            margin: 50px auto;
            background: #fff;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.18);
        }

        .cart-wrapper h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th {
            background: #fafafa;
            padding: 18px;
            font-size: 20px;
            color: #333;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .cart-table td {
            padding: 18px;
            font-size: 20px;
            color: #333;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .cart-img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-name {
            color: #333;
            text-decoration: none;
        }

        .product-name:hover {
            color: #e91e63;
        }

        .remove-btn {
            background: #ef3b35;
            color: #fff;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        .remove-btn:hover {
            background: #d62828;
        }

        .empty {
            text-align: center;
            font-size: 20px;
            color: #777;
            padding: 40px;
        }

        @media (max-width: 800px) {
            .cart-wrapper {
                margin: 25px 12px;
                padding: 18px;
                overflow-x: auto;
            }

            .cart-table {
                min-width: 750px;
            }

            .cart-table th,
            .cart-table td {
                font-size: 16px;
                padding: 14px;
            }
        }
    </style>
</head>

<body>

<?php include "navbar.php"; ?>

<div class="cart-wrapper">
    <h1>My Cart</h1>

    <?php if ($result->num_rows > 0): ?>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="<?php echo htmlspecialchars($row["detail_link"]); ?>">
                                <img class="cart-img" src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="Product">
                            </a>
                        </td>

                        <td>
                            <a class="product-name" href="<?php echo htmlspecialchars($row["detail_link"]); ?>">
                                <?php echo htmlspecialchars($row["title"]); ?>
                            </a>
                        </td>

                        <td><?php echo htmlspecialchars($row["product_type"]); ?></td>

                        <td>Rs <?php echo number_format((float)$row["price"], 0); ?></td>

                        <td>
                            <a class="remove-btn" href="/crochet/remove_cart.php?id=<?php echo $row["cart_id"]; ?>">
                                Remove
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p class="empty">Your cart is empty.</p>

    <?php endif; ?>
</div>

<?php include "footer.php"; ?>

</body>
</html>