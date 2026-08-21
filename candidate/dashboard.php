<?php
$page_title = 'Student / Candidate Hub';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$cand_success = $_SESSION['candidate_success'] ?? ($_SESSION['login_success'] ?? '');
unset($_SESSION['candidate_success']);
unset($_SESSION['login_success']);

$cand_error = $_SESSION['candidate_error'] ?? '';
unset($_SESSION['candidate_error']);

// Compute Profile Completeness
$profileScore = 20; // base score for account creation
if (!empty($candidate_profile['phone'])) $profileScore += 15;
if (!empty($candidate_profile['degree'])) $profileScore += 15;
if (!empty($candidate_profile['skills'])) $profileScore += 20;
if (!empty($candidate_profile['bio'])) $profileScore += 15;
if (!empty($candidate_profile['resume_url']) || !empty($candidate_profile['portfolio_url'])) $profileScore += 15;

// Candidate Application Metrics
$cEmailSafe = mysqli_real_escape_string($con, $candidate_email);
$appsQ = "SELECT ja.*, j.title as job_title, j.company_name, j.location, j.salary_range, j.company_logo 
          FROM job_applications ja 
          LEFT JOIN jobs j ON ja.job_id = j.id 
          WHERE ja.user_id = $candidate_user_id OR ja.email = '$cEmailSafe' 
          ORDER BY ja.id DESC";
$appsRes = mysqli_query($con, $appsQ);

$totalApplied = 0;
$inReview = 0;
$shortlisted = 0;
$recentAppsList = [];

if ($appsRes) {
    while ($row = mysqli_fetch_assoc($appsRes)) {
        $recentAppsList[] = $row;
        $totalApplied++;
        if ($row['status'] === 'Under Review' || $row['status'] === 'Applied') $inReview++;
        if (in_array($row['status'], ['Shortlisted', 'Interview Scheduled', 'Selected'])) $shortlisted++;
    }
}

// Recommended Jobs (Limit 4)
$recQ = "SELECT * FROM jobs WHERE status = 'Active' ORDER BY id DESC LIMIT 4";
$recRes = mysqli_query($con, $recQ);
?>

