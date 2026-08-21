<?php
require_once __DIR__ . '/auth_check.php';

// Handle Add Company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = trim($_POST['name'] ?? '');
    $industry = trim($_POST['industry'] ?? 'Information Technology');
    $location = trim($_POST['location'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $logo = trim($_POST['logo'] ?? 'fas fa-building');
    $about = trim($_POST['about'] ?? '');

    if (!empty($name)) {
        $stmt = mysqli_prepare($con, "INSERT INTO companies (name, industry, location, website, email, logo, about) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssss", $name, $industry, $location, $website, $email, $logo, $about);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['admin_success'] = "Company <strong>" . htmlspecialchars($name) . "</strong> added successfully!";
            } else {
                $_SESSION['admin_error'] = "Failed to add company.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $_SESSION['admin_error'] = "Company name is required.";
    }
    header('Location: companies.php');
    exit();
}

// Handle Delete Company
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_id = (int)($_GET['id'] ?? 0);
    if ($del_id > 0) {
        $delStmt = mysqli_prepare($con, "DELETE FROM companies WHERE id = ?");
        if ($delStmt) {
            mysqli_stmt_bind_param($delStmt, "i", $del_id);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);
            $_SESSION['admin_success'] = "Company record removed.";
        }
    }
    header('Location: companies.php');
    exit();
}

$page_title = 'Company Directory Management';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$admin_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);
$admin_error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);

// Fetch companies with open job count
$companiesQ = "SELECT c.*, COUNT(j.id) as job_count 
               FROM companies c 
               LEFT JOIN jobs j ON c.id = j.company_id 
               GROUP BY c.id 
               ORDER BY c.name ASC";
$companiesRes = mysqli_query($con, $companiesQ);
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Company Directory 🏢</h1>
        <p>Manage employer partner organizations, corporate profiles, and active hiring posts.</p>
    </div>

    <div>
        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#addCompanyModal" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
            <i class="fas fa-plus-circle me-1"></i> Add New Company
        </button>
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

<!-- Company Grid -->
<div class="row g-4">
    <?php if ($companiesRes && mysqli_num_rows($companiesRes) > 0): ?>
        <?php while ($c = mysqli_fetch_assoc($companiesRes)): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 rounded-4 shadow-sm h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="rounded-3 bg-light border p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="<?php echo !empty($c['logo']) ? htmlspecialchars($c['logo']) : 'fas fa-building'; ?> fa-2x text-primary"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li>
                                        <a class="dropdown-item text-danger" href="companies.php?action=delete&id=<?php echo $c['id']; ?>" onclick="return confirm('Delete this company?');">
                                            <i class="fas fa-trash-alt me-2"></i> Delete Company
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;"><?php echo htmlspecialchars($c['name']); ?></h4>
                        <div class="badge bg-primary bg-opacity-10 text-primary mb-2"><?php echo htmlspecialchars($c['industry']); ?></div>
                        
                        <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                            <?php echo !empty($c['about']) ? htmlspecialchars(substr($c['about'], 0, 120)) . '...' : 'Leading tech employer on JobPortal platform.'; ?>
                        </p>

                        <div class="text-secondary mb-1" style="font-size: 0.82rem;">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($c['location']); ?>
                        </div>

                        <?php if (!empty($c['website'])): ?>
                            <div class="text-muted mb-2" style="font-size: 0.82rem;">
                                <a href="<?php echo htmlspecialchars($c['website']); ?>" target="_blank" class="text-decoration-none text-primary">
                                    <i class="fas fa-globe me-1"></i> <?php echo htmlspecialchars($c['website']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
                        <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold">
                            <i class="fas fa-briefcase me-1 text-primary"></i> <?php echo (int)$c['job_count']; ?> Openings
                        </span>
                        <a href="jobs.php?search=<?php echo urlencode($c['name']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                            View Jobs
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-building fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>No companies registered yet</h5>
            <p>Click "Add New Company" to enroll top employers.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add Company Modal -->
<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="companies.php">
                <input type="hidden" name="add_company" value="1">

                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold" id="addCompanyModalLabel">
                        <i class="fas fa-building text-primary me-2"></i> Add Hiring Company
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Adobe India" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Industry / Sector</label>
                        <input type="text" class="form-control" name="industry" placeholder="e.g. Fintech / SaaS / E-Commerce" value="Information Technology">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Headquarters Location</label>
                        <input type="text" class="form-control" name="location" placeholder="e.g. Bengaluru, Karnataka">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Website URL</label>
                        <input type="url" class="form-control" name="website" placeholder="https://company.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">FontAwesome Icon Class (Logo)</label>
                        <input type="text" class="form-control" name="logo" placeholder="e.g. fa-brands fa-google or fas fa-building" value="fas fa-building">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">About / Company Bio</label>
                        <textarea class="form-control" name="about" rows="3" placeholder="Brief company summary and mission..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-save me-1"></i> Save Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
