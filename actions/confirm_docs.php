<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $user_type = $_POST['user_type']; // 'user' or 'agent'
    
    try {
        if ($user_type === 'user') {
            if (!isset($_SESSION['user_id'])) die("Unauthorized");
            $stmt = $pdo->prepare("UPDATE bookings SET user_doc_confirmed = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $_SESSION['user_id']]);
            
            // Notify Admin
            $notify = $pdo->prepare("INSERT INTO notifications (user_type, message) VALUES ('admin', ?)");
            $notify->execute(["User confirmed document handover for Booking (#$booking_id)"]);
            
            header("Location: ../user/booking_details.php?id=$booking_id");
        } elseif ($user_type === 'agent') {
            if (!isset($_SESSION['agent_id'])) die("Unauthorized");
            $stmt = $pdo->prepare("UPDATE bookings SET agent_doc_confirmed = 1 WHERE id = ? AND agent_id = ?");
            $stmt->execute([$booking_id, $_SESSION['agent_id']]);

            // Notify User
            $booking_stmt = $pdo->prepare("SELECT user_id FROM bookings WHERE id = ?");
            $booking_stmt->execute([$booking_id]);
            $uid = $booking_stmt->fetchColumn();

            $notify = $pdo->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('user', ?, ?)");
            $notify->execute([$uid, "Agent has accepted and confirmed your document submission for booking #$booking_id."]);

            header("Location: ../agent/dashboard.php?success=1");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
