<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to subscribe.']);
    exit;
}

include 'conn.php';  

$email = $_SESSION['user_email'];
$userID = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM newslettersubscriber WHERE Email = ? AND UserID = ?");
    $stmt->bind_param("si", $email, $userID);  // Bind parameters ('s' for string, 'i' for integer)
    $stmt->execute();
    $result = $stmt->get_result();
    $subscriber = $result->fetch_assoc();

    if ($subscriber) {
        $newStatus = $subscriber['IsActive'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE newslettersubscriber SET IsActive = ?, SubscribedDate = NOW() WHERE SubscriberID = ?");
        $stmt->bind_param("ii", $newStatus, $subscriber['SubscriberID']); 
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => $newStatus ? 'Subscribed successfully.' : 'Unsubscribed successfully.',
            'status' => $newStatus ? 'subscribed' : 'unsubscribed'
        ]);
    } else {
        $stmt = $conn->prepare("INSERT INTO newslettersubscriber (Email, UserID, SubscribedDate, IsActive) VALUES (?, ?, NOW(), 1)");
        $stmt->bind_param("si", $email, $userID);  
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Subscribed successfully.',
            'status' => 'subscribed'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>
