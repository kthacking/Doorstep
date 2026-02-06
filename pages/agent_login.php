<?php 
$page_title = "Agent Login";
include '../includes/header.php'; 
?>

<div class="container" style="max-width: 450px;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Agent Portal Login</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="../actions/agent_login_action.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="agent@doorstep.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login as Agent</button>
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                Not an agent yet? <a href="doorstepcare.php">Apply at DoorstepCare</a>
            </p>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
