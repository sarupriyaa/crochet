<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

/* 1. Access Control: Security Gate Check */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* 2. Fetch Personal Identity Properties */
$user_stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user || $user["role"] !== "admin") {
    header("Location: profile.php");
    exit();
}

/* 3. Aggregate Payment Summaries dynamically */
// Changed status filter to catch all records and display Rs.
$sum_result = $conn->query("SELECT SUM(amount) AS gross, COUNT(id) as total_tx FROM orders WHERE status IS NOT NULL");
$sum_row = $sum_result->fetch_assoc();
$gross_revenue = "Rs. " . number_format((float)($sum_row['gross'] ?? 0), 2);
$total_transactions = $sum_row['total_tx'] ?? 0;

/* 4. Query transactions rows matching schema structure precisely */
$payments_query = "SELECT id, product_id, amount, payment_method, status, created_at, customer_name, region, city, area, phone_number FROM orders ORDER BY id DESC";
$payments_result = $conn->query($payments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Ledger - Crochet Management Center</title>

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

        .payments-summary-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .summary-box {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.01);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .summary-info h3 {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .summary-info p {
            font-size: 24px;
            font-weight: 700;
            color: #111;
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

        .bg-purple { background: rgba(99, 91, 255, 0.1); color: var(--primary-accent); }
        .bg-green { background: rgba(41, 204, 151, 0.1); color: #29cc97; }

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

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-success { background: rgba(41, 204, 151, 0.1); color: #29cc97; }
        .status-pending { background: rgba(255, 159, 64, 0.1); color: #ff9f40; }
        .status-failed { background: rgba(255, 99, 132, 0.1); color: #ff6384; }
        .status-default { background: rgba(126, 126, 143, 0.1); color: var(--text-muted); }

        .tx-id { font-weight: 700; color: #111; }
        .amount-text { font-weight: 600; color: #111; }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
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
                <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Overview</a></li>
                <li><a href="home.php"><i class="fa-solid fa-house"></i> View Live Site</a></li>
                
                <div class="nav-divider"></div>

                <li><a href="orders.php"><i class="fa-solid fa-box-archive"></i> Manage Orders</a></li>
                <li class="active"><a href="admin_payments.php"><i class="fa-solid fa-credit-card"></i> Payments Logs</a></li>
                <li><a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
                <li><a href="admin_bouquets.php"><i class="fa-solid fa-fleur-de-lis"></i> Bouquets Dept</a></li>
                <li><a href="admin_fashion.php"><i class="fa-solid fa-shirt"></i> Fashion Catalog</a></li>
                <li><a href="admin_decors.php"><i class="fa-solid fa-couch"></i> Decor Products</a></li>
                <li><a href="admin_contacts.php"><i class="fa-solid fa-envelope-open-text"></i> Contact Messages</a></li>
                
                <div class="nav-divider"></div>

                <li><a href="profile.php"><i class="fa-solid fa-user-circle"></i> My Profile</a></li>
                <li><a href="logout.php" style="color:#eb5757;"><i class="fa-solid fa-door-open"></i> Sign Out</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        
        <header class="dashboard-header">
            <h1>Payment Audit Transactions</h1>
            <p>Track store checkout logs, monitor total real-time balance revenue metrics, and analyze gate methods.</p>
        </header>

        <section class="payments-summary-row">
            <div class="summary-box">
                <div class="summary-info">
                    <h3>Audited Revenue</h3>
                    <p><?php echo $gross_revenue; ?></p>
                </div>
                <div class="summary-icon-box bg-green"><i class="fa-solid fa-money-bill-trend-up"></i></div>
            </div>

            <div class="summary-box">
                <div class="summary-info">
                    <h3>Settled Sales</h3>
                    <p><?php echo $total_transactions; ?> Success</p>
                </div>
                <div class="summary-icon-box bg-purple"><i class="fa-solid fa-receipt"></i></div>
            </div>
        </section>

        <section class="table-panel">
            <h2>Payment Invoices History Logs</h2>

            <?php if ($payments_result && $payments_result->num_rows > 0) { ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>TX ID</th>
                            <th>Customer Details</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $payments_result->fetch_assoc()) { 
                            $status_clean = strtolower(trim($row["status"] ?? ''));

                            $status_class = ($status_clean === "success" || $status_clean === "paid" || $status_clean === "completed") 
                                ? "status-success" 
                                : (($status_clean === "pending" || $status_clean === "processing") 
                                    ? "status-pending" 
                                    : (($status_clean === "failed" || $status_clean === "cancelled" || $status_clean === "canceled")
                                        ? "status-failed"
                                        : "status-default"));

                            $location_parts = array_filter([
                                $row["region"] ?? "",
                                $row["city"] ?? "",
                                $row["area"] ?? ""
                            ]);

                            $customer_location = !empty($location_parts)
                                ? implode(", ", $location_parts)
                                : "No address";
                        ?>

                            <tr>
                                <td style="font-weight:700;">
                                    TXN-<?php echo str_pad($row["id"], 5, "0", STR_PAD_LEFT); ?>
                                </td>

                                <td>
                                    <div style="font-weight:600;">
                                        <?php echo htmlspecialchars($row["customer_name"] ?? 'N/A'); ?>
                                    </div>

                                    <div style="font-size:12px; color:var(--text-muted);">
                                        <?php echo htmlspecialchars($customer_location); ?>
                                    </div>

                                    <div style="font-size:12px; color:var(--primary-accent);">
                                        <?php echo htmlspecialchars($row["phone_number"] ?? 'No phone'); ?>
                                    </div>
                                </td>

                                <td style="font-weight:600;">
                                    Rs. <?php echo number_format((float)$row["amount"], 2); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(strtoupper($row["payment_method"] ?? 'N/A')); ?>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($row["status"] ?? 'Unknown'); ?>
                                    </span>
                                </td>

                                <td style="color:var(--text-muted);">
                                    <?php echo date("M d, Y", strtotime($row["created_at"])); ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="fa-solid fa-barcode"></i>
                    <p>No billing invoices or incoming transaction entities found inside this database system table query.</p>
                </div>
            <?php } ?>
        </section>

    </main>

</body>
</html>