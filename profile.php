<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "db.php";

/* Check login */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* Fetch user */
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>

<style>
body {
    font-family: Arial;
    background: #f5f5f5;
}

.profile-box {
    width: 400px;
    margin: 80px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
}

p {
    font-size: 18px;
    margin: 10px 0;
}

.logout {
    display: block;
    margin-top: 20px;
    text-align: center;
    background: #e91e63;
    color: white;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
}
</style>

</head>

<body>

<div class="profile-box">

<h2>My Profile</h2>

<p><b>Name:</b> <?php echo htmlspecialchars($user["name"]); ?></p>
<p><b>Email:</b> <?php echo htmlspecialchars($user["email"]); ?></p>
<p><b>Role:</b> <?php echo htmlspecialchars($user["role"]); ?></p>

<a class="logout" href="logout.php">Logout</a>

</div>

</body>
</html>