<?php
// Database Configuration
// Enable error reporting for debugging 500 errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Local XAMPP Settings
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', 'FaJa@2004');
    define('DB_NAME', 'hr_db');
    $projectFolder = '/Healthylife';
} else {
    // Live Server Settings (InfinityFree / Wuaze)
    // TODO: UPDATE THESE WITH YOUR INFINITYFREE CREDENTIALS
    define('DB_HOST', 'sql300.infinityfree.com'); // Example: sql300.infinityfree.com
    define('DB_USER', 'if0_38321356');           // Example: if0_38321356
    define('DB_PASS', 'YOUR_VPANEL_PASSWORD');   // Your vPanel password
    define('DB_NAME', 'if0_38321356_hr_db');     // Example: if0_38321356_hr_db
    $projectFolder = ''; // Usually empty if uploaded to htdocs root
}

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Dynamic base URL (Robust Method for all environments)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Calculate project root based on file location
// config/db.php is located in [ROOT]/config/
// So we want the parent directory of this file's directory
$projectRoot = dirname(__DIR__); 

// Get the document root (where the web server points to)
$docRoot = $_SERVER['DOCUMENT_ROOT'];

// Calculate relative path from doc root to project root
// Normalize slashes for Windows/Linux compatibility
$projectRootNormalized = str_replace('\\', '/', $projectRoot);
$docRootNormalized = str_replace('\\', '/', $docRoot);

// If project root starts with doc root, we can determine the subfolder
if (strpos($projectRootNormalized, $docRootNormalized) === 0) {
    $relativePath = substr($projectRootNormalized, strlen($docRootNormalized));
} else {
    // Fallback if paths don't match simply (e.g. symlinks, alias)
    // Assume root if not found, or user can set manually
    $relativePath = ''; 
}

// Ensure no trailing slash
$relativePath = rtrim($relativePath, '/');

define('APP_URL', $protocol . '://' . $host . $relativePath);
?>
