<?php
session_start();
require_once __DIR__ . '/connection.php';

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_role = strtolower($_SESSION['user_role'] ?? '');
$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? '';

// Filters
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$job_type = trim($_GET['type'] ?? '');
$exp_level = trim($_GET['exp'] ?? '');
$location = trim($_GET['loc'] ?? '');

$where = ["status = 'Active'"];

if (!empty($search)) {
    $sSafe = mysqli_real_escape_string($con, $search);
    $where[] = "(title LIKE '%$sSafe%' OR company_name LIKE '%$sSafe%' OR description LIKE '%$sSafe%' OR requirements LIKE '%$sSafe%')";
}

if (!empty($category)) {
    $cSafe = mysqli_real_escape_string($con, $category);
    $where[] = "category = '$cSafe'";
}

if (!empty($job_type)) {
    $tSafe = mysqli_real_escape_string($con, $job_type);
    $where[] = "job_type = '$tSafe'";
}

if (!empty($exp_level)) {
    $eSafe = mysqli_real_escape_string($con, $exp_level);
    $where[] = "experience_level = '$eSafe'";
}

if (!empty($location)) {
    $lSafe = mysqli_real_escape_string($con, $location);
    $where[] = "location LIKE '%$lSafe%'";
}

$whereClause = "WHERE " . implode(" AND ", $where);
$jobsQuery = "SELECT * FROM jobs $whereClause ORDER BY id DESC";
$jobsRes = mysqli_query($con, $jobsQuery);
$totalJobsCount = $jobsRes ? mysqli_num_rows($jobsRes) : 0;

// Categories for filter list
$catListQ = mysqli_query($con, "SELECT category, COUNT(*) as cnt FROM jobs WHERE status = 'Active' GROUP BY category ORDER BY cnt DESC");

