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

try {
    $model = $_POST['model'] ?? '';
    $serial_number = $_POST['serial_number'] ?? '';
    $property_number = $_POST['property_number'] ?? '';
    $operating_system = $_POST['operating_system'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $memory = $_POST['memory'] ?? '';
    $end_user = $_POST['end_user'] ?? '';
    $location = $_POST['location'] ?? '';
    $property_category = $_POST['property_category'] ?? '';

    // Validate required fields
    if (!$model || !$serial_number || !$property_number) {
        throw new Exception('Required fields missing');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Check if serial number already exists
    $stmt = $pdo->prepare("SELECT id FROM items WHERE serial_number = ?");
    $stmt->execute([$serial_number]);
    if ($stmt->fetch()) {
        throw new Exception('Serial number already exists');
    }

    // Insert new item
    $stmt = $pdo->prepare("INSERT INTO items (model, serial_number, property_number, operating_system, 
                                            brand, memory, description, end_user, location, property_category, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->execute([
        $model,
        $serial_number,
        $property_number,
        $operating_system,
        $brand,
        $memory,
        $_POST['description'] ?? '',
        $end_user,
        $location,
        $property_category
    ]);

    $newItemId = $pdo->lastInsertId();

    // Log the activity
    $user_id = $_SESSION['user']['id'];
    $activity = "Added new item: $model (SN: $serial_number)";
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$user_id, $activity]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Item added successfully',
        'item_id' => $newItemId
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
