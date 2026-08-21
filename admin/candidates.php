<?php
$page_title = 'Candidate Management (Admin Only)';
require_once __DIR__ . '/includes/header.php';

// Flash messages
$admin_success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_success']);
$admin_error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_error']);

// Handle Search and Filter Query
$search = trim($_GET['search'] ?? '');
$filter_role = trim($_GET['role'] ?? '');
$filter_degree = trim($_GET['degree'] ?? '');

// Build Query for jobregistration
$where = [];
if (!empty($search)) {
    $sSafe = mysqli_real_escape_string($con, $search);
    $where[] = "(name LIKE '%$sSafe%' OR email LIKE '%$sSafe%' OR mobile LIKE '%$sSafe%' OR degree LIKE '%$sSafe%' OR refer LIKE '%$sSafe%' OR jobpost LIKE '%$sSafe%')";
}

if (!empty($filter_role)) {
    $rSafe = mysqli_real_escape_string($con, $filter_role);
    $where[] = "jobpost = '$rSafe'";
}

if (!empty($filter_degree)) {
    $dSafe = mysqli_real_escape_string($con, $filter_degree);
    $where[] = "degree = '$dSafe'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$selectquery = "SELECT * FROM jobregistration $whereClause ORDER BY id DESC";
$query = mysqli_query($con, $selectquery);
$count = $query ? mysqli_num_rows($query) : 0;

// Fetch unique job roles & degrees for filter dropdowns
$rolesRes = mysqli_query($con, "SELECT DISTINCT jobpost FROM jobregistration WHERE jobpost != '' ORDER BY jobpost ASC");
$degreesRes = mysqli_query($con, "SELECT DISTINCT degree FROM jobregistration WHERE degree != '' ORDER BY degree ASC");
?>

<div class="page-header-box">
    <div class="page-header-title">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1>Candidate Directory 📋</h1>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                <i class="fas fa-lock me-1"></i> RESTRICTED ADMIN VIEW
            </span>
        </div>
        <p>Comprehensive repository of all candidate registrations, resumes, qualifications, and application positions.</p>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" id="exportCsvBtn" style="font-size: 0.88rem;">
            <i class="fas fa-file-csv me-1 text-success"></i> Export CSV
        </button>
        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" onclick="window.print()" style="font-size: 0.88rem;">
            <i class="fas fa-print me-1 text-secondary"></i> Print
        </button>
        <a href="candidate_add.php" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.88rem; background: var(--primary-gradient); border: none;">
            <i class="fas fa-user-plus me-1"></i> Add Candidate
        </a>
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

<!-- Candidate Filter Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="candidates.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search candidate by name, email, phone, referral..." value="<?php echo htmlspecialchars($search); ?>" id="candidateLiveSearch">
                </div>
            </div>

            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">All Applied Roles</option>
                    <?php if ($rolesRes): ?>
                        <?php while ($r = mysqli_fetch_assoc($rolesRes)): ?>
                            <option value="<?php echo htmlspecialchars($r['jobpost']); ?>" <?php echo ($filter_role === $r['jobpost']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['jobpost']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2">
                <select name="degree" class="form-select" onchange="this.form.submit()">
                    <option value="">All Qualifications</option>
                    <?php if ($degreesRes): ?>
                        <?php while ($d = mysqli_fetch_assoc($degreesRes)): ?>
                            <option value="<?php echo htmlspecialchars($d['degree']); ?>" <?php echo ($filter_degree === $d['degree']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['degree']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold" style="font-size: 0.88rem;">Filter</button>
                <?php if (!empty($search) || !empty($filter_role) || !empty($filter_degree)): ?>
                    <a href="candidates.php" class="btn btn-outline-secondary rounded-3" title="Clear Filters">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Candidate List Table Card -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
            <i class="fas fa-users text-primary me-2"></i> All Candidates (<?php echo $count; ?>)
        </h5>
        <span class="text-muted" style="font-size: 0.85rem;">Showing total verified records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="candidatesTable">
            <thead class="table-light text-uppercase text-muted" style="font-size: 0.76rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Candidate</th>
                    <th>Qualification</th>
                    <th>Contact Details</th>
                    <th>Reference</th>
                    <th>Applied Role</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query && mysqli_num_rows($query) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): 
                        $initial = strtoupper(substr($row['name'], 0, 1));
                    ?>
                        <tr class="candidate-row-item">
                            <td class="ps-4">
                                <span class="badge bg-light text-primary border fw-bold">#<?php echo $row['id']; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                        <?php echo $initial; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></div>
                                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-muted text-decoration-none" style="font-size: 0.8rem;">
                                            <i class="fas fa-envelope me-1" style="font-size: 0.72rem;"></i> <?php echo htmlspecialchars($row['email']); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2 py-1 fw-semibold">
                                    <i class="fas fa-graduation-cap me-1 text-primary"></i> <?php echo htmlspecialchars($row['degree']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="tel:<?php echo htmlspecialchars($row['mobile']); ?>" class="text-dark text-decoration-none fw-semibold" style="font-size: 0.85rem;">
                                    <i class="fas fa-phone-alt me-1 text-muted" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($row['mobile']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.85rem;">
                                    <?php echo !empty($row['refer']) ? htmlspecialchars($row['refer']) : '<span class="text-muted opacity-50">Direct</span>'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill" style="background: #ede9fe; color: #6d28d9; padding: 6px 12px; font-size: 0.8rem;">
                                    <i class="fas fa-briefcase me-1"></i> <?php echo htmlspecialchars($row['jobpost']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <!-- View Details Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-light border text-primary" title="View Profile" 
                                            data-candidate="<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>"
                                            onclick="viewCandidateModal(this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="candidate_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-secondary" title="Edit Record">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="candidate_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger" title="Delete Record" 
                                       onclick="return confirm('Are you sure you want to permanently delete candidate #<?php echo $row['id']; ?> (<?php echo htmlspecialchars(addslashes($row['name'])); ?>)?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted">No candidate records found</h5>
                            <p class="text-muted mb-3" style="font-size: 0.88rem;">Try adjusting your search criteria or add a new candidate.</p>
                            <a href="candidate_add.php" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                <i class="fas fa-plus me-1"></i> Add New Candidate
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Candidate Details Modal -->
<div class="modal fade" id="candidateModal" tabindex="-1" aria-labelledby="candidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="candidateModalLabel">Candidate Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold mx-auto d-flex align-items-center justify-content-center mb-3" id="mAvatar" style="width: 70px; height: 70px; font-size: 1.8rem;">
                    A
                </div>
                <h4 class="fw-bold mb-1" id="mName">Candidate Name</h4>
                <div class="badge bg-primary bg-opacity-10 text-primary mb-3" id="mRole">Applied Role</div>

                <div class="card bg-light border-0 rounded-4 text-start p-3 mb-3">
                    <div class="row g-2" style="font-size: 0.88rem;">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">QUALIFICATION</span>
                            <strong id="mDegree">-</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">MOBILE</span>
                            <strong id="mMobile">-</strong>
                        </div>
                        <div class="col-12 mt-2">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">EMAIL ADDRESS</span>
                            <strong id="mEmail">-</strong>
                        </div>
                        <div class="col-12 mt-2">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">REFERRAL SOURCE</span>
                            <strong id="mRefer">-</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="#" id="mEditBtn" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="#" id="mEmailBtn" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fas fa-envelope me-1"></i> Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewCandidateModal(btnOrData) {
    let data;
    try {
        if (btnOrData instanceof HTMLElement) {
            data = JSON.parse(btnOrData.getAttribute('data-candidate'));
        } else if (typeof btnOrData === 'string') {
            data = JSON.parse(btnOrData);
        } else {
            data = btnOrData;
        }
    } catch (e) {
        console.error('Error parsing candidate data:', e);
        return;
    }

    if (!data) return;

    document.getElementById('mName').textContent = data.name || '-';
    document.getElementById('mAvatar').textContent = data.name ? data.name.charAt(0).toUpperCase() : 'C';
    document.getElementById('mRole').textContent = data.jobpost || '-';
    document.getElementById('mDegree').textContent = data.degree || '-';
    document.getElementById('mMobile').textContent = data.mobile || '-';
    document.getElementById('mEmail').textContent = data.email || '-';
    document.getElementById('mRefer').textContent = data.refer ? data.refer : 'Direct Application';

    document.getElementById('mEditBtn').href = 'candidate_edit.php?id=' + (data.id || '');
    document.getElementById('mEmailBtn').href = 'mailto:' + (data.email || '');

    const modalEl = document.getElementById('candidateModal');
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
}

// Live filter table on typing
document.addEventListener('DOMContentLoaded', function() {
    const liveSearch = document.getElementById('candidateLiveSearch');
    const table = document.getElementById('candidatesTable');
    if (liveSearch && table) {
        const rows = table.querySelectorAll('tbody tr.candidate-row-item');
        liveSearch.addEventListener('input', function() {
            const val = this.value.toLowerCase().trim();
            rows.forEach(r => {
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(val) ? '' : 'none';
            });
        });
    }

    // Export CSV
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function() {
            let csv = "ID,Name,Qualification,Mobile,Email,Reference,Applied Role\n";
            const rows = document.querySelectorAll('#candidatesTable tbody tr.candidate-row-item');
            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                if (cols.length >= 6) {
                    const id = cols[0].innerText.replace('#', '').trim();
                    const name = cols[1].querySelector('.fw-bold').innerText.trim();
                    const email = cols[1].querySelector('a').innerText.trim();
                    const qual = cols[2].innerText.trim();
                    const mobile = cols[3].innerText.trim();
                    const refer = cols[4].innerText.trim();
                    const role = cols[5].innerText.trim();

                    csv += `"${id}","${name}","${qual}","${mobile}","${email}","${refer}","${role}"\n`;
                }
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', 'jobportal_candidates_' + new Date().toISOString().slice(0,10) + '.csv');
            a.click();
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
