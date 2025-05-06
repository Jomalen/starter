<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

if (!$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get current action and page
$action = $_GET['action'] ?? 'list';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Handle category filtering
$categoryFilter = $_GET['category'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Build the base queries
$query = "SELECT * FROM items WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM items WHERE 1=1";
$params = [];

if ($categoryFilter) {
    $query .= " AND property_category = :category";
    $countQuery .= " AND property_category = :category";
    $params[':category'] = $categoryFilter;
}

if ($searchTerm) {
    $query .= " AND (model LIKE :search OR serial_number LIKE :search OR property_number LIKE :search OR brand LIKE :search OR end_user LIKE :search)";
    $countQuery .= " AND (model LIKE :search OR serial_number LIKE :search OR property_number LIKE :search OR brand LIKE :search OR end_user LIKE :search)";
    $params[':search'] = "%$searchTerm%";
}

// Get total records and pages
$stmt = $pdo->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Add sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

// Prepare and execute the final query
$stmt = $pdo->prepare($query);

// Bind the existing parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind LIMIT and OFFSET with explicit types
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter
$categories = $pdo->query("SELECT DISTINCT property_category FROM items WHERE property_category IS NOT NULL ORDER BY property_category")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - IHOMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Include Topbar -->
        <?php include '../includes/header.php'; ?>

        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Inventory Management</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Inventory</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                <i class="fas fa-plus me-2"></i>Add New Item
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-2"></i>Filter Category
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item <?= $categoryFilter === '' ? 'active' : '' ?>" href="?action=list">All Categories</a></li>
                                    <?php foreach ($categories as $category): ?>
                                        <li><a class="dropdown-item <?= $categoryFilter === $category ? 'active' : '' ?>" 
                                           href="?action=list&category=<?= urlencode($category) ?>">
                                           <?= htmlspecialchars($category) ?>
                                        </a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <form action="" method="GET" class="d-flex gap-2">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search items..." 
                                   value="<?= htmlspecialchars($searchTerm) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <?php if ($searchTerm || $categoryFilter): ?>
                            <a href="?action=list" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ITEM</th>
                                    <th>SERIAL NO.</th>
                                    <th>PROPERTY NO.</th>
                                    <th>BRAND</th>
                                    <th>CATEGORY</th>
                                    <th>LOCATION</th>
                                    <th>END USER</th>
                                    <th>DESCRIPTION</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($items) > 0): ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr data-item-id="<?= $item['id'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="product-icon bg-primary-subtle text-primary me-2">
                                                        <i class="fas fa-desktop"></i>
                                                    </div>
                                                    <div>
                                                        <a href="#" class="text-decoration-none" onclick="viewItem(<?= $item['id'] ?>); return false;">
                                                            <div class="fw-bold text-primary"><?= htmlspecialchars($item['model']) ?></div>
                                                        </a>
                                                        <div class="text-muted small">
                                                            <?= htmlspecialchars($item['operating_system'] ?? 'N/A') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($item['serial_number']) ?></td>
                                            <td><?= htmlspecialchars($item['property_number']) ?></td>
                                            <td><?= htmlspecialchars($item['brand']) ?></td>
                                            <td><?= htmlspecialchars($item['property_category']) ?></td>
                                            <td><?= htmlspecialchars($item['location']) ?></td>
                                            <td><?= htmlspecialchars($item['end_user']) ?></td>
                                            <td>
                                                <div class="description-cell" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?= htmlspecialchars($item['description'] ?? 'N/A') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewItem(<?= $item['id'] ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="editItem(<?= $item['id'] ?>)" title="Edit Item">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="printItem(<?= $item['id'] ?>)" title="Print Details">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>)" title="Delete Item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <p class="mb-0">No items found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page-1 ?><?= $categoryFilter ? '&category='.urlencode($categoryFilter) : '' ?><?= $searchTerm ? '&search='.urlencode($searchTerm) : '' ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= $categoryFilter ? '&category='.urlencode($categoryFilter) : '' ?><?= $searchTerm ? '&search='.urlencode($searchTerm) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page+1 ?><?= $categoryFilter ? '&category='.urlencode($categoryFilter) : '' ?><?= $searchTerm ? '&search='.urlencode($searchTerm) : '' ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Item Modal -->
            <div class="modal fade" id="addItemModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addItemForm" action="../php/inventory/add_item.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Model</label>
                                    <input type="text" class="form-control" name="model" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Serial Number</label>
                                    <input type="text" class="form-control" name="serial_number" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Property Number</label>
                                    <input type="text" class="form-control" name="property_number" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Operating System</label>
                                    <input type="text" class="form-control" name="operating_system">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <input type="text" class="form-control" name="brand">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Memory</label>
                                    <input type="text" class="form-control" name="memory">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Enter item description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End User</label>
                                    <input type="text" class="form-control" name="end_user">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="property_category">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category) ?>">
                                                <?= htmlspecialchars($category) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="submitItemForm()">Add Item</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Item Modal -->
            <div class="modal fade" id="viewItemModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Item Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Content will be loaded dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Item Modal -->
            <div class="modal fade" id="editItemModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editItemForm">
                                <input type="hidden" name="id" id="editItemId">
                                <div class="mb-3">
                                    <label class="form-label">Model</label>
                                    <input type="text" class="form-control" name="model" id="editModel" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Serial Number</label>
                                    <input type="text" class="form-control" name="serial_number" id="editSerialNumber" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Property Number</label>
                                    <input type="text" class="form-control" name="property_number" id="editPropertyNumber" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Operating System</label>
                                    <input type="text" class="form-control" name="operating_system" id="editOperatingSystem">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <input type="text" class="form-control" name="brand" id="editBrand">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Memory</label>
                                    <input type="text" class="form-control" name="memory" id="editMemory">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="editDescription" rows="3" placeholder="Enter item description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End User</label>
                                    <input type="text" class="form-control" name="end_user" id="editEndUser">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location" id="editLocation">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="property_category" id="editCategory">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category) ?>">
                                                <?= htmlspecialchars($category) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="updateItem()">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <script>
    function viewItem(itemId) {
        // Load item details and show in modal
        fetch(`../php/inventory/get_item.php?id=${itemId}`)
            .then(response => response.json())
            .then(item => {
                const modal = document.querySelector('#viewItemModal .modal-body');
                modal.innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Model</label>
                            <p class="mb-0">${item.model}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Serial Number</label>
                            <p class="mb-0">${item.serial_number}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Property Number</label>
                            <p class="mb-0">${item.property_number}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Operating System</label>
                            <p class="mb-0">${item.operating_system || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Brand</label>
                            <p class="mb-0">${item.brand || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Memory</label>
                            <p class="mb-0">${item.memory || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">End User</label>
                            <p class="mb-0">${item.end_user || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <p class="mb-0">${item.location || 'N/A'}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Category</label>
                            <p class="mb-0">${item.property_category || 'N/A'}</p>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="text-muted small">Description</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDescriptionEdit(this, ${item.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                            <div id="descriptionView" class="mb-0">${item.description || 'N/A'}</div>
                            <div id="descriptionEdit" class="d-none">
                                <textarea class="form-control mb-2" rows="3">${item.description || ''}</textarea>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-success" onclick="saveDescription(${item.id})">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="cancelDescriptionEdit()">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Initialize Bootstrap modal
                new bootstrap.Modal(document.getElementById('viewItemModal')).show();
            });
    }

    // Toggle description edit mode
    function toggleDescriptionEdit(button, itemId) {
        const viewDiv = document.getElementById('descriptionView');
        const editDiv = document.getElementById('descriptionEdit');
        
        viewDiv.classList.add('d-none');
        editDiv.classList.remove('d-none');
        button.classList.add('d-none');
    }

    // Cancel description edit
    function cancelDescriptionEdit() {
        const viewDiv = document.getElementById('descriptionView');
        const editDiv = document.getElementById('descriptionEdit');
        const editButton = document.querySelector('#viewItemModal button[onclick^="toggleDescriptionEdit"]');
        
        viewDiv.classList.remove('d-none');
        editDiv.classList.add('d-none');
        editButton.classList.remove('d-none');
    }

    // Save description changes
    function saveDescription(itemId) {
        const description = document.querySelector('#descriptionEdit textarea').value;
        const formData = new FormData();
        formData.append('id', itemId);
        formData.append('description', description);

        fetch('../php/inventory/edit_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the view
                document.getElementById('descriptionView').textContent = description || 'N/A';
                // Update the preview in the table
                const previewElement = document.querySelector(`tr[data-item-id="${itemId}"] .description-preview`);
                if (previewElement) {
                    previewElement.textContent = description.length > 50 ? description.substring(0, 50) + '...' : description;
                }
                cancelDescriptionEdit();
            } else {
                alert(data.message || 'Error updating description');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating description');
        });
    }

    function editItem(itemId) {
        // Load item details and show in edit modal
        fetch(`../php/inventory/get_item.php?id=${itemId}`)
            .then(response => response.json())
            .then(item => {
                document.getElementById('editItemId').value = item.id;
                document.getElementById('editModel').value = item.model;
                document.getElementById('editSerialNumber').value = item.serial_number;
                document.getElementById('editPropertyNumber').value = item.property_number;
                document.getElementById('editOperatingSystem').value = item.operating_system || '';
                document.getElementById('editBrand').value = item.brand || '';
                document.getElementById('editMemory').value = item.memory || '';
                document.getElementById('editEndUser').value = item.end_user || '';
                document.getElementById('editLocation').value = item.location || '';
                document.getElementById('editCategory').value = item.property_category || '';
                document.getElementById('editDescription').value = item.description || '';

                new bootstrap.Modal(document.getElementById('editItemModal')).show();
            });
    }

    function deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch(`../php/inventory/delete_item.php?id=${itemId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Error deleting item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting item');
            });
        }
    }

    function submitItemForm() {
        const form = document.getElementById('addItemForm');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error adding item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding item');
        });
    }

    function updateItem() {
        const form = document.getElementById('editItemForm');
        const formData = new FormData(form);

        fetch('../php/inventory/edit_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error updating item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating item');
        });
    }

    function printItem(itemId) {
        // Open print view in a new window
        const printWindow = window.open(`../php/inventory/print_item.php?id=${itemId}`, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }
    </script>
</body>
</html>
