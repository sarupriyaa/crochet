<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

/* 1. Access Control */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* 2. Fetch User Info */
$user_stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$is_admin = ($user && ($user["role"] ?? "") === "admin");

/* 3. Safe Query Logic */
$column_check = $conn->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
$has_user_id_col = ($column_check && $column_check->num_rows > 0);

$where_sql = "";
if (!$is_admin && $has_user_id_col) {
    $where_sql = "WHERE user_id = " . intval($user_id);
}

/* 4. Execute Queries */
$metrics_query = "SELECT 
    COUNT(id) as total,
    SUM(CASE WHEN LOWER(status) IN ('success', 'completed', 'paid') THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN LOWER(status) IN ('pending', 'processing') THEN 1 ELSE 0 END) as pending
    FROM orders $where_sql";

$metrics_res = $conn->query($metrics_query)->fetch_assoc();

$count_total     = $metrics_res['total'] ?? 0;
$count_completed = $metrics_res['completed'] ?? 0;
$count_pending   = $metrics_res['pending'] ?? 0;

$orders_query = "SELECT id, product_id, amount, payment_method, status, created_at, customer_name, phone_number, region, city, area 
                 FROM orders $where_sql ORDER BY id DESC";
$orders_result = $conn->query($orders_query);

/* FIX: Status class function */
function render_status_class($status) {
    $status_clean = strtolower(trim($status ?? ""));

    if (in_array($status_clean, ["success", "completed", "paid"])) {
        return "status-success";
    } elseif (in_array($status_clean, ["pending", "processing"])) {
        return "status-pending";
    } elseif (in_array($status_clean, ["failed", "cancelled", "canceled"])) {
        return "status-failed";
    } else {
        return "status-default";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Orders - DaisyHook</title>
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
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
    --green: #29cc97;
    --orange: #ff9f40;
    --red: #ff6384;
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
    padding: 30px 20px;
    position: fixed;
    height: 100vh;
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
}

.nav-links li.active a,
.nav-links a:hover {
    background: rgba(99, 91, 255, 0.08);
    color: var(--primary-accent);
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
}

.dashboard-header {
    margin-bottom: 35px;
}

.dashboard-header h1 {
    font-size: 28px;
    color: #111;
}

.dashboard-header p {
    margin-top: 4px;
    color: var(--text-muted);
}

.orders-summary-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    margin-bottom: 35px;
}

.summary-box {
    background: var(--card-bg);
    border-radius: 18px;
    padding: 24px;
    display: flex;
    justify-content: space-between;
}

.summary-info h3 {
    font-size: 14px;
    color: var(--text-muted);
    text-transform: uppercase;
    margin-bottom: 6px;
}

.summary-info p {
    font-size: 24px;
    font-weight: 700;
}

.summary-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.bg-purple { background: rgba(99,91,255,0.1); color: var(--primary-accent); }
.bg-green { background: rgba(41,204,151,0.1); color: var(--green); }
.bg-orange { background: rgba(255,159,64,0.1); color: var(--orange); }

.table-panel {
    background: white;
    border-radius: 20px;
    padding: 30px;
    overflow-x: auto;
}

.table-panel h2 {
    font-size: 18px;
    margin-bottom: 20px;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
}

.orders-table th {
    background: #fafafa;
    color: var(--text-muted);
    padding: 16px;
    font-size: 13px;
    text-transform: uppercase;
    text-align: left;
}

.orders-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
}

.order-id {
    font-weight: 700;
    color: #111;
}

.amount-text {
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-success { background: rgba(41,204,151,0.1); color: var(--green); }
.status-pending { background: rgba(255,159,64,0.1); color: var(--orange); }
.status-failed { background: rgba(255,99,132,0.1); color: var(--red); }
.status-default { background: rgba(126,126,143,0.1); color: var(--text-muted); }

.customer-name {
    font-weight: 600;
}

.customer-address,
.customer-phone {
    font-size: 12px;
    margin-top: 2px;
}

.customer-address {
    color: var(--text-muted);
}

.customer-phone {
    color: var(--primary-accent);
}

.empty-state {
    text-align: center;
    padding: 50px;
    color: var(--text-muted);
}

/* USER VIEW */
.user-shell {
    width: 100%;
    min-height: 100vh;
    padding: 34px;
}

.user-main {
    max-width: 1120px;
    margin: 0 auto;
}

.user-topbar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}

