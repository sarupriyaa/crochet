<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

if (!isset($_GET['id'])) {
    header("Location: bouquets.php");
    exit();
}

$id = intval($_GET['id']);

/* Product query */
$stmt = $conn->prepare("SELECT * FROM bouquets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Bouquet not found";
    exit();
}

/* Related bouquets */
$related = $conn->prepare("SELECT id, title, image, price FROM bouquets WHERE id != ? ORDER BY RAND() LIMIT 10");
$related->bind_param("i", $id);
$related->execute();
$relatedResult = $related->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="bouquet.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../search/search.css">

    <style>
        a{
            text-decoration:none;
        }

        h4{
            min-height:50px;
        }

        .action-links{
            display:flex;
            gap:15px;
            margin-top:20px;
            flex-wrap:wrap;
        }

        /* Quantity */
        .quantity-box{
            margin-top:20px;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .quantity-box label{
            font-size:18px;
            font-weight:600;
            color:#333;
        }

        .quantity-box input{
            width:90px;
            padding:10px;
            border:1px solid #ccc;
            border-radius:8px;
            font-size:16px;
            outline:none;
        }

        .quantity-box input:focus{
            border-color:#e91e63;
        }

        /* .cart-btn{
            background:#ff6b6b;
            border:none;
            padding:12px 22px;
            border-radius:8px;
            cursor:pointer;
            color:white;
            font-size:16px;
        }

        .cart-btn:hover{
            background:#ff4f4f;
        } */

        
        /* .wishlist-btn{
            background:#e91e63;
            border:none;
            padding:12px 22px;
            border-radius:8px;
            cursor:pointer;
        }

        .wishlist-btn:hover{
            background:#d81b60;
        }

        .wishlist-btn a{
            color:white;
        } */
    </style>
</head>

<body>

<?php include "../navbar.php"; ?>

<section class="single-product">
    <div class="product-container">
        <div class="product-image">
            <?php if (!empty($product['image'])): ?>
                <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="Bouquet">
            <?php else: ?>
                <img src="../images/default.png" alt="No Image">
            <?php endif; ?>
        </div>

        <div class="product-details">
            <h2><?php echo htmlspecialchars($product['title']); ?></h2>

            <p class="price">
                Rs <?php echo htmlspecialchars($product['price']); ?>
            </p>

            <p class="description">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <form id="purchaseForm" method="GET">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="type" value="bouquet">

                <div class="quantity-box">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="10">
                </div>

                <div class="action-links">
                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <button type="button" class="cart-btn" onclick="location.href='/crochet/login.php'">Add to Cart</button>
                        <button type="button" class="wishlist-btn" onclick="location.href='/crochet/login.php'">❤️ Add to Wishlist</button>
                        <button type="button" class="buy" onclick="location.href='/crochet/login.php'">Buy Now</button>
                    <?php else: ?>
                        <button type="submit" formaction="/crochet/add_to_cart.php" class="cart-btn">Add to Cart</button>
                        <button type="button" class="wishlist-btn">
                            <a href="/crochet/add_to_wishlist.php?id=<?php echo $product['id']; ?>&type=bouquet">❤️ Add to Wishlist</a>
                        </button>
                        <button type="submit" formaction="/crochet/payment.php" class="buy">Buy Now</button>

                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<hr>

<section class="double-product">
    <h3>You may also like</h3>
    <div class="related-grid">
        <?php while ($row = $relatedResult->fetch_assoc()): ?>
            <div class="related-card">
                <a href="bouquet.php?id=<?php echo $row['id']; ?>">
                    <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="Related">
                    <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                    <p class="price">Rs <?php echo htmlspecialchars($row['price']); ?></p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php include "../footer.php"; ?>

</body>
</html>