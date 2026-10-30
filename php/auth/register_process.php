<?php
session_start();
require_once __DIR__ . 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../../register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../../login.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Username or email already exists.";
        header("Location: ../../registration.php");
        exit();
    }
}
?>