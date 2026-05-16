<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /crochet/login.php");
    exit();
}

if (isset($_GET["id"])) {
    $wishlist_id = intval($_GET["id"]);
    $user_id = $_SESSION["user_id"];

    $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $wishlist_id, $user_id);
    $stmt->execute();
}

header("Location: /crochet/wishlist.php");
exit();
?>