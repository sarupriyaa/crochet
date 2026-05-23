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

/* 2. Verify Session Profiling Permissions */
$user_stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$current_user = $user_stmt->get_result()->fetch_assoc();

if (!$current_user || $current_user["role"] !== "admin") {
    header("Location: profile.php");
    exit();
}

/* 3. Handle Catalog Actions (Create / Update / Delete) */
$msg = "";

// Action A: Delete Item
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del_stmt = $conn->prepare("DELETE FROM bouquets WHERE id = ?");
    $del_stmt->bind_param("i", $delete_id);
    if ($del_stmt->execute()) {
        header("Location: admin_bouquets.php?status=deleted");
        exit();
    }
}

// Action B: Save Item (Add New or Edit Existing)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_bouquet'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = trim($_POST['title']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $quantity = intval($_POST['quantity']);
    
    // Set image string from fallback hidden field
    $image_name = trim($_POST['fallback_image']); 
    if (isset($_FILES['image_file']) && $_FILES['image_file']['name'] != '') {
        $image_name = basename($_FILES['image_file']['name']);
        // Optional file placement: move_uploaded_file($_FILES['image_file']['tmp_name'], "images/" . $image_name);
    }

    if ($id > 0) {
        // Update operation using database schema fields
        $up_stmt = $conn->prepare("UPDATE bouquets SET title=?, image=?, price=?, description=?, quantity=? WHERE id=?");
        $up_stmt->bind_param("ssdsii", $title, $image_name, $price, $description, $quantity, $id);
        $up_stmt->execute();
    } else {
        // Insert operation using database schema fields
        $in_stmt = $conn->prepare("INSERT INTO bouquets (title, image, price, description, quantity) VALUES (?, ?, ?, ?, ?)");
        $in_stmt->bind_param("ssdsi", $title, $image_name, $price, $description, $quantity);
        $in_stmt->execute();
    }
    header("Location: admin_bouquets.php?status=saved");
    exit();
}

/* 4. Aggregate Product Statistics dynamically from database fields */
$stats_query = "SELECT COUNT(id) as catalog_count, SUM(quantity) as stock_volume, AVG(price) as average_cost FROM bouquets";
$stats_res = $conn->query($stats_query)->fetch_assoc();

$total_products  = $stats_res['catalog_count'] ?? 0;
$total_inventory = $stats_res['stock_volume'] ?? 0;
$avg_price       = number_format((float)($stats_res['average_cost'] ?? 0), 2);

/* 5. Fetch full data list matching schema records */
$catalog_result = $conn->query("SELECT id, title, image, price, description, quantity FROM bouquets ORDER BY id DESC");

