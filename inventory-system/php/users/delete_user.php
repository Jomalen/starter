<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_GET['id'] ?? null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    // First check if user exists and is not trying to delete themselves
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($userId == $_SESSION['user']['id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        exit;
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    // Log the activity
    $admin_id = $_SESSION['user']['id'];
    $activity = "Deleted user: " . $user['username'];
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$admin_id, $activity]);

    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
