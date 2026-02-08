<?php 
$page_title = "My Bookings";
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT b.*, s.service_name, a.agent_name, a.phone as agent_phone, a.profile_summary as agent_profile, a.gender as agent_gender 
                      FROM bookings b 
                      JOIN services s ON b.service_id = s.id 
                      LEFT JOIN agents a ON b.agent_id = a.id
                      WHERE b.user_id = ? 
                      ORDER BY b.created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <h1>Welcome back, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?></h1>
        <a href="../pages/services.php" class="btn btn-primary">Book New Service</a>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="card" style="text-align: center; padding: 5rem;">
            <i class="fas fa-calendar-times" style="font-size: 4rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
            <h3>No Bookings Found</h3>
            <p style="color: var(--secondary);">You haven't booked any doorstep services yet.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php foreach ($bookings as $booking): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                        <div>
                            <span style="font-size: 0.8rem; background: var(--primary); color: white; padding: 0.2rem 0.6rem; border-radius: 5px; text-transform: uppercase;">#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></span>
                            <h2 style="margin-top: 0.5rem;"><?php echo $booking['service_name']; ?></h2>
                            <p style="color: var(--secondary); font-size: 0.9rem;"><i class="fas fa-calendar-day"></i> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?> | <?php echo $booking['time_slot']; ?></p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);"><?php echo CURRENCY . $booking['total_amount']; ?></div>
                            <?php if ($booking['status'] === 'Cancelled'): ?>
                                <span style="background: #fef2f2; color: #dc2626; padding: 5px 12px; border-radius: 50px; font-weight: 700; font-size: 0.75rem; border: 1px solid #fecaca;"><?php echo strtoupper($booking['status']); ?></span>
                            <?php else: ?>
                                <span style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 5px 12px; border-radius: 50px; font-weight: 700; font-size: 0.75rem;"><?php echo strtoupper($booking['status']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php 
                    $is_cancelled = ($booking['status'] === 'Cancelled');
                    if (!$is_cancelled):
                        $statuses = ['Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed'];
                        $current_status_index = array_search($booking['status'], $statuses);
                    ?>
                    <div class="stepper">
                        <?php foreach ($statuses as $index => $status): 
                            $class = '';
                            if ($index < $current_status_index) $class = 'completed';
                            elseif ($index === $current_status_index) $class = 'active';
                        ?>
                            <div class="step <?php echo $class; ?>">
                                <div class="step-circle">
                                    <?php if ($index < $current_status_index): ?>
                                        <i class="fas fa-check"></i>
                                    <?php else: ?>
                                        <?php echo $index + 1; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label"><?php echo $status; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 1rem; border-radius: 12px; text-align: center; margin-bottom: 2rem;">
                            <h3 style="color: #dc2626; font-size: 1rem; margin-bottom: 0.2rem;">Booking Cancelled</h3>
                            <p style="color: #991b1b; font-size: 0.8rem;">This request has been cancelled.</p>
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 15px;">
                            <h4 style="font-size: 0.75rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem;">Latest Activity</h4>
                            <?php
                            $log_stmt = $pdo->prepare("SELECT * FROM status_logs WHERE booking_id = ? ORDER BY updated_at DESC LIMIT 1");
                            $log_stmt->execute([$booking['id']]);
                            $log = $log_stmt->fetch();
                            if ($log): ?>
                                <p style='font-size: 0.9rem;'><strong><?php echo $log['status']; ?>:</strong> <?php echo $log['remarks']; ?></p>
                                <span style='font-size: 0.7rem; color: #94a3b8;'><i class="far fa-clock"></i> <?php echo date('d M, H:i', strtotime($log['updated_at'])); ?></span>
                            <?php else: ?>
                                <p style="color: var(--secondary); font-size: 0.9rem;">No updates yet.</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($booking['agent_id']): ?>
                            <div class="agent-card" style="margin-top: 0; background: #eef2ff; border: 1px solid #c7d2fe; cursor: pointer;" onclick="showAgentProfile('<?php echo addslashes($booking['agent_name']); ?>', '<?php echo $booking['agent_gender']; ?>', '<?php echo addslashes($booking['agent_profile'] ?: 'Official verification agent.'); ?>', '<?php echo $booking['agent_phone']; ?>')">
                                <div class="agent-thumb">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--primary); font-weight: 700; text-transform: uppercase;">Assigned Professional</div>
                                    <div style="font-weight: 700;"><?php echo $booking['agent_name']; ?></div>
                                    <div style="font-size: 0.8rem; color: var(--secondary);"><i class="fas fa-phone"></i> <?php echo $booking['agent_phone']; ?></div>
                                </div>
                                <div style="margin-left: auto; color: var(--primary);"><i class="fas fa-chevron-right"></i></div>
                            </div>
                        <?php else: ?>
                            <div style="background: #fffbeb; padding: 1.5rem; border-radius: 15px; border: 1px solid #fde68a;">
                                <div style="color: #b45309; font-size: 0.85rem; font-weight: 600;">
                                    <i class="fas fa-hourglass-half"></i> Assigning Agent...
                                </div>
                                <p style="font-size: 0.8rem; color: #92400e; margin-top: 0.5rem;">We are finding the best professional near your location. Preference: <?php echo $booking['preferred_gender']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
                        <?php if (in_array($booking['status'], ['Booking Placed', 'Agent Assigned'])): ?>
                            <form action="../actions/cancel_booking.php" method="POST" onsubmit="return confirm('<?php echo __('cancel_confirm_msg'); ?>')">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <button type="submit" class="btn" style="background: #fee2e2; color: #dc2626; padding: 0.6rem 2rem; border: 1px solid #fecaca;"><?php echo __('cancel_booking'); ?></button>
                            </form>
                        <?php endif; ?>
                        <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary" style="padding: 0.6rem 2rem;">Track Progress & Full Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
<?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Agent Profile Modal -->
<div id="profileModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 400px; width: 90%; text-align: center; position: relative;">
        <button onclick="closeModal()" style="position: absolute; top: 1rem; right: 1rem; border: none; background: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        <div class="agent-thumb" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 1.5rem;">
            <i class="fas fa-user-tie"></i>
        </div>
        <h2 id="modalName">Agent Name</h2>
        <p id="modalGender" style="color: var(--primary); font-weight: 600; font-size: 0.9rem; margin-bottom: 1rem;">Gender</p>
        <p id="modalBio" style="color: var(--secondary); font-size: 0.9rem; line-height: 1.5; margin-bottom: 2rem;">Profile summary goes here.</p>
        <a id="modalCall" href="#" class="btn btn-primary" style="width: 100%;"><i class="fas fa-phone"></i> Contact Agent</a>
    </div>
</div>

<script>
function showAgentProfile(name, gender, bio, phone) {
    document.getElementById('modalName').innerText = name;
    document.getElementById('modalGender').innerText = gender + " Agent";
    document.getElementById('modalBio').innerText = bio;
    document.getElementById('modalCall').href = "tel:" + phone;
    document.getElementById('profileModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('profileModal').style.display = 'none';
}
// Close on outside click
window.onclick = function(event) {
    if (event.target == document.getElementById('profileModal')) closeModal();
}
</script>

<?php include '../includes/footer.php'; ?>
