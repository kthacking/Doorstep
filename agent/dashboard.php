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
        <h1>Agent Portal: <?php echo $_SESSION['agent_name']; ?></h1>
        <div style="background: var(--success); color: white; padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700;">
            <i class="fas fa-check-circle"></i> Online & Active
        </div>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="card" style="text-align: center; padding: 4rem;">
            <i class="fas fa-clipboard-list" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 1rem;"></i>
            <h3>No Assignments</h3>
            <p style="color: var(--secondary);">You don't have any active doorstep visits assigned yet.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 1.5rem;">
            <?php foreach ($assignments as $task): ?>
                <div class="card" style="border-left: 4px solid var(--primary); padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem;">
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

                            <div style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                                    <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary);">Document Checklist</h4>
                                    <button onclick="addDoc(<?php echo $task['id']; ?>)" class="btn" style="padding: 2px 8px; font-size: 0.65rem; background: var(--primary); color: white;">+ Add Item</button>
                                </div>
                                <div id="checklist-<?php echo $task['id']; ?>" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem;">
                                    <?php 
                                    $docs = explode(',', $task['dynamic_checklist']);
                                    foreach($docs as $doc): 
                                        if (empty(trim($doc))) continue;
                                    ?>
                                        <div style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                            <i class="far fa-circle" style="color: var(--primary); cursor: pointer;" onclick="markDone(this)"></i>
                                            <span><?php echo trim($doc); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div style="border-left: 1px solid #f1f5f9; padding-left: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 0.8rem;">Contact User</h4>
                                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                                    <a href="tel:<?php echo $task['user_phone']; ?>" class="btn" style="background: #e2e8f0; color: var(--dark); flex: 1; text-align: center; padding: 0.4rem;"><i class="fas fa-phone"></i> Call</a>
                                    <a href="https://wa.me/<?php echo $task['user_phone']; ?>" target="_blank" class="btn" style="background: #25D366; color: white; flex: 1; text-align: center; padding: 0.4rem;"><i class="fab fa-whatsapp"></i> Chat</a>
                                </div>

                                <label style="font-size: 0.7rem; font-weight: 700; display: block; margin-bottom: 0.4rem; color: var(--secondary);">Current Status</label>
                                <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 700; width: 100%; display: block; text-align: center; font-size: 0.75rem; margin-bottom: 1.5rem;">
                                    <?php echo $task['status']; ?>
                                </span>
                            </div>

                            <div style="margin-bottom: 2rem;">
                                <label style="font-size: 0.7rem; font-weight: 700; display: block; margin-bottom: 0.4rem; color: var(--secondary);">Document Handover</label>
                                <?php if($task['agent_doc_confirmed']): ?>
                                    <span style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 700; width: 100%; display: block; text-align: center; font-size: 0.75rem;">
                                        <i class="fas fa-check-circle"></i> Handover Accepted
                                    </span>
                                <?php else: ?>
                                    <form action="../actions/confirm_docs.php" method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $task['id']; ?>">
                                        <input type="hidden" name="user_type" value="agent">
                                        <button type="submit" class="btn" style="background: var(--warning); color: white; width: 100%; font-size: 0.75rem; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 700;">Confirm Receipt of Docs</button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div style="display: grid; gap: 0.4rem;">
                                <a href="update_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary" style="text-align: center;">Update Progress</a>
                                <a href="generate_receipt.php?id=<?php echo $task['id']; ?>" class="btn" style="background: white; border: 1px solid var(--primary); color: var(--primary); text-align: center;"><i class="fas fa-file-pdf"></i> Receipt</a>
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
    if (el.classList.contains('fa-circle')) {
        el.classList.remove('fa-circle', 'far');
        el.classList.add('fa-check-circle', 'fas');
        el.style.color = 'var(--success)';
        el.parentElement.style.opacity = '0.6';
    } else {
        el.classList.remove('fa-check-circle', 'fas');
        el.classList.add('fa-circle', 'far');
        el.style.color = 'var(--primary)';
        el.parentElement.style.opacity = '1';
    }
}

function addDoc(bookingId) {
    const item = prompt("Enter additional document name:");
    if (item) {
        // Simple AJAX-less update for session (In real app, use fetch)
        const container = document.getElementById('checklist-' + bookingId);
        const div = document.createElement('div');
        div.style.fontSize = '0.75rem';
        div.style.display = 'flex';
        div.style.alignItems = 'center';
        div.style.gap = '0.4rem';
        div.innerHTML = `<i class="far fa-circle" style="color: var(--primary); cursor: pointer;" onclick="markDone(this)"></i> <span>${item}</span>`;
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
