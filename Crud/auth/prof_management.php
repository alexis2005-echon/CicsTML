<?php
// filepath: c:\xampp\htdocs\Crud\auth\prof_management.php

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once "../config/database.php";

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (!empty(trim($_POST["password"]))) {
        if (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }
    }

    // Validate confirm password
    if (!empty(trim($_POST["password"]))) {
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm your password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if ($password !== $confirm_password) {
                $confirm_password_err = "Passwords do not match.";
            }
        }
    }

    // Check for errors before updating the database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare an update statement
        $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : $_SESSION["password"];
            mysqli_stmt_bind_param($stmt, "ssi", $username, $hashed_password, $_SESSION["id"]);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Update session variables
                $_SESSION["username"] = $username;

                // Redirect to the dashboard
                header("location: ../crud/read.php");
                exit;
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
    <title>Profile Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    // Include the header
    require_once "../includes/header.php";
    ?>

    <div class="container mt-5 mb-5">
        <h2>Profile Management</h2>
        <p>Update your profile information below:</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : ''; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group mb-3">
                <label>New Password (optional)</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group mb-3">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Profile">
                <a href="../crud/read.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>


    <?php
    // Include the footer
    require_once "../includes/footer.php";
    ?>
</body>

</html>