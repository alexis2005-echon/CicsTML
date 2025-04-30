<?php
// Include header
require_once "includes/header.php";
?>
<div class="container mt-5 mb-5" style="background-color: #fff7e6; padding: 20px; border-radius: 10px;"> <!-- Subtle orange background -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #ff6600; color: #fff;">
                    <h4 class="mb-0 text-center">Welcome to D'Plato Sales Management Panel</h4>
                </div>
                <div class="card-body">
                    <p class="lead text-center mb-4">Welcome to our management panel, where you can create, read, update, and delete sales reports. Choose below what you want to do.</p>
                    <?php if ($loggedIn): ?>
                        <div class="alert text-center" style="background-color: rgba(255, 102, 0, 0.1); border: 1px solid #ff6600; color: #ff6600;">
                            <h4>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h4>
                            <p>What actions do you want to take? You can:</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="crud/read.php" class="btn btn-outline-primary" style="background-color: #fff; border-color: #ff6600; color: #ff6600;">View Dashboard</a> <!-- Updated button -->
                                <a href="crud/create.php" class="btn btn-outline-primary" style="background-color: #ff6600; border-color: #ff6600; color: #fff;">Create New Sales Report</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <p>Please login or register to manage sales reports.</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="auth/login.php" class="btn btn-outline-primary" style="background-color: #ff6600; border-color: #ff6600; color: #fff;">Login</a>
                                <a href="auth/register.php" class="btn btn-outline-secondary">Register</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Include footer
require_once "includes/footer.php";
?>