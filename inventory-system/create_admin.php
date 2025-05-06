<?php
require_once 'includes/db.php'; // correct path based on your setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = 'admin';
    $created_at = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, role, is_active, status, created_at)
                               VALUES (:username, :password, :full_name, :email, :role, 1, 'Active', :created_at)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':full_name' => $full_name,
            ':email' => $email,
            ':role' => $role,
            ':created_at' => $created_at
        ]);

        echo "Admin created successfully!";
    } catch (PDOException $e) {
        echo "Error creating admin: " . $e->getMessage();
    }
}
?>

<!-- Simple HTML form -->
<form method="post" style="max-width:400px; margin:auto;">
    <h2>Create Admin</h2>
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Full Name:</label><br>
    <input type="text" name="full_name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <button type="submit">Create Admin</button>
</form>
