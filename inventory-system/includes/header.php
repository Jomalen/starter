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
                            <span class="me-2" style="font-weight: 500;">
                                <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Admin') ?>
                            </span>
                            <?php
                            $profilePic = $_SESSION['user']['profile_pic'] ?? 'assets/img/default-avatar.jpg';
                            $profileInitial = strtoupper($_SESSION['user']['username'][0] ?? 'A');
                            if ($profilePic && file_exists("../" . $profilePic)): ?>
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

<style>
.dropdown-item:hover {
    background-color: #e8f5e9;
}
.topbar {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1000;
}
.main-content {
    margin-top: 80px;
    margin-left: 250px;
    padding: 20px;
    background-color: #e8f5e9;
}
@media (max-width: 768px) {
    .topbar {
        margin-left: 0;
    }
    .main-content {
        margin-left: 0;
    }
}
</style>

<!-- Add clock script -->
<script src="../assets/js/clock.js"></script>
