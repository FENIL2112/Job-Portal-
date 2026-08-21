<?php
require_once __DIR__ . '/auth_check.php';

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_role'])) {
    $uid = (int)($_POST['user_id'] ?? 0);
    $new_role = strtolower(trim($_POST['role'] ?? 'candidate'));
    $is_active = (int)($_POST['is_active'] ?? 1);

    if ($uid > 0 && in_array($new_role, ['admin', 'candidate'])) {
        // Prevent demoting current active logged in admin user to prevent lockout
        if ($uid === (int)$_SESSION['user_id'] && $new_role !== 'admin') {
            $_SESSION['admin_error'] = "You cannot change your own admin role while logged in.";
        } else {
            $uStmt = mysqli_prepare($con, "UPDATE users SET role = ?, is_active = ? WHERE id = ?");
            if ($uStmt) {
                mysqli_stmt_bind_param($uStmt, "sii", $new_role, $is_active, $uid);
                if (mysqli_stmt_execute($uStmt)) {
                    $_SESSION['admin_success'] = "User #$uid permissions updated.";
                } else {
                    $_SESSION['admin_error'] = "Failed to update user.";
                }
                mysqli_stmt_close($uStmt);
            }
        }
    }
    header('Location: users.php');
    exit();
}

$page_title = 'System User Management';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$admin_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);
$admin_error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);

$search = trim($_GET['search'] ?? '');
$role_filter = trim($_GET['role'] ?? '');

$where = [];
if (!empty($search)) {
    $sSafe = mysqli_real_escape_string($con, $search);
    $where[] = "(name LIKE '%$sSafe%' OR email LIKE '%$sSafe%')";
}
if (!empty($role_filter)) {
    $rSafe = mysqli_real_escape_string($con, $role_filter);
    $where[] = "role = '$rSafe'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$usersQ = "SELECT * FROM users $whereClause ORDER BY id DESC";
$usersRes = mysqli_query($con, $usersQ);
?>

<div class="page-header-box">
    <div class="page-header-title">
        <h1>User Account & Role Management 🔐</h1>
        <p>Manage system credentials, administrator privileges, and student/candidate profiles.</p>
    </div>
</div>

<!-- Flash Alerts -->
<?php if (!empty($admin_success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $admin_success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($admin_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $admin_error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- User Filter Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="users.php" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search user by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">All User Roles</option>
                    <option value="admin" <?php echo ($role_filter === 'admin') ? 'selected' : ''; ?>>Admin Users</option>
                    <option value="candidate" <?php echo ($role_filter === 'candidate') ? 'selected' : ''; ?>>Candidate Users</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold" style="font-size: 0.88rem;">Filter</button>
                <?php if (!empty($search) || !empty($role_filter)): ?>
                    <a href="users.php" class="btn btn-outline-secondary rounded-3" title="Clear Filters">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
            <i class="fas fa-users text-primary me-2"></i> Registered Accounts
        </h5>
        <span class="text-muted" style="font-size: 0.85rem;">
            Total <?php echo $usersRes ? mysqli_num_rows($usersRes) : 0; ?> users
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase text-muted" style="font-size: 0.76rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-4">User</th>
                    <th>Role & Permissions</th>
                    <th>Account Status</th>
                    <th>Registered On</th>
                    <th>Last Active</th>
                    <th class="text-end pe-4">Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usersRes && mysqli_num_rows($usersRes) > 0): ?>
                    <?php while ($u = mysqli_fetch_assoc($usersRes)): 
                        $isAdmin = (strtolower($u['role'] ?? '') === 'admin');
                        $isActive = isset($u['is_active']) ? (int)$u['is_active'] === 1 : true;
                    ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle <?php echo $isAdmin ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'; ?> fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['name']); ?></div>
                                        <div class="text-muted" style="font-size: 0.8rem;"><?php echo htmlspecialchars($u['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                        <i class="fas fa-shield-alt me-1"></i> Administrator
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                        <i class="fas fa-user-graduate me-1"></i> Candidate / Student
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                        <i class="fas fa-ban me-1"></i> Deactivated
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.82rem;">
                                    <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.82rem;">
                                    <?php echo !empty($u['last_login']) ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-light border text-primary fw-semibold" 
                                        data-user="<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8'); ?>"
                                        onclick="editUserModal(this)">
                                    <i class="fas fa-user-cog me-1"></i> Edit Permissions
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No user accounts matching search.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="users.php">
                <input type="hidden" name="update_user_role" value="1">
                <input type="hidden" name="user_id" id="uModalId">

                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold" id="userModalLabel">
                        <i class="fas fa-user-shield text-primary me-2"></i> User Permissions
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="card bg-light border-0 rounded-4 p-3 mb-3">
                        <strong class="fs-6 text-dark d-block" id="uModalName">-</strong>
                        <span class="text-muted" style="font-size: 0.85rem;" id="uModalEmail">-</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Account Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" id="uModalRole" required>
                            <option value="candidate">🎓 Candidate / Student (Can browse, apply & view own dashboard)</option>
                            <option value="admin">🛡️ Administrator (Full control, candidate management & ATS)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Account Status</label>
                        <select class="form-select" name="is_active" id="uModalActive">
                            <option value="1">✅ Active</option>
                            <option value="0">⛔ Deactivated</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUserModal(btnOrData) {
    let user;
    try {
        if (btnOrData instanceof HTMLElement) {
            user = JSON.parse(btnOrData.getAttribute('data-user'));
        } else if (typeof btnOrData === 'string') {
            user = JSON.parse(btnOrData);
        } else {
            user = btnOrData;
        }
    } catch (e) {
        console.error('Error parsing user data:', e);
        return;
    }

    if (!user) return;

    document.getElementById('uModalId').value = user.id || '';
    document.getElementById('uModalName').textContent = user.name || '-';
    document.getElementById('uModalEmail').textContent = user.email || '-';
    document.getElementById('uModalRole').value = user.role ? user.role.toLowerCase() : 'candidate';
    document.getElementById('uModalActive').value = (user.is_active !== undefined) ? user.is_active : '1';

    const modalEl = document.getElementById('userModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
