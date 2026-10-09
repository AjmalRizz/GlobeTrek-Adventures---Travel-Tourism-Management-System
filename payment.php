<?php
// payment.php - Booking Payment Simulator
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

require_role(1); // Enforce customer

$booking_ref = isset($_GET['booking_ref']) ? trim($_GET['booking_ref']) : '';
$booking = null;

if ($pdo && !empty($booking_ref)) {
    try {
        $stmt = $pdo->prepare("SELECT b.*, tp.title, tp.destination, tp.duration_days FROM bookings b 
                             JOIN travel_packages tp ON b.package_id = tp.id 
                             WHERE b.booking_ref = ? AND b.user_id = ? AND b.status = 'pending'");
        $stmt->execute([$booking_ref, $_SESSION['user_id']]);
        $booking = $stmt->fetch();
    } catch (PDOException $e) {
        // Fallback
    }
}

if (!$booking) {
    header("Location: customer/bookings.php?error=booking_not_found");
    exit();
}

require_once 'includes/header.php';
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="customer/bookings.php" class="text-decoration-none">My Bookings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Complete Payment</li>
        </ol>
    </nav>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'booking_recorded'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Your Booking has Successfully Recorded
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'empty_fields'): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Please fill in all required fields.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'db_error'): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> A database error occurred. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 justify-content-center">
        <!-- Booking Summary (col-md-5) -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white h-100">
                <h4 class="fw-bold text-dark mb-4">Booking Summary</h4>
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($booking['title']); ?></h5>
                    <span class="text-muted small"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($booking['destination']); ?></span>
                </div>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Booking Reference</span>
                        <span class="fw-bold small text-dark"><?php echo htmlspecialchars($booking['booking_ref']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Travel Date</span>
                        <span class="fw-semibold small text-dark"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Duration</span>
                        <span class="fw-semibold small text-dark"><?php echo htmlspecialchars($booking['duration_days']); ?> Days</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Number of Travelers</span>
                        <span class="fw-semibold small text-dark"><?php echo htmlspecialchars($booking['num_travelers']); ?> Person(s)</span>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-4 d-flex justify-content-between align-items-center mt-auto">
                    <span class="fw-bold text-muted small">Total Due</span>
                    <span class="fs-3 fw-bold text-primary price-display" data-usd="<?php echo htmlspecialchars($booking['total_price']); ?>">$<?php echo number_format($booking['total_price'], 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Form (col-md-6) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg p-4 rounded-4 bg-white border-top border-primary border-4">
                <h4 class="fw-bold text-dark mb-2">Simulated Payment</h4>
                <p class="text-muted small mb-4">This is a secure academic demo payment simulation. Do NOT use real credit card numbers.</p>

                <form action="actions/payment_process.php" method="POST" id="paymentForm" onsubmit="return validatePayment()">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <input type="hidden" name="booking_ref" value="<?php echo $booking['booking_ref']; ?>">
                    <input type="hidden" name="amount" value="<?php echo $booking['total_price']; ?>">

                    <!-- Cardholder Name -->
                    <div class="mb-3">
                        <label for="card_holder" class="form-label small fw-semibold text-secondary">Card Holder Name</label>
                        <input type="text" class="form-control form-control-custom" id="card_holder" name="card_holder" placeholder="e.g. JANE DOE" required>
                    </div>

                    <!-- Card Number -->
                    <div class="mb-3">
                        <label for="card_number" class="form-label small fw-semibold text-secondary">Card Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 border-secondary-subtle" style="border-radius: 12px 0 0 12px;"><i class="bi bi-credit-card text-muted"></i></span>
                            <input type="text" class="form-control form-control-custom border-start-0 border-secondary-subtle" id="card_number" name="card_number" placeholder="1234 5678 1234 5678" maxlength="19" required style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    <!-- Expiry Date & CVV -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="expiry" class="form-label small fw-semibold text-secondary">Expiration Date</label>
                            <input type="text" class="form-control form-control-custom" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="cvv" class="form-label small fw-semibold text-secondary">CVV</label>
                            <input type="password" class="form-control form-control-custom" id="cvv" name="cvv" placeholder="123" maxlength="3" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-custom-primary w-100 py-3 rounded-pill text-white fw-bold">Pay <span class="price-display" data-usd="<?php echo htmlspecialchars($booking['total_price']); ?>">$<?php echo number_format($booking['total_price'], 2); ?></span> Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Expiry slash insertion
document.getElementById('expiry').addEventListener('input', function (e) {
    var val = e.target.value.replace(/\D/g, '');
    if (val.length >= 2) {
        e.target.value = val.slice(0, 2) + '/' + val.slice(2, 4);
    } else {
        e.target.value = val;
    }
});

// Card spacing insertion
document.getElementById('card_number').addEventListener('input', function (e) {
    var val = e.target.value.replace(/\D/g, '');
    var matches = val.match(/\d{4,16}/g);
    var match = matches && matches[0] || '';
    var parts = [];

    for (var i=0, len=match.length; i<len; i+=4) {
        parts.push(match.substring(i, i+4));
    }

    if (parts.length > 0) {
        e.target.value = parts.join(' ');
    } else {
        e.target.value = val;
    }
});

function validatePayment() {
    const cardNum = document.getElementById('card_number').value.replace(/\s/g, '');
    const expiry = document.getElementById('expiry').value;
    const cvv = document.getElementById('cvv').value;

    if (cardNum.length !== 16) {
        alert('Invalid card number format. Must be 16 digits.');
        return false;
    }

    const expiryParts = expiry.split('/');
    if (expiryParts.length !== 2 || expiryParts[0].length !== 2 || expiryParts[1].length !== 2) {
        alert('Invalid expiry date format. Use MM/YY.');
        return false;
    }

    const month = parseInt(expiryParts[0]);
    if (month < 1 || month > 12) {
        alert('Invalid expiration month.');
        return false;
    }

    if (cvv.length !== 3) {
        alert('Invalid CVV format. Must be 3 digits.');
        return false;
    }

    return true;
}
</script>

<?php require_once 'includes/footer.php'; ?>
