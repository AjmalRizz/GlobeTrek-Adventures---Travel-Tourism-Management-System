<?php
// package_details.php - Detailed Tour Package Info & Booking Form
require_once 'config/db.php';
require_once 'includes/auth_helper.php';
require_once 'includes/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pkg = null;
$images = [];

if ($pdo && $id > 0) {
    try {
        // Fetch package details
        $stmt = $pdo->prepare("SELECT * FROM travel_packages WHERE id = ?");
        $stmt->execute([$id]);
        $pkg = $stmt->fetch();

        if ($pkg) {
            // Fetch package images
            $stmt = $pdo->prepare("SELECT * FROM package_images WHERE package_id = ?");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        // fallback
    }
}

if (!$pkg) {
    header("Location: 404.php");
    exit();
}

$is_customer = (is_logged_in() && $_SESSION['role_id'] == 1);
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="packages.php" class="text-decoration-none">Tour Packages</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($pkg['title']); ?></li>
        </ol>
    </nav>

    <!-- Package Title and Basic Info -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-dark"><?php echo htmlspecialchars($pkg['title']); ?></h1>
            <div class="d-flex flex-wrap gap-3 mt-2 small text-muted">
                <span><i class="bi bi-geo-alt-fill text-primary me-1"></i><?php echo htmlspecialchars($pkg['destination']); ?></span>
                <span><i class="bi bi-clock-fill text-primary me-1"></i><?php echo htmlspecialchars($pkg['duration_days']); ?> Days / <?php echo intval($pkg['duration_days']) - 1; ?> Nights</span>
                <span><i class="bi bi-star-fill text-warning me-1"></i>4.8 (Verified reviews)</span>
                <span class="badge <?php echo $pkg['status'] === 'available' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> rounded-pill border">
                    <?php echo ucfirst($pkg['status']); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main details (col-lg-8) -->
        <div class="col-lg-8">
            <!-- Big Image Banner -->
            <div class="rounded-4 overflow-hidden shadow-sm mb-5" style="height: 400px;">
                <?php 
                $img_src = !empty($images) ? $images[0]['image_path'] : 'assets/images/kandylake.jpeg';
                ?>
                <img src="<?php echo htmlspecialchars($img_src); ?>"'assets/images/sigiriya.jpg" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($pkg['title']); ?>">
            </div>

            <!-- Description -->
            <h3 class="fw-bold text-dark mb-3">Tour Overview</h3>
            <p class="text-muted" style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($pkg['description'])); ?></p>
            
            <!-- Core Activities -->
            <div class="my-4 p-3 bg-light rounded-3 d-flex flex-wrap align-items-center gap-3">
                <span class="fw-bold small text-dark"><i class="bi bi-tags-fill me-2 text-primary"></i>Core Activities:</span>
                <?php 
                $acts = explode(',', $pkg['activities']);
                foreach ($acts as $act): 
                ?>
                    <span class="badge bg-white text-secondary border rounded-pill px-3 py-2 small fw-semibold"><?php echo htmlspecialchars(trim($act)); ?></span>
                <?php endforeach; ?>
            </div>

            <!-- Itinerary Accordion -->
            <h3 class="fw-bold text-dark mt-5 mb-4">Detailed Itinerary</h3>
            <div class="accordion border-0 shadow-sm rounded-4 overflow-hidden mb-5" id="itineraryAccordion">
                <?php 
                $lines = explode("\n", $pkg['itinerary']);
                $day_count = 1;
                foreach ($lines as $line):
                    if (empty(trim($line))) continue;
                    // Standard day format e.g. "Day 1: Arrival & Lagoon"
                    $parts = explode(':', $line, 2);
                    $day_title = isset($parts[0]) ? trim($parts[0]) : "Day $day_count";
                    $day_desc = isset($parts[1]) ? trim($parts[1]) : trim($line);
                ?>
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $day_count > 1 ? 'collapsed' : ''; ?> fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDay<?php echo $day_count; ?>" aria-expanded="<?php echo $day_count === 1 ? 'true' : 'false'; ?>" aria-controls="collapseDay<?php echo $day_count; ?>">
                                <span class="bg-primary-light text-primary rounded-circle px-2 py-1 small me-2" style="font-size:0.75rem;"><?php echo $day_title; ?></span>
                            </button>
                        </h2>
                        <div id="collapseDay<?php echo $day_count; ?>" class="accordion-collapse collapse <?php echo $day_count === 1 ? 'show' : ''; ?>" data-bs-parent="#itineraryAccordion">
                            <div class="accordion-body text-muted small py-3" style="line-height:1.7;">
                                <?php echo htmlspecialchars($day_desc); ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    $day_count++;
                endforeach; 
                ?>
            </div>

            <!-- Included and Excluded -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white h-100">
                        <h5 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>Included Services</h5>
                        <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                            <?php 
                            $inc = explode(',', $pkg['included']);
                            foreach ($inc as $i): 
                            ?>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-check-lg text-success mt-1"></i>
                                    <span><?php echo htmlspecialchars(trim($i)); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white h-100">
                        <h5 class="fw-bold text-danger mb-3"><i class="bi bi-x-circle-fill me-2"></i>Excluded Services</h5>
                        <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                            <?php 
                            $exc = explode(',', $pkg['excluded']);
                            foreach ($exc as $e): 
                            ?>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="bi bi-x-lg text-danger mt-1"></i>
                                    <span><?php echo htmlspecialchars(trim($e)); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side column booking card (col-lg-4) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg p-4 rounded-4 bg-white sticky-top border-top border-primary border-4" style="top: 100px; z-index: 10;">
                <div class="pb-3 border-bottom mb-4">
                    <span class="text-muted small d-block">Price per Person</span>
                    <div class="d-flex align-items-baseline gap-1">
                        <span class="fs-2 fw-bold text-primary price-display" data-usd="<?php echo htmlspecialchars($pkg['price']); ?>">$<?php echo number_format($pkg['price'], 2); ?></span>
                        <span class="text-muted small currency-suffix">USD</span>
                    </div>
                </div>

                <?php if ($pkg['status'] !== 'available'): ?>
                    <div class="alert alert-danger border-0 rounded-3 text-center mb-0" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i> Currently Unavailable
                    </div>
                <?php elseif ($is_customer): ?>
                    <!-- Booking Form -->
                    <h5 class="fw-bold mb-3 text-dark">Book This Package</h5>
                    <form action="actions/booking.php" method="POST" id="bookingForm">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        <input type="hidden" name="package_id" value="<?php echo $pkg['id']; ?>">
                        <input type="hidden" name="price_per_person" id="pricePerPerson" value="<?php echo $pkg['price']; ?>">
                        
                        <div class="mb-3">
                            <label for="travel_date" class="form-label small fw-semibold text-secondary">Travel Date</label>
                            <input type="date" class="form-control form-control-custom" id="travel_date" name="travel_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="num_travelers" class="form-label small fw-semibold text-secondary">Number of Travelers</label>
                            <input type="number" class="form-control form-control-custom" id="num_travelers" name="num_travelers" min="1" max="15" value="1" required onchange="calcTotal()">
                        </div>

                        <div class="mb-4">
                            <label for="special_requests" class="form-label small fw-semibold text-secondary">Special Requests (Optional)</label>
                            <textarea class="form-control form-control-custom" id="special_requests" name="special_requests" rows="3" placeholder="e.g. Vegetarian diet, baby cot, airport pick-up time..."></textarea>
                        </div>

                        <!-- Calculation summary display -->
                        <div class="bg-light p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold text-muted">Est. Total Price:</span>
                            <span class="fs-4 fw-bold text-dark price-display" id="totalPriceDisplay" data-usd="<?php echo htmlspecialchars($pkg['price']); ?>">$<?php echo number_format($pkg['price'], 2); ?></span>
                        </div>

                        <button type="submit" class="btn btn-custom-primary w-100 py-3 rounded-pill text-white fw-bold">Proceed to Booking</button>
                    </form>
                <?php else: ?>
                    <!-- Redirect Prompt -->
                    <div class="text-center py-3">
                        <p class="text-muted small mb-4">Please log in as a Traveler to book this customized tour package.</p>
                        <a href="login.php" class="btn btn-custom-primary text-white w-100 rounded-pill py-2 fw-semibold mb-2">Log In to Book</a>
                        <a href="register.php" class="small text-decoration-none text-secondary">Don't have an account? Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function calcTotal() {
    const ppp = parseFloat(document.getElementById('pricePerPerson').value);
    const count = parseInt(document.getElementById('num_travelers').value);
    const display = document.getElementById('totalPriceDisplay');
    
    if (isNaN(count) || count < 1) {
        display.setAttribute('data-usd', '0');
        display.innerHTML = window.formatPrice(0);
        return;
    }
    
    const total = ppp * count;
    display.setAttribute('data-usd', total.toString());
    display.innerHTML = window.formatPrice(total);
}
</script>

<?php require_once 'includes/footer.php'; ?>
