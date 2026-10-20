<?php
$page_title = "About SDG 16 & Grievance Redressal";
$active_page = "about";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<section class="bg-primary text-white py-5" style="background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%) !important;">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                    <i class="fa-solid fa-scale-balanced me-1"></i> Public Integrity & Redressal
                </span>
                <h1 class="fw-bold display-5">About the System</h1>
                <p class="lead text-white-50 mb-0">
                    Bridging citizens and public administration through an open, transparent, and technology-driven grievance redressal portal.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="text-primary text-uppercase fw-bold small">Our Mission</span>
                <h2 class="fw-bold mb-4">Empowering Citizens with Transparent Public Accountability</h2>
                <p class="text-secondary leading-relaxed mb-3">
                    Civic infrastructure issues—such as pothole-ridden roads, contaminated water pipelines, malfunctioning street lamps, and unattended waste—directly degrade quality of life and jeopardize public safety.
                </p>
                <p class="text-secondary leading-relaxed mb-4">
                    The <strong>Online Complaint Registration System</strong> transforms public service delivery from an opaque bureaucratic process into an open, auditable, and citizen-friendly digital ecosystem. Every citizen receives a verifiable tracking token, direct departmental oversight, and clear completion logs.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded bg-light border">
                            <i class="fa-solid fa-eye fs-3 text-primary mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Total Transparency</h6>
                                <p class="text-muted small mb-0">Live status progression visible without bureaucratic friction.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded bg-light border">
                            <i class="fa-solid fa-user-shield fs-3 text-success mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Direct Assignment</h6>
                                <p class="text-muted small mb-0">Departmental officers personally assigned and accountable.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%);">
                    <div class="text-center mb-4">
                        <div class="brand-icon-box mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.8rem; background: #00689D;">
                            <i class="fa-solid fa-scale-balanced text-white"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Commitment to SDG 16</h4>
                        <p class="text-muted small">Peace, Justice and Strong Institutions</p>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="bg-white p-3 rounded-3 shadow-sm border-start border-4 border-primary">
                            <div class="fw-bold text-dark mb-1">Target 16.6: Accountable Institutions</div>
                            <div class="text-muted small">Develop effective, accountable, and transparent institutions at all levels through digitized complaints and public resolution histories.</div>
                        </div>
                        <div class="bg-white p-3 rounded-3 shadow-sm border-start border-4 border-success">
                            <div class="fw-bold text-dark mb-1">Target 16.7: Responsive Decision-Making</div>
                            <div class="text-muted small">Ensure responsive, inclusive, participatory, and representative decision-making at all civic levels.</div>
                        </div>
                        <div class="bg-white p-3 rounded-3 shadow-sm border-start border-4 border-info">
                            <div class="fw-bold text-dark mb-1">Target 16.10: Public Access to Information</div>
                            <div class="text-muted small">Guarantee public access to information and protect fundamental civic rights through open tracking.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Citizen Charter Section -->
<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-bold mb-2">Service Guarantees</span>
            <h2 class="fw-bold">Citizen Charter & Service Level Agreement (SLA)</h2>
            <p class="text-muted">Standard operating turnaround timelines across municipal departments.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card h-100 p-4 border text-center shadow-sm">
                    <div class="display-6 fw-bold text-primary mb-2">24h</div>
                    <h5 class="fw-bold">Initial Verification</h5>
                    <p class="text-muted small mb-0">System triage and authenticity verification by the municipal helpdesk.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 p-4 border text-center shadow-sm">
                    <div class="display-6 fw-bold text-warning mb-2">48h</div>
                    <h5 class="fw-bold">Officer Assignment</h5>
                    <p class="text-muted small mb-0">Designation of a specific municipal executive or on-ground engineer.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 p-4 border text-center shadow-sm">
                    <div class="display-6 fw-bold text-info mb-2">3-5 Days</div>
                    <h5 class="fw-bold">On-Site Work</h5>
                    <p class="text-muted small mb-0">Physical inspection, utility repair, waste clearing or replacement.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 p-4 border text-center shadow-sm">
                    <div class="display-6 fw-bold text-success mb-2">Closure</div>
                    <h5 class="fw-bold">Verified Redressal</h5>
                    <p class="text-muted small mb-0">Formal resolution log, official notes, and citizen feedback loop.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Architecture Callout -->
<section class="py-5 bg-white border-top">
    <div class="container py-3">
        <div class="text-center mb-5">
            <h3 class="fw-bold">Engineered for Reliability</h3>
            <p class="text-muted">Built with robust, accessible web standards.</p>
        </div>

        <div class="row g-4 text-center justify-content-center">
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded border">
                    <i class="fa-brands fa-html5 fs-2 text-danger mb-2"></i>
                    <div class="fw-bold">HTML5 & CSS3</div>
                    <div class="text-muted small">Modern Semantic UI</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded border">
                    <i class="fa-brands fa-php fs-2 text-primary mb-2"></i>
                    <div class="fw-bold">PHP & PDO</div>
                    <div class="text-muted small">Prepared Statements & Security</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded border">
                    <i class="fa-solid fa-database fs-2 text-success mb-2"></i>
                    <div class="fw-bold">MySQL Database</div>
                    <div class="text-muted small">Relational Integrity & Indexes</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 bg-light rounded border">
                    <i class="fa-solid fa-server fs-2 text-warning mb-2"></i>
                    <div class="fw-bold">XAMPP Environment</div>
                    <div class="text-muted small">Apache & Localhost Ready</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
