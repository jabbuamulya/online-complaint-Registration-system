<?php
/**
 * Application Helpers & Utility Functions
 * Online Complaint Registration System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * XSS Clean output
 */
function e($string) {
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Dynamic Base URL detection for portable XAMPP paths
 */
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine base folder from document root and project root directory
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    
    if (!empty($docRoot) && stripos($projectRoot, $docRoot) === 0) {
        $relativeFolder = substr($projectRoot, strlen($docRoot));
    } else {
        $relativeFolder = str_ireplace($docRoot, '', $projectRoot);
    }
    
    $relativeFolder = trim($relativeFolder, '/');
    $base = $protocol . $host . ($relativeFolder !== '' ? '/' . $relativeFolder : '');
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Complaint Categories List
 */
function get_categories() {
    return [
        'Roads' => ['icon' => 'fa-road', 'color' => '#f59e0b'],
        'Water Supply' => ['icon' => 'fa-faucet-drip', 'color' => '#0284c7'],
        'Electricity' => ['icon' => 'fa-bolt', 'color' => '#eab308'],
        'Sanitation' => ['icon' => 'fa-pump-soap', 'color' => '#059669'],
        'Street Lights' => ['icon' => 'fa-lightbulb', 'color' => '#ca8a04'],
        'Public Transport' => ['icon' => 'fa-bus', 'color' => '#6366f1'],
        'Garbage' => ['icon' => 'fa-trash-can', 'color' => '#d97706'],
        'Drainage' => ['icon' => 'fa-water', 'color' => '#0d9488'],
        'Other' => ['icon' => 'fa-layer-group', 'color' => '#64748b']
    ];
}

/**
 * Valid Complaint Statuses
 */
function get_statuses() {
    return [
        'Submitted',
        'Under Review',
        'Assigned',
        'In Progress',
        'Resolved',
        'Rejected'
    ];
}

/**
 * Render visual badge for complaint status
 */
function get_status_badge($status) {
    switch ($status) {
        case 'Submitted':
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="fa-solid fa-paper-plane me-1"></i> Submitted</span>';
        case 'Under Review':
            return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="fa-solid fa-magnifying-glass me-1"></i> Under Review</span>';
        case 'Assigned':
            return '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="fa-solid fa-user-tag me-1"></i> Assigned</span>';
        case 'In Progress':
            return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-arrows-rotate me-1"></i> In Progress</span>';
        case 'Resolved':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-circle-check me-1"></i> Resolved</span>';
        case 'Rejected':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa-solid fa-circle-xmark me-1"></i> Rejected</span>';
        default:
            return '<span class="badge bg-light text-dark">' . e($status) . '</span>';
    }
}

/**
 * Format datetime string
 */
function format_date($datetime, $format = 'd M Y, h:i A') {
    if (!$datetime) return 'N/A';
    $time = strtotime($datetime);
    return date($format, $time);
}

/**
 * Human-readable relative time (e.g. 2 hours ago)
 */
function time_ago($datetime) {
    if (!$datetime) return 'N/A';
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('d M Y', $timestamp);
    }
}

/**
 * Generate unique complaint ID: CMP + YEAR + 4-digit number
 * e.g., CMP20260001
 */
function generate_complaint_id($pdo) {
    $currentYear = date('Y');
    $prefix = "CMP" . $currentYear;
    
    // Look up max existing complaint ID for the current year
    $stmt = $pdo->prepare("SELECT complaint_id FROM complaints WHERE complaint_id LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $lastId = $stmt->fetchColumn();

    if ($lastId) {
        // Extract sequence number
        $sequence = (int)substr($lastId, strlen($prefix));
        $nextSequence = $sequence + 1;
    } else {
        $nextSequence = 1;
    }

    return $prefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
}

/**
 * Flash message helper
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function render_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = 'fa-info-circle';
        if ($flash['type'] === 'success') $icon = 'fa-check-circle';
        if ($flash['type'] === 'danger') $icon = 'fa-exclamation-triangle';
        if ($flash['type'] === 'warning') $icon = 'fa-triangle-exclamation';

        echo '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="fa-solid ' . $icon . ' fs-5 me-2"></i>
                <div class="flex-grow-1">' . $flash['message'] . '</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/**
 * Citizen Authentication Helpers
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to access your account.');
        header('Location: ' . base_url('login.php'));
        exit();
    }
}

/**
 * Admin Authentication Helpers
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_admin() {
    if (!is_admin_logged_in()) {
        set_flash('danger', 'Administrator privileges required. Please sign in.');
        header('Location: ' . base_url('admin/login.php'));
        exit();
    }
}
