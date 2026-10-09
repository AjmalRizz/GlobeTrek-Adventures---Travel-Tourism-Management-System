<?php
// unauthorized.php - 403 Forbidden Page
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';
?>
<div class="container py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="text-center">
        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 5rem;"></i>
        <h2 class="fw-bold mt-4">Access Denied (403)</h2>
        <p class="text-muted">You do not have permission to access this page. Please make sure you are logged in with the correct role.</p>
        <div class="mt-4 d-flex justify-content-center gap-2">
            <a href="index.php" class="btn btn-custom-primary text-white">Go to Home</a>
            <a href="login.php" class="btn btn-outline-secondary">Log In</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
