<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

/* 1. Access Control: Check login status */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* 2. Fetch authenticated user properties */
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if user record actually exists inside the database engine
if (!$user) {
    header("Location: logout.php");
    exit();
}

// Store the admin check variable to dynamically configure the page theme/sidebar links below
$is_admin = ($user["role"] === "admin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Crochet Management System</title>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #f4f5fa;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            /* Changes color dynamic palette accents if user is administrative */
            --primary-accent: <?php echo $is_admin ? '#635bff' : '#e91e63'; ?>; 
            --text-dark: #1e1e2f;
            --text-muted: #7e7e8f;
            --border-color: #f1f1f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo-area {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-accent);
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-area span {
            font-size: 11px;
            font-weight: 400;
            color: var(--text-muted);
            display: block;
        }

        .nav-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .nav-links li.active a, 
        .nav-links a:hover {
            background: <?php echo $is_admin ? 'rgba(99, 91, 255, 0.08)' : 'rgba(233, 30, 99, 0.06)'; ?>;
            color: var(--primary-accent);
        }

        .nav-links a i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .nav-divider {
            height: 1px;
            background: var(--border-color);
            margin: 15px 0;
        }

        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .profile-card {
            width: 100%;
            max-width: 750px;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.02);
        }

        .profile-top {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 35px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 30px;
        }

        .profile-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: <?php echo $is_admin ? 'linear-gradient(135deg, #a380ff 0%, var(--primary-accent) 100%)' : 'linear-gradient(135deg, #ff4081 0%, var(--primary-accent) 100%)'; ?>;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 700;
            box-shadow: <?php echo $is_admin ? '0 6px 16px rgba(99, 91, 255, 0.25)' : '0 6px 16px rgba(233, 30, 99, 0.25)'; ?>;
        }

        .profile-details h1 {
            font-size: 28px;
            color: #111;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .profile-details p {
            color: var(--text-muted);
            font-size: 15px;
        }

        .info-box {
            background: #fafafa;
            border-radius: 14px;
            padding: 20px 24px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-accent);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .info-box:hover {
            background: #f5f5f7;
            transform: translateX(3px);
        }

        .info-text-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-title {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .info-value {
            font-size: 18px;
            color: #111;
            font-weight: 600;
        }

        .info-icon {
            color: var(--text-muted);
            font-size: 18px;
        }

        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            .profile-top {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div>
            <?php if ($is_admin): ?>
                <div class="logo-area">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <div>DaisyHook <span>Enterprise v2.4</span></div>
                </div>
            <?php else: ?>
                <div class="logo-area">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <div>My Account <span>User Panel</span></div>
                </div>
            <?php endif; ?>
            
            <ul class="nav-links">
                <?php if ($is_admin): ?>
                    <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Overview</a></li>
                    <li><a href="home.php"><i class="fa-solid fa-house"></i> View Live Site</a></li>
                    
                    <div class="nav-divider"></div>

                    <li><a href="/crochet/orders.php"><i class="fa-solid fa-box-archive"></i> Manage Orders</a></li>
                    <li><a href="/crochet/admin_payments.php"><i class="fa-solid fa-credit-card"></i> Payments Logs</a></li>
                    <li><a href="/crochet/admin_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
                    <li><a href="admin_bouquets.php"><i class="fi fi-ss-daisy-alt"></i> Bouquets Management</a></li>
                    <li><a href="admin_fashion.php"><i class="fa-solid fa-shirt"></i> Fashion Catalog</a></li>
                    <li><a href="admin_decors.php"><i class="fa-solid fa-couch"></i> Decor Products</a></li>
                    
                    <div class="nav-divider"></div>

                    <li><a href="admin_contacts.php"><i class="fa-solid fa-envelope-open-text"></i> Contact Messages</a></li>
                    <li class="active"><a href="profile.php"><i class="fa-solid fa-user-circle"></i> My Profile</a></li>
                <?php else: ?>
                    <li><a href="home.php"><i class="fa-solid fa-house"></i> Home Marketplace</a></li>
                    <li class="active"><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
                    <li><a href="orders.php"><i class="fa-solid fa-box"></i> Order History</a></li>
                    <li><a href="wishlist.php"><i class="fa-solid fa-heart"></i> My Wishlist</a></li>
                    <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Shopping Cart</a></li>
                    
                    <div class="nav-divider"></div>
                <?php endif; ?>
                
                <li><a href="logout.php" style="color: #eb5757;"><i class="fa-solid fa-door-open"></i> Logout</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        
        <div class="profile-card">

            <div class="profile-top">
                <div class="profile-icon">
                    <?php echo strtoupper(substr($user["name"], 0, 1)); ?>
                </div>
                <div class="profile-details">
                    <h1><?php echo htmlspecialchars($user["name"]); ?></h1>
                    <p>Welcome to your personal crochet <?php echo $is_admin ? 'administrative system' : 'client account'; ?> dashboard.</p>
                </div>
            </div>

            <div class="info-box">
                <div class="info-text-group">
                    <div class="info-title">Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($user["name"]); ?></div>
                </div>
                <div class="info-icon"><i class="fa-regular fa-id-card"></i></div>
            </div>

            <div class="info-box">
                <div class="info-text-group">
                    <div class="info-title">Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($user["email"]); ?></div>
                </div>
                <div class="info-icon"><i class="fa-regular fa-envelope"></i></div>
            </div>

            <div class="info-box">
                <div class="info-text-group">
                    <div class="info-title">Account Access Status</div>
                    <div class="info-value" style="color: <?php echo $is_admin ? '#635bff' : '#27ae60'; ?>; text-transform: capitalize;">
                        <i class="fa-solid fa-circle-check" style="font-size:14px; margin-right:4px;"></i> 
                        <?php echo htmlspecialchars($user["role"]); ?> Account
                    </div>
                </div>
                <div class="info-icon"><i class="fa-solid fa-shield-halved"></i></div>
            </div>

        </div>

    </main>

</body>
</html>

