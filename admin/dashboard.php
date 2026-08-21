<?php
$page_title = 'Dashboard & Analytics';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$admin_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);
$admin_error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);

// Metrics Queries
$totalCandidates = 0;
$cRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM jobregistration");
if ($cRes) $totalCandidates = mysqli_fetch_assoc($cRes)['cnt'] ?? 0;

$totalJobs = 0;
$jRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM jobs WHERE status = 'Active'");
if ($jRes) $totalJobs = mysqli_fetch_assoc($jRes)['cnt'] ?? 0;

$totalApplications = 0;
$aRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM job_applications");
if ($aRes) $totalApplications = mysqli_fetch_assoc($aRes)['cnt'] ?? 0;

$totalShortlisted = 0;
$sRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM job_applications WHERE status IN ('Shortlisted', 'Interview Scheduled', 'Selected')");
if ($sRes) $totalShortlisted = mysqli_fetch_assoc($sRes)['cnt'] ?? 0;

$totalCompanies = 0;
$compRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM companies");
if ($compRes) $totalCompanies = mysqli_fetch_assoc($compRes)['cnt'] ?? 0;

// Application status breakdown
$statusCounts = [
    'Applied' => 0,
    'Under Review' => 0,
    'Shortlisted' => 0,
    'Interview Scheduled' => 0,
    'Selected' => 0,
    'Rejected' => 0
];
$stRes = mysqli_query($con, "SELECT status, COUNT(*) as cnt FROM job_applications GROUP BY status");
if ($stRes) {
    while ($r = mysqli_fetch_assoc($stRes)) {
        if (isset($statusCounts[$r['status']])) {
            $statusCounts[$r['status']] = (int)$r['cnt'];
        }
    }
}

// Recent Applications (Limit 6)
$recentAppsQuery = "SELECT ja.*, j.title as job_title, j.company_name 
                    FROM job_applications ja 
                    LEFT JOIN jobs j ON ja.job_id = j.id 
                    ORDER BY ja.id DESC LIMIT 6";
$recentApps = mysqli_query($con, $recentAppsQuery);

// Recent Job Postings (Limit 4)
$recentJobsQuery = "SELECT j.*, COUNT(ja.id) as applicant_count 
                    FROM jobs j 
                    LEFT JOIN job_applications ja ON j.id = ja.job_id 
                    GROUP BY j.id 
                    ORDER BY j.id DESC LIMIT 4";
$recentJobs = mysqli_query($con, $recentJobsQuery);
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Welcome Back, <?php echo htmlspecialchars($admin_user_name); ?> 👋</h1>
        <p>Real-time overview of candidates, open positions, application pipelines, and hiring metrics.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="candidate_add.php" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
            <i class="fas fa-user-plus me-1"></i> Add Candidate
        </a>
        <a href="job_add.php" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
            <i class="fas fa-plus-circle me-1"></i> Post New Job
        </a>
    </div>
</div>

