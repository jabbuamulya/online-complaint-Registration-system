<?php
$page_title = "Manage Registered Citizens";
$admin_active_page = "users";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $userIdToDelete = (int)($_POST['user_id'] ?? 0);
    if ($userIdToDelete > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userIdToDelete]);
            set_flash('success', 'User account and associated records removed successfully.');
            header('Location: ' . base_url('admin/users.php'));
            exit();
        } catch (PDOException $e) {
            set_flash('danger', 'Error deleting user: ' . $e->getMessage());
        }
    }
}

// Search
$search = trim($_GET['search'] ?? '');
$sql = "
    SELECT u.*, COUNT(c.id) AS total_complaints,
           SUM(CASE WHEN c.status = 'Resolved' THEN 1 ELSE 0 END) AS resolved_complaints
    FROM users u
    LEFT JOIN complaints c ON u.id = c.user_id
";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?";
    $term = '%' . $search . '%';
    $params = [$term, $term, $term];
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . e($e->getMessage()) . '</div>';
    $users = [];
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Registered Citizens Directory</h3>
        <p class="text-muted small mb-0">Overview of all citizens participating in the grievance redressal mechanism.</p>
    </div>
    <div class="d-flex gap-2">
        <form action="<?php echo base_url('admin/users.php'); ?>" method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, email, phone..." value="<?php echo e($search); ?>">
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
            <?php if (!empty($search)): ?>
                <a href="<?php echo base_url('admin/users.php'); ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <h6 class="text-muted">No citizen accounts found matching your search.</h6>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>#</th>
                            <th>Citizen Name</th>
                            <th>Contact Info</th>
                            <th>Address / Ward</th>
                            <th>Complaints Filed</th>
                            <th>Registration Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $u): ?>
                            <tr>
                                <td><?php echo ($index + 1); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px;">
                                            <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?php echo e($u['name']); ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;">ID: #<?php echo $u['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark"><i class="fa-solid fa-envelope me-1 text-muted"></i> <?php echo e($u['email']); ?></div>
                                    <div class="small text-muted"><i class="fa-solid fa-phone me-1 text-muted"></i> <?php echo e($u['phone']); ?></div>
                                </td>
                                <td>
                                    <span class="small text-secondary text-truncate d-inline-block" style="max-width: 200px;">
                                        <?php echo !empty($u['address']) ? e($u['address']) : '<span class="text-muted fst-italic">Not provided</span>'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo base_url('admin/complaints.php?search=' . urlencode($u['email'])); ?>" class="badge bg-primary text-white text-decoration-none">
                                        <?php echo $u['total_complaints']; ?> total
                                    </a>
                                    <?php if ($u['resolved_complaints'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle small ms-1">
                                            <?php echo $u['resolved_complaints']; ?> resolved
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?php echo format_date($u['created_at'], 'd M Y'); ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo base_url('admin/complaints.php?search=' . urlencode($u['email'])); ?>" class="btn btn-outline-primary" title="View citizen complaints">
                                            <i class="fa-solid fa-folder-open me-1"></i> Complaints
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal<?php echo $u['id']; ?>" title="Delete User">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteUserModal<?php echo $u['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-trash-can me-1"></i> Delete User Account</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete account for <strong><?php echo e($u['name']); ?></strong> (<?php echo e($u['email']); ?>)?</p>
                                                    <div class="alert alert-warning small mb-0">This will delete all their registered complaints and logs due to foreign key cascade.</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="<?php echo base_url('admin/users.php'); ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
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
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
