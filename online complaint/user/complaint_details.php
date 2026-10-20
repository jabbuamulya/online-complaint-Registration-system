<?php
$page_title = "Complaint Details";
$user_active_page = "complaints";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/includes/user_header.php';

$complaintId = trim($_GET['id'] ?? '');
$userId = $_SESSION['user_id'];

if (empty($complaintId)) {
    header('Location: ' . base_url('user/complaints.php'));
    exit();
}

// Fetch complaint with ownership check
try {
    $stmt = $pdo->prepare("
        SELECT c.*, o.name AS officer_name, o.department AS officer_dept, o.phone AS officer_phone, o.email AS officer_email
        FROM complaints c
        LEFT JOIN officers o ON c.assigned_to = o.id
        WHERE c.complaint_id = ? AND c.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$complaintId, $userId]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        set_flash('danger', 'Complaint not found or access denied.');
        header('Location: ' . base_url('user/complaints.php'));
        exit();
    }

    // Handle posting additional citizen information / comments
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_citizen_update'])) {
        $additionalInfo = trim($_POST['additional_info'] ?? '');
        if (!empty($additionalInfo)) {
            $upStmt = $pdo->prepare("
                INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $upStmt->execute([
                $complaintId,
                $complaint['status'],
                "Citizen Note: " . $additionalInfo,
                $_SESSION['user_name'] . " (Citizen)"
            ]);

            // Update timestamp
            $touchStmt = $pdo->prepare("UPDATE complaints SET updated_at = NOW() WHERE complaint_id = ?");
            $touchStmt->execute([$complaintId]);

            set_flash('success', 'Additional information successfully submitted and logged to case history.');
            header('Location: ' . base_url('user/complaint_details.php?id=' . urlencode($complaintId)));
            exit();
        } else {
            $updateError = "Please enter some text before submitting additional information.";
        }
    }

    // Fetch chronological updates
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

$stages = ['Submitted', 'Under Review', 'Assigned', 'In Progress', 'Resolved'];
$currentStatus = $complaint['status'];
$isRejected = ($currentStatus === 'Rejected');
$currentIndex = array_search($currentStatus, $stages);
if ($currentIndex === false && !$isRejected) $currentIndex = 0;
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="<?php echo base_url('user/complaints.php'); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h3 class="fw-bold text-dark mb-0">Complaint Details</h3>
            <span class="badge bg-light text-primary border font-monospace"><?php echo e($complaint['complaint_id']); ?></span>
        </div>
        <p class="text-muted small mb-0">Comprehensive record, assigned officer, timeline logs, and official feedback.</p>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
            <i class="fa-solid fa-print me-1"></i> Print Case
        </button>
        <a href="<?php echo base_url('track.php?id=' . urlencode($complaint['complaint_id'])); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Public Tracker View
        </a>
    </div>
</div>

