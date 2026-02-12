<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/browser_cache_control.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . APP_URL . "/" . strtolower($_SESSION['role']) . "/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['date_of_birth'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'patient';
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone, gender, date_of_birth, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $full_name, $email, $hashed, $role, $phone, $gender, $dob, $address);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: " . APP_URL . "/auth/login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register as a patient at HealthyLife Hospital">
    <title>HealthyLife - Patient Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/auth/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="auth-branding">
                <div class="brand-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h1>HealthyLife</h1>
                <p>Hospital Management System</p>
                <div class="brand-features">
                    <div class="feature"><i class="fas fa-calendar-check"></i><span>Book Appointments</span></div>
                    <div class="feature"><i class="fas fa-file-medical"></i><span>View Medical Reports</span></div>
                    <div class="feature"><i class="fas fa-comment-medical"></i><span>Send Feedback</span></div>
                </div>
            </div>
        </div>
        <div class="auth-right">
            <div class="auth-form-container">
                <a href="<?php echo APP_URL; ?>/index.php" class="btn-home-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <div class="auth-form-header">
                    <h2>Create Account</h2>
                    <p>Register as a new patient</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="John Doe" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="you@healthylife.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="+94771234567" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender"><i class="fas fa-venus-mars"></i> Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">Select</option>
                                <option value="male" <?php echo ($gender ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($gender ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($gender ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth"><i class="fas fa-calendar"></i> Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($dob ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2" placeholder="Your address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password *</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Retype password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-auth">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
                <div class="auth-footer">
                    <p>Already have an account? <a href="<?php echo APP_URL; ?>/auth/login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
