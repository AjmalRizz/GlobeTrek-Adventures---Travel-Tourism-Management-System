<?php
// staff_actions.php - Process Staff Actions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce Staff (2) or Admin (3) roles
require_role([2, 3]);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        header("Location: ../staff/dashboard.php?error=csrf_invalid");
        exit();
    }

    if ($action === 'respond_inquiry') {
        $inquiry_id = intval($_POST['inquiry_id']);
        $response = trim($_POST['response']);

        if ($inquiry_id <= 0 || empty($response)) {
            header("Location: ../staff/inquiries.php?error=empty_fields");
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE inquiries SET response = ?, responded_by = ?, status = 'resolved' WHERE id = ?");
            $stmt->execute([$response, $user_id, $inquiry_id]);
            
            log_event($pdo, 'INQUIRY_RESPONDED', "Staff responded to inquiry ticket ID: $inquiry_id");
            header("Location: ../staff/inquiries.php?success=responded");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/inquiries.php?error=db_error");
            exit();
        }

    } elseif ($action === 'respond_custom_plan') {
        $request_id = intval($_POST['request_id']);
        $staff_response = trim($_POST['staff_response']);

        if ($request_id <= 0 || empty($staff_response)) {
            header("Location: ../staff/custom_plans.php?error=empty_fields");
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE custom_travel_requests SET staff_response = ?, reviewed_by = ?, status = 'responded' WHERE id = ?");
            $stmt->execute([$staff_response, $user_id, $request_id]);
            
            log_event($pdo, 'CUSTOM_PLAN_RESPONDED', "Staff replied to custom plan request ID: $request_id");
            header("Location: ../staff/custom_plans.php?success=responded");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/custom_plans.php?error=db_error");
            exit();
        }

    } elseif ($action === 'add_package') {
        $title = trim($_POST['title']);
        $destination = trim($_POST['destination']);
        $duration_days = intval($_POST['duration_days']);
        $activities = trim($_POST['activities']);
        $price = floatval($_POST['price']);
        $status = trim($_POST['status']);
        $description = trim($_POST['description']);
        $itinerary = trim($_POST['itinerary']);
        $included = trim($_POST['included']);
        $excluded = trim($_POST['excluded']);
        $image_path = trim($_POST['image_path']); // academic placeholder image

        if (empty($title) || empty($destination) || $duration_days <= 0 || empty($activities) || $price <= 0 || empty($description) || empty($itinerary) || empty($included) || empty($excluded)) {
            header("Location: ../staff/packages.php?error=empty_fields");
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO travel_packages (title, destination, duration_days, activities, price, status, description, itinerary, included, excluded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $destination, $duration_days, $activities, $price, $status, $description, $itinerary, $included, $excluded]);
            $package_id = $pdo->lastInsertId();

            // Insert image path
            if (empty($image_path)) {
                $image_path = 'assets/images/sigiriya.jpg';
            }
            $stmt = $pdo->prepare("INSERT INTO package_images (package_id, image_path, is_featured) VALUES (?, ?, ?)");
            $stmt->execute([$package_id, $image_path, 1]);

            $pdo->commit();

            log_event($pdo, 'PACKAGE_CREATED', "New tour package created: $title");
            header("Location: ../staff/packages.php?success=package_added");
            exit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            header("Location: ../staff/packages.php?error=db_error");
            exit();
        }

    } elseif ($action === 'edit_package') {
        $package_id = intval($_POST['package_id']);
        $title = trim($_POST['title']);
        $destination = trim($_POST['destination']);
        $duration_days = intval($_POST['duration_days']);
        $activities = trim($_POST['activities']);
        $price = floatval($_POST['price']);
        $status = trim($_POST['status']);
        $description = trim($_POST['description']);
        $itinerary = trim($_POST['itinerary']);
        $included = trim($_POST['included']);
        $excluded = trim($_POST['excluded']);

        if ($package_id <= 0 || empty($title) || empty($destination) || $duration_days <= 0 || empty($activities) || $price <= 0 || empty($description) || empty($itinerary) || empty($included) || empty($excluded)) {
            header("Location: ../staff/packages.php?error=empty_fields&edit=$package_id");
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE travel_packages SET title = ?, destination = ?, duration_days = ?, activities = ?, price = ?, status = ?, description = ?, itinerary = ?, included = ?, excluded = ? WHERE id = ?");
            $stmt->execute([$title, $destination, $duration_days, $activities, $price, $status, $description, $itinerary, $included, $excluded, $package_id]);
            
            log_event($pdo, 'PACKAGE_UPDATED', "Tour package ID: $package_id updated.");
            header("Location: ../staff/packages.php?success=package_updated");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/packages.php?error=db_error&edit=$package_id");
            exit();
        }

    } elseif ($action === 'add_accommodation') {
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $rating = floatval($_POST['rating']);
        $location = trim($_POST['location']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $image_path = trim($_POST['image_path']);

        if (empty($name) || empty($type) || $rating <= 0 || empty($location) || $price <= 0 || empty($description)) {
            header("Location: ../staff/listings.php?error=empty_fields");
            exit();
        }

        try {
            if (empty($image_path)) {
                $image_path = 'assets/images/jetwing_blue.jpg';
            }
            $stmt = $pdo->prepare("INSERT INTO accommodations (name, type, rating, location, price_per_night, image_path, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $rating, $location, $price, $image_path, $description]);

            log_event($pdo, 'ACCOMMODATION_CREATED', "New partner accommodation listed: $name");
            header("Location: ../staff/listings.php?success=acc_added");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/listings.php?error=db_error");
            exit();
        }

    } elseif ($action === 'add_transport') {
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $capacity = intval($_POST['capacity']);
        $price = floatval($_POST['price']);
        $description = trim($_POST['description']);
        $image_path = trim($_POST['image_path']);

        if (empty($name) || empty($type) || $capacity <= 0 || $price <= 0 || empty($description)) {
            header("Location: ../staff/listings.php?error=empty_fields");
            exit();
        }

        try {
            if (empty($image_path)) {
                $image_path = 'assets/images/airport_transfer.jpg';
            }
            $stmt = $pdo->prepare("INSERT INTO transport_services (name, type, capacity, price, description, image_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $type, $capacity, $price, $description, $image_path]);

            log_event($pdo, 'TRANSPORT_CREATED', "New transportation service listed: $name");
            header("Location: ../staff/listings.php?success=trans_added");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/listings.php?error=db_error");
            exit();
        }
    }

} else {
    // GET Actions
    if ($action === 'delete_package') {
        $package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($package_id <= 0) {
            header("Location: ../staff/packages.php?error=invalid_id");
            exit();
        }

        try {
            // Check if active bookings exist on package to protect referential integrity
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE package_id = ? AND status = 'confirmed'");
            $stmt->execute([$package_id]);
            if ($stmt->fetchColumn() > 0) {
                header("Location: ../staff/packages.php?error=active_bookings");
                exit();
            }

            $stmt = $pdo->prepare("DELETE FROM travel_packages WHERE id = ?");
            $stmt->execute([$package_id]);

            log_event($pdo, 'PACKAGE_DELETED', "Tour package ID: $package_id deleted.");
            header("Location: ../staff/packages.php?success=package_deleted");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/packages.php?error=db_error");
            exit();
        }

    } elseif ($action === 'delete_accommodation') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            header("Location: ../staff/listings.php?error=invalid_id");
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM accommodations WHERE id = ?");
            $stmt->execute([$id]);

            log_event($pdo, 'ACCOMMODATION_DELETED', "Partner accommodation ID: $id deleted.");
            header("Location: ../staff/listings.php?success=acc_deleted");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/listings.php?error=db_error");
            exit();
        }

    } elseif ($action === 'delete_transport') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            header("Location: ../staff/listings.php?error=invalid_id");
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM transport_services WHERE id = ?");
            $stmt->execute([$id]);

            log_event($pdo, 'TRANSPORT_DELETED', "Transportation service ID: $id deleted.");
            header("Location: ../staff/listings.php?success=trans_deleted");
            exit();
        } catch (PDOException $e) {
            header("Location: ../staff/listings.php?error=db_error");
            exit();
        }

    } elseif ($action === 'update_booking_status') {
        $booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';

        if ($booking_id <= 0 || !in_array($status, ['confirmed', 'pending', 'cancelled', 'completed'])) {
            header("Location: ../staff/bookings.php?error=invalid_params");
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $booking_id]);

            $stmt = $pdo->prepare("INSERT INTO booking_details (booking_id, status, notes, updated_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$booking_id, $status, "Booking status updated to " . ucfirst($status) . " by staff/admin.", $user_id]);

            $pdo->commit();

            log_event($pdo, 'BOOKING_STATUS_CHANGED', "Booking ID: $booking_id updated to $status");
            header("Location: ../staff/bookings.php?success=status_updated");
            exit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            header("Location: ../staff/bookings.php?error=db_error");
            exit();
        }
    } else {
        header("Location: ../staff/dashboard.php");
        exit();
    }
}
?>
