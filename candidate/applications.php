<?php
require_once __DIR__ . '/auth_check.php';

// Handle Withdraw Application
if (isset($_GET['action']) && $_GET['action'] === 'withdraw') {
    $w_id = (int)($_GET['id'] ?? 0);
    if ($w_id > 0) {
        $wEmailSafe = mysqli_real_escape_string($con, $candidate_email);
        $wStmt = mysqli_prepare($con, "DELETE FROM job_applications WHERE id = ? AND (user_id = ? OR email = ?)");
        if ($wStmt) {
            mysqli_stmt_bind_param($wStmt, "iis", $w_id, $candidate_user_id, $wEmailSafe);
            if (mysqli_stmt_execute($wStmt)) {
                $_SESSION['candidate_success'] = "Application #$w_id has been withdrawn.";
            }
            mysqli_stmt_close($wStmt);
        }
    }
    header('Location: applications.php');
    exit();
}

$page_title = 'My Submitted Applications';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$cand_success = $_SESSION['candidate_success'] ?? '';
unset($_SESSION['candidate_success']);
$cand_error = $_SESSION['candidate_error'] ?? '';
unset($_SESSION['candidate_error']);

$cEmailSafe = mysqli_real_escape_string($con, $candidate_email);
$appsQ = "SELECT ja.*, j.title as job_title, j.company_name, j.location, j.salary_range, j.category, j.company_logo, j.status as job_active_status 
          FROM job_applications ja 
          LEFT JOIN jobs j ON ja.job_id = j.id 
          WHERE ja.user_id = $candidate_user_id OR ja.email = '$cEmailSafe' 
          ORDER BY ja.id DESC";
$appsRes = mysqli_query($con, $appsQ);
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>My Applications 📂</h1>
        <p>Monitor your active hiring pipeline, recruiter reviews, scheduled interview invites, and decisions.</p>
    </div>

    <div>
        <a href="../jobs.php" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: var(--primary-gradient); border: none;">
            <i class="fas fa-search me-1"></i> Browse More Openings
        </a>
    </div>
</div>

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
    .app-card-item {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-subtle);
        padding: 24px;
        margin-bottom: 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .app-card-item:hover {
        box-shadow: var(--shadow-card);
    }

    /* Status Stepper Progress Bar */
    .status-stepper {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin: 24px 0 12px;
    }

    .status-stepper::before {
        content: '';
        position: absolute;
        top: 14px;
        left: 30px;
        right: 30px;
        height: 3px;
        background: #e2e8f0;
        z-index: 1;
    }

    .stepper-step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }

    .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: #64748b;
        margin: 0 auto 6px;
        transition: all 0.3s;
    }

    .stepper-step.completed .step-circle {
        background: var(--success);
        border-color: var(--success);
        color: #ffffff;
    }

    .stepper-step.active .step-circle {
        background: var(--primary);
        border-color: var(--primary);
        color: #ffffff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
    }

    .stepper-step.rejected .step-circle {
        background: var(--danger);
        border-color: var(--danger);
        color: #ffffff;
    }

    .step-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
    }

    .stepper-step.active .step-title,
    .stepper-step.completed .step-title {
        color: var(--text-main);
    }
</style>

