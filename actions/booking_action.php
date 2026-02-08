<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $time_slot = $_POST['time_slot'];
    $preferred_gender = $_POST['preferred_gender'] ?? 'Any';
    $address = $_POST['address'];
    $total_amount = $_POST['total_amount'];

    try {
        $pdo->beginTransaction();

        // Fetch service details for initial checklist and notification
        $svc_stmt = $pdo->prepare("SELECT service_name, required_documents FROM services WHERE id = ?");
        $svc_stmt->execute([$service_id]);
        $service = $svc_stmt->fetch();
        
        $service_name = $service['service_name'];
        $initial_docs = $service['required_documents'];

        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, booking_date, time_slot, preferred_gender, status, address_confirmed, dynamic_checklist, total_amount) VALUES (?, ?, ?, ?, ?, 'Booking Placed', ?, ?, ?)");
        $stmt->execute([$user_id, $service_id, $booking_date, $time_slot, $preferred_gender, $address, $initial_docs, $total_amount]);
        
        $booking_id = $pdo->lastInsertId();

        // Initial Status Log
        $log_stmt = $pdo->prepare("INSERT INTO status_logs (booking_id, status, remarks) VALUES (?, 'Booking Placed', 'Your booking has been received and is waiting for agent assignment.')");
        $log_stmt->execute([$booking_id]);

        // Notify Admin with User and Service Name (Key-based for multilingual support)
        $notif_msg = "notif_new_booking::" . json_encode(['service' => $service_name, 'id' => $booking_id, 'user' => $_SESSION['user_name']]);
        $notify = $pdo->prepare("INSERT INTO notifications (user_type, message) VALUES ('admin', ?)");
        $notify->execute([$notif_msg]);

        $pdo->commit();
        
        $_SESSION['success'] = "Booking confirmed! You can track your status here.";
        header("Location: ../user/dashboard.php");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['error'] = "Booking failed: " . $e->getMessage();
        header("Location: ../pages/services.php");
    }
}
?>
