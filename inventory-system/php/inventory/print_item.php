<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    die('No item ID provided');
}

$id = (int)$_GET['id'];

// Get item details
$query = "SELECT * FROM items WHERE id = $id";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die('Item not found');
}

$item = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Item Details - <?= htmlspecialchars($item['model']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .print-header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .item-details {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        .detail-row {
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 40px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <!-- Print Header -->
        <div class="print-header">
            <img src="../../assets/img/DJNRMHS.png" alt="Hospital Logo">
            <h4>Dr. Jose N. Rodriguez Memorial Hospital</h4>
            <h5>Inventory Management System</h5>
            <h6>Item Details Report</h6>
        </div>

        <!-- Item Details -->
        <div class="item-details">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Model</div>
                        <div><?= htmlspecialchars($item['model']) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Serial Number</div>
                        <div><?= htmlspecialchars($item['serial_number']) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Property Number</div>
                        <div><?= htmlspecialchars($item['property_number']) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Operating System</div>
                        <div><?= htmlspecialchars($item['operating_system'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Brand</div>
                        <div><?= htmlspecialchars($item['brand'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Memory</div>
                        <div><?= htmlspecialchars($item['memory'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">End User</div>
                        <div><?= htmlspecialchars($item['end_user'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="detail-label">Location</div>
                        <div><?= htmlspecialchars($item['location'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="detail-row">
                        <div class="detail-label">Category</div>
                        <div><?= htmlspecialchars($item['property_category'] ?? 'N/A') ?></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="detail-row">
                        <div class="detail-label">Description</div>
                        <div><?= htmlspecialchars($item['description'] ?? 'N/A') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section row">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="signature-line"></div>
                    <div>Prepared by</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="signature-line"></div>
                    <div>Checked by</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="signature-line"></div>
                    <div>Approved by</div>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-4 no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print
            </button>
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