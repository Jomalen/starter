<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No request ID provided']);
    exit;
}

$id = (int)$_GET['id'];
$mode = $_GET['mode'] ?? 'view'; // Add mode parameter

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'update') {
    $updateFields = [];
    $updateValues = [];
    
    // Fields that can be updated
    $allowedFields = [
        'request_type', 'description', 'other_details', 'serial_property_number',
        'requested_by', 'requesting_office', 'approved_by', 'received_by',
        'pre_maintenance_eval', 'inspected_by', 'corrective_action', 'result',
        'recommendation', 'accomplished_by', 'service_degree'
    ];

    foreach ($allowedFields as $field) {
        if (isset($_POST[$field])) {
            $updateFields[] = "$field = ?";
            $updateValues[] = $_POST[$field];
        }
    }

    if (!empty($updateFields)) {
        $updateValues[] = $id;
        $sql = "UPDATE maintenance_request SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param(str_repeat('s', count($updateValues)), ...$updateValues);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Error updating request']);
        exit;
    }
}

$query = "SELECT * FROM maintenance_request WHERE id = $id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

$request = $result->fetch_assoc();

// Get current status
$status = 'Pending';
$statusClass = 'warning';

if ($request['date_received']) {
    if ($request['date_accomplished']) {
        $status = 'Completed';
        $statusClass = 'success';
    } else {
        $status = 'In Progress';
        $statusClass = 'info';
    }
}

if ($request['for_disposal']) {
    $status = 'For Disposal';
    $statusClass = 'danger';
}

// Build HTML for the modal
$html = '
<div class="container-fluid">
    <!-- Status Badge -->
    <div class="text-center mb-4">
        <span class="badge bg-' . $statusClass . '-subtle text-' . $statusClass . ' px-4 py-2" style="font-size: 1rem;">
            ' . $status . '
        </span>
    </div>

    <!-- Request Information -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Request Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Request ID:</strong> #' . str_pad($request['id'], 5, '0', STR_PAD_LEFT) . '</p>
                    <p class="mb-2"><strong>Date Requested:</strong> ' . date('F d, Y', strtotime($request['date_requested'])) . '</p>
                    <p class="mb-2"><strong>Request Type:</strong> ' . htmlspecialchars($request['request_type']) . '</p>
                    <p class="mb-2"><strong>Service Degree:</strong> ' . htmlspecialchars($request['service_degree'] ?? 'N/A') . '</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Requested By:</strong> ' . htmlspecialchars($request['requested_by']) . '</p>
                    <p class="mb-2"><strong>Office:</strong> ' . htmlspecialchars($request['requesting_office']) . '</p>
                    <p class="mb-2"><strong>Approved By:</strong> ' . htmlspecialchars($request['approved_by'] ?? 'N/A') . '</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Details -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Equipment Details</h6>
        </div>
        <div class="card-body">
            <p class="mb-2"><strong>Serial/Property Number:</strong> ' . htmlspecialchars($request['serial_property_number']) . '</p>
            <p class="mb-2"><strong>Description:</strong></p>
            <div class="bg-light p-3 rounded mb-3">' . nl2br(htmlspecialchars($request['description'])) . '</div>
            ' . ($request['other_details'] ? '
            <p class="mb-2"><strong>Additional Details:</strong></p>
            <div class="bg-light p-3 rounded">' . nl2br(htmlspecialchars($request['other_details'])) . '</div>
            ' : '') . '
        </div>
    </div>

    <!-- Maintenance Information -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Maintenance Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Date Received:</strong> ' . ($request['date_received'] ? date('F d, Y', strtotime($request['date_received'])) : 'N/A') . '</p>
                    <p class="mb-2"><strong>Received By:</strong> ' . htmlspecialchars($request['received_by'] ?? 'N/A') . '</p>
                    <p class="mb-2"><strong>Inspected By:</strong> ' . htmlspecialchars($request['inspected_by'] ?? 'N/A') . '</p>
                    <p class="mb-2"><strong>Inspection Date:</strong> ' . ($request['inspection_date'] ? date('F d, Y', strtotime($request['inspection_date'])) : 'N/A') . '</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Date Accomplished:</strong> ' . ($request['date_accomplished'] ? date('F d, Y', strtotime($request['date_accomplished'])) : 'N/A') . '</p>
                    <p class="mb-2"><strong>Accomplished By:</strong> ' . htmlspecialchars($request['accomplished_by'] ?? 'N/A') . '</p>
                </div>
            </div>

            ' . ($request['pre_maintenance_eval'] ? '
            <div class="mt-3">
                <p class="mb-2"><strong>Pre-maintenance Evaluation:</strong></p>
                <div class="bg-light p-3 rounded">' . nl2br(htmlspecialchars($request['pre_maintenance_eval'])) . '</div>
            </div>
            ' : '') . '

            ' . ($request['corrective_action'] ? '
            <div class="mt-3">
                <p class="mb-2"><strong>Corrective Action:</strong></p>
                <div class="bg-light p-3 rounded">' . nl2br(htmlspecialchars($request['corrective_action'])) . '</div>
            </div>
            ' : '') . '

            ' . ($request['result'] ? '
            <div class="mt-3">
                <p class="mb-2"><strong>Result:</strong></p>
                <div class="bg-light p-3 rounded">' . nl2br(htmlspecialchars($request['result'])) . '</div>
            </div>
            ' : '') . '

            ' . ($request['recommendation'] ? '
            <div class="mt-3">
                <p class="mb-2"><strong>Recommendation:</strong></p>
                <div class="bg-light p-3 rounded">' . nl2br(htmlspecialchars($request['recommendation'])) . '</div>
            </div>
            ' : '') . '
        </div>
    </div>

    <!-- Disposal Information -->
    ' . ($request['for_disposal'] ? '
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Disposal Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Disposal Type:</strong> ' . htmlspecialchars($request['disposal_type']) . '</p>
                    <p class="mb-2"><strong>Equipment Type:</strong> ' . htmlspecialchars($request['disposal_equipment_type']) . '</p>
                    <p class="mb-2"><strong>Property Number:</strong> ' . htmlspecialchars($request['disposal_property_no']) . '</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Serial Number:</strong> ' . htmlspecialchars($request['disposal_serial_no']) . '</p>
                    <p class="mb-2"><strong>Confirmed By:</strong> ' . htmlspecialchars($request['disposal_confirmed_by']) . '</p>
                    <p class="mb-2"><strong>Accepted By:</strong> ' . htmlspecialchars($request['disposal_accepted_by']) . '</p>
                </div>
            </div>
        </div>
    </div>
    ' : '') . '
