<?php 
$page_title = "My Profile";
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="container" style="max-width: 600px;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 80px; height: 80px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem;">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h2>Member Profile</h2>
            <p style="color: var(--secondary);">Manage your personal information</p>
        </div>

        <form action="../actions/profile_update.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address (Cannot be changed)</label>
                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
            </div>
            <div class="form-group">
                <label>Permanent Address</label>
                <textarea name="address" class="form-control" rows="3" required><?php echo $user['address']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Information</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
