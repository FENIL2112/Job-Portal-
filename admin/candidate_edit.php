<?php
require_once __DIR__ . '/auth_check.php';

$ids = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($ids <= 0) {
    $_SESSION['admin_error'] = 'Invalid candidate ID specified for update.';
    header('Location: candidates.php');
    exit();
}

$form_error = '';

// Fetch existing record from jobregistration
$showquery = "SELECT * FROM jobregistration WHERE id = ?";
$showstmt = mysqli_prepare($con, $showquery);
if ($showstmt) {
    mysqli_stmt_bind_param($showstmt, "i", $ids);
    mysqli_stmt_execute($showstmt);
    $result = mysqli_stmt_get_result($showstmt);
    $arrdata = mysqli_fetch_assoc($result);
    mysqli_stmt_close($showstmt);

    if (!$arrdata) {
        $_SESSION['admin_error'] = "Candidate #$ids not found.";
        header('Location: candidates.php');
        exit();
    }
} else {
    $_SESSION['admin_error'] = 'Database error while fetching candidate record.';
    header('Location: candidates.php');
    exit();
}

// Process update on POST
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
        $check_query = "SELECT id FROM jobregistration WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($con, $check_query);
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "si", $email, $ids);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $form_error = "This email is already in use by another candidate.";
            } else {
                $updatequery = "UPDATE jobregistration SET name=?, degree=?, mobile=?, email=?, refer=?, jobpost=? WHERE id=?";
                $update_stmt = mysqli_prepare($con, $updatequery);
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "ssssssi", $name, $qualification, $mobile, $email, $refer, $position, $ids);
                    if (mysqli_stmt_execute($update_stmt)) {
                        mysqli_stmt_close($update_stmt);

                        // Also update matched application in job_applications if exists
                        $updateApp = "UPDATE job_applications SET name=?, degree=?, mobile=?, email=?, refer=?, jobpost=? WHERE email=?";
                        $uAppStmt = mysqli_prepare($con, $updateApp);
                        if ($uAppStmt) {
                            mysqli_stmt_bind_param($uAppStmt, "sssssss", $name, $qualification, $mobile, $email, $refer, $position, $arrdata['email']);
                            mysqli_stmt_execute($uAppStmt);
                            mysqli_stmt_close($uAppStmt);
                        }

                        $_SESSION['admin_success'] = "Candidate #$ids (<strong>" . htmlspecialchars($name) . "</strong>) updated successfully!";
                        header('Location: candidates.php');
                        exit();
                    } else {
                        $form_error = "Update failed. Please try again.";
                    }
                    mysqli_stmt_close($update_stmt);
                }
            }
            mysqli_stmt_close($check_stmt);
        }
    } else {
        $form_error = implode(' ', $errors);
    }

    // Reload candidate data if update had error
    $showquery = "SELECT * FROM jobregistration WHERE id = ?";
    $showstmt = mysqli_prepare($con, $showquery);
    if ($showstmt) {
        mysqli_stmt_bind_param($showstmt, "i", $ids);
        mysqli_stmt_execute($showstmt);
        $result = mysqli_stmt_get_result($showstmt);
        $arrdata = mysqli_fetch_assoc($result);
        mysqli_stmt_close($showstmt);
    }
}

$page_title = 'Edit Candidate Profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>Edit Candidate #<?php echo $ids; ?> ✏️</h1>
        <p>Modify qualification, contact details, and assigned job post for <?php echo htmlspecialchars($arrdata['name']); ?>.</p>
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
        <form method="POST" action="candidate_edit.php?id=<?php echo $ids; ?>">
            <input type="hidden" name="id" value="<?php echo $ids; ?>">

            <div class="row g-4">
                
                <!-- Full Name -->
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="name" name="name" value="<?php echo htmlspecialchars($arrdata['name']); ?>" required>
                    </div>
                </div>

                <!-- Degree / Qualification -->
                <div class="col-md-6">
                    <label for="qualification" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Qualification / Degree <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-graduation-cap text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="qualification" name="qualification" value="<?php echo htmlspecialchars($arrdata['degree']); ?>" required>
                    </div>
                </div>

                <!-- Mobile -->
                <div class="col-md-6">
                    <label for="mobile" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Mobile Number (10 Digits) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                        <input type="tel" class="form-control border-start-0" id="mobile" name="mobile" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($arrdata['mobile']); ?>" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label for="email" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" value="<?php echo htmlspecialchars($arrdata['email']); ?>" required>
                    </div>
                </div>

                <!-- Reference -->
                <div class="col-md-6">
                    <label for="refer" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Reference Source</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-link text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="refer" name="refer" value="<?php echo htmlspecialchars($arrdata['refer']); ?>">
                    </div>
                </div>

                <!-- Position -->
                <div class="col-md-6">
                    <label for="job_position" class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Applied Position <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-briefcase text-muted"></i></span>
                        <select class="form-select border-start-0" id="job_position" name="job_position" required>
                            <option value="Web Developer" <?php echo ($arrdata['jobpost'] === 'Web Developer') ? 'selected' : ''; ?>>🌐 Web Developer</option>
                            <option value="UI/UX Designer" <?php echo ($arrdata['jobpost'] === 'UI/UX Designer') ? 'selected' : ''; ?>>🎨 UI/UX Designer</option>
                            <option value="Frontend Developer" <?php echo ($arrdata['jobpost'] === 'Frontend Developer') ? 'selected' : ''; ?>>⚡ Frontend Developer</option>
                            <option value="Backend Developer" <?php echo ($arrdata['jobpost'] === 'Backend Developer') ? 'selected' : ''; ?>>🔧 Backend Developer</option>
                            <option value="Operations Manager" <?php echo ($arrdata['jobpost'] === 'Operations Manager') ? 'selected' : ''; ?>>⚙️ Operations Manager</option>
                            <option value="Full Stack Developer" <?php echo ($arrdata['jobpost'] === 'Full Stack Developer') ? 'selected' : ''; ?>>🚀 Full Stack Developer</option>
                            <option value="DevOps & Cloud Engineer" <?php echo ($arrdata['jobpost'] === 'DevOps & Cloud Engineer') ? 'selected' : ''; ?>>☁️ DevOps & Cloud Engineer</option>
                            <option value="Other" <?php echo ($arrdata['jobpost'] === 'Other') ? 'selected' : ''; ?>>❓ Other</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="candidates.php" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
                    <button type="submit" name="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-save me-1"></i> Update Candidate Record
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
