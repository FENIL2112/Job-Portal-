<?php
require_once __DIR__ . '/auth_check.php';

$form_error = '';
$form_success = '';

// Handle Profile Update Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $degree = trim($_POST['degree'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $graduation_year = trim($_POST['graduation_year'] ?? '');
    $experience_level = trim($_POST['experience_level'] ?? 'Fresher');
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $portfolio_url = trim($_POST['portfolio_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $linkedin_url = trim($_POST['linkedin_url'] ?? '');
    $resume_url = trim($_POST['resume_url'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');

    if (empty($name)) {
        $form_error = 'Your full name is required.';
    } else {
        // Update user name in users table
        $uStmt = mysqli_prepare($con, "UPDATE users SET name = ? WHERE id = ?");
        if ($uStmt) {
            mysqli_stmt_bind_param($uStmt, "si", $name, $candidate_user_id);
            mysqli_stmt_execute($uStmt);
            mysqli_stmt_close($uStmt);
            $_SESSION['user_name'] = $name;
            $candidate_name = $name;
        }

        // Check if profile exists
        $pCheck = mysqli_query($con, "SELECT id FROM candidate_profiles WHERE user_id = $candidate_user_id");
        if (mysqli_num_rows($pCheck) > 0) {
            $upSql = "UPDATE candidate_profiles SET 
                headline=?, phone=?, degree=?, institution=?, graduation_year=?, experience_level=?, 
                skills=?, bio=?, resume_url=?, portfolio_url=?, github_url=?, linkedin_url=?, city=?, state=?, updated_at=NOW() 
                WHERE user_id=?";
            $pStmt = mysqli_prepare($con, $upSql);
            if ($pStmt) {
                mysqli_stmt_bind_param($pStmt, "ssssssssssssssi", 
                    $headline, $phone, $degree, $institution, $graduation_year, $experience_level, 
                    $skills, $bio, $resume_url, $portfolio_url, $github_url, $linkedin_url, $city, $state, $candidate_user_id
                );
                if (mysqli_stmt_execute($pStmt)) {
                    $_SESSION['candidate_success'] = 'Profile updated successfully!';
                    header('Location: profile.php');
                    exit();
                } else {
                    $form_error = 'Failed to update profile details.';
                }
                mysqli_stmt_close($pStmt);
            }
        } else {
            $insSql = "INSERT INTO candidate_profiles 
                (user_id, headline, phone, degree, institution, graduation_year, experience_level, skills, bio, resume_url, portfolio_url, github_url, linkedin_url, city, state) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pStmt = mysqli_prepare($con, $insSql);
            if ($pStmt) {
                mysqli_stmt_bind_param($pStmt, "issssssssssssss", 
                    $candidate_user_id, $headline, $phone, $degree, $institution, $graduation_year, $experience_level, 
                    $skills, $bio, $resume_url, $portfolio_url, $github_url, $linkedin_url, $city, $state
                );
                if (mysqli_stmt_execute($pStmt)) {
                    $_SESSION['candidate_success'] = 'Profile initialized and saved successfully!';
                    header('Location: profile.php');
                    exit();
                }
                mysqli_stmt_close($pStmt);
            }
        }
    }
}

// Reload latest profile data
$profQ = mysqli_query($con, "SELECT * FROM candidate_profiles WHERE user_id = $candidate_user_id");
$candidate_profile = mysqli_fetch_assoc($profQ) ?: [];
$cand_success = $_SESSION['candidate_success'] ?? '';
unset($_SESSION['candidate_success']);

$page_title = 'My Profile & Resume Details';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Student / Candidate Profile 🎓</h1>
        <p>Complete your education, technical skills, and resume links for 1-click job applications.</p>
    </div>

    <div>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" style="font-size: 0.88rem;">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if (!empty($cand_success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $cand_success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($form_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $form_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="profile.php">
            <input type="hidden" name="update_profile" value="1">

            <!-- Section 1: Basic Information -->
            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                <i class="fas fa-id-card text-primary me-2"></i> Personal Information
            </h5>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="name" value="<?php echo htmlspecialchars($candidate_name); ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Email Address (Account ID)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0 bg-light" value="<?php echo htmlspecialchars($candidate_email); ?>" readonly disabled>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Contact Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                        <input type="tel" class="form-control border-start-0" name="phone" value="<?php echo htmlspecialchars($candidate_profile['phone'] ?? ''); ?>" placeholder="9876543210">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Professional Headline</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="headline" value="<?php echo htmlspecialchars($candidate_profile['headline'] ?? ''); ?>" placeholder="e.g. Frontend Developer | React & UI Specialist">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">City</label>
                    <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($candidate_profile['city'] ?? ''); ?>" placeholder="e.g. Ahmedabad">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">State / Region</label>
                    <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($candidate_profile['state'] ?? ''); ?>" placeholder="e.g. Gujarat">
                </div>
            </div>

            <!-- Section 2: Education & Qualifications -->
            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                <i class="fas fa-graduation-cap text-primary me-2"></i> Academic Background & Degree
            </h5>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Degree / Highest Qualification <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="degree" value="<?php echo htmlspecialchars($candidate_profile['degree'] ?? ''); ?>" placeholder="e.g. B.Tech Computer Science / MCA / BCA" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">College / University / Institution</label>
                    <input type="text" class="form-control" name="institution" value="<?php echo htmlspecialchars($candidate_profile['institution'] ?? ''); ?>" placeholder="e.g. Gujarat Technological University">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Passing Year / Expected Graduation</label>
                    <input type="text" class="form-control" name="graduation_year" value="<?php echo htmlspecialchars($candidate_profile['graduation_year'] ?? ''); ?>" placeholder="e.g. 2025 / 2026">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Experience Level</label>
                    <select class="form-select" name="experience_level">
                        <option value="Fresher" <?php echo (($candidate_profile['experience_level'] ?? '') === 'Fresher') ? 'selected' : ''; ?>>Fresher / College Graduate</option>
                        <option value="1-2 Years" <?php echo (($candidate_profile['experience_level'] ?? '') === '1-2 Years') ? 'selected' : ''; ?>>1-2 Years</option>
                        <option value="3+ Years" <?php echo (($candidate_profile['experience_level'] ?? '') === '3+ Years') ? 'selected' : ''; ?>>3+ Years</option>
                    </select>
                </div>
            </div>

            <!-- Section 3: Technical Skills & Profile Links -->
            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                <i class="fas fa-laptop-code text-primary me-2"></i> Skills, Portfolio & Resume
            </h5>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Skills & Technical Stack (Comma Separated)</label>
                    <input type="text" class="form-control" name="skills" value="<?php echo htmlspecialchars($candidate_profile['skills'] ?? ''); ?>" placeholder="e.g. PHP, MySQL, JavaScript, React, HTML5, CSS3, Bootstrap, Git, Figma">
                    <div class="form-text text-muted">Enter technical skills separated by commas so employers can discover your profile.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">LinkedIn Profile URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fab fa-linkedin text-primary"></i></span>
                        <input type="url" class="form-control border-start-0" name="linkedin_url" value="<?php echo htmlspecialchars($candidate_profile['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">GitHub Profile URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fab fa-github text-dark"></i></span>
                        <input type="url" class="form-control border-start-0" name="github_url" value="<?php echo htmlspecialchars($candidate_profile['github_url'] ?? ''); ?>" placeholder="https://github.com/username">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Portfolio / Personal Website</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-globe text-info"></i></span>
                        <input type="url" class="form-control border-start-0" name="portfolio_url" value="<?php echo htmlspecialchars($candidate_profile['portfolio_url'] ?? ''); ?>" placeholder="https://myportfolio.com">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Resume Link / Hosted PDF URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-file-pdf text-danger"></i></span>
                        <input type="url" class="form-control border-start-0" name="resume_url" value="<?php echo htmlspecialchars($candidate_profile['resume_url'] ?? ''); ?>" placeholder="https://drive.google.com/your-resume-link or direct link">
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Professional Summary / Bio</label>
                    <textarea class="form-control" name="bio" rows="4" placeholder="Briefly describe your career objectives, projects built, and what makes you a great candidate..."><?php echo htmlspecialchars($candidate_profile['bio'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                <a href="dashboard.php" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                    <i class="fas fa-save me-1"></i> Save Profile Details
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
