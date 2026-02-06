<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['agent_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $summary = trim($_POST['profile_summary']);
    $address = trim($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Get auto-approve setting
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_agents'");
    $stmt->execute();
    $auto_approve = $stmt->fetchColumn() == '1' ? 'active' : 'pending';

    try {
        $pdo->beginTransaction();

        // Check if already applied
        $check = $pdo->prepare("SELECT id FROM agents WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $_SESSION['error'] = "An application with this email already exists.";
            header("Location: ../pages/doorstepcare.php");
            exit();
        }

        // Insert into agents
        $stmt = $pdo->prepare("INSERT INTO agents (agent_name, gender, profile_summary, email, phone, address, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $gender, $summary, $email, $phone, $address, $password, $auto_approve]);
        
        $agent_id = $pdo->lastInsertId();

        // Notify Admin
        $msg = "New Agent Application: $name ($email). Status: $auto_approve";
        $notify = $pdo->prepare("INSERT INTO notifications (user_type, message) VALUES ('admin', ?)");
        $notify->execute([$msg]);

        $pdo->commit();
        
        if ($auto_approve == 'active') {
            $_SESSION['success'] = "Application approved! You can now login as an agent.";
        } else {
            $_SESSION['success'] = "Application submitted! Our admin will review it shortly.";
        }
        header("Location: ../pages/doorstepcare.php");
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../pages/doorstepcare.php");
    }
}
?>
