<?php
$pageTitle = "Admin Dashboard";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['admin']);

// Dashboard stats
$totalDoctors = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='doctor'")->fetch_assoc()['c'];
$totalPatients = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='patient'")->fetch_assoc()['c'];
$totalAppointments = $conn->query("SELECT COUNT(*) as c FROM appointments")->fetch_assoc()['c'];
$pendingAppointments = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM billing WHERE payment_status='paid'")->fetch_assoc()['t'];
$totalStaff = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='receptionist'")->fetch_assoc()['c'];

// Recent appointments
$recentAppointments = $conn->query("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name 
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    JOIN users d ON a.doctor_id = d.id 
    ORDER BY a.created_at DESC LIMIT 5
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
        <div class="stat-details">
            <h3><?php echo $totalDoctors; ?></h3>
            <p>Total Doctors</p>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-users"></i></div>
        <div class="stat-details">
            <h3><?php echo $totalPatients; ?></h3>
            <p>Total Patients</p>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-details">
            <h3><?php echo $totalAppointments; ?></h3>
            <p>Total Appointments</p>
        </div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-details">
            <h3><?php echo $pendingAppointments; ?></h3>
            <p>Pending Approvals</p>
        </div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon teal"><i class="fas fa-user-tie"></i></div>
        <div class="stat-details">
            <h3><?php echo $totalStaff; ?></h3>
            <p>Receptionists</p>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon red"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-details">
            <h3>$<?php echo number_format($totalRevenue, 0); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<h3 class="admin-section-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
<div class="quick-actions">
    <a href="<?php echo APP_URL; ?>/admin/manage_doctors.php" class="quick-action-card">
        <i class="fas fa-user-md"></i>
        <span>Manage Doctors</span>
    </a>
    <a href="<?php echo APP_URL; ?>/admin/manage_patients.php" class="quick-action-card">
        <i class="fas fa-users"></i>
        <span>Manage Patients</span>
    </a>
    <a href="<?php echo APP_URL; ?>/admin/appointments.php" class="quick-action-card">
        <i class="fas fa-calendar-alt"></i>
        <span>Appointments</span>
    </a>
    <a href="<?php echo APP_URL; ?>/admin/billing_reports.php" class="quick-action-card">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Billing Reports</span>
    </a>
    <a href="<?php echo APP_URL; ?>/admin/manage_staff.php" class="quick-action-card">
        <i class="fas fa-user-tie"></i>
        <span>Manage Staff</span>
    </a>
</div>

<!-- Recent Appointments Table -->
<div class="card admin-table-card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Recent Appointments</h2>
        <a href="<?php echo APP_URL; ?>/admin/appointments.php" class="btn btn-sm">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentAppointments->num_rows > 0): ?>
                        <?php while ($apt = $recentAppointments->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Patient"><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                            <td data-label="Doctor"><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                            <td data-label="Date"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                            <td data-label="Time"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
                            <td data-label="Status"><span class="badge badge-<?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="empty-state"><i class="fas fa-calendar-times"></i><p>No appointments yet</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
