<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    die('No request ID provided');
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM maintenance_request WHERE id = $id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die('Request not found');
}

$request = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request #<?= str_pad($request['id'], 5, '0', STR_PAD_LEFT) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1.5cm;
            }
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hospital-logo {
            max-width: 100px;
            height: auto;
            margin-bottom: 1rem;
        }
        .request-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
        }
        .request-header h4 {
            color: #2e7d32;
            margin: 0.5rem 0;
            font-weight: 600;
        }
        .request-header h5 {
            color: #558b2f;
            margin-bottom: 0;
        }
        .section-title {
            color: #2e7d32;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #a5d6a7;
        }
        .info-row {
            margin-bottom: 0.5rem;
        }
        .info-label {
            font-weight: 600;
            color: #558b2f;
        }
        .signature-section {
            margin-top: 3rem;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 3rem auto 0.5rem;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 2rem;
        }
        .print-button {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button class="btn btn-primary print-button no-print" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print Request
    </button>

    <div class="container my-5">
        <div class="card">
            <!-- Header -->
            <div class="request-header">
                <img src="../../assets/img/DJNRMHS.png" alt="Hospital Logo" class="hospital-logo">
                <h4>Dr. Jose N. Rodriguez Memorial Hospital</h4>
                <h5>Equipment Maintenance Request Form</h5>
                <div class="mt-3">
                    <span class="status-badge bg-<?php
                        if ($request['date_accomplished']) echo 'success-subtle text-success';
                        elseif ($request['date_received']) echo 'info-subtle text-info';
                        elseif ($request['for_disposal']) echo 'danger-subtle text-danger';
                        else echo 'warning-subtle text-warning';
                    ?>">
                        <?php
                        if ($request['date_accomplished']) echo 'Completed';
                        elseif ($request['date_received']) echo 'In Progress';
                        elseif ($request['for_disposal']) echo 'For Disposal';
                        else echo 'Pending';
                        ?>
                    </span>
                </div>
            </div>

            <!-- Request Information -->
            <div class="mb-4">
                <h6 class="section-title">Request Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Request ID:</span>
                            <span>#<?= str_pad($request['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date Requested:</span>
                            <span><?= date('F d, Y', strtotime($request['date_requested'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Request Type:</span>
                            <span><?= htmlspecialchars($request['request_type']) ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Requested By:</span>
                            <span><?= htmlspecialchars($request['requested_by']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Office:</span>
                            <span><?= htmlspecialchars($request['requesting_office']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Details -->
            <div class="mb-4">
                <h6 class="section-title">Equipment Details</h6>
                <div class="info-row">
                    <span class="info-label">Serial/Property Number:</span>
                    <span><?= htmlspecialchars($request['serial_property_number']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Description:</span>
                    <span><?= nl2br(htmlspecialchars($request['description'])) ?></span>
                </div>
            </div>

            <?php if ($request['date_received']): ?>
            <!-- Maintenance Information -->
            <div class="mb-4">
                <h6 class="section-title">Maintenance Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Date Received:</span>
                            <span><?= date('F d, Y', strtotime($request['date_received'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Inspected By:</span>
                            <span><?= htmlspecialchars($request['inspected_by']) ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Inspection Date:</span>
                            <span><?= $request['inspection_date'] ? date('F d, Y', strtotime($request['inspection_date'])) : 'N/A' ?></span>
                        </div>
                    </div>
                </div>
                <div class="info-row mt-3">
                    <span class="info-label">Pre-maintenance Evaluation:</span>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($request['pre_maintenance_eval'])) ?></p>
                </div>
            </div>

            <?php if ($request['date_accomplished'] || $request['corrective_action']): ?>
            <!-- Action Taken -->
            <div class="mb-4">
                <h6 class="section-title">Action Taken</h6>
                <div class="info-row">
                    <span class="info-label">Corrective Action:</span>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($request['corrective_action'] ?? 'N/A')) ?></p>
                </div>
                <div class="info-row mt-3">
                    <span class="info-label">Result:</span>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($request['result'] ?? 'N/A')) ?></p>
                </div>
                <div class="info-row mt-3">
                    <span class="info-label">Recommendation:</span>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($request['recommendation'] ?? 'N/A')) ?></p>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Accomplished By:</span>
                            <span><?= htmlspecialchars($request['accomplished_by'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Date Accomplished:</span>
                            <span><?= $request['date_accomplished'] ? date('F d, Y', strtotime($request['date_accomplished'])) : 'N/A' ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($request['for_disposal']): ?>
            <!-- Disposal Information -->
            <div class="mb-4">
                <h6 class="section-title">Disposal Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Disposal Type:</span>
                            <span><?= htmlspecialchars($request['disposal_type'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Equipment Type:</span>
                            <span><?= htmlspecialchars($request['disposal_equipment_type'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Property Number:</span>
                            <span><?= htmlspecialchars($request['disposal_property_no'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Serial Number:</span>
                            <span><?= htmlspecialchars($request['disposal_serial_no'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Signatures -->
            <div class="signature-section row">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="signature-line"></div>
                        <div class="info-label">Requested by</div>
                        <div><?= htmlspecialchars($request['requested_by']) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="signature-line"></div>
                        <div class="info-label">Inspected by</div>
                        <div><?= htmlspecialchars($request['inspected_by'] ?? '') ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="signature-line"></div>
                        <div class="info-label">Approved by</div>
                        <div><?= htmlspecialchars($request['approved_by'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when the page loads
        window.onload = function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
