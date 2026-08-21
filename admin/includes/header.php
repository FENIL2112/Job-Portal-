<?php
require_once __DIR__ . '/../auth_check.php';

// Active page identification helper
$current_page = basename($_SERVER['PHP_SELF']);

// Retrieve pending application count for notification badge
$pendingAppsCount = 0;
$pAppRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM job_applications WHERE status = 'Applied'");
if ($pAppRes) {
    $pendingAppsCount = mysqli_fetch_assoc($pAppRes)['cnt'] ?? 0;
}

$totalCandidatesCount = 0;
$cRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM jobregistration");
if ($cRes) {
    $totalCandidatesCount = mysqli_fetch_assoc($cRes)['cnt'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> | JobPortal Control Center</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #4f46e5;
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: rgba(79, 70, 229, 0.1);
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --radius-md: 14px;
            --radius-lg: 20px;
            --shadow-subtle: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --shadow-card: 0 10px 30px -5px rgba(0, 0, 0, 0.06);
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
            overflow-x: hidden;
        }

        /* Sidebar Container */
        .admin-sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand {
            padding: 24px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }

        .sidebar-brand-text h4 {
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .sidebar-brand-text span {
            font-size: 0.75rem;
            color: #94a3b8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 20px 16px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #64748b;
            padding: 14px 12px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 12px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-link i {
            font-size: 1.05rem;
            width: 22px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: #ffffff;
            transform: translateX(3px);
        }

        .sidebar-link:hover i {
            transform: scale(1.15);
        }

        .sidebar-link.active {
            background: var(--sidebar-active);
            color: #ffffff;
            box-shadow: 0 4px 18px rgba(79, 70, 229, 0.35);
        }

        .sidebar-badge {
            margin-left: auto;
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
        }

        .admin-user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .admin-info {
            flex: 1;
            min-width: 0;
        }

        .admin-info .name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-info .role {
            font-size: 0.72rem;
            color: #34d399;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Main Content Layout */
        .admin-main {
            margin-left: 280px;
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Navbar */
        .admin-topbar {
            background: #ffffff;
            height: 70px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 99;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-toggle-btn {
            display: none;
            background: none;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            border-radius: 10px;
            color: var(--text-main);
            font-size: 1.1rem;
            cursor: pointer;
        }

        .topbar-search {
            position: relative;
            min-width: 280px;
        }

        .topbar-search input {
            width: 100%;
            height: 40px;
            padding: 8px 16px 8px 38px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.88rem;
            background: #f8fafc;
            transition: all 0.2s;
        }

        .topbar-search input:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .topbar-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.88rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .btn-portal-public {
            background: #f1f5f9;
            color: #334155;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            border: 1px solid var(--border-color);
        }

        .btn-portal-public:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-admin-logout {
            background: #fef2f2;
            color: #dc2626;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #fecaca;
            transition: all 0.2s;
        }

        .btn-admin-logout:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Page Content Area */
        .admin-content {
            padding: 32px;
            flex: 1;
        }

        .page-header-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 28px;
        }

        .page-header-title h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .page-header-title p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .admin-main {
                margin-left: 0;
            }
            .sidebar-toggle-btn {
                display: inline-block;
            }
            .admin-content {
                padding: 20px 16px;
            }
            .admin-topbar {
                padding: 0 16px;
            }
            .topbar-search {
                display: none;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar Include -->
<?php include_once __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Global search jobs, candidates, apps..." id="globalSearchInput">
            </div>
        </div>

        <div class="topbar-right">
            <a href="../jobs.php" target="_blank" class="btn-portal-public" title="View Public Job Directory">
                <i class="fas fa-external-link-alt"></i> <span class="d-none d-sm-inline">Public Site</span>
            </a>
            
            <a href="../logout.php" class="btn-admin-logout" title="Sign Out">
                <i class="fas fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Logout</span>
            </a>
        </div>
    </header>

    <main class="admin-content">
