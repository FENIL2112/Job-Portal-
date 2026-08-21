<?php
session_start();
require_once __DIR__ . '/connection.php';

$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_role = strtolower($_SESSION['user_role'] ?? '');
$user_name = $_SESSION['user_name'] ?? 'User';

// Metrics from DB
$totalJobs = 0;
$jRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM jobs WHERE status = 'Active'");
if ($jRes) $totalJobs = mysqli_fetch_assoc($jRes)['cnt'] ?? 0;

$totalCompanies = 0;
$cRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM companies");
if ($cRes) $totalCompanies = mysqli_fetch_assoc($cRes)['cnt'] ?? 0;

$totalCandidates = 0;
$candRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM jobregistration");
if ($candRes) $totalCandidates = mysqli_fetch_assoc($candRes)['cnt'] ?? 0;

// Featured Jobs (Limit 6)
$featQ = mysqli_query($con, "SELECT * FROM jobs WHERE status = 'Active' ORDER BY id DESC LIMIT 6");

// Top Categories with Counts
$catQ = mysqli_query($con, "SELECT category, COUNT(*) as cnt FROM jobs WHERE status = 'Active' GROUP BY category ORDER BY cnt DESC LIMIT 8");

// Featured Companies
$compQ = mysqli_query($con, "SELECT * FROM companies ORDER BY id ASC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobPortal | The Next-Gen Job & Candidate Career Platform</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
            --shadow-subtle: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 12px 35px -5px rgba(79, 70, 229, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Top Navigation */
        .navbar-landing {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 14px 32px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .landing-brand {
            font-size: 1.4rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link-custom {
            color: #334155;
            font-size: 0.92rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-link-custom:hover {
            color: var(--primary);
            background: #f1f5f9;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #31104b 100%);
            color: #ffffff;
            padding: 90px 20px 120px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.18;
            letter-spacing: -1px;
            max-width: 850px;
            margin: 0 auto 20px;
        }

        .hero-title span {
            background: linear-gradient(135deg, #a78bfa 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: rgba(255, 255, 255, 0.82);
            max-width: 650px;
            margin: 0 auto 36px;
            line-height: 1.6;
        }

        /* Hero Search Bar */
        .hero-search-wrapper {
            background: #ffffff;
            border-radius: 24px;
            padding: 10px;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.35);
            max-width: 880px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        /* Stat Counter Strip */
        .stat-strip-container {
            max-width: 1100px;
            margin: -50px auto 70px;
            padding: 0 20px;
            position: relative;
            z-index: 20;
        }

        .stat-strip {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }

        .stat-strip-item {
            border-right: 1px solid var(--border-color);
        }

        .stat-strip-item:last-child {
            border-right: none;
        }

        .stat-strip-num {
            font-size: 2.2rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .stat-strip-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* Category Cards */
        .cat-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            padding: 24px;
            text-align: center;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.25s ease;
            box-shadow: var(--shadow-subtle);
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }

        .cat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-card);
            border-color: rgba(79, 70, 229, 0.4);
            color: var(--primary);
        }

        .cat-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(79, 70, 229, 0.08);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 14px;
            transition: all 0.25s ease;
        }

        .cat-card:hover .cat-icon-box {
            background: var(--primary-gradient);
            color: #ffffff;
        }

        /* Job Cards */
        .landing-job-card {
            background: #ffffff;
            border-radius: 22px;
            border: 1px solid var(--border-color);
            padding: 26px;
            box-shadow: var(--shadow-subtle);
            transition: all 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .landing-job-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-card);
            border-color: rgba(79, 70, 229, 0.4);
        }

        /* CTA Banner */
        .cta-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
            color: #ffffff;
            border-radius: 28px;
            padding: 60px 40px;
            margin: 80px auto 40px;
            max-width: 1150px;
            text-align: center;
            box-shadow: 0 20px 50px -10px rgba(79, 70, 229, 0.45);
        }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.2rem; }
            .stat-strip { grid-template-columns: 1fr 1fr; }
            .stat-strip-item:nth-child(2) { border-right: none; }
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<header class="navbar-landing d-flex justify-content-between align-items-center">
    <a href="index.php" class="landing-brand">
        <i class="fas fa-briefcase text-primary"></i> JobPortal
    </a>

    <div class="d-none d-md-flex align-items-center gap-2">
        <a href="jobs.php" class="nav-link-custom">Browse Jobs</a>
        <a href="#categories" class="nav-link-custom">Categories</a>
        <a href="#companies" class="nav-link-custom">Top Employers</a>
        <a href="#howItWorks" class="nav-link-custom">How It Works</a>
    </div>

    <div class="d-flex align-items-center gap-2">
        <?php if ($is_logged_in): ?>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/dashboard.php" class="btn btn-danger rounded-pill px-4 py-2 fw-bold" style="font-size: 0.88rem;">
                    <i class="fas fa-shield-alt me-1"></i> Admin Portal
                </a>
            <?php else: ?>
                <a href="candidate/dashboard.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
                    <i class="fas fa-user-circle me-1"></i> Candidate Hub
                </a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-light rounded-pill px-3 py-2 fw-semibold text-danger border" style="font-size: 0.88rem;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold" style="font-size: 0.88rem;">
                Sign In
            </a>
            <a href="register.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
                Candidate Sign Up
            </a>
        <?php endif; ?>
    </div>
