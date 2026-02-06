<?php
require_once '../config/db.php';

if (!isset($_SESSION['agent_id'])) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $agent_id = $_SESSION['agent_id'];
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);

    try {
        $pdo->beginTransaction();

        // Ensure this agent is assigned to this booking AND get service name
        $check = $pdo->prepare("SELECT b.user_id, s.service_name 
                               FROM bookings b 
                               JOIN services s ON b.service_id = s.id 
                               WHERE b.id = ? AND b.agent_id = ?");
        $check->execute([$booking_id, $agent_id]);
        $booking = $check->fetch();

        if (!$booking) {
            die("Unauthorized update attempt.");
        }

        $service_name = $booking['service_name'];

        // Update Booking Status
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);

        // Log Status change
        $log = $pdo->prepare("INSERT INTO status_logs (booking_id, status, remarks) VALUES (?, ?, ?)");
        $log->execute([$booking_id, $status, $remarks]);

        // Notify User with Service Name
        $msg = "Update on your $service_name (#$booking_id): Status changed to '$status'. Note: $remarks";
        $notify = $pdo->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('user', ?, ?)");
        $notify->execute([$booking['user_id'], $msg]);

        // Notify Admin if completed
        if ($status == 'Completed') {
            $admin_msg = "$service_name (#$booking_id) has been marked as Completed by " . $_SESSION['agent_name'];
            $notify_admin = $pdo->prepare("INSERT INTO notifications (user_type, message) VALUES ('admin', ?)");
            $notify_admin->execute([$admin_msg]);
        }

        $pdo->commit();
        header("Location: ../agent/dashboard.php?success=1");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
