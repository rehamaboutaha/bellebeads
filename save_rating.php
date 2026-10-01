<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login to rate products");
}

$userId = $_SESSION['user_id'];
$productId = intval($_POST['product_id']);
$rating = intval($_POST['rating']);

if ($rating == 0) {
    $conn->query("DELETE FROM review WHERE UserID = $userId AND ProductID = $productId");
} else {
    if ($rating < 1 || $rating > 5) {
        die("Invalid rating value");
    }
    
    $check = $conn->query("SELECT 1 FROM review WHERE UserID = $userId AND ProductID = $productId");
    
    if ($check->num_rows > 0) {
        $conn->query("UPDATE review SET Rating = $rating WHERE UserID = $userId AND ProductID = $productId");
    } else {
        $conn->query("INSERT INTO review (UserID, ProductID, Rating) VALUES ($userId, $productId, $rating)");
    }
}

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
?>