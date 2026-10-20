<?php
$page_title = "Manage Complaint Details";
$admin_active_page = "complaints";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

$complaintId = trim($_GET['id'] ?? '');

if (empty($complaintId)) {
    header('Location: ' . base_url('admin/complaints.php'));
    exit();
}

// Fetch Complaint Details & Citizen Info
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.id AS citizen_id, u.name AS citizen_name, u.email AS citizen_email, 
               u.phone AS citizen_phone, u.address AS citizen_address,
               o.id AS officer_id, o.name AS officer_name, o.department AS officer_dept, 
               o.phone AS officer_phone, o.email AS officer_email
        FROM complaints c
        LEFT JOIN users u ON c.user_id = u.id
        LEFT JOIN officers o ON c.assigned_to = o.id
        WHERE c.complaint_id = ?
        LIMIT 1
    ");
    $stmt->execute([$complaintId]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        set_flash('danger', 'Complaint record not found.');
        header('Location: ' . base_url('admin/complaints.php'));
        exit();
    }

    // Fetch active officers for assignment dropdown
    $officers = $pdo->query("SELECT * FROM officers WHERE status = 'Active' ORDER BY department ASC, name ASC")->fetchAll();

    // Handle Form Actions: Assign, Update Status, Add Response, Resolve, Reject
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // 1. Assign Officer
        if ($action === 'assign_officer') {
            $officerId = !empty($_POST['officer_id']) ? (int)$_POST['officer_id'] : null;
            $assignNotes = trim($_POST['assign_notes'] ?? '');

            if ($officerId) {
                // Get officer details
                $offStmt = $pdo->prepare("SELECT name, department FROM officers WHERE id = ?");
                $offStmt->execute([$officerId]);
                $offInfo = $offStmt->fetch();

                // If currently Submitted or Under Review, transition status to Assigned
                $newStatus = in_array($complaint['status'], ['Submitted', 'Under Review']) ? 'Assigned' : $complaint['status'];

                $up = $pdo->prepare("
                    UPDATE complaints 
                    SET assigned_to = ?, status = ?, updated_at = NOW() 
                    WHERE complaint_id = ?
                ");
                $up->execute([$officerId, $newStatus, $complaintId]);

                $logMsg = "Complaint assigned to Officer " . $offInfo['name'] . " (" . $offInfo['department'] . ").";
                if (!empty($assignNotes)) {
                    $logMsg .= " Instructions: " . $assignNotes;
                }

                $log = $pdo->prepare("
                    INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                    VALUES (?, ?, ?, 'Admin', NOW())
                ");
                $log->execute([$complaintId, $newStatus, $logMsg]);

                set_flash('success', "Complaint assigned successfully to {$offInfo['name']}.");
            } else {
                // Unassign
                $up = $pdo->prepare("UPDATE complaints SET assigned_to = NULL, updated_at = NOW() WHERE complaint_id = ?");
                $up->execute([$complaintId]);

                $log = $pdo->prepare("
                    INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                    VALUES (?, ?, 'Officer assignment cleared by Admin.', 'Admin', NOW())
                ");
                $log->execute([$complaintId, $complaint['status']]);

                set_flash('info', 'Officer assignment cleared.');
            }

            header('Location: ' . base_url('admin/complaint_details.php?id=' . urlencode($complaintId)));
            exit();
        }

        // 2. Change Status & Add Response / Comments
        if ($action === 'update_status') {
            $newStatus = trim($_POST['status'] ?? '');
            $adminResponse = trim($_POST['admin_response'] ?? '');
            $internalRemarks = trim($_POST['internal_remarks'] ?? '');

            if (in_array($newStatus, get_statuses())) {
                $resolvedAt = ($newStatus === 'Resolved') ? date('Y-m-d H:i:s') : (($complaint['status'] === 'Resolved') ? $complaint['resolved_at'] : null);

                $up = $pdo->prepare("
                    UPDATE complaints 
                    SET status = ?, admin_response = ?, resolved_at = ?, updated_at = NOW() 
                    WHERE complaint_id = ?
                ");
                $up->execute([$newStatus, $adminResponse, $resolvedAt, $complaintId]);

                $logMessage = "Status changed to '{$newStatus}' by Admin.";
                if (!empty($internalRemarks)) {
                    $logMessage .= " Remarks: " . $internalRemarks;
                } elseif (!empty($adminResponse)) {
                    $logMessage .= " Response: " . $adminResponse;
                }

                $log = $pdo->prepare("
                    INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                    VALUES (?, ?, ?, 'Admin', NOW())
                ");
                $log->execute([$complaintId, $newStatus, $logMessage]);

                set_flash('success', "Complaint status updated to {$newStatus}.");
                header('Location: ' . base_url('admin/complaint_details.php?id=' . urlencode($complaintId)));
                exit();
            }
        }

        // 3. Quick Mark as Resolved
        if ($action === 'quick_resolve') {
            $resolutionRemarks = trim($_POST['resolution_remarks'] ?? 'Grievance redressed and resolved after departmental verification.');

            $up = $pdo->prepare("
                UPDATE complaints 
                SET status = 'Resolved', admin_response = ?, resolved_at = NOW(), updated_at = NOW() 
                WHERE complaint_id = ?
            ");
            $up->execute([$resolutionRemarks, $complaintId]);

            $log = $pdo->prepare("
                INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                VALUES (?, 'Resolved', ?, 'Admin', NOW())
            ");
            $log->execute([$complaintId, "Issue marked as Resolved. Official note: " . $resolutionRemarks]);

            set_flash('success', "Complaint $complaintId has been marked as Resolved.");
            header('Location: ' . base_url('admin/complaint_details.php?id=' . urlencode($complaintId)));
            exit();
        }

        // 4. Quick Reject with Reason
        if ($action === 'quick_reject') {
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');
            if (empty($rejectionReason)) {
                set_flash('danger', 'Please provide a clear reason for rejecting this complaint.');
            } else {
                $up = $pdo->prepare("
                    UPDATE complaints 
                    SET status = 'Rejected', admin_response = ?, updated_at = NOW() 
                    WHERE complaint_id = ?
                ");
                $up->execute([$rejectionReason, $complaintId]);

                $log = $pdo->prepare("
                    INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                    VALUES (?, 'Rejected', ?, 'Admin', NOW())
                ");
                $log->execute([$complaintId, "Complaint rejected. Reason: " . $rejectionReason]);

                set_flash('warning', "Complaint $complaintId was rejected.");
                header('Location: ' . base_url('admin/complaint_details.php?id=' . urlencode($complaintId)));
                exit();
            }
        }
    }

    // Chronological updates
    $updatesStmt = $pdo->prepare("
        SELECT * FROM complaint_updates 
        WHERE complaint_id = ? 
        ORDER BY created_at ASC
    ");
    $updatesStmt->execute([$complaintId]);
    $updates = $updatesStmt->fetchAll();

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . e($e->getMessage()) . '</div>';
    exit();
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Title & Back Nav -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="<?php echo base_url('admin/complaints.php'); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h3 class="fw-bold text-dark mb-0">Complaint Redressal Desk</h3>
            <span class="badge bg-light text-primary border font-monospace fs-6"><?php echo e($complaint['complaint_id']); ?></span>
        </div>
        <p class="text-muted small mb-0">Review evidence, assign municipal officers, update status, and communicate decisions with citizens.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="<?php echo base_url('track.php?id=' . urlencode($complaint['complaint_id'])); ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Public View
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteComplaintModal">
            <i class="fa-solid fa-trash-can me-1"></i> Delete
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Complaint Details & Timeline -->
    <div class="col-lg-7">
        
        <!-- Complaint Content Card -->
        <div class="card card-custom p-4 mb-4 border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-start gap-2 border-bottom pb-3 mb-3">
                <div>
                    <span class="badge bg-secondary-subtle text-secondary border mb-2"><?php echo e($complaint['category']); ?></span>
                    <h4 class="fw-bold text-dark mb-1"><?php echo e($complaint['title']); ?></h4>
                    <div class="text-muted small">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo e($complaint['location']); ?>
                        <span class="mx-2">•</span>
                        <i class="fa-solid fa-clock me-1"></i> Registered <?php echo format_date($complaint['created_at']); ?>
                    </div>
                </div>
                <div>
                    <?php echo get_status_badge($complaint['status']); ?>
                </div>
            </div>

            <h6 class="fw-bold text-secondary text-uppercase small mb-2">Complaint Description</h6>
            <div class="p-3 bg-light rounded-3 text-secondary leading-relaxed mb-3" style="white-space: pre-line;">
                <?php echo e($complaint['description']); ?>
            </div>

            <?php if (!empty($complaint['attachment'])): ?>
                <div class="p-3 border rounded-3 bg-white mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-semibold small text-dark">
                            <i class="fa-solid fa-paperclip text-primary me-1"></i> Attached Evidence: <code><?php echo e($complaint['attachment']); ?></code>
                        </span>
                        <a href="<?php echo base_url('uploads/' . rawurlencode($complaint['attachment'])); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View / Download File
                        </a>
                    </div>
                    <?php 
                    $ext = strtolower(pathinfo($complaint['attachment'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <div class="mt-3 text-center">
                            <img src="<?php echo base_url('uploads/' . rawurlencode($complaint['attachment'])); ?>" alt="Site Photo" class="img-fluid rounded border" style="max-height: 320px; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($complaint['admin_response'])): ?>
                <div class="alert alert-success border-0 p-3 mb-0">
                    <div class="fw-bold text-success-emphasis mb-1">
                        <i class="fa-solid fa-comment-dots me-1"></i> Current Official Response:
                    </div>
                    <div class="small leading-relaxed"><?php echo e($complaint['admin_response']); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Full Audit Trail / Updates Timeline -->
        <div class="card card-custom p-4 border-0 shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-timeline text-primary me-2"></i> Chronological Timeline & Audit Trail</h5>
            <div class="timeline-feed">
                <?php foreach ($updates as $up): ?>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                            <span class="fw-bold text-dark"><?php echo e($up['status']); ?></span>
                            <span class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo format_date($up['created_at']); ?></span>
                        </div>
                        <div class="text-secondary small leading-relaxed mb-1">
                            <?php echo e($up['message']); ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            Action by: <strong><?php echo e($up['updated_by']); ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Right Column: Administrative Actions & Citizen Info -->
    <div class="col-lg-5">
        
        <!-- Citizen Information Card -->
        <div class="card card-custom p-3 mb-4 border-0 shadow-sm bg-white">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                <i class="fa-solid fa-user text-primary me-1"></i> Citizen Information
            </h6>
            <div class="small text-secondary">
                <div class="mb-2"><strong>Name:</strong> <?php echo e($complaint['citizen_name']); ?></div>
                <div class="mb-2"><strong>Email:</strong> <a href="mailto:<?php echo e($complaint['citizen_email']); ?>"><?php echo e($complaint['citizen_email']); ?></a></div>
                <div class="mb-2"><strong>Phone:</strong> <a href="tel:<?php echo e($complaint['citizen_phone']); ?>"><?php echo e($complaint['citizen_phone']); ?></a></div>
                <div><strong>Address:</strong> <?php echo e($complaint['citizen_address'] ?? 'Not specified'); ?></div>
            </div>
        </div>

        <!-- Officer Assignment Box -->
        <div class="card card-custom p-4 mb-4 border-0 shadow-sm">
            <h6 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-user-tag text-info me-1"></i> Officer Assignment
            </h6>
            <p class="text-muted small mb-3">Designate a departmental engineer or officer responsible for resolution.</p>

            <form action="<?php echo base_url('admin/complaint_details.php?id=' . urlencode($complaintId)); ?>" method="POST">
                <input type="hidden" name="action" value="assign_officer">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Select Officer</label>
                    <select name="officer_id" class="form-select form-select-sm" required>
                        <option value="">-- Choose Department Officer --</option>
                        <?php foreach ($officers as $off): ?>
                            <option value="<?php echo e($off['id']); ?>" <?php echo ($complaint['assigned_to'] == $off['id']) ? 'selected' : ''; ?>>
                                <?php echo e($off['name']); ?> (<?php echo e($off['department']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Assignment Instructions (Optional)</label>
                    <textarea name="assign_notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Inspect site on 25th Sept and verify water valve leakage..."></textarea>
                </div>

                <button type="submit" class="btn btn-info text-white btn-sm w-100 fw-semibold">
                    <i class="fa-solid fa-check me-1"></i> Assign Officer
                </button>
            </form>
        </div>

        <!-- Status & Response Updater -->
        <div class="card card-custom p-4 mb-4 border-0 shadow-sm">
            <h6 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-sliders text-primary me-1"></i> Update Status & Official Response
            </h6>
            <p class="text-muted small mb-3">Advance the complaint through the redressal workflow.</p>

            <form action="<?php echo base_url('admin/complaint_details.php?id=' . urlencode($complaintId)); ?>" method="POST">
                <input type="hidden" name="action" value="update_status">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Workflow Status</label>
                    <select name="status" class="form-select form-select-sm" required>
                        <?php foreach (get_statuses() as $st): ?>
                            <option value="<?php echo e($st); ?>" <?php echo ($complaint['status'] === $st) ? 'selected' : ''; ?>>
                                <?php echo e($st); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Citizen-Facing Official Response</label>
                    <textarea name="admin_response" class="form-control form-control-sm" rows="3" placeholder="This response will be visible to the citizen and on public tracking..."><?php echo e($complaint['admin_response']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Internal Audit Remarks (Optional)</label>
                    <input type="text" name="internal_remarks" class="form-control form-control-sm" placeholder="e.g. Verified with municipal supervisor">
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Status & Response
                </button>
            </form>
        </div>

        <!-- Quick Action Buttons: Resolve / Reject -->
        <div class="card card-custom p-3 border-0 shadow-sm">
            <div class="fw-bold small text-dark mb-2">Fast Redressal Actions</div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#resolveModal">
                    <i class="fa-solid fa-circle-check me-1"></i> Mark Resolved
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fa-solid fa-circle-xmark me-1"></i> Reject Case
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Mark as Resolved -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo base_url('admin/complaint_details.php?id=' . urlencode($complaintId)); ?>" method="POST">
                <input type="hidden" name="action" value="quick_resolve">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-circle-check me-1"></i> Mark Complaint as Resolved</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Please enter the official completion notes and verification remarks for the citizen:</p>
                    <textarea name="resolution_remarks" class="form-control" rows="4" required>Field repair work completed by municipal team. Inspection verified on site.</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Confirm Resolution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reject Complaint -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?php echo base_url('admin/complaint_details.php?id=' . urlencode($complaintId)); ?>" method="POST">
                <input type="hidden" name="action" value="quick_reject">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Reject Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Enter the formal justification for rejection (e.g. duplicate complaint, private property issue, insufficient details):</p>
                    <textarea name="rejection_reason" class="form-control" rows="4" placeholder="State reason clearly..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Delete Complaint -->
<div class="modal fade" id="deleteComplaintModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-trash-can me-1"></i> Permanently Delete Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently remove complaint <strong><?php echo e($complaint['complaint_id']); ?></strong>?</p>
                <div class="alert alert-warning small mb-0">This will remove all timeline entries, attachments, and data permanently.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo base_url('admin/complaints.php'); ?>" method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete_complaint">
                    <input type="hidden" name="complaint_id" value="<?php echo e($complaint['complaint_id']); ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
