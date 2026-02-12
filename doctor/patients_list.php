<?php
$pageTitle = "My Patients";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['doctor']);

$doctorId = $_SESSION['user_id'];

// Only show patients with approved or completed appointments
$patients = $conn->query("
    SELECT DISTINCT p.id, p.full_name, p.email, p.phone, p.gender, p.date_of_birth,
        (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id AND doctor_id = $doctorId AND status IN ('approved','completed')) as total_visits,
        (SELECT MAX(appointment_date) FROM appointments WHERE patient_id = p.id AND doctor_id = $doctorId AND status IN ('approved','completed')) as last_visit
    FROM users p
    JOIN appointments a ON a.patient_id = p.id
    WHERE a.doctor_id = $doctorId AND p.role = 'patient' AND a.status IN ('approved','completed')
    ORDER BY last_visit DESC
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-users"></i> My Patients</h2>
        <span class="badge badge-approved"><?php echo $patients->num_rows; ?> Total</span>
    </div>
    <div class="card-body">
        <?php if ($patients->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Phone</th><th>Gender</th><th>Visits</th><th>Last Visit</th><th>Details</th></tr>
                </thead>
                <tbody>
                    <?php $n=1; while ($p = $patients->fetch_assoc()): ?>
                    <tr>
                        <td data-label="#"><?php echo $n++; ?></td>
                        <td data-label="Name"><strong><?php echo htmlspecialchars($p['full_name']); ?></strong></td>
                        <td data-label="Phone"><?php echo htmlspecialchars($p['phone'] ?? '—'); ?></td>
                        <td data-label="Gender"><?php echo ucfirst($p['gender'] ?? '—'); ?></td>
                        <td data-label="Visits"><span class="badge badge-approved"><?php echo $p['total_visits']; ?></span></td>
                        <td data-label="Last Visit"><?php echo $p['last_visit'] ? date('M d, Y', strtotime($p['last_visit'])) : '—'; ?></td>
                        <td data-label="Details">
                            <a href="<?php echo APP_URL; ?>/doctor/patient_details.php?patient_id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <p>No patients yet</p>
                <small style="color:var(--text-muted);">Patients will appear here after their appointments are approved</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
