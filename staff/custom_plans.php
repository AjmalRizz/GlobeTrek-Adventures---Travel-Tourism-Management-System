<?php
// custom_plans.php - Staff Custom Travel Requests Review Manager
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce staff/admin login
require_role([2, 3]);

$user_name = $_SESSION['full_name'];
$requests = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT cr.*, u.full_name AS customer_name, u.email AS customer_email 
                             FROM custom_travel_requests cr 
                             JOIN users u ON cr.user_id = u.id 
                             ORDER BY cr.created_at DESC");
        $requests = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'responded') {
        $success_msg = 'Response to custom travel request has been logged successfully!';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $error_msg = 'Response content cannot be empty.';
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
            <li class="breadcrumb-item active" aria-current="page">Custom Travel Requests</li>
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
                    <i class="bi bi-person-badge-fill text-secondary" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Staff)</h5>
                    <span class="badge bg-light text-secondary border mt-1">Tour Coordinator</span>
                </div>
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                <a class="nav-link" href="packages.php"><i class="bi bi-airplane"></i>Manage Packages</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link active" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-quote"></i>Inquiries</a>
                <a class="nav-link" href="listings.php"><i class="bi bi-building"></i>Listings CRUD</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                <h4 class="fw-bold text-dark mb-4">Review Custom Trip Requests</h4>

                <?php if (!empty($requests)): ?>
                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($requests as $req): ?>
                            <div class="p-4 border border-light bg-light rounded-4">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($req['destination']); ?></h5>
                                        <span class="text-muted small">Requested by: <strong><?php echo htmlspecialchars($req['customer_name']); ?></strong> (<?php echo htmlspecialchars($req['customer_email']); ?>)</span>
                                    </div>
                                    <?php 
                                    $st = $req['status'];
                                    $badge_class = 'bg-secondary';
                                    if ($st === 'pending') $badge_class = 'bg-warning-subtle text-warning border border-warning';
                                    elseif ($st === 'responded') $badge_class = 'bg-success-subtle text-success border border-success';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 py-2 small" style="font-size:0.75rem;">
                                        <?php echo ucfirst($st); ?>
                                    </span>
                                </div>

                                <div class="row g-3 mb-4 text-muted small">
                                    <div class="col-md-3"><strong>Duration:</strong> <?php echo htmlspecialchars($req['duration_days']); ?> Days</div>
                                    <div class="col-md-3"><strong>Max Budget:</strong> <span class="price-display" data-usd="<?php echo htmlspecialchars($req['budget']); ?>">$<?php echo number_format($req['budget'], 2); ?></span></div>
                                    <div class="col-md-3"><strong>Accommodation:</strong> <?php echo htmlspecialchars($req['accommodation_type']); ?></div>
                                    <div class="col-md-3"><strong>Transport:</strong> <?php echo htmlspecialchars($req['transport_preference']); ?></div>
                                    <div class="col-12 mt-2"><strong>Core Activities:</strong> <?php echo htmlspecialchars($req['activities']); ?></div>
                                    <?php if (!empty($req['special_requests'])): ?>
                                        <div class="col-12"><strong>Special Requests:</strong> <?php echo htmlspecialchars($req['special_requests']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($st === 'pending'): ?>
                                    <!-- Response Form -->
                                    <form action="../actions/staff_actions.php?action=respond_custom_plan" method="POST" class="border-top pt-3">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label for="staff_response_<?php echo $req['id']; ?>" class="form-label small fw-semibold text-secondary">Write Evaluation / Quote Details</label>
                                            <textarea class="form-control form-control-custom bg-white" id="staff_response_<?php echo $req['id']; ?>" name="staff_response" rows="3" placeholder="Provide pricing, itinerary recommendations, or hotel availability notes..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-custom-primary text-white rounded-pill px-4 fw-bold">Send Quote Details</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Responded view -->
                                    <div class="bg-white p-3 rounded-3 border-start border-success border-4 small mt-2">
                                        <strong class="text-success small d-block mb-1"><i class="bi bi-check-circle-fill me-1"></i>Response Logged:</strong>
                                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($req['staff_response'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4 small mb-0">No custom travel requests submitted by customers.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
