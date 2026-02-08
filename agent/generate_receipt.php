<?php
require_once '../config/db.php';
require_once __DIR__ . '/../includes/lang.php';

// Allow both users and agents to view the receipt
$allowed = false;
$viewer_name = "";

if (isset($_SESSION['agent_id'])) {
    $allowed = true;
    $viewer_id = $_SESSION['agent_id'];
    $viewer_type = 'agent';
} elseif (isset($_SESSION['user_id'])) {
    $allowed = true;
    $viewer_id = $_SESSION['user_id'];
    $viewer_type = 'user';
}

if (!$allowed) {
    die("Unauthorized Access");
}

$booking_id = $_GET['id'] ?? null;

// Combined query to verify access regardless of who is viewing
$stmt = $pdo->prepare("SELECT b.*, u.full_name as user_name, u.phone as user_phone, u.address as user_address, 
                              s.service_name, s.required_documents as initial_docs, a.agent_name, a.id as agent_id
                      FROM bookings b 
                      JOIN users u ON b.user_id = u.id 
                      JOIN services s ON b.service_id = s.id 
                      LEFT JOIN agents a ON b.agent_id = a.id
                      WHERE b.id = ?");
$stmt->execute([$booking_id]);
$data = $stmt->fetch();

if (!$data) die("Data not found");

// Check ownership
if ($viewer_type === 'user' && $data['user_id'] != $viewer_id) die("Unauthorized access to this booking.");
if ($viewer_type === 'agent' && $data['agent_id'] != $viewer_id) die("Unauthorized access to this booking.");

// IMPORTANT: ENFORCE TWO-STEP CONFIRMATION
if (!$data['user_doc_confirmed'] || !$data['agent_doc_confirmed']) {
    die("<h3>Access Denied</h3><p>Acknowledgment receipt is locked until both User and Agent confirm the document handover. Current progress: Customer: " . ($data['user_doc_confirmed'] ? 'Done' : 'Pending') . " | Agent: " . ($data['agent_doc_confirmed'] ? 'Done' : 'Pending') . "</p><a href='javascript:history.back()'>Go Back</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Acknowledgment - #<?php echo $booking_id; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Playfair+Display:ital,wght@1,600&family=Dancing+Script:wght@400;700&display=swap');

        * { box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; color: #1e293b; margin: 0; padding: 20px; background: #f8fafc; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        .receipt-container { 
            width: 100%;
            max-width: 210mm; /* Max standard A4 Width */
            min-height: 297mm;
            margin: 20px auto; 
            background: white; 
            border: 1px solid #e2e8f0; 
            padding: 20mm; 
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
        }
        
        /* Premium Background elements */
        .receipt-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            z-index: 20;
        }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 50px; position: relative; z-index: 5; }
        .logo { color: #6366f1; font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
        .status-header { text-align: right; }
        
        .status-badge { 
            background: #1e293b; 
            color: white; 
            padding: 6px 16px; 
            border-radius: 4px; 
            font-size: 11px; 
            text-transform: uppercase; 
            font-weight: 800; 
            letter-spacing: 0.1em;
            display: inline-block;
        }

        .section { margin-bottom: 45px; }
        .section-title { 
            font-size: 11px; 
            font-weight: 800; 
            color: #94a3b8; 
            text-transform: uppercase; 
            margin-bottom: 15px; 
            letter-spacing: 0.15em; 
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .details-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        .info-label { font-size: 13px; color: #64748b; font-weight: 600; margin-bottom: 4px; }
        .info-value { font-size: 14px; color: #1e293b; font-weight: 700; word-break: break-word; }

        .service-highlight { 
            background: #f8fafc; 
            padding: 30px; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0;
            margin-bottom: 40px;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 15px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800; border-bottom: 2px solid #f1f5f9; }
        td { padding: 16px 15px; font-size: 14px; border-bottom: 1px solid #f1f5f9; color: #334155; }

        /* Stamp Styles */
        .official-stamp {
            position: absolute;
            top: 40px;
            right: 40px;
            border: 4px solid #ef4444;
            color: #ef4444;
            padding: 10px 20px;
            font-weight: 900;
            text-transform: uppercase;
            transform: rotate(-12deg);
            border-radius: 4px;
            opacity: 0.8;
            pointer-events: none;
            background: rgba(255, 255, 255, 0.95);
            text-align: center;
            line-height: 1.2;
            z-index: 15;
            box-shadow: 0 0 15px rgba(255,255,255,0.8);
        }
        .stamp-date { font-size: 10px; border-top: 1px solid #ef4444; margin-top: 4px; padding-top: 2px; }

        /* Signature Section */
        .signature-container { 
            margin-top: 80px; 
            display: flex; 
            justify-content: space-between; 
            gap: 50px;
        }
        
        .sig-box { flex: 1; text-align: center; }
        .sig-line { 
            border-bottom: 2px solid #1e293b; 
            height: 100px; 
            margin-bottom: 15px; 
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .agent-sig {
            font-family: 'Dancing Script', cursive;
            font-size: 42px;
            color: #4338ca;
            position: absolute;
            bottom: 5px;
            transform: rotate(-2deg);
        }

        .customer-guide {
            font-size: 42px;
            font-weight: 800;
            color: rgba(30, 41, 59, 0.04);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            pointer-events: none;
            user-select: none;
        }

        .no-print { text-align: center; margin: 40px 0; }
        
        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            body { padding: 0; margin: 0; background: white; }
            .no-print { display: none; }
            .receipt-container { 
                box-shadow: none !important; 
                border: none !important; 
                width: 100% !important; 
                max-width: none !important;
                margin: 0 !important;
                padding: 10mm !important;
                height: auto !important;
                min-height: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button id="printBtn" onclick="window.print()" style="padding: 14px 40px; background: #1e293b; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; font-family: inherit;">
            <i class="fas fa-print"></i> <?php echo strtoupper(__('print_ack')); ?>
        </button>
    </div>

    <div class="receipt-container">
        <!-- Official Stamp -->
        <div class="official-stamp">
            <div><?php echo ($data['status'] === 'Completed') ? __('docs_returned') : __('docs_received'); ?></div>
            <div style="font-size: 14px; margin: 4px 0;">DOORSTEP CARE</div>
            <div class="stamp-date"><?php echo date('d-m-Y'); ?></div>
        </div>

        <div class="header">
            <div>
                <div class="logo">DOORSTEP CARE</div>
                <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-top: 4px;">GOVERNMENT SERVICE ASSISTANCE PANEL</div>
            </div>
            <div class="status-header">
                <?php 
                    $stat_map = ['Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed'];
                    $idx = array_search($data['status'], $stat_map);
                    $status_key = ($idx !== false) ? 'step_' . $idx : 'status_pending';
                ?>
                <div class="status-badge"><?php echo __($status_key); ?></div>
                <div style="margin-top: 10px; font-size: 16px; font-weight: 800;">REF #<?php echo str_pad($booking_id, 5, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <div class="service-highlight">
            <div class="section-title"><?php echo __('acknowledged_service'); ?></div>
            <div style="font-size: 24px; font-weight: 800; color: #1e293b;"><?php echo $data['service_name']; ?></div>
            <p style="font-size: 14px; color: #64748b; margin-top: 10px; line-height: 1.6;">
                <?php echo __('ack_desc'); ?>
            </p>
        </div>

        <div class="section details-grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div>
                <div class="section-title"><?php echo __('customer_details'); ?></div>
                <div style="margin-bottom: 12px;">
                    <div class="info-label"><?php echo __('full_name'); ?></div>
                    <div class="info-value"><?php echo $data['user_name']; ?></div>
                </div>
                <div>
                    <div class="info-label"><?php echo __('contact'); ?></div>
                    <div class="info-value" style="font-size: 13px;"><?php echo $data['user_phone']; ?></div>
                </div>
            </div>
            <div>
                <div class="section-title"><?php echo __('agent_details'); ?></div>
                <div style="margin-bottom: 12px;">
                    <div class="info-label"><?php echo __('agent_name'); ?></div>
                    <div class="info-value"><?php echo $data['agent_name']; ?></div>
                </div>
                <div>
                    <div class="info-label"><?php echo __('agent_id'); ?></div>
                    <div class="info-value" style="font-size: 13px;"><?php echo str_pad($data['agent_id'], 4, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>
            <div>
                <div class="section-title"><?php echo __('service_window'); ?></div>
                <div style="margin-bottom: 12px;">
                    <div class="info-label"><?php echo __('visit_date'); ?></div>
                    <div class="info-value"><?php echo date('d M Y', strtotime($data['booking_date'])); ?></div>
                </div>
                <div>
                    <div class="info-label"><?php echo __('time_slot'); ?></div>
                    <div class="info-value" style="font-size: 13px;"><?php echo $data['time_slot']; ?></div>
                </div>
            </div>
        </div>

        <div class="section" style="margin-top: -10px;">
            <div class="section-title"><?php echo __('registered_address'); ?></div>
            <div class="info-value" style="font-size: 14px; color: #475569; font-weight: 500; font-style: italic;"><?php echo $data['user_address']; ?></div>
        </div>

        <div class="section">
            <div class="section-title"><?php echo __('verified_checklist'); ?></div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php echo __('no'); ?></th>
                        <th><?php echo __('document_name'); ?></th>
                        <th style="text-align: right;"><?php echo __('status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $all_docs = array_merge(explode(',', $data['initial_docs']), explode(',', $data['dynamic_checklist']));
                    $count = 1;
                    foreach($all_docs as $doc): 
                        if(empty(trim($doc))) continue;
                    ?>
                        <tr>
                            <td style="font-weight: 700; color: #94a3b8;"><?php echo str_pad($count++, 2, '0', STR_PAD_LEFT); ?></td>
                            <td style="font-weight: 600;"><?php echo trim($doc); ?></td>
                            <td style="text-align: right; color: #059669; font-weight: 800; font-size: 11px;">
                                <i class="fas fa-check"></i> <?php echo __('received'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="signature-container">
            <div class="sig-box">
                <div class="sig-line">
                    <div class="agent-sig"><?php echo $data['agent_name']; ?></div>
                </div>
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase;"><?php echo __('agent_digital_sig'); ?></div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">ID: <?php echo $booking_id . '/' . substr($data['agent_name'], 0, 3) . '-' . date('y'); ?></div>
            </div>
            <div class="sig-box">
                <div class="sig-line">
                    <div class="customer-guide"><?php echo $data['user_name']; ?></div>
                </div>
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase;"><?php echo __('customer_sig'); ?></div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;"><?php echo __('sig_confirm'); ?></div>
            </div>
        </div>

        <div style="margin-top: 60px; padding-top: 30px; border-top: 1px solid #f1f5f9; text-align: center;">
            <div style="font-size: 10px; color: #94a3b8; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">
                <?php echo __('digital_ack'); ?> &bull; Hash: <?php echo substr(sha1($booking_id), 0, 16); ?>
            </div>
        </div>
    </div>
</body>
</html>
