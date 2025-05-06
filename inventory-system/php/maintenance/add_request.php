<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate and sanitize input
$data = [
    'date_requested' => $conn->real_escape_string($_POST['date_requested'] ?? date('Y-m-d')),
    'request_type' => $conn->real_escape_string($_POST['request_type'] ?? ''),
    'serial_property_number' => $conn->real_escape_string($_POST['serial_property_number'] ?? ''),
    'description' => $conn->real_escape_string($_POST['description'] ?? ''),
    'requested_by' => $conn->real_escape_string($_POST['requested_by'] ?? ''),
    'requesting_office' => $conn->real_escape_string($_POST['requesting_office'] ?? ''),
    'other_details' => $conn->real_escape_string($_POST['other_details'] ?? '')
];

// Validate required fields
if (!$data['request_type'] || !$data['serial_property_number'] || !$data['description'] || 
    !$data['requested_by'] || !$data['requesting_office']) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

// Build the SQL query
$fields = array_filter($data, function($value) {
    return $value !== '';
});

$columns = implode(', ', array_keys($fields));
$values = implode(', ', array_map(function($value) use ($conn) {
    return "'" . $value . "'";
}, array_values($fields)));

$query = "INSERT INTO maintenance_request ($columns) VALUES ($values)";

if ($conn->query($query)) {
    // Log the activity
    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $activity = "Created new maintenance request for equipment: " . $data['serial_property_number'];
        $conn->query("INSERT INTO activity_log (user_id, activity) VALUES ($userId, '$activity')");
    }
    
    echo json_encode(['success' => true, 'message' => 'Request added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding request: ' . $conn->error]);
}
