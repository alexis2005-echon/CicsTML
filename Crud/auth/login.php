<?php
// Initialize the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: ../index.php");
    exit;
}

// Include database connection
require_once "../config/database.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Redirect user to welcome page
                            header("location: ../index.php");
                        } else {
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - D'Plato</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <style>
    /* Ensure the footer sticks to the bottom */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        scroll-behavior: smooth;
    }

    .flex-grow-1 {
        flex: 1;
    }

    footer {
        margin-top: auto;
    }
    .icon-gap {
        gap: 30px;
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
            <a class="navbar-brand fw-bold fs-3" href="login.php">D'Plato</a>
        </div>
    </nav>
    <div class="flex-grow-1">
        <div class="container mt-5 text-center">
            <h1>Welcome to D'Plato</h1>
            <p class="lead">Your sales management solution</p>
            <p class="lead">Please login to continue</p>
        </div>
        <hr style="border: 1px solid #ccc; width: 80%; margin: 20px auto;">
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Login</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            if (!empty($login_err)) {
                                echo '<div class="alert alert-danger">' . $login_err . '</div>';
                            }
                            ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                </div>
                                <div class="form-group mt-3">
                                    <input type="submit" class="btn btn-primary" value="Login">
                                </div>
                                <p class="mt-3">Don't have an account? <a href="register.php">Sign up now</a>.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container text-center">
            <p class="mb-1">&copy; 2023 D'Plato. All rights reserved.</p>
            <p class="mb-3">Designed by <span class="fw-bold">CicsTML Team</span></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#about" class="text-decoration-none text-black">About</a>
                <span>|</span>
                <a href="#contact" class="text-decoration-none text-black">Contact</a>
            </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <footer class="bg-dark text-white py-4">
        <br>
        <div id="about" class="container text-center">
            <h5 class="fw-bold">About D'Plato Sales Management System</h5>
            <p class="mb-0">D'Plato is a sales management system designed to help businesses manage their sales reports efficiently.</p>
            <p>It allows users to create, read, update, and delete sales reports with ease.</p>
        </div>
        <hr class="border-secondary my-4" style="width: 80%; margin: 0 auto;">
        <div id="about" class="container text-center">
    <h5>Contact Us:</h5>
    <br>
    <div id="contact" class="d-flex justify-content-center gap-4 mt-3 icon-gap">
        <!-- Facebook Icon -->
        <a href="https://www.facebook.com/profile.php?id=61575244903155" target="_blank" class="text-decoration-none text-white mx-3">
            <i class="fab fa-facebook fa-2x"></i>
        </a>
        <!-- Phone Icon -->
        <a href="09670488771" class="text-decoration-none text-white mx-3">
            <i class="fas fa-phone fa-2x"></i>
        </a>
        <!-- Gmail Icon -->
        <a href="mailto:sgtanatoli@gmail.com" class="text-decoration-none text-white mx-3">
            <i class="fas fa-envelope fa-2x"></i>
        </a>
    </div>
        <br>
    </footer>
</body>

</html>

</html>