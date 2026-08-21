<?php
require_once __DIR__ . '/auth_check.php';

// Handle Remove Bookmark
if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $r_job_id = (int)($_GET['id'] ?? 0);
    if ($r_job_id > 0) {
        $delB = mysqli_prepare($con, "DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        if ($delB) {
            mysqli_stmt_bind_param($delB, "ii", $candidate_user_id, $r_job_id);
            mysqli_stmt_execute($delB);
            mysqli_stmt_close($delB);
            $_SESSION['candidate_success'] = "Job removed from your saved list.";
        }
    }
    header('Location: saved_jobs.php');
    exit();
}

$page_title = 'My Saved / Bookmarked Jobs';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$cand_success = $_SESSION['candidate_success'] ?? '';
unset($_SESSION['candidate_success']);
$cand_error = $_SESSION['candidate_error'] ?? '';
unset($_SESSION['candidate_error']);

$savedQ = "SELECT j.*, sj.created_at as saved_at 
           FROM saved_jobs sj 
           JOIN jobs j ON sj.job_id = j.id 
           WHERE sj.user_id = $candidate_user_id 
           ORDER BY sj.id DESC";
$savedRes = mysqli_query($con, $savedQ);
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Saved Jobs 📌</h1>
        <p>Positions you have bookmarked for review and future application submission.</p>
    </div>

    <div>
        <a href="../jobs.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold" style="font-size: 0.88rem;">
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

<div class="row g-3">
    <?php if ($savedRes && mysqli_num_rows($savedRes) > 0): ?>
        <?php while ($job = mysqli_fetch_assoc($savedRes)): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 rounded-4 shadow-sm h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.75rem;">
                                <?php echo htmlspecialchars($job['category']); ?>
                            </span>
                            <a href="saved_jobs.php?action=remove&id=<?php echo $job['id']; ?>" class="text-danger p-1" title="Remove Bookmark">
                                <i class="fas fa-bookmark"></i>
                            </a>
                        </div>

                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.1rem;"><?php echo htmlspecialchars($job['title']); ?></h4>
                        <div class="text-muted mb-2" style="font-size: 0.85rem;">
                            <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                        </div>

                        <div class="text-secondary mb-2" style="font-size: 0.82rem;">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($job['location']); ?> •
                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($job['job_type']); ?></span>
                        </div>

                        <div class="text-success fw-bold mb-3" style="font-size: 0.9rem;">
                            <i class="fas fa-wallet me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?>
                        </div>
                    </div>

                    <div class="pt-3 border-top d-flex gap-2">
                        <a href="../job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary w-100 rounded-pill fw-semibold" style="background: var(--primary-gradient); border: none;">
                            Apply Now
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm text-center py-5">
                <i class="fas fa-bookmark fa-3x text-muted mb-3 opacity-50"></i>
                <h4 class="text-muted">No Saved Jobs Yet</h4>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">Click the bookmark icon on any job card to save it here for later.</p>
                <div>
                    <a href="../jobs.php" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-search me-1"></i> Browse Open Positions
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
