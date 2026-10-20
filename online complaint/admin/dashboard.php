<?php
$page_title = "Admin Dashboard";
$admin_active_page = "dashboard";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/includes/admin_header.php';

try {
    // 1. Total complaints
    $totalComplaints = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();

    // 2. New complaints (Submitted)
    $newComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Submitted'")->fetchColumn();

    // 3. Pending complaints (Under Review)
    $underReviewComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Under Review'")->fetchColumn();

    // 4. In-progress complaints (Assigned & In Progress)
    $inProgressComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('Assigned', 'In Progress')")->fetchColumn();

    // 5. Resolved complaints
    $resolvedComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Resolved'")->fetchColumn();

    // 6. Rejected complaints
    $rejectedComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Rejected'")->fetchColumn();

    // Additional system counts
    $totalCitizens = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalOfficers = $pdo->query("SELECT COUNT(*) FROM officers WHERE status = 'Active'")->fetchColumn();

    // Fetch latest 6 complaints
    $recentStmt = $pdo->query("
        SELECT c.*, u.name AS citizen_name, u.email AS citizen_email, o.name AS officer_name
        FROM complaints c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN officers o ON c.assigned_to = o.id
        ORDER BY c.created_at DESC
        LIMIT 6
    ");
    $recentComplaints = $recentStmt->fetchAll();

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error fetching admin statistics: ' . e($e->getMessage()) . '</div>';
    $totalComplaints = $newComplaints = $underReviewComplaints = $inProgressComplaints = $resolvedComplaints = $rejectedComplaints = $totalCitizens = $totalOfficers = 0;
    $recentComplaints = [];
}
?>

<!-- Admin Greeting & Quick Actions -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Administrative Grievance Console</h3>
        <p class="text-muted small mb-0">Overview of public complaints, departmental assignments, and SDG 16 resolution benchmarks.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo base_url('admin/complaints.php'); ?>" class="btn btn-primary btn-sm fw-semibold shadow-sm">
            <i class="fa-solid fa-list-check me-1"></i> Manage All Complaints
        </a>
        <a href="<?php echo base_url('admin/reports.php'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-chart-column me-1"></i> View Reports
        </a>
    </div>
</div>

<!-- 6 Core Required Complaint Status Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div>
                <div class="stat-label small">Total</div>
                <div class="stat-value fs-3 text-dark"><?php echo $totalComplaints; ?></div>
                <span class="text-muted" style="font-size: 0.72rem;">All Grievances</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-blue" style="width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-folder"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div>
                <div class="stat-label small">New</div>
                <div class="stat-value fs-3 text-danger"><?php echo $newComplaints; ?></div>
                <span class="text-muted" style="font-size: 0.72rem;">Awaiting Review</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-red" style="width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div>
                <div class="stat-label small">Under Review</div>
                <div class="stat-value fs-3 text-amber"><?php echo $underReviewComplaints; ?></div>
                <span class="text-muted" style="font-size: 0.72rem;">Triage & Audit</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-amber" style="width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div>
                <div class="stat-label small">In Progress</div>
                <div class="stat-value fs-3 text-purple"><?php echo $inProgressComplaints; ?></div>
                <span class="text-muted" style="font-size: 0.72rem;">Assigned / Action</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-purple" style="width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div>
                <div class="stat-label small">Resolved</div>
                <div class="stat-value fs-3 text-success"><?php echo $resolvedComplaints; ?></div>
                <span class="text-muted" style="font-size: 0.72rem;">Redressed</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-green" style="width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="stat-card p-3">
            <div>
                <div class="stat-label small">Rejected</div>
                <div class="stat-value fs-3 text-secondary"><?php echo $rejectedComplaints; ?></div>
                <span class="text-muted" style="font-size: 0.72rem;">Declined Cases</span>
            </div>
            <div class="stat-icon-wrapper bg-light text-secondary" style="width: 44px; height: 44px; font-size: 1.2rem;">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics: Registered Citizens & Officers -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card p-3 bg-white border-0 shadow-sm d-flex flex-row align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper stat-icon-cyan">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><?php echo $totalCitizens; ?> Citizens</h5>
                    <span class="text-muted small">Registered in system with verified contact info</span>
                </div>
            </div>
            <a href="<?php echo base_url('admin/users.php'); ?>" class="btn btn-outline-primary btn-sm">
                Manage Users
            </a>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 bg-white border-0 shadow-sm d-flex flex-row align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon-wrapper stat-icon-blue">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><?php echo $totalOfficers; ?> Active Officers</h5>
                    <span class="text-muted small">Municipal field engineers & department heads</span>
                </div>
            </div>
            <a href="<?php echo base_url('admin/officers.php'); ?>" class="btn btn-outline-primary btn-sm">
                Manage Officers
            </a>
        </div>
    </div>
</div>

<!-- Latest Complaints Table -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-bell me-2 text-warning"></i> Recent Complaints Requiring Attention
            </h5>
        </div>
        <a href="<?php echo base_url('admin/complaints.php'); ?>" class="btn btn-outline-primary btn-sm">
            View All Complaints &rarr;
        </a>
    </div>

    <div class="card-body p-0">
        <?php if (empty($recentComplaints)): ?>
            <div class="text-center py-5">
                <h6 class="text-muted">No complaints recorded yet in the database.</h6>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>ID</th>
                            <th>Citizen</th>
                            <th>Category & Title</th>
                            <th>Location</th>
                            <th>Filed</th>
                            <th>Assigned Officer</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentComplaints as $c): ?>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">
                                        <?php echo e($c['complaint_id']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo e($c['citizen_name']); ?></div>
                                    <div class="text-muted small"><?php echo e($c['citizen_email']); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;">
                                        <?php echo e($c['title']); ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border small">
                                        <?php echo e($c['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-muted text-truncate d-inline-block" style="max-width: 140px;">
                                        <?php echo e($c['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?php echo format_date($c['created_at'], 'd M Y'); ?></span>
                                </td>
                                <td>
                                    <span class="small">
                                        <?php echo !empty($c['officer_name']) ? e($c['officer_name']) : '<span class="text-danger small fst-italic">Unassigned</span>'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo get_status_badge($c['status']); ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo base_url('admin/complaint_details.php?id=' . urlencode($c['complaint_id'])); ?>" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-gear me-1"></i> Manage
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

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
