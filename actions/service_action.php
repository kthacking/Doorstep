<?php
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if (isset($_POST['add'])) {
    $name = $_POST['service_name'];
    $charge = $_POST['charge'];
    $days = $_POST['days'];
    $docs = $_POST['documents'];
    $desc = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO services (service_name, service_charge, estimated_days, required_documents, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $charge, $days, $docs, $desc]);
    header("Location: ../admin/manage_services.php");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ../admin/manage_services.php");
}
?>
