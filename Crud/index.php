<?php
// Include header
require_once "includes/header.php";
?>
<br>
<br>
<br>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Welcome to D'Plato Sales Management Panel</h4>
            </div>
            <div class="card-body">
                <p class="lead">Welcome to our management panel, here we can create, read, update, and delete new sales report.
                    Choose below what you want to do.
                </p>
                <?php if ($loggedIn): ?>
                    <div class="alert alert-success">
                        <h4>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h4>
                        <p>What actions do you want to take? You can:</p>
                        <a href="crud/read.php" class="btn btn-info">View Dashboard</a>
                        <a href="crud/create.php" class="btn btn-primary">Create New Sales Report</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Please login or register to manage sales reports.</p>
                        <a href="auth/login.php" class="btn btn-primary">Login</a>
                        <a href="auth/register.php" class="btn btn-secondary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<?php
// Include header
require_once "includes/footer.php";
?>