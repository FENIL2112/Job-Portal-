<?php
require_once __DIR__ . '/../auth_check.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Count total applications by this candidate
$myAppsCount = 0;
$cEmailSafe = mysqli_real_escape_string($con, $candidate_email);
$cAppsRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM job_applications WHERE user_id = $candidate_user_id OR email = '$cEmailSafe'");
if ($cAppsRes) {
    $myAppsCount = mysqli_fetch_assoc($cAppsRes)['cnt'] ?? 0;
}

// Count saved jobs
$savedJobsCount = 0;
$sJRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM saved_jobs WHERE user_id = $candidate_user_id");
if ($sJRes) {
    $savedJobsCount = mysqli_fetch_assoc($sJRes)['cnt'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Candidate Dashboard'; ?> | JobPortal Careers</title>
    
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
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius-md: 14px;
            --radius-lg: 22px;
            --shadow-subtle: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 10px 30px -5px rgba(0, 0, 0, 0.07);
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .candidate-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 28px;
            box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.04);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .cand-brand {
            font-size: 1.3rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .cand-nav-link {
            color: #475569;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .cand-nav-link:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .cand-nav-link.active {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
        }

        .nav-badge-pill {
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: 50px;
        }

        .cand-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: #ffffff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
        }

        /* Content Container */
        .cand-main-container {
            flex: 1;
            max-width: 1240px;
            width: 100%;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        /* Badges */
        .badge-status {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-applied { background: #fef3c7; color: #b45309; }
        .status-review { background: #e0f2fe; color: #0369a1; }
        .status-shortlisted { background: #ede9fe; color: #6d28d9; }
        .status-interview { background: #ffedd5; color: #c2410c; }
        .status-selected { background: #dcfce7; color: #15803d; }
        .status-rejected { background: #fee2e2; color: #b91c1c; }

        @media (max-width: 768px) {
            .candidate-navbar {
                padding: 10px 16px;
            }
            .cand-main-container {
                padding: 20px 14px 40px;
            }
        }
    </style>
</head>
<body>

<!-- Candidate Navigation Bar -->
<nav class="candidate-navbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-4">
        <a href="dashboard.php" class="cand-brand">
            <i class="fas fa-graduation-cap text-primary"></i> JobPortal Candidate
        </a>

        <div class="d-none d-lg-flex align-items-center gap-1">
            <a href="dashboard.php" class="cand-nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="../jobs.php" class="cand-nav-link">
                <i class="fas fa-search"></i> Browse Jobs
            </a>
            <a href="applications.php" class="cand-nav-link <?php echo ($currentPage === 'applications.php') ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> My Applications
                <?php if ($myAppsCount > 0): ?>
                    <span class="nav-badge-pill bg-primary text-white"><?php echo $myAppsCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="saved_jobs.php" class="cand-nav-link <?php echo ($currentPage === 'saved_jobs.php') ? 'active' : ''; ?>">
                <i class="fas fa-bookmark"></i> Saved Jobs
                <?php if ($savedJobsCount > 0): ?>
                    <span class="nav-badge-pill bg-light text-dark border"><?php echo $savedJobsCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php" class="cand-nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> My Profile
            </a>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <?php if ($user_role === 'admin'): ?>
            <a href="../admin/dashboard.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold me-2">
                <i class="fas fa-shield-alt me-1"></i> Admin Panel
            </a>
        <?php endif; ?>

        <div class="dropdown">
            <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill p-1 pe-3 border shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="cand-user-avatar">
                    <?php echo strtoupper(substr($candidate_name, 0, 1)); ?>
                </div>
                <span class="d-none d-sm-inline-block fw-semibold text-dark" style="font-size: 0.88rem;">
                    <?php echo htmlspecialchars($candidate_name); ?>
                </span>
                <i class="fas fa-chevron-down text-muted ms-1" style="font-size: 0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 mt-2 p-2" style="min-width: 220px;">
                <li class="px-3 py-2 border-bottom mb-1">
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($candidate_name); ?></div>
                    <div class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($candidate_email); ?></div>
                    <span class="badge bg-primary bg-opacity-10 text-primary mt-1" style="font-size: 0.7rem;">Candidate Account</span>
                </li>
                <li><a class="dropdown-item rounded-3 py-2" href="profile.php"><i class="fas fa-user me-2 text-primary"></i> Edit Profile</a></li>
                <li><a class="dropdown-item rounded-3 py-2" href="applications.php"><i class="fas fa-file-alt me-2 text-primary"></i> My Applications</a></li>
                <li><a class="dropdown-item rounded-3 py-2" href="saved_jobs.php"><i class="fas fa-bookmark me-2 text-primary"></i> Saved Jobs</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item rounded-3 py-2 text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="cand-main-container">
