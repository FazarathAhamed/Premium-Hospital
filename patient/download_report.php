<?php
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT mr.*, p.full_name as patient_name, p.email as patient_email, p.gender, p.date_of_birth,
           d.full_name as doctor_name, d.specialization
    FROM medical_reports mr
    JOIN users p ON mr.patient_id = p.id
    JOIN users d ON mr.doctor_id = d.id
    WHERE mr.id = ? AND mr.patient_id = ?
");
$stmt->bind_param("ii", $reportId, $userId);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    $_SESSION['error'] = "Report not found.";
    header("Location: " . APP_URL . "/patient/medical_reports.php");
    exit();
}

$patientAge = $report['date_of_birth'] ? floor((time() - strtotime($report['date_of_birth'])) / 31536000) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Report - <?php echo htmlspecialchars($report['report_title']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', 'Inter', sans-serif; background: #f5f5f5; padding: 20px; }
        .report-container { max-width: 750px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .report-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 3px double #1a1f3d; margin-bottom: 24px; }
        .report-header h1 { font-size: 22px; color: #1a1f3d; }
        .report-header p { font-size: 12px; color: #6b7280; }
        .report-id { background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .info-item { font-size: 13px; }
        .info-item .label { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-item .value { font-weight: 600; color: #111827; margin-top: 2px; }
        .report-title { font-size: 20px; font-weight: 700; color: #1a1f3d; padding: 16px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 20px; }
        .report-content { font-size: 14px; line-height: 1.8; color: #374151; padding: 20px; background: #fafafa; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 24px; }
        .report-footer { text-align: center; padding-top: 20px; border-top: 1px dashed #d1d5db; color: #9ca3af; font-size: 11px; }
        .doctor-sign { text-align: right; margin-top: 30px; padding-top: 16px; }
        .doctor-sign .line { width: 200px; border-top: 1px solid #374151; margin-left: auto; margin-top: 40px; padding-top: 6px; font-size: 13px; color: #374151; font-weight: 600; }
        .actions { text-align: center; margin-top: 20px; }
        .btn-print { display: inline-block; background: #3b82f6; color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; margin-right: 8px; font-size: 14px; }
        .btn-back { display: inline-block; background: #e5e7eb; color: #374151; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; }
        @media print { .actions { display: none; } body { background: #fff; padding: 0; } .report-container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <div>
                <h1>❤️ HealthyLife Hospital</h1>
                <p>Medical Report</p>
            </div>
            <div style="text-align:right;">
                <div class="report-id">RPT-<?php echo str_pad($report['id'], 6, '0', STR_PAD_LEFT); ?></div>
                <p style="margin-top:6px;"><?php echo date('M d, Y', strtotime($report['created_at'])); ?></p>
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-item"><div class="label">Patient Name</div><div class="value"><?php echo htmlspecialchars($report['patient_name']); ?></div></div>
            <div class="info-item"><div class="label">Email</div><div class="value"><?php echo htmlspecialchars($report['patient_email']); ?></div></div>
            <div class="info-item"><div class="label">Age / Gender</div><div class="value"><?php echo $patientAge . ' years / ' . ucfirst($report['gender'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="label">Doctor</div><div class="value">Dr. <?php echo htmlspecialchars($report['doctor_name']); ?></div></div>
            <div class="info-item"><div class="label">Specialization</div><div class="value"><?php echo htmlspecialchars($report['specialization']); ?></div></div>
            <div class="info-item"><div class="label">Date</div><div class="value"><?php echo date('F d, Y', strtotime($report['created_at'])); ?></div></div>
        </div>
        
        <div class="report-title"><?php echo htmlspecialchars($report['report_title']); ?></div>
        
        <div class="report-content">
            <?php echo nl2br(htmlspecialchars($report['report_description'] ?? 'No description provided.')); ?>
        </div>
        
        <div class="doctor-sign">
            <div class="line">
                Dr. <?php echo htmlspecialchars($report['doctor_name']); ?><br>
                <span style="font-weight:400;font-size:12px;color:#6b7280;"><?php echo htmlspecialchars($report['specialization']); ?></span>
            </div>
        </div>
        
        <div class="report-footer">
            <p>This is a computer-generated medical report from HealthyLife Hospital.</p>
            <p>For queries, contact us at info@healthylife.com</p>
        </div>
    </div>
    
    <div class="actions">
        <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
        <a href="<?php echo APP_URL; ?>/patient/medical_reports.php" class="btn-back">← Back to Reports</a>
    </div>
</body>
</html>
