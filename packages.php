<?php
// packages.php - Tour Packages Catalog
require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$dest_filter = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$duration = isset($_GET['duration']) ? intval($_GET['duration']) : 0;
$activity = isset($_GET['activity']) ? trim($_GET['activity']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

$packages = [];
if ($pdo) {
    try {
        $sql = "SELECT tp.*, pi.image_path FROM travel_packages tp 
                LEFT JOIN package_images pi ON tp.id = pi.package_id AND pi.is_featured = 1 
                WHERE tp.status = 'available'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (tp.title LIKE ? OR tp.destination LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($dest_filter)) {
            $sql .= " AND tp.destination LIKE ?";
            $params[] = "%$dest_filter%";
        }

        if ($max_price > 0) {
            $sql .= " AND tp.price <= ?";
            $params[] = $max_price;
        }

        if ($duration > 0) {
            $sql .= " AND tp.duration_days <= ?";
            $params[] = $duration;
        }

        if (!empty($activity)) {
            $sql .= " AND tp.activities LIKE ?";
            $params[] = "%$activity%";
        }

        // Sorting
        if ($sort === 'price_asc') {
            $sql .= " ORDER BY tp.price ASC";
        } elseif ($sort === 'price_desc') {
            $sql .= " ORDER BY tp.price DESC";
        } elseif ($sort === 'duration') {
            $sql .= " ORDER BY tp.duration_days ASC";
        } else {
            $sql .= " ORDER BY tp.id DESC"; // default newest
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $packages = $stmt->fetchAll();
    } catch (PDOException $e) {
        // graceful fallback
    }
}
?>

<style>
/* Scoped styles for Packages Catalog page to optimize spacing and alignment */
.packages-page-container {
    padding-top: 2rem !important;
    padding-bottom: 5rem !important;
}

.packages-main-row {
    row-gap: 32px !important;
}

.packages-grid-row {
    row-gap: 32px !important;
}

/* Sidebar Custom Styling */
.sidebar {
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px !important;
    padding: 28px 22px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
}

.sidebar h5 {
    font-size: 1.15rem !important;
    border-bottom: 2px solid var(--primary-light);
    padding-bottom: 12px !important;
    margin-bottom: 24px !important;
}

.form-control-custom {
    border-radius: 12px !important;
    padding: 12px 16px !important;
    font-size: 0.9rem !important;
    border: 1.5px solid var(--border) !important;
}

.form-label {
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    margin-bottom: 8px !important;
    color: var(--text-muted) !important;
}

.sidebar-buttons {
    margin-top: 28px;
    display: flex;
    gap: 12px;
}

.sidebar-buttons .btn {
    padding: 12px 20px !important;
    font-size: 0.9rem !important;
    font-weight: 600 !important;
    border-radius: 12px !important;
    height: 48px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Glassmorphic Package Card Redesign */
.pkg-catalog-card {
    background: var(--surface) !important;
    border: 1px solid var(--border) !important;
    border-radius: 20px !important;
    overflow: hidden;
    /* Only transition transform — box-shadow and border-color cause CPU repaints on scroll */
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.35s ease !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
    position: relative;
    /* Promote card to its own GPU compositing layer to avoid repaints during scroll */
    will-change: transform;
    /* Contain layout changes within each card to avoid full-page reflows */
    contain: layout style;
}

.pkg-catalog-card:hover {
    transform: translateY(-8px) !important;
    border-color: rgba(12, 173, 218, 0.4) !important;
    /* Use a pseudo-element for shadow so it doesn't repaint the card itself */
    box-shadow: 0 20px 40px rgba(12, 173, 218, 0.12) !important;
}

.pkg-catalog-img-wrap {
    height: 220px;
    position: relative;
    overflow: hidden;
}

.pkg-catalog-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.pkg-catalog-card:hover .pkg-catalog-img {
    transform: scale(1.08);
}

.pkg-catalog-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.35) 0%, transparent 60%);
    pointer-events: none;
}

.pkg-badge-duration {
    position: absolute;
    top: 14px;
    left: 14px;
    /* Replaced backdrop-filter: blur with solid color — blur repaints every card on scroll */
    background: rgba(10, 17, 35, 0.82) !important;
    color: #ffffff !important;
    border-radius: 50px !important;
    padding: 5px 12px !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.02em;
    border: 1px solid rgba(255, 255, 255, 0.18);
    z-index: 2;
}

.pkg-badge-rating {
    position: absolute;
    top: 14px;
    right: 14px;
    /* Replaced backdrop-filter: blur with solid color — blur repaints every card on scroll */
    background: rgba(10, 17, 35, 0.82) !important;
    color: #ffffff !important;
    border-radius: 50px !important;
    padding: 5px 12px !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    border: 1px solid rgba(255, 255, 255, 0.18);
    z-index: 2;
}

