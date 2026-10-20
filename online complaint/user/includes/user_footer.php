    </div>
</main>

<footer class="bg-white border-top py-3 mt-auto">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-muted small">
        <div>
            &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Supporting SDG 16.
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="<?php echo base_url('track.php'); ?>" class="text-secondary text-decoration-none">Track Status</a>
            <span>•</span>
            <a href="<?php echo base_url('about.php'); ?>" class="text-secondary text-decoration-none">Citizen Charter</a>
            <span>•</span>
            <a href="<?php echo base_url('logout.php'); ?>" class="text-secondary text-decoration-none">Logout</a>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo base_url('assets/js/script.js'); ?>"></script>
</body>
</html>
