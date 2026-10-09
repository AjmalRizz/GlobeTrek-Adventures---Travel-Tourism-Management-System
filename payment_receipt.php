<?php
// payment_receipt.php - Print-friendly payment receipt page
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

require_role(1); // Enforce customer

$booking_ref = isset($_GET['booking_ref']) ? trim($_GET['booking_ref']) : '';
$receipt = null;

if ($pdo && !empty($booking_ref)) {
    try {
        // Fetch booking, package, and payment details in a single query
        $stmt = $pdo->prepare("SELECT b.*, tp.title, tp.destination, tp.duration_days, 
                             p.payment_ref, p.created_at AS payment_date, p.card_holder, u.full_name, u.email, u.phone 
                             FROM bookings b 
                             JOIN travel_packages tp ON b.package_id = tp.id 
                             JOIN payments p ON b.id = p.booking_id 
                             JOIN users u ON b.user_id = u.id
                             WHERE b.booking_ref = ? AND b.user_id = ? AND b.status = 'confirmed'");
        $stmt->execute([$booking_ref, $_SESSION['user_id']]);
        $receipt = $stmt->fetch();
    } catch (PDOException $e) {
        // Fallback
    }
}

if (!$receipt) {
    header("Location: customer/bookings.php?error=receipt_not_found");
    exit();
}

require_once 'includes/header.php';
?>

<!-- Print-specific styles -->
<style>
@media print {
    /* Hide the navigation bars, footers, breadcrumbs, and action buttons during the print */
    nav, footer, .btn, .breadcrumb, .no-print {
        display: none !important;
    }
    body {
        background-color: #ffffff;
        color: #000000;
    }
    .receipt-card {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<div class="container py-5 animated-item">
    <!-- Breadcrumb (No Print) -->
    <nav aria-label="breadcrumb" class="mb-4 no-print">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="customer/bookings.php" class="text-decoration-none">My Bookings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Receipt</li>
        </ol>
    </nav>

    <!-- Payment Success Alert -->
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4 no-print" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <strong>Payment Successful!</strong> Your payment has been processed and your booking is now confirmed.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Receipt Card -->
            <div class="card border-0 shadow-lg p-5 rounded-4 bg-white receipt-card">
                <!-- Receipt Header -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div>
                        <h3 class="fw-bold text-primary mb-1"><i class="bi bi-compass-fill me-2"></i>GlobeTrek Adventures</h3>
                        <p class="text-muted small mb-0">123 Lewis Place, Negombo, Sri Lanka<br>Phone: +94 31 222 1234</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-bold mb-2">PAID</span>
                        <h5 class="fw-bold text-dark mb-0">Payment Receipt</h5>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Client Info -->
                    <div class="col-sm-6">
                        <span class="text-muted small d-block uppercase font-weight-bold">Billed To:</span>
                        <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($receipt['full_name']); ?></h6>
                        <p class="text-muted small mb-0">
                            Email: <?php echo htmlspecialchars($receipt['email']); ?><br>
                            Phone: <?php echo htmlspecialchars($receipt['phone']); ?>
                        </p>
                    </div>

                    <!-- Payment details -->
                    <div class="col-sm-6 text-sm-end">
                        <span class="text-muted small d-block">Transaction Details:</span>
                        <p class="text-muted small mb-0">
                            <strong>Payment Ref:</strong> <?php echo htmlspecialchars($receipt['payment_ref']); ?><br>
                            <strong>Booking Ref:</strong> <?php echo htmlspecialchars($receipt['booking_ref']); ?><br>
                            <strong>Payment Date:</strong> <?php echo date('M d, Y H:i', strtotime($receipt['payment_date'])); ?><br>
                            <strong>Cardholder:</strong> <?php echo htmlspecialchars($receipt['card_holder']); ?>
                        </p>
                    </div>
                </div>

                <!-- Invoice Details Table -->
                <h5 class="fw-bold mb-3">Tour Details</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-3">Description</th>
                                <th class="py-3 text-center" style="width: 120px;">Travelers</th>
                                <th class="py-3 text-end" style="width: 150px;">Unit Price (<span class="currency-suffix">USD</span>)</th>
                                <th class="py-3 text-end" style="width: 150px;">Total (<span class="currency-suffix">USD</span>)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-3 px-3">
                                    <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($receipt['title']); ?></h6>
                                    <span class="text-muted small"><i class="bi bi-geo-alt-fill text-primary me-1"></i><?php echo htmlspecialchars($receipt['destination']); ?> (<?php echo htmlspecialchars($receipt['duration_days']); ?> Days)</span>
                                    <span class="text-muted small d-block mt-1">Travel Date: <?php echo date('M d, Y', strtotime($receipt['travel_date'])); ?></span>
                                </td>
                                <td class="py-3 text-center small fw-semibold text-dark"><?php echo htmlspecialchars($receipt['num_travelers']); ?></td>
                                <td class="py-3 text-end small"><span class="price-display" data-usd="<?php echo htmlspecialchars($receipt['total_price'] / $receipt['num_travelers']); ?>">$<?php echo number_format($receipt['total_price'] / $receipt['num_travelers'], 2); ?></span></td>
                                <td class="py-3 text-end small fw-bold text-dark"><span class="price-display" data-usd="<?php echo htmlspecialchars($receipt['total_price']); ?>">$<?php echo number_format($receipt['total_price'], 2); ?></span></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border-0"></td>
                                <td class="py-3 text-end fw-bold text-dark bg-light">Grand Total:</td>
                                <td class="py-3 text-end fw-bold text-primary bg-light"><span class="price-display" data-usd="<?php echo htmlspecialchars($receipt['total_price']); ?>">$<?php echo number_format($receipt['total_price'], 2); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Special Requests if any -->
                <?php if (!empty($receipt['special_requests'])): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">Special Request Notes:</h6>
                        <div class="bg-light p-3 rounded-3 small text-muted">
                            <?php echo nl2br(htmlspecialchars($receipt['special_requests'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Footer note -->
                <div class="text-center pt-4 border-top mt-4 text-muted small">
                    <p class="mb-1">Thank you for booking with GlobeTrek Adventures! We wish you a safe and memorable journey.</p>
                    <span class="text-muted" style="font-size: 0.75rem;">This is an academic web application seeder demonstration receipt.</span>
                </div>
            </div>

            <!-- Actions (No Print) -->
            <div class="text-center mt-4 no-print d-flex justify-content-center gap-3">
                <button onclick="window.print()" class="btn btn-custom-primary text-white rounded-pill px-4 py-2"><i class="bi bi-printer-fill me-2"></i>Print Receipt</button>
                <a href="customer/bookings.php" class="btn btn-custom-secondary rounded-pill px-4 py-2">Go to My Bookings</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
