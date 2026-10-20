<?php
/**
 * Public Header Component
 * Online Complaint Registration System
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$page_title = $page_title ?? 'Online Complaint Registration System';
$active_page = $active_page ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - <?php echo APP_NAME; ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231e40af'><path d='M12 2L1 21h22L12 2zm0 3.8L19.5 19h-15L12 5.8zM11 10h2v4h-2zm0 6h2v2h-2z'/></svg>">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>

<!-- Top Civic SDG Strip -->
<div class="gov-top-strip">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <span><i class="fa-solid fa-flag me-1 text-warning"></i> National Public Grievance Portal</span>
            <span class="d-none d-md-inline text-secondary">|</span>
            <span class="d-none d-md-inline"><i class="fa-solid fa-clock me-1"></i> Redressal SLA: 48 Hours Response</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="sdg-badge" title="Aligned with UN Sustainable Development Goal 16">
                <i class="fa-solid fa-scale-balanced"></i> SDG 16: Peace, Justice & Strong Institutions
            </span>
        </div>
    </div>
</div>

<!-- Main Navigation -->
<nav class="navbar navbar-expand-lg navbar-gov sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo base_url('index.php'); ?>">
            <div class="brand-icon-box">
                <i class="fa-solid fa-landmark-dome"></i>
            </div>
            <div>
                <div class="lh-1 text-primary-gov" style="font-size: 1.15rem; font-weight: 800;">CIVIC RESOLVE</div>
                <div class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.8px; font-weight: 600; text-transform: uppercase;">
                    Complaint Registration Portal
                </div>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar" aria-controls="publicNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars fs-4 text-dark"></i>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-nav-item">
                    <a class="nav-link <?php echo ($active_page === 'home') ? 'active' : ''; ?>" href="<?php echo base_url('index.php'); ?>">
                        <i class="fa-solid fa-house me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page === 'about') ? 'active' : ''; ?>" href="<?php echo base_url('about.php'); ?>">
                        <i class="fa-solid fa-circle-info me-1"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page === 'track') ? 'active' : ''; ?>" href="<?php echo base_url('track.php'); ?>">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Track Complaint
                    </a>
                </li>

                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-primary fw-semibold" href="<?php echo base_url('user/dashboard.php'); ?>">
                            <i class="fa-solid fa-gauge-high me-1"></i> Citizen Dashboard
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-danger btn-sm px-3 rounded-pill" href="<?php echo base_url('logout.php'); ?>">
                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-primary btn-sm px-3 rounded-pill <?php echo ($active_page === 'login') ? 'active' : ''; ?>" href="<?php echo base_url('login.php'); ?>">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-1">
                        <a class="btn btn-primary btn-sm px-3 rounded-pill text-white <?php echo ($active_page === 'register') ? 'active' : ''; ?>" href="<?php echo base_url('register.php'); ?>">
                            <i class="fa-solid fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item ms-lg-2 d-none d-lg-block border-start ps-2">
                    <a class="btn btn-light btn-sm text-secondary rounded-pill" href="<?php echo base_url('admin/login.php'); ?>" title="Administrative Access">
                        <i class="fa-solid fa-shield-halved text-primary"></i> Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>
    <div class="container mt-3">
        <?php render_flash(); ?>
    </div>
