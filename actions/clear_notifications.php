<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['agent_id'])) {
    die("Unauthorized");
}

$role = $_SESSION['role'];

if ($role === 'admin') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_type = 'admin'");
    $stmt->execute();
} elseif ($role === 'agent') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_type = 'agent' AND user_id = ?");
    $stmt->execute([$_SESSION['agent_id']]);
} elseif ($role === 'user') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_type = 'user' AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
