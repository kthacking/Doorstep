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

<div class="container" style="max-width: 900px;">
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2><?php echo __('confirm_booking'); ?></h2>
                <button id="voiceBtn" class="btn" style="background: var(--primary); color: white; border-radius: 50px; font-size: 0.8rem; padding: 8px 15px;">
                    <i class="fas fa-microphone"></i> <?php echo __('voice_booking'); ?>
                </button>
            </div>
            <p style="color: var(--secondary); margin-bottom: 2rem;">Confirm your service and preferred visit time.</p>
            
            <div style="padding: 1rem; background: var(--light); border-radius: 15px; margin-bottom: 2rem; border: 1px solid #e2e8f0;">
                <h3 style="font-size: 1rem; color: var(--primary);"><?php echo $service['service_name']; ?></h3>
                <p style="font-size: 0.9rem; color: var(--secondary);"><?php echo $service['description']; ?></p>
                <div style="margin-top: 1rem; font-weight: 700; color: var(--primary);">Amount: <?php echo CURRENCY . $service['service_charge']; ?></div>
            </div>

            <form id="bookingForm" action="../actions/booking_action.php" method="POST">
                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                <input type="hidden" name="total_amount" value="<?php echo $service['service_charge']; ?>">
                
                <div class="form-group">
                    <label><?php echo __('visit_date'); ?></label>
                    <input type="date" name="booking_date" id="visit_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>

                <div class="form-group">
                    <label><?php echo __('time_slot'); ?></label>
                    <select name="time_slot" id="time_slot" class="form-control" required>
                        <option value=""><?php echo __('time_slot'); ?></option>
                        <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                        <option value="12:00 PM - 03:00 PM">12:00 PM - 03:00 PM</option>
                        <option value="03:00 PM - 06:00 PM">03:00 PM - 06:00 PM</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo __('agent_gender'); ?> (Optional)</label>
                    <select name="preferred_gender" id="gender" class="form-control">
                        <option value="Any">Any Gender</option>
                        <option value="Male">Male Agent</option>
                        <option value="Female">Female Agent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?php echo __('visit_address'); ?></label>
                    <textarea name="address" id="address" class="form-control" rows="3" required><?php echo $user_address; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; font-weight: 700;"><?php echo __('confirm_booking'); ?></button>
            </form>
        </div>

        <div>
            <!-- Checklist Card -->
            <div class="card" style="margin-bottom: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                <h3 style="font-size: 1.1rem;"><?php echo __('service_checklist'); ?></h3>
                <p style="font-size: 0.8rem; margin-top: 0.5rem; margin-bottom: 1.5rem; color: var(--secondary);"><?php echo __('keep_ready'); ?></p>
                
                <ul style="list-style: none; padding: 0;">
                    <?php 
                    $docs = explode(',', $service['required_documents']);
                    foreach($docs as $doc): ?>
                        <li class="doc-rule-item" style="margin-bottom: 0.8rem; display: flex; align-items: flex-start; gap: 0.75rem; transition: 0.3s;">
                            <i class="fas fa-check-circle" style="color: #cbd5e1; margin-top: 3px;"></i>
                            <span style="font-size: 0.85rem; font-weight: 500;"><?php echo trim($doc); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Smart Reminder -->
            <div id="smartReminder" class="card" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; border: none; display: none;">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.9;"><?php echo __('reminder'); ?></h4>
                        <p id="reminderText" style="font-size: 0.85rem; font-weight: 600; margin-top: 2px;">Keep your original Aadhaar card ready for scanning.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Voice-Based Booking (Accessibility Enhancement)
const voiceBtn = document.getElementById('voiceBtn');
if ('webkitSpeechRecognition' in window) {
    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-IN';

    voiceBtn.onclick = () => {
        voiceBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <?php echo __('listening'); ?>';
        voiceBtn.style.background = 'var(--danger)';
        recognition.start();
    };

    recognition.onresult = (event) => {
        const text = event.results[0][0].transcript.toLowerCase();
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i> <?php echo __('voice_booking'); ?>';
        voiceBtn.style.background = 'var(--primary)';
        
        console.log('Voice Command:', text);
        
        // Smart Voice Mapping
        if (text.includes('next week')) {
            const date = new Date();
            date.setDate(date.getDate() + 7);
            document.getElementById('visit_date').value = date.toISOString().split('T')[0];
        } else if (text.includes('tomorrow')) {
            const date = new Date();
            date.setDate(date.getDate() + 1);
            document.getElementById('visit_date').value = date.toISOString().split('T')[0];
        }

        if (text.includes('morning')) document.getElementById('time_slot').value = '10:00 AM - 12:00 PM';
        if (text.includes('afternoon')) document.getElementById('time_slot').value = '12:00 PM - 03:00 PM';
        if (text.includes('evening')) document.getElementById('time_slot').value = '03:00 PM - 06:00 PM';
        
        if (text.includes('female')) document.getElementById('gender').value = 'Female';
        if (text.includes('male')) document.getElementById('gender').value = 'Male';

        alert('Voice recognized: "' + text + '". Data filled automatically.');
    };

    recognition.onerror = () => {
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i> <?php echo __('voice_booking'); ?>';
        voiceBtn.style.background = 'var(--primary)';
        alert('Voice recognition failed. Please try again or type manually.');
    };
} else {
    voiceBtn.style.display = 'none';
}

// Smart Reminders & Automated Document Rules
document.getElementById('bookingForm').onchange = function() {
    const reminder = document.getElementById('smartReminder');
    const reminderText = document.getElementById('reminderText');
    const date = document.getElementById('visit_date').value;
    
    if (date) {
        reminder.style.display = 'block';
        reminderText.innerText = "Visit scheduled for " + new Date(date).toLocaleDateString() + ". Keep all originals handy.";
        
        // Highlight Checklist
        document.querySelectorAll('.doc-rule-item i').forEach(icon => {
            icon.style.color = 'var(--success)';
        });
    }
};
</script>

<?php include '../includes/footer.php'; ?>
