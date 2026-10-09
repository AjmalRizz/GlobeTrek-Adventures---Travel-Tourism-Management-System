<?php
// customer_actions.php - Process Customer Actions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer role

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        header("Location: ../customer/dashboard.php?error=csrf_invalid");
        exit();
    }

    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($full_name) || empty($phone)) {
            header("Location: ../customer/dashboard.php?error=empty_fields");
            exit();
        }

        try {
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    header("Location: ../customer/dashboard.php?error=passwords_mismatch");
                    exit();
                }
                if (strlen($password) < 6) {
                    header("Location: ../customer/dashboard.php?error=password_weak");
                    exit();
                }

                $hashed_pw = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $hashed_pw, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $phone, $user_id]);
            }

            // Update session values
            $_SESSION['full_name'] = $full_name;

            log_event($pdo, 'PROFILE_UPDATED', "User updated profile details.");
            header("Location: ../customer/dashboard.php?success=profile_updated");
            exit();

        } catch (PDOException $e) {
            header("Location: ../customer/dashboard.php?error=db_error");
            exit();
        }

    } elseif ($action === 'custom_plan') {
        $destination = trim($_POST['destination']);
        $duration_days = intval($_POST['duration_days']);
        $accommodation_type = trim($_POST['accommodation_type']);
        $transport_preference = trim($_POST['transport_preference']);
        $activities = trim($_POST['activities']);
        $budget = floatval($_POST['budget']);
        $special_requests = trim($_POST['special_requests']);

        if (empty($destination) || $duration_days <= 0 || empty($accommodation_type) || empty($transport_preference) || empty($activities) || $budget <= 0) {
            header("Location: ../customer/custom_plans.php?error=empty_fields");
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO custom_travel_requests (user_id, destination, duration_days, accommodation_type, transport_preference, activities, budget, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $destination, $duration_days, $accommodation_type, $transport_preference, $activities, $budget, $special_requests, 'pending']);

            log_event($pdo, 'CUSTOM_PLAN_REQUESTED', "Custom trip request to $destination submitted.");
            header("Location: ../customer/custom_plans.php?success=request_submitted");
            exit();

        } catch (PDOException $e) {
            header("Location: ../customer/custom_plans.php?error=db_error");
            exit();
        }
    }

} else {
    // GET actions
    if ($action === 'cancel_booking') {
        $booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($booking_id <= 0) {
            header("Location: ../customer/bookings.php?error=booking_not_found");
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Check if booking belongs to user and is pending or confirmed
            $stmt = $pdo->prepare("SELECT status, booking_ref FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $user_id]);
            $bk = $stmt->fetch();

            if (!$bk) {
                $pdo->rollBack();
                header("Location: ../customer/bookings.php?error=booking_not_found");
                exit();
            }

            if ($bk['status'] === 'completed' || $bk['status'] === 'cancelled') {
                $pdo->rollBack();
                header("Location: ../customer/bookings.php?error=cannot_cancel");
                exit();
            }

            // Update status
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$booking_id]);

            // Log details
            $stmt = $pdo->prepare("INSERT INTO booking_details (booking_id, status, notes, updated_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$booking_id, 'cancelled', 'Booking cancelled by customer.', $user_id]);

            log_event($pdo, 'BOOKING_CANCELLED', "Booking " . $bk['booking_ref'] . " cancelled by customer.");

            $pdo->commit();
            header("Location: ../customer/bookings.php?success=booking_cancelled");
            exit();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            header("Location: ../customer/bookings.php?error=db_error");
            exit();
        }
    }
}
header("Location: ../customer/dashboard.php");
exit();
