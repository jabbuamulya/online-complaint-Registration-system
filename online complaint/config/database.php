<?php
/**
 * Database Configuration
 * Online Complaint Registration System
 */

// Database credentials for XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'complaint_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base Application Configuration
define('APP_NAME', 'Online Complaint Registration System');
define('APP_TAGLINE', 'Transparent, Accountable & Responsive Public Grievance Redressal');
define('SDG_TARGET', 'SDG 16: Peace, Justice & Strong Institutions');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // If the database has not been created yet or MySQL is stopped
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error - <?php echo APP_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            body { background: #f1f5f9; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; min-height: 100vh; padding: 20px; }
            .card { max-width: 650px; margin: auto; border: none; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        </style>
    </head>
    <body>
        <div class="card p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="text-danger mb-3" style="font-size: 3rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="fw-bold text-dark">Database Setup Required</h3>
                <p class="text-muted">The system could not connect to MySQL database <strong>complaint_system</strong>.</p>
            </div>
            
            <div class="alert alert-warning border-0 small">
                <strong>Error Details:</strong> <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>

            <h5 class="fw-semibold mt-3 mb-2"><i class="fa-solid fa-list-check me-2 text-primary"></i> Quick Setup Steps (XAMPP):</h5>
            <ol class="small text-secondary ps-3 lh-lg">
                <li>Open <strong>XAMPP Control Panel</strong> and ensure both <strong>Apache</strong> and <strong>MySQL</strong> are started (green).</li>
                <li>Open your browser and navigate to: <a href="http://localhost/phpmyadmin" target="_blank" class="fw-bold">http://localhost/phpmyadmin</a></li>
                <li>Click <strong>New</strong> and create a database named: <code>complaint_system</code></li>
                <li>Click <strong>Import</strong> tab, browse for file: <code>database/complaint_system.sql</code> in this project folder, and click <strong>Go</strong>.</li>
                <li>Refresh this page.</li>
            </ol>

            <div class="text-center mt-4">
                <button onclick="window.location.reload();" class="btn btn-primary px-4">
                    <i class="fa-solid fa-rotate-right me-2"></i> Retry Connection
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
