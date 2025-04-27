<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$base_url = "/Crud"; // Adjust this to match your project's root directory
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP CRUD Application</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/custom.css" rel="stylesheet">
    <style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .flex-grow-1 {
        flex: 1;
    }

    footer {
        margin-top: auto;
    }
</style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container d-flex align-items-center" style="gap: 10px;">
            <!-- Placeholder for the logo/image -->
            <div class="placeholder-logo" style="width: 120px; height: 50px; background-color: #6c757d; border-radius: 5px;">
                <!-- You can replace this div with an <img> tag later -->
            </div>
            <!-- Header text/logo -->
            <a class="navbar-brand fw-bold fs-3" href="<?php echo $base_url; ?>/index.php" style="margin-left:20px;">D'Plato</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($loggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Crud/crud/read.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Crud/crud/create.php">Create Report</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Crud/auth/prof_management.php">Profile Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Crud/auth/logout.php" onclick="return confirm
                            ('Are you sure you want to Log Out?');">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/Crud/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/Crud/auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="ms-3">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#about">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">