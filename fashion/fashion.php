<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

if (!isset($_GET['id'])) {
    header("Location: fashions.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM fashion WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Fashion item not found";
    exit();
}

// Stock logic: Hardcoded to 30 as you don't have a database column
$stock = 20;

$related = $conn->prepare("
    SELECT id, title, fashions, price 
    FROM fashion
    WHERE id != ?
    ORDER BY RAND()
    LIMIT 10
");
$related->bind_param("i", $id);
$related->execute();
$relatedResult = $related->get_result();

$mainImage = trim($product['fashions'] ?? '');
$mainFile = __DIR__ . "/../fashions/" . $mainImage;
$mainSrc  = "../fashions/" . $mainImage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="fashion.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../search/search.css">
</head>
<body>

<?php include "../navbar.php"; ?>

<section class="single-product">
    <div class="product-container">
        <div class="product-image">
            <?php if (!empty($mainImage) && file_exists($mainFile)): ?>
                <img src="<?php echo htmlspecialchars($mainSrc); ?>" alt="Fashion">
            <?php else: ?>
                <img src="../fashions/default.png" alt="No Image">
            <?php endif; ?>
        </div>

        <div class="product-details">
            <h2><?php echo htmlspecialchars($product['title']); ?></h2>
            <p class="price">Rs <?php echo htmlspecialchars($product['price']); ?></p>
            <p class="description">
                <?php echo htmlspecialchars($product['description'] ?? 'No description available.'); ?>
            </p>

            <form method="GET">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="type" value="fashion">

                <div class="quantity-box">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>">
                    <small style="display:block; margin-top:5px; color:#555;">In stock: <?php echo $stock; ?></small>
                </div>

                <div class="action-links">
                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <button type="button" class="cart-btn" onclick="location.href='/crochet/login.php'">Add to Cart</button>
                        <button type="button" class="wishlist-btn" onclick="location.href='/crochet/login.php'">❤️ Add to Wishlist</button>
                        <button type="button" class="buy" onclick="location.href='/crochet/login.php'">Buy Now</button>
                    <?php else: ?>
                        <button type="submit" formaction="/crochet/add_to_cart.php" class="cart-btn">Add to Cart</button>
                        <button type="button" class="wishlist-btn">
                            <a href="/crochet/add_to_wishlist.php?id=<?php echo $product['id']; ?>&type=fashion">❤️ Add to Wishlist</a>
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
            <?php
            $relatedImage = trim($row['fashions'] ?? '');
            $relatedFile = __DIR__ . "/../fashions/" . $relatedImage;
            $relatedSrc  = "../fashions/" . $relatedImage;
            ?>
            <div class="related-card">
                <a href="fashion.php?id=<?php echo $row['id']; ?>">
                    <?php if (!empty($relatedImage) && file_exists($relatedFile)): ?>
                        <img src="<?php echo htmlspecialchars($relatedSrc); ?>" alt="Related Fashion">
                    <?php else: ?>
                        <img src="../fashions/default.png" alt="No Image">
                    <?php endif; ?>
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