<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /../../login.php");
    exit();
}
require_once __DIR__ . '/../auth/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Retrieve student information to obtain the image link before deletion
    $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $profile_picture_url = $student['profile_picture'];

        // 2. If an image is stored in Supabase, we delete it.
        if (!empty($profile_picture_url)) {
            // Extracting the final filename from a URL
            $fileName = basename($profile_picture_url);
            
            $bucketName = 'uploads';
            // API endpoint for deletion in Supabase Storage
            $url = "https://rtcmhbxpnufxbhlohmgo.supabase.co/storage/v1/object/" . $bucketName . "/avatars/" . $fileName;

            // The secret key to bypassing deletion restrictions
            $supabase_key = $_ENV['SUPABASE_KEY'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // use command DELETE
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

            $headers = [
                "apikey: " . $supabase_key,
                "Authorization: Bearer " . $supabase_key
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);
        }

        // 3. Delete the student record from the database
        $deleteStmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $deleteStmt->execute([$id]);
    }
}

// Return to the student view page upon completion
header("Location: view_students.php");
exit();
?>