</header>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-badge">
            <i class="fas fa-sparkles text-warning"></i> Empowering 10,000+ Student & Tech Placements
        </div>

        <h1 class="hero-title">
            Land Your Dream Tech Job with <span>1-Click Applications</span>
        </h1>

        <p class="hero-subtitle">
            Explore verified career openings across Web Development, UI/UX Design, Cloud, and Software Engineering from India's leading companies.
        </p>

        <!-- Search Form -->
        <div class="hero-search-wrapper">
            <form method="GET" action="jobs.php" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0" placeholder="Job title, technical skill, or keyword...">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-map-marker-alt text-danger"></i></span>
                        <input type="text" name="loc" class="form-control border-0" placeholder="Bengaluru, Ahmedabad, Remote...">
                    </div>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold" style="background: var(--primary-gradient); border: none; font-size: 0.95rem;">
                        Find Jobs <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Stat Counter Strip -->
<div class="stat-strip-container">
    <div class="stat-strip">
        <div class="stat-strip-item">
            <div class="stat-strip-num"><?php echo $totalJobs; ?>+</div>
            <div class="stat-strip-label">Active Job Openings</div>
        </div>
        <div class="stat-strip-item">
            <div class="stat-strip-num"><?php echo $totalCompanies; ?>+</div>
            <div class="stat-strip-label">Hiring Companies</div>
        </div>
        <div class="stat-strip-item">
            <div class="stat-strip-num"><?php echo $totalCandidates; ?>+</div>
            <div class="stat-strip-label">Registered Candidates</div>
        </div>
        <div class="stat-strip-item">
            <div class="stat-strip-num">98%</div>
            <div class="stat-strip-label">Placement Response</div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<section class="container mb-5 pb-4" id="categories">
    <div class="text-center mb-5">
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
            POPULAR DOMAINS
        </span>
        <h2 class="fw-bold mt-2" style="font-size: 2rem;">Explore Jobs by Category</h2>
        <p class="text-muted" style="font-size: 0.95rem;">Browse positions matching your technical background and career interest</p>
    </div>

    <div class="row g-3">
        <?php if ($catQ && mysqli_num_rows($catQ) > 0): ?>
            <?php while ($cat = mysqli_fetch_assoc($catQ)): 
                $icon = 'fas fa-laptop-code';
                if (stripos($cat['category'], 'Web') !== false) $icon = 'fas fa-globe';
                if (stripos($cat['category'], 'Frontend') !== false) $icon = 'fas fa-code';
                if (stripos($cat['category'], 'Backend') !== false) $icon = 'fas fa-server';
                if (stripos($cat['category'], 'Design') !== false || stripos($cat['category'], 'UI') !== false) $icon = 'fas fa-palette';
                if (stripos($cat['category'], 'Cloud') !== false || stripos($cat['category'], 'DevOps') !== false) $icon = 'fas fa-cloud';
                if (stripos($cat['category'], 'Data') !== false || stripos($cat['category'], 'AI') !== false) $icon = 'fas fa-brain';
            ?>
                <div class="col-6 col-md-3">
                    <a href="jobs.php?category=<?php echo urlencode($cat['category']); ?>" class="cat-card">
                        <div class="cat-icon-box">
                            <i class="<?php echo $icon; ?>"></i>
                        </div>
                        <h5 class="fw-bold mb-1" style="font-size: 1rem;"><?php echo htmlspecialchars($cat['category']); ?></h5>
                        <span class="text-muted" style="font-size: 0.82rem;"><?php echo (int)$cat['cnt']; ?> Open Positions</span>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Jobs Section -->
