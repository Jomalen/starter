<?php
$host = 'localhost';
$dbname = 'inventory1'; // Changed from Inventory1 to inventory1
$user = 'root';
$pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create mysqli connection for legacy compatibility
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("mysqli connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
