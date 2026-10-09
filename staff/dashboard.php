<?php
// dashboard.php - Staff Dashboard Overview
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce staff or admin role
require_role([2, 3]);

$user_name = $_SESSION['full_name'];

// Counters
$total_bookings = 0;
$pending_custom = 0;
$active_inquiries = 0;
$total_packages = 0;
$recent_bookings = [];

if ($pdo) {
    try {
        $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $pending_custom = $pdo->query("SELECT COUNT(*) FROM custom_travel_requests WHERE status = 'pending'")->fetchColumn();
        $active_inquiries = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
        $total_packages = $pdo->query("SELECT COUNT(*) FROM travel_packages WHERE status = 'available'")->fetchColumn();
        
        // Fetch 5 recent bookings
        $stmt = $pdo->query("SELECT b.*, u.full_name, p.title FROM bookings b 
                             JOIN users u ON b.user_id = u.id 
                             JOIN travel_packages p ON b.package_id = p.id 
                             ORDER BY b.created_at DESC LIMIT 5");
        $recent_bookings = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Staff Dashboard</li>
        </ol>
    </nav>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'logged_in'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <strong>Login Successful!</strong> Welcome back, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="sidebar d-flex flex-column gap-2">
                <div class="text-center pb-3 border-bottom mb-3">
                    <i class="bi bi-person-badge-fill text-secondary" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Staff)</h5>
                    <span class="badge bg-light text-secondary border mt-1">Tour Coordinator</span>
                </div>
                <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                <a class="nav-link" href="packages.php"><i class="bi bi-airplane"></i>Manage Packages</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-quote"></i>Inquiries</a>
                <a class="nav-link" href="listings.php"><i class="bi bi-building"></i>Listings CRUD</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <!-- Stats Counters Row -->
            <div class="row g-4 mb-5">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-primary"><i class="bi bi-journal-check"></i></div>
                        <span class="text-muted small d-block">Total Bookings</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $total_bookings; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-secondary"><i class="bi bi-sliders"></i></div>
                        <span class="text-muted small d-block">Custom Trips</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $pending_custom; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-accent"><i class="bi bi-chat-dots"></i></div>
                        <span class="text-muted small d-block">New Inquiries</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $active_inquiries; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success-subtle text-success" style="width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:15px;"><i class="bi bi-tree"></i></div>
                        <span class="text-muted small d-block">Active Tours</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $total_packages; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Recent Bookings</h4>
                    <a href="bookings.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">View All Bookings</a>
                </div>

                <?php if (!empty($recent_bookings)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="bg-light">
                                    <th class="py-3 px-3">Reference</th>
                                    <th class="py-3">Customer</th>
                                    <th class="py-3">Tour Package</th>
                                    <th class="py-3">Travel Date</th>
                                    <th class="py-3 text-end">Price (<span class="currency-suffix">USD</span>)</th>
                                    <th class="py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $rb): ?>
                                    <tr class="border-bottom">
                                        <td class="py-3 px-3 fw-bold text-dark small"><?php echo htmlspecialchars($rb['booking_ref']); ?></td>
                                        <td class="py-3 small"><?php echo htmlspecialchars($rb['full_name']); ?></td>
                                        <td class="py-3 small text-secondary fw-semibold"><?php echo htmlspecialchars($rb['title']); ?></td>
                                        <td class="py-3 small"><?php echo date('M d, Y', strtotime($rb['travel_date'])); ?></td>
                                        <td class="py-3 text-end fw-bold text-primary small price-display" data-usd="<?php echo htmlspecialchars($rb['total_price']); ?>">$<?php echo number_format($rb['total_price'], 2); ?></td>
                                        <td class="py-3 text-center">
                                            <?php 
                                            $st = $rb['status'];
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
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0 small text-center py-4">No bookings logged yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
