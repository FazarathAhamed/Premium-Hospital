<?php
$pageTitle = "Patient Dashboard";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

$totalAppointments = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $userId")->fetch_assoc()['c'];
$pendingAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $userId AND status='pending'")->fetch_assoc()['c'];
$completedAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $userId AND status='completed'")->fetch_assoc()['c'];
$totalReports = $conn->query("SELECT COUNT(*) as c FROM medical_reports WHERE patient_id = $userId")->fetch_assoc()['c'];
$unpaidBills = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM billing WHERE patient_id = $userId AND payment_status='unpaid'")->fetch_assoc()['t'];

// Upcoming appointments
$upcoming = $conn->query("
    SELECT a.*, d.full_name as doctor_name, d.specialization
    FROM appointments a 
    JOIN users d ON a.doctor_id = d.id 
    WHERE a.patient_id = $userId AND a.appointment_date >= CURDATE() AND a.status IN ('pending','approved')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 5
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="welcome-card">
    <h2>👋 Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
    <p>Manage your appointments, view reports, and stay on top of your health.</p>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-details"><h3><?php echo $totalAppointments; ?></h3><p>Total Appointments</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-details"><h3><?php echo $pendingAppts; ?></h3><p>Pending</p></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
        <div class="stat-details"><h3><?php echo $completedAppts; ?></h3><p>Completed</p></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-file-medical"></i></div>
        <div class="stat-details"><h3><?php echo $totalReports; ?></h3><p>Medical Reports</p></div>
    </div>
</div>

<?php if ($unpaidBills > 0): ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> You have unpaid bills totaling <strong>$<?php echo number_format($unpaidBills, 2); ?></strong>. 
    <a href="<?php echo APP_URL; ?>/patient/billing.php" style="font-weight:700;text-decoration:underline;">Pay Now</a>
</div>
<?php endif; ?>

<!-- Upcoming Appointments -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h2>
        <a href="<?php echo APP_URL; ?>/patient/book_appointment.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Book New</a>
    </div>
    <div class="card-body">
        <?php if ($upcoming->num_rows > 0): ?>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while ($u = $upcoming->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Doctor"><strong><?php echo htmlspecialchars($u['doctor_name']); ?></strong></td>
                        <td data-label="Specialization"><?php echo htmlspecialchars($u['specialization']); ?></td>
                        <td data-label="Date"><?php echo date('M d, Y', strtotime($u['appointment_date'])); ?></td>
                        <td data-label="Time"><?php echo date('h:i A', strtotime($u['appointment_time'])); ?></td>
                        <td data-label="Status"><span class="badge badge-<?php echo $u['status']; ?>"><?php echo ucfirst($u['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No upcoming appointments</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
