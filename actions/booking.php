<?php
// booking.php - Process Customer Tour Package Booking

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Enforce customer role
require_role(1);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        header("Location: ../packages.php?error=csrf_invalid");
        exit();
    }

    $package_id = intval($_POST['package_id']);
    $travel_date = trim($_POST['travel_date']);
    $num_travelers = intval($_POST['num_travelers']);
    $special_requests = trim($_POST['special_requests']);

    if ($package_id <= 0 || empty($travel_date) || $num_travelers <= 0) {
        header("Location: ../package_details.php?id=$package_id&error=empty_fields");
        exit();
    }

    try {
        // Fetch package details to check price & status
        $stmt = $pdo->prepare("SELECT price, status FROM travel_packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $pkg = $stmt->fetch();

        if (!$pkg || $pkg['status'] !== 'available') {
            header("Location: ../packages.php?error=package_unavailable");
            exit();
        }

        // Calculate total price
        $total_price = floatval($pkg['price']) * $num_travelers;

        // Generate unique booking reference number: GT-XXXXXX
        $booking_ref = '';
        $is_unique = false;
        while (!$is_unique) {
            $rand_num = strval(rand(100000, 999999));
            $booking_ref = "GT-" . $rand_num;

            // Check db
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_ref = ?");
            $stmt->execute([$booking_ref]);
            if ($stmt->fetchColumn() == 0) {
                $is_unique = true;
            }
        }

        $pdo->beginTransaction();

        // 1. Insert into bookings
        $stmt = $pdo->prepare("INSERT INTO bookings (booking_ref, user_id, package_id, travel_date, num_travelers, special_requests, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$booking_ref, $user_id, $package_id, $travel_date, $num_travelers, $special_requests, $total_price, 'pending']);
        $booking_id = $pdo->lastInsertId();

        // 2. Insert into booking_details (history)
        $stmt = $pdo->prepare("INSERT INTO booking_details (booking_id, status, notes, updated_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$booking_id, 'pending', 'Booking created by customer. Awaiting payment.', $user_id]);

        $pdo->commit();

        log_event($pdo, 'BOOKING_CREATED', "Booking $booking_ref created by user ID $user_id. Total: $$total_price");

        // Redirect to payment screen
        header("Location: ../payment.php?booking_ref=$booking_ref&success=booking_recorded");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: ../package_details.php?id=$package_id&error=db_error");
        exit();
    }
} else {
    header("Location: ../packages.php");
    exit();
}