<?php if (isset($updateError)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e($updateError); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Main Overview Card -->
<div class="card card-custom p-4 p-md-5 mb-4 border-0 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 border-bottom pb-4 mb-4">
        <div>
            <span class="badge bg-secondary-subtle text-secondary border mb-2">Category: <?php echo e($complaint['category']); ?></span>
            <h3 class="fw-bold text-dark mb-1"><?php echo e($complaint['title']); ?></h3>
            <div class="text-muted small">
                <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo e($complaint['location']); ?>
                <span class="mx-2">•</span>
                <i class="fa-solid fa-calendar me-1"></i> Submitted: <?php echo format_date($complaint['created_at']); ?>
            </div>
        </div>

        <div class="text-md-end">
            <div class="mb-1"><?php echo get_status_badge($complaint['status']); ?></div>
            <div class="text-muted small">Last Activity: <?php echo time_ago($complaint['updated_at']); ?></div>
        </div>
    </div>

    <!-- Progress Timeline Tracker -->
    <div class="my-3">
        <h6 class="fw-bold text-secondary text-uppercase small mb-3">Redressal Stage</h6>
        <?php if ($isRejected): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="fa-solid fa-circle-xmark fs-3 me-3 text-danger"></i>
                <div>
                    <div class="fw-bold">Complaint Rejected</div>
                    <div class="small">The department reviewed this grievance and closed it as rejected. Please check the administrative response below.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="status-tracker d-none d-md-flex">
                <?php foreach ($stages as $idx => $stg): 
                    $class = '';
                    if ($idx < $currentIndex) {
                        $class = 'completed';
                    } elseif ($idx === $currentIndex) {
                        $class = 'active';
                    }
                ?>
                    <div class="tracker-node <?php echo $class; ?>">
                        <div class="tracker-circle">
                            <?php if ($idx < $currentIndex): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <?php echo ($idx + 1); ?>
                            <?php endif; ?>
                        </div>
                        <div class="tracker-label"><?php echo $stg; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Details Grid -->
    <div class="row g-4 mt-2">
        <div class="col-md-8">
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-align-left text-primary me-2"></i> Complaint Description</h6>
                <p class="text-secondary leading-relaxed mb-0" style="white-space: pre-line;">
                    <?php echo e($complaint['description']); ?>
                </p>

                <?php if (!empty($complaint['attachment'])): ?>
                    <hr class="my-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="small fw-semibold text-secondary">
                            <i class="fa-solid fa-paperclip me-1 text-primary"></i> Supporting Attachment: <code><?php echo e($complaint['attachment']); ?></code>
                        </span>
                        <a href="<?php echo base_url('uploads/' . rawurlencode($complaint['attachment'])); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open / Download File
                        </a>
                    </div>
                    <?php 
                    $ext = strtolower(pathinfo($complaint['attachment'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <div class="mt-3">
                            <img src="<?php echo base_url('uploads/' . rawurlencode($complaint['attachment'])); ?>" alt="Complaint Evidence" class="img-fluid rounded border" style="max-height: 280px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="p-3 bg-light rounded-3 border h-100">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-user-shield text-info me-2"></i> Department Handling</h6>
                
                <div class="mb-3 pb-2 border-bottom">
                    <div class="text-muted small">Assigned Officer</div>
                    <?php if (!empty($complaint['officer_name'])): ?>
                        <div class="fw-bold text-dark"><?php echo e($complaint['officer_name']); ?></div>
                        <div class="text-muted small"><?php echo e($complaint['officer_dept']); ?></div>
                        <?php if (!empty($complaint['officer_phone'])): ?>
                            <div class="text-secondary small mt-1"><i class="fa-solid fa-phone me-1"></i> <?php echo e($complaint['officer_phone']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-muted fst-italic small">Pending officer allocation by administrative desk</div>
                    <?php endif; ?>
                </div>

                <div class="mb-3 pb-2 border-bottom">
                    <div class="text-muted small">Date Resolved</div>
                    <div class="fw-bold text-dark">
                        <?php echo !empty($complaint['resolved_at']) ? format_date($complaint['resolved_at']) : '<span class="text-muted small fw-normal">In Progress</span>'; ?>
                    </div>
                </div>

                <div>
                    <div class="text-muted small">SDG 16 Tracking Token</div>
                    <div class="font-monospace small fw-bold text-primary"><?php echo e($complaint['complaint_id']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Response Box -->
    <?php if (!empty($complaint['admin_response'])): ?>
        <div class="alert alert-success border-0 shadow-sm mt-4 p-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-building-columns text-success fs-5"></i>
                <h6 class="fw-bold mb-0 text-success-emphasis">Official Administrative Response</h6>
            </div>
            <p class="mb-0 text-dark leading-relaxed" style="white-space: pre-line;">
                <?php echo e($complaint['admin_response']); ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Audit Trail / Timeline Logs -->
    <div class="mt-5">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-timeline me-2 text-primary"></i> Grievance Activity Log</h5>
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
                    <div class="text-muted" style="font-size: 0.75rem;">
                        Logged by: <strong><?php echo e($up['updated_by']); ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Additional Information Section -->
    <?php if ($complaint['status'] !== 'Resolved' && $complaint['status'] !== 'Rejected'): ?>
        <div class="mt-4 pt-4 border-top">
            <h5 class="fw-bold mb-2"><i class="fa-solid fa-plus-circle text-primary me-2"></i> Provide Additional Information</h5>
            <p class="text-muted small mb-3">Has the situation changed, or do you have further details/landmarks for the inspection officer?</p>
            
            <form action="<?php echo base_url('user/complaint_details.php?id=' . urlencode($complaintId)); ?>" method="POST">
                <div class="mb-3">
                    <textarea name="additional_info" class="form-control" rows="3" placeholder="Enter additional details or clarifications here..." required></textarea>
                </div>
                <button type="submit" name="post_citizen_update" class="btn btn-primary btn-sm px-4">
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit Information
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
