<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand -->
    <a href="dashboard.php" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="sidebar-brand-text">
            <h4>JobPortal</h4>
            <span>Admin Control</span>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section-title">Core Management</div>

        <a href="dashboard.php" class="sidebar-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <!-- Candidate Management - Restricted to Admin -->
        <a href="candidates.php" class="sidebar-link <?php echo in_array($currentPage, ['candidates.php', 'candidate_add.php', 'candidate_edit.php']) ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i>
            <span>Candidate List</span>
            <span class="sidebar-badge bg-primary text-white"><?php echo $totalCandidatesCount ?? 0; ?></span>
        </a>

        <!-- Applications (ATS) -->
        <a href="applications.php" class="sidebar-link <?php echo ($currentPage === 'applications.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-signature"></i>
            <span>Applications (ATS)</span>
            <?php if (($pendingAppsCount ?? 0) > 0): ?>
                <span class="sidebar-badge bg-warning text-dark"><?php echo $pendingAppsCount; ?></span>
            <?php endif; ?>
        </a>

        <!-- Jobs Management -->
        <a href="jobs.php" class="sidebar-link <?php echo in_array($currentPage, ['jobs.php', 'job_add.php', 'job_edit.php']) ? 'active' : ''; ?>">
            <i class="fas fa-briefcase"></i>
            <span>Job Postings</span>
        </a>

        <div class="nav-section-title">Directory & System</div>

        <!-- Companies -->
        <a href="companies.php" class="sidebar-link <?php echo ($currentPage === 'companies.php') ? 'active' : ''; ?>">
            <i class="fas fa-building"></i>
            <span>Companies</span>
        </a>

        <!-- User Accounts & Roles -->
        <a href="users.php" class="sidebar-link <?php echo ($currentPage === 'users.php') ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i>
            <span>User Accounts</span>
        </a>

        <div class="nav-section-title">Portals & Links</div>

        <a href="../candidate/dashboard.php" target="_blank" class="sidebar-link">
            <i class="fas fa-graduation-cap"></i>
            <span>Candidate Portal <i class="fas fa-arrow-up-right-from-square ms-1" style="font-size: 0.75rem;"></i></span>
        </a>

        <a href="../index.php" target="_blank" class="sidebar-link">
            <i class="fas fa-globe"></i>
            <span>Public Website <i class="fas fa-arrow-up-right-from-square ms-1" style="font-size: 0.75rem;"></i></span>
        </a>
    </nav>

    <!-- Admin User Info Footer -->
    <div class="sidebar-footer">
        <div class="admin-user-card">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($admin_user_name ?? 'A', 0, 1)); ?>
            </div>
            <div class="admin-info">
                <div class="name"><?php echo htmlspecialchars($admin_user_name ?? 'Administrator'); ?></div>
                <div class="role"><i class="fas fa-shield-check me-1"></i> Admin</div>
            </div>
            <a href="../logout.php" class="text-danger p-1" title="Logout">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </div>
</aside>
