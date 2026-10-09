<?php
// listings.php - Staff Listings CRUD Manager (Accommodations and Transports)
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce staff/admin login
require_role([2, 3]);

$user_name = $_SESSION['full_name'];
$accommodations = [];
$transports = [];

if ($pdo) {
    try {
        $accommodations = $pdo->query("SELECT * FROM accommodations ORDER BY id DESC")->fetchAll();
        $transports = $pdo->query("SELECT * FROM transport_services ORDER BY id DESC")->fetchAll();
    } catch (PDOException $e) {
        // Fallback
    }
}

require_once '../includes/header.php';

// Alerts
$error_msg = '';
$success_msg = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields': $error_msg = 'Please fill in all listing details.'; break;
        case 'csrf_invalid': $error_msg = 'Security token invalid. Please refresh the page.'; break;
        case 'db_error': $error_msg = 'A database error occurred. Please try again.'; break;
        case 'invalid_id': $error_msg = 'Invalid listing ID reference.'; break;
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'acc_added': $success_msg = 'Partner accommodation added successfully!'; break;
        case 'acc_deleted': $success_msg = 'Partner accommodation deleted successfully!'; break;
        case 'trans_added': $success_msg = 'Transportation service added successfully!'; break;
        case 'trans_deleted': $success_msg = 'Transportation service deleted successfully!'; break;
    }
}
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Staff Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Listings</li>
        </ol>
    </nav>

    <!-- Alerts -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="sidebar d-flex flex-column gap-2">
                <div class="text-center pb-3 border-bottom mb-3">
                    <i class="bi bi-person-badge-fill text-secondary" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?> (Staff)</h5>
                    <span class="badge bg-light text-secondary border mt-1">Tour Coordinator</span>
                </div>
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i>Overview</a>
                <a class="nav-link" href="packages.php"><i class="bi bi-airplane"></i>Manage Packages</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-quote"></i>Inquiries</a>
                <a class="nav-link active" href="listings.php"><i class="bi bi-building"></i>Listings CRUD</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2" id="listingTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" id="acc-tab" data-bs-toggle="tab" data-bs-target="#acc-pane" type="button" role="tab" aria-controls="acc-pane" aria-selected="true"><i class="bi bi-building me-2"></i>Accommodations</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="trans-tab" data-bs-toggle="tab" data-bs-target="#trans-pane" type="button" role="tab" aria-controls="trans-pane" aria-selected="false"><i class="bi bi-car-front me-2"></i>Transportation</button>
                </li>
            </ul>

            <div class="tab-content" id="listingTabContent">
                
                <!-- Accommodations Panel -->
                <div class="tab-pane fade show active" id="acc-pane" role="tabpanel" aria-labelledby="acc-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                                <h5 class="fw-bold text-dark mb-3">Add Accommodation</h5>
                                <form action="../actions/staff_actions.php?action=add_accommodation" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                    
                                    <div class="mb-3">
                                        <label for="acc_name" class="form-label small fw-semibold">Name</label>
                                        <input type="text" class="form-control form-control-custom" id="acc_name" name="name" placeholder="e.g. Jetwing Blue" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="acc_type" class="form-label small fw-semibold">Type</label>
                                        <select class="form-select form-control-custom" id="acc_type" name="type">
                                            <option value="Hotel">Hotel</option>
                                            <option value="Resort">Resort</option>
                                            <option value="Guest House">Guest House</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="acc_rating" class="form-label small fw-semibold">Rating</label>
                                            <input type="number" class="form-control form-control-custom" id="acc_rating" name="rating" min="1" max="5" step="0.1" value="4.5" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="acc_price" class="form-label small fw-semibold">Price per Night</label>
                                            <input type="number" class="form-control form-control-custom" id="acc_price" name="price" min="0" placeholder="e.g. 150" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="acc_location" class="form-label small fw-semibold">Location</label>
                                        <input type="text" class="form-control form-control-custom" id="acc_location" name="location" placeholder="e.g. Negombo Beach, Sri Lanka" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="acc_desc" class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control form-control-custom" id="acc_desc" name="description" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label for="acc_img" class="form-label small fw-semibold">Image Path</label>
                                        <select class="form-select form-control-custom" id="acc_img" name="image_path">
                                            <option value="assets/images/jetwing_blue.jpg">Jetwing Blue Resort</option>
                                            <option value="assets/images/heritance_negombo.jpg">Heritance Negombo Hotel</option>
                                            <option value="assets/images/beach_guesthouse.jpg">Beach Guesthouse</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-custom-primary text-white rounded-pill px-4 w-100 fw-bold">Add Listing</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                                <h5 class="fw-bold text-dark mb-3">Accommodation Catalog</h5>
                                <?php if (!empty($accommodations)): ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th class="py-3 px-3">Name</th>
                                                    <th class="py-3">Type</th>
                                                    <th class="py-3 text-end">Price (<span class="currency-suffix">USD</span>)</th>
                                                    <th class="py-3 text-center">Rating</th>
                                                    <th class="py-3 text-end px-3">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($accommodations as $acc): ?>
                                                    <tr class="border-bottom">
                                                        <td class="py-3 px-3 fw-bold text-dark small"><?php echo htmlspecialchars($acc['name']); ?></td>
                                                        <td class="py-3 small"><?php echo htmlspecialchars($acc['type']); ?></td>
                                                        <td class="py-3 text-end small"><span class="price-display" data-usd="<?php echo htmlspecialchars($acc['price_per_night']); ?>">$<?php echo number_format($acc['price_per_night'], 2); ?></span></td>
                                                        <td class="py-3 text-center small"><i class="bi bi-star-fill text-warning me-1"></i><?php echo htmlspecialchars($acc['rating']); ?></td>
                                                        <td class="py-3 text-end px-3">
                                                            <a href="../actions/staff_actions.php?action=delete_accommodation&id=<?php echo $acc['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="return confirm('Delete this accommodation?');">Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4 small mb-0">No accommodations added yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transportation Panel -->
                <div class="tab-pane fade" id="trans-pane" role="tabpanel" aria-labelledby="trans-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                                <h5 class="fw-bold text-dark mb-3">Add Transport Service</h5>
                                <form action="../actions/staff_actions.php?action=add_transport" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                    
                                    <div class="mb-3">
                                        <label for="trans_name" class="form-label small fw-semibold">Service Name</label>
                                        <input type="text" class="form-control form-control-custom" id="trans_name" name="name" placeholder="e.g. Airport Shuttle" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="trans_type" class="form-label small fw-semibold">Category Type</label>
                                        <select class="form-select form-control-custom" id="trans_type" name="type">
                                            <option value="Airport Transfer">Airport Transfer</option>
                                            <option value="Private Vehicle">Private Vehicle</option>
                                            <option value="Tour Bus">Tour Bus</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="trans_cap" class="form-label small fw-semibold">Capacity (Pass)</label>
                                            <input type="number" class="form-control form-control-custom" id="trans_cap" name="capacity" min="1" value="4" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="trans_price" class="form-label small fw-semibold">Base Price (USD)</label>
                                            <input type="number" class="form-control form-control-custom" id="trans_price" name="price" min="0" placeholder="e.g. 50" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="trans_desc" class="form-label small fw-semibold">Description</label>
                                        <textarea class="form-control form-control-custom" id="trans_desc" name="description" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label for="trans_img" class="form-label small fw-semibold">Image Path</label>
                                        <select class="form-select form-control-custom" id="trans_img" name="image_path">
                                            <option value="assets/images/airport_transfer.jpg">Airport Sedan Transfer</option>
                                            <option value="assets/images/luxury_suv.jpg">Luxury SUV Chauffeur</option>
                                            <option value="assets/images/tour_bus.jpg">Coaster Tour Bus</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-custom-primary text-white rounded-pill px-4 w-100 fw-bold">Add Fleet</button>
                                </form>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                                <h5 class="fw-bold text-dark mb-3">Transportation Catalog</h5>
                                <?php if (!empty($transports)): ?>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr class="bg-light">
                                                    <th class="py-3 px-3">Service Name</th>
                                                    <th class="py-3">Type</th>
                                                    <th class="py-3 text-center">Capacity</th>
                                                    <th class="py-3 text-end">Price (<span class="currency-suffix">USD</span>)</th>
                                                    <th class="py-3 text-end px-3">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($transports as $tr): ?>
                                                    <tr class="border-bottom">
                                                        <td class="py-3 px-3 fw-bold text-dark small"><?php echo htmlspecialchars($tr['name']); ?></td>
                                                        <td class="py-3 small"><?php echo htmlspecialchars($tr['type']); ?></td>
                                                        <td class="py-3 text-center small"><?php echo htmlspecialchars($tr['capacity']); ?> Pass</td>
                                                        <td class="py-3 text-end small"><span class="price-display" data-usd="<?php echo htmlspecialchars($tr['price']); ?>">$<?php echo number_format($tr['price'], 2); ?></span></td>
                                                        <td class="py-3 text-end px-3">
                                                            <a href="../actions/staff_actions.php?action=delete_transport&id=<?php echo $tr['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" onclick="return confirm('Delete this fleet listing?');">Delete</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-4 small mb-0">No transport listings added yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
