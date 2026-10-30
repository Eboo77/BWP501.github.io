<?php
session_start();
require_once __DIR__ . 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['login_identifier']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ../student management/view_students.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid username/email or password.";
        header("Location: ../../login.php");
        exit();
    }
}
?>