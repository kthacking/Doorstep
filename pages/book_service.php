<?php 
$page_title = "Book Service";
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to book a service.";
    header("Location: login.php");
    exit();
}

$service_id = $_GET['id'] ?? null;
if (!$service_id) {
    header("Location: services.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: services.php");
    exit();
}

// Fetch user default address
$user_stmt = $pdo->prepare("SELECT address FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user_address = $user_stmt->fetchColumn();
?>

<div class="container" style="max-width: 800px;">
    <div class="card" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <h2>Booking Details</h2>
            <p style="color: var(--secondary); margin-bottom: 2rem;">Confirm your service and preferred visit time.</p>
            
            <div style="padding: 1rem; background: var(--light); border-radius: 15px; margin-bottom: 2rem;">
                <h3 style="font-size: 1rem;"><?php echo $service['service_name']; ?></h3>
                <p style="font-size: 0.9rem; color: var(--secondary);"><?php echo $service['description']; ?></p>
                <div style="margin-top: 1rem; font-weight: 700; color: var(--primary);">Amount: <?php echo CURRENCY . $service['service_charge']; ?></div>
            </div>

            <form action="../actions/booking_action.php" method="POST">
                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                <input type="hidden" name="total_amount" value="<?php echo $service['service_charge']; ?>">
                
                <div class="form-group">
                    <label>Preferred Visit Date</label>
                    <input type="date" name="booking_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>

                <div class="form-group">
                    <label>Preferred Time Slot</label>
                    <select name="time_slot" class="form-control" required>
                        <option value="">Select a slot</option>
                        <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                        <option value="12:00 PM - 03:00 PM">12:00 PM - 03:00 PM</option>
                        <option value="03:00 PM - 06:00 PM">03:00 PM - 06:00 PM</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Preferred Agent Gender (Optional)</label>
                    <select name="preferred_gender" class="form-control">
                        <option value="Any">Any Gender</option>
                        <option value="Male">Male Agent</option>
                        <option value="Female">Female Agent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Visit Address</label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo $user_address; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm Booking</button>
            </form>
        </div>

        <div style="background: var(--light); padding: 2rem; border-radius: 15px; border-left: 4px solid var(--primary);">
            <h3>Checklist</h3>
            <p style="font-size: 0.9rem; margin-top: 0.5rem; margin-bottom: 2rem;">Please keep these original documents ready for the agent visit.</p>
            
            <ul style="list-style: none; padding: 0;">
                <?php 
                $docs = explode(',', $service['required_documents']);
                foreach($docs as $doc): ?>
                    <li style="margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 0.75rem;">
                        <i class="fas fa-file-alt" style="color: var(--primary); margin-top: 3px;"></i>
                        <span><?php echo trim($doc); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div style="margin-top: 3rem; background: rgba(99, 102, 241, 0.1); padding: 1rem; border-radius: 10px; font-size: 0.85rem;">
                <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                Our agent will verify these documents and scan them on the spot. No need to visit any office.
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
