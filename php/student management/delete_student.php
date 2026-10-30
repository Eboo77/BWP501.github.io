<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /../../login.php");
    exit();
}
require_once __DIR__ . '/../auth/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Fetch image path to delete file from server
    $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        if ($student['profile_picture'] && file_exists($student['profile_picture'])) {
            unlink($student['profile_picture']);
        }
        $delStmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $delStmt->execute([$id]);
    }
}

header("Location: view_students.php");
exit();
?>