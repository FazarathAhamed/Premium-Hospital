<?php
$pageTitle = "My Profile";
$roleCss = true;
require_once __DIR__ . '/../middleware/check_login.php';
require_once __DIR__ . '/../middleware/check_role.php';
checkRole(['patient']);

$userId = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'];
        $dob = $_POST['date_of_birth'];
        $address = trim($_POST['address']);
        
        $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, gender=?, date_of_birth=?, address=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $phone, $gender, $dob, $address, $userId);
        $stmt->execute();
        
        $_SESSION['full_name'] = $name;
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: " . APP_URL . "/patient/my_profile.php");
        exit();
    }
    
    if ($_POST['action'] === 'change_password') {
        $currentPw = $_POST['current_password'];
        $newPw = $_POST['new_password'];
        $confirmPw = $_POST['confirm_password'];
        
        $user = $conn->query("SELECT password FROM users WHERE id = $userId")->fetch_assoc();
        
        if (!password_verify($currentPw, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
        } elseif ($newPw !== $confirmPw) {
            $_SESSION['error'] = "New passwords do not match.";
        } elseif (strlen($newPw) < 6) {
            $_SESSION['error'] = "New password must be at least 6 characters.";
        } else {
            $hashed = password_hash($newPw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $userId);
            $stmt->execute();
            $_SESSION['success'] = "Password changed successfully!";
        }
        header("Location: " . APP_URL . "/patient/my_profile.php");
        exit();
    }
}

// Fetch user data
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

// Fetch stats
$totalAppts = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE patient_id = $userId")->fetch_assoc()['c'];
$totalReports = $conn->query("SELECT COUNT(*) as c FROM medical_reports WHERE patient_id = $userId")->fetch_assoc()['c'];
$totalPaid = $conn->query("SELECT COALESCE(SUM(amount), 0) as t FROM billing WHERE patient_id = $userId AND payment_status='paid'")->fetch_assoc()['t'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Profile Header Card -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-body" style="padding:30px;">
        <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;flex-shrink:0;">
                <i class="fas fa-user"></i>
            </div>
            <div style="flex:1;">
                <h2 style="font-size:24px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </h2>
                <p style="font-size:14px;color:var(--text-muted);">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?> &nbsp;•&nbsp;
                    <i class="fas fa-calendar"></i> Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                </p>
            </div>
            <div style="display:flex;gap:20px;text-align:center;flex-wrap:wrap;justify-content:center;">
                <div>
                    <div style="font-size:24px;font-weight:700;color:var(--text-primary);"><?php echo $totalAppts; ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Appointments</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;color:var(--text-primary);"><?php echo $totalReports; ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Reports</div>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:700;color:var(--text-primary);">$<?php echo number_format($totalPaid, 0); ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Total Paid</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Profile Info Form -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-edit"></i> Personal Information</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small style="color:var(--text-muted);font-size:11px;">Email cannot be changed</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="form-control">
                            <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo $user['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo $user['date_of_birth'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-lock"></i> Security</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
