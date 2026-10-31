<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /../../login.php");
    exit();
}

require_once __DIR__ . '/../auth/db.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture = null;

    // Handle File Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "", $_FILES['photo']['name']);
    
    // Supabase Configuration
    $supabaseUrl = "https://rtcmhbxpnufxbhlohmgo.supabase.co"; // Replace with your actual Supabase project URL base (e.g. https://xyz.supabase.co)
    $supabaseServiceKey = "YOUR_SUPABASE_SERVICE_OR_ANON_KEY"; // Your Supabase API key
    $bucketName = "uplaods"; // Your exact bucket name

    $url = "$supabaseUrl/storage/v1/object/$bucketName/$fileName";

    // Read file data
    $fileData = file_get_contents($fileTmpPath);

    // Send file to Supabase Storage via cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $supabaseServiceKey",
        "Content-Type: " . $_FILES['photo']['type'],
        "x-upsert: true"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        // Generate the public URL to save in your PostgreSQL database
        $profile_picture_url = "$supabaseUrl/storage/v1/object/public/$bucketName/$fileName";
    }
}

    if (!empty($name) && !empty($email)) {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, address, profile_picture) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $profile_picture]);
        header("Location: view_students.php");
        exit();
    } else {
        $message = "Name and Email are required fields.";
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
                <div class="card shadow-sm border-0 p-4">
                    <h3 class="mb-3 text-center">Add New Student</h3>
                    <?php if($message): ?><div class="alert alert-danger"><?= $message ?></div><?php endif; ?>
                    
                    <form id="studentForm" action="add_student.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                            <div id="emailFeedback" class="text-danger small"></div>
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
                    <div class="text-center mt-3"><a href="view_students.php">Back to Student List</a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Real-time Validation Script -->
    <script>
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!name || !email) {
                alert('Please fill out all required fields.');
                e.preventDefault();
            } else if (!emailRegex.test(email)) {
                document.getElementById('emailFeedback').innerText = 'Please enter a valid email address.';
                e.preventDefault();
            }
        });
    </script>
</body>
</html>