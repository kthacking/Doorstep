<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM agents WHERE email = ?");
    $stmt->execute([$email]);
    $agent = $stmt->fetch();

    if ($agent && password_verify($password, $agent['password'])) {
        if ($agent['status'] !== 'active') {
            $_SESSION['error'] = "Your account is " . $agent['status'] . ". Please contact admin.";
            header("Location: ../pages/agent_login.php");
            exit();
        }

        $_SESSION['agent_id'] = $agent['id'];
        $_SESSION['agent_name'] = $agent['agent_name'];
        $_SESSION['role'] = 'agent';

        header("Location: ../agent/dashboard.php");
    } else {
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: ../pages/agent_login.php");
    }
}
?>
