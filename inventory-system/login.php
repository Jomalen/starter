<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	
	if (auth::login($username, $password)) {
		$role = $_SESSION['user']['role'];
		if ($role === 'admin')
		{
			header('Location: admin/dashboard.php');
		} else {
			header('Location: users/dashboard.php');
		}
		exit;
	} else {
		$error = "Invalid username or password.";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Login - IHOMS Inventory</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap.bundle.min.js"></script>
	<script src="https://kit.fontawesome.com/a2e0c1e6a5.js" crossorigin="anonymous"></script>
	<style>
		body {
			background: #f0f4f8;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}
		.login-container {
			background: white; 
			padding: 40px;
			border-radius: 15px;
			box-shadow: 0 0 20px rgba(0,0,0,0.15);
			width: 450px;
			position: relative;
		}
		.logo-row {
			display: flex;
			justify-content: center;
			align-items: center;
			margin-bottom: 20px;
		}
		.logo-row img {
			width: 60px;
		}
		.logo-center {
			width: 70px;
		}
		.password-toggle {
			position: relative;
		}
		.password-toggle i {
			position: absolute;
			right: 15px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
			color: #6c757d;
		}
	</style>
</head>
<body>
	<div class="login-container">
		<div class="logo-row">
			<img src="assets/img/logo1.jpg" alt="Logo Left">
			<img src="assets/img/logo2.jpg" class="logo-center" alt="Logo Center">
			<img src="assets/img/logo3.jpg" alt="Logo Right">
		</div>
		
		<h4 class="text-center mb-3">Hospital Inventory System</h4>
		
		<?php if ($error): ?>
			<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
			
			<?php endif; ?>
			
			<form method="POST">
				<div class="mb-3">
					<label class="form-label">Username</label>
						<input type="text" name="username" class="form-control" required autocomplete="off">
					</div>
					<div class="mb-3 password-toggle">
						<label class="form-label">Password</label>
						<input type="password" name="password" id="password" class="form-control" required autocomplete="off">
						<i class="fas fa-eye" id="togglePassword"></i>
					</div>
					<button type="submit" class="btn btn-success w-100">Login</button>
			</form>
		</div>
		
		<script>
			const togglePassword = document.querySelector('#togglePassword');
			const passwordField = document.querySelector('#password');
			
			togglePassword.addEventListener('click', function () {
				const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
				passwordField.setAttribute('type', type);
				this.classList.toggle('fa-eye-slash');
			});
		</script>
	</body>
	</html>


