<?php 
$page_title = "DoorstepCare - Join Our Team";
include '../includes/header.php'; 
?>

<div class="container">
    <div style="text-align: center; margin-bottom: 4rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Join the DoorstepCare Team</h1>
        <p style="color: var(--secondary); max-width: 600px; margin: 0 auto;">Help your community by assisting with government services. Become a certified doorstep agent and earn while you work.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
        <div>
            <div class="card" style="background: var(--primary); color: white; margin-bottom: 2rem;">
                <h3>Why join us?</h3>
                <ul style="list-style: none; padding: 0; margin-top: 1.5rem;">
                    <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle"></i> Flexible working hours</li>
                    <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle"></i> Professional training provided</li>
                    <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle"></i> Steady income per service booking</li>
                    <li><i class="fas fa-check-circle"></i> Impactful community work</li>
                </ul>
            </div>
            <img src="https://i.pinimg.com/736x/c7/72/51/c77251b05951cb34532ed7b7417b9f5b.jpg" alt="Join us" style="width: 100%; border-radius: 20px; box-shadow: var(--card-shadow);">
        </div>

        <div class="card">
            <h2 style="margin-bottom: 2rem;">Apply Now</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../actions/apply_agent.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="agent_name" class="form-control" required placeholder="John Doe">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="9876543210">
                    </div>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Professional Bio / Summary</label>
                    <textarea name="profile_summary" class="form-control" rows="2" placeholder="Tell us about your experience..."></textarea>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3" required placeholder="Your residence address"></textarea>
                </div>
                <div class="form-group">
                    <label>Preferred Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    <small style="color: var(--secondary);">You will use this to login once approved.</small>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Submit Application</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
