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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Poppins', sans-serif; /* Apply Poppins font */
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding-top: 70px; /* Adjust for fixed navbar height */
        }

        .flex-grow-1 {
            flex: 1;
        }

        footer {
            margin-top: auto;
        }

        /* Hover effect for logo */
        .logo-container:hover {
            transform: scale(1.1);
        }

        /* Hover effect for D'Plato text */
        .navbar-brand:hover {
            color: #ffe6cc !important; /* Lighter shade of orange */
            transform: scale(1.1);
        }

        /* Hover effect for navbar links */
        .nav-link:hover {
            color: #ffe6cc !important; /* Lighter shade of orange */
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg" style="background-color: #ff6600; position: fixed; top: 0; width: 100%; z-index: 1030;"> 
        <div class="container d-flex align-items-center" style="gap: 10px;">
            <!-- Logo -->
            <div class="logo-container" style="width: 120px; height: 50px; border-radius: 5px; overflow: hidden; transition: transform 0.3s;">
                <img src="<?php echo $base_url; ?>/images/1 (1).png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <!-- Header text/logo -->
            <a class="navbar-brand fw-bold fs-3 text-white" href="<?php echo $base_url; ?>/index.php" style="margin-left: 20px; transition: color 0.3s, transform 0.3s;">D'Plato</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($loggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Crud/crud/read.php" style="transition: color 0.3s;">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Crud/crud/create.php" style="transition: color 0.3s;">Create Report</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Crud/auth/prof_management.php" style="transition: color 0.3s;">Profile Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Crud/auth/logout.php" onclick="return confirm('Are you sure you want to Log Out?');" style="transition: color 0.3s;">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Crud/auth/login.php" style="transition: color 0.3s;">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/Crud/auth/register.php" style="transition: color 0.3s;">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="ms-3">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#about" style="transition: color 0.3s;">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <style>
            /* Hover effect for logo */
            .logo-container:hover {
                transform: scale(1.1);
            }

            /* Hover effect for D'Plato text */
            .navbar-brand:hover {
                color: #ffe6cc !important; /* Lighter shade of orange */
                transform: scale(1.1);
            }

            /* Hover effect for navbar links */
            .nav-link:hover {
                color: #ffe6cc !important; /* Lighter shade of orange */
            }
        </style>