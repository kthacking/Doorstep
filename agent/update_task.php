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

<div class="container" style="max-width: 600px;">
    <a href="dashboard.php" style="text-decoration: none; color: var(--secondary); margin-bottom: 2rem; display: inline-block;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    
    <div class="card">
        <h2>Update Service Progress</h2>
        <p style="color: var(--secondary); margin-bottom: 2rem;">Task: <?php echo $task['service_name']; ?> for <?php echo $task['user_name']; ?></p>

        <form action="../actions/agent_update_action.php" method="POST">
            <input type="hidden" name="booking_id" value="<?php echo $task['id']; ?>">
            
            <div class="form-group">
                <label>Update Status to:</label>
                <select name="status" class="form-control" required>
                    <option value="Agent Assigned" <?php echo $task['status'] == 'Agent Assigned' ? 'selected' : ''; ?>>Agent Assigned</option>
                    <option value="En Route" <?php echo $task['status'] == 'En Route' ? 'selected' : ''; ?>>En Route (Traveling to location)</option>
                    <option value="Arrived" <?php echo $task['status'] == 'Arrived' ? 'selected' : ''; ?>>Arrived at Doorstep</option>
                    <option value="Documents Collected" <?php echo $task['status'] == 'Documents Collected' ? 'selected' : ''; ?>>Documents Collected & Verified</option>
                    <option value="Application Submitted" <?php echo $task['status'] == 'Application Submitted' ? 'selected' : ''; ?>>Application Submitted to Gov</option>
                    <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>>Completed (Service Delivered)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes / Feedback (Visible to User)</label>
                <textarea name="remarks" class="form-control" rows="4" placeholder="e.g. Arrived at location. Collecting documents now..." required></textarea>
            </div>

            <div style="background: rgba(245, 158, 11, 0.1); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--warning);">
                <i class="fas fa-exclamation-triangle"></i> Updating status will notify the user immediately.
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Information</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
