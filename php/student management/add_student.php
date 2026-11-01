<?php
ini_set('display_errors', 1);
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// استدعاء ملف الاتصال بقاعدة البيانات (تأكد من صحة المسار حسب مشروعك)
require_once '../auth/db.php'; 

// استدعاء الـ Autoload الخاصة بـ Composer إذا أردت استخدام ملفات البيئة
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture_url = null;

    // ضع مفتاح الـ Anon Key الخاص بـ Supabase هنا أو استدعِه من متغيرات البيئة
    $supabase_anon_key = $_ENV['SUPABASE_ANON_KEY'] ?? '';

    // التحقق من الحقول الإجبارية
    if (empty($name) || empty($email)) {
        $message = "Name and Email are required fields.";
    } else {
        // معالجة رفع الصورة إذا وجدت وبدون أخطاء
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $tmpFilePath = $_FILES['profile_picture']['tmp_name'];
            $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
            $fileData = file_get_contents($tmpFilePath);
            $fileType = $_FILES['profile_picture']['type'];

            $bucketName = 'uploads'; // اسم الـ Bucket في Supabase
            $url = "https://rtcmhbxpnufxbhlohmgo.supabase.co/storage/v1/object/" . $bucketName . "/avatars/" . $fileName;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

            $headers = [
                "apikey: " . $supabase_anon_key,
                "Authorization: Bearer " . $supabase_anon_key,
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
                    // رابط الصورة العام للوصول إليها
                    $profile_picture_url = "https://rtcmhbxpnufxbhlohmgo.supabase.co/storage/v1/object/public/" . $bucketName . "/avatars/" . $fileName;
                } else {
                    $message = "HTTP Code: " . $httpCode . " - Response: " . $response;
                }
            }
        }

        // إدخال البيانات في قاعدة البيانات في حال عدم وجود أخطاء سابقة
        if (empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, address, profile_picture) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $address, $profile_picture_url]);
            
            // التوجيه السليم بعد نجاح العملية
            header("Location: view_students.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow sm border-0 p-4">
                    <h3 class="mb-3 text-center">Add New Student</h3>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form id="studentForm" action="add_student.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                            <div id="emailfeedback" class="text-danger small"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profile Picture (Optional)</label>
                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Student</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="view_students.php">Back to Student List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!name || !email) {
                alert('Please fill out all required fields.');
                e.preventDefault();
            } else if (!emailRegex.test(email)) {
                document.getElementById('emailfeedback').innerText = 'Please enter a valid email address.';
                e.preventDefault();
            }
        });
    </script>
</body>
</html>