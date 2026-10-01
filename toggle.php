<?php
session_start();
include 'conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registereduser') {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

$userId = $_SESSION['user_id'];
$productId = intval($_GET['id']);

$productExists = $conn->query("SELECT ProductID FROM product WHERE ProductID = $productId")->num_rows > 0;
if (!$productExists) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

$isFavorited = $conn->query("SELECT FavoriteID FROM favorite WHERE UserID = $userId AND ProductID = $productId")->num_rows > 0;

if ($isFavorited) {
    $conn->query("DELETE FROM favorite WHERE UserID = $userId AND ProductID = $productId");
    $action = 'removed';
} else {
    $conn->query("INSERT INTO favorite (UserID, ProductID) VALUES ($userId, $productId)");
    $action = 'added';
}

echo json_encode(['success' => true, 'action' => $action]);
$conn->close();
?>