// Saved jobs list for bookmark icon
$savedJobIds = [];
if ($is_logged_in && $user_id > 0) {
    $sRes = mysqli_query($con, "SELECT job_id FROM saved_jobs WHERE user_id = $user_id");
    while ($sr = mysqli_fetch_assoc($sRes)) {
        $savedJobIds[] = (int)$sr['job_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs & Opportunities | JobPortal</title>
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

        /* Hero */
        .jobs-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #31104b 100%);
            color: #ffffff;
            padding: 44px 20px 70px;
            position: relative;
            overflow: hidden;
        }

        .hero-search-box {
            background: #ffffff;
            border-radius: 18px;
            padding: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 900px;
            margin: 20px auto -35px;
            position: relative;
            z-index: 10;
        }

        .content-container {
            max-width: 1200px;
            margin: 50px auto 60px;
            padding: 0 20px;
            flex: 1;
        }

        .filter-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 90px;
        }

        .job-card-row {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .job-card-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.12);
            border-color: rgba(79, 70, 229, 0.3);
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
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
            <i class="fas fa-home me-1"></i> Home
        </a>

        <?php if ($is_logged_in): ?>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/dashboard.php" class="btn btn-danger rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                    <i class="fas fa-shield-alt me-1"></i> Admin Panel
                </a>
            <?php else: ?>
                <a href="candidate/dashboard.php" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
                    <i class="fas fa-user-circle me-1"></i> Candidate Dashboard
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
<div class="jobs-hero text-center">
    <h1 class="fw-bold mb-2" style="font-size: 2.2rem;">Explore Developer & Tech Careers</h1>
    <p class="text-white-50 mb-0" style="font-size: 0.95rem;">Find the right role from verified enterprise recruiters and fast-growing tech startups</p>

    <!-- Hero Search Bar -->
    <div class="hero-search-box">
        <form method="GET" action="jobs.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0" placeholder="Job title, keywords, or company..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-map-marker-alt text-danger"></i></span>
                    <input type="text" name="loc" class="form-control border-0" placeholder="City or Remote" value="<?php echo htmlspecialchars($location); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="background: var(--primary-gradient); border: none;">
                    Search Jobs
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Main Content Layout -->
<div class="content-container">
    <div class="row g-4">
        
        <!-- Left Filter Sidebar -->
        <div class="col-lg-3">
            <div class="filter-card">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">
                        <i class="fas fa-sliders-h text-primary me-2"></i> Filters
                    </h5>
                    <?php if (!empty($search) || !empty($category) || !empty($job_type) || !empty($exp_level) || !empty($location)): ?>
                        <a href="jobs.php" class="text-danger text-decoration-none fw-semibold" style="font-size: 0.78rem;">
                            Reset All
                        </a>
                    <?php endif; ?>
                </div>

                <form method="GET" action="jobs.php" id="filterForm">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="loc" value="<?php echo htmlspecialchars($location); ?>">

                    <!-- Job Category -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.82rem;">JOB CATEGORY</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="" id="catAll" <?php echo empty($category) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <label class="form-check-label" for="catAll" style="font-size: 0.85rem;">All Categories</label>
                            </div>
                            <?php if ($catListQ): ?>
                                <?php while ($cat = mysqli_fetch_assoc($catListQ)): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category" value="<?php echo htmlspecialchars($cat['category']); ?>" id="cat_<?php echo md5($cat['category']); ?>" <?php echo ($category === $cat['category']) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <label class="form-check-label d-flex justify-content-between" for="cat_<?php echo md5($cat['category']); ?>" style="font-size: 0.85rem;">
                                            <span><?php echo htmlspecialchars($cat['category']); ?></span>
                                            <span class="text-muted">(<?php echo $cat['cnt']; ?>)</span>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Employment Type -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.82rem;">EMPLOYMENT TYPE</label>
                        <select class="form-select form-select-sm" name="type" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="Full-Time" <?php echo ($job_type === 'Full-Time') ? 'selected' : ''; ?>>Full-Time</option>
                            <option value="Part-Time" <?php echo ($job_type === 'Part-Time') ? 'selected' : ''; ?>>Part-Time</option>
                            <option value="Remote" <?php echo ($job_type === 'Remote') ? 'selected' : ''; ?>>Remote</option>
                            <option value="Internship" <?php echo ($job_type === 'Internship') ? 'selected' : ''; ?>>Internship</option>
                            <option value="Contract" <?php echo ($job_type === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                        </select>
                    </div>

                    <!-- Experience Level -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.82rem;">EXPERIENCE LEVEL</label>
                        <select class="form-select form-select-sm" name="exp" onchange="this.form.submit()">
                            <option value="">All Experience Levels</option>
                            <option value="Fresher / Entry Level" <?php echo ($exp_level === 'Fresher / Entry Level') ? 'selected' : ''; ?>>Fresher / Entry Level</option>
                            <option value="1-3 Years" <?php echo ($exp_level === '1-3 Years') ? 'selected' : ''; ?>>1-3 Years</option>
                            <option value="3-5 Years" <?php echo ($exp_level === '3-5 Years') ? 'selected' : ''; ?>>3-5 Years</option>
                            <option value="5+ Years" <?php echo ($exp_level === '5+ Years') ? 'selected' : ''; ?>>5+ Years</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Job Listings -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    Available Openings <span class="text-muted fw-normal fs-6">(<?php echo $totalJobsCount; ?> jobs)</span>
                </h5>
            </div>

            <?php if ($jobsRes && mysqli_num_rows($jobsRes) > 0): ?>
                <?php while ($job = mysqli_fetch_assoc($jobsRes)): 
                    $isBookmarked = in_array((int)$job['id'], $savedJobIds);
                ?>
                    <div class="job-card-row">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light border p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                    <i class="<?php echo !empty($job['company_logo']) ? htmlspecialchars($job['company_logo']) : 'fas fa-briefcase text-primary'; ?> fa-lg"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="text-dark text-decoration-none hover-primary">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </a>
                                    </h4>
                                    <div class="text-muted" style="font-size: 0.85rem;">
                                        <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?> •
                                        <i class="fas fa-map-marker-alt ms-2 me-1 text-danger"></i> <?php echo htmlspecialchars($job['location']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <?php if ($is_logged_in): ?>
                                    <a href="save_job_process.php?id=<?php echo $job['id']; ?>&return=jobs.php" class="btn btn-sm btn-light border rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;" title="<?php echo $isBookmarked ? 'Remove Bookmark' : 'Save Job'; ?>">
                                        <i class="<?php echo $isBookmarked ? 'fas text-warning' : 'far text-secondary'; ?> fa-bookmark"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: var(--primary-gradient); border: none; font-size: 0.88rem;">
                                    View & Apply
                                </a>
                            </div>
                        </div>

                        <p class="text-secondary mb-3 mt-2" style="font-size: 0.88rem; line-height: 1.5;">
                            <?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?>
                        </p>

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.82rem;">
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                    <?php echo htmlspecialchars($job['category']); ?>
                                </span>
                                <span class="badge bg-light text-secondary border px-2 py-1">
                                    <?php echo htmlspecialchars($job['job_type']); ?>
                                </span>
                                <span class="badge bg-light text-secondary border px-2 py-1">
                                    <?php echo htmlspecialchars($job['experience_level']); ?>
                                </span>
                            </div>

                            <div class="text-success fw-bold fs-6">
                                <i class="fas fa-wallet me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card border-0 rounded-4 shadow-sm text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>
                    <h4 class="text-muted">No Jobs Match Your Filters</h4>
                    <p class="text-muted mb-4" style="font-size: 0.9rem;">Try broadening your keyword or clearing active category filters.</p>
                    <div>
                        <a href="jobs.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                            Clear All Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>
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
