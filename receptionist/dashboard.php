<?php
$pageTitle = "Receptionist Dashboard";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

$totalAppointments = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
$todayAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['c'];
$pendingAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$totalPatients = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='patient'")->fetch_assoc()['c'];
$pendingPayments = $conn->query("SELECT COUNT(*) as c FROM billing WHERE payment_status='pending_verification'")->fetch_assoc()['c'];
$pendingInquiries = $conn->query("SELECT COUNT(*) as c FROM feedback WHERE reply IS NULL")->fetch_assoc()['c'];

// Recent appointments
$recentAppts = $conn->query("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id 
    ORDER BY a.created_at DESC LIMIT 5
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-details"><h3><?php echo $totalAppointments; ?></h3><p>Total Appointments</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-details"><h3><?php echo $todayAppts; ?></h3><p>Today's Appointments</p></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
        <div class="stat-details"><h3><?php echo $pendingAppts; ?></h3><p>Pending Approvals</p></div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon teal"><i class="fas fa-users"></i></div>
        <div class="stat-details"><h3><?php echo $totalPatients; ?></h3><p>Total Patients</p></div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-receipt"></i></div>
        <div class="stat-details"><h3><?php echo $pendingPayments; ?></h3><p>Pending Payments</p></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
        <div class="stat-details"><h3><?php echo $pendingInquiries; ?></h3><p>Pending Inquiries</p></div>
    </div>
</div>

<!-- Quick Actions -->
<h3 class="admin-section-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
<div class="quick-actions">
    <a href="<?php echo APP_URL; ?>/receptionist/register_patient.php" class="quick-action-card">
        <i class="fas fa-user-plus"></i>
        <span>Register Patient</span>
    </a>
    <a href="<?php echo APP_URL; ?>/receptionist/book_appointment.php" class="quick-action-card">
        <i class="fas fa-calendar-plus"></i>
        <span>Book Appointment</span>
    </a>
    <a href="<?php echo APP_URL; ?>/receptionist/manage_patients.php" class="quick-action-card">
        <i class="fas fa-users"></i>
        <span>Manage Patients</span>
    </a>
    <a href="<?php echo APP_URL; ?>/receptionist/manage_appointments.php" class="quick-action-card">
        <i class="fas fa-calendar-alt"></i>
        <span>Manage Appointments</span>
    </a>
    <a href="<?php echo APP_URL; ?>/receptionist/assign_rooms.php" class="quick-action-card">
        <i class="fas fa-bed"></i>
        <span>Assign Rooms</span>
    </a>
    <a href="<?php echo APP_URL; ?>/receptionist/verify_payments.php" class="quick-action-card">
        <i class="fas fa-receipt"></i>
        <span>Verify Payments</span>
    </a>
    <a href="<?php echo APP_URL; ?>/receptionist/inquiries.php" class="quick-action-card">
        <i class="fas fa-envelope"></i>
        <span>View Inquiries</span>
    </a>
</div>

<!-- Recent Appointments -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Recent Appointments</h2>
        <a href="<?php echo APP_URL; ?>/receptionist/manage_appointments.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="card-body">
        <?php if ($recentAppts->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while ($a = $recentAppts->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Patient"><?php echo htmlspecialchars($a['patient_name']); ?></td>
                        <td data-label="Doctor"><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $a['status']; ?>"><?php echo ucfirst($a['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No appointments yet</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
