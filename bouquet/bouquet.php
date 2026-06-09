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

$stmt = $conn->prepare("SELECT * FROM bouquets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Bouquet not found";
    exit();
}

// Logic: Use database stock for display
$stock = isset($product['stock']) ? intval($product['stock']) : 0;

$related = $conn->prepare("SELECT id, title, image, price FROM bouquets WHERE id != ? ORDER BY RAND() LIMIT 10");
$related->bind_param("i", $id);
$related->execute();
$relatedResult = $related->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="bouquet.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../search/search.css">
</head>
<body>

<?php include "../navbar.php"; ?>

<section class="single-product">
    <div class="product-container">
        <div class="product-image">
            <img src="../images/<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'default.png'; ?>" alt="Bouquet">
        </div>

        <div class="product-details">
            <h2><?php echo htmlspecialchars($product['title']); ?></h2>
            <p class="price">Rs <?php echo htmlspecialchars($product['price']); ?></p>
            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>

            <form method="GET">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="type" value="bouquet">

                <div class="quantity-box">
                    <label>Quantity:</label>
                    <?php if ($stock > 0): ?>
                        <input type="number" name="quantity" value="1" min="1" max="30">
                        <small style="display:block; margin-top:5px; color:#555;">In Stock: <?php echo $stock; ?></small>
                    <?php else: ?>
                        <input type="number" value="0" disabled>
                        <p style="color: red; font-weight: bold;">Out of Stock</p>
                    <?php endif; ?>
                </div>

                <div class="action-links">
                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <button type="button" class="cart-btn" onclick="location.href='/crochet/login.php'">Add to Cart</button>
                        <button type="button" class="wishlist-btn" onclick="location.href='/crochet/login.php'">❤️ Add to Wishlist</button>
                        <button type="button" class="buy" onclick="location.href='/crochet/login.php'">Buy Now</button>
                    <?php elseif ($stock <= 0): ?>
                        <button type="button" class="cart-btn" disabled style="background:#ccc; cursor:not-allowed;">Out of Stock</button>
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