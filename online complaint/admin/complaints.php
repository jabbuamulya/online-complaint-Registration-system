<?php
$page_title = "Manage Complaints";
$admin_active_page = "complaints";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

$categories = get_categories();
$statuses = get_statuses();

// Handle Complaint Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_complaint') {
    $complaintIdToDelete = trim($_POST['complaint_id'] ?? '');
    if (!empty($complaintIdToDelete)) {
        try {
            // Fetch attachment if any to delete file from disk
            $attStmt = $pdo->prepare("SELECT attachment FROM complaints WHERE complaint_id = ?");
            $attStmt->execute([$complaintIdToDelete]);
            $att = $attStmt->fetchColumn();

            if ($att) {
                $filePath = __DIR__ . '/../../uploads/' . $att;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $delStmt = $pdo->prepare("DELETE FROM complaints WHERE complaint_id = ?");
            $delStmt->execute([$complaintIdToDelete]);

            set_flash('success', "Complaint $complaintIdToDelete deleted successfully.");
            header('Location: ' . base_url('admin/complaints.php'));
            exit();
        } catch (PDOException $e) {
            set_flash('danger', 'Error deleting complaint: ' . $e->getMessage());
        }
    }
}

// Filters
$search = trim($_GET['search'] ?? '');
$filterCategory = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build Query
$sql = "
    SELECT c.*, u.name AS citizen_name, u.email AS citizen_email, u.phone AS citizen_phone,
           o.name AS officer_name, o.department AS officer_dept
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN officers o ON c.assigned_to = o.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.complaint_id LIKE ? OR c.title LIKE ? OR u.name LIKE ? OR c.location LIKE ?)";
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($filterCategory) && array_key_exists($filterCategory, $categories)) {
    $sql .= " AND c.category = ?";
    $params[] = $filterCategory;
}

if (!empty($filterStatus) && in_array($filterStatus, $statuses)) {
    $sql .= " AND c.status = ?";
    $params[] = $filterStatus;
}

if (!empty($dateFrom)) {
    $sql .= " AND DATE(c.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $sql .= " AND DATE(c.created_at) <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY c.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . e($e->getMessage()) . '</div>';
    $complaints = [];
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Grievance Management Registry</h3>
        <p class="text-muted small mb-0">Search, filter, assign officers, and update resolution statuses across all citizen complaints.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
            <i class="fa-solid fa-print me-1"></i> Print Table
        </button>
    </div>
</div>

<!-- Filters Card -->
<div class="card card-custom p-3 mb-4 border-0 shadow-sm">
    <form action="<?php echo base_url('admin/complaints.php'); ?>" method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Search</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="ID, Title, Citizen or Location..." value="<?php echo e($search); ?>">
            </div>
        </div>

        <div class="col-md-2 col-6">
            <label class="form-label small fw-semibold text-secondary">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat => $val): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo ($filterCategory === $cat) ? 'selected' : ''; ?>>
                        <?php echo e($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 col-6">
            <label class="form-label small fw-semibold text-secondary">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo e($st); ?>" <?php echo ($filterStatus === $st) ? 'selected' : ''; ?>>
                        <?php echo e($st); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 col-6">
            <label class="form-label small fw-semibold text-secondary">Date From</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e($dateFrom); ?>">
        </div>

        <div class="col-md-2 col-6">
            <label class="form-label small fw-semibold text-secondary">Date To</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e($dateTo); ?>">
        </div>

        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filters">Filter</button>
            <?php if (!empty($search) || !empty($filterCategory) || !empty($filterStatus) || !empty($dateFrom) || !empty($dateTo)): ?>
                <a href="<?php echo base_url('admin/complaints.php'); ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Complaints Registry Table -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($complaints)): ?>
            <div class="text-center py-5">
                <div class="text-muted mb-2 fs-2"><i class="fa-solid fa-folder-open"></i></div>
                <h5 class="fw-bold">No complaints match your criteria</h5>
                <p class="text-muted small mb-0">Try clearing filters to display all registered records.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Complaint ID</th>
                            <th>Citizen Details</th>
                            <th>Category & Title</th>
                            <th>Location</th>
                            <th>Date Filed</th>
                            <th>Assigned Officer</th>
                            <th>Status</th>
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
                                    <div class="fw-semibold text-dark"><?php echo e($c['citizen_name']); ?></div>
                                    <div class="text-muted small">
                                        <i class="fa-solid fa-phone me-1 text-secondary" style="font-size: 0.75rem;"></i> <?php echo e($c['citizen_phone']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 230px;">
                                        <?php echo e($c['title']); ?>
                                    </div>
                                    <span class="badge bg-light text-secondary border small">
                                        <?php echo e($c['category']); ?>
                                    </span>
                                    <?php if (!empty($c['attachment'])): ?>
                                        <i class="fa-solid fa-paperclip text-muted small ms-1" title="Attachment available"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-muted text-truncate d-inline-block" style="max-width: 150px;">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo e($c['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?php echo format_date($c['created_at'], 'd M Y'); ?></span>
                                    <div class="text-muted" style="font-size: 0.72rem;"><?php echo time_ago($c['created_at']); ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($c['officer_name'])): ?>
                                        <span class="small fw-semibold text-dark"><?php echo e($c['officer_name']); ?></span>
                                        <div class="text-muted" style="font-size: 0.72rem;"><?php echo e($c['officer_dept']); ?></div>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo get_status_badge($c['status']); ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo base_url('admin/complaint_details.php?id=' . urlencode($c['complaint_id'])); ?>" class="btn btn-primary" title="Manage Complaint">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> Manage
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="Delete inappropriate complaint" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($c['id']); ?>">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo e($c['id']); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Delete Inappropriate Complaint
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to permanently delete complaint <strong><?php echo e($c['complaint_id']); ?></strong>?</p>
                                                    <div class="p-3 bg-light rounded-3 small">
                                                        <div><strong>Title:</strong> <?php echo e($c['title']); ?></div>
                                                        <div><strong>Citizen:</strong> <?php echo e($c['citizen_name']); ?></div>
                                                    </div>
                                                    <p class="text-muted small mt-2 mb-0">This action will remove the complaint, timeline history, and any uploaded files from the server.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="<?php echo base_url('admin/complaints.php'); ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_complaint">
                                                        <input type="hidden" name="complaint_id" value="<?php echo e($c['complaint_id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Confirm Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-2 px-3 text-muted small d-flex justify-content-between align-items-center">
                <span>Total records: <strong><?php echo count($complaints); ?></strong></span>
                <span>SDG 16 Public Record Registry</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
