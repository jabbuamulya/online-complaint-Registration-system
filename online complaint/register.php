<?php
$page_title = "Citizen Registration";
$active_page = "register";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('user/dashboard.php'));
    exit();
}

$errors = [];
$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = trim($_POST['address'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = "Please enter your full name.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }
    if (empty($phone) || strlen($phone) < 7) {
        $errors[] = "Please provide a valid contact phone number.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "An account with this email address is already registered. Please login instead.";
        }
    }

    // Insert user
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $hashedPassword, $address]);
            
            $newUserId = $pdo->lastInsertId();

            // Automatically log in
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            set_flash('success', 'Registration successful! Welcome to the Civic Complaint Portal.');
            header('Location: ' . base_url('user/dashboard.php'));
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error during registration: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-custom p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 50px; height: 50px; font-size: 1.4rem;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Citizen Registration</h3>
                    <p class="text-muted small">Create your account to register and track civic complaints online.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 small ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo base_url('register.php'); ?>" method="POST" id="citizenRegistrationForm" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo e($name); ?>" placeholder="e.g. Ramesh Kumar" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo e($email); ?>" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-muted"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo e($phone); ?>" placeholder="10-digit mobile number" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 characters" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-shield-halved text-muted"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label small fw-semibold">Residential / Ward Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-muted"></i></span>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="House/Flat No, Street, Ward, City..."><?php echo e($address); ?></textarea>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
                        <label class="form-check-label small text-muted" for="termsCheck">
                            I declare the complaints submitted by me will be authentic and for public interest under SDG 16 guidelines.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-user-check me-2"></i> Complete Registration
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <span class="text-muted small">Already have a citizen account?</span>
                    <a href="<?php echo base_url('login.php'); ?>" class="small fw-bold text-primary ms-1">Sign In here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
