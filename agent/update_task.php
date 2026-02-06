<?php 
$page_title = "Update Progress";
include '../includes/header.php'; 

if (!isset($_SESSION['agent_id'])) {
    header("Location: ../pages/agent_login.php");
    exit();
}

$booking_id = $_GET['id'] ?? null;
$agent_id = $_SESSION['agent_id'];

$stmt = $pdo->prepare("SELECT b.*, u.full_name as user_name, s.service_name 
                      FROM bookings b 
                      JOIN users u ON b.user_id = u.id 
                      JOIN services s ON b.service_id = s.id 
                      WHERE b.id = ? AND b.agent_id = ?");
$stmt->execute([$booking_id, $agent_id]);
$task = $stmt->fetch();

if (!$task) {
    echo "<div class='container card'><h3>Unauthorized or Invalid Task</h3></div>";
    include '../includes/footer.php';
    exit();
}
?>

<div class="container" style="max-width: 550px; margin-top: 4rem;">
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" style="text-decoration: none; color: var(--secondary); font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
            <i class="fas fa-arrow-left"></i> Back to assignments
        </a>
    </div>
    
    <div class="card" style="padding: 2.5rem; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);">
        <h2 style="font-size: 1.6rem; margin-bottom: 0.5rem;">Update Status</h2>
        <p style="color: var(--secondary); font-size: 0.9rem; margin-bottom: 2.5rem;">
            Update progress for <span style="color: var(--dark); font-weight: 700;"><?php echo $task['user_name']; ?></span> regarding <span style="color: var(--primary); font-weight: 700;"><?php echo $task['service_name']; ?></span>.
        </p>

        <form action="../actions/agent_update_action.php" method="POST">
            <input type="hidden" name="booking_id" value="<?php echo $task['id']; ?>">
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary); margin-bottom: 0.8rem; display: block;">Service Stage</label>
                <div style="position: relative;">
                    <select name="status" class="form-control" required style="appearance: none; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.8rem 1rem; border-radius: 12px; font-weight: 600;">
                        <option value="Agent Assigned" <?php echo $task['status'] == 'Agent Assigned' ? 'selected' : ''; ?>>Agent Assigned</option>
                        <option value="En Route" <?php echo $task['status'] == 'En Route' ? 'selected' : ''; ?>>Traveling to Location</option>
                        <option value="Arrived" <?php echo $task['status'] == 'Arrived' ? 'selected' : ''; ?>>Arrived at Doorstep</option>
                        <option value="Documents Collected" <?php echo $task['status'] == 'Documents Collected' ? 'selected' : ''; ?>>Documents Collected</option>
                        <option value="Application Submitted" <?php echo $task['status'] == 'Application Submitted' ? 'selected' : ''; ?>>Application Submitted</option>
                        <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>>Service Completed</option>
                    </select>
                    <i class="fas fa-chevron-down" style="position: absolute; right: 1rem; top: 1.1rem; color: var(--secondary); pointer-events: none;"></i>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary); margin-bottom: 0.8rem; display: block;">Notes for Client</label>
                <textarea name="remarks" class="form-control" rows="4" placeholder="Update the customer on what's happening..." required style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem;"></textarea>
            </div>

            <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 1rem; border-radius: 12px; margin-bottom: 2.5rem; display: flex; gap: 0.8rem; align-items: flex-start;">
                <i class="fas fa-info-circle" style="color: #d97706; margin-top: 2px;"></i>
                <p style="font-size: 0.8rem; color: #92400e; line-height: 1.5; font-weight: 500;">
                    Changes will be reflected on the user's dashboard immediately and a notification will be sent.
                </p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 700; border-radius: 12px; font-size: 0.95rem; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">Update Timeline</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
