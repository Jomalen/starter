<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

// Set content type to JSON
header('Content-Type: application/json');

// Get item ID from request
$itemId = $_GET['id'] ?? null;

if (!$itemId) {
    http_response_code(400);
    echo json_encode(['error' => 'Item ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
        exit;
    }
    
    echo json_encode($item);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}