<?php
session_start();
include "../db.php";

error_reporting(E_ALL);
ini_set("display_errors", 1);

/* Get Headwear (Hats) */
$sql = "SELECT * FROM fashion WHERE category = 'Hats' ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Headwear Collection</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="fashion.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../navbar.css">
    <link rel="stylesheet" href="../search/search.css">

    <style>
        .fashion-section {
            padding: 60px 20px;
            text-align: center;
        }

        .fashion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
            max-width: 1100px;
            margin: auto;
        }

        .fashion-card {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .fashion-card:hover {
            transform: translateY(-8px);
        }

        .fashion-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
        }

        .fashion-card h3 {
            margin-top: 15px;
            font-size: 18px;
            min-height: 50px;
        }

        .price {
            font-weight: bold;
            color: #ff3366;
            font-size: 18px;
            margin-top: 10px;
        }

        .category {
            color: #666;
            margin-top: 8px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>

<body>

<?php include "../navbar.php"; ?>

<section class="fashion-section">
    <h2 class="page-title">Headwear Collection</h2>

    <div class="fashion-grid">

        <?php while ($row = $result->fetch_assoc()): ?>

        <div class="fashion-card">
            <a href="fashion.php?id=<?php echo $row['id']; ?>">

                <?php
                $img = trim($row['fashions']);
                $file = __DIR__ . "/../fashions/" . $img;
                $src  = "../fashions/" . $img;
                ?>

                <?php if (!empty($img) && file_exists($file)): ?>
                    <img src="<?php echo $src; ?>">
                <?php else: ?>
                    <img src="../fashions/default.png">
                <?php endif; ?>

                <h3><?php echo $row['title']; ?></h3>
                <p class="price">Rs <?php echo $row['price']; ?></p>

            </a>
        </div>

        <?php endwhile; ?>

    </div>
</section>

<?php include "../footer.php"; ?>

</body>
</html>