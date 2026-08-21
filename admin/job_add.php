<?php
require_once __DIR__ . '/auth_check.php';

$form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $title = trim($_POST['title'] ?? '');
    $company_id = (int)($_POST['company_id'] ?? 0);
    $company_name = trim($_POST['company_name'] ?? '');
    $company_logo = trim($_POST['company_logo'] ?? 'fas fa-building');
    $category = trim($_POST['category'] ?? '');
    $job_type = trim($_POST['job_type'] ?? 'Full-Time');
    $experience_level = trim($_POST['experience_level'] ?? 'Fresher');
    $location = trim($_POST['location'] ?? 'Remote');
    $salary_range = trim($_POST['salary_range'] ?? '');
    $openings = (int)($_POST['openings'] ?? 1);
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $status = trim($_POST['status'] ?? 'Active');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');

    // If company selected from dropdown, fill company_name & logo
    if ($company_id > 0) {
        $cQ = mysqli_query($con, "SELECT name, logo FROM companies WHERE id = $company_id");
        if ($cRow = mysqli_fetch_assoc($cQ)) {
            $company_name = $cRow['name'];
            $company_logo = $cRow['logo'] ?: $company_logo;
        }
    }

    $errors = [];
    if (empty($title)) $errors[] = "Job title is required.";
    if (empty($company_name)) $errors[] = "Company name is required.";
    if (empty($category)) $errors[] = "Category is required.";
    if (empty($salary_range)) $errors[] = "Salary range is required.";
    if (empty($description)) $errors[] = "Job description is required.";
    if (empty($requirements)) $errors[] = "Job requirements are required.";

    if (empty($errors)) {
        $insertQuery = "INSERT INTO jobs 
            (company_id, company_name, company_logo, title, category, job_type, experience_level, location, salary_range, openings, deadline, status, description, requirements, benefits, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($con, $insertQuery);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssssssisssss", 
                $company_id, $company_name, $company_logo, $title, $category, $job_type, $experience_level, $location, $salary_range, $openings, $deadline, $status, $description, $requirements, $benefits
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['admin_success'] = "Job posting <strong>" . htmlspecialchars($title) . "</strong> created successfully!";
                header('Location: jobs.php');
                exit();
            } else {
                $form_error = "Database execution error: " . mysqli_error($con);
            }
            mysqli_stmt_close($stmt);
        } else {
            $form_error = "Database preparation error.";
        }
    } else {
        $form_error = implode(' ', $errors);
    }
}

// Retrieve company list for dropdown
$companiesRes = mysqli_query($con, "SELECT id, name, logo FROM companies ORDER BY name ASC");

$page_title = 'Post New Job';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Post New Job Opening 📝</h1>
        <p>Publish a new career opportunity for student and candidate applications.</p>
    </div>

    <div>
        <a href="jobs.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" style="font-size: 0.88rem;">
            <i class="fas fa-arrow-left me-1"></i> Back to Jobs
        </a>
    </div>
</div>

<?php if (!empty($form_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $form_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="job_add.php">
            <div class="row g-4">
                
                <!-- Job Title -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Job Title <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-heading text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="title" placeholder="e.g. Senior Full Stack Engineer" required autofocus>
                    </div>
                </div>

                <!-- Company Select / Input -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Hiring Company <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-building text-muted"></i></span>
                        <select class="form-select border-start-0" name="company_id" id="companySelect">
                            <option value="0">Custom / Enter Name Manually</option>
                            <?php if ($companiesRes): ?>
                                <?php while ($comp = mysqli_fetch_assoc($companiesRes)): ?>
                                    <option value="<?php echo $comp['id']; ?>">
                                        <?php echo htmlspecialchars($comp['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                        <input type="text" class="form-control" name="company_name" id="customCompanyName" placeholder="or enter company name">
                    </div>
                </div>

                <!-- Category -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Job Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="category" required>
                        <option value="">Select Category *</option>
                        <option value="Web Development" selected>Web Development</option>
                        <option value="Frontend Development">Frontend Development</option>
                        <option value="Backend Development">Backend Development</option>
                        <option value="UI/UX Design">UI/UX Design</option>
                        <option value="DevOps & Cloud">DevOps & Cloud</option>
                        <option value="Data Science & AI">Data Science & AI</option>
                        <option value="Mobile App Development">Mobile App Development</option>
                        <option value="Quality Assurance">Quality Assurance</option>
                        <option value="Operations & Management">Operations & Management</option>
                    </select>
                </div>

                <!-- Job Type -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Employment Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="job_type" required>
                        <option value="Full-Time" selected>Full-Time</option>
                        <option value="Part-Time">Part-Time</option>
                        <option value="Remote">Remote</option>
                        <option value="Internship">Internship</option>
                        <option value="Contract">Contract</option>
                    </select>
                </div>

                <!-- Experience Level -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Experience Required <span class="text-danger">*</span></label>
                    <select class="form-select" name="experience_level" required>
                        <option value="Fresher / Entry Level" selected>Fresher / Entry Level</option>
                        <option value="1-3 Years">1-3 Years</option>
                        <option value="3-5 Years">3-5 Years</option>
                        <option value="5+ Years">5+ Years</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Location <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="location" placeholder="e.g. Bengaluru / Remote" required>
                </div>

                <!-- Salary Range -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Salary / CTC Range <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="salary_range" placeholder="e.g. ₹6,00,000 - ₹10,00,000 PA" required>
                </div>

                <!-- Openings & Deadline -->
                <div class="col-md-2">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Openings Count</label>
                    <input type="number" class="form-control" name="openings" value="2" min="1">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Application Deadline</label>
                    <input type="date" class="form-control" name="deadline" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                </div>

                <!-- Status -->
                <div class="col-md-12">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Publication Status</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="stActive" value="Active" checked>
                            <label class="form-check-label fw-semibold text-success" for="stActive">Active (Visible to Candidates)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="stDraft" value="Draft">
                            <label class="form-check-label fw-semibold text-secondary" for="stDraft">Draft (Hidden)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="stClosed" value="Closed">
                            <label class="form-check-label fw-semibold text-danger" for="stClosed">Closed</label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-md-12">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Job Overview & Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Describe the role, team context, and everyday responsibilities..." required></textarea>
                </div>

                <!-- Requirements -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Requirements & Technical Skills <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="requirements" rows="4" placeholder="• Proficiency in PHP, JS, MySQL...&#10;• Experience with Git and REST APIs..." required></textarea>
                </div>

                <!-- Benefits -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Perks & Benefits (Optional)</label>
                    <textarea class="form-control" name="benefits" rows="4" placeholder="• Health Insurance&#10;• Hybrid / Remote Flexibility&#10;• Annual learning stipend..."></textarea>
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="jobs.php" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
                    <button type="submit" name="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-check-circle me-1"></i> Publish Job Opening
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
