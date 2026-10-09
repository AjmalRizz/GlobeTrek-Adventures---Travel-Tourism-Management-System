<?php
//  GlobeTrek Adventures Homepage
require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';

$featured_packages = [];
if ($pdo) {
    try {
        // Fetch 3 featured packages
        $stmt = $pdo->query("SELECT tp.*, pi.image_path FROM travel_packages tp 
                             LEFT JOIN package_images pi ON tp.id = pi.package_id AND pi.is_featured = 1
                             WHERE tp.status = 'available' ORDER BY tp.id DESC LIMIT 3");
        $featured_packages = $stmt->fetchAll();
    } catch (PDOException $e) {
        // graceful fallback
    }
}
?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'logged_out'): ?>
    <div class="container pt-4">
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-box-arrow-right me-2"></i> <strong>Successfully Logged Out.</strong> See you again soon!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Hero Banner Section -->
<section class="hero-section text-center text-lg-start d-flex align-items-center">
    <div class="hero-bg-overlay"></div>
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 animated-item">
                <span class="badge hero-badge-unique px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-geo-alt-fill me-1"></i> Gateway to Sri Lanka</span>
                <h1 class="hero-title mb-4">Discover the Golden Coast of <span class="hero-title-gradient">Negombo</span> & Beyond</h1>
                <p class="lead hero-lead-text mb-4">Immerse yourself in heritage canal boat safaris, ancient lion rocks, and pristine sandy beaches. Tailor your dream Sri Lankan escape with local travel experts.</p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="packages.php" class="btn btn-custom-primary text-white px-4 py-3 rounded-pill fw-bold text-center"><i class="bi bi-compass-fill me-2"></i>Explore Packages</a>
                    <a href="contact.php" class="btn btn-outline-light px-4 py-3 rounded-pill fw-semibold text-center"><i class="bi bi-chat-left-dots-fill me-2"></i>Consult Travel Staff</a>
                </div>
            </div>
            <div class="col-lg-6 animated-item">
                <div class="position-relative">
                    <!-- Modern HQ image wrapper -->
                    <div class="hero-img-wrapper overflow-hidden">
                        <img src="assets/images/kandy_city_hq.png" alt="Kandy City Sri Lanka Golden Temple and Lake" class="img-fluid w-100">
                    </div>
                    <div class="position-absolute bottom-0 start-0 hero-floating-glass-card p-3 rounded-4 m-3 d-flex align-items-center gap-2">
                        <div class="bg-warning-subtle text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:42px; height:42px;">
                            <i class="bi bi-star-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-white">4.8 / 5.0 Rating</h6>
                            <span class="small" style="font-size:0.75rem; color: #cbd5e1;">Trusted by 1000+ Travelers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Destinations Section -->

