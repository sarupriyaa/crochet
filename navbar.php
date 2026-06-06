<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = $_SERVER['PHP_SELF'];

$show_search = (
    strpos($current_page, "/fashion/") !== false ||
    strpos($current_page, "/decor/") !== false ||
    strpos($current_page, "/bouquet/") !== false
);
?>

<link rel="stylesheet" href="/crochet/navbar.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/crochet/search/search.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="/crochet/navbar.js" defer></script>

<div class="top-header">
    <div class="top-header-inner">

        <div class="top-logo">
            <a href="/crochet/home.php">DaisyHook</a>
        </div>

        <?php if ($show_search): ?>
            <div class="top-search">
                <?php include $_SERVER['DOCUMENT_ROOT'] . "/crochet/search/search_box.php"; ?>
            </div>
        <?php else: ?>
            <div class="top-search">
                <div style="font-size:16px; font-weight:600; color:#a14f73; letter-spacing:1px;">
                    Handmade Crochet • Crafted with Love
                </div>
            </div>
        <?php endif; ?>

        <div class="top-social">
            <a href="https://www.pinterest.com/" aria-label="Pinterest">
                <i class="fa-brands fa-pinterest-p"></i>
            </a>
            <a href="https://www.facebook.com/" aria-label="Facebook">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com/" aria-label="Instagram">
                <i class="fa-brands fa-instagram"></i>
            </a>
            <a href="https://www.youtube.com/" aria-label="YouTube">
                <i class="fa-brands fa-youtube"></i>
            </a>
        </div>

    </div>
</div>

<nav class="navbar" id="navbar">

    <div class="hamburger" id="hamburger">☰</div>

    <ul class="nav-links" id="nav-links">

        <li><a href="/crochet/home.php">Home</a></li>

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
        <!-- <li><a href="/crochet/about.php">About</a></li>
        <li><a href="/crochet/contact.php">Contact</a></li> -->

        <?php if (isset($_SESSION["user_id"])): ?>

            <?php if ($_SESSION["role"] === "admin"): ?>

                <li class="dropdown profile-dropdown">
                    <a href="javascript:void(0)" class="dropdown-btn">
                        <i class="fa-solid fa-user-shield"></i>
                        <?php echo htmlspecialchars($_SESSION["name"]); ?> ▼
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/crochet/admin_dashboard.php">Admin Dashboard</a></li>
                        <li><a href="/crochet/logout.php">Logout</a></li>
                    </ul>
                </li>

            <?php else: ?>

                <!-- <li><a href="/crochet/cart.php">Cart</a></li>
                <li><a href="/crochet/wishlist.php">Wishlist</a></li> -->

                <li class="dropdown profile-dropdown">
                    <a href="javascript:void(0)" class="dropdown-btn">
                        <i class="fa-solid fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION["name"]); ?> ▼
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/crochet/profile.php">My Profile</a></li>
                        <!-- <li><a href="/crochet/orders.php">My Orders</a></li> -->
                        <li><a href="/crochet/logout.php">Logout</a></li>
                    </ul>
                </li>

            <?php endif; ?>

        <?php else: ?>

            <li><a href="/crochet/login.php">Login</a></li>
            <li><a href="/crochet/register.php">Sign Up</a></li>

        <?php endif; ?>

    </ul>

</nav>