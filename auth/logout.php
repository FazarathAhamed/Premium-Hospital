<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Clear client-side data
header("Clear-Site-Data: \"cache\", \"storage\", \"executionContexts\"");

session_unset();
session_destroy();

header("Location: " . APP_URL . "/index.php");
exit();
?>