<section class="container mb-5 pb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                HOT OPPORTUNITIES
            </span>
            <h2 class="fw-bold mt-2 mb-1" style="font-size: 2rem;">Featured Job Openings</h2>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Recently posted roles from top engineering teams</p>
        </div>

        <a href="jobs.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
            Explore All Jobs <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php if ($featQ && mysqli_num_rows($featQ) > 0): ?>
            <?php while ($job = mysqli_fetch_assoc($featQ)): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="landing-job-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="rounded-3 bg-light border p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="<?php echo !empty($job['company_logo']) ? htmlspecialchars($job['company_logo']) : 'fas fa-building text-primary'; ?> fa-lg"></i>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.72rem;">
                                    <?php echo htmlspecialchars($job['category']); ?>
                                </span>
                            </div>

                            <h4 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                                <a href="job_details.php?id=<?php echo $job['id']; ?>" class="text-dark text-decoration-none hover-primary">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </a>
                            </h4>

                            <div class="text-muted mb-2" style="font-size: 0.85rem;">
                                <i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?>
                            </div>

                            <div class="text-secondary mb-3" style="font-size: 0.82rem;">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo htmlspecialchars($job['location']); ?> •
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($job['job_type']); ?></span>
                            </div>

                            <div class="text-success fw-bold fs-6 mb-3">
                                <i class="fas fa-wallet me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex gap-2">
                            <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary w-100 rounded-pill fw-semibold" style="background: var(--primary-gradient); border: none;">
                                View & Apply
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Top Hiring Companies -->
<section class="container mb-5 pb-4" id="companies">
    <div class="text-center mb-5">
        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
            TOP RECRUITERS
        </span>
        <h2 class="fw-bold mt-2" style="font-size: 2rem;">Companies Hiring on JobPortal</h2>
        <p class="text-muted" style="font-size: 0.95rem;">Join thousands of students and developers working at leading organizations</p>
    </div>

    <div class="row g-3">
        <?php if ($compQ && mysqli_num_rows($compQ) > 0): ?>
            <?php while ($comp = mysqli_fetch_assoc($compQ)): ?>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 rounded-4 shadow-sm p-3 text-center h-100 d-flex flex-column justify-content-center align-items-center">
                        <div class="rounded-circle bg-light border p-3 mb-2" style="width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;">
                            <i class="<?php echo !empty($comp['logo']) ? htmlspecialchars($comp['logo']) : 'fas fa-building text-primary'; ?> fa-lg"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;"><?php echo htmlspecialchars($comp['name']); ?></h6>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works Section -->
<section class="container mb-5 pb-4" id="howItWorks">
    <div class="text-center mb-5">
        <span class="badge bg-purple bg-opacity-10 text-purple px-3 py-2 rounded-pill fw-bold" style="background: rgba(147, 51, 234, 0.1); color: #9333ea; font-size: 0.8rem;">
            SEAMLESS EXPERIENCE
        </span>
        <h2 class="fw-bold mt-2" style="font-size: 2rem;">How the Platform Works</h2>
        <p class="text-muted" style="font-size: 0.95rem;">Separate, specialized workflows tailored for Candidates & Administrators</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 text-center h-100">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.4rem;">
                    1
                </div>
                <h4 class="fw-bold mb-2" style="font-size: 1.2rem;">Create Student Profile</h4>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Sign up as a candidate, specify your degree, tech stack, and portfolio links.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 text-center h-100">
                <div class="rounded-circle bg-success bg-opacity-10 text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.4rem;">
                    2
                </div>
                <h4 class="fw-bold mb-2" style="font-size: 1.2rem;">Apply in 1-Click</h4>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Browse verified openings and apply directly with prefilled candidate information.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 text-center h-100">
                <div class="rounded-circle bg-info bg-opacity-10 text-info mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.4rem;">
                    3
                </div>
                <h4 class="fw-bold mb-2" style="font-size: 1.2rem;">Track & Land Interviews</h4>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Track recruiter reviews, receive interview invitations, and get hired.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<div class="container">
    <div class="cta-banner">
        <h2 class="fw-bold mb-3" style="font-size: 2.3rem;">Ready to Accelerate Your Career?</h2>
        <p class="mb-4 text-white-50" style="font-size: 1.05rem; max-width: 600px; margin: 0 auto;">
            Join over 10,000 developers and students applying for top tech opportunities today.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="register.php" class="btn btn-light rounded-pill px-5 py-3 fw-bold text-primary shadow" style="font-size: 1rem;">
                Create Free Candidate Account
            </a>
            <a href="login.php" class="btn btn-outline-light rounded-pill px-5 py-3 fw-bold" style="font-size: 1rem;">
                Sign In to Portal
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-white border-top py-5 mt-5">
    <div class="container text-center text-muted" style="font-size: 0.88rem;">
        <div class="mb-3">
            <a href="index.php" class="text-muted text-decoration-none mx-2 fw-semibold">Home</a> •
            <a href="jobs.php" class="text-muted text-decoration-none mx-2 fw-semibold">Browse Jobs</a> •
            <a href="login.php" class="text-muted text-decoration-none mx-2 fw-semibold">Sign In</a> •
            <a href="register.php" class="text-muted text-decoration-none mx-2 fw-semibold">Candidate Register</a> •
            <a href="terms.php" class="text-muted text-decoration-none mx-2">Terms of Service</a> •
            <a href="privacy.php" class="text-muted text-decoration-none mx-2">Privacy Policy</a>
        </div>
        <p class="mb-0">&copy; <?php echo date('Y'); ?> JobPortal Careers & Placement Management. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>