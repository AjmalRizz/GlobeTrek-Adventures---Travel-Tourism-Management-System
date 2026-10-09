<?php
// inquiries.php - Customer Inquiry Ticket Manager
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer

$user_id = $_SESSION['user_id'];
$inquiries = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT i.*, u.full_name AS staff_name FROM inquiries i 
                             LEFT JOIN users u ON i.responded_by = u.id
                             WHERE i.user_id = ? 
                             ORDER BY i.created_at DESC");
        $stmt->execute([$user_id]);
        $inquiries = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'inquiry_submitted') {
        $success_msg = 'Your inquiry ticket has been submitted successfully! Check responses below.';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $error_msg = 'Please fill in all form fields.';
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
            <li class="breadcrumb-item active" aria-current="page">Support Tickets</li>
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
                <a class="nav-link active" href="inquiries.php"><i class="bi bi-chat-left-text"></i>Inquiries</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card"></i>Payments</a>
            </div>
        </div>

        <!-- Inquiries Content -->
        <div class="col-lg-9">
            <div class="row g-4">
                <!-- Submit Form (col-md-5) -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                        <h5 class="fw-bold text-dark mb-3">Submit Inquiry</h5>
                        <form action="../actions/inquiry.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <!-- Autofill user info in hidden fields -->
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>">

                            <div class="mb-3">
                                <label for="subject" class="form-label small fw-semibold text-secondary">Subject</label>
                                <input type="text" class="form-control form-control-custom" id="subject" name="subject" placeholder="e.g. Travel Date Adjustments" required>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label small fw-semibold text-secondary">Inquiry Details</label>
                                <textarea class="form-control form-control-custom" id="message" name="message" rows="4" placeholder="Detail your question..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-custom-primary text-white rounded-pill px-4 w-100 fw-bold">Submit Ticket</button>
                        </form>
                    </div>
                </div>

                <!-- Inquiry List (col-md-7) -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h5 class="fw-bold text-dark mb-4">Inquiry History</h5>

                        <?php if (!empty($inquiries)): ?>
                            <div class="d-flex flex-column gap-3" style="max-height: 500px; overflow-y: auto; padding-right: 5px;">
                                <?php foreach ($inquiries as $inq): ?>
                                    <div class="p-3 border border-light bg-light rounded-4">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($inq['subject']); ?></h6>
                                            <?php 
                                            $st = $inq['status'];
                                            $badge_class = 'bg-secondary';
                                            if ($st === 'new') $badge_class = 'bg-warning-subtle text-warning border border-warning';
                                            elseif ($st === 'resolved') $badge_class = 'bg-success-subtle text-success border border-success';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-2 py-1 small" style="font-size:0.65rem;">
                                                <?php echo ucfirst($st); ?>
                                            </span>
                                        </div>
                                        <p class="text-muted mb-3" style="font-size:0.75rem; line-height:1.5;"><?php echo nl2br(htmlspecialchars($inq['message'])); ?></p>

                                        <?php if ($st === 'resolved' && !empty($inq['response'])): ?>
                                            <div class="bg-white p-3 rounded-3 border-start border-success border-4 small">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <strong class="text-success small"><i class="bi bi-chat-text-fill me-1"></i>Staff Reply:</strong>
                                                    <span class="text-muted" style="font-size: 0.65rem;">Replied by <?php echo htmlspecialchars($inq['staff_name']); ?></span>
                                                </div>
                                                <p class="text-muted mb-0" style="font-size:0.75rem;"><?php echo nl2br(htmlspecialchars($inq['response'])); ?></p>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted d-block small mt-2" style="font-size:0.65rem;"><i class="bi bi-hourglass-split me-1 text-warning"></i>Awaiting response from local coordinator</span>
                                        <?php endif; ?>
                                        <span class="text-muted d-block mt-2 text-end" style="font-size: 0.65rem;">Submitted on: <?php echo date('M d, Y', strtotime($inq['created_at'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-5 small mb-0">No inquiry tickets logged yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
