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

// Get the current action
$action = $_GET['action'] ?? 'list';

// Fetch users for the list view
$users = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle role filtering
$roleFilter = $_GET['role'] ?? '';
if ($roleFilter) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
    $stmt->execute([$roleFilter]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - IHOMS</title>
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
                            <h2 class="mb-1">User Management</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Users</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus me-2"></i>Add New User
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-2"></i>Filter Role
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item <?= $roleFilter === '' ? 'active' : '' ?>" href="?action=list">All Users</a></li>
                                    <li><a class="dropdown-item <?= $roleFilter === 'admin' ? 'active' : '' ?>" href="?action=list&role=admin">Administrators</a></li>
                                    <li><a class="dropdown-item <?= $roleFilter === 'user' ? 'active' : '' ?>" href="?action=list&role=user">Regular Users</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Role</th>
                                            <th>Office</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($user['profile_pic'] && file_exists("../" . $user['profile_pic'])): ?>
                                                            <img src="../<?= htmlspecialchars($user['profile_pic']) ?>" class="rounded-circle me-2" width="40" height="40" alt="">
                                                        <?php else: ?>
                                                            <div class="profile-initial rounded-circle me-2" style="width: 40px; height: 40px;">
                                                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($user['full_name']) ?></div>
                                                            <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($user['office']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewUser(<?= $user['id'] ?>)" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-info" onclick="editUser(<?= $user['id'] ?>)" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>)" title="Delete User">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" action="../php/users/save_user.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password">
                            <small class="text-muted">Leave blank to generate a default password</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="user">Regular User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Office</label>
                            <input type="text" class="form-control" name="office">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitUserForm()">Add User</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Display Modal -->
    <div class="modal fade" id="passwordDisplayModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Created Successfully</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle me-2"></i>New user has been created successfully!
                    </div>
                    <div id="defaultPasswordSection" class="d-none">
                        <div class="alert alert-info">
                            <p class="mb-2"><i class="fas fa-info-circle me-2"></i>Default password has been generated:</p>
                            <div class="input-group">
                                <input type="text" id="defaultPasswordInput" class="form-control" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <small class="d-block mt-2">Make sure to share this password securely with the user.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" name="id" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="editFullName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="editPhone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Office</label>
                            <input type="text" class="form-control" name="office" id="editOffice">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="editRole" required>
                                <option value="user">Regular User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="editIsActive">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateUser()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <script>
    function viewUser(userId) {
        // Load user details via AJAX and show in modal
        fetch(`../php/users/get_user.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                const modal = document.querySelector('#viewUserModal .modal-body');
                modal.innerHTML = `
                    <div class="text-center mb-4">
                        ${user.profile_pic ? 
                            `<img src="../${user.profile_pic}" class="rounded-circle" width="100" height="100" alt="">` :
                            `<div class="profile-initial rounded-circle mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                ${user.username.charAt(0).toUpperCase()}
                            </div>`
                        }
                        <h4 class="mt-3 mb-1">${user.full_name}</h4>
                        <p class="text-muted">${user.role}</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Username</label>
                            <p>${user.username}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <p>${user.email}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Phone</label>
                            <p>${user.phone || 'Not set'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Office</label>
                            <p>${user.office || 'Not set'}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Status</label>
                            <p><span class="badge bg-${user.is_active ? 'success' : 'danger'}">
                                ${user.is_active ? 'Active' : 'Inactive'}
                            </span></p>
                        </div>
                    </div>
                `;
                new bootstrap.Modal(document.getElementById('viewUserModal')).show();
            });
    }

    function editUser(userId) {
        // Load user details via AJAX and show in modal
        fetch(`../php/users/get_user.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                // Fill form fields with user data
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editFullName').value = user.full_name;
                document.getElementById('editEmail').value = user.email;
                document.getElementById('editPhone').value = user.phone || '';
                document.getElementById('editOffice').value = user.office || '';
                document.getElementById('editRole').value = user.role;
                document.getElementById('editIsActive').checked = user.is_active == 1;

                // Show edit modal
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            });
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`../php/users/delete_user.php?id=${userId}`, { method: 'POST' })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        location.reload();
                    } else {
                        alert('Error deleting user: ' + result.message);
                    }
                });
        }
    }

    function submitUserForm() {
        const form = document.getElementById('addUserForm');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide add user modal
                bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                
                // If default password was generated, show it
                if (data.default_password) {
                    document.getElementById('defaultPasswordSection').classList.remove('d-none');
                    document.getElementById('defaultPasswordInput').value = data.default_password;
                } else {
                    document.getElementById('defaultPasswordSection').classList.add('d-none');
                }

                // Show success modal
                const successModal = new bootstrap.Modal(document.getElementById('passwordDisplayModal'));
                successModal.show();

                // Refresh the page after modal is closed
                document.getElementById('passwordDisplayModal').addEventListener('hidden.bs.modal', function () {
                    location.reload();
                });
            } else {
                alert(data.message || 'Error creating user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating user');
        });
    }

    function updateUser() {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        const userId = formData.get('id');

        // Add is_active as boolean
        formData.set('is_active', document.getElementById('editIsActive').checked);

        fetch(`../php/users/edit_user.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide edit modal
                bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                // Show success message and refresh
                alert('User updated successfully');
                location.reload();
            } else {
                alert(data.message || 'Error updating user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating user');
        });
    }

    function copyPassword() {
        const passwordInput = document.getElementById('defaultPasswordInput');
        passwordInput.select();
        document.execCommand('copy');
        
        // Show copy feedback
        const button = event.currentTarget;
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 2000);
    }
    </script>
</body>
</html>
