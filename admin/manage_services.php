<?php 
$page_title = "Manage Services";
include '../includes/header.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

$services = $pdo->query("SELECT * FROM services ORDER BY service_name ASC")->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Manage Services</h1>
        <button onclick="toggleForm()" class="btn btn-primary">Add New Service</button>
    </div>

    <!-- Add Service Form (Hidden by default) -->
    <div id="addServiceForm" class="card" style="display: none; margin-bottom: 3rem; background: #f8fafc;">
        <h3>Add New Service</h3>
        <form action="../actions/service_action.php" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" name="service_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Charge (<?php echo CURRENCY; ?>)</label>
                    <input type="number" name="charge" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Estimated Days</label>
                    <input type="number" name="days" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Required Documents (Comma separated)</label>
                    <input type="text" name="documents" class="form-control" placeholder="Aadhaar, Photo, etc." required>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" required></textarea>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Save Service</button>
            <button type="button" onclick="toggleForm()" class="btn" style="background: transparent; color: var(--secondary);">Cancel</button>
        </form>
    </div>

    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9;">
                    <th style="padding: 1rem; text-align: left;">Name</th>
                    <th style="padding: 1rem; text-align: left;">Documents</th>
                    <th style="padding: 1rem; text-align: left;">Charge</th>
                    <th style="padding: 1rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem;"><strong><?php echo $s['service_name']; ?></strong></td>
                        <td style="padding: 1rem; font-size: 0.8rem; color: var(--secondary);"><?php echo $s['required_documents']; ?></td>
                        <td style="padding: 1rem;"><?php echo CURRENCY . $s['service_charge']; ?></td>
                        <td style="padding: 1rem; text-align: right;">
                            <a href="../actions/service_action.php?delete=<?php echo $s['id']; ?>" style="color: var(--danger); font-size: 0.9rem;" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('addServiceForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
