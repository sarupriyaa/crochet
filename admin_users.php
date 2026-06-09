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

/* 2. Fetch Current Logged-in User Identity Properties */
$user_stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$current_user = $user_stmt->get_result()->fetch_assoc();

// Force kick non-admin users trying to access this management console
if (!$current_user || $current_user["role"] !== "admin") {
    header("Location: profile.php");
    exit();
}

/* 3. Handle User Role Update Request if submitted */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_role"])) {
    $target_user_id = intval($_POST["target_user_id"]);
    $new_role = $_POST["new_role"] === "admin" ? "admin" : "users";
    
    // Prevent admin from accidentally changing their own role to stay locked in
    if ($target_user_id !== intval($user_id)) {
        $update_stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
        $update_stmt->bind_param("si", $new_role, $target_user_id);
        $update_stmt->execute();
    }
    header("Location: admin_users.php");
    exit();
}

/* 4. Aggregate User Metrics dynamically from database schema */
$stats_query = "SELECT 
    COUNT(id) as total_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins,
    SUM(CASE WHEN role != 'admin' THEN 1 ELSE 0 END) as standard_clients
    FROM users";
$stats_row = $conn->query($stats_query)->fetch_assoc();

$total_accounts = $stats_row['total_users'] ?? 0;
$admin_accounts = $stats_row['total_admins'] ?? 0;
$client_accounts = $stats_row['standard_clients'] ?? 0;

/* 5. Fetch All User Accounts based on exact image_07b2f3.png column fields */
$users_query = "SELECT id, name, email, role FROM users ORDER BY id DESC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SuperDash</title>
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

        /* --- DASHBOARD SIDEBAR NAVIGATION --- */
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

        /* --- MAIN DATA VIEWPORT PANEL --- */
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

        /* Statistical Grid Summary Cards */
        .user-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.01);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .metric-info h3 {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .metric-info p {
            font-size: 26px;
            font-weight: 700;
            color: #111;
        }

        .icon-box-wrapper {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .bg-indigo { background: rgba(99, 91, 255, 0.1); color: var(--primary-accent); }
        .bg-cyan { background: rgba(54, 162, 235, 0.1); color: #36a2eb; }
        .bg-teal { background: rgba(41, 204, 151, 0.1); color: #29cc97; }

        /* Main Records Table Card Styling */
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

        .users-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .users-table th {
            background-color: #fafafa;
            color: var(--text-muted);
            padding: 16px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table td {
            padding: 16px;
            font-size: 15px;
            color: #333;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        /* Identity Badge Display Pills */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-admin { background: rgba(233, 30, 99, 0.1); color: #e91e63; }
        .badge-user { background: rgba(99, 91, 255, 0.1); color: var(--primary-accent); }

        .user-id-text { font-weight: 700; color: var(--text-muted); }
        .user-name-text { font-weight: 600; color: #111; }

        /* Elegant Role Switcher Form Action Selection */
        .action-select {
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: #fff;
            font-size: 13px;
            color: #333;
            outline: none;
            cursor: pointer;
            transition: border 0.2s;
        }

        .action-select:focus {
            border-color: var(--primary-accent);
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            border: none;
            background-color: #333;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 4px;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background-color: #111;
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
                <li><a href="admin_payments.php"><i class="fa-solid fa-credit-card"></i> Payments Logs</a></li>
                <li class="active"><a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
                <li><a href="admin_bouquets.php"><i class="fi fi-ss-daisy-alt"></i> Bouquets Dept</a></li>
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
            <h1>User Credentials Matrix</h1>
            <p>Audit system privilege states, track account registrations, and alter access roles dynamically.</p>
        </header>

        <section class="user-metrics-grid">
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Total Profiles</h3>
                    <p><?php echo $total_accounts; ?></p>
                </div>
                <div class="icon-box-wrapper bg-indigo"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Administrators</h3>
                    <p><?php echo $admin_accounts; ?></p>
                </div>
                <div class="icon-box-wrapper bg-cyan"><i class="fa-solid fa-user-shield"></i></div>
            </div>
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Standard Clients</h3>
                    <p><?php echo $client_accounts; ?></p>
                </div>
                <div class="icon-box-wrapper bg-teal"><i class="fa-solid fa-user-tag"></i></div>
            </div>
        </section>

        <section class="table-panel">
            <h2>Registered Database User Profiles</h2>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Privilege Access Level</th>
                        <th>Modify Clearance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users_result->fetch_assoc()) { 
                        $is_admin = (strtolower(trim($row["role"])) === 'admin');
                    ?>
                        <tr>
                            <td class="user-id-text">#USR-<?php echo str_pad($row["id"], 4, "0", STR_PAD_LEFT); ?></td>
                            <td class="user-name-text"><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td style="color: #555;"><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td>
                                <span class="role-badge <?php echo $is_admin ? 'badge-admin' : 'badge-user'; ?>">
                                    <i class="fa-solid <?php echo $is_admin ? 'fa-key' : 'fa-user'; ?>" style="font-size:10px;"></i>
                                    <?php echo htmlspecialchars($row["role"]); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="" style="display: inline-flex; align-items: center;">
                                    <input type="hidden" name="target_user_id" value="<?php echo $row["id"]; ?>">
                                    <select name="new_role" class="action-select">
                                        <option value="users" <?php if(!$is_admin) echo 'selected'; ?>>Standard User</option>
                                        <option value="admin" <?php if($is_admin) echo 'selected'; ?>>Administrator</option>
                                    </select>
                                    <button type="submit" name="update_role" class="action-btn" <?php if(intval($row["id"]) === intval($user_id)) echo 'disabled style="opacity:0.4; cursor:not-allowed;"'; ?>>
                                        Apply
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

    </main>

</body>
</html>