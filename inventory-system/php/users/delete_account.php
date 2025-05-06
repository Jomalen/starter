<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check authentication
$auth = new auth();
$auth->checkAuth();

// Set content type to JSON
header('Content-Type: application/json');

// Get the JSON data
$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';
$userId = $_SESSION['user']['id'];

try {
    // Verify password first
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Delete profile picture if it exists and is not the default
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile['profile_pic'] && 
        $profile['profile_pic'] !== 'assets/img/default-avatar.jpg' && 
        file_exists("../../" . $profile['profile_pic'])) {
        unlink("../../" . $profile['profile_pic']);
    }

    // Delete user's activity logs
    $stmt = $pdo->prepare("DELETE FROM activity_log WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    $pdo->commit();

    // Clear session
    session_destroy();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error deleting account']);
}