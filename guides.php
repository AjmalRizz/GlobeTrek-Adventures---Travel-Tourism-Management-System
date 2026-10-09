<?php
// Travel Articles & Guide Tips
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';
?>

<!-- Guides Hero Section -->
<section class="page-hero-section guides-hero">
    <div class="container py-4 animated-item">
        <span class="badge page-hero-badge px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-journal-richtext me-1"></i> GlobeTrek Blog</span>
        <h1 class="fw-bold mb-3 text-white" style="font-size: 3rem; letter-spacing:-0.02em;">Sri Lanka Travel Articles</h1>
        <p class="page-hero-lead lead">Expert tips, local histories, visit beautiful destinations, and comprehensive travel guides written by our coordinators.</p>
    </div>
</section>

<div class="container py-2 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Travel Guides</li>
        </ol>
    </nav>

    <!-- Guides List -->
    <div class="row g-4">
        <!-- Article 1: Negombo Dutch Canals -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 d-flex flex-column">
                <div class="overflow-hidden" style="height: 200px;">
                    <img src="assets/images/guide_canal.png" class="w-100 h-100" style="object-fit: cover;" alt="Negombo Dutch canals boat safari">
                </div>
                <div class="card-body p-4">
                    <span class="text-primary small fw-bold">Negombo History</span>
                    <h5 class="fw-bold text-dark mt-2 mb-2">Cruising the Historic Dutch Canals</h5>
                    <p class="text-muted small" style="font-size:0.8rem; line-height:1.6;">Negombo's canal network stretches over 100km, built by Portuguese and Dutch colonial forces to transport cinnamon. Today, it hosts scenic bird-watching boat safaris.</p>
                </div>
                <div class="card-footer bg-white border-top-0 px-4 pb-4 mt-auto">
                    <span class="small text-muted"><i class="bi bi-calendar-event me-2"></i>June 5, 2026</span>
                </div>
            </div>
        </div>

        <!-- Article 2: Sigiriya Climbing Guide -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 d-flex flex-column">
                <div class="overflow-hidden" style="height: 200px;">
                    <img src="assets/images/guide_sigiriya.png" class="w-100 h-100" style="object-fit: cover;" alt="Sigiriya Lion Rock Sri Lanka">
                </div>
                <div class="card-body p-4">
                    <span class="text-secondary small fw-bold">Cultural Triangle</span>
                    <h5 class="fw-bold text-dark mt-2 mb-2">Climbing Sigiriya Lion Rock: A Guide</h5>
                    <p class="text-muted small" style="font-size:0.8rem; line-height:1.6;">Ascending the 1,200 steps to the summit of King Kashyapa's palace fortress. Tips on early morning climbing, avoiding hornets, and taking stunning sunset photos.</p>
                </div>
                <div class="card-footer bg-white border-top-0 px-4 pb-4 mt-auto">
                    <span class="small text-muted"><i class="bi bi-calendar-event me-2"></i>May 28, 2026</span>
                </div>
            </div>
        </div>

        <!-- Article 3: Negombo Seafood Guide -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 d-flex flex-column">
                <div class="overflow-hidden" style="height: 200px;">
                    <img src="assets/images/guide_seafood.png" class="w-100 h-100" style="object-fit: cover;" alt="Sri Lankan seafood cuisine">
                </div>
                <div class="card-body p-4">
                    <span class="text-accent small fw-bold">Local Cuisines</span>
                    <h5 class="fw-bold text-dark mt-2 mb-2">Negombo Seafood Cuisines to Try</h5>
                    <p class="text-muted small" style="font-size:0.8rem; line-height:1.6;">From Lagoon Crab curry to spicy butter cuttlefish and fish ambul thiyal. A guide to eating authentic food along Lewis Place road restaurants in Negombo.</p>
                </div>
                <div class="card-footer bg-white border-top-0 px-4 pb-4 mt-auto">
                    <span class="small text-muted"><i class="bi bi-calendar-event me-2"></i>May 15, 2026</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
