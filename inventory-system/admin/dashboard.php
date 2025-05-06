<?php
// Include required files
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user']['username'];
} else {
    header('Location: ../../login.php');
    exit;
}

if (!$auth->isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Fetch user details
$profilePic = $_SESSION['user']['profile_pic'] ?? 'assets/img/default-avatar.jpg';
$profileInitial = strtoupper($user[0]);

// Fetch statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM items) as total_items,
    (SELECT COUNT(*) FROM maintenance_request WHERE date_accomplished IS NULL AND for_disposal = FALSE) as pending_maintenance,
    (SELECT COUNT(*) FROM maintenance_request WHERE for_disposal = TRUE) as for_disposal,
    (SELECT COUNT(*) FROM users WHERE role = 'user' AND is_active = TRUE) as active_users";

$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Calculate percentage changes (comparing to last month)
$lastMonthQuery = "SELECT 
    (SELECT COUNT(*) FROM items WHERE MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) as last_month_items,
    (SELECT COUNT(*) FROM maintenance_request WHERE date_accomplished IS NULL AND for_disposal = FALSE 
     AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) as last_month_maintenance,
    (SELECT COUNT(*) FROM maintenance_request WHERE for_disposal = TRUE 
     AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) as last_month_disposal,
    (SELECT COUNT(*) FROM users WHERE role = 'user' AND is_active = TRUE 
     AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) as last_month_users";

$lastMonthResult = $conn->query($lastMonthQuery);
$lastMonth = $lastMonthResult->fetch_assoc();

// Calculate percentage changes
function calculatePercentageChange($current, $previous) {
    if ($previous == 0) return 100;
    return round((($current - $previous) / $previous) * 100);
}

$itemsChange = calculatePercentageChange($stats['total_items'], $lastMonth['last_month_items']);
$maintenanceChange = calculatePercentageChange($stats['pending_maintenance'], $lastMonth['last_month_maintenance']);
$disposalChange = calculatePercentageChange($stats['for_disposal'], $lastMonth['last_month_disposal']);
$usersChange = calculatePercentageChange($stats['active_users'], $lastMonth['last_month_users']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Latest Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Latest Font Awesome 6.5.1 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(112, 190, 118);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #c8e6c9;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        }

        .stats-card .stats-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-title {
            color: #2e7d32;
            font-size: 1rem;
            font-weight: 600;
        }

        .stats-number {
            color: #1b5e20;
            font-size: 1.75rem;
            font-weight: bold;
        }

        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #c8e6c9;
            color: #2e7d32;
            font-weight: 600;
            border-bottom: 2px solid #a5d6a7;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e8f5e9;
        }

        .product-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .btn-primary {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }

        .btn-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }

        .btn-outline-primary {
            color: #2e7d32;
            border-color: #2e7d32;
        }

        .btn-outline-primary:hover {
            background-color: #2e7d32;
            color: white;
        }

        .badge.bg-success-subtle {
            background-color: #d7f5dd !important;
            color: #1b5e20 !important;
        }

        .badge.bg-danger-subtle {
            background-color: #ffe7e6 !important;
            color: #d32f2f !important;
        }

        .main-content {
            background-color: #e8f5e9;
        }

        .quick-actions-top .btn {
            padding: 0.5rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 8px;
        }

        .input-group {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .input-group .form-control {
            border: 1px solidrgb(79, 164, 81);
            padding: 0.5rem 1rem;
        }

        .input-group .form-control:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 0.2rem rgba(129, 188, 132, 0.25);
        }

        .btn-outline-secondary {
            color: #2e7d32;
            border-color: #c8e6c9;
        }

        .btn-outline-secondary:hover {
            background-color: #e8f5e9;
            color: #1b5e20;
            border-color: #2e7d32;
        }

        /* Profile Modal Styling */
        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background-color: #e8f5e9;
            border-bottom: 1px solid #c8e6c9;
        }

        .modal-title {
            color: #2e7d32;
            font-weight: 600;
        }

        .profile-info-item label {
            color: #558b2f;
            font-weight: 500;
        }

        .profile-info-item p {
            color: #2e7d32;
        }

        .nav-link:hover {
            background-color: #a5d6a7 !important;
            color: #1b5e20 !important;
        }
        .nav-link.active {
            background-color: #81c784 !important;
            color: #1b5e20 !important;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" style="background-color: #e8f5e9; min-height: 100vh; width: 250px; position: fixed; left: 0; top: 0; padding: 0; box-shadow: 2px 0 5px rgba(0,0,0,0.1); z-index: 1001;">
        <div class="sidebar-header text-center py-4" style="background-color: #c8e6c9; border-bottom: 1px solid #a5d6a7;">
            <img src="../assets/img/DJNRMHS.png" alt="Hospital Logo" class="img-fluid mb-3" style="max-width: 120px;">
            <h5 style="color: #2e7d32; margin: 0; font-weight: 600;">Inventory System</h5>
        </div>
        
        <nav class="mt-3 px-3">
            <div class="nav-section mb-2">
                <small style="color: #558b2f; text-transform: uppercase; font-weight: 600; padding-left: 1rem;">Main Menu</small>
            </div>
            
            <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>"
               style="display: flex; align-items: center; padding: 0.75rem 1rem; color: #2e7d32; text-decoration: none; 
                      margin-bottom: 0.5rem; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fas fa-home me-3" style="width: 20px;"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="inventory.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'inventory.php' ? 'active' : '' ?>"
               style="display: flex; align-items: center; padding: 0.75rem 1rem; color: #2e7d32; text-decoration: none; 
                      margin-bottom: 0.5rem; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fas fa-boxes me-3" style="width: 20px;"></i>
                <span>Inventory</span>
            </a>
            
            <a href="requests.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'requests.php' ? 'active' : '' ?>"
               style="display: flex; align-items: center; padding: 0.75rem 1rem; color: #2e7d32; text-decoration: none; 
                      margin-bottom: 0.5rem; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fas fa-tasks me-3" style="width: 20px;"></i>
                <span>Requests</span>
            </a>
            
            <a href="reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>"
               style="display: flex; align-items: center; padding: 0.75rem 1rem; color: #2e7d32; text-decoration: none; 
                      margin-bottom: 0.5rem; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fas fa-chart-bar me-3" style="width: 20px;"></i>
                <span>Reports</span>
            </a>
            
            <a href="users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>"
               style="display: flex; align-items: center; padding: 0.75rem 1rem; color: #2e7d32; text-decoration: none; 
                      margin-bottom: 0.5rem; border-radius: 8px; transition: all 0.3s ease;">
                <i class="fas fa-users me-3" style="width: 20px;"></i>
                <span>Users</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar" style="background-color: #e8f5e9; padding: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-left: 250px;">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="toggle-sidebar btn" id="sidebarToggle" style="color: #2e7d32; border: none; background: none;">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    
                    <div class="d-flex align-items-center gap-4">
                        <!-- Date Display -->
                        <div class="d-flex align-items-center px-3 py-2" style="background: #c8e6c9; border-radius: 8px;">
                            <i class="far fa-calendar-alt me-2" style="color: #2e7d32;"></i>
                            <span class="current-date" style="color: #2e7d32; font-weight: 500;">
                                <?= date('F d, Y') ?>
                            </span>
                        </div>

                        <!-- Time Display -->
                        <div class="d-flex align-items-center px-3 py-2" style="background: #c8e6c9; border-radius: 8px;">
                            <i class="far fa-clock me-2" style="color: #2e7d32;"></i>
                            <span id="philippine-time" style="color: #2e7d32; font-weight: 500;">--:--:--</span>
                        </div>

                        <!-- User Profile -->
                        <div class="profile-section">
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" 
                                   id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                   style="color: #2e7d32;">
                                    <span class="me-2" style="font-weight: 500;"><?= htmlspecialchars($user) ?></span>
                                    <?php if ($profilePic && file_exists("../" . $profilePic)): ?>
                                        <img src="../<?= htmlspecialchars($profilePic) ?>" class="profile-img" 
                                             alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #81c784;">
                                    <?php else: ?>
                                        <div class="profile-initial" style="width: 40px; height: 40px; border-radius: 50%; 
                                             background-color: #81c784; color: white; display: flex; align-items: center; 
                                             justify-content: center; font-weight: bold;">
                                            <?= htmlspecialchars($profileInitial) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" 
                                    style="border: none; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; margin-top: 10px;">
                                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                                        <i class="fas fa-user-circle me-2" style="color: #2e7d32;"></i>View Profile
                                    </a></li>
                                    <li><a class="dropdown-item py-2" href="edit_profile.php">
                                        <i class="fas fa-user-edit me-2" style="color: #2e7d32;"></i>Edit Profile
                                    </a></li>
                                    <li><a class="dropdown-item py-2" href="users.php">
                                        <i class="fas fa-users-cog me-2" style="color: #2e7d32;"></i>User Management
                                    </a></li>
                                    <li><hr class="dropdown-divider" style="margin: 0.5rem 0;"></li>
                                    <li><a class="dropdown-item py-2 text-danger" href="../logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="profileModalLabel">User Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <?php if ($profilePic && file_exists("../" . $profilePic)): ?>
                                <img src="../<?= htmlspecialchars($profilePic) ?>" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" alt="Profile Picture">
                            <?php else: ?>
                                <div class="profile-initial rounded-circle mx-auto" style="width: 120px; height: 120px; font-size: 3rem;">
                                    <?= htmlspecialchars($profileInitial) ?>
                                </div>
                            <?php endif; ?>
                            <h4 class="mt-3 mb-1"><?= htmlspecialchars($_SESSION['user']['full_name'] ?? $user) ?></h4>
                            <p class="text-muted mb-3"><?= htmlspecialchars($_SESSION['user']['position'] ?? 'Administrator') ?></p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="profile-info-item">
                                    <label class="text-muted small">Email</label>
                                    <p class="mb-2"><?= htmlspecialchars($_SESSION['user']['email'] ?? 'Not set') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-info-item">
                                    <label class="text-muted small">Phone</label>
                                    <p class="mb-2"><?= htmlspecialchars($_SESSION['user']['phone'] ?? 'Not set') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-info-item">
                                    <label class="text-muted small">Office</label>
                                    <p class="mb-2"><?= htmlspecialchars($_SESSION['user']['office'] ?? 'Not set') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-info-item">
                                    <label class="text-muted small">Role</label>
                                    <p class="mb-2"><span class="badge bg-primary"><?= htmlspecialchars(ucfirst($_SESSION['user']['role'])) ?></span></p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="profile-info-item">
                                    <label class="text-muted small">Address</label>
                                    <p class="mb-2"><?= htmlspecialchars($_SESSION['user']['address'] ?? 'Not set') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Inventory Dashboard</h2>
                        <p class="text-muted mb-0">Overview and statistics</p>
                    </div>
                    <div class="quick-actions-top">
                        <button class="btn btn-primary" onclick="window.location.href='inventory.php?action=add'">
                            <i class="fas fa-plus"></i> Add New Item
                        </button>
                        <button class="btn btn-outline-primary" onclick="window.location.href='reports.php?action=generate'">
                            <i class="fas fa-download"></i> Export Data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card stats-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stats-icon bg-primary-subtle">
                                <i class="fas fa-boxes text-primary"></i>
                            </div>
                            <span class="badge <?php echo $itemsChange >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                <i class="fas fa-arrow-<?php echo $itemsChange >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs($itemsChange); ?>%
                            </span>
                        </div>
                        <h4 class="stats-title mb-2">Total Items</h4>
                        <div class="d-flex align-items-baseline">
                            <h2 class="stats-number mb-0"><?php echo number_format($stats['total_items']); ?></h2>
                            <p class="text-muted ms-2 mb-0">items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card stats-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stats-icon bg-warning-subtle">
                                <i class="fas fa-tools text-warning"></i>
                            </div>
                            <span class="badge <?php echo $maintenanceChange <= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                <i class="fas fa-arrow-<?php echo $maintenanceChange <= 0 ? 'down' : 'up'; ?>"></i>
                                <?php echo abs($maintenanceChange); ?>%
                            </span>
                        </div>
                        <h4 class="stats-title mb-2">Pending Maintenance</h4>
                        <div class="d-flex align-items-baseline">
                            <h2 class="stats-number mb-0"><?php echo number_format($stats['pending_maintenance']); ?></h2>
                            <p class="text-muted ms-2 mb-0">requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card stats-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stats-icon bg-danger-subtle">
                                <i class="fas fa-archive text-danger"></i>
                            </div>
                            <span class="badge <?php echo $disposalChange <= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                <i class="fas fa-arrow-<?php echo $disposalChange <= 0 ? 'down' : 'up'; ?>"></i>
                                <?php echo abs($disposalChange); ?>%
                            </span>
                        </div>
                        <h4 class="stats-title mb-2">For Disposal</h4>
                        <div class="d-flex align-items-baseline">
                            <h2 class="stats-number mb-0"><?php echo number_format($stats['for_disposal']); ?></h2>
                            <p class="text-muted ms-2 mb-0">items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card stats-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="stats-icon bg-success-subtle">
                                <i class="fas fa-users text-success"></i>
                            </div>
                            <span class="badge <?php echo $usersChange >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                <i class="fas fa-arrow-<?php echo $usersChange >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs($usersChange); ?>%
                            </span>
                        </div>
                        <h4 class="stats-title mb-2">Active Users</h4>
                        <div class="d-flex align-items-baseline">
                            <h2 class="stats-number mb-0"><?php echo number_format($stats['active_users']); ?></h2>
                            <p class="text-muted ms-2 mb-0">users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fetch items for the dashboard table -->
            <?php
            // Fetch items for the dashboard table
            $itemsQuery = "SELECT * FROM items ORDER BY created_at DESC LIMIT 5";
            $itemsResult = $conn->query($itemsQuery);
            ?>
            <!-- Inventory Items -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="mb-0">Recent Inventory Items</h3>
                            <div class="d-flex gap-2">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search items..." id="searchInput">
                                    <button class="btn btn-outline-secondary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <button class="btn btn-outline-secondary">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                        
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
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($itemsResult->num_rows > 0): ?>
                                        <?php while ($item = $itemsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="product-icon bg-primary-subtle text-primary me-2">
                                                            <i class="fas fa-desktop"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($item['model']) ?></div>
                                                            <div class="text-muted small"><?= htmlspecialchars($item['operating_system'] ?? 'N/A') ?></div>
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
                                                    <div class="d-flex gap-2">
                                                        <a href="inventory.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Item">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>)" title="Delete Item">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                                    <p class="mb-0">No inventory items found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">Showing the 5 most recent items</div>
                            <a href="inventory.php" class="btn btn-outline-primary btn-sm">
                                View All Items <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/clock.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <script>
    // Add delete functionality
    function deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch(`../php/inventory/delete_item.php?id=${itemId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Refresh the page to show updated list
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
    </script>
</body>
</html>
