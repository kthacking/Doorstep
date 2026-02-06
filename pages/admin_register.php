<?php 
$page_title = "Admin Registration";
include '../includes/header.php'; 
?>

<div class="container" style="max-width: 500px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Admin Registration</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="../actions/admin_register_action_public.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" required placeholder="Admin Name">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="admin@doorstep.com">
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
                <label>Office Address</label>
                <textarea name="address" class="form-control" rows="3" required placeholder="Office location"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register as Admin</button>
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
