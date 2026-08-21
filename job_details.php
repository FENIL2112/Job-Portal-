<?php
session_start();
require_once __DIR__ . '/connection.php';

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    header('Location: jobs.php');
    exit();
}

$jobQ = mysqli_query($con, "SELECT j.*, c.about as company_about, c.website as company_website, c.industry as company_industry 
                           FROM jobs j 
                           LEFT JOIN companies c ON j.company_id = c.id 
                           WHERE j.id = $job_id");
$job = mysqli_fetch_assoc($jobQ);

if (!$job) {
    header('Location: jobs.php');
    exit();
}

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_role = strtolower($_SESSION['user_role'] ?? '');
$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

// Check if candidate profile is available to prefill
$cand_phone = '';
$cand_degree = '';
$cand_skills = '';
$cand_experience = 'Fresher';

if ($user_id > 0) {
    $cpQ = mysqli_query($con, "SELECT * FROM candidate_profiles WHERE user_id = $user_id");
    if ($cpRow = mysqli_fetch_assoc($cpQ)) {
        $cand_phone = $cpRow['phone'] ?? '';
        $cand_degree = $cpRow['degree'] ?? '';
        $cand_skills = $cpRow['skills'] ?? '';
        $cand_experience = $cpRow['experience_level'] ?? 'Fresher';
    }
}

// Check if already applied
$hasApplied = false;
if ($is_logged_in) {
    $chkApp = mysqli_query($con, "SELECT id, status FROM job_applications WHERE job_id = $job_id AND (user_id = $user_id OR email = '" . mysqli_real_escape_string($con, $user_email) . "')");
    if ($appRow = mysqli_fetch_assoc($chkApp)) {
        $hasApplied = true;
        $appliedStatus = $appRow['status'];
    }
}

// Check if saved
$isSaved = false;
if ($is_logged_in) {
    $chkSaved = mysqli_query($con, "SELECT id FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id");
    if (mysqli_num_rows($chkSaved) > 0) {
        $isSaved = true;
    }
}

$home_success = $_SESSION['home_success'] ?? '';
unset($_SESSION['home_success']);
$app_error = $_SESSION['app_error'] ?? '';
unset($_SESSION['app_error']);

// Similar jobs (Limit 3)
$simQ = mysqli_query($con, "SELECT * FROM jobs WHERE category = '" . mysqli_real_escape_string($con, $job['category']) . "' AND id != $job_id AND status = 'Active' LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> at <?php echo htmlspecialchars($job['company_name']); ?> | JobPortal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 14px 28px;
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand-text {
            font-size: 1.35rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .job-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #31104b 100%);
            color: #ffffff;
            padding: 50px 20px 80px;
            position: relative;
            overflow: hidden;
        }

        .job-hero-container {
            max-width: 1100px;
            margin: auto;
            position: relative;
            z-index: 2;
        }

        .main-job-container {
            max-width: 1100px;
            margin: -45px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .job-content-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            padding: 40px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .job-side-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .spec-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .spec-label {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 700;
        }

        .spec-val {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-main);
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar-custom d-flex justify-content-between align-items-center">
    <a href="index.php" class="navbar-brand-text">
        <i class="fas fa-briefcase text-primary"></i> JobPortal Careers
    </a>

    <div class="d-flex align-items-center gap-2">
        <a href="jobs.php" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
            <i class="fas fa-search me-1"></i> Browse Jobs
        </a>

        <?php if ($is_logged_in): ?>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/dashboard.php" class="btn btn-danger rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                    <i class="fas fa-shield-alt me-1"></i> Admin Panel
                </a>
            <?php else: ?>
                <a href="candidate/dashboard.php" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
                    <i class="fas fa-user-circle me-1"></i> My Dashboard
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                Sign In
            </a>
            <a href="register.php" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
                Register
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Hero Section -->
<div class="job-hero">
    <div class="job-hero-container">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="jobs.php" class="text-white-50 text-decoration-none" style="font-size: 0.85rem;">
                <i class="fas fa-arrow-left me-1"></i> Back to All Jobs
            </a>
            <span class="text-white-50">•</span>
            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                <?php echo htmlspecialchars($job['category']); ?>
            </span>
        </div>

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="fw-bold mb-2" style="font-size: 2.2rem;"><?php echo htmlspecialchars($job['title']); ?></h1>
                <div class="d-flex align-items-center gap-3 flex-wrap text-white-50" style="font-size: 0.95rem;">
                    <span><i class="fas fa-building text-warning me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?></span>
                    <span><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                    <span><i class="fas fa-clock text-info me-1"></i> <?php echo htmlspecialchars($job['job_type']); ?></span>
                    <span><i class="fas fa-wallet text-success me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?></span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($is_logged_in): ?>
                    <a href="save_job_process.php?id=<?php echo $job['id']; ?>&return=details" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold" title="Save Job">
                        <i class="<?php echo $isSaved ? 'fas text-warning' : 'far'; ?> fa-bookmark me-1"></i> <?php echo $isSaved ? 'Saved' : 'Save Job'; ?>
                    </a>
                <?php endif; ?>

                <?php if ($hasApplied): ?>
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold" disabled>
                        <i class="fas fa-check-circle me-1"></i> Applied (<?php echo htmlspecialchars($appliedStatus); ?>)
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow" style="background: var(--primary-gradient); border: none;" data-bs-toggle="modal" data-bs-target="#applyJobModal">
                        <i class="fas fa-paper-plane me-1"></i> Apply Now
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Details Layout -->
<div class="main-job-container">
    
    <!-- Flash Messages -->
    <?php if (!empty($home_success)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $home_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($app_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $app_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        
        <!-- Left: Main Job Content -->
        <div class="col-lg-8">
            <div class="job-content-card">
                
                <!-- Description -->
                <h4 class="fw-bold text-dark mb-3">
                    <i class="fas fa-align-left text-primary me-2"></i> Role Description
                </h4>
                <div class="text-secondary mb-4" style="line-height: 1.7; font-size: 0.95rem; white-space: pre-line;">
                    <?php echo htmlspecialchars($job['description']); ?>
                </div>

                <!-- Requirements -->
                <h4 class="fw-bold text-dark mb-3 pt-3 border-top">
                    <i class="fas fa-check-square text-primary me-2"></i> Qualifications & Skills Required
                </h4>
                <div class="text-secondary mb-4" style="line-height: 1.7; font-size: 0.95rem; white-space: pre-line;">
                    <?php echo htmlspecialchars($job['requirements']); ?>
                </div>

                <!-- Perks & Benefits -->
                <?php if (!empty($job['benefits'])): ?>
                    <h4 class="fw-bold text-dark mb-3 pt-3 border-top">
                        <i class="fas fa-gift text-primary me-2"></i> Perks & Compensation Benefits
                    </h4>
                    <div class="text-secondary mb-4" style="line-height: 1.7; font-size: 0.95rem; white-space: pre-line;">
                        <?php echo htmlspecialchars($job['benefits']); ?>
                    </div>
                <?php endif; ?>

                <!-- Apply Banner at Bottom -->
                <div class="p-4 rounded-4 bg-light bg-opacity-75 border mt-4 text-center">
                    <h5 class="fw-bold text-dark mb-1">Interested in this opportunity?</h5>
                    <p class="text-muted mb-3" style="font-size: 0.88rem;">Submit your profile and resume directly to the hiring team.</p>
                    
                    <?php if ($hasApplied): ?>
                        <span class="badge bg-success px-4 py-2 rounded-pill fs-6">
                            <i class="fas fa-check-circle me-1"></i> Already Applied
                        </span>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-bold" style="background: var(--primary-gradient); border: none;" data-bs-toggle="modal" data-bs-target="#applyJobModal">
                            <i class="fas fa-paper-plane me-1"></i> Submit Application
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Right: Job Highlights & Company Info -->
        <div class="col-lg-4">
            
            <!-- Quick Summary Card -->
            <div class="job-side-card">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="fas fa-info-circle text-primary me-2"></i> Job Overview
                </h5>

                <div class="spec-item">
                    <div class="spec-icon"><i class="fas fa-wallet"></i></div>
                    <div>
                        <div class="spec-label">Offered Salary</div>
                        <div class="spec-val text-success"><?php echo htmlspecialchars($job['salary_range']); ?></div>
                    </div>
                </div>

                <div class="spec-item">
                    <div class="spec-icon"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <div class="spec-label">Experience</div>
                        <div class="spec-val"><?php echo htmlspecialchars($job['experience_level']); ?></div>
                    </div>
                </div>

                <div class="spec-item">
                    <div class="spec-icon"><i class="fas fa-briefcase"></i></div>
                    <div>
                        <div class="spec-label">Job Category</div>
                        <div class="spec-val"><?php echo htmlspecialchars($job['category']); ?></div>
                    </div>
                </div>

                <div class="spec-item">
                    <div class="spec-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="spec-label">Open Positions</div>
                        <div class="spec-val"><?php echo (int)$job['openings']; ?> Openings</div>
                    </div>
                </div>

                <?php if (!empty($job['deadline'])): ?>
                    <div class="spec-item mb-0">
                        <div class="spec-icon"><i class="fas fa-hourglass-end"></i></div>
                        <div>
                            <div class="spec-label">Application Deadline</div>
                            <div class="spec-val"><?php echo date('M d, Y', strtotime($job['deadline'])); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Company Overview Card -->
            <div class="job-side-card">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="fas fa-building text-primary me-2"></i> About <?php echo htmlspecialchars($job['company_name']); ?>
                </h5>

                <p class="text-muted" style="font-size: 0.88rem; line-height: 1.6;">
                    <?php echo !empty($job['company_about']) ? htmlspecialchars($job['company_about']) : 'Verified hiring employer on JobPortal.'; ?>
                </p>

                <?php if (!empty($job['company_website'])): ?>
                    <div class="pt-2 border-top">
                        <a href="<?php echo htmlspecialchars($job['company_website']); ?>" target="_blank" class="text-primary text-decoration-none fw-semibold" style="font-size: 0.85rem;">
                            <i class="fas fa-globe me-1"></i> Visit Company Website <i class="fas fa-external-link-alt ms-1" style="font-size: 0.72rem;"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Similar Jobs Card -->
            <?php if ($simQ && mysqli_num_rows($simQ) > 0): ?>
                <div class="job-side-card">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="fas fa-briefcase text-primary me-2"></i> Similar Opportunities
                    </h5>

                    <div class="d-flex flex-column gap-3">
                        <?php while ($sim = mysqli_fetch_assoc($simQ)): ?>
                            <div class="p-2 border-bottom pb-2">
                                <a href="job_details.php?id=<?php echo $sim['id']; ?>" class="fw-bold text-dark text-decoration-none d-block mb-1 hover-primary" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($sim['title']); ?>
                                </a>
                                <div class="text-muted d-flex justify-content-between" style="font-size: 0.78rem;">
                                    <span><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($sim['company_name']); ?></span>
                                    <span class="text-success fw-bold"><?php echo htmlspecialchars($sim['salary_range']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<!-- Apply Modal Form -->
<div class="modal fade" id="applyJobModal" tabindex="-1" aria-labelledby="applyJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="apply_process.php">
                <input type="hidden" name="submit_application" value="1">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <input type="hidden" name="jobpost" value="<?php echo htmlspecialchars($job['title']); ?>">

                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold" id="applyJobModalLabel">
                        <i class="fas fa-paper-plane text-primary me-2"></i> Apply for <?php echo htmlspecialchars($job['title']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="Your Full Name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_email); ?>" placeholder="name@example.com" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Mobile Number (10 Digits) <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($cand_phone); ?>" placeholder="9876543210" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Highest Qualification / Degree <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="qualification" value="<?php echo htmlspecialchars($cand_degree); ?>" placeholder="e.g. B.Tech / MCA / BCA" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Technical Skills / Stack</label>
                            <input type="text" class="form-control" name="skills" value="<?php echo htmlspecialchars($cand_skills); ?>" placeholder="e.g. PHP, JavaScript, React, MySQL">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Experience</label>
                            <select class="form-select" name="experience">
                                <option value="Fresher" <?php echo ($cand_experience === 'Fresher') ? 'selected' : ''; ?>>Fresher / Graduate</option>
                                <option value="1-2 Years" <?php echo ($cand_experience === '1-2 Years') ? 'selected' : ''; ?>>1-2 Years</option>
                                <option value="3+ Years" <?php echo ($cand_experience === '3+ Years') ? 'selected' : ''; ?>>3+ Years</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Reference Source (Optional)</label>
                            <input type="text" class="form-control" name="refer" placeholder="e.g. LinkedIn, JobPortal, Referral Name">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Cover Note / Why are you a great fit?</label>
                            <textarea class="form-control" name="cover_note" rows="3" placeholder="Write a short message to the recruiter..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-paper-plane me-1"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-white border-top py-4 mt-auto">
    <div class="container text-center text-muted" style="font-size: 0.85rem;">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> JobPortal Careers Platform. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
