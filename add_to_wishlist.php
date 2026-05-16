<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /crochet/login.php");
    exit();
}

if (!isset($_GET["id"]) || !isset($_GET["type"])) {
    header("Location: /crochet/home.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$product_id = intval($_GET["id"]);
$product_type = trim($_GET["type"]);

$allowed_types = ["bouquet", "decor", "fashion"];

if (!in_array($product_type, $allowed_types)) {
    header("Location: /crochet/home.php");
    exit();
}

$check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? AND product_type = ?");
$check->bind_param("iis", $user_id, $product_id, $product_type);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id, product_type) VALUES (?, ?, ?)");
    $insert->bind_param("iis", $user_id, $product_id, $product_type);
    $insert->execute();
}

header("Location: /crochet/wishlist.php");
exit();
?>