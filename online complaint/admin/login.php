<?php
/**
 * Administrator Login
 * Online Complaint Registration System
 */
$page_title = "Admin Login";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Redirect if already logged in as admin
if (is_admin_logged_in()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit();
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter your administrative username and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
                // Self-harden password if stored as plain text
                if ($password === $admin['password']) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $upStmt->execute([$newHash, $admin['id']]);
                }

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];

                set_flash('success', 'Logged in successfully as Administrator.');
                header('Location: ' . base_url('admin/dashboard.php'));
                exit();
            } else {
                $error = "Invalid administrator credentials. Access denied.";
            }
        } catch (PDOException $e) {
            $error = "Authentication error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body style="background: linear-gradient(135deg, #091e3a 0%, #112d4e 100%); min-height: 100vh; display: flex; align-items: center;">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-9">
            <div class="card p-4 p-md-5 border-0 shadow-lg rounded-4">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 54px; height: 54px; font-size: 1.5rem; background: #e11d48;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Admin Console</h3>
                    <p class="text-muted small">Restricted authority access for grievance officers & supervisors.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo e($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php render_flash(); ?>

                <form action="<?php echo base_url('admin/login.php'); ?>" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-semibold">Admin Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-user-shield text-muted"></i></span>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo e($username); ?>" placeholder="e.g. admin" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold mt-3" style="background: #e11d48; border-color: #e11d48;">
                        <i class="fa-solid fa-lock-open me-2"></i> Access Admin Console
                    </button>
                </form>

                <!-- Demo Credentials Helper -->
                <div class="card bg-light border-0 p-3 mt-4 rounded-3 small">
                    <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-key text-warning me-1"></i> Default Admin Credentials:</div>
                    <div class="text-secondary">Username: <code>admin</code></div>
                    <div class="text-secondary">Password: <code>admin123</code></div>
                </div>

                <div class="text-center mt-4 pt-2 border-top">
                    <a href="<?php echo base_url('index.php'); ?>" class="small text-muted text-decoration-none">
                        &larr; Back to Public Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
