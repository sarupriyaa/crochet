<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="/crochet/style.css">
<script src="/crochet/navbar.js" defer></script>

<nav class="navbar" id="navbar">

    <div class="logo">
        <a href="/crochet/home.php">DaisyHook</a>
    </div>

    <div class="hamburger" id="hamburger">☰</div>

    <ul class="nav-links" id="nav-links">

        <li><a href="/crochet/home.php">Home</a></li>

        <!-- Fashion Dropdown -->
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropdown-btn">Fashion ▼</a>
            <ul class="dropdown-menu">
                <li><a href="/crochet/fashion/fashions.php">All Fashions</a></li>
                <li><a href="/crochet/fashion/bags.php">Bags</a></li>
                <li><a href="/crochet/fashion/headwear.php">Headwear</a></li>
                <li><a href="/crochet/fashion/clothing.php">Clothing</a></li>
                <li><a href="/crochet/fashion/accessories.php">Accessories</a></li>
            </ul>
        </li>

        <!-- Home Decor Dropdown -->
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropdown-btn">Home Decor ▼</a>
            <ul class="dropdown-menu">
                <li><a href="/crochet/decor/decors.php">All Decors</a></li>
                <li><a href="/crochet/decor/floral.php">Floral Decor</a></li>
                <li><a href="/crochet/decor/wall.php">Wall Decor</a></li>
                <li><a href="/crochet/decor/car.php">Car Decor</a></li>
                <li><a href="/crochet/decor/textile.php">Textile Decor</a></li>
            </ul>
        </li>

        <li><a href="/crochet/bouquet/bouquets.php">Bouquet</a></li>
        <!-- <li><a href="/crochet/checkout/check.php">Check</a></li> -->

        <!-- <?php if (!isset($_SESSION["role"])): ?>
            <li><a href="/crochet/login.php" class="btn">Sign In</a></li>
            <li><a href="/crochet/register.php" class="btn primary">Sign Up</a></li>

        <?php else: ?>
            <?php if ($_SESSION["role"] == "user"): ?>
                <li><a href="/crochet/orders.php">My Orders</a></li>
            <?php endif; ?>

            <?php if ($_SESSION["role"] == "admin"): ?>
                <li><a href="/crochet/admin_dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <li><a href="/crochet/profile.php">Profile</a></li>
            <li><a href="/crochet/logout.php" class="btn logout">Logout</a></li>
        <?php endif; ?> -->

    </ul>

</nav>