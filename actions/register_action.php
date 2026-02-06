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
        header("Location: ../pages/register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $address]);
        
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../pages/login.php");
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../pages/register.php");
    }
}
?>
