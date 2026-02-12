<?php
$pageTitle = "Medical Reports";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

$reports = $conn->query("
    SELECT mr.*, d.full_name as doctor_name, d.specialization
    FROM medical_reports mr
    JOIN users d ON mr.doctor_id = d.id
    WHERE mr.patient_id = $userId
    ORDER BY mr.created_at DESC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-notes-medical"></i> My Medical Reports</h2>
        <span class="badge badge-approved"><?php echo $reports->num_rows; ?> Reports</span>
    </div>
    <div class="card-body">
        <?php if ($reports->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Report Title</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($r = $reports->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Report Title"><strong><?php echo htmlspecialchars($r['report_title']); ?></strong></td>
                        <td data-label="Doctor">Dr. <?php echo htmlspecialchars($r['doctor_name']); ?></td>
                        <td data-label="Specialization"><?php echo htmlspecialchars($r['specialization']); ?></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                        <td data-label="Actions">
                            <div class="action-btns">
                                <button class="btn btn-info btn-sm" onclick="viewReport(<?php echo htmlspecialchars(json_encode($r)); ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($r['file_path']): ?>
                                <a href="<?php echo APP_URL . '/' . $r['file_path']; ?>" download class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo APP_URL; ?>/patient/download_report.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-file-pdf"></i> Print
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-notes-medical"></i><p>No medical reports found</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal-overlay" id="reportModal">
    <div class="modal">
        <div class="modal-header">
            <h2 id="rm_title">Report Details</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Doctor</span>
                <p style="font-size:14px;font-weight:600;color:var(--text-primary);" id="rm_doctor"></p>
            </div>
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Date</span>
                <p style="font-size:14px;font-weight:600;color:var(--text-primary);" id="rm_date"></p>
            </div>
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Description</span>
                <p style="font-size:14px;color:var(--text-secondary);line-height:1.6;background:var(--bg-light);padding:12px;border-radius:8px;" id="rm_desc"></p>
            </div>
        </div>
    </div>
</div>

<script>
function viewReport(r) {
    document.getElementById('rm_title').textContent = r.report_title;
    document.getElementById('rm_doctor').textContent = 'Dr. ' + r.doctor_name + ' (' + r.specialization + ')';
    document.getElementById('rm_date').textContent = new Date(r.created_at).toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'});
    document.getElementById('rm_desc').textContent = r.report_description || 'No description available.';
    document.getElementById('reportModal').classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
