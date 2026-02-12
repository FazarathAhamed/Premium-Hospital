<?php
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-heartbeat"></i>
        <span>HealthyLife</span>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></span>
            <span class="user-role"><?php echo ucfirst($role); ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
            <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/manage_doctors.php" class="nav-link <?php echo $currentPage === 'manage_doctors.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-md"></i><span>Manage Doctors</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/manage_patients.php" class="nav-link <?php echo $currentPage === 'manage_patients.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i><span>Manage Patients</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/manage_staff.php" class="nav-link <?php echo $currentPage === 'manage_staff.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i><span>Manage Staff</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/user_management.php" class="nav-link <?php echo $currentPage === 'user_management.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i><span>User Management</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/appointments.php" class="nav-link <?php echo $currentPage === 'appointments.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i><span>Appointments</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/billing_reports.php" class="nav-link <?php echo $currentPage === 'billing_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i><span>Billing Reports</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/view_feedback.php" class="nav-link <?php echo $currentPage === 'view_feedback.php' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i><span>Patient Feedback</span>
            </a>

        <?php elseif ($role === 'doctor'): ?>
            <a href="<?php echo APP_URL; ?>/doctor/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/doctor/today_schedule.php" class="nav-link <?php echo $currentPage === 'today_schedule.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i><span>Today's Schedule</span>
            </a>
            <a href="<?php echo APP_URL; ?>/doctor/patients_list.php" class="nav-link <?php echo $currentPage === 'patients_list.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i><span>My Patients</span>
            </a>
            <a href="<?php echo APP_URL; ?>/doctor/patient_details.php" class="nav-link <?php echo $currentPage === 'patient_details.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-medical"></i><span>Patient Details</span>
            </a>
            <a href="<?php echo APP_URL; ?>/doctor/upload_reports.php" class="nav-link <?php echo $currentPage === 'upload_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-upload"></i><span>Upload Reports</span>
            </a>

        <?php elseif ($role === 'patient'): ?>
            <a href="<?php echo APP_URL; ?>/patient/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/patient/book_appointment.php" class="nav-link <?php echo $currentPage === 'book_appointment.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-plus"></i><span>Book Appointment</span>
            </a>
            <a href="<?php echo APP_URL; ?>/patient/appointment_history.php" class="nav-link <?php echo $currentPage === 'appointment_history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i><span>Appointment History</span>
            </a>
            <a href="<?php echo APP_URL; ?>/patient/medical_reports.php" class="nav-link <?php echo $currentPage === 'medical_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-notes-medical"></i><span>Medical Reports</span>
            </a>
            <a href="<?php echo APP_URL; ?>/patient/billing.php" class="nav-link <?php echo $currentPage === 'billing.php' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i><span>Billing & Payments</span>
            </a>
            <a href="<?php echo APP_URL; ?>/patient/my_profile.php" class="nav-link <?php echo $currentPage === 'my_profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-edit"></i><span>My Profile</span>
            </a>
            <a href="<?php echo APP_URL; ?>/patient/feedback.php" class="nav-link <?php echo $currentPage === 'feedback.php' ? 'active' : ''; ?>">
                <i class="fas fa-comment-dots"></i><span>Feedback</span>
            </a>

        <?php elseif ($role === 'receptionist'): ?>
            <a href="<?php echo APP_URL; ?>/receptionist/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/register_patient.php" class="nav-link <?php echo $currentPage === 'register_patient.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i><span>Register Patient</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/book_appointment.php" class="nav-link <?php echo $currentPage === 'book_appointment.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-plus"></i><span>Book Appointment</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/manage_patients.php" class="nav-link <?php echo $currentPage === 'manage_patients.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i><span>Manage Patients</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/manage_appointments.php" class="nav-link <?php echo $currentPage === 'manage_appointments.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i><span>Manage Appointments</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/assign_rooms.php" class="nav-link <?php echo $currentPage === 'assign_rooms.php' ? 'active' : ''; ?>">
                <i class="fas fa-bed"></i><span>Assign Rooms</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/verify_payments.php" class="nav-link <?php echo $currentPage === 'verify_payments.php' ? 'active' : ''; ?>">
                <i class="fas fa-receipt"></i><span>Verify Payments</span>
            </a>
            <a href="<?php echo APP_URL; ?>/receptionist/inquiries.php" class="nav-link <?php echo $currentPage === 'inquiries.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i><span>Inquiries</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo APP_URL; ?>/auth/logout.php" class="nav-link logout-link">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile sidebar toggle -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
        <div class="top-bar-right">
            <span class="greeting">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></span>
            <a href="<?php echo APP_URL; ?>/auth/logout.php" class="btn-logout" title="Logout">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </div>
    <div class="content-area">
