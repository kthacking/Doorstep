<?php 
$page_title = "Manage Agents";
include '../includes/header.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

// Handle Auto-approve toggle
if (isset($_GET['toggle_auto'])) {
    $current = $_GET['toggle_auto'] == '1' ? '0' : '1';
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'auto_approve_agents'");
    $stmt->execute([$current]);
    header("Location: manage_agents.php");
    exit();
}

$auto_approve = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_agents'")->fetchColumn();
$agents = $pdo->query("SELECT * FROM agents ORDER BY status DESC, applied_at DESC")->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Service Professionals & Applicants</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="?toggle_auto=<?php echo $auto_approve; ?>" class="btn" style="background: <?php echo $auto_approve == '1' ? 'var(--success)' : 'var(--secondary)'; ?>; color: white;">
                Auto-Approve: <?php echo $auto_approve == '1' ? 'ON' : 'OFF'; ?>
            </a>
            <button onclick="toggleForm()" class="btn btn-primary">Add Manually</button>
        </div>
    </div>

    <!-- Add Agent Form -->
    <div id="addAgentForm" class="card" style="display: none; margin-bottom: 3rem; background: #f8fafc;">
        <h3>Add New Professional</h3>
        <form action="../actions/agent_action.php" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                <div class="form-group">
                    <label>Agent Name</label>
                    <input type="text" name="agent_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Save Agent</button>
            <button type="button" onclick="toggleForm()" class="btn" style="background: transparent; color: var(--secondary);">Cancel</button>
        </form>
    </div>

    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9;">
                    <th style="padding: 1rem; text-align: left;">Agent Details</th>
                    <th style="padding: 1rem; text-align: left;">Address</th>
                    <th style="padding: 1rem; text-align: left;">Status</th>
                    <th style="padding: 1rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $a): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: <?php echo $a['status'] == 'pending' ? 'rgba(239, 68, 68, 0.05)' : ''; ?>">
                        <td style="padding: 1rem;">
                            <strong><?php echo $a['agent_name']; ?></strong>
                            <div style="font-size: 0.8rem; color: var(--secondary);"><?php echo $a['phone']; ?> | <?php echo $a['email']; ?></div>
                            <div style="font-size: 0.7rem; color: #94a3b8;">Applied: <?php echo date('d M Y', strtotime($a['applied_at'])); ?></div>
                        </td>
                        <td style="padding: 1rem; font-size: 0.85rem; color: var(--secondary); max-width: 250px;">
                            <?php echo $a['address'] ?: 'N/A'; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?php 
                            $bg = 'rgba(100, 116, 139, 0.1)'; $clr = 'var(--secondary)';
                            if($a['status'] == 'active') { $bg = 'rgba(34, 197, 94, 0.1)'; $clr = 'var(--success)'; }
                            if($a['status'] == 'pending') { $bg = 'rgba(245, 158, 11, 0.1)'; $clr = 'var(--warning)'; }
                            ?>
                            <span style="font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 5px; background: <?php echo $bg; ?>; color: <?php echo $clr; ?>; text-transform: uppercase; font-weight: 700;">
                                <?php echo $a['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <?php if($a['status'] == 'pending'): ?>
                                <a href="../actions/agent_action.php?approve=<?php echo $a['id']; ?>" class="btn" style="background: var(--success); color: white; padding: 0.3rem 0.8rem; font-size: 0.8rem; border-radius: 5px;">Approve</a>
                            <?php endif; ?>
                            <a href="javascript:void(0)" onclick="openPassModal(<?php echo $a['id']; ?>, '<?php echo $a['agent_name']; ?>')" style="color: var(--primary); font-size: 0.9rem; margin-left: 1rem;"><i class="fas fa-key"></i></a>
                            <a href="../actions/agent_action.php?delete=<?php echo $a['id']; ?>" style="color: var(--danger); font-size: 0.9rem; margin-left: 1rem;" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passModal" class="modal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.5);">
    <div style="background: white; width: 400px; margin: 15% auto; padding: 2rem; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="modalAgentName">Change Password</h3>
        <form action="../actions/agent_action.php" method="POST" style="margin-top: 1.5rem;">
            <input type="hidden" name="agent_id" id="modalAgentId">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
            </div>
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" name="change_password" class="btn btn-primary" style="flex: 1;">Update Password</button>
                <button type="button" onclick="closePassModal()" class="btn" style="flex: 1; background: #f1f5f9;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('addAgentForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function openPassModal(id, name) {
    document.getElementById('modalAgentId').value = id;
    document.getElementById('modalAgentName').innerText = "Update Password: " + name;
    document.getElementById('passModal').style.display = 'block';
}

function closePassModal() {
    document.getElementById('passModal').style.display = 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
