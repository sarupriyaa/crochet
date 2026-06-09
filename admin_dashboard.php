<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user["role"] !== "admin") {
    header("Location: profile.php");
    exit();
}

$user_query = $conn->query("SELECT COUNT(id) AS total FROM users");
$user_data = $user_query->fetch_assoc();
$total_users = isset($user_data['total']) ? number_format($user_data['total']) : "0";

$order_query = $conn->query("SELECT COUNT(id) AS total_sales, SUM(amount) AS revenue FROM orders");
$order_data = $order_query->fetch_assoc();

$active_sales = isset($order_data['total_sales']) ? number_format($order_data['total_sales']) : "0";
$total_revenue = isset($order_data['revenue']) ? "$" . number_format($order_data['revenue'], 2) : "$0.00";
$growth_rate = "0.0%";

/* Revenue chart: current month + next 5 months */
/* Revenue chart: Show current month and 5 previous months */
$chart_data = [];

for ($i = 5; $i >= 0; $i--) {
    $month_key = date("Y-m", strtotime("-$i month"));
    $month_label = date("M", strtotime("-$i month"));
    $chart_data[$month_key] = [
        "label" => $month_label,
        "revenue" => 0,
        "height" => 0
    ];
}

$start_month = date("Y-m-01", strtotime("-5 month"));
$end_month = date("Y-m-t");

$revenue_query = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS order_month, SUM(amount) AS monthly_revenue
    FROM orders
    WHERE created_at BETWEEN '$start_month' AND '$end_month'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
");

$max_revenue = 0;

if ($revenue_query) {
    while ($row = $revenue_query->fetch_assoc()) {
        $month = $row["order_month"];
        $revenue = (float)$row["monthly_revenue"];

        if (isset($chart_data[$month])) {
            $chart_data[$month]["revenue"] = $revenue;
            if ($revenue > $max_revenue) {
                $max_revenue = $revenue;
            }
        }
    }
}

foreach ($chart_data as $key => $data) {
    if ($max_revenue > 0) {
        $chart_data[$key]["height"] = max(8, round(($data["revenue"] / $max_revenue) * 100));
    }
}
$recent_activities = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SuperDash - Crochet Admin Center</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
<style>
:root {
    --bg-color: #f4f5fa;
    --sidebar-bg: #ffffff;
    --card-bg: #ffffff;
    --primary-purple: #635bff;
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
}

.logo-area {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-purple);
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
}

.nav-links li.active a,
.nav-links a:hover {
    background: rgba(99, 91, 255, 0.08);
    color: var(--primary-purple);
}

.nav-divider {
    height: 1px;
    background: var(--border-color);
    margin: 15px 0;
}

.upgrade-btn {
    background: var(--primary-purple);
    color: white;
    text-align: center;
    padding: 14px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: block;
}

.main-content {
    margin-left: 260px;
    flex-grow: 1;
    padding: 40px;
    max-width: 1400px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.header-actions {
    display: flex;
    gap: 12px;
}

.btn-date {
    background: white;
    border: 1px solid var(--border-color);
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 500;
}

.btn-export {
    background: var(--primary-purple);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 500;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.metric-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
}

.metric-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 500;
}

.metric-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(99, 91, 255, 0.08);
    color: var(--primary-purple);
    display: flex;
    align-items: center;
    justify-content: center;
}

.metric-value {
    font-size: 26px;
    font-weight: 700;
    margin: 12px 0 6px;
    color: #111;
}

.metric-trend {
    font-size: 12px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}

.trend-neutral {
    background: rgba(126, 126, 143, 0.1);
    color: var(--text-muted);
}

.progress-track {
    background: #f0f0f5;
    border-radius: 10px;
    height: 5px;
    margin-top: 15px;
    overflow: hidden;
}

.progress-bar {
    background: var(--primary-purple);
    height: 100%;
}

.dashboard-body-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

.panel {
    background: white;
    border-radius: 20px;
    padding: 30px;
}

.panel-header {
    margin-bottom: 25px;
}

.panel-header h2 {
    font-size: 18px;
    font-weight: 600;
}

.chart-container {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 260px;
    padding-top: 15px;
}

.chart-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-grow: 1;
    height: 100%;
    justify-content: flex-end;
}

.chart-bar {
    width: 42px;
    background: linear-gradient(180deg, rgba(163, 128, 255, 0.8), var(--primary-purple));
    border-radius: 6px 6px 0 0;
}

