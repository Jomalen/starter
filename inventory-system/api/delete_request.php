<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Check authentication
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$requestId = isset($data['id']) ? (int)$data['id'] : 0;

if ($requestId > 0) {
    // Delete the request
    $query = "DELETE FROM maintenance_request WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $requestId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
}
