<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $agent_id = $_POST['agent_id'] ?: null;
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']) ?: "Status updated by Admin to $status";

    try {
        $pdo->beginTransaction();

        // Get details for notification
        $check = $pdo->prepare("SELECT b.user_id, s.service_name 
                               FROM bookings b 
                               JOIN services s ON b.service_id = s.id 
                               WHERE b.id = ?");
        $check->execute([$booking_id]);
        $booking = $check->fetch();

        if (!$booking) {
            die("Booking not found.");
        }

        $service_name = $booking['service_name'];

        // Update Booking
        $stmt = $pdo->prepare("UPDATE bookings SET agent_id = ?, status = ? WHERE id = ?");
        $stmt->execute([$agent_id, $status, $booking_id]);

        // Insert Status Log
        $log_stmt = $pdo->prepare("INSERT INTO status_logs (booking_id, status, remarks) VALUES (?, ?, ?)");
        $log_stmt->execute([$booking_id, $status, $remarks]);

        // Notify User
        $user_msg = "notif_agent_assigned::" . json_encode([
            'agent' => $agent_id ? ($pdo->query("SELECT agent_name FROM agents WHERE id = $agent_id")->fetchColumn()) : 'None',
            'service' => $service_name,
            'id' => $booking_id
        ]);
        $notify_user = $pdo->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('user', ?, ?)");
        $notify_user->execute([$booking['user_id'], $user_msg]);

        // If agent assigned, notify agent
        if ($agent_id) {
            $agent_msg = "notif_agent_assigned::" . json_encode([
                'agent' => 'You',
                'service' => $service_name,
                'id' => $booking_id
            ]);
            $notify_agent = $pdo->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('agent', ?, ?)");
            $notify_agent->execute([$agent_id, $agent_msg]);
        }

        $pdo->commit();
        header("Location: ../admin/manage_booking.php?id=$booking_id");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
