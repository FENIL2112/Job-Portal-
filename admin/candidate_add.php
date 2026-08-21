<?php
require_once __DIR__ . '/auth_check.php';

$form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $refer = trim($_POST['refer'] ?? '');
    $position = trim($_POST['job_position'] ?? '');

    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Please enter a valid full name.";
    }

    if (empty($qualification)) {
        $errors[] = "Qualification is required.";
    }

    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Please enter a valid 10-digit mobile number.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($position)) {
        $errors[] = "Please select a job position.";
    }

    if (empty($errors)) {
        $check_query = "SELECT id FROM jobregistration WHERE email = ?";
        $check_stmt = mysqli_prepare($con, $check_query);
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $form_error = "This email is already registered in the candidate directory.";
            } else {
                $insertquery = "INSERT INTO jobregistration (name, degree, mobile, email, refer, jobpost) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($con, $insertquery);
                if ($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "ssssss", $name, $qualification, $mobile, $email, $refer, $position);
                    if (mysqli_stmt_execute($insert_stmt)) {
                        mysqli_stmt_close($insert_stmt);

                        // Also record in job_applications ATS
                        $stmtATS = mysqli_prepare($con, "INSERT INTO job_applications (name, email, mobile, degree, refer, jobpost, status, applied_at) VALUES (?, ?, ?, ?, ?, ?, 'Applied', NOW())");
                        if ($stmtATS) {
                            mysqli_stmt_bind_param($stmtATS, "ssssss", $name, $email, $mobile, $qualification, $refer, $position);
                            mysqli_stmt_execute($stmtATS);
                            mysqli_stmt_close($stmtATS);
                        }

                        $_SESSION['admin_success'] = "Candidate <strong>" . htmlspecialchars($name) . "</strong> registered successfully!";
                        header('Location: candidates.php');
                        exit();
                    } else {
                        $form_error = "Failed to insert candidate. Please try again.";
                    }
                    mysqli_stmt_close($insert_stmt);
                } else {
                    $form_error = "Database preparation error.";
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    } else {
        $form_error = implode(' ', $errors);
    }
}

$page_title = 'Add New Candidate';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Add New Candidate 👤</h1>
        <p>Manually enroll a candidate profile into the database.</p>
    </div>

    <div>
        <a href="candidates.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" style="font-size: 0.88rem;">
            <i class="fas fa-arrow-left me-1"></i> Back to Candidate List
        </a>
    </div>
</div>

<?php if (!empty($form_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $form_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="candidate_add.php">
            <div class="row g-4">
                
                <!-- Full Name -->
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="name" name="name" placeholder="e.g. John Doe" required autofocus>
                    </div>
                </div>

                <!-- Degree / Qualification -->
                <div class="col-md-6">
                    <label for="qualification" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Qualification / Degree <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-graduation-cap text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="qualification" name="qualification" placeholder="e.g. B.Tech / MCA / BCA" required>
                    </div>
                </div>

                <!-- Mobile -->
                <div class="col-md-6">
                    <label for="mobile" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Mobile Number (10 Digits) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                        <input type="tel" class="form-control border-start-0" id="mobile" name="mobile" pattern="[0-9]{10}" placeholder="9876543210" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="email" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="candidate@example.com" required>
                    </div>
                </div>

                <!-- Reference -->
                <div class="col-md-6">
                    <label for="refer" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Reference Source (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-link text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="refer" name="refer" placeholder="e.g. LinkedIn / Recruiter Name">
                    </div>
                </div>

                <!-- Position -->
                <div class="col-md-6">
                    <label for="job_position" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Applied Position <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-briefcase text-muted"></i></span>
                        <select class="form-select border-start-0" id="job_position" name="job_position" required>
                            <option value="">Select Position *</option>
                            <option value="Web Developer" selected>🌐 Web Developer</option>
                            <option value="UI/UX Designer">🎨 UI/UX Designer</option>
                            <option value="Frontend Developer">⚡ Frontend Developer</option>
                            <option value="Backend Developer">🔧 Backend Developer</option>
                            <option value="Operations Manager">⚙️ Operations Manager</option>
                            <option value="Full Stack Developer">🚀 Full Stack Developer</option>
                            <option value="DevOps & Cloud Engineer">☁️ DevOps & Cloud Engineer</option>
                            <option value="Other">❓ Other</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="candidates.php" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
                    <button type="submit" name="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-save me-1"></i> Save Candidate Record
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