<!-- Flash Alerts -->
<?php if (!empty($admin_success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $admin_success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($admin_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $admin_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<style>
    /* Metric Cards */
    .kpi-card {
        background: #ffffff;
        border-radius: var(--radius-lg);
        padding: 24px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-subtle);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-card);
    }

    .kpi-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .kpi-num {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .kpi-lbl {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .kpi-badge-link {
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 10px;
    }

    /* Content Cards */
    .admin-card {
        background: #ffffff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-subtle);
        margin-bottom: 28px;
        overflow: hidden;
    }

    .admin-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
    }

    .admin-card-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Status Badges */
    .badge-status {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-applied { background: #fef3c7; color: #b45309; }
    .status-review { background: #e0f2fe; color: #0369a1; }
    .status-shortlisted { background: #ede9fe; color: #6d28d9; }
    .status-interview { background: #ffedd5; color: #c2410c; }
    .status-selected { background: #dcfce7; color: #15803d; }
    .status-rejected { background: #fee2e2; color: #b91c1c; }

    /* Pipeline Bars */
    .pipeline-item {
        margin-bottom: 16px;
    }

    .pipeline-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .pipeline-bar {
        height: 8px;
        background: #f1f5f9;
        border-radius: 4px;
        overflow: hidden;
    }

    .pipeline-progress {
        height: 100%;
        border-radius: 4px;
        transition: width 0.6s ease;
    }
</style>

<!-- Top KPI Row -->
<div class="row g-3 mb-4">
    
    <!-- Candidate List (Restricted Admin Feature) -->
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-card-top">
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold" style="font-size: 0.72rem;">ADMIN ONLY</span>
            </div>
            <div class="kpi-num"><?php echo $totalCandidates; ?></div>
            <div class="kpi-lbl">Total Candidates Registered</div>
            <a href="candidates.php" class="kpi-badge-link text-primary">
                <span>Manage Candidate List</span> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Jobs -->
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-card-top">
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-briefcase"></i>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 0.72rem;">ACTIVE</span>
            </div>
            <div class="kpi-num"><?php echo $totalJobs; ?></div>
            <div class="kpi-lbl">Open Job Postings</div>
            <a href="jobs.php" class="kpi-badge-link text-success">
                <span>View Job Openings</span> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Applications -->
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-card-top">
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-file-signature"></i>
                </div>
                <span class="badge bg-warning bg-opacity-10 text-warning fw-bold" style="font-size: 0.72rem;">ATS INBOX</span>
            </div>
            <div class="kpi-num"><?php echo $totalApplications; ?></div>
            <div class="kpi-lbl">Total Job Applications</div>
            <a href="applications.php" class="kpi-badge-link text-warning">
                <span>Application Tracker</span> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Shortlisted / Placed -->
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-card-top">
                <div class="kpi-icon bg-purple bg-opacity-10 text-purple" style="background: rgba(147, 51, 234, 0.1); color: #9333ea;">
                    <i class="fas fa-trophy"></i>
                </div>
                <span class="badge bg-info bg-opacity-10 text-info fw-bold" style="font-size: 0.72rem;">SUCCESS</span>
            </div>
            <div class="kpi-num"><?php echo $totalShortlisted; ?></div>
            <div class="kpi-lbl">Shortlisted & Selected</div>
            <a href="applications.php?status=Shortlisted" class="kpi-badge-link" style="color: #9333ea;">
                <span>View Qualified</span> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

</div>

<!-- Middle Section: Recent Applications & Pipeline Stats -->
<div class="row g-4 mb-4">
    
    <!-- Recent Applications Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3><i class="fas fa-inbox text-primary"></i> Recent Candidate Applications</h3>
                <a href="applications.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">Candidate</th>
                            <th>Applied Position</th>
                            <th>Qualification</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentApps && mysqli_num_rows($recentApps) > 0): ?>
                            <?php while ($app = mysqli_fetch_assoc($recentApps)): 
                                $sClass = 'status-applied';
                                switch ($app['status']) {
                                    case 'Under Review': $sClass = 'status-review'; break;
                                    case 'Shortlisted': $sClass = 'status-shortlisted'; break;
                                    case 'Interview Scheduled': $sClass = 'status-interview'; break;
                                    case 'Selected': $sClass = 'status-selected'; break;
                                    case 'Rejected': $sClass = 'status-rejected'; break;
                                }
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                <?php echo strtoupper(substr($app['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['name']); ?></div>
                                                <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($app['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($app['jobpost']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($app['degree']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?php echo $sClass; ?>">
                                            <i class="fas fa-circle" style="font-size: 0.45rem;"></i>
                                            <?php echo htmlspecialchars($app['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="applications.php?search=<?php echo urlencode($app['name']); ?>" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-2x mb-2 text-secondary"></i>
                                    <div>No applications received yet.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Application Pipeline Breakdown -->
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h3><i class="fas fa-filter text-info"></i> Pipeline Funnel</h3>
                <span class="badge bg-light text-dark border"><?php echo $totalApplications; ?> Total</span>
            </div>

            <div class="p-4">
                <?php
                $pipelineStatuses = [
                    ['Applied', $statusCounts['Applied'], '#f59e0b'],
                    ['Under Review', $statusCounts['Under Review'], '#0284c7'],
                    ['Shortlisted', $statusCounts['Shortlisted'], '#7c3aed'],
                    ['Interview Scheduled', $statusCounts['Interview Scheduled'], '#ea580c'],
                    ['Selected', $statusCounts['Selected'], '#16a34a'],
                    ['Rejected', $statusCounts['Rejected'], '#dc2626']
                ];

                foreach ($pipelineStatuses as $p):
                    $name = $p[0];
                    $cnt = $p[1];
                    $color = $p[2];
                    $pct = ($totalApplications > 0) ? round(($cnt / $totalApplications) * 100) : 0;
                ?>
                    <div class="pipeline-item">
                        <div class="pipeline-info">
                            <span><?php echo $name; ?></span>
                            <span class="text-muted"><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
                        </div>
                        <div class="pipeline-bar">
                            <div class="pipeline-progress" style="width: <?php echo $pct; ?>%; background: <?php echo $color; ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="mt-4 pt-3 border-top d-grid gap-2">
                    <a href="candidates.php" class="btn btn-outline-primary rounded-pill fw-semibold" style="font-size: 0.85rem;">
                        <i class="fas fa-users me-1"></i> Go to Candidate Management
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Recent Jobs Grid -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-briefcase text-success"></i> Active Job Openings</h3>
        <a href="jobs.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
            Manage All Jobs <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="p-4">
        <div class="row g-3">
            <?php if ($recentJobs && mysqli_num_rows($recentJobs) > 0): ?>
                <?php while ($job = mysqli_fetch_assoc($recentJobs)): ?>
                    <div class="col-md-6 col-xl-3">
                        <div class="p-3 border rounded-4 bg-light bg-opacity-50 h-100 d-flex flex-direction-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.72rem;">
                                        <?php echo htmlspecialchars($job['category']); ?>
                                    </span>
                                    <span class="badge <?php echo ($job['status'] === 'Active') ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo htmlspecialchars($job['status']); ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1" style="font-size: 0.98rem;"><?php echo htmlspecialchars($job['title']); ?></h5>
                                <div class="text-muted mb-2" style="font-size: 0.82rem;">
                                    <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                                </div>
                                <div class="text-secondary fw-semibold mb-3" style="font-size: 0.82rem;">
                                    <i class="fas fa-wallet me-1 text-success"></i> <?php echo htmlspecialchars($job['salary_range']); ?>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-muted" style="font-size: 0.78rem;">
                                    <i class="fas fa-users me-1 text-primary"></i> <strong><?php echo $job['applicant_count']; ?></strong> Applicants
                                </span>
                                <a href="job_edit.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                    Edit Job
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4 text-muted">
                    No active job postings yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
