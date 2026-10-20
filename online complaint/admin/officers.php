<?php
$page_title = "Manage Department Officers";
$admin_active_page = "officers";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

// Handle Add Officer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_officer') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($department)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO officers (name, email, phone, department, status, created_at) VALUES (?, ?, ?, ?, 'Active', NOW())");
            $stmt->execute([$name, $email, $phone, $department]);
            set_flash('success', "Officer $name added to $department successfully.");
            header('Location: ' . base_url('admin/officers.php'));
            exit();
        } catch (PDOException $e) {
            set_flash('danger', 'Error adding officer: ' . $e->getMessage());
        }
    } else {
        set_flash('danger', 'All fields are mandatory.');
    }
}

// Handle Edit Officer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_officer') {
    $officerId = (int)($_POST['officer_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if ($officerId > 0 && !empty($name) && !empty($email)) {
        try {
            $stmt = $pdo->prepare("UPDATE officers SET name = ?, email = ?, phone = ?, department = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $department, $status, $officerId]);
            set_flash('success', "Officer record updated successfully.");
            header('Location: ' . base_url('admin/officers.php'));
            exit();
        } catch (PDOException $e) {
            set_flash('danger', 'Error updating officer: ' . $e->getMessage());
        }
    }
}

// Handle Delete Officer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_officer') {
    $officerId = (int)($_POST['officer_id'] ?? 0);
    if ($officerId > 0) {
        try {
            // Note: foreign key in complaints sets assigned_to to NULL on delete
            $stmt = $pdo->prepare("DELETE FROM officers WHERE id = ?");
            $stmt->execute([$officerId]);
            set_flash('success', 'Officer removed successfully.');
            header('Location: ' . base_url('admin/officers.php'));
            exit();
        } catch (PDOException $e) {
            set_flash('danger', 'Error deleting officer: ' . $e->getMessage());
        }
    }
}

// Fetch Officers with assigned complaint counts
try {
    $sql = "
        SELECT o.*, 
               COUNT(c.id) AS assigned_count,
               SUM(CASE WHEN c.status = 'Resolved' THEN 1 ELSE 0 END) AS resolved_count,
               SUM(CASE WHEN c.status IN ('Assigned', 'In Progress') THEN 1 ELSE 0 END) AS active_count
        FROM officers o
        LEFT JOIN complaints c ON o.id = c.assigned_to
        GROUP BY o.id
        ORDER BY o.department ASC, o.name ASC
    ";
    $officers = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . e($e->getMessage()) . '</div>';
    $officers = [];
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Department Officers Directory</h3>
        <p class="text-muted small mb-0">Manage municipal officers, field engineers, and department allocations.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addOfficerModal">
            <i class="fa-solid fa-user-plus me-1"></i> Add New Officer
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($officers)): ?>
            <div class="text-center py-5">
                <h6 class="text-muted">No officers found in the database. Click 'Add New Officer' to create one.</h6>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Officer Name</th>
                            <th>Department</th>
                            <th>Contact Information</th>
                            <th>Active Cases</th>
                            <th>Resolved Cases</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($officers as $o): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-info-subtle text-info-emphasis rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?php echo e($o['name']); ?></div>
                                            <span class="text-muted" style="font-size: 0.72rem;">Officer ID: #<?php echo $o['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border fw-semibold">
                                        <?php echo e($o['department']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-dark"><i class="fa-solid fa-envelope me-1 text-muted"></i> <?php echo e($o['email']); ?></div>
                                    <div class="small text-muted"><i class="fa-solid fa-phone me-1 text-muted"></i> <?php echo e($o['phone']); ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis">
                                        <?php echo $o['active_count']; ?> in progress
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        <?php echo $o['resolved_count']; ?> resolved
                                    </span>
                                </td>
                                <td>
                                    <?php if ($o['status'] === 'Active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $o['id']; ?>" title="Edit Officer">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $o['id']; ?>" title="Delete Officer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $o['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form action="<?php echo base_url('admin/officers.php'); ?>" method="POST">
                                                    <input type="hidden" name="action" value="edit_officer">
                                                    <input type="hidden" name="officer_id" value="<?php echo $o['id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Officer: <?php echo e($o['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Full Name</label>
                                                            <input type="text" name="name" class="form-control form-control-sm" value="<?php echo e($o['name']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control form-control-sm" value="<?php echo e($o['email']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Phone Number</label>
                                                            <input type="tel" name="phone" class="form-control form-control-sm" value="<?php echo e($o['phone']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Department</label>
                                                            <input type="text" name="department" class="form-control form-control-sm" value="<?php echo e($o['department']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Status</label>
                                                            <select name="status" class="form-select form-select-sm">
                                                                <option value="Active" <?php echo ($o['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                                <option value="Inactive" <?php echo ($o['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $o['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-trash-can me-1"></i> Delete Officer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to remove Officer <strong><?php echo e($o['name']); ?></strong>?</p>
                                                    <p class="text-muted small mb-0">Complaints currently assigned to this officer will be preserved and reassigned to Unassigned status.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="<?php echo base_url('admin/officers.php'); ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete_officer">
                                                        <input type="hidden" name="officer_id" value="<?php echo $o['id']; ?>">
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

<!-- Modal: Add New Officer -->
<div class="modal fade" id="addOfficerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo base_url('admin/officers.php'); ?>" method="POST">
                <input type="hidden" name="action" value="add_officer">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-1 text-primary"></i> Add Department Officer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Officer Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Suresh Kulkarni" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Official Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="officer@department.gov.in" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contact Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control form-control-sm" placeholder="10-digit mobile number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department" class="form-select form-select-sm" required>
                            <option value="">-- Select Department --</option>
                            <option value="Roads & Infrastructure">Roads & Infrastructure</option>
                            <option value="Water Supply & Sewage">Water Supply & Sewage</option>
                            <option value="Electricity & Power">Electricity & Power</option>
                            <option value="Sanitation & Waste Management">Sanitation & Waste Management</option>
                            <option value="Street Lighting">Street Lighting</option>
                            <option value="Public Transport">Public Transport</option>
                            <option value="Drainage Systems">Drainage Systems</option>
                            <option value="General Municipal Works">General Municipal Works</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Create Officer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