.user-brand {
    color: var(--primary-accent);
    font-size: 22px;
    font-weight: 700;
}

.user-actions a {
    text-decoration: none;
    color: var(--text-muted);
    font-weight: 600;
    margin-left: 18px;
}

.user-hero {
    background: linear-gradient(135deg, rgba(99,91,255,0.11), rgba(41,204,151,0.10));
    border-radius: 26px;
    padding: 34px;
    margin-bottom: 28px;
}

.user-hero h1 {
    font-size: 30px;
    margin-bottom: 8px;
}

.user-hero p {
    color: var(--text-muted);
}

.user-orders-title {
    font-size: 20px;
    margin: 26px 0 16px;
}

.user-order-card {
    background: white;
    border-radius: 18px;
    padding: 20px;
    margin-bottom: 14px;
    display: grid;
    grid-template-columns: 1.2fr 1fr auto;
    gap: 18px;
    align-items: center;
}

.user-order-card .meta {
    color: var(--text-muted);
    font-size: 13px;
    margin-top: 5px;
}

.user-order-card .amount {
    font-size: 18px;
    font-weight: 700;
}
@media(max-width: 900px) {
    body {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main-content {
        margin-left: 0;
        padding: 20px;
    }

    .orders-summary-row {
        grid-template-columns: 1fr;
    }

    .user-order-card {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<?php if ($is_admin) { ?>

<aside class="sidebar">
    <div class="logo-area">
        <i class="fa-solid fa-square-poll-vertical"></i>
        <div>DaisyHook <span>Enterprise v2.4</span></div>
    </div>

    <ul class="nav-links">
        <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Overview</a></li>
        <li><a href="home.php"><i class="fa-solid fa-house"></i> View Live Site</a></li>

        <div class="nav-divider"></div>

        <li class="active"><a href="orders.php"><i class="fa-solid fa-box-archive"></i> Manage Orders</a></li>
        <li><a href="admin_payments.php"><i class="fa-solid fa-credit-card"></i> Payments Logs</a></li>
        <li><a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
        <li><a href="admin_bouquets.php"><i class="fi fi-ss-daisy-alt"></i> Bouquets Dept</a></li>
        <li><a href="admin_fashion.php"><i class="fa-solid fa-shirt"></i> Fashion Catalog</a></li>
        <li><a href="admin_decors.php"><i class="fa-solid fa-couch"></i> Decor Products</a></li>
        <li><a href="admin_contacts.php"><i class="fa-solid fa-envelope-open-text"></i> Contact Messages</a></li>

        <div class="nav-divider"></div>

        <li><a href="profile.php"><i class="fa-solid fa-user-circle"></i> My Profile</a></li>
        <li><a href="logout.php" style="color:#eb5757;"><i class="fa-solid fa-door-open"></i> Sign Out</a></li>
    </ul>
</aside>

<main class="main-content">
    <header class="dashboard-header">
        <h1>Order Fulfilment Center</h1>
        <p>Track customer checkout orders, monitor fulfilment status, and review delivery details.</p>
    </header>

    <section class="orders-summary-row">
        <div class="summary-box">
            <div class="summary-info">
                <h3>Volume Total</h3>
                <p><?php echo $count_total; ?> Orders</p>
            </div>
            <div class="summary-icon-box bg-purple">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-info">
                <h3>Settled Orders</h3>
                <p><?php echo $count_completed; ?> Success</p>
            </div>
            <div class="summary-icon-box bg-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-info">
                <h3>In Pipeline</h3>
                <p><?php echo $count_pending; ?> Pending</p>
            </div>
            <div class="summary-icon-box bg-orange">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </section>

    <section class="table-panel">
        <h2>Active Checkout Order Logs</h2>

        <?php if ($orders_result && $orders_result->num_rows > 0) { ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Details</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <!-- <th>Status</th> -->
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($row = $orders_result->fetch_assoc()) {
                    $location_parts = array_filter([
                        $row["region"] ?? "",
                        $row["city"] ?? "",
                        $row["area"] ?? ""
                    ]);

                    $location = !empty($location_parts)
                        ? implode(", ", $location_parts)
                        : "No address";

                    // $status_class = render_status_class($row["status"] ?? "");
                ?>
                    <tr>
                        <td class="order-id">
                            ORD-<?php echo str_pad($row["id"], 5, "0", STR_PAD_LEFT); ?>
                        </td>

                        <td>
                            <div class="customer-name">
                                <?php echo htmlspecialchars($row["customer_name"] ?? "Guest"); ?>
                            </div>
                            <div class="customer-address">
                                <?php echo htmlspecialchars($location); ?>
                            </div>
                            <div class="customer-phone">
                                <?php echo htmlspecialchars($row["phone_number"] ?? "N/A"); ?>
                            </div>
                        </td>

                        <td>PID-<?php echo htmlspecialchars($row["product_id"] ?? "N/A"); ?></td>

                        <td class="amount-text">
                            Rs.<?php echo number_format((float)($row["amount"] ?? 0), 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars(strtoupper($row["payment_method"] ?? "N/A")); ?>
                        </td>

                        <!-- <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($row["status"] ?? "Unknown"); ?>
                            </span>
                        </td> -->

                        <td style="color:var(--text-muted);">
                            <?php echo !empty($row["created_at"]) ? date("M d, Y", strtotime($row["created_at"])) : "N/A"; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <p>No orders found.</p>
            </div>
        <?php } ?>
    </section>
</main>

<?php } else { ?>

<div class="user-shell">
    <main class="user-main">

        <div class="user-topbar">
            <div class="user-brand">
                <i class="fa-solid fa-bag-shopping"></i> DaisyHook
            </div>

            <div class="user-actions">
                <a href="home.php">Shop</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" style="color:#eb5757;">Logout</a>
            </div>
        </div>

        <section class="user-hero">
            <h1>My Orders</h1>
            <p>Hi <?php echo htmlspecialchars($user["name"] ?? "there"); ?>, here are only the orders connected to your account.</p>
        </section>

        <section class="orders-summary-row">
            <div class="summary-box">
                <div class="summary-info">
                    <h3>Total Orders</h3>
                    <p><?php echo $count_total; ?></p>
                </div>
                <div class="summary-icon-box bg-purple">
                    <i class="fa-solid fa-box"></i>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-info">
                    <h3>Completed</h3>
                    <p><?php echo $count_completed; ?></p>
                </div>
                <div class="summary-icon-box bg-green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-info">
                    <h3>Pending</h3>
                    <p><?php echo $count_pending; ?></p>
                </div>
                <div class="summary-icon-box bg-orange">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </section>

        <h2 class="user-orders-title">Order History</h2>

        <?php if ($orders_result && $orders_result->num_rows > 0) { ?>
            <?php while ($row = $orders_result->fetch_assoc()) {
                $status_class = render_status_class($row["status"] ?? "");
            ?>
                <div class="user-order-card">
                    <div>
                        <div class="order-id">
                            ORD-<?php echo str_pad($row["id"], 5, "0", STR_PAD_LEFT); ?>
                        </div>
                        <div class="meta">
                            Product: PID-<?php echo htmlspecialchars($row["product_id"] ?? "N/A"); ?>
                            •
                            <?php echo !empty($row["created_at"]) ? date("M d, Y", strtotime($row["created_at"])) : "N/A"; ?>
                        </div>
                    </div>

                    <div>
                        <div class="amount">
                            $<?php echo number_format((float)($row["amount"] ?? 0), 2); ?>
                        </div>
                        <div class="meta">
                            Payment: <?php echo htmlspecialchars(strtoupper($row["payment_method"] ?? "N/A")); ?>
                        </div>
                    </div>

                    <div>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($row["status"] ?? "Unknown"); ?>
                        </span>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="empty-state">
                <i class="fa-solid fa-box-open"></i>
                <p>You have not placed any orders yet.</p>
            </div>
        <?php } ?>

    </main>
</div>

<?php } ?>

</body>
</html>