<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: ../pages/admin_register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Create as admin
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, 'admin')");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $address]);
        
        $_SESSION['success'] = "Admin registration successful! Please login.";
        header("Location: ../pages/login.php");
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../pages/admin_register.php");
    }
}
?>
