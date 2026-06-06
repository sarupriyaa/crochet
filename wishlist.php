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
        wishlist.id AS wishlist_id,
        wishlist.product_id,
        wishlist.product_type,

        CASE
            WHEN wishlist.product_type = 'bouquet' THEN bouquets.title
            WHEN wishlist.product_type = 'decor' THEN decors.title
            WHEN wishlist.product_type = 'fashion' THEN fashion.title
        END AS title,

        CASE
            WHEN wishlist.product_type = 'bouquet' THEN bouquets.price
            WHEN wishlist.product_type = 'decor' THEN decors.price
            WHEN wishlist.product_type = 'fashion' THEN fashion.price
        END AS price,

        CASE
            WHEN wishlist.product_type = 'bouquet' THEN CONCAT('/crochet/images/', bouquets.image)
            WHEN wishlist.product_type = 'decor' THEN CONCAT('/crochet/decoration/', decors.decoration)
            WHEN wishlist.product_type = 'fashion' THEN CONCAT('/crochet/fashions /', fashion.fashions)
        END AS image_path,

        CASE
            WHEN wishlist.product_type = 'bouquet' THEN CONCAT('/crochet/bouquet/bouquet.php?id=', bouquets.id)
            WHEN wishlist.product_type = 'decor' THEN CONCAT('/crochet/decor/decor.php?id=', decors.id)
            WHEN wishlist.product_type = 'fashion' THEN CONCAT('/crochet/fashion/fashion.php?id=', fashion.id)
        END AS detail_link

    FROM wishlist

    LEFT JOIN bouquets
        ON wishlist.product_type = 'bouquet'
        AND wishlist.product_id = bouquets.id

    LEFT JOIN decors
        ON wishlist.product_type = 'decor'
        AND wishlist.product_id = decors.id

    LEFT JOIN fashion
        ON wishlist.product_type = 'fashion'
        AND wishlist.product_id = fashion.id

    WHERE wishlist.user_id = ?
    ORDER BY wishlist.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Wishlist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/crochet/style.css">
    <link rel="stylesheet" href="/crochet/footer.css">
    <link rel="stylesheet" href="/crochet/navbar.css">

    <style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}
body {
    font-family: Arial, sans-serif;
    background: #f8f5f2;
    margin: 0;
}

.wishlist-section {
    max-width: 1100px;
    margin: 50px auto;
    padding: 20px;
    min-height: 60vh;
}

.wishlist-section h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #fbf9f8;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 25px;
    align-items: stretch;
}

.wishlist-card {
    background: white;
    padding: 15px;
    border-radius: 16px;
    box-shadow: 0 5px 18px rgba(0,0,0,0.08);
    text-align: center;
    min-height: 430px;

    display: flex;
    flex-direction: column;
}

.wishlist-card a {
    text-decoration: none;
    /* color: inherit; */
    color:white;
}

.wishlist-card img {
    width: 100%;
    height: 190px;
    object-fit: cover;
    border-radius: 12px;
}

.wishlist-card h3 {
    margin: 12px 0 8px;
    color: #333;
    min-height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wishlist-card p {
    color: #555;
    margin-bottom: 10px;
}

.wishlist-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 15px;
}

.add-btn,
.remove-btn {
    padding: 10px 16px;
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    
}

.add-btn {
    background: #b3775b;
}

.remove-btn {
    background: #ef3573;
}

.empty {
    text-align: center;
    font-size: 18px;
    color: #777;
}
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<section class="wishlist-section">
    <h1>My Wishlist</h1>
            

    <?php if ($result->num_rows > 0): ?>
        <div class="wishlist-grid">

            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="wishlist-card">
                    <a href="<?php echo htmlspecialchars($row["detail_link"]); ?>">
                        <img src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="Product">
                        <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
                    </a>

                    <p>Type: <?php echo htmlspecialchars(ucfirst($row["product_type"])); ?></p>
                    <p>Rs <?php echo htmlspecialchars($row["price"]); ?></p>

                    <div class="wishlist-actions">
                        <a class="add-btn" href="/crochet/add_to_cart.php?id=<?php echo $row["product_id"]; ?>&type=<?php echo $row["product_type"]; ?>">
                            Add to Cart
                        </a>

                        <a class="remove-btn" href="/crochet/remove_wishlist.php?id=<?php echo $row["wishlist_id"]; ?>">
                            Remove
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    <?php else: ?>
        <p class="empty">Your wishlist is empty.</p>
    <?php endif; ?>

</section>

<?php include "footer.php"; ?>

</body>
</html>