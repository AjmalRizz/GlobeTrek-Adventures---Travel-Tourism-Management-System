<?php
// Page Not Found
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';
?>
<div class="container py-5 d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="text-center">
        <i class="bi bi-geo-fill text-warning" style="font-size: 5rem;"></i>
        <h2 class="fw-bold mt-4">Page Not Found (404)</h2>
        <p class="text-muted">Oops! It looks like you've wandered off the trail. The page you are looking for does not exist.</p>
        <div class="mt-4">
            <a href="index.php" class="btn btn-custom-primary text-white px-4 py-2 rounded-pill"><i class="bi bi-arrow-left me-2"></i>Back to Home</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
