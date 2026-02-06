<?php 
$page_title = "Register New Admin";
include '../includes/header.php'; 

// Only existing admins can register new admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}
?>

<div class="container" style="max-width: 500px;">
    <a href="dashboard.php" style="text-decoration: none; color: var(--secondary); margin-bottom: 2rem; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 2rem;">Register New Admin</h2>
        
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

        <form action="../actions/admin_register_action.php" method="POST">
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
                <label>Office Address / Branch</label>
                <textarea name="address" class="form-control" rows="3" required placeholder="Admin office location"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Admin Account</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
