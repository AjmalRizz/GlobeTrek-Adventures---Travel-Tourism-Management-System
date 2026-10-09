<?php
// bookings.php - Customer Bookings History & Status Tracker
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer

$user_id = $_SESSION['user_id'];
$bookings = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT b.*, tp.title, tp.destination, tp.duration_days, pi.image_path 
                             FROM bookings b 
                             JOIN travel_packages tp ON b.package_id = tp.id 
                             LEFT JOIN package_images pi ON tp.id = pi.package_id AND pi.is_featured = 1
                             WHERE b.user_id = ? 
                             ORDER BY b.created_at DESC");
        $stmt->execute([$user_id]);
        $bookings = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'booking_cancelled') {
        $success_msg = 'Your booking has been cancelled successfully!';
    } elseif ($_GET['success'] === 'booking_recorded') {
        $success_msg = 'Your Booking has Successfully Recorded';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'booking_not_found') {
        $error_msg = 'Selected booking reference not found.';
    } elseif ($_GET['error'] === 'cannot_cancel') {
        $error_msg = 'This booking cannot be cancelled because it is already completed or processed.';
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
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">My Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Bookings</li>
        </ol>
    </nav>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
                <a class="nav-link active" href="bookings.php"><i class="bi bi-journal-check"></i>My Bookings</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-text"></i>Inquiries</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card"></i>Payments</a>
            </div>
        </div>

        <!-- Bookings Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">My Bookings</h4>

                <?php if (!empty($bookings)): ?>
                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($bookings as $bk): ?>
                            <div class="card border border-light rounded-4 overflow-hidden shadow-sm">
                                <div class="row g-0">
                                    <div class="col-md-3">
                                        <div class="position-relative h-100" style="min-height: 180px;">
                                            <?php
                                            $img_path = !empty($bk['image_path']) ? '../' . htmlspecialchars($bk['image_path']) : 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&q=80&w=800';
                                            ?>
                                            <img src="<?php echo $img_path; ?>" onerror="this.src='https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&q=80&w=800'" class="w-100 h-100 position-absolute" style="object-fit: cover;" alt="<?php echo htmlspecialchars($bk['title']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9 p-4 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($bk['title']); ?></h5>
                                                <?php 
                                                $status = $bk['status'];
                                                $badge_class = 'bg-secondary';
                                                if ($status === 'pending') $badge_class = 'bg-warning-subtle text-warning border border-warning';
                                                elseif ($status === 'confirmed') $badge_class = 'bg-success-subtle text-success border border-success';
                                                elseif ($status === 'cancelled') $badge_class = 'bg-danger-subtle text-danger border border-danger';
                                                elseif ($status === 'completed') $badge_class = 'bg-info-subtle text-info border border-info';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 py-2 small" style="font-size: 0.75rem;">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </div>
                                            <span class="text-muted small"><i class="bi bi-geo-alt-fill text-primary me-1"></i><?php echo htmlspecialchars($bk['destination']); ?> (<?php echo htmlspecialchars($bk['duration_days']); ?> Days)</span>
                                            
                                            <div class="row g-3 mt-2">
                                                <div class="col-sm-4">
                                                    <span class="text-muted d-block small">Booking Ref</span>
                                                    <span class="fw-bold text-dark small"><?php echo htmlspecialchars($bk['booking_ref']); ?></span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <span class="text-muted d-block small">Travel Date</span>
                                                    <span class="fw-semibold text-dark small"><?php echo date('M d, Y', strtotime($bk['travel_date'])); ?></span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <span class="text-muted d-block small">Travelers</span>
                                                    <span class="fw-semibold text-dark small"><?php echo htmlspecialchars($bk['num_travelers']); ?> Person(s)</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
                                            <div>
                                                <span class="text-muted small d-block" style="font-size:0.75rem;">Total Price</span>
                                                <span class="fs-5 fw-bold text-primary price-display" data-usd="<?php echo htmlspecialchars($bk['total_price']); ?>">$<?php echo number_format($bk['total_price'], 2); ?></span>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <?php if ($status === 'pending'): ?>
                                                    <a href="../payment.php?booking_ref=<?php echo $bk['booking_ref']; ?>" class="btn btn-sm btn-custom-primary text-white rounded-pill px-3 fw-bold">Pay Now</a>
                                                    <a href="../actions/customer_actions.php?action=cancel_booking&id=<?php echo $bk['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                                                <?php elseif ($status === 'confirmed'): ?>
                                                    <a href="../payment_receipt.php?booking_ref=<?php echo $bk['booking_ref']; ?>" class="btn btn-sm btn-custom-secondary rounded-pill px-3 fw-bold"><i class="bi bi-printer-fill me-1"></i>Receipt</a>
                                                    <a href="../actions/customer_actions.php?action=cancel_booking&id=<?php echo $bk['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure you want to cancel this booking? We will contact you for refunds.');">Cancel</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-journal-x fs-1 text-muted"></i>
                        <h5 class="fw-bold text-dark mt-3">No Bookings Logged Yet</h5>
                        <p class="text-muted mb-0">Explore our tour packages directory to start booking adventures.</p>
                        <a href="../packages.php" class="btn btn-custom-primary text-white mt-3 px-4 rounded-pill">Explore Packages</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
