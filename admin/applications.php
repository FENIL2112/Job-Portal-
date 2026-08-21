<?php
require_once __DIR__ . '/auth_check.php';

// Handle Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = (int)($_POST['app_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    $allowedStatuses = ['Applied', 'Under Review', 'Shortlisted', 'Interview Scheduled', 'Selected', 'Rejected'];

    if ($app_id > 0 && in_array($new_status, $allowedStatuses)) {
        $upStmt = mysqli_prepare($con, "UPDATE job_applications SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
        if ($upStmt) {
            mysqli_stmt_bind_param($upStmt, "ssi", $new_status, $admin_notes, $app_id);
            if (mysqli_stmt_execute($upStmt)) {
                $_SESSION['admin_success'] = "Application #$app_id status updated to <strong>$new_status</strong>.";
            } else {
                $_SESSION['admin_error'] = "Failed to update application status.";
            }
            mysqli_stmt_close($upStmt);
        }
    }
    header('Location: applications.php');
    exit();
}

// Handle Delete Application
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_id = (int)($_GET['id'] ?? 0);
    if ($del_id > 0) {
        $delStmt = mysqli_prepare($con, "DELETE FROM job_applications WHERE id = ?");
        if ($delStmt) {
            mysqli_stmt_bind_param($delStmt, "i", $del_id);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);
            $_SESSION['admin_success'] = "Application record #$del_id deleted.";
        }
    }
    header('Location: applications.php');
    exit();
}

$page_title = 'Application Tracking System (ATS)';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$admin_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);
$admin_error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);

// Filters
$search = trim($_GET['search'] ?? '');
$job_filter = (int)($_GET['job_id'] ?? 0);
$status_filter = trim($_GET['status'] ?? '');

$where = [];
if (!empty($search)) {
    $sSafe = mysqli_real_escape_string($con, $search);
    $where[] = "(ja.name LIKE '%$sSafe%' OR ja.email LIKE '%$sSafe%' OR ja.mobile LIKE '%$sSafe%' OR ja.jobpost LIKE '%$sSafe%')";
}

if ($job_filter > 0) {
    $where[] = "ja.job_id = $job_filter";
}

