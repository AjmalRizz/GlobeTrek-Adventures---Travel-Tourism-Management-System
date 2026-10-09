<?php
// bookings.php - Staff Booking Manager
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce staff or admin role
require_role([2, 3]);

$user_name = $_SESSION['full_name'];
$bookings = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT b.*, u.full_name, u.email, tp.title FROM bookings b 
                             JOIN users u ON b.user_id = u.id 
                             JOIN travel_packages tp ON b.package_id = tp.id 
                             ORDER BY b.created_at DESC");
        $bookings = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'status_updated') {
        $success_msg = 'Booking status has been updated successfully!';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid_params') {
        $error_msg = 'Invalid parameters passed.';
    } elseif ($_GET['error'] === 'db_error') {
        $error_msg = 'A system error occurred. Please try again.';
    }
}
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Staff Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Bookings</li>
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
                <?php $is_admin = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3); ?>
                <div class="text-center pb-3 border-bottom mb-3">
                    <?php if ($is_admin): ?>
                        <i class="bi bi-shield-lock-fill text-accent" style="font-size: 3.5rem;"></i>
                        <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Admin)</h5>
                        <span class="badge bg-light text-accent border mt-1">Administrator</span>
                    <?php else: ?>
                        <i class="bi bi-person-badge-fill text-secondary" style="font-size: 3.5rem;"></i>
                        <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Staff)</h5>
                        <span class="badge bg-light text-secondary border mt-1">Tour Coordinator</span>
                    <?php endif; ?>
                </div>
                <?php if ($is_admin): ?>
                    <a class="nav-link" href="../admin/dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                    <a class="nav-link active" href="bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                    <a class="nav-link" href="../admin/staff_management.php"><i class="bi bi-people"></i>Staff Accounts</a>
                    <a class="nav-link" href="../admin/audit_logs.php"><i class="bi bi-shield-check"></i>Audit Logs</a>
                <?php else: ?>
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                    <a class="nav-link" href="packages.php"><i class="bi bi-airplane"></i>Manage Packages</a>
                    <a class="nav-link active" href="bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                    <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                    <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-quote"></i>Inquiries</a>
                    <a class="nav-link" href="listings.php"><i class="bi bi-building"></i>Listings CRUD</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">Manage Customer Bookings</h4>

                <?php if (!empty($bookings)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="bg-light">
                                    <th class="py-3 px-3">Reference</th>
                                    <th class="py-3">Customer Info</th>
                                    <th class="py-3">Tour Package</th>
                                    <th class="py-3">Travel Date</th>
                                    <th class="py-3 text-end">Price (<span class="currency-suffix">USD</span>)</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-end px-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $bk): ?>
                                    <tr class="border-bottom">
                                        <td class="py-3 px-3 fw-bold text-dark small"><?php echo htmlspecialchars($bk['booking_ref']); ?></td>
                                        <td class="py-3 small">
                                            <span class="fw-semibold d-block text-dark"><?php echo htmlspecialchars($bk['full_name']); ?></span>
                                            <span class="text-muted" style="font-size:0.75rem;"><?php echo htmlspecialchars($bk['email']); ?></span>
                                        </td>
                                        <td class="py-3 small text-secondary fw-semibold"><?php echo htmlspecialchars($bk['title']); ?></td>
                                        <td class="py-3 small">
                                            <span class="d-block"><?php echo date('M d, Y', strtotime($bk['travel_date'])); ?></span>
                                            <span class="text-muted" style="font-size:0.75rem;"><?php echo htmlspecialchars($bk['num_travelers']); ?> Traveler(s)</span>
                                        </td>
                                        <td class="py-3 text-end fw-bold text-primary small price-display" data-usd="<?php echo htmlspecialchars($bk['total_price']); ?>">$<?php echo number_format($bk['total_price'], 2); ?></td>
                                        <td class="py-3 text-center">
                                            <?php 
                                            $st = $bk['status'];
                                            $badge_class = 'bg-secondary';
                                            if ($st === 'pending') $badge_class = 'bg-warning-subtle text-warning border border-warning';
                                            elseif ($st === 'confirmed') $badge_class = 'bg-success-subtle text-success border border-success';
                                            elseif ($st === 'cancelled') $badge_class = 'bg-danger-subtle text-danger border border-danger';
                                            elseif ($st === 'completed') $badge_class = 'bg-info-subtle text-info border border-info';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 py-2 small" style="font-size:0.7rem;">
                                                <?php echo ucfirst($st); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-end px-3">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3 py-1 small fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.75rem;">
                                                    <i class="bi bi-gear-fill me-1"></i> Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2" style="min-width: 175px;">
                                                    <li class="dropdown-header text-uppercase text-muted small fw-bold px-3 py-1" style="font-size:0.65rem;">Select Action</li>
                                                    
                                                    <!-- Option 1: Confirm -->
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3 small d-flex align-items-center justify-content-between <?php echo ($st === 'confirmed') ? 'active bg-success text-white fw-bold' : 'text-success'; ?>" 
                                                           href="../actions/staff_actions.php?action=update_booking_status&id=<?php echo $bk['id']; ?>&status=confirmed">
                                                            <span><i class="bi bi-check-circle-fill me-2"></i>Confirm</span>
                                                            <?php if ($st === 'confirmed'): ?><span class="badge bg-white text-success rounded-pill" style="font-size:0.6rem;">Active</span><?php endif; ?>
                                                        </a>
                                                    </li>

                                                    <!-- Option 2: Pending -->
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3 small d-flex align-items-center justify-content-between <?php echo ($st === 'pending') ? 'active bg-warning text-dark fw-bold' : 'text-warning'; ?>" 
                                                           href="../actions/staff_actions.php?action=update_booking_status&id=<?php echo $bk['id']; ?>&status=pending">
                                                            <span><i class="bi bi-clock-history me-2"></i>Pending</span>
                                                            <?php if ($st === 'pending'): ?><span class="badge bg-dark text-warning rounded-pill" style="font-size:0.6rem;">Active</span><?php endif; ?>
                                                        </a>
                                                    </li>

                                                    <!-- Option 3: Cancel -->
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3 small d-flex align-items-center justify-content-between <?php echo ($st === 'cancelled') ? 'active bg-danger text-white fw-bold' : 'text-danger'; ?>" 
                                                           href="../actions/staff_actions.php?action=update_booking_status&id=<?php echo $bk['id']; ?>&status=cancelled" 
                                                           onclick="return confirm('Cancel this booking?');">
                                                            <span><i class="bi bi-x-circle-fill me-2"></i>Cancel</span>
                                                            <?php if ($st === 'cancelled'): ?><span class="badge bg-white text-danger rounded-pill" style="font-size:0.6rem;">Active</span><?php endif; ?>
                                                        </a>
                                                    </li>

                                                    <?php if ($st === 'confirmed'): ?>
                                                        <li><hr class="dropdown-divider my-1"></li>
                                                        <li>
                                                            <a class="dropdown-item py-2 px-3 small d-flex align-items-center text-info" 
                                                               href="../actions/staff_actions.php?action=update_booking_status&id=<?php echo $bk['id']; ?>&status=completed">
                                                                <i class="bi bi-check2-all me-2"></i>Complete
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4 small mb-0">No customer bookings logged.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
