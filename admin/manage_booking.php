<?php 
$page_title = "Manage Booking";
include '../includes/header.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("SELECT b.*, u.full_name, u.phone, u.email as u_email, s.service_name 
                      FROM bookings b 
                      JOIN users u ON b.user_id = u.id 
                      JOIN services s ON b.service_id = s.id 
                      WHERE b.id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

$agents = $pdo->query("SELECT * FROM agents WHERE status = 'active'")->fetchAll();
?>

<div class="container" style="max-width: 900px;">
    <a href="dashboard.php" style="text-decoration: none; color: var(--secondary); margin-bottom: 2rem; display: inline-block;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div class="card">
            <h3>Booking Information</h3>
            <hr style="margin: 1rem 0; opacity: 0.1;">
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; color: var(--secondary); text-transform: uppercase;">Service</label>
                <div style="font-weight: 600;"><?php echo $booking['service_name']; ?></div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; color: var(--secondary); text-transform: uppercase;">Customer</label>
                <div style="font-weight: 600;"><?php echo $booking['full_name']; ?></div>
                <div style="font-size: 0.85rem; color: var(--secondary);"><?php echo $booking['phone']; ?> | <?php echo $booking['u_email']; ?></div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; color: var(--secondary); text-transform: uppercase;">Visit Schedule</label>
                <div style="font-weight: 600;"><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></div>
                <div style="font-size: 0.85rem;"><?php echo $booking['time_slot']; ?></div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; color: var(--secondary); text-transform: uppercase;">Address</label>
                <div style="font-size: 0.9rem;"><?php echo $booking['address_confirmed']; ?></div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; color: var(--secondary); text-transform: uppercase;">Agent Preference</label>
                <div style="font-size: 0.9rem; font-weight: 600; color: var(--primary);"><?php echo $booking['preferred_gender']; ?> Agent</div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.75rem; color: var(--secondary); text-transform: uppercase;">Doc Confirmation</label>
                <div style="display: flex; gap: 0.5rem; margin-top: 5px;">
                    <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: <?php echo $booking['user_doc_confirmed'] ? 'var(--success)' : '#e2e8f0'; ?>; color: <?php echo $booking['user_doc_confirmed'] ? 'white' : '#64748b'; ?>;">User: <?php echo $booking['user_doc_confirmed'] ? 'YES' : 'NO'; ?></span>
                    <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: <?php echo $booking['agent_doc_confirmed'] ? 'var(--success)' : '#e2e8f0'; ?>; color: <?php echo $booking['agent_doc_confirmed'] ? 'white' : '#64748b'; ?>;">Agent: <?php echo $booking['agent_doc_confirmed'] ? 'YES' : 'NO'; ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Action Center</h3>
            <hr style="margin: 1rem 0; opacity: 0.1;">
            
            <form action="../actions/update_booking.php" method="POST">
                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                
                <div class="form-group">
                    <label>Assign Agent</label>
                    <select name="agent_id" class="form-control">
                        <option value="">Select an Agent</option>
                        <?php foreach($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>" <?php echo $booking['agent_id'] == $agent['id'] ? 'selected' : ''; ?>>
                                <?php echo $agent['agent_name']; ?> (<?php echo $agent['phone']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Update Status</label>
                    <select name="status" class="form-control" required>
                        <?php 
                        $statuses = ['Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed', 'Cancelled'];
                        foreach($statuses as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $booking['status'] == $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Internal Remarks / Update for User</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Explain the current progress..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Booking</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h3>Status History</h3>
        <hr style="margin: 1rem 0; opacity: 0.1;">
        <?php
        $logs = $pdo->prepare("SELECT * FROM status_logs WHERE booking_id = ? ORDER BY updated_at DESC");
        $logs->execute([$booking_id]);
        while($log = $logs->fetch()): ?>
            <div style="padding: 1rem; border-left: 3px solid var(--primary); background: #f8fafc; margin-bottom: 1rem; border-radius: 0 10px 10px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: var(--primary);"><?php echo $log['status']; ?></strong>
                    <span style="font-size: 0.75rem; color: var(--secondary);"><?php echo date('d M Y, H:i', strtotime($log['updated_at'])); ?></span>
                </div>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;"><?php echo $log['remarks']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
