<?php
session_start();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Student Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="php/student management/view_students.php">View Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="php/student management/add_student.php">Add Student</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <span class="nav-link text-info">Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-danger btn-sm px-3 ms-2 text-white" href="php/auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light text-dark btn-sm px-3 ms-2 mb-1 mb-lg-0 bg-light" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary btn-sm px-3 ms-2 text-white" href="registration.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container my-5">
        <div class="p-5 mb-4 bg-white rounded-3 shadow-sm text-center">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold text-primary">Welcome to Student Registration System</h1>
                <p class="col-md-8 fs-4 mx-auto text-muted">Manage student records efficiently with real-time validation, pagination, and secure account authentication.</p>
                <div class="mt-4">
                    <a href="php/student management/view_students.php" class="btn btn-primary btn-lg me-2">View Students</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="registration.php" class="btn btn-outline-secondary btn-lg">Create Account</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>