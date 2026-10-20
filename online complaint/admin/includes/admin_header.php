<?php
/**
 * Administrator Layout Header
 * Online Complaint Registration System
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

// Protect admin pages
require_admin();

$page_title = $page_title ?? 'Admin Console';
$admin_active_page = $admin_active_page ?? 'dashboard';

// Fetch admin user details
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([$adminId]);
$currentAdmin = $stmt->fetch();

// Quick badge counts for pending complaints
$badgeNewCount = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Submitted'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - Admin Control Center</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230a2540'><path d='M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z'/></svg>">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body class="bg-light">

<!-- Top Admin Bar -->
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-2 shadow-sm" style="background: #091a2b !important;">
    <div class="container-fluid px-3">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 fs-6 fw-bold d-flex align-items-center gap-2" href="<?php echo base_url('admin/dashboard.php'); ?>">
            <div class="brand-icon-box" style="width: 32px; height: 32px; font-size: 0.9rem; background: #e11d48;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <span>ADMIN PORTAL</span>
        </a>

        <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-nav flex-row gap-3 align-items-center ms-auto">
            <a href="<?php echo base_url('index.php'); ?>" target="_blank" class="nav-link text-white-50 small d-none d-sm-inline">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Public Site
            </a>

            <div class="nav-item text-nowrap dropdown">
                <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.8rem;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <span class="small fw-semibold d-none d-sm-inline"><?php echo e($currentAdmin['name'] ?? 'Administrator'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li class="dropdown-header small text-muted">Signed in as Administrator</li>
                    <li><span class="dropdown-item-text small fw-bold"><?php echo e($currentAdmin['username'] ?? 'admin'); ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger small" href="<?php echo base_url('admin/logout.php'); ?>">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Log Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <nav id="adminSidebar" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse dashboard-sidebar p-3">
            <div class="position-sticky pt-2">
                <div class="text-uppercase text-secondary small fw-bold px-3 mb-2" style="font-size: 0.72rem; letter-spacing: 0.6px;">Main Menu</div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="sidebar-nav-link <?php echo ($admin_active_page === 'dashboard') ? 'active' : ''; ?>" href="<?php echo base_url('admin/dashboard.php'); ?>">
                            <i class="fa-solid fa-gauge-high"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="sidebar-nav-link justify-content-between <?php echo ($admin_active_page === 'complaints') ? 'active' : ''; ?>" href="<?php echo base_url('admin/complaints.php'); ?>">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-list-check"></i> Manage Complaints
                            </div>
                            <?php if ($badgeNewCount > 0): ?>
                                <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?php echo $badgeNewCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="sidebar-nav-link <?php echo ($admin_active_page === 'reports') ? 'active' : ''; ?>" href="<?php echo base_url('admin/reports.php'); ?>">
                            <i class="fa-solid fa-chart-pie"></i> Grievance Reports
                        </a>
                    </li>
                </ul>

                <hr class="my-3">
                <div class="text-uppercase text-secondary small fw-bold px-3 mb-2" style="font-size: 0.72rem; letter-spacing: 0.6px;">Administration</div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="sidebar-nav-link <?php echo ($admin_active_page === 'users') ? 'active' : ''; ?>" href="<?php echo base_url('admin/users.php'); ?>">
                            <i class="fa-solid fa-users"></i> Registered Citizens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="sidebar-nav-link <?php echo ($admin_active_page === 'officers') ? 'active' : ''; ?>" href="<?php echo base_url('admin/officers.php'); ?>">
                            <i class="fa-solid fa-user-shield"></i> Department Officers
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="sidebar-nav-link text-danger" href="<?php echo base_url('admin/logout.php'); ?>">
                            <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                        </a>
                    </li>
                </ul>

                <div class="mt-4 p-3 bg-light rounded-3 text-center border">
                    <span class="sdg-badge mb-1">
                        <i class="fa-solid fa-scale-balanced"></i> SDG 16
                    </span>
                    <div class="text-muted" style="font-size: 0.72rem;">Peace, Justice & Strong Institutions</div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php render_flash(); ?>