<!-- Flash Alerts -->
<?php if (!empty($cand_success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $cand_success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($cand_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $cand_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<style>
    /* Hero Banner */
    .cand-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #31104b 100%);
        color: #ffffff;
        border-radius: 24px;
        padding: 36px 32px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px -10px rgba(79, 70, 229, 0.3);
    }

    .cand-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
        filter: blur(40px);
    }

    .cand-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 24px;
    }

    .cand-hero-title h1 {
        font-size: 1.85rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .cand-hero-title p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    /* Completeness Box */
    .profile-meter-card {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 18px;
        padding: 16px 20px;
        min-width: 260px;
    }

    .meter-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: #ffffff;
    }

    .meter-progress-bar {
        height: 8px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .meter-progress-fill {
        height: 100%;
        background: #34d399;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    /* Metric Cards */
    .metric-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-subtle);
        transition: transform 0.2s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
    }

    .metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 12px;
    }

    .metric-num {
        font-size: 1.9rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
    }

    .metric-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    /* Job Cards */
    .rec-job-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--border-color);
        padding: 20px;
        box-shadow: var(--shadow-subtle);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .rec-job-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-card);
        border-color: rgba(79, 70, 229, 0.3);
    }

    /* Step Timeline */
    .step-timeline {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
    }

    .step-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #cbd5e1;
    }

    .step-dot.active {
        background: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    .step-dot.complete {
        background: var(--success);
    }
</style>

<!-- Candidate Hero Banner -->
<div class="cand-hero">
    <div class="cand-hero-content">
        <div class="cand-hero-title">
            <div class="badge bg-white bg-opacity-20 text-white mb-2 px-3 py-1 rounded-pill" style="font-size: 0.8rem;">
                <i class="fas fa-sparkles text-warning me-1"></i> Candidate Career Center
            </div>
            <h1>Welcome back, <?php echo htmlspecialchars($candidate_name); ?>! 👋</h1>
            <p><?php echo htmlspecialchars($candidate_profile['headline'] ?? 'Aspiring Technology Professional'); ?> • <?php echo htmlspecialchars($candidate_profile['city'] ?? 'India'); ?></p>
        </div>

        <div class="profile-meter-card">
            <div class="meter-label">
                <span>Profile Strength</span>
                <span class="text-success"><?php echo $profileScore; ?>% Complete</span>
            </div>
            <div class="meter-progress-bar">
                <div class="meter-progress-fill" style="width: <?php echo $profileScore; ?>%;"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-white-50" style="font-size: 0.75rem;">Keep your resume updated</span>
                <a href="profile.php" class="btn btn-sm btn-light rounded-pill px-3 fw-bold" style="font-size: 0.75rem;">
                    Update Profile
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Key Stat Counters -->
<div class="row g-3 mb-4">
    
    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="metric-num"><?php echo $totalApplied; ?></div>
            <div class="metric-label">Applications Submitted</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="metric-num"><?php echo $inReview; ?></div>
            <div class="metric-label">In Review / Evaluation</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="metric-num"><?php echo $shortlisted; ?></div>
            <div class="metric-label">Shortlisted & Interviews</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="metric-card">
            <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="metric-num"><?php echo $savedJobsCount; ?></div>
            <div class="metric-label">Bookmarked Jobs</div>
        </div>
    </div>

</div>

<!-- Main Grid: Recent Application Trackers & Quick Profile Snippet -->
<div class="row g-4 mb-5">
    
    <!-- Application Tracking List -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                    <i class="fas fa-tasks text-primary me-2"></i> Application Status Tracker
                </h5>
                <a href="applications.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                    View All Applications <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($recentAppsList)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php 
                        $slice = array_slice($recentAppsList, 0, 4);
                        foreach ($slice as $app): 
                            $sClass = 'status-applied';
                            switch ($app['status']) {
                                case 'Under Review': $sClass = 'status-review'; break;
                                case 'Shortlisted': $sClass = 'status-shortlisted'; break;
                                case 'Interview Scheduled': $sClass = 'status-interview'; break;
                                case 'Selected': $sClass = 'status-selected'; break;
                                case 'Rejected': $sClass = 'status-rejected'; break;
                            }
                        ?>
                            <div class="p-3 border rounded-4 bg-light bg-opacity-50 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($app['jobpost']); ?></h6>
                                        <span class="badge-status <?php echo $sClass; ?>">
                                            <i class="fas fa-circle" style="font-size: 0.45rem;"></i>
                                            <?php echo htmlspecialchars($app['status']); ?>
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.82rem;">
                                        <i class="fas fa-building me-1"></i> <?php echo !empty($app['company_name']) ? htmlspecialchars($app['company_name']) : 'Verified Recruiter'; ?> • 
                                        <i class="fas fa-calendar-alt ms-2 me-1"></i> Applied on <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                    </div>
                                    <?php if (!empty($app['admin_notes'])): ?>
                                        <div class="mt-2 text-primary p-2 bg-white rounded-3 border" style="font-size: 0.8rem;">
                                            <i class="fas fa-comment-dots me-1"></i> <strong>Recruiter Note:</strong> <?php echo htmlspecialchars($app['admin_notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <a href="applications.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-file-signature fa-3x mb-3 text-secondary opacity-50"></i>
                        <h5>No Job Applications Yet</h5>
                        <p style="font-size: 0.88rem;">Explore available openings and apply in 1-click using your profile.</p>
                        <a href="../jobs.php" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: var(--primary-gradient); border: none;">
                            <i class="fas fa-search me-1"></i> Browse Verified Jobs
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Candidate Profile Summary Snippet -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm h-100 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                    <i class="fas fa-user-circle text-primary me-1"></i> My Profile
                </h5>
                <a href="profile.php" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold" style="font-size: 0.75rem;">
                    Edit
                </a>
            </div>

            <div class="text-center py-3 border-bottom mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold mx-auto d-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px; font-size: 1.6rem;">
                    <?php echo strtoupper(substr($candidate_name, 0, 1)); ?>
                </div>
                <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($candidate_name); ?></h5>
                <div class="text-muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($candidate_email); ?></div>
            </div>

            <div class="mb-3">
                <span class="text-muted d-block" style="font-size: 0.75rem;">QUALIFICATION</span>
                <div class="fw-semibold text-dark" style="font-size: 0.9rem;">
                    <i class="fas fa-graduation-cap text-primary me-1"></i> <?php echo htmlspecialchars($candidate_profile['degree'] ?? 'Not specified'); ?>
                </div>
            </div>

            <div class="mb-3">
                <span class="text-muted d-block" style="font-size: 0.75rem;">SKILLS & STACK</span>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <?php 
                    $skills = !empty($candidate_profile['skills']) ? explode(',', $candidate_profile['skills']) : ['HTML5', 'CSS3', 'JavaScript', 'PHP'];
                    foreach ($skills as $sk): 
                        if (trim($sk)):
                    ?>
                        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem;"><?php echo htmlspecialchars(trim($sk)); ?></span>
                    <?php endif; endforeach; ?>
                </div>
            </div>

            <div class="mt-auto pt-3 border-top d-grid gap-2">
                <a href="../jobs.php" class="btn btn-primary rounded-pill fw-semibold" style="background: var(--primary-gradient); border: none;">
                    <i class="fas fa-briefcase me-1"></i> Explore New Jobs
                </a>
            </div>
        </div>
    </div>

</div>

<!-- Recommended Jobs Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold text-dark mb-1" style="font-size: 1.3rem;">
            <i class="fas fa-bolt text-warning me-2"></i> Recommended Career Openings
        </h3>
        <p class="text-muted mb-0" style="font-size: 0.88rem;">Opportunities curated based on developer roles and student qualifications</p>
    </div>

    <a href="../jobs.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold" style="font-size: 0.85rem;">
        See All Openings <i class="fas fa-arrow-right ms-1"></i>
    </a>
</div>

<div class="row g-3">
    <?php if ($recRes && mysqli_num_rows($recRes) > 0): ?>
        <?php while ($job = mysqli_fetch_assoc($recRes)): ?>
            <div class="col-md-6 col-xl-3">
                <div class="rec-job-card">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.72rem;">
                                <?php echo htmlspecialchars($job['category']); ?>
                            </span>
                            <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">
                                <?php echo htmlspecialchars($job['job_type']); ?>
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1" style="font-size: 1rem;"><?php echo htmlspecialchars($job['title']); ?></h5>
                        <div class="text-muted mb-2" style="font-size: 0.82rem;">
                            <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                        </div>

                        <div class="text-muted mb-2" style="font-size: 0.82rem;">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                        </div>

                        <div class="text-success fw-bold mb-3" style="font-size: 0.85rem;">
                            <i class="fas fa-wallet me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?>
                        </div>
                    </div>

                    <div class="pt-2 border-top d-flex gap-2">
                        <a href="../job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary w-100 rounded-pill fw-semibold" style="background: var(--primary-gradient); border: none;">
                            View & Apply
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
