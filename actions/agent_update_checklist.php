<?php
require_once '../config/db.php';

if (!isset($_SESSION['agent_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $new_item = trim($_POST['item']);
    $agent_id = $_SESSION['agent_id'];

    if (!empty($new_item)) {
        // Fetch current checklist
        $stmt = $pdo->prepare("SELECT dynamic_checklist FROM bookings WHERE id = ? AND agent_id = ?");
        $stmt->execute([$booking_id, $agent_id]);
        $current = $stmt->fetchColumn();

        $updated = $current ? $current . ', ' . $new_item : $new_item;

        // Update database
        $upd = $pdo->prepare("UPDATE bookings SET dynamic_checklist = ? WHERE id = ?");
        $upd->execute([$updated, $booking_id]);
        
        echo "Success";
    }
}
?>
