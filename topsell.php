<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "snugglestitch");

// Check if this file is being fetched by AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Function to generate the content
function renderShopContent($conn) {
    $categories = ['bouquet', 'fashion', 'decor'];
    foreach ($categories as $cat) {
        echo "<div class='row-container'>";
        $table = ($cat == 'bouquet') ? 'bouquets' : (($cat == 'fashion') ? 'fashion' : 'decors');
        $folder = ($cat == 'bouquet') ? 'images' : (($cat == 'fashion') ? 'fashions' : 'decoration');
        $col = ($cat == 'bouquet') ? 'image' : (($cat == 'fashion') ? 'fashions' : 'decoration');
        
        $result = $conn->query("SELECT id, title, $col AS img_url FROM $table ORDER BY id DESC LIMIT 6");
        while ($row = $result->fetch_assoc()) {
            $imagePath = $folder . "/" . $row['img_url'];
            echo "<div class='product-card'>
                    <div class='image-container'>
                        <img src='".htmlspecialchars($imagePath)."'>
                        <a href='$cat/$cat.php?id=".$row['id']."' class='view-details-btn'>View Details</a>
                    </div>
                    <h3>".htmlspecialchars($row['title'])."</h3>
                  </div>";
        }
        echo "</div>";
    }
}

// If NOT AJAX, show the full page structure
if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Sellers</title>
    <style>
        .row-container { display: flex; overflow-x: auto; gap: 20px; padding: 20px; }
        .product-card { min-width: 220px; background: #fff; border: 1px solid #eee; border-radius: 15px; padding: 15px; text-align: center; }
        .image-container { position: relative; height: 180px; overflow: hidden; }
        .view-details-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #e91e63; color: white; padding: 10px 20px; border-radius: 25px; text-decoration: none; opacity: 0; transition: 0.3s; }
        .image-container:hover .view-details-btn { opacity: 1; }
    </style>
</head>
<body>
    <h1>Top Sellers</h1>
    <?php renderShopContent($conn); ?>
</body>
</html>
<?php
} else {
    // If AJAX, only output the items
    renderShopContent($conn);
}
$conn->close();
?>