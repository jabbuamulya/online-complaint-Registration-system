<?php
/**
 * User Dashboard Header Component
 * Online Complaint Registration System
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

// Protect user dashboard pages
require_login();

$page_title = $page_title ?? 'Citizen Dashboard';
$user_active_page = $user_active_page ?? 'dashboard';

// Fetch current user details from DB for latest info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    // If user was deleted or invalid
    session_destroy();
    header('Location: ' . base_url('login.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - Citizen Portal</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231e40af'><path d='M12 2L1 21h22L12 2zm0 3.8L19.5 19h-15L12 5.8zM11 10h2v4h-2zm0 6h2v2h-2z'/></svg>">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body class="bg-light">

<!-- Top Gov Strip -->
<div class="gov-top-strip">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <span><i class="fa-solid fa-flag text-warning me-1"></i> Citizen Grievance Redressal Portal</span>
        </div>
        <div>
            <span class="sdg-badge">
                <i class="fa-solid fa-scale-balanced"></i> SDG 16: Accountable Institutions
            </span>
        </div>
    </div>
</div>

<!-- Citizen Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: #0a2540;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo base_url('user/dashboard.php'); ?>">
            <div class="brand-icon-box" style="width: 38px; height: 38px; font-size: 1rem; background: #0284c7;">
                <i class="fa-solid fa-landmark-dome"></i>
            </div>
            <div>
                <div class="fw-bold fs-6 lh-1">CIVIC PORTAL</div>
                <div class="text-white-50" style="font-size: 0.7rem; letter-spacing: 0.5px;">Citizen Portal</div>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
            <i class="fa-solid fa-bars text-white"></i>
        </button>

        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav me-auto ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($user_active_page === 'dashboard') ? 'fw-bold text-warning active' : ''; ?>" href="<?php echo base_url('user/dashboard.php'); ?>">
                        <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($user_active_page === 'submit') ? 'fw-bold text-warning active' : ''; ?>" href="<?php echo base_url('user/submit_complaint.php'); ?>">
                        <i class="fa-solid fa-file-circle-plus me-1"></i> Register Complaint
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($user_active_page === 'complaints') ? 'fw-bold text-warning active' : ''; ?>" href="<?php echo base_url('user/complaints.php'); ?>">
                        <i class="fa-solid fa-clipboard-list me-1"></i> My Complaints
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="<?php echo base_url('track.php'); ?>">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Public Tracker
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo base_url('user/profile.php'); ?>" class="text-white text-decoration-none d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="d-none d-sm-block text-start">
                        <div class="fw-semibold small leading-tight"><?php echo e($currentUser['name']); ?></div>
                        <div class="text-white-50" style="font-size: 0.72rem;"><?php echo e($currentUser['email']); ?></div>
                    </div>
                </a>
                <a href="<?php echo base_url('logout.php'); ?>" class="btn btn-sm btn-outline-light rounded-pill px-3" title="Sign Out">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span class="d-none d-md-inline">Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php render_flash(); ?>
