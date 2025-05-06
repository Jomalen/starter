<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Authentication check
$auth = new auth();
$auth->checkAuth();

$userId = $_SESSION['user']['id'];
$successMessage = '';
$errorMessage = '';

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error fetching user data";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $office = $_POST['office'] ?? '';
    $address = $_POST['address'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Verify current password if trying to change password
        if (!empty($new_password)) {
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
        }

        // Handle profile picture upload
        $profile_pic = $user['profile_pic']; // Keep existing by default
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid file type. Allowed: jpg, jpeg, png, gif");
            }

            $newFilename = "profile_" . $userId . "_" . time() . "." . $ext;
            $uploadPath = "../assets/img/profiles/" . $newFilename;

            // Create directory if it doesn't exist
            if (!file_exists("../assets/img/profiles/")) {
                mkdir("../assets/img/profiles/", 0777, true);
            }

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {
                // Delete old profile picture if it exists and is not the default
                if ($profile_pic && $profile_pic !== 'assets/img/default-avatar.jpg' && file_exists("../" . $profile_pic)) {
                    unlink("../" . $profile_pic);
                }
                $profile_pic = "assets/img/profiles/" . $newFilename;
            }
        }

        // Update user data
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, office = ?, address = ?, profile_pic = ?";
        $params = [$full_name, $email, $phone, $office, $address, $profile_pic];

        // Add password update if new password is provided
        if (!empty($new_password)) {
            $sql .= ", password = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Log the activity
        $activity = "Updated profile information";
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
        $stmt->execute([$userId, $activity]);

        $pdo->commit();

        // Update session data
        $_SESSION['user'] = array_merge($_SESSION['user'], [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'office' => $office,
            'address' => $address,
            'profile_pic' => $profile_pic
        ]);
        $_SESSION['profile_pic'] = $profile_pic; // Add this line to update profile pic separately

        $successMessage = "Profile updated successfully!";
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - IHOMS</title>
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
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Edit Profile</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($successMessage): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                            <?php endif; ?>
                            <?php if ($errorMessage): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                            <?php endif; ?>

                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        <?php if ($user['profile_pic'] && file_exists("../" . $user['profile_pic'])): ?>
                                            <img src="../<?= htmlspecialchars($user['profile_pic']) ?>" 
                                                 class="rounded-circle"
                                                 style="width: 150px; height: 150px; object-fit: cover;"
                                                 id="profilePreview"
                                                 alt="Profile Picture">
                                        <?php else: ?>
                                            <div class="profile-initial rounded-circle"
                                                 style="width: 150px; height: 150px; font-size: 3rem;"
                                                 id="profileInitial">
                                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <label class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 cursor-pointer">
                                            <i class="fas fa-camera"></i>
                                            <input type="file" name="profile_pic" class="d-none" id="profilePicInput" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Office</label>
                                        <input type="text" class="form-control" name="office" 
                                               value="<?= htmlspecialchars($user['office'] ?? '') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-4">
                                        <h6 class="mb-3">Change Password</h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password">
                                        <small class="text-muted">Required only if changing password</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password">
                                        <small class="text-muted">Leave blank to keep current password</small>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                        <i class="fas fa-trash me-2"></i>Delete Account
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Warning: This action cannot be undone!</p>
                    <p>Are you sure you want to delete your account? All your data will be permanently removed.</p>
                    <form id="deleteAccountForm">
                        <div class="mb-3">
                            <label class="form-label">Enter your password to confirm</label>
                            <input type="password" class="form-control" id="deleteAccountPassword" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteAccount()">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Profile picture preview
    document.getElementById('profilePicInput')?.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profilePreview');
                const initial = document.getElementById('profileInitial');
                
                if (preview) {
                    preview.src = e.target.result;
                } else if (initial) {
                    // Create new image preview
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.id = 'profilePreview';
                    img.className = 'rounded-circle';
                    img.style.width = '150px';
                    img.style.height = '150px';
                    img.style.objectFit = 'cover';
                    initial.parentNode.replaceChild(img, initial);
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Delete account
    function deleteAccount() {
        const password = document.getElementById('deleteAccountPassword').value;
        if (!password) {
            alert('Please enter your password to confirm deletion');
            return;
        }

        if (confirm('Are you absolutely sure you want to delete your account?')) {
            fetch('../php/users/delete_account.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Account deleted successfully');
                    window.location.href = '../logout.php';
                } else {
                    alert(data.message || 'Error deleting account');
                }
            })
            .catch(error => {
                alert('Error deleting account');
                console.error('Error:', error);
            });
        }
    }
    </script>
</body>
</html>