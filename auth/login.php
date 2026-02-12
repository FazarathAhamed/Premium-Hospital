<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/browser_cache_control.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . APP_URL . "/" . strtolower($_SESSION['role']) . "/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (!$user['is_active']) {
                $error = "Your account has been deactivated. Contact admin.";
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                header("Location: " . APP_URL . "/" . strtolower($user['role']) . "/dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
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
    <meta name="description" content="Login to HealthyLife Hospital Management System">
    <title>HealthyLife - Login</title>
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
                    <div class="feature"><i class="fas fa-shield-alt"></i><span>Secure Access</span></div>
                    <div class="feature"><i class="fas fa-clock"></i><span>24/7 Available</span></div>
                    <div class="feature"><i class="fas fa-users"></i><span>Multi-Role Support</span></div>
                </div>
            </div>
        </div>
        <div class="auth-right">
            <div class="auth-form-container">
                <a href="<?php echo APP_URL; ?>/index.php" class="btn-home-link">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <div class="auth-form-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@healthylife.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-options">
                        <a href="<?php echo APP_URL; ?>/auth/forgot_password.php" class="forgot-link">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-auth">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                <div class="auth-footer">
                    <p>Don't have an account? <a href="<?php echo APP_URL; ?>/auth/register.php">Register as Patient</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>
</body>
</html>
