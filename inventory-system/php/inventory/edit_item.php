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
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Item ID is required');
    }

    // Get current item data
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception('Item not found');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Check if serial number exists and is not the current item
    $serial_number = $_POST['serial_number'] ?? '';
    if ($serial_number !== $item['serial_number']) {
        $stmt = $pdo->prepare("SELECT id FROM items WHERE serial_number = ? AND id != ?");
        $stmt->execute([$serial_number, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Serial number already exists');
        }
    }

    // Prepare update data
    $updateData = [
        'model' => $_POST['model'] ?? $item['model'],
        'serial_number' => $serial_number,
        'property_number' => $_POST['property_number'] ?? $item['property_number'],
        'operating_system' => $_POST['operating_system'] ?? $item['operating_system'],
        'brand' => $_POST['brand'] ?? $item['brand'],
        'memory' => $_POST['memory'] ?? $item['memory'],
        'description' => $_POST['description'] ?? $item['description'],
        'end_user' => $_POST['end_user'] ?? $item['end_user'],
        'location' => $_POST['location'] ?? $item['location'],
        'property_category' => $_POST['property_category'] ?? $item['property_category']
    ];

    // Build update query
    $updateFields = [];
    $params = [];
    foreach ($updateData as $field => $value) {
        $updateFields[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $id;

    $sql = "UPDATE items SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Log the activity
    $user_id = $_SESSION['user']['id'];
    $activity = "Updated item: {$updateData['model']} (SN: {$updateData['serial_number']})";
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$user_id, $activity]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Item updated successfully',
        'item' => $updateData
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