if (!empty($status_filter)) {
    $stSafe = mysqli_real_escape_string($con, $status_filter);
    $where[] = "ja.status = '$stSafe'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$appsQuery = "SELECT ja.*, j.title as job_title, j.company_name, cp.resume_url, cp.portfolio_url, cp.github_url 
              FROM job_applications ja 
              LEFT JOIN jobs j ON ja.job_id = j.id 
              LEFT JOIN candidate_profiles cp ON ja.user_id = cp.user_id 
              $whereClause 
              ORDER BY ja.id DESC";
$appsResult = mysqli_query($con, $appsQuery);

// Fetch job list for filter dropdown
$jobsListRes = mysqli_query($con, "SELECT id, title, company_name FROM jobs ORDER BY title ASC");
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Application Tracking System 📥</h1>
        <p>Review incoming candidate applications, schedule interviews, update hiring status, and add recruiter notes.</p>
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

<!-- Filter Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="applications.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search applicant name, email, role..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="job_id" class="form-select" onchange="this.form.submit()">
                    <option value="0">All Jobs</option>
                    <?php if ($jobsListRes): ?>
                        <?php while ($jl = mysqli_fetch_assoc($jobsListRes)): ?>
                            <option value="<?php echo $jl['id']; ?>" <?php echo ($job_filter === (int)$jl['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jl['title'] . ' (' . $jl['company_name'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Applied" <?php echo ($status_filter === 'Applied') ? 'selected' : ''; ?>>Applied</option>
                    <option value="Under Review" <?php echo ($status_filter === 'Under Review') ? 'selected' : ''; ?>>Under Review</option>
                    <option value="Shortlisted" <?php echo ($status_filter === 'Shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                    <option value="Interview Scheduled" <?php echo ($status_filter === 'Interview Scheduled') ? 'selected' : ''; ?>>Interview Scheduled</option>
                    <option value="Selected" <?php echo ($status_filter === 'Selected') ? 'selected' : ''; ?>>Selected</option>
                    <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold" style="font-size: 0.88rem;">Filter</button>
                <?php if (!empty($search) || $job_filter > 0 || !empty($status_filter)): ?>
                    <a href="applications.php" class="btn btn-outline-secondary rounded-3" title="Clear Filters">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Applications Table Card -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
            <i class="fas fa-inbox text-primary me-2"></i> Application Submissions
        </h5>
        <span class="text-muted" style="font-size: 0.85rem;">
            Total <?php echo $appsResult ? mysqli_num_rows($appsResult) : 0; ?> applications
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase text-muted" style="font-size: 0.76rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-4">Candidate</th>
                    <th>Job Position</th>
                    <th>Qualification & Contact</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appsResult && mysqli_num_rows($appsResult) > 0): ?>
                    <?php while ($app = mysqli_fetch_assoc($appsResult)): 
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
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?php echo strtoupper(substr($app['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['name']); ?></div>
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($app['email']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($app['jobpost']); ?></div>
                                <?php if (!empty($app['company_name'])): ?>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($app['company_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-light text-secondary border px-2 py-1 mb-1">
                                        <?php echo htmlspecialchars($app['degree']); ?>
                                    </span>
                                </div>
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    <i class="fas fa-phone-alt me-1"></i> <?php echo htmlspecialchars($app['mobile']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.82rem;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status <?php echo $sClass; ?>">
                                    <i class="fas fa-circle" style="font-size: 0.45rem;"></i>
                                    <?php echo htmlspecialchars($app['status']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold btn-manage-app" style="font-size: 0.8rem;" 
                                        data-app="<?php echo htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8'); ?>"
                                        onclick="openStatusModal(this)">
                                    <i class="fas fa-tasks me-1"></i> Manage
                                </button>
                                <a href="applications.php?action=delete&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-light border text-danger" title="Delete" onclick="return confirm('Delete this application?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                            <h5>No applications matching criteria</h5>
                            <p style="font-size: 0.88rem;">Try clearing your active filters or check back later.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Status & ATS Review Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="applications.php">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold" id="statusModalLabel">
                        <i class="fas fa-user-check text-primary me-2"></i> Application Review & ATS Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" name="app_id" id="modAppId">
                    <input type="hidden" name="update_status" value="1">

                    <!-- Candidate Summary Strip -->
                    <div class="card bg-light border-0 rounded-4 p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">CANDIDATE NAME</span>
                                <strong class="fs-6 text-dark" id="modName">-</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">APPLIED POSITION</span>
                                <strong class="fs-6 text-primary" id="modRole">-</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">EMAIL & PHONE</span>
                                <div id="modEmail">-</div>
                                <div id="modPhone">-</div>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">QUALIFICATION & SKILLS</span>
                                <div id="modDegree">-</div>
                                <div id="modSkills" class="text-muted" style="font-size: 0.8rem;">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Change Status Dropdown -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Update Hiring Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="modStatus" required>
                            <option value="Applied">🟡 Applied (New Submission)</option>
                            <option value="Under Review">🔵 Under Review (Profile Evaluation)</option>
                            <option value="Shortlisted">🟣 Shortlisted (Profile Qualified)</option>
                            <option value="Interview Scheduled">🟠 Interview Scheduled (Invitation Sent)</option>
                            <option value="Selected">🟢 Selected / Offer Extended</option>
                            <option value="Rejected">🔴 Rejected / Not Selected</option>
                        </select>
                    </div>

                    <!-- Admin Feedback Notes -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Internal Recruiter Notes / Interview Feedback</label>
                        <textarea class="form-control" name="admin_notes" id="modNotes" rows="3" placeholder="e.g. Cleared 1st round technical test. Schedule system design interview on Friday at 3 PM..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-save me-1"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openStatusModal(btnOrData) {
    let data;
    try {
        if (btnOrData instanceof HTMLElement) {
            data = JSON.parse(btnOrData.getAttribute('data-app'));
        } else if (typeof btnOrData === 'string') {
            data = JSON.parse(btnOrData);
        } else {
            data = btnOrData;
        }
    } catch (e) {
        console.error('Error parsing application data:', e);
        return;
    }

    if (!data) return;

    document.getElementById('modAppId').value = data.id || '';
    document.getElementById('modName').textContent = data.name || '-';
    document.getElementById('modRole').textContent = data.jobpost || '-';
    document.getElementById('modEmail').textContent = data.email || '-';
    document.getElementById('modPhone').textContent = data.mobile || '-';
    document.getElementById('modDegree').textContent = data.degree || '-';
    document.getElementById('modSkills').textContent = data.skills ? data.skills : 'No explicit skills tags provided';
    document.getElementById('modStatus').value = data.status || 'Applied';
    document.getElementById('modNotes').value = data.admin_notes ? data.admin_notes : '';

    const modalEl = document.getElementById('statusModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