</div>';

if ($mode === 'edit') {
    $html = '
    <form id="editRequestForm" class="container-fluid">
        <!-- Request Information -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Request Information</h6>
                <span class="text-muted small">ID: #'.str_pad($request['id'], 5, '0', STR_PAD_LEFT).'</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Request Type</label>
                        <select class="form-select" name="request_type" required>
                            <option value="">Select Type</option>
                            <option value="Hardware Repair" '.($request['request_type'] === 'Hardware Repair' ? 'selected' : '').'>Hardware Repair</option>
                            <option value="Software Issue" '.($request['request_type'] === 'Software Issue' ? 'selected' : '').'>Software Issue</option>
                            <option value="Network Problem" '.($request['request_type'] === 'Network Problem' ? 'selected' : '').'>Network Problem</option>
                            <option value="Preventive Maintenance" '.($request['request_type'] === 'Preventive Maintenance' ? 'selected' : '').'>Preventive Maintenance</option>
                            <option value="Others" '.($request['request_type'] === 'Others' ? 'selected' : '').'>Others</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Requested</label>
                        <input type="date" class="form-control" name="date_requested" value="'.($request['date_requested'] ?? date('Y-m-d')).'" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Requested By</label>
                        <input type="text" class="form-control" name="requested_by" value="'.htmlspecialchars($request['requested_by']).'" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Requesting Office</label>
                        <input type="text" class="form-control" name="requesting_office" value="'.htmlspecialchars($request['requesting_office']).'" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Approved By</label>
                        <input type="text" class="form-control" name="approved_by" value="'.htmlspecialchars($request['approved_by'] ?? '').'">
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Details -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Equipment Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Serial/Property Number</label>
                        <input type="text" class="form-control" name="serial_property_number" value="'.htmlspecialchars($request['serial_property_number']).'" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required>'.htmlspecialchars($request['description']).'</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Other Details</label>
                        <textarea class="form-control" name="other_details" rows="2">'.htmlspecialchars($request['other_details']).'</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Details -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Maintenance Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Received By</label>
                        <input type="text" class="form-control" name="received_by" value="'.htmlspecialchars($request['received_by'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Received</label>
                        <input type="date" class="form-control" name="date_received" value="'.($request['date_received'] ?? '').'">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Pre-maintenance Evaluation</label>
                        <textarea class="form-control" name="pre_maintenance_eval" rows="3">'.htmlspecialchars($request['pre_maintenance_eval'] ?? '').'</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Inspected By</label>
                        <input type="text" class="form-control" name="inspected_by" value="'.htmlspecialchars($request['inspected_by'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Inspection Date</label>
                        <input type="date" class="form-control" name="inspection_date" value="'.($request['inspection_date'] ?? '').'">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Corrective Action</label>
                        <textarea class="form-control" name="corrective_action" rows="3">'.htmlspecialchars($request['corrective_action'] ?? '').'</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Result</label>
                        <textarea class="form-control" name="result" rows="2">'.htmlspecialchars($request['result'] ?? '').'</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Recommendation</label>
                        <textarea class="form-control" name="recommendation" rows="2">'.htmlspecialchars($request['recommendation'] ?? '').'</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accomplished By</label>
                        <input type="text" class="form-control" name="accomplished_by" value="'.htmlspecialchars($request['accomplished_by'] ?? '').'>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Accomplished</label>
                        <input type="date" class="form-control" name="date_accomplished" value="'.($request['date_accomplished'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service Degree</label>
                        <select class="form-select" name="service_degree">
                            <option value="">Select Degree</option>
                            <option value="Minor" '.($request['service_degree'] === 'Minor' ? 'selected' : '').'>Minor</option>
                            <option value="Major" '.($request['service_degree'] === 'Major' ? 'selected' : '').'>Major</option>
                            <option value="Critical" '.($request['service_degree'] === 'Critical' ? 'selected' : '').'>Critical</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disposal Information -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Disposal Information</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="forDisposal" name="for_disposal" '.($request['for_disposal'] ? 'checked' : '').'>
                    <label class="form-check-label" for="forDisposal">Mark for Disposal</label>
                </div>
            </div>
            <div class="card-body collapse '.($request['for_disposal'] ? 'show' : '').'" id="disposalFields">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Disposal Type</label>
                        <select class="form-select" name="disposal_type">
                            <option value="">Select Type</option>
                            <option value="Condemned" '.($request['disposal_type'] === 'Condemned' ? 'selected' : '').'>Condemned</option>
                            <option value="Donated" '.($request['disposal_type'] === 'Donated' ? 'selected' : '').'>Donated</option>
                            <option value="Sold" '.($request['disposal_type'] === 'Sold' ? 'selected' : '').'>Sold</option>
                            <option value="Recycled" '.($request['disposal_type'] === 'Recycled' ? 'selected' : '').'>Recycled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Equipment Type</label>
                        <input type="text" class="form-control" name="disposal_equipment_type" value="'.htmlspecialchars($request['disposal_equipment_type'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Property Number</label>
                        <input type="text" class="form-control" name="disposal_property_no" value="'.htmlspecialchars($request['disposal_property_no'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Serial Number</label>
                        <input type="text" class="form-control" name="disposal_serial_no" value="'.htmlspecialchars($request['disposal_serial_no'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmed By</label>
                        <input type="text" class="form-control" name="disposal_confirmed_by" value="'.htmlspecialchars($request['disposal_confirmed_by'] ?? '').'">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accepted By</label>
                        <input type="text" class="form-control" name="disposal_accepted_by" value="'.htmlspecialchars($request['disposal_accepted_by'] ?? '').'">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>';
}

echo json_encode(['success' => true, 'html' => $html, 'mode' => $mode]);