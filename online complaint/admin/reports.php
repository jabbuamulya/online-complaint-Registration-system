<?php
$page_title = "Complaint Analytics & Reports";
$admin_active_page = "reports";
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

try {
    // 1. Overall Metrics
    $totalComplaints = (int)$pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
    $resolvedComplaints = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Resolved'")->fetchColumn();
    $rejectedComplaints = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'Rejected'")->fetchColumn();
    $activeComplaints = $totalComplaints - $resolvedComplaints - $rejectedComplaints;
    $resolutionRate = ($totalComplaints > 0) ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 0;

    // 2. Complaints by Category
    $catStmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM complaints 
        GROUP BY category 
        ORDER BY count DESC
    ");
    $categoryData = $catStmt->fetchAll();

    $catLabels = [];
    $catCounts = [];
    foreach ($categoryData as $row) {
        $catLabels[] = $row['category'];
        $catCounts[] = (int)$row['count'];
    }

    // 3. Complaints by Status
    $statusStmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM complaints 
        GROUP BY status
    ");
    $statusData = $statusStmt->fetchAll();

    $statusLabels = [];
    $statusCounts = [];
    foreach ($statusData as $row) {
        $statusLabels[] = $row['status'];
        $statusCounts[] = (int)$row['count'];
    }

    // 4. Monthly Complaints Trend
    $monthlyStmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') as month_label, COUNT(*) as count 
        FROM complaints 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
        ORDER BY MIN(created_at) ASC
        LIMIT 6
    ");
    $monthlyData = $monthlyStmt->fetchAll();

    $monthLabels = [];
    $monthCounts = [];
    foreach ($monthlyData as $row) {
        $monthLabels[] = $row['month_label'];
        $monthCounts[] = (int)$row['count'];
    }

    // Fallback if data is minimal
    if (empty($monthLabels)) {
        $monthLabels = [date('M Y')];
        $monthCounts = [$totalComplaints];
    }

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error generating reports: ' . e($e->getMessage()) . '</div>';
    $totalComplaints = $resolvedComplaints = $rejectedComplaints = $activeComplaints = $resolutionRate = 0;
    $catLabels = $catCounts = $statusLabels = $statusCounts = $monthLabels = $monthCounts = [];
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- Include Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Grievance Analytics & Redressal Audit</h3>
        <p class="text-muted small mb-0">Quantitative assessment of municipal complaint turnaround, category loads, and SDG 16 institutional performance.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="window.print();">
            <i class="fa-solid fa-file-pdf me-1"></i> Print / Export Report
        </button>
    </div>
</div>

<!-- KPI Summary Highlights -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Filed</div>
                <div class="stat-value text-primary"><?php echo $totalComplaints; ?></div>
                <span class="text-muted small">All recorded cases</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-blue">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Resolved</div>
                <div class="stat-value text-success"><?php echo $resolvedComplaints; ?></div>
                <span class="text-muted small">Formally closed</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Active / Pending</div>
                <div class="stat-value text-amber"><?php echo $activeComplaints; ?></div>
                <span class="text-muted small">In pipeline</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-amber">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Resolution Rate</div>
                <div class="stat-value text-info"><?php echo $resolutionRate; ?>%</div>
                <span class="text-muted small">SLA compliance</span>
            </div>
            <div class="stat-icon-wrapper stat-icon-cyan">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Complaints by Category -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-1">Complaints by Category</h5>
            <p class="text-muted small mb-3">Distribution of grievances across municipal departments.</p>
            <div style="position: relative; height: 280px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Complaints by Status -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-1">Complaints by Status</h5>
            <p class="text-muted small mb-3">Breakdown across all 6 grievance workflow phases.</p>
            <div style="position: relative; height: 280px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 3: Monthly Intake Trend -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-1">Monthly Complaints Intake</h5>
            <p class="text-muted small mb-3">Volume trend over consecutive operational months.</p>
            <div style="position: relative; height: 280px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 4: Resolved vs Pending -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-1">Resolved vs Pending Pipeline</h5>
            <p class="text-muted small mb-3">Proportion of closed grievances to outstanding cases.</p>
            <div style="position: relative; height: 280px;">
                <canvas id="ratioChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabular Report Breakdown -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-table me-2 text-primary"></i> Category Redressal Audit Table</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase">
                        <th>Department Category</th>
                        <th>Total Complaints</th>
                        <th>Resolved Cases</th>
                        <th>Active Pending</th>
                        <th>Resolution %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmtTot = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE category = ?");
                    $stmtRes = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE category = ? AND status = 'Resolved'");
                    foreach (get_categories() as $cName => $cMeta):
                        $stmtTot->execute([$cName]);
                        $cTotal = (int)$stmtTot->fetchColumn();

                        $stmtRes->execute([$cName]);
                        $cRes = (int)$stmtRes->fetchColumn();

                        $cAct = $cTotal - $cRes;
                        $cRate = ($cTotal > 0) ? round(($cRes / $cTotal) * 100) : 0;
                    ?>
                        <tr>
                            <td>
                                <i class="fa-solid <?php echo $cMeta['icon']; ?> me-2" style="color: <?php echo $cMeta['color']; ?>;"></i>
                                <span class="fw-semibold text-dark"><?php echo e($cName); ?></span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $cTotal; ?></span></td>
                            <td><span class="badge bg-success-subtle text-success"><?php echo $cRes; ?></span></td>
                            <td><span class="badge bg-warning-subtle text-warning-emphasis"><?php echo $cAct; ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $cRate; ?>%"></div>
                                    </div>
                                    <span class="small fw-bold text-secondary"><?php echo $cRate; ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Category Bar Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($catLabels); ?>,
            datasets: [{
                label: 'Complaints',
                data: <?php echo json_encode($catCounts); ?>,
                backgroundColor: '#0284c7',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // 2. Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($statusLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($statusCounts); ?>,
                backgroundColor: [
                    '#64748b', // Submitted
                    '#f59e0b', // Under Review
                    '#06b6d4', // Assigned
                    '#3b82f6', // In Progress
                    '#10b981', // Resolved
                    '#ef4444'  // Rejected
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 3. Monthly Intake Line Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($monthLabels); ?>,
            datasets: [{
                label: 'Registered Grievances',
                data: <?php echo json_encode($monthCounts); ?>,
                borderColor: '#1e40af',
                backgroundColor: 'rgba(30, 64, 175, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // 4. Resolved vs Pending Pie Chart
    const ratioCtx = document.getElementById('ratioChart').getContext('2d');
    new Chart(ratioCtx, {
        type: 'pie',
        data: {
            labels: ['Resolved', 'Active / In Progress', 'Rejected'],
            datasets: [{
                data: [<?php echo $resolvedComplaints; ?>, <?php echo $activeComplaints; ?>, <?php echo $rejectedComplaints; ?>],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
