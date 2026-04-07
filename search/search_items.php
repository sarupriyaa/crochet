<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

include "../db.php";

if (!isset($_GET['ajax'])) {
    exit("Invalid request");
}

$q = isset($_GET['q']) ? trim($_GET['q']) : "";
$type = isset($_GET['type']) ? trim($_GET['type']) : "";

$safeQ = $conn->real_escape_string($q);

$sql = "";

if ($type === "bouquet") {
    $sql = "SELECT id, title, description, '' AS category
            FROM bouquets
            WHERE title LIKE '%$safeQ%'
               OR description LIKE '%$safeQ%'
            LIMIT 12";

} elseif ($type === "decor") {
    $sql = "SELECT id, title, description, category
            FROM decors
            WHERE title LIKE '%$safeQ%'
               OR description LIKE '%$safeQ%'
               OR category LIKE '%$safeQ%'
            LIMIT 12";

} elseif ($type === "fashion") {
    $sql = "SELECT id, title, description, '' AS category
            FROM fashions
            WHERE title LIKE '%$safeQ%'
               OR description LIKE '%$safeQ%'
            LIMIT 12";

} else {
    header("Content-Type: application/json");
    echo json_encode([]);
    exit;
}

$result = $conn->query($sql);

if (!$result) {
    header("Content-Type: application/json");
    echo json_encode([
        "error" => $conn->error
    ]);
    exit;
}

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
exit;
?>