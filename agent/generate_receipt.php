<?php
require_once '../config/db.php';

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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; margin: 0; padding: 40px; background: #f1f5f9; }
        .receipt-container { max-width: 800px; margin: 0 auto; background: white; border: 1px solid #ddd; padding: 50px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #6366f1; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { color: #6366f1; font-size: 26px; font-weight: bold; }
        .status-badge { background: #22c55e; color: white; padding: 5px 15px; border-radius: 5px; font-size: 14px; text-transform: uppercase; font-weight: 700; }
        .section { margin-bottom: 35px; }
        .section-title { font-size: 13px; font-weight: bold; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .service-info { background: #eef2ff; padding: 25px; border-radius: 10px; border-left: 6px solid #6366f1; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; font-size: 12px; text-transform: uppercase; color: #64748b; }
        .signature-area { margin-top: 60px; display: flex; justify-content: space-between; align-items: flex-end; }
        .sig-box { border-top: 2px solid #333; width: 280px; padding-top: 15px; text-align: center; font-size: 14px; }
        .watermark { position: absolute; transform: rotate(-45deg); font-size: 80px; color: rgba(34, 197, 94, 0.05); font-weight: 900; top: 40%; left: 20%; pointer-events: none; text-transform: uppercase; }
        .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 60px; line-height: 1.6; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            .receipt-container { border: none; width: 100%; box-shadow: none; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 30px;">
        <button onclick="window.print()" style="padding: 12px 30px; background: #6366f1; color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(99,102,241,0.3);"><i class="fas fa-print"></i> Download / Print Acknowledgment</button>
        <p style="font-size: 13px; color: #64748b; margin-top: 10px;">This serves as a legal proof of document submission.</p>
    </div>

    <div class="receipt-container" style="position: relative;">
        <div class="watermark">VERIFIED</div>
        
        <div class="header">
            <div>
                <div class="logo">DOORSTEP CARE</div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Service Acknowledgment Form</div>
            </div>
            <div style="text-align: right;">
                <div class="status-badge">SUBMITTED</div>
                <div style="margin-top: 8px; font-size: 15px; font-weight: bold;">Ref: #<?php echo str_pad($booking_id, 5, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <div class="section details-grid">
            <div>
                <div class="section-title">Customer Information</div>
                <div style="font-size: 18px; font-weight: 700; color: #1e293b;"><?php echo $data['user_name']; ?></div>
                <div style="font-size: 14px; color: #475569; margin-top: 5px; line-height: 1.4;"><?php echo $data['user_address']; ?></div>
                <div style="font-size: 14px; color: #475569; margin-top: 5px;"><i class="fas fa-phone"></i> <?php echo $data['user_phone']; ?></div>
            </div>
            <div>
                <div class="section-title">Service Timeline</div>
                <div style="font-size: 15px; color: #1e293b;"><strong>Visit Date:</strong> <?php echo date('d F Y', strtotime($data['booking_date'])); ?></div>
                <div style="font-size: 15px; color: #1e293b; margin-top: 5px;"><strong>Time Slot:</strong> <?php echo $data['time_slot']; ?></div>
                <div style="font-size: 15px; color: #1e293b; margin-top: 5px;"><strong>Verification Agent:</strong> <?php echo $data['agent_name']; ?></div>
            </div>
        </div>

        <div class="section service-info">
            <div class="section-title" style="color: #4338ca;">Service Specifics</div>
            <div style="font-size: 20px; font-weight: 800; color: #312e81;"><?php echo $data['service_name']; ?></div>
            <div style="font-size: 14px; color: #4338ca; margin-top: 8px; line-height: 1.6; opacity: 0.8;">
                This acknowledgment confirms the verified collection of documents for government processing. The agent has reviewed originals and collected copies as per the checklist below.
            </div>
        </div>

        <div class="section">
            <div class="section-title">Verified Document Checklist</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Document Description</th>
                        <th style="text-align: right;">Handover Status</th>
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
                            <td><?php echo $count++; ?>.</td>
                            <td style="font-weight: 600; color: #334155;"><?php echo trim($doc); ?></td>
                            <td style="text-align: right; color: #22c55e;"><i class="fas fa-check-double"></i> RECEIVED</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="signature-area">
            <div class="sig-box">
                <div style="font-family: 'Brush Script MT', cursive; font-size: 28px; margin-bottom: 5px; color: #1e293b;"><?php echo $data['agent_name']; ?></div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Certified Agent Signature</div>
            </div>
            <div class="sig-box">
                <div style="font-family: 'Brush Script MT', cursive; font-size: 28px; margin-bottom: 5px; color: #1e293b;"><?php echo $data['user_name']; ?></div>
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Customer Acknowledgment</div>
            </div>
        </div>

        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 5px;">DIGITALLY VERIFIED BY DOORSTEP CARE PLATFORM</div>
            Confirmation Hash: <?php echo sha1($booking_id . $data['user_id'] . $data['agent_id']); ?>
            <br>This is an official receipt and can be used for tracking with the government department.
            <br>&copy; <?php echo date('Y'); ?> DoorstepCare - All rights reserved.
        </div>
    </div>
</body>
</html>
