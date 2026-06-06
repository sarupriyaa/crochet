<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header("Content-Type: application/json");

include "../db.php";

if (!isset($_GET['ajax'])) {
    exit(json_encode(["error" => "Invalid request"]));
}

$q = isset($_GET['q']) ? trim($_GET['q']) : "";
$type = isset($_GET['type']) ? trim($_GET['type']) : "";

if (empty($q)) {
    echo json_encode([]);
    exit;
}

$safeQ = $conn->real_escape_string($q);
$sql = "";

switch (strtolower($type)) {
    case 'all':
        // We use empty strings '' for categories if the table doesn't have them
        // to ensure all UNION queries have exactly 4 columns.
        $sql = "SELECT id, title, description, 'Bouquet' AS category FROM bouquets 
                WHERE title LIKE '%$safeQ%' OR description LIKE '%$safeQ%'
                UNION
                SELECT id, title, description, category FROM decors 
                WHERE title LIKE '%$safeQ%' OR description LIKE '%$safeQ%' OR category LIKE '%$safeQ%'
                UNION
                SELECT id, title, description, 'Fashion' AS category FROM fashion 
                WHERE title LIKE '%$safeQ%' OR description LIKE '%$safeQ%'
                LIMIT 12";
        break;

    case 'bouquet':
    case 'bouquets':
        $sql = "SELECT id, title, description, 'Bouquet' AS category FROM bouquets 
                WHERE title LIKE '%$safeQ%' OR description LIKE '%$safeQ%' LIMIT 12";
        break;

    case 'decor':
    case 'decors':
        $sql = "SELECT id, title, description, category FROM decors 
                WHERE title LIKE '%$safeQ%' OR description LIKE '%$safeQ%' OR category LIKE '%$safeQ%' LIMIT 12";
        break;

    case 'fashion':
        $sql = "SELECT id, title, description, 'Fashion' AS category FROM fashion 
                WHERE title LIKE '%$safeQ%' OR description LIKE '%$safeQ%' LIMIT 12";
        break;

    default:
        echo json_encode([]);
        exit;
}

$result = $conn->query($sql);

if (!$result) {
    // This is the most important line for debugging
    echo json_encode(["error" => "SQL Error: " . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
exit;
?>