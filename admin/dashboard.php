<?php 
$page_title = "Admin Dashboard";
include '../includes/header.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

// Stats
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status != 'Completed'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM bookings WHERE status = 'Completed'")->fetchColumn();
$pending_agents = $pdo->query("SELECT COUNT(*) FROM agents WHERE status = 'pending'")->fetchColumn();

// Recent Notifications
$notifications = $pdo->query("SELECT * FROM notifications WHERE user_type = 'admin' ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent Bookings
$stmt = $pdo->query("SELECT b.*, u.full_name, s.service_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.id 
                    JOIN services s ON b.service_id = s.id 
                    ORDER BY b.created_at DESC LIMIT 10");
$bookings = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Admin Command Center</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="register.php" class="btn" style="background: white; color: var(--primary); border: 1px solid var(--primary);">Add Admin Account</a>
            <a href="manage_services.php" class="btn" style="background: white; color: var(--primary); border: 1px solid var(--primary);">Manage Services</a>
            <a href="manage_agents.php" class="btn" style="background: white; color: var(--primary); border: 1px solid var(--primary);">Manage Agents</a>
        </div>
    </div>

    <!-- Notifications Section -->
    <?php if (!empty($notifications)): ?>
    <div class="card" style="margin-bottom: 2rem; border-left: 5px solid var(--primary); padding: 1.5rem;">
        <h4 style="margin-bottom: 1rem; color: var(--secondary); font-size: 0.8rem; text-transform: uppercase;">Recent Alerts</h4>
        <?php foreach ($notifications as $n): ?>
            <div style="font-size: 0.9rem; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <span><i class="fas fa-bell" style="color: var(--primary); margin-right: 0.5rem;"></i> <?php echo $n['message']; ?></span>
                <span style="font-size: 0.75rem; color: #94a3b8;"><?php echo date('H:i', strtotime($n['created_at'])); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="card" style="padding: 1.5rem;">
            <div style="color: var(--secondary); font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Total Bookings</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo $total_bookings; ?></div>
        </div>
        <div class="card" style="padding: 1.5rem;">
            <div style="color: var(--secondary); font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Active Tasks</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--warning);"><?php echo $pending_bookings; ?></div>
        </div>
        <div class="card" style="padding: 1.5rem; <?php echo $pending_agents > 0 ? 'border: 2px solid var(--danger);' : ''; ?>">
            <div style="color: var(--secondary); font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Pending Agents</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--danger);"><?php echo $pending_agents; ?></div>
            <?php if($pending_agents > 0): ?>
                <a href="manage_agents.php" style="font-size: 0.75rem; color: var(--danger);">Review applicants &rarr;</a>
            <?php endif; ?>
        </div>
        <div class="card" style="padding: 1.5rem;">
            <div style="color: var(--secondary); font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Revenue</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--success);"><?php echo CURRENCY . (float)$total_revenue; ?></div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1.5rem;">Step-by-Step Tracker</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9;">
                        <th style="padding: 1rem;">ID</th>
                        <th style="padding: 1rem;">Customer</th>
                        <th style="padding: 1rem;">Service</th>
                        <th style="padding: 1rem;">Agent Assigned</th>
                        <th style="padding: 1rem;">Status</th>
                        <th style="padding: 1rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">#<?php echo str_pad($b['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td style="padding: 1rem;"><?php echo $b['full_name']; ?></td>
                            <td style="padding: 1rem; font-weight: 600;"><?php echo $b['service_name']; ?></td>
                            <td style="padding: 1rem; font-size: 0.85rem;">
                                <?php 
                                $ag_stmt = $pdo->prepare("SELECT agent_name FROM agents WHERE id = ?");
                                $ag_stmt->execute([$b['agent_id']]);
                                echo $ag_stmt->fetchColumn() ?: '<span style="color: var(--danger);">Unassigned</span>';
                                ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 5px; background: <?php echo $b['status'] == 'Completed' ? 'rgba(34,197,94,0.1)' : 'rgba(245,158,11,0.1)'; ?>; color: <?php echo $b['status'] == 'Completed' ? 'var(--success)' : 'var(--warning)'; ?>;">
                                    <?php echo $b['status']; ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <a href="manage_booking.php?id=<?php echo $b['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: var(--primary); color: white;">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
