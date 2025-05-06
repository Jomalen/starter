<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get request ID and action
$id = (int)$_POST['id'];
$action = $_POST['action'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

// Validate and sanitize input based on action
$data = [];
$now = date('Y-m-d H:i:s');

switch ($action) {
    case 'receive':
        $data = [
            'date_received' => $now,
            'received_by' => $conn->real_escape_string($_POST['received_by'] ?? ''),
            'pre_maintenance_eval' => $conn->real_escape_string($_POST['pre_maintenance_eval'] ?? ''),
            'inspected_by' => $conn->real_escape_string($_POST['inspected_by'] ?? ''),
            'inspection_date' => $now
        ];

        // Validate required fields
        if (!$data['pre_maintenance_eval'] || !$data['inspected_by']) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            exit;
        }
        break;

    case 'complete':
        $data = [
            'date_accomplished' => $now,
            'accomplished_by' => $conn->real_escape_string($_POST['accomplished_by'] ?? ''),
            'corrective_action' => $conn->real_escape_string($_POST['corrective_action'] ?? ''),
            'result' => $conn->real_escape_string($_POST['result'] ?? ''),
            'recommendation' => $conn->real_escape_string($_POST['recommendation'] ?? ''),
            'service_degree' => $conn->real_escape_string($_POST['service_degree'] ?? '')
        ];

        // Validate required fields
        if (!$data['accomplished_by'] || !$data['corrective_action'] || !$data['result'] || !$data['service_degree']) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            exit;
        }
        break;

    case 'dispose':
        $data = [
            'date_accomplished' => $now,
            'accomplished_by' => $conn->real_escape_string($_POST['accomplished_by'] ?? ''),
            'corrective_action' => $conn->real_escape_string($_POST['corrective_action'] ?? ''),
            'result' => $conn->real_escape_string($_POST['result'] ?? ''),
            'recommendation' => $conn->real_escape_string($_POST['recommendation'] ?? ''),
            'service_degree' => $conn->real_escape_string($_POST['service_degree'] ?? ''),
            'for_disposal' => 1,
            'disposal_type' => $conn->real_escape_string($_POST['disposal_type'] ?? ''),
            'disposal_equipment_type' => $conn->real_escape_string($_POST['disposal_equipment_type'] ?? ''),
            'disposal_property_no' => $conn->real_escape_string($_POST['disposal_property_no'] ?? ''),
            'disposal_serial_no' => $conn->real_escape_string($_POST['disposal_serial_no'] ?? ''),
            'disposal_confirmed_by' => $conn->real_escape_string($_POST['disposal_confirmed_by'] ?? ''),
            'disposal_accepted_by' => $conn->real_escape_string($_POST['disposal_accepted_by'] ?? '')
        ];

        // Validate required fields
        if (!$data['accomplished_by'] || !$data['corrective_action'] || !$data['result'] || !$data['service_degree'] ||
            !$data['disposal_type'] || !$data['disposal_equipment_type'] || !$data['disposal_property_no'] ||
            !$data['disposal_serial_no'] || !$data['disposal_confirmed_by'] || !$data['disposal_accepted_by']) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            exit;
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

// Build update query
$updates = [];
foreach ($data as $key => $value) {
    $updates[] = "$key = '" . $value . "'";
}
$updateString = implode(', ', $updates);

$query = "UPDATE maintenance_request SET $updateString WHERE id = $id";

if ($conn->query($query)) {
    // Log the activity
    if (isset($_SESSION['user']['id'])) {
        $userId = $_SESSION['user']['id'];
        $activity = "Updated maintenance request #$id - Action: $action";
        $conn->query("INSERT INTO activity_log (user_id, activity) VALUES ($userId, '$activity')");
    }
    
    echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating request: ' . $conn->error]);
}