<!-- Promotional Banners Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark section-title-premium">Special Sri Lankan Tour Deals</h2>
            <p class="text-muted text-subtitle-premium">Grab our limited-time promotions and customized itinerary discounts</p>
        </div>
        
        <div class="asymmetric-promo-grid">
            <!-- Promotion Banner 1: Gampola Ambuluwawa Tower -->
            <div class="promo-banner-item active-first position-relative overflow-hidden rounded-4 shadow-sm">
                <img src="assets/images/ambuluwawa_tower_hq.png" alt="Gampola Ambuluwawa Tower Promo" class="promo-img w-100 h-100" style="object-fit: cover;">
                <div class="promo-overlay d-flex flex-column justify-content-between p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold text-white shadow-sm"><i class="bi bi-fire me-1"></i>HOT DEAL - 15% OFF</span>
                    </div>
                    <div class="promo-details mt-auto">
                        <h3 class="fw-bold text-white mb-2">Gampola Ambuluwawa Tower</h3>
                        <p class="text-white-50 small mb-3 promo-desc">Climb the iconic spiral tower on Ambuluwawa peak for breathtaking 360° panoramic views of Sri Lanka's central highlands &amp; biodiversity complex.</p>
                        <div class="promo-action-row d-flex align-items-center justify-content-between">
                            <span class="fs-4 fw-extrabold text-warning">$180.00 <del class="fs-6 text-white-50 fw-normal">$220.00</del></span>
                            <a href="contact.php?subject=Inquiry%20regarding%20Ambuluwawa%20Tower%20Promo" class="btn btn-sm btn-light rounded-pill px-4 py-2 fw-bold text-primary">Claim Offer <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotion Banner 2: Trincomalee Nilaveli Beach -->
            <div class="promo-banner-item position-relative overflow-hidden rounded-4 shadow-sm">
                <img src="assets/images/nilaveli_beach_hq.png" alt="Trincomalee Nilaveli Beach Promo" class="promo-img w-100 h-100" style="object-fit: cover;">
                <div class="promo-overlay d-flex flex-column justify-content-between p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm"><i class="bi bi-star-fill me-1"></i>BEST SELLER - PIGEON ISLAND</span>
                    </div>
                    <div class="promo-details mt-auto">
                        <h3 class="fw-bold text-white mb-2">Trincomalee Nilaveli Beach</h3>
                        <p class="text-white-50 small mb-3 promo-desc">Dive into crystal-clear turquoise waters, pristine white sand, coral reef snorkeling &amp; Pigeon Island boat excursions.</p>
                        <div class="promo-action-row d-flex align-items-center justify-content-between">
                            <span class="fs-4 fw-extrabold text-warning">$290.00 <del class="fs-6 text-white-50 fw-normal">$340.00</del></span>
                            <a href="contact.php?subject=Inquiry%20regarding%20Trincomalee%20Nilaveli%20Promo" class="btn btn-sm btn-light rounded-pill px-4 py-2 fw-bold text-primary">Claim Offer <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotion Banner 3: Colombo Galle Face Beach -->
            <div class="promo-banner-item position-relative overflow-hidden rounded-4 shadow-sm">
                <img src="assets/images/colombo_galle_face_hq.png" alt="Colombo Galle Face Beach Promo" class="promo-img w-100 h-100" style="object-fit: cover;">
                <div class="promo-overlay d-flex flex-column justify-content-between p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="badge bg-info text-white rounded-pill px-3 py-2 fw-bold shadow-sm"><i class="bi bi-bookmark-star-fill me-1"></i>EXCLUSIVE - CITY &amp; OCEAN</span>
                    </div>
                    <div class="promo-details mt-auto">
                        <h3 class="fw-bold text-white mb-2">Colombo Galle Face Walk</h3>
                        <p class="text-white-50 small mb-3 promo-desc">Stroll the famous oceanfront promenade at golden hour, savor street food delicacies &amp; enjoy modern seaside skyline views.</p>
                        <div class="promo-action-row d-flex align-items-center justify-content-between">
                            <span class="fs-4 fw-extrabold text-warning">$210.00 <del class="fs-6 text-white-50 fw-normal">$250.00</del></span>
                            <a href="contact.php?subject=Inquiry%20regarding%20Colombo%20Galle%20Face%20Promo" class="btn btn-sm btn-light rounded-pill px-4 py-2 fw-bold text-primary">Claim Offer <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Packages Section -->
