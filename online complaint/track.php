<?php
$page_title = "Track Complaint Status";
$active_page = "track";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$complaintId = strtoupper(trim($_GET['id'] ?? ''));
$complaint = null;
$updates = [];
$error = '';

if (!empty($complaintId)) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, o.name AS officer_name, o.department AS officer_dept, u.name AS citizen_name
            FROM complaints c
            LEFT JOIN officers o ON c.assigned_to = o.id
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.complaint_id = ?
            LIMIT 1
        ");
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch();

        if ($complaint) {
            // Fetch chronological updates
            $upStmt = $pdo->prepare("
                SELECT * FROM complaint_updates 
                WHERE complaint_id = ? 
                ORDER BY created_at ASC
            ");
            $upStmt->execute([$complaintId]);
            $updates = $upStmt->fetchAll();
        } else {
            $error = "No complaint record found matching ID: " . e($complaintId) . ". Please verify the Complaint ID.";
        }
    } catch (PDOException $e) {
        $error = "Database query error: " . $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Section -->
<section class="bg-primary text-white py-4" style="background: linear-gradient(135deg, #091e3a 0%, #1e40af 100%) !important;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="fw-bold mb-1"><i class="fa-solid fa-magnifying-glass me-2"></i> Public Complaint Tracker</h2>
                <p class="text-white-50 mb-0 small">Verify real-time status and departmental action on any registered complaint.</p>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <form action="<?php echo base_url('track.php'); ?>" method="GET" class="d-flex gap-2">
                    <input type="text" name="id" class="form-control text-uppercase fw-bold shadow-sm" placeholder="Enter ID e.g. CMP20260001" value="<?php echo e($complaintId); ?>" required>
                    <button type="submit" class="btn btn-warning fw-bold px-4 shadow-sm text-nowrap">
                        Track Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <?php if (!empty($error)): ?>
        <div class="alert alert-warning alert-dismissible fade show text-center py-4 card-custom" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-2 text-warning mb-2 d-block"></i>
            <h5 class="fw-bold"><?php echo e($error); ?></h5>
            <p class="text-muted small mb-3">Please check your receipt or confirmation message for the correct format (e.g. <code>CMP20260001</code>).</p>
            <div class="small">Try sample tracking IDs: <a href="<?php echo base_url('track.php?id=CMP20260001'); ?>" class="fw-bold">CMP20260001</a> or <a href="<?php echo base_url('track.php?id=CMP20260002'); ?>" class="fw-bold">CMP20260002</a></div>
        </div>
    <?php elseif ($complaint): ?>
        
        <!-- Status Card Overview -->
        <div class="card card-custom p-4 p-md-5 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 border-bottom pb-4 mb-4">
                <div>
                    <span class="badge bg-light text-secondary border px-3 py-1 mb-2 font-monospace fs-6">
                        ID: <?php echo e($complaint['complaint_id']); ?>
                    </span>
                    <h3 class="fw-bold text-dark mb-1"><?php echo e($complaint['title']); ?></h3>
                    <div class="text-muted small">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo e($complaint['location']); ?>
                        <span class="mx-2">•</span>
                        <i class="fa-solid fa-calendar me-1"></i> Filed on <?php echo format_date($complaint['created_at']); ?>
                    </div>
                </div>

                <div class="text-md-end">
                    <div class="mb-1"><?php echo get_status_badge($complaint['status']); ?></div>
                    <div class="text-muted small">
                        Last Activity: <?php echo time_ago($complaint['updated_at']); ?>
                    </div>
                </div>
            </div>

            <!-- Visual Stage Progress Tracker -->
            <?php
            $stages = ['Submitted', 'Under Review', 'Assigned', 'In Progress', 'Resolved'];
            $currentStatus = $complaint['status'];
            $isRejected = ($currentStatus === 'Rejected');
            $currentIndex = array_search($currentStatus, $stages);
            if ($currentIndex === false && !$isRejected) $currentIndex = 0;
            ?>

            <div class="my-4">
                <h6 class="fw-bold text-secondary text-uppercase small mb-3">Resolution Progress Timeline</h6>
                
                <?php if ($isRejected): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4">
                        <i class="fa-solid fa-circle-xmark fs-3 me-3 text-danger"></i>
                        <div>
                            <div class="fw-bold">Complaint Rejected by Authority</div>
                            <div class="small">This complaint was reviewed and closed as rejected. See remarks below for justification.</div>
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

                    <!-- Mobile Simple Progress Bar -->
                    <div class="d-md-none mb-4">
                        <div class="progress" style="height: 10px;">
                            <?php $percent = (($currentIndex + 1) / count($stages)) * 100; ?>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mt-1">
                            <span>Submitted</span>
                            <span class="fw-bold text-primary"><?php echo $currentStatus; ?></span>
                            <span>Resolved</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Grid Details -->
            <div class="row g-4 mt-2">
                <div class="col-md-8">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-align-left text-primary me-2"></i> Grievance Description</h6>
                        <p class="text-secondary leading-relaxed mb-0" style="white-space: pre-line;">
                            <?php echo e($complaint['description']); ?>
                        </p>

                        <?php if (!empty($complaint['attachment'])): ?>
                            <hr class="my-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="small fw-semibold text-secondary">
                                    <i class="fa-solid fa-paperclip me-1"></i> Attached Supporting Document/Photo
                                </span>
                                <a href="<?php echo base_url('uploads/' . rawurlencode($complaint['attachment'])); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> View Attachment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3 border h-100">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-info text-info me-2"></i> Case Overview</h6>
                        
                        <div class="mb-2 pb-2 border-bottom">
                            <div class="text-muted small">Category</div>
                            <div class="fw-bold text-dark"><?php echo e($complaint['category']); ?></div>
                        </div>

                        <div class="mb-2 pb-2 border-bottom">
                            <div class="text-muted small">Assigned Officer</div>
                            <div class="fw-bold text-dark">
                                <?php echo !empty($complaint['officer_name']) ? e($complaint['officer_name']) . ' (' . e($complaint['officer_dept']) . ')' : '<span class="text-muted fw-normal">Pending Assignment</span>'; ?>
                            </div>
                        </div>

                        <div>
                            <div class="text-muted small">Resolution Date</div>
                            <div class="fw-bold text-dark">
                                <?php echo !empty($complaint['resolved_at']) ? format_date($complaint['resolved_at']) : '<span class="text-muted fw-normal">In Redressal Workflow</span>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Official Response / Action Remarks -->
            <?php if (!empty($complaint['admin_response'])): ?>
                <div class="alert alert-success border-0 shadow-sm mt-4 p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-building-columns text-success fs-5"></i>
                        <h6 class="fw-bold mb-0 text-success-emphasis">Official Redressal Authority Response</h6>
                    </div>
                    <p class="mb-0 text-dark leading-relaxed" style="white-space: pre-line;">
                        <?php echo e($complaint['admin_response']); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Timeline of Redressal Steps -->
            <div class="mt-5">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-list-check me-2 text-primary"></i> Transparency Audit Trail</h5>
                
                <?php if (empty($updates)): ?>
                    <p class="text-muted small">No detailed logs recorded yet.</p>
                <?php else: ?>
                    <div class="timeline-feed">
                        <?php foreach ($updates as $up): ?>
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                    <span class="fw-bold text-dark">
                                        <?php echo e($up['status']); ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fa-solid fa-clock me-1"></i> <?php echo format_date($up['created_at']); ?>
                                    </span>
                                </div>
                                <div class="text-secondary small leading-relaxed mb-1">
                                    <?php echo e($up['message']); ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    Updated by: <strong><?php echo e($up['updated_by']); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
                    <i class="fa-solid fa-print me-1"></i> Print Grievance Summary
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="copyComplaintId('<?php echo e($complaint['complaint_id']); ?>')">
                    <i class="fa-solid fa-copy me-1"></i> Copy Complaint ID
                </button>
            </div>
        </div>

    <?php else: ?>
        <!-- Default State: Search Form & Guide -->
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card card-custom p-4 p-md-5 text-center shadow-sm">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.5rem;">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 class="fw-bold">Track Your Grievance</h3>
                    <p class="text-muted small mb-4">
                        Enter the unique Complaint ID issued upon submission to see current departmental status, officer notes, and estimated closure.
                    </p>

                    <form action="<?php echo base_url('track.php'); ?>" method="GET" class="mb-4">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-hashtag text-primary"></i></span>
                            <input type="text" name="id" class="form-control text-uppercase fw-bold" placeholder="e.g. CMP20260001" required autofocus>
                            <button type="submit" class="btn btn-primary fw-bold px-4">Track</button>
                        </div>
                    </form>

                    <div class="bg-light p-3 rounded-3 text-start small">
                        <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i> Don't have a tracking ID?</div>
                        <p class="text-secondary mb-2">
                            If you submitted a complaint while logged in, you can view all your filed grievances under your <a href="<?php echo base_url('user/complaints.php'); ?>">My Complaints</a> dashboard.
                        </p>
                        <div class="text-muted">
                            Try demo tracking tokens: 
                            <a href="<?php echo base_url('track.php?id=CMP20260001'); ?>" class="badge bg-secondary text-white text-decoration-none">CMP20260001 (Resolved)</a>
                            <a href="<?php echo base_url('track.php?id=CMP20260002'); ?>" class="badge bg-secondary text-white text-decoration-none">CMP20260002 (In Progress)</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
