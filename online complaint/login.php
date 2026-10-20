<?php
$page_title = "Citizen Login";
$active_page = "login";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('user/dashboard.php'));
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email address and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
                // If it was plain-text from seed data, upgrade to secure bcrypt hash
                if ($password === $user['password']) {
                    $upgradedHash = password_hash($password, PASSWORD_DEFAULT);
                    $upStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upStmt->execute([$upgradedHash, $user['id']]);
                }

                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                set_flash('success', 'Welcome back, ' . e($user['name']) . '!');
                
                // If redirect intended
                $redirect = $_GET['redirect'] ?? base_url('user/dashboard.php');
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = "Invalid email address or password. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Login error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card card-custom p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.4rem;">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Citizen Sign In</h3>
                    <p class="text-muted small">Access your grievance dashboard & registered complaints.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div><?php echo e($error); ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo base_url('login.php'); ?>" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label small fw-semibold mb-0">Password</label>
                        </div>
                        <div class="input-group mt-1">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mt-2">
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Sign In to Dashboard
                    </button>
                </form>

                <!-- Demo Credentials Helper Box -->
                <div class="card bg-light border-0 p-3 mt-4 rounded-3 small">
                    <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-circle-info text-info me-1"></i> Demo Citizen Credentials:</div>
                    <div class="text-secondary">Email: <code>rahul@example.com</code></div>
                    <div class="text-secondary">Password: <code>user123</code></div>
                </div>

                <div class="text-center mt-4 pt-2 border-top">
                    <span class="text-muted small">New to the portal?</span>
                    <a href="<?php echo base_url('register.php'); ?>" class="small fw-bold text-primary ms-1">Register New Account</a>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="<?php echo base_url('admin/login.php'); ?>" class="text-secondary small text-decoration-none">
                    <i class="fa-solid fa-shield-halved me-1"></i> Government Officer / Administrator Login &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