<section class="py-5 section-packages-dark">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5">
            <div class="text-center text-md-start mb-3 mb-md-0">
                <h2 class="fw-bold pkg-section-heading mb-1">Featured Tour Packages</h2>
                <p class="pkg-section-sub mb-0">Curated itineraries with exceptional pricing for maximum adventures</p>
            </div>
            <a href="packages.php" class="btn btn-pkg-outline mt-3 mt-md-0">View All Packages<i class="bi bi-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_packages)): ?>
                <?php foreach ($featured_packages as $pkg): ?>
                    <div class="col-md-4">
                        <div class="pkg-dark-card h-100 d-flex flex-column">
                            <div class="position-relative overflow-hidden pkg-img-wrap">
                                <img src="<?php echo htmlspecialchars($pkg['image_path']); ?>" onerror="this.src='https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&q=80&w=800'" class="w-100 h-100 pkg-img" style="object-fit: cover;" alt="<?php echo htmlspecialchars($pkg['title']); ?>">
                                <div class="pkg-img-fade"></div>
                                <span class="pkg-days-badge">
                                    <?php echo htmlspecialchars($pkg['duration_days']); ?> Days
                                </span>
                            </div>
                            <div class="pkg-dark-body p-4 d-flex flex-column flex-grow-1">
                                <span class="pkg-dest-tag"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($pkg['destination']); ?></span>
                                <h5 class="pkg-title mt-2 mb-3"><?php echo htmlspecialchars($pkg['title']); ?></h5>
                                <p class="pkg-desc mb-4"><?php echo htmlspecialchars(mb_strimwidth($pkg['description'], 0, 120, "...")); ?></p>
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-3 pkg-footer-line">
                                    <div>
                                        <span class="pkg-price-label d-block">Price / Person</span>
                                        <span class="pkg-price price-display" data-usd="<?php echo htmlspecialchars($pkg['price']); ?>">$<?php echo number_format($pkg['price'], 2); ?></span>
                                    </div>
                                    <a href="package_details.php?id=<?php echo $pkg['id']; ?>" class="btn btn-pkg-glow px-3 py-2 rounded-pill small fw-semibold">View Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="pkg-section-sub">No packages seeded yet. Please run the installer script!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 section-why-dark">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Why Choose GlobeTrek</h2>
            <p class="text-muted">We provide unmatched local service and professional tour coordination in Sri Lanka</p>
        </div>

        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 why-card-dark text-center h-100">
                    <div class="bg-primary-light text-primary rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size:1.75rem;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Local Expertise</h5>
                    <p class="text-muted small mb-0">Our local coordinators possess unmatched insights on Negombo routes and hidden gems.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 why-card-dark text-center h-100">
                    <div class="bg-success-subtle text-success rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size:1.75rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Transparent Pricing</h5>
                    <p class="text-muted small mb-0">No hidden fees. Every package outlines exact price breakdowns, inclusive/exclusive details.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 why-card-dark text-center h-100">
                    <div class="bg-warning-subtle text-warning rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size:1.75rem;">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Tailored Trip Plans</h5>
                    <p class="text-muted small mb-0">Customize your destination, accommodation category, and transportation limits in real-time.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="p-4 rounded-4 why-card-dark text-center h-100">
                    <div class="bg-info-subtle text-info rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size:1.75rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Safe & Protected</h5>
                    <p class="text-muted small mb-0">Highly vetted transport vehicles and resorts. Multi-tiered security and RBAC protection.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 section-testimonials-bg">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Traveler Reviews</h2>
            <p class="text-muted">Read feedback from our global adventurers who stayed in Negombo Beach</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card testimonial-card-glass border-0 p-4 rounded-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="testimonial-avatar rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width:50px; height:50px;">JD</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Jane Doe</h6>
                            <span class="text-muted small">Traveler from USA</span>
                        </div>
                    </div>
                    <div class="mb-2 text-warning">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                    </div>
                    <p class="text-muted small mb-0">"Our Sigiriya and Dambulla tour was flawlessly coordinated! GlobeTrek Adventures picked us up from the airport and checking in to Jetwing Blue was seamless. Will definitely book again!"</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card testimonial-card-glass border-0 p-4 rounded-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="testimonial-avatar rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width:50px; height:50px;">MK</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Mark Kruis</h6>
                            <span class="text-muted small">Traveler from Germany</span>
                        </div>
                    </div>
                    <div class="mb-2 text-warning">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                    </div>
                    <p class="text-muted small mb-0">"We booked a custom trip package. Their travel coordinator was very responsive and customized our transport preferences to a private luxury SUV. Perfect guides."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 section-cta-bg text-white text-center position-relative">
    <div class="container py-4 position-relative">
        <h2 class="fw-bold mb-3">Start Your Next Sri Lankan Adventure Today</h2>
        <p class="mb-4 text-white-50 max-width-md mx-auto">Create a traveler profile, browse our catalog of customized tour packages, and book with ease.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="register.php" class="btn btn-light text-primary rounded-pill px-4 py-2 fw-semibold">Sign Up Free</a>
            <a href="packages.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">Explore Tours</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
