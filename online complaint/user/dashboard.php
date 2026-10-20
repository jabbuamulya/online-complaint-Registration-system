<?php
$page_title = "Citizen Dashboard";
$user_active_page = "dashboard";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/includes/user_header.php';

$userId = $_SESSION['user_id'];

// Fetch User Complaint Metrics
try {
    // Total complaints
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ?");
    $stmtTotal->execute([$userId]);
    $totalComplaints = $stmtTotal->fetchColumn();

    // Pending complaints (Submitted, Under Review)
    $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ? AND status IN ('Submitted', 'Under Review')");
    $stmtPending->execute([$userId]);
    $pendingComplaints = $stmtPending->fetchColumn();

    // In-progress complaints (Assigned, In Progress)
    $stmtProgress = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ? AND status IN ('Assigned', 'In Progress')");
    $stmtProgress->execute([$userId]);
    $inProgressComplaints = $stmtProgress->fetchColumn();

    // Resolved complaints
    $stmtResolved = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ? AND status = 'Resolved'");
    $stmtResolved->execute([$userId]);
    $resolvedComplaints = $stmtResolved->fetchColumn();

    // Fetch recent 5 complaints
    $stmtRecent = $pdo->prepare("
        SELECT c.*, o.name AS officer_name 
        FROM complaints c
        LEFT JOIN officers o ON c.assigned_to = o.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmtRecent->execute([$userId]);
    $recentComplaints = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading dashboard: ' . e($e->getMessage()) . '</div>';
    $totalComplaints = $pendingComplaints = $inProgressComplaints = $resolvedComplaints = 0;
    $recentComplaints = [];
}
?>

<!-- Citizen Welcome Greeting -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Welcome back, <?php echo e($currentUser['name']); ?>!</h3>
        <p class="text-muted small mb-0">Here is the current status overview of all your registered grievances.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo base_url('user/submit_complaint.php'); ?>" class="btn btn-primary fw-semibold shadow-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> File New Complaint
        </a>
    </div>
</div>

<!-- Four Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Filed</div>
                <div class="stat-value text-primary"><?php echo $totalComplaints; ?></div>
                <span class="text-muted small">Registered cases</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-blue">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Pending / Review</div>
                <div class="stat-value text-amber"><?php echo $pendingComplaints; ?></div>
                <span class="text-muted small">Awaiting review</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-amber">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">In Progress</div>
                <div class="stat-value text-purple"><?php echo $inProgressComplaints; ?></div>
                <span class="text-muted small">Active on-ground work</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-purple">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Resolved</div>
                <div class="stat-value text-success"><?php echo $resolvedComplaints; ?></div>
                <span class="text-muted small">Successfully closed</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Complaints Section -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recent Complaints</h5>
        </div>
        <a href="<?php echo base_url('user/complaints.php'); ?>" class="btn btn-outline-primary btn-sm">
            View All History <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="card-body p-0">
        <?php if (empty($recentComplaints)): ?>
            <div class="text-center py-5">
                <div class="text-muted mb-3" style="font-size: 3rem;">
                    <i class="fa-solid fa-file-circle-check text-secondary-subtle"></i>
                </div>
                <h5 class="fw-bold text-dark">No complaints registered yet</h5>
                <p class="text-muted small mb-3">You have not submitted any civic complaints. Facing a public issue in your area?</p>
                <a href="<?php echo base_url('user/submit_complaint.php'); ?>" class="btn btn-primary btn-sm px-4">
                    <i class="fa-solid fa-plus-circle me-1"></i> File First Complaint
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Complaint ID</th>
                            <th>Title & Category</th>
                            <th>Location</th>
                            <th>Filed Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentComplaints as $cmp): ?>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">
                                        <?php echo e($cmp['complaint_id']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 280px;">
                                        <?php echo e($cmp['title']); ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border small">
                                        <?php echo e($cmp['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted text-truncate d-inline-block" style="max-width: 180px;">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo e($cmp['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary">
                                        <?php echo format_date($cmp['created_at'], 'd M Y'); ?>
                                    </span>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?php echo time_ago($cmp['created_at']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo get_status_badge($cmp['status']); ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo base_url('user/complaint_details.php?id=' . urlencode($cmp['complaint_id'])); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye me-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
