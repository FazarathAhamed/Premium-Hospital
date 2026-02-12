<?php
// Dynamic header component
$role = $_SESSION['role'] ?? '';
$fullName = $_SESSION['full_name'] ?? 'User';

// Role-based theme colors
$themeColors = [
    'admin'        => '#1a1f3d',
    'doctor'       => '#0d4f4f',
    'patient'      => '#1b3a5c',
    'receptionist' => '#3d1a3d'
];
$themeColor = $themeColors[$role] ?? '#1a1f3d';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HealthyLife Hospital Management System - <?php echo ucfirst($role); ?> Portal">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>HealthyLife - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/includes/includes.css">
    <?php if (!empty($roleCss)): ?>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/<?php echo $role; ?>/<?php echo $role; ?>.css">
    <?php endif; ?>
    <style>
        :root { --theme-primary: <?php echo $themeColor; ?>; }
    </style>
</head>
<body>
<div class="app-layout">
