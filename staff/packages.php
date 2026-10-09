<?php
// packages.php - Staff Package CRUD Manager
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce staff/admin login
require_role([2, 3]);

$user_name = $_SESSION['full_name'];
$packages = [];
$edit_pkg = null;
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

if ($pdo) {
    try {
        // Fetch all packages
        $packages = $pdo->query("SELECT * FROM travel_packages ORDER BY id DESC")->fetchAll();
        
        // If edit mode requested, fetch details
        if ($edit_id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM travel_packages WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_pkg = $stmt->fetch();
        }
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
        case 'empty_fields': $error_msg = 'Please fill in all package details.'; break;
        case 'csrf_invalid': $error_msg = 'Security token invalid. Please refresh the page.'; break;
        case 'db_error': $error_msg = 'A database error occurred. Please try again.'; break;
        case 'active_bookings': $error_msg = 'Cannot delete this package because it has active confirmed bookings associated with it.'; break;
        case 'invalid_id': $error_msg = 'Invalid package reference ID.'; break;
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'package_added': $success_msg = 'New tour package created successfully!'; break;
        case 'package_updated': $success_msg = 'Tour package details updated successfully!'; break;
        case 'package_deleted': $success_msg = 'Tour package deleted successfully!'; break;
    }
}
?>

<div class="container py-5 animated-item">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Staff Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Packages</li>
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
                <a class="nav-link active" href="packages.php"><i class="bi bi-airplane"></i>Manage Packages</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-journal-text"></i>Manage Bookings</a>
                <a class="nav-link" href="custom_plans.php"><i class="bi bi-sliders"></i>Custom Trips</a>
                <a class="nav-link" href="inquiries.php"><i class="bi bi-chat-left-quote"></i>Inquiries</a>
                <a class="nav-link" href="listings.php"><i class="bi bi-building"></i>Listings CRUD</a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="col-lg-9">
            <div class="row g-4">
                
                <!-- Package Form (Add or Edit) -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white border-top border-primary border-4">
                        <h4 class="fw-bold mb-3"><?php echo $edit_pkg ? 'Edit Tour Package' : 'Create New Tour Package'; ?></h4>
                        
                        <form action="../actions/staff_actions.php?action=<?php echo $edit_pkg ? 'edit_package' : 'add_package'; ?>" method="POST" id="packageManageForm">
                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                            <?php if ($edit_pkg): ?>
                                <input type="hidden" name="package_id" value="<?php echo $edit_pkg['id']; ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label small fw-semibold">Package Title</label>
                                    <input type="text" class="form-control form-control-custom" id="title" name="title" placeholder="e.g. Cultural Triangle Explorer" value="<?php echo $edit_pkg ? htmlspecialchars($edit_pkg['title']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="destination" class="form-label small fw-semibold">Destination</label>
                                    <input type="text" class="form-control form-control-custom" id="destination" name="destination" placeholder="e.g. Nuwara Eliya, Sri Lanka" value="<?php echo $edit_pkg ? htmlspecialchars($edit_pkg['destination']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="duration_days" class="form-label small fw-semibold">Duration (Days)</label>
                                    <input type="number" class="form-control form-control-custom" id="duration_days" name="duration_days" min="1" value="<?php echo $edit_pkg ? htmlspecialchars($edit_pkg['duration_days']) : '4'; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label small fw-semibold">Price per Traveler (USD)</label>
                                    <input type="number" class="form-control form-control-custom" id="price" name="price" min="10" placeholder="e.g. 250" value="<?php echo $edit_pkg ? htmlspecialchars($edit_pkg['price']) : ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label small fw-semibold">Status</label>
                                    <select class="form-select form-control-custom" id="status" name="status">
                                        <option value="available" <?php echo ($edit_pkg && $edit_pkg['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="unavailable" <?php echo ($edit_pkg && $edit_pkg['status'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="activities" class="form-label small fw-semibold">Core Activities (Comma separated)</label>
                                <input type="text" class="form-control form-control-custom" id="activities" name="activities" placeholder="e.g. Hiking, Boat safari, Temple explorer" value="<?php echo $edit_pkg ? htmlspecialchars($edit_pkg['activities']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label small fw-semibold">Short Description</label>
                                <textarea class="form-control form-control-custom" id="description" name="description" rows="3" placeholder="Provide an exciting summary of the package..." required><?php echo $edit_pkg ? htmlspecialchars($edit_pkg['description']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="itinerary" class="form-label small fw-semibold">Day-by-Day Itinerary (Format - Day 1: Activity description. New line for next day)</label>
                                <textarea class="form-control form-control-custom" id="itinerary" name="itinerary" rows="5" placeholder="Day 1: Arrival & transfer to hotel.&#10;Day 2: Morning lagoon tour..." required><?php echo $edit_pkg ? htmlspecialchars($edit_pkg['itinerary']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="included" class="form-label small fw-semibold">Included Services (Comma separated)</label>
                                    <textarea class="form-control form-control-custom" id="included" name="included" rows="3" placeholder="e.g. AC transport, guide, entrance fees, lunch" required><?php echo $edit_pkg ? htmlspecialchars($edit_pkg['included']) : ''; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="excluded" class="form-label small fw-semibold">Excluded Services (Comma separated)</label>
                                    <textarea class="form-control form-control-custom" id="excluded" name="excluded" rows="3" placeholder="e.g. Flights, tips, alcoholic drinks, personal insurance" required><?php echo $edit_pkg ? htmlspecialchars($edit_pkg['excluded']) : ''; ?></textarea>
                                </div>
                            </div>

                            <?php if (!$edit_pkg): ?>
                                <div class="mb-4">
                                    <label for="image_path" class="form-label small fw-semibold">Tour Image (Placeholder URL or local path)</label>
                                    <select class="form-select form-control-custom" id="image_path" name="image_path">
                                        <option value="assets/images/sigiriya.jpg">Sigiriya Lion Rock Fortress</option>
                                        <option value="assets/images/negombo_lagoon.jpg">Negombo Lagoon Boat safari</option>
                                        <option value="assets/images/galle.jpg">Galle Dutch Fort Coastline</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-custom-primary rounded-pill px-4 text-white fw-bold">
                                    <?php echo $edit_pkg ? 'Save Updates' : 'Add Package'; ?>
                                </button>
                                <?php if ($edit_pkg): ?>
                                    <a href="packages.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel Edit</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Package Listings Table -->
                <div class="col-12 mt-4">
                    <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                        <h4 class="fw-bold mb-4">Current Active Packages</h4>
                        
                        <?php if (!empty($packages)): ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="py-3 px-3">Title</th>
                                            <th class="py-3">Destination</th>
                                            <th class="py-3 text-center">Days</th>
                                            <th class="py-3 text-end">Price (<span class="currency-suffix">USD</span>)</th>
                                            <th class="py-3 text-center">Status</th>
                                            <th class="py-3 text-end px-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($packages as $pkg): ?>
                                            <tr class="border-bottom">
                                                <td class="py-3 px-3 fw-bold text-dark small"><?php echo htmlspecialchars($pkg['title']); ?></td>
                                                <td class="py-3 small text-muted"><?php echo htmlspecialchars($pkg['destination']); ?></td>
                                                <td class="py-3 text-center small"><?php echo htmlspecialchars($pkg['duration_days']); ?></td>
                                                <td class="py-3 text-end fw-bold text-primary small"><span class="price-display" data-usd="<?php echo htmlspecialchars($pkg['price']); ?>">$<?php echo number_format($pkg['price'], 2); ?></span></td>
                                                <td class="py-3 text-center">
                                                    <span class="badge <?php echo $pkg['status'] === 'available' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger'; ?> rounded-pill px-2 py-1 small" style="font-size:0.7rem;">
                                                        <?php echo ucfirst($pkg['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 text-end px-3">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="packages.php?edit=<?php echo $pkg['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Edit</a>
                                                        <a href="../actions/staff_actions.php?action=delete_package&id=<?php echo $pkg['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                           onclick="return confirm('Are you sure you want to delete this package? This cannot be undone.');">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-4 small mb-0">No tour packages found.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
