<?php
$pageTitle = "Doctor Dashboard";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['doctor']);

$doctorId = $_SESSION['user_id'];

// Stats - Only count approved/completed patients (not pending - those are for receptionist)
$totalPatients = $conn->query("SELECT COUNT(DISTINCT patient_id) as c FROM appointments WHERE doctor_id = $doctorId AND status IN ('approved','completed')")->fetch_assoc()['c'];
$todayAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctorId AND appointment_date = CURDATE() AND status IN ('approved','completed')")->fetch_assoc()['c'];
$approvedAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctorId AND status='approved'")->fetch_assoc()['c'];
$completedAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = $doctorId AND status='completed'")->fetch_assoc()['c'];
$reportsUploaded = $conn->query("SELECT COUNT(*) as c FROM medical_reports WHERE doctor_id = $doctorId")->fetch_assoc()['c'];

// Today's schedule - Only show approved appointments
$todaySchedule = $conn->query("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id
    WHERE a.doctor_id = $doctorId AND a.appointment_date = CURDATE() AND a.status IN ('approved','completed')
    ORDER BY a.appointment_time ASC
");

// Upcoming appointments (future days) - Only show approved
$upcomingAppts = $conn->query("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id
    WHERE a.doctor_id = $doctorId AND a.appointment_date > CURDATE() AND a.status = 'approved'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 10
");

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-details"><h3><?php echo $totalPatients; ?></h3><p>Total Patients</p></div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon orange"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-details"><h3><?php echo $todayAppts; ?></h3><p>Today's Appointments</p></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
        <div class="stat-details"><h3><?php echo $approvedAppts; ?></h3><p>Awaiting Consultation</p></div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-details"><h3><?php echo $completedAppts; ?></h3><p>Completed</p></div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon teal"><i class="fas fa-file-medical"></i></div>
        <div class="stat-details"><h3><?php echo $reportsUploaded; ?></h3><p>Reports Uploaded</p></div>
    </div>
</div>

<div class="grid-2">
    <!-- Today's Schedule -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-day"></i> Today's Schedule</h2>
            <a href="<?php echo APP_URL; ?>/doctor/today_schedule.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if ($todaySchedule->num_rows > 0): ?>
                <?php while ($appt = $todaySchedule->fetch_assoc()): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border-color);flex-wrap:wrap;gap:8px;">
                    <div>
                        <strong style="font-size:14px;color:var(--text-primary);"><?php echo htmlspecialchars($appt['patient_name']); ?></strong>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                            <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($appt['appointment_time'])); ?>
                            <?php if ($appt['patient_phone']): ?>
                                &nbsp;•&nbsp; <i class="fas fa-phone"></i> <?php echo htmlspecialchars($appt['patient_phone']); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($appt['reason']): ?>
                            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">
                                <i class="fas fa-comment"></i> <?php echo htmlspecialchars($appt['reason']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span class="badge badge-<?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-calendar-check"></i><p>No appointments today</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h2>
        </div>
        <div class="card-body">
            <?php if ($upcomingAppts->num_rows > 0): ?>
                <?php $lastDate = ''; while ($appt = $upcomingAppts->fetch_assoc()): ?>
                    <?php 
                    $apptDate = date('M d, Y (l)', strtotime($appt['appointment_date']));
                    if ($apptDate !== $lastDate): 
                        $lastDate = $apptDate;
                    ?>
                        <div style="font-size:12px;font-weight:700;color:var(--text-accent);text-transform:uppercase;letter-spacing:0.5px;padding:8px 0 4px;margin-top:8px;border-bottom:2px solid var(--border-color);">
                            <i class="fas fa-calendar"></i> <?php echo $apptDate; ?>
                        </div>
                    <?php endif; ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f3f4f6;flex-wrap:wrap;gap:6px;">
                        <div>
                            <strong style="font-size:14px;color:var(--text-primary);"><?php echo htmlspecialchars($appt['patient_name']); ?></strong>
                            <span style="font-size:12px;color:var(--text-muted);margin-left:8px;">
                                <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($appt['appointment_time'])); ?>
                            </span>
                        </div>
                        <span class="badge badge-<?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state"><i class="fas fa-calendar-times"></i><p>No upcoming appointments</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
