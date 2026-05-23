<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

/* 1. Access Control: Security Check */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* 2. Fetch User Info to maintain active session profiling */
$user_stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

/* 3. Execute Dynamic Queries based on your exact SQL schema columns */
// Count distinct pipeline status segments for metrics
$metrics_query = "SELECT 
    COUNT(id) as total,
    SUM(CASE WHEN LOWER(status) IN ('success', 'completed', 'paid') THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN LOWER(status) IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending
    FROM orders";
$metrics_res = $conn->query($metrics_query)->fetch_assoc();

$count_total     = $metrics_res['total'] ?? 0;
$count_completed = $metrics_res['completed'] ?? 0;
$count_pending   = $metrics_res['pending'] ?? 0;

// Fetch all rows according to schema: id, product_id, amount, payment_method, status, created_at
// Fetch all rows including customer details
$orders_query = "SELECT id, product_id, amount, payment_method, status, created_at, customer_name, address, phone_number FROM orders ORDER BY id DESC";
$orders_result = $conn->query($orders_query);?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Center - SuperDash</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #f4f5fa;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --primary-accent: #635bff; 
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

        /* --- DASHBOARD SIDEBAR CONTAINER --- */
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
            background: rgba(99, 91, 255, 0.08);
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

        /* --- MAIN VIEWPORT PANEL --- */
        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 40px;
            max-width: 1400px;
        }

        .dashboard-header {
            margin-bottom: 35px;
        }

        .dashboard-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111;
        }

        .dashboard-header p {
            margin-top: 4px;
            color: var(--text-muted);
        }

        /* Statistical Pipeline Overview Grid Rows */
        .order-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .metric-mini-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.01);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .metric-meta h3 {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .metric-meta p {
            font-size: 26px;
            font-weight: 700;
            color: #111;
        }

        .icon-circle-wrapper {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .cl-blue { background: rgba(54, 162, 235, 0.1); color: #36a2eb; }
        .cl-green { background: rgba(41, 204, 151, 0.1); color: #29cc97; }
        .cl-orange { background: rgba(255, 159, 64, 0.1); color: #ff9f40; }

        /* Main Data Display Panel Table Layout */
        .table-panel {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
            overflow-x: auto;
        }

        .table-panel h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .orders-table th {
            background-color: #fafafa;
            color: var(--text-muted);
            padding: 16px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table td {
            padding: 16px;
            font-size: 15px;
            color: #333;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        /* Status Badge Indicators */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .st-success { background: rgba(41, 204, 151, 0.1); color: #29cc97; }
        .st-pending { background: rgba(255, 159, 64, 0.1); color: #ff9f40; }
        .st-failed { background: rgba(255, 99, 132, 0.1); color: #ff6384; }
        .st-default { background: rgba(126, 126, 143, 0.1); color: var(--text-muted); }

        .item-id { font-weight: 700; color: var(--primary-accent); }
        .price-tag { font-weight: 600; color: #111; }

        .empty-view {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-view i {
            font-size: 52px;
            margin-bottom: 15px;
            color: #e0e0e6;
        }

        @media (max-width: 900px) {
            body { flex-direction: column; }
            .sidebar {
                width: 100%; height: auto; position: relative; padding: 20px;
                border-right: none; border-bottom: 1px solid var(--border-color);
            }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div>
            <div class="logo-area">
                <i class="fa-solid fa-square-poll-vertical"></i>
                <div>DaisyHook <span>Enterprise v2.4</span></div>
            </div>
            
            <ul class="nav-links">
                <?php if ($user["role"] === "admin") { ?>
                    <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Overview</a></li>
                <?php } ?>
                <li><a href="home.php"><i class="fa-solid fa-house"></i> View Live Site</a></li>
                
                <div class="nav-divider"></div>

                <li class="active"><a href="orders.php"><i class="fa-solid fa-box-archive"></i> Manage Orders</a></li>
                
                <?php if ($user["role"] === "admin") { ?>
                    <li><a href="admin_payments.php"><i class="fa-solid fa-credit-card"></i> Payments Logs</a></li>
                    <li><a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
                    <li><a href="admin_bouquets.php"><i class="fa-solid fa-fleur-de-lis"></i> Bouquets Dept</a></li>
                    <li><a href="admin_fashion.php"><i class="fa-solid fa-shirt"></i> Fashion Catalog</a></li>
                    <li><a href="admin_decors.php"><i class="fa-solid fa-couch"></i> Decor Products</a></li>
                    <li><a href="admin_contacts.php"><i class="fa-solid fa-envelope-open-text"></i> Contact Messages</a></li>
                <?php } else { ?>
                    <li><a href="wishlist.php"><i class="fa-solid fa-heart"></i> My Wishlist</a></li>
                    <li><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i> Shopping Cart</a></li>
                <?php } ?>
                
                <div class="nav-divider"></div>

                <li><a href="profile.php"><i class="fa-solid fa-user-circle"></i> My Profile</a></li>
                <li><a href="logout.php" style="color:#eb5757;"><i class="fa-solid fa-door-open"></i> Sign Out</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        
        <header class="dashboard-header">
            <h1>Order Fulfilment Center</h1>
            <p>Track processing statuses, inspect target product IDs, and monitor gateway transaction parameters.</p>
        </header>

        <section class="order-metrics-grid">
            <div class="metric-mini-card">
                <div class="metric-meta">
                    <h3>Volume Total</h3>
                    <p><?php echo $count_total; ?></p>
                </div>
                <div class="icon-circle-wrapper cl-blue"><i class="fa-solid fa-boxes-stacked"></i></div>
            </div>
            <div class="metric-mini-card">
                <div class="metric-meta">
                    <h3>Settled orders</h3>
                    <p><?php echo $count_completed; ?></p>
                </div>
                <div class="icon-circle-wrapper cl-green"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="metric-mini-card">
                <div class="metric-meta">
                    <h3>In Pipeline</h3>
                    <p><?php echo $count_pending; ?></p>
                </div>
                <div class="icon-circle-wrapper cl-orange"><i class="fa-solid fa-spinner fa-spin-pulse"></i></div>
            </div>
        </section>

        <section class="table-panel">
            <h2>Active Checkout Logs</h2>

            <?php if ($orders_result && $orders_result->num_rows > 0) { ?>
                <table class="orders-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer Details</th> <!-- New Header -->
            <th>Product Reference</th>
            <th>Bill Amount</th>
            <th>Payment Gateway</th>
            <th>Status Condition</th>
            <th>Creation Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $orders_result->fetch_assoc()) { 
            /* ... (keep your existing badge logic here) ... */
        ?>
            <tr>
                <td class="item-id">#ORD-<?php echo str_pad($row["id"], 5, "0", STR_PAD_LEFT); ?></td>
                
                <!-- New Customer Details Column -->
                <td>
                    <div style="font-weight:600; font-size:14px;"><?php echo htmlspecialchars($row["customer_name"] ?? 'N/A'); ?></div>
                    <div style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($row["address"] ?? 'No address'); ?></div>
                    <div style="font-size:12px; color:var(--primary-accent);"><?php echo htmlspecialchars($row["phone_number"] ?? 'No phone'); ?></div>
                </td>

                <td><span style="background:#f1f1f9; padding:4px 8px; border-radius:6px; font-size:14px; font-weight:600;">PID-<?php echo htmlspecialchars($row["product_id"]); ?></span></td>
                <td class="price-tag">$<?php echo number_format((float)($row["amount"] ?? 0), 2); ?></td>
                <td>
                    <span style="font-weight: 500; color: #555;">
                        <i class="fa-solid fa-credit-card" style="color:var(--text-muted); margin-right:6px; font-size:13px;"></i>
                        <?php echo htmlspecialchars(strtoupper($row["payment_method"] ?? 'N/A')); ?>
                    </span>
                </td>
                <td>
                    <span class="status-pill <?php echo $badge_style; ?>">
                        <i class="fa-solid fa-circle" style="font-size: 6px;"></i>
                        <?php echo htmlspecialchars($row["status"] ?? 'Unknown'); ?>
                    </span>
                </td>
                <td style="color: var(--text-muted); font-size: 14px;">
                    <?php echo date("Y-m-d H:i", strtotime($row["created_at"])); ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
            <?php } else { ?>
                <div class="empty-view">
                    <i class="fa-solid fa-box-open"></i>
                    <p>No recorded checkouts or purchases exist within the database entity parameters.</p>
                </div>
            <?php } ?>
        </section>

    </main>

</body>
</html>