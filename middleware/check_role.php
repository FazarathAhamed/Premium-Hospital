<?php
// Role-based access control
// Usage: require this file, then call checkRole(['admin']) etc.

function checkRole($allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        // Redirect to their own dashboard or login
        if (isset($_SESSION['role'])) {
            $role = $_SESSION['role'];
            header("Location: " . APP_URL . "/$role/dashboard.php");
        } else {
            header("Location: " . APP_URL . "/auth/login.php");
        }
        exit();
    }
}
?>
