<?php
// dashboard.php - Customer Dashboard Profile
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer role

$user_id = $_SESSION['user_id'];
$user = null;
$stats = [
    'bookings' => 0,
    'custom_plans' => 0,
    'inquiries' => 0,
    'total_spent' => 0.00
];

if ($pdo) {
    try {
        // Fetch profile
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Fetch stats
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['bookings'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM custom_travel_requests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['custom_plans'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['inquiries'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments p 
                             JOIN bookings b ON p.booking_id = b.id 
                             WHERE b.user_id = ? AND p.status = 'success'");
        $stmt->execute([$user_id]);
        $spent = $stmt->fetchColumn();
        $stats['total_spent'] = $spent ? floatval($spent) : 0.00;

    } catch (PDOException $e) {
        // Fallback
    }
}

if (!$user) {
    header("Location: ../logout.php");
    exit();
}

require_once '../includes/header.php';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'profile_updated') {
        $success_msg = 'Your profile details have been updated successfully!';
    } elseif ($_GET['success'] === 'registered') {
        $success_msg = 'Registration Successful! Welcome to GlobeTrek Adventures.';
    } elseif ($_GET['success'] === 'logged_in') {
        $success_msg = 'Login Successful! Welcome back.';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $error_msg = 'Please fill in all the profile fields.';
    } elseif ($_GET['error'] === 'db_error') {
        $error_msg = 'A database error occurred. Please try again.';
    } elseif ($_GET['error'] === 'passwords_mismatch') {
        $error_msg = 'New password and confirm password fields do not match.';
    }
}
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Dashboard</li>
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
        <!-- Sidebar Navigation (col-lg-3) -->
        <div class="col-lg-3">
            <div class="sidebar d-flex flex-column gap-2">
                <div class="text-center pb-3 border-bottom mb-3">
                    <i class="bi bi-person-circle text-primary" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?></h5>
                    <span class="badge bg-light text-primary border mt-1">Traveler Account</span>
                </div>
                <a class="nav-link active" href="dashboard.php"><i class="bi bi-person"></i>My Profile</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-check"></i>My Bookings</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-text"></i>Inquiries</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card"></i>Payments</a>
            </div>
        </div>

        <!-- Dashboard Content (col-lg-9) -->
        <div class="col-lg-9">
            <!-- Stats Row -->
            <div class="row g-4 mb-5">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-primary"><i class="bi bi-journal-check"></i></div>
                        <span class="text-muted small d-block">My Bookings</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['bookings']; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-secondary"><i class="bi bi-sliders"></i></div>
                        <span class="text-muted small d-block">Custom Trips</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['custom_plans']; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-accent"><i class="bi bi-chat-left-dots"></i></div>
                        <span class="text-muted small d-block">Active Inquiries</span>
                        <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $stats['inquiries']; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                        <span class="text-muted small d-block">Total Spent <span class="currency-suffix">USD</span></span>
                        <h2 class="fw-bold text-dark mb-0 mt-1 price-display" data-usd="<?php echo htmlspecialchars($stats['total_spent']); ?>" style="font-size: 1.4rem;">$<?php echo number_format($stats['total_spent'], 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Profile Details Form -->
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                <h4 class="fw-bold text-dark mb-3">Update Profile</h4>
                
                <form action="../actions/customer_actions.php?action=update_profile" method="POST" id="profileForm">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label small fw-semibold text-secondary">Full Name</label>
                            <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Email Address (Cannot Change)</label>
                            <input type="email" class="form-control form-control-custom bg-light" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label small fw-semibold text-secondary">Phone Number</label>
                            <input type="tel" class="form-control form-control-custom" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label small fw-semibold text-secondary">New Password (Leave blank to keep current)</label>
                            <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="At least 6 characters">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4 offset-md-6">
                            <label for="confirm_password" class="form-label small fw-semibold text-secondary">Confirm New Password</label>
                            <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-custom-primary text-white rounded-pill px-4 py-2 fw-bold">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
