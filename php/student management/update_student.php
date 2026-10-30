<?php
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
    header("Location: /view_students.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture = $student['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            if ($profile_picture && file_exists($profile_picture)) {
                unlink($profile_picture); // Remove old picture
            }
            $profile_picture = $target_file;
        }
    }

    $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $address, $profile_picture, $id]);
    header("Location: /view_students.php");
    exit();
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
                            <?php if ($student['profile_picture'] && file_exists($student['profile_picture'])): ?>
                                <img src="<?= $student['profile_picture'] ?>" class="rounded mb-2" width="60" height="60" style="object-fit: cover;">
                            <?php else: ?>
                                <p class="text-muted small">No picture uploaded.</p>
                            <?php endif; ?>
                            <input type="file" class="form-control mt-1" name="profile_picture" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Student</button>
                    </form>
                    <div class="text-center mt-3"><a href="/view_students.php">Cancel</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>