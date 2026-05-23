<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

$searchType = "bouquet";
$detailPage = "bouquet.php";
$searchPlaceholder = "Search bouquets...";

$sql = "SELECT * FROM bouquets ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouquets</title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="bouquet.css">
    <link rel="stylesheet" href="../footer.css">
    <link rel="stylesheet" href="../search/search.css">
    <link rel="stylesheet" href="../navbar.css">

    <style>
        h2 {
            padding: 20px;
            color:darkblue;
        }
        a {
            text-decoration: none;
        }
    </style>
</head>

<body>

<?php include "../navbar.php"; ?>

<section class="bouquet-section">

    <!-- <?php include "../search/search_box.php"; ?> -->

    <h2>Our Bouquets</h2>

    <div class="bouquet-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bouquet-card">
                    <a href="bouquet.php?id=<?php echo $row['id']; ?>">
                        <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="Bouquet">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="price">Rs <?php echo htmlspecialchars($row['price']); ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No bouquets found.</p>
        <?php endif; ?>
    </div>

</section>

<?php include "../footer.php"; ?>

</body>
</html>