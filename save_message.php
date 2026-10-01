<?php
session_start();
include 'conn.php';

// Only allow registered users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registereduser') {
    echo "<script>alert('You must be logged in to send a message.'); window.history.back();</script>";
    exit();

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_SESSION['user_id']); // Logged-in user's ID
    $name = $_POST['con_name'] ?? '';
    $email = $_POST['con_email'] ?? '';
    $subject = $_POST['con_content'] ?? '';
    $message = $_POST['con_message'] ?? '';

    // Basic email validation (optional but good)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    // Use prepared statement for security
    $stmt = $conn->prepare("INSERT INTO message (UserID, Name, Email, Subject, Content) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $userId, $name, $email, $subject, $message);
        if ($stmt->execute()) {
            // Redirect back to contact page with success flag
echo "✅ Your message has been sent successfully!";
            exit();
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}
?>
