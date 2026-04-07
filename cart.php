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
$type = trim($_GET["type"]);

/* Example table: cart (id, user_id, product_id, product_type, quantity) */
$check = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND product_type = ?");
$check->bind_param("iis", $user_id, $product_id, $type);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_type, quantity) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("iis", $user_id, $product_id, $type);
    $stmt->execute();
}

header("Location: /crochet/cart.php");
exit();
?>