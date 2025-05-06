<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check authentication
$auth = new Auth();
$auth->checkAuth();

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Validate required fields
    if (!isset($_POST['id']) || !isset($_POST['action'])) {
        throw new Exception('Missing required parameters');
    }

    $id = (int)$_POST['id'];
    $action = $conn->real_escape_string($_POST['action']);
    $currentDate = date('Y-m-d');
    $updateFields = [];
    $updateValues = [];

    // Handle different actions
    switch ($action) {
        case 'receive':
            // Validate receive fields
            $requiredFields = ['pre_maintenance_eval', 'inspected_by', 'inspection_date', 'received_by'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field is required");
                }
                $updateFields[] = "$field = ?";
                $updateValues[] = $_POST[$field];
            }
            $updateFields[] = "date_received = ?";
            $updateValues[] = $currentDate;
            break;

        case 'complete':
            // Validate complete fields
            $requiredFields = ['corrective_action', 'result', 'recommendation', 'accomplished_by', 'service_degree'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field is required");
                }
                $updateFields[] = "$field = ?";
                $updateValues[] = $_POST[$field];
            }
            $updateFields[] = "date_accomplished = ?";
            $updateValues[] = $currentDate;
            break;

        case 'dispose':
            // Validate disposal fields
            $requiredFields = ['disposal_type', 'disposal_equipment_type', 'disposal_property_no', 'disposal_serial_no'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field is required");
                }
                $updateFields[] = "$field = ?";
                $updateValues[] = $_POST[$field];
            }
            $updateFields[] = "for_disposal = 1";
            $updateFields[] = "date_accomplished = ?";
            $updateValues[] = $currentDate;
            break;

        default:
            throw new Exception('Invalid action');
    }

    // Optional fields
    $optionalFields = ['description', 'other_details', 'disposal_confirmed_by', 'disposal_accepted_by'];
    foreach ($optionalFields as $field) {
        if (!empty($_POST[$field])) {
            $updateFields[] = "$field = ?";
            $updateValues[] = $_POST[$field];
        }
    }

    // Add ID to values array
    $updateValues[] = $id;

    // Prepare and execute update query
    $sql = "UPDATE maintenance_request SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $types = str_repeat('s', count($updateValues));
        $stmt->bind_param($types, ...$updateValues);
        
        if ($stmt->execute()) {
            // Log the activity
            $activity = "Updated maintenance request #$id - Action: $action";
            $userId = $_SESSION['user']['id'] ?? 0;
            $conn->query("INSERT INTO activity_log (user_id, activity) VALUES ($userId, '$activity')");

            $response['success'] = true;
            $response['message'] = 'Request updated successfully';
        } else {
            throw new Exception('Error executing update query');
        }
        $stmt->close();
    } else {
        throw new Exception('Error preparing update query');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
