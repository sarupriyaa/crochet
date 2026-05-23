<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

$searchType = "decor";
$detailPage = "decors.php";
$searchPlaceholder = "Search decors...";

$sql = "SELECT * FROM decors ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Decor Collection</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="decor.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../search/search.css">
    <link rel="stylesheet" href="../navbar.css">

    <style>
        .bouquet-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .bouquet-card {
            border: 1px solid #ccc;
            padding: 10px;
            width: 200px;
            text-align: center;
        }

        .bouquet-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .price {
            color: green;
            font-weight: bold;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
        h2{
            padding:3%;
            color:darkblue;
        }
    </style>
</head>
<body>

<?php include "../navbar.php"; ?>

<section class="bouquet-section">
        <!-- <?php include "../search/search_box.php"; ?> -->

    <h2>Our Decor Items</h2>
    <div class="bouquet-grid">

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="bouquet-card">
                    <a href="decor.php?id=<?php echo $row['id']; ?>">

                        <?php
                        $imageName = !empty($row['decoration']) ? $row['decoration'] : '';
                        $imageFile = __DIR__ . "/../decoration/" . $imageName;
                        $imageSrc  = "../decoration/" . $imageName;

                        if (!empty($imageName) && file_exists($imageFile)) {
                            echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="Decor">';
                        } else {
                            echo '<img src="../decoration/default.png" alt="No Image">';
                        }
                        ?>

                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="price">Rs <?php echo htmlspecialchars($row['price']); ?></p>
                    </a>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>No decor items found.</p>
        <?php endif; ?>

    </div>
</section>
<?php include "../footer.php"?>
</body>
</html>