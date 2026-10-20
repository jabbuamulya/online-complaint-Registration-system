<?php
$page_title = "My Registered Complaints";
$user_active_page = "complaints";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/includes/user_header.php';

$userId = $_SESSION['user_id'];
$categories = get_categories();
$statuses = get_statuses();

// Filter & Search Params
$filterStatus = $_GET['status'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$searchQuery = trim($_GET['search'] ?? '');

// Construct Query
$sql = "
    SELECT c.*, o.name AS officer_name, o.department AS officer_dept
    FROM complaints c
    LEFT JOIN officers o ON c.assigned_to = o.id
    WHERE c.user_id = ?
";
$params = [$userId];

if (!empty($filterStatus) && in_array($filterStatus, $statuses)) {
    $sql .= " AND c.status = ?";
    $params[] = $filterStatus;
}

if (!empty($filterCategory) && array_key_exists($filterCategory, $categories)) {
    $sql .= " AND c.category = ?";
    $params[] = $filterCategory;
}

if (!empty($searchQuery)) {
    $sql .= " AND (c.complaint_id LIKE ? OR c.title LIKE ? OR c.location LIKE ?)";
    $term = '%' . $searchQuery . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY c.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error retrieving complaints: ' . e($e->getMessage()) . '</div>';
    $complaints = [];
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">My Grievance History</h3>
        <p class="text-muted small mb-0">Track all civic issues you have reported across municipal departments.</p>
    </div>
    <a href="<?php echo base_url('user/submit_complaint.php'); ?>" class="btn btn-primary fw-semibold shadow-sm">
        <i class="fa-solid fa-plus-circle me-1"></i> Register New Complaint
    </a>
</div>

<!-- Search & Filters Toolbar -->
<div class="card card-custom p-3 mb-4 border-0 shadow-sm">
    <form action="<?php echo base_url('user/complaints.php'); ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by ID, title or location..." value="<?php echo e($searchQuery); ?>">
            </div>
        </div>

        <div class="col-md-3 col-6">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo e($st); ?>" <?php echo ($filterStatus === $st) ? 'selected' : ''; ?>>
                        <?php echo e($st); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 col-6">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat => $val): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo ($filterCategory === $cat) ? 'selected' : ''; ?>>
                        <?php echo e($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
            <?php if (!empty($searchQuery) || !empty($filterStatus) || !empty($filterCategory)): ?>
                <a href="<?php echo base_url('user/complaints.php'); ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Complaints Table -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($complaints)): ?>
            <div class="text-center py-5">
                <div class="text-muted mb-3" style="font-size: 2.8rem;">
                    <i class="fa-solid fa-inbox text-secondary-subtle"></i>
                </div>
                <h5 class="fw-bold">No complaints found</h5>
                <p class="text-muted small mb-3">No grievance records match your current search or filter criteria.</p>
                <a href="<?php echo base_url('user/submit_complaint.php'); ?>" class="btn btn-primary btn-sm px-4">
                    <i class="fa-solid fa-plus-circle me-1"></i> Register a Complaint
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Complaint ID</th>
                            <th>Category & Title</th>
                            <th>Location</th>
                            <th>Filed Date</th>
                            <th>Officer Assigned</th>
                            <th>Current Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $c): ?>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">
                                        <?php echo e($c['complaint_id']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                        <?php echo e($c['title']); ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border small">
                                        <?php echo e($c['category']); ?>
                                    </span>
                                    <?php if (!empty($c['attachment'])): ?>
                                        <i class="fa-solid fa-paperclip text-muted small ms-1" title="Attachment included"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-muted text-truncate d-inline-block" style="max-width: 170px;">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo e($c['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary">
                                        <?php echo format_date($c['created_at'], 'd M Y'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-dark">
                                        <?php echo !empty($c['officer_name']) ? e($c['officer_name']) : '<span class="text-muted fst-italic">Pending</span>'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo get_status_badge($c['status']); ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo base_url('user/complaint_details.php?id=' . urlencode($c['complaint_id'])); ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        </a>
                                        <a href="<?php echo base_url('track.php?id=' . urlencode($c['complaint_id'])); ?>" class="btn btn-outline-secondary" title="Public Timeline">
                                            <i class="fa-solid fa-route"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between align-items-center">
                <span>Showing <strong><?php echo count($complaints); ?></strong> complaint(s)</span>
                <span>Sorted by latest registered date</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
