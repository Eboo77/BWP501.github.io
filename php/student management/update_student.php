<?php
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /../../login.php");
    exit();
}

require_once __DIR__ . '/../auth/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: view_students.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture_url = $student['profile_picture']; // الاحتفاظ بالرابط القديم مبدئياً

    // ضع مفتاح الـ service_role السري الخاص بـ Supabase هنا لتجاوز قيود RLS
    $supabase_service_role_key = $_ENV['SUPABASE_KEY'];
    $bucketName = 'uploads'; // اسم الـ Bucket في Supabase

    // التحقق من رفع صورة جديدة
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $tmpFilePath = $_FILES['profile_picture']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
        $fileData = file_get_contents($tmpFilePath);
        $fileType = $_FILES['profile_picture']['type'];

        $url = "https://rtcmhbxpnufxhblohngo.supabase.co/storage/v1/object/" . $bucketName . "/avatars/" . $fileName;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $headers = [
            "apikey: " . $supabase_service_role_key,
            "Authorization: Bearer " . $supabase_service_role_key,
            "Content-Type: " . $fileType,
            "x-upsert: true"
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $message = "CURL Error: " . curl_error($ch);
            curl_close($ch);
        } else {
            curl_close($ch);
            if ($httpCode === 200 || $httpCode === 201) {
                // الحصول على الرابط العام الجديد للصورة في Supabase
                $profile_picture_url = "https://rtcmhbxpnufxbhlohmgo.supabase.co/storage/v1/object/public/" . $bucketName . "/avatars/" . $fileName;
            } else {
                $message = "HTTP Code: " . $httpCode . " - Response: " . $response;
            }
        }
    }

    // تحديث البيانات في قاعدة البيانات إذا لم يحدث خطأ في الرفع
    if (empty($message)) {
        $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $profile_picture_url, $id]);
        
        header("Location: view_students.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-4">
                    <h3 class="mb-3 text-center">Update Student</h3>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form action="update_student.php?id=<?= $student['id'] ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($student['phone']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($student['address']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Profile Picture</label><br>
                            <?php if (!empty($student['profile_picture'])): ?>
                                <img src="<?= htmlspecialchars($student['profile_picture']) ?>" class="rounded mb-2" width="60" height="60" style="object-fit: cover;">
                            <?php else: ?>
                                <p class="text-muted small">No picture uploaded.</p>
                            <?php endif; ?>
                            <input type="file" class="form-control mt-1" name="profile_picture" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Student</button>
                    </form>
                    <div class="text-center mt-3"><a href="view_students.php">Cancel</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>