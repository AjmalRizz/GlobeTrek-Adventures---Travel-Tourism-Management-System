<?php
// staff_management.php - Admin Staff CRUD Control Panel
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce admin role
require_role(3);

$user_name = $_SESSION['full_name'];
$staff_members = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT u.id, u.full_name, u.email, u.phone, u.status, sa.department, sa.hire_date 
                             FROM users u 
                             JOIN staff_accounts sa ON u.id = sa.user_id 
                             WHERE u.role_id = 2 
                             ORDER BY sa.id DESC");
        $staff_members = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

// Alerts
$error_msg = '';
$success_msg = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields': $error_msg = 'Please fill in all staff account details.'; break;
        case 'csrf_invalid': $error_msg = 'Security token invalid. Please refresh the page.'; break;
        case 'email_exists': $error_msg = 'An account with this email address already exists.'; break;
        case 'db_error': $error_msg = 'A database error occurred. Please try again.'; break;
        case 'invalid_id': $error_msg = 'Invalid staff user ID.'; break;
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'staff_added': $success_msg = 'New staff member account created successfully!'; break;
        case 'status_toggled': $success_msg = 'Staff member status has been toggled successfully!'; break;
    }
}
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Admin Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Staff Management</li>
        </ol>
    </nav>

    <!-- Alerts -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="sidebar d-flex flex-column gap-2">
                <div class="text-center pb-3 border-bottom mb-3">
                    <i class="bi bi-shield-lock-fill text-accent" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Admin)</h5>
                    <span class="badge bg-light text-accent border mt-1">Administrator</span>
                </div>
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                <a class="nav-link" href="../staff/bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link active" href="staff_management.php"><i class="bi bi-people"></i>Staff Accounts</a>
                <a class="nav-link" href="audit_logs.php"><i class="bi bi-shield-check"></i>Audit Logs</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <div class="row g-4">
                
                <!-- Create Staff Form (col-md-5) -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-accent border-4">
                        <h5 class="fw-bold text-dark mb-3">Create Staff Account</h5>
                        <form action="actions/admin_actions.php?action=create_staff" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label small fw-semibold">Full Name</label>
                                <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" placeholder="e.g. Sunil Perera" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-semibold">Email Address</label>
                                <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="e.g. sunil@globetrek.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label small fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control form-control-custom" id="phone" name="phone" placeholder="e.g. +94771234567" required>
                            </div>
                            <div class="mb-3">
                                <label for="department" class="form-label small fw-semibold">Department</label>
                                <input type="text" class="form-control form-control-custom" id="department" name="department" placeholder="e.g. Tour Management" required>
                            </div>
                            <div class="mb-3">
                                <label for="hire_date" class="form-label small fw-semibold">Hire Date</label>
                                <input type="date" class="form-control form-control-custom" id="hire_date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label small fw-semibold">Password</label>
                                <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="At least 6 characters" required>
                            </div>
                            <button type="submit" class="btn btn-custom-accent text-white rounded-pill px-4 w-100 fw-bold">Create Staff</button>
                        </form>
                    </div>
                </div>

                <!-- Staff List (col-md-7) -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h5 class="fw-bold text-dark mb-4">Active Staff Members</h5>
                        <?php if (!empty($staff_members)): ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="py-3 px-3">Name</th>
                                            <th class="py-3">Department</th>
                                            <th class="py-3 text-center">Status</th>
                                            <th class="py-3 text-end px-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staff_members as $sm): ?>
                                            <tr class="border-bottom small">
                                                <td class="py-3 px-3">
                                                    <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($sm['full_name']); ?></span>
                                                    <span class="text-muted" style="font-size:0.75rem;"><?php echo htmlspecialchars($sm['email']); ?></span>
                                                </td>
                                                <td class="py-3">
                                                    <span class="fw-semibold text-secondary d-block"><?php echo htmlspecialchars($sm['department']); ?></span>
                                                    <span class="text-muted" style="font-size:0.75rem;">Hired: <?php echo date('M d, Y', strtotime($sm['hire_date'])); ?></span>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <span class="badge <?php echo $sm['status'] === 'active' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger'; ?> rounded-pill px-2 py-1 small" style="font-size:0.65rem;">
                                                        <?php echo ucfirst($sm['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 text-end px-3">
                                                    <a href="actions/admin_actions.php?action=toggle_staff_status&id=<?php echo $sm['id']; ?>" class="btn btn-sm <?php echo $sm['status'] === 'active' ? 'btn-outline-danger' : 'btn-success text-white'; ?> rounded-pill px-3 py-1 small" style="font-size:0.7rem;">
                                                        <?php echo $sm['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-4 small mb-0">No staff members listed.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
