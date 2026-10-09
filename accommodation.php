<?php
// Show partner hotels, resorts, and guest houses


require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';

$accommodations = [];
if ($pdo) {
    try {
        $accommodations = $pdo->query("SELECT * FROM accommodations ORDER BY rating DESC")->fetchAll();
    } catch (PDOException $e) {
        // graceful fallback
    }
}
?>

<!-- Accommodation Showcase -->
<!-- Accommodation Hero Section -->
<section class="page-hero-section accommodation-hero">
    <div class="container py-4 animated-item">
        <span class="badge page-hero-badge px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-house-door-fill me-1"></i> Partner Hotels</span>
        <h1 class="fw-bold mb-3 text-white" style="font-size: 3rem; letter-spacing:-0.02em;">Stay in Comfort</h1>
        <p class="page-hero-lead lead">Explore our curated selection of luxury beach resorts, premium hotels providing delicious seafood, and cozy local guest houses located across Sri Lanka.</p>
    </div>
</section>

<div class="container py-2 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Accommodations</li>
        </ol>
    </nav>

    <!-- Listings Grid -->
    <div class="row g-4">
        <?php if (!empty($accommodations)): ?>
            <?php foreach ($accommodations as $acc): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card custom-card h-100 d-flex flex-column">
                        <div class="overflow-hidden position-relative" style="height: 230px;">
                            <?php
                            $acc_fallbacks = [
                                'Resort'     => 'assets/images/jetwing_blue.jpg',
                                'Hotel'      => 'assets/images/heritance_negombo.jpg',
                                'Guest House'=> 'assets/images/lewisplace.png',
                            ];
                            $fallback = $acc_fallbacks[$acc['type']] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&q=80&w=800';
                            ?>
                            <img src="<?php echo htmlspecialchars($acc['image_path']); ?>" onerror="this.onerror=null;this.src='<?php echo $fallback; ?>'" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($acc['name']); ?>">

                            <span class="position-absolute top-0 start-0 bg-secondary text-white rounded-pill px-3 py-1 m-3 small fw-bold shadow-sm">
                                <?php echo htmlspecialchars($acc['type']); ?>
                            </span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small"><i class="bi bi-geo-alt-fill text-primary me-1"></i><?php echo htmlspecialchars($acc['location']); ?></span>
                                <div class="text-warning small d-flex align-items-center gap-1">
                                    <i class="bi bi-star-fill"></i>
                                    <span class="fw-bold text-dark small"><?php echo htmlspecialchars($acc['rating']); ?></span>
                                </div>
                            </div>
                            <h5 class="card-title fw-bold text-dark mt-1 mb-2"><?php echo htmlspecialchars($acc['name']); ?></h5>
                            <p class="card-text text-muted small mb-4" style="line-height:1.6; font-size:0.8rem;"><?php echo htmlspecialchars($acc['description']); ?></p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                                <div>
                                    <span class="text-muted small d-block" style="font-size:0.75rem;">Starts From</span>
                                    <span class="fs-5 fw-bold text-primary price-display" data-usd="<?php echo htmlspecialchars($acc['price_per_night']); ?>">$<?php echo number_format($acc['price_per_night'], 2); ?></span>
                                    <span class="text-muted small">/ night</span>
                                </div>
                                <a href="contact.php?subject=Inquiry%20regarding%20<?php echo urlencode($acc['name']); ?>" class="btn btn-sm btn-custom-secondary px-3 py-2">Inquire Stay</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted small">No partner accommodations found. Run database seeder first!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
