<?php
require_once '../config/db.php';

// Only existing admins can register new admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

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
        header("Location: ../admin/register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Explicitly set role as 'admin'
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, 'admin')");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $address]);
        
        $_SESSION['success'] = "New Admin account created successfully!";
        header("Location: ../admin/register.php");
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../admin/register.php");
    }
}
?>
