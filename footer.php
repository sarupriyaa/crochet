<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="/crochet/footer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<footer class="footer">
    <div class="footer-container">

        <!-- Brand / Account -->
        <div class="footer-box">
            <h2 class="footer-logo">DaisyHook</h2>

            <!-- <div class="footer-account">

                <?php if (!isset($_SESSION["role"])): ?>
                    <a href="/crochet/login.php" class="footer-btn">Sign In</a>

                <?php else: ?>

                    <?php if ($_SESSION["role"] == "admin"): ?>
                        <a href="/crochet/admin_dashboard.php" class="footer-link">Dashboard</a>
                        <a href="/crochet/profile.php" class="footer-link">Profile</a>

                    <?php else: ?>
                        <a href="/crochet/profile.php" class="footer-link">Profile</a>
                        <a href="/crochet/cart.php" class="footer-link">Cart</a>
                        <a href="/crochet/wishlist.php" class="footer-link">Wishlist</a>
                        <a href="/crochet/orders.php" class="footer-link">My Orders</a>
                    <?php endif; ?>

                    <a href="/crochet/logout.php" class="footer-btn logout-btn">Logout</a>

                <?php endif; ?>

            </div> -->

            <div class="footer-contact">
                <p><i class="fa-regular fa-envelope"></i> daisyhook123gmail.com</p>
                <p><i class="fa-solid fa-phone"></i> 9348430320</p>
            </div>
        </div>

        <!-- Navigation -->
        <div class="footer-box">
            <h3>Navigation</h3>
            <ul class="footer-links">
                <li><a href="/crochet/home.php">Home</a></li>
                <li><a href="/crochet/about.php">About</a></li>
                <li><a href="/crochet/fashion/fashions.php">Fashion</a></li>
                <li><a href="/crochet/decor/decors.php">Home Decor</a></li>
                <li><a href="/crochet/bouquet/bouquets.php">Bouquets</a></li>
                <li><a href="/crochet/contact.php">Contact</a></li>
            </ul>
        </div>

        <!-- Fashion -->
        <div class="footer-box">
            <h3>Fashion</h3>
            <ul class="footer-links">
                <li><a href="/crochet/fashion/accessories.php">Accessories</a></li>
                <li><a href="/crochet/fashion/bags.php">Bags</a></li>
                <li><a href="/crochet/fashion/headwear.php">Headwear</a></li>
                <li><a href="/crochet/fashion/clothing.php">Clothing</a></li>
            </ul>
        </div>

        <!-- Home Decor -->
        <div class="footer-box">
            <h3>Home Decor</h3>
            <ul class="footer-links">
                <li><a href="/crochet/decor/floral.php">Floral</a></li>
                <li><a href="/crochet/decor/wall.php">Wall Decor</a></li>
                <li><a href="/crochet/decor/car.php">Car Decor</a></li>
                <li><a href="/crochet/decor/textile.php">Textile</a></li>
            </ul>
        </div>

        <!-- Social -->
        <!-- <div class="footer-box social-box">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a> -->
                <!-- <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                <a href="#"><i class="fa-brands fa-tiktok"></i></a>
                <a href="#"><i class="fa-brands fa-linkedIn"></i></a>

            </div>
        </div> -->

    </div>

    <div class="footer-bottom">
        <p>© <?php echo date("Y"); ?> SnuggleStitch. All rights reserved.</p>
    </div>
</footer>