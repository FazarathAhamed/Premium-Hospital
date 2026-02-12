<?php
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];
$billId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch bill details
$stmt = $conn->prepare("
    SELECT b.*, p.full_name as patient_name, p.email as patient_email, p.phone as patient_phone,
           a.appointment_date, a.appointment_time, a.reason,
           d.full_name as doctor_name, d.specialization
    FROM billing b
    JOIN users p ON b.patient_id = p.id
    LEFT JOIN appointments a ON b.appointment_id = a.id
    LEFT JOIN users d ON a.doctor_id = d.id
    WHERE b.id = ? AND b.patient_id = ? AND b.payment_status = 'paid'
");
$stmt->bind_param("ii", $billId, $userId);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();

if (!$bill) {
    $_SESSION['error'] = "Bill not found or payment not completed.";
    header("Location: " . APP_URL . "/patient/billing.php");
    exit();
}

// Generate HTML receipt for print/download
$receiptNumber = 'REC-' . str_pad($bill['id'], 6, '0', STR_PAD_LEFT);
$paidDate = $bill['paid_at'] ? date('M d, Y h:i A', strtotime($bill['paid_at'])) : date('M d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - <?php echo $receiptNumber; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', 'Inter', sans-serif; background: #f5f5f5; padding: 20px; }
        .receipt-container { max-width: 700px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .receipt-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .receipt-header h1 { font-size: 28px; color: #1a1f3d; margin-bottom: 4px; }
        .receipt-header p { color: #6b7280; font-size: 14px; }
        .receipt-number { display: inline-block; background: #f0fdf4; color: #065f46; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 14px; margin-top: 10px; }
        .receipt-body { margin-bottom: 30px; }
        .receipt-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .receipt-row .label { color: #6b7280; }
        .receipt-row .value { font-weight: 600; color: #111827; }
        .receipt-total { background: linear-gradient(135deg, #1a1f3d, #2d3561); color: #fff; padding: 20px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .receipt-total .amount { font-size: 28px; font-weight: 700; }
        .receipt-footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px dashed #d1d5db; color: #9ca3af; font-size: 12px; }
        .paid-stamp { display: inline-block; border: 3px solid #16a34a; color: #16a34a; padding: 6px 20px; border-radius: 8px; font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 4px; transform: rotate(-5deg); margin-top: 12px; }
        .btn-print { display: inline-block; background: #3b82f6; color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; cursor: pointer; border: none; margin-right: 8px; }
        .btn-back { display: inline-block; background: #e5e7eb; color: #374151; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
        .actions { text-align: center; margin-top: 20px; }
        @media print { .actions { display: none; } body { background: #fff; padding: 0; } .receipt-container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>❤️ HealthyLife Hospital</h1>
            <p>Payment Receipt</p>
            <div class="receipt-number"><?php echo $receiptNumber; ?></div>
        </div>
        
        <div class="receipt-body">
            <div class="receipt-row"><span class="label">Patient Name</span><span class="value"><?php echo htmlspecialchars($bill['patient_name']); ?></span></div>
            <div class="receipt-row"><span class="label">Email</span><span class="value"><?php echo htmlspecialchars($bill['patient_email']); ?></span></div>
            <div class="receipt-row"><span class="label">Phone</span><span class="value"><?php echo htmlspecialchars($bill['patient_phone'] ?? 'N/A'); ?></span></div>
            <div class="receipt-row"><span class="label">Description</span><span class="value"><?php echo htmlspecialchars($bill['description'] ?? 'Consultation'); ?></span></div>
            <div class="receipt-row"><span class="label">Doctor</span><span class="value"><?php echo htmlspecialchars(($bill['doctor_name'] ?? 'N/A') . ($bill['specialization'] ? ' (' . $bill['specialization'] . ')' : '')); ?></span></div>
            <div class="receipt-row"><span class="label">Appointment Date</span><span class="value"><?php echo $bill['appointment_date'] ? date('M d, Y', strtotime($bill['appointment_date'])) : '-'; ?></span></div>
            <div class="receipt-row"><span class="label">Payment Method</span><span class="value"><?php echo ucfirst($bill['payment_method']); ?><?php echo $bill['card_last_four'] ? ' (****' . $bill['card_last_four'] . ')' : ''; ?></span></div>
            <?php if ($bill['payment_code']): ?>
            <div class="receipt-row"><span class="label">Payment Code</span><span class="value"><?php echo htmlspecialchars($bill['payment_code']); ?></span></div>
            <?php endif; ?>
            <div class="receipt-row"><span class="label">Paid On</span><span class="value"><?php echo $paidDate; ?></span></div>
        </div>
        
        <div class="receipt-total">
            <span style="font-size:16px;">Total Amount Paid</span>
            <span class="amount">$<?php echo number_format($bill['amount'], 2); ?></span>
        </div>
        
        <div style="text-align:center;margin-top:20px;">
            <div class="paid-stamp">✓ PAID</div>
        </div>
        
        <div class="receipt-footer">
            <p>Thank you for choosing HealthyLife Hospital</p>
            <p>This is a computer-generated receipt. No signature required.</p>
            <p>Generated on <?php echo date('M d, Y h:i A'); ?></p>
        </div>
    </div>
    
    <div class="actions">
        <button onclick="window.print()" class="btn-print">
            🖨️ Print / Save as PDF
        </button>
        <a href="<?php echo APP_URL; ?>/patient/billing.php" class="btn-back">← Back to Billing</a>
    </div>
</body>
</html>
