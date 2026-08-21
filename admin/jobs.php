<?php
$page_title = 'Job Postings Management';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$admin_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);
$admin_error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);

// Filter & search
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');

$where = [];
if (!empty($search)) {
    $sSafe = mysqli_real_escape_string($con, $search);
    $where[] = "(j.title LIKE '%$sSafe%' OR j.company_name LIKE '%$sSafe%' OR j.location LIKE '%$sSafe%')";
}

if (!empty($category)) {
    $cSafe = mysqli_real_escape_string($con, $category);
    $where[] = "j.category = '$cSafe'";
}

if (!empty($status)) {
    $stSafe = mysqli_real_escape_string($con, $status);
    $where[] = "j.status = '$stSafe'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$jobsQuery = "SELECT j.*, COUNT(ja.id) as applicant_count 
              FROM jobs j 
              LEFT JOIN job_applications ja ON j.id = ja.job_id 
              $whereClause 
              GROUP BY j.id 
              ORDER BY j.id DESC";
$jobsResult = mysqli_query($con, $jobsQuery);

$catRes = mysqli_query($con, "SELECT DISTINCT category FROM jobs ORDER BY category ASC");
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Job Management 💼</h1>
        <p>Create, update, monitor applicant volumes, and manage employment postings across categories.</p>
    </div>

    <div>
        <a href="job_add.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
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

<!-- Filter Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="jobs.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search job title, company, or city..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Job Categories</option>
                    <?php if ($catRes): ?>
                        <?php while ($c = mysqli_fetch_assoc($catRes)): ?>
                            <option value="<?php echo htmlspecialchars($c['category']); ?>" <?php echo ($category === $c['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Active" <?php echo ($status === 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Closed" <?php echo ($status === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                    <option value="Draft" <?php echo ($status === 'Draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold" style="font-size: 0.88rem;">Filter</button>
                <?php if (!empty($search) || !empty($category) || !empty($status)): ?>
                    <a href="jobs.php" class="btn btn-outline-secondary rounded-3" title="Clear Filters">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Jobs Table Card -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
            <i class="fas fa-briefcase text-primary me-2"></i> Job Listings
        </h5>
        <span class="text-muted" style="font-size: 0.85rem;">
            Total <?php echo $jobsResult ? mysqli_num_rows($jobsResult) : 0; ?> postings
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase text-muted" style="font-size: 0.76rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-4">Position & Company</th>
                    <th>Category & Type</th>
                    <th>Location & Salary</th>
                    <th>Openings</th>
                    <th>Applicants</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($jobsResult && mysqli_num_rows($jobsResult) > 0): ?>
                    <?php while ($job = mysqli_fetch_assoc($jobsResult)): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-light border p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="<?php echo !empty($job['company_logo']) ? htmlspecialchars($job['company_logo']) : 'fas fa-building text-primary'; ?> fa-lg"></i>
                                    </div>
                                    <div>
                                        <a href="../job_details.php?id=<?php echo $job['id']; ?>" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                            <i class="fas fa-arrow-up-right-from-square text-muted ms-1" style="font-size: 0.75rem;"></i>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.82rem;">
                                            <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1 d-inline-block">
                                    <?php echo htmlspecialchars($job['category']); ?>
                                </span>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($job['job_type']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                                </div>
                                <div class="text-success fw-bold" style="font-size: 0.82rem;">
                                    <?php echo htmlspecialchars($job['salary_range']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?php echo (int)$job['openings']; ?> Positions
                                </span>
                            </td>
                            <td>
                                <a href="applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold text-primary">
                                    <i class="fas fa-users me-1"></i> <?php echo (int)$job['applicant_count']; ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($job['status'] === 'Active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="fas fa-circle me-1" style="font-size: 0.45rem;"></i> Active
                                    </span>
                                <?php elseif ($job['status'] === 'Closed'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                        <i class="fas fa-circle me-1" style="font-size: 0.45rem;"></i> Closed
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                        <i class="fas fa-circle me-1" style="font-size: 0.45rem;"></i> Draft
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="job_edit.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-light border text-secondary" title="Edit Job">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="job_delete.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-light border text-danger" title="Delete Job" onclick="return confirm('Are you sure you want to delete this job posting?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-briefcase fa-3x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted">No job postings found</h5>
                            <p class="text-muted mb-3" style="font-size: 0.88rem;">Start by creating a new job opening.</p>
                            <a href="job_add.php" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                <i class="fas fa-plus me-1"></i> Post New Job
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
