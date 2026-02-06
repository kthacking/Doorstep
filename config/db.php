<?php
// Database Configuration
$host = 'localhost';
$dbname = 'doorstep_service_db';
$username = 'root';
$password = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Global settings
define('APP_NAME', 'Doorstep Government Services');
define('CURRENCY', '₹');

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
