<?php 
$page_title = "Agent Dashboard";
include '../includes/header.php'; 

if (!isset($_SESSION['agent_id'])) {
    header("Location: ../pages/agent_login.php");
    exit();
}

$agent_id = $_SESSION['agent_id'];

// Get assigned bookings
$stmt = $pdo->prepare("SELECT b.*, u.full_name as user_name, u.phone as user_phone, u.address as user_address, s.service_name 
                      FROM bookings b 
                      JOIN users u ON b.user_id = u.id 
                      JOIN services s ON b.service_id = s.id 
                      WHERE b.agent_id = ? 
                      ORDER BY b.booking_date ASC");
$stmt->execute([$agent_id]);
$assignments = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1><?php echo __('agent_portal'); ?>: <?php echo $_SESSION['agent_name']; ?></h1>
        <div style="background: var(--success); color: white; padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700;">
            <i class="fas fa-check-circle"></i> <?php echo __('online_active'); ?>
        </div>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="card" style="text-align: center; padding: 4rem;">
            <i class="fas fa-clipboard-list" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
            <h3><?php echo __('no_assignments'); ?></h3>
            <p style="color: var(--secondary);"><?php echo __('no_assignments_desc'); ?></p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach ($assignments as $task): ?>
                <div class="card" style="border-left: 4px solid var(--primary); padding: 1.5rem; background: white;">
                    <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 2rem;">
                        <div>
                            <div style="display: flex; gap: 0.8rem; align-items: center; margin-bottom: 0.8rem;">
                                <span style="background: var(--primary); color: white; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700;">#<?php echo str_pad($task['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                <h2 style="font-size: 1.25rem;"><?php echo $task['user_name']; ?></h2>
                                <?php if ($task['preferred_gender'] !== 'Any'): ?>
                                    <span style="font-size: 0.65rem; background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Pref: <?php echo $task['preferred_gender']; ?> Agent</span>
                                <?php endif; ?>
                            </div>

                            <div style="margin-bottom: 1rem;">
                                <p style="font-weight: 600; color: var(--primary); font-size: 0.9rem;"><i class="fas fa-file-contract"></i> <?php echo $task['service_name']; ?></p>
                                <p style="color: var(--secondary); font-size: 0.8rem; margin-top: 0.3rem;"><i class="fas fa-map-marker-alt"></i> <?php echo $task['address_confirmed']; ?></p>
                                <p style="color: var(--secondary); font-size: 0.8rem;"><i class="fas fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($task['booking_date'])); ?> @ <?php echo $task['time_slot']; ?></p>
                            </div>

                            <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary);"><?php echo __('doc_checklist'); ?></h4>
                                    <button onclick="addDoc(<?php echo $task['id']; ?>)" class="btn" style="padding: 2px 8px; font-size: 0.65rem; background: var(--primary); color: white;">+ <?php echo __('add_item'); ?></button>
                                </div>
                                <div id="checklist-<?php echo $task['id']; ?>" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem;">
                                    <?php 
                                    $docs = explode(',', $task['dynamic_checklist']);
                                    foreach($docs as $doc): 
                                        if (empty(trim($doc))) continue;
                                    ?>
                                        <div class="checklist-item">
                                            <i class="far fa-circle checklist-icon" style="color: var(--primary);" onclick="markDone(this)"></i>
                                            <span><?php echo trim($doc); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div style="border-left: 1px solid #f1f5f9; padding-left: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.8rem; letter-spacing: 0.05em;"><?php echo __('contact'); ?></h4>
                                <div class="dual-action-btn-container">
                                    <a href="tel:<?php echo $task['user_phone']; ?>" class="dual-action-btn call"><i class="fas fa-phone"></i> <?php echo __('call'); ?></a>
                                    <a href="https://wa.me/<?php echo $task['user_phone']; ?>" target="_blank" class="dual-action-btn chat"><i class="fab fa-whatsapp"></i> <?php echo __('chat'); ?></a>
                                </div>

                                <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.5rem; letter-spacing: 0.05em;"><?php echo __('status'); ?></h4>
                                <?php 
                                $status = $task['status'];
                                $steps = [__('step_mini_0'), __('step_mini_1'), __('step_mini_2'), __('step_mini_3'), __('step_mini_4')];
                                $current_step = 0;
                                if ($status == 'Agent Assigned') $current_step = 0;
                                elseif ($status == 'En Route' || $status == 'Arrived') $current_step = 1;
                                elseif ($status == 'Documents Collected') $current_step = 2;
                                elseif ($status == 'Application Submitted') $current_step = 3;
                                elseif ($status == 'Completed') $current_step = 4;
                                ?>
                                <div class="progress-stepper-mini">
                                    <?php foreach ($steps as $idx => $s_name): ?>
                                        <div class="progress-step-mini <?php echo ($idx < $current_step) ? 'completed' : (($idx == $current_step) ? 'active' : ''); ?>">
                                            <span class="progress-label-mini"><?php echo $s_name; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.8rem; letter-spacing: 0.05em;">Handover Process</h4>
                                <?php if($task['agent_doc_confirmed']): ?>
                                    <div class="document-badge" style="width: 100%; justify-content: center; background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 0.5rem; border-radius: 8px;">
                                        <i class="fas fa-check-circle"></i> Handover Accepted
                                    </div>
                                <?php else: ?>
                                    <form action="../actions/confirm_docs.php" method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $task['id']; ?>">
                                        <input type="hidden" name="user_type" value="agent">
                                    <form action="../actions/confirm_docs.php" method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $task['id']; ?>">
                                        <input type="hidden" name="user_type" value="agent">
                                        <button type="submit" class="btn" style="background: var(--warning); color: white; width: 100%; font-size: 0.75rem; padding: 0.4rem; border-radius: 8px; font-weight: 700;"><?php echo __('agent_acceptance'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; margin-top: auto;">
                                <a href="update_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary" style="text-align: center; padding: 0.5rem; font-size: 0.75rem;"><?php echo __('update_task_status'); ?></a>
                                <a href="generate_receipt.php?id=<?php echo $task['id']; ?>" class="btn" style="background: #f8fafc; border: 1px solid #e2e8f0; color: var(--secondary); text-align: center; padding: 0.5rem; font-size: 0.75rem;"><i class="fas fa-file-pdf"></i> Receipt</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function markDone(el) {
    const isDone = el.classList.contains('fa-check-circle');
    const parent = el.closest('.checklist-item');
    
    if (!isDone) {
        el.classList.replace('fa-circle', 'fa-check-circle');
        el.classList.replace('far', 'fas');
        el.style.color = 'var(--success)';
        parent.style.opacity = '0.6';
        parent.style.background = '#f8fafc';
    } else {
        el.classList.replace('fa-check-circle', 'fa-circle');
        el.classList.replace('fas', 'far');
        el.style.color = 'var(--primary)';
        parent.style.opacity = '1';
        parent.style.background = 'white';
    }
}

function addDoc(bookingId) {
    const item = prompt("Enter additional document name:");
    if (item) {
        const container = document.getElementById('checklist-' + bookingId);
        const div = document.createElement('div');
        div.className = 'checklist-item';
        div.innerHTML = `<i class="far fa-circle checklist-icon" style="color: var(--primary);" onclick="markDone(this)"></i> <span>${item}</span>`;
        container.appendChild(div);

        // Update Backend
        fetch('../actions/agent_update_checklist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `booking_id=${bookingId}&item=${encodeURIComponent(item)}`
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
