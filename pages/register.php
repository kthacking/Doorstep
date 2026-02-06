<?php 
$page_title = "Register";
include '../includes/header.php'; 
?>

<div class="container" style="max-width: 500px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Create Account</h2>
        <form action="../actions/register_action.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="john@example.com">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" required placeholder="9876543210">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label>Permanent Address</label>
                <textarea name="address" class="form-control" rows="3" required placeholder="Your full address for service visits"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
