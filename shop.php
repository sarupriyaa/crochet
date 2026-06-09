<?php
$conn = new mysqli("localhost", "root", "", "snugglestitch");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Categories list
$categories = ['bouquet', 'fashion', 'decor'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SnuggleStitch Shop</title>
    <style>
        body { font-family: sans-serif; background-color: #fafafa; }
        
        /* Container for each category row */
        .row-container { 
            display: flex; 
            overflow-x: auto; 
            gap: 20px; 
            padding: 20px; 
            scrollbar-width: none; 
            max-width: 1000px; 
            margin: 0 auto; 
            margin-bottom: 20px;
        }
        .row-container::-webkit-scrollbar { display: none; }

        /* Card Styling */
        .product-card { 
            min-width: 220px; 
            background: #fff; 
            border: 1px solid #eee; 
            border-radius: 15px; 
            padding: 15px; 
            text-align: center; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
        }

        .image-container { 
            position: relative; 
            width: 100%; 
            height: 180px; 
            border-radius: 10px; 
            overflow: hidden; 
        }

        .image-container img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: 0.3s; 
        }

        /* Hover effects */
        .view-details-btn { 
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
            background: rgba(233, 30, 99, 0.9); color: white; padding: 10px 20px; border-radius: 25px; 
            text-decoration: none; font-size: 14px; opacity: 0; transition: 0.3s; 
        }
        .image-container:hover img { filter: brightness(60%); }
        .image-container:hover .view-details-btn { opacity: 1; }

        .product-card h3 { font-size: 16px; margin: 12px 0 5px 0; }
        .product-card p { font-size: 13px; color: #888; margin: 0; text-transform: capitalize; }
    </style>
</head>
<body>

<?php
foreach ($categories as $cat) {
    echo "<div class='row-container'>";
    
    // Determine table, folder, and column names based on category
    $table = ($cat == 'bouquet') ? 'bouquets' : (($cat == 'fashion') ? 'fashion' : 'decors');
    $folder = ($cat == 'bouquet') ? 'images' : (($cat == 'fashion') ? 'fashions' : 'decoration');
    $col = ($cat == 'bouquet') ? 'image' : (($cat == 'fashion') ? 'fashions' : 'decoration');
    
    // Select only top 6 newest products
    $query = "SELECT id, title, $col AS img_url FROM $table ORDER BY id DESC LIMIT 6";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $imagePath = $folder . "/" . $row['img_url'];
        $link = $cat . "/" . $cat . ".php?id=" . $row['id'];
?>
    <div class="product-card">
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
            <a href="<?php echo $link; ?>" class="view-details-btn">View Details</a>
        </div>
        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
        <p><?php echo $cat; ?></p>
    </div>
<?php
    }
    echo "</div>"; 
}
$conn->close();
?>

</body>
</html>