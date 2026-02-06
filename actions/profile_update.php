<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $user_id]);

        $_SESSION['user_name'] = $name; // Update session name
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: ../user/profile.php");
    } catch (PDOException $e) {
        die("Error updating profile: " . $e->getMessage());
    }
}
?>
