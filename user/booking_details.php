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
    <a href="dashboard.php" style="text-decoration: none; color: var(--secondary); margin-bottom: 2rem; display: inline-block;"><i class="fas fa-arrow-left"></i> <?php echo __('back_dashboard'); ?></a>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem;">
        <div>
            <h1 style="font-size: 2.5rem;"><?php echo $booking['service_name']; ?></h1>
            <p style="color: var(--secondary);"><?php echo __('booking_id_label'); ?>: #<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?> | <?php echo __('placed_on'); ?>: <?php echo date('d M Y', strtotime($booking['created_at'])); ?></p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?php echo CURRENCY . $booking['total_amount']; ?></div>
            <?php if ($booking['status'] === 'Cancelled'): ?>
                <span style="background: #fef2f2; color: #dc2626; padding: 8px 20px; border-radius: 50px; font-weight: 700; text-transform: uppercase; border: 1px solid #fecaca;">
                    Cancelled
                </span>
            <?php else: ?>
                <?php 
                    $status_key = 'step_' . array_search($booking['status'], ['Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed']);
                ?>
                <span style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 8px 20px; border-radius: 50px; font-weight: 700; text-transform: uppercase;">
                    <?php echo __($status_key); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <?php if (in_array($booking['status'], ['Booking Placed', 'Agent Assigned'])): ?>
            <form action="../actions/cancel_booking.php" method="POST" onsubmit="return confirm('<?php echo __('cancel_confirm_msg'); ?>')">
                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                <button type="submit" class="btn" style="background: #fee2e2; color: #dc2626; padding: 0.8rem 2.5rem; border: 1px solid #fecaca; font-weight: 700;">
                    <i class="fas fa-times-circle"></i> <?php echo __('cancel_booking'); ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Progress Stepper -->
    <?php if ($booking['status'] !== 'Cancelled'): ?>
    <div class="card" style="margin-bottom: 2rem; padding: 3rem 1rem;">
        <div class="stepper">
            <?php 
            $status_labels = ['Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed'];
            foreach ($status_labels as $index => $label): 
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
                    <div class="step-label"><?php echo __('step_' . $index); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="card" style="margin-bottom: 2rem; padding: 3rem; text-align: center; background: #fff1f2;">
            <i class="fas fa-ban" style="font-size: 4rem; color: #e11d48; margin-bottom: 1rem;"></i>
            <h2 style="color: #be123c;">This booking has been cancelled</h2>
            <p style="color: #9f1239;">You cancelled this request. If this was a mistake, please book a new service.</p>
        </div>
    <?php endif; ?>

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
                        <h3><?php echo __('service_delivery'); ?>: <?php echo $booking['service_type']; ?></h3>
                        <p style="font-size: 0.9rem; color: var(--secondary); margin-top: 0.5rem;">
                            <?php if($booking['service_type'] === 'Remote'): ?>
                                <?php echo __('remote_desc'); ?>
                            <?php elseif($booking['service_type'] === 'In-Person Visit'): ?>
                                <?php echo __('in_person_desc'); ?>
                            <?php else: ?>
                                <?php echo __('agent_handled_desc'); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="card" style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.25rem;"><?php echo __('verification_checklist'); ?></h3>
                    <span class="document-badge"><i class="fas fa-shield-alt"></i> Official Verification</span>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 12px;">
                        <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem; letter-spacing: 0.05em;"><?php echo __('base_docs'); ?></h4>
                        <ul style="list-style: none; padding: 0;">
                            <?php 
                            $initial_docs = explode(',', $booking['initial_docs']);
                            foreach($initial_docs as $doc): ?>
                                <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; font-weight: 500;">
                                    <i class="fas fa-check-circle" style="color: var(--success); font-size: 0.9rem;"></i>
                                    <span><?php echo trim($doc); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 12px;">
                        <h4 style="font-size: 0.7rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem; letter-spacing: 0.05em;"><?php echo __('agent_reqs'); ?></h4>
                        <ul style="list-style: none; padding: 0;">
                            <?php 
                            $dyn_docs = explode(',', $booking['dynamic_checklist']);
                            $has_dyn = false;
                            foreach($dyn_docs as $doc): 
                                if(empty(trim($doc))) continue;
                                $has_dyn = true;
                            ?>
                                <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; font-weight: 500;">
                                    <?php if($booking['agent_doc_confirmed']): ?>
                                        <i class="fas fa-check-circle" style="color: var(--success); font-size: 0.9rem;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-plus-circle" style="color: var(--primary); font-size: 0.9rem;"></i>
                                    <?php endif; ?>
                                    <span><?php echo trim($doc); ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if(!$has_dyn): ?>
                                <li style="color: #94a3b8; font-size: 0.8rem; font-style: italic; padding-top: 0.5rem;"><?php echo __('no_reqs'); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Two-Step Confirmation -->
            <div class="card" style="background: #f8fafc; border: 1px dashed var(--primary);">
                <h3><?php echo __('doc_sub_confirm'); ?></h3>
                <p style="font-size: 0.9rem; color: var(--secondary); margin-top: 0.5rem;"><?php echo __('doc_sub_desc'); ?></p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                    <div style="text-align: center; padding: 1.5rem; background: white; border-radius: 15px;">
                        <div style="font-size: 1.5rem; margin-bottom: 1rem;">
                            <?php if($booking['user_doc_confirmed']): ?>
                                <i class="fas fa-check-circle" style="color: var(--success); font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem;"><?php echo __('you_confirmed'); ?></h4>
                            <?php else: ?>
                                <i class="fas fa-user-clock" style="color: #94a3b8; font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem; color: #94a3b8;"><?php echo __('user_confirm'); ?></h4>
                                <form action="../actions/confirm_docs.php" method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <input type="hidden" name="user_type" value="user">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.8rem;"><?php echo __('i_have_given_docs'); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="text-align: center; padding: 1.5rem; background: white; border-radius: 15px;">
                        <div style="font-size: 1.5rem; margin-bottom: 1rem;">
                            <?php if($booking['agent_doc_confirmed']): ?>
                                <i class="fas fa-check-circle" style="color: var(--success); font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem;"><?php echo __('agent_confirmed'); ?></h4>
                            <?php else: ?>
                                <i class="fas fa-user-tie" style="color: #94a3b8; font-size: 3rem;"></i>
                                <h4 style="margin-top: 0.5rem; color: #94a3b8;"><?php echo __('agent_acceptance'); ?></h4>
                                <p style="font-size: 0.75rem; margin-top: 0.5rem; color: var(--secondary);">Agent will confirm once they verify all documents.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($booking['user_doc_confirmed'] && $booking['agent_doc_confirmed']): ?>
                    <div style="margin-top: 2rem; background: rgba(34, 197, 94, 0.1); padding: 1.25rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
                        <i class="fas fa-file-signature" style="color: var(--success); font-size: 1.8rem;"></i>
                        <div style="flex: 1;">
                            <strong style="color: var(--success); font-size: 1rem;"><?php echo __('sub_verified'); ?></strong>
                            <p style="font-size: 0.85rem; color: #166534; margin-top: 2px;"><?php echo __('sub_verified_desc'); ?></p>
                        </div>
                        <a href="../agent/generate_receipt.php?id=<?php echo $booking['id']; ?>" target="_blank" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);"><i class="fas fa-download"></i> Receipt</a>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 2rem; background: #fffbeb; padding: 1.25rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem; border: 1px solid #fde68a;">
                        <i class="fas fa-lock" style="color: #d97706; font-size: 1.8rem;"></i>
                        <div style="flex: 1;">
                            <strong style="color: #92400e; font-size: 0.9rem;"><?php echo __('waiting_verification'); ?></strong>
                            <p style="font-size: 0.8rem; color: #b45309; margin-top: 2px;"><?php echo __('receipt_unlock_desc'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Next Steps Section -->
            <div class="card" style="margin-top: 2rem; border: none; background: linear-gradient(to bottom, #ffffff, #f8fafc);">
                <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem;"><i class="fas fa-paper-plane" style="color: var(--primary);"></i> What Happens Next?</h3>
                <div style="display: grid; gap: 1rem;">
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0;">1</div>
                        <div>
                            <p style="font-size: 0.85rem; font-weight: 600;">Government Submission</p>
                            <p style="font-size: 0.8rem; color: var(--secondary);">Agent submits your application to the respective department within 24 hours.</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0;">2</div>
                        <div>
                            <p style="font-size: 0.85rem; font-weight: 600; color: var(--secondary);">Internal Verification</p>
                            <p style="font-size: 0.8rem; color: var(--secondary);">The department verifies documents (takes <?php echo $booking['estimated_days'] ?? '5-7'; ?> business days).</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0;">3</div>
                        <div>
                            <p style="font-size: 0.85rem; font-weight: 600; color: var(--secondary);">Approval & Dispatch</p>
                            <p style="font-size: 0.8rem; color: var(--secondary);">Your final certificate/card will be dispatched to your registered address.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Agent & Location -->
        <div>
            <!-- Agent Profile Card -->
            <?php if($booking['agent_id']): ?>
                <div class="card" style="margin-bottom: 2rem; padding: 2rem 1.5rem; text-align: center; border: none; background: white; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.05);">
                    <div style="position: relative; width: 110px; height: 110px; margin: 0 auto 1.5rem;">
                        <div class="agent-thumb" style="width: 100%; height: 100%; font-size: 3rem; background: linear-gradient(135deg, var(--primary), #818cf8); color: white; border: 4px solid #f8fafc; box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);">
                            <?php 
                                $name_parts = explode(' ', $booking['agent_name']);
                                echo strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                            ?>
                        </div>
                        <div style="position: absolute; bottom: 5px; right: 5px; width: 22px; height: 22px; background: var(--success); border: 3px solid white; border-radius: 50%;"></div>
                    </div>
                    
                    <h2 style="font-size: 1.4rem; margin-bottom: 0.25rem;"><?php echo $booking['agent_name']; ?></h2>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 4px 12px; border-radius: 50px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Certified Agent</span>
                        <div style="color: #fbbf24; font-size: 0.8rem;"><i class="fas fa-star"></i> 4.9</div>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; text-align: left; margin-bottom: 1.5rem;">
                        <h4 style="text-transform: uppercase; color: #94a3b8; font-size: 0.6rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: 0.05em;">Professional Bio</h4>
                        <p style="font-size: 0.8rem; line-height: 1.6; color: #475569; position: relative; padding-left: 1rem;">
                            <i class="fas fa-quote-left" style="position: absolute; left: 0; top: 3px; font-size: 0.6rem; color: #cbd5e1;"></i>
                            <?php echo $booking['agent_profile'] ?: 'Official verification expert assigned to handle your documentation and government submission process.'; ?>
                        </p>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <a href="tel:<?php echo $booking['agent_phone']; ?>" class="btn" style="background: #f1f5f9; color: var(--dark); font-size: 0.8rem; font-weight: 700; padding: 0.6rem;"><i class="fas fa-phone"></i> Call</a>
                        <a href="https://wa.me/<?php echo $booking['agent_phone']; ?>" target="_blank" class="btn" style="background: #25D366; color: white; font-size: 0.8rem; font-weight: 700; padding: 0.6rem;"><i class="fab fa-whatsapp"></i> Chat</a>
                    </div>
                </div>

                <!-- Feedback Section -->
                <div class="card" style="margin-bottom: 2rem; border: 1px solid #f1f5f9; background: #fafafa;">
                    <h4 style="font-size: 0.8rem; margin-bottom: 1rem;"><?php echo __('rate_experience'); ?></h4>
                    <div id="star-rating" style="display: flex; gap: 0.5rem; justify-content: center; font-size: 1.5rem; color: #e2e8f0; margin-bottom: 1rem;">
                        <i class="fas fa-star" data-rating="1" style="cursor: pointer; transition: color 0.2s;"></i>
                        <i class="fas fa-star" data-rating="2" style="cursor: pointer; transition: color 0.2s;"></i>
                        <i class="fas fa-star" data-rating="3" style="cursor: pointer; transition: color 0.2s;"></i>
                        <i class="fas fa-star" data-rating="4" style="cursor: pointer; transition: color 0.2s;"></i>
                        <i class="fas fa-star" data-rating="5" style="cursor: pointer; transition: color 0.2s;"></i>
                    </div>
                    <button class="btn" style="width: 100%; font-size: 0.75rem; background: transparent; border: 1px solid #e2e8f0; color: var(--secondary);"><?php echo __('write_review'); ?></button>
                    
                    <script>
                        const stars = document.querySelectorAll('#star-rating .fa-star');
                        let currentRating = 0;

                        stars.forEach(star => {
                            star.addEventListener('mouseover', () => {
                                highlightStars(star.dataset.rating);
                            });

                            star.addEventListener('mouseout', () => {
                                highlightStars(currentRating);
                            });

                            star.addEventListener('click', () => {
                                currentRating = star.dataset.rating;
                                highlightStars(currentRating);
                                alert("Thank you for rating " + currentRating + " stars!");
                            });
                        });

                        function highlightStars(rating) {
                            stars.forEach(s => {
                                if (s.dataset.rating <= rating) {
                                    s.style.color = '#fbbf24';
                                } else {
                                    s.style.color = '#e2e8f0';
                                }
                            });
                        }
                    </script>
                </div>
<?php else: ?>
                <div class="card" style="background: #fffbeb; text-align: center;">
                    <i class="fas fa-user-secret" style="font-size: 3rem; color: #f59e0b; margin-bottom: 1rem;"></i>
                    <h3><?php echo __('finding_agent'); ?></h3>
                    <p style="font-size: 0.85rem; color: var(--secondary);"><?php echo __('finding_agent_desc'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Location Card -->
            <div class="card" style="margin-top: 2rem;">
                <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--secondary); margin-bottom: 1rem;"><?php echo __('service_location'); ?></h4>
                <div style="display: flex; gap: 0.8rem;">
                    <i class="fas fa-map-marker-alt" style="color: var(--primary); margin-top: 4px;"></i>
                    <p style="font-size: 0.9rem; line-height: 1.5;"><?php echo $booking['address_confirmed']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
