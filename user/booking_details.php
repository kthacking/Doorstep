<?php 
$page_title = "Booking Details";
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Comprehensive query to get booking, service and agent details
$stmt = $pdo->prepare("SELECT b.*, s.service_name, s.description as s_desc, s.required_documents as initial_docs, s.service_type, 
                              a.agent_name, a.phone as agent_phone, a.gender as agent_gender, a.profile_summary as agent_profile, a.address as agent_address
                      FROM bookings b 
                      JOIN services s ON b.service_id = s.id 
                      LEFT JOIN agents a ON b.agent_id = a.id
                      WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}

$statuses = ['Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed'];
$current_status_index = array_search($booking['status'], $statuses);
?>

<div class="container">
    <a href="dashboard.php" style="text-decoration: none; color: var(--secondary); margin-bottom: 2rem; display: inline-block;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem;">
        <div>
            <h1 style="font-size: 2.5rem;"><?php echo $booking['service_name']; ?></h1>
            <p style="color: var(--secondary);">Booking ID: #<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?> | Placed on: <?php echo date('d M Y', strtotime($booking['created_at'])); ?></p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo CURRENCY . $booking['total_amount']; ?></div>
            <span style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 8px 20px; border-radius: 50px; font-weight: 700; text-transform: uppercase;"><?php echo $booking['status']; ?></span>
        </div>
    </div>

    <!-- Progress Stepper -->
    <div class="card" style="margin-bottom: 2rem; padding: 3rem 1rem;">
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
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Left Column: Details -->
        <div>
            <!-- Service Type Section -->
            <div class="card" style="margin-bottom: 2rem; border-left: 5px solid var(--primary);">
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <div style="font-size: 2.5rem; color: var(--primary);">
                        <?php if($booking['service_type'] === 'Remote'): ?>
                            <i class="fas fa-globe"></i>
                        <?php elseif($booking['service_type'] === 'In-Person Visit'): ?>
                            <i class="fas fa-home"></i>
                        <?php else: ?>
                            <i class="fas fa-user-shield"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3>Service Delivery: <?php echo $booking['service_type']; ?></h3>
                        <p style="font-size: 0.9rem; color: var(--secondary); margin-top: 0.5rem;">
                            <?php if($booking['service_type'] === 'Remote'): ?>
                                This service is processed digitally. No physical visit is required. Our agents handle the backend submission.
                            <?php elseif($booking['service_type'] === 'In-Person Visit'): ?>
                                A certified agent will visit your home to verify documents and provide guidance for your application.
                            <?php else: ?>
                                Our agent will visit, collect all required documents, and handle the entire submission process on your behalf.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="card" style="margin-bottom: 2rem;">
                <h3>Documents & Checklist</h3>
                <hr style="margin: 1.5rem 0; opacity: 0.1;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem;">Initially Required</h4>
                        <ul style="list-style: none; padding: 0;">
                            <?php 
                            $initial_docs = explode(',', $booking['initial_docs']);
                            foreach($initial_docs as $doc): ?>
                                <li style="margin-bottom: 0.8rem; display: flex; gap: 0.5rem; font-size: 0.9rem;">
                                    <i class="fas fa-file-alt" style="color: var(--secondary);"></i>
                                    <span><?php echo trim($doc); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem;">Agent's Dynamic Checklist</h4>
                        <ul style="list-style: none; padding: 0;">
                            <?php 
                            $dyn_docs = explode(',', $booking['dynamic_checklist']);
                            foreach($dyn_docs as $doc): 
                                if(empty(trim($doc))) continue;
                            ?>
                                <li style="margin-bottom: 0.8rem; display: flex; gap: 0.5rem; font-size: 0.9rem;">
                                    <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
                                    <span><?php echo trim($doc); ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if(empty(array_filter($dyn_docs))): ?>
                                <li style="color: #94a3b8; font-size: 0.85rem; font-style: italic;">No additional documents added by agent yet.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Two-Step Confirmation -->
            <div class="card" style="background: #f8fafc; border: 1px dashed var(--primary);">
                <h3>Document Submission Confirmation</h3>
                <p style="font-size: 0.9rem; color: var(--secondary); margin-top: 0.5rem;">Both you and the agent must confirm document handover before the final receipt can be generated.</p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                    <div style="text-align: center; padding: 1.5rem; background: white; border-radius: 15px;">
                        <div style="font-size: 1.5rem; margin-bottom: 1rem;">
                            <?php if($booking['user_doc_confirmed']): ?>
                                <i class="fas fa-check-circle" style="color: var(--success); font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem;">You Confirmed</h4>
                            <?php else: ?>
                                <i class="fas fa-user-clock" style="color: #94a3b8; font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem; color: #94a3b8;">User Confirmation</h4>
                                <form action="../actions/confirm_docs.php" method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <input type="hidden" name="user_type" value="user">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;">I have given documents</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="text-align: center; padding: 1.5rem; background: white; border-radius: 15px;">
                        <div style="font-size: 1.5rem; margin-bottom: 1rem;">
                            <?php if($booking['agent_doc_confirmed']): ?>
                                <i class="fas fa-check-circle" style="color: var(--success); font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem;">Agent Confirmed</h4>
                            <?php else: ?>
                                <i class="fas fa-user-tie" style="color: #94a3b8; font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem; color: #94a3b8;">Agent Acceptance</h4>
                                <p style="font-size: 0.75rem; margin-top: 0.5rem; color: var(--secondary);">Agent will confirm once they verify all documents.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($booking['user_doc_confirmed'] && $booking['agent_doc_confirmed']): ?>
                    <div style="margin-top: 2rem; background: rgba(34, 197, 94, 0.1); padding: 1rem; border-radius: 10px; display: flex; align-items: center; gap: 1rem;">
                        <i class="fas fa-file-signature" style="color: var(--success); font-size: 1.5rem;"></i>
                        <div style="flex: 1;">
                            <strong style="color: var(--success);">Submission Verified!</strong>
                            <p style="font-size: 0.85rem; color: #166534;">The acknowledgment receipt is now available for download.</p>
                        </div>
                        <a href="../agent/generate_receipt.php?id=<?php echo $booking['id']; ?>" target="_blank" class="btn btn-primary"><i class="fas fa-download"></i> Download Receipt</a>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 2rem; background: #fffbeb; padding: 1rem; border-radius: 10px; display: flex; align-items: center; gap: 1rem;">
                        <i class="fas fa-lock" style="color: #b45309; font-size: 1.5rem;"></i>
                        <p style="font-size: 0.85rem; color: #92400e;">Receipt access is locked until both parties confirm document submission.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Agent & Location -->
        <div>
            <!-- Agent Profile Card -->
            <?php if($booking['agent_id']): ?>
                <div class="card" style="margin-bottom: 2rem; text-align: center;">
                    <div class="agent-thumb" style="width: 100px; height: 100px; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h2><?php echo $booking['agent_name']; ?></h2>
                    <span style="background: var(--primary); color: white; padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Professional Agent</span>
                    <p style="color: var(--secondary); font-size: 0.9rem; margin: 1rem 0;"><?php echo $booking['agent_gender']; ?></p>
                    <hr style="margin: 1.5rem 0; opacity: 0.1;">
                    <div style="text-align: left; font-size: 0.85rem;">
                        <h4 style="text-transform: uppercase; color: var(--secondary); font-size: 0.7rem; margin-bottom: 0.5rem;">Bio</h4>
                        <p style="margin-bottom: 1.5rem; line-height: 1.4;"><?php echo $booking['agent_profile'] ?: 'Official verification expert assigned to your region.'; ?></p>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <a href="tel:<?php echo $booking['agent_phone']; ?>" class="btn" style="flex: 1; text-align: center; background: #e2e8f0; color: var(--dark); padding: 0.5rem;"><i class="fas fa-phone"></i> Call</a>
                            <a href="https://wa.me/<?php echo $booking['agent_phone']; ?>" target="_blank" class="btn" style="flex: 1; text-align: center; background: #25D366; color: white; padding: 0.5rem;"><i class="fab fa-whatsapp"></i> Chat</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card" style="background: #fffbeb; text-align: center;">
                    <i class="fas fa-user-secret" style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                    <h3>Finding Agent</h3>
                    <p style="font-size: 0.85rem; color: var(--secondary);">We are assigning a professional for your location.</p>
                </div>
            <?php endif; ?>

            <!-- Location Card -->
            <div class="card" style="margin-top: 2rem;">
                <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem;">Service Location</h4>
                <div style="display: flex; gap: 0.8rem;">
                    <i class="fas fa-map-marker-alt" style="color: var(--primary); margin-top: 4px;"></i>
                    <p style="font-size: 0.9rem; line-height: 1.5;"><?php echo $booking['address_confirmed']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
