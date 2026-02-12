<?php
$pageTitle = "Register Patient";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['receptionist']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];
    $dob = $_POST['date_of_birth'];
    $address = trim($_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email already exists. Please use a different email.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone, gender, date_of_birth, address) VALUES (?, ?, ?, 'patient', ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $password, $phone, $gender, $dob, $address);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Patient registered successfully!";
        } else {
            $_SESSION['error'] = "Failed to register patient. Please try again.";
        }
    }
    header("Location: " . APP_URL . "/receptionist/register_patient.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="grid-2">
    <div class="card register-form-card">
        <div class="card-header">
            <h2><i class="fas fa-user-plus"></i> Register New Patient</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="patient@example.com" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+94 77 123 4567">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">-- Select Gender --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Set initial password" required minlength="6">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Enter patient address"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="background:var(--receptionist-accent);">
                    <i class="fas fa-user-plus"></i> Register Patient
                </button>
            </form>
        </div>
    </div>

    <!-- Recently Registered Patients -->
    <div>
        <h3 class="admin-section-title"><i class="fas fa-history"></i> Recently Registered</h3>
        <?php
        $recentPatients = $conn->query("SELECT * FROM users WHERE role='patient' ORDER BY created_at DESC LIMIT 5");
        if ($recentPatients->num_rows > 0):
            while ($p = $recentPatients->fetch_assoc()):
        ?>
        <div class="room-card">
            <div class="room-info">
                <h4><?php echo htmlspecialchars($p['full_name']); ?></h4>
                <div class="room-meta">
                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($p['email']); ?></span>
                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($p['phone'] ?? 'N/A'); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($p['created_at'])); ?></span>
                </div>
            </div>
            <span class="badge <?php echo $p['is_active'] ? 'badge-approved' : 'badge-cancelled'; ?>">
                <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
            </span>
        </div>
        <?php endwhile; else: ?>
            <div class="empty-state"><i class="fas fa-users"></i><p>No patients registered yet</p></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
