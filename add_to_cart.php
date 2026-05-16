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

$check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND product_type = ?");
$check->bind_param("iis", $user_id, $product_id, $product_type);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_quantity = $row["quantity"] + 1;

    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_quantity, $row["id"]);
    $update->execute();
} else {
    $quantity = 1;

    $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, product_type, quantity) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iisi", $user_id, $product_id, $product_type, $quantity);
    $insert->execute();
}

header("Location: /crochet/cart.php");
exit();
?>