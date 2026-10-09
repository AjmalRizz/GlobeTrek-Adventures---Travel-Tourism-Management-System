<?php
// dashboard.php - Admin Dashboard with Analytics & Reporting Modules
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce admin role
require_role(3);

$user_name = $_SESSION['full_name'];

// Counters
$stats = [
    'customers' => 0,
    'packages' => 0,
    'bookings' => 0,
    'revenue' => 0.00,
    'inquiries' => 0
];

$recent_payments = [];
$top_packages = [];
$booking_statuses = [
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'completed' => 0
];

if ($pdo) {
    try {
        // Customer count
        $stats['customers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 1")->fetchColumn();
        
        // Packages count
        $stats['packages'] = $pdo->query("SELECT COUNT(*) FROM travel_packages WHERE status = 'available'")->fetchColumn();
        
        // Bookings count
        $stats['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        
        // Revenue
        $rev = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'success'")->fetchColumn();
        $stats['revenue'] = $rev ? floatval($rev) : 0.00;
        
        // Inquiries
        $stats['inquiries'] = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

        // Fetch recent payments
        $stmt = $pdo->query("SELECT p.*, b.booking_ref, u.full_name FROM payments p 
                             JOIN bookings b ON p.booking_id = b.id
                             JOIN users u ON b.user_id = u.id
                             ORDER BY p.created_at DESC LIMIT 5");
        $recent_payments = $stmt->fetchAll();

        // Booking status breakdown
        $status_data = $pdo->query("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status")->fetchAll();
        foreach ($status_data as $sd) {
            $booking_statuses[$sd['status']] = intval($sd['cnt']);
        }

        // Popular packages (top booked)
        $top_packages = $pdo->query("SELECT tp.title, COUNT(b.id) as booking_cnt, SUM(b.total_price) as pkg_revenue 
                                     FROM travel_packages tp 
                                     JOIN bookings b ON tp.id = b.package_id 
                                     WHERE b.status != 'cancelled'
                                     GROUP BY tp.id 
                                     ORDER BY booking_cnt DESC LIMIT 3")->fetchAll();

    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';
?>

<!-- Load Chart.js via CDN for Admin Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
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
                    <i class="bi bi-shield-lock-fill text-accent" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Admin)</h5>
                    <span class="badge bg-light text-accent border mt-1">Administrator</span>
                </div>
                <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                <a class="nav-link" href="../staff/bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link" href="staff_management.php"><i class="bi bi-people"></i>Staff Accounts</a>
                <a class="nav-link" href="audit_logs.php"><i class="bi bi-shield-check"></i>Audit Logs</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <!-- Stats Counters Row -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-md-4 col-lg-2-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
                        <span class="text-muted small d-block" style="font-size:0.75rem;">Travelers</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['customers']; ?></h3>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-airplane-fill"></i></div>
                        <span class="text-muted small d-block" style="font-size:0.75rem;">Packages</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['packages']; ?></h3>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-journal-check"></i></div>
                        <span class="text-muted small d-block" style="font-size:0.75rem;">Bookings</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['bookings']; ?></h3>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-lg-2-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-cash-stack"></i></div>
                        <span class="text-muted small d-block" style="font-size:0.75rem;">Revenue <span class="currency-suffix">USD</span></span>
                        <h3 class="fw-bold text-dark mb-0 mt-1 price-display" data-usd="<?php echo htmlspecialchars($stats['revenue']); ?>" style="font-size: 1.3rem;">$<?php echo number_format($stats['revenue'], 2); ?></h3>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-2-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-secondary-subtle text-secondary"><i class="bi bi-chat-dots-fill"></i></div>
                        <span class="text-muted small d-block" style="font-size:0.75rem;">Inquiries</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['inquiries']; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Charts and Breakdown Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3">Booking Status Summary</h5>
                        <div style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3">Popular Packages</h5>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr class="small text-muted border-bottom">
                                        <th class="py-2">Package</th>
                                        <th class="py-2 text-center">Booked</th>
                                        <th class="py-2 text-end">Sales (<span class="currency-suffix">USD</span>)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($top_packages)): ?>
                                        <?php foreach ($top_packages as $tp_pkg): ?>
                                            <tr class="border-bottom small">
                                                <td class="py-3 fw-bold text-dark"><?php echo htmlspecialchars($tp_pkg['title']); ?></td>
                                                <td class="py-3 text-center"><?php echo htmlspecialchars($tp_pkg['booking_cnt']); ?> times</td>
                                                <td class="py-3 text-end fw-bold text-primary price-display" data-usd="<?php echo htmlspecialchars($tp_pkg['pkg_revenue']); ?>">$<?php echo number_format($tp_pkg['pkg_revenue'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">No packages statistics recorded yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payments Table -->
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h4 class="fw-bold mb-4">Recent Payments Transaction Log</h4>
                <?php if (!empty($recent_payments)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="bg-light">
                                    <th class="py-3 px-3">Transaction Ref</th>
                                    <th class="py-3">Customer</th>
                                    <th class="py-3">Booking Ref</th>
                                    <th class="py-3">Paid Date</th>
                                    <th class="py-3 text-end px-3">Amount (<span class="currency-suffix">USD</span>)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_payments as $rp): ?>
                                    <tr class="border-bottom small">
                                        <td class="py-3 px-3 fw-bold text-dark"><?php echo htmlspecialchars($rp['payment_ref']); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($rp['full_name']); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($rp['booking_ref']); ?></td>
                                        <td class="py-3 text-muted"><?php echo date('M d, Y H:i', strtotime($rp['created_at'])); ?></td>
                                        <td class="py-3 text-end fw-bold text-success px-3 price-display" data-usd="<?php echo htmlspecialchars($rp['amount']); ?>">$<?php echo number_format($rp['amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4 small mb-0">No payment logs recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Chart initialization
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('statusChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
            datasets: [{
                data: [
                    <?php echo $booking_statuses['pending']; ?>,
                    <?php echo $booking_statuses['confirmed']; ?>,
                    <?php echo $booking_statuses['cancelled']; ?>,
                    <?php echo $booking_statuses['completed']; ?>
                ],
                backgroundColor: ['#f59e0b', '#10b981', '#f43f5e', '#0ea5e9'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { family: "'Outfit', sans-serif", size: 12 }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
