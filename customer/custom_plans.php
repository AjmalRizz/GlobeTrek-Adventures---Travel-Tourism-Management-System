<?php
// custom_plans.php - Customer Travel Customization Requests Log
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer role

$user_id = $_SESSION['user_id'];
$requests = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT cr.*, u.full_name AS staff_name FROM custom_travel_requests cr 
                             LEFT JOIN users u ON cr.reviewed_by = u.id
                             WHERE cr.user_id = ? 
                             ORDER BY cr.created_at DESC");
        $stmt->execute([$user_id]);
        $requests = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'request_submitted') {
        $success_msg = 'Your custom travel customization request has been submitted successfully! Check evaluations below.';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $error_msg = 'Please fill in all the required request fields.';
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
            <li class="breadcrumb-item active" aria-current="page">Custom Travel Requests</li>
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
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-check"></i>My Bookings</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-text"></i>Inquiries</a>
                <a class="nav-link active" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card"></i>Payments</a>
            </div>
        </div>

        <!-- Custom Plans Content -->
        <div class="col-lg-9">
            <div class="row g-4">
                
                <!-- Submit Form (col-md-5) -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                        <h5 class="fw-bold text-dark mb-3">Plan Custom Trip</h5>
                        <form action="../actions/customer_actions.php?action=custom_plan" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            
                            <div class="mb-3">
                                <label for="destination" class="form-label small fw-semibold text-secondary">Target Destination</label>
                                <input type="text" class="form-control form-control-custom" id="destination" name="destination" placeholder="e.g. Nuwara Eliya & Ella" required>
                            </div>

                            <div class="mb-3">
                                <label for="duration_days" class="form-label small fw-semibold text-secondary">Duration (Days)</label>
                                <input type="number" class="form-control form-control-custom" id="duration_days" name="duration_days" min="1" max="30" value="5" required>
                            </div>

                            <div class="mb-3">
                                <label for="accommodation_type" class="form-label small fw-semibold text-secondary">Accommodation Category</label>
                                <select class="form-select form-control-custom" id="accommodation_type" name="accommodation_type">
                                    <option value="Hotel">Hotel</option>
                                    <option value="Resort">Resort</option>
                                    <option value="Guest House">Guest House</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="transport_preference" class="form-label small fw-semibold text-secondary">Transport Category</label>
                                <select class="form-select form-control-custom" id="transport_preference" name="transport_preference">
                                    <option value="Airport Transfer">Airport Transfer Only</option>
                                    <option value="Private Vehicle">Private Chauffeur SUV</option>
                                    <option value="Tour Bus">Group Tour Bus</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="activities" class="form-label small fw-semibold text-secondary">Core Activities</label>
                                <input type="text" class="form-control form-control-custom" id="activities" name="activities" placeholder="e.g. Hiking, Tea tasting, train ride" required>
                            </div>

                            <div class="mb-3">
                                <label for="budget" class="form-label small fw-semibold text-secondary">Budget (USD)</label>
                                <input type="number" class="form-control form-control-custom" id="budget" name="budget" placeholder="e.g. 500" min="50" required>
                            </div>

                            <div class="mb-4">
                                <label for="special_requests" class="form-label small fw-semibold text-secondary">Special Notes (Optional)</label>
                                <textarea class="form-control form-control-custom" id="special_requests" name="special_requests" rows="3" placeholder="Provide details..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-custom-primary text-white rounded-pill px-4 w-100 fw-bold">Request Quotation</button>
                        </form>
                    </div>
                </div>

                <!-- Request History List (col-md-7) -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h5 class="fw-bold text-dark mb-4">Requests History</h5>

                        <?php if (!empty($requests)): ?>
                            <div class="d-flex flex-column gap-4" style="max-height: 700px; overflow-y: auto; padding-right: 5px;">
                                <?php foreach ($requests as $req): ?>
                                    <div class="p-3 border border-light bg-light rounded-4">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($req['destination']); ?></h6>
                                            <?php 
                                            $st = $req['status'];
                                            $badge_class = 'bg-secondary';
                                            if ($st === 'pending') $badge_class = 'bg-warning-subtle text-warning border border-warning';
                                            elseif ($st === 'responded') $badge_class = 'bg-success-subtle text-success border border-success';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-2 py-1 small" style="font-size:0.65rem;">
                                                <?php echo ucfirst($st); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="row g-2 mb-3 small text-muted" style="font-size: 0.75rem;">
                                            <div class="col-6"><strong>Duration:</strong> <?php echo htmlspecialchars($req['duration_days']); ?> Days</div>
                                            <div class="col-6"><strong>Budget:</strong> <span class="price-display" data-usd="<?php echo htmlspecialchars($req['budget']); ?>">$<?php echo number_format($req['budget'], 2); ?></span></div>
                                            <div class="col-6"><strong>Stay:</strong> <?php echo htmlspecialchars($req['accommodation_type']); ?></div>
                                            <div class="col-6"><strong>Transport:</strong> <?php echo htmlspecialchars($req['transport_preference']); ?></div>
                                            <div class="col-12 mt-1"><strong>Activities:</strong> <?php echo htmlspecialchars($req['activities']); ?></div>
                                            <?php if (!empty($req['special_requests'])): ?>
                                                <div class="col-12"><strong>Notes:</strong> <?php echo htmlspecialchars($req['special_requests']); ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($st === 'responded' && !empty($req['staff_response'])): ?>
                                            <div class="bg-white p-3 rounded-3 border-start border-success border-4 small">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <strong class="text-success small"><i class="bi bi-chat-left-quote-fill me-1"></i>Staff Evaluation & Reply:</strong>
                                                    <span class="text-muted" style="font-size: 0.65rem;">Reviewed by <?php echo htmlspecialchars($req['staff_name']); ?></span>
                                                </div>
                                                <p class="text-muted mb-0" style="font-size:0.75rem;"><?php echo nl2br(htmlspecialchars($req['staff_response'])); ?></p>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted d-block small mt-2" style="font-size:0.65rem;"><i class="bi bi-hourglass-split me-1 text-warning"></i>Awaiting quote & review from local staff</span>
                                        <?php endif; ?>
                                        <span class="text-muted d-block mt-2 text-end" style="font-size: 0.65rem;">Submitted on: <?php echo date('M d, Y', strtotime($req['created_at'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-5 small mb-0">No customized travel requests logged.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
