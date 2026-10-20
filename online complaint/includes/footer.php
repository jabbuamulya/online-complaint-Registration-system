</main>

<footer class="footer-gov">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon-box" style="width: 38px; height: 38px; font-size: 1rem;">
                        <i class="fa-solid fa-landmark-dome"></i>
                    </div>
                    <span class="fs-5 fw-bold text-white"><?php echo APP_NAME; ?></span>
                </div>
                <p class="text-secondary small leading-relaxed">
                    A citizen-centric grievance redressal system built to enhance transparency, public accountability, and prompt civic issue resolution.
                </p>
                <div class="mt-3">
                    <span class="sdg-badge">
                        <i class="fa-solid fa-scale-balanced"></i> SDG 16: Peace, Justice & Strong Institutions
                    </span>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled small lh-lg">
                    <li><a href="<?php echo base_url('index.php'); ?>"><i class="fa-solid fa-chevron-right me-1 text-primary"></i> Home</a></li>
                    <li><a href="<?php echo base_url('about.php'); ?>"><i class="fa-solid fa-chevron-right me-1 text-primary"></i> About SDG 16</a></li>
                    <li><a href="<?php echo base_url('track.php'); ?>"><i class="fa-solid fa-chevron-right me-1 text-primary"></i> Track Complaint</a></li>
                    <li><a href="<?php echo base_url('register.php'); ?>"><i class="fa-solid fa-chevron-right me-1 text-primary"></i> Citizen Register</a></li>
                    <li><a href="<?php echo base_url('login.php'); ?>"><i class="fa-solid fa-chevron-right me-1 text-primary"></i> Citizen Login</a></li>
                    <li><a href="<?php echo base_url('admin/login.php'); ?>"><i class="fa-solid fa-chevron-right me-1 text-primary"></i> Admin Portal</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h5>Categories</h5>
                <ul class="list-unstyled small lh-lg">
                    <li><i class="fa-solid fa-road me-2 text-warning"></i> Roads & Potholes</li>
                    <li><i class="fa-solid fa-faucet-drip me-2 text-info"></i> Water Supply</li>
                    <li><i class="fa-solid fa-bolt me-2 text-warning"></i> Electricity & Power</li>
                    <li><i class="fa-solid fa-trash-can me-2 text-danger"></i> Garbage & Cleanliness</li>
                    <li><i class="fa-solid fa-lightbulb me-2 text-warning"></i> Street Lighting</li>
                    <li><i class="fa-solid fa-water me-2 text-teal"></i> Drainage & Sewage</li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Citizen Helpline</h5>
                <p class="small text-secondary mb-2">Need immediate assistance or escalation?</p>
                <div class="d-flex align-items-center gap-2 mb-2 text-white">
                    <i class="fa-solid fa-phone text-warning"></i>
                    <span class="fw-bold">1800-111-2026 (Toll-Free)</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2 small text-secondary">
                    <i class="fa-solid fa-envelope text-primary"></i>
                    <span>support@complaints.gov.in</span>
                </div>
                <div class="d-flex align-items-center gap-2 small text-secondary">
                    <i class="fa-solid fa-clock text-info"></i>
                    <span>Monday - Saturday: 9:00 AM - 6:00 PM</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-secondary small">
            <div>
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
            </div>
            <div>
                Developed for B.Tech Web Technology Project | Designed for XAMPP
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo base_url('assets/js/script.js'); ?>"></script>
</body>
</html>
