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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? ''; // Admin provided password or empty for default
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $office = $_POST['office'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Generate default password if none provided
    if (empty($password)) {
        // Default password format: first letter of full name + username + @123
        $defaultPass = strtolower(substr($full_name, 0, 1) . $username . '@123');
        $password = $defaultPass;
    }

    // Validate required fields
    if (!$username || !$full_name || !$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Required fields missing']);
        exit;
    }

    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role, office, phone, is_active, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'Active', NOW())");
        
        $stmt->execute([
            $username,
            $hashed_password,
            $full_name,
            $email,
            $role,
            $office,
            $phone
        ]);

        $newUserId = $pdo->lastInsertId();

        // Log the activity
        $admin_id = $_SESSION['user']['id'];
        $activity = "Created new user: $username";
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
        $stmt->execute([$admin_id, $activity]);

        // Return success with user info and default password if one was generated
        $response = [
            'success' => true, 
            'message' => 'User created successfully',
            'user_id' => $newUserId
        ];

        // Only include default password in response if one was generated
        if (empty($_POST['password'])) {
            $response['default_password'] = $defaultPass;
        }

        echo json_encode($response);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
