<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if (isset($_POST['add'])) {
    $name = $_POST['agent_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    // Default password for manual add is 123456
    $pw = password_hash('123456', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO agents (agent_name, phone, email, password, address, status) VALUES (?, ?, ?, ?, 'Manually Added', 'active')");
    $stmt->execute([$name, $phone, $email, $pw]);
    header("Location: ../admin/manage_agents.php");
}

if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    
    // Get agent info for notification
    $get = $pdo->prepare("SELECT agent_name FROM agents WHERE id = ?");
    $get->execute([$id]);
    $name = $get->fetchColumn();

    $stmt = $pdo->prepare("UPDATE agents SET status = 'active' WHERE id = ?");
    $stmt->execute([$id]);

    // Internal notification
    $msg = "Agent $name has been approved and welcomed to the team.";
    $notify = $pdo->prepare("INSERT INTO notifications (user_type, message) VALUES ('admin', ?)");
    $notify->execute([$msg]);

    header("Location: ../admin/manage_agents.php?success=approved");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM agents WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../admin/manage_agents.php");
}

if (isset($_POST['change_password'])) {
    $id = $_POST['agent_id'];
    $new_pw = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE agents SET password = ? WHERE id = ?");
    $stmt->execute([$new_pw, $id]);
    header("Location: ../admin/manage_agents.php?success=password_changed");
}
?>
