<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

$searchType = "fashion";
$detailPage = "fashions.php";
$searchPlaceholder = "Search fashions...";

$sql = "SELECT * FROM fashion ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fashion Collection</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="fashion.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../search/search.css">
    <link rel="stylesheet" href="../navbar.css">

    <style>
        .fashion-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .fashion-card {
            border: 1px solid #ccc;
            padding: 10px;
            width: 200px;
            text-align: center;
        }

        .fashion-card img {
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
        }
    </style>
</head>

<body>

<?php include "../navbar.php"; ?>

<section class="fashion-section">
            <!-- <?php include "../search/search_box.php"; ?> -->

    <h2>Our Fashion Items</h2>

    <div class="fashion-grid">

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="fashion-card">
                    <a href="fashion.php?id=<?php echo $row['id']; ?>">

                        <?php
                        $imageName = !empty($row['fashions']) ? $row['fashions'] : '';
                        $imageFile = __DIR__ . "/../fashions/" . $imageName;
                        $imageSrc  = "../fashions/" . $imageName;

                        if (!empty($imageName) && file_exists($imageFile)) {
                            echo '<img src="' . htmlspecialchars($imageSrc) . '" alt="Fashion">';
                        } else {
                            echo '<img src="../fashions/default.png" alt="No Image">';
                        }
                        ?>

                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="price">Rs <?php echo htmlspecialchars($row['price']); ?></p>

                    </a>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>No fashion items found.</p>
        <?php endif; ?>

    </div>
</section>
<?php include "../footer.php"?>
</body>
</html>