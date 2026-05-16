<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /crochet/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<link rel="stylesheet" href="/crochet/style.css">
<link rel="stylesheet" href="/crochet/footer.css">
<link rel="stylesheet" href="/crochet/navbar.css">


<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f7f3ef;
}

.dashboard {
    padding: 60px 20px;
    min-height: 70vh;
}

.dashboard h1 {
    text-align: center;
    margin-bottom: 40px;
    color: #7a4e3a;
}

.dashboard-grid {
    max-width: 1000px;
    margin: auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
}

.dashboard-card {
    background: white;
    padding: 30px 20px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 8px 22px rgba(0,0,0,0.1);
    transition: 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-8px);
}

.dashboard-card h3 {
    margin-bottom: 15px;
    color: #333;
}

.dashboard-card a {
    display: inline-block;
    padding: 10px 20px;
    background: #b56e4a;
    color: white;
    text-decoration: none;
    border-radius: 25px;
}

.dashboard-card a:hover {
    background: #8f5437;
}
</style>
</head>

<body>

<?php include "navbar.php"; ?>

<section class="dashboard">

    <h1>Admin Dashboard</h1>

    <div class="dashboard-grid">

        <div class="dashboard-card">
            <h3>Manage Bouquets</h3>
            <a href="/crochet/admin_bouquets.php">Open</a>
        </div>

        <div class="dashboard-card">
            <h3>Manage Fashion</h3>
            <a href="/crochet/admin_fashion.php">Open</a>
        </div>

        <div class="dashboard-card">
            <h3>Manage Decor</h3>
            <a href="/crochet/admin_decors.php">Open</a>
        </div>

        <div class="dashboard-card">
            <h3>Manage Orders</h3>
            <a href="/crochet/admin_orders.php">Open</a>
        </div>

        <div class="dashboard-card">
            <h3>Manage Users</h3>
            <a href="/crochet/admin_users.php">Open</a>
        </div>

        <div class="dashboard-card">
            <h3>Profile</h3>
            <a href="/crochet/profile.php">Open</a>
        </div>

    </div>

</section>

<?php include "footer.php"; ?>

</body>
</html>