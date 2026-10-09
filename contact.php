<?php
// contact.php - Contact Details & Inquiry Submission
require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';

$logged_user = null;
if (is_logged_in() && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $logged_user = $stmt->fetch();
    } catch (PDOException $e) {
        // fallback
    }
}

// Check for parameters passed from lists (e.g. inquiry about hotel)
$prefill_subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';

$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'inquiry_submitted') {
        $success_msg = 'Your inquiry has been submitted successfully! Our staff will get back to you shortly.';
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $error_msg = 'Please fill in all the required form fields.';
    } elseif ($_GET['error'] === 'invalid_email') {
        $error_msg = 'Please provide a valid email address.';
    } elseif ($_GET['error'] === 'db_error') {
        $error_msg = 'A database error occurred. Please try again later.';
    }
}
?>

<!-- Contact Hero Section -->
<section class="page-hero-section contact-hero">
    <div class="container py-4 animated-item">
        <span class="badge page-hero-badge px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-chat-left-dots-fill me-1"></i> Get In Touch</span>
        <h1 class="fw-bold mb-3 text-white" style="font-size: 3rem; letter-spacing:-0.02em;">Contact GlobeTrek Adventures</h1>
        <p class="page-hero-lead lead">We are here to help you plan your dream trip to Sri Lanka. Send us an inquiry or visit our office in Negombo.</p>
    </div>
</section>

<div class="container py-2 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Contact Form Column (col-md-7) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                <h3 class="fw-bold text-dark mb-2">Send an Inquiry</h3>
                <p class="text-muted small mb-4">Have questions about tour plans, hotel listings, or transport options? Submit your inquiry details below.</p>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4 small py-2" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4 small py-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>

                <form action="actions/inquiry.php" method="POST" id="inquiryForm">
                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label small fw-semibold text-secondary">Full Name</label>
                            <input type="text" class="form-control form-control-custom" id="name" name="name" 
                                   value="<?php echo $logged_user ? htmlspecialchars($logged_user['full_name']) : ''; ?>" placeholder="e.g. Jane Doe" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" 
                                   value="<?php echo $logged_user ? htmlspecialchars($logged_user['email']) : ''; ?>" placeholder="e.g. traveler@gmail.com" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label small fw-semibold text-secondary">Phone Number (Optional)</label>
                            <input type="tel" class="form-control form-control-custom" id="phone" name="phone" 
                                   value="<?php echo $logged_user ? htmlspecialchars($logged_user['phone']) : ''; ?>" placeholder="e.g. +94771234567">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label small fw-semibold text-secondary">Subject</label>
                            <input type="text" class="form-control form-control-custom" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($prefill_subject); ?>" placeholder="e.g. Booking Inquiry" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label small fw-semibold text-secondary">Your Message</label>
                        <textarea class="form-control form-control-custom" id="message" name="message" rows="5" placeholder="Write your questions or special tour requirements here..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-custom-primary text-white rounded-pill px-4 py-2 fw-bold">Submit Inquiry</button>
                </form>
            </div>
        </div>

        <!-- Contact Info & Map Column (col-md-5) -->
        <div class="col-lg-5">
            <!-- Details Card -->
            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white mb-4">
                <h4 class="fw-bold text-dark mb-4">GlobeTrek HQ</h4>
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-primary-light text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:44px; height:44px; font-size:1.2rem;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Office Address</h6>
                            <span class="text-muted small">123 Lewis Place, Negombo, Sri Lanka</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-secondary-light text-secondary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:44px; height:44px; font-size:1.2rem;">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Phone Numbers</h6>
                            <span class="text-muted small">+94 31 222 1234<br>+94 77 123 4567</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-warning-subtle text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:44px; height:44px; font-size:1.2rem;">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Addresses</h6>
                            <span class="text-muted small">info@globetrek.com<br>support@globetrek.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bigger Google Map -->
            <div class="rounded-4 overflow-hidden border border-light shadow-sm" style="height: 300px;">
                <iframe 
                    src="https://maps.google.com/maps?q=Negombo,%20Sri%20Lanka&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
