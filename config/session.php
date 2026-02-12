<?php
if (session_status() === PHP_SESSION_NONE) {
    session_cache_limiter('nocache');
    session_start();
}

// Session timeout (30 minutes)
$timeout = 1800;

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['error'] = "Session expired. Please login again.";
        header("Location: " . APP_URL . "/index.php");
        exit();
    }
}
$_SESSION['last_activity'] = time();
?>
