<?php
$pageTitle = "Patient Details";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['doctor']);

$userId = $_SESSION['user_id'];
$patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

if (!$patientId) {
    header("Location: " . APP_URL . "/doctor/patients_list.php");
    exit();
}

// Get patient info
$patientStmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role='patient'");
$patientStmt->bind_param("i", $patientId);
$patientStmt->execute();
$patient = $patientStmt->get_result()->fetch_assoc();

if (!$patient) {
    header("Location: " . APP_URL . "/doctor/patients_list.php");
    exit();
}

// Get appointment history
$appointments = $conn->query("
    SELECT * FROM appointments 
    WHERE patient_id = $patientId AND doctor_id = $userId 
    ORDER BY appointment_date DESC, appointment_time DESC
");

// Get medical reports
$reports = $conn->query("
    SELECT * FROM medical_reports 
    WHERE patient_id = $patientId AND doctor_id = $userId 
    ORDER BY created_at DESC
");

$age = $patient['date_of_birth'] ? date_diff(date_create($patient['date_of_birth']), date_create('today'))->y : 'N/A';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Patient Info Card -->
<div class="patient-detail-card">
    <h3><i class="fas fa-user-circle"></i> Patient Information</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Full Name</span>
            <span class="detail-value"><?php echo htmlspecialchars($patient['full_name']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value"><?php echo htmlspecialchars($patient['email']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone</span>
            <span class="detail-value"><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Gender</span>
            <span class="detail-value"><?php echo ucfirst($patient['gender'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Date of Birth</span>
            <span class="detail-value"><?php echo $patient['date_of_birth'] ? date('M d, Y', strtotime($patient['date_of_birth'])) : 'N/A'; ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Age</span>
            <span class="detail-value"><?php echo $age; ?> years</span>
        </div>
        <div class="detail-item" style="grid-column: 1 / -1;">
            <span class="detail-label">Address</span>
            <span class="detail-value"><?php echo htmlspecialchars($patient['address'] ?? 'N/A'); ?></span>
        </div>
    </div>
</div>

<!-- Appointment History -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Appointment History</h2>
        <span class="badge badge-approved"><?php echo $appointments->num_rows; ?> Visits</span>
    </div>
    <div class="card-body">
        <?php if ($appointments->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php while ($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></td>
                        <td data-label="Reason"><?php echo htmlspecialchars($a['reason'] ?? '-'); ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $a['status']; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        <td data-label="Notes"><?php echo htmlspecialchars($a['notes'] ?? '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No appointments found</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- Medical Reports -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-file-medical"></i> Medical Reports</h2>
        <?php
        $hasConsulted = $conn->query("SELECT 1 FROM appointments WHERE doctor_id = $userId AND patient_id = $patientId AND status = 'completed' LIMIT 1")->num_rows > 0;
        if ($hasConsulted):
        ?>
        <a href="<?php echo APP_URL; ?>/doctor/upload_reports.php?patient_id=<?php echo $patientId; ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-upload"></i> Upload New Report
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($reports->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Title</th><th>Description</th><th>Date</th><th>File</th></tr></thead>
                <tbody>
                    <?php while ($r = $reports->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Title"><strong><?php echo htmlspecialchars($r['report_title']); ?></strong></td>
                        <td data-label="Description"><?php echo htmlspecialchars($r['report_description'] ?? '-'); ?></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                        <td data-label="File">
                            <?php if ($r['file_path']): ?>
                                <a href="<?php echo APP_URL . '/' . $r['file_path']; ?>" class="btn btn-info btn-sm" target="_blank">
                                    <i class="fas fa-download"></i> View
                                </a>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-size:12px;">No file</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-file-medical"></i><p>No reports uploaded yet</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
