<?php
session_start();
require_once 'db.php';

class auth {
	public static function checkAuth() {
		if (!isset($_SESSION['user'])) {
			header('Location: ../../login.php');
			exit;
		}
	}
	
	public static function user() {
		return $_SESSION['user'] ?? null;
	}
	
	public static function isAdmin() {
		return self::user() ['role'] === 'admin';
	}
	
	public static function login($username, $password) {
		global $pdo;
		$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
		
		$stmt->execute([$username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($user && password_verify($password, $user['password'])) {
			$_SESSION['user'] = $user;
				return true;
		}
		return false;
	}
	
	public static function logout() {
		session_destroy();
		
		header('Location: ../../login.php');
		exit;
	}
}
?>
