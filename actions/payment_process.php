<?php
// payment_process.php - Process Simulated Payment Transaction

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth_helper.php';

require_role(1); // Enforce customer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        header("Location: ../customer/bookings.php?error=csrf_invalid");
        exit();
    }

    $booking_id = intval($_POST['booking_id']);
    $booking_ref = trim($_POST['booking_ref']);
    $amount = floatval($_POST['amount']);
    $card_holder = trim($_POST['card_holder']);
    $card_number = trim($_POST['card_number']);
    $expiry = trim($_POST['expiry']);
    $cvv = trim($_POST['cvv']);

    if ($booking_id <= 0 || empty($card_holder) || empty($card_number) || empty($expiry) || empty($cvv)) {
        header("Location: ../payment.php?booking_ref=$booking_ref&error=empty_fields");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Double check the booking status is pending and belongs to this user
        $stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $pdo->rollBack();
            header("Location: ../customer/bookings.php?error=booking_unavailable");
            exit();
        }

        // 2. Generate unique payment reference: PAY-XXXXXXXXXX
        $payment_ref = 'PAY-' . strval(rand(10000000, 99999999));

        // 3. Insert into payments
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, card_holder, payment_ref, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$booking_id, $amount, strtoupper($card_holder), $payment_ref, 'success']);

        // 4. Update bookings status to 'confirmed'
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$booking_id]);

        // 5. Insert audit trial into booking_details
        $stmt = $pdo->prepare("INSERT INTO booking_details (booking_id, status, notes, updated_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$booking_id, 'confirmed', 'Payment verified successfully. Booking status updated to Confirmed.', $_SESSION['user_id']]);

        // 6. Log event
        log_event($pdo, 'PAYMENT_RECEIVED', "Payment $payment_ref received for booking $booking_ref. Amount: $$amount");

        $pdo->commit();

        // Redirect to receipt
        header("Location: ../payment_receipt.php?booking_ref=$booking_ref");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../payment.php?booking_ref=$booking_ref&error=db_error");
        exit();
    }
} else {
    header("Location: ../customer/bookings.php");
    exit();
}
