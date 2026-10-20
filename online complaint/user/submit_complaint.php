<?php
$page_title = "Register New Complaint";
$user_active_page = "submit";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/includes/user_header.php';

$categories = get_categories();
$selectedCat = $_GET['cat'] ?? '';
$errors = [];
$submittedComplaintId = null;

$title = '';
$category = array_key_exists($selectedCat, $categories) ? $selectedCat : '';
$description = '';
$location = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $attachmentName = null;

    // Validation
    if (empty($title)) {
        $errors[] = "Please provide a brief, descriptive complaint title.";
    }
    if (empty($category) || !array_key_exists($category, $categories)) {
        $errors[] = "Please select a valid complaint category.";
    }
    if (empty($description) || strlen($description) < 15) {
        $errors[] = "Please provide a detailed description (at least 15 characters).";
    }
    if (empty($location)) {
        $errors[] = "Please specify the exact location or landmark of the issue.";
    }

    // Handle File Attachment
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error code: " . $file['error'];
        } else {
            // Check size (5MB max)
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = "Attached file exceeds the maximum permitted limit of 5MB.";
            }

            // Check file extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            if (!in_array($ext, $allowedExtensions)) {
                $errors[] = "Invalid file type (." . e($ext) . "). Only JPG, PNG, PDF, and DOC files are allowed.";
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $attachmentName = 'CMP_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destination = $uploadDir . $attachmentName;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    $errors[] = "Failed to save the uploaded attachment. Please try again.";
                }
            }
        }
    }

    // Save Complaint
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Generate Complaint ID (e.g. CMP20260005)
            $newComplaintId = generate_complaint_id($pdo);

            $stmt = $pdo->prepare("
                INSERT INTO complaints 
                (complaint_id, user_id, title, category, description, location, attachment, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Submitted', NOW(), NOW())
            ");
            $stmt->execute([
                $newComplaintId,
                $_SESSION['user_id'],
                $title,
                $category,
                $description,
                $location,
                $attachmentName
            ]);

            // Insert initial timeline entry in complaint_updates
            $upStmt = $pdo->prepare("
                INSERT INTO complaint_updates (complaint_id, status, message, updated_by, created_at)
                VALUES (?, 'Submitted', ?, ?, NOW())
            ");
            $upStmt->execute([
                $newComplaintId,
                "Complaint registered by citizen " . $_SESSION['user_name'] . " under " . $category . " department.",
                $_SESSION['user_name']
            ]);

            $pdo->commit();
            $submittedComplaintId = $newComplaintId;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to submit complaint: " . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-9">

        <?php if ($submittedComplaintId): ?>
            <!-- Success Modal / Presentation View -->
            <div class="card card-custom p-4 p-md-5 text-center shadow-sm border-0">
                <div class="text-success mb-3" style="font-size: 3.5rem;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="fw-bold text-dark">Complaint submitted successfully!</h3>
                <p class="text-secondary">Your civic grievance has been safely registered in the central system and forwarded to the appropriate department.</p>

                <div class="complaint-id-box my-4 mx-auto" style="max-width: 480px;">
                    <div class="text-muted small text-uppercase fw-bold mb-1">Your Complaint ID is:</div>
                    <div class="complaint-id-text"><?php echo e($submittedComplaintId); ?></div>
                    <p class="text-muted small mt-2 mb-0">Use this ID to track the complaint.</p>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-3 mt-2">
                    <button type="button" class="btn btn-outline-secondary px-3" onclick="copyComplaintId('<?php echo e($submittedComplaintId); ?>')">
                        <i class="fa-solid fa-copy me-1"></i> Copy ID
                    </button>
                    <a href="<?php echo base_url('track.php?id=' . urlencode($submittedComplaintId)); ?>" class="btn btn-warning fw-bold px-4">
                        <i class="fa-solid fa-crosshairs me-1"></i> Track Status Now
                    </a>
                    <a href="<?php echo base_url('user/complaint_details.php?id=' . urlencode($submittedComplaintId)); ?>" class="btn btn-primary px-4">
                        <i class="fa-solid fa-eye me-1"></i> View Complaint Details
                    </a>
                    <a href="<?php echo base_url('user/submit_complaint.php'); ?>" class="btn btn-outline-primary px-3">
                        <i class="fa-solid fa-plus me-1"></i> Submit Another
                    </a>
                </div>
            </div>

        <?php else: ?>

            <!-- Submission Form -->
            <div class="card card-custom p-4 p-md-5 shadow-sm border-0">
                <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                    <div class="brand-icon-box" style="width: 44px; height: 44px; font-size: 1.2rem;">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Register a New Grievance</h4>
                        <span class="text-muted small">All fields marked with an asterisk (<span class="text-danger">*</span>) are mandatory.</span>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 small ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo base_url('user/submit_complaint.php'); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label small fw-semibold">Complaint Subject / Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="title" name="title" value="<?php echo e($title); ?>" placeholder="e.g. Broken water pipe leaking near Sector 9 market" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label small fw-semibold">Department Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $catName => $catDetails): ?>
                                    <option value="<?php echo e($catName); ?>" <?php echo ($category === $catName) ? 'selected' : ''; ?>>
                                        <?php echo e($catName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label small fw-semibold">Exact Location / Landmark <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo e($location); ?>" placeholder="e.g. Main Street, Opposite Post Office" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold">Detailed Description of Grievance <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Explain the civic issue thoroughly (when it started, severity, danger to public safety, previous attempts to report, etc.)..." required><?php echo e($description); ?></textarea>
                        <div class="form-text small">Please be as specific as possible to assist on-ground engineers in pinpointing the issue.</div>
                    </div>

                    <div class="mb-4">
                        <label for="attachment" class="form-label small fw-semibold">Supporting Image or Document (Optional)</label>
                        <input class="form-control" type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text small">Allowed formats: JPG, PNG, PDF, DOC (Max file size: 5 MB). Uploading site photos accelerates resolution.</div>
                        <div id="file-preview-container"></div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <span class="fw-bold small text-dark">SDG 16 Public Integrity Guarantee</span>
                        </div>
                        <p class="text-muted small mb-0">
                            Upon submission, a unique Complaint ID will be automatically generated. You can trace every subsequent action taken by municipal officials.
                        </p>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo base_url('user/dashboard.php'); ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 fw-semibold">
                            <i class="fa-solid fa-paper-plane me-2"></i> Submit Complaint
                        </button>
                    </div>
                </form>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
