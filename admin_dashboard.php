<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

/* 1. Access Control: Ensure user is logged in */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* 2. Fetch User & Verify Admin Privilege Status */
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user["role"] !== "admin") {
    header("Location: profile.php");
    exit();
}

/* 3. Operational Data Counters - Pulled Real-Time From Database */

// Calculate total users dynamically
$user_query = $conn->query("SELECT COUNT(id) AS total FROM users");
$user_data = $user_query->fetch_assoc();
$total_users = isset($user_data['total']) ? number_format($user_data['total']) : "0";

// Fetch Order Metrics dynamically from 'orders' table
$order_query = $conn->query("SELECT COUNT(id) AS total_sales, SUM(amount) AS revenue FROM orders");
$order_data = $order_query->fetch_assoc();

// Dynamically assign values
$active_sales = isset($order_data['total_sales']) ? number_format($order_data['total_sales']) : "0";
$total_revenue = isset($order_data['revenue']) ? "$" . number_format($order_data['revenue'], 2) : "$0.00";
$growth_rate = "0.0%"; // Placeholder for custom logic

// 6-Month Visual Chart Array
$chart_data = [
    "Jan" => 0, "Feb" => 0, "Mar" => 0, "Apr" => 0, "May" => 0, "Jun" => 0
];

// Checking for any simulated or recorded orders to decide timeline visibility
// (Assuming an 'orders' table exists, otherwise defaults gracefully to an empty timeline)
$recent_activities = [];
try {
    $activity_query = $conn->query("SELECT id FROM orders LIMIT 1");
    if ($activity_query && $activity_query->num_rows > 0) {
        // Placeholder loop container if elements ever populate in future production stages
    }
} catch (Exception $e) {
    // Suppress if tables aren't made yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperDash - Crochet Admin Center</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

        /* --- SIDEBAR CONTAINER --- */
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
            transition: all 0.2s ease;
        }

        .nav-links li.active a, 
        .nav-links a:hover {
            background: rgba(99, 91, 255, 0.08);
            color: var(--primary-purple);
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
            box-shadow: 0 4px 12px rgba(99, 91, 255, 0.2);
        }

        /* --- MAIN VIEWPORT PANEL --- */
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
            cursor: pointer;
            font-weight: 500;
            color: var(--text-dark);
        }

        .btn-export {
            background: var(--primary-purple);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
        }

        /* --- STATISTIC CARD ROWS --- */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
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
            font-size: 16px;
        }

        .metric-value {
            font-size: 26px;
            font-weight: 700;
            margin: 12px 0 6px 0;
            color: #111;
        }

        .metric-trend {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .trend-neutral { background: rgba(126, 126, 143, 0.1); color: var(--text-muted); }

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
            border-radius: 10px;
        }

        /* --- DATA BLOCK GRID --- */
        .dashboard-body-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .panel {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .panel-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        /* Pure CSS Chart Renderer Component */
        .chart-container {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 220px;
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
            background: linear-gradient(180deg, rgba(163, 128, 255, 0.8) 0%, var(--primary-purple) 100%);
            border-radius: 6px 6px 0 0;
            position: relative;
            animation: barGrow 0.8s ease-out forwards;
        }

        .chart-label {
            margin-top: 12px;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* --- TIMELINE EMPTY STATE --- */
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

        @keyframes barGrow {
            from { height: 0; }
        }

        /* Responsive Breakdowns */
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
                padding: 20px;
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
                
                <li><a href="admin_bouquets.php"><i class="fa-solid fa-fleur-de-lis"></i> Bouquets Management</a></li>
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
                <button class="btn-date"><i class="fa-regular fa-calendar-days"></i> Last 30 Days</button>
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
                <div class="metric-trend trend-neutral"><i class="fa-solid fa-minus"></i> Sales </div>
                <div class="progress-track">
                    <div class="progress-bar" style="width: 0%;"></div>
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
                    <div class="progress-bar" style="width: 5%;"></div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-title-row">
                    <span>Active Sales</span>
                    <div class="metric-icon-box"><i class="fa-solid fa-cart-shopping"></i></div>
                </div>
                <div class="metric-value"><?php echo $active_sales; ?></div>
                <div class="metric-trend trend-neutral"><i class="fa-solid fa-minus"></i>Orders</div>
                <div class="progress-track">
                    <div class="progress-bar" style="width: 0%;"></div>
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
                    <div>
                        <h2>Revenue Analytics</h2>
                        <p style="font-size: 13px; color: var(--text-muted); margin-top:3px;">Performance parameters over the last 6 months</p>
                    </div>
                </div>
                <div class="chart-container">
                    <?php foreach ($chart_data as $month => $height): ?>
                        <div class="chart-column">
                            <div class="chart-bar" style="height: <?php echo $height; ?>%;"></div>
                            <div class="chart-label"><?php echo $month; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Store Activity</h2>
                </div>
                
                <div class="empty-timeline-state">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <p>No transactions or account activities logged yet.</p>
                </div>
            </div>

        </div>

    </main>

</body>
</html>

