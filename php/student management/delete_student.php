<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /../../login.php");
    exit();
}
require_once __DIR__ . '/../auth/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. جلب معلومات الطالب للحصول على رابط الصورة قبل الحذف
    $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $profile_picture_url = $student['profile_picture'];

        // 2. إذا كانت هناك صورة مخزنة في Supabase، نقوم بحذفها
        if (!empty($profile_picture_url)) {
            // استخراج اسم الملف الأخير من الرابط (مثلاً: 1712345678_image.jpg)
            $fileName = basename($profile_picture_url);
            
            $bucketName = 'uploads';
            // رابط الـ API الخاص بالحذف في Supabase Storage
            $url = "https://rtcmhbxpnufxbhlohmgo.supabase.co/storage/v1/object/" . $bucketName . "/avatars/" . $fileName;

            // مفتاح الـ service_role السري لتجاوز قيود الحذف
            $supabase_key = $_ENV['SUPABASE_KEY'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // استخدام طلب DELETE
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

        // 3. حذف سجل الطالب من قاعدة البيانات
        $deleteStmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $deleteStmt->execute([$id]);
    }
}

// العودة إلى صفحة عرض الطلاب بعد الانتهاء
header("Location: view_students.php");
exit();
?>