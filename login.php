<?php
// Authentication Login Form
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = $_SESSION['role_id'];
    if ($role == 1) header("Location: customer/dashboard.php");
    elseif ($role == 2) header("Location: staff/dashboard.php");
    elseif ($role == 3) header("Location: admin/dashboard.php");
    exit();
}

require_once 'includes/header.php';

$error_msg = '';
$success_msg = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields': $error_msg = 'Please fill in both email and password.'; break;
        case 'invalid_credentials': $error_msg = 'Incorrect email or password.'; break;
        case 'account_deactivated': $error_msg = 'Your account has been deactivated. Please contact support.'; break;
        case 'session_expired': $error_msg = 'Your session has expired. Please log in again.'; break;
        case 'db_error': $error_msg = 'A system error occurred. Please try again later.'; break;
        case 'email_not_found': $error_msg = 'No account found with that email address.'; break;
        case 'forgot_empty_email': $error_msg = 'Please enter your email to reset the password.'; break;
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'password_reset': $success_msg = 'Your password has been reset to default: "reset123". Please log in and change it immediately.'; break;
    }
}
?>

<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="card border-0 shadow-lg p-4 rounded-4 bg-white" style="width: 100%; max-width: 450px;">
        <div class="text-center mb-4">
            <i class="bi bi-compass text-primary fs-1"></i>
            <h3 class="fw-bold mt-2 mb-1">Welcome Back</h3>
            <p class="text-muted small">Log in to manage your GlobeTrek adventures</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3 small py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-3 small py-2" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="actions/auth.php?action=login" method="POST" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 border-secondary-subtle" style="border-radius: 12px 0 0 12px;"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" class="form-control form-control-custom border-start-0 border-secondary-subtle" id="email" name="email" placeholder="e.g. traveler@gmail.com" required style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                    <a href="#" class="small text-decoration-none text-primary" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 border-secondary-subtle" style="border-radius: 12px 0 0 12px;"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control form-control-custom border-start-0 border-secondary-subtle" id="password" name="password" placeholder="Enter password" required style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <button type="submit" class="btn btn-custom-primary w-100 py-3 rounded-pill text-white fw-bold">Sign In</button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="text-decoration-none text-primary fw-semibold">Register Here</a></p>
        </div>
        
        <!-- Demo hint removed for production. See README.md for local setup instructions. -->
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotModal" tabindex="-1" aria-labelledby="forgotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="forgotModalLabel">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="actions/auth.php?action=forgot_password" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="modal-body">
                    <p class="small text-muted mb-3">Enter the email associated with your account. For this academic demonstration, we will reset your password to "<strong>reset123</strong>".</p>
                    <div class="mb-3">
                        <label for="forgot_email" class="form-label small fw-semibold text-secondary">Email Address</label>
                        <input type="email" class="form-control form-control-custom" id="forgot_email" name="email" placeholder="e.g. traveler@gmail.com" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom-primary rounded-pill px-4 text-white">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
