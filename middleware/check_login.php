<?php
// Check if user is logged in
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/browser_cache_control.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to continue.";
    header("Location: " . APP_URL . "/auth/login.php");
    exit();
}
?>
