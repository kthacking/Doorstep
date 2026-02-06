<?php 
$page_title = "Login";
include '../includes/header.php'; 
?>

<div class="container" style="max-width: 450px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Login</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="../actions/login_action.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="john@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                Don't have an account? <a href="register.php">Register now</a><br>
                <a href="admin_register.php" style="color: var(--secondary); font-size: 0.8rem; margin-top: 0.5rem; display: inline-block;">Admin Registration</a> | 
                <a href="agent_login.php" style="color: var(--primary); font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; display: inline-block;">Agent Login Portal</a>
            </p>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