.chart-value {
    font-size: 11px;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.chart-label {
    margin-top: 12px;
    font-size: 12px;
    color: var(--text-muted);
}

.empty-timeline-state {
    text-align: center;
    padding: 40px 10px;
    color: var(--text-muted);
    font-size: 14px;
}

.empty-timeline-state i {
    font-size: 28px;
    color: #d1d1db;
    margin-bottom: 12px;
    display: block;
}

@media (max-width: 1024px) {
    .dashboard-body-grid {
        grid-template-columns: 1fr;
    }

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
            <li class="active"><a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Overview</a></li>
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
            <li><a href="profile.php"><i class="fa-solid fa-user-circle"></i> My Profile (<?php echo htmlspecialchars(strtoupper($user['name'])); ?>)</a></li>
            <li><a href="logout.php" style="color:#eb5757;"><i class="fa-solid fa-door-open"></i> Sign Out</a></li>
        </ul>
    </div>

    <div>
        <a href="#" class="upgrade-btn"><i class="fa-solid fa-circle-up"></i> Upgrade Pro</a>
    </div>
</aside>

<main class="main-content">

<header class="dashboard-header">
    <div>
        <h1>Overview Dashboard</h1>
        <p>Welcome back admin, <strong><?php echo htmlspecialchars($user["name"]); ?></strong>. Here is your daily crochet store summary.</p>
    </div>

    <div class="header-actions">
        <button class="btn-date"><i class="fa-regular fa-calendar-days"></i> From This Month</button>
        <button class="btn-export"><i class="fa-solid fa-cloud-arrow-down"></i> Export Summary</button>
    </div>
</header>

<section class="metrics-grid">

    <div class="metric-card">
        <div class="metric-title-row">
            <span>Total Revenue</span>
            <div class="metric-icon-box"><i class="fa-solid fa-dollar-sign"></i></div>
        </div>
        <div class="metric-value"><?php echo $total_revenue; ?></div>
        <div class="metric-trend trend-neutral"><i class="fa-solid fa-minus"></i> Sales</div>
        <div class="progress-track">
            <div class="progress-bar" style="width: 70%;"></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-title-row">
            <span>Active Users</span>
            <div class="metric-icon-box"><i class="fa-solid fa-user-group"></i></div>
        </div>
        <div class="metric-value"><?php echo $total_users; ?></div>
        <div class="metric-trend trend-neutral"><i class="fa-solid fa-user"></i> Live Counts</div>
        <div class="progress-track">
            <div class="progress-bar" style="width: 40%;"></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-title-row">
            <span>Active Sales</span>
            <div class="metric-icon-box"><i class="fa-solid fa-cart-shopping"></i></div>
        </div>
        <div class="metric-value"><?php echo $active_sales; ?></div>
        <div class="metric-trend trend-neutral"><i class="fa-solid fa-minus"></i> Orders</div>
        <div class="progress-track">
            <div class="progress-bar" style="width: 50%;"></div>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-title-row">
            <span>Growth Rate</span>
            <div class="metric-icon-box"><i class="fa-solid fa-chart-line"></i></div>
        </div>
        <div class="metric-value"><?php echo $growth_rate; ?></div>
        <div class="metric-trend trend-neutral"><i class="fa-solid fa-minus"></i> Static</div>
        <div class="progress-track">
            <div class="progress-bar" style="width: 0%;"></div>
        </div>
    </div>

</section>

<div class="dashboard-body-grid">

    <div class="panel">
        <div class="panel-header">
            <h2>Revenue Analytics</h2>
            <p style="font-size:13px;color:var(--text-muted);margin-top:3px;">
                Performance from this month onward
            </p>
        </div>

        <div class="chart-container">
            <?php foreach ($chart_data as $data): ?>
                <div class="chart-column">
                    <div class="chart-value">
                        Rs. <?php echo number_format($data["revenue"], 0); ?>
                    </div>
                    <div class="chart-bar" style="height: <?php echo $data["height"]; ?>%;"></div>
                    <div class="chart-label"><?php echo $data["label"]; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
$recent_query = $conn->query("
    SELECT 
        id,
        customer_name,
        amount,
        payment_method,
        status,
        created_at
    FROM orders
    ORDER BY id DESC
    LIMIT 5
");
?>

<div class="panel">

    <div class="panel-header">
        <h2>Recent Store Activity</h2>
    </div>

    <?php if ($recent_query && $recent_query->num_rows > 0) { ?>

        <?php while ($activity = $recent_query->fetch_assoc()) { 

            $status_clean = strtolower(trim($activity["status"] ?? ""));

            $status_color = "#7e7e8f";

            if (
                $status_clean === "success" ||
                $status_clean === "completed" ||
                $status_clean === "paid"
            ) {
                $status_color = "#29cc97";
            } elseif (
                $status_clean === "pending" ||
                $status_clean === "processing"
            ) {
                $status_color = "#ff9f40";
            } elseif (
                $status_clean === "failed" ||
                $status_clean === "cancelled"
            ) {
                $status_color = "#ff6384";
            }
        ?>

        <div style="
            display:flex;
            gap:14px;
            align-items:flex-start;
            padding:18px 0;
            border-bottom:1px solid #f1f1f5;
        ">

            <div style="
                width:42px;
                height:42px;
                border-radius:12px;
                background:rgba(99,91,255,0.08);
                color:#635bff;
                display:flex;
                align-items:center;
                justify-content:center;
                flex-shrink:0;
            ">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>

            <div style="flex:1;">

                <div style="
                    font-size:14px;
                    line-height:1.6;
                    color:#222;
                ">

                    <strong>
                        <?php echo htmlspecialchars($activity['customer_name'] ?? 'Guest'); ?>
                    </strong>

                    placed an order of

                    <strong>
                        Rs. <?php echo number_format((float)$activity['amount'], 2); ?>
                    </strong>

                    using

                    <strong>
                        <?php echo htmlspecialchars(strtoupper($activity['payment_method'] ?? 'N/A')); ?>
                    </strong>

                </div>

                <div style="
                    margin-top:6px;
                    display:flex;
                    align-items:center;
                    gap:10px;
                    flex-wrap:wrap;
                ">

                    <span style="
                        font-size:12px;
                        color:#7e7e8f;
                    ">
                        <?php echo date("M d, Y h:i A", strtotime($activity['created_at'])); ?>
                    </span>

                    <span style="
                        font-size:11px;
                        font-weight:600;
                        padding:5px 10px;
                        border-radius:20px;
                        background:rgba(0,0,0,0.04);
                        color:<?php echo $status_color; ?>;
                    ">
                        <?php echo htmlspecialchars(ucfirst($activity['status'])); ?>
                    </span>

                </div>

            </div>

        </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty-timeline-state">
            <i class="fa-solid fa-clipboard-list"></i>
            <p>No transactions or account activities logged yet.</p>
        </div>

    <?php } ?>

</div>
</div>

</main>

</body>
</html>