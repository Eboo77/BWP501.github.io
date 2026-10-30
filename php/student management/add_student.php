<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . '../auth/db.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture = null;

    // Handle File Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $target_file;
            }
        }
    }

    if (!empty($name) && !empty($email)) {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, phone, address, profile_picture) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $address, $profile_picture]);
        header("Location: /view_students.php");
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
                    <div class="text-center mt-3"><a href="/view_students.php">Back to Student List</a></div>
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