// Fetch single record if edit target trigger context exists
$edit_bouquet = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_stmt = $conn->prepare("SELECT * FROM bouquets WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_bouquet = $edit_stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouquets Catalog - SuperDash</title>

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

        /* Statistical Metric Cards Row */
        .metrics-grid-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
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
            margin-bottom: 6px;
        }

        .metric-info p {
            font-size: 24px;
            font-weight: 700;
            color: #111;
        }

        .icon-circle-box {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .bg-purple { background: rgba(99, 91, 255, 0.1); color: var(--primary-accent); }
        .bg-orange { background: rgba(255, 159, 64, 0.1); color: #ff9f40; }
        .bg-green { background: rgba(41, 204, 151, 0.1); color: #29cc97; }

        /* Workspace Grid Splitter for Forms and Tables */
        .workspace-split {
            display: grid;
            grid-template-columns: 1fr 2.2fr;
            gap: 30px;
            align-items: start;
        }

        @media (max-width: 1200px) {
            .workspace-split { grid-template-columns: 1fr; }
        }

        /* Modern Panel Card Box */
        .panel-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.015);
        }

        .panel-card h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form Fields Style */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e1e1e6;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-accent);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: var(--primary-accent);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #5148e5;
        }

        /* Table Layout Containers */
        .catalog-table-container {
            overflow-x: auto;
        }

        .catalog-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .catalog-table th {
            background-color: #fafafa;
            color: var(--text-muted);
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        .catalog-table td {
            padding: 14px 16px;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .item-thumb {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f1f5;
        }

        /* Stock Status Level Tags */
        .stock-indicator {
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
        }
        .stock-ok { background: rgba(41, 204, 151, 0.1); color: #29cc97; }
        .stock-low { background: rgba(255, 159, 64, 0.1); color: #ff9f40; }

        .action-icon-btn {
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 4px;
            display: inline-flex;
            align-items: center;
        }

        .btn-edit { background: rgba(99, 91, 255, 0.08); color: var(--primary-accent); }
        .btn-delete { background: rgba(255, 99, 132, 0.08); color: #ff6484; }

        .empty-view {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
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
                <li><a href="admin_users.php"><i class="fa-solid fa-users-gear"></i> Manage Users</a></li>
                <li class="active"><a href="admin_bouquets.php"><i class="fa-solid fa-fleur-de-lis"></i> Bouquets Dept</a></li>
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
            <div>
                <h1>Bouquets Catalog Inventory</h1>
                <p>Register new configurations, modify text specifications, and audit store quantities safely.</p>
            </div>
        </header>

        <section class="metrics-grid-layout">
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Distinct Designs</h3>
                    <p><?php echo $total_products; ?> Items</p>
                </div>
                <div class="icon-circle-box bg-purple"><i class="fa-solid fa-fleur-de-lis"></i></div>
            </div>
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Total Quantity Stock</h3>
                    <p><?php echo $total_inventory; ?> Units</p>
                </div>
                <div class="icon-circle-box bg-orange"><i class="fa-solid fa-warehouse"></i></div>
            </div>
            <div class="metric-card">
                <div class="metric-info">
                    <h3>Average Cost</h3>
                    <p>Rs. <?php echo $avg_price; ?></p>
                </div>
                <div class="icon-circle-box bg-green"><i class="fa-solid fa-tags"></i></div>
            </div>
        </section>

        <div class="workspace-split">
            
            <section class="panel-card">
                <h2>
                    <i class="fa-solid <?php echo $edit_bouquet ? 'fa-pen-to-square' : 'fa-circle-plus'; ?>" style="color:var(--primary-accent);"></i>
                    <?php echo $edit_bouquet ? 'Modify Design' : 'Create New Design'; ?>
                </h2>

                <form method="POST" action="admin_bouquets.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $edit_bouquet ? intval($edit_bouquet['id']) : 0; ?>">
                    <input type="hidden" name="fallback_image" value="<?php echo $edit_bouquet ? htmlspecialchars($edit_bouquet['image']) : 'default.jpg'; ?>">

                    <div class="form-group">
                        <label>Bouquet Title Name</label>
                        <input type="text" name="title" class="form-control" required value="<?php echo $edit_bouquet ? htmlspecialchars($edit_bouquet['title']) : ''; ?>" placeholder="e.g., Sunflower Arrangement">
                    </div>

                    <div class="form-group">
                        <label>Pricing Cost (Rs.)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $edit_bouquet ? floatval($edit_bouquet['price']) : ''; ?>" placeholder="e.g., 1200">
                    </div>

                    <div class="form-group">
                        <label>Quantity Available In-Store</label>
                        <input type="number" name="quantity" class="form-control" required value="<?php echo $edit_bouquet ? intval($edit_bouquet['quantity']) : '1'; ?>">
                    </div>

                    <div class="form-group">
                        <label>Product Catalog Image File</label>
                        <input type="file" name="image_file" class="form-control">
                        <?php if ($edit_bouquet && $edit_bouquet['image']) { ?>
                            <small style="color:var(--text-muted); display:block; margin-top:4px;">Assigned String: <?php echo htmlspecialchars($edit_bouquet['image']); ?></small>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label>Detailed Item Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Detail the stitch yarn patterns, layout color design components..." style="resize:none;"><?php echo $edit_bouquet ? htmlspecialchars($edit_bouquet['description']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="save_bouquet" class="btn-submit">
                        <i class="fa-solid fa-floppy-disk"></i> Commit Storage Records
                    </button>
                    
                    <?php if ($edit_bouquet) { ?>
                        <a href="admin_bouquets.php" style="display:block; text-align:center; font-size:13px; margin-top:10px; color:var(--text-muted); text-decoration:none;">Discard Edit Target</a>
                    <?php } ?>
                </form>
            </section>

            <section class="panel-card catalog-table-container">
                <h2><i class="fa-solid fa-list-check" style="color:var(--primary-accent);"></i> Active Ledger Index</h2>

                <?php if ($catalog_result && $catalog_result->num_rows > 0) { ?>
                    <table class="catalog-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Media Preview</th>
                                <th>Item Identification Details</th>
                                <th>Price</th>
                                <th>Volume</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $catalog_result->fetch_assoc()) { 
                                $is_low = (intval($row['quantity']) <= 1);
                            ?>
                                <tr>
                                    <td style="font-weight:700; color:var(--text-muted);">#<?php echo $row['id']; ?></td>
                                    <td>
                                        <img src="images/<?php echo htmlspecialchars($row['image'] ?: 'default.jpg'); ?>" class="item-thumb" alt="Bouquet view">
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:#111; margin-bottom:2px;"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <div style="font-size:12px; color:var(--text-muted); max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($row['description']); ?>">
                                            <?php echo htmlspecialchars($row['description']); ?>
                                        </div>
                                    </td>
                                    <td style="font-weight:600; color:#111;">Rs. <?php echo number_format($row['price'], 2); ?></td>
                                    <td>
                                        <span class="stock-indicator <?php echo $is_low ? 'stock-low' : 'stock-ok'; ?>">
                                            <?php echo intval($row['quantity']); ?> items
                                        </span>
                                    </td>
                                    <td>
                                        <a href="admin_bouquets.php?edit_id=<?php echo $row['id']; ?>" class="action-icon-btn btn-edit" title="Modify properties">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="admin_bouquets.php?delete_id=<?php echo $row['id']; ?>" class="action-icon-btn btn-delete" onclick="return confirm('Confirm permanent catalog deletion erasure check?');" title="Erase design">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="empty-view">
                        <i class="fa-solid fa-box-open" style="font-size:40px; color:#ddd; margin-bottom:10px; display:block;"></i>
                        <p>No bouquet listings located in the storage engine context logs.</p>
                    </div>
                <?php } ?>
            </section>

        </div>

    </main>

</body>
</html>
