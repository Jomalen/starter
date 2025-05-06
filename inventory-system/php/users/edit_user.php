<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

// Set content type to JSON for AJAX responses
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the user ID and check permissions
$userId = $_POST['id'] ?? null;
$currentUser = $_SESSION['user'];

// Only admin can edit other users, regular users can only edit their own profile
if (!$auth->isAdmin() && $userId != $currentUser['id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get current user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Prepare update data
    $updateData = [
        'full_name' => $_POST['full_name'] ?? $user['full_name'],
        'email' => $_POST['email'] ?? $user['email'],
        'phone' => $_POST['phone'] ?? $user['phone'],
        'office' => $_POST['office'] ?? $user['office'],
        'address' => $_POST['address'] ?? $user['address']
    ];

    // Only admin can update these fields
    if ($auth->isAdmin()) {
        $updateData['role'] = $_POST['role'] ?? $user['role'];
        $updateData['is_active'] = isset($_POST['is_active']) ? 
            filter_var($_POST['is_active'], FILTER_VALIDATE_BOOLEAN) : 
            $user['is_active'];
    }

    // Handle password update
    if (!empty($_POST['new_password'])) {
        // Verify current password
        if (!password_verify($_POST['current_password'], $user['password'])) {
            throw new Exception('Current password is incorrect');
        }
        $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception('Invalid file type. Allowed: jpg, jpeg, png, gif');
        }

        $newFilename = "profile_" . $userId . "_" . time() . "." . $ext;
        $uploadPath = "../../assets/img/profiles/" . $newFilename;

        // Create directory if it doesn't exist
        if (!file_exists("../../assets/img/profiles/")) {
            mkdir("../../assets/img/profiles/", 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {
            // Delete old profile picture if it exists and is not the default
            if ($user['profile_pic'] && 
                $user['profile_pic'] !== 'assets/img/default-avatar.jpg' && 
                file_exists("../../" . $user['profile_pic'])) {
                unlink("../../" . $user['profile_pic']);
            }
            $updateData['profile_pic'] = "assets/img/profiles/" . $newFilename;
        }
    }

    // Build update query
    $updateFields = [];
    $params = [];
    foreach ($updateData as $field => $value) {
        $updateFields[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $userId;

    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Log the activity
    $activityUser = $auth->isAdmin() && $userId != $currentUser['id'] ? 
        "Updated user: " . $user['username'] :
        "Updated own profile";
    
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$currentUser['id'], $activityUser]);

    $pdo->commit();

    // Update session data if user updated their own profile
    if ($userId == $currentUser['id']) {
        $_SESSION['user'] = array_merge($_SESSION['user'], $updateData);
    }

    // Return success response with updated user data
    $updateData['id'] = $userId;
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully',
        'user' => $updateData
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
