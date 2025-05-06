<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Welcome - IHOMS System</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			background-color: #f0f4f8;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
		}
		.welcome-card {
			background: white;
			padding: 40px;
			border-radius: 20px;
			box-shadow: 0 0 20px rgba(0,0,0,0.1);
			text-align: center;
			max-width: 500px;
			width: 90%;
		}
		.welcome-logo {
			width: 100px;
			margin-bottom: 20px;
		}
		.btn-green {
			background-color: #2e7d32;
			color: white;
		}
		.btn-green:hover {
			background-color: rgb(65, 165, 71);
		}
		.logo-row {
			display: flex;
			justify-content: space-between;
			margin-bottom: 20px;
		}
		.logo-row img {
			width: 30%;
			object-fit: contain;
		}
	</style>
</head>
<body>
	<div class="welcome-card">
		<div class="logo-row">
			<img src="assets/img/logo1.jpg" alt="Logo Left">
			<img src="assets/img/logo2.jpg" class="logo-center" alt="Logo Center">
			<img src="assets/img/logo3.jpg" alt="Logo Right">
		</div>
		<h2>Welcome to the Hospital Inventory System</h2>
		<p class="mb-4">Efficiently manage hospital items and maintenance requests.</p>
		<div class="d-grid gap-2">
			<a href="login.php" class="btn btn-green">Login</a>
			<a href="https://hospital-website.gov.ph" class="btn btn-outline-secondary" target="_blank">Hospital Homepage</a>
		</div>
	</div>
</body>
</html>
