<?php
// about.php - About Us Page
require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';
?>

<!-- About Hero Section -->
<section class="about-hero-section text-center d-flex align-items-center">
    <div class="container py-4 animated-item">
        <span class="badge about-badge px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-info-circle-fill me-1"></i> About GlobeTrek</span>
        <h1 class="fw-bold mb-3 text-white" style="font-size: 3.2rem; letter-spacing:-0.02em;">Our Journey, Mission & Team</h1>
        <p class="about-lead max-width-md mx-auto lead w-100" style="max-width: 700px;">GlobeTrek Adventures is a premier travel agency based in Negombo, Sri Lanka. Established to showcase the stunning landscapes, diverse cultures, and warm hospitality of the teardrop island.</p>
    </div>
</section>

<!-- Company Introduction & Mission/Vision -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark mb-3">Welcome to GlobeTrek Adventures</h2>
            <p class="text-muted mx-auto text-center w-100" style="line-height:1.8; width: 900px;">Located strategically in Negombo, a historic coastal city just minutes away from Bandaranaike International Airport, we are optimally positioned to greet incoming travelers. We offer customized island tours, transport logs, and beach resort bookings.</p>
            <p class="text-muted mx-auto text-center w-100" style="line-height:1.8; width: 900px;">From ancient historical landmarks in the Cultural Triangle to wildlife excursions in Yala National Park, we strive to render every trip unforgettable, combining safety, comfort, and local authenticity.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white h-100 text-center">
                    <div class="mb-3"><i class="bi bi-bullseye text-primary" style="font-size:2rem;"></i></div>
                    <h5 class="fw-bold text-primary mb-2">Our Mission</h5>
                    <p class="text-muted small mb-0">To connect travelers with local Sri Lankan experiences by providing reliable, high-quality, and secure travel solutions that enrich spirits and support local communities.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white h-100 text-center">
                    <div class="mb-3"><i class="bi bi-eye-fill text-secondary" style="font-size:2rem;"></i></div>
                    <h5 class="fw-bold text-secondary mb-2">Our Vision</h5>
                    <p class="text-muted small mb-0">To be the leading adventure-inspired travel coordination system in Sri Lanka, recognized for service innovation, safety, and sustainable tourism practices.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white h-100 text-center">
                    <div class="mb-3"><i class="bi bi-gem text-accent" style="font-size:2rem;"></i></div>
                    <h5 class="fw-bold text-accent mb-2">Our Core Values</h5>
                    <p class="text-muted small mb-0">Integrity, customer safety first, local community empowerment, transparent billing, and a commitment to detail.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Overview Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Our Leadership Team</h2>
            <p class="text-muted">Dedicated travel consultants and software engineers managing operations in Negombo</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Coordinator 1 -->
            <div class="col-md-4 col-sm-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width:140px;height:140px;border-radius:50%;overflow:hidden;border:4px solid #0cadda;box-shadow:0 8px 24px rgba(12,173,218,0.18);">
                        <img src="assets/images/team_rohan_silva.png"
                             alt="Rohan Silva"
                             style="width:100%;height:100%;object-fit:cover;object-position:top;display:block;">
                    </div>
                    <h5 class="fw-bold mb-1">Rohan Silva</h5>
                    <p class="text-primary small fw-semibold mb-2">Founder &amp; Managing Director</p>
                    <p class="text-muted small px-3">Over 15 years of tour operations experience in Negombo. Rohan oversees global agency growth.</p>
                </div>
            </div>

            <!-- Coordinator 2 -->
            <div class="col-md-4 col-sm-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width:140px;height:140px;border-radius:50%;overflow:hidden;border:4px solid #14b8a6;box-shadow:0 8px 24px rgba(20,184,166,0.18);">
                        <img src="assets/images/team_sunil_perera.png"
                             alt="Sunil Perera"
                             style="width:100%;height:100%;object-fit:cover;object-position:top;display:block;">
                    </div>
                    <h5 class="fw-bold mb-1">Sunil Perera</h5>
                    <p class="text-secondary small fw-semibold mb-2">Senior Tour Coordinator</p>
                    <p class="text-muted small px-3">Sunil manages custom trip evaluations, bookings coordination, and staff dashboard operations.</p>
                </div>
            </div>

            <!-- Coordinator 3 -->
            <div class="col-md-4 col-sm-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width:140px;height:140px;border-radius:50%;overflow:hidden;border:4px solid #f59e0b;box-shadow:0 8px 24px rgba(245,158,11,0.18);">
                        <img src="assets/images/team_anura_fernando.png"
                             alt="Anura Fernando"
                             style="width:100%;height:100%;object-fit:cover;object-position:top;display:block;">
                    </div>
                    <h5 class="fw-bold mb-1">Anura Fernando</h5>
                    <p class="text-accent small fw-semibold mb-2">Lead Chauffeur &amp; Guide</p>
                    <p class="text-muted small px-3">Highly trained driver fluent in English and German. Anura coordinates all transportation logs.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