<div class="row">
    <div class="col-12">
        <?php if ($appsRes && mysqli_num_rows($appsRes) > 0): ?>
            <?php while ($app = mysqli_fetch_assoc($appsRes)): 
                $status = $app['status'];
                $sClass = 'status-applied';
                
                // Stepper index calculation
                $stepIdx = 1; // Applied
                if ($status === 'Under Review') $stepIdx = 2;
                if ($status === 'Shortlisted') $stepIdx = 3;
                if ($status === 'Interview Scheduled') $stepIdx = 3;
                if ($status === 'Selected') $stepIdx = 4;
                if ($status === 'Rejected') $stepIdx = -1;

                switch ($status) {
                    case 'Under Review': $sClass = 'status-review'; break;
                    case 'Shortlisted': $sClass = 'status-shortlisted'; break;
                    case 'Interview Scheduled': $sClass = 'status-interview'; break;
                    case 'Selected': $sClass = 'status-selected'; break;
                    case 'Rejected': $sClass = 'status-rejected'; break;
                }
            ?>
                <div class="app-card-item">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-light border p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i class="<?php echo !empty($app['company_logo']) ? htmlspecialchars($app['company_logo']) : 'fas fa-briefcase text-primary'; ?> fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                                    <?php echo htmlspecialchars($app['jobpost']); ?>
                                </h4>
                                <div class="text-muted" style="font-size: 0.85rem;">
                                    <i class="fas fa-building me-1"></i> <?php echo !empty($app['company_name']) ? htmlspecialchars($app['company_name']) : 'Recruiting Company'; ?> • 
                                    <i class="fas fa-map-marker-alt ms-2 me-1 text-danger"></i> <?php echo !empty($app['location']) ? htmlspecialchars($app['location']) : 'Remote / Hybrid'; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-status <?php echo $sClass; ?>">
                                <i class="fas fa-circle" style="font-size: 0.45rem;"></i>
                                <?php echo htmlspecialchars($status); ?>
                            </span>

                            <a href="applications.php?action=withdraw&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size: 0.78rem;" onclick="return confirm('Withdraw application for <?php echo htmlspecialchars(addslashes($app['jobpost'])); ?>?');">
                                Withdraw
                            </a>
                        </div>
                    </div>

                    <!-- Visual Step Progression -->
                    <?php if ($status !== 'Rejected'): ?>
                        <div class="status-stepper">
                            <div class="stepper-step <?php echo ($stepIdx > 1) ? 'completed' : (($stepIdx === 1) ? 'active' : ''); ?>">
                                <div class="step-circle"><i class="fas fa-check"></i></div>
                                <div class="step-title">Submitted</div>
                            </div>
                            <div class="stepper-step <?php echo ($stepIdx > 2) ? 'completed' : (($stepIdx === 2) ? 'active' : ''); ?>">
                                <div class="step-circle"><i class="fas <?php echo ($stepIdx > 2) ? 'fa-check' : 'fa-hourglass-half'; ?>"></i></div>
                                <div class="step-title">Under Review</div>
                            </div>
                            <div class="stepper-step <?php echo ($stepIdx > 3) ? 'completed' : (($stepIdx === 3) ? 'active' : ''); ?>">
                                <div class="step-circle"><i class="fas <?php echo ($stepIdx > 3) ? 'fa-check' : 'fa-calendar-check'; ?>"></i></div>
                                <div class="step-title">Shortlisted / Interview</div>
                            </div>
                            <div class="stepper-step <?php echo ($stepIdx === 4) ? 'completed' : ''; ?>">
                                <div class="step-circle"><i class="fas fa-award"></i></div>
                                <div class="step-title">Offer Extended</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-4 py-2 px-3 my-3" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i> Application closed. The recruiting team has decided to proceed with other candidates.
                        </div>
                    <?php endif; ?>

                    <!-- Recruiter Note if present -->
                    <?php if (!empty($app['admin_notes'])): ?>
                        <div class="bg-light rounded-3 p-3 mt-3 border" style="font-size: 0.85rem;">
                            <strong class="text-primary"><i class="fas fa-comments me-1"></i> Recruiter Feedback:</strong>
                            <p class="mb-0 mt-1 text-dark"><?php echo htmlspecialchars($app['admin_notes']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" style="font-size: 0.82rem;">
                        <span class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i> Submitted on <?php echo date('F d, Y - h:i A', strtotime($app['applied_at'])); ?>
                        </span>
                        <?php if (!empty($app['job_id'])): ?>
                            <a href="../job_details.php?id=<?php echo $app['job_id']; ?>" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                View Job Posting <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card border-0 rounded-4 shadow-sm text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                <h4 class="text-muted">No Applications Found</h4>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">You haven't submitted any job applications yet.</p>
                <div>
                    <a href="../jobs.php" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-search me-1"></i> Explore Available Jobs
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
