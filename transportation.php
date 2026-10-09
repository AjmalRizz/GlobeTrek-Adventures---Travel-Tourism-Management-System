<?php
// transportation.php - Showcase vehicle rental and airport transfers
require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';

$vehicles = [];
if ($pdo) {
    try {
        $vehicles = $pdo->query("SELECT * FROM transport_services WHERE type != 'Airport Transfer' ORDER BY price ASC")->fetchAll();
    } catch (PDOException $e) {
        // graceful fallback
    }
}
?>

<!-- Transport Hero Section -->
<section class="page-hero-section transport-hero">
    <div class="container py-4 animated-item">
        <span class="badge page-hero-badge px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-shield-check-fill me-1"></i> Secure Travel</span>
        <h1 class="fw-bold mb-3 text-white" style="font-size: 3rem; letter-spacing:-0.02em;">Local Transport Services</h1>
        <p class="page-hero-lead lead">From airport shuttles straight to your hotel to custom full-day hires with experienced English-speaking drivers, explore our transport fleet.</p>
    </div>
</section>

<div class="container py-2 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Transportation</li>
        </ol>
    </nav>

    <!-- Listings Grid -->
    <div class="row g-4">
        <?php if (!empty($vehicles)): ?>
            <?php foreach ($vehicles as $veh): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card custom-card h-100 d-flex flex-column">
                        <div class="overflow-hidden position-relative" style="height: 230px;">
                            <?php
                            // Always use reliable Unsplash images by vehicle type
                            $transport_images = [
                                'Airport Transfer' => 'assets\images\airport_transfer.jpg',
                                'Private Vehicle'  => 'assets\images\transport_car.png',
                                'Tour Bus'         => 'assets\images\coasterbus.jpg',
                                'Tuk-Tuk'          => 'assets\images\transport_tuktuk.png',
                            ];
                            $img_src = $transport_images[$veh['type']] ?? 'https://images.unsplash.com/photo-1544620347460-35ea508f7b9c?auto=format&fit=crop&q=80&w=800';
                            ?>
                            <img src="<?php echo $img_src; ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($veh['name']); ?>">

                            <span class="position-absolute top-0 start-0 bg-secondary text-white rounded-pill px-3 py-1 m-3 small fw-bold shadow-sm">
                                <?php echo htmlspecialchars($veh['type']); ?>
                            </span>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <span class="text-muted small mb-2"><i class="bi bi-people-fill text-primary me-1"></i>Max Capacity: <strong><?php echo htmlspecialchars($veh['capacity']); ?> passengers</strong></span>
                            <h5 class="card-title fw-bold text-dark mt-1 mb-2"><?php echo htmlspecialchars($veh['name']); ?></h5>
                            <p class="card-text text-muted small mb-4" style="line-height:1.6; font-size:0.8rem;"><?php echo htmlspecialchars($veh['description']); ?></p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                                <div>
                                    <span class="text-muted small d-block" style="font-size:0.75rem;">Price starting at</span>
                                    <span class="fs-5 fw-bold text-primary price-display" data-usd="<?php echo htmlspecialchars($veh['price']); ?>">$<?php echo number_format($veh['price'], 2); ?></span>
                                    <span class="text-muted small currency-suffix">USD</span>
                                </div>
                                <a href="contact.php?subject=Inquiry%20regarding%20<?php echo urlencode($veh['name']); ?>" class="btn btn-sm btn-custom-secondary px-3 py-2">Book Transfer</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted small">No transportation services found. Run database seeder first!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
