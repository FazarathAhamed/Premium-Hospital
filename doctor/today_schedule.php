<?php
$pageTitle = "Today's Schedule";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['doctor']);

$doctorId = $_SESSION['user_id'];

// Handle marking as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete') {
    $aptId = (int)$_POST['appointment_id'];
    $notes = trim($_POST['notes'] ?? '');
    $stmt = $conn->prepare("UPDATE appointments SET status='completed', notes=? WHERE id=? AND doctor_id=? AND status='approved'");
    $stmt->bind_param("sii", $notes, $aptId, $doctorId);
    $stmt->execute();
    $_SESSION['success'] = "Consultation marked as completed!";
    header("Location: " . APP_URL . "/doctor/today_schedule.php");
    exit();
}

// Only show approved and completed appointments for today
$appointments = $conn->query("
    SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone, p.gender AS patient_gender
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    WHERE a.doctor_id = $doctorId 
    AND a.appointment_date = CURDATE()
    AND a.status IN ('approved', 'completed')
    ORDER BY a.appointment_time ASC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Today's Schedule — <?php echo date('l, F j, Y'); ?></h2>
        <span class="badge badge-approved"><?php echo $appointments->num_rows; ?> Patients</span>
    </div>
    <div class="card-body">
        <?php if ($appointments->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Patient</th><th>Phone</th><th>Time</th><th>Reason</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Patient"><strong><?php echo htmlspecialchars($a['patient_name']); ?></strong></td>
                        <td data-label="Phone"><?php echo htmlspecialchars($a['patient_phone'] ?? '—'); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></td>
                        <td data-label="Reason"><?php echo htmlspecialchars(substr($a['reason'] ?? '', 0, 50)); ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $a['status']; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                        <td data-label="Action">
                            <?php if ($a['status'] === 'approved'): ?>
                                <button class="btn btn-success btn-sm" onclick="openCompleteModal(<?php echo $a['id']; ?>, '<?php echo htmlspecialchars($a['patient_name'], ENT_QUOTES); ?>')">
                                    <i class="fas fa-check-circle"></i> Complete
                                </button>
                            <?php else: ?>
                                <span style="font-size:11px; color:#3b82f6; font-weight:600;"><i class="fas fa-check-double"></i> Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No approved appointments for today</p>
                <small style="color:var(--text-muted);">Pending appointments need receptionist approval first</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Complete Consultation Modal -->
<div class="modal-overlay" id="completeModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-notes-medical"></i> Complete Consultation</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="complete">
                <input type="hidden" name="appointment_id" id="modal_apt_id">
                <p style="margin-bottom:16px; color:var(--text-secondary);">
                    <i class="fas fa-user"></i> Patient: <strong id="modal_patient_name"></strong>
                </p>
                <div class="form-group">
                    <label><i class="fas fa-stethoscope"></i> Consultation Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Add any consultation notes, diagnosis, or prescription details..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Mark as Completed</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCompleteModal(aptId, patientName) {
    document.getElementById('modal_apt_id').value = aptId;
    document.getElementById('modal_patient_name').textContent = patientName;
    document.getElementById('completeModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
