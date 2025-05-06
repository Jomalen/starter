<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

// Set content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$itemId = $_GET['id'] ?? null;

if (!$itemId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get item details for logging
    $stmt = $pdo->prepare("SELECT model, serial_number FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception('Item not found');
    }

    // Delete the item
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
    $stmt->execute([$itemId]);

    // Log the activity
    $user_id = $_SESSION['user']['id'];
    $activity = "Deleted item: {$item['model']} (SN: {$item['serial_number']})";
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$user_id, $activity]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
