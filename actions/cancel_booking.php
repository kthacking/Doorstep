<?php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id']) && isset($_SESSION['user_id'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    // Verify booking belongs to user and is in a cancellable state
    $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if ($booking) {
        $allowed_statuses = ['Booking Placed', 'Agent Assigned']; // Can only cancel before agent starts moving
        if (in_array($booking['status'], $allowed_statuses)) {
            // Update booking status
            $update = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
            if ($update->execute([$booking_id])) {
                // Log the status change
                $log = $pdo->prepare("INSERT INTO status_logs (booking_id, status, remarks) VALUES (?, 'Cancelled', 'Booking cancelled by user.')");
                $log->execute([$booking_id]);

                // Notify admin
                $notif = $pdo->prepare("INSERT INTO notifications (user_type, message) VALUES ('admin', ?)");
                $notif->execute(["Booking #$booking_id has been cancelled by the user."]);

                header("Location: ../user/dashboard.php?success=Booking cancelled successfully.");
                exit();
            }
        } else {
            header("Location: ../user/dashboard.php?error=Booking cannot be cancelled at this stage.");
            exit();
        }
    }
}

header("Location: ../user/dashboard.php");
exit();