.pkg-catalog-body {
    padding: 22px !important;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.pkg-dest-pill {
    display: inline-flex;
    align-items: center;
    background: rgba(20, 184, 166, 0.1);
    color: var(--secondary) !important;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 4px 12px;
    border-radius: 50px;
    margin-bottom: 10px;
    width: fit-content;
}

.pkg-catalog-title {
    font-size: 1.15rem !important;
    font-weight: 800 !important;
    line-height: 1.4 !important;
    margin-bottom: 10px !important;
    color: var(--text) !important;
    min-height: 48px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pkg-catalog-desc {
    font-size: 0.85rem !important;
    line-height: 1.6 !important;
    color: var(--text-muted) !important;
    margin-bottom: 20px !important;
    min-height: 60px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pkg-catalog-footer {
    border-top: 1px solid var(--border) !important;
    padding-top: 16px !important;
    margin-top: auto !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.pkg-price-amount {
    font-size: 1.35rem !important;
    font-weight: 800 !important;
    color: var(--primary) !important;
    line-height: 1 !important;
}

.btn-pkg-details {
    padding: 9px 20px !important;
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    border-radius: 50px !important;
    background: linear-gradient(135deg, #0cadda 0%, #0d9488 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(12, 173, 218, 0.25) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
    border: none !important;
}

.btn-pkg-details:hover {
    background: linear-gradient(135deg, #0bb8e8 0%, #0ea5e9 100%) !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(12, 173, 218, 0.4) !important;
}
/* Background Wallpaper Wrapper for Packages Catalog */
.packages-page-wrapper {
    position: relative;
    /*
     * PERFORMANCE FIX: Changed `fixed` → `scroll` background-attachment.
     * `background-attachment: fixed` (parallax) forces the browser to repaint
     * the entire background on every single scroll event — the #1 cause of jank.
     * Using `scroll` lets the GPU composite the layer without CPU repaints.
     */
    background: linear-gradient(135deg, rgba(248, 250, 252, 0.90) 0%, rgba(241, 245, 249, 0.93) 100%),
                url('assets/images/hero_bg_wallpaper_hq.png') center center / cover scroll no-repeat;
}

[data-theme="dark"] .packages-page-wrapper {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(10, 16, 32, 0.95) 100%),
                url('assets/images/hero_bg_wallpaper_hq.png') center center / cover scroll no-repeat;
}
</style>

<!-- Packages Hero Section -->
<section class="page-hero-section packages-hero">
    <div class="container py-4 animated-item">
        <span class="badge page-hero-badge px-3 py-2 rounded-pill fw-bold mb-3"><i class="bi bi-compass-fill me-1"></i> Tour Packages</span>
        <h1 class="fw-bold mb-3 text-white" style="font-size: 3rem; letter-spacing:-0.02em;">Explore Our Curated Packages</h1>
        <p class="page-hero-lead lead">Discover the ancient ruins, mist-shrouded tea fields, wildlife safaris, and sun-kissed beaches of Sri Lanka with our premium tour itineraries.</p>
    </div>
</section>

<div class="packages-page-wrapper">
<div class="container py-2 animated-item packages-page-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tour Packages</li>
        </ol>
    </nav>

    <div class="row g-4 packages-main-row">
        <!-- Catalog Area (col-12 Full Width) -->
        <div class="col-12">
            <!-- Header Catalog Row -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
                <span class="text-muted small">Showing <strong class="text-dark"><?php echo count($packages); ?></strong> verified tour packages</span>
                
                <!-- Sort Select -->
                <div class="d-flex align-items-center gap-2" style="max-width: 250px;">
                    <label for="sortSelect" class="small text-nowrap fw-semibold text-secondary mb-0">Sort By:</label>
                    <select class="form-select form-select-sm border-secondary-subtle rounded-pill" id="sortSelect" onchange="applySort(this.value)">
                        <option value="" <?php echo $sort === '' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="duration" <?php echo $sort === 'duration' ? 'selected' : ''; ?>>Duration (Days)</option>
                    </select>
                </div>
            </div>

            <!-- Packages Grid -->
            <div class="row g-4 packages-grid-row">
                <?php if (!empty($packages)): ?>
                    <?php foreach ($packages as $pkg): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card pkg-catalog-card h-100 d-flex flex-column">
                                <div class="pkg-catalog-img-wrap">
                                    <img src="<?php echo htmlspecialchars($pkg['image_path']); ?>" onerror="this.src='https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&q=80&w=800'" class="pkg-catalog-img" alt="<?php echo htmlspecialchars($pkg['title']); ?>">
                                    <div class="pkg-catalog-overlay"></div>
                                    <span class="pkg-badge-duration">
                                        <i class="bi bi-clock-fill me-1"></i><?php echo htmlspecialchars($pkg['duration_days']); ?> Days
                                    </span>
                                    <span class="pkg-badge-rating">
                                        <i class="bi bi-star-fill text-warning me-1"></i>4.9
                                    </span>
                                </div>
                                <div class="pkg-catalog-body">
                                    <span class="pkg-dest-pill"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($pkg['destination']); ?></span>
                                    <h5 class="pkg-catalog-title"><?php echo htmlspecialchars($pkg['title']); ?></h5>
                                    <p class="pkg-catalog-desc"><?php echo htmlspecialchars(mb_strimwidth($pkg['description'], 0, 120, "...")); ?></p>
                                    
                                    <div class="pkg-catalog-footer">
                                        <div>
                                            <span class="small text-muted d-block" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.04em;">Price / Person</span>
                                            <span class="pkg-price-amount price-display" data-usd="<?php echo htmlspecialchars($pkg['price']); ?>">$<?php echo number_format($pkg['price'], 2); ?></span>
                                        </div>
                                        <a href="package_details.php?id=<?php echo $pkg['id']; ?>" class="btn btn-pkg-details">Explore Details <i class="bi bi-arrow-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart text-muted" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark mt-3">No Tour Packages Found</h5>
                        <p class="text-muted small">Check back soon for new Sri Lankan tour itineraries!</p>
                        <a href="packages.php" class="btn btn-custom-secondary mt-2">View All Packages</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function applySort(sortVal) {
    // Build URL from current query params, replacing/adding the sort value
    var params = new URLSearchParams(window.location.search);
    if (sortVal === '') {
        params.delete('sort');
    } else {
        params.set('sort', sortVal);
    }
    var qs = params.toString();
    window.location.href = 'packages.php' + (qs ? '?' + qs : '');
}
</script>

<?php require_once 'includes/footer.php'; ?>
