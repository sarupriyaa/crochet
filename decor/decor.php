<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

if (!isset($_GET['id'])) {
    header("Location: decors.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM decors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Decor item not found";
    exit();
}

$related = $conn->prepare(
    "SELECT id, title, decoration, price FROM decors
     WHERE category = ? AND id != ?
     ORDER BY RAND() LIMIT 10"
);

$category = $product['category'] ?? '';
$related->bind_param("si", $category, $id);
$related->execute();
$relatedResult = $related->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="decor.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../navbar.css">

    <style>
        a { text-decoration: none; }
        h4 { min-height: 50px; }
        .category {
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .action-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>

<?php include "../navbar.php"; ?>

<section class="single-product">
    <div class="product-container">

        <div class="product-image">
            <?php if (!empty($product['decoration'])): ?>
                <img src="../decoration/<?php echo htmlspecialchars($product['decoration']); ?>" alt="Decor">
            <?php else: ?>
                <img src="../decoration/default.png" alt="No Image">
            <?php endif; ?>
        </div>

        <div class="product-details">
            <h2><?php echo htmlspecialchars($product['title']); ?></h2>

            <p class="category">
                Category: <?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?>
            </p>

            <p class="price">Rs <?php echo htmlspecialchars($product['price']); ?></p>

            <p class="description">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <div class="action-links">
                <?php if (!isset($_SESSION["user_id"])): ?>
                    <button class="cart-btn">
                        <a href="/crochet/login.php">Add to Cart</a>
                    </button>

                    <button class="wishlist-btn">
                        <a href="/crochet/login.php">❤️ Add to Wishlist</a>
                    </button>
                <?php else: ?>
                    <button class="cart-btn">
                        <a href="/crochet/add_to_cart.php?id=<?php echo $product['id']; ?>&type=decor">Add to Cart</a>
                    </button>

                    <button class="wishlist-btn">
                        <a href="/crochet/add_to_wishlist.php?id=<?php echo $product['id']; ?>&type=decor">❤️ Add to Wishlist</a>
                    </button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<hr>

<section class="double-product">
    <h3>Similar in <?php echo htmlspecialchars($product['category'] ?? 'Decor'); ?></h3>

    <div class="related-grid">
        <?php while ($row = $relatedResult->fetch_assoc()): ?>
            <div class="related-card">
                <a href="decor.php?id=<?php echo $row['id']; ?>">
                    <?php if (!empty($row['decoration'])): ?>
                        <img src="../decoration/<?php echo htmlspecialchars($row['decoration']); ?>" alt="Related">
                    <?php else: ?>
                        <img src="../decoration/default.png" alt="No Image">
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