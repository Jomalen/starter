<!-- sidebar.php -->
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

<style>
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

