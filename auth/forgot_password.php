<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed, $email);
            $update->execute();
            $update->close();
            
            $_SESSION['success'] = "Password reset successfully! Please login.";
            header("Location: " . APP_URL . "/auth/login.php");
            exit();
        } else {
            $error = "No account found with this email.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthyLife - Reset Password</title>
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
            </div>
        </div>
        <div class="auth-right">
            <div class="auth-form-container">
                <a href="<?php echo APP_URL; ?>/index.php" class="btn-home-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <div class="auth-form-header">
                    <h2>Reset Password</h2>
                    <p>Enter your email and new password</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@healthylife.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Min 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Retype password" required>
                    </div>
                    <button type="submit" class="btn btn-auth">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </form>
                <div class="auth-footer">
                    <p>Remember your password? <a href="<?php echo APP_URL; ?>/auth/login.php">Sign In</a></p>
                    <a href="<?php echo APP_URL; ?>/index.php" class="btn-home-footer">
                        <i class="fas fa-home"></i> Go to Home Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
