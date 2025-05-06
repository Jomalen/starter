<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Get request type filter if set
$requestType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterClause = $requestType !== 'all' ? "WHERE request_type = '" . $conn->real_escape_string($requestType) . "'" : "";

// Fetch maintenance requests
$query = "SELECT * FROM maintenance_request $filterClause ORDER BY date_requested DESC";
$result = $conn->query($query);

// Get unique request types for filter
$typesQuery = "SELECT DISTINCT request_type FROM maintenance_request WHERE request_type IS NOT NULL";
$typesResult = $conn->query($typesQuery);
$requestTypes = [];
while ($type = $typesResult->fetch_assoc()) {
    if ($type['request_type']) {
        $requestTypes[] = $type['request_type'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests - Admin Dashboard</title>
    <!-- Latest Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Latest Font Awesome 6.5.1 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div class="main-content">
        <div class="container-fluid p-4">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col">
                    <h2 class="mb-1">Maintenance Requests</h2>
                    <p class="text-muted mb-0">Manage maintenance requests and track their status</p>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                        <i class="fas fa-plus me-2"></i>New Request
                    </button>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="dashboard-card mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-0" id="searchInput" placeholder="Search requests...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select border-0" id="typeFilter" onchange="filterRequests(this.value)">
                            <option value="all" <?= $requestType === 'all' ? 'selected' : '' ?>>All Request Types</option>
                            <?php foreach ($requestTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= $requestType === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-primary w-100" onclick="exportToExcel()">
                            <i class="fas fa-download me-2"></i>Export to Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Equipment Info</th>
                                <th>Requester Details</th>
                                <th>Maintenance Info</th>
                                <th>Disposal Status</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($request = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= str_pad($request['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">Requested:</small>
                                                <span><?= date('M d, Y', strtotime($request['date_requested'])) ?></span>
                                                <?php if ($request['date_received']): ?>
                                                    <small class="text-muted mt-1">Received:</small>
                                                    <span><?= date('M d, Y', strtotime($request['date_received'])) ?></span>
                                                <?php endif; ?>
                                                <?php if ($request['date_accomplished']): ?>
                                                    <small class="text-muted mt-1">Completed:</small>
                                                    <span><?= date('M d, Y', strtotime($request['date_accomplished'])) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <?= htmlspecialchars($request['request_type']) ?>
                                            </span>
                                            <?php if ($request['service_degree']): ?>
                                                <br>
                                                <small class="text-muted mt-1">Service Degree:</small>
                                                <span class="badge bg-info-subtle text-info">
                                                    <?= htmlspecialchars($request['service_degree']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="product-icon bg-light text-primary me-2">
                                                    <i class="fas fa-desktop"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($request['serial_property_number']) ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($request['description']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold"><?= htmlspecialchars($request['requested_by']) ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($request['requesting_office']) ?></small>
                                                <?php if ($request['received_by']): ?>
                                                    <small class="text-muted mt-1">Received by:</small>
                                                    <span><?= htmlspecialchars($request['received_by']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($request['pre_maintenance_eval']): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted">Evaluation:</small>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($request['pre_maintenance_eval']) ?>">
                                                        <?= htmlspecialchars($request['pre_maintenance_eval']) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($request['inspected_by']): ?>
                                                <div>
                                                    <small class="text-muted">Inspected by:</small>
                                                    <div><?= htmlspecialchars($request['inspected_by']) ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($request['corrective_action']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">Action Taken:</small>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($request['corrective_action']) ?>">
                                                        <?= htmlspecialchars($request['corrective_action']) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($request['for_disposal']): ?>
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-danger-subtle text-danger mb-1">
                                                        For Disposal - <?= htmlspecialchars($request['disposal_type'] ?? 'Not Specified') ?>
                                                    </span>
                                                    <?php if ($request['disposal_confirmed_by']): ?>
                                                        <small class="text-muted">Confirmed by:</small>
                                                        <span><?= htmlspecialchars($request['disposal_confirmed_by']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($request['disposal_accepted_by']): ?>
                                                        <small class="text-muted mt-1">Accepted by:</small>
                                                        <span><?= htmlspecialchars($request['disposal_accepted_by']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Not for disposal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
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
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>">
                                                <?= $status ?>
                                            </span>
                                            <?php if ($request['result']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">Result:</small>
                                                    <div class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($request['result']) ?>">
                                                        <?= htmlspecialchars($request['result']) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?= $request['id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <!-- Replace single button with dropdown -->
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-tasks"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <?php if ($status === 'Pending'): ?>
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $request['id'] ?>, 'receive')">
                                                                <i class="fas fa-clock text-info me-2"></i>Mark In Progress
                                                            </a></li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($status === 'In Progress'): ?>
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $request['id'] ?>, 'complete')">
                                                                <i class="fas fa-check text-success me-2"></i>Mark as Completed
                                                            </a></li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($status === 'Completed'): ?>
                                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $request['id'] ?>, 'reopen')">
                                                                <i class="fas fa-redo text-warning me-2"></i>Reopen Request
                                                            </a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>

                                                <button class="btn btn-sm btn-outline-info" onclick="printRequest(<?= $request['id'] ?>)" title="Print Details">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?= $request['id'] ?>, 'edit')" title="Edit Request">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-clipboard-list fa-2x mb-3"></i>
                                            <p class="mb-0">No maintenance requests found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Request Modal -->
    <div class="modal fade" id="addRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Maintenance Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="requestForm">
                        <!-- Request Information -->
                        <div class="mb-3">
                            <label class="form-label">Request Type</label>
                            <select class="form-select" name="request_type" required>
                                <option value="">Select Type</option>
                                <option value="Hardware Repair">Hardware Repair</option>
                                <option value="Software Issue">Software Issue</option>
                                <option value="Network Problem">Network Problem</option>
                                <option value="Preventive Maintenance">Preventive Maintenance</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>

                        <!-- Equipment Details -->
                        <div class="mb-3">
                            <label class="form-label">Equipment Serial/Property Number</label>
                            <input type="text" class="form-control" name="serial_property_number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Details</label>
                            <textarea class="form-control" name="other_details" rows="2"></textarea>
                        </div>

                        <!-- Requester Information -->
                        <div class="mb-3">
                            <label class="form-label">Requested By</label>
                            <input type="text" class="form-control" name="requested_by" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requesting Office</label>
                            <input type="text" class="form-control" name="requesting_office" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitRequest()">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Request Modal -->
    <div class="modal fade" id="viewRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="requestDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Request Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="statusModalBody">
                    <!-- Form will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStatus">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <script src="../assets/js/requests.js"></script>
</body>
</html>
