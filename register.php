<?php
// register.php - User Registration Form
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

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields': $error_msg = 'Please fill in all the required fields.'; break;
        case 'invalid_email': $error_msg = 'Please provide a valid email address.'; break;
        case 'password_mismatch': $error_msg = 'Passwords do not match.'; break;
        case 'password_weak': $error_msg = 'Password is too weak. It must be at least 6 characters long.'; break;
        case 'email_exists': $error_msg = 'An account with this email address already exists.'; break;
        case 'db_error': $error_msg = 'A system error occurred. Please try again later.'; break;
    }
}
?>

<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card border-0 shadow-lg p-4 rounded-4 bg-white" style="width: 100%; max-width: 500px;">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus text-primary fs-1"></i>
            <h3 class="fw-bold mt-2 mb-1">Create Account</h3>
            <p class="text-muted small">Register now to book tours and manage custom plans</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3 small py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="actions/auth.php?action=register" method="POST" id="regForm" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="full_name" class="form-label small fw-semibold text-secondary">Full Name</label>
                <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" placeholder="e.g. Jane Doe" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="e.g. traveler@gmail.com" required>
                <div class="invalid-feedback small">Please provide a valid email format.</div>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label small fw-semibold text-secondary">Phone Number</label>
                <input type="tel" class="form-control form-control-custom" id="phone" name="phone" placeholder="e.g. +94771234567" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="At least 6 characters" required onkeyup="checkPasswordStrength()">
                <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                    <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="strengthText" class="form-text small text-muted mt-1">Strength: Weak</div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label small fw-semibold text-secondary">Confirm Password</label>
                <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required onkeyup="checkPasswordMatch()">
                <div id="matchText" class="form-text small mt-1"></div>
            </div>

            <button type="submit" class="btn btn-custom-primary w-100 py-3 rounded-pill text-white fw-bold">Register Now</button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-decoration-none text-primary fw-semibold">Sign In</a></p>
        </div>
    </div>
</div>

<script>
function checkPasswordStrength() {
    const pwd = document.getElementById('password').value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let strength = 0;
    
    if (pwd.length >= 6) strength += 25;
    if (pwd.match(/[a-z]+/)) strength += 25;
    if (pwd.match(/[A-Z]+/)) strength += 25;
    if (pwd.match(/[0-9]+/)) strength += 25;
    
    bar.style.width = strength + '%';
    if (strength <= 25) {
        bar.className = 'progress-bar bg-danger';
        text.innerHTML = 'Strength: Weak (Min 6 characters)';
        text.className = 'form-text small text-danger mt-1';
    } else if (strength <= 75) {
        bar.className = 'progress-bar bg-warning';
        text.innerHTML = 'Strength: Medium';
        text.className = 'form-text small text-warning mt-1';
    } else {
        bar.className = 'progress-bar bg-success';
        text.innerHTML = 'Strength: Strong';
        text.className = 'form-text small text-success mt-1';
    }
}

function checkPasswordMatch() {
    const pwd = document.getElementById('password').value;
    const cpwd = document.getElementById('confirm_password').value;
    const text = document.getElementById('matchText');
    
    if (cpwd.length === 0) {
        text.innerHTML = '';
        return;
    }
    
    if (pwd === cpwd) {
        text.innerHTML = 'Passwords match';
        text.className = 'form-text small text-success mt-1';
    } else {
        text.innerHTML = 'Passwords do not match';
        text.className = 'form-text small text-danger mt-1';
    }
}

function validateForm() {
    const pwd = document.getElementById('password').value;
    const cpwd = document.getElementById('confirm_password').value;
    const email = document.getElementById('email').value;
    
    if (pwd.length < 6) {
        alert('Password must be at least 6 characters long.');
        return false;
    }
    if (pwd !== cpwd) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}
</script>

<?php require_once 'includes/footer.php'; ?>
