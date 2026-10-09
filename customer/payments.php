<?php
// payments.php - Customer Payment History Log
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer role

$user_id = $_SESSION['user_id'];
$payments = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, b.booking_ref, b.travel_date, tp.title FROM payments p 
                             JOIN bookings b ON p.booking_id = b.id 
                             JOIN travel_packages tp ON b.package_id = tp.id
                             WHERE b.user_id = ? 
                             ORDER BY p.created_at DESC");
        $stmt->execute([$user_id]);
        $payments = $stmt->fetchAll();
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
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">My Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payment Logs</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="sidebar d-flex flex-column gap-2">
                <div class="text-center pb-3 border-bottom mb-3">
                    <i class="bi bi-person-circle text-primary" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?></h5>
                    <span class="badge bg-light text-primary border mt-1">Traveler Account</span>
                </div>
                <a class="nav-link" href="dashboard.php"><i class="bi bi-person"></i>My Profile</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-check"></i>My Bookings</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-text"></i>Inquiries</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link active" href="payments.php"><i class="bi bi-credit-card"></i>Payments</a>
            </div>
        </div>

        <!-- Payments Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">Payment Logs</h4>

                <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="bg-light">
                                    <th class="py-3 px-3">Transaction Ref</th>
                                    <th class="py-3">Booking Ref</th>
                                    <th class="py-3">Package Title</th>
                                    <th class="py-3">Paid On</th>
                                    <th class="py-3 text-end">Amount (<span class="currency-suffix">USD</span>)</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $pay): ?>
                                    <tr class="border-bottom">
                                        <td class="py-3 px-3 fw-bold text-dark small"><?php echo htmlspecialchars($pay['payment_ref']); ?></td>
                                        <td class="py-3 small"><?php echo htmlspecialchars($pay['booking_ref']); ?></td>
                                        <td class="py-3 small text-secondary fw-semibold"><?php echo htmlspecialchars($pay['title']); ?></td>
                                        <td class="py-3 small"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                        <td class="py-3 text-end fw-bold text-primary small"><span class="price-display" data-usd="<?php echo htmlspecialchars($pay['amount']); ?>">$<?php echo number_format($pay['amount'], 2); ?></span></td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1 small" style="font-size:0.65rem;">
                                                Paid
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <a href="../payment_receipt.php?booking_ref=<?php echo $pay['booking_ref']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 small" style="font-size:0.75rem;">Receipt</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-5 small mb-0">No payment logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
