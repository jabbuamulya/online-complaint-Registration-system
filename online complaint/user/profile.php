<?php
$page_title = "Citizen Profile";
$user_active_page = "profile";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/includes/user_header.php';

$userId = $_SESSION['user_id'];
$profileError = '';
$profileSuccess = '';
$pwdError = '';
$pwdSuccess = '';

// Handle Profile Info Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($phone)) {
        $profileError = "Name and phone number are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $address, $userId]);
            $_SESSION['user_name'] = $name;
            set_flash('success', 'Profile information updated successfully.');
            header('Location: ' . base_url('user/profile.php'));
            exit();
        } catch (PDOException $e) {
            $profileError = "Database error: " . $e->getMessage();
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pwd = $_POST['current_password'] ?? '';
    $new_pwd = $_POST['new_password'] ?? '';
    $confirm_pwd = $_POST['confirm_new_password'] ?? '';

    if (empty($current_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
        $pwdError = "All password fields are mandatory.";
    } elseif (strlen($new_pwd) < 6) {
        $pwdError = "New password must be at least 6 characters long.";
    } elseif ($new_pwd !== $confirm_pwd) {
        $pwdError = "New password and confirmation do not match.";
    } else {
        // Fetch current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();

        if (password_verify($current_pwd, $storedHash) || $current_pwd === $storedHash) {
            $newHashed = password_hash($new_pwd, PASSWORD_DEFAULT);
            $upStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upStmt->execute([$newHashed, $userId]);
            $pwdSuccess = "Your password has been changed successfully.";
        } else {
            $pwdError = "Current password is incorrect.";
        }
    }
}

// Fetch refreshed data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Edit Profile Info -->
        <div class="card card-custom p-4 p-md-5 border-0 shadow-sm">
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-id-card me-2 text-primary"></i> Citizen Profile</h4>
            <p class="text-muted small mb-4">Manage your personal contact details used for complaint verification.</p>

            <?php if (!empty($profileError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e($profileError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?php echo base_url('user/profile.php'); ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address (Read-only)</label>
                    <input type="email" class="form-control bg-light" value="<?php echo e($user['email']); ?>" readonly disabled>
                    <div class="form-text small">Your registered email address is fixed for security and audit trail.</div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo e($user['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label small fw-semibold">Contact Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo e($user['phone']); ?>" required>
                </div>

                <div class="mb-4">
                    <label for="address" class="form-label small fw-semibold">Residential / Ward Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo e($user['address']); ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Registered on: <?php echo format_date($user['created_at']); ?></span>
                    <button type="submit" name="update_profile" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Change Password Card -->
        <div class="card card-custom p-4 p-md-5 border-0 shadow-sm">
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-lock me-2 text-warning"></i> Security & Password</h4>
            <p class="text-muted small mb-4">Ensure your account uses a strong and unique password.</p>

            <?php if (!empty($pwdError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e($pwdError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pwdSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e($pwdSuccess); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?php echo base_url('user/profile.php'); ?>" method="POST">
                <div class="mb-3">
                    <label for="current_password" class="form-label small fw-semibold">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label small fw-semibold">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Min. 6 characters" required>
                </div>

                <div class="mb-4">
                    <label for="confirm_new_password" class="form-label small fw-semibold">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                </div>

                <button type="submit" name="change_password" class="btn btn-warning w-100 fw-bold">
                    <i class="fa-solid fa-key me-1"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/user_footer.php'; ?>
