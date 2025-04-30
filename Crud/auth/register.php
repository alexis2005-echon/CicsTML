<?php
// Include database connection
require_once "../config/database.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header("location: login.php");
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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - D'Plato</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
            background-color: #fff; /* White background */
            color: #333; /* Dark text for contrast */
            scroll-behavior: smooth;
        }

        .flex-grow-1 {
            flex: 1;
        }

        /* Navbar styling */
        .navbar {
            background-color: #ff6600; /* Orange background */
        }

        .navbar-brand {
            color: #fff !important; /* White text */
        }

        /* Card styling */
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #ff6600; /* Orange header */
            color: #fff; /* White text */
        }

        .btn-primary {
            background-color: #ff6600; /* Orange button */
            border-color: #ff6600;
        }

        .btn-primary:hover {
            background-color: #e65c00; /* Darker orange on hover */
            border-color: #e65c00;
        }

        /* Footer styling */
        footer {
            margin-top: auto;
            background-color: #ff6600; /* Orange background */
            color: #fff; /* White text */
            padding: 20px 0;
        }

        footer a {
            color: #fff; /* White links */
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Additional styling */
        hr {
            border: 1px solid #ff6600; /* Orange horizontal line */
            width: 80%;
            margin: 20px auto;
        }

        a.text-decoration-none {
            color: #ff6600; /* Orange links */
        }

        a.text-decoration-none:hover {
            color: #e65c00; /* Darker orange on hover */
        }

        /* Hover effect for logo */
        .logo-container:hover {
            transform: scale(1.1); /* Slight zoom effect */
        }

        /* Hover effect for D'Plato text */
        .navbar-brand:hover {
            color: #ffe6cc !important; /* Lighter shade of orange */
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg" style="background-color: #ff6000 !important;">
        <div class="container d-flex align-items-center" style="gap: 10px;">
            <div class="logo-container" style="width: 120px; height: 50px; border-radius: 5px; overflow: hidden; transition: transform 0.3s;">
                <img src="../images/1 (1).png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <a class="navbar-brand fw-bold fs-3 text-white" href="login.php" style="transition: color 0.3s;">D'Plato</a>
        </div>
    </nav>
    <div class="flex-grow-1">
        <div class="container mt-5 text-center">
            <h1>Create Your Account</h1>
            <p class="lead">Join D'Plato to manage your sales efficiently</p>
        </div>
        <hr>
        <div class="container mt-4 mb-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Register</h4>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                </div>
                                <div class="form-group mt-3">
                                    <input type="submit" class="btn btn-primary" value="Register">
                                    <a class="btn btn-secondary ml-2" href="login.php">Cancel</a>
                                </div>
                                <p class="mt-3">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mb-5">
        <p style="font-size: 0.9rem; color: #333; font-weight: bold; margin-bottom: 0;">
            © 2025 D'Plato. All rights reserved.
        </p>
        <p style="font-size: 0.9rem; color: #ff6600; font-weight: bold;">
            Designed by <span style="color: #333;">CicsTML Team</span>
        </p>
    </div>
    <footer>
        <div class="container text-center">
            <h5 class="fw-bold">About D'Plato Sales Management System</h5>
            <p class="mb-0">D'Plato is a sales management system designed to help businesses manage their sales reports efficiently.</p>
            <p>It allows users to create, read, update, and delete sales reports with ease.</p>
            <hr class="border-secondary my-4" style="width: 80%; margin: 0 auto;">
            <h5>Contact Us:</h5>
            <div class="d-flex justify-content-center gap-4 mt-3 icon-gap">
                <a href="https://www.facebook.com/profile.php?id=61575244903155" target="_blank" class="text-decoration-none text-white mx-3">
                    <i class="fab fa-facebook fa-2x"></i>
                </a>
                <a href="tel:09670488771" class="text-decoration-none text-white mx-3">
                    <i class="fas fa-phone fa-2x"></i>
                </a>
                <a href="mailto:sgtanatoli@gmail.com" class="text-decoration-none text-white mx-3">
                    <i class="fas fa-envelope fa-2x"></i>
                </a>
            </div>
        </div>
    </footer>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
    </script>
</body>

